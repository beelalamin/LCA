<?php

namespace App\Filament\Resources\Lookups\CriticalityLevelResource\Pages;

use App\Filament\Resources\Lookups\CriticalityLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCriticalityLevels extends ManageRecords
{
    protected static string $resource = CriticalityLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
