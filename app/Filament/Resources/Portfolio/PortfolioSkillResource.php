<?php

namespace App\Filament\Resources\Portfolio;

use App\Modules\Portfolio\Models\Skill;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PortfolioSkillResource extends Resource
{
    protected static ?string $model = Skill::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Portofolio';
    protected static ?string $navigationLabel = 'Keahlian';
    protected static ?string $modelLabel = 'Keahlian';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('tenant_id')
                ->relationship('tenant', 'name')->required()->label('Pemilik'),
            Forms\Components\TextInput::make('category')->required()
                ->helperText('mis. it-support, networking, programming'),
            Forms\Components\Fieldset::make('Judul (dua bahasa)')->schema([
                Forms\Components\TextInput::make('title.id')->label('Indonesia')->required(),
                Forms\Components\TextInput::make('title.en')->label('English')->required(),
            ]),
            Forms\Components\Fieldset::make('Deskripsi (dua bahasa)')->schema([
                Forms\Components\Textarea::make('description.id')->label('Indonesia')->rows(3)->required(),
                Forms\Components\Textarea::make('description.en')->label('English')->rows(3)->required(),
            ]),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('category')->badge()->searchable(),
            Tables\Columns\TextColumn::make('title.id')->label('Judul'),
            Tables\Columns\TextColumn::make('sort_order')->sortable(),
            Tables\Columns\TextColumn::make('tenant.name')->label('Pemilik'),
        ])->defaultSort('sort_order')
          ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => PortfolioSkillResource\Pages\ListPortfolioSkills::route('/'),
            'create' => PortfolioSkillResource\Pages\CreatePortfolioSkill::route('/create'),
            'edit'   => PortfolioSkillResource\Pages\EditPortfolioSkill::route('/{record}/edit'),
        ];
    }
}
