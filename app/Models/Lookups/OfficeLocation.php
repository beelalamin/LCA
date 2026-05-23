<?php

namespace App\Models\Lookups;

use App\Models\Asset;
use App\Models\Transaction;
use App\Models\User;

class OfficeLocation extends BaseLookup
{
    protected $table = 'office_locations';

    public function usages(): array
    {
        return [
            [User::class, 'office_location_id'],
            [Asset::class, 'office_location_id'],
            [Transaction::class, 'office_location_id'],
        ];
    }
}
