<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class DoughnutChartWidget extends ChartWidget
{
    protected static ?string $heading = 'توزيع متصفحات المستخدمين';

    

    // Limit the max height and width (adjust values as needed)
    protected static ?string $maxHeight = '300px';  // pixels
    protected static ?string $maxWidth = '300px';   // pixels

    protected function getData(): array
    {
        return [
            'labels' => ['Chrome', 'Firefox', 'Safari', 'Edge'],
            'datasets' => [[
                'data' => [55, 25, 15, 5],
                'backgroundColor' => ['#3b82f6', '#f97316', '#fbbf24', '#64748b'],
            ]],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
