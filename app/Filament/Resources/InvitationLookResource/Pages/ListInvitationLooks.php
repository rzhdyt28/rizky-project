<?php

namespace App\Filament\Resources\InvitationLookResource\Pages;

use App\Filament\Resources\InvitationLookResource;
use Filament\Resources\Pages\ListRecords;

class ListInvitationLooks extends ListRecords
{
    protected static string $resource = InvitationLookResource::class;

    // Tidak ada CreateAction -- child theme HANYA dibuat lewat provisioning
    // otomatis (lihat InvitationThemeProvisioner), tidak boleh dibuat manual.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
