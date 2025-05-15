<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class RoleDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'توزيع الأدوار';

    

    /** Make the chart smaller if you like */
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Users per Role',
                    'data'  => [35, 20, 15, 10, 5],   // dummy numbers
                    'backgroundColor' => [
                        '#3b82f6', // blue
                        '#10b981', // green
                        '#f59e0b', // amber
                        '#ef4444', // red
                        '#6366f1', // indigo
                    ],
                ],
            ],
            'labels' => ['Admin', 'Editor', 'Manager', 'Viewer', 'Guest'],
        ];
    }

    /** Tell Filament/Chart.js to render a pie */
    protected function getType(): string
    {
        return 'pie';
    }
}
