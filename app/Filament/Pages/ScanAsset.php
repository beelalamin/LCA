<?php

namespace App\Filament\Pages;

use App\Models\Asset;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ScanAsset extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static string $view = 'filament.pages.scan-asset';
    protected static ?string $navigationLabel = 'Scan / Search Asset';
    protected static ?string $title = 'Scan or Search Asset';

    public ?string $asset_tag = null;

    public function findAsset(): void
    {
        $asset = Asset::where('asset_tag', $this->asset_tag)
            ->orWhere('serial_number', $this->asset_tag)
            ->first();

        if ($asset) {
            $this->redirect(AssetResource::getUrl('edit', ['record' => $asset]));
        } else {
            Notification::make()
                ->title('Asset not found')
                ->danger()
                ->send();
            
            $this->asset_tag = null;
        }
    }

    protected function getActions(): array
    {
        return [
            Action::make('manual_search')
                ->form([
                    TextInput::make('tag')
                        ->label('Asset Tag / Serial Number')
                        ->required()
                        ->autofocus()
                ])
                ->action(function (array $data) {
                    $this->asset_tag = $data['tag'];
                    $this->findAsset();
                })
        ];
    }
}

