<?php

namespace App\Filament\Resources\Portfolio;

use App\Modules\Portfolio\Models\Profile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PortfolioProfileResource extends Resource
{
    protected static ?string $model = Profile::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Portofolio';
    protected static ?string $navigationLabel = 'Profil';
    protected static ?string $modelLabel = 'Profil';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('full_name')->label('Nama Lengkap')->required(),
            Forms\Components\Fieldset::make('Headline (dua bahasa)')->schema([
                Forms\Components\TextInput::make('headline.id')->label('Indonesia')->required(),
                Forms\Components\TextInput::make('headline.en')->label('English')->required(),
            ]),
            Forms\Components\Fieldset::make('Tentang Saya (dua bahasa)')->schema([
                Forms\Components\Textarea::make('about.id')->label('Indonesia')->rows(4)->required(),
                Forms\Components\Textarea::make('about.en')->label('English')->rows(4)->required(),
            ]),
            Forms\Components\TextInput::make('location')->label('Lokasi'),
            Forms\Components\FileUpload::make('photo_path')
                ->label('Foto Profil')->image()->directory('portfolio'),
            Forms\Components\FileUpload::make('cv_path')
                ->label('CV (PDF)')->directory('portfolio')->acceptedFileTypes(['application/pdf']),
            Forms\Components\Fieldset::make('Kontak & Sosial Media')->schema([
                Forms\Components\TextInput::make('socials.email')->label('Email')->email(),
                Forms\Components\TextInput::make('socials.whatsapp')->label('WhatsApp')
                    ->helperText('Format internasional tanpa +, mis. 628993766315'),
                Forms\Components\TextInput::make('socials.linkedin')->label('LinkedIn URL')->url(),
                Forms\Components\TextInput::make('socials.github')->label('GitHub URL')->url(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('full_name')->label('Nama')->searchable(),
            Tables\Columns\TextColumn::make('headline.id')->label('Headline'),
            Tables\Columns\TextColumn::make('location')->label('Lokasi'),
        ])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => PortfolioProfileResource\Pages\ListPortfolioProfiles::route('/'),
            'create' => PortfolioProfileResource\Pages\CreatePortfolioProfile::route('/create'),
            'edit' => PortfolioProfileResource\Pages\EditPortfolioProfile::route('/{record}/edit'),
        ];
    }
}
