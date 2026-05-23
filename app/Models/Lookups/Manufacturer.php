<?php

namespace App\Models\Lookups;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manufacturer extends BaseLookup
{
    protected $table = 'manufacturers';

    public function models(): HasMany
    {
        return $this->hasMany(AssetModel::class, 'manufacturer_id');
    }

    public function usages(): array
    {
        return [
            [Asset::class, 'manufacturer_id'],
            [AssetModel::class, 'manufacturer_id'],
        ];
    }
}
