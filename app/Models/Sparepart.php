<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    protected $table = 'spareparts';
    protected $primaryKey = 'id_sparepart';

    protected $fillable = [
        'category',
        'sparepart_type',
        'sparepart_name',
        'size',
        'price',
    ];

    protected $casts = [
        'size' => 'integer',
        'price' => 'decimal:2',
    ];
}
