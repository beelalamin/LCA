<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('Activity Logs');
    }

    public static function getModelLabel(): string
    {
        return __('Activity Log');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Activity Logs');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('System Administration');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('performed_at')
                    ->label(__('Performed At'))
                    ->dateTime()
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('asset.name')
                    ->label(__('Asset'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('performedBy.full_name')
                    ->label(__('User'))
                    ->default(__('System'))
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'REGISTERED' => 'REGISTERED',
                        'CHECKED_OUT' => 'CHECKED_OUT',
                        'CHECKED_IN' => 'CHECKED_IN',
                        'STATUS_CHANGED' => 'STATUS_CHANGED',
                        'MAINTENANCE_SCHEDULED' => 'MAINTENANCE_SCHEDULED',
                        'MAINTENANCE_COMPLETED' => 'MAINTENANCE_COMPLETED',
                        'BULK_IMPORTED' => 'BULK_IMPORTED',
                    ]),
                Tables\Filters\SelectFilter::make('performed_by')
                    ->relationship('performedBy', 'full_name'),
                Filter::make('performed_at_range')
                    ->label(__('Date Range'))
                    ->form([
                        Forms\Components\Select::make('preset')
                            ->label(__('Preset'))
                            ->options([
                                'last_day' => __('Last 24 hours'),
                                'last_week' => __('Last week'),
                                'last_month' => __('Last month'),
                                'last_year' => __('Last year'),
                                'custom' => __('Custom range'),
                            ])
                            ->live(),
                        Forms\Components\DatePicker::make('from')
                            ->label(__('From'))
                            ->visible(fn ($get) => $get('preset') === 'custom'),
                        Forms\Components\DatePicker::make('to')
                            ->label(__('To'))
                            ->visible(fn ($get) => $get('preset') === 'custom'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $preset = $data['preset'] ?? null;

                        if ($preset === 'last_day') {
                            return $query->where('performed_at', '>=', now()->subDay());
                        }
                        if ($preset === 'last_week') {
                            return $query->where('performed_at', '>=', now()->subWeek());
                        }
                        if ($preset === 'last_month') {
                            return $query->where('performed_at', '>=', now()->subMonth());
                        }
                        if ($preset === 'last_year') {
                            return $query->where('performed_at', '>=', now()->subYear());
                        }
                        if ($preset === 'custom') {
                            if (! empty($data['from'])) {
                                $query->whereDate('performed_at', '>=', $data['from']);
                            }
                            if (! empty($data['to'])) {
                                $query->whereDate('performed_at', '<=', $data['to']);
                            }
                            return $query;
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (! empty($data['preset']) && $data['preset'] !== 'custom') {
                            $indicators[] = __('Range: :preset', ['preset' => str_replace('_', ' ', $data['preset'])]);
                        }
                        if (! empty($data['from'])) {
                            $indicators[] = __('From: :date', ['date' => $data['from']]);
                        }
                        if (! empty($data['to'])) {
                            $indicators[] = __('To: :date', ['date' => $data['to']]);
                        }
                        return $indicators;
                    }),
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('performed_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAuditLogs::route('/'),
        ];
    }
}
