<?php

namespace App\Modules\Invitation\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invitation extends Model
{
    use BelongsToTenant, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'theme_options'     => 'array',
        'co_hosts'          => 'array',
        'rsvp_enabled'      => 'bool',
        'guestbook_enabled' => 'bool',
        'published_at'      => 'datetime',
    ];

    public function theme()     { return $this->belongsTo(Theme::class); }
    public function events()    { return $this->hasMany(InvitationEvent::class)->orderBy('sort_order'); }
    public function stories()   { return $this->hasMany(LoveStory::class)->orderBy('sort_order'); }
    public function photos()    { return $this->hasMany(GalleryPhoto::class)->orderBy('sort_order'); }
    public function gifts()     { return $this->hasMany(Gift::class); }
    public function rsvps()     { return $this->hasMany(Rsvp::class); }
    public function guests()    { return $this->hasMany(Guest::class)->latest(); }
    public function guestbook() { return $this->hasMany(GuestbookEntry::class)->where('is_approved', true)->latest(); }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['slug', 'status', 'theme_id'])->logOnlyDirty();
    }
}
