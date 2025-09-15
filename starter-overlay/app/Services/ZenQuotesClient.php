<?php

namespace App\Services;

use App\Contracts\QuoteApiClient;
use App\DTOs\QuoteDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ZenQuotesClient implements QuoteApiClient
{
    private string $base = 'https://zenquotes.io/api';

    public function fetchQuoteOfTheDay(): QuoteDTO
    {
        $key = 'qod.current';
        $isCached = Cache::has($key);
        $dto = Cache::remember($key, now()->addSeconds(config('quotes.ttl')), function () {
            $resp = Http::timeout(5)->get($this->base . '/today');
            $data = $resp->json();
            $row = is_array($data) ? ($data[0] ?? []) : [];
            return QuoteDTO::fromZenQuotes($row, false);
        });
        $dto->cached = $isCached;
        return $dto;
    }

    public function fetchRandomQuotes(int $count = 10): array
    {
        $key = 'quotes.batch';
        $isCached = Cache::has($key);
        $batch = Cache::remember($key, now()->addSeconds(config('quotes.ttl')), function () {
            $resp = Http::timeout(5)->get($this->base . '/quotes');
            $data = $resp->json();
            return is_array($data) ? $data : [];
        });

        shuffle($batch);
        $subset = array_slice($batch, 0, $count);

        return array_map(fn($row) => QuoteDTO::fromZenQuotes($row, $isCached), $subset);
    }
}
