<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = ['code','name','is_parent','is_active','sort_order'];
    protected $casts    = ['is_parent'=>'boolean','is_active'=>'boolean'];

    public function subSites()          { return $this->hasMany(SubSite::class)->orderBy('sort_order'); }
    public function dailyProductions()  { return $this->hasMany(DailyProduction::class); }
    public function productionPlans()   { return $this->hasMany(ProductionPlan::class); }

    public function scopeOperational($q) {
        return $q->where('is_parent',false)->where('is_active',true)->orderBy('sort_order');
    }

    public function getPlanForMonth(int $year, int $month): float {
        return $this->productionPlans()
            ->where('year',$year)->where('month',$month)
            ->value('fc_plan') ?? 0;
    }
}
