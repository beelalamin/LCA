<?php

namespace App\Models\Lookups;

use App\Models\Asset;

class DisposalReason extends BaseLookup
{
    protected $table = 'disposal_reasons';

    public function usages(): array
    {
        return [
            [Asset::class, 'disposal_reason_id'],
        ];
    }
}
