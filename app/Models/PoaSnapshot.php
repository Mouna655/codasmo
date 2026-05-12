<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PoaSnapshot extends Model
{
    protected $fillable = [
        'upload_date', 'data_year', 'filename',
        'original_filename', 'total_rows',
        'status', 'error_message', 'uploaded_by',
    ];

    protected $casts = [
        'upload_date' => 'date',
    ];

    public function records(): HasMany
    {
        return $this->hasMany(PoaRecord::class, 'snapshot_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Ambil snapshot terbaru yang diupload pada atau sebelum tanggal tertentu.
     * Ini kunci fitur "time travel".
     */
    public static function latestBefore(string $date, int $year): ?self
    {
        return static::where('status', 'success')
            ->where('data_year', $year)
            ->whereDate('upload_date', '<=', $date)
            ->orderByDesc('upload_date')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Ambil snapshot terbaru yang tersedia (untuk default view).
     */
    public static function latest_available(): ?self
    {
        return static::where('status', 'success')
            ->orderByDesc('upload_date')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Semua tanggal upload yang tersedia (untuk date picker).
     */
    public static function availableDates(): array
    {
        return static::where('status', 'success')
            ->selectRaw('DATE(upload_date) as d, data_year')
            ->distinct()
            ->orderByDesc('d')
            ->limit(90)
            ->get()
            ->map(fn($r) => [
                'date'  => $r->d,
                'data_year'  => $r->data_year,
                'label' => Carbon::parse($r->d)->translatedFormat('d M Y'),
            ])
            ->toArray();
    }
}