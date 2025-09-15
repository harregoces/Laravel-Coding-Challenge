<?php

namespace App\Services\Stubs;

use App\Contracts\QuoteApiClient;
use App\DTOs\QuoteDTO;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class ZenQuotesClientStub implements QuoteApiClient
{
    public function fetchQuoteOfTheDay(): QuoteDTO
    {
        $key = 'qod.current';
        $isCached = Cache::has($key);
        $dto = Cache::remember($key, now()->addSeconds(config('quotes.ttl')), function () {
            $path = database_path('fixtures/qod.json');
            $data = json_decode(File::get($path), true);
            $row = $data[0] ?? ['q' => 'Hello world', 'a' => 'Anonymous'];
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
            $path = database_path('fixtures/quotes.json');
            return json_decode(File::get($path), true);
        });

        shuffle($batch);
        $subset = array_slice($batch, 0, $count);

        return array_map(fn($row) => QuoteDTO::fromZenQuotes($row, $isCached), $subset);
    }
}
