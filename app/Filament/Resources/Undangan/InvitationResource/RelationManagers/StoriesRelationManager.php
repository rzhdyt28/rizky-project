<?php

namespace App\Filament\Resources\Undangan\InvitationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'stories';
    protected static ?string $title = 'Kisah Cinta';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\DatePicker::make('happened_at')->label('Tanggal kejadian'),
            Forms\Components\Textarea::make('story')->rows(4)->required(),
            Forms\Components\FileUpload::make('photo')
                ->label('Foto kisah (opsional)')
                ->image()->disk('public')->directory('undangan/stories')
                ->helperText('Tampil di undangan hanya bila toggle "Tampilkan foto kisah" di Section Kisah Kami aktif. Saran rasio 4:3, ≤300KB.'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title'),
            Tables\Columns\TextColumn::make('happened_at')->date(),
        ])->headerActions([Tables\Actions\CreateAction::make()])
          ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}