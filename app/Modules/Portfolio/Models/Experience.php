<?php

namespace App\Modules\Portfolio\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use BelongsToTenant;

    protected $table = 'portfolio_experiences';
    protected $guarded = [];
    protected $casts = ['role' => 'array', 'bullets' => 'array', 'start_date' => 'date', 'end_date' => 'date'];
}
