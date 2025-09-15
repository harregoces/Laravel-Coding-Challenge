<?php

declare(strict_types=1);

namespace App\Services\Stubs;

use App\Contracts\QuoteApiClient;
use App\DTOs\QuoteDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * ZenQuotesClientStub
 *
 * A local, offline implementation of QuoteApiClient that uses
 * JSON fixtures from database/fixtures and the shared cache keys.
 *
 * Intent:
 *  - Enable running tests and the app without network calls.
 *  - Mirror cache semantics (keys, TTL) used by the real client.
 *  - Keep behavior simple; no HTTP, no retry/backoff logic here.
 *
 * Controllers clear cache keys on `?new=1`. This stub simply
 * reads/sets cache and marks DTOs as cached based on whether the
 * value existed *before* this call.
 */
final class ZenQuotesClientStub implements QuoteApiClient
{
    /** Cache keys shared with the real client. */
    private const KEY_QOD   = 'qod.current';
    private const KEY_BATCH = 'quotes.batch';

    /** @var string absolute path to QOD fixture */
    private string $qodPath;

    /** @var string absolute path to quotes batch fixture */
    private string $batchPath;

    public function __construct()
    {
        $this->qodPath   = database_path('fixtures/qod.json');
        $this->batchPath = database_path('fixtures/quotes.json');
    }

    public function fetchQuoteOfTheDay(): QuoteDTO
    {
        $ttl = (int) config('quotes.ttl', 30);

        // IMPORTANT: check BEFORE remember() so we can flag DTO as cached or not
        $wasCached = Cache::has(self::KEY_QOD);

        $row = Cache::remember(self::KEY_QOD, now()->addSeconds($ttl), function () {
            $data = $this->readJsonArray($this->qodPath);
            // qod.json is an array; take first element or fallback
            return $data[0] ?? ['q' => 'Hello world', 'a' => 'Anonymous'];
        });

        return QuoteDTO::fromZenQuotes($row, $wasCached);
    }

    /**
     * @return array<QuoteDTO>
     */
    public function fetchRandomQuotes(int $count = 10): array
    {
        $ttl = (int) config('quotes.ttl', 30);

        // Clamp count to a sane range; API layer enforces auth for >5
        $count = max(1, min(10, $count));

        $wasCached = Cache::has(self::KEY_BATCH);

        $batch = Cache::remember(self::KEY_BATCH, now()->addSeconds($ttl), function () {
            $data = $this->readJsonArray($this->batchPath);
            // Ensure array-of-arrays with ZenQuotes-like shape ['q' => ..., 'a' => ...]
            return is_array($data) ? array_values($data) : [];
        });

        // Sample locally without additional "API calls"
        if (!empty($batch)) {
            shuffle($batch);
        }
        $subset = array_slice($batch, 0, $count);

        return array_map(
            fn (array $row) => QuoteDTO::fromZenQuotes($row, $wasCached),
            $subset
        );
    }

    /**
     * Best-effort JSON reader that returns an array (or empty array on error).
     */
    private function readJsonArray(string $path): array
    {
        try {
            if (!File::exists($path)) {
                return [];
            }
            $raw = File::get($path);
            $data = json_decode($raw, true);
            return is_array($data) ? $data : [];
        } catch (\Throwable $e) {
            // Silent fallback for the stub: return empty to keep tests deterministic
            return [];
        }
    }
}
