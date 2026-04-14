<?php

namespace App\Models\Traits;

trait HasTranslations
{
    /**
     * Get a field value translated to the given locale.
     *
     * Falls back to the original field value (default Spanish) when the
     * translation does not exist.
     */
    public function getTranslated(string $field, string $locale, string $fallback = 'es'): string
    {
        if ($locale === $fallback) {
            return (string) ($this->{$field} ?? '');
        }

        $translations = $this->translations;

        if (is_string($translations)) {
            $translations = json_decode($translations, true);
        }

        $translated = $translations[$locale][$field] ?? null;

        if ($translated !== null && $translated !== '') {
            return (string) $translated;
        }

        return (string) ($this->{$field} ?? '');
    }
}
