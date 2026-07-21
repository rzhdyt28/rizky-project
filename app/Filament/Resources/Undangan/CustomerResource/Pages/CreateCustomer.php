<?php

namespace App\Filament\Resources\Undangan\CustomerResource\Pages;

use App\Core\Models\Tenant;
use App\Filament\Resources\Undangan\CustomerResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    /**
     * Meniru persis AuthController::register() (registrasi mandiri di
     * frontend publik) supaya customer yang dibuat admin dari sini
     * berperilaku identik dengan yang daftar sendiri -- role 'user' +
     * 1 Tenant otomatis. TIDAK ada Auth::login() di sini (beda dengan
     * alur registrasi publik) karena ini dibuat oleh admin, bukan oleh
     * customer itu sendiri.
     */
    protected function afterCreate(): void
    {
        $this->record->assignRole('user');

        Tenant::create([
            'id'            => Str::slug($this->record->name) . '-' . Str::lower(Str::random(6)),
            'name'          => $this->record->name,
            'owner_user_id' => $this->record->id,
        ]);
    }
}
