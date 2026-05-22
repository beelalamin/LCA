<?php

namespace App\Filament\Resources\Lookups\DisposalReasonResource\Pages;

use App\Filament\Resources\Lookups\DisposalReasonResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDisposalReasons extends ManageRecords
{
    protected static string $resource = DisposalReasonResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
