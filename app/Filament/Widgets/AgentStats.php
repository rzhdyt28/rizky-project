<?php

namespace App\Filament\Widgets;

use App\Modules\AgentMonitoring\Models\JobApplication;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Throwable;

/** Statistik Auto Apply Agent — aman bila file SQLite agent belum ada. */
class AgentStats extends BaseWidget
{
    protected function getStats(): array
    {
        try {
            return [
                Stat::make('Lamaran terkirim', JobApplication::where('status', 'applied')->count())
                    ->icon('heroicon-o-paper-airplane'),
                Stat::make('Scam diblokir', JobApplication::where('scam_status', 'BLOCK')->count())
                    ->icon('heroicon-o-shield-check')->color('danger'),
                Stat::make('Rata-rata match', round((float) JobApplication::avg('match_score'), 1).'%')
                    ->icon('heroicon-o-sparkles'),
            ];
        } catch (Throwable) {
            return [
                Stat::make('Auto Apply Agent', 'Offline')
                    ->description('Database agent belum tersinkron (cek AGENT_DB_PATH)')
                    ->color('gray'),
            ];
        }
    }
}
