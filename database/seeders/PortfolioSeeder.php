<?php

namespace Database\Seeders;

use App\Core\Models\Tenant;
use App\Modules\Portfolio\Models\Education;
use App\Modules\Portfolio\Models\Experience;
use App\Modules\Portfolio\Models\ExperiencePhoto;
use App\Modules\Portfolio\Models\Profile;
use App\Modules\Portfolio\Models\Skill;
use Illuminate\Database\Seeder;

/**
 * Isi konten portofolio (profile, skills, experiences+photos, education)
 * meniru rzhdyt28.github.io — modul portofolio bersifat central/personal,
 * jadi tenant_id di sini hanya untuk memenuhi FK (tidak difilter saat
 * diakses lewat domain central, lihat BelongsToTenant::bootBelongsToTenant).
 */
class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = Tenant::query()->value('id');
        if (! $tenantId) {
            $this->command?->warn('Tidak ada tenant — lewati PortfolioSeeder.');
            return;
        }

        $profile = Profile::updateOrCreate(
            ['tenant_id' => $tenantId, 'full_name' => 'Rizky Hidayat'],
            [
                'headline' => [
                    'id' => 'Junior System Administrator · IT Support · Desktop Support Engineer',
                    'en' => 'Junior System Administrator · IT Support · Desktop Support Engineer',
                ],
                'about' => [
                    'id' => 'Profesional IT dengan pengalaman sekitar empat tahun di bidang IT support, desktop support, dan implementasi perangkat jaringan di lingkungan enterprise perbankan. Terbiasa menangani troubleshooting hardware dan software, dukungan end-user, deployment perangkat, koordinasi lapangan lintas cabang, serta penyusunan dokumentasi implementasi. Saat ini aktif mengembangkan kemampuan ke arah System Administrator di bidang jaringan dan sistem, dengan nilai tambah dari latar belakang pengembangan web (Laravel, Vue.js) dan pengelolaan database (MySQL).',
                    'en' => 'An IT professional with around four years of experience in IT support, desktop support, and network device implementation within enterprise banking environments. Experienced in hardware and software troubleshooting, end-user support, device deployment, cross-branch field coordination, and implementation documentation. Currently developing skills toward a System Administrator role in networking and systems, with added value from a web development background (Laravel, Vue.js) and database management (MySQL).',
                ],
                'location' => 'Bekasi Kota, Jawa Barat',
                'photo_path' => asset('storage/portfolio/profile.png'),
                'cv_path' => asset('storage/portfolio/Rizky_Hidayat_CV.pdf'),
                'socials' => [
                    'email' => 'rzhdyt28@gmail.com',
                    'whatsapp' => '628993766315',
                    'linkedin' => 'https://www.linkedin.com/in/rzh28',
                ],
            ],
        );

        $skills = [
            ['category' => 'it-support', 'title' => ['id' => 'IT & Desktop Support', 'en' => 'IT & Desktop Support'], 'description' => [
                'id' => 'Troubleshooting hardware & software, instalasi dan konfigurasi perangkat, dukungan end-user (±25 pengguna), maintenance operasional, remote support via AnyDesk, UltraViewer, MobaXterm (SSH).',
                'en' => 'Hardware & software troubleshooting, device installation and configuration, end-user support (±25 users), operational maintenance, remote support via AnyDesk, UltraViewer, MobaXterm (SSH).',
            ]],
            ['category' => 'deployment', 'title' => ['id' => 'Deployment Perangkat Jaringan', 'en' => 'Network Device Deployment'], 'description' => [
                'id' => 'Deployment access point, switch, dan router (Huawei); penanganan perangkat Cisco; assessment pra-implementasi; labeling, inventory, asset tracking, checklist, dan laporan implementasi.',
                'en' => 'Deployment of access points, switches, and routers (Huawei); Cisco device handling; pre-implementation assessment; labeling, inventory, asset tracking, checklists, and implementation reports.',
            ]],
            ['category' => 'networking', 'title' => ['id' => 'Jaringan Dasar', 'en' => 'Networking Fundamentals'], 'description' => [
                'id' => 'IP addressing (static & DHCP), DNS, VLAN, basic networking; pengujian LAN; crimping kabel; perangkat Cisco dan Huawei (switch, router, AP).',
                'en' => 'IP addressing (static & DHCP), DNS, VLAN, basic networking; LAN testing; cable crimping; Cisco and Huawei devices (switches, routers, APs).',
            ]],
            ['category' => 'sysadmin', 'title' => ['id' => 'Sistem & Administrasi', 'en' => 'Systems & Administration'], 'description' => [
                'id' => 'Windows 10/11; Ubuntu LTS & Linux CLI dasar; Windows CMD (ipconfig, ping, netstat, sfc); virtualisasi VirtualBox; manajemen user & permission; backup, restore, patching.',
                'en' => 'Windows 10/11; Ubuntu LTS & basic Linux CLI; Windows CMD (ipconfig, ping, netstat, sfc); VirtualBox virtualization; user & permission management; backup, restore, patching.',
            ]],
            ['category' => 'tools', 'title' => ['id' => 'Tools & Platform', 'en' => 'Tools & Platforms'], 'description' => [
                'id' => 'Microsoft 365 (Word, Excel, PowerPoint, Visio, Outlook, Teams); Google Workspace; Git; Postman; HeidiSQL; VS Code; Laragon; Google Form.',
                'en' => 'Microsoft 365 (Word, Excel, PowerPoint, Visio, Outlook, Teams); Google Workspace; Git; Postman; HeidiSQL; VS Code; Laragon; Google Forms.',
            ]],
            ['category' => 'dev', 'title' => ['id' => 'Programming (Nilai Tambah)', 'en' => 'Programming (Added Value)'], 'description' => [
                'id' => 'PHP (Laravel), JavaScript (Vue.js), MySQL — pemahaman alur aplikasi dan integrasi API untuk mendukung troubleshooting lintas tim.',
                'en' => 'PHP (Laravel), JavaScript (Vue.js), MySQL — understanding of application flows and API integration to support cross-team troubleshooting.',
            ]],
        ];
        foreach ($skills as $i => $s) {
            Skill::updateOrCreate(
                ['tenant_id' => $tenantId, 'category' => $s['category']],
                $s + ['sort_order' => $i],
            );
        }

        $experiences = [
            [
                'slug' => 'berca',
                'company' => 'PT Berca Hardayaperkasa',
                'role' => ['id' => 'IT Desktop Support Engineer', 'en' => 'IT Desktop Support Engineer'],
                'location' => 'Jakarta',
                'start_date' => '2026-01-01',
                'end_date' => '2026-06-30',
                'tags' => ['access-point', 'switch', 'router', 'huawei', 'smart-branch', 'asset-tracking'],
                'bullets' => [
                    ['id' => 'Dua project implementasi infrastruktur IT perbankan skala enterprise di lingkungan Bank Mandiri — cakupan lebih dari 30 cabang di dalam dan luar Jabodetabek.', 'en' => 'Two enterprise-scale banking IT infrastructure implementation projects within Bank Mandiri — covering 30+ branches inside and outside Greater Jakarta.'],
                    ['id' => 'Pre-deployment preparation perangkat access point dan tablet untuk program Smart Branch, koordinasi kesiapan lokasi dengan cabang.', 'en' => 'Pre-deployment preparation of access points and tablets for the Smart Branch program, coordinating site readiness with branches.'],
                    ['id' => 'Deployment, verifikasi fungsi perangkat, labeling, checklist, dan asset tracking di setiap cabang.', 'en' => 'Deployment, device function verification, labeling, checklists, and asset tracking at each branch.'],
                    ['id' => 'Assessment dan penggantian switch–router Huawei, instalasi dan upgrade untuk stabilitas jaringan cabang.', 'en' => 'Assessment and replacement of Huawei switches and routers, installation and upgrades for branch network stability.'],
                ],
                'photos' => [
                    ['file' => 'berca-1.jpg', 'caption' => ['id' => 'Deployment access point — Smart Branch', 'en' => 'Access point deployment — Smart Branch']],
                    ['file' => 'berca-2.jpg', 'caption' => ['id' => 'Instalasi switch & router Huawei', 'en' => 'Huawei switch & router installation']],
                    ['file' => 'berca-3.jpg', 'caption' => ['id' => 'Labeling & asset tracking perangkat', 'en' => 'Device labeling & asset tracking']],
                    ['file' => 'berca-4.jpg', 'caption' => ['id' => 'Dokumentasi implementasi di cabang', 'en' => 'Branch implementation documentation']],
                ],
            ],
            [
                'slug' => 'teguh-karya',
                'company' => 'CV Teguh Karya Mandiri',
                'role' => ['id' => 'IT Staff', 'en' => 'IT Staff'],
                'location' => 'Jakarta',
                'start_date' => '2024-08-01',
                'end_date' => '2025-08-31',
                'tags' => ['it-support', 'windows', 'printer', 'ip-addressing', 'digitalisasi-arsip'],
                'bullets' => [
                    ['id' => 'Dukungan teknis hardware, software, dan jaringan dasar untuk ±25 staf dan tim proyek.', 'en' => 'Provided hardware, software, and basic network support for ±25 staff and project teams.'],
                    ['id' => 'Instalasi Windows, software, driver, dan antivirus pada laptop, desktop, serta perangkat jaringan.', 'en' => 'Installed Windows, software, drivers, and antivirus on laptops, desktops, and network devices.'],
                    ['id' => 'Mendukung digitalisasi administrasi dan pengelolaan arsip dokumen proyek secara digital.', 'en' => 'Supported administrative digitalization and digital management of project document archives.'],
                    ['id' => 'Pemeliharaan jaringan kantor: konfigurasi IP addressing, printer, dan troubleshooting konektivitas.', 'en' => 'Maintained office network: IP addressing configuration, printer setup, and connectivity troubleshooting.'],
                ],
                'photos' => [
                    ['file' => 'teguh-karya-1.jpg', 'caption' => ['id' => 'Instalasi & setup perangkat kantor', 'en' => 'Office device installation & setup']],
                    ['file' => 'teguh-karya-2.jpg', 'caption' => ['id' => 'Pemeliharaan jaringan & printer', 'en' => 'Network & printer maintenance']],
                    ['file' => 'teguh-karya-3.jpg', 'caption' => ['id' => 'Digitalisasi arsip dokumen proyek', 'en' => 'Project document digitalization']],
                ],
            ],
            [
                'slug' => 'jiexpo',
                'company' => 'PT Jakarta International Expo',
                'role' => ['id' => 'Freelance IT Support', 'en' => 'Freelance IT Support'],
                'location' => 'Jakarta',
                'start_date' => '2024-05-01',
                'end_date' => '2024-07-31',
                'tags' => ['pos-terminal', 'ticket-printer', 'gate-scanner', 'event-support'],
                'bullets' => [
                    ['id' => 'Mendukung operasional sistem dan perangkat IT selama Pekan Raya Jakarta (PRJ).', 'en' => 'Supported IT systems and device operations during the Jakarta Fair (PRJ).'],
                    ['id' => 'Konfigurasi dan troubleshooting terminal POS, printer tiket, scanner gate, jaringan, dan perangkat pembayaran.', 'en' => 'Configured and troubleshot POS terminals, ticket printers, gate scanners, network, and payment devices.'],
                    ['id' => 'Solusi teknis real-time untuk gangguan pembacaan tiket di gate demi kelancaran event.', 'en' => 'Delivered real-time fixes for gate ticket-reading issues to keep the event running smoothly.'],
                ],
                'photos' => [
                    ['file' => 'jiexpo-1.jpg', 'caption' => ['id' => 'Setup terminal POS & perangkat pembayaran', 'en' => 'POS terminal & payment device setup']],
                    ['file' => 'jiexpo-2.jpg', 'caption' => ['id' => 'Troubleshooting scanner gate tiket', 'en' => 'Gate ticket scanner troubleshooting']],
                    ['file' => 'jiexpo-3.jpg', 'caption' => ['id' => 'Dukungan teknis lapangan selama event', 'en' => 'On-site technical support during the event']],
                ],
            ],
            [
                'slug' => 'kreasindo',
                'company' => 'PT Kreasindo',
                'role' => ['id' => 'Freelance IT Helpdesk / Support', 'en' => 'Freelance IT Helpdesk / Support'],
                'location' => 'Bekasi',
                'start_date' => '2024-01-01',
                'end_date' => '2024-04-30',
                'tags' => ['monitoring', 'log-analysis', 'issue-reporting', 'helpdesk'],
                'bullets' => [
                    ['id' => 'Memantau performa internal system dan menganalisis log aplikasi untuk identifikasi masalah.', 'en' => 'Monitored internal system performance and analyzed application logs to identify issues.'],
                    ['id' => 'Melaporkan rata-rata ±15 issue per bulan kepada tim developer untuk perbaikan berkelanjutan.', 'en' => 'Reported ±15 issues per month on average to the developer team for continuous improvement.'],
                    ['id' => 'Menyusun laporan kinerja aplikasi berkala sebagai bahan evaluasi manajemen.', 'en' => 'Prepared periodic application performance reports for management evaluation.'],
                ],
                'photos' => [
                    ['file' => 'kreasindo-1.jpg', 'caption' => ['id' => 'Pemantauan performa sistem', 'en' => 'System performance monitoring']],
                    ['file' => 'kreasindo-2.jpg', 'caption' => ['id' => 'Laporan analisis issue aplikasi', 'en' => 'Application issue analysis report']],
                ],
            ],
            [
                'slug' => 'chronos',
                'company' => 'PT Chronos Universal',
                'role' => ['id' => 'Junior Frontend Developer', 'en' => 'Junior Frontend Developer'],
                'location' => 'Tangerang',
                'start_date' => '2022-08-01',
                'end_date' => '2023-07-31',
                'tags' => ['vue.js', 'frontend', 'responsive-ui'],
                'bullets' => [
                    ['id' => 'Mengembangkan antarmuka web dan mobile responsif menggunakan Vue.js.', 'en' => 'Built responsive web and mobile interfaces using Vue.js.'],
                    ['id' => 'Implementasi fitur baru dan kolaborasi tim untuk efisiensi pengembangan produk.', 'en' => 'Implemented new features and collaborated with the team to improve development efficiency.'],
                ],
                'photos' => [
                    ['file' => 'chronos-1.jpg', 'caption' => ['id' => 'Antarmuka web dengan Vue.js', 'en' => 'Web interface built with Vue.js']],
                    ['file' => 'chronos-2.jpg', 'caption' => ['id' => 'Tampilan mobile responsif', 'en' => 'Responsive mobile view']],
                ],
            ],
            [
                'slug' => 'bigrich',
                'company' => 'The Big Rich Group',
                'role' => ['id' => 'Junior Backend Developer', 'en' => 'Junior Backend Developer'],
                'location' => 'Jakarta Selatan',
                'start_date' => '2020-10-01',
                'end_date' => '2022-07-31',
                'tags' => ['laravel', 'rest-api', 'mysql', 'backend'],
                'bullets' => [
                    ['id' => 'Merancang dan mengembangkan fitur aplikasi dari sisi frontend maupun backend.', 'en' => 'Designed and developed application features on both frontend and backend.'],
                    ['id' => 'Membangun REST API untuk integrasi sistem internal dan pihak ketiga.', 'en' => 'Built REST APIs for internal and third-party system integration.'],
                    ['id' => 'Maintenance dan optimasi aplikasi untuk menjaga stabilitas performa sistem.', 'en' => 'Maintained and optimized applications to keep system performance stable.'],
                ],
                'photos' => [
                    ['file' => 'bigrich-1.jpg', 'caption' => ['id' => 'Pengembangan REST API', 'en' => 'REST API development']],
                    ['file' => 'bigrich-2.jpg', 'caption' => ['id' => 'Fitur aplikasi frontend & backend', 'en' => 'Frontend & backend application features']],
                ],
            ],
        ];

        foreach ($experiences as $i => $e) {
            $photos = $e['photos'];
            unset($e['photos']);

            $exp = Experience::updateOrCreate(
                ['tenant_id' => $tenantId, 'slug' => $e['slug']],
                $e + ['sort_order' => $i],
            );

            foreach ($photos as $j => $p) {
                ExperiencePhoto::updateOrCreate(
                    ['experience_id' => $exp->id, 'path' => 'portfolio/' . $p['file']],
                    ['caption' => $p['caption'], 'sort_order' => $j],
                );
            }
        }

        $educations = [
            ['degree' => ['id' => 'S1 Teknik Informatika', 'en' => 'B.Sc. in Informatics Engineering'], 'institution' => 'Universitas Krisnadwipayana — Bekasi', 'period' => '2016 – 2020', 'gpa' => '3.20'],
            ['degree' => ['id' => 'SMK Teknik Instalasi Tenaga Listrik', 'en' => 'Vocational High School — Electrical Power Installation'], 'institution' => 'SMK Dinamika Pembangunan 1 — Jakarta', 'period' => '2011 – 2014', 'gpa' => null],
        ];
        foreach ($educations as $i => $e) {
            Education::updateOrCreate(
                ['tenant_id' => $tenantId, 'kind' => 'education', 'institution' => $e['institution']],
                $e + ['kind' => 'education', 'sort_order' => $i],
            );
        }

        $certifications = [
            ['degree' => ['id' => 'Cisco CCNA v7', 'en' => 'Cisco CCNA v7'], 'institution' => 'Cisco NetAcad — kerja sama dengan Universitas Krisnadwipayana'],
            ['degree' => ['id' => 'Fullstack Web Development: Laravel & Vue.js', 'en' => 'Fullstack Web Development: Laravel & Vue.js'], 'institution' => 'BuildWithAngga (BWA)'],
        ];
        foreach ($certifications as $i => $c) {
            Education::updateOrCreate(
                ['tenant_id' => $tenantId, 'kind' => 'certification', 'institution' => $c['institution']],
                $c + ['kind' => 'certification', 'sort_order' => $i],
            );
        }

        $this->command?->info("Portfolio seeded for profile #{$profile->id} (tenant {$tenantId}).");
    }
}
