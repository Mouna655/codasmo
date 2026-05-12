<?php
namespace App\Imports;

use App\Models\{ShipmentSnapshot, ShipmentRecord};
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class ShipmentImport
{
    public int   $imported = 0;
    public array $errors   = [];

    // Sheet name → month_number
    private const SHEET_MAP = [
        'Month 1' => 1,
        'Month 2' => 2,
        'Month 3' => 3,
        'Month 4' => 4,
        'Month 5' => 5,
        'Month 6' => 6,
    ];

    /**
     * Kolom kunci (0-based index):
     *   [0]  No.              [3]  Company        [4]  Type
     *   [5]  Vessel           [6]  Buyer           [7]  End user
     *   [8]  Load Port        [9]  ETA             [10] ETB
     *   [11] ETD              [55] Total           [58] %
     *   [59] Status           [66] TS(AR)          [68] CV(AR)
     *   [69] CV(NAR)
     */
    public function import(string $filePath, int $snapshotId): void
    {
        $snapshot    = ShipmentSnapshot::findOrFail($snapshotId);
        $spreadsheet = IOFactory::load($filePath);
        $buffer      = [];
        $now         = now();

        foreach (self::SHEET_MAP as $sheetName => $monthNumber) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            if (!$sheet) {
                $this->errors[] = "Sheet '{$sheetName}' tidak ditemukan.";
                continue;
            }

            // formatData=FALSE → nilai mentah (tidak diformat)
            $rows = $sheet->toArray(null, true, false, false);

            // Deteksi label bulan dari data pertama (col [2])
            $monthLabel = $this->detectMonthLabel($rows, $monthNumber);
            $monthDate  = $this->detectMonthDate($rows);

            foreach ($rows as $i => $row) {
                if ($i < 3) continue;   // skip 2 baris kosong + header

                $no = $row[0] ?? null;
                if ($no === null || $no === '') continue;
                if (!is_numeric($no)) continue;

                $loadPort = trim((string) ($row[8] ?? ''));
                if (empty($loadPort)) continue;

                $toFloat = function ($v, float $def = 0.0): float {
                    if ($v === null || $v === '') return $def;
                    if (is_numeric($v)) return (float) $v;
                    $c = str_replace([',', ' '], '', (string) $v);
                    if (preg_match('/^\(([0-9.]+)\)$/', $c, $m)) return -(float)$m[1];
                    return is_numeric($c) ? (float)$c : $def;
                };

                $toNull = function ($v) use ($toFloat): ?float {
                    if ($v === null || $v === '') return null;
                    if (is_numeric($v)) return (float) $v;
                    $c = str_replace([',', ' '], '', (string) $v);
                    return is_numeric($c) ? (float)$c : null;
                };

                $buffer[] = [
                    'snapshot_id'   => $snapshotId,
                    'month_number'  => $monthNumber,
                    'month_label'   => $monthLabel,
                    'month_date'    => $monthDate,
                    'no_row'        => (int) $no,
                    'company'       => trim((string) ($row[3] ?? '')),
                    'shipment_type' => trim((string) ($row[4] ?? '')),
                    'vessel_name'   => trim((string) ($row[5] ?? '')),
                    'buyer'         => trim((string) ($row[6] ?? '')),
                    'end_user'      => trim((string) ($row[7] ?? '')),
                    'load_port'     => $loadPort,
                    'eta'           => $this->fmtDate($row[9]  ?? null),
                    'etb'           => $this->fmtDate($row[10] ?? null),
                    'etd'           => $this->fmtDate($row[11] ?? null),
                    'total_tonnage' => $toFloat($row[55] ?? 0),
                    'pct_shipper'   => $toFloat($row[58] ?? 0),
                    'status'        => trim((string) ($row[59] ?? '')),
                    'ts_ar'         => $toNull($row[66] ?? null),
                    'cv_ar'         => $toNull($row[68] ?? null),
                    'cv_nar'        => $toNull($row[69] ?? null),
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];

                if (count($buffer) >= 100) {
                    ShipmentRecord::insert($buffer);
                    $this->imported += count($buffer);
                    $buffer = [];
                }
            }
        }

        if (!empty($buffer)) {
            ShipmentRecord::insert($buffer);
            $this->imported += count($buffer);
        }

        $snapshot->update([
            'total_rows' => $this->imported,
            'status'     => empty($this->errors) ? 'success' : 'partial',
        ]);
    }

    private function detectMonthLabel(array $rows, int $monthNumber): string
    {
        foreach ($rows as $i => $row) {
            if ($i < 3) continue;
            $v = $row[2] ?? null;
            if ($v instanceof \DateTimeInterface) {
                return Carbon::instance($v)->format('F Y');
            }
            if (is_numeric($v) && $v > 40000) {
                try {
                    return Carbon::instance(
                        \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$v)
                    )->format('F Y');
                } catch (\Exception $e) {}
            }
        }
        return 'Month ' . $monthNumber;
    }

    private function detectMonthDate(array $rows): ?string
    {
        foreach ($rows as $i => $row) {
            if ($i < 3) continue;
            $v = $row[2] ?? null;
            if ($v instanceof \DateTimeInterface) {
                return Carbon::instance($v)->format('Y-m-d');
            }
        }
        return null;
    }

    private function fmtDate($v): ?string
    {
        if ($v === null) return null;
        if ($v instanceof \DateTimeInterface) {
            return Carbon::instance($v)->format('d M');
        }
        if (is_numeric($v) && $v > 40000) {
            try {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$v)
                )->format('d M');
            } catch (\Exception $e) {}
        }
        return is_string($v) ? $v : null;
    }
}