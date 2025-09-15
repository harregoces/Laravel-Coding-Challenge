<?php

namespace Tests\Unit;

use App\Flawed\NaiveQuoteCache;
use PHPUnit\Framework\TestCase;

class FlawedNaiveCacheTest extends TestCase
{
    public function test_is_fresh_returns_false_when_older_than_ttl(): void
    {
        $cache = new NaiveQuoteCache();
        $storedAt = time() - 60;
        $this->assertFalse($cache->isFresh($storedAt, 30), 'Cache older than TTL should be stale');
    }

    public function test_is_fresh_returns_true_when_within_ttl(): void
    {
        $cache = new NaiveQuoteCache();
        $storedAt = time() - 10;
        $this->assertTrue($cache->isFresh($storedAt, 30), 'Cache within TTL should be fresh');
    }
}
