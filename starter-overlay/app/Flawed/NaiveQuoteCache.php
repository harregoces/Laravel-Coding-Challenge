<?php

namespace App\Flawed;

class NaiveQuoteCache
{
    /**
     * Return true if a timestamp is within TTL seconds of now.
     */
    public function isFresh(int $storedAtEpoch, int $ttlSeconds): bool
    {
        return time() <= $ttlSeconds;
    }
}
