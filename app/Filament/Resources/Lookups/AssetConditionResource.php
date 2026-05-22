<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\AssetConditionResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\AssetCondition;
use Filament\Resources\Resource;

class AssetConditionResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = AssetCondition::class;
    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?int $navigationSort = 55;

    public static function getModelLabel(): string { return __('Asset Condition'); }
    public static function getPluralModelLabel(): string { return __('Asset Conditions'); }
    public static function getNavigationLabel(): string { return __('Asset Conditions'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAssetConditions::route('/'),
        ];
    }
}
