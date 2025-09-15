<?php

namespace App\Contracts;

use App\DTOs\QuoteDTO;

interface QuoteApiClient
{
    public function fetchQuoteOfTheDay(): QuoteDTO;

    /** @return QuoteDTO[] */
    public function fetchRandomQuotes(int $count = 10): array;
}
