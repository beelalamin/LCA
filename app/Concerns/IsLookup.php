<?php

namespace App\Concerns;

trait IsLookup
{
    public function getTranslatedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $name = $this->getTranslation('name', $locale, false);

        if (! $name) {
            $name = $this->getTranslation('name', 'en', false);
        }

        return (string) ($name ?? $this->code ?? '');
    }

    public function getTranslatedDescription(?string $locale = null): ?string
    {
        if (! $this->description) {
            return null;
        }

        $locale = $locale ?? app()->getLocale();
        $description = $this->getTranslation('description', $locale, false);

        if (! $description) {
            $description = $this->getTranslation('description', 'en', false);
        }

        return $description;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }
}
