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
 * A local, offline implementation of QuoteApiClient that uses JSON fixtures (database/fixtures)
 * and the shared cache keys. Mirrors cache semantics used by the real client.
 *
 * Note:
 *  - No HTTP/network logic here.
 *  - No retry/backoff.
 *  - Keep behavior deterministic for tests.
 */
final class ZenQuotesClientStub implements QuoteApiClient
{
    private const KEY_QOD   = 'qod.current';
    private const KEY_BATCH = 'quotes.batch';

    private string $qodPath;
    private string $batchPath;

    public function __construct()
    {
        $this->qodPath   = database_path('fixtures/qod.json');
        $this->batchPath = database_path('fixtures/quotes.json');
    }

    public function fetchQuoteOfTheDay(): QuoteDTO
    {
        $ttl = (int) config('quotes.ttl', 30);
        $wasCached = Cache::has(self::KEY_QOD);

        $row = Cache::remember(self::KEY_QOD, now()->addSeconds($ttl), function () {
            $data = $this->readJsonArray($this->qodPath);
            return $data[0] ?? ['q' => 'Hello world', 'a' => 'Anonymous'];
        });

        return QuoteDTO::fromZenQuotes($row, $wasCached);
    }

    /** @return QuoteDTO[] */
    public function fetchRandomQuotes(int $count = 10): array
    {
        $ttl = (int) config('quotes.ttl', 30);
        $count = max(1, min(10, $count));

        $wasCached = Cache::has(self::KEY_BATCH);

        $batch = Cache::remember(self::KEY_BATCH, now()->addSeconds($ttl), function () {
            $data = $this->readJsonArray($this->batchPath);
            return is_array($data) ? array_values($data) : [];
        });

        if (!empty($batch)) {
            shuffle($batch);
        }
        $subset = array_slice($batch, 0, $count);

        return array_map(
            fn (array $row) => QuoteDTO::fromZenQuotes($row, $wasCached),
            $subset
        );
    }

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
            return [];
        }
    }
}
