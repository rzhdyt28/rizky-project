<?php

namespace App\Filament\Support;

use Filament\Forms;
use Filament\Notifications\Notification;

/**
 * Definisi field yang dipakai BERSAMA oleh ThemeResource (default_options.*)
 * dan InvitationResource (theme_options.*) untuk kelompok opsi yang murni
 * "preferensi tampilan" (warna/font/kartu/label) — field baru cukup
 * ditambah SEKALI di sini supaya kedua form otomatis tetap sinkron, alih-alih
 * ditulis manual dua kali dan ngedrift (mis. field ada di satu form tapi
 * tidak di form lainnya).
 *
 * Field yang isinya KONTEN per-undangan (foto, teks kutipan, slideshow, dst)
 * SENGAJA tidak di sini — itu cuma masuk akal ada di InvitationResource.
 */
class ThemeOptionsSchema
{
    /** Elemen teks hero yang bisa diatur ukuran+warna sendiri (berlaku di SEMUA gaya hero, bukan cuma Custom). */
    public const HERO_TEXT_ELEMENTS = [
        'eyebrow'         => 'Label "Undangan Pernikahan"',
        'names'           => 'Nama pasangan',
        'date'            => 'Tanggal acara',
        'countdown_label' => 'Label "Menuju hari bahagia" (countdown)',
        'dresscode'       => 'Dress code',
        'guest'           => 'Kepada (nama tamu)',
        'button'          => 'Tombol "Buka Undangan"',
    ];

    /**
     * KATEGORI FONT GLOBAL (v6) -- satu-satunya sumber font sungguhan ada di
     * 4 field ini (typographyFields(): Judul/Isi/Kaligrafi/Aksen). Elemen
     * PER-KEY (eyebrow, label, nama acara, dst) TIDAK LAGI boleh isi nama
     * font bebas sendiri -- cuma boleh "nunut" ke salah satu dari 4 kategori
     * ini (elementFontField() di bawah), supaya ganti 1 font global otomatis
     * konsisten ke semua elemen yang memakainya. Value tersimpan = key
     * kategori ('heading'/'body'/'script'/'accent'), BUKAN nama font.
     */
    public const FONT_CATEGORIES = [
        'heading' => 'Ikut Font Judul Section',
        'body'    => 'Ikut Font Teks Isi',
        'script'  => 'Ikut Font Kaligrafi',
        'accent'  => 'Ikut Font Aksen',
    ];

    /** Satu field pilihan KATEGORI font untuk 1 elemen -- dipakai heroElementStyleFields() & sectionElementStyleFields(). */
    protected static function elementFontField(string $formPath, string $label): Forms\Components\Select
    {
        return Forms\Components\Select::make($formPath)
            ->label("Font — $label")
            ->options(self::FONT_CATEGORIES)
            ->placeholder('Bawaan (ikut gaya elemen)')
            ->native(false);
    }

    /**
     * "Tipografi, Ukuran & Warna tiap elemen teks hero" -- 3 field per elemen
     * (font/ukuran/warna, v5 -- font digabung ke sini, MENGGANTIKAN field
     * "Font nama pasangan di hero" yang terpisah/tidak termuat font Google-nya).
     * Dipakai ThemeResource & InvitationLookResource.
     */
    public static function heroElementStyleFields(string $prefix = 'default_options'): array
    {
        $fields = [];
        foreach (self::HERO_TEXT_ELEMENTS as $key => $label) {
            $fields[] = Forms\Components\Grid::make(['default' => 1, 'sm' => 3])->schema([
                self::elementFontField("$prefix.hero.elements.$key.font", $label),
                self::fontSizeField("$prefix.hero.elements.$key.size", "Ukuran — $label (px)", 'Bawaan gaya'),
                Forms\Components\ColorPicker::make("$prefix.hero.elements.$key.color")
                    ->label("Warna — $label"),
            ]);
        }

        return $fields;
    }

