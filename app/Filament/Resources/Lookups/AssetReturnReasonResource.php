<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\AssetReturnReasonResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\AssetReturnReason;
use Filament\Resources\Resource;

class AssetReturnReasonResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = AssetReturnReason::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?int $navigationSort = 95;

    public static function getModelLabel(): string { return __('Asset Return Reason'); }
    public static function getPluralModelLabel(): string { return __('Asset Return Reasons'); }
    public static function getNavigationLabel(): string { return __('Asset Return Reasons'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAssetReturnReasons::route('/'),
        ];
    }
}
