<?php

namespace App\Filament\Widgets;

use App\Models\Assignment;
use App\Models\Employee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PeopleStatsWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    protected function getStats(): array
    {
        $totalStaff = Employee::where('is_active', true)->count();
        $activeAssignments = Assignment::where('is_active', true)->count();
        $staffWithZero = Employee::where('is_active', true)
            ->whereDoesntHave('activeAssignments')
            ->count();

        return [
            Stat::make(__('Total Staff'), $totalStaff)->color('primary'),
            Stat::make(__('Active Assignments'), $activeAssignments)->color('info'),
            Stat::make(__('Staff with zero assignments'), $staffWithZero)->color('gray'),
        ];
    }
}
