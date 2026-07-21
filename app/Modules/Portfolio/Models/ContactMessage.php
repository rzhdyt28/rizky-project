<?php

namespace App\Modules\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $connection = 'portfolio';
    protected $table = 'portfolio_contact_messages';
    protected $guarded = [];
    protected $casts = ['is_read' => 'bool'];
}
