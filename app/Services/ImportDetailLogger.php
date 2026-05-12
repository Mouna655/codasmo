<?php

namespace App\Services;

use App\Models\{ExcelImportLog, ImportDetailLog};
use Illuminate\Support\Collection;

/**
 * Manage error tracking dan logging untuk import process
 * Track errors per baris dan simpan ke database untuk audit trail
 */
class ImportDetailLogger
{
    private ExcelImportLog $importLog;
    private Collection $errors; // Array of error records
    private int $rowCount = 0;

    public function __construct(ExcelImportLog $importLog)
    {
        $this->importLog = $importLog;
        $this->errors = collect();
    }

    /**
     * Log error untuk satu baris
     *
     * @param int $rowNumber Nomor baris di file (1-indexed)
     * @param array $fieldErrors Mapping field => error message
     * @param string $siteCode Site code (untuk reference)
     * @param string $subSiteCode Sub-site code (untuk reference)
     * @param string $errorType Jenis error (validation, not_found, etc)
     */
    public function logRowError(
        int $rowNumber,
        array $fieldErrors,
        string $siteCode = '',
        string $subSiteCode = '',
        string $errorType = 'validation_error'
    ): void {
        foreach ($fieldErrors as $field => $message) {
            $this->errors->push([
                'import_log_id'   => $this->importLog->id,
                'row_number'      => $rowNumber,
                'site_code'       => $siteCode,
                'sub_site_code'   => $subSiteCode,
                'error_field'     => $field,
                'error_message'   => $message,
                'error_value'     => null,
                'error_type'      => $errorType,
                'created_at'      => now(),
            ]);
        }
    }

    /**
     * Log generic error (tidak terikat ke field tertentu)
     */
    public function logGenericError(
        int $rowNumber,
        string $message,
        string $errorType = 'validation_error',
        string $siteCode = '',
        string $subSiteCode = ''
    ): void {
        $this->errors->push([
            'import_log_id'   => $this->importLog->id,
            'row_number'      => $rowNumber,
            'site_code'       => $siteCode,
            'sub_site_code'   => $subSiteCode,
            'error_field'     => null,
            'error_message'   => $message,
            'error_value'     => null,
            'error_type'      => $errorType,
            'created_at'      => now(),
        ]);
    }

    /**
     * Log header validation error
     */
    public function logHeaderError(string $message): void
    {
        $this->errors->push([
            'import_log_id'   => $this->importLog->id,
            'row_number'      => 0,
            'site_code'       => '',
            'sub_site_code'   => '',
            'error_field'     => 'header',
            'error_message'   => $message,
            'error_value'     => null,
            'error_type'      => 'header_mismatch',
            'created_at'      => now(),
        ]);
    }

    /**
     * Log format error (file parsing error)
     */
    public function logFormatError(string $message, int $rowNumber = 0): void
    {
        $this->errors->push([
            'import_log_id'   => $this->importLog->id,
            'row_number'      => $rowNumber,
            'site_code'       => '',
            'sub_site_code'   => '',
            'error_field'     => null,
            'error_message'   => $message,
            'error_value'     => null,
            'error_type'      => 'format_error',
            'created_at'      => now(),
        ]);
    }

    /**
     * Get error count
     */
    public function getErrorCount(): int
    {
        return $this->errors->count();
    }

    /**
     * Get all errors
     */
    public function getErrors(): Collection
    {
        return $this->errors;
    }

    /**
     * Get formatted error summary
     */
    public function getErrorSummary(): array
    {
        $byType = $this->errors->groupBy('error_type');
        $byField = $this->errors->groupBy('error_field');

        return [
            'total' => $this->getErrorCount(),
            'by_type' => $byType->map->count()->toArray(),
            'by_field' => $byField->map->count()->toArray(),
            'rows_affected' => $this->errors->pluck('row_number')->unique()->count(),
        ];
    }

    /**
     * Persist all errors ke database
     * Gunakan batch insert untuk performance
     */
    public function persist(): void
    {
        if ($this->errors->isEmpty()) {
            return;
        }

        // Batch insert dengan chunking
        $this->errors->chunk(100)->each(function($chunk) {
            ImportDetailLog::insert($chunk->toArray());
        });

        // Update excel_import_logs dengan error count
        $this->importLog->update([
            'errors_count' => $this->getErrorCount(),
        ]);
    }

    /**
     * Get errors for display dalam Filament table
     */
    public function getDisplayErrors(int $perPage = 50, int $page = 1)
    {
        return ImportDetailLog::where('import_log_id', $this->importLog->id)
            ->orderBy('row_number')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Export errors ke CSV format
     */
    public function exportToCsv(): string
    {
        $csv = "Row\tSite\tSub-Site\tField\tError Message\tType\tTime\n";
        
        ImportDetailLog::where('import_log_id', $this->importLog->id)
            ->orderBy('row_number')
            ->chunk(100, function($errors) use (&$csv) {
                foreach ($errors as $error) {
                    $csv .= implode("\t", [
                        $error->row_number,
                        $error->site_code,
                        $error->sub_site_code,
                        $error->error_field ?? '-',
                        $error->error_message,
                        $error->error_type,
                        $error->created_at->format('Y-m-d H:i:s'),
                    ]) . "\n";
                }
            });

        return $csv;
    }

    /**
     * Clear errors (untuk retry/testing)
     */
    public function clear(): void
    {
        $this->errors = collect();
    }
}
