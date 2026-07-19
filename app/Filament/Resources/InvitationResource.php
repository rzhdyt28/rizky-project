<?php

namespace App\Filament\Resources;

use App\Modules\Invitation\Models\Invitation;
use App\Modules\Invitation\Models\ThemeAsset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * UNDANGAN — form v3: DISUSUN BERURUTAN PER-SECTION mengikuti urutan tampil
 * di undangan (Hero -> Mempelai -> Acara -> Countdown -> Kisah -> Galeri ->
 * Video -> RSVP -> Ucapan -> Kado -> Turut Mengundang), lalu pengaturan
 * GLOBAL di bawahnya. Tiap section membawa pengaturannya sendiri:
 * tampil/sembunyi, kartu (bisa DI-MIX per section: ikut global / pakai /
 * tanpa), gaya kartu per section, dan background per section — supaya
 * maintenance per section gampang.
 *
 * Duplikasi DIHAPUS: toggle RSVP & Ucapan kini HANYA kolom rsvp_enabled /
 * guestbook_enabled (di dalam section masing-masing) — toggle kembar di
 * "Tampilkan/Sembunyikan Section" lama dibuang.
 */
class InvitationResource extends Resource
{
    protected static ?string $model = Invitation::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Undangan';
    protected static ?string $navigationLabel = 'Undangan';
    protected static ?string $modelLabel = 'Undangan';

    /** Pilihan gaya kartu — satu sumber untuk global & per-section. */
    protected const CARD_STYLES = [
        'glass'    => 'Glass — kaca buram (blur)',
        'outline'  => 'Outline — garis tepi, tanpa isi',
        'flat'     => 'Flat — polos tanpa shadow',
        'gradient' => 'Gradient — gradasi lembut',
        'stamp'    => 'Stamp — tepi perangko',
        'default'  => 'Polos bawaan tema',
    ];

    /**
     * Pengaturan tampilan yang SAMA untuk tiap section: kartu (mix), gaya
     * kartu, background, dan tipografi judul/isi (font, ukuran, warna —
     * cermin dari section Global Tipografi, tapi per-section). Kunci
     * penyimpanan mengikuti skema lama (sections.{key}.* dan
     * section_bg.{key}) sehingga data yang sudah ada TETAP terbaca tanpa
     * migrasi.
     */
    protected static function sectionDisplayFields(string $key): array
    {
        return [
            Forms\Components\Select::make("theme_options.sections.$key.card")
                ->label('Kartu section ini')
                ->options(['on' => 'Selalu pakai kartu', 'off' => 'Tanpa kartu (full)'])
                ->placeholder('Ikut pengaturan global')
                ->helperText('Bisa di-mix: mis. galeri full tanpa kartu, section lain berkartu.'),
            Forms\Components\Select::make("theme_options.sections.$key.card_style")
                ->label('Gaya kartu section ini')
                ->options(self::CARD_STYLES)
                ->placeholder('Ikut gaya global'),
            Forms\Components\FileUpload::make("theme_options.section_bg.$key")
                ->label('Background section (mode tanpa kartu)')
                ->image()->disk('public')->directory('section-bg')
                ->helperText('Tampil hanya saat section ini TANPA kartu.'),
            Forms\Components\TextInput::make("theme_options.sections.$key.font_heading")
                ->label('Font JUDUL section ini')
                ->placeholder('Ikut font judul global')
                ->datalist(['Cormorant Garamond', 'Playfair Display', 'Cinzel', 'Lora', 'EB Garamond', 'Marcellus']),
            Forms\Components\TextInput::make("theme_options.sections.$key.title_size")
                ->label('Ukuran judul section ini (px)')
                ->numeric()->minValue(14)->maxValue(96)->placeholder('Ikut ukuran global'),
            Forms\Components\ColorPicker::make("theme_options.sections.$key.title_color")
                ->label('Warna judul section ini'),
            Forms\Components\TextInput::make("theme_options.sections.$key.font_body")
                ->label('Font ISI section ini')
                ->placeholder('Ikut font isi global')
                ->datalist(['Jost', 'Poppins', 'Lato', 'Open Sans', 'Nunito', 'Inter', 'Mulish']),
            Forms\Components\TextInput::make("theme_options.sections.$key.body_size")
                ->label('Ukuran isi section ini (px)')
                ->numeric()->minValue(10)->maxValue(28)->placeholder('Ikut ukuran global'),
            Forms\Components\ColorPicker::make("theme_options.sections.$key.body_color")
                ->label('Warna isi section ini'),
        ];
    }

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

            Forms\Components\Section::make('Data Mempelai')->schema([
                Forms\Components\TextInput::make('groom_name')->label('Mempelai Pria')->required(),
                Forms\Components\TextInput::make('bride_name')->label('Mempelai Wanita')->required(),
                Forms\Components\TextInput::make('groom_parents')->label('Orang Tua Pria'),
                Forms\Components\TextInput::make('bride_parents')->label('Orang Tua Wanita'),
                Forms\Components\Textarea::make('opening_text')->label('Teks Pembuka')->rows(3)->columnSpanFull(),
            ])->columns(2),

            /* =========================================================
               1) SECTION HERO (SAMPUL)
               ========================================================= */
            Forms\Components\Section::make('1 — Section HERO (Sampul)')
                ->description('Layar pertama yang dilihat tamu. Background, posisi konten, kartu, countdown hero, dan dresscode diatur di sini.')
                ->schema([
                    Forms\Components\Select::make('theme_options.hero.position')
                        ->label('Posisi konten hero')
                        ->options([
                            'split'  => '1. Split — eyebrow di atas, konten di bawah (rekomendasi, paling seimbang untuk foto potret)',
                            'center' => '2. Center — semua di tengah layar (formal & simetris)',
                            'bottom' => '3. Bottom — konten menumpuk di bawah (foto tetap dominan)',
                            'left'   => '4. Left — rata kiri bawah (editorial/majalah, cocok foto lanskap)',
                        ])->placeholder('Split (rekomendasi)')
                        ->helperText('Berlaku penuh saat hero TANPA kartu (full-foto). Saat hero berkartu, konten mengikuti kartu.')
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('theme_options.background.photo')
                        ->label('Foto utama (desktop)')
                        ->image()->disk('public')->directory('covers')
                        ->helperText('Lanskap 16:9, saran 1920×1080 px, ≤500KB.'),
                    Forms\Components\FileUpload::make('theme_options.background.photo_mobile')
                        ->label('Foto versi HP (opsional)')
                        ->image()->disk('public')->directory('covers')
                        ->helperText('Potret 9:16, saran 1080×1920 px.'),
                    Forms\Components\Select::make('theme_options.background.ornament_asset')
                        ->label('Ornamen transparan (dari Pustaka)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament') + ThemeAsset::optionsFor('divider'))
                        ->searchable()->placeholder('— pilih dari pustaka —'),
                    Forms\Components\FileUpload::make('theme_options.background.ornament_upload')
                        ->label('Ornamen transparan (upload)')
                        ->image()->disk('public')->directory('ornaments')
                        ->helperText('Kalau diisi, mengalahkan pilihan pustaka.'),

                    Forms\Components\FileUpload::make('theme_options.hero.slideshow')
                        ->label('Slideshow background (opsional, maks. 3 foto)')
                        ->image()->disk('public')->directory('covers')
                        ->multiple()->maxFiles(3)->reorderable()
                        ->helperText('REKOMENDASI: cukup 3 foto — 1 utama + 2 tambahan. Lebih dari itu memperberat loading di HP dan jarang sempat dilihat tamu sebelum menekan "Buka". Jika diisi, slideshow MENGGANTIKAN foto utama di atas.')
                        ->columnSpan(2),
                    Forms\Components\Select::make('theme_options.hero.effect')
                        ->label('Efek pergantian')
                        ->options([
                            'fade'     => 'Fade — pudar halus (rekomendasi)',
                            'kenburns' => 'Ken Burns — fade + zoom perlahan (ease)',
                        ])->placeholder('Fade (rekomendasi)'),
                    Forms\Components\TextInput::make('theme_options.hero.interval')
                        ->label('Jeda per foto (detik)')
                        ->numeric()->minValue(4)->maxValue(12)->placeholder('6'),

                    Forms\Components\Select::make('theme_options.layout.hero_card')
                        ->label('Kartu hero')
                        ->options([
                            'inherit' => 'Ikut pengaturan konten',
                            'card'    => 'Selalu pakai kartu',
                            'plain'   => 'Tanpa kartu (full-foto)',
                        ])->placeholder('Ikut pengaturan konten'),
                    Forms\Components\Select::make('theme_options.hero.card_style')
                        ->label('Gaya kartu hero')
                        ->options(self::CARD_STYLES)
                        ->placeholder('Ikut gaya global')
                        ->helperText('Glass di atas foto = paling cantik untuk hero.'),
                    Forms\Components\TextInput::make('theme_options.hero.name_font')
                        ->label('Font nama pasangan di hero')
                        ->placeholder('Ikut font kaligrafi global')
                        ->datalist(['Great Vibes', 'Dancing Script', 'Parisienne', 'Allura', 'Sacramento', 'Alex Brush']),

                    Forms\Components\Toggle::make('theme_options.sections.countdown_hero.visible')
                        ->label('Countdown di hero')->default(true)
                        ->helperText('Terpisah dari countdown isi. Datanya = acara pertama di tab "Events".'),

                    Forms\Components\Toggle::make('theme_options.hero.dresscode_enabled')
                        ->label('Tampilkan dresscode')->default(false)->live(),
                    Forms\Components\TextInput::make('theme_options.hero.dresscode')
                        ->label('Teks dresscode')
                        ->placeholder('mis. Batik / Nuansa Earth Tone')
                        ->visible(fn (Forms\Get $get) => (bool) $get('theme_options.hero.dresscode_enabled'))
                        ->helperText('Posisi tampil: di bawah countdown hero.'),
                ])->columns(2),

            /* =========================================================
               2) SECTION MEMPELAI
               ========================================================= */
            Forms\Components\Section::make('2 — Section MEMPELAI')
                ->description('Perkenalan pasangan. 4 desain tampilan + foto pria/wanita.')
                ->collapsed()
                ->schema(array_merge([
                    Forms\Components\Toggle::make('theme_options.sections.couple.visible')
                        ->label('Tampilkan section')->default(true),
                    Forms\Components\Select::make('theme_options.couple.style')
                        ->label('Desain tampilan')
                        ->options([
                            'classic' => '1. Classic — teks bertumpuk klasik (tanpa foto pun rapi)',
                            'cards'   => '2. Cards — dua kartu foto berdampingan, nama di bawah foto',
                            'circle'  => '3. Circle — foto lingkaran besar, "&" kaligrafi di tengah',
                            'arch'    => '4. Arch — foto bingkai lengkung ala gerbang, berdampingan',
                        ])->placeholder('Classic (default)'),
                    Forms\Components\Toggle::make('theme_options.couple.show_photos')
                        ->label('Tampilkan foto mempelai')->default(false)->live()
                        ->helperText('Desain Cards/Circle/Arch paling maksimal dengan foto; tanpa foto otomatis jatuh ke Classic.'),
                    Forms\Components\FileUpload::make('theme_options.couple.groom_photo')
                        ->label('Foto Mempelai Pria')
                        ->image()->disk('public')->directory('couple')
                        ->visible(fn (Forms\Get $get) => (bool) $get('theme_options.couple.show_photos'))
                        ->helperText('Rasio 3:4 (potret), ≤300KB.'),
                    Forms\Components\FileUpload::make('theme_options.couple.bride_photo')
                        ->label('Foto Mempelai Wanita')
                        ->image()->disk('public')->directory('couple')
                        ->visible(fn (Forms\Get $get) => (bool) $get('theme_options.couple.show_photos')),
                ], self::sectionDisplayFields('couple')))->columns(2),

            /* =========================================================
               3) SECTION ACARA
               ========================================================= */
            Forms\Components\Section::make('3 — Section ACARA')
                ->description('ISI acara (Akad, Resepsi, tanggal, lokasi) dikelola di tab "Events" di bawah halaman ini. Acara PERTAMA juga menjadi sumber tanggal countdown.')
                ->collapsed()
                ->schema(array_merge([
                    Forms\Components\Toggle::make('theme_options.sections.events.visible')
                        ->label('Tampilkan section')->default(true),
                ], self::sectionDisplayFields('events')))->columns(2),

            /* =========================================================
               4) SECTION COUNTDOWN (ISI)
               ========================================================= */
            Forms\Components\Section::make('4 — Section COUNTDOWN (isi)')
                ->description('Hitung mundur di dalam isi undangan. Sumber tanggal: acara pertama tab "Events" (bukan dari plans — plans hanya masa aktif & izin fitur paket).')
                ->collapsed()
                ->schema(array_merge([
                    Forms\Components\Toggle::make('theme_options.sections.countdown.visible')
                        ->label('Tampilkan section')->default(true),
                    Forms\Components\Select::make('theme_options.countdown.style')
                        ->label('Gaya angka (berlaku juga di hero)')
                        ->options([
                            'circle'  => '1. Bulat (default)',
                            'boxed'   => '2. Kotak berbingkai',
                            'minimal' => '3. Minimal — titik dua',
                            'pill'    => '4. Pil memanjang',
                            'flip'    => '5. Flip clock',
                        ])->placeholder('Bulat (default)'),
                    Forms\Components\Select::make('theme_options.countdown.layout')
                        ->label('Isi section')->live()
                        ->options([
                            'simple' => '1. Sederhana',
                            'photo'  => '2. Foto + nama pasangan',
                            'date'   => '3. Tanggal besar',
                            'quote'  => '4. Kutipan pembuka',
                        ])->placeholder('Sederhana'),
                    Forms\Components\FileUpload::make('theme_options.countdown.photo')
                        ->label('Foto latar countdown')
                        ->image()->disk('public')->directory('section-bg')
                        ->visible(fn (Forms\Get $get) => $get('theme_options.countdown.layout') === 'photo'),
                    Forms\Components\Textarea::make('theme_options.countdown.quote')
                        ->label('Teks kutipan')->rows(2)
                        ->visible(fn (Forms\Get $get) => $get('theme_options.countdown.layout') === 'quote'),
                ], self::sectionDisplayFields('countdown')))->columns(2),

            /* =========================================================
               5) SECTION KISAH KAMI
               ========================================================= */
            Forms\Components\Section::make('5 — Section KISAH KAMI')
                ->description('ISI kisah (judul, tanggal, cerita, foto per-kisah) dikelola di tab "Kisah Cinta" di bawah halaman ini.')
                ->collapsed()
                ->schema(array_merge([
                    Forms\Components\Toggle::make('theme_options.sections.love_story.visible')
                        ->label('Tampilkan section')->default(true),
                    Forms\Components\Toggle::make('theme_options.love_story.show_photos')
                        ->label('Tampilkan foto kisah')->default(false)
                        ->helperText('Foto di-upload per kisah di tab "Kisah Cinta". Toggle ini menyalakan/mematikan SEMUA foto kisah sekaligus.'),
                ], self::sectionDisplayFields('love_story')))->columns(2),

            /* =========================================================
               6) SECTION GALERI
               ========================================================= */
            Forms\Components\Section::make('6 — Section GALERI')
                ->description('ISI foto dikelola di tab "Gallery Photos". 4 model tampilan, semua tetap punya popup lightbox.')
                ->collapsed()
                ->schema(array_merge([
                    Forms\Components\Toggle::make('theme_options.sections.gallery.visible')
                        ->label('Tampilkan section')->default(true),
                    Forms\Components\Select::make('theme_options.gallery.style')
                        ->label('Model galeri')
                        ->options([
                            'carousel' => '1. Carousel — geser per halaman 4 foto (default)',
                            'grid'     => '2. Grid — kotak rapi 2 kolom, paginasi tiap 3 baris',
                            'masonry'  => '3. Masonry — susun bata mengikuti tinggi foto, paginasi tiap 3 baris',
                            'polaroid' => '4. Polaroid — kartu foto miring selang-seling, paginasi tiap 3 baris',
                            'floating' => '5. Floating — foto besar mengambang + strip thumbnail & tombol kembali ke atas',
                        ])->placeholder('Carousel (default)'),
                ], self::sectionDisplayFields('gallery')))->columns(2),

            /* =========================================================
               7) SECTION VIDEO
               ========================================================= */
            Forms\Components\Section::make('7 — Section VIDEO')
                ->description('Bukan sekadar iframe polos: ada eyebrow, kalimat pengantar, dan credit pasangan di bawah video.')
                ->collapsed()
                ->schema(array_merge([
                    Forms\Components\Toggle::make('theme_options.sections.video.visible')
                        ->label('Tampilkan section')->default(true),
                    Forms\Components\TextInput::make('video_url')->url()->label('URL video (YouTube)')
                        ->helperText('Contoh: https://www.youtube.com/watch?v=xxxx — otomatis jadi embed.'),
                    Forms\Components\TextInput::make('theme_options.video.eyebrow')
                        ->label('Eyebrow (label kecil di atas judul)')
                        ->placeholder('Wedding Film'),
                    Forms\Components\Textarea::make('theme_options.video.caption')
                        ->label('Kalimat pengantar')->rows(2)
                        ->placeholder('Sepenggal momen perjalanan kami menuju hari bahagia. Selamat menyaksikan.')
                        ->helperText('Kosong = memakai kalimat default di atas. Di bawah video otomatis tampil credit: nama pasangan + tanggal acara.'),
                ], self::sectionDisplayFields('video')))->columns(2),

            /* =========================================================
               8) SECTION RSVP
               ========================================================= */
            Forms\Components\Section::make('8 — Section RSVP')
                ->description('Toggle di sini adalah SATU-SATUNYA saklar RSVP (duplikatnya sudah dihapus).')
                ->collapsed()
                ->schema(array_merge([
                    Forms\Components\Toggle::make('rsvp_enabled')
                        ->label('RSVP aktif')->default(true),
                ], self::sectionDisplayFields('rsvp')))->columns(2),

            /* =========================================================
               9) SECTION UCAPAN & DOA
               ========================================================= */
            Forms\Components\Section::make('9 — Section UCAPAN & DOA')
                ->description('Toggle di sini adalah SATU-SATUNYA saklar buku ucapan (duplikatnya sudah dihapus).')
                ->collapsed()
                ->schema(array_merge([
                    Forms\Components\Toggle::make('guestbook_enabled')
                        ->label('Buku ucapan aktif')->default(true),
                ], self::sectionDisplayFields('guestbook')))->columns(2),

            /* =========================================================
               10) SECTION KADO
               ========================================================= */
            Forms\Components\Section::make('10 — Section KADO')
                ->description('ISI rekening/e-wallet/QRIS dikelola di tab "Gifts" di bawah halaman ini.')
                ->collapsed()
                ->schema(array_merge([
                    Forms\Components\Toggle::make('theme_options.sections.gift.visible')
                        ->label('Tampilkan section')->default(true),
                ], self::sectionDisplayFields('gift')))->columns(2),

            /* =========================================================
               11) SECTION TURUT MENGUNDANG
               ========================================================= */
            Forms\Components\Section::make('11 — Section TURUT MENGUNDANG (Premium+)')
                ->collapsed()
                ->schema(array_merge([
                    Forms\Components\Toggle::make('theme_options.sections.co_host.visible')
                        ->label('Tampilkan section')->default(true),
                    Forms\Components\Repeater::make('co_hosts')
                        ->label('Daftar nama')
                        ->schema([
                            Forms\Components\TextInput::make('name')->label('Nama')->required()
                                ->placeholder('mis. Kel. Besar Bpk. H. Ahmad'),
                            Forms\Components\Select::make('side')->label('Kriteria')
                                ->options([
                                    'pria'    => 'Pihak Pria',
                                    'wanita'  => 'Pihak Wanita',
                                    'spesial' => 'Tamu Spesial',
                                ])->default('pria')->required(),
                        ])->columns(2)
                        ->defaultItems(0)->columnSpanFull()
                        ->helperText('Tamu Spesial tampil paling atas; pihak pria & wanita 2 kolom di desktop, bertumpuk di HP.'),
                ], self::sectionDisplayFields('co_host')))->columns(2),

            /* =========================================================
               PENGATURAN GLOBAL
               ========================================================= */
            Forms\Components\Section::make('Global — Tata Letak & Kartu Default')
                ->description('Nilai DEFAULT untuk semua section; tiap section bisa menimpanya lewat pengaturannya masing-masing di atas.')
                ->collapsed()
                ->schema([
                    Forms\Components\Toggle::make('theme_options.layout.card')
                        ->label('Kartu untuk section konten (default)')->default(true),
                    Forms\Components\Select::make('theme_options.layout.section_height')
                        ->label('Tinggi section')
                        ->options([
                            'full'  => 'Satu layar penuh (default)',
                            'auto'  => 'Setinggi konten (tanpa gap)',
                            'smart' => 'Otomatis: penuh jika ada background',
                        ])->placeholder('Satu layar penuh (default)'),
                    Forms\Components\Select::make('theme_options.card.style')
                        ->label('Gaya kartu (default global)')
                        ->options(self::CARD_STYLES)
                        ->placeholder('Bawaan tema'),
                    Forms\Components\ColorPicker::make('theme_options.card.bg')->label('Warna kartu'),
                    Forms\Components\TextInput::make('theme_options.card.opacity')->label('Opacity (%)')
                        ->numeric()->minValue(0)->maxValue(100)->placeholder('100'),
                    Forms\Components\ColorPicker::make('theme_options.card.shadow_color')->label('Warna shadow'),
                    Forms\Components\Select::make('theme_options.card.shadow_size')->label('Ketebalan shadow')
                        ->options([
                            'none'   => 'Tanpa shadow',
                            'lembut' => 'Lembut',
                            'sedang' => 'Sedang (default)',
                            'kuat'   => 'Kuat',
                        ])->placeholder('Sedang (default)'),
                    Forms\Components\Fieldset::make('Radius sudut kartu — per sudut (px)')
                        ->schema([
                            Forms\Components\TextInput::make('theme_options.card.radius_tl')
                                ->label('Atas kiri')->numeric()->minValue(0)->maxValue(120)->placeholder('bawaan'),
                            Forms\Components\TextInput::make('theme_options.card.radius_tr')
                                ->label('Atas kanan')->numeric()->minValue(0)->maxValue(120)->placeholder('bawaan'),
                            Forms\Components\TextInput::make('theme_options.card.radius_bl')
                                ->label('Bawah kiri')->numeric()->minValue(0)->maxValue(120)->placeholder('bawaan'),
                            Forms\Components\TextInput::make('theme_options.card.radius_br')
                                ->label('Bawah kanan')->numeric()->minValue(0)->maxValue(120)->placeholder('bawaan'),
                        ])->columns(4)->columnSpanFull(),
                ])->columns(3),

            Forms\Components\Section::make('Global — Floral 4 Sudut Halaman')
                ->collapsed()
                ->schema([
                    Forms\Components\Select::make('theme_options.florals.tl')
                        ->label('Floral kiri-atas')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('theme_options.florals.tr')
                        ->label('Floral kanan-atas')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('theme_options.florals.bl')
                        ->label('Floral kiri-bawah')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('theme_options.florals.br')
                        ->label('Floral kanan-bawah')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                ])->columns(2),

            Forms\Components\Section::make('Global — Animasi Scroll (GSAP)')
                ->collapsed()
                ->schema([
                    Forms\Components\Select::make('theme_options.animation.preset')
                        ->label('Preset animasi section')
                        ->options([
                            'fade-up'    => 'Muncul dari bawah (default)',
                            'fade-down'  => 'Muncul dari atas',
                            'fade-left'  => 'Geser dari kanan',
                            'fade-right' => 'Geser dari kiri',
                            'zoom'       => 'Zoom lembut',
                            'none'       => 'Tanpa animasi',
                        ])->placeholder('Muncul dari bawah (default)'),
                ]),

            Forms\Components\Section::make('Global — Tipografi (Judul & Isi)')
                ->description('Kosongkan = bawaan tema. Nama font Google Fonts bebas diketik — dimuat otomatis.')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('theme_options.fonts.heading')
                        ->label('Font JUDUL')->placeholder('Bawaan tema')
                        ->datalist(['Cormorant Garamond', 'Playfair Display', 'Cinzel', 'Lora', 'EB Garamond', 'Marcellus']),
                    Forms\Components\TextInput::make('theme_options.type.title_size')
                        ->label('Ukuran judul (px)')->numeric()->minValue(14)->maxValue(96)->placeholder('bawaan tema'),
                    Forms\Components\ColorPicker::make('theme_options.type.title_color')->label('Warna judul'),
                    Forms\Components\TextInput::make('theme_options.fonts.body')
                        ->label('Font ISI')->placeholder('Bawaan tema')
                        ->datalist(['Jost', 'Poppins', 'Lato', 'Open Sans', 'Nunito', 'Inter', 'Mulish']),
                    Forms\Components\TextInput::make('theme_options.type.body_size')
                        ->label('Ukuran isi (px)')->numeric()->minValue(10)->maxValue(28)->placeholder('bawaan tema'),
                    Forms\Components\ColorPicker::make('theme_options.type.body_color')->label('Warna isi'),
                    Forms\Components\TextInput::make('theme_options.fonts.script')
                        ->label('Font KALIGRAFI')->placeholder('Bawaan tema')
                        ->datalist(['Great Vibes', 'Dancing Script', 'Parisienne', 'Allura', 'Sacramento', 'Alex Brush']),
                    Forms\Components\TextInput::make('theme_options.fonts.css_url')
                        ->label('URL CSS font (opsional, non-Google)')->url()
                        ->helperText('Tempel link stylesheet @font-face (Adobe Fonts / self-host).')
                        ->columnSpan(2),
                ])->columns(3),

            Forms\Components\Section::make('Global — Musik Latar (Premium+)')
                ->collapsed()
                ->schema([
                    Forms\Components\FileUpload::make('music_url')
                        ->label('Musik latar (mp3)')
                        ->disk('public')->directory('music')
                        ->acceptedFileTypes(['audio/mpeg', 'audio/mp3'])
                        ->maxSize(15360)
                        ->helperText('Upload file mp3 milik sendiri.'),
                ]),

            Forms\Components\Section::make('Global — Override Warna (mengalahkan tema)')
                ->collapsed()
                ->schema([
                    Forms\Components\ColorPicker::make('theme_options.colors.accent')->label('Aksen'),
                    Forms\Components\ColorPicker::make('theme_options.colors.paper')->label('Permukaan kartu'),
                    Forms\Components\ColorPicker::make('theme_options.colors.ink')->label('Teks'),
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
                Tables\Columns\TextColumn::make('guests_count')->counts('guests')->label('Tamu'),
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
            InvitationResource\RelationManagers\GuestsRelationManager::class,
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
