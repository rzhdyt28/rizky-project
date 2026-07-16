<?php

namespace App\Modules\Portfolio\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use BelongsToTenant;

    protected $table = 'portfolio_contact_messages';
    protected $guarded = [];
    protected $casts = ['is_read' => 'bool'];
}
