<?php

namespace App\Filament\Resources;

use App\Filament\Support\ThemeOptionsSchema;
use App\Modules\Invitation\Models\Theme;
use App\Modules\Invitation\Models\ThemeAsset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * TEMA — form DISEDERHANAKAN (v2 arsitektur).
 * Warna/font default tema kini hidup di frontend (themes/<key>/tokens.js);
 * nilai di sini hanyalah OVERRIDE admin di atasnya, jadi semua field boleh
 * dikosongkan dan tema tetap tampil benar.
 * Fitur "Background Global" & pengaturan per-Section DIHAPUS dari produk
 * karena efeknya di frontend tidak konsisten (sumber bug).
 */
class ThemeResource extends Resource
{
    protected static ?string $model = Theme::class;
    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?string $navigationGroup = 'Konten';

    /**
     * Tema privat milik 1 undangan (invitation_id terisi -- lihat
     * InvitationLookResource) TIDAK PERNAH boleh muncul di sini. Resource
     * ini murni untuk tema DASAR/publik yang bisa dipilih banyak undangan.
     * Discope di level query dasar (bukan cuma table()) supaya list, edit,
     * view, delete, dan global search semuanya konsisten -- termasuk kalau
     * ada yang coba akses /admin/themes/{child_id}/edit langsung, akan 404.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNull('invitation_id');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas Tema')->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('component_key')
                    ->helperText('Nama folder tema Vue di themes/* (mis. elegant, mildness)')
                    ->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('tier')
                    ->options(['free' => 'Free', 'premium' => 'Premium', 'platinum' => 'Platinum'])
                    ->default('free'),
                Forms\Components\Select::make('parent_id')
                    ->label('Tema induk (parent)')
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query, ?Theme $record) => ($record ? $query->whereKeyNot($record->getKey()) : $query)
                            ->whereNull('invitation_id'),
                    )
                    ->placeholder('— tanpa parent —')
                    ->helperText('Tema ini MEWARISI seluruh default parent; field yang diisi di sini menimpanya. component_key boleh dibiarkan berbeda dari parent — bila folder Vue-nya tidak ada, frontend otomatis memakai layout parent.'),
                Forms\Components\FileUpload::make('preview_image')->image()->disk('public')->directory('themes'),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),

            Forms\Components\Tabs::make('Opsi Desain Default')->tabs([

                Forms\Components\Tabs\Tab::make('Warna & Font')->schema(array_merge([
                    Forms\Components\Placeholder::make('info_tokens')
                        ->label('')
                        ->content(function (?Theme $record) {
                            $base = 'Kosongkan = memakai bawaan tema (tokens.js di frontend). Isi hanya kalau mau menimpa TAMPILAN DASAR tema ini.';
                            if (! $record) {
                                return $base;
                            }
                            $childCount = $record->children()->whereNotNull('invitation_id')->count();
                            if ($childCount === 0) {
                                return $base;
                            }

                            return new \Illuminate\Support\HtmlString($base . " <strong style=\"color:#b45309\">Perhatian:</strong> tema ini dipakai {$childCount} undangan yang SUDAH punya kustomisasi tampilan sendiri — warna yang diubah di sini TIDAK akan terlihat di undangan itu (kustomisasi undangan selalu menang). Untuk mengubah tampilan SATU undangan tertentu, buka undangannya lalu klik tombol \"Edit Tampilan\".");
                        }),
                ], ThemeOptionsSchema::colorFields('default_options'), ThemeOptionsSchema::typographyFields('default_options')))->columns(3),

                Forms\Components\Tabs\Tab::make('Ornamen & Latar')->schema([
                    Forms\Components\Placeholder::make('info_cover')
                        ->label('')
                        ->content('Default untuk SEMUA undangan bertema ini. Tiap undangan bisa menimpanya lewat form Undangan. (Ornamen di dalam kartu sudah DIHAPUS dari produk.)'),
                    Forms\Components\Select::make('default_options.background.ornament_asset')
                        ->label('Ornamen background halaman — dari Pustaka')
                        ->options(fn () => ThemeAsset::optionsFor('ornament') + ThemeAsset::optionsFor('divider'))
                        ->searchable()->placeholder('— pilih dari pustaka —')
                        ->helperText('Lapis ornamen transparan di latar halaman (foto pengantin diatur per-undangan).'),
                    Forms\Components\Select::make('default_options.florals.tl')
                        ->label('Floral atas 1 (kiri-atas)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('default_options.florals.tr')
                        ->label('Floral atas 2 (kanan-atas)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('default_options.florals.bl')
                        ->label('Floral bawah 1 (kiri-bawah)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('default_options.florals.br')
                        ->label('Floral bawah 2 (kanan-bawah)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Toggle::make('default_options.layout.card')
                        ->label('Gunakan kartu mengambang')->default(true)
                        ->helperText('Nonaktif: tiap section tampil layar penuh tanpa kartu.'),
                ])->columns(3),

                Forms\Components\Tabs\Tab::make('Countdown & Kartu')->schema(array_merge([
                    Forms\Components\Select::make('default_options.countdown.style')
                        ->label('Gaya angka countdown')
                        ->options(ThemeOptionsSchema::COUNTDOWN_STYLES)
                        ->placeholder('Bulat (default)'),
                    Forms\Components\Select::make('default_options.countdown.layout')
                        ->label('Isi section countdown')
                        ->options(ThemeOptionsSchema::COUNTDOWN_LAYOUTS)
                        ->placeholder('Sederhana'),
                    Forms\Components\Select::make('default_options.animation.preset')
                        ->label('Preset animasi scroll')
                        ->options(ThemeOptionsSchema::ANIMATION_PRESETS)
                        ->placeholder('Muncul dari bawah (default)'),
                    Forms\Components\Select::make('default_options.layout.hero_card')
                        ->label('Kartu untuk HERO')
                        ->options(ThemeOptionsSchema::HERO_CARD_MODES)
                        ->placeholder('Ikut pengaturan konten'),
                    Forms\Components\Select::make('default_options.layout.section_height')
                        ->label('Tinggi section')
                        ->options(ThemeOptionsSchema::SECTION_HEIGHTS)
                        ->placeholder('Satu layar penuh (default)'),
                ], ThemeOptionsSchema::cardFields('default_options', ThemeOptionsSchema::CARD_STYLES)))->columns(3),

                Forms\Components\Tabs\Tab::make('Label Teks')
                    ->schema(ThemeOptionsSchema::labelFields('default_options'))->columns(2),

                Forms\Components\Tabs\Tab::make('Section Aktif')->schema(array_merge([
                    Forms\Components\Placeholder::make('info_sections')
                        ->label('')
                        ->content('Section mana yang aktif SECARA DEFAULT untuk tema ini. Tiap undangan bisa menimpanya sendiri-sendiri lewat checklist "Section Aktif" di form Undangan.'),
                ], ThemeOptionsSchema::sectionVisibilityFields('default_options')))->columns(3),

            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('preview_image'),
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('component_key')->badge(),
            Tables\Columns\TextColumn::make('tier')->badge(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ThemeResource\Pages\ListThemes::route('/'),
            'create' => ThemeResource\Pages\CreateTheme::route('/create'),
            'edit'   => ThemeResource\Pages\EditTheme::route('/{record}/edit'),
        ];
    }
}
