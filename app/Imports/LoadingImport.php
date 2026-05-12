<?php
namespace App\Imports;

use App\Models\{LoadingSnapshot, LoadingRecord};
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class LoadingImport
{
    public int   $imported = 0;
    public array $errors   = [];

    /**
     * Mapping kolom Excel (0-based) → field DB untuk produk
     *
     * Verifikasi Python:
     *   col 15 = IMM-WB.HCV.LS → t_imm_wb_ls
     *   col 16 = IMM-WB.MCV.HS → t_imm_wb_hs
     *   col 17 = IMM-EB.MCV.LS → t_imm_eb_ls
     *   col 18 = IMM-EB.MCV.MS → t_imm_eb_ms
     *   col 19 = IMM-EB.MCV.HS → t_imm_eb_hs  (row1 = 19148.58 ✓)
     *   col 20 = TCM.HCV.LS    → t_tcm_ls
     *   col 21 = TCM.HCV.HS    → t_tcm_hs
     *   col 22 = TCM.HCV.MS    → t_tcm_ms      (row1 = 5580.71 ✓)
     *   col 23 = BEK.MCV.LS    → t_bek_ls      (row1 = 38369.29 ✓)
     *   col 24 = BEK.HCV.MS    → t_bek_hs
     *   col 25 = JBG           → t_jbg
     *   col 26 = GPK           → t_gpk          (row1 = 4094.13 ✓)
     *   col 27 = TIS           → t_tis
     */
    private const PRODUCT_MAP = [
        15 => 't_imm_wb_ls',
        16 => 't_imm_wb_hs',
        17 => 't_imm_eb_ls',
        18 => 't_imm_eb_ms',
        19 => 't_imm_eb_hs',
        20 => 't_tcm_ls',
        21 => 't_tcm_hs',
        22 => 't_tcm_ms',
        23 => 't_bek_ls',
        24 => 't_bek_hs',
        25 => 't_jbg',
        26 => 't_gpk',
        27 => 't_tis',
    ];

    public function import(string $filePath, int $snapshotId): void
    {
        $snapshot = LoadingSnapshot::findOrFail($snapshotId);

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('ITM Summary');

        if (!$sheet) {
            throw new \Exception("Sheet 'ITM Summary' tidak ditemukan.");
        }

        /**
         * ════════════════════════════════════════════════════════
         * KUNCI PERBAIKAN: formatData = FALSE (parameter ke-3)
         *
         * toArray($nullValue, $calculateFormulas, $formatData, $returnCellRef)
         *
         * Dengan formatData=TRUE (salah, versi lama):
         *   - 69112  → "69,112"   → is_numeric() = FALSE → disimpan 0
         *   - -1.044 → "(1.04)"   → is_numeric() = FALSE → disimpan null
         *   - 0.1085 → "10.85%"   → is_numeric() = FALSE → disimpan 0
         *
         * Dengan formatData=FALSE (benar, versi baru):
         *   - 69112  → 69112      → is_numeric() = TRUE  → disimpan 69112
         *   - -1.044 → -1.044     → is_numeric() = TRUE  → disimpan -1.044
         *   - 0.1085 → 0.1085     → is_numeric() = TRUE  → disimpan 0.1085
         * ════════════════════════════════════════════════════════
         */
        $rows = $sheet->toArray(
            null,   // $nullValue       — nilai default untuk sel kosong
            true,   // $calculateFormulas — hitung formula Excel
            false,  // $formatData      — JANGAN format, kembalikan nilai mentah
            false   // $returnCellRef    — gunakan index numerik (0-based)
        );

        $buffer = [];
        $now    = now();

        foreach ($rows as $i => $row) {
            // Baris 0 dan 1 = kosong, baris 2 = header → skip ketiganya
            if ($i < 3) continue;

            // Kolom pertama (No.) harus angka — jika tidak, end of data
            $no = $row[0] ?? null;
            if ($no === null || $no === '') continue;
            if (!is_numeric($no)) {
                // Jika ada judul baris total di bawah, skip saja jangan break
                continue;
            }

            $loadPort = trim((string) ($row[8] ?? ''));
            if (!in_array($loadPort, ['BoCT', 'Muara Berau', 'GPK Port'])) continue;

            /**
             * Helper: konversi nilai Excel ke float.
             * Menangani: int, float, string angka, null.
             * Nilai negatif TETAP disimpan sebagai negatif.
             */
            $toFloat = function ($v, float $default = 0.0): float {
                if ($v === null || $v === '') return $default;

                // Sudah numeric (int atau float termasuk negatif)
                if (is_numeric($v)) return (float) $v;

                // String dengan format khusus — bersihkan lalu coba lagi
                // Hapus: koma ribuan, spasi, simbol mata uang
                $cleaned = str_replace([',', ' ', '$', '%', "\u{00A0}"], '', (string) $v);
                // Format akuntansi negatif: (1.044) → -1.044
                if (preg_match('/^\(([0-9.]+)\)$/', $cleaned, $m)) {
                    return -(float) $m[1];
                }
                return is_numeric($cleaned) ? (float) $cleaned : $default;
            };

            /**
             * Helper: konversi ke float, kembalikan null jika kosong.
             * PENTING: nilai negatif VALID dan dikembalikan sebagai negatif.
             */
            $toFloatNull = function ($v) use ($toFloat): ?float {
                if ($v === null || $v === '') return null;
                $result = $toFloat($v, PHP_FLOAT_MIN);
                return $result === PHP_FLOAT_MIN ? null : $result;
            };

            // ── Products tonnage (col 15-27) ──────────────────────────
            $products = [];
            foreach (self::PRODUCT_MAP as $colIdx => $dbCol) {
                // None/null = produk ini tidak ada dalam shipment ini → simpan 0
                $products[$dbCol] = $toFloat($row[$colIdx] ?? null);
            }

            // ── Pen & Dem — SELALU dari CC (col 80) dan CK (col 88) ──
            // Nilai ini FIXED, tidak bergantung week.
            // Verifikasi: row1 pen=0.3983, dem=-1.0441 → cocok dengan screenshot
            $penValue = $toFloatNull($row[80] ?? null);
            $demValue = $toFloatNull($row[88] ?? null);

            $record = [
                'snapshot_id'   => $snapshotId,
                'no_row'        => (int) $no,
                'no_mahakam'    => (int) ($row[1] ?? 0),
                'company'       => trim((string) ($row[3] ?? '')),
                'shipment_type' => trim((string) ($row[4] ?? '')),
                'vessel_name'   => trim((string) ($row[5] ?? '')),
                'end_user'      => trim((string) ($row[7] ?? '')),
                'load_port'     => $loadPort,

                // Tanggal sudah string '27.Mar' langsung dari Excel
                'eta'           => $this->cleanDate($row[10] ?? null),
                'etb'           => $this->cleanDate($row[12] ?? null),
                'etd'           => $this->cleanDate($row[14] ?? null),
                'lay'           => $this->cleanDate($row[63] ?? null),  // Lay S. = col BL = index 63
                'can'           => $this->cleanDate($row[65] ?? null),  // Can S. = col BN = index 65

                // Total tonnage — col BJ = index 61
                // Verifikasi: row1 = 69112 ✓
                'total_tonnage' => $toFloat($row[61] ?? 0),

                // % Shipper — col BO = index 66
                // Nilai decimal: 0.1085 = 10.85% (dikalikan 100 di controller)
                // Verifikasi: row1 = 0.1085 → *100 = 10.9% ✓
                'pct_shipper'   => $toFloat($row[66] ?? 0),

                'status'        => trim((string) ($row[67] ?? 'Plan')),

                // Quality — TS(AR) dan CV(AR)
                'ts_ar'         => $toFloatNull($row[74] ?? null),
                'cv_ar'         => $toFloatNull($row[76] ?? null),

                // Pen dari CC (index 80) — bisa positif atau negatif
                'pen_value'     => $penValue,
                // Dem dari CK (index 88) — bisa positif atau negatif
                'dem_value'     => $demValue,

                'created_at'    => $now,
                'updated_at'    => $now,
            ] + $products;

            $buffer[] = $record;

            if (count($buffer) >= 100) {
                LoadingRecord::insert($buffer);
                $this->imported += count($buffer);
                $buffer = [];
            }
        }

        if (!empty($buffer)) {
            LoadingRecord::insert($buffer);
            $this->imported += count($buffer);
        }

        $snapshot->update([
            'total_rows' => $this->imported,
            'status'     => empty($this->errors) ? 'success' : 'partial',
        ]);
    }

    /**
     * Bersihkan nilai tanggal dari berbagai format Excel.
     * Dengan formatData=false, nilai tanggal bisa berupa:
     * - String '27.Mar' (sudah bersih)
     * - DateTime object dari PhpSpreadsheet
     * - Numeric serial Excel (seperti 45012)
     */
    private function cleanDate($value): ?string
    {
        if ($value === null || $value === '') return null;

        // Sudah string format '27.Mar', '1.Apr', dll
        if (is_string($value) && preg_match('/\d{1,2}[\.\-]\w{3,}/', $value)) {
            return $value;
        }

        // DateTime object dari PhpSpreadsheet
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('d.M');
        }

        // Numeric serial date Excel (contoh: 45012 = 1 Jan 2023)
        if (is_numeric($value) && $value > 40000 && $value < 60000) {
            try {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)
                )->format('d.M');
            } catch (\Exception $e) {
                return null;
            }
        }

        // Fallback: kembalikan as-is jika masih string
        return is_string($value) ? $value : null;
    }
}