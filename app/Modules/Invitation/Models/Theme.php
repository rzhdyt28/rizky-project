<?php

namespace App\Modules\Invitation\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $guarded = [];
    protected $casts = ['default_options' => 'array', 'is_active' => 'bool'];

    /** Tema induk — tema ini MEWARISI default_options milik parent-nya. */
    public function parent()   { return $this->belongsTo(Theme::class, 'parent_id'); }
    public function children() { return $this->hasMany(Theme::class, 'parent_id'); }

    /** Kalau terisi, tema ini PRIVAT -- child theme milik 1 undangan (bukan tema dasar/publik). */
    public function invitation() { return $this->belongsTo(Invitation::class, 'invitation_id'); }

    /**
     * Rantai pewarisan: [leluhur tertua, ..., parent, tema ini].
     * Guard kedalaman 6 mencegah loop tak sengaja (A->B->A).
     */
    public function ancestryChain(): array
    {
        $chain = [$this];
        $node  = $this;
        $guard = 0;
        while ($node->parent_id && $guard++ < 6) {
            $node = $node->parent;
            if (! $node || in_array($node->getKey(), array_map(fn ($t) => $t->getKey(), $chain), true)) {
                break; // parent hilang atau siklus terdeteksi
            }
            array_unshift($chain, $node);
        }

        return $chain;
    }
}
