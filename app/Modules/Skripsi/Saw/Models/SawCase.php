<?php

namespace App\Modules\Skripsi\Saw\Models;

use Illuminate\Database\Eloquent\Model;

class SawCase extends Model
{
    protected $connection = 'skripsi';
    protected $guarded = [];
    protected $casts = [
        'result_snapshot' => 'array',
        'calculated_at' => 'datetime',
        'show_description' => 'boolean',
    ];

    public function criteria()
    {
        return $this->hasMany(SawCriterion::class, 'case_id');
    }

    public function alternatives()
    {
        return $this->hasMany(SawAlternative::class, 'case_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Modules\Skripsi\Core\Models\User::class, 'user_id');
    }
}
