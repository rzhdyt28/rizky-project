<?php

namespace App\Modules\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $connection = 'portfolio';
    protected $table = 'portfolio_educations';
    protected $guarded = [];
    protected $casts = ['degree' => 'array'];
}
