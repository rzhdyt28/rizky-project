<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TenantsRelationManager extends RelationManager
{
    protected static string $relationship = 'tenants';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return 'Tenant';
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama tenant')->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID'),
                Tables\Columns\TextColumn::make('name')->label('Nama'),
                Tables\Columns\TextColumn::make('invitations_count')->counts('invitations')->label('Undangan'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y')->label('Dibuat'),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }
}