    /**
     * TIPOGRAFI, UKURAN & WARNA TIAP ELEMEN TEKS SECTION (v5) — generik untuk
     * section SELAIN hero, 3 field per elemen (font/ukuran/warna). $elements =
     * ['elKey' => 'Label tampil', ...], key 'title' KHUSUS menimpa judul
     * section (SectionWrapper), key lain dikonsumsi masing-masing *Style*.vue
     * lewat var(--el-{elKey}-font/color/size, ...) -- lihat useThemeOptions.js
     * sectionFontVars(). Dipakai ThemeResource & InvitationLookResource.
     */
    public static function sectionElementStyleFields(string $prefix, string $key, array $elements): array
    {
        $fields = [];
        foreach ($elements as $elKey => $label) {
            $fields[] = Forms\Components\Grid::make(['default' => 1, 'sm' => 3])->schema([
                self::elementFontField("$prefix.sections.$key.elements.$elKey.font", $label),
                self::fontSizeField("$prefix.sections.$key.elements.$elKey.size", "Ukuran — $label (px)", 'Bawaan tema'),
                Forms\Components\ColorPicker::make("$prefix.sections.$key.elements.$elKey.color")
                    ->label("Warna — $label"),
            ]);
        }

        return $fields;
    }

    /** Satu sumber pilihan gaya kartu — dipakai ThemeResource & InvitationResource. */
    public const CARD_STYLES = [
        'default'    => 'Bawaan tema',
        'glass'      => 'Glass — kaca buram (blur)',
        'outline'    => 'Outline — garis tepi, tanpa isi',
        'flat'       => 'Flat — polos tanpa shadow',
        'gradient'   => 'Gradient — gradasi lembut',
        'stamp'      => 'Stamp — tepi perangko',
        'java'       => 'Java — border ganda emas-cokelat bermotif',
        'brutalist'  => 'Modern — sudut kotak, border tebal, shadow keras',
        'vintage'    => 'Klasik — bingkai ganda ala pigura foto lama',
        'line'       => 'Garis Minimalis — cuma garis tipis, tanpa isi',
        'royal'      => 'Royal — border foil emas, kesan mewah',
    ];

    /**
     * Katalog opsi gaya per-section — ini SATU-SATUNYA tempat daftar gaya
     * (hero, mempelai, acara, countdown, kisah, galeri, animasi, dst)
     * didefinisikan. ThemeResource & InvitationResource membaca const-const
     * di bawah ini alih-alih menulis ulang array opsi masing-masing, supaya
     * pilihan yang tampil di admin TIDAK PERNAH beda antara dua form itu.
     *
     * Menambah varian gaya baru (mis. hero.style ke-6):
     *   1. Tambah entri key => label di const yang relevan di sini dulu,
     *      supaya pilihannya muncul di dropdown admin (Theme & Invitation).
     *   2. Implementasikan cabang render-nya di Vue — per tema, di
     *      `themes/<nama-tema>/sections/Cover.vue` (atau section lain yang
     *      relevan) di repo rizky-project-web. Tidak semua tema wajib
     *      mendukung semua gaya (lihat _template/README.md).
     *   3. Tambah CSS baru di `theme.css` tema itu kalau perlu tampilan
     *      custom di luar yang sudah ada.
     */
    public const HERO_STYLES = [
        'classic'  => '1. Classic — nama & tanggal bertumpuk (default)',
        'framed'   => '2. Framed — foto bulat berbingkai di atas nama',
        'split'    => '3. Split — teks & foto berbingkai dua kolom',
        'minimal'  => '4. Minimal — nama besar + garis tipis, sangat bersih',
        'arch'     => '5. Arch — foto besar berbingkai lengkung di atas nama',
        'monogram' => '6. Monogram — inisial besar di tengah, sangat minimalis',
        'polaroid' => '7. Polaroid — foto gaya polaroid miring, playful',
        'poster'   => '8. Poster — foto latar penuh, nama besar menumpuk di bawah, dramatis',
    ];


    public const HERO_EFFECTS = [
        'fade'     => 'Fade — pudar halus (rekomendasi)',
        'kenburns' => 'Ken Burns — fade + zoom perlahan (ease)',
    ];

