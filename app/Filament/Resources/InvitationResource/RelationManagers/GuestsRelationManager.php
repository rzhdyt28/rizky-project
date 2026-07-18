<?php

namespace App\Filament\Resources\InvitationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

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
            ->headerActions([Tables\Actions\CreateAction::make()->label('Tambah tamu')])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
