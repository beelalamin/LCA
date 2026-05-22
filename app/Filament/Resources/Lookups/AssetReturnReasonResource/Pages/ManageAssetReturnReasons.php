<?php

namespace App\Filament\Resources\Lookups\AssetReturnReasonResource\Pages;

use App\Filament\Resources\Lookups\AssetReturnReasonResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAssetReturnReasons extends ManageRecords
{
    protected static string $resource = AssetReturnReasonResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
