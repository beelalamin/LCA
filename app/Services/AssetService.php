<?php

namespace App\Services;

use App\Models\Asset;
use App\Enums\AssetStatus;

class AssetService
{
    public function generateAssetTag(): string
    {
        // Simple logic for tag generation
        $prefix = config('app.asset_tag_prefix', 'LC');
        $year = date('Y');
        $padding = config('app.asset_tag_padding', 5);
        
        $latest = Asset::whereYear('created_at', $year)->count();
        $sequence = str_pad((string)($latest + 1), $padding, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$year}-{$sequence}";
    }

    public function transitionStatus(Asset $asset, AssetStatus $newStatus): void
    {
        $validTransitions = [
            AssetStatus::PURCHASED->value => [AssetStatus::AVAILABLE->value],
            AssetStatus::AVAILABLE->value => [AssetStatus::ASSIGNED->value, AssetStatus::IN_REPAIR->value, AssetStatus::RETIRED->value],
            AssetStatus::ASSIGNED->value  => [AssetStatus::AVAILABLE->value, AssetStatus::IN_REPAIR->value],
            AssetStatus::IN_REPAIR->value => [AssetStatus::AVAILABLE->value, AssetStatus::RETIRED->value],
            AssetStatus::RETIRED->value   => [AssetStatus::DISPOSED->value],
        ];

        $current = $asset->status;

        if (!in_array($newStatus->value, $validTransitions[$current] ?? [])) {
            throw new \Exception("Invalid status transition from {$current} to {$newStatus->value}");
        }

        $asset->update(['status' => $newStatus->value]);
    }
}
