<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PeopleStatsWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    protected function getStats(): array
    {
        $staffQuery = User::query()
            ->where('is_active', true)
            ->whereNotNull('employee_number');

        $totalStaff = (clone $staffQuery)->count();
        $activeAssignments = Asset::whereNotNull('assigned_to_user_id')->count();
        $staffWithZero = (clone $staffQuery)
            ->whereDoesntHave('activeAssignments')
            ->count();

        return [
            Stat::make(__('Total Staff'), $totalStaff)->color('primary'),
            Stat::make(__('Active Assignments'), $activeAssignments)->color('info'),
            Stat::make(__('Staff with zero assignments'), $staffWithZero)->color('gray'),
        ];
    }
}
