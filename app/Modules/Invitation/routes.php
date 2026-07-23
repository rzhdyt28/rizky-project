<?php

use App\Modules\Invitation\Http\Controllers\EventController;
use App\Modules\Invitation\Http\Controllers\GalleryPhotoController;
use App\Modules\Invitation\Http\Controllers\GiftController;
use App\Modules\Invitation\Http\Controllers\GuestbookController;
use App\Modules\Invitation\Http\Controllers\GuestController;
use App\Modules\Invitation\Http\Controllers\InvitationController;
use App\Modules\Invitation\Http\Controllers\LoveStoryController;
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
    Route::get('/themes', [InvitationController::class, 'themes']);
    Route::apiResource('/', InvitationController::class)->parameters(['' => 'invitation']);
    Route::post('/{invitation}/upload', [InvitationController::class, 'upload']);
    Route::get('/{invitation}/look',    [InvitationController::class, 'look']);
    Route::put('/{invitation}/look',    [InvitationController::class, 'updateLook']);

    Route::get('/{invitation}/rsvps',        [RsvpController::class, 'index']);
    Route::get('/{invitation}/rsvps/export', [RsvpController::class, 'export']);

    Route::get('/{invitation}/guests',            [GuestController::class, 'index']);
    Route::post('/{invitation}/guests',           [GuestController::class, 'store']);
    Route::post('/{invitation}/guests/import',    [GuestController::class, 'import']);
    Route::delete('/{invitation}/guests/{guest}', [GuestController::class, 'destroy']);

    Route::get('/{invitation}/events',              [EventController::class, 'index']);
    Route::post('/{invitation}/events',             [EventController::class, 'store']);
    Route::put('/{invitation}/events/{event}',      [EventController::class, 'update']);
    Route::delete('/{invitation}/events/{event}',   [EventController::class, 'destroy']);

    Route::get('/{invitation}/stories',             [LoveStoryController::class, 'index']);
    Route::post('/{invitation}/stories',            [LoveStoryController::class, 'store']);
    Route::put('/{invitation}/stories/{story}',     [LoveStoryController::class, 'update']);
    Route::delete('/{invitation}/stories/{story}',  [LoveStoryController::class, 'destroy']);

    Route::get('/{invitation}/gifts',               [GiftController::class, 'index']);
    Route::post('/{invitation}/gifts',              [GiftController::class, 'store']);
    Route::put('/{invitation}/gifts/{gift}',        [GiftController::class, 'update']);
    Route::delete('/{invitation}/gifts/{gift}',     [GiftController::class, 'destroy']);

    Route::get('/{invitation}/photos',              [GalleryPhotoController::class, 'index']);
    Route::post('/{invitation}/photos',             [GalleryPhotoController::class, 'store']);
    Route::post('/{invitation}/photos/reorder',     [GalleryPhotoController::class, 'reorder']);
    Route::put('/{invitation}/photos/{photo}',      [GalleryPhotoController::class, 'update']);
    Route::delete('/{invitation}/photos/{photo}',   [GalleryPhotoController::class, 'destroy']);

    Route::get('/{invitation}/guestbook',            [GuestbookController::class, 'index']);
    Route::put('/{invitation}/guestbook/{entry}',    [GuestbookController::class, 'update']);
    Route::delete('/{invitation}/guestbook/{entry}', [GuestbookController::class, 'destroy']);
});
