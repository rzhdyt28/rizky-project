<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use SoftDeletes;

class Plan extends Model
{
    protected $guarded = [];
    protected $casts = ['custom_domain' => 'bool', 'remove_branding' => 'bool', 'music_enabled' => 'bool', 'is_active' => 'bool'];
}
