<?php

namespace App\Models\Lookups;

use App\Models\User;

class JobTitle extends BaseLookup
{
    protected $table = 'job_titles';

    public function usages(): array
    {
        return [
            [User::class, 'job_title_id'],
        ];
    }
}
