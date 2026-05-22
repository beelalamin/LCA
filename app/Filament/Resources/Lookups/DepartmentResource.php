<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\DepartmentResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\Department;
use Filament\Resources\Resource;

class DepartmentResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = Department::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 35;

    public static function getModelLabel(): string { return __('Department'); }
    public static function getPluralModelLabel(): string { return __('Departments'); }
    public static function getNavigationLabel(): string { return __('Departments'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDepartments::route('/'),
        ];
    }
}
