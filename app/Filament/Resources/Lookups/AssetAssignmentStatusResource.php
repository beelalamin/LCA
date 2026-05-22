<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\AssetAssignmentStatusResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\AssetAssignmentStatus;
use Filament\Resources\Resource;

class AssetAssignmentStatusResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = AssetAssignmentStatus::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?int $navigationSort = 90;

    public static function getModelLabel(): string { return __('Asset Assignment Status'); }
    public static function getPluralModelLabel(): string { return __('Asset Assignment Statuses'); }
    public static function getNavigationLabel(): string { return __('Asset Assignment Statuses'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageAssetAssignmentStatuses::route('/'),
        ];
    }
}
