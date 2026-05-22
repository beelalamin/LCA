<?php

namespace App\Filament\Resources\Lookups\WarrantyStatusResource\Pages;

use App\Filament\Resources\Lookups\WarrantyStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWarrantyStatuses extends ManageRecords
{
    protected static string $resource = WarrantyStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
