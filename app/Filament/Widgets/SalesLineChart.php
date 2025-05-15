<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class SalesLineChart extends ChartWidget
{
    protected static ?string $heading = 'مبيعات شهرية';

    // Limit height (no 'px')
    protected static ?string $maxHeight = '200';
    

    // Set width: 1 column on medium screens and up
    protected int|array|string $columnSpan = [
        'md' => 1,
    ];

    protected function getData(): array
    {
        return [
            'datasets' => [[
                'label' => 'المبيعات',
                'data' => [10, 25, 15, 40, 30, 50, 60],
                'borderColor' => '#3b82f6',
                'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                'fill' => true,
                'tension' => 0.4, // smooth line
            ]],
            'labels' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
