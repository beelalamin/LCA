<?php

namespace App\Filament\Resources\Lookups\WarrantyProviderResource\Pages;

use App\Filament\Resources\Lookups\WarrantyProviderResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWarrantyProviders extends ManageRecords
{
    protected static string $resource = WarrantyProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
