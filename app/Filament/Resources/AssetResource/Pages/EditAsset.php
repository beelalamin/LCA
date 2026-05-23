<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use Filament\Actions;
use Filament\Forms\Components\Wizard\Step;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\HasWizard;

class EditAsset extends EditRecord
{
    use HasWizard;

    protected static string $resource = AssetResource::class;

    protected function getSteps(): array
    {
        return [
            Step::make(__('Identification'))
                ->icon('heroicon-o-identification')
                ->schema(AssetResource::identificationSchema()),
            Step::make(__('Specs'))
                ->icon('heroicon-o-cog-6-tooth')
                ->schema(AssetResource::specsSchema()),
            Step::make(__('Financial & Warranty'))
                ->icon('heroicon-o-banknotes')
                ->schema(AssetResource::financialSchema()),
            Step::make(__('Location'))
                ->icon('heroicon-o-map-pin')
                ->schema(AssetResource::locationSchema()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
