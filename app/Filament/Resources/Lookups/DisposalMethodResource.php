<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\DisposalMethodResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\DisposalMethod;
use Filament\Resources\Resource;

class DisposalMethodResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = DisposalMethod::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box-x-mark';
    protected static ?int $navigationSort = 80;

    public static function getModelLabel(): string { return __('Disposal Method'); }
    public static function getPluralModelLabel(): string { return __('Disposal Methods'); }
    public static function getNavigationLabel(): string { return __('Disposal Methods'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDisposalMethods::route('/'),
        ];
    }
}
