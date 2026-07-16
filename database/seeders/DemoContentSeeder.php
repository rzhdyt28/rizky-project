<?php

namespace Database\Seeders;

use App\Core\Models\{Plan, Subscription, Tenant, User};
use App\Modules\Invitation\Models\{
    GalleryPhoto, Gift, GuestbookEntry, Invitation, InvitationEvent, LoveStory, Theme
};
use App\Modules\Portfolio\Models\{
    ContactMessage, Education, Experience, Profile, Skill
};
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        // ================================================================
        // 1. USER + TENANT DEMO
        // ================================================================
        $user = User::firstOrCreate(
            ['email' => 'rizky@test.com'],
            ['name' => 'Rizky', 'password' => 'password123']
        );
        $user->assignRole('user');

        $tenant = Tenant::firstOrCreate(
            ['id' => 'rizky-demo'],
            ['name' => 'Rizky', 'owner_user_id' => $user->id]
        );

        // Subscription aktif paket Free — tanpa ini modul Invitation akan 402
        $free = Plan::where('slug', 'free')->first();
        if ($free) {
            Subscription::firstOrCreate(
                ['tenant_id' => $tenant->id, 'plan_id' => $free->id],
                ['status' => 'active', 'starts_at' => now(), 'ends_at' => now()->addYear()]
            );
        }

        // ================================================================
        // 2. PORTOFOLIO
        // ================================================================
        Profile::updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'full_name' => 'Rizky Hidayat',
                'headline'  => [
                    'id' => 'IT Support & Network Engineer',
                    'en' => 'IT Support & Network Engineer',
                ],
                'about' => [
                    'id' => 'Berpengalaman menangani infrastruktur jaringan, troubleshooting hardware/software, dan dukungan teknis untuk perusahaan skala menengah.',
                    'en' => 'Experienced in handling network infrastructure, hardware/software troubleshooting, and technical support for mid-size companies.',
                ],
                'location' => 'Jakarta, Indonesia',
                'socials'  => [
                    'email'    => 'rizky@test.com',
                    'whatsapp' => '6281234567890',
                    'linkedin' => 'linkedin.com/in/rizkyhidayat',
                    'github'   => 'github.com/rzhdyt28',
                ],
            ]
        );

        $skills = [
            ['category' => 'it-support', 'title_id' => 'IT Support', 'title_en' => 'IT Support',
             'desc_id' => 'Troubleshooting hardware, software, dan jaringan kantor.',
             'desc_en' => 'Hardware, software, and office network troubleshooting.', 'sort' => 1],
            ['category' => 'networking', 'title_id' => 'Jaringan Komputer', 'title_en' => 'Networking',
             'desc_id' => 'Konfigurasi router, switch, dan VPN untuk kebutuhan kantor.',
             'desc_en' => 'Router, switch, and VPN configuration for office needs.', 'sort' => 2],
            ['category' => 'programming', 'title_id' => 'Pemrograman Dasar', 'title_en' => 'Basic Programming',
             'desc_id' => 'PHP, Laravel, dan otomasi skrip sederhana.',
             'desc_en' => 'PHP, Laravel, and simple script automation.', 'sort' => 3],
            ['category' => 'cloud', 'title_id' => 'Cloud & Server', 'title_en' => 'Cloud & Server',
             'desc_id' => 'Deploy dan maintain aplikasi di VPS Linux.',
             'desc_en' => 'Deploying and maintaining applications on Linux VPS.', 'sort' => 4],
        ];
        foreach ($skills as $s) {
            Skill::updateOrCreate(
                ['tenant_id' => $tenant->id, 'category' => $s['category']],
                [
                    'title'       => ['id' => $s['title_id'], 'en' => $s['title_en']],
                    'description' => ['id' => $s['desc_id'], 'en' => $s['desc_en']],
                    'sort_order'  => $s['sort'],
                ]
            );
        }

        Experience::updateOrCreate(
            ['tenant_id' => $tenant->id, 'company' => 'PT Teknologi Nusantara'],
            [
                'role'       => ['id' => 'IT Support Staff', 'en' => 'IT Support Staff'],
                'location'   => 'Jakarta',
                'start_date' => '2023-01-01',
                'end_date'   => null,
                'bullets'    => [
                    ['id' => 'Menangani troubleshooting jaringan kantor harian', 'en' => 'Handled daily office network troubleshooting'],
                    ['id' => 'Mengelola 50+ endpoint komputer karyawan', 'en' => 'Managed 50+ employee computer endpoints'],
                ],
                'sort_order' => 1,
            ]
        );

        Experience::updateOrCreate(
            ['tenant_id' => $tenant->id, 'company' => 'CV Solusi Digital'],
            [
                'role'       => ['id' => 'Magang IT', 'en' => 'IT Intern'],
                'location'   => 'Bandung',
                'start_date' => '2022-01-01',
                'end_date'   => '2022-12-31',
                'bullets'    => [
                    ['id' => 'Membantu instalasi dan konfigurasi jaringan cabang baru', 'en' => 'Assisted new branch network installation and setup'],
                ],
                'sort_order' => 2,
            ]
        );

        Education::updateOrCreate(
            ['tenant_id' => $tenant->id, 'institution' => 'Universitas Teknologi Indonesia', 'kind' => 'education'],
            [
                'degree'     => ['id' => 'S1 Teknik Informatika', 'en' => 'Bachelor of Computer Science'],
                'period'     => '2019 - 2023',
                'gpa'        => '3.6',
                'sort_order' => 1,
            ]
        );

        Education::updateOrCreate(
            ['tenant_id' => $tenant->id, 'institution' => 'Cisco Networking Academy', 'kind' => 'certification'],
            [
                'degree'     => ['id' => 'CCNA - Cisco Certified Network Associate', 'en' => 'CCNA - Cisco Certified Network Associate'],
                'period'     => '2023',
                'sort_order' => 1,
            ]
        );

        ContactMessage::updateOrCreate(
            ['tenant_id' => $tenant->id, 'sender_email' => 'klien@contoh.com'],
            [
                'sender_name' => 'Budi Santoso',
                'message'     => 'Halo, saya tertarik dengan jasa IT support Anda. Bisa diskusi lebih lanjut?',
                'is_read'     => false,
            ]
        );

        // ================================================================
        // 3. UNDANGAN
        // ================================================================
        $themeElegant = Theme::where('component_key', 'elegant')->first();

        $invitation = Invitation::updateOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'reza-mega'],
            [
                'theme_id'          => $themeElegant?->id,
                'groom_name'        => 'Reza',
                'bride_name'        => 'Mega',
                'groom_parents'     => 'Bpk. Ahmad & Ibu Siti',
                'bride_parents'     => 'Bpk. Joko & Ibu Ani',
                'opening_text'      => 'Dengan memohon rahmat dan ridha Allah SWT, kami bermaksud menyelenggarakan pernikahan putra-putri kami.',
                'rsvp_enabled'      => true,
                'guestbook_enabled' => true,
                'status'            => 'published',
                'published_at'      => now(),
            ]
        );

        InvitationEvent::updateOrCreate(
            ['invitation_id' => $invitation->id, 'title' => 'Akad Nikah'],
            [
                'starts_at'  => now()->addMonth()->setTime(8, 0),
                'ends_at'    => now()->addMonth()->setTime(9, 30),
                'venue_name' => 'Masjid Al-Ikhlas',
                'address'    => 'Jl. Merdeka No. 10, Jakarta Selatan',
                'maps_url'   => 'https://maps.google.com/?q=Masjid+Al-Ikhlas+Jakarta',
                'sort_order' => 1,
            ]
        );

        InvitationEvent::updateOrCreate(
            ['invitation_id' => $invitation->id, 'title' => 'Resepsi'],
            [
                'starts_at'  => now()->addMonth()->setTime(11, 0),
                'ends_at'    => now()->addMonth()->setTime(14, 0),
                'venue_name' => 'Gedung Serbaguna Graha Utama',
                'address'    => 'Jl. Sudirman No. 25, Jakarta Pusat',
                'maps_url'   => 'https://maps.google.com/?q=Graha+Utama+Jakarta',
                'sort_order' => 2,
            ]
        );

        LoveStory::updateOrCreate(
            ['invitation_id' => $invitation->id, 'title' => 'Awal Bertemu'],
            [
                'happened_at' => now()->subYears(3),
                'story'       => 'Kami pertama bertemu di sebuah acara kampus tahun 2023, dan sejak itu sering berkomunikasi.',
                'sort_order'  => 1,
            ]
        );

        LoveStory::updateOrCreate(
            ['invitation_id' => $invitation->id, 'title' => 'Lamaran'],
            [
                'happened_at' => now()->subMonths(6),
                'story'       => 'Setelah menjalin hubungan selama 2 tahun, Reza melamar Mega di depan keluarga besar.',
                'sort_order'  => 2,
            ]
        );

        GalleryPhoto::updateOrCreate(
            ['invitation_id' => $invitation->id, 'caption' => 'Prewedding di Pantai'],
            ['path' => 'demo/prewedding-1.jpg', 'sort_order' => 1]
        );

        GalleryPhoto::updateOrCreate(
            ['invitation_id' => $invitation->id, 'caption' => 'Prewedding di Taman'],
            ['path' => 'demo/prewedding-2.jpg', 'sort_order' => 2]
        );

        Gift::updateOrCreate(
            ['invitation_id' => $invitation->id, 'type' => 'bank', 'provider' => 'BCA'],
            ['account_name' => 'Reza Pratama', 'account_number' => '1234567890']
        );

        Gift::updateOrCreate(
            ['invitation_id' => $invitation->id, 'type' => 'ewallet', 'provider' => 'OVO'],
            ['account_name' => 'Reza Pratama', 'account_number' => '081234567890']
        );

        Gift::updateOrCreate(
            ['invitation_id' => $invitation->id, 'type' => 'address'],
            ['shipping_address' => 'Jl. Contoh No. 1, RT 01/RW 02, Jakarta Selatan, 12345']
        );

        GuestbookEntry::updateOrCreate(
            ['invitation_id' => $invitation->id, 'guest_name' => 'Andi Wijaya'],
            ['message' => 'Selamat menempuh hidup baru! Semoga langgeng sampai kakek nenek.', 'is_approved' => true]
        );

        GuestbookEntry::updateOrCreate(
            ['invitation_id' => $invitation->id, 'guest_name' => 'Siti Rahma'],
            ['message' => 'Barakallahu lakuma, semoga menjadi keluarga sakinah mawaddah warahmah.', 'is_approved' => true]
        );

        $this->command->info('Demo data siap!');
        $this->command->info('Login: rizky@test.com / password123');
        $this->command->info('Portofolio: GET /api/portfolio');
        $this->command->info('Undangan: GET /api/invitation/p/reza-mega');
    }
}