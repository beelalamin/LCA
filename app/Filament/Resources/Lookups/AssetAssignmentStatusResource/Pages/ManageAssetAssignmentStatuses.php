<?php

namespace App\Filament\Resources\Lookups\AssetAssignmentStatusResource\Pages;

use App\Filament\Resources\Lookups\AssetAssignmentStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAssetAssignmentStatuses extends ManageRecords
{
    protected static string $resource = AssetAssignmentStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
