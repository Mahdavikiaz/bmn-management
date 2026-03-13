<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetService extends Model
{
    use HasFactory;
    
    protected $table = 'asset_services';
    protected $primaryKey = 'id_service';

    protected $fillable = [
        'id_asset',
        'service_date',
        'service_description',
    ];

    protected $casts = [
        'service_date' => 'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'id_asset', 'id_asset');
    }
}
