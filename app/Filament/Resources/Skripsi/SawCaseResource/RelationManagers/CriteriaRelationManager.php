<?php

namespace App\Filament\Resources\Skripsi\SawCaseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CriteriaRelationManager extends RelationManager
{
    protected static string $relationship = 'criteria';
    protected static ?string $title = 'Kriteria';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama Kriteria')->required(),
            Forms\Components\TextInput::make('weight')->label('Bobot')->numeric()->minValue(0)->required(),
            Forms\Components\Select::make('type')->label('Tipe')->options([
                'benefit' => 'Benefit',
                'cost' => 'Cost',
            ])->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Kriteria'),
                Tables\Columns\TextColumn::make('weight')->label('Bobot'),
                Tables\Columns\TextColumn::make('type')->label('Tipe')->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'benefit' ? 'Benefit' : 'Cost'),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
