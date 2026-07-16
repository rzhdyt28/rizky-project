<?php

namespace App\Filament\Widgets;

use App\Core\Models\Payment;
use App\Core\Models\Tenant;
use App\Modules\Invitation\Models\Invitation;
use App\Modules\Invitation\Models\Rsvp;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Ringkasan lintas modul untuk Dashboard kustom. */
class PlatformStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Tenant terdaftar', Tenant::count())
                ->description('Total pelanggan platform')
                ->icon('heroicon-o-users'),
            Stat::make('Undangan publish', Invitation::withoutGlobalScope('tenant')->where('status', 'published')->count())
                ->description('Modul Invitation')
                ->icon('heroicon-o-envelope-open'),
            Stat::make('RSVP hari ini', Rsvp::whereDate('created_at', today())->count())
                ->description('Konfirmasi tamu masuk')
                ->icon('heroicon-o-check-badge'),
            Stat::make('Pendapatan bulan ini', 'Rp '.number_format(
                    (int) Payment::where('status', 'settlement')->whereMonth('created_at', now()->month)->sum('gross_amount'), 0, ',', '.'))
                ->description('Pembayaran settlement')
                ->icon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
