<?php

namespace App\Filament\Resources\Lookups\MaintenanceStatusResource\Pages;

use App\Filament\Resources\Lookups\MaintenanceStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMaintenanceStatuses extends ManageRecords
{
    protected static string $resource = MaintenanceStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
