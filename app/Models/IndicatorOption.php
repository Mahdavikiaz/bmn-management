<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicatorOption extends Model
{
    protected $table = 'indicator_options';
    protected $primaryKey = 'id_option';

    protected $fillable = [
        'id_question',
        'label',
        'option',
        'star_value',
    ];

    public function question()
    {
        return $this->belongsTo(IndicatorQuestion::class, 'id_question', 'id_question');
    }
}
