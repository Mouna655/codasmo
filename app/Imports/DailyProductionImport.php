<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\{Site, SubSite, DailyProduction, ExcelImportLog, ProductionPlan};
use App\Services\{ImportValidationService, ImportDetailLogger};
use Illuminate\Support\Facades\Auth;

/**
 * Excel importer untuk Daily Production Report
 * Support: Multiple sheets, detailed error tracking, audit trail
 *
 * Usage:
 *   $importer = new DailyProductionImport($reportDate, $importLogId);
 *   Excel::import($importer, $filePath);
 *   // Chek $importer->imported, $importer->errorLogger
 */
class DailyProductionImport implements WithMultipleSheets
{
    public string $reportDate;
    public int $imported = 0;
    public array $errors = []; // Backward compatibility
    
    private ExcelImportLog $importLog;
    private ?ImportDetailLogger $errorLogger = null;
    private ImportValidationService $validator;

    public function __construct(string $date, int $importLogId = null)
    {
        $this->reportDate = $date;
        
        // Jika importLogId diberikan, gunakan existing log
        if ($importLogId) {
            $this->importLog = ExcelImportLog::findOrFail($importLogId);
        }
        
        $this->validator = new ImportValidationService();
    }

    /**
     * Set existing import log (untuk koordinasi dengan Filament)
     */
    public function setImportLog(ExcelImportLog $log): self
    {
        $this->importLog = $log;
        $this->errorLogger = null; // 
        return $this;
    }

    /**
     * Get error logger instance
     */
    public function getErrorLogger(): ImportDetailLogger
    {
        if (!$this->errorLogger) {
            if (!isset($this->importLog)) {
                throw new \Exception('ImportLog belum di-set sebelum menggunakan logger');
            }

            $this->errorLogger = new ImportDetailLogger($this->importLog);
        }

        return $this->errorLogger;
    }

    public function sheets(): array
    {
        return [
            // Hanya baca sheet "Daily Production Report"
            'Daily Production Report' => new DailySheetImport(
                $this->reportDate,
                $this,
                $this->validator
            ),
        ];
    }

    /**
     * Finalize: persist errors dan update log
     */
    public function finalize(): void
    {
        if (isset($this->importLog) && isset($this->errorLogger)) {
            $this->errorLogger->persist();
        }
    }
}

// ═══════════════════════════════════════════════════════════
//  DailySheetImport — membaca baris per baris
// ═══════════════════════════════════════════════════════════
class DailySheetImport implements ToCollection
{
    // ── MAPPING KOLOM EXCEL (0-based, kolom A tetap dihitung) ──────
    // Maatwebsite ToCollection menyertakan SEMUA kolom termasuk A yang kosong
    //
    //  row[0]  = kolom A — KOSONG, dilewati
    //  row[1]  = kolom B — Date
    //  row[2]  = kolom C — Site
    //  row[3]  = kolom D — Sub-Site
    //  row[4]  = kolom E — FC Production (Daily)
    //  row[5]  = kolom F — FC Production (MTD)
    //  row[6]  = kolom G — Port Stock Yard (Daily)
    //  row[7]  = kolom H — Port Stock Yard (MTD)
    //  row[8]  = kolom I — Coal Winning (Daily)
    //  row[9]  = kolom J — Coal Winning (MTD)
    //  row[10] = kolom K — ROM Stock
    //  row[11] = kolom L — FC Percentage (SKIP — formula Excel)
    //  row[12] = kolom M — FC Plan → disimpan ke production_plans

    const COL_DATE      = 1;
    const COL_SITE      = 2;
    const COL_SUBSITE   = 3;
    const COL_FC_DAILY  = 4;
    const COL_FC_MTD    = 5;
    const COL_PSY_DAILY = 6;
    const COL_PSY_MTD   = 7;
    const COL_CW_DAILY  = 8;
    const COL_CW_MTD    = 9;
    const COL_ROM       = 10;
    // COL 11 = FC Percentage — SKIP
    const COL_FC_PLAN   = 12;

    private string                  $reportDate;
    private DailyProductionImport   $parent;
    private ImportValidationService $validator;
    private ImportDetailLogger      $errorLogger;

    public function __construct(
        string $date,
        DailyProductionImport $parent,
        ImportValidationService $validator
    ) {
        $this->reportDate  = $date;
        $this->parent      = $parent;
        $this->validator   = $validator;
        $this->errorLogger = $parent->getErrorLogger();
    }

