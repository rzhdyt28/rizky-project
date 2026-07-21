<?php

namespace App\Filament\Resources\Portfolio;

use App\Modules\Portfolio\Models\Education;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PortfolioEducationResource extends Resource
{
    protected static ?string $model = Education::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Portofolio';
    protected static ?string $navigationLabel = 'Pendidikan & Sertifikasi';
    protected static ?string $modelLabel = 'Pendidikan/Sertifikasi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('tenant_id')
                ->relationship('tenant', 'name')->required()->label('Pemilik'),
            Forms\Components\Select::make('kind')->options([
                'education'     => 'Pendidikan',
                'certification' => 'Sertifikasi',
            ])->default('education')->required(),
            Forms\Components\Fieldset::make('Gelar/Nama (dua bahasa)')->schema([
                Forms\Components\TextInput::make('degree.id')->label('Indonesia')->required(),
                Forms\Components\TextInput::make('degree.en')->label('English')->required(),
            ]),
            Forms\Components\TextInput::make('institution')->required(),
            Forms\Components\TextInput::make('period')->helperText('mis. 2019 - 2023'),
            Forms\Components\TextInput::make('gpa')->label('IPK/Nilai'),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('kind')->badge()
                ->formatStateUsing(fn (string $state) => $state === 'education' ? 'Pendidikan' : 'Sertifikasi'),
            Tables\Columns\TextColumn::make('degree.id')->label('Gelar/Nama'),
            Tables\Columns\TextColumn::make('institution')->searchable(),
            Tables\Columns\TextColumn::make('period'),
        ])->filters([
            Tables\Filters\SelectFilter::make('kind')->options([
                'education' => 'Pendidikan', 'certification' => 'Sertifikasi',
            ]),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => PortfolioEducationResource\Pages\ListPortfolioEducation::route('/'),
            'create' => PortfolioEducationResource\Pages\CreatePortfolioEducation::route('/create'),
            'edit'   => PortfolioEducationResource\Pages\EditPortfolioEducation::route('/{record}/edit'),
        ];
    }
}