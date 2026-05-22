<?php

namespace App\Models\Lookups;

class AssetCondition extends BaseLookup
{
    protected $table = 'asset_conditions';

    public function getColour(): string
    {
        return match ($this->code) {
            'excellent', 'new' => 'success',
            'good' => 'info',
            'fair' => 'warning',
            'poor', 'damaged', 'broken' => 'danger',
            default => 'gray',
        };
    }
}
