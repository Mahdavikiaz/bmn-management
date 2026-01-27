<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetType extends Model
{
    use HasFactory;

    protected $table = 'asset_types';
    protected $primaryKey = 'id_type';

    protected $fillable = [
        'type_code',
        'type_name',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'id_type', 'id_type');
    }
}
