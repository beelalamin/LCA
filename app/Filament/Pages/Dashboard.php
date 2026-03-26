<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AssetStatsWidget;
use App\Filament\Widgets\AssetsByCategoryChart;
use App\Filament\Widgets\AssetsByConditionChart;
use App\Filament\Widgets\AssetsByStatusChart;
use App\Filament\Widgets\RecentAuditLogsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            AssetStatsWidget::class,
            // AssetsByStatusChart::class,
            // AssetsByCategoryChart::class,
            // AssetsByConditionChart::class,
            RecentAuditLogsWidget::class,
        ];
    }
}
