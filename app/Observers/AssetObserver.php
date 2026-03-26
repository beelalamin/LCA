<?php

namespace App\Observers;

use App\Models\Asset;
use App\Services\LabelGenerationService;
use App\Services\AuditLogger;
use App\Services\AssetService;

class AssetObserver
{
    /**
     * Handle the Asset "creating" event.
     */
    public function creating(Asset $asset): void
    {
        if (empty($asset->asset_tag)) {
            $asset->asset_tag = app(AssetService::class)->generateAssetTag();
        }

        if (empty($asset->created_by)) {
            $asset->created_by = auth()->id();
        }
    }

    /**
     * Handle the Asset "created" event.
     */
    public function created(Asset $asset): void
    {
        // Generate labels automatically
        app(LabelGenerationService::class)->generateBoth($asset);

        // Map status changes if applicable, otherwise simple REGISTERED
        AuditLogger::log('REGISTERED', $asset, null, $asset->toArray());
    }

    /**
     * Handle the Asset "updated" event.
     */
    public function updated(Asset $asset): void
    {
        if ($asset->isDirty('status')) {
            AuditLogger::log(
                'STATUS_CHANGED', 
                $asset, 
                ['status' => $asset->getOriginal('status')], 
                ['status' => $asset->status]
            );
        }
    }

    /**
     * Handle the Asset "deleted" event.
     */
    public function deleted(Asset $asset): void
    {
        //
    }

    /**
     * Handle the Asset "restored" event.
     */
    public function restored(Asset $asset): void
    {
        //
    }

    /**
     * Handle the Asset "force deleted" event.
     */
    public function forceDeleted(Asset $asset): void
    {
        //
    }
}
