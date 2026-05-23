<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\Lookups\Status;
use App\Models\Transaction;
use App\Services\AssetService;
use App\Services\AuditLogger;
use App\Services\LabelGenerationService;

class AssetObserver
{
    public function creating(Asset $asset): void
    {
        if (empty($asset->asset_tag)) {
            $asset->asset_tag = app(AssetService::class)->generateAssetTag();
        }

        if (empty($asset->created_by)) {
            $asset->created_by = auth()->id();
        }

        if (empty($asset->status_id)) {
            $purchased = Status::forAssets()->where('code', 'purchased')->first();
            if ($purchased) {
                $asset->status_id = $purchased->id;
            }
        }

        if (empty($asset->assignment_status_id)) {
            $available = Status::forAssignment()->where('code', 'available')->first();
            if ($available) {
                $asset->assignment_status_id = $available->id;
            }
        }
    }

    public function created(Asset $asset): void
    {
        app(LabelGenerationService::class)->generateBoth($asset);

        AuditLogger::log('REGISTERED', $asset, null, $asset->toArray());

        if (! empty($asset->assigned_to_user_id)) {
            Transaction::create([
                'asset_id' => $asset->id,
                'type' => Transaction::TYPE_REGISTERED,
                'user_id' => $asset->assigned_to_user_id,
                'assigned_by' => auth()->id() ?? $asset->created_by,
                'assignment_status_id' => $asset->assignment_status_id,
                'condition_out_id' => $asset->condition_id,
            ]);
        }
    }

    public function updated(Asset $asset): void
    {
        if ($asset->isDirty('status_id')) {
            AuditLogger::log(
                'STATUS_CHANGED',
                $asset,
                ['status_id' => $asset->getOriginal('status_id')],
                ['status_id' => $asset->status_id]
            );
        }
    }

    public function deleted(Asset $asset): void
    {
    }

    public function restored(Asset $asset): void
    {
    }

    public function forceDeleted(Asset $asset): void
    {
    }
}
