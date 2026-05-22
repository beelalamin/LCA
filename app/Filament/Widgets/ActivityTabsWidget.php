<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AssetResource;
use App\Models\AuditLog;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class ActivityTabsWidget extends BaseWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.activity-tabs';

    public string $activeTab = 'top_staff';

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    public function getTabs(): array
    {
        return [
            'top_staff' => [
                'label' => __('Top 10 Staff'),
                'icon' => 'heroicon-o-trophy',
            ],
            'recent_activity' => [
                'label' => __('Recent Activity'),
                'icon' => 'heroicon-o-bolt',
            ],
        ];
    }

    public function table(Table $table): Table
    {
        if ($this->activeTab === 'recent_activity') {
            return $this->recentActivityTable($table);
        }

        return $this->topStaffTable($table);
    }

    protected function topStaffTable(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereNotNull('employee_number')
                    ->select('users.*')
                    ->selectSub(
                        DB::table('assignments')
                            ->selectRaw('count(*)')
                            ->whereColumn('assignments.user_id', 'users.id')
                            ->where('is_active', true),
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
                Tables\Columns\TextColumn::make('full_name')
                    ->label(__('Staff'))
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('department.code')
                    ->label(__('Department'))
                    ->formatStateUsing(fn ($state, $record) => $record->department?->getTranslatedName())
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('active_assignments_count')
                    ->label(__('Active Assignments'))
                    ->alignment('center')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('held_value')
                    ->label(__('Held Value'))
                    ->alignment('end')
                    ->formatStateUsing(fn ($state) => 'QAR ' . number_format((float) $state, 2)),
            ])
            ->paginated(false);
    }

    protected function recentActivityTable(Table $table): Table
    {
        return $table
            ->query(
                AuditLog::query()
                    ->with(['asset', 'performedBy'])
                    ->latest('performed_at')
                    ->limit(15)
            )
            ->columns([
                Tables\Columns\TextColumn::make('performed_at')
                    ->label(__('When'))
                    ->since()
                    ->tooltip(fn ($record) => $record->performed_at?->format('Y-m-d H:i')),
                Tables\Columns\TextColumn::make('action')
                    ->label(__('Action'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'REGISTERED' => 'info',
                        'CHECKED_OUT' => 'warning',
                        'CHECKED_IN' => 'success',
                        'STATUS_CHANGED' => 'gray',
                        'MAINTENANCE_SCHEDULED' => 'warning',
                        'MAINTENANCE_COMPLETED' => 'success',
                        'BULK_IMPORTED' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('asset.asset_tag')
                    ->label(__('Asset'))
                    ->weight('medium')
                    ->url(fn ($record) => $record->asset_id
                        ? AssetResource::getUrl('view', ['record' => $record->asset_id])
                        : null),
                Tables\Columns\TextColumn::make('performedBy.full_name')
                    ->label(__('User'))
                    ->default(__('System'))
                    ->color('gray'),
            ])
            ->paginated(false);
    }
}
