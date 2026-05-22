<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Lookups\Status;

class AssetService
{
    public function generateAssetTag(): string
    {
        $prefix = config('app.asset_tag_prefix', 'LC');
        $year = date('Y');
        $padding = config('app.asset_tag_padding', 5);

        $latest = Asset::whereYear('created_at', $year)->count();
        $sequence = str_pad((string) ($latest + 1), $padding, '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}-{$sequence}";
    }

    public function transitionStatus(Asset $asset, string $newStatusCode): void
    {
        $validTransitions = [
            'purchased' => ['available'],
            'available' => ['assigned', 'reserved', 'in_repair', 'retired'],
            'reserved'  => ['available', 'assigned'],
            'assigned'  => ['available', 'in_repair'],
            'in_repair' => ['available', 'retired'],
            'retired'   => ['disposed'],
        ];

        $currentCode = $asset->status?->code;

        if ($currentCode && ! in_array($newStatusCode, $validTransitions[$currentCode] ?? [])) {
            throw new \Exception("Invalid status transition from {$currentCode} to {$newStatusCode}");
        }

        $newStatus = Status::forAssets()->where('code', $newStatusCode)->first();

        if (! $newStatus) {
            throw new \Exception("Unknown asset status code: {$newStatusCode}");
        }

        $asset->update(['status_id' => $newStatus->id]);
    }

    public function resolveStatusId(string $code): ?string
    {
        return Status::forAssets()->where('code', $code)->value('id');
    }
}
