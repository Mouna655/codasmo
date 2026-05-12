<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThirdPartyCoal extends Model
{
    protected $table    = 'third_party_coal';
    protected $fillable = [
        'year', 'month', 'quality', 'shipper',
        'plan', 'actual', 'upload_batch', 'uploaded_by',
    ];
    protected $casts = [
        'plan'   => 'float',
        'actual' => 'float',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /* ── Scope helpers ─────────────────────────────── */
    public function scopeForYear($q, int $year)  { return $q->where('year', $year); }
    public function scopeYtd($q, int $year)       { return $q->where('year', $year); }
    public function scopeHasData($q)              { return $q->where(fn($q) => $q->where('plan','>',0)->orWhere('actual','>',0)); }
}