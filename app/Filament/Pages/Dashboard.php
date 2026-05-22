<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ActivityTabsWidget;
use App\Filament\Widgets\AssetAgeWidget;
use App\Filament\Widgets\AssetAlertsWidget;
use App\Filament\Widgets\AssetKpiWidget;
use App\Filament\Widgets\AssetsByCategoryChart;
use App\Filament\Widgets\AssetsByConditionChart;
use App\Filament\Widgets\AssetsByCriticalityChart;
use App\Filament\Widgets\AssetsByDepartmentChart;
use App\Filament\Widgets\AssetsByOfficeLocationChart;
use App\Filament\Widgets\PeopleStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?int $navigationSort = 0;

    public function getWidgets(): array
    {
        return [
            AssetKpiWidget::class,
            AssetAlertsWidget::class,
            AssetAgeWidget::class,
            AssetsByCategoryChart::class,
            AssetsByDepartmentChart::class,
            AssetsByOfficeLocationChart::class,
            AssetsByConditionChart::class,
            AssetsByCriticalityChart::class,
            PeopleStatsWidget::class,
            ActivityTabsWidget::class,
        ];
    }
}
