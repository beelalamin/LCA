<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssetStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Assets', Asset::count())
                ->description('All recorded assets')
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('primary'),
                
            Stat::make('Available', Asset::where('status', 'AVAILABLE')->count())
                ->description('Ready for assignment')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Assigned', Asset::where('status', 'ASSIGNED')->count())
                ->description('Currently checked out')
                ->descriptionIcon('heroicon-m-arrow-right-on-rectangle')
                ->color('warning'),
                
            Stat::make('In Repair', Asset::where('status', 'IN_REPAIR')->count())
                ->description('Currently undergoing maintenance')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('danger'),
        ];
    }
}
