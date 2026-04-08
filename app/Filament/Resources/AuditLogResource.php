<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                    ]),
                Tables\Filters\SelectFilter::make('performed_by')
                    ->relationship('performedBy', 'full_name'),
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
