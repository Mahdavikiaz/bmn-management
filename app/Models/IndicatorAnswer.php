<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicatorAnswer extends Model
{
    protected $table = 'indicator_answers';
    protected $primaryKey = 'id_answer';
    public $timestamps = false;

    protected $fillable = [
        'id_option',
        'id_spec',
        'star_rating',
        'datetime',
    ];

    protected $casts = [
        'datetime' => 'datetime',
    ];

    public function option()
    {
        return $this->belongsTo(IndicatorOption::class, 'id_option', 'id_option');
    }
}
