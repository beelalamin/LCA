<?php

namespace App\Models\Lookups;

use App\Models\User;

class EmploymentType extends BaseLookup
{
    protected $table = 'employment_types';

    public function usages(): array
    {
        return [
            [User::class, 'employment_type_id'],
        ];
    }
}
