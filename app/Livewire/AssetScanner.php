<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Filament\Resources\AssetResource;
use Filament\Notifications\Notification;
use Livewire\Component;

class AssetScanner extends Component
{
    public ?string $asset_tag = null;

    public function findAsset(): void
    {
        $this->validate([
            'asset_tag' => 'required|string|min:2',
        ]);

        $asset = Asset::where('asset_tag', $this->asset_tag)
            ->orWhere('serial_number', $this->asset_tag)
            ->first();

        if ($asset) {
            $this->redirect(AssetResource::getUrl('view', ['record' => $asset]));
        } else {
            Notification::make()
                ->title(__('Asset not found'))
                ->danger()
                ->send();
            
            $this->asset_tag = null;
            $this->dispatch('resetScanner');
        }
    }

    public function render()
    {
        return view('livewire.asset-scanner');
    }
}
