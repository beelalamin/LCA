<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ActivityTabsWidget;
use App\Filament\Widgets\AssetAlertsWidget;
use App\Filament\Widgets\AssetKpiWidget;
use App\Filament\Widgets\ChartsTabsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?int $navigationSort = 0;

    public function getWidgets(): array
    {
        return [
            AssetKpiWidget::class,
            ChartsTabsWidget::class,
            AssetAlertsWidget::class,
            ActivityTabsWidget::class,
        ];
    }
}
