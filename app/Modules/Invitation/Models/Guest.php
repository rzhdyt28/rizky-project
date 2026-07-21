<?php

namespace App\Modules\Invitation\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    protected $connection = 'undangan';
    protected $guarded = [];

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }
}
