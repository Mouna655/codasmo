<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ShipmentSnapshot extends Model
{
    protected $fillable = [
        'upload_date','original_filename','filename',
        'total_rows','status','error_message','uploaded_by',
    ];
    protected $casts = ['upload_date' => 'date'];

    public function records()  { return $this->hasMany(ShipmentRecord::class, 'snapshot_id'); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }

    /** Snapshot terbaru yang diupload ≤ tanggal tertentu */
    public static function latestBefore(string $date): ?self
    {
        return static::where('status','success')
            ->whereDate('upload_date','<=',$date)
            ->orderByDesc('upload_date')->orderByDesc('id')
            ->first();
    }

    public static function latestAvailable(): ?self
    {
        return static::where('status','success')
            ->orderByDesc('upload_date')->orderByDesc('id')
            ->first();
    }

    /** Semua tanggal upload untuk date picker */
    public static function availableDates(): array
    {
        return static::where('status','success')
            ->selectRaw('DATE(upload_date) as d')
            ->distinct()->orderByDesc('d')->limit(90)->pluck('d')
            ->map(fn($d) => [
                'date'  => $d,
                'label' => Carbon::parse($d)->translatedFormat('d M Y'),
            ])->toArray();
    }
}