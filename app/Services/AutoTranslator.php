<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Free automatic translator backed by MyMemory (https://mymemory.translated.net/doc/spec.php).
 * No API key required. Anonymous limit: 5000 chars/day per IP.
 * Increase to 50000 chars/day by setting services.mymemory.email in config.
 */
class AutoTranslator
{
    private const ENDPOINT = 'https://api.mymemory.translated.net/get';

    /**
     * Translate an associative array of strings in one batched call pool.
     *
     * @param  array<string, string>  $items  keyed strings (keys preserved in output)
     * @return array<string, string>          same keys, translated values; missing on failure
     */
    public function translateBatch(array $items, string $sourceLocale, string $targetLocale): array
    {
        $items = array_filter($items, fn ($v) => is_string($v) && trim($v) !== '');

        if (empty($items) || $sourceLocale === $targetLocale) {
            return $items;
        }

        $langpair = "{$sourceLocale}|{$targetLocale}";
        $email = config('services.mymemory.email');
        $keys = array_keys($items);

        try {
            $responses = Http::pool(function (Pool $pool) use ($items, $langpair, $email) {
                $requests = [];
                foreach ($items as $key => $text) {
                    $query = ['q' => $text, 'langpair' => $langpair];
                    if ($email) {
                        $query['de'] = $email;
                    }
                    $requests[$key] = $pool->as((string) $key)
                        ->timeout(12)
                        ->get(self::ENDPOINT, $query);
                }

                return $requests;
            });
        } catch (\Throwable $e) {
            Log::warning('AutoTranslator pool failed', ['error' => $e->getMessage()]);

            return [];
        }

        $translated = [];
        foreach ($keys as $key) {
            $response = $responses[$key] ?? null;
            if (! $response || ! $response->ok()) {
                continue;
            }
            $translatedText = data_get($response->json(), 'responseData.translatedText');
            if (is_string($translatedText) && trim($translatedText) !== '') {
                $translated[$key] = html_entity_decode($translatedText, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }

        return $translated;
    }
}
