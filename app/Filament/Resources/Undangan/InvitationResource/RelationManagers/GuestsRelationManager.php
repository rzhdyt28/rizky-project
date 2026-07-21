<?php

namespace App\Filament\Resources\Undangan\InvitationResource\RelationManagers;

use App\Modules\Invitation\Support\GuestSheetImporter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class GuestsRelationManager extends RelationManager
{
    protected static string $relationship = 'guests';
    protected static ?string $title = 'Daftar Tamu';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama tamu')->required()->maxLength(120),
            Forms\Components\TextInput::make('phone')->label('No. WhatsApp')->tel()->maxLength(30)
                ->helperText('Opsional. Format bebas; dipakai tombol bagikan WA di dashboard user.'),
            Forms\Components\TextInput::make('note')->label('Catatan')->maxLength(160),
        ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('WA')->placeholder('—'),
                Tables\Columns\TextColumn::make('note')->label('Catatan')->placeholder('—')->limit(30),
                // Link personal — path relatif; domain frontend bisa berbeda per lingkungan.
                Tables\Columns\TextColumn::make('link')->label('Link personal')
                    ->state(fn ($record) => '/i/' . $record->invitation->slug . '?to=' . rawurlencode($record->name))
                    ->copyable()->copyMessage('Path tersalin — tambahkan domain frontend di depannya.'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Tambah tamu'),
                Tables\Actions\Action::make('import')
                    ->label('Import Excel/CSV')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->modalDescription('Format kolom: A = Nama (wajib), B = No. WhatsApp, C = Catatan. Baris judul (header) boleh ada — terdeteksi otomatis. Maks. ' . GuestSheetImporter::MAX_ROWS . ' baris sekali import.')
                    ->form([
                        Forms\Components\FileUpload::make('file')
                            ->label('File .xlsx atau .csv')
                            ->disk('local')->directory('tmp-imports')
                            ->acceptedFileTypes([
                                'text/csv', 'text/plain', 'application/csv',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data, RelationManager $livewire): void {
                        $path = Storage::disk('local')->path($data['file']);
                        try {
                            $rows = GuestSheetImporter::parse($path);
                            $livewire->getOwnerRecord()->guests()->createMany($rows);
                            Notification::make()
                                ->title(count($rows) . ' tamu berhasil di-import')
                                ->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Import gagal')
                                ->body($e->getMessage())
                                ->danger()->send();
                        } finally {
                            // File sementara selalu dibersihkan, sukses maupun gagal.
                            Storage::disk('local')->delete($data['file']);
                        }
                    }),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
