<?php

namespace App\Modules\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $connection = 'portfolio';
    protected $table = 'portfolio_skills';
    protected $guarded = [];
    protected $casts = ['title' => 'array', 'description' => 'array'];
}
