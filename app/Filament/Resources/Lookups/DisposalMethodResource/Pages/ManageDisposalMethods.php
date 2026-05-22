<?php

namespace App\Filament\Resources\Lookups\DisposalMethodResource\Pages;

use App\Filament\Resources\Lookups\DisposalMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDisposalMethods extends ManageRecords
{
    protected static string $resource = DisposalMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
