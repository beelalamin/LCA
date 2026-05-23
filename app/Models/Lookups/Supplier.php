<?php

namespace App\Models\Lookups;

use App\Models\Asset;

class Supplier extends BaseLookup
{
    protected $table = 'suppliers';

    public function usages(): array
    {
        return [
            [Asset::class, 'supplier_id'],
        ];
    }
}
