<?php

namespace App\Models\Lookups;

class AssetAssignmentStatus extends BaseLookup
{
    protected $table = 'asset_assignment_statuses';

    public function getColour(): string
    {
        return match ($this->code) {
            'available' => 'success',
            'assigned', 'active' => 'info',
            'pending_return' => 'warning',
            'returned', 'closed' => 'gray',
            default => 'gray',
        };
    }
}
