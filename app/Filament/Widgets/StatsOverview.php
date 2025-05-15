<?php

namespace App\Filament\Widgets;

use App\Models\Contract;
use App\Models\Correspondence;
use App\Models\Correspondence_log;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = -2;   // keeps this widget first

    protected function getStats(): array
    {
        return [
            // 1) جميع العقود
            Stat::make('إجمالي العقود', Contract::count())
                ->description('عدد العقود المسجلة')
                ->icon('heroicon-o-document-text')
                ->color('primary'),

            // 2) جميع المراسلات
            Stat::make('إجمالي المراسلات', Correspondence::count())
                ->description('عدد المراسلات المسجلة')
                ->icon('heroicon-o-inbox-stack')
                ->color('success'),

            // 3) سجل المراسلات
            // Stat::make('سجلات المراسلات', Correspondence_log::count())
            //     ->description('إجمالي عمليات المراسلة بالموقع')
            //     ->icon('heroicon-o-clipboard-document-list')
            //     ->color('info'),

            // 4) العقود النشطة (تنتهي في المستقبل أو اليوم)
            Stat::make(
                'العقود النشطة',
                Contract::whereDate('end_date', '>=', Carbon::today())->count()
            )
                ->description('العقود الجارية حالياً')
                ->icon('heroicon-o-check-badge')
                ->color('warning'),

            // 5) المراسلات قيد الانتظار (status = pending)
            Stat::make(
                'المراسلات قيد الانتظار',
                Correspondence::where('status', 'قيد الانتظار')->count()
            )
                ->description('تحتاج إلى إجراء')
                ->icon('heroicon-o-clock')
                ->color('danger'),
        ];
    }
}
