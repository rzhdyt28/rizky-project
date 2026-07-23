<?php

namespace App\Modules\Skripsi\Saw\Models;

use Illuminate\Database\Eloquent\Model;

class SawCriterion extends Model
{
    protected $connection = 'skripsi';
    protected $guarded = [];
    protected $casts = ['weight' => 'float'];

    public function case()
    {
        return $this->belongsTo(SawCase::class, 'case_id');
    }
}
