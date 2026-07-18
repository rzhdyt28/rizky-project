<?php

namespace App\Modules\Invitation\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ThemeAsset extends Model
{
    protected $guarded = [];

    protected $casts = ['is_active' => 'bool'];

    public const CATEGORIES = [
        'ornament' => 'Ornamen',
        'divider'  => 'Divider',
        'monogram' => 'Monogram',
        'lainnya'  => 'Lainnya',
    ];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /** Opsi dropdown Filament: [path => name] per kategori. */
    public static function optionsFor(string $category): array
    {
        return static::active()
            ->where('category', $category)
            ->orderBy('name')
            ->pluck('name', 'path')
            ->all();
    }
}
