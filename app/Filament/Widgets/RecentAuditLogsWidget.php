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
                Tables\Columns\TextColumn::make('performed_at')->dateTime(),
                Tables\Columns\TextColumn::make('action')->badge(),
                Tables\Columns\TextColumn::make('asset.name')->label('Asset'),
                Tables\Columns\TextColumn::make('performedBy.full_name')->label('User'),
            ])
            ->paginated(false);
    }
}
