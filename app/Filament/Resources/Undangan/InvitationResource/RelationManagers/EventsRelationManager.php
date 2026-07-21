<?php

namespace App\Filament\Resources\Undangan\InvitationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EventsRelationManager extends RelationManager
{
    protected static string $relationship = 'events';
    protected static ?string $title = 'Acara';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->helperText('mis. Akad, Resepsi'),
            Forms\Components\DateTimePicker::make('starts_at')->required()->label('Mulai'),
            Forms\Components\DateTimePicker::make('ends_at')->label('Selesai'),
            Forms\Components\TextInput::make('venue_name')->required()->label('Nama Tempat'),
            Forms\Components\Textarea::make('address')->rows(2)->label('Alamat'),
            Forms\Components\TextInput::make('maps_url')->url()->label('Link Google Maps'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title'),
            Tables\Columns\TextColumn::make('starts_at')->dateTime('d M Y H:i'),
            Tables\Columns\TextColumn::make('venue_name'),
        ])->headerActions([Tables\Actions\CreateAction::make()])
          ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}