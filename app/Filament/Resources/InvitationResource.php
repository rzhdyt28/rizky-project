<?php

namespace App\Filament\Resources;

use App\Modules\Invitation\Models\Invitation;
use App\Modules\Invitation\Models\ThemeAsset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * UNDANGAN — form DISEDERHANAKAN (v2).
 * - Override warna: 3 picker saja, kosong = ikut tema.
 * - Sampul: DUA slot — (1) Ornamen: pilih dari Pustaka Aset ATAU upload
 *   sendiri; (2) Foto / Monogram: upload bebas.
 * - Fitur Background Global / per-section DIHAPUS dari produk (sumber bug).
 */
class InvitationResource extends Resource
{
    protected static ?string $model = Invitation::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Undangan';
    protected static ?string $navigationLabel = 'Undangan';
    protected static ?string $modelLabel = 'Undangan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Utama')->schema([
                Forms\Components\Select::make('tenant_id')
                    ->relationship('tenant', 'name')->required()->label('Pemilik'),
                Forms\Components\TextInput::make('slug')->required()->alphaDash()->unique(ignoreRecord: true)
                    ->helperText('Hanya huruf, angka, strip. URL: /i/{slug}'),
                Forms\Components\Select::make('theme_id')
                    ->relationship('theme', 'name')->required()->label('Tema'),
                Forms\Components\Select::make('status')->options([
                    'draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived',
                ])->default('draft')->required(),
            ])->columns(2),

            Forms\Components\Section::make('Mempelai')->schema([
                Forms\Components\TextInput::make('groom_name')->label('Mempelai Pria')->required(),
                Forms\Components\TextInput::make('bride_name')->label('Mempelai Wanita')->required(),
                Forms\Components\TextInput::make('groom_parents')->label('Orang Tua Pria'),
                Forms\Components\TextInput::make('bride_parents')->label('Orang Tua Wanita'),
                Forms\Components\Textarea::make('opening_text')->label('Teks Pembuka')->rows(3)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Ornamen di Dalam Kartu Sampul')
                ->description('Hiasan kecil di dalam kartu hero (opsional). Foto pengantin TIDAK di sini lagi — pindah ke "Background Halaman" di bawah.')
                ->schema([
                    Forms\Components\Select::make('theme_options.cover.ornament_asset')
                        ->label('Ornamen atas — dari Pustaka Aset')
                        ->options(fn () => ThemeAsset::optionsFor('ornament') + ThemeAsset::optionsFor('divider'))
                        ->searchable()->placeholder('— pilih dari pustaka —'),
                    Forms\Components\FileUpload::make('theme_options.cover.ornament_upload')
                        ->label('Ornamen atas — upload sendiri')
                        ->image()->disk('public')->directory('ornaments')
                        ->helperText('Kalau diisi, mengalahkan pilihan pustaka.'),
                    Forms\Components\FileUpload::make('theme_options.cover.ornament_bottom_upload')
                        ->label('Ornamen bawah (opsional)')
                        ->image()->disk('public')->directory('ornaments')
                        ->helperText('Slot tambahan di bawah tombol. Kosong = tidak tampil.'),
                ])->columns(3),

            Forms\Components\Section::make('Background Halaman')
                ->description('Latar DI LUAR kartu, 2 lapis: foto pengantin di belakang + ornamen transparan di depannya.')
                ->schema([
                    Forms\Components\FileUpload::make('theme_options.background.photo')
                        ->label('Lapis 1 — Foto pengantin (desktop)')
                        ->image()->disk('public')->directory('covers')
                        ->helperText('Lanskap 16:9, saran 1920×1080 px, ≤500KB (JPG/WebP). Subjek di tengah karena tepi bisa terpotong.'),
                    Forms\Components\FileUpload::make('theme_options.background.photo_mobile')
                        ->label('Lapis 1 — Foto versi HP (opsional)')
                        ->image()->disk('public')->directory('covers')
                        ->helperText('Potret 9:16, saran 1080×1920 px. Tampil menggantikan foto desktop di layar HP.'),
                    Forms\Components\Select::make('theme_options.background.ornament_asset')
                        ->label('Lapis 2 — Ornamen (dari Pustaka)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament') + ThemeAsset::optionsFor('divider'))
                        ->searchable()->placeholder('— pilih dari pustaka —'),
                    Forms\Components\FileUpload::make('theme_options.background.ornament_upload')
                        ->label('Lapis 2 — Ornamen (upload sendiri)')
                        ->image()->disk('public')->directory('ornaments')
                        ->helperText('Kalau diisi, mengalahkan pilihan pustaka.'),
                ])->columns(3),

            Forms\Components\Section::make('Floral 4 Sudut Halaman')
                ->description('Pilih floral untuk tiap sudut secara bebas dari Pustaka Aset. Kosongkan semua = memakai floral bawaan tema. Tanpa migrasi baru — tersimpan di kolom JSON yang sudah ada.')
                ->schema([
                    Forms\Components\Select::make('theme_options.florals.tl')
                        ->label('Floral atas 1 (kiri-atas)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('theme_options.florals.tr')
                        ->label('Floral atas 2 (kanan-atas)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('theme_options.florals.bl')
                        ->label('Floral bawah 1 (kiri-bawah)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                    Forms\Components\Select::make('theme_options.florals.br')
                        ->label('Floral bawah 2 (kanan-bawah)')
                        ->options(fn () => ThemeAsset::optionsFor('ornament'))
                        ->searchable()->placeholder('— tanpa floral —'),
                ])->columns(2),

            Forms\Components\Section::make('Tata Letak')
                ->schema([
                    Forms\Components\Toggle::make('theme_options.layout.card')
                        ->label('Kartu untuk section konten')->default(true)
                        ->helperText('Nonaktif: section tampil layar penuh tanpa kartu dan bisa diberi background sendiri.'),
                    Forms\Components\Select::make('theme_options.layout.hero_card')
                        ->label('Kartu untuk HERO (terpisah)')
                        ->options([
                            'inherit' => 'Ikut pengaturan konten',
                            'card'    => 'Selalu pakai kartu',
                            'plain'   => 'Tanpa kartu (full)',
                        ])->placeholder('Ikut pengaturan konten')
                        ->helperText('Hero bisa beda dari section lain — mis. hero full-foto, isi berkartu.'),
                    Forms\Components\Select::make('theme_options.layout.section_height')
                        ->label('Tinggi section')
                        ->options([
                            'full' => 'Satu layar penuh (default)',
                            'auto' => 'Setinggi konten (tanpa gap)',
                        ])->placeholder('Satu layar penuh (default)')
                        ->helperText('Pilih "Setinggi konten" untuk menghilangkan ruang kosong pada section pendek.'),
                ])->columns(3),

            Forms\Components\Section::make('Countdown')
                ->description('Gaya angka & isi section hitung mundur.')
                ->schema([
                    Forms\Components\Select::make('theme_options.countdown.style')
                        ->label('Gaya angka')
                        ->options([
                            'circle'  => '1. Bulat (default)',
                            'boxed'   => '2. Kotak berbingkai',
                            'minimal' => '3. Minimal — titik dua',
                            'pill'    => '4. Pil memanjang',
                            'flip'    => '5. Flip clock',
                        ])->placeholder('Bulat (default)'),
                    Forms\Components\Select::make('theme_options.countdown.layout')
                        ->label('Isi section countdown')->live()
                        ->options([
                            'simple' => '1. Sederhana',
                            'photo'  => '2. Foto + nama pasangan',
                            'date'   => '3. Tanggal besar',
                            'quote'  => '4. Kutipan pembuka',
                        ])->placeholder('Sederhana'),
                    Forms\Components\FileUpload::make('theme_options.countdown.photo')
                        ->label('Foto latar countdown')
                        ->image()->disk('public')->directory('section-bg')
                        ->visible(fn (Forms\Get $get) => $get('theme_options.countdown.layout') === 'photo'),
                    Forms\Components\Textarea::make('theme_options.countdown.quote')
                        ->label('Teks kutipan')->rows(2)
                        ->visible(fn (Forms\Get $get) => $get('theme_options.countdown.layout') === 'quote'),
                ])->columns(2),

            Forms\Components\Section::make('Kartu — Tampilan')
                ->description('Kustomisasi kartu mengambang. Kosongkan = bawaan tema.')
                ->collapsed()
                ->schema([
                    Forms\Components\ColorPicker::make('theme_options.card.bg')->label('Warna kartu'),
                    Forms\Components\TextInput::make('theme_options.card.opacity')->label('Opacity (%)')
                        ->numeric()->minValue(0)->maxValue(100)->placeholder('100'),
                    Forms\Components\TextInput::make('theme_options.card.radius')->label('Radius sudut (px)')
                        ->numeric()->minValue(0)->maxValue(80)->placeholder('28'),
                    Forms\Components\ColorPicker::make('theme_options.card.shadow_color')->label('Warna shadow'),
                    Forms\Components\Select::make('theme_options.card.shadow_size')->label('Ketebalan shadow')
                        ->options([
                            'none'   => 'Tanpa shadow',
                            'lembut' => 'Lembut',
                            'sedang' => 'Sedang (default)',
                            'kuat'   => 'Kuat',
                        ])->placeholder('Sedang (default)'),
                ])->columns(3),

            Forms\Components\Section::make('Animasi Scroll (GSAP)')
                ->schema([
                    Forms\Components\Select::make('theme_options.animation.preset')
                        ->label('Preset animasi section')
                        ->options([
                            'fade-up'    => 'Muncul dari bawah (default)',
                            'fade-down'  => 'Muncul dari atas',
                            'fade-left'  => 'Geser dari kanan',
                            'fade-right' => 'Geser dari kiri',
                            'zoom'       => 'Zoom lembut',
                            'none'       => 'Tanpa animasi',
                        ])->placeholder('Muncul dari bawah (default)'),
                ]),

            Forms\Components\Section::make('Tampilkan / Sembunyikan Section')
                ->description('Nonaktifkan section yang tidak ingin ditampilkan. Section premium tetap butuh fitur paketnya aktif.')
                ->collapsed()
                ->schema([
                    Forms\Components\Toggle::make('theme_options.sections.countdown.visible')->label('Countdown')->default(true),
                    Forms\Components\Toggle::make('theme_options.sections.couple.visible')->label('Mempelai')->default(true),
                    Forms\Components\Toggle::make('theme_options.sections.events.visible')->label('Acara')->default(true),
                    Forms\Components\Toggle::make('theme_options.sections.co_host.visible')->label('Turut Mengundang')->default(true),
                    Forms\Components\Toggle::make('theme_options.sections.love_story.visible')->label('Kisah Kami')->default(true),
                    Forms\Components\Toggle::make('theme_options.sections.gallery.visible')->label('Galeri')->default(true),
                    Forms\Components\Toggle::make('theme_options.sections.video.visible')->label('Video')->default(true),
                    Forms\Components\Toggle::make('theme_options.sections.rsvp.visible')->label('RSVP')->default(true),
                    Forms\Components\Toggle::make('theme_options.sections.guestbook.visible')->label('Ucapan & Doa')->default(true),
                    Forms\Components\Toggle::make('theme_options.sections.gift.visible')->label('Kado')->default(true),
                ])->columns(2),

            Forms\Components\Section::make('Background per Section (mode tanpa kartu)')
                ->description('Foto latar berbeda untuk tiap section. Hanya berlaku saat "kartu mengambang" dinonaktifkan.')
                ->collapsed()
                ->schema([
                    Forms\Components\FileUpload::make('theme_options.section_bg.countdown')->label('Countdown')->image()->disk('public')->directory('section-bg'),
                    Forms\Components\FileUpload::make('theme_options.section_bg.couple')->label('Mempelai')->image()->disk('public')->directory('section-bg'),
                    Forms\Components\FileUpload::make('theme_options.section_bg.events')->label('Acara')->image()->disk('public')->directory('section-bg'),
                    Forms\Components\FileUpload::make('theme_options.section_bg.co_host')->label('Turut Mengundang')->image()->disk('public')->directory('section-bg'),
                    Forms\Components\FileUpload::make('theme_options.section_bg.love_story')->label('Kisah Kami')->image()->disk('public')->directory('section-bg'),
                    Forms\Components\FileUpload::make('theme_options.section_bg.gallery')->label('Galeri')->image()->disk('public')->directory('section-bg'),
                    Forms\Components\FileUpload::make('theme_options.section_bg.video')->label('Video')->image()->disk('public')->directory('section-bg'),
                    Forms\Components\FileUpload::make('theme_options.section_bg.rsvp')->label('RSVP')->image()->disk('public')->directory('section-bg'),
                    Forms\Components\FileUpload::make('theme_options.section_bg.guestbook')->label('Ucapan & Doa')->image()->disk('public')->directory('section-bg'),
                    Forms\Components\FileUpload::make('theme_options.section_bg.gift')->label('Kado')->image()->disk('public')->directory('section-bg'),
                ])->columns(2),

            Forms\Components\Section::make('Turut Mengundang (Premium+)')->schema([
                Forms\Components\Repeater::make('co_hosts')
                    ->label('Daftar nama')
                    ->simple(Forms\Components\TextInput::make('name')->required()
                        ->placeholder('mis. Kel. Besar Bpk. H. Ahmad'))
                    ->defaultItems(0)
                    ->helperText('Tampil hanya jika paket pelanggan mengaktifkan fitur Turut Mengundang.'),
            ]),

            Forms\Components\Section::make('Media (Premium+)')->schema([
                Forms\Components\FileUpload::make('music_url')
                    ->label('Musik latar (mp3)')
                    ->disk('public')
                    ->directory('music')
                    ->acceptedFileTypes(['audio/mpeg', 'audio/mp3'])
                    ->maxSize(15360)
                    ->helperText('Upload file mp3 milik sendiri.'),
                Forms\Components\TextInput::make('video_url')->url()->label('URL video (YouTube)')
                    ->helperText('Contoh: https://www.youtube.com/watch?v=xxxx — otomatis diubah jadi embed.'),
            ])->columns(2),

            Forms\Components\Section::make('Fitur Interaksi')->schema([
                Forms\Components\Toggle::make('rsvp_enabled')->label('RSVP aktif')->default(true),
                Forms\Components\Toggle::make('guestbook_enabled')->label('Buku ucapan aktif')->default(true),
            ])->columns(2),

            Forms\Components\Section::make('Override Warna (opsional, mengalahkan tema)')
                ->collapsed()
                ->schema([
                    Forms\Components\ColorPicker::make('theme_options.colors.accent')->label('Aksen'),
                    Forms\Components\ColorPicker::make('theme_options.colors.paper')->label('Permukaan kartu'),
                    Forms\Components\ColorPicker::make('theme_options.colors.ink')->label('Teks'),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withoutGlobalScope('tenant'))
            ->columns([
                Tables\Columns\TextColumn::make('slug')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('groom_name')->label('Pria'),
                Tables\Columns\TextColumn::make('bride_name')->label('Wanita'),
                Tables\Columns\TextColumn::make('theme.name')->label('Tema')->badge(),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'published' => 'success', 'draft' => 'warning', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('rsvps_count')->counts('rsvps')->label('RSVP'),
                Tables\Columns\TextColumn::make('guests_count')->counts('guests')->label('Tamu'),
                Tables\Columns\TextColumn::make('tenant.name')->label('Pemilik'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived',
                ]),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getRelations(): array
    {
        return [
            InvitationResource\RelationManagers\EventsRelationManager::class,
            InvitationResource\RelationManagers\StoriesRelationManager::class,
            InvitationResource\RelationManagers\GiftsRelationManager::class,
            InvitationResource\RelationManagers\RsvpsRelationManager::class,
            InvitationResource\RelationManagers\GalleryPhotosRelationManager::class,
            InvitationResource\RelationManagers\GuestsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => InvitationResource\Pages\ListInvitations::route('/'),
            'create' => InvitationResource\Pages\CreateInvitation::route('/create'),
            'edit'   => InvitationResource\Pages\EditInvitation::route('/{record}/edit'),
        ];
    }
}
