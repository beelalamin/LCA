<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentAuditLogsWidget extends BaseWidget
{
    protected static ?int $sort = 9;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(AuditLog::query()->with(['asset', 'performedBy'])->latest('performed_at')->limit(15))
            ->columns([
                Tables\Columns\TextColumn::make('performed_at')->label(__('Performed at'))->dateTime(),
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
                    ->url(fn ($record) => $record->asset_id
                        ? \App\Filament\Resources\AssetResource::getUrl('view', ['record' => $record->asset_id])
                        : null),
                Tables\Columns\TextColumn::make('performedBy.full_name')
                    ->label(__('User'))
                    ->default(__('System')),
            ])
            ->heading(__('Recent Activity'))
            ->paginated(false);
    }
}
