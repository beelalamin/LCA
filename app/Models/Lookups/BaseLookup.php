<?php

namespace App\Models\Lookups;

use App\Concerns\IsLookup;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

abstract class BaseLookup extends Model
{
    use HasUuids, HasTranslations, IsLookup;

    protected $guarded = [];

    public $translatable = ['name', 'description'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
