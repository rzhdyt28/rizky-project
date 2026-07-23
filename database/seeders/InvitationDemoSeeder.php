<?php

namespace Database\Seeders;

use App\Core\Models\Plan;
use App\Core\Models\Subscription;
use App\Core\Models\Tenant;
use App\Core\Models\User;
use App\Modules\Invitation\Models\GalleryPhoto;
use App\Modules\Invitation\Models\Gift;
use App\Modules\Invitation\Models\Guest;
use App\Modules\Invitation\Models\GuestbookEntry;
use App\Modules\Invitation\Models\Invitation;
use App\Modules\Invitation\Models\InvitationEvent;
use App\Modules\Invitation\Models\LoveStory;
use App\Modules\Invitation\Models\Rsvp;
use App\Modules\Invitation\Models\Theme;
use App\Modules\Invitation\Support\InvitationThemeProvisioner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Undangan demo LENGKAP & VARIATIF — 4 pasangan, sengaja beda kombinasi
 * gaya section (hero/couple/countdown/kisah/galeri) supaya semua varian
 * gaya yang ada di ThemeOptionsSchema teruji sekaligus lewat data nyata.
 * Foto dari public/storage/undangan/example/ (bride.jpg, groom.jpg, image1-10.jpg,
 * akad-background.webp, resepsi-background.webp, QRIS.png) -- disimpan
 * sebagai path relatif ke disk 'public' (mis. "undangan/example/bride.jpg"), TIDAK
 * disalin ke folder lain.
 *
 * PENTING (v4 arsitektur): pengaturan visual TIDAK LAGI ditulis ke
 * Invitation.theme_options (kolom itu sudah tidak dibaca lagi oleh
 * PublicInvitationController) -- setiap undangan dapat CHILD THEME sendiri
 * lewat InvitationThemeProvisioner, dan visual options masuk ke
 * default_options child theme itu. Lihat InvitationLookResource untuk form
 * admin yang setara.
 *
 * Aman dijalankan berkali-kali (updateOrCreate/firstOrCreate semua,
 * termasuk reuse child theme yang sudah ada alih-alih bikin baru).
 *   php artisan db:seed --class=Database\\Seeders\\InvitationDemoSeeder
 */
class InvitationDemoSeeder extends Seeder
{
    /** Semua path relatif ke disk 'public' (storage/app/public/undangan/example/...). */
    private const GALLERY = [
        'undangan/example/image1.jpg', 'undangan/example/image2.jpg', 'undangan/example/image3.jpg', 'undangan/example/image4.jpg',
        'undangan/example/image5.jpg', 'undangan/example/image6.jpg', 'undangan/example/image7.jpg', 'undangan/example/image8.jpg',
        'undangan/example/image9.jpg', 'undangan/example/image10.jpg',
    ];

    private const HERO_PHOTO = 'undangan/example/about.jpg';
    private const BRIDE_PHOTO = 'undangan/example/bride.jpg';
    private const GROOM_PHOTO = 'undangan/example/groom.jpg';
    private const AKAD_BG = 'undangan/example/akad-background.webp';
    private const RESEPSI_BG = 'undangan/example/resepsi-background.webp';
    private const QRIS = 'undangan/example/QRIS.png';

    /**
     * Satu baris = satu undangan demo, SENGAJA beda kombinasi gaya section
     * supaya semua varian di ThemeOptionsSchema teruji lewat data nyata.
     * theme_key harus match component_key Theme dasar yang sudah di-seed
     * DatabaseSeeder (mildness/senja).
     */
    private const COUPLES = [
        [
            'groom' => 'Andra', 'bride' => 'Via', 'theme_key' => 'mildness',
            'groom_parents' => 'Bpk. Hendra & Ibu Laela', 'bride_parents' => 'Bpk. Danang & Ibu Siti',
            'hero_style' => 'classic', 'couple_style' => 'classic',
            'countdown_style' => 'circle', 'countdown_layout' => 'simple',
            'love_story_style' => 'stacked', 'gallery_style' => 'carousel', 'events_style' => 'card',
        ],
        [
            'groom' => 'Rian', 'bride' => 'Dewi', 'theme_key' => 'senja',
            'groom_parents' => 'Bpk. Joko & Ibu Sri', 'bride_parents' => 'Bpk. Bambang & Ibu Endah',
            'hero_style' => 'classic', 'couple_style' => 'cards',
            'countdown_style' => 'boxed', 'countdown_layout' => 'photo',
            'love_story_style' => 'timeline', 'gallery_style' => 'grid', 'events_style' => 'elegant',
        ],
        [
            'groom' => 'Fajar', 'bride' => 'Lestari', 'theme_key' => 'mildness',
            'groom_parents' => 'Bpk. Wahyu & Ibu Ningsih', 'bride_parents' => 'Bpk. Agus & Ibu Rahma',
            'hero_style' => 'framed', 'couple_style' => 'circle',
            'countdown_style' => 'pill', 'countdown_layout' => 'date',
            'love_story_style' => 'alternate', 'gallery_style' => 'masonry', 'events_style' => 'timeline',
        ],
        [
            'groom' => 'Bima', 'bride' => 'Sekar', 'theme_key' => 'mildness',
            'groom_parents' => 'Bpk. Yusuf & Ibu Dian', 'bride_parents' => 'Bpk. Hendro & Ibu Wulan',
            'hero_style' => 'split', 'couple_style' => 'arch',
            'countdown_style' => 'flip', 'countdown_layout' => 'quote',
            'love_story_style' => 'polaroid', 'gallery_style' => 'floating', 'events_style' => 'minimal',
        ],
    ];

    public function run(): void
    {
        $planPlatinum = Plan::where('slug', 'platinum')->first();
        if (! $planPlatinum) {
            $this->command?->warn('Plan Platinum belum ada — jalankan DatabaseSeeder dulu. Dibatalkan.');

            return;
        }

        $themesByKey = Theme::whereNull('invitation_id')->get()->keyBy('component_key');
        $provisioner = app(InvitationThemeProvisioner::class);

        foreach (self::COUPLES as $i => $couple) {
            $baseTheme = $themesByKey->get($couple['theme_key']);
            if (! $baseTheme) {
                $this->command?->warn("Tema dasar '{$couple['theme_key']}' belum ada — lewati {$couple['groom']} & {$couple['bride']}.");

                continue;
            }

            $this->seedCouple($couple, $i, $baseTheme, $planPlatinum, $provisioner);
        }

        $this->command?->info('Undangan demo (' . count(self::COUPLES) . ') ter-seed. Login pakai password: "password".');
    }

