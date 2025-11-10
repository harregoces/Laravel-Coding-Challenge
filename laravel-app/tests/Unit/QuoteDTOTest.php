<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTOs\QuoteDTO;
use PHPUnit\Framework\TestCase;

class QuoteDTOTest extends TestCase
{
    /**
     * Test normalization from ZenQuotes format with 'q' and 'a' keys.
     */
    public function test_creates_from_zenquotes_format_with_q_and_a_keys(): void
    {
        $payload = [
            'q' => 'Life is what happens when you are busy making other plans.',
            'a' => 'John Lennon',
        ];

        $dto = QuoteDTO::fromZenQuotes($payload, false);

        $this->assertSame('Life is what happens when you are busy making other plans.', $dto->text);
        $this->assertSame('John Lennon', $dto->author);
        $this->assertFalse($dto->cached);
    }

    /**
     * Test normalization from API Ninjas format with 'text' and 'author' keys.
     */
    public function test_creates_from_api_ninjas_format_with_text_and_author_keys(): void
    {
        $payload = [
            'text' => 'The only way to do great work is to love what you do.',
            'author' => 'Steve Jobs',
        ];

        $dto = QuoteDTO::fromZenQuotes($payload, false);

        $this->assertSame('The only way to do great work is to love what you do.', $dto->text);
        $this->assertSame('Steve Jobs', $dto->author);
        $this->assertFalse($dto->cached);
    }

    /**
     * Test that cached flag is correctly set.
     */
    public function test_cached_flag_is_correctly_assigned(): void
    {
        $payload = ['text' => 'Test quote', 'author' => 'Test Author'];

        $freshDto = QuoteDTO::fromZenQuotes($payload, false);
        $cachedDto = QuoteDTO::fromZenQuotes($payload, true);

        $this->assertFalse($freshDto->cached);
        $this->assertTrue($cachedDto->cached);
    }

    /**
     * Test that toArray() prefixes text with [cached] when cached is true.
     */
    public function test_to_array_prefixes_text_with_cached_when_cached(): void
    {
        $dto = new QuoteDTO('Test quote', 'Test Author', true);
        $array = $dto->toArray();

        $this->assertSame('[cached] Test quote', $array['text']);
        $this->assertSame('Test Author', $array['author']);
        $this->assertTrue($array['cached']);
    }

    /**
     * Test that toArray() does not prefix text when cached is false.
     */
    public function test_to_array_does_not_prefix_text_when_not_cached(): void
    {
        $dto = new QuoteDTO('Test quote', 'Test Author', false);
        $array = $dto->toArray();

        $this->assertSame('Test quote', $array['text']);
        $this->assertSame('Test Author', $array['author']);
        $this->assertFalse($array['cached']);
    }

    /**
     * Test handling of null author.
     */
    public function test_handles_null_author(): void
    {
        $payload = ['text' => 'Anonymous quote'];

        $dto = QuoteDTO::fromZenQuotes($payload, false);

        $this->assertSame('Anonymous quote', $dto->text);
        $this->assertNull($dto->author);
    }

    /**
     * Test handling of empty payload with defaults.
     */
    public function test_handles_empty_payload_with_defaults(): void
    {
        $payload = [];

        $dto = QuoteDTO::fromZenQuotes($payload, false);

        $this->assertSame('', $dto->text);
        $this->assertNull($dto->author);
    }

    /**
     * Test direct constructor instantiation.
     */
    public function test_direct_constructor_instantiation(): void
    {
        $dto = new QuoteDTO('Direct quote', 'Direct Author', true);

        $this->assertSame('Direct quote', $dto->text);
        $this->assertSame('Direct Author', $dto->author);
        $this->assertTrue($dto->cached);
    }
}
