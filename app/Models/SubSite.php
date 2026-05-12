<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubSite extends Model
{
    protected $fillable = ['site_id','code','name','is_primary','chart_color','sort_order','is_active'];
    protected $casts    = ['is_primary'=>'boolean','is_active'=>'boolean'];

    public function site() { return $this->belongsTo(Site::class); }
    public function dailyProductions() { return $this->hasMany(DailyProduction::class); }
}
