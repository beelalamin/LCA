<?php

namespace App\Filament\Resources\Lookups\MaintenanceTypeResource\Pages;

use App\Filament\Resources\Lookups\MaintenanceTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMaintenanceTypes extends ManageRecords
{
    protected static string $resource = MaintenanceTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
