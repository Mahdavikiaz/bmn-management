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
    ];
}
