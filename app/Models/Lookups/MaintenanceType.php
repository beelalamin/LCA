<?php

namespace App\Models\Lookups;

use App\Models\Asset;
use App\Models\Transaction;

class MaintenanceType extends BaseLookup
{
    protected $table = 'maintenance_types';

    public function usages(): array
    {
        return [
            [Asset::class, 'maintenance_type_id'],
            [Transaction::class, 'maintenance_type_id'],
        ];
    }
}
