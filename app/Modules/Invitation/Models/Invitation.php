<?php

namespace App\Modules\Invitation\Models;

use App\Core\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invitation extends Model
{
    use BelongsToTenant, LogsActivity;

    protected $guarded = [];

    protected $casts = [
        'theme_options'     => 'array',
        'rsvp_enabled'      => 'bool',
        'guestbook_enabled' => 'bool',
        'published_at'      => 'datetime',
    ];

    /**
     * co_hosts (Turut Mengundang) — kini terstruktur {name, side} dengan
     * side ∈ pria|wanita|spesial. Data LAMA berupa array string dinormalisasi
     * saat dibaca (default side 'pria' — koreksi lewat Filament bila perlu),
     * jadi TANPA migrasi data & Filament repeater tidak pernah menerima string.
     */
    protected function coHosts(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                $arr = is_array($value) ? $value : (json_decode($value ?? '[]', true) ?: []);

                return array_values(array_map(
                    fn ($it) => is_array($it)
                        ? ['name' => $it['name'] ?? '', 'side' => $it['side'] ?? 'pria']
                        : ['name' => (string) $it, 'side' => 'pria'],
                    $arr
                ));
            },
            set: fn ($value) => json_encode($value ?? [], JSON_UNESCAPED_UNICODE),
        );
    }

    public function theme()     { return $this->belongsTo(Theme::class); }
    public function events()    { return $this->hasMany(InvitationEvent::class)->orderBy('sort_order'); }
    public function stories()   { return $this->hasMany(LoveStory::class)->orderBy('sort_order'); }
    public function photos()    { return $this->hasMany(GalleryPhoto::class)->orderBy('sort_order'); }
    public function gifts()     { return $this->hasMany(Gift::class); }
    public function rsvps()     { return $this->hasMany(Rsvp::class); }
    public function guests()    { return $this->hasMany(Guest::class)->latest(); }
    public function guestbook() { return $this->hasMany(GuestbookEntry::class)->where('is_approved', true)->latest(); }
    /** Semua ucapan TANPA filter is_approved — dipakai moderasi (pemilik undangan). */
    public function guestbookEntries() { return $this->hasMany(GuestbookEntry::class); }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['slug', 'status', 'theme_id'])->logOnlyDirty();
    }
}
