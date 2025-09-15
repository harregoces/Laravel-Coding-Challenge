<?php

namespace App\Flawed;

/**
 * Intentionally flawed time-to-live check for micro-exercise.
 * The tests should fail until this is fixed by the candidate.
 */
class NaiveQuoteCache
{
    /**
     * Return true if a timestamp is within TTL seconds of now.
     * BUG: compares TTL to now instead of (now - storedAt).
     */
    public function isFresh(int $storedAtEpoch, int $ttlSeconds): bool
    {
        // BUG: This is wrong on purpose.
        return time() <= $ttlSeconds;
    }
}
