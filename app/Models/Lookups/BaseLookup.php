<?php

namespace App\Models\Lookups;

use App\Concerns\IsLookup;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
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

    protected static function booted(): void
    {
        static::creating(function (BaseLookup $model) {
            if (empty($model->code)) {
                $model->code = static::generateCodeFromName($model);
            }

            if (! isset($model->attributes['sort_order']) || $model->sort_order === null) {
                $model->sort_order = static::nextSortOrder($model);
            }

            if (! isset($model->attributes['is_active'])) {
                $model->is_active = true;
            }
        });
    }

    protected static function generateCodeFromName(BaseLookup $model): string
    {
        $name = $model->getTranslation('name', 'en', false)
            ?: $model->getTranslation('name', app()->getLocale(), false)
            ?: 'item';

        $base = Str::slug($name, '_');
        $code = $base;
        $i = 2;

        $query = static::query()->where('code', $code);
        foreach (static::codeUniquenessScopeAttributes($model) as $attr => $value) {
            $query->where($attr, $value);
        }

        while ($query->exists()) {
            $code = $base . '_' . $i++;
            $query = static::query()->where('code', $code);
            foreach (static::codeUniquenessScopeAttributes($model) as $attr => $value) {
                $query->where($attr, $value);
            }
        }

        return $code;
    }

    protected static function nextSortOrder(BaseLookup $model): int
    {
        $query = static::query();
        foreach (static::codeUniquenessScopeAttributes($model) as $attr => $value) {
            $query->where($attr, $value);
        }

        return ((int) $query->max('sort_order')) + 10;
    }

    /**
     * Attributes used to scope auto-code uniqueness and next-sort lookups.
     * Override in subclasses that have a composite key (e.g. Status: scope+code).
     */
    protected static function codeUniquenessScopeAttributes(BaseLookup $model): array
    {
        return [];
    }
}
