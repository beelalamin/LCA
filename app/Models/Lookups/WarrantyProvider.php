<?php

namespace App\Models\Lookups;

use App\Models\Asset;
use App\Models\Transaction;

class WarrantyProvider extends BaseLookup
{
    protected $table = 'warranty_providers';

    public function usages(): array
    {
        return [
            [Asset::class, 'warranty_provider_id'],
            [Transaction::class, 'warranty_provider_id'],
        ];
    }
}
