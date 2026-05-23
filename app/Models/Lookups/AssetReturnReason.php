<?php

namespace App\Models\Lookups;

use App\Models\Asset;
use App\Models\Transaction;

class AssetReturnReason extends BaseLookup
{
    protected $table = 'asset_return_reasons';

    public function usages(): array
    {
        return [
            [Asset::class, 'return_reason_id'],
            [Transaction::class, 'return_reason_id'],
        ];
    }
}
