<?php

namespace App\Filament\Resources;

use App\Modules\Portfolio\Models\ContactMessage;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox';
    protected static ?string $navigationGroup = 'Portofolio';
    protected static ?string $navigationLabel = 'Pesan Masuk';
    protected static ?string $modelLabel = 'Pesan';

    public static function canCreate(): bool
    {
        return false; // pesan hanya masuk dari form kontak publik
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) ContactMessage::where('is_read', false)->count() ?: null;
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\IconColumn::make('is_read')->label('Dibaca')->boolean(),
            Tables\Columns\TextColumn::make('sender_name')->searchable(),
            Tables\Columns\TextColumn::make('sender_email'),
            Tables\Columns\TextColumn::make('message')->limit(60)->wrap(),
            Tables\Columns\TextColumn::make('created_at')->since()->label('Masuk'),
        ])->defaultSort('created_at', 'desc')
          ->actions([
              Tables\Actions\Action::make('toggleRead')
                  ->label(fn (ContactMessage $r) => $r->is_read ? 'Tandai belum dibaca' : 'Tandai dibaca')
                  ->icon('heroicon-o-envelope-open')
                  ->action(fn (ContactMessage $r) => $r->update(['is_read' => ! $r->is_read])),
              Tables\Actions\DeleteAction::make(),
          ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ContactMessageResource\Pages\ListContactMessages::route('/'),
        ];
    }
}
