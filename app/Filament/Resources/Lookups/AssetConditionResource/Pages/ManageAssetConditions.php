<?php

namespace App\Filament\Resources\Lookups\AssetConditionResource\Pages;

use App\Filament\Resources\Lookups\AssetConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAssetConditions extends ManageRecords
{
    protected static string $resource = AssetConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
