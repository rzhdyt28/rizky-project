<?php

namespace App\Modules\Invitation\Models;

use Illuminate\Database\Eloquent\Model;

class InvitationEvent extends Model
{
    protected $connection = 'undangan';
    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }
}
