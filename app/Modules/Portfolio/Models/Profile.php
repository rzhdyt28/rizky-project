<?php

namespace App\Modules\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $connection = 'portfolio';
    protected $table = 'portfolio_profiles';
    protected $guarded = [];
    protected $casts = ['headline' => 'array', 'about' => 'array', 'socials' => 'array'];
}
