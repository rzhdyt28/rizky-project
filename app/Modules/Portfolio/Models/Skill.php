<?php

namespace App\Modules\Portfolio\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use BelongsToTenant;

    protected $table = 'portfolio_skills';
    protected $guarded = [];
    protected $casts = ['title' => 'array', 'description' => 'array'];
}
