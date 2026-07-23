<?php

namespace App\Filament\Resources\Skripsi;

use App\Modules\Skripsi\Saw\Models\SawCase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SawCaseResource extends Resource
{
    protected static ?string $model = SawCase::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'Skripsi';
    protected static ?string $navigationLabel = 'SAW — Studi Kasus';
    protected static ?string $modelLabel = 'Studi Kasus SAW';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->relationship('user', 'email')->required()->label('Pemilik')
                ->preload()->searchable(),
            Forms\Components\TextInput::make('title')->required()->label('Judul'),
            Forms\Components\Textarea::make('description')->label('Deskripsi'),
            Forms\Components\Placeholder::make('calculated_at')
                ->label('Terakhir dihitung')
                ->content(fn (?SawCase $record) => $record?->calculated_at?->translatedFormat('d M Y H:i') ?? '—'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('user.email')->label('Pemilik')->searchable(),
            Tables\Columns\TextColumn::make('title')->label('Judul')->searchable(),
            Tables\Columns\TextColumn::make('criteria_count')->counts('criteria')->label('Kriteria'),
            Tables\Columns\TextColumn::make('alternatives_count')->counts('alternatives')->label('Alternatif'),
            Tables\Columns\TextColumn::make('calculated_at')->label('Terakhir dihitung')->dateTime('d M Y H:i')->placeholder('Belum dihitung'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            SawCaseResource\RelationManagers\CriteriaRelationManager::class,
            SawCaseResource\RelationManagers\AlternativesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => SawCaseResource\Pages\ListSawCases::route('/'),
            'create' => SawCaseResource\Pages\CreateSawCase::route('/create'),
            'edit'   => SawCaseResource\Pages\EditSawCase::route('/{record}/edit'),
        ];
    }
}