    /** Efek CSS filter untuk Background Video hero — diterapkan lewat elemen <video> di FE. */
    public const VIDEO_EFFECTS = [
        'none'       => 'Tanpa efek',
        'sepia'      => 'Sepia',
        'bw'         => 'Hitam-putih (B&W)',
        'vintage'    => 'Vintage (sepia + kontras rendah)',
        'blur'       => 'Blur lembut',
        'brightness' => 'Lebih terang',
        'contrast'   => 'Kontras tinggi',
        'saturate'   => 'Saturasi tinggi (warna lebih hidup)',
        'hue-rotate' => 'Hue-rotate (pergeseran warna)',
    ];

    public const VIDEO_STYLES = [
        'classic'   => '1. Classic — 16:9, eyebrow & caption di atas (default)',
        'cinematic' => '2. Cinematic — lebar 21:9 gelap ala layar bioskop',
        'framed'    => '3. Framed — bingkai ornamen tebal, caption di bawah',
    ];

    public const RSVP_STYLES = [
        'card'    => '1. Card — panel bertepi, field bertumpuk (default)',
        'minimal' => '2. Minimal — tanpa kotak, field underline tipis',
        'chips'   => '3. Chips — kehadiran dipilih lewat 3 tombol chip',
    ];

    public const GUESTBOOK_STYLES = [
        'list'   => '1. List — form di atas, daftar ucapan bertumpuk (default)',
        'wall'   => '2. Wall — daftar ucapan jadi kartu 2 kolom',
        'quotes' => '3. Quotes — tiap ucapan tampil ala kutipan bertanda petik',
    ];

    public const GIFT_STYLES = [
        'panel'  => '1. Panel — kartu bertepi putus-putus (default)',
        'stack'  => '2. Stack — daftar padat, ikon jenis kado di kiri',
        'ticket' => '3. Ticket — kartu ala tiket sobek',
    ];

    public const HERO_CARD_MODES = [
        'inherit' => 'Ikut pengaturan konten',
        'card'    => 'Selalu pakai kartu',
        'plain'   => 'Tanpa kartu (full-foto)',
    ];

    public const SECTION_HEIGHTS = [
        'full'  => 'Satu layar penuh (default)',
        'auto'  => 'Setinggi konten (tanpa gap)',
        'smart' => 'Otomatis: penuh jika ada background',
    ];

    public const COUPLE_STYLES = [
        'classic'  => '1. Classic — teks bertumpuk klasik (tanpa foto pun rapi)',
        'cards'    => '2. Cards — dua kartu foto berdampingan, nama di bawah foto',
        'circle'   => '3. Circle — foto lingkaran besar, "&" kaligrafi di tengah',
        'arch'     => '4. Arch — foto bingkai lengkung ala gerbang, berdampingan',
        'portrait' => '5. Portrait — foto potret besar bertumpuk ke bawah',
        'ribbon'   => '6. Ribbon — dua foto disatukan badge "&" di tengah',
        'polaroid' => '7. Polaroid — dua foto gaya polaroid miring, playful',
    ];

    public const CO_HOST_STYLES = [
        'classic' => '1. Classic — tamu spesial di atas, 2 kolom pria/wanita (default)',
        'grid'    => '2. Grid — tiap nama jadi chip, cocok daftar panjang',
        'elegant' => '3. Elegant — flourish tipis, tipografi vintage',
        'compact' => '4. Compact — satu daftar padat dengan label pihak inline',
    ];

    public const EVENTS_STYLES = [
        'card'     => '1. Card — panel bertepi (default)',
        'elegant'  => '2. Elegant — flourish & tipografi vintage',
        'timeline' => '3. Timeline — garis putus-putus + penanda',
        'minimal'  => '4. Minimal — tanpa kotak, garis tipis',
        'badge'    => '5. Badge — nomor urut bulat + detail di samping',
        'ticket'   => '6. Ticket — kartu ala tiket sobek, playful',
        'compact'  => '7. Compact — daftar padat, cocok banyak acara',
    ];

