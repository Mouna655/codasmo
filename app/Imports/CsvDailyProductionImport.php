<?php

namespace App\Imports;

use App\Models\{Site, SubSite, DailyProduction, ExcelImportLog};
use App\Services\{ImportValidationService, ImportDetailLogger, CsvProductionParser};
use Illuminate\Support\Facades\Auth;

/**
 * CSV importer untuk Daily Production Report
 * Parallel structure dengan DailyProductionImport tapi untuk CSV format
 *
 * Usage:
 *   $importer = new CsvDailyProductionImport($reportDate, $importLogId);
 *   $importer->import($csvFilePath);
 */
class CsvDailyProductionImport
{
    private string $reportDate;
    private int $imported = 0;
    private array $errors = [];
    
    private ExcelImportLog $importLog;
    private ImportDetailLogger $errorLogger;
    private ImportValidationService $validator;
    private CsvProductionParser $parser;

    public const EXPECTED_HEADERS = [
        'Date', 'Site', 'Sub-Site',
        'FC Production (Daily)', 'FC Production (MTD)',
        'Port Stock Yard (Daily)', 'Port Stock Yard (MTD)',
        'Coal Winning (Daily)', 'Coal Winning (MTD)',
        'ROM Stock', 'FC Percentage', 'FC Plan'
    ];

    public function __construct(string $date, int $importLogId = null)
    {
        $this->reportDate = $date;
        
        if ($importLogId) {
            $this->importLog = ExcelImportLog::findOrFail($importLogId);
        } else {
            // Create temporary log
            $this->importLog = ExcelImportLog::create([
                'report_date' => $date,
                'filename' => 'temp',
                'file_type' => 'csv',
                'rows_imported' => 0,
                'errors_count' => 0,
                'status' => 'pending',
                'uploaded_by' => Auth::id(),
            ]);
        }
        
        $this->validator = new ImportValidationService();
        $this->errorLogger = new ImportDetailLogger($this->importLog);
    }

    /**
     * Import CSV file
     */
    public function import(string $filePath): bool
    {
        try {
            // Parse CSV
            $this->parser = new CsvProductionParser($filePath, ';');
            
            if (!$this->parser->parse()) {
                foreach ($this->parser->getParseErrors() as $error) {
                    $this->errorLogger->logFormatError($error);
                }
                $this->errorLogger->persist();
                return false;
            }

            // Validate headers
            $headerErrors = $this->parser->validateStructure(self::EXPECTED_HEADERS);
            if (!empty($headerErrors)) {
                foreach ($headerErrors as $error) {
                    $this->errorLogger->logHeaderError($error);
                }
                $this->errorLogger->persist();
                return false;
            }

            // Process rows
            $this->processRows();

            // Persist all errors
            $this->errorLogger->persist();

            return true;

        } catch (\Exception $e) {
            $this->errorLogger->logFormatError("Critical error: " . $e->getMessage());
            $this->errorLogger->persist();
            return false;
        }
    }

    /**
     * Process each row dari CSV
     */
    private function processRows(): void
{
    $operatorId = Auth::id();
    $rows       = $this->parser->getRows();

    foreach ($rows as $item) {
        $rowNumber = $item['row_number'];
        $row       = $item['data'];

        // Pastikan row cukup panjang
        while (count($row) <= 10) {
            $row[] = null;
        }

        // ── CSV mapping (tanpa kolom A kosong) ────────────────────
        // index 0 = Date (diabaikan, date dari form)
        // index 1 = Site
        // index 2 = Sub-Site
        // index 3 = FC Production Daily
        // index 4 = FC Production MTD
        // index 5 = Port Stock Yard Daily
        // index 6 = Port Stock Yard MTD
        // index 7 = Coal Winning Daily
        // index 8 = Coal Winning MTD
        // index 9 = ROM Stock
        // index 10 = FC Plan (FC Percentage tidak diikutkan di CSV)

        $siteCode = strtoupper(trim((string)($row[1] ?? '')));
        $subCode  = strtoupper(trim((string)($row[2] ?? '')));

        // Skip kosong atau ITMG
        if (empty($siteCode) || empty($subCode) || $siteCode === 'ITMG') {
            continue;
        }

        // Lookup
        $site = $this->validator->getSiteByCode($siteCode);
        if (!$site) {
            $this->errorLogger->logRowError(
                $rowNumber,
                ['site' => "Site '{$siteCode}' tidak ditemukan"],
                $siteCode, $subCode, 'not_found'
            );
            $this->errors[] = "Baris {$rowNumber}: Site '{$siteCode}' tidak ditemukan.";
            continue;
        }

        $subSite = $this->validator->getSubSiteByCode($siteCode, $subCode);
        if (!$subSite) {
            $this->errorLogger->logRowError(
                $rowNumber,
                ['sub_site' => "Sub-site '{$subCode}' tidak ditemukan di '{$siteCode}'"],
                $siteCode, $subCode, 'not_found'
            );
            $this->errors[] = "Baris {$rowNumber}: Sub-site '{$subCode}' tidak ditemukan.";
            continue;
        }

        try {
            $isPrimary = $this->validator->isPrimarySubSite($site->id, $subSite->id);

            $fcDaily  = ImportValidationService::cleanNumeric($row[3] ?? null);
            $fcMtd    = ImportValidationService::cleanNumeric($row[4] ?? null);
            $psyDaily = ImportValidationService::cleanNumeric($row[5] ?? null);
            $psyMtd   = ImportValidationService::cleanNumeric($row[6] ?? null);
            $cwDaily  = $isPrimary ? ImportValidationService::cleanNumeric($row[7] ?? null) : null;
            $cwMtd    = $isPrimary ? ImportValidationService::cleanNumeric($row[8] ?? null) : null;
            $romStock = $isPrimary ? ImportValidationService::cleanNumeric($row[9] ?? null) : null;

            // FC Plan dari index 10 (hanya primary sub-site)
            $fcPlan = $isPrimary
                ? ImportValidationService::cleanNumeric($row[10] ?? null, true)
                : null;

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

            $this->imported++;

            // Simpan FC Plan ke production_plans
            if ($isPrimary && $fcPlan !== null && $fcPlan > 0) {
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
            }

        } catch (\Exception $e) {
            $this->errorLogger->logGenericError(
                $rowNumber, "Error: " . $e->getMessage(), 'business_logic', $siteCode, $subCode
            );
            $this->errors[] = "Baris {$rowNumber}: " . $e->getMessage();
        }
    }
}

    // Accessors
    public function getImported(): int { return $this->imported; }
    public function getErrors(): array { return $this->errors; }
    public function getErrorLogger(): ImportDetailLogger { return $this->errorLogger; }
}
