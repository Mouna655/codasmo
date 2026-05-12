<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExcelImportLog extends Model
{
    // use HasFactory;

    protected $fillable = [
        'report_date',
        'filename',
        'file_type',
        'rows_imported',
        'errors_count',
        'errors',
        'status',
        'uploaded_by',
    ];

    protected $casts = [
        'report_date' => 'date',
        'rows_imported' => 'integer',
        'errors_count' => 'integer',
    ];

    // Relationships
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function detailLogs(): HasMany
    {
        return $this->hasMany(ImportDetailLog::class, 'import_log_id');
    }

    // Helpers
    public function getHasErrorsAttribute(): bool
    {
        return $this->errors_count > 0;
    }

    public function getSuccessRateAttribute(): float
    {
        $total = $this->rows_imported + $this->errors_count;
        return $total > 0 ? round(($this->rows_imported / $total) * 100, 2) : 100;
    }
}