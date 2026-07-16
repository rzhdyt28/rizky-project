<?php

namespace App\Filament\Resources;

use App\Modules\Invitation\Models\Theme;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ThemeResource extends Resource
{
    protected static ?string $model = Theme::class;
    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?string $navigationGroup = 'Konten';

    /** Daftar section yang bisa diatur visibility/order/background-nya. */
    public const SECTIONS = [
        'countdown'  => 'Countdown',
        'couple'     => 'Mempelai',
        'events'     => 'Acara',
        'co_host'    => 'Turut Mengundang',
        'love_story' => 'Kisah Cinta',
        'gallery'    => 'Galeri',
        'video'      => 'Video',
        'rsvp'       => 'RSVP',
        'guestbook'  => 'Ucapan & Doa',
        'gift'       => 'Hadiah',
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas Tema')->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('component_key')
                    ->helperText('Nama folder Layout Vue di themes/* (mis. elegant)')
                    ->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('tier')
                    ->options(['free' => 'Free', 'premium' => 'Premium', 'platinum' => 'Platinum'])
                    ->default('free'),
                Forms\Components\FileUpload::make('preview_image')->image()->disk('public')->directory('themes'),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),

            Forms\Components\Tabs::make('Opsi Desain Default')->tabs([

                Forms\Components\Tabs\Tab::make('Warna')->schema([
                    Forms\Components\ColorPicker::make('default_options.colors.accent')->label('Aksen utama')->default('#2F4A3C'),
                    Forms\Components\ColorPicker::make('default_options.colors.paper')->label('Background dasar')->default('#F7F4EC'),
                    Forms\Components\ColorPicker::make('default_options.colors.ink')->label('Warna teks')->default('#22301F'),
                    Forms\Components\ColorPicker::make('default_options.colors.gold')->label('Aksen dekoratif')->default('#B08D4A'),
                    Forms\Components\ColorPicker::make('default_options.colors.button_bg')->label('Tombol: background')->default('#2F4A3C'),
                    Forms\Components\ColorPicker::make('default_options.colors.button_text')->label('Tombol: teks')->default('#F7F4EC'),
                ])->columns(3),

                Forms\Components\Tabs\Tab::make('Font')->schema([
                    Forms\Components\Select::make('default_options.fonts.heading')->label('Font judul')->options([
                        'Cormorant Garamond' => 'Cormorant Garamond',
                        'Playfair Display'   => 'Playfair Display',
                        'Great Vibes'        => 'Great Vibes',
                        'Cinzel'             => 'Cinzel',
                        'Lora'               => 'Lora',
                    ])->default('Cormorant Garamond'),
                    Forms\Components\Select::make('default_options.fonts.body')->label('Font isi')->options([
                        'Jost'      => 'Jost',
                        'Poppins'   => 'Poppins',
                        'Lato'      => 'Lato',
                        'Open Sans' => 'Open Sans',
                        'Nunito'    => 'Nunito',
                    ])->default('Jost'),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('Background Global')->schema([
                    Forms\Components\Select::make('default_options.background.type')
                        ->options(['color' => 'Warna solid', 'image' => 'Gambar'])
                        ->default('color')->live()->label('Jenis background'),
                    Forms\Components\ColorPicker::make('default_options.background.value')->label('Warna')
                        ->visible(fn (Forms\Get $get) => $get('default_options.background.type') !== 'image'),
                    Forms\Components\FileUpload::make('default_options.background.image')->label('Gambar')
                        ->image()->disk('public')->directory('theme-bg')
                        ->visible(fn (Forms\Get $get) => $get('default_options.background.type') === 'image'),
                    Forms\Components\TextInput::make('default_options.background.overlay_opacity')
                        ->numeric()->minValue(0)->maxValue(1)->step(0.05)->default(0)
                        ->label('Overlay gelap (0-1)')
                        ->helperText('Meredupkan gambar supaya teks terbaca. 0 = tanpa overlay.'),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('Ornamen & Sampul')->schema([
                    Forms\Components\FileUpload::make('default_options.cover.bg_image')->label('Background sampul')->image()->disk('public')->directory('covers'),
                    Forms\Components\FileUpload::make('default_options.cover.ornament_top')->label('Ornamen atas')->image()->disk('public')->directory('ornaments'),
                    Forms\Components\FileUpload::make('default_options.cover.ornament_bottom')->label('Ornamen bawah')->image()->disk('public')->directory('ornaments'),
                    Forms\Components\Toggle::make('default_options.cover.show_monogram')->label('Tampilkan monogram')->default(true),
                ])->columns(2),

                Forms\Components\Tabs\Tab::make('Section')->schema(
                    collect(self::SECTIONS)->map(fn (string $label, string $key) =>
                        Forms\Components\Fieldset::make($label)->schema([
                            Forms\Components\Toggle::make("default_options.sections.$key.visible")
                                ->label('Tampil')->default(true),
                            Forms\Components\TextInput::make("default_options.sections.$key.order")
                                ->numeric()->label('Urutan')
                                ->default(array_search($key, array_keys(self::SECTIONS)) + 1),
                            Forms\Components\Select::make("default_options.sections.$key.background.type")
                                ->options(['inherit' => 'Ikut global', 'color' => 'Warna', 'image' => 'Gambar'])
                                ->default('inherit')->live()->label('Background section'),
                            Forms\Components\ColorPicker::make("default_options.sections.$key.background.value")
                                ->label('Warna')
                                ->visible(fn (Forms\Get $get) => $get("default_options.sections.$key.background.type") === 'color'),
                            Forms\Components\FileUpload::make("default_options.sections.$key.background.image")
                                ->label('Gambar')->image()->disk('public')->directory('section-bg')
                                ->visible(fn (Forms\Get $get) => $get("default_options.sections.$key.background.type") === 'image'),
                        ])->columns(3)
                    )->values()->all()
                ),

                Forms\Components\Tabs\Tab::make('Label Teks')->schema([
                    Forms\Components\TextInput::make('default_options.labels.btn_open')->label('Tombol buka')->default('Buka Undangan'),
                    Forms\Components\TextInput::make('default_options.labels.btn_rsvp')->label('Tombol RSVP')->default('Kirim Konfirmasi'),
                    Forms\Components\TextInput::make('default_options.labels.title_events')->default('Rangkaian Acara'),
                    Forms\Components\TextInput::make('default_options.labels.title_co_host')->default('Turut Mengundang'),
                    Forms\Components\TextInput::make('default_options.labels.title_story')->default('Kisah Kami'),
                    Forms\Components\TextInput::make('default_options.labels.title_gallery')->default('Galeri'),
                    Forms\Components\TextInput::make('default_options.labels.title_video')->default('Video'),
                    Forms\Components\TextInput::make('default_options.labels.title_rsvp')->default('Konfirmasi Kehadiran'),
                    Forms\Components\TextInput::make('default_options.labels.title_guestbook')->default('Ucapan & Doa'),
                    Forms\Components\TextInput::make('default_options.labels.title_gift')->default('Kirim Hadiah'),
                ])->columns(2),

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