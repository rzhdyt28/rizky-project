<?php

namespace App\Modules\Skripsi\Saw\Models;

use Illuminate\Database\Eloquent\Model;

class SawAlternative extends Model
{
    protected $connection = 'skripsi';
    protected $guarded = [];

    public function case()
    {
        return $this->belongsTo(SawCase::class, 'case_id');
    }

    public function scores()
    {
        return $this->hasMany(SawScore::class, 'alternative_id');
    }
}
