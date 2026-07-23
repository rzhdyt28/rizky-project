# Graph Report - rizky-project  (2026-07-21)

## Corpus Check
- 231 files · ~52,820 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 980 nodes · 1653 edges · 123 communities (93 shown, 30 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 55 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `0492fed7`
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
- PortfolioSkillResource
- SubscriptionResource
- CreateActivityLogTable
- AddEventColumnToActivityLogTable
- ExampleTest
- autoload-dev
- keywords
- Controller.php
- add-custom-domain.sh
- deploy.sh
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
- Invitation.php
- SubscriptionResource
- AddBatchUuidColumnToActivityLogTable
- .getTitle
- AddEventColumnToActivityLogTable
- ThemeResource.php
- InvitationLookResource
- ContactMessageResource.php
- Illuminate\Database\Seeder
- InvitationController.php
- UserResource.php
- InvitationResource

## God Nodes (most connected - your core abstractions)
1. `Invitation` - 71 edges
2. `SawCase` - 31 edges
3. `Tenant` - 26 edges
4. `Theme` - 26 edges
5. `User` - 22 edges
6. `PlanLimitService` - 22 edges
7. `InvitationLookResource` - 15 edges
8. `Plan` - 13 edges
9. `InvitationController` - 13 edges
10. `What You Must Do When Invoked` - 12 edges

## Surprising Connections (you probably didn't know these)
- `GalleryPhotoController` --references--> `PlanLimitService`  [EXTRACTED]
  app/Modules/Invitation/Http/Controllers/GalleryPhotoController.php → app/Core/Services/PlanLimitService.php
- `GuestController` --references--> `PlanLimitService`  [EXTRACTED]
  app/Modules/Invitation/Http/Controllers/GuestController.php → app/Core/Services/PlanLimitService.php
- `InvitationController` --references--> `PlanLimitService`  [EXTRACTED]
  app/Modules/Invitation/Http/Controllers/InvitationController.php → app/Core/Services/PlanLimitService.php
- `ensureBelongsToInvitation()` --references--> `Invitation`  [EXTRACTED]
  app/Modules/Invitation/Http/Controllers/Concerns/ManagesInvitationChildren.php → app/Modules/Invitation/Models/Invitation.php
- `InvitationController` --references--> `InvitationThemeProvisioner`  [EXTRACTED]
  app/Modules/Invitation/Http/Controllers/InvitationController.php → app/Modules/Invitation/Support/InvitationThemeProvisioner.php

## Import Cycles
- None detected.

## Communities (123 total, 30 thin omitted)

### Community 0 - "Invitation"
Cohesion: 0.27
Nodes (5): Dashboard, AdminPanelProvider, Filament\Pages\Page, Filament\Panel, Filament\PanelProvider

### Community 1 - "User"
Cohesion: 0.23
Nodes (9): User, InvitationPolicy, User, Filament\Models\Contracts\FilamentUser, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens, Spatie\Activitylog\Traits\LogsActivity (+1 more)

### Community 2 - "Filament\Resources\Pages\EditRecord"
Cohesion: 0.07
Nodes (13): EditContactMessage, EditPortfolioEducation, EditPortfolioExperiencePhoto, EditPortfolioExperience, EditPortfolioProfile, EditPortfolioSkill, EditSawCase, EditUser (+5 more)

### Community 3 - "Filament\Resources\Pages\ListRecords"
Cohesion: 0.05
Nodes (17): ContactMessageResource, ListContactMessages, ListPortfolioEducation, ListPortfolioExperiencePhotos, ListPortfolioExperiences, ListPortfolioProfiles, ListPortfolioSkills, ListSawCases (+9 more)

### Community 4 - ".run"
Cohesion: 0.18
Nodes (3): Select, Select, ThemeOptionsSchema

### Community 5 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.14
Nodes (9): PortfolioController, ContactMessage, Education, Experience, ExperiencePhoto, Profile, Skill, PortfolioSeeder (+1 more)

### Community 6 - "Filament\Tables\Table"
Cohesion: 0.11
Nodes (12): AlternativesRelationManager, CriteriaRelationManager, TenantsRelationManager, EventsRelationManager, GalleryPhotosRelationManager, GiftsRelationManager, GuestsRelationManager, RsvpsRelationManager (+4 more)

### Community 7 - "Subscription"
Cohesion: 0.11
Nodes (8): Tenant, PlanLimitService, LoveStoryController, LoveStory, Stancl\Tenancy\Contracts\TenantWithDatabase, Stancl\Tenancy\Database\Concerns\HasDatabase, Stancl\Tenancy\Database\Concerns\HasDomains, Stancl\Tenancy\Database\Models\Tenant

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
Cohesion: 0.17
Nodes (7): CreateContactMessage, CreatePortfolioEducation, CreateSawCase, CreateCustomer, CreateSubscription, CreateTheme, Filament\Resources\Pages\CreateRecord

### Community 16 - "Theme"
Cohesion: 0.17
Nodes (11): 0. Reinstall VPS + akses awal, 1. Hardening dasar (sebelum install apa pun), 2. DNS, 3. Install stack di VPS, 4. Deploy backend (Laravel API), 5. Build & deploy frontend (Vue), 6. Konfigurasi Nginx, 7. SSL (Let's Encrypt) (+3 more)

### Community 17 - "require"
Cohesion: 0.18
Nodes (11): require, filament/filament, laravel/framework, laravel/pulse, laravel/sanctum, laravel/tinker, midtrans/midtrans-php, php (+3 more)

### Community 18 - "GuestSheetImporter"
Cohesion: 0.33
Nodes (3): PlatformStats, Rsvp, Filament\Widgets\StatsOverviewWidget

### Community 19 - "dashboard.blade.php"
Cohesion: 0.20
Nodes (9): pulse.cache, pulse.exceptions, pulse.queues, pulse.servers, pulse.slow-jobs, pulse.slow-outgoing-requests, pulse.slow-queries, pulse.slow-requests (+1 more)

### Community 21 - "require-dev"
Cohesion: 0.22
Nodes (9): require-dev, fakerphp/faker, laravel/pail, laravel/pint, laravel/sail, laravel/telescope, mockery/mockery, nunomaduro/collision (+1 more)

### Community 22 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.28
Nodes (3): CreateTenantUserImpersonationTokensTable, CreateActivityLogTable, Illuminate\Database\Migrations\Migration

### Community 23 - "InvitationResource.php"
Cohesion: 0.06
Nodes (19): AuthController, EnsureSubscriptionActive, PublicInvitationController, AuthController, DashboardController, SawAlternativeController, SawCalculationController, SawCaseController (+11 more)

### Community 24 - "composer.json"
Cohesion: 0.18
Nodes (10): autoload-dev, psr-4, description, license, minimum-stability, name, prefer-stable, Tests\\ (+2 more)

### Community 25 - "PortfolioEducationResource.php"
Cohesion: 0.15
Nodes (4): CreatePortfolioExperience, PortfolioExperienceResource, SawCaseResource, Filament\Resources\Resource

### Community 26 - "PortfolioExperienceResource.php"
Cohesion: 0.22
Nodes (5): LogOptions, LogOptions, Attribute, Illuminate\Database\Eloquent\Casts\Attribute, Spatie\Activitylog\LogOptions

### Community 29 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 30 - "TestCase"
Cohesion: 0.40
Nodes (3): Illuminate\Foundation\Testing\TestCase, ExampleTest, TestCase

### Community 31 - "psr-4"
Cohesion: 0.40
Nodes (5): autoload, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\

### Community 32 - "extra"
Cohesion: 0.40
Nodes (5): dev-master, extra, branch-alias, laravel, dont-discover

### Community 34 - "PortfolioSkillResource"
Cohesion: 0.19
Nodes (4): AgentStats, AgentController, JobApplication, RunLog

### Community 35 - "SubscriptionResource"
Cohesion: 0.14
Nodes (3): GuestController, Guest, GuestSheetImporter

### Community 49 - "autoload-dev"
Cohesion: 0.21
Nodes (3): ensureBelongsToInvitation(), GuestbookController, GuestbookEntry

### Community 50 - "keywords"
Cohesion: 0.67
Nodes (3): keywords, framework, laravel

### Community 85 - "Langkah membuat modul baru (contoh: modul "Toko")"
Cohesion: 0.11
Nodes (18): 1. Copy folder ini, 1. Tambah connection baru di `config/database.php`, 2. Copy `app/Core` jadi auth+billing milik project ini sendiri, 2. Isi tiap folder — apa yang ditaruh di mana, 3. Minimal yang harus dibuat, 3. Semua model & migration project ini set `$connection`, 4. Jalankan migrate, 4. Routes project ini (+10 more)

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

### Community 91 - "ContactMessageResource"
Cohesion: 0.17
Nodes (3): CheckoutController, Coupon, MidtransService

### Community 92 - "Illuminate\Routing\Controller"
Cohesion: 0.28
Nodes (3): Payment, RevenueChart, Filament\Widgets\ChartWidget

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

### Community 108 - "Invitation.php"
Cohesion: 0.27
Nodes (5): bootBelongsToTenant(), tenant(), UserFactory, Illuminate\Database\Eloquent\Factories\Factory, static

### Community 113 - "ThemeResource.php"
Cohesion: 0.13
Nodes (5): EditInvitationLook, ThemeResource, ThemeAsset, ThemeAssetSeeder, Illuminate\Database\Eloquent\Builder

### Community 114 - "InvitationLookResource"
Cohesion: 0.19
Nodes (3): InvitationLookResource, Theme, Get

### Community 117 - "Illuminate\Database\Seeder"
Cohesion: 0.20
Nodes (6): Plan, InvitationThemeProvisioner, DatabaseSeeder, InvitationDemoSeeder, PlanFeatureSeeder, Illuminate\Database\Seeder

## Knowledge Gaps
- **156 isolated node(s):** `Controller`, `$schema`, `name`, `type`, `description` (+151 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **30 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Invitation` connect `Payment` to `User`, `SubscriptionResource`, `CreateActivityLogTable`, `Illuminate\Database\Eloquent\Model`, `Subscription`, `GiftController`, `Illuminate\Database\Eloquent\Model`, `Invitation.php`, `autoload-dev`, `GuestSheetImporter`, `GalleryPhotoController`, `Illuminate\Database\Seeder`, `InvitationController.php`, `InvitationResource.php`, `PortfolioExperienceResource.php`?**
  _High betweenness centrality (0.066) - this node is a cross-community bridge._
- **Why does `User` connect `User` to `Invitation`, `AdminPanelProvider.php`, `Illuminate\Database\Seeder`, `InvitationResource.php`, `PortfolioExperienceResource.php`?**
  _High betweenness centrality (0.031) - this node is a cross-community bridge._
- **Why does `Theme` connect `InvitationLookResource` to `Illuminate\Database\Eloquent\Model`, `Payment`, `Illuminate\Database\Eloquent\Model`, `ThemeResource.php`, `Illuminate\Database\Seeder`, `InvitationResource`?**
  _High betweenness centrality (0.031) - this node is a cross-community bridge._
- **Are the 2 inferred relationships involving `Invitation` (e.g. with `.getStats()` and `.seedCouple()`) actually correct?**
  _`Invitation` has 2 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Controller`, `$schema`, `name` to the rest of the system?**
  _156 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Filament\Resources\Pages\EditRecord` be split into smaller, more focused modules?**
  _Cohesion score 0.07207207207207207 - nodes in this community are weakly interconnected._
- **Should `Filament\Resources\Pages\ListRecords` be split into smaller, more focused modules?**
  _Cohesion score 0.050980392156862744 - nodes in this community are weakly interconnected._