<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    protected $table = 'recommendations';
    protected $primaryKey = 'id_recommendation';

    protected $fillable = [
        'category',
        'action',
        'explanation',
        'priority_level',
        
        'target_type',
        'size_mode',
        'target_size_gb',
        'target_multiplier',
    ];

     protected $casts = [
        'priority_level'    => 'integer',
        'target_size_gb'    => 'integer',
        'target_multiplier' => 'float',
    ];
}
