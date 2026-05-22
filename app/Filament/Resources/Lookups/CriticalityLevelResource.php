<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\CriticalityLevelResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\CriticalityLevel;
use Filament\Resources\Resource;

class CriticalityLevelResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = CriticalityLevel::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?int $navigationSort = 70;

    public static function getModelLabel(): string { return __('Criticality Level'); }
    public static function getPluralModelLabel(): string { return __('Criticality Levels'); }
    public static function getNavigationLabel(): string { return __('Criticality Levels'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCriticalityLevels::route('/'),
        ];
    }
}
