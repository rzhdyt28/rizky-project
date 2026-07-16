<?php

namespace App\Modules\Portfolio\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use BelongsToTenant;

    protected $table = 'portfolio_profiles';
    protected $guarded = [];
    protected $casts = ['headline' => 'array', 'about' => 'array', 'socials' => 'array'];
}
