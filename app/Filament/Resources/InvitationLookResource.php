<?php

namespace App\Filament\Resources;

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
 * TAMPILAN UNDANGAN — semua pengaturan visual satu undangan (warna, font,
 * gaya kartu, layout hero, background/section, dst) hidup di sini, BUKAN di
 * InvitationResource lagi. Model-nya tetap `Theme` -- record yang diedit di
 * sini adalah CHILD THEME privat milik 1 undangan (invitation_id terisi,
 * lihat InvitationThemeProvisioner & migration
 * add_invitation_id_to_themes_table). Data disimpan ke default_options
 * child theme itu, digabung live lewat Theme::ancestryChain() ke tema
 * dasarnya saat halaman publik undangan dirender (PublicInvitationController).
 *
 * Tidak ada di sidebar navigasi (shouldRegisterNavigation = false) --
 * dijangkau lewat tombol "Edit Tampilan" di tabel InvitationResource.
 * Tidak ada action Create/Delete: child theme HANYA boleh dibuat lewat
 * provisioning otomatis (CreateInvitation/InvitationController::store()),
 * dan menghapusnya langsung akan membuat invitation.theme_id menggantung
 * (nullOnDelete) -- undangan jadi tidak bertema sama sekali.
 */
class InvitationLookResource extends Resource
{
    protected static ?string $model = Theme::class;
    protected static ?string $slug = 'invitation-looks';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $modelLabel = 'Tampilan Undangan';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNotNull('invitation_id');
    }

    /** Elemen hero yang bisa diatur bebas (order + rata) di mode Custom. */
    protected const HERO_ELEMENTS = [
        'eyebrow'   => 'Label "Undangan Pernikahan"',
        'photo'     => 'Foto berbingkai (dari "Foto utama")',
        'names'     => 'Nama pasangan',
        'date'      => 'Tanggal acara',
        'countdown' => 'Countdown',
        'dresscode' => 'Dress code',
        'guest'     => 'Kepada (nama tamu)',
        'button'    => 'Tombol "Buka Undangan"',
    ];

    protected static function heroElementFields(): array
    {
        $fields = [];
        $i = 0;
        foreach (self::HERO_ELEMENTS as $key => $label) {
            $i++;
            $fields[] = Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make("default_options.hero.elements.$key.align")
                    ->label($label)
                    ->options(['left' => 'Rata kiri', 'center' => 'Rata tengah', 'right' => 'Rata kanan'])
                    ->placeholder('Tengah (default)'),
                Forms\Components\TextInput::make("default_options.hero.elements.$key.order")
                    ->label('Urutan')
                    ->numeric()->minValue(1)->maxValue(8)
                    ->placeholder((string) $i),
            ]);
        }

        return $fields;
    }

    protected static function sectionDisplayFields(string $key): array
    {
        return [
            Forms\Components\Select::make("default_options.sections.$key.card")
                ->label('Kartu section ini')
                ->options(['on' => 'Selalu pakai kartu', 'off' => 'Tanpa kartu (full)'])
                ->placeholder('Ikut pengaturan global')
                ->helperText('Bisa di-mix: mis. galeri full tanpa kartu, section lain berkartu.'),
            Forms\Components\Select::make("default_options.sections.$key.card_style")
                ->label('Gaya kartu section ini')
                ->options(ThemeOptionsSchema::CARD_STYLES)
                ->placeholder('Ikut gaya global'),
            Forms\Components\Select::make("default_options.sections.$key.height")
                ->label('Tinggi section ini')
                ->options(ThemeOptionsSchema::SECTION_HEIGHTS)
                ->placeholder('Ikut pengaturan global')
                ->helperText('Bisa di-mix per section: mis. section berkartu = "Setinggi konten" (rapat), section full-foto = "Satu layar penuh".'),
            Forms\Components\FileUpload::make("default_options.section_bg.$key")
                ->label('Background section (mode tanpa kartu)')
                ->image()->disk('public')->directory('section-bg')
                ->helperText('Tampil hanya saat section ini TANPA kartu.'),
            Forms\Components\TextInput::make("default_options.sections.$key.font_heading")
                ->label('Font JUDUL section ini')
                ->placeholder('Ikut font judul global')
                ->datalist(['Cormorant Garamond', 'Playfair Display', 'Cinzel', 'Lora', 'EB Garamond', 'Marcellus']),
            Forms\Components\TextInput::make("default_options.sections.$key.title_size")
                ->label('Ukuran judul section ini (px)')
                ->numeric()->minValue(14)->maxValue(96)->placeholder('Ikut ukuran global'),
            Forms\Components\ColorPicker::make("default_options.sections.$key.title_color")
                ->label('Warna judul section ini'),
            Forms\Components\TextInput::make("default_options.sections.$key.font_body")
                ->label('Font ISI section ini')
                ->placeholder('Ikut font isi global')
                ->datalist(['Jost', 'Poppins', 'Lato', 'Open Sans', 'Nunito', 'Inter', 'Mulish']),
            Forms\Components\TextInput::make("default_options.sections.$key.body_size")
                ->label('Ukuran isi section ini (px)')
                ->numeric()->minValue(10)->maxValue(28)->placeholder('Ikut ukuran global'),
            Forms\Components\ColorPicker::make("default_options.sections.$key.body_color")
                ->label('Warna isi section ini'),
        ];
    }

    /** True kalau tema dasar (leluhur) undangan ini adalah/turunan dari component_key 'senja'. */
    protected static function isSenjaBased(?Theme $record): bool
    {
        if (! $record?->parent) {
            return false;
        }

        foreach ($record->parent->ancestryChain() as $ancestor) {
            if ($ancestor->component_key === 'senja') {
                return true;
            }
        }

        return false;
    }

    /** Gabungan default_options SELURUH LELUHUR (tidak termasuk $record sendiri) -- dipakai untuk helperText & fallback toggle. */
    protected static function resolveParentDefaults(?Theme $record): array
    {
        if (! $record?->parent) {
            return [];
        }

        $defaults = [];
        foreach ($record->parent->ancestryChain() as $ancestor) {
            $defaults = array_replace_recursive($defaults, $ancestor->default_options ?? []);
        }

        return $defaults;
    }

    /** public: dipanggil sebagai callable array [self::class, 'parentColor'] dari ThemeOptionsSchema (kelas lain). */
    public static function parentColor(Forms\Get $get, string $key, ?Theme $record): ?string
    {
        return self::resolveParentDefaults($record)['colors'][$key] ?? null;
    }

    /** public: dipanggil sebagai callable array [self::class, 'parentSectionVisible'] dari ThemeOptionsSchema (kelas lain). */
    public static function parentSectionVisible(Forms\Get $get, string $key, ?Theme $record): bool
    {
        return self::resolveParentDefaults($record)['sections'][$key]['visible'] ?? true;
    }

    /**
     * 4 Toggle biner (bukan tri-state Select) yang kalau tidak diberi
     * hydration eksplisit akan jatuh ke `false` begitu saja saat state
     * tersimpan masih null -- lalu `false` itu ikut tersimpan sebagai
     * override nyata pada save berikutnya (ini bug yang sudah didiagnosis
     * di child theme lama sebelum resource ini ada). Pola sama persis
     * dengan ThemeOptionsSchema::sectionVisibilityFields yang sudah benar.
     */
    protected static function parentToggleField(string $path, string $label, bool $fallbackDefault = false): Forms\Components\Toggle
    {
        return Forms\Components\Toggle::make("default_options.$path")
            ->label($label)
            ->afterStateHydrated(function (Forms\Components\Toggle $component, $state, ?Theme $record) use ($path, $fallbackDefault) {
                if ($state === null) {
                    $parentValue = data_get(self::resolveParentDefaults($record), $path, $fallbackDefault);
                    $component->state((bool) $parentValue);
                }
            });
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Info')->schema([
                Forms\Components\Placeholder::make('invitation_info')
                    ->label('Undangan')
                    ->content(fn (?Theme $record) => $record?->invitation ? "{$record->invitation->slug} ({$record->invitation->groom_name} & {$record->invitation->bride_name})" : '—'),
                Forms\Components\Placeholder::make('base_theme_info')
                    ->label('Tema dasar')
                    ->content(fn (?Theme $record) => $record?->parent?->name ?? '—'),
            ])->columns(2),

            Forms\Components\Section::make('Warna Tema')
                ->description('Ganti warna HANYA untuk undangan ini. Kosongkan lagi (klik ikon hapus di pojok color picker) untuk kembali memakai warna bawaan tema dasar, atau pakai tombol "Reset ke Default Theme" di atas untuk reset semua sekaligus.')
                ->schema(ThemeOptionsSchema::colorFields('default_options', [self::class, 'parentColor']))
                ->columns(3),

            Forms\Components\Section::make('Section Aktif')
                ->description('Nyala/mati tiap section untuk undangan ini. Belum pernah diubah = ikut default tema dasar. Section Hero selalu tampil.')
                ->schema(ThemeOptionsSchema::sectionVisibilityFields('default_options', [self::class, 'parentSectionVisible']))
                ->columns(3),

            /* =========================================================
               1) SECTION HERO (SAMPUL)
               ========================================================= */
            Forms\Components\Section::make('1 — Section HERO (Sampul)')
                ->description('Layar pertama yang dilihat tamu. Background, posisi konten, kartu, countdown hero, dresscode, dan tipografi hero diatur di sini.')
                ->schema(array_merge([
                    Forms\Components\Select::make('default_options.hero.style')
                        ->label('Model tampilan hero')
                        ->live()
                        ->options(ThemeOptionsSchema::HERO_STYLES)
                        ->placeholder('Classic (default)')
                        ->helperText('Model Framed/Split/Custom memakai "Foto utama (desktop)" di bawah sebagai foto berbingkai. Hanya berlaku untuk tema yang mendukung banyak layout hero (saat ini: Mildness) — tema lain (mis. Senja) mengabaikan pilihan ini dan selalu pakai layout hero bawaannya sendiri.')
                        ->columnSpanFull(),

                    Forms\Components\Fieldset::make('Atur bebas tiap elemen (mode Custom)')
                        ->visible(fn (Forms\Get $get) => $get('default_options.hero.style') === 'custom')
                        ->columnSpanFull()
                        ->schema(self::heroElementFields()),

                    Forms\Components\Select::make('default_options.hero.position')
                        ->label('Posisi konten hero')
                        ->options(ThemeOptionsSchema::HERO_POSITIONS)
                        ->placeholder('Split (rekomendasi)')
                        ->helperText('Berlaku penuh saat hero TANPA kartu (full-foto). Saat hero berkartu, konten mengikuti kartu.')
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('default_options.background.photo')
                        ->label('Foto utama (desktop)')
                        ->image()->disk('public')->directory('covers')
                        ->helperText('Lanskap 16:9, saran 1920×1080 px, ≤500KB.'),
                    Forms\Components\FileUpload::make('default_options.background.photo_mobile')
                        ->label('Foto versi HP (opsional)')
                        ->image()->disk('public')->directory('covers')
                        ->helperText('Potret 9:16, saran 1080×1920 px.'),
                    Forms\Components\Select::make('default_options.background.ornament_asset')
                        ->label('Ornamen transparan (dari Pustaka)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament') + ThemeAsset::optionsFor('divider'))
                        ->searchable()->placeholder('— pilih dari pustaka —'),
                    Forms\Components\FileUpload::make('default_options.background.ornament_upload')
                        ->label('Ornamen transparan (upload)')
                        ->image()->disk('public')->directory('ornaments')
                        ->helperText('Kalau diisi, mengalahkan pilihan pustaka.'),

                    Forms\Components\FileUpload::make('default_options.hero.slideshow')
                        ->label('Slideshow background (opsional, maks. 3 foto)')
                        ->image()->disk('public')->directory('covers')
                        ->multiple()->maxFiles(3)->reorderable()
                        ->helperText('REKOMENDASI: cukup 3 foto — 1 utama + 2 tambahan. Jika diisi, slideshow MENGGANTIKAN foto utama di atas.')
                        ->columnSpan(2),
                    Forms\Components\Select::make('default_options.hero.effect')
                        ->label('Efek pergantian')
                        ->options(ThemeOptionsSchema::HERO_EFFECTS)
                        ->placeholder('Fade (rekomendasi)'),
                    Forms\Components\TextInput::make('default_options.hero.interval')
                        ->label('Jeda per foto (detik)')
                        ->numeric()->minValue(4)->maxValue(12)->placeholder('6'),

                    Forms\Components\Select::make('default_options.layout.hero_card')
                        ->label('Kartu hero')
                        ->options(ThemeOptionsSchema::HERO_CARD_MODES)
                        ->placeholder('Ikut pengaturan konten'),
                    Forms\Components\Select::make('default_options.hero.card_style')
                        ->label('Gaya kartu hero')
                        ->options(ThemeOptionsSchema::CARD_STYLES)
                        ->placeholder('Ikut gaya global')
                        ->helperText('Glass di atas foto = paling cantik untuk hero.'),
                    Forms\Components\TextInput::make('default_options.hero.name_font')
                        ->label('Font nama pasangan di hero')
                        ->placeholder('Ikut font kaligrafi global')
                        ->datalist(['Great Vibes', 'Dancing Script', 'Parisienne', 'Allura', 'Sacramento', 'Alex Brush']),

                    Forms\Components\Toggle::make('default_options.sections.countdown_hero.visible')
                        ->label('Countdown di hero')->default(true)
                        ->helperText('Terpisah dari countdown isi. Datanya = acara pertama undangan ini.'),

                    self::parentToggleField('hero.dresscode_enabled', 'Tampilkan dresscode')->live(),
                    Forms\Components\TextInput::make('default_options.hero.dresscode')
                        ->label('Teks dresscode')
                        ->placeholder('mis. Batik / Nuansa Earth Tone')
                        ->visible(fn (Forms\Get $get) => (bool) $get('default_options.hero.dresscode_enabled'))
                        ->helperText('Posisi tampil: di bawah countdown hero.'),

                    // Contoh field yang disesuaikan STRUKTUR tema tertentu (bukan
                    // generik sama rata semua tema) -- Senja punya panel kiri diam
                    // (split hero) yang backgroundnya selama ini hardcode di CSS,
                    // sekarang bisa ditimpa admin. Cuma muncul untuk tema turunan Senja.
                    Forms\Components\ColorPicker::make('default_options.hero.senja_left_bg')
                        ->label('Warna latar panel kiri (khusus tema Senja)')
                        ->helperText('Menimpa gradient coklat bawaan panel kiri diam. Kosongkan untuk kembali ke gradient asli.')
                        ->visible(fn (?Theme $record) => self::isSenjaBased($record))
                        ->columnSpanFull(),
                ], self::sectionDisplayFields('hero')))->columns(2),

            /* =========================================================
               2) SECTION MEMPELAI (tampilan)
               ========================================================= */
            Forms\Components\Section::make('2 — Section MEMPELAI (tampilan)')
                ->description('Nama, orang tua, dan teks pembuka diisi di form Undangan — di sini cuma gaya tampilan + foto.')
                ->collapsed()
                ->schema(array_merge([
                    Forms\Components\Select::make('default_options.couple.style')
                        ->label('Desain tampilan')
                        ->options(ThemeOptionsSchema::COUPLE_STYLES)
                        ->placeholder('Classic (default)'),
                    self::parentToggleField('couple.show_photos', 'Tampilkan foto mempelai')->live()
                        ->helperText('Desain Cards/Circle/Arch paling maksimal dengan foto; tanpa foto otomatis jatuh ke Classic.'),
                    Forms\Components\FileUpload::make('default_options.couple.groom_photo')
                        ->label('Foto Mempelai Pria')
                        ->image()->disk('public')->directory('couple')
                        ->visible(fn (Forms\Get $get) => (bool) $get('default_options.couple.show_photos'))
                        ->helperText('Rasio 3:4 (potret), ≤300KB.'),
                    Forms\Components\FileUpload::make('default_options.couple.bride_photo')
                        ->label('Foto Mempelai Wanita')
                        ->image()->disk('public')->directory('couple')
                        ->visible(fn (Forms\Get $get) => (bool) $get('default_options.couple.show_photos')),
                ], self::sectionDisplayFields('couple')))->columns(2),

            /* =========================================================
               3) SECTION ACARA (tampilan)
               ========================================================= */
            Forms\Components\Section::make('3 — Section ACARA (tampilan)')
                ->description('Isi acara (Akad, Resepsi, tanggal, lokasi) diisi di form Undangan.')
                ->collapsed()
                ->schema(array_merge([
                    Forms\Components\Select::make('default_options.events.style')
                        ->label('Model tampilan')
                        ->options(ThemeOptionsSchema::EVENTS_STYLES)
                        ->placeholder('Card (default)'),
                ], self::sectionDisplayFields('events')))->columns(2),

            /* =========================================================
               4) SECTION COUNTDOWN (tampilan)
               ========================================================= */
            Forms\Components\Section::make('4 — Section COUNTDOWN (tampilan)')
                ->description('Hitung mundur di dalam isi undangan. Sumber tanggal: acara pertama undangan ini.')
                ->collapsed()
                ->schema(array_merge([
                    Forms\Components\Select::make('default_options.countdown.style')
                        ->label('Gaya angka (berlaku juga di hero)')
                        ->options(ThemeOptionsSchema::COUNTDOWN_STYLES)
                        ->placeholder('Bulat (default)'),
                    Forms\Components\Select::make('default_options.countdown.layout')
                        ->label('Isi section')->live()
                        ->options(ThemeOptionsSchema::COUNTDOWN_LAYOUTS)
                        ->placeholder('Sederhana'),
                    Forms\Components\FileUpload::make('default_options.countdown.photo')
                        ->label('Foto latar countdown')
                        ->image()->disk('public')->directory('section-bg')
                        ->visible(fn (Forms\Get $get) => $get('default_options.countdown.layout') === 'photo'),
                    Forms\Components\Textarea::make('default_options.countdown.quote')
                        ->label('Teks kutipan')->rows(2)
                        ->visible(fn (Forms\Get $get) => $get('default_options.countdown.layout') === 'quote'),
                ], self::sectionDisplayFields('countdown')))->columns(2),

            /* =========================================================
               5) SECTION KISAH KAMI (tampilan)
               ========================================================= */
            Forms\Components\Section::make('5 — Section KISAH KAMI (tampilan)')
                ->description('Judul, tanggal, cerita, dan foto per-kisah diisi di form Undangan (tab "Kisah Cinta").')
                ->collapsed()
                ->schema(array_merge([
                    self::parentToggleField('love_story.show_photos', 'Tampilkan foto kisah')
                        ->helperText('Foto di-upload per kisah di form Undangan. Toggle ini menyalakan/mematikan SEMUA foto kisah sekaligus.'),
                    Forms\Components\Select::make('default_options.love_story.style')
                        ->label('Model tampilan')
                        ->options(ThemeOptionsSchema::LOVE_STORY_STYLES)
                        ->placeholder('Stacked (default)'),
                ], self::sectionDisplayFields('love_story')))->columns(2),

            /* =========================================================
               6) SECTION GALERI (tampilan)
               ========================================================= */
            Forms\Components\Section::make('6 — Section GALERI (tampilan)')
                ->description('Foto galeri diisi di form Undangan (tab "Gallery Photos").')
                ->collapsed()
                ->schema(array_merge([
                    Forms\Components\Select::make('default_options.gallery.style')
                        ->label('Model galeri')
                        ->options(ThemeOptionsSchema::GALLERY_STYLES)
                        ->placeholder('Carousel (default)'),
                ], self::sectionDisplayFields('gallery')))->columns(2),

            /* =========================================================
               7) SECTION VIDEO (tampilan)
               ========================================================= */
            Forms\Components\Section::make('7 — Section VIDEO (tampilan)')
                ->description('URL video, eyebrow, dan kalimat pengantar diisi di form Undangan.')
                ->collapsed()
                ->schema(self::sectionDisplayFields('video'))->columns(2),

            /* =========================================================
               8-10) RSVP / UCAPAN / KADO (tampilan)
               ========================================================= */
            Forms\Components\Section::make('8 — Section RSVP (tampilan)')->collapsed()
                ->schema(self::sectionDisplayFields('rsvp'))->columns(2),
            Forms\Components\Section::make('9 — Section UCAPAN & DOA (tampilan)')->collapsed()
                ->schema(self::sectionDisplayFields('guestbook'))->columns(2),
            Forms\Components\Section::make('10 — Section KADO (tampilan)')->collapsed()
                ->schema(self::sectionDisplayFields('gift'))->columns(2),
            Forms\Components\Section::make('11 — Section TURUT MENGUNDANG (tampilan)')->collapsed()
                ->schema(self::sectionDisplayFields('co_host'))->columns(2),

            /* =========================================================
               PENGATURAN GLOBAL
               ========================================================= */
            Forms\Components\Section::make('Global — Tata Letak & Kartu Default')
                ->description('Nilai DEFAULT untuk semua section; tiap section bisa menimpanya lewat pengaturannya masing-masing di atas.')
                ->collapsed()
                ->schema(array_merge([
                    self::parentToggleField('layout.card', 'Kartu untuk section konten (default)', true),
                    Forms\Components\Select::make('default_options.layout.section_height')
                        ->label('Tinggi section')
                        ->options(ThemeOptionsSchema::SECTION_HEIGHTS)
                        ->placeholder('Satu layar penuh (default)'),
                ], ThemeOptionsSchema::cardFields('default_options', ThemeOptionsSchema::CARD_STYLES)))->columns(3),

            Forms\Components\Section::make('Global — Floral 4 Sudut Halaman')
                ->collapsed()
                ->schema([
                    Forms\Components\Select::make('default_options.florals.tl')
                        ->label('Floral kiri-atas')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('default_options.florals.tr')
                        ->label('Floral kanan-atas')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('default_options.florals.bl')
                        ->label('Floral kiri-bawah')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('default_options.florals.br')
                        ->label('Floral kanan-bawah')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                ])->columns(2),

            Forms\Components\Section::make('Global — Animasi Scroll (GSAP)')
                ->collapsed()
                ->schema([
                    Forms\Components\Select::make('default_options.animation.preset')
                        ->label('Preset animasi section')
                        ->options(ThemeOptionsSchema::ANIMATION_PRESETS)
                        ->placeholder('Muncul dari bawah (default)'),
                ]),

            Forms\Components\Section::make('Global — Tipografi (Judul & Isi)')
                ->description('Kosongkan = bawaan tema dasar. Nama font Google Fonts bebas diketik — dimuat otomatis.')
                ->collapsed()
                ->schema(ThemeOptionsSchema::typographyFields('default_options'))
                ->columns(3),

            Forms\Components\Section::make('Global — Label Teks (Override)')
                ->description('Kosongkan = pakai label bawaan tema dasar (atau bawaan produk kalau tema dasar juga belum mengisi).')
                ->collapsed()
                ->schema(ThemeOptionsSchema::labelFields('default_options'))
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invitation.slug')->label('Undangan')->searchable(),
                Tables\Columns\TextColumn::make('parent.name')->label('Tema dasar')->badge(),
                Tables\Columns\TextColumn::make('updated_at')->label('Diubah')->dateTime('d M Y H:i')->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => InvitationLookResource\Pages\ListInvitationLooks::route('/'),
            'edit'  => InvitationLookResource\Pages\EditInvitationLook::route('/{record}/edit'),
        ];
    }
}
