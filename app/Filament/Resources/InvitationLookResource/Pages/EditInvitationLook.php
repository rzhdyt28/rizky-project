<?php

namespace App\Filament\Resources\InvitationLookResource\Pages;

use App\Filament\Resources\InvitationLookResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInvitationLook extends EditRecord
{
    protected static string $resource = InvitationLookResource::class;

    // Tidak ada DeleteAction -- menghapus child theme langsung membuat
    // invitation.theme_id menggantung (FK nullOnDelete), undangan jadi
    // tidak bertema. Kalau undangannya sendiri dihapus, child theme ikut
    // terhapus otomatis (cascadeOnDelete, lihat migration terkait).
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('viewInvitation')
                ->label('Lihat Undangan')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/') . '/i/' . $this->record->invitation?->slug)
                ->openUrlInNewTab()
                ->visible(fn () => (bool) $this->record->invitation?->slug),
            Actions\Action::make('resetToDefaultTheme')
                ->label('Reset ke Default Theme')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reset ke Default Theme?')
                ->modalDescription('Semua pengaturan tampilan khusus undangan ini akan dihapus dan kembali mengikuti default tema dasar. Undangan lain TIDAK terpengaruh.')
                ->modalSubmitActionLabel('Ya, reset')
                ->action(function (): void {
                    $this->record->update(['default_options' => []]);
                    $this->fillForm();

                    Notification::make()
                        ->title('Berhasil direset ke default tema')
                        ->success()
                        ->send();
                }),
        ];
    }
}
