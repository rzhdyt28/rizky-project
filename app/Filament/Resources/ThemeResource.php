<?php

namespace App\Filament\Resources;

use App\Modules\Invitation\Models\Theme;
use App\Modules\Invitation\Models\ThemeAsset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
    protected static ?string $navigationGroup = 'Konten';

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
                Forms\Components\FileUpload::make('preview_image')->image()->disk('public')->directory('themes'),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),

            Forms\Components\Tabs::make('Opsi Desain Default')->tabs([

                Forms\Components\Tabs\Tab::make('Warna & Font')->schema([
                    Forms\Components\Placeholder::make('info_tokens')
                        ->label('')
                        ->content('Kosongkan = memakai bawaan tema (tokens.js di frontend). Isi hanya kalau mau menimpa.'),
                    Forms\Components\ColorPicker::make('default_options.colors.accent')->label('Aksen utama'),
                    Forms\Components\ColorPicker::make('default_options.colors.paper')->label('Permukaan kartu'),
                    Forms\Components\ColorPicker::make('default_options.colors.ink')->label('Warna teks'),
                    Forms\Components\ColorPicker::make('default_options.colors.gold')->label('Aksen dekoratif'),
                    Forms\Components\ColorPicker::make('default_options.colors.button_bg')->label('Tombol: background'),
                    Forms\Components\ColorPicker::make('default_options.colors.button_text')->label('Tombol: teks'),
                    Forms\Components\Select::make('default_options.fonts.heading')->label('Font judul')->options([
                        'Cormorant Garamond' => 'Cormorant Garamond',
                        'Playfair Display'   => 'Playfair Display',
                        'Cinzel'             => 'Cinzel',
                        'Lora'               => 'Lora',
                    ])->placeholder('Bawaan tema'),
                    Forms\Components\Select::make('default_options.fonts.body')->label('Font isi')->options([
                        'Jost'      => 'Jost',
                        'Poppins'   => 'Poppins',
                        'Lato'      => 'Lato',
                        'Open Sans' => 'Open Sans',
                        'Nunito'    => 'Nunito',
                    ])->placeholder('Bawaan tema'),
                    Forms\Components\Select::make('default_options.fonts.script')->label('Font kaligrafi')->options([
                        'Great Vibes' => 'Great Vibes',
                    ])->placeholder('Bawaan tema'),
                ])->columns(3),

                Forms\Components\Tabs\Tab::make('Ornamen & Latar')->schema([
                    Forms\Components\Placeholder::make('info_cover')
                        ->label('')
                        ->content('Default untuk SEMUA undangan bertema ini. Tiap undangan bisa menimpanya lewat form Undangan.'),
                    Forms\Components\Select::make('default_options.cover.ornament_asset')
                        ->label('Ornamen dalam kartu — dari Pustaka')
                        ->options(fn () => ThemeAsset::optionsFor('ornament') + ThemeAsset::optionsFor('divider'))
                        ->searchable()->placeholder('— pilih dari pustaka —'),
                    Forms\Components\FileUpload::make('default_options.cover.ornament_upload')
                        ->label('Ornamen dalam kartu — upload')
                        ->image()->disk('public')->directory('ornaments'),
                    Forms\Components\Select::make('default_options.background.ornament_asset')
                        ->label('Ornamen background halaman — dari Pustaka')
                        ->options(fn () => ThemeAsset::optionsFor('ornament') + ThemeAsset::optionsFor('divider'))
                        ->searchable()->placeholder('— pilih dari pustaka —')
                        ->helperText('Lapis ornamen transparan di latar halaman (foto pengantin diatur per-undangan).'),
                    Forms\Components\Select::make('default_options.florals.tl')
                        ->label('Floral atas 1 (kiri-atas)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('default_options.florals.tr')
                        ->label('Floral atas 2 (kanan-atas)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('default_options.florals.bl')
                        ->label('Floral bawah 1 (kiri-bawah)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('default_options.florals.br')
                        ->label('Floral bawah 2 (kanan-bawah)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Toggle::make('default_options.layout.card')
                        ->label('Gunakan kartu mengambang')->default(true)
                        ->helperText('Nonaktif: tiap section tampil layar penuh tanpa kartu.'),
                ])->columns(3),

                Forms\Components\Tabs\Tab::make('Countdown & Kartu')->schema([
                    Forms\Components\Select::make('default_options.countdown.style')
                        ->label('Gaya angka countdown')
                        ->options([
                            'circle'  => '1. Bulat (default)',
                            'boxed'   => '2. Kotak berbingkai',
                            'minimal' => '3. Minimal — titik dua',
                            'pill'    => '4. Pil memanjang',
                            'flip'    => '5. Flip clock',
                        ])->placeholder('Bulat (default)'),
                    Forms\Components\Select::make('default_options.countdown.layout')
                        ->label('Isi section countdown')
                        ->options([
                            'simple' => '1. Sederhana',
                            'photo'  => '2. Foto + nama pasangan',
                            'date'   => '3. Tanggal besar',
                            'quote'  => '4. Kutipan pembuka',
                        ])->placeholder('Sederhana'),
                    Forms\Components\Select::make('default_options.animation.preset')
                        ->label('Preset animasi scroll')
                        ->options([
                            'fade-up'    => 'Muncul dari bawah (default)',
                            'fade-down'  => 'Muncul dari atas',
                            'fade-left'  => 'Geser dari kanan',
                            'fade-right' => 'Geser dari kiri',
                            'zoom'       => 'Zoom lembut',
                            'none'       => 'Tanpa animasi',
                        ])->placeholder('Muncul dari bawah (default)'),
                    Forms\Components\Select::make('default_options.layout.hero_card')
                        ->label('Kartu untuk HERO')
                        ->options([
                            'inherit' => 'Ikut pengaturan konten',
                            'card'    => 'Selalu pakai kartu',
                            'plain'   => 'Tanpa kartu (full)',
                        ])->placeholder('Ikut pengaturan konten'),
                    Forms\Components\Select::make('default_options.layout.section_height')
                        ->label('Tinggi section')
                        ->options([
                            'full' => 'Satu layar penuh (default)',
                            'auto' => 'Setinggi konten (tanpa gap)',
                        ])->placeholder('Satu layar penuh (default)'),
                    Forms\Components\ColorPicker::make('default_options.card.bg')->label('Warna kartu'),
                    Forms\Components\TextInput::make('default_options.card.opacity')->label('Opacity kartu (%)')
                        ->numeric()->minValue(0)->maxValue(100)->placeholder('100'),
                    Forms\Components\TextInput::make('default_options.card.radius')->label('Radius kartu (px)')
                        ->numeric()->minValue(0)->maxValue(80)->placeholder('28'),
                    Forms\Components\ColorPicker::make('default_options.card.shadow_color')->label('Warna shadow'),
                    Forms\Components\Select::make('default_options.card.shadow_size')->label('Ketebalan shadow')
                        ->options([
                            'none'   => 'Tanpa shadow',
                            'lembut' => 'Lembut',
                            'sedang' => 'Sedang (default)',
                            'kuat'   => 'Kuat',
                        ])->placeholder('Sedang (default)'),
                ])->columns(3),

                Forms\Components\Tabs\Tab::make('Label Teks')->schema([
                    Forms\Components\TextInput::make('default_options.labels.btn_open')->label('Tombol buka')->placeholder('Buka Undangan'),
                    Forms\Components\TextInput::make('default_options.labels.btn_rsvp')->label('Tombol RSVP')->placeholder('Kirim Konfirmasi'),
                    Forms\Components\TextInput::make('default_options.labels.title_events')->placeholder('Rangkaian Acara'),
                    Forms\Components\TextInput::make('default_options.labels.title_co_host')->placeholder('Turut Mengundang'),
                    Forms\Components\TextInput::make('default_options.labels.title_story')->placeholder('Kisah Kami'),
                    Forms\Components\TextInput::make('default_options.labels.title_gallery')->placeholder('Galeri'),
                    Forms\Components\TextInput::make('default_options.labels.title_video')->placeholder('Video'),
                    Forms\Components\TextInput::make('default_options.labels.title_rsvp')->placeholder('Konfirmasi Kehadiran'),
                    Forms\Components\TextInput::make('default_options.labels.title_guestbook')->placeholder('Ucapan & Doa'),
                    Forms\Components\TextInput::make('default_options.labels.title_gift')->placeholder('Kirim Hadiah'),
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
