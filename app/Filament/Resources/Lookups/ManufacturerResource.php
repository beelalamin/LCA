<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\ManufacturerResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\Manufacturer;
use Filament\Resources\Resource;

class ManufacturerResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = Manufacturer::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?int $navigationSort = 20;

    public static function getModelLabel(): string { return __('Manufacturer'); }
    public static function getPluralModelLabel(): string { return __('Manufacturers'); }
    public static function getNavigationLabel(): string { return __('Manufacturers'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageManufacturers::route('/'),
        ];
    }
}
