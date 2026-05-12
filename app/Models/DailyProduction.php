<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyProduction extends Model
{
    protected $fillable = [
        'report_date','site_id','sub_site_id',
        'fc_production_daily','fc_production_mtd',
        'port_stock_yard_daily','port_stock_yard_mtd',
        'coal_winning_daily','coal_winning_mtd','rom_stock',
        'fc_plan','version','is_active',
        'created_by','updated_by','input_at',
    ];
    protected $casts = [
        'report_date'           => 'date',
        'input_at'              => 'datetime',
        'fc_production_daily'   => 'float',
        'fc_production_mtd'     => 'float',
        'port_stock_yard_daily' => 'float',
        'port_stock_yard_mtd'   => 'float',
        'coal_winning_daily'    => 'float',
        'coal_winning_mtd'      => 'float',
        'rom_stock'             => 'float',
        'fc_plan'               => 'float',
        'is_active'             => 'boolean',
    ];

    public function site()    { return $this->belongsTo(Site::class); }
    public function subSite() { return $this->belongsTo(SubSite::class); }
    public function creator() { return $this->belongsTo(User::class,'created_by'); }

    // Konversi 0.0001 (placeholder nol Excel) → 0
    public static function zeroIfNoise(?float $v): float {
        return ($v === null || $v < 0.001) ? 0.0 : $v;
    }
}