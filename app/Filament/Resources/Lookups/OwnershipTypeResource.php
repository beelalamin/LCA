<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\OwnershipTypeResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\OwnershipType;
use Filament\Resources\Resource;

class OwnershipTypeResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = OwnershipType::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?int $navigationSort = 65;

    public static function getModelLabel(): string { return __('Ownership Type'); }
    public static function getPluralModelLabel(): string { return __('Ownership Types'); }
    public static function getNavigationLabel(): string { return __('Ownership Types'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOwnershipTypes::route('/'),
        ];
    }
}
