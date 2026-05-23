<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\Lookups\Status;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AssetKpiWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $totalCount = Asset::count();
        $totalValue = Asset::sum('purchase_cost');

        $availStatusId = Status::forAssignment()->where('code', 'available')->value('id');
        $assignedStatusId = Status::forAssignment()->where('code', 'assigned')->value('id');
        $maintenanceStatusId = Status::forMaintenance()->where('code', 'in_progress')->value('id');

        $available = $availStatusId ? Asset::where('assignment_status_id', $availStatusId)->count() : 0;
        $assigned = $assignedStatusId ? Asset::where('assignment_status_id', $assignedStatusId)->count() : 0;
        $inMaintenance = $maintenanceStatusId ? Asset::where('maintenance_status_id', $maintenanceStatusId)->count() : 0;
        $disposed = Asset::whereNotNull('disposal_date')->count();

        $staffQuery = User::query()->where('is_active', true)->whereNotNull('employee_number');
        $totalStaff = (clone $staffQuery)->count();
        $activeAssignments = Asset::whereNotNull('assigned_to_user_id')->count();
        $staffWithZero = (clone $staffQuery)->whereDoesntHave('activeAssignments')->count();

        return [
            Stat::make(__('Total Assets'), $totalCount)
                ->description(__('Registered assets'))
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
            Stat::make(__('Total Staff'), $totalStaff)
                ->description(__('Active employees'))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make(__('Active Assignments'), $activeAssignments)
                ->description(__('Current checkouts'))
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('info'),
            Stat::make(__('Staff with zero assignments'), $staffWithZero)
                ->description(__('Holding no assets'))
                ->descriptionIcon('heroicon-m-user-minus')
                ->color('gray'),
        ];
    }
}
