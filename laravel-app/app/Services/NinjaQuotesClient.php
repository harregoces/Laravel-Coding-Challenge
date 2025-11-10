<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\QuoteApiClient;
use App\DTOs\QuoteDTO;

final class NinjaQuotesClient implements QuoteApiClient
{
    /** Base URL for the ZenQuotes free API. */
    private string $base = 'https://api.api-ninjas.com/v2';

    /** Endpoint for random quotes. */
    private string $randomQuoteEndpoint = 'randomquotes';

    /** Endpoint for quote of the day. */
    private string $quoteOfTheDayEndpoint = 'quoteoftheday';


    /** Cache keys shared across surfaces. */
    private const KEY_QOD   = 'qod.current';
    private const KEY_BATCH = 'quotes.batch';

    /** @inheritDoc */
    public function fetchQuoteOfTheDay(): QuoteDTO
    {
        $ttl = (int) config('quotes.ttl', 30);

        // Check cache first
        $cached = cache()->get(self::KEY_QOD);
        if ($cached !== null) {
            return QuoteDTO::fromZenQuotes($cached, true);
        }

        // Fetch from API Ninjas
        try {
            $response = $this->fetchWithRetry($this->quoteOfTheDayEndpoint);

            if (empty($response)) {
                throw new \RuntimeException('Empty response from API Ninjas');
            }

            // API Ninjas returns an array, take the first quote
            $quoteData = $response[0];

            // Normalize to expected format
            $normalized = [
                'text' => $quoteData['quote'] ?? '',
                'author' => $quoteData['author'] ?? null,
            ];

            // Cache the normalized data
            cache()->put(self::KEY_QOD, $normalized, $ttl);

            return QuoteDTO::fromZenQuotes($normalized, false);
        } catch (\Exception $e) {
            \Log::warning('Failed to fetch quote of the day from API Ninjas', [
                'error' => $e->getMessage(),
            ]);


            // Try to return cached data if available
            $fallback = cache()->get(self::KEY_QOD);
            if ($fallback !== null) {
                return QuoteDTO::fromZenQuotes($fallback, true);
            }

            throw new \RuntimeException('Unable to fetch quote of the day: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Fetch from API Ninjas with retry logic for rate limits and timeouts.
     *
     * @param string $endpoint
     * @param array<string, mixed> $params
     * @return array<int, array<string, mixed>>
     * @throws \RuntimeException
     */
    private function fetchWithRetry(string $endpoint, array $params = []): array
    {
        $apiKey = config('services.api_ninjas.key');
        if (empty($apiKey)) {
            throw new \RuntimeException('API Ninjas API key not configured');
        }

        $maxRetries = 3;
        $baseDelay = 1; // seconds

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $url = $this->base . '/' . $endpoint;
                if (!empty($params)) {
                    $url .= '?' . http_build_query($params);
                }

                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'header' => "X-Api-Key: {$apiKey}\r\n",
                        'timeout' => 10,
                    ],
                ]);

                $response = @file_get_contents($url, false, $context);

                if ($response === false) {
                    $error = error_get_last();
                    throw new \RuntimeException($error['message'] ?? 'Unknown error');
                }

                $decoded = json_decode($response, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \RuntimeException('Invalid JSON response: ' . json_last_error_msg());
                }

                return $decoded;
            } catch (\Exception $e) {
                // Check if we should retry
                if ($attempt < $maxRetries - 1) {
                    // Exponential backoff
                    $delay = $baseDelay * (2 ** $attempt);
                    sleep($delay);
                    continue;
                }

                throw $e;
            }
        }

        throw new \RuntimeException('Max retries exceeded');
    }

    /** @inheritDoc */
    public function fetchRandomQuotes(int $count = 10): array
    {
        $ttl = (int) config('quotes.ttl', 30);

        // Check if batch exists in cache
        $batch = cache()->get(self::KEY_BATCH);
        $wasCached = $batch !== null;

        // If not cached, fetch from API
        if ($batch === null) {
            try {
                $response = $this->fetchWithRetry($this->randomQuoteEndpoint);

                if (empty($response) || !is_array($response)) {
                    throw new \RuntimeException('Invalid response from API Ninjas');
                }

                // Normalize all quotes to expected format
                $batch = array_map(function ($quoteData) {
                    return [
                        'text' => $quoteData['quote'] ?? '',
                        'author' => $quoteData['author'] ?? null,
                    ];
                }, $response);

                // Cache the normalized batch
                cache()->put(self::KEY_BATCH, $batch, $ttl);
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch random quotes from API Ninjas', [
                    'error' => $e->getMessage(),
                ]);

                // Try to return cached data if available
                $fallback = cache()->get(self::KEY_BATCH);
                if ($fallback !== null) {
                    $batch = $fallback;
                    $wasCached = true;
                } else {
                    throw new \RuntimeException('Unable to fetch random quotes: ' . $e->getMessage(), 0, $e);
                }
            }
        }

        // Shuffle and take requested count
        $shuffled = $batch;
        shuffle($shuffled);
        $subset = array_slice($shuffled, 0, $count);

        // Map to QuoteDTO with appropriate cached flag
        return array_map(
            fn($row) => QuoteDTO::fromZenQuotes($row, $wasCached),
            $subset
        );
    }
}
