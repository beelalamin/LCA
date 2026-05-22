<?php

namespace App\Filament\Resources\Lookups\AssetModelResource\Pages;

use App\Filament\Resources\Lookups\AssetModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAssetModels extends ManageRecords
{
    protected static string $resource = AssetModelResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
