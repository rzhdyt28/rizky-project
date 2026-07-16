<?php

namespace App\Filament\Resources;

use App\Modules\Invitation\Models\Invitation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvitationResource extends Resource
{
    protected static ?string $model = Invitation::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Undangan';
    protected static ?string $navigationLabel = 'Undangan';
    protected static ?string $modelLabel = 'Undangan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Utama')->schema([
                Forms\Components\Select::make('tenant_id')
                    ->relationship('tenant', 'name')->required()->label('Pemilik'),
                Forms\Components\TextInput::make('slug')->required()->alphaDash()->unique(ignoreRecord: true)
                    ->helperText('Hanya huruf, angka, strip. URL: /i/{slug}'),
                Forms\Components\Select::make('theme_id')
                    ->relationship('theme', 'name')->required()->label('Tema'),
                Forms\Components\Select::make('status')->options([
                    'draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived',
                ])->default('draft')->required(),
            ])->columns(2),

            Forms\Components\Section::make('Mempelai')->schema([
                Forms\Components\TextInput::make('groom_name')->label('Mempelai Pria')->required(),
                Forms\Components\TextInput::make('bride_name')->label('Mempelai Wanita')->required(),
                Forms\Components\TextInput::make('groom_parents')->label('Orang Tua Pria'),
                Forms\Components\TextInput::make('bride_parents')->label('Orang Tua Wanita'),
                Forms\Components\Textarea::make('opening_text')->label('Teks Pembuka')->rows(3)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Turut Mengundang (Premium+)')->schema([
                Forms\Components\Repeater::make('co_hosts')
                    ->label('Daftar nama')
                    ->simple(Forms\Components\TextInput::make('name')->required()
                        ->placeholder('mis. Kel. Besar Bpk. H. Ahmad'))
                    ->defaultItems(0)
                    ->helperText('Tampil hanya jika paket pelanggan mengaktifkan fitur Turut Mengundang.'),
            ]),

            Forms\Components\Section::make('Media (Premium+)')->schema([
                 Forms\Components\FileUpload::make('music_url')
                    ->label('Musik latar (mp3)')
                    ->disk('public')
                    ->directory('music')
                    ->acceptedFileTypes(['audio/mpeg', 'audio/mp3'])
                    ->maxSize(15360) // 15 MB, cukup untuk 1 lagu mp3 kualitas standar
                    ->helperText('Upload file mp3 milik sendiri.'),
                Forms\Components\TextInput::make('video_url')->url()->label('URL video (YouTube)')
                    ->helperText('Contoh: https://www.youtube.com/watch?v=xxxx — otomatis diubah jadi embed.'),
            ])->columns(2),

            Forms\Components\Section::make('Fitur Interaksi')->schema([
                Forms\Components\Toggle::make('rsvp_enabled')->label('RSVP aktif')->default(true),
                Forms\Components\Toggle::make('guestbook_enabled')->label('Buku ucapan aktif')->default(true),
            ])->columns(2),

            Forms\Components\Section::make('Override Tampilan (opsional, mengalahkan default tema)')
                ->collapsed()
                ->schema([
                    Forms\Components\ColorPicker::make('theme_options.colors.accent')->label('Aksen'),
                    Forms\Components\ColorPicker::make('theme_options.colors.paper')->label('Background'),
                    Forms\Components\ColorPicker::make('theme_options.colors.ink')->label('Teks'),
                    Forms\Components\Select::make('theme_options.background.type')
                        ->options(['inherit' => 'Ikut tema', 'color' => 'Warna', 'image' => 'Gambar'])
                        ->default('inherit')->live()->label('Background global'),
                    Forms\Components\ColorPicker::make('theme_options.background.value')->label('Warna BG')
                        ->visible(fn (Forms\Get $get) => $get('theme_options.background.type') === 'color'),
                    Forms\Components\FileUpload::make('theme_options.background.image')->label('Gambar BG')
                        ->image()->directory('invitation-bg')
                        ->visible(fn (Forms\Get $get) => $get('theme_options.background.type') === 'image'),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withoutGlobalScope('tenant'))
            ->columns([
                Tables\Columns\TextColumn::make('slug')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('groom_name')->label('Pria'),
                Tables\Columns\TextColumn::make('bride_name')->label('Wanita'),
                Tables\Columns\TextColumn::make('theme.name')->label('Tema')->badge(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'published' => 'success', 'draft' => 'warning', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('rsvps_count')->counts('rsvps')->label('RSVP'),
                Tables\Columns\TextColumn::make('tenant.name')->label('Pemilik'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getRelations(): array
    {
        return [
            InvitationResource\RelationManagers\EventsRelationManager::class,
            InvitationResource\RelationManagers\StoriesRelationManager::class,
            InvitationResource\RelationManagers\GiftsRelationManager::class,
            InvitationResource\RelationManagers\RsvpsRelationManager::class,
            InvitationResource\RelationManagers\GalleryPhotosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => InvitationResource\Pages\ListInvitations::route('/'),
            'create' => InvitationResource\Pages\CreateInvitation::route('/create'),
            'edit'   => InvitationResource\Pages\EditInvitation::route('/{record}/edit'),
        ];
    }
}
