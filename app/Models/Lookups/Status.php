<?php

namespace App\Models\Lookups;

class Status extends BaseLookup
{
    protected $table = 'statuses';

    public function scopeForAssets($query)
    {
        return $query->where('scope', 'asset');
    }

    public function scopeForUsers($query)
    {
        return $query->where('scope', 'user');
    }

    public function getColour(): string
    {
        if ($this->color) {
            return $this->color;
        }

        return match ($this->code) {
            'purchased', 'reserved' => 'gray',
            'available', 'active' => 'success',
            'assigned' => 'info',
            'in_repair', 'maintenance' => 'warning',
            'retired', 'disposed', 'damaged', 'lost', 'inactive' => 'danger',
            default => 'gray',
        };
    }
}
