<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\QuoteIdentity;
use PHPUnit\Framework\TestCase;

class QuoteIdentityTest extends TestCase
{
    /**
     * Test basic canonicalization with trim.
     */
    public function test_canonicalizes_text_with_trim(): void
    {
        [$canonText, $canonAuthor, $hash] = QuoteIdentity::hashFrom(
            '  Life is what happens  ',
            '  John Lennon  '
        );

        $this->assertSame('Life is what happens', $canonText);
        $this->assertSame('John Lennon', $canonAuthor);
    }

    /**
     * Test whitespace collapsing in text.
     */
    public function test_collapses_multiple_whitespace_in_text(): void
    {
        [$canonText, , ] = QuoteIdentity::hashFrom(
            'Life    is     what   happens',
            'Author'
        );

        $this->assertSame('Life is what happens', $canonText);
    }

    /**
     * Test whitespace collapsing in author.
     */
    public function test_collapses_multiple_whitespace_in_author(): void
    {
        [, $canonAuthor, ] = QuoteIdentity::hashFrom(
            'Quote text',
            'John    Lennon'
        );

        $this->assertSame('John Lennon', $canonAuthor);
    }

    /**
     * Test null author remains null.
     */
    public function test_null_author_remains_null(): void
    {
        [, $canonAuthor, ] = QuoteIdentity::hashFrom('Quote text', null);

        $this->assertNull($canonAuthor);
    }

    /**
     * Test empty author string becomes null.
     */
    public function test_empty_author_string_becomes_null(): void
    {
        [, $canonAuthor, ] = QuoteIdentity::hashFrom('Quote text', '   ');

        $this->assertNull($canonAuthor);
    }

    /**
     * Test hash is SHA-256 and consistent.
     */
    public function test_hash_is_sha256_and_consistent(): void
    {
        [, , $hash1] = QuoteIdentity::hashFrom('Test quote', 'Test Author');
        [, , $hash2] = QuoteIdentity::hashFrom('Test quote', 'Test Author');

        $this->assertSame(64, strlen($hash1)); // SHA-256 produces 64 hex characters
        $this->assertSame($hash1, $hash2); // Same input produces same hash
    }

    /**
     * Test hash differs for different text.
     */
    public function test_hash_differs_for_different_text(): void
    {
        [, , $hash1] = QuoteIdentity::hashFrom('Quote A', 'Author');
        [, , $hash2] = QuoteIdentity::hashFrom('Quote B', 'Author');

        $this->assertNotSame($hash1, $hash2);
    }

    /**
     * Test hash differs for different author.
     */
    public function test_hash_differs_for_different_author(): void
    {
        [, , $hash1] = QuoteIdentity::hashFrom('Quote', 'Author A');
        [, , $hash2] = QuoteIdentity::hashFrom('Quote', 'Author B');

        $this->assertNotSame($hash1, $hash2);
    }

    /**
     * Test hash is same after canonicalization.
     */
    public function test_hash_same_after_canonicalization(): void
    {
        [, , $hash1] = QuoteIdentity::hashFrom('  Life   is  what happens  ', '  John   Lennon  ');
        [, , $hash2] = QuoteIdentity::hashFrom('Life is what happens', 'John Lennon');

        $this->assertSame($hash1, $hash2);
    }

    /**
     * Test hash includes null author as empty string.
     */
    public function test_hash_with_null_author(): void
    {
        [, , $hash1] = QuoteIdentity::hashFrom('Quote', null);
        [, , $hash2] = QuoteIdentity::hashFrom('Quote', '');

        // Both null and empty string should produce same hash
        $this->assertSame($hash1, $hash2);
    }

    /**
     * Test full array structure returned.
     */
    public function test_returns_full_array_structure(): void
    {
        $result = QuoteIdentity::hashFrom('Test quote', 'Test Author');

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertSame('Test quote', $result[0]); // canonText
        $this->assertSame('Test Author', $result[1]); // canonAuthor
        $this->assertIsString($result[2]); // uniqueHash
    }

    /**
     * Test known hash value for specific input (regression test).
     */
    public function test_known_hash_value(): void
    {
        [, , $hash] = QuoteIdentity::hashFrom('Test', 'Author');

        // This is the expected SHA-256 of "Test|Author"
        $expected = hash('sha256', 'Test|Author');

        $this->assertSame($expected, $hash);
    }
}
