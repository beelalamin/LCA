<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\EmploymentTypeResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\EmploymentType;
use Filament\Resources\Resource;

class EmploymentTypeResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = EmploymentType::class;
    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?int $navigationSort = 45;

    public static function getModelLabel(): string { return __('Employment Type'); }
    public static function getPluralModelLabel(): string { return __('Employment Types'); }
    public static function getNavigationLabel(): string { return __('Employment Types'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEmploymentTypes::route('/'),
        ];
    }
}
