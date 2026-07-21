<?php

namespace App\Modules\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    protected $connection = 'portfolio';
    protected $table = 'portfolio_experiences';
    protected $guarded = [];
    protected $casts = [
        'role' => 'array',
        'bullets' => 'array',
        'tags' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function photos()
    {
        return $this->hasMany(ExperiencePhoto::class)->orderBy('sort_order');
    }
}