    public const COUNTDOWN_STYLES = [
        'circle'  => '1. Bulat (default)',
        'boxed'   => '2. Kotak berbingkai',
        'minimal' => '3. Minimal — titik dua',
        'pill'    => '4. Pil memanjang',
        'flip'    => '5. Flip clock',
    ];

    public const COUNTDOWN_LAYOUTS = [
        'simple' => '1. Sederhana',
        'photo'  => '2. Foto + nama pasangan',
        'date'   => '3. Tanggal besar',
        'quote'  => '4. Kutipan pembuka',
    ];

    public const LOVE_STORY_STYLES = [
        'stacked'   => '1. Stacked — daftar polos bertumpuk (default)',
        'timeline'  => '2. Timeline — garis putus-putus + penanda hati',
        'alternate' => '3. Alternate — zigzag kiri-kanan',
        'polaroid'  => '4. Polaroid — kartu foto miring ala scrapbook',
        'letter'    => '5. Letter — ala surat/postcard, foto bulat kecil',
        'grid'      => '6. Grid — mosaic foto 2 kolom',
        'minimal'   => '7. Minimal — teks di tengah, foto bulat kecil',
    ];

    public const GALLERY_STYLES = [
        'carousel' => '1. Carousel — geser per halaman 4 foto (default)',
        'grid'     => '2. Grid — kotak rapi 2 kolom, paginasi tiap 3 baris',
        'masonry'  => '3. Masonry — susun bata mengikuti tinggi foto, paginasi tiap 3 baris',
        'polaroid' => '4. Polaroid — kartu foto miring selang-seling, paginasi tiap 3 baris',
        'floating' => '5. Floating — foto besar mengambang + strip thumbnail & tombol kembali ke atas',
        'circles'  => '6. Circles — thumbnail bulat 3 kolom, paginasi tiap 3 baris',
        'strip'    => '7. Strip — satu baris scroll horizontal (native swipe di HP)',
        'framed'   => '8. Framed — 1 kolom foto besar berbingkai, paginasi tiap 3 baris',
    ];

    public const ANIMATION_PRESETS = [
        'fade-up'    => 'Muncul dari bawah (default)',
        'fade-down'  => 'Muncul dari atas',
        'fade-left'  => 'Geser dari kanan',
        'fade-right' => 'Geser dari kiri',
        'zoom'       => 'Zoom lembut',
        'none'       => 'Tanpa animasi',
    ];

    /**
     * @param  (callable(Forms\Get $get, string $key, ?\Illuminate\Database\Eloquent\Model $record): ?string)|null  $defaultFor
     *         Kalau diisi, tiap field dapat helperText "Bawaan tema: X" — dipakai InvitationResource/
     *         InvitationLookResource untuk menunjukkan nilai bawaan tema. $record di-inject otomatis oleh
     *         Filament (record yang sedang diedit) -- diabaikan closure lama yang cuma deklarasi 2 argumen.
     */
    /**
     * Label & contoh dituliskan berdasarkan makna asli tiap warna di file
     * tokens.js tiap tema (mis. themes/senja/tokens.js) -- itu sumber
     * kebenaran peran tiap warna, supaya label di sini match dengan yang
     * benar-benar dipakai frontend, bukan tebakan.
     */
    private const COLOR_META = [
        'accent'      => ['label' => 'Warna Aksen', 'hint' => 'Judul section (mis. "Kisah Kami"), angka countdown, elemen yang mau menonjol.'],
        'paper'       => ['label' => 'Warna Latar Kartu', 'hint' => 'Latar kartu mengambang tempat isi section ditulis -- BUKAN latar halaman penuh (itu belum bisa diatur, lihat catatan di bawah tab ini).'],
        'ink'         => ['label' => 'Warna Teks Utama', 'hint' => 'Warna teks isi (paragraf, label) di seluruh undangan.'],
        'gold'        => ['label' => 'Warna Aksen Sekunder', 'hint' => 'Elemen kecil: label dresscode, garis pemisah, ornamen dekoratif.'],
        'button_bg'   => ['label' => 'Warna Latar Tombol', 'hint' => 'Latar tombol "Buka Undangan" dan tombol lain sejenis.'],
        'button_text' => ['label' => 'Warna Teks Tombol', 'hint' => 'Warna tulisan DI ATAS tombol (harus kontras dengan latar tombol di atas).'],
    ];

