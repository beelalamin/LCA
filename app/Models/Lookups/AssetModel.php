<?php

namespace App\Models\Lookups;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetModel extends BaseLookup
{
    protected $table = 'models';

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }
}
