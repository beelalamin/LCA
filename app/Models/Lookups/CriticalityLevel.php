<?php

namespace App\Models\Lookups;

use App\Models\Asset;

class CriticalityLevel extends BaseLookup
{
    protected $table = 'criticality_levels';

    public function usages(): array
    {
        return [
            [Asset::class, 'criticality_id'],
        ];
    }

    public function getColour(): string
    {
        return match ($this->code) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'success',
            default => 'gray',
        };
    }
}
