<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\ContractsBarChart;

class Dashboard extends \Filament\Pages\Dashboard 
{
    protected function getHeaderWidgets(): array
    {
        return Filament::gerHeaderWidgets(); 
    }
}
