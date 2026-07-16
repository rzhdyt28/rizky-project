<?php

namespace App\Modules\Invitation\Models;

use Illuminate\Database\Eloquent\Model;

class Rsvp extends Model
{
    protected $guarded = [];

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }
}
