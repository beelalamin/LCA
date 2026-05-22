<?php

namespace App\Models\Lookups;

class CriticalityLevel extends BaseLookup
{
    protected $table = 'criticality_levels';

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
