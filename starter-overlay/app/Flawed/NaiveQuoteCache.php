<?php

namespace App\Flawed;

class NaiveQuoteCache
{
    /**
     * Return true if a timestamp is within TTL seconds of now.
     * BUG: uses absolute TTL instead of relative to timestamp.
     */
    public function isFresh(int $storedAtEpoch, int $ttlSeconds): bool
    {
        // BUG: compares TTL to now rather than (now - storedAt)
        return time() <= $ttlSeconds;
    }
}
