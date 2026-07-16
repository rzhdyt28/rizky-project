<?php

namespace App\Filament\Widgets;

use App\Core\Models\Payment;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Pendapatan 6 bulan terakhir';
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $rows = Payment::where('status', 'settlement')
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(gross_amount) as total")
            ->groupBy('ym')->orderBy('ym')->pluck('total', 'ym');

        return [
            'datasets' => [[ 'label' => 'Rupiah', 'data' => $rows->values(), 'fill' => true ]],
            'labels'   => $rows->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
