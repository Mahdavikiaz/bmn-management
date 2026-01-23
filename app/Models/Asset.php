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

    public function specifications()
    {
        return $this->hasMany(AssetsSpecifications::class, 'id_asset', 'id_asset');
    }

    public function latestSpecification()
    {
        return $this->hasOne(AssetsSpecifications::class, 'id_asset', 'id_asset')
            ->latestOfMany('datetime');
    }

    public function performanceReports()
    {
        return $this->hasMany(PerformanceReport::class, 'id_asset', 'id_asset');
    }

    public function latestPerformanceReport()
    {
        return $this->hasOne(PerformanceReport::class, 'id_asset', 'id_asset')->latestOfMany('created_at');
    }
}
