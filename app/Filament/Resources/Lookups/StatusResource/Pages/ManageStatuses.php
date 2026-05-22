<?php

namespace App\Filament\Resources\Lookups\StatusResource\Pages;

use App\Filament\Resources\Lookups\StatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageStatuses extends ManageRecords
{
    protected static string $resource = StatusResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
