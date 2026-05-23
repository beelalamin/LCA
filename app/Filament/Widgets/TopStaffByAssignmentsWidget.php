<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopStaffByAssignmentsWidget extends BaseWidget
{
    protected static ?int $sort = 8;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereNotNull('employee_number')
                    ->select('users.*')
                    ->selectSub(
                        DB::table('assets')
                            ->selectRaw('count(*)')
                            ->whereColumn('assets.assigned_to_user_id', 'users.id'),
                        'active_assignments_count'
                    )
                    ->selectSub(
                        DB::table('assets')
                            ->selectRaw('coalesce(sum(purchase_cost), 0)')
                            ->whereColumn('assets.assigned_to_user_id', 'users.id'),
                        'held_value'
                    )
                    ->orderByDesc('active_assignments_count')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->label(__('Staff')),
                Tables\Columns\TextColumn::make('active_assignments_count')->label(__('Active Assignments')),
                Tables\Columns\TextColumn::make('held_value')
                    ->label(__('Held Value'))
                    ->formatStateUsing(fn ($state) => 'QAR ' . number_format((float) $state, 2)),
            ])
            ->heading(__('Top 10 staff by assignments'))
            ->paginated(false);
    }
}
