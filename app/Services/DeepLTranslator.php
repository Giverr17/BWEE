<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepLTranslator
{
    /** Words DeepL must leave exactly as they are. */
    private array $protected = [
        "Black Women's Enterprise Exchange",
        'Black Women’s Enterprise Exchange',
        'BWEE',
        'WYAF',
        'School of Women in Leadership & Business',
        'The Nikki Clarke Network',
    ];

    /**
     * Translate English fields to French.
     * Input:  ['title' => 'Hello', 'caption' => 'World']
     * Output: same keys in French, or null if something failed.
     */
    public function translate(array $fields): ?array
    {
        $fields = array_filter($fields, fn ($v) => is_string($v) && trim($v) !== '');

        if ($fields === []) {
            return [];
        }

        $key = config('services.deepl.key');
        if (! $key) {
            Log::warning('DeepLTranslator: DEEPL_API_KEY is missing.');

            return null;
        }

        $result = [];
        $pending = [];

        // Same text twice costs nothing: check the cache first.
        foreach ($fields as $name => $text) {
            $cached = Cache::get($this->cacheKey($text));
            if ($cached !== null) {
                $result[$name] = $cached;
            } else {
                $pending[$name] = $text;
            }
        }

        if ($pending !== []) {
            $translated = $this->callDeepL($pending, $key);
            if ($translated === null) {
                return null;
            }
            foreach ($translated as $name => $french) {
                Cache::put($this->cacheKey($pending[$name]), $french, now()->addDay());
                $result[$name] = $french;
            }
        }

        // Keep the original field order.
        return array_replace(array_fill_keys(array_keys($fields), null), $result);
    }

    /** Characters used and allowed this month, or null if unavailable. */
    public function usage(): ?array
    {
        try {
            $r = Http::withHeaders(['Authorization' => 'DeepL-Auth-Key '.config('services.deepl.key')])
                ->timeout(8)
                ->get(rtrim(config('services.deepl.url'), '/').'/v2/usage');

            return $r->successful()
                ? ['used' => $r->json('character_count'), 'limit' => $r->json('character_limit')]
                : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function callDeepL(array $pending, string $key): ?array
    {
        $names = array_keys($pending);
        $texts = array_map(fn ($t) => $this->protect($t), array_values($pending));

        try {
            $response = Http::withHeaders(['Authorization' => 'DeepL-Auth-Key '.$key])
                ->timeout(15)
                ->post(rtrim(config('services.deepl.url'), '/').'/v2/translate', [
                    'text' => $texts,
                    'source_lang' => 'EN',
                    'target_lang' => 'FR',
                    'tag_handling' => 'xml',
                    'ignore_tags' => ['keep'],
                    'preserve_formatting' => true,
                ]);
        } catch (\Throwable $e) {
            Log::warning('DeepLTranslator request failed: '.$e->getMessage());

            return null;
        }

        if (! $response->successful()) {
            Log::warning('DeepLTranslator HTTP '.$response->status().': '.$response->body());

            return null;
        }

        $items = $response->json('translations');
        if (! is_array($items) || count($items) !== count($names)) {
            return null;
        }

        $out = [];
        foreach ($names as $i => $name) {
            $french = $this->restore($items[$i]['text'] ?? '');
            if (trim($french) === '') {
                return null;
            }
            $out[$name] = $french;
        }

        return $out;
    }

    /** Escape the text for XML and wrap protected words so DeepL skips them. */
    private function protect(string $text): string
    {
        $text = htmlspecialchars($text, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $patterns = array_map(fn ($p) => preg_quote(htmlspecialchars($p, ENT_NOQUOTES), '/'), $this->protected);
        $patterns[] = '[\w.+-]+@[\w-]+\.[\w.-]+';     // emails
        $patterns[] = 'https?:\/\/[^\s<]+';           // links

        return preg_replace('/('.implode('|', $patterns).')/u', '<keep>$1</keep>', $text);
    }

    public function restore(string $text): string
    {
        $text = preg_replace('#</?keep>#', '', $text);
        $text = htmlspecialchars_decode($text, ENT_NOQUOTES);

        // All named BWEE people are women, so use the feminine French titles.
        $text = preg_replace('/\b(?:le|Le) (Dre|Dr\.?) /u', 'la Dre ', $text);
        $text = preg_replace('/\b(?:du) (Dre|Dr\.?) /u', 'de la Dre ', $text);
        $text = preg_replace('/\bDr\.? /u', 'Dre ', $text);
        $text = preg_replace('/\bProf\.? /u', 'Professeure ', $text);

        return trim(str_replace("\u{2014}", ',', $text));
    }

    private function cacheKey(string $text): string
    {
        return 'deepl:fr:'.md5($text);
    }
}
