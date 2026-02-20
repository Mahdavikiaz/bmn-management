<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetsSpecifications extends Model
{
    use HasFactory;

    protected $table = 'assets_specifications';
    protected $primaryKey = 'id_spec';

    public $timestamps = false;

    protected $fillable = [
        'id_asset',
        'owner_asset',
        'processor',
        'ram',
        'storage',
        'os_version',
        'is_hdd',
        'is_ssd',
        'is_nvme',
        'issue_note',
        'issue_image_uri',
        'datetime',
    ];

    protected $casts = [
        'ram' => 'integer',
        'storage' => 'integer',
        'is_hdd' => 'boolean',
        'is_ssd' => 'boolean',
        'is_nvme' => 'boolean',
        'datetime' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'id_asset', 'id_asset');
    }
}