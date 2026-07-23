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
            $fields[] = Forms\Components\Grid::make(['default' => 1, 'sm' => 2])->schema([
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

    /**
     * @param  bool  $withCardFields  Hero TIDAK pakai ini (false) -- hero punya field kartu SENDIRI
     *   ("Kartu hero" / "Gaya kartu hero" di heroTabFields()) yang benar-benar dibaca saat render
     *   (lihat Layout.vue: heroUseCard/heroCardStyle). Field "Kartu section ini"/"Gaya kartu section
     *   ini"/"Tinggi section ini" di sini TIDAK PERNAH dibaca untuk hero (hero render lewat blok
     *   <section> terpisah, bukan lewat SectionRenderer) -- dulu tampil ganda & salah satunya mati.
     */
    protected static function sectionDisplayFields(string $key, bool $withCardFields = true): array
    {
        return [
            ...($withCardFields ? [
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
            ] : []),
            Forms\Components\ColorPicker::make("default_options.sections.$key.card_bg")
                ->label('Warna kartu section ini')
                ->helperText('Menimpa "Warna Kartu" global HANYA untuk kartu section ini. Kosongkan = ikut warna kartu global. Berguna terutama untuk gaya kartu Glass/Outline/Gradient yang tembus pandang -- kalau section itu ada di atas background foto/video, isi warna di sini supaya tetap dominan (tidak tercampur warna foto).'),
            Forms\Components\FileUpload::make("default_options.section_bg.$key")
                ->label('Background section (mode tanpa kartu)')
                ->image()->disk('public')->directory('undangan/section-bg')
                ->live()
                ->helperText('Tampil sebagai latar section PENUH -- hanya kalau section ini TANPA kartu.'),
            Forms\Components\FileUpload::make("default_options.sections.$key.card_bg_photo")
                ->label('Background di dalam kartu')
                ->image()->disk('public')->directory('undangan/section-bg')
                ->helperText('Foto TERPISAH, khusus tampil DI DALAM kartu -- hanya kalau section ini BERKARTU. Beda upload dari "Background section (mode tanpa kartu)" di atas, supaya bisa diisi independen sesuai mode kartu yang aktif.'),
            Forms\Components\ColorPicker::make("default_options.sections.$key.bg_color")
                ->label('Warna background section ini')
                ->helperText('Tampil di belakang section ini, berkartu maupun tanpa kartu.'),
            // Font/ukuran/warna judul & isi generik (per-section) SENGAJA DIHAPUS
            // dari sini (2026-07-22) -- tumpang tindih dengan "Ukuran & Warna
            // tiap elemen teks section" (lihat elementStyleFields()) yang lebih
            // presisi (per-elemen, bukan pukul rata judul/isi) dan efeknya lebih
            // terlihat. Global > Tipografi tetap jadi fallback dasar.
        ];
    }

    /**
     * UKURAN & WARNA TIAP ELEMEN TEKS SECTION (v4) — pola generik yang sama
     * dengan heroElementStyleFields(), dipakai section SELAIN hero. $elements
     * = ['elKey' => 'Label tampil', ...], key 'title' KHUSUS menimpa judul
     * section (SectionWrapper), key lain dikonsumsi masing-masing *Style*.vue
     * lewat var(--el-{elKey}-color/size, ...) -- lihat useThemeOptions.js
     * sectionFontVars(). Data: default_options.sections.$key.elements.$elKey.
     */
    protected static function sectionElementStyleFields(string $key, array $elements): array
    {
        return ThemeOptionsSchema::sectionElementStyleFields('default_options', $key, $elements);
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

    /** public: dipanggil sebagai callable array [self::class, 'parentBool'] dari ThemeOptionsSchema::tristateToggle(). */
    public static function parentBool(Forms\Get $get, string $path, ?Theme $record): bool
    {
        return (bool) data_get(self::resolveParentDefaults($record), $path, false);
    }

    /** Sama seperti parentBool(), tapi fallback-nya true kalau parent juga tidak mengisi apa-apa (mis. layout.card). */
    protected static function parentToggleField(string $path, string $label, bool $fallbackDefault = false): Forms\Components\Select
    {
        return ThemeOptionsSchema::tristateToggle(
            "default_options.$path",
            $path,
            $label,
            fn (Forms\Get $get, string $key, ?Theme $record) => (bool) data_get(self::resolveParentDefaults($record), $key, $fallbackDefault),
        );
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
            ])->columns(['default' => 1, 'sm' => 2]),

            Forms\Components\Tabs::make('Pengaturan Tampilan')
                ->persistTabInQueryString()
                ->columnSpanFull()
                ->tabs([
                    Forms\Components\Tabs\Tab::make('Global')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->schema(self::globalTabFields()),
                    Forms\Components\Tabs\Tab::make('Hero')
                        ->icon('heroicon-o-photo')
                        ->schema(self::heroTabFields()),
                    Forms\Components\Tabs\Tab::make('Mempelai')
                        ->icon('heroicon-o-user-group')
                        ->schema(self::coupleTabFields()),
                    Forms\Components\Tabs\Tab::make('Acara')
                        ->icon('heroicon-o-calendar-days')
                        ->schema(self::eventsTabFields()),
                    Forms\Components\Tabs\Tab::make('Countdown')
                        ->icon('heroicon-o-clock')
                        ->schema(self::countdownTabFields()),
                    Forms\Components\Tabs\Tab::make('Kisah Cinta')
                        ->icon('heroicon-o-heart')
                        ->schema(self::loveStoryTabFields()),
                    Forms\Components\Tabs\Tab::make('Galeri Foto')
                        ->icon('heroicon-o-photo')
                        ->schema(self::galleryTabFields()),
                    Forms\Components\Tabs\Tab::make('Video')
                        ->icon('heroicon-o-video-camera')
                        ->schema(self::videoTabFields()),
                    Forms\Components\Tabs\Tab::make('Konfirmasi Kehadiran')
                        ->icon('heroicon-o-check-circle')
                        ->schema(self::rsvpTabFields()),
                    Forms\Components\Tabs\Tab::make('Ucapan & Doa')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->schema(self::guestbookTabFields()),
                    Forms\Components\Tabs\Tab::make('Hadiah Digital')
                        ->icon('heroicon-o-gift')
                        ->schema(self::giftTabFields()),
                    Forms\Components\Tabs\Tab::make('Turut Mengundang')
                        ->icon('heroicon-o-users')
                        ->schema(self::coHostTabFields()),
                ]),
        ]);
    }

    /** Tab "Global": tata letak dasar, section aktif, floral, animasi, tipografi, label. */
    protected static function globalTabFields(): array
    {
        return [
            Forms\Components\Section::make('Global Tampilan')
                ->description('Pengaturan tata letak dasar undangan ini. Kosongkan field apa pun (klik ikon hapus di pojok, atau kosongkan Select) untuk kembali memakai bawaan tema dasar, atau pakai tombol "Reset ke Default Theme" di atas untuk reset semua sekaligus.')
                ->schema([
                    Forms\Components\Fieldset::make('Full Layout')
                        ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                        ->schema(array_merge([
                            Forms\Components\ColorPicker::make('default_options.background.color')
                                ->label('Warna background halaman')
                                ->helperText('Warna latar HALAMAN penuh (di belakang kartu/konten) — beda dari warna kartu di bawah.'),
                            self::parentToggleField('layout.card', 'Kartu untuk section konten (default)', true),
                            Forms\Components\Select::make('default_options.layout.section_height')
                                ->label('Tinggi section')
                                ->options(ThemeOptionsSchema::SECTION_HEIGHTS)
                                ->placeholder('Satu layar penuh (default)'),
                        ], ThemeOptionsSchema::cardFields('default_options', ThemeOptionsSchema::CARD_STYLES))),

                    Forms\Components\Fieldset::make('Background Foto/Video Halaman')
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema([
                            Forms\Components\Placeholder::make('hero_bg_note')
                                ->label('')
                                ->content('Latar tetap di belakang SELURUH halaman undangan (BUKAN foto berbingkai hero -- itu field terpisah di tab Hero). Pilih SATU sumber -- foto/slideshow ATAU video, tidak bisa dua-duanya sekaligus (mengganti sumber otomatis mengosongkan yang lain, supaya tidak ada data nyangkut yang bikin foto/video "hilang" tanpa sebab).')
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
                                ->label('Slideshow background (opsional, maks. 3 foto)')
                                ->image()->disk('public')->directory('undangan/covers')
                                ->multiple()->maxFiles(3)->reorderable()
                                ->live()
                                ->helperText('Khusus latar halaman. Untuk foto berbingkai di gaya hero Framed/Split/Custom/Arch/Polaroid, isi "Foto Berbingkai Hero" di tab Hero.')
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
                                ->disk('public')->directory('undangan/covers')
                                ->acceptedFileTypes(['video/mp4'])
                                ->maxSize(51200)
                                ->live()
                                ->helperText('Upload file .mp4 dari perangkat Anda (maks. 50MB).')
                                ->visible(fn (Forms\Get $get) => $get('hero_bg_source') === 'video')
                                ->columnSpanFull(),
                            Forms\Components\Select::make('default_options.hero.video_effect')
                                ->label('Efek video')
                                ->options(ThemeOptionsSchema::VIDEO_EFFECTS)
                                ->placeholder('Tanpa efek (default)')
                                ->visible(fn (Forms\Get $get) => $get('hero_bg_source') === 'video'),
                        ]),

                    Forms\Components\Fieldset::make('Split Layout (khusus tema Senja) — Panel Kiri (diam)')
                        ->visible(fn (?Theme $record) => self::isSenjaBased($record))
                        ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                        ->schema([
                            Forms\Components\ColorPicker::make('default_options.hero.senja_left_bg')
                                ->label('Warna latar (fallback)')
                                ->helperText('Dipakai kalau foto & video di bawah kosong. Kosongkan semua untuk kembali ke gradient asli.'),
                            Forms\Components\FileUpload::make('default_options.hero.senja_left_photo')
                                ->label('Foto latar panel kiri')
                                ->image()->disk('public')->directory('undangan/senja-panels')
                                ->live()
                                ->helperText('Menang atas warna latar. Kosongkan lagi untuk kembali ke warna/gradient.'),
                            Forms\Components\FileUpload::make('default_options.hero.senja_left_video')
                                ->label('Video latar panel kiri (upload)')
                                ->disk('public')->directory('undangan/senja-panels')
                                ->acceptedFileTypes(['video/mp4'])
                                ->maxSize(51200)
                                ->helperText('Upload file .mp4 (maks. 50MB), BUKAN link. Menang atas foto & warna kalau diisi.')
                                ->visible(fn (Forms\Get $get) => empty($get('default_options.hero.senja_left_photo'))),
                            Forms\Components\ColorPicker::make('default_options.hero.senja_left_text')
                                ->label('Warna teks panel kiri')
                                ->helperText('Berlaku untuk nama, tanggal, quote, dan label "Kepada" di panel kiri sekaligus.')
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Fieldset::make('Split Layout (khusus tema Senja) — Panel Kanan (scroll)')
                        ->visible(fn (?Theme $record) => self::isSenjaBased($record))
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema([
                            Forms\Components\ColorPicker::make('default_options.hero.senja_right_bg')
                                ->label('Warna latar (fallback)')
                                ->helperText('Dipakai kalau foto di bawah kosong.'),
                            Forms\Components\FileUpload::make('default_options.hero.senja_right_photo')
                                ->label('Foto latar panel kanan')
                                ->image()->disk('public')->directory('undangan/senja-panels')
                                ->helperText('Menang atas warna latar. Tampil di belakang isi undangan yang bisa di-scroll.'),
                        ]),
                ]),

            Forms\Components\Section::make('Section Aktif')
                ->description('Nyala/mati tiap section untuk undangan ini. Belum pernah diubah = ikut default tema dasar. Section Hero selalu tampil.')
                ->schema(ThemeOptionsSchema::sectionVisibilityFields('default_options', [self::class, 'parentSectionVisible']))
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 3]),

            Forms\Components\Section::make('Floral 4 Sudut Halaman')
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
                ])->columns(['default' => 1, 'sm' => 2]),

            Forms\Components\Section::make('Animasi Scroll (GSAP)')
                ->collapsed()
                ->schema([
                    Forms\Components\Select::make('default_options.animation.preset')
                        ->label('Preset animasi section')
                        ->options(ThemeOptionsSchema::ANIMATION_PRESETS)
                        ->placeholder('Muncul dari bawah (default)'),
                ]),

            Forms\Components\Section::make('Font (Judul, Isi, Kaligrafi)')
                ->description('Kosongkan = bawaan tema dasar. Nama font Google Fonts bebas diketik — dimuat otomatis. Ukuran & warna diatur per-section lewat "Ukuran & Warna tiap elemen teks section" di tiap tab.')
                ->collapsed()
                ->schema(ThemeOptionsSchema::typographyFields('default_options'))
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 3]),

            Forms\Components\Section::make('Label Teks (Override)')
                ->description('Kosongkan = pakai label bawaan tema dasar (atau bawaan produk kalau tema dasar juga belum mengisi).')
                ->collapsed()
                ->schema(ThemeOptionsSchema::labelFields('default_options'))
                ->columns(['default' => 1, 'sm' => 2]),
        ];
    }

    /** Tab "Hero": background, posisi, kartu, dresscode, tipografi hero. */
    protected static function heroTabFields(): array
    {
        return [
            Forms\Components\Section::make('Section HERO (Sampul)')
                ->description('Layar pertama yang dilihat tamu. Nyala/mati "Countdown di Hero" ada di tab Global > checklist "Section Aktif" (datanya = acara pertama undangan ini).')
                ->schema([
                    Forms\Components\Select::make('default_options.hero.style')
                        ->label('Model tampilan hero')
                        ->live()
                        ->options(ThemeOptionsSchema::HERO_STYLES)
                        ->placeholder('Classic (default)')
                        ->helperText('Model Framed/Split/Custom/Arch/Polaroid memakai "Foto Berbingkai Hero" di bawah -- WAJIB diisi di situ, kalau tidak foto tidak akan tampil (field ini TERPISAH dari background halaman di tab Global). Hanya berlaku untuk tema yang mendukung banyak layout hero (saat ini: Mildness) — tema lain (mis. Senja) mengabaikan pilihan ini dan selalu pakai layout hero bawaannya sendiri.')
                        ->columnSpanFull(),
                    Forms\Components\Fieldset::make('Foto Berbingkai Hero')
                        ->visible(fn (Forms\Get $get) => in_array($get('default_options.hero.style'), ['framed', 'split', 'custom', 'arch', 'polaroid'], true))
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema([
                            Forms\Components\FileUpload::make('default_options.hero.framed_photo')
                                ->label('Foto berbingkai')
                                ->image()->disk('public')->directory('undangan/covers')
                                ->live()
                                ->helperText('Khusus untuk gaya hero Framed/Split/Custom/Arch/Polaroid -- TERPISAH dari background halaman (tab Global). Kosong = tidak ada foto berbingkai yang tampil.')
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Fieldset::make('Atur bebas tiap elemen (mode Custom)')
                        ->visible(fn (Forms\Get $get) => $get('default_options.hero.style') === 'custom')
                        ->columnSpanFull()
                        ->schema(self::heroElementFields()),

                    Forms\Components\Fieldset::make('Dresscode')
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema([
                            self::parentToggleField('hero.dresscode_enabled', 'Tampilkan dresscode')->live(),
                            Forms\Components\TextInput::make('default_options.hero.dresscode')
                                ->label('Teks dresscode')
                                ->placeholder('mis. Batik / Nuansa Earth Tone')
                                ->visible(fn (Forms\Get $get) => (bool) $get('default_options.hero.dresscode_enabled'))
                                ->helperText('Posisi tampil: di bawah countdown hero.'),
                        ]),

                    Forms\Components\Fieldset::make('Tipografi, Ukuran & Warna tiap elemen teks hero')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(ThemeOptionsSchema::heroElementStyleFields()),

                    Forms\Components\Fieldset::make('Tampilan section (kartu, background)')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema([
                            Forms\Components\Select::make('default_options.layout.hero_card')
                                ->label('Kartu hero')
                                ->options(ThemeOptionsSchema::HERO_CARD_MODES)
                                ->placeholder('Ikut pengaturan konten'),
                            Forms\Components\Select::make('default_options.hero.card_style')
                                ->label('Gaya kartu hero')
                                ->options(ThemeOptionsSchema::CARD_STYLES)
                                ->placeholder('Ikut gaya global')
                                ->helperText('Glass di atas foto = paling cantik untuk hero.'),
                            ...self::sectionDisplayFields('hero', withCardFields: false),
                        ]),
                ])->columns(['default' => 1, 'sm' => 2]),
        ];
    }

    /** Tab "Mempelai". */
    protected static function coupleTabFields(): array
    {
        return [
            Forms\Components\Section::make('Section MEMPELAI (tampilan)')
                ->description('Nama, orang tua, dan teks pembuka diisi di form Undangan — di sini cuma gaya tampilan + foto.')
                ->schema([
                    Forms\Components\Fieldset::make('Desain & Foto')
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema([
                            Forms\Components\Select::make('default_options.couple.style')
                                ->label('Desain tampilan')
                                ->options(ThemeOptionsSchema::COUPLE_STYLES)
                                ->placeholder('Classic (default)'),
                            self::parentToggleField('couple.show_photos', 'Tampilkan foto mempelai')->live()
                                ->helperText('Desain Cards/Circle/Arch paling maksimal dengan foto; tanpa foto otomatis jatuh ke Classic.'),
                            Forms\Components\FileUpload::make('default_options.couple.groom_photo')
                                ->label('Foto Mempelai Pria')
                                ->image()->disk('public')->directory('undangan/couple')
                                ->visible(fn (Forms\Get $get) => (bool) $get('default_options.couple.show_photos'))
                                ->helperText('Rasio 3:4 (potret), ≤300KB.'),
                            Forms\Components\FileUpload::make('default_options.couple.bride_photo')
                                ->label('Foto Mempelai Wanita')
                                ->image()->disk('public')->directory('undangan/couple')
                                ->visible(fn (Forms\Get $get) => (bool) $get('default_options.couple.show_photos')),
                        ]),

                    Forms\Components\Fieldset::make('Kalimat pembuka (basmalah)')
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema([
                            self::parentToggleField('couple.show_eyebrow', 'Tampilkan baris ini', true)
                                ->live()
                                ->helperText('Matikan kalau tidak relevan dengan agama/adat Anda (mis. bukan Muslim) -- section tetap tampil normal tanpa baris ini.'),
                            Forms\Components\TextInput::make('default_options.couple.eyebrow_text')
                                ->label('Teks')
                                ->placeholder('Bismillahirrahmanirrahim')
                                ->visible(fn (Forms\Get $get) => (bool) ($get('default_options.couple.show_eyebrow') ?? true)),
                        ]),

                    Forms\Components\Fieldset::make('Ukuran & Warna tiap elemen teks section')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionElementStyleFields('couple', [
                            'eyebrow' => 'Kalimat pembuka basmalah',
                            'opening' => 'Kalimat pengantar',
                            'names'   => 'Nama mempelai',
                            'parents' => 'Nama orang tua',
                        ])),

                    Forms\Components\Fieldset::make('Tampilan section (background, kartu)')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionDisplayFields('couple')),
                ])->columns(['default' => 1, 'sm' => 2]),
        ];
    }

    /** Tab "Acara". */
    protected static function eventsTabFields(): array
    {
        return [
            Forms\Components\Section::make('Section ACARA (tampilan)')
                ->description('Isi acara (Akad, Resepsi, tanggal, lokasi) diisi di form Undangan.')
                ->schema([
                    Forms\Components\Fieldset::make('Gaya & Peta')
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema([
                            Forms\Components\Select::make('default_options.events.style')
                                ->label('Model tampilan')
                                ->options(ThemeOptionsSchema::EVENTS_STYLES)
                                ->placeholder('Card (default)'),
                            self::parentToggleField('events.show_maps', 'Tampilkan peta (embed Google Maps)', true)
                                ->helperText('Butuh fitur "Peta Lokasi" aktif di paket langganan. Kalau dimatikan (atau paket tidak punya fitur ini), tamu tetap dapat tombol "Lihat lokasi" yang langsung membuka aplikasi peta -- link Google Maps diisi di form Undangan per acara.'),
                        ]),

                    Forms\Components\Fieldset::make('Ukuran & Warna tiap elemen teks section')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionElementStyleFields('events', [
                            'title'  => 'Judul section ("Rangkaian Acara")',
                            'name'   => 'Nama acara (mis. "Akad Nikah")',
                            'date'   => 'Tanggal & jam',
                            'venue'  => 'Nama & alamat lokasi',
                        ])),

                    Forms\Components\Fieldset::make('Tampilan section (background, kartu)')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionDisplayFields('events')),
                ])->columns(['default' => 1, 'sm' => 2]),
        ];
    }

    /** Tab "Countdown". */
    protected static function countdownTabFields(): array
    {
        return [
            Forms\Components\Section::make('Section COUNTDOWN (tampilan)')
                ->description('Hitung mundur di dalam isi undangan. Sumber tanggal: acara pertama undangan ini.')
                ->schema([
                    Forms\Components\Fieldset::make('Gaya Tampilan')
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema([
                            Forms\Components\Select::make('default_options.countdown.style')
                                ->label('Gaya angka (berlaku juga di hero)')
                                ->options(ThemeOptionsSchema::COUNTDOWN_STYLES)
                                ->placeholder('Bulat (default)'),
                            Forms\Components\Select::make('default_options.countdown.layout')
                                ->label('Isi section')->live()
                                ->options(ThemeOptionsSchema::COUNTDOWN_LAYOUTS)
                                ->placeholder('Sederhana'),
                        ]),

                    Forms\Components\Fieldset::make('Konten (khusus gaya "Foto"/"Kutipan")')
                        ->visible(fn (Forms\Get $get) => in_array($get('default_options.countdown.layout'), ['photo', 'quote'], true))
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema([
                            Forms\Components\FileUpload::make('default_options.countdown.photo')
                                ->label('Foto latar countdown')
                                ->image()->disk('public')->directory('undangan/section-bg')
                                ->visible(fn (Forms\Get $get) => $get('default_options.countdown.layout') === 'photo'),
                            Forms\Components\Textarea::make('default_options.countdown.quote')
                                ->label('Teks kutipan')->rows(2)
                                ->visible(fn (Forms\Get $get) => $get('default_options.countdown.layout') === 'quote'),
                        ]),

                    Forms\Components\Fieldset::make('Ukuran & Warna tiap elemen teks section')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionElementStyleFields('countdown', [
                            'eyebrow' => 'Label "Menuju hari bahagia"',
                            'date'    => 'Tanggal (khusus isi "Tanggal")',
                            'quote'   => 'Kutipan (khusus isi "Kutipan")',
                            'label'   => 'Label Hari/Jam/Menit/Detik',
                        ])),

                    Forms\Components\Fieldset::make('Tampilan section (background, kartu)')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionDisplayFields('countdown')),
                ])->columns(['default' => 1, 'sm' => 2]),
        ];
    }

    /** Tab "Kisah Cinta". */
    protected static function loveStoryTabFields(): array
    {
        return [
            Forms\Components\Section::make('Section KISAH KAMI (tampilan)')
                ->description('Judul, tanggal, cerita, dan foto per-kisah diisi di form Undangan (tab "Kisah Cinta").')
                ->schema([
                    self::parentToggleField('love_story.show_photos', 'Tampilkan foto kisah')
                        ->helperText('Foto di-upload per kisah di form Undangan. Toggle ini menyalakan/mematikan SEMUA foto kisah sekaligus.'),
                    Forms\Components\Select::make('default_options.love_story.style')
                        ->label('Model tampilan')
                        ->options(ThemeOptionsSchema::LOVE_STORY_STYLES)
                        ->placeholder('Stacked (default)'),

                    Forms\Components\Fieldset::make('Ukuran & Warna tiap elemen teks section')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionElementStyleFields('love_story', [
                            'title'    => 'Judul section ("Kisah Kami")',
                            'date'     => 'Tanggal tiap kisah',
                            'name'     => 'Judul tiap kisah',
                            'text'     => 'Isi cerita',
                        ])),

                    Forms\Components\Fieldset::make('Tampilan section (background, kartu)')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionDisplayFields('love_story')),
                ])->columns(['default' => 1, 'sm' => 2]),
        ];
    }

    /** Tab "Galeri Foto". */
    protected static function galleryTabFields(): array
    {
        return [
            Forms\Components\Section::make('Section GALERI (tampilan)')
                ->description('Foto galeri diisi di form Undangan (tab "Gallery Photos").')
                ->schema([
                    Forms\Components\Select::make('default_options.gallery.style')
                        ->label('Model galeri')
                        ->options(ThemeOptionsSchema::GALLERY_STYLES)
                        ->placeholder('Carousel (default)'),
                    Forms\Components\Textarea::make('default_options.gallery.caption')
                        ->label('Kalimat pengantar (opsional, di bawah judul)')
                        ->rows(2)
                        ->placeholder('mis. Sepenggal momen kebersamaan kami.')
                        ->columnSpanFull(),

                    Forms\Components\Fieldset::make('Ukuran & Warna tiap elemen teks section')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionElementStyleFields('gallery', [
                            'title'   => 'Judul section ("Galeri")',
                            'caption' => 'Kalimat pengantar',
                        ])),

                    Forms\Components\Fieldset::make('Tampilan section (background, kartu)')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionDisplayFields('gallery')),
                ])->columns(['default' => 1, 'sm' => 2]),
        ];
    }

    /** Tab "Video". */
    protected static function videoTabFields(): array
    {
        return [
            Forms\Components\Section::make('Section VIDEO (tampilan)')
                ->description('URL/upload video, eyebrow, dan kalimat pengantar diisi di form Undangan.')
                ->schema([
                    Forms\Components\Select::make('default_options.video.style')
                        ->label('Model tampilan')
                        ->options(ThemeOptionsSchema::VIDEO_STYLES)
                        ->placeholder('Classic (default)'),

                    Forms\Components\Fieldset::make('Ukuran & Warna tiap elemen teks section')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionElementStyleFields('video', [
                            'title'   => 'Judul section ("Video")',
                            'eyebrow' => 'Label kecil (mis. "Wedding Film")',
                            'caption' => 'Kalimat pengantar',
                            'credit'  => 'Credit (nama & tanggal di bawah video)',
                        ])),

                    Forms\Components\Fieldset::make('Tampilan section (background, kartu)')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionDisplayFields('video')),
                ])->columns(['default' => 1, 'sm' => 2]),
        ];
    }

    /** Tab "Konfirmasi Kehadiran" (RSVP). */
    protected static function rsvpTabFields(): array
    {
        return [
            Forms\Components\Section::make('Section RSVP (tampilan)')
                ->schema([
                    Forms\Components\Select::make('default_options.rsvp.style')
                        ->label('Model tampilan')
                        ->options(ThemeOptionsSchema::RSVP_STYLES)
                        ->placeholder('Card (default)'),

                    Forms\Components\Fieldset::make('Ukuran & Warna tiap elemen teks section')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionElementStyleFields('rsvp', [
                            'title'  => 'Judul section ("Konfirmasi Kehadiran")',
                            'button' => 'Tombol/chip kehadiran (independen dari warna aksen tema)',
                        ])),

                    Forms\Components\Fieldset::make('Tampilan section (background, kartu)')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionDisplayFields('rsvp')),
                ])->columns(['default' => 1, 'sm' => 2]),
        ];
    }

    /** Tab "Ucapan & Doa". */
    protected static function guestbookTabFields(): array
    {
        return [
            Forms\Components\Section::make('Section UCAPAN & DOA (tampilan)')
                ->schema([
                    Forms\Components\Select::make('default_options.guestbook.style')
                        ->label('Model tampilan')
                        ->options(ThemeOptionsSchema::GUESTBOOK_STYLES)
                        ->placeholder('List (default)'),

                    Forms\Components\Fieldset::make('Ukuran & Warna tiap elemen teks section')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionElementStyleFields('guestbook', [
                            'title' => 'Judul section ("Ucapan & Doa")',
                            'name'  => 'Nama penulis ucapan',
                            'text'  => 'Isi ucapan',
                        ])),

                    Forms\Components\Fieldset::make('Tampilan section (background, kartu)')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionDisplayFields('guestbook')),
                ])->columns(['default' => 1, 'sm' => 2]),
        ];
    }

    /** Tab "Hadiah Digital" (Gift). */
    protected static function giftTabFields(): array
    {
        return [
            Forms\Components\Section::make('Section KADO (tampilan)')
                ->schema([
                    Forms\Components\Select::make('default_options.gift.style')
                        ->label('Model tampilan')
                        ->options(ThemeOptionsSchema::GIFT_STYLES)
                        ->placeholder('Panel (default)'),

                    Forms\Components\Fieldset::make('Ukuran & Warna tiap elemen teks section')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionElementStyleFields('gift', [
                            'title' => 'Judul section ("Hadiah Digital")',
                            'name'  => 'Judul kado (mis. nama bank/QRIS)',
                            'value' => 'Nomor rekening / alamat',
                        ])),

                    Forms\Components\Fieldset::make('Tampilan section (background, kartu)')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionDisplayFields('gift')),
                ])->columns(['default' => 1, 'sm' => 2]),
        ];
    }

    /** Tab "Turut Mengundang". */
    protected static function coHostTabFields(): array
    {
        return [
            Forms\Components\Section::make('Section TURUT MENGUNDANG (tampilan)')
                ->description('Daftar kel. pria, kel. wanita, dan tamu spesial diisi di form Undangan.')
                ->schema([
                    Forms\Components\Select::make('default_options.co_host.style')
                        ->label('Model tampilan')
                        ->options(ThemeOptionsSchema::CO_HOST_STYLES)
                        ->placeholder('Classic (default)'),

                    Forms\Components\Fieldset::make('Ukuran & Warna tiap elemen teks section')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionElementStyleFields('co_host', [
                            'title' => 'Judul section ("Turut Mengundang")',
                            'label' => 'Label pihak (mis. "Pihak Mempelai Pria")',
                            'name'  => 'Nama tamu',
                        ])),

                    Forms\Components\Fieldset::make('Tampilan section (background, kartu)')
                        ->columnSpanFull()
                        ->columns(['default' => 1, 'sm' => 2])
                        ->schema(self::sectionDisplayFields('co_host')),
                ])->columns(['default' => 1, 'sm' => 2]),
        ];
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
