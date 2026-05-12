<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoaRecord extends Model
{
    protected $fillable = [
        'snapshot_id', 
        'year', 
        'month_number', 
        'month_name',
        'company', 
        'product',
        'outlook', 
        'actual', 
        'previous', 
        'is_provisional',
    ];

    protected $casts = [
        'outlook'     => 'float',
        'actual'      => 'float',
        'previous'    => 'float',
  
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(PoaSnapshot::class, 'snapshot_id');
    }
}