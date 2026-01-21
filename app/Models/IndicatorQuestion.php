<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicatorQuestion extends Model
{
    protected $table = 'indicator_questions';
    protected $primaryKey = 'id_question';

    protected $fillable = [
        'category',
        'indicator_name',
        'question',
    ];

    public function options()
    {
        return $this->hasMany(IndicatorOption::class, 'id_question', 'id_question');
    }
}
