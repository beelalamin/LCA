<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\OfficeLocationResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\OfficeLocation;
use Filament\Resources\Resource;

class OfficeLocationResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = OfficeLocation::class;
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?int $navigationSort = 50;

    public static function getModelLabel(): string { return __('Office Location'); }
    public static function getPluralModelLabel(): string { return __('Office Locations'); }
    public static function getNavigationLabel(): string { return __('Office Locations'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOfficeLocations::route('/'),
        ];
    }
}
