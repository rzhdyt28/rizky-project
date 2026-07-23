<?php

namespace App\Filament\Resources\Undangan;

use App\Filament\Support\ThemeOptionsSchema;
use App\Modules\Invitation\Models\Theme;
use App\Modules\Invitation\Models\ThemeAsset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * TEMA — form DISEDERHANAKAN (v2 arsitektur).
 * Warna/font default tema kini hidup di frontend (themes/<key>/tokens.js);
 * nilai di sini hanyalah OVERRIDE admin di atasnya, jadi semua field boleh
 * dikosongkan dan tema tetap tampil benar.
 * Fitur "Background Global" & pengaturan per-Section DIHAPUS dari produk
 * karena efeknya di frontend tidak konsisten (sumber bug).
 */
class ThemeResource extends Resource
{
    protected static ?string $model = Theme::class;
    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?string $navigationGroup = 'Undangan - Konten';

    /**
     * Tema privat milik 1 undangan (invitation_id terisi -- lihat
     * InvitationLookResource) TIDAK PERNAH boleh muncul di sini. Resource
     * ini murni untuk tema DASAR/publik yang bisa dipilih banyak undangan.
     * Discope di level query dasar (bukan cuma table()) supaya list, edit,
     * view, delete, dan global search semuanya konsisten -- termasuk kalau
     * ada yang coba akses /admin/themes/{child_id}/edit langsung, akan 404.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNull('invitation_id');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas Tema')->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('component_key')
                    ->helperText('Nama folder tema Vue di themes/* (mis. elegant, mildness)')
                    ->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('tier')
                    ->options(['free' => 'Free', 'premium' => 'Premium', 'platinum' => 'Platinum'])
                    ->default('free'),
                Forms\Components\Select::make('parent_id')
                    ->label('Tema induk (parent)')
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query, ?Theme $record) => ($record ? $query->whereKeyNot($record->getKey()) : $query)
                            ->whereNull('invitation_id'),
                    )
                    ->placeholder('— tanpa parent —')
                    ->helperText('Tema ini MEWARISI seluruh default parent; field yang diisi di sini menimpanya. component_key boleh dibiarkan berbeda dari parent — bila folder Vue-nya tidak ada, frontend otomatis memakai layout parent.'),
                Forms\Components\FileUpload::make('preview_image')->image()->disk('public')->directory('undangan/themes'),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(['default' => 1, 'sm' => 2]),

            Forms\Components\Tabs::make('Opsi Desain Default')->tabs([

                Forms\Components\Tabs\Tab::make('Warna & Font')->schema(array_merge([
                    Forms\Components\Placeholder::make('info_tokens')
                        ->label('')
                        ->content(function (?Theme $record) {
                            $base = 'Kosongkan = memakai bawaan tema (tokens.js di frontend). Isi hanya kalau mau menimpa TAMPILAN DASAR tema ini.';
                            if (! $record) {
                                return $base;
                            }
                            $childCount = $record->children()->whereNotNull('invitation_id')->count();
                            if ($childCount === 0) {
                                return $base;
                            }

                            return new \Illuminate\Support\HtmlString($base . " <strong style=\"color:#b45309\">Perhatian:</strong> tema ini dipakai {$childCount} undangan yang SUDAH punya kustomisasi tampilan sendiri — warna yang diubah di sini TIDAK akan terlihat di undangan itu (kustomisasi undangan selalu menang). Untuk mengubah tampilan SATU undangan tertentu, buka undangannya lalu klik tombol \"Edit Tampilan\".");
                        }),
                ], ThemeOptionsSchema::colorFields('default_options'), ThemeOptionsSchema::typographyFields('default_options')))->columns(['default' => 1, 'sm' => 2, 'lg' => 3]),

                Forms\Components\Tabs\Tab::make('Hero')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\Select::make('default_options.hero.style')
                            ->label('Model tampilan hero (default)')
                            ->live()
                            ->options(ThemeOptionsSchema::HERO_STYLES)
                            ->placeholder('Classic (default)')
                            ->helperText('Model Framed/Split/Custom/Arch/Polaroid butuh "Foto Berbingkai" di bawah -- kalau kosong, tidak akan tampil foto. Tiap undangan bisa menimpa gaya ini sendiri lewat "Edit Tampilan".')
                            ->columnSpanFull(),
                        Forms\Components\Fieldset::make('Foto Berbingkai Hero (default)')
                            ->columnSpanFull()
                            ->schema([
                                Forms\Components\FileUpload::make('default_options.hero.framed_photo')
                                    ->label('Foto berbingkai')
                                    ->image()->disk('public')->directory('undangan/covers')
                                    ->imageEditor()
                                    ->imageEditorAspectRatios(['1:1', '3:4', '4:5', null])
                                    ->imageEditorViewportWidth('800')
                                    ->imageEditorViewportHeight('1000')
                                    ->helperText('Khusus gaya Framed/Split/Custom/Arch/Polaroid -- TERPISAH dari background halaman di bawah. Biasanya dikosongkan di tema dasar (foto asli pasangan diisi per-undangan), kecuali untuk contoh/preview tema. Klik foto setelah upload untuk crop (rekomendasi rasio: 1:1 Framed/Custom, 3:4 Split, 4:5 Arch/Polaroid).'),
                            ]),

                        Forms\Components\Fieldset::make('Dresscode (default)')
                            ->columns(['default' => 1, 'sm' => 2])
                            ->schema([
                                Forms\Components\Toggle::make('default_options.hero.dresscode_enabled')
                                    ->label('Tampilkan dresscode')->live(),
                                Forms\Components\TextInput::make('default_options.hero.dresscode')
                                    ->label('Teks dresscode')
                                    ->placeholder('mis. Batik / Nuansa Earth Tone')
                                    ->visible(fn (Forms\Get $get) => (bool) $get('default_options.hero.dresscode_enabled')),
                            ]),

                        Forms\Components\Fieldset::make('Kartu Hero')
                            ->columns(['default' => 1, 'sm' => 2])
                            ->schema([
                                Forms\Components\Select::make('default_options.layout.hero_card')
                                    ->label('Kartu hero')
                                    ->options(ThemeOptionsSchema::HERO_CARD_MODES)
                                    ->placeholder('Ikut pengaturan konten'),
                                Forms\Components\Select::make('default_options.hero.card_style')
                                    ->label('Gaya kartu hero')
                                    ->options(ThemeOptionsSchema::CARD_STYLES)
                                    ->placeholder('Ikut gaya global'),
                            ]),

                        Forms\Components\Fieldset::make('Tipografi, Ukuran & Warna tiap elemen teks hero (default)')
                            ->columnSpanFull()
                            ->columns(['default' => 1, 'sm' => 2])
                            ->schema(ThemeOptionsSchema::heroElementStyleFields()),
                    ])->columns(['default' => 1, 'sm' => 2]),

                Forms\Components\Tabs\Tab::make('Background Halaman')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\Placeholder::make('info_hero_bg')
                            ->label('')
                            ->content('Latar TETAP di belakang seluruh halaman (BUKAN foto berbingkai hero -- itu ada di tab Hero). Biasanya dikosongkan di tema dasar; diisi per-undangan. Pilih SATU sumber -- ganti sumber otomatis mengosongkan yang lain.')
                            ->columnSpanFull(),
                        Forms\Components\Radio::make('hero_bg_source')
                            ->label('Sumber background')
                            ->options(['slideshow' => 'Foto / Slideshow', 'video' => 'Video'])
                            ->afterStateHydrated(function (Forms\Components\Radio $component, Forms\Get $get) {
                                $component->state(filled($get('default_options.hero.video_url')) ? 'video' : 'slideshow');
                            })
                            ->inline()->live()->dehydrated(false)
                            ->afterStateUpdated(function (?string $state, Forms\Set $set) {
                                if ($state === 'video') {
                                    $set('default_options.hero.slideshow', []);
                                } else {
                                    $set('default_options.hero.video_url', null);
                                }
                            })
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('default_options.hero.slideshow')
                            ->label('Slideshow background (maks. 3 foto)')
                            ->image()->disk('public')->directory('undangan/covers')
                            ->multiple()->maxFiles(3)->reorderable()
                            ->visible(fn (Forms\Get $get) => ($get('hero_bg_source') ?? 'slideshow') === 'slideshow')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('default_options.hero.effect')
                            ->label('Efek pergantian slideshow')
                            ->options(ThemeOptionsSchema::HERO_EFFECTS)
                            ->placeholder('Fade (rekomendasi)')
                            ->visible(fn (Forms\Get $get) => ($get('hero_bg_source') ?? 'slideshow') === 'slideshow'),
                        Forms\Components\TextInput::make('default_options.hero.interval')
                            ->label('Jeda per foto (detik)')
                            ->numeric()->minValue(4)->maxValue(12)->placeholder('6')
                            ->visible(fn (Forms\Get $get) => ($get('hero_bg_source') ?? 'slideshow') === 'slideshow'),
                        Forms\Components\FileUpload::make('default_options.hero.video_url')
                            ->label('Background Video (upload .mp4)')
                            ->disk('public')->directory('undangan/videos')
                            ->acceptedFileTypes(['video/mp4'])->maxSize(51200)
                            ->visible(fn (Forms\Get $get) => $get('hero_bg_source') === 'video')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('default_options.hero.video_effect')
                            ->label('Efek video')
                            ->options(ThemeOptionsSchema::VIDEO_EFFECTS)
                            ->placeholder('Tanpa efek (default)')
                            ->visible(fn (Forms\Get $get) => $get('hero_bg_source') === 'video'),
                    ])->columns(['default' => 1, 'sm' => 2]),

                Forms\Components\Tabs\Tab::make('Ornamen & Latar')->schema([
                    Forms\Components\Placeholder::make('info_cover')
                        ->label('')
                        ->content('Default untuk SEMUA undangan bertema ini. Tiap undangan bisa menimpanya lewat form Undangan. (Ornamen di dalam kartu sudah DIHAPUS dari produk.)'),
                    Forms\Components\Select::make('default_options.background.ornament_asset')
                        ->label('Ornamen background halaman — dari Pustaka')
                        ->options(fn () => ThemeAsset::optionsFor('ornament') + ThemeAsset::optionsFor('divider'))
                        ->searchable()->placeholder('— pilih dari pustaka —')
                        ->helperText('Lapis ornamen transparan di latar halaman (foto pengantin diatur per-undangan).'),
                    Forms\Components\Toggle::make('default_options.florals.disabled')
                        ->label('Nonaktifkan floral sepenuhnya')
                        ->helperText('Beda dari mengosongkan pilihan di bawah (=pakai floral SVG bawaan): toggle ini benar-benar MELEPAS floral, tidak ada floral sama sekali.')
                        ->live()
                        ->columnSpanFull(),
                    Forms\Components\Select::make('default_options.florals.tl')
                        ->disabled(fn (Forms\Get $get) => (bool) $get('default_options.florals.disabled'))
                        ->label('Floral atas 1 (kiri-atas)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('default_options.florals.tr')
                        ->disabled(fn (Forms\Get $get) => (bool) $get('default_options.florals.disabled'))
                        ->label('Floral atas 2 (kanan-atas)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('default_options.florals.bl')
                        ->disabled(fn (Forms\Get $get) => (bool) $get('default_options.florals.disabled'))
                        ->label('Floral bawah 1 (kiri-bawah)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('default_options.florals.br')
                        ->disabled(fn (Forms\Get $get) => (bool) $get('default_options.florals.disabled'))
                        ->label('Floral bawah 2 (kanan-bawah)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Toggle::make('default_options.layout.card')
                        ->label('Gunakan kartu mengambang')->default(true)
                        ->helperText('Nonaktif: tiap section tampil layar penuh tanpa kartu.'),
                ])->columns(['default' => 1, 'sm' => 2, 'lg' => 3]),

                Forms\Components\Tabs\Tab::make('Countdown & Kartu')->schema(array_merge([
                    Forms\Components\Select::make('default_options.countdown.style')
                        ->label('Gaya angka countdown')
                        ->options(ThemeOptionsSchema::COUNTDOWN_STYLES)
                        ->placeholder('Bulat (default)'),
                    Forms\Components\Select::make('default_options.countdown.layout')
                        ->label('Isi section countdown')
                        ->options(ThemeOptionsSchema::COUNTDOWN_LAYOUTS)
                        ->placeholder('Sederhana'),
                    Forms\Components\Select::make('default_options.animation.preset')
                        ->label('Preset animasi scroll')
                        ->options(ThemeOptionsSchema::ANIMATION_PRESETS)
                        ->placeholder('Muncul dari bawah (default)'),
                    Forms\Components\Select::make('default_options.layout.section_height')
                        ->label('Tinggi section')
                        ->options(ThemeOptionsSchema::SECTION_HEIGHTS)
                        ->placeholder('Satu layar penuh (default)'),
                ], ThemeOptionsSchema::cardFields('default_options', ThemeOptionsSchema::CARD_STYLES)))->columns(['default' => 1, 'sm' => 2, 'lg' => 3]),

                Forms\Components\Tabs\Tab::make('Gaya Section')
                    ->icon('heroicon-o-swatch')
                    ->schema([
                        Forms\Components\Placeholder::make('info_section_styles')
                            ->label('')
                            ->content('Model tampilan DEFAULT tiap section untuk tema ini. Tiap undangan bisa menimpa gayanya sendiri lewat "Edit Tampilan" -- pengaturan detail (ukuran/warna per-elemen, background, kartu per-section) sengaja HANYA ada di tingkat undangan supaya halaman Tema ini tetap sederhana.')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('default_options.couple.style')
                            ->label('Mempelai')->options(ThemeOptionsSchema::COUPLE_STYLES)->placeholder('Classic (default)'),
                        Forms\Components\Select::make('default_options.events.style')
                            ->label('Acara')->options(ThemeOptionsSchema::EVENTS_STYLES)->placeholder('Card (default)'),
                        Forms\Components\Select::make('default_options.love_story.style')
                            ->label('Kisah Cinta')->options(ThemeOptionsSchema::LOVE_STORY_STYLES)->placeholder('Stacked (default)'),
                        Forms\Components\Select::make('default_options.gallery.style')
                            ->label('Galeri Foto')->options(ThemeOptionsSchema::GALLERY_STYLES)->placeholder('Carousel (default)'),
                        Forms\Components\Select::make('default_options.video.style')
                            ->label('Video')->options(ThemeOptionsSchema::VIDEO_STYLES)->placeholder('Classic (default)'),
                        Forms\Components\Select::make('default_options.rsvp.style')
                            ->label('Konfirmasi Kehadiran')->options(ThemeOptionsSchema::RSVP_STYLES)->placeholder('Card (default)'),
                        Forms\Components\Select::make('default_options.guestbook.style')
                            ->label('Ucapan & Doa')->options(ThemeOptionsSchema::GUESTBOOK_STYLES)->placeholder('List (default)'),
                        Forms\Components\Select::make('default_options.gift.style')
                            ->label('Hadiah Digital')->options(ThemeOptionsSchema::GIFT_STYLES)->placeholder('Panel (default)'),
                        Forms\Components\Select::make('default_options.co_host.style')
                            ->label('Turut Mengundang')->options(ThemeOptionsSchema::CO_HOST_STYLES)->placeholder('Classic (default)'),
                    ])->columns(['default' => 1, 'sm' => 2, 'lg' => 3]),

                Forms\Components\Tabs\Tab::make('Label Teks')
                    ->schema(ThemeOptionsSchema::labelFields('default_options'))->columns(['default' => 1, 'sm' => 2]),

                Forms\Components\Tabs\Tab::make('Section Aktif')->schema(array_merge([
                    Forms\Components\Placeholder::make('info_sections')
                        ->label('')
                        ->content('Section mana yang aktif SECARA DEFAULT untuk tema ini. Tiap undangan bisa menimpanya sendiri-sendiri lewat checklist "Section Aktif" di form Undangan.'),
                ], ThemeOptionsSchema::sectionVisibilityFields('default_options')))->columns(['default' => 1, 'sm' => 2, 'lg' => 3]),

            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('preview_image'),
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('component_key')->badge(),
            Tables\Columns\TextColumn::make('tier')->badge(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ThemeResource\Pages\ListThemes::route('/'),
            'create' => ThemeResource\Pages\CreateTheme::route('/create'),
            'edit'   => ThemeResource\Pages\EditTheme::route('/{record}/edit'),
        ];
    }
}
