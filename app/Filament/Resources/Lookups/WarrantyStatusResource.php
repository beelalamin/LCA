<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\WarrantyStatusResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\WarrantyStatus;
use Filament\Resources\Resource;

class WarrantyStatusResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = WarrantyStatus::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?int $navigationSort = 60;

    public static function getModelLabel(): string { return __('Warranty Status'); }
    public static function getPluralModelLabel(): string { return __('Warranty Statuses'); }
    public static function getNavigationLabel(): string { return __('Warranty Statuses'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWarrantyStatuses::route('/'),
        ];
    }
}
