<?php

namespace Database\Seeders;

use App\Core\Models\Plan;
use App\Core\Models\Subscription;
use App\Core\Models\Tenant;
use App\Core\Models\User;
use App\Modules\Invitation\Models\Gift;
use App\Modules\Invitation\Models\GalleryPhoto;
use App\Modules\Invitation\Models\GuestbookEntry;
use App\Modules\Invitation\Models\Invitation;
use App\Modules\Invitation\Models\InvitationEvent;
use App\Modules\Invitation\Models\LoveStory;
use App\Modules\Invitation\Models\Rsvp;
/*
 * perbedaan tampilan Free vs Platinum.
 *
 * Cara pakai:
 *   php artisan db:seed --class=Database\\Seeders\\InvitationDemoSeeder
 *
 * (atau tambahkan `$this->call(InvitationDemoSeeder::class);` di
 * DatabaseSeeder::run() supaya ikut ter-seed saat `php artisan migrate:fresh --seed`)
 *
 * CATATAN PENTING: kolom foto (`gallery_photos.path`, `gifts.qris_image`)
 * di sini diisi path CONTOH SAJA -- filenya tidak benar-benar ada di
 * storage. Untuk melihat galeri/QRIS tampil sungguhan, upload foto asli
 * lewat Filament (Undangan -> edit -> tab Galeri / Hadiah) setelah seeder
 * ini jalan.
 */
class InvitationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $planFree     = Plan::where('slug', 'free')->firstOrFail();
        $planPlatinum = Plan::where('slug', 'platinum')->firstOrFail();

        $themeElegant = Theme::where('component_key', 'elegant')->first();
        $themeRustic  = Theme::where('component_key', 'rustic')->first();

        // =====================================================================
        // USER 1 — paket FREE
        // =====================================================================
        $userFree = User::firstOrCreate(
            ['email' => 'demo.free@rizky-project.test'],
            ['name' => 'Budi Santoso', 'password' => 'password']
        );
        $userFree->assignRole('user');

        $tenantFree = Tenant::firstOrCreate(
            ['id' => 'demo-free'],
            ['name' => 'Budi Santoso', 'owner_user_id' => $userFree->id]
        );

        // Subscription eksplisit ke plan Free -- supaya tampil jelas di
        // Filament (Komersial -> Aktivasi Paket), bukan cuma "tidak ada baris".
        Subscription::updateOrCreate(
            ['tenant_id' => $tenantFree->id, 'plan_id' => $planFree->id],
            [
                'status'     => 'active',
                'starts_at'  => now(),
                'ends_at'    => now()->addYear(),
            ]
        );

        $invitationFree = Invitation::updateOrCreate(
            ['slug' => 'budi-siti-free'],
            [
                'tenant_id'         => $tenantFree->id,
                'theme_id'          => $themeElegant?->id,
                'groom_name'        => 'Budi',
                'bride_name'        => 'Siti',
                'groom_parents'     => 'Bpk. Hasan & Ibu Ratna',
                'bride_parents'     => 'Bpk. Umar & Ibu Wati',
                'opening_text'      => 'Dengan memohon rahmat dan ridha Allah SWT, kami bermaksud menyelenggarakan pernikahan putra-putri kami.',
                // Paket Free: tanpa musik, tanpa video, tanpa co-host (sesuai toggle plan).
                'music_url'         => null,
                'theme_options'     => [],
                'rsvp_enabled'      => true,
                'guestbook_enabled' => true,
                'status'            => 'published',
                'published_at'      => now(),
            ]
        );

        InvitationEvent::updateOrCreate(
            ['invitation_id' => $invitationFree->id, 'title' => 'Akad Nikah'],
            [
                'starts_at'  => now()->addMonths(2)->setTime(8, 0),
                'ends_at'    => now()->addMonths(2)->setTime(10, 0),
                'venue_name' => 'Masjid Al-Ikhlas',
                'address'    => 'Jl. Melati No. 10, Bandung',
                'sort_order' => 1,
            ]
        );

        // Paket Free: max_photos = 4 -> contoh 2 foto saja (di bawah kuota).
        foreach (['gallery/demo-free-1.jpg', 'gallery/demo-free-2.jpg'] as $i => $path) {
            GalleryPhoto::updateOrCreate(
                ['invitation_id' => $invitationFree->id, 'path' => $path],
                ['sort_order' => $i + 1]
            );
        }

        Rsvp::updateOrCreate(
            ['invitation_id' => $invitationFree->id, 'guest_name' => 'Andi Wijaya'],
            ['phone' => '081234567890', 'attendance' => 'attending', 'pax' => 2]
        );

        GuestbookEntry::updateOrCreate(
            ['invitation_id' => $invitationFree->id, 'guest_name' => 'Andi Wijaya'],
            ['message' => 'Selamat menempuh hidup baru, semoga sakinah mawaddah warahmah!', 'is_approved' => true]
        );

        // =====================================================================
        // USER 2 — paket PLATINUM
        // =====================================================================
        $userPlatinum = User::firstOrCreate(
            ['email' => 'demo.platinum@rizky-project.test'],
            ['name' => 'Rian Pratama', 'password' => 'password']
        );
        $userPlatinum->assignRole('user');

        $tenantPlatinum = Tenant::firstOrCreate(
            ['id' => 'demo-platinum'],
            ['name' => 'Rian Pratama', 'owner_user_id' => $userPlatinum->id]
        );

        Subscription::updateOrCreate(
            ['tenant_id' => $tenantPlatinum->id, 'plan_id' => $planPlatinum->id],
            [
                'status'    => 'active',
                'starts_at' => now(),
                'ends_at'   => now()->addYear(),
            ]
        );

        $invitationPlatinum = Invitation::updateOrCreate(
            ['slug' => 'rian-dewi-platinum'],
            [
                'tenant_id'         => $tenantPlatinum->id,
                'theme_id'          => $themeRustic?->id ?? $themeElegant?->id,
                'groom_name'        => 'Rian',
                'bride_name'        => 'Dewi',
                'groom_parents'     => 'Bpk. Joko & Ibu Sri',
                'bride_parents'     => 'Bpk. Bambang & Ibu Endah',
                'opening_text'      => 'Tanpa mengurangi rasa hormat, kami mengundang Bapak/Ibu/Saudara/i untuk berkenan hadir dan memberikan doa restu.',
                // Paket Platinum: semua fitur premium aktif.
                'music_url'         => null, // upload mp3 lewat Filament (lihat catatan di atas)
                'video_url'         => 'https://www.youtube.com/watch?v=U1XVINd-wiE',
                'co_hosts'          => ['Kel. Besar Bapak Hendra', 'Kel. Besar Ibu Ningsih'],
                'theme_options'     => [
                    'colors' => ['accent' => '#8A5A33', 'paper' => '#FBF6EF', 'ink' => '#3B2A1A'],
                ],
                'rsvp_enabled'      => true,
                'guestbook_enabled' => true,
                'status'            => 'published',
                'published_at'      => now(),
            ]
        );

        foreach ([
            ['title' => 'Akad Nikah', 'jam_mulai' => 8,  'jam_selesai' => 10, 'venue' => 'Masjid Agung Al-Furqon', 'alamat' => 'Jl. Sudirman No. 5, Jakarta'],
            ['title' => 'Resepsi',    'jam_mulai' => 11, 'jam_selesai' => 15, 'venue' => 'Gedung Serbaguna Graha Mitra', 'alamat' => 'Jl. Gatot Subroto No. 88, Jakarta'],
        ] as $i => $e) {
            InvitationEvent::updateOrCreate(
                ['invitation_id' => $invitationPlatinum->id, 'title' => $e['title']],
                [
                    'starts_at'  => now()->addMonths(3)->setTime($e['jam_mulai'], 0),
                    'ends_at'    => now()->addMonths(3)->setTime($e['jam_selesai'], 0),
                    'venue_name' => $e['venue'],
                    'address'    => $e['alamat'],
                    'sort_order' => $i + 1,
                ]
            );
        }

        LoveStory::updateOrCreate(
            ['invitation_id' => $invitationPlatinum->id, 'title' => 'Pertama Bertemu'],
            [
                'happened_at' => now()->subYears(3),
                'story'       => 'Kami pertama bertemu di sebuah acara kampus dan langsung merasa cocok satu sama lain.',
                'sort_order'  => 1,
            ]
        );
        LoveStory::updateOrCreate(
            ['invitation_id' => $invitationPlatinum->id, 'title' => 'Lamaran'],
            [
                'happened_at' => now()->subMonths(6),
                'story'       => 'Setelah tiga tahun menjalin hubungan, Rian resmi melamar Dewi di depan keluarga besar.',
                'sort_order'  => 2,
            ]
        );

        // Paket Platinum: max_photos = 60 -> contoh 6 foto.
        foreach (range(1, 6) as $i) {
            GalleryPhoto::updateOrCreate(
                ['invitation_id' => $invitationPlatinum->id, 'path' => "gallery/demo-platinum-{$i}.jpg"],
                ['sort_order' => $i]
            );
        }

        Gift::updateOrCreate(
            ['invitation_id' => $invitationPlatinum->id, 'type' => 'bank'],
            ['provider' => 'BCA', 'account_name' => 'Rian Pratama', 'account_number' => '1234567890']
        );
        Gift::updateOrCreate(
            ['invitation_id' => $invitationPlatinum->id, 'type' => 'qris'],
            ['provider' => 'QRIS', 'qris_image' => 'qris/demo-platinum-qris.jpg']
        );

        foreach ([
            ['name' => 'Fajar Nugroho', 'attendance' => 'attending', 'pax' => 2],
            ['name' => 'Lina Marlina',  'attendance' => 'maybe',     'pax' => 1],
        ] as $r) {
            Rsvp::updateOrCreate(
                ['invitation_id' => $invitationPlatinum->id, 'guest_name' => $r['name']],
                ['attendance' => $r['attendance'], 'pax' => $r['pax']]
            );
        }

        GuestbookEntry::updateOrCreate(
            ['invitation_id' => $invitationPlatinum->id, 'guest_name' => 'Fajar Nugroho'],
            ['message' => 'Selamat menempuh hidup baru Rian & Dewi, bahagia selalu!', 'is_approved' => true]
        );

        // =====================================================================
        // Ringkasan supaya gampang dicek di terminal setelah seeding
        // =====================================================================
        $this->command?->info('Demo undangan berhasil dibuat:');
        $this->command?->info("  FREE     -> /i/{$invitationFree->slug}  (login: demo.free@rizky-project.test / password)");
        $this->command?->info("  PLATINUM -> /i/{$invitationPlatinum->slug}  (login: demo.platinum@rizky-project.test / password)");
    
         $matrix = [
            'free' => [
                'gallery_enabled' => true,  'love_story_enabled' => false, 'gift_enabled' => false,
                'countdown_enabled' => false, 'video_enabled' => false, 'co_host_enabled' => false,
                'maps_enabled' => false, 'custom_font_enabled' => false,
                'custom_background_enabled' => false, 'custom_ornament_enabled' => false,
                'music_enabled' => false, 'remove_branding' => false, 'custom_domain' => false,
                'max_love_stories' => 0,
            ],
            'premium' => [
                'gallery_enabled' => true, 'love_story_enabled' => true, 'gift_enabled' => true,
                'countdown_enabled' => true, 'video_enabled' => true, 'co_host_enabled' => true,
                'maps_enabled' => true, 'custom_font_enabled' => false,
                'custom_background_enabled' => false, 'custom_ornament_enabled' => false,
                'music_enabled' => true, 'remove_branding' => true, 'custom_domain' => false,
                'max_love_stories' => 5,
            ],
            'platinum' => [
                'gallery_enabled' => true, 'love_story_enabled' => true, 'gift_enabled' => true,
                'countdown_enabled' => true, 'video_enabled' => true, 'co_host_enabled' => true,
                'maps_enabled' => true, 'custom_font_enabled' => true,
                'custom_background_enabled' => true, 'custom_ornament_enabled' => true,
                'music_enabled' => true, 'remove_branding' => true, 'custom_domain' => true,
                'max_love_stories' => 99,
            ],
        ];

        foreach ($matrix as $slug => $features) {
            Plan::where('slug', $slug)->update($features);
        }

        $this->command->info('Toggle fitur per paket ter-update (free/premium/platinum).');
   
    
    }
}