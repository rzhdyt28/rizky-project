<?php

namespace Database\Seeders;

use App\Core\Models\{Plan, Subscription, Tenant, User};
use App\Modules\Invitation\Models\{
    GalleryPhoto, Gift, GuestbookEntry, Invitation, InvitationEvent, LoveStory, Theme
};
use Illuminate\Database\Seeder;

/**
 * Seeder KHUSUS untuk uji coba tema Mildness.
 *
 * Sengaja dipisah dari DemoContentSeeder (yang mengunci undangan 'reza-mega'
 * ke tema Elegant secara hardcode). Kalau tema Mildness ditaruh di seeder yang
 * sama, menjalankan ulang DemoContentSeeder akan mengembalikan undangan uji
 * ke Elegant lagi — dengan file ini, keduanya tidak saling menimpa.
 *
 * PENTING: theme_options TIDAK diisi sama sekali di sini, supaya tidak ada
 * override warna yang menimpa default_options tema Mildness (lihat catatan
 * di 2.2 dokumentasi: theme_options undangan di-merge di atas default_options
 * tema lewat array_replace_recursive di PublicInvitationController).
 *
 * Jalankan terpisah, tidak lewat DatabaseSeeder utama:
 *   php artisan db:seed --class=Database\\Seeders\\MildnessDemoSeeder
 *
 * Aman dijalankan berkali-kali (semua updateOrCreate/firstOrCreate).
 */
class MildnessThemes extends Seeder
{
    public function run(): void
    {
        // ================================================================
        // 1. Pastikan tema Mildness ada. Kalau DatabaseSeeder utama belum
        //    pernah dijalankan, baris ini yang membuatnya (bukan menduplikasi
        //    kalau sudah ada, karena component_key jadi kunci updateOrCreate).
        // ================================================================
        $themeMildness = Theme::updateOrCreate(
            ['component_key' => 'mildness'],
            [
                'name'            => 'Mildness',
                'tier'            => 'premium',
                'is_active'       => true,
                'default_options' => [
                    'colors' => [
                        'accent'      => '#3F5B7C',
                        'paper'       => '#EDF1F5',
                        'ink'         => '#46586A',
                        'gold'        => '#8AA1BC',
                        'button_bg'   => '#3F5B7C',
                        'button_text' => '#FFFFFF',
                    ],
                    'fonts' => [
                        'heading' => 'Cormorant Garamond',
                        'body'    => 'Jost',
                        'script'  => 'Great Vibes',
                    ],
                ],
            ]
        );

        // ================================================================
        // 2. Tenant uji coba TERPISAH dari tenant demo utama ('rizky-demo'),
        //    supaya tidak bentrok kalau DemoContentSeeder dijalankan ulang.
        // ================================================================
        $user = User::firstOrCreate(
            ['email' => 'mildness-demo@test.com'],
            ['name' => 'Mildness Demo', 'password' => 'password123']
        );
        if (method_exists($user, 'assignRole') && ! $user->hasRole('user')) {
            $user->assignRole('user');
        }

        $tenant = Tenant::firstOrCreate(
            ['id' => 'mildness-demo'],
            ['name' => 'Mildness Demo', 'owner_user_id' => $user->id]
        );

        // Subscription aktif paket Premium (Mildness bertier 'premium', jadi
        // fitur toggle-nya perlu subscription yang mencakup itu). Fallback
        // ke Free kalau plan Premium belum ada di seeder plan Anda.
        $plan = Plan::where('slug', 'premium')->first() ?? Plan::where('slug', 'free')->first();
        if ($plan) {
            Subscription::updateOrCreate(
                ['tenant_id' => $tenant->id, 'plan_id' => $plan->id],
                ['status' => 'active', 'starts_at' => now(), 'ends_at' => now()->addYear()]
            );
        }

        // ================================================================
        // 3. Undangan uji coba — theme_id LANGSUNG ke Mildness,
        //    theme_options SENGAJA tidak diisi (null) supaya tidak ada
        //    override warna yang menimpa default_options tema.
        // ================================================================
        $invitation = Invitation::updateOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'via-andra'],
            [
                'theme_id'          => $themeMildness->id,
                'groom_name'        => 'Andra',
                'bride_name'        => 'Via',
                'groom_parents'     => 'Bpk. Hendra & Ibu Laela',
                'bride_parents'     => 'Bpk. Danang & Ibu Siti',
                'opening_text'      => 'Tanpa mengurangi rasa hormat, kami mengundang Bapak/Ibu/Saudara/i untuk berkenan hadir dan memberikan doa restu.',
                'rsvp_enabled'      => true,
                'guestbook_enabled' => true,
                'status'            => 'published',
                'published_at'      => now(),
            ]
        );

        InvitationEvent::updateOrCreate(
            ['invitation_id' => $invitation->id, 'title' => 'Akad Nikah'],
            [
                'starts_at'  => now()->addDays(91)->setTime(16, 0),
                'ends_at'    => now()->addDays(91)->setTime(17, 0),
                'venue_name' => 'Maximo Resto & Garden',
                'address'    => 'Jl. Dr. Setiabudi No. 378',
                'maps_url'   => 'https://maps.google.com/?q=Maximo+Resto+Garden',
                'sort_order' => 1,
            ]
        );

        InvitationEvent::updateOrCreate(
            ['invitation_id' => $invitation->id, 'title' => 'Resepsi'],
            [
                'starts_at'  => now()->addDays(91)->setTime(18, 30),
                'ends_at'    => now()->addDays(91)->setTime(20, 30),
                'venue_name' => 'Maximo Resto & Garden',
                'address'    => 'Jl. Dr. Setiabudi No. 378',
                'maps_url'   => 'https://maps.google.com/?q=Maximo+Resto+Garden',
                'sort_order' => 2,
            ]
        );

        LoveStory::updateOrCreate(
            ['invitation_id' => $invitation->id, 'title' => 'Pertemuan Pertama'],
            [
                'happened_at' => now()->subYears(3),
                'story'       => 'Pertama kali kami bertemu saat menjadi anggota sebuah organisasi di kampus. Kebetulan kami berada di divisi yang sama, yang menjadikan kami lebih akrab.',
                'sort_order'  => 1,
            ]
        );

        // Catatan: path di bawah ini placeholder (sama seperti pola di
        // DemoContentSeeder) — file fisiknya tidak otomatis ada, jadi foto
        // tidak akan tampil sampai Anda unggah foto asli lewat Filament
        // (GalleryPhotosRelationManager) atau ganti path ke file yang nyata.
        GalleryPhoto::updateOrCreate(
            ['invitation_id' => $invitation->id, 'caption' => 'Prewedding 1'],
            ['path' => 'demo/mildness-prewedding-1.jpg', 'sort_order' => 1]
        );

        GalleryPhoto::updateOrCreate(
            ['invitation_id' => $invitation->id, 'caption' => 'Prewedding 2'],
            ['path' => 'demo/mildness-prewedding-2.jpg', 'sort_order' => 2]
        );

        Gift::updateOrCreate(
            ['invitation_id' => $invitation->id, 'type' => 'bank', 'provider' => 'BCA'],
            ['account_name' => 'Andra Pratama', 'account_number' => '1234567890']
        );

        GuestbookEntry::updateOrCreate(
            ['invitation_id' => $invitation->id, 'guest_name' => 'Lia'],
            ['message' => 'Selamat menempuh hidup baru, Via & Andra!', 'is_approved' => true]
        );

        $this->command->info('Mildness demo siap!');
        $this->command->info('Undangan: GET /api/invitation/p/via-andra');
        $this->command->info('Publik  : http://<domain-anda>/i/via-andra (sesuaikan route Vue Anda)');
    }
}