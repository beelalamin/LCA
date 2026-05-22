<?php

namespace App\Services\Lookups;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LookupResolver
{
    /** @var array<string, array<string, string>> */
    protected array $cache = [];

    /**
     * Find or create a lookup row matching `$name` (case-insensitive) on the given model class.
     */
    public function resolveOrCreate(string $modelClass, ?string $name, array $extra = []): ?string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $key = $modelClass.'|'.strtolower($name);
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $instance = new $modelClass;

        $existing = $modelClass::query()
            ->where(fn ($q) => $q
                ->whereRaw('LOWER(code) = ?', [Str::slug($name, '_')])
                ->orWhereRaw("LOWER(json_extract(name, '$.en')) = ?", [strtolower($name)])
                ->orWhereRaw("LOWER(json_extract(name, '$.ar')) = ?", [strtolower($name)]))
            ->first();

        if ($existing) {
            return $this->cache[$key] = $existing->id;
        }

        $row = array_merge([
            'code' => $this->uniqueCode($modelClass, $name),
            'name' => ['en' => $name],
            'is_active' => true,
            'sort_order' => 999,
        ], $extra);

        $instance = $modelClass::create($row);

        return $this->cache[$key] = $instance->id;
    }

    public function resolveCategory(?string $name, ?string $parentId = null): ?string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $key = 'category|'.($parentId ?? 'root').'|'.strtolower($name);
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $existing = Category::query()
            ->when($parentId, fn ($q) => $q->where('parent_id', $parentId))
            ->when(! $parentId, fn ($q) => $q->whereNull('parent_id'))
            ->whereRaw("LOWER(json_extract(name, '$.en')) = ?", [strtolower($name)])
            ->first();

        if ($existing) {
            return $this->cache[$key] = $existing->id;
        }

        $category = Category::create([
            'name' => ['en' => $name],
            'parent_id' => $parentId,
        ]);

        return $this->cache[$key] = $category->id;
    }

    protected function uniqueCode(string $modelClass, string $name): string
    {
        $base = Str::slug($name, '_') ?: 'item';
        $base = substr($base, 0, 60);
        $code = $base;
        $i = 2;

        while ($modelClass::where('code', $code)->exists()) {
            $code = $base.'_'.$i;
            $i++;
        }

        return $code;
    }
}
