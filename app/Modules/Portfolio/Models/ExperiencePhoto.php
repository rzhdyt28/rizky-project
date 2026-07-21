<?php

namespace App\Modules\Portfolio\Models;

use Illuminate\Database\Eloquent\Model;

class ExperiencePhoto extends Model
{
    protected $connection = 'portfolio';
    protected $table = 'portfolio_experience_photos';

    protected $fillable = [
        'experience_id',
        'path',
        'caption',
        'sort_order',
    ];

    protected $casts = [
        'caption' => 'array',
    ];

    public function experience()
    {
        return $this->belongsTo(Experience::class, 'experience_id');
    }

    // Path disimpan relatif (mis. "portfolio/berca-1.jpg"), accessor ini
    // mengembalikan URL publik lengkap supaya Vue tinggal pakai langsung.
    public function getUrlAttribute(): string
    {
        // Bug fix: kalau path sudah berupa URL absolut (http/https),
        // jangan digabung lagi dengan asset('storage/...') — dulu ini
        // akan menghasilkan URL rusak seperti
        // ".../storage/https://cdn.example.com/foo.jpg"
        if (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://')) {
            return $this->path;
        }

        return asset('storage/' . ltrim($this->path, '/'));
    }
}