<?php

namespace App\Filament\Resources\Skripsi\SawCaseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AlternativesRelationManager extends RelationManager
{
    protected static string $relationship = 'alternatives';
    protected static ?string $title = 'Alternatif';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama Alternatif')->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Alternatif'),
                Tables\Columns\TextColumn::make('scores_count')->counts('scores')->label('Jumlah Nilai Terisi'),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