    public function collection(Collection $rows): void
    {
        $operatorId = Auth::id();
        $arrayRows  = $rows->toArray();

        foreach ($arrayRows as $i => $row) {
            // Baris pertama (index 0) = header — lewati
            if ($i === 0) {
                continue;
            }

            // Pastikan row punya cukup elemen
            while (count($row) <= self::COL_FC_PLAN) {
                $row[] = null;
            }

            $rowNumber = $i + 1; // nomor baris di Excel (1-based, +1 karena header)

            // ── 1. Ambil Site code (index 2 = kolom C) ────────────
            $siteCode = strtoupper(trim((string)($row[self::COL_SITE] ?? '')));

            // Lewati baris kosong
            if (empty($siteCode)) {
                continue;
            }

            // Lewati baris total ITMG
            if ($siteCode === 'ITMG') {
                continue;
            }

            // ── 2. Ambil Sub-Site code (index 3 = kolom D) ────────
            $subCode = strtoupper(trim((string)($row[self::COL_SUBSITE] ?? '')));
            if (empty($subCode)) {
                $this->errorLogger->logGenericError(
                    $rowNumber, "Sub-site kosong untuk site {$siteCode}", 'validation_error', $siteCode
                );
                continue;
            }

            // ── 3. Lookup Site ─────────────────────────────────────
            $site = $this->validator->getSiteByCode($siteCode);
            if (!$site) {
                $this->errorLogger->logRowError(
                    $rowNumber,
                    ['site' => "Site '{$siteCode}' tidak ditemukan di database"],
                    $siteCode, $subCode, 'not_found'
                );
                $this->parent->errors[] = "Baris {$rowNumber}: Site '{$siteCode}' tidak ditemukan.";
                continue;
            }

            // ── 4. Lookup SubSite (pakai site_id + code agar tidak ambigu) ──
            $subSite = $this->validator->getSubSiteByCode($siteCode, $subCode);
            if (!$subSite) {
                $this->errorLogger->logRowError(
                    $rowNumber,
                    ['sub_site' => "Sub-site '{$subCode}' tidak ditemukan di site '{$siteCode}'"],
                    $siteCode, $subCode, 'not_found'
                );
                $this->parent->errors[] = "Baris {$rowNumber}: Sub-site '{$subCode}' di '{$siteCode}' tidak ditemukan.";
                continue;
            }

            // ── 5. Extract & konversi nilai numerik ────────────────
            // cleanNumeric: handle '-', null, noise, dan ×1000
            try {
                $fcDaily  = ImportValidationService::cleanNumeric($row[self::COL_FC_DAILY]);
                $fcMtd    = ImportValidationService::cleanNumeric($row[self::COL_FC_MTD]);
                $psyDaily = ImportValidationService::cleanNumeric($row[self::COL_PSY_DAILY]);
                $psyMtd   = ImportValidationService::cleanNumeric($row[self::COL_PSY_MTD]);

                $isPrimary = $this->validator->isPrimarySubSite($site->id, $subSite->id);

                // Coal Winning & ROM Stock — hanya primary sub-site
                $cwDaily  = $isPrimary ? ImportValidationService::cleanNumeric($row[self::COL_CW_DAILY]) : null;
                $cwMtd    = $isPrimary ? ImportValidationService::cleanNumeric($row[self::COL_CW_MTD])   : null;
                $romStock = $isPrimary ? ImportValidationService::cleanNumeric($row[self::COL_ROM])       : null;

                // FC Plan (kolom M = index 12) — SAMA untuk semua sub-site dalam satu site
                $fcPlan = ImportValidationService::cleanNumeric($row[self::COL_FC_PLAN] ?? null);

            } catch (\Exception $e) {
                $this->errorLogger->logGenericError(
                    $rowNumber, "Error parse numerik: " . $e->getMessage(), 'type_error', $siteCode, $subCode
                );
                $this->parent->errors[] = "Baris {$rowNumber}: Error parse - " . $e->getMessage();
                continue;
            }

            // ── 6. Simpan ke daily_productions ────────────────────
            try {
                DailyProduction::updateOrCreate(
                    [
                        'report_date' => $this->reportDate,
                        'site_id'     => $site->id,
                        'sub_site_id' => $subSite->id,
                    ],
                    [
                        'version'               => 1,
                        'is_active'             => true,
                        'fc_production_daily'   => $fcDaily,
                        'fc_production_mtd'     => $fcMtd,
                        'port_stock_yard_daily' => $psyDaily,
                        'port_stock_yard_mtd'   => $psyMtd,
                        'coal_winning_daily'    => $cwDaily,
                        'coal_winning_mtd'      => $cwMtd,
                        'rom_stock'             => $romStock,
                        'created_by'            => $operatorId,
                        'input_at'              => now(),
                    ]
                );

                $this->parent->imported++;

            } catch (\Exception $e) {
                $this->errorLogger->logGenericError(
                    $rowNumber, "Error DB insert: " . $e->getMessage(), 'business_logic', $siteCode, $subCode
                );
                $this->parent->errors[] = "Baris {$rowNumber}: Error DB - " . $e->getMessage();
                continue;
            }

            // ── 7. Simpan FC Plan ke production_plans ─────────────
            // Hanya dari baris primary sub-site dengan nilai FC Plan valid
            if ($isPrimary && $fcPlan !== null && $fcPlan > 0) {
                try {
                    $parsedDate = Carbon::parse($this->reportDate);

                    ProductionPlan::updateOrCreate(
                        [
                            'site_id' => $site->id,
                            'year'    => $parsedDate->year,
                            'month'   => $parsedDate->month,
                        ],
                        [
                            'fc_plan'           => $fcPlan,
                            'coal_winning_plan' => $cwMtd ?? 0,
                        ]
                    );

                } catch (\Exception $e) {
                    // FC Plan error tidak critical, log warning saja
                    $this->parent->errors[] = "Baris {$rowNumber}: Gagal simpan FC Plan - " . $e->getMessage();
                }
            }
        }

        // Finalize error logging
        $this->parent->finalize();
// dd($rows->get(1));        
// dd([
//     'site' => $siteCode,
//     'sub' => $subCode,
//     'fc_daily' => $fcDaily,
//     'fc_mtd' => $fcMtd,
// ]);
// dd(
//     DailyProduction::select('report_date','site_id','sub_site_id','is_active')
//         ->latest()
//         ->take(10)
//         ->get()
// );
    }
}