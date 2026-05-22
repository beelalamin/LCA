<?php

namespace App\Filament\Resources\Lookups\OwnershipTypeResource\Pages;

use App\Filament\Resources\Lookups\OwnershipTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageOwnershipTypes extends ManageRecords
{
    protected static string $resource = OwnershipTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
