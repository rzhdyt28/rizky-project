<?php

namespace App\Modules\AgentMonitoring\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Membaca tabel `applications` dari database SQLite milik auto-apply-agent
 * (tools/auto-apply-agent/data/app.db) melalui koneksi terpisah 'agent'.
 * Laravel = layer monitoring (read-only); agent Node.js tetap pemilik datanya.
 */
class JobApplication extends Model
{
    protected $connection = 'agent';
    protected $table = 'applications';
    public $timestamps = false; // dikelola oleh agent

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'match_score' => 'integer',
    ];

    public function scopePlatform($q, ?string $platform)
    {
        return $platform ? $q->where('platform', $platform) : $q;
    }

    public function scopeStatus($q, ?string $status)
    {
        return $status ? $q->where('status', $status) : $q;
    }
}
