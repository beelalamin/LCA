<?php

namespace App\Models\Lookups;

use App\Models\Asset;
use App\Models\Transaction;
use App\Models\User;

class Department extends BaseLookup
{
    protected $table = 'departments';

    public function usages(): array
    {
        return [
            [User::class, 'department_id'],
            [Asset::class, 'department_id'],
            [Transaction::class, 'department_id'],
        ];
    }
}
