<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\QuoteApiClient;
use App\DTOs\QuoteDTO;

/**
 * ZenQuotesClient (TEMPLATE)
 *
 * Implement the "real" client for the ZenQuotes **free** API.
 * Requirements (see CHALLENGE.md for details):
 *  - Use only free endpoints (e.g., /api/today and /api/quotes).
 *  - Share cache with web/API using keys:
 *      - 'qod.current'  (Quote of the Day)
 *      - 'quotes.batch' (random quotes batch)
 *  - TTL = config('quotes.ttl') seconds (default 30).
 *  - Controllers already clear cache keys on `?new=1`.
 *  - Normalize responses to QuoteDTO; set `cached` flag appropriately.
 *  - Add basic resilience: bounded retries/backoff for 429/timeouts; fallback to cached data.
 *  - Log upstream failures at warning level.
 *
 * Do NOT use premium endpoints.
 */
final class ZenQuotesClient implements QuoteApiClient
{
    /** Base URL for the ZenQuotes free API. */
    private string $base = 'https://zenquotes.io/api';

    /** Cache keys shared across surfaces. */
    private const KEY_QOD   = 'qod.current';
    private const KEY_BATCH = 'quotes.batch';

    /**
     * Fetch the Quote of the Day.
     *
     * Expectations:
     *  - Read from cache if present; otherwise fetch from `${base}/today`, normalize, and cache.
     *  - Normalize to QuoteDTO (see QuoteDTO::fromZenQuotes()).
     *  - Set the DTO's `cached` flag based on whether the value came from cache.
     *  - Handle transient errors (429/timeouts) with bounded retries/backoff.
     *
     * @return QuoteDTO
     */
    public function fetchQuoteOfTheDay(): QuoteDTO
    {
        // TODO (candidate):
        //  1) Determine TTL: $ttl = (int) config('quotes.ttl', 30);
        //  2) If cached under self::KEY_QOD, return cached DTO (with cached=true).
        //  3) Otherwise HTTP GET `${base}/today` (free endpoint), handle 429/timeouts with retry/backoff.
        //  4) Normalize the first item of the JSON array to QuoteDTO (cached=false), store in cache for $ttl, and return it.
        //  5) If upstream fails and cache exists, fall back to cached DTO; otherwise propagate a controlled error.
        throw new \LogicException('Not implemented: fetchQuoteOfTheDay()');
    }

    /**
     * Fetch a list of random quotes (count up to 10).
     *
     * Expectations:
     *  - Maintain a shared "batch" cache under self::KEY_BATCH (array of raw quote rows).
     *  - On cache hit: mark returned DTOs as cached=true; on miss: fetch `/api/quotes` once, cache the array, then sample.
     *  - Shuffle and take `$count` items from the cached batch to avoid extra network calls.
     *  - Handle 429/timeouts with bounded retries/backoff; fall back to existing cached batch when possible.
     *
     * @param  int  $count  Number of quotes requested (controllers/API bound to 5/10 and auth rules).
     * @return array<QuoteDTO>
     */
    public function fetchRandomQuotes(int $count = 10): array
    {
        // TODO (candidate):
        //  1) Determine TTL: $ttl = (int) config('quotes.ttl', 30);
        //  2) Read array batch from cache (self::KEY_BATCH). If missing:
        //       - HTTP GET `${base}/quotes` (free endpoint), with retry/backoff on 429/timeouts.
        //       - Validate/normalize the array shape; cache the raw batch for $ttl.
        //  3) Shuffle the batch and take $count items.
        //  4) Return array_map(fn($row) => QuoteDTO::fromZenQuotes($row, $cached), $subset)
        //     where $cached is true iff self::KEY_BATCH existed before this call.
        //  5) On upstream failure with no cache, propagate a controlled error; with cache, return cached subset.
        throw new \LogicException('Not implemented: fetchRandomQuotes()');
    }

    // You may add private helper methods if useful, for example:
    //
    // private function getWithRetry(string $url): array { ... }
    // private function isRateLimited(\Throwable $e): bool { ... }
    // private function ttl(): int { return (int) config('quotes.ttl', 30); }
}
