<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class UserRegistrationsChart extends ChartWidget
{
    protected static ?string $heading = 'تسجيلات المستخدمين';

    protected static ?string $maxHeight = '300px';

    

    protected int $refreshInterval = 60; // in seconds

    protected static bool $isLazy = true;

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'التسجيلات',
                    'data' => [5, 10, 15, 8, 25, 18, 30, 35, 40, 38, 42, 50],
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
