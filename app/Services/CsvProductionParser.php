<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * Parser untuk CSV file dengan format Daily Production Report
 * Support: semicolon delimiter, handle quoted values, etc
 */
class CsvProductionParser
{
    private string $filePath;
    private string $delimiter;
    private ?array $headers = null;
    private Collection $rows;
    private array $parseErrors = [];

    /**
     * Initialize parser
     *
     * @param string $filePath Path ke file CSV
     * @param string $delimiter Delimiter (default: semicolon)
     */
    public function __construct(string $filePath, string $delimiter = ';')
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File tidak ditemukan: $filePath");
        }

        $this->filePath = $filePath;
        $this->delimiter = $delimiter;
        $this->rows = collect();
    }

    /**
     * Parse file dan extract headers + rows
     *
     * @return bool Success status
     */
    public function parse(): bool
    {
        try {
            $handle = fopen($this->filePath, 'r');
            if (!$handle) {
                $this->parseErrors[] = "Tidak bisa membuka file: " . $this->filePath;
                return false;
            }

            $rowNumber = 0;
            $headerRow = null;

            while (($row = fgetcsv($handle, 0, $this->delimiter)) !== false) {
                $rowNumber++;

                // Skip baris kosong
                if (empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) {
                    continue;
                }

                // Baris pertama = header
                if ($headerRow === null) {
                    $this->headers = $this->sanitizeRow($row);
                    $headerRow = $rowNumber;
                    continue;
                }

                // Data rows
                $sanitized = $this->sanitizeRow($row);
                
                // Ensure array memiliki cukup elemen
                while (count($sanitized) < count($this->headers)) {
                    $sanitized[] = null;
                }

                $this->rows->push([
                    'row_number' => $rowNumber,
                    'data' => array_slice($sanitized, 0, count($this->headers))
                ]);
            }

            fclose($handle);

            if ($this->headers === null) {
                $this->parseErrors[] = "File tidak memiliki header row";
                return false;
            }

            if ($this->rows->isEmpty()) {
                $this->parseErrors[] = "File tidak memiliki data rows";
                return false;
            }

            return true;

        } catch (\Exception $e) {
            $this->parseErrors[] = "Error parsing CSV: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Sanitize row: trim whitespace, handle quoted values, etc
     */
    private function sanitizeRow(array $row): array
    {
        return array_map(function($value) {
            // Handle null
            if ($value === null) {
                return null;
            }
            
            $value = (string)$value;
            
            // Remove leading/trailing quotes
            $value = trim($value, '"\'');
            
            // Trim whitespace (IMPORTANT: remove leading spaces)
            $value = trim($value);
            
            // Replace European decimal separator (comma) with dot
            if (is_numeric($value) && strpos($value, ',') !== false) {
                $value = str_replace(',', '.', $value);
            }
            
            // Convert empty strings to null
            return $value === '' ? null : $value;
        }, $row);
    }

    /**
     * Get headers
     */
    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    /**
     * Get rows
     */
    public function getRows(): Collection
    {
        return $this->rows;
    }

    /**
     * Get parse errors
     */
    public function getParseErrors(): array
    {
        return $this->parseErrors;
    }

    /**
     * Get row count (excluding header)
     */
    public function getRowCount(): int
    {
        return $this->rows->count();
    }

    /**
     * Get sample row (untuk preview/debugging)
     */
    public function getSampleRow(int $index = 0): ?array
    {
        $row = $this->rows->get($index);
        return $row ? $row['data'] : null;
    }

    /**
     * Validate file structure
     */
    public function validateStructure(array $expectedHeaders): array
    {
        $errors = [];

        if ($this->headers === null) {
            $errors[] = "Header belum di-parse";
            return $errors;
        }

        // Check header count
        if (count($this->headers) < count($expectedHeaders)) {
            $errors[] = "Kolom tidak lengkap. Expected: " . count($expectedHeaders) 
                      . ", found: " . count($this->headers);
        }

        // Check header names (case-insensitive)
        foreach ($expectedHeaders as $idx => $expected) {
            $cleanExpected = strtolower(trim($expected));
            $cleanActual = strtolower(trim($this->headers[$idx] ?? ''));
            
            if ($cleanActual !== $cleanExpected) {
                $errors[] = "Kolom ke-" . ($idx + 1) . ": expected '$expected', "
                          . "got '" . ($this->headers[$idx] ?? '-') . "'";
            }
        }

        return $errors;
    }

    /**
     * Convert ke format array (untuk compatibility dengan Excel parser)
     * Array index 0 = header, index 1+ = data rows
     */
    public function toArray(): array
    {
        $result = [];
        
        // Header
        $result[] = $this->headers;
        
        // Data
        $this->rows->each(function($row) use (&$result) {
            $result[] = $row['data'];
        });

        return $result;
    }

    /**
     * Get encoding (detect BOM, handle UTF-8 BOM)
     */
    private function detectEncoding(): string
    {
        $bom = file_get_contents($this->filePath, false, null, 0, 3);
        
        if ($bom === "\xEF\xBB\xBF") {
            return 'UTF-8-BOM';
        } elseif (strpos($bom, "\xFF\xFE") === 0) {
            return 'UTF-16LE';
        }
        
        return 'UTF-8';
    }

    /**
     * Helper: Detect delimiter (auto-detect if needed)
     * Gunakan jika delimiter tidak pasti
     */
    public static function detectDelimiter(string $filePath): string
    {
        $handle = fopen($filePath, 'r');
        $line = fgetcsv($handle, 0);
        fclose($handle);

        if (!$line) return ';';

        // Heuristic: check common delimiters
        $content = implode('', $line);
        
        if (substr_count($content, ';') > 0) return ';';
        if (substr_count($content, ',') > 0) return ',';
        if (substr_count($content, "\t") > 0) return "\t";
        
        return ';'; // Default
    }
}
