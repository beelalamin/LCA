<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\MaintenanceStatusResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\MaintenanceStatus;
use Filament\Resources\Resource;

class MaintenanceStatusResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = MaintenanceStatus::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?int $navigationSort = 75;

    public static function getModelLabel(): string { return __('Maintenance Status'); }
    public static function getPluralModelLabel(): string { return __('Maintenance Statuses'); }
    public static function getNavigationLabel(): string { return __('Maintenance Statuses'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMaintenanceStatuses::route('/'),
        ];
    }
}
