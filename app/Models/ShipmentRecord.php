<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentRecord extends Model
{
    protected $fillable = [
        'snapshot_id','month_number','month_label','month_date',
        'no_row','company','shipment_type','vessel_name',
        'buyer','end_user','load_port',
        'eta','etb','etd',
        'total_tonnage','pct_shipper','status',
        'ts_ar','cv_ar','cv_nar',
    ];

    protected $casts = [
        'total_tonnage' => 'float',
        'pct_shipper'   => 'float',
        'ts_ar'         => 'float',
        'cv_ar'         => 'float',
        'cv_nar'        => 'float',
        'month_date'    => 'date',
    ];

    public function snapshot() { return $this->belongsTo(ShipmentSnapshot::class,'snapshot_id'); }

    public function getTabAttribute(): string {
        return match($this->load_port) {
            'BoCT'                   => 'boct',
            'Muara Berau','GPK Port' => 'mahakam',
            default                  => 'other',
        };
    }
}