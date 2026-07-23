<?php

namespace App\Modules\Skripsi\Saw\Models;

use Illuminate\Database\Eloquent\Model;

class SawScore extends Model
{
    protected $connection = 'skripsi';
    protected $guarded = [];
    protected $casts = ['value' => 'float'];

    public function alternative()
    {
        return $this->belongsTo(SawAlternative::class, 'alternative_id');
    }

    public function criterion()
    {
        return $this->belongsTo(SawCriterion::class, 'criterion_id');
    }
}
