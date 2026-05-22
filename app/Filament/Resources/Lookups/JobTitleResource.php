<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\JobTitleResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\JobTitle;
use Filament\Resources\Resource;

class JobTitleResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = JobTitle::class;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?int $navigationSort = 40;

    public static function getModelLabel(): string { return __('Job Title'); }
    public static function getPluralModelLabel(): string { return __('Job Titles'); }
    public static function getNavigationLabel(): string { return __('Job Titles'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageJobTitles::route('/'),
        ];
    }
}
