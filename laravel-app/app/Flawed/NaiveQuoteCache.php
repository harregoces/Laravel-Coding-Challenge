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
     * FIXED: Now correctly compares the age of the stored item against the TTL.
     * Diagnosis: The bug was comparing current timestamp (time()) directly with TTL seconds,
     * which would always return true when TTL < current epoch time. The correct logic
     * calculates the age of the cached item (time() - storedAtEpoch) and checks if
     * that age is within the allowed TTL period.
     */
    public function isFresh(int $storedAtEpoch, int $ttlSeconds): bool
    {
        // Fixed: Calculate age and compare with TTL
        return (time() - $storedAtEpoch) <= $ttlSeconds;
    }
}
