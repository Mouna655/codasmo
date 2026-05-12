<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\PoaSnapshot;
use App\Models\PoaRecord;

use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;


class PoaImport
{
    public int    $imported = 0;
    public int    $dataYear = 0;
    public array  $errors   = [];

     private const MONTH_MAP = [
        'january'=>1,'february'=>2,'march'=>3,'april'=>4,
        'may'=>5,'june'=>6,'july'=>7,'august'=>8,
        'september'=>9,'october'=>10,'november'=>11,'december'=>12,
    ];

    // public function __construct(int $snapshotId)
    // {
    //     $this->snapshotId = $snapshotId;
    // }

    public function import(string $filePath, int $snapshotId): void
    {
        $snapshot = PoaSnapshot::findOrFail($snapshotId);

        // Buka workbook untuk baca sheet names
        $spreadsheet = IOFactory::load($filePath);
        $sheetNames  = $spreadsheet->getSheetNames();

        // Filter sheet yang berupa angka tahun, ambil terbesar
        $yearSheets = array_filter($sheetNames, fn($n) => preg_match('/^\d{4}$/', trim($n)));

        if (empty($yearSheets)) {
            throw new \Exception("Tidak ada sheet bertahun (misal: 2026) ditemukan di file.");
        }

        $latestYear  = (int) max($yearSheets);
        $this->dataYear = $latestYear;
        $snapshot->update(['data_year' => $latestYear]);

        // Ambil sheet tersebut
        $sheet = $spreadsheet->getSheetByName((string) $latestYear);
        if (!$sheet) {
            throw new \Exception("Sheet '{$latestYear}' tidak ditemukan.");
        }

        $rows = $sheet->toArray(null, true, true, false);

        // Bulk insert untuk performa
        $buffer = [];
        $now    = now();

        foreach ($rows as $i => $row) {
            // Skip header
            if ($i === 0) continue;

            $year      = (int) ($row[1] ?? 0);
            $monthName = trim((string) ($row[2] ?? ''));
            $company   = trim((string) ($row[3] ?? ''));
            $product   = trim((string) ($row[4] ?? ''));

            if ($year < 2020 || empty($monthName) || empty($company) || empty($product)) continue;

            $monthNum = self::MONTH_MAP[strtolower($monthName)] ?? 0;
            if ($monthNum === 0) {
                $this->errors[] = "Baris " . ($i + 1) . ": bulan '{$monthName}' tidak dikenal.";
                continue;
            }

            $clean = function ($v): float {
                if (is_numeric($v)) return round((float) $v, 4);
                return 0.0;
            };

            $buffer[] = [
                'snapshot_id'  => $snapshotId,
                'year'         => $year,
                'month_number' => $monthNum,
                'month_name'   => $monthName,
                'company'      => $company,
                'product'      => $product,
                'outlook'      => $clean($row[5] ?? 0),
                'actual'       => $clean($row[6] ?? 0),
                'previous'     => $clean($row[7] ?? 0),
                'created_at'   => $now,
                'updated_at'   => $now,
            ];

            // Insert per 100 baris agar tidak timeout
            if (count($buffer) >= 100) {
                PoaRecord::insert($buffer);
                $this->imported += count($buffer);
                $buffer = [];
            }
        }

        // Sisa buffer
        if (!empty($buffer)) {
            PoaRecord::insert($buffer);
            $this->imported += count($buffer);
        }

        $snapshot->update([
            'total_rows' => $this->imported,
            'status'     => count($this->errors) === 0 ? 'success' : 'partial',
        ]);
    }


    /**
     * Otomatis ambil sheet dengan tahun TERBESAR.
     * Saat ada sheet 2027 nanti, otomatis terbaca.
     */
    public function sheets(): array
    {
        // Akan di-resolve di PoaSheetImport
        return [0 => new PoaSheetResolver($this)];
    }
}

/* ════════════════════════════════════════════════════════════ */

class PoaSheetResolver implements ToCollection
{
    private PoaImport $parent;

    // Mapping nama bulan → nomor bulan
    private const MONTH_MAP = [
        'january'=>1,'february'=>2,'march'=>3,'april'=>4,
        'may'=>5,'june'=>6,'july'=>7,'august'=>8,
        'september'=>9,'october'=>10,'november'=>11,'december'=>12,
    ];

    public function __construct(PoaImport $parent)
    {
        $this->parent = $parent;
    }

    public function collection(Collection $rows): void
    {
        $snapshot = PoaSnapshot::find($this->parent->snapshotId);

        foreach ($rows as $i => $row) {
            // Skip header baris 0
            if ($i === 0) continue;

            // Ambil tahun dari kolom B (index 1)
            $year = (int) ($row[1] ?? 0);
            if ($year < 2020 || $year > 2035) continue;

            // Set data_year dari baris pertama yang valid
            if ($this->parent->dataYear === 0) {
                $this->parent->dataYear = $year;
                $snapshot?->update(['data_year' => $year]);
            }

            $monthName = trim((string) ($row[2] ?? ''));
            $company   = trim((string) ($row[3] ?? ''));
            $product   = trim((string) ($row[4] ?? ''));

            if (empty($monthName) || empty($company) || empty($product)) continue;

            $monthNum = self::MONTH_MAP[strtolower($monthName)] ?? 0;
            if ($monthNum === 0) {
                $this->parent->errors[] = "Baris " . ($i + 1) . ": bulan '$monthName' tidak dikenal.";
                continue;
            }

            // Bersihkan nilai numerik
            $clean = function ($v): float {
                if (is_numeric($v)) return (float) $v;
                // Tangani formula Excel yang lolos sebagai string
                if (is_string($v) && str_contains($v, '=')) return 0.0;
                return 0.0;
            };

            PoaRecord::create([
                'snapshot_id'  => $this->parent->snapshotId,
                'year'         => $year,
                'month_number' => $monthNum,
                'month_name'   => $monthName,
                'company'      => $company,
                'product'      => $product,
                'outlook'      => $clean($row[5] ?? 0),
                'actual'       => $clean($row[6] ?? 0),
                'previous'     => $clean($row[7] ?? 0),
            ]);

            $this->parent->imported++;
        }
    }
}