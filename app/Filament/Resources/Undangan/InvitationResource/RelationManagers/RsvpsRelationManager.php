<?php

namespace App\Filament\Resources\Undangan\InvitationResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RsvpsRelationManager extends RelationManager
{
    protected static string $relationship = 'rsvps';
    protected static ?string $title = 'Konfirmasi Kehadiran';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('guest_name')->searchable(),
            Tables\Columns\TextColumn::make('attendance')->badge()
                ->color(fn (string $state) => match ($state) {
                    'attending' => 'success', 'not_attending' => 'danger', default => 'warning',
                }),
            Tables\Columns\TextColumn::make('pax')->label('Jumlah'),
            Tables\Columns\TextColumn::make('phone'),
            Tables\Columns\TextColumn::make('created_at')->since(),
        ])->defaultSort('created_at', 'desc');
    }
}