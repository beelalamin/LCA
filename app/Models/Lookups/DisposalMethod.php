<?php

namespace App\Models\Lookups;

use App\Models\Asset;

class DisposalMethod extends BaseLookup
{
    protected $table = 'disposal_methods';

    public function usages(): array
    {
        return [
            [Asset::class, 'disposal_method_id'],
        ];
    }
}