    private function seedCouple(array $couple, int $i, Theme $baseTheme, Plan $plan, InvitationThemeProvisioner $provisioner): void
    {
        $slug  = Str::slug("{$couple['groom']}-{$couple['bride']}");
        $email = "{$slug}@rizky-project.test";

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => "{$couple['groom']} & {$couple['bride']}", 'password' => 'password']
        );
        if (method_exists($user, 'hasRole') && ! $user->hasRole('user')) {
            $user->assignRole('user');
        }

        $tenant = Tenant::firstOrCreate(
            ['id' => "demo-{$slug}"],
            ['name' => "{$couple['groom']} & {$couple['bride']}", 'owner_user_id' => $user->id]
        );

        Subscription::updateOrCreate(
            ['tenant_id' => $tenant->id, 'plan_id' => $plan->id],
            ['status' => 'active', 'starts_at' => now(), 'ends_at' => now()->addYear()]
        );

        // ---- Invitation: MURNI DATA (v4 arsitektur, lihat InvitationResource) ----
        $invitation = Invitation::updateOrCreate(
            ['slug' => $slug],
            [
                'tenant_id'         => $tenant->id,
                'groom_name'        => $couple['groom'],
                'bride_name'        => $couple['bride'],
                'groom_parents'     => $couple['groom_parents'],
                'bride_parents'     => $couple['bride_parents'],
                'opening_text'      => 'Tanpa mengurangi rasa hormat, kami mengundang Bapak/Ibu/Saudara/i untuk berkenan hadir dan memberikan doa restu kepada kedua mempelai.',
                'video_url'         => 'https://www.youtube.com/watch?v=U1XVINd-wiE',
                'co_hosts'          => [
                    ['name' => 'Kel. Besar Bapak Hendra', 'side' => 'pria'],
                    ['name' => 'Kel. Besar Ibu Ningsih', 'side' => 'wanita'],
                ],
                'rsvp_enabled'      => true,
                'guestbook_enabled' => true,
                'status'            => 'published',
                'published_at'      => now(),
            ]
        );

        // ---- Child theme privat (pengganti Invitation.theme_options lama) ----
        $childTheme = $invitation->theme?->invitation_id === $invitation->id
            ? $invitation->theme
            : $provisioner->provision($invitation, $baseTheme);

        if ($invitation->theme_id !== $childTheme->id) {
            $invitation->update(['theme_id' => $childTheme->id]);
        }

        $childTheme->update([
            'default_options' => [
                'layout'   => ['section_height' => 'smart'],
                'hero' => [
                    'style'             => $couple['hero_style'],
                    'slideshow'         => [self::HERO_PHOTO],
                    'dresscode_enabled' => true,
                    'dresscode'         => 'Batik / Nuansa Earth Tone',
                ],
                'couple' => [
                    'style'       => $couple['couple_style'],
                    'show_photos' => true,
                    'groom_photo' => self::GROOM_PHOTO,
                    'bride_photo' => self::BRIDE_PHOTO,
                ],
                'events' => ['style' => $couple['events_style']],
                'countdown' => [
                    'style'  => $couple['countdown_style'],
                    'layout' => $couple['countdown_layout'],
                    'photo'  => $i % 2 === 0 ? self::AKAD_BG : self::RESEPSI_BG,
                ],
                'love_story' => [
                    'show_photos' => true,
                    'style'       => $couple['love_story_style'],
                ],
                'gallery' => ['style' => $couple['gallery_style']],
                'video'   => [
                    'eyebrow' => 'Wedding Film',
                    'caption' => 'Sepenggal momen perjalanan kami menuju hari bahagia. Selamat menyaksikan.',
                ],
            ],
        ]);

        // ---- Konten relasional (tidak berubah dari pola lama) ----
        foreach ([
            ['title' => 'Akad Nikah', 'jam_mulai' => 8, 'jam_selesai' => 10, 'venue' => 'Masjid Agung Al-Furqon', 'alamat' => 'Jl. Sudirman No. 5, Jakarta'],
            ['title' => 'Resepsi', 'jam_mulai' => 11, 'jam_selesai' => 15, 'venue' => 'Gedung Serbaguna Graha Mitra', 'alamat' => 'Jl. Gatot Subroto No. 88, Jakarta'],
        ] as $j => $e) {
            InvitationEvent::updateOrCreate(
                ['invitation_id' => $invitation->id, 'title' => $e['title']],
                [
                    'starts_at'  => now()->addMonths(3)->addDays($i)->setTime($e['jam_mulai'], 0),
                    'ends_at'    => now()->addMonths(3)->addDays($i)->setTime($e['jam_selesai'], 0),
                    'venue_name' => $e['venue'],
                    'address'    => $e['alamat'],
                    'maps_url'   => 'https://maps.google.com/?q=' . urlencode($e['venue']),
                    'sort_order' => $j + 1,
                ]
            );
        }

        foreach ([
            ['title' => 'Pertama Bertemu', 'tahun' => 3, 'story' => 'Kami pertama bertemu di sebuah acara kampus dan langsung merasa cocok satu sama lain.'],
            ['title' => 'Menjalin Hubungan', 'tahun' => 2, 'story' => 'Setelah beberapa bulan dekat, kami memutuskan untuk menjalani hubungan yang lebih serius.'],
            ['title' => 'Lamaran', 'tahun' => 0.5, 'story' => 'Di depan keluarga besar, lamaran resmi diucapkan dan direstui kedua belah pihak.'],
        ] as $j => $s) {
            LoveStory::updateOrCreate(
                ['invitation_id' => $invitation->id, 'title' => $s['title']],
                [
                    'happened_at' => now()->subYears((int) floor($s['tahun']))->subMonths((int) round(($s['tahun'] - floor($s['tahun'])) * 12)),
                    'story'       => $s['story'],
                    'photo'       => self::GALLERY[($i + $j) % count(self::GALLERY)],
                    'sort_order'  => $j + 1,
                ]
            );
        }

        foreach (range(0, 5) as $j) {
            GalleryPhoto::updateOrCreate(
                ['invitation_id' => $invitation->id, 'path' => self::GALLERY[($i + $j) % count(self::GALLERY)]],
                ['sort_order' => $j + 1]
            );
        }

        Gift::updateOrCreate(
            ['invitation_id' => $invitation->id, 'type' => 'bank'],
            ['provider' => 'BCA', 'account_name' => "{$couple['groom']} {$couple['bride']}", 'account_number' => '1234567890' . $i]
        );
        Gift::updateOrCreate(
            ['invitation_id' => $invitation->id, 'type' => 'ewallet'],
            ['provider' => 'GoPay', 'account_name' => $couple['groom'], 'account_number' => '08123456' . str_pad((string) $i, 3, '0', STR_PAD_LEFT)]
        );
        Gift::updateOrCreate(
            ['invitation_id' => $invitation->id, 'type' => 'qris'],
            ['provider' => 'QRIS', 'qris_image' => self::QRIS]
        );
        Gift::updateOrCreate(
            ['invitation_id' => $invitation->id, 'type' => 'address'],
            ['shipping_address' => 'Jl. Melati No. 10, Bandung (u.p. ' . $couple['bride'] . ')']
        );

        foreach ([
            ['name' => 'Andi Wijaya', 'attendance' => 'attending', 'pax' => 2],
            ['name' => 'Fajar Nugroho', 'attendance' => 'attending', 'pax' => 1],
            ['name' => 'Lina Marlina', 'attendance' => 'maybe', 'pax' => 1],
            ['name' => 'Sari Dewanti', 'attendance' => 'not_attending', 'pax' => 0],
        ] as $r) {
            Rsvp::updateOrCreate(
                ['invitation_id' => $invitation->id, 'guest_name' => $r['name']],
                ['phone' => '0812345678' . random_int(10, 99), 'attendance' => $r['attendance'], 'pax' => $r['pax']]
            );
        }

        foreach (['Andi Wijaya', 'Fajar Nugroho', 'Sari Dewanti'] as $name) {
            Guest::updateOrCreate(
                ['invitation_id' => $invitation->id, 'name' => $name],
                ['phone' => '0812345678' . random_int(10, 99), 'note' => 'Undangan digital via WA']
            );
        }

        foreach ([
            "Selamat menempuh hidup baru {$couple['groom']} & {$couple['bride']}, semoga sakinah mawaddah warahmah!",
            'Bahagia selalu untuk kalian berdua, semoga langgeng sampai kakek nenek!',
        ] as $j => $msg) {
            GuestbookEntry::updateOrCreate(
                ['invitation_id' => $invitation->id, 'guest_name' => $j === 0 ? 'Andi Wijaya' : 'Fajar Nugroho'],
                ['message' => $msg, 'is_approved' => true]
            );
        }

        $this->command?->info("  {$baseTheme->name} -> /i/{$invitation->slug}  (login: {$email} / password)");
    }
}
