<?php

namespace App\Filament\Widgets;

use App\Models\AuditLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentAuditLogsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(AuditLog::query()->with(['asset', 'performedBy'])->latest('performed_at')->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('performed_at')->label(__('Performed at'))->dateTime(),
                Tables\Columns\TextColumn::make('action')->label(__('Action'))->badge(),
                Tables\Columns\TextColumn::make('asset.name')->label(__('Asset')),
                Tables\Columns\TextColumn::make('performedBy.full_name')
                    ->label(__('User'))
                    ->default(__('System')),
            ])
            ->heading(__('Recent Audit Logs'))
            ->paginated(false);
    }
}
