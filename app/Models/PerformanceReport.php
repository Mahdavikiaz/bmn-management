<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceReport extends Model
{
    protected $table = 'performance_reports';
    protected $primaryKey = 'id_report';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'id_asset',
        'id_spec',
        'prior_ram',
        'prior_storage',
        'prior_processor',
        'recommendation_ram',
        'recommendation_storage',
        'recommendation_processor',
        'upgrade_ram_price',
        'upgrade_storage_price',
        'datetime',
    ];

    protected $casts = [
        'datetime' => 'datetime',
        'upgrade_ram_price' => 'decimal:0',
        'upgrade_storage_price' => 'decimal:0',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'id_asset', 'id_asset');
    }

    public function spec()
    {
        return $this->belongsTo(AssetsSpecifications::class, 'id_spec', 'id_spec');
    }
    
}
