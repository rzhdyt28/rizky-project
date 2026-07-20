<?php

namespace App\Filament\Resources\InvitationResource\Pages;

use App\Filament\Resources\InvitationResource;
use App\Modules\Invitation\Models\Theme;
use App\Modules\Invitation\Support\InvitationThemeProvisioner;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvitation extends EditRecord
{
    protected static string $resource = InvitationResource::class;

    /**
     * Select 'theme_id' di form menampilkan & mengirim TEMA DASAR (lihat
     * InvitationResource::form()), bukan child theme privat undangan ini.
     * Route lewat InvitationThemeProvisioner supaya kustomisasi tampilan
     * yang sudah dibuat tidak hilang saat admin ganti tema dasar — sama
     * persis dengan InvitationController::update() (API customer).
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['theme_id'])) {
            $newBaseTheme = Theme::whereNull('invitation_id')->find($data['theme_id']);
            if ($newBaseTheme) {
                $data['theme_id'] = app(InvitationThemeProvisioner::class)
                    ->resolveOnBaseThemeChange($this->record, $newBaseTheme);
            }
        }

        return $data;
    }

    // "Reset ke Default Theme" pindah ke InvitationLookResource (mengedit
    // child theme langsung) -- lihat EditInvitationLook.
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
