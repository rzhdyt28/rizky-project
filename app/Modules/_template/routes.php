<?php

// Contoh routes modul — otomatis diprefix /api/<nama-modul-kebab>.
// Lihat README.md di folder ini untuk panduan lengkap membuat modul baru.

use Illuminate\Support\Facades\Route;

Route::get('/ping', fn () => response()->json(['module' => 'template', 'ok' => true]));
