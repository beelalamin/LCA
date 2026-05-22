<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\Lookups\AssetAssignmentStatus;
use App\Models\Lookups\MaintenanceStatus;
use App\Models\Lookups\Status;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssetKpiWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $activeAssets = Asset::where('is_active', true);

        $totalCount = (clone $activeAssets)->count();
        $totalValue = (clone $activeAssets)->sum('purchase_cost');

        $availStatusId = AssetAssignmentStatus::where('code', 'available')->value('id');
        $assignedStatusId = AssetAssignmentStatus::where('code', 'assigned')->value('id');
        $maintenanceStatusId = MaintenanceStatus::where('code', 'in_progress')->value('id');

        $available = $availStatusId ? Asset::where('assignment_status_id', $availStatusId)->count() : 0;
        $assigned = $assignedStatusId ? Asset::where('assignment_status_id', $assignedStatusId)->count() : 0;
        $inMaintenance = $maintenanceStatusId ? Asset::where('maintenance_status_id', $maintenanceStatusId)->count() : 0;
        $disposed = Asset::whereNotNull('disposal_date')->count();

        return [
            Stat::make(__('Total Assets'), $totalCount)
                ->description(__('Active assets'))
                ->descriptionIcon('heroicon-m-computer-desktop')
                ->color('primary'),
            Stat::make(__('Total Asset Value'), 'QAR ' . number_format((float) $totalValue, 2))
                ->description(__('Sum of purchase cost'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make(__('Available'), $available)
                ->description(__('Ready for assignment'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
            Stat::make(__('Assigned'), $assigned)
                ->description(__('Currently checked out'))
                ->descriptionIcon('heroicon-m-user'),
            Stat::make(__('In Maintenance'), $inMaintenance)
                ->description(__('Currently being repaired'))
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('warning'),
            Stat::make(__('Disposed'), $disposed)
                ->description(__('Lifecycle complete'))
                ->descriptionIcon('heroicon-m-archive-box-x-mark')
                ->color('danger'),
        ];
    }
}
