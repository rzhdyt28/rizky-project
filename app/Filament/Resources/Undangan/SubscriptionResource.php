<?php

namespace App\Filament\Resources\Undangan;

use App\Core\Models\Plan;
use App\Core\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Pengganti sementara Midtrans: admin bisa memberi tenant sebuah paket AKTIF
 * secara manual, tanpa alur pembayaran apa pun.
 *
 * Cara kerjanya memakai apa yang sudah ada, tidak menambah logika baru:
 * Tenant::activeSubscription() (lihat Tenant.php) mengambil record dengan
 * status='active' DAN ends_at > now(). Begitu record di sini disimpan dengan
 * kriteria itu, seluruh sistem fitur (PlanLimitService::featuresFor(),
 * middleware EnsureSubscriptionActive) langsung mengenalinya secara otomatis.
 * Nanti kalau Midtrans dipasang, cukup buat Subscription lewat controller
 * pembayaran -- resource ini tidak perlu diubah / dihapus.
 */
class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'Undangan - Komersial';
    protected static ?string $navigationLabel = 'Aktivasi Paket';
    protected static ?string $modelLabel = 'Aktivasi Paket';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('tenant_id')
                ->label('Pelanggan (Tenant)')
                ->relationship('tenant', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('plan_id')
                ->label('Paket')
                ->relationship('plan', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(function (Forms\Set $set, $state) {
                    // Saat paket dipilih, otomatis isi "Berakhir" sesuai
                    // duration_days milik paket itu -- tetap bisa diubah manual.
                    $plan = Plan::find($state);
                    if ($plan?->duration_days) {
                        $set('ends_at', now()->addDays($plan->duration_days));
                    }
                }),

            Forms\Components\Select::make('status')
                ->options([
                    'active'    => 'Aktif',
                    'pending'   => 'Menunggu',
                    'expired'   => 'Kedaluwarsa',
                    'cancelled' => 'Dibatalkan',
                ])
                ->default('active')
                ->required(),

            Forms\Components\DateTimePicker::make('starts_at')
                ->label('Mulai')
                ->default(now())
                ->required(),

            Forms\Components\DateTimePicker::make('ends_at')
                ->label('Berakhir')
                ->default(now()->addMonth())
                ->required()
                ->helperText('Tenant dianggap AKTIF selama status = Aktif DAN tanggal ini belum lewat.'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')->label('Pelanggan')->searchable(),
                Tables\Columns\TextColumn::make('plan.name')->label('Paket')->badge(),
                Tables\Columns\TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    'active'               => 'success',
                    'pending'              => 'warning',
                    'expired', 'cancelled' => 'danger',
                    default                => 'gray',
                }),
                Tables\Columns\TextColumn::make('ends_at')->label('Berakhir')->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('ends_at', 'desc')
            ->actions([
                // Aksi cepat: perpanjang tanpa buka form edit -- ini "toggle" praktisnya.
                Tables\Actions\Action::make('extend_month')
                    ->label('+1 Bulan')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Subscription $record) {
                        $base = ($record->ends_at && $record->ends_at->isFuture()) ? $record->ends_at : now();
                        $record->update(['status' => 'active', 'ends_at' => $base->copy()->addMonth()]);
                    }),

                Tables\Actions\Action::make('extend_year')
                    ->label('+1 Tahun')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Subscription $record) {
                        $base = ($record->ends_at && $record->ends_at->isFuture()) ? $record->ends_at : now();
                        $record->update(['status' => 'active', 'ends_at' => $base->copy()->addYear()]);
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => SubscriptionResource\Pages\ListSubscriptions::route('/'),
            'create' => SubscriptionResource\Pages\CreateSubscription::route('/create'),
            'edit'   => SubscriptionResource\Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}