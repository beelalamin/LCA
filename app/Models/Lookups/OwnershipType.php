<?php

namespace App\Models\Lookups;

use App\Models\Asset;

class OwnershipType extends BaseLookup
{
    protected $table = 'ownership_types';

    public function usages(): array
    {
        return [
            [Asset::class, 'ownership_type_id'],
        ];
    }
}
