<?php

namespace App\Filament\Support;

use Filament\Forms;

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
    /** Satu sumber pilihan gaya kartu — dipakai ThemeResource & InvitationResource. */
    public const CARD_STYLES = [
        'default'  => 'Bawaan tema',
        'glass'    => 'Glass — kaca buram (blur)',
        'outline'  => 'Outline — garis tepi, tanpa isi',
        'flat'     => 'Flat — polos tanpa shadow',
        'gradient' => 'Gradient — gradasi lembut',
        'stamp'    => 'Stamp — tepi perangko',
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
        'classic' => '1. Classic — nama & tanggal bertumpuk (default)',
        'framed'  => '2. Framed — foto bulat berbingkai di atas nama',
        'split'   => '3. Split — teks & foto berbingkai dua kolom',
        'minimal' => '4. Minimal — nama besar + garis tipis, sangat bersih',
        'custom'  => '5. Custom — atur urutan & rata tiap elemen sendiri',
    ];

    public const HERO_POSITIONS = [
        'split'  => '1. Split — eyebrow di atas, konten di bawah (rekomendasi, paling seimbang untuk foto potret)',
        'center' => '2. Center — semua di tengah layar (formal & simetris)',
        'bottom' => '3. Bottom — konten menumpuk di bawah (foto tetap dominan)',
        'left'   => '4. Left — rata kiri bawah (editorial/majalah, cocok foto lanskap)',
    ];

    public const HERO_EFFECTS = [
        'fade'     => 'Fade — pudar halus (rekomendasi)',
        'kenburns' => 'Ken Burns — fade + zoom perlahan (ease)',
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
        'classic' => '1. Classic — teks bertumpuk klasik (tanpa foto pun rapi)',
        'cards'   => '2. Cards — dua kartu foto berdampingan, nama di bawah foto',
        'circle'  => '3. Circle — foto lingkaran besar, "&" kaligrafi di tengah',
        'arch'    => '4. Arch — foto bingkai lengkung ala gerbang, berdampingan',
    ];

    public const EVENTS_STYLES = [
        'card'     => '1. Card — panel bertepi (default)',
        'elegant'  => '2. Elegant — flourish & tipografi vintage',
        'timeline' => '3. Timeline — garis putus-putus + penanda',
        'minimal'  => '4. Minimal — tanpa kotak, garis tipis',
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
    ];

    public const GALLERY_STYLES = [
        'carousel' => '1. Carousel — geser per halaman 4 foto (default)',
        'grid'     => '2. Grid — kotak rapi 2 kolom, paginasi tiap 3 baris',
        'masonry'  => '3. Masonry — susun bata mengikuti tinggi foto, paginasi tiap 3 baris',
        'polaroid' => '4. Polaroid — kartu foto miring selang-seling, paginasi tiap 3 baris',
        'floating' => '5. Floating — foto besar mengambang + strip thumbnail & tombol kembali ke atas',
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

    public static function typographyFields(string $prefix): array
    {
        return [
            Forms\Components\TextInput::make("$prefix.fonts.heading")
                ->label('Font Judul Section')->placeholder('Bawaan tema')
                ->helperText('Nama Google Fonts bebas diketik (mis. "Cormorant Garamond") -- dimuat otomatis.')
                ->datalist(['Cormorant Garamond', 'Playfair Display', 'Cinzel', 'Lora', 'EB Garamond', 'Marcellus']),
            Forms\Components\TextInput::make("$prefix.type.title_size")
                ->label('Ukuran Font Judul (px)')->numeric()->minValue(14)->maxValue(96)->placeholder('bawaan tema'),
            Forms\Components\ColorPicker::make("$prefix.type.title_color")->label('Warna Judul Section'),
            Forms\Components\TextInput::make("$prefix.fonts.body")
                ->label('Font Teks Isi')->placeholder('Bawaan tema')
                ->helperText('Dipakai untuk paragraf & label biasa (bukan judul).')
                ->datalist(['Jost', 'Poppins', 'Lato', 'Open Sans', 'Nunito', 'Inter', 'Mulish']),
            Forms\Components\TextInput::make("$prefix.type.body_size")
                ->label('Ukuran Font Isi (px)')->numeric()->minValue(10)->maxValue(28)->placeholder('bawaan tema'),
            Forms\Components\ColorPicker::make("$prefix.type.body_color")->label('Warna Teks Isi'),
            Forms\Components\TextInput::make("$prefix.fonts.script")
                ->label('Font Kaligrafi (Nama Pasangan)')->placeholder('Bawaan tema')
                ->helperText('Khusus font gaya tulisan tangan untuk nama pasangan di Hero.')
                ->datalist(['Great Vibes', 'Dancing Script', 'Parisienne', 'Allura', 'Sacramento', 'Alex Brush']),
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
                ])->columns(4)->columnSpanFull(),
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
     * Checklist "Section Aktif": satu Toggle per section, label sejajar toggle
     * (bawaan Filament Toggle). Dipakai Theme (default_options.sections.*.visible)
     * SEBAGAI DEFAULT, dan Invitation (theme_options.sections.*.visible) SEBAGAI
     * OVERRIDE di atasnya.
     *
     * @param  (callable(Forms\Get $get, string $key, ?\Illuminate\Database\Eloquent\Model $record): bool)|null  $defaultFor
     *         Kalau diisi, nilai awal toggle di-hydrate dari sini saat state tersimpan masih null
     *         (mis. undangan belum pernah override -> ikut default tema). $record di-inject otomatis
     *         oleh Filament -- diabaikan closure lama yang cuma deklarasi 2 argumen.
     */
    public static function sectionVisibilityFields(string $prefix, ?callable $defaultFor = null): array
    {
        return collect(self::SECTION_VISIBILITY_LABELS)->map(
            function (string $label, string $key) use ($prefix, $defaultFor) {
                $toggle = Forms\Components\Toggle::make("$prefix.sections.$key.visible")->label($label);

                if ($defaultFor) {
                    $toggle->afterStateHydrated(function (Forms\Components\Toggle $component, $state, Forms\Get $get, ?\Illuminate\Database\Eloquent\Model $record) use ($key, $defaultFor) {
                        if ($state === null) {
                            $component->state($defaultFor($get, $key, $record));
                        }
                    });
                } else {
                    $toggle->default(true);
                }

                return $toggle;
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
