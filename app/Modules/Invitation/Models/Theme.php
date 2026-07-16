<?php

namespace App\Modules\Invitation\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $guarded = [];
    protected $casts = ['default_options' => 'array', 'is_active' => 'bool'];
}