    public static function colorFields(string $prefix, ?callable $defaultFor = null): array
    {
        $field = function (string $key) use ($prefix, $defaultFor) {
            $meta = self::COLOR_META[$key];
            $picker = Forms\Components\ColorPicker::make("$prefix.colors.$key")
                ->label($meta['label'])
                ->helperText($meta['hint']);
            if ($defaultFor) {
                $picker->helperText(fn (Forms\Get $get, ?\Illuminate\Database\Eloquent\Model $record) => $meta['hint'] . ' Bawaan tema: ' . ($defaultFor($get, $key, $record) ?? '—'));
            }

            return $picker;
        };

        return [
            $field('accent'),
            $field('paper'),
            $field('ink'),
            $field('gold'),
            $field('button_bg'),
            $field('button_text'),
        ];
    }

    /**
     * Field ukuran font BEBAS (tanpa batas minValue/maxValue) — admin boleh isi
     * berapa saja. Di atas $warnAt px, kirim Notification peringatan (bukan blokir)
     * supaya admin sadar risikonya tapi tetap bisa lanjut kalau memang disengaja.
     */
    public static function fontSizeField(string $name, string $label, string $placeholder, int $warnAt = 60): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make($name)
            ->label($label)->numeric()->live(onBlur: true)->placeholder($placeholder)
            ->afterStateUpdated(function ($state) use ($label, $warnAt) {
                if (filled($state) && (float) $state >= $warnAt) {
                    Notification::make()
                        ->title("Ukuran {$label} cukup besar ({$state}px)")
                        ->body('Pastikan sudah dicek tampilannya di layar HP supaya tidak terpotong/menumpuk.')
                        ->warning()->send();
                }
            });
    }

    public static function typographyFields(string $prefix): array
    {
        return [
            Forms\Components\TextInput::make("$prefix.fonts.heading")
                ->label('Font Judul Section')->placeholder('Bawaan tema')
                ->helperText('Nama Google Fonts bebas diketik (mis. "Cormorant Garamond") -- dimuat otomatis. Ukuran/warna judul & isi kini diatur per-section lewat "Ukuran & Warna tiap elemen teks section" di tiap tab, bukan di sini lagi.')
                ->datalist(['Cormorant Garamond', 'Playfair Display', 'Cinzel', 'Lora', 'EB Garamond', 'Marcellus']),
            Forms\Components\TextInput::make("$prefix.fonts.body")
                ->label('Font Teks Isi')->placeholder('Bawaan tema')
                ->helperText('Dipakai untuk paragraf & label biasa (bukan judul).')
                ->datalist(['Jost', 'Poppins', 'Lato', 'Open Sans', 'Nunito', 'Inter', 'Mulish']),
            Forms\Components\TextInput::make("$prefix.fonts.script")
                ->label('Font Kaligrafi (Nama Pasangan)')->placeholder('Bawaan tema')
                ->helperText('Khusus font gaya tulisan tangan untuk nama pasangan di Hero.')
                ->datalist(['Great Vibes', 'Dancing Script', 'Parisienne', 'Allura', 'Sacramento', 'Alex Brush']),
            Forms\Components\TextInput::make("$prefix.fonts.accent")
                ->label('Font Aksen (Label kecil)')->placeholder('Ikut Font Teks Isi')
                ->helperText('Dipakai untuk label kecil huruf kapital (mis. "Undangan Pernikahan", "Dress Code", label countdown) -- elemen lain (judul/isi/kaligrafi) TIDAK memakai ini.')
                ->datalist(['Jost', 'Poppins', 'Lato', 'Open Sans', 'Nunito', 'Inter', 'Mulish']),
            Forms\Components\TextInput::make("$prefix.fonts.css_url")
                ->label('URL CSS font (non-Google)')->url()
                ->helperText('Untuk font di luar Google Fonts (Adobe Fonts / self-host): tempel link stylesheet-nya.')
                ->columnSpan(2),
        ];
    }

    /** @param array<string,string> $cardStyles value => label, satu sumber dipakai pemanggil. */
    public static function cardFields(string $prefix, array $cardStyles): array
    {
        return [
            Forms\Components\Select::make("$prefix.card.style")->label('Gaya kartu')
                ->options($cardStyles)->placeholder('Bawaan tema'),
            Forms\Components\ColorPicker::make("$prefix.card.bg")->label('Warna kartu'),
            Forms\Components\TextInput::make("$prefix.card.opacity")->label('Opacity kartu (%)')
                ->numeric()->minValue(0)->maxValue(100)->placeholder('100'),
            Forms\Components\ColorPicker::make("$prefix.card.shadow_color")->label('Warna shadow'),
            Forms\Components\Select::make("$prefix.card.shadow_size")->label('Ketebalan shadow')
                ->options([
                    'none'   => 'Tanpa shadow',
                    'lembut' => 'Lembut',
                    'sedang' => 'Sedang (default)',
                    'kuat'   => 'Kuat',
                ])->placeholder('Sedang (default)'),
            Forms\Components\Fieldset::make('Radius sudut kartu — per sudut (px)')
                ->schema([
                    Forms\Components\TextInput::make("$prefix.card.radius_tl")
                        ->label('Atas kiri')->numeric()->minValue(0)->maxValue(120)->placeholder('bawaan'),
                    Forms\Components\TextInput::make("$prefix.card.radius_tr")
                        ->label('Atas kanan')->numeric()->minValue(0)->maxValue(120)->placeholder('bawaan'),
                    Forms\Components\TextInput::make("$prefix.card.radius_bl")
                        ->label('Bawah kiri')->numeric()->minValue(0)->maxValue(120)->placeholder('bawaan'),
                    Forms\Components\TextInput::make("$prefix.card.radius_br")
                        ->label('Bawah kanan')->numeric()->minValue(0)->maxValue(120)->placeholder('bawaan'),
                ])->columns(['default' => 2, 'sm' => 2, 'lg' => 4])->columnSpanFull(),
        ];
    }

    public static function animationField(string $prefix): array
    {
        return [
            Forms\Components\Select::make("$prefix.animation.preset")
                ->label('Preset animasi section')
                ->options(self::ANIMATION_PRESETS)
                ->placeholder('Muncul dari bawah (default)'),
        ];
    }

    /** Satu sumber daftar section yang punya toggle tampil/sembunyi — dipakai checklist Theme & Invitation. */
    public const SECTION_VISIBILITY_LABELS = [
        'couple'         => 'Mempelai',
        'events'         => 'Acara',
        'countdown'      => 'Countdown (isi)',
        'countdown_hero' => 'Countdown di Hero',
        'love_story'     => 'Kisah Kami',
        'gallery'        => 'Galeri',
        'video'          => 'Video',
        'rsvp'           => 'RSVP',
        'guestbook'      => 'Ucapan & Doa',
        'gift'           => 'Kado',
        'co_host'        => 'Turut Mengundang',
    ];

    /**
     * Field tri-state ("Ikuti tema dasar" / "Aktif" / "Nonaktif") pengganti
     * Toggle biner untuk SEMUA pengaturan yang punya makna "kosong = ikut
     * parent". Toggle biner TIDAK BISA merepresentasikan "belum disentuh" --
     * begitu form disave sekali, Filament Toggle selalu menulis true/false
     * konkret, jadi update di parent theme setelahnya tidak pernah "menembus"
     * lagi ke child yang sudah pernah disave (bug yang sempat dilaporkan:
     * section tetap mati walau toggle induk sudah dinyalakan).
     *
     * Disimpan sebagai null (kosong/ikut parent), true, atau false -- 100%
     * kompatibel dengan merge default_options via array_replace_recursive di
     * Theme::ancestryChain() (null dianggap "tidak ada", diprune sebelum
     * merge, lihat PublicInvitationController::prune()).
     *
     * @param  string  $formPath  path lengkap field di form, mis. "default_options.sections.countdown.visible"
     * @param  string  $key  key yang dikirim ke $defaultFor (biasanya sama dengan bagian akhir $formPath tanpa prefix)
     * @param  (callable(Forms\Get $get, string $key, ?\Illuminate\Database\Eloquent\Model $record): bool)|null  $defaultFor
     *         Kalau diisi, helper text menampilkan nilai bawaan tema dasar SAAT INI (live, bukan snapshot).
     */
    public static function tristateToggle(string $formPath, string $key, string $label, ?callable $defaultFor = null): Forms\Components\Select
    {
        $select = Forms\Components\Select::make($formPath)
            ->label($label)
            ->options(['1' => 'Aktif', '0' => 'Nonaktif'])
            ->placeholder('— Ikuti tema dasar —')
            ->native(false)
            ->afterStateHydrated(function (Forms\Components\Select $component, $state) {
                // Data lama (sebelum migrasi ke tri-state) masih boolean asli di JSON.
                if (is_bool($state)) {
                    $component->state($state ? '1' : '0');
                }
            })
            ->dehydrateStateUsing(fn ($state) => ($state === null || $state === '') ? null : (bool) (int) $state);

        if ($defaultFor) {
            $select->helperText(fn (Forms\Get $get, ?\Illuminate\Database\Eloquent\Model $record) => 'Bawaan tema dasar saat ini: ' . ($defaultFor($get, $key, $record) ? 'Aktif' : 'Nonaktif'));
        }

        return $select;
    }

    /**
     * Checklist "Section Aktif": satu field tri-state per section. Dipakai
     * Theme (default_options.sections.*.visible) SEBAGAI DEFAULT, dan
     * Invitation (theme_options.sections.*.visible) SEBAGAI OVERRIDE di atasnya.
     *
     * @param  (callable(Forms\Get $get, string $key, ?\Illuminate\Database\Eloquent\Model $record): bool)|null  $defaultFor
     *         Kalau diisi, field ini tri-state (ikut parent kalau kosong). Kalau TIDAK diisi (dipakai
     *         di ThemeResource tanpa parent), field jadi Toggle biner default AKTIF -- tidak ada
     *         "parent" untuk diikuti di level tema paling dasar.
     */
    public static function sectionVisibilityFields(string $prefix, ?callable $defaultFor = null): array
    {
        return collect(self::SECTION_VISIBILITY_LABELS)->map(
            function (string $label, string $key) use ($prefix, $defaultFor) {
                if ($defaultFor) {
                    return self::tristateToggle("$prefix.sections.$key.visible", $key, $label, $defaultFor);
                }

                return Forms\Components\Toggle::make("$prefix.sections.$key.visible")->label($label)->default(true);
            }
        )->values()->all();
    }

    public static function labelFields(string $prefix): array
    {
        return [
            Forms\Components\TextInput::make("$prefix.labels.btn_open")->label('Tombol buka')->placeholder('Buka Undangan'),
            Forms\Components\TextInput::make("$prefix.labels.btn_rsvp")->label('Tombol RSVP')->placeholder('Kirim Konfirmasi'),
            Forms\Components\TextInput::make("$prefix.labels.title_events")->placeholder('Rangkaian Acara'),
            Forms\Components\TextInput::make("$prefix.labels.title_co_host")->placeholder('Turut Mengundang'),
            Forms\Components\TextInput::make("$prefix.labels.title_story")->placeholder('Kisah Kami'),
            Forms\Components\TextInput::make("$prefix.labels.title_gallery")->placeholder('Galeri'),
            Forms\Components\TextInput::make("$prefix.labels.title_video")->placeholder('Video'),
            Forms\Components\TextInput::make("$prefix.labels.title_rsvp")->placeholder('Konfirmasi Kehadiran'),
            Forms\Components\TextInput::make("$prefix.labels.title_guestbook")->placeholder('Ucapan & Doa'),
            Forms\Components\TextInput::make("$prefix.labels.title_gift")->placeholder('Kirim Hadiah'),
        ];
    }
}
