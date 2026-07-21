<?php

namespace App\Filament\Resources\Undangan\InvitationResource\Pages;

use App\Filament\Resources\Undangan\InvitationResource;
use App\Modules\Invitation\Models\Theme;
use App\Modules\Invitation\Support\InvitationThemeProvisioner;
use Filament\Resources\Pages\CreateRecord;

class CreateInvitation extends CreateRecord
{
    protected static string $resource = InvitationResource::class;

    /**
     * "Tema" yang dipilih di form Create ini adalah tema DASAR (lihat
     * InvitationResource::form(), Select theme_id di-scope whereNull('invitation_id')).
     * Undangan sebenarnya butuh CHILD THEME sendiri -- provision di sini,
     * lalu arahkan theme_id undangan ke child theme itu (bukan tema dasarnya
     * langsung). Sama persis dengan InvitationController::store() (API customer).
     */
    protected function afterCreate(): void
    {
        $baseTheme = Theme::whereNull('invitation_id')->find($this->record->theme_id);
        if (! $baseTheme) {
            return;
        }

        $childTheme = app(InvitationThemeProvisioner::class)->provision($this->record, $baseTheme);
        $this->record->update(['theme_id' => $childTheme->id]);
    }
}
