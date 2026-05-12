<?php
// app/Services/ImportValidationService.php

namespace App\Services;

use App\Models\{Site, SubSite};

/**
 * Shared validation & helper service untuk semua importer
 * (Excel maupun CSV)
 */
class ImportValidationService
{
    // Cache agar tidak query DB berulang per baris
    private array $siteCache    = [];  // code → Site
    private array $subSiteCache = [];  // "siteId:CODE" → SubSite
    private array $primaryCache = [];  // "siteId:subSiteId" → bool

    public function __construct()
    {
        $this->preloadCaches();
    }

    // ── Preload semua master data ke memori ──────────────────────
    private function preloadCaches(): void
    {
        foreach (Site::where('is_active', true)->get() as $site) {
            $this->siteCache[strtoupper($site->code)] = $site;
        }

        foreach (SubSite::where('is_active', true)->get() as $sub) {
            $key = "{$sub->site_id}:" . strtoupper($sub->code);
            $this->subSiteCache[$key] = $sub;

            $pkKey = "{$sub->site_id}:{$sub->id}";
            $this->primaryCache[$pkKey] = (bool) $sub->is_primary;
        }
    }

    // ── Lookup Site by code ──────────────────────────────────────
    public function getSiteByCode(string $code): ?Site
    {
        return $this->siteCache[strtoupper(trim($code))] ?? null;
    }

    // ── Lookup SubSite by site code + sub-site code ──────────────
    // WAJIB pakai dua parameter karena TCM & BEK sama-sama punya 'LS' dan 'HS'
    public function getSubSiteByCode(string $siteCode, string $subCode): ?SubSite
    {
        $site = $this->getSiteByCode($siteCode);
        if (!$site) return null;

        $key = "{$site->id}:" . strtoupper(trim($subCode));
        return $this->subSiteCache[$key] ?? null;
    }

    // ── Cek apakah sub-site adalah primary ──────────────────────
    public function isPrimarySubSite(int $siteId, int $subSiteId): bool
    {
        return $this->primaryCache["{$siteId}:{$subSiteId}"] ?? false;
    }

    // ── Validasi satu baris numerik ──────────────────────────────
    // Return: [bool $valid, array $errors]
    public function validateRow(array $row, string $reportDate, int $rowNumber): array
    {
        $errors = [];

        // Cek kolom-kolom numerik kritis tidak bernilai string aneh
        $numericCols = [
            4  => 'FC Production Daily',
            5  => 'FC Production MTD',
            6  => 'Port Stock Yard Daily',
            7  => 'Port Stock Yard MTD',
        ];

        foreach ($numericCols as $idx => $label) {
            $val = $row[$idx] ?? null;
            if ($val === null || $val === '' || $val === '-' || $val === '—') {
                continue; // Boleh kosong
            }
            // Jika ada nilai, harus bisa di-parse ke angka
            $cleaned = str_replace(['.', ','], ['', '.'], (string)$val);
            if (!is_numeric($cleaned) && !is_numeric($val)) {
                $errors[$label] = "Nilai '{$val}' bukan angka valid di kolom {$label}";
            }
        }

        return [empty($errors), $errors];
    }

    // ── Static helper: bersihkan & konversi nilai numerik ────────
    /**
     * Konversi nilai dari Excel/CSV ke ton di database.
     *
     * Aturan:
     * - null, '', '-', '—'  → 0.0 (atau null jika $allowNull=true)
     * - Nilai < 0.1 setelah konversi → 0.0 (noise Excel: 0.0001)
     * - Excel pakai ribuan ton → KALIKAN 1000
     * - Handle format Indonesia: titik sebagai pemisah ribuan
     *
     * Contoh:
     *   "1.231"  → 1231.0   (1.231 ribu ton)
     *   "23.489" → 23489.0  (23.489 ribu ton)
     *   "-"      → 0.0
     *   null     → 0.0
     *   0.0001   → 0.0      (noise placeholder Excel)
     */
    public static function cleanNumeric(mixed $raw, bool $allowNull = false): ?float
    {
        // Handle null / empty
        if ($raw === null || $raw === '') {
            return $allowNull ? null : 0.0;
        }

        $str = trim((string)$raw);

        // Tanda '-' atau '—' = nol/kosong
        if ($str === '-' || $str === '—' || $str === '--') {
            return $allowNull ? null : 0.0;
        }

        // Jika sudah numeric (PHP tidak akan bingung dengan separator)
        if (is_numeric($raw)) {
            $val = (float) $raw;
        } else {
            // Handle format Indonesia: "23.489" = 23489 (titik = pemisah ribuan)
            // Strategy: jika ada titik tapi tidak ada koma, titik = ribuan separator
            // Jika ada koma, koma = decimal separator
            if (strpos($str, ',') !== false) {
                // European format: 1.234,56 → hapus titik, ganti koma dengan titik
                $cleaned = str_replace(['.', ','], ['', '.'], $str);
            } else {
                // Indonesian format: 23.489 → hapus titik
                $cleaned = str_replace('.', '', $str);
            }

            if (!is_numeric($cleaned)) {
                return 0.0;
            }

            $val = (float) $cleaned;
        }

        $result = $val;

        // Noise filter: nilai sangat kecil = placeholder nol Excel
        if ($result < 0.1) {
            return 0.0;
        }

        return round($result, 2);
    }
}