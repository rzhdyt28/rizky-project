# Graph Report - rizky-project  (2026-07-19)

## Corpus Check
- 186 files · ~42,452 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 770 nodes · 1225 edges · 98 communities (75 shown, 23 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 38 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `59746e25`
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
- AddEventColumnToActivityLogTable
- ExampleTest
- keywords
- Controller.php
- add-custom-domain.sh
- deploy.sh
- Langkah membuat modul baru (contoh: modul "Toko")
- graphify reference: extra exports and benchmark
- README.md
- graphify reference: query, path, explain
- Menghubungkan Vue Terpisah ke API (CORS + Sanctum)
- ContactMessageResource
- EnsureSubscriptionActive.php
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

## God Nodes (most connected - your core abstractions)
1. `Invitation` - 67 edges
2. `Tenant` - 22 edges
3. `User` - 19 edges
4. `What You Must Do When Invoked` - 12 edges
5. `InvitationController` - 12 edges
6. `Plan` - 12 edges
7. `/graphify` - 11 edges
8. `Theme` - 11 edges
9. `Payment` - 11 edges
10. `require` - 11 edges

## Surprising Connections (you probably didn't know these)
- `CheckoutController` --references--> `MidtransService`  [EXTRACTED]
  app/Core/Http/Controllers/CheckoutController.php → app/Core/Services/MidtransService.php
- `ExampleTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/ExampleTest.php → tests/TestCase.php

## Import Cycles
- None detected.

## Communities (98 total, 23 thin omitted)

### Community 0 - "Invitation"
Cohesion: 0.05
Nodes (23): AuthController, App\Core\Services\PlanLimitService, EventController, GalleryPhotoController, GiftController, GuestbookController, GuestController, InvitationController (+15 more)

### Community 1 - "User"
Cohesion: 0.13
Nodes (12): LogOptions, User, InvitationPolicy, Attribute, Illuminate\Database\Eloquent\Casts\Attribute, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens (+4 more)

### Community 2 - "Filament\Resources\Pages\EditRecord"
Cohesion: 0.08
Nodes (12): EditContactMessage, EditInvitation, EditPlan, EditPortfolioEducation, EditPortfolioExperiencePhoto, EditPortfolioExperience, EditPortfolioProfile, EditPortfolioSkill (+4 more)

### Community 3 - "Filament\Resources\Pages\ListRecords"
Cohesion: 0.08
Nodes (12): ListContactMessages, ListInvitations, ListPlans, ListPortfolioEducation, ListPortfolioExperiencePhotos, ListPortfolioExperiences, ListPortfolioProfiles, ListPortfolioSkills (+4 more)

### Community 5 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.07
Nodes (22): App\Core\Concerns\BelongsToTenant, bootBelongsToTenant(), tenant(), AgentStats, AgentController, JobApplication, RunLog, Guest (+14 more)

### Community 6 - "Filament\Tables\Table"
Cohesion: 0.17
Nodes (8): EventsRelationManager, GalleryPhotosRelationManager, GiftsRelationManager, RsvpsRelationManager, StoriesRelationManager, Filament\Forms\Form, Filament\Resources\RelationManagers\RelationManager, Filament\Tables\Table

### Community 7 - "Subscription"
Cohesion: 0.19
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

### Community 14 - "Filament\Resources\Resource"
Cohesion: 0.18
Nodes (4): CreatePlan, PlanResource, ThemeResource, Filament\Resources\Resource

### Community 15 - "Filament\Resources\Pages\CreateRecord"
Cohesion: 0.20
Nodes (6): CreateContactMessage, CreateInvitation, CreatePortfolioProfile, CreateThemeAsset, CreateTheme, Filament\Resources\Pages\CreateRecord

### Community 17 - "require"
Cohesion: 0.18
Nodes (11): require, filament/filament, laravel/framework, laravel/pulse, laravel/sanctum, laravel/tinker, midtrans/midtrans-php, php (+3 more)

### Community 19 - "dashboard.blade.php"
Cohesion: 0.20
Nodes (9): pulse.cache, pulse.exceptions, pulse.queues, pulse.servers, pulse.slow-jobs, pulse.slow-outgoing-requests, pulse.slow-queries, pulse.slow-requests (+1 more)

### Community 20 - "AdminPanelProvider.php"
Cohesion: 0.31
Nodes (5): Dashboard, AdminPanelProvider, Filament\Pages\Page, Filament\Panel, Filament\PanelProvider

### Community 21 - "require-dev"
Cohesion: 0.22
Nodes (9): require-dev, fakerphp/faker, laravel/pail, laravel/pint, laravel/sail, laravel/telescope, mockery/mockery, nunomaduro/collision (+1 more)

### Community 22 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.28
Nodes (3): CreateTenantUserImpersonationTokensTable, AddBatchUuidColumnToActivityLogTable, Illuminate\Database\Migrations\Migration

### Community 24 - "composer.json"
Cohesion: 0.18
Nodes (10): autoload-dev, psr-4, description, license, minimum-stability, name, prefer-stable, Tests\\ (+2 more)

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

### Community 50 - "keywords"
Cohesion: 0.67
Nodes (3): keywords, framework, laravel

### Community 85 - "Langkah membuat modul baru (contoh: modul "Toko")"
Cohesion: 0.20
Nodes (9): 1. Copy folder ini, 2. Isi tiap folder — apa yang ditaruh di mana, 3. Minimal yang harus dibuat, 4. Jalankan migrate, 5. (Opsional) Tambahan yang sering diperlukan, 6. Frontend-nya (repo rizky-project-web), Checklist sebelum dianggap selesai, Langkah membuat modul baru (contoh: modul "Toko") (+1 more)

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
Cohesion: 0.05
Nodes (22): CheckoutController, Coupon, Payment, Plan, Subscription, MidtransService, PlatformStats, RevenueChart (+14 more)

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

## Knowledge Gaps
- **138 isolated node(s):** `graphify`, `Usage`, `What graphify is for`, `Step 0 - GitHub repos and multi-path merge (only if a URL or several paths)`, `Step 1 - Ensure graphify is installed` (+133 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **23 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Invitation` connect `Invitation` to `User`, `ContactMessageResource`, `Illuminate\Database\Eloquent\Model`?**
  _High betweenness centrality (0.081) - this node is a cross-community bridge._
- **Why does `Tenant` connect `Subscription` to `Invitation`, `ContactMessageResource`?**
  _High betweenness centrality (0.020) - this node is a cross-community bridge._
- **Why does `Plan` connect `ContactMessageResource` to `Invitation`, `SubscriptionResource`, `Illuminate\Database\Eloquent\Model`, `Subscription`?**
  _High betweenness centrality (0.019) - this node is a cross-community bridge._
- **Are the 3 inferred relationships involving `Invitation` (e.g. with `.getStats()` and `.run()`) actually correct?**
  _`Invitation` has 3 INFERRED edges - model-reasoned connections that need verification._
- **What connects `graphify`, `Usage`, `What graphify is for` to the rest of the system?**
  _138 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Invitation` be split into smaller, more focused modules?**
  _Cohesion score 0.05217757205975174 - nodes in this community are weakly interconnected._
- **Should `User` be split into smaller, more focused modules?**
  _Cohesion score 0.13438735177865613 - nodes in this community are weakly interconnected._