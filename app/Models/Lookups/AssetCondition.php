<?php

namespace App\Models\Lookups;

use App\Models\Asset;
use App\Models\Transaction;

class AssetCondition extends BaseLookup
{
    protected $table = 'asset_conditions';

    public function usages(): array
    {
        return [
            [Asset::class, 'condition_id'],
            [Transaction::class, 'condition_out_id'],
            [Transaction::class, 'condition_in_id'],
        ];
    }

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
