<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\WarrantyProviderResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\WarrantyProvider;
use Filament\Resources\Resource;

class WarrantyProviderResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = WarrantyProvider::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';
    protected static ?int $navigationSort = 85;

    public static function getModelLabel(): string { return __('Warranty Provider'); }
    public static function getPluralModelLabel(): string { return __('Warranty Providers'); }
    public static function getNavigationLabel(): string { return __('Warranty Providers'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWarrantyProviders::route('/'),
        ];
    }
}
