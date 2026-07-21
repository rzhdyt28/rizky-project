<?php

namespace App\Filament\Resources\Undangan;

use App\Modules\Invitation\Models\ThemeAsset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * PUSTAKA ASET — satu tempat admin mengunggah ornamen/divider/monogram.
 * Aset di sini muncul sebagai pilihan dropdown di form Tema & Undangan,
 * jadi ganti ornamen = tinggal pilih, bukan upload ulang di tiap tempat.
 */
class ThemeAssetResource extends Resource
{
    protected static ?string $model = ThemeAsset::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Undangan - Konten';
    protected static ?string $navigationLabel = 'Pustaka Aset';
    protected static ?string $modelLabel = 'Aset';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama aset')->required()->maxLength(120)
                ->helperText('Nama ini yang tampil di dropdown pemilihan ornamen.'),
            Forms\Components\Select::make('category')->label('Kategori')
                ->options(ThemeAsset::CATEGORIES)->default('ornament')->required(),
            Forms\Components\FileUpload::make('path')->label('File gambar')
                ->image()->disk('public')->directory('assets-pustaka')
                ->acceptedFileTypes(['image/png', 'image/svg+xml', 'image/webp'])
                ->required()
                ->helperText('PNG transparan / SVG dianjurkan supaya menyatu dengan tema.'),
            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true)
                ->helperText('Nonaktifkan untuk menyembunyikan dari dropdown tanpa menghapus.'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('path')->label('Pratinjau')->disk('public')->square(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('category')->label('Kategori')->badge()
                    ->formatStateUsing(fn (string $state) => ThemeAsset::CATEGORIES[$state] ?? $state),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->options(ThemeAsset::CATEGORIES),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ThemeAssetResource\Pages\ListThemeAssets::route('/'),
            'create' => ThemeAssetResource\Pages\CreateThemeAsset::route('/create'),
            'edit'   => ThemeAssetResource\Pages\EditThemeAsset::route('/{record}/edit'),
        ];
    }
}
