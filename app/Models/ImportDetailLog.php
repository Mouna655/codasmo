<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportDetailLog extends Model
{
    protected $table = 'import_detail_logs';
    public $timestamps = false; // Only has created_at
    
    protected $fillable = [
        'import_log_id',
        'row_number',
        'site_code',
        'sub_site_code',
        'error_field',
        'error_message',
        'error_value',
        'error_type',
    ];

    protected $casts = [
        'row_number' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Relationship: Milik ke ExcelImportLog
     */
    public function importLog(): BelongsTo
    {
        return $this->belongsTo(ExcelImportLog::class, 'import_log_id');
    }

    /**
     * Scope: Filter by error type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('error_type', $type);
    }

    /**
     * Scope: Filter by field
     */
    public function scopeByField($query, string $field)
    {
        return $query->where('error_field', $field);
    }

    /**
     * Helper: Format error message untuk display
     */
    public function getFormattedErrorAttribute(): string
    {
        $location = [];
        if ($this->site_code) $location[] = "Site: {$this->site_code}";
        if ($this->sub_site_code) $location[] = "SubSite: {$this->sub_site_code}";
        if ($this->error_field) $location[] = "Field: {$this->error_field}";
        
        $loc = implode(' | ', $location);
        return "[Row {$this->row_number}] {$this->error_message}" . ($loc ? " — $loc" : '');
    }

    /**
     * Helper: Get error severity
     */
    public function getSeverityAttribute(): string
    {
        return match($this->error_type) {
            'format_error', 'header_mismatch' => 'critical',
            'validation_error', 'business_logic' => 'warning',
            'not_found' => 'info',
            default => 'unknown',
        };
    }
}
