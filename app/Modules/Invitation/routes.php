<?php

use App\Modules\Invitation\Http\Controllers\GuestbookController;
use App\Modules\Invitation\Http\Controllers\GuestController;
use App\Modules\Invitation\Http\Controllers\InvitationController;
use App\Modules\Invitation\Http\Controllers\PublicInvitationController;
use App\Modules\Invitation\Http\Controllers\RsvpController;
use Illuminate\Support\Facades\Route;

/*
| MODUL INVITATION -> otomatis diprefix /api/invitation oleh ModuleServiceProvider
*/

// Publik (halaman undangan Vue + form tamu)
Route::get('/p/{slug}',            [PublicInvitationController::class, 'show']);
Route::post('/p/{slug}/rsvp',      [RsvpController::class, 'store'])->middleware('throttle:10,1');
Route::post('/p/{slug}/guestbook', [GuestbookController::class, 'store'])->middleware('throttle:10,1');

// Pemilik undangan (login + langganan aktif)
Route::middleware(['auth:sanctum', 'subscription.active'])->group(function () {
    Route::apiResource('/', InvitationController::class)->parameters(['' => 'invitation']);
    Route::get('/{invitation}/rsvps', [RsvpController::class, 'index']);
    Route::get('/{invitation}/guests',            [GuestController::class, 'index']);
    Route::post('/{invitation}/guests',           [GuestController::class, 'store']);
    Route::delete('/{invitation}/guests/{guest}', [GuestController::class, 'destroy']);
});
