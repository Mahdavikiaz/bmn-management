<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';
    protected $primaryKey = 'id_asset';

    protected $fillable = [
        'bmn_code',
        'device_name',
        'device_type',
        'gpu',
        'ram_type',
        'procurement_year',
    ];
}
