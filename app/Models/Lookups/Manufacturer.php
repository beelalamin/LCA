<?php

namespace App\Models\Lookups;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Manufacturer extends BaseLookup
{
    protected $table = 'manufacturers';

    public function models(): HasMany
    {
        return $this->hasMany(AssetModel::class, 'manufacturer_id');
    }
}
