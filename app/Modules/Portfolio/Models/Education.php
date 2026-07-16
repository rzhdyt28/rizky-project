<?php

namespace App\Modules\Portfolio\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use BelongsToTenant;

    protected $table = 'portfolio_educations';
    protected $guarded = [];
    protected $casts = ['degree' => 'array'];
}
