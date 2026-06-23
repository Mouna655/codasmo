<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LoadingSnapshot extends Model
{
    // Hapus 'week_number' dari $fillable, sisanya tetap sama
protected $fillable = [
    'upload_date','data_month','data_year','data_month_label',
    'pen_week_label','dem_week_label',    // ← week_number dihapus
    'filename','original_filename','total_rows',
    'status','error_message','uploaded_by',
];
    protected $casts = ['upload_date' => 'date'];

    public function records()  { return $this->hasMany(LoadingRecord::class, 'snapshot_id'); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }

    /**
     * Snapshot terbaru yang diupload pada atau sebelum tanggal tertentu.
     */
    public static function latestBefore(string $date, int $month, int $year): ?self
    {
        return static::where('status', 'success')
            ->where('data_month', $month)
            ->where('data_year', $year)
            ->whereDate('upload_date', '<=', $date)
            ->orderByDesc('upload_date')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Snapshot terbaru tanpa filter tanggal.
     */
    public static function latestAvailable(): ?self
    {
        return static::where('status', 'success')
            ->orderByDesc('upload_date')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Semua tanggal upload yang tersedia untuk date picker.
     */
    public static function availableDates(): array
    {
        return static::where('status', 'success')
            ->selectRaw('CAST(upload_date AS DATE) as d, data_month, data_year, data_month_label, pen_week_label, dem_week_label')
            ->distinct()
            ->orderByDesc('d')
            ->limit(90)
            ->get()
            ->map(fn($r) => [
                'date'           => $r->d,
                'data_month'     => $r->data_month,
                'data_year'      => $r->data_year,
                'month_label'    => $r->data_month_label,
                'pen_week_label' => $r->pen_week_label,
                'dem_week_label' => $r->dem_week_label,
                'label'          => Carbon::parse($r->d)->translatedFormat('d M Y'),
            ])
            ->toArray();
    }
}