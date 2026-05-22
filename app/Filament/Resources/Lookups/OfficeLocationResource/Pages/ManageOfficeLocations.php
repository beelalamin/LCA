<?php

namespace App\Filament\Resources\Lookups\OfficeLocationResource\Pages;

use App\Filament\Resources\Lookups\OfficeLocationResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageOfficeLocations extends ManageRecords
{
    protected static string $resource = OfficeLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
