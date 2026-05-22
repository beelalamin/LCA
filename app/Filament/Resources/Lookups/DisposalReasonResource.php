<?php

namespace App\Filament\Resources\Lookups;

use App\Filament\Resources\Lookups\DisposalReasonResource\Pages;
use App\Filament\Resources\Lookups\Concerns\LookupResourceSchema;
use App\Models\Lookups\DisposalReason;
use Filament\Resources\Resource;

class DisposalReasonResource extends Resource
{
    use LookupResourceSchema;

    protected static ?string $model = DisposalReason::class;
    protected static ?string $navigationIcon = 'heroicon-o-trash';
    protected static ?int $navigationSort = 105;

    public static function getModelLabel(): string { return __('Disposal Reason'); }
    public static function getPluralModelLabel(): string { return __('Disposal Reasons'); }
    public static function getNavigationLabel(): string { return __('Disposal Reasons'); }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDisposalReasons::route('/'),
        ];
    }
}
