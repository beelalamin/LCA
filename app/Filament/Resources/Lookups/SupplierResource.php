<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\SupplierResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\Supplier;
use Filament\Resources\Resource;

class SupplierResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = Supplier::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?int $navigationSort = 30;

    public static function getModelLabel(): string { return __('Vendor'); }
    public static function getPluralModelLabel(): string { return __('Vendors'); }
    public static function getNavigationLabel(): string { return __('Vendors'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSuppliers::route('/'),
        ];
    }
}
