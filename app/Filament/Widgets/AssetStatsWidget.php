<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\Lookups\Status;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssetStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $statusIds = Status::forAssets()->pluck('id', 'code');

        $countByCode = function (string $code) use ($statusIds): int {
            $id = $statusIds[$code] ?? null;
            return $id ? Asset::where('status_id', $id)->count() : 0;
        };

        return [
            Stat::make(__('Total Assets'), Asset::count())
                ->description(__('All recorded assets'))
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('primary'),

            Stat::make(__('Available'), $countByCode('available'))
                ->description(__('Ready for assignment'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('Assigned'), $countByCode('assigned'))
                ->description(__('Currently checked out'))
                ->descriptionIcon('heroicon-m-arrow-right-on-rectangle')
                ->color('warning'),

            Stat::make(__('In Repair'), $countByCode('in_repair'))
                ->description(__('Currently undergoing maintenance'))
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('danger'),
        ];
    }
}
