<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortfolioExperiencePhotoResource\Pages;
use App\Modules\Portfolio\Models\ExperiencePhoto;
use App\Modules\Portfolio\Models\Experience;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PortfolioExperiencePhotoResource extends Resource
{
    protected static ?string $model = ExperiencePhoto::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Portfolio';
    protected static ?string $navigationLabel = 'Foto Dokumentasi';
    protected static ?string $modelLabel = 'Foto Dokumentasi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('experience_id')
                ->label('Pengalaman Kerja')
                ->options(
                    Experience::query()
                        ->get()
                        ->mapWithKeys(fn ($exp) => [
                            $exp->id => $exp->company . ' — ' . ($exp->role['id'] ?? $exp->role['en'] ?? ''),
                        ])
                )
                ->searchable()
                ->required(),

            Forms\Components\FileUpload::make('path')
                ->label('Foto')
                ->image()
                ->disk('public')
                ->directory('portfolio/experience-photos')
                ->imageEditor()
                ->required(),

            Forms\Components\TextInput::make('caption_id')
                ->label('Caption (Bahasa Indonesia)')
                ->maxLength(255),

            Forms\Components\TextInput::make('caption_en')
                ->label('Caption (English)')
                ->maxLength(255),

            Forms\Components\TextInput::make('sort_order')
                ->label('Urutan Tampil')
                ->numeric()
                ->default(0)
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label('Foto')
                    ->disk('public'),

                Tables\Columns\TextColumn::make('experience.company')
                    ->label('Pengalaman')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('caption')
                    ->label('Caption')
                    ->formatStateUsing(fn ($state) => $state['id'] ?? $state['en'] ?? '-')
                    ->limit(40),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('experience_id')
                    ->label('Pengalaman Kerja')
                    ->relationship('experience', 'company'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPortfolioExperiencePhotos::route('/'),
            'create' => Pages\CreatePortfolioExperiencePhoto::route('/create'),
            'edit' => Pages\EditPortfolioExperiencePhoto::route('/{record}/edit'),
        ];
    }
}