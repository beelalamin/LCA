<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\MaintenanceTypeResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\MaintenanceType;
use Filament\Resources\Resource;

class MaintenanceTypeResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = MaintenanceType::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench';
    protected static ?int $navigationSort = 100;

    public static function getModelLabel(): string { return __('Maintenance Type'); }
    public static function getPluralModelLabel(): string { return __('Maintenance Types'); }
    public static function getNavigationLabel(): string { return __('Maintenance Types'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMaintenanceTypes::route('/'),
        ];
    }
}
