<?php

namespace App\Filament\Resources;

use App\Modules\Invitation\Models\Invitation;
use App\Modules\Invitation\Models\Theme;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * UNDANGAN — murni DATA (v4 arsitektur). Semua pengaturan tampilan (warna,
 * font, gaya kartu, layout hero, background/section, dst) TIDAK LAGI di
 * sini — pindah ke InvitationLookResource ("Tampilan Undangan"), yang
 * mengedit child theme privat milik undangan ini (lihat
 * InvitationThemeProvisioner). Form ini isinya cuma: siapa pemiliknya,
 * data pasangan, dan toggle/isi yang genuinely konten (bukan gaya) —
 * acara/kisah/galeri/kado tetap lewat RelationManager di bawah, tidak
 * berubah.
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
                    ->label('Tema')
                    ->options(fn () => Theme::whereNull('invitation_id')->pluck('name', 'id'))
                    ->afterStateHydrated(function (Forms\Components\Select $component, ?Invitation $record) {
                        // theme_id sebenarnya milik CHILD theme privat undangan ini
                        // (lihat InvitationThemeProvisioner) — yang ditampilkan/dipilih
                        // di sini adalah TEMA DASAR-nya (parent_id child theme itu).
                        // $record null saat form Create (belum ada record) -- guard dulu
                        // sebelum akses ->theme, jangan cuma null-safe di sisi kanan.
                        if ($record && $record->theme?->invitation_id === $record->id) {
                            $component->state($record->theme->parent_id);
                        }
                    })
                    ->required()->live()
                    ->helperText('Ganti tema dasar TIDAK menghapus kustomisasi tampilan undangan ini (child theme-nya cuma di-reparent).'),
                Forms\Components\Select::make('status')->options([
                    'draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived',
                ])->default('draft')->required(),
            ])->columns(2),

            Forms\Components\Section::make('Mempelai')
                ->description('Nama, orang tua, dan teks pembuka. Gaya tampilan (desain, foto mempelai) diatur lewat tombol "Edit Tampilan".')
                ->schema([
                    Forms\Components\TextInput::make('groom_name')->label('Mempelai Pria')->required(),
                    Forms\Components\TextInput::make('bride_name')->label('Mempelai Wanita')->required(),
                    Forms\Components\TextInput::make('groom_parents')->label('Orang Tua Pria'),
                    Forms\Components\TextInput::make('bride_parents')->label('Orang Tua Wanita'),
                    Forms\Components\Textarea::make('opening_text')->label('Teks Pembuka')->rows(3)->columnSpanFull(),
                ])->columns(2),

            Forms\Components\Section::make('Countdown')
                ->description('Hitung mundur di dalam isi undangan. Sumber tanggal: acara pertama tab "Events" di bawah halaman ini.')
                ->collapsed()
                ->schema([
                    Forms\Components\Placeholder::make('countdown_source_info')
                        ->label('Sumber tanggal countdown')
                        ->content(function (?Invitation $record) {
                            $event = $record?->events()->orderBy('sort_order')->first();
                            if (! $event) {
                                return new \Illuminate\Support\HtmlString(
                                    '<span style="color:#b45309">Belum ada acara diisi.</span> Scroll ke BAWAH halaman ini (setelah undangan disimpan) → buka tab <strong>"Acara"</strong> → tambah acara pertama dengan tanggal &amp; jam mulai. Countdown otomatis mengikuti tanggal itu.'
                                );
                            }

                            return new \Illuminate\Support\HtmlString(
                                'Mengikuti acara <strong>' . e($event->title) . '</strong>: <strong>'
                                . e(\Carbon\Carbon::parse($event->starts_at)->format('d M Y, H:i')) . '</strong> WIB — dari tab <strong>"Acara"</strong> di bawah halaman ini. Ubah tanggalnya di sana, bukan di sini.'
                            );
                        })->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Video')
                ->description('Bukan sekadar iframe polos: ada eyebrow, kalimat pengantar, dan credit pasangan di bawah video.')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('video_url')->url()->label('URL video (YouTube)')
                        ->helperText('Contoh: https://www.youtube.com/watch?v=xxxx — otomatis jadi embed.'),
                    Forms\Components\TextInput::make('theme_options.video.eyebrow')
                        ->label('Eyebrow (label kecil di atas judul)')
                        ->placeholder('Wedding Film'),
                    Forms\Components\Textarea::make('theme_options.video.caption')
                        ->label('Kalimat pengantar')->rows(2)
                        ->placeholder('Sepenggal momen perjalanan kami menuju hari bahagia. Selamat menyaksikan.')
                        ->helperText('Kosong = memakai kalimat default di atas. Di bawah video otomatis tampil credit: nama pasangan + tanggal acara.'),
                ])->columns(2),

            Forms\Components\Section::make('RSVP')
                ->description('Toggle di sini adalah SATU-SATUNYA saklar RSVP.')
                ->collapsed()
                ->schema([
                    Forms\Components\Toggle::make('rsvp_enabled')->label('RSVP aktif')->default(true),
                ]),

            Forms\Components\Section::make('Ucapan & Doa')
                ->description('Toggle di sini adalah SATU-SATUNYA saklar buku ucapan.')
                ->collapsed()
                ->schema([
                    Forms\Components\Toggle::make('guestbook_enabled')->label('Buku ucapan aktif')->default(true),
                ]),

            Forms\Components\Section::make('Turut Mengundang (Premium+)')
                ->collapsed()
                ->schema([
                    Forms\Components\Repeater::make('co_hosts')
                        ->label('Daftar nama')
                        ->schema([
                            Forms\Components\TextInput::make('name')->label('Nama')->required()
                                ->placeholder('mis. Kel. Besar Bpk. H. Ahmad'),
                            Forms\Components\Select::make('side')->label('Kriteria')
                                ->options([
                                    'pria'    => 'Pihak Pria',
                                    'wanita'  => 'Pihak Wanita',
                                    'spesial' => 'Tamu Spesial',
                                ])->default('pria')->required(),
                        ])->columns(2)
                        ->defaultItems(0)->columnSpanFull()
                        ->helperText('Tamu Spesial tampil paling atas; pihak pria & wanita 2 kolom di desktop, bertumpuk di HP.'),
                ]),

            Forms\Components\Section::make('Musik Latar (Premium+)')
                ->collapsed()
                ->schema([
                    Forms\Components\FileUpload::make('music_url')
                        ->label('Musik latar (mp3)')
                        ->disk('public')->directory('music')
                        ->acceptedFileTypes(['audio/mpeg', 'audio/mp3'])
                        ->maxSize(15360)
                        ->helperText('Upload file mp3 milik sendiri.'),
                ]),
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
            ->actions([
                Tables\Actions\Action::make('editLook')
                    ->label('Edit Tampilan')
                    ->icon('heroicon-o-paint-brush')
                    ->color('warning')
                    ->url(fn (Invitation $record) => $record->theme_id
                        ? InvitationLookResource::getUrl('edit', ['record' => $record->theme_id])
                        : null)
                    ->visible(fn (Invitation $record) => (bool) $record->theme_id),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
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
