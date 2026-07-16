<?php

namespace App\Filament\Resources;

use App\Modules\Portfolio\Models\Experience;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PortfolioExperienceResource extends Resource
{
    protected static ?string $model = Experience::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationGroup = 'Portofolio';
    protected static ?string $navigationLabel = 'Pengalaman';
    protected static ?string $modelLabel = 'Pengalaman';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('tenant_id')
                ->relationship('tenant', 'name')->required()->label('Pemilik'),
            Forms\Components\TextInput::make('company')->required(),
            Forms\Components\Fieldset::make('Posisi (dua bahasa)')->schema([
                Forms\Components\TextInput::make('role.id')->label('Indonesia')->required(),
                Forms\Components\TextInput::make('role.en')->label('English')->required(),
            ]),
            Forms\Components\TextInput::make('location'),
            Forms\Components\DatePicker::make('start_date')->required(),
            Forms\Components\DatePicker::make('end_date')
                ->helperText('Kosongkan jika masih bekerja di sini'),
            Forms\Components\Repeater::make('bullets')->label('Poin pekerjaan')->schema([
                Forms\Components\TextInput::make('id')->label('Indonesia')->required(),
                Forms\Components\TextInput::make('en')->label('English')->required(),
            ])->columns(2)->defaultItems(1),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('company')->searchable(),
            Tables\Columns\TextColumn::make('role.id')->label('Posisi'),
            Tables\Columns\TextColumn::make('start_date')->date('M Y'),
            Tables\Columns\TextColumn::make('end_date')->date('M Y')->placeholder('Sekarang'),
        ])->defaultSort('start_date', 'desc')
          ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => PortfolioExperienceResource\Pages\ListPortfolioExperiences::route('/'),
            'create' => PortfolioExperienceResource\Pages\CreatePortfolioExperience::route('/create'),
            'edit'   => PortfolioExperienceResource\Pages\EditPortfolioExperience::route('/{record}/edit'),
        ];
    }
}
