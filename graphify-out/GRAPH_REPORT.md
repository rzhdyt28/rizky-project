# Graph Report - rizky-project  (2026-07-24)

## Corpus Check
- 233 files · ~57,892 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 1004 nodes · 1676 edges · 121 communities (85 shown, 36 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 50 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `1b91b300`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Invitation
- User
- Filament\Resources\Pages\EditRecord
- Filament\Resources\Pages\ListRecords
- .run
- Illuminate\Database\Eloquent\Model
- Filament\Tables\Table
- Subscription
- Tenant
- devDependencies
- Payment
- TenancyServiceProvider
- scripts
- Filament\Resources\Resource
- Filament\Resources\Pages\CreateRecord
- Theme
- require
- GuestSheetImporter
- dashboard.blade.php
- AdminPanelProvider.php
- require-dev
- Illuminate\Database\Migrations\Migration
- InvitationResource.php
- composer.json
- PortfolioEducationResource.php
- PortfolioExperienceResource.php
- ThemeAssetResource.php
- config
- TestCase
- psr-4
- extra
- PortfolioExperiencePhotoResource
- PortfolioSkillResource
- SubscriptionResource
- CreateActivityLogTable
- ContactMessageResource
- ExampleTest
- autoload-dev
- keywords
- Controller.php
- add-custom-domain.sh
- deploy.sh
- routes.php
- app.php
- app.php
- dashboard.blade.php
- GalleryPhotoController
- Langkah membuat modul baru (contoh: modul "Toko")
- GalleryPhotoController
- graphify reference: extra exports and benchmark
- README.md
- graphify reference: query, path, explain
- Menghubungkan Vue Terpisah ke API (CORS + Sanctum)
- ContactMessageResource
- Illuminate\Routing\Controller
- CLAUDE.md
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- Deploy Semua di Satu VPS (API + Filament + Vue static)
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- CLAUDE.md
- extraction-spec.md
- cleanup-old-project.md
- BOOTSTRAP-REGISTRATION.md
- GiftController
- PortfolioSkillResource.php
- LoveStoryController
- Illuminate\Database\Eloquent\Model
- SubscriptionResource
- AddBatchUuidColumnToActivityLogTable
- .getTitle
- PortfolioEducationResource.php
- ThemeResource.php
- AddEventColumnToActivityLogTable
- Illuminate\Database\Seeder
- InvitationController.php
- UserResource.php

## God Nodes (most connected - your core abstractions)
1. `Invitation` - 64 edges
2. `SawCase` - 31 edges
3. `InvitationLookResource` - 27 edges
4. `Tenant` - 26 edges
5. `User` - 22 edges
6. `PlanLimitService` - 19 edges
7. `ThemeOptionsSchema` - 17 edges
8. `Theme` - 17 edges
9. `InvitationController` - 15 edges
10. `Plan` - 13 edges

## Surprising Connections (you probably didn't know these)
- `GalleryPhotoController` --references--> `PlanLimitService`  [EXTRACTED]
  app/Modules/Invitation/Http/Controllers/GalleryPhotoController.php → app/Core/Services/PlanLimitService.php
- `GuestController` --references--> `PlanLimitService`  [EXTRACTED]
  app/Modules/Invitation/Http/Controllers/GuestController.php → app/Core/Services/PlanLimitService.php
- `LoveStoryController` --references--> `PlanLimitService`  [EXTRACTED]
  app/Modules/Invitation/Http/Controllers/LoveStoryController.php → app/Core/Services/PlanLimitService.php
- `ensureBelongsToInvitation()` --references--> `Invitation`  [EXTRACTED]
  app/Modules/Invitation/Http/Controllers/Concerns/ManagesInvitationChildren.php → app/Modules/Invitation/Models/Invitation.php
- `CheckoutController` --references--> `MidtransService`  [EXTRACTED]
  app/Core/Http/Controllers/CheckoutController.php → app/Core/Services/MidtransService.php

## Import Cycles
- None detected.

## Communities (121 total, 36 thin omitted)

### Community 0 - "Invitation"
Cohesion: 0.67
Nodes (3): keywords, framework, laravel

### Community 1 - "User"
Cohesion: 0.10
Nodes (19): app/Core/Models/User.php, LogOptions, User, Dashboard, LogOptions, InvitationPolicy, Skripsi/Core/Models/User.php, User (+11 more)

### Community 2 - "Filament\Resources\Pages\EditRecord"
Cohesion: 0.06
Nodes (15): EditContactMessage, EditPortfolioEducation, EditPortfolioExperiencePhoto, EditPortfolioExperience, EditPortfolioProfile, EditPortfolioSkill, EditSawCase, EditUser (+7 more)

### Community 3 - "Filament\Resources\Pages\ListRecords"
Cohesion: 0.06
Nodes (15): ListContactMessages, ListPortfolioEducation, ListPortfolioExperiencePhotos, ListPortfolioExperiences, ListPortfolioProfiles, ListPortfolioSkills, ListSawCases, ListUsers (+7 more)

### Community 5 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.07
Nodes (18): ContactMessageResource, AgentStats, AgentController, JobApplication, RunLog, AgentMonitoring/routes.php, Guest, PortfolioController (+10 more)

### Community 6 - "Filament\Tables\Table"
Cohesion: 0.11
Nodes (12): AlternativesRelationManager, CriteriaRelationManager, TenantsRelationManager, EventsRelationManager, GalleryPhotosRelationManager, GiftsRelationManager, GuestsRelationManager, RsvpsRelationManager (+4 more)

### Community 7 - "Subscription"
Cohesion: 0.18
Nodes (6): Tenant, PlanLimitService, Stancl\Tenancy\Contracts\TenantWithDatabase, Stancl\Tenancy\Database\Concerns\HasDatabase, Stancl\Tenancy\Database\Concerns\HasDomains, Stancl\Tenancy\Database\Models\Tenant

### Community 8 - "Tenant"
Cohesion: 0.07
Nodes (26): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+18 more)

### Community 9 - "devDependencies"
Cohesion: 0.11
Nodes (18): axios, concurrently, laravel-vite-plugin, devDependencies, axios, concurrently, laravel-vite-plugin, tailwindcss (+10 more)

### Community 11 - "TenancyServiceProvider"
Cohesion: 0.18
Nodes (4): AppServiceProvider, ModuleServiceProvider, TenancyServiceProvider, Illuminate\Support\ServiceProvider

### Community 12 - "scripts"
Cohesion: 0.12
Nodes (16): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, Composer\\Config::disableProcessTimeout, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump (+8 more)

### Community 15 - "Filament\Resources\Pages\CreateRecord"
Cohesion: 0.16
Nodes (7): CreateContactMessage, CreatePortfolioEducation, CreatePortfolioExperiencePhoto, CreatePortfolioProfile, CreateCustomer, CreateInvitation, Filament\Resources\Pages\CreateRecord

### Community 16 - "Theme"
Cohesion: 0.17
Nodes (11): 0. Reinstall VPS + akses awal, 1. Hardening dasar (sebelum install apa pun), 2. DNS, 3. Install stack di VPS, 4. Deploy backend (Laravel API), 5. Build & deploy frontend (Vue), 6. Konfigurasi Nginx, 7. SSL (Let's Encrypt) (+3 more)

### Community 17 - "require"
Cohesion: 0.18
Nodes (11): require, filament/filament, laravel/framework, laravel/pulse, laravel/sanctum, laravel/tinker, midtrans/midtrans-php, php (+3 more)

### Community 18 - "GuestSheetImporter"
Cohesion: 0.21
Nodes (3): ensureBelongsToInvitation(), GuestbookController, GuestbookEntry

### Community 19 - "dashboard.blade.php"
Cohesion: 0.20
Nodes (10): pulse.cache, pulse.exceptions, pulse.queues, pulse.servers, pulse.slow-jobs, pulse.slow-outgoing-requests, pulse.slow-queries, pulse.slow-requests (+2 more)

### Community 21 - "require-dev"
Cohesion: 0.22
Nodes (9): require-dev, fakerphp/faker, laravel/pail, laravel/pint, laravel/sail, laravel/telescope, mockery/mockery, nunomaduro/collision (+1 more)

### Community 22 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.28
Nodes (3): CreateTenantUserImpersonationTokensTable, CreateActivityLogTable, Illuminate\Database\Migrations\Migration

### Community 23 - "InvitationResource.php"
Cohesion: 0.08
Nodes (13): app/Core/Http/Controllers/AuthController.php, DashboardController, Skripsi/routes.php, SawAlternativeController, SawCaseController, SawCriterionController, SawScoreController, SawAlternative (+5 more)

### Community 24 - "composer.json"
Cohesion: 0.18
Nodes (10): autoload-dev, psr-4, description, license, minimum-stability, name, prefer-stable, Tests\\ (+2 more)

### Community 25 - "PortfolioEducationResource.php"
Cohesion: 0.18
Nodes (4): PortfolioEducationResource, CreatePortfolioExperience, PortfolioExperienceResource, Filament\Resources\Resource

### Community 29 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 30 - "TestCase"
Cohesion: 0.40
Nodes (4): Illuminate\Foundation\Testing\TestCase, Feature/ExampleTest.php, ExampleTest, TestCase

### Community 31 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 32 - "extra"
Cohesion: 0.40
Nodes (5): dev-master, extra, branch-alias, laravel, dont-discover

### Community 37 - "ContactMessageResource"
Cohesion: 0.12
Nodes (3): Select, ThemeOptionsSchema, TextInput

### Community 38 - "ExampleTest"
Cohesion: 0.67
Nodes (3): PHPUnit\Framework\TestCase, Unit/ExampleTest.php, ExampleTest

### Community 49 - "autoload-dev"
Cohesion: 0.11
Nodes (12): bootBelongsToTenant(), tenant(), App\Core\Services\PlanLimitService, InvitationController, App\Modules\Invitation\Models\Invitation, App\Modules\Invitation\Support\InvitationThemeProvisioner, Attribute, UserFactory (+4 more)

### Community 50 - "keywords"
Cohesion: 0.21
Nodes (6): AuthController, EnsureSubscriptionActive, Skripsi/Core/Http/Controllers/AuthController.php, AuthController, Closure, Illuminate\Http\Request

### Community 85 - "Langkah membuat modul baru (contoh: modul "Toko")"
Cohesion: 0.11
Nodes (19): _template/README.md, 1. Copy folder ini, 1. Tambah connection baru di `config/database.php`, 2. Copy `app/Core` jadi auth+billing milik project ini sendiri, 2. Isi tiap folder — apa yang ditaruh di mana, 3. Minimal yang harus dibuat, 3. Semua model & migration project ini set `$connection`, 4. Jalankan migrate (+11 more)

### Community 86 - "GalleryPhotoController"
Cohesion: 0.16
Nodes (3): RsvpController, Invitation, Illuminate\Foundation\Auth\Access\AuthorizesRequests

### Community 87 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 88 - "README.md"
Cohesion: 0.22
Nodes (8): About Laravel, Code of Conduct, Contributing, Laravel Sponsors, Learning Laravel, License, Premium Partners, Security Vulnerabilities

### Community 89 - "graphify reference: query, path, explain"
Cohesion: 0.33
Nodes (5): For /graphify explain, For /graphify path, graphify reference: query, path, explain, Step 0 — Constrained query expansion (REQUIRED before traversal), Step 1 — Traversal

### Community 90 - "Menghubungkan Vue Terpisah ke API (CORS + Sanctum)"
Cohesion: 0.33
Nodes (5): Alur auth dari Vue, Catatan custom domain pelanggan (Pola C), Menghubungkan Vue Terpisah ke API (CORS + Sanctum), Saat development (paling sering dipakai), Saat produksi (domain berbeda: rizky.com -> api.rizky.com)

### Community 92 - "Illuminate\Routing\Controller"
Cohesion: 0.38
Nodes (3): SawCalculationController, SawCalculator, Illuminate\Routing\Controller

### Community 93 - "CLAUDE.md"
Cohesion: 0.50
Nodes (3): graphify, Project, Token efficiency

### Community 94 - "graphify reference: add a URL and watch a folder"
Cohesion: 0.50
Nodes (3): For /graphify add, For --watch, graphify reference: add a URL and watch a folder

### Community 95 - "graphify reference: commit hook and native CLAUDE.md integration"
Cohesion: 0.50
Nodes (3): For git commit hook, For native CLAUDE.md integration, graphify reference: commit hook and native CLAUDE.md integration

### Community 96 - "graphify reference: incremental update and cluster-only"
Cohesion: 0.50
Nodes (3): For --cluster-only, For --update (incremental re-extraction), graphify reference: incremental update and cluster-only

### Community 97 - "Deploy Semua di Satu VPS (API + Filament + Vue static)"
Cohesion: 0.50
Nodes (3): Cek akhir, Deploy Semua di Satu VPS (API + Filament + Vue static), Ringkasan langkah (detail teknis identik pola sebelumnya)

### Community 113 - "ThemeResource.php"
Cohesion: 0.20
Nodes (4): CreateTheme, ThemeAsset, ThemeAssetSeeder, Illuminate\Database\Eloquent\Builder

### Community 117 - "Illuminate\Database\Seeder"
Cohesion: 0.05
Nodes (18): CheckoutController, Coupon, Payment, Plan, Subscription, app/Core/routes.php, MidtransService, PlatformStats (+10 more)

## Knowledge Gaps
- **167 isolated node(s):** `app/Core/routes.php`, `Controller`, `AgentMonitoring/routes.php`, `Portfolio/routes.php`, `Skripsi/Core/routes.php` (+162 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **36 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Invitation` connect `GalleryPhotoController` to `User`, `SubscriptionResource`, `CreateActivityLogTable`, `Illuminate\Database\Eloquent\Model`, `GiftController`, `Payment`, `autoload-dev`, `GuestSheetImporter`, `GalleryPhotoController`, `Illuminate\Database\Seeder`, `InvitationController.php`?**
  _High betweenness centrality (0.061) - this node is a cross-community bridge._
- **Why does `InvitationLookResource` connect `.run` to `PortfolioSkillResource`, `ContactMessageResource`, `ThemeResource.php`, `PortfolioEducationResource.php`, `ContactMessageResource`?**
  _High betweenness centrality (0.027) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `keywords`, `AdminPanelProvider.php`, `Illuminate\Database\Seeder`, `InvitationResource.php`?**
  _High betweenness centrality (0.023) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `Invitation` (e.g. with `.getStats()` and `.seedCouple()`) actually correct?**
  _`Invitation` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `app/Core/routes.php`, `Controller`, `AgentMonitoring/routes.php` to the rest of the system?**
  _167 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.1028225806451613 - nodes in this community are weakly interconnected._
- **Should `Filament\Resources\Pages\EditRecord` be split into smaller, more focused modules?**
  _Cohesion score 0.06025369978858351 - nodes in this community are weakly interconnected._