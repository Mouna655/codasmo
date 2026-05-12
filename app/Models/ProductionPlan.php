<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionPlan extends Model
{
    protected $fillable = ['site_id','year','month','fc_plan','coal_winning_plan'];
    protected $casts    = ['fc_plan'=>'float','coal_winning_plan'=>'float'];

    public function site() { return $this->belongsTo(Site::class); }
}
