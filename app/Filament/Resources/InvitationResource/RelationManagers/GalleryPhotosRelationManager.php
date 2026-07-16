<?php

namespace App\Filament\Resources\InvitationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Tab "Galeri Foto" di halaman edit Undangan.
 * Sebelumnya tidak terdaftar di InvitationResource::getRelations(),
 * sehingga tidak ada tempat mengunggah foto galeri sama sekali.
 *
 * Relasi 'photos' mengarah ke Invitation::photos() -> GalleryPhoto (kolom: path, caption, sort_order).
 */
class GalleryPhotosRelationManager extends RelationManager
{
    protected static string $relationship = 'photos';

    protected static ?string $title = 'Galeri Foto';

    protected static ?string $modelLabel = 'Foto';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('path')
                ->label('Foto')
                ->image()
                ->disk('public')          // WAJIB public agar bisa diakses lewat /storage/...
                ->directory('gallery')     // tersimpan di storage/app/public/gallery
                ->imageEditor()
                ->maxSize(4096)            // 4 MB, sesuaikan bila perlu
                ->required()
                ->columnSpanFull(),

            Forms\Components\TextInput::make('caption')
                ->label('Keterangan (opsional)')
                ->maxLength(255),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')    // seret-lepas untuk mengurutkan foto
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label('Foto')
                    ->disk('public')
                    ->height(60),

                Tables\Columns\TextColumn::make('caption')
                    ->label('Keterangan')
                    ->placeholder('—')
                    ->wrap(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Tambah Foto'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}