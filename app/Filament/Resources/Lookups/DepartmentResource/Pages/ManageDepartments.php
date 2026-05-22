<?php

namespace App\Filament\Resources\Lookups\DepartmentResource\Pages;

use App\Filament\Resources\Lookups\DepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDepartments extends ManageRecords
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
