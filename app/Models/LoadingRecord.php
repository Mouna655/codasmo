<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoadingRecord extends Model
{
    protected $fillable = [
        'snapshot_id','no_row','no_mahakam','company','shipment_type',
        'vessel_name','end_user','load_port',
        'eta','etb','etd','lay','can',
        'total_tonnage','pct_shipper','status',
        'ts_ar','cv_ar',
        'pen_value',   // dari kolom CC (Excel index 80, header PW2)
        'dem_value',   // dari kolom CK (Excel index 88, header DW3)
        // Products
        't_imm_wb_ls','t_imm_wb_hs','t_imm_eb_ls','t_imm_eb_ms','t_imm_eb_hs',
        't_tcm_ls','t_tcm_hs','t_tcm_ms',
        't_bek_ls','t_bek_hs','t_jbg','t_gpk','t_tis',
    ];

    protected $casts = [
        'total_tonnage' => 'float',
        'pct_shipper'   => 'float',
        'ts_ar'         => 'float',
        'cv_ar'         => 'float',
        'pen_value'     => 'float',
        'dem_value'     => 'float',
    ];

    public function snapshot() { return $this->belongsTo(LoadingSnapshot::class, 'snapshot_id'); }

    /** Tab routing berdasarkan load port */
    public function getTabAttribute(): string
    {
        return match($this->load_port) {
            'BoCT'                   => 'boct',
            'Muara Berau','GPK Port' => 'mahakam',
            default                  => 'overall',
        };
    }
}