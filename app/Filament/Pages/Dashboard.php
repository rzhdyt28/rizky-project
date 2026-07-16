<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

/**
 * HALAMAN DASHBOARD KUSTOM (menggantikan dashboard default Filament).
 * View-nya bebas didesain di resources/views/filament/pages/dashboard.blade.php.
 * Widget di-render di dalamnya (stats semua modul dalam satu layar).
 */
class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Pusat Monitoring';
    protected static ?int $navigationSort = -2;
    protected static string $view = 'filament.pages.dashboard';
    // protected static ?string $slug = '/';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\PlatformStats::class,
            \App\Filament\Widgets\RevenueChart::class,
            \App\Filament\Widgets\AgentStats::class,
        ];
    }
}
