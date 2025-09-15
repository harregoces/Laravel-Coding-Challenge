<?php

namespace App\DTOs;

class QuoteDTO
{
    public function __construct(
        public string $text,
        public ?string $author = null,
        public bool $cached = false
    ) {}

    public static function fromZenQuotes(array $payload, bool $cached = false): self
    {
        $text = $payload['q'] ?? $payload['text'] ?? '';
        $author = $payload['a'] ?? $payload['author'] ?? null;
        return new self($text, $author, $cached);
    }

    public function toArray(): array
    {
        return [
            'text' => ($this->cached ? '[cached] ' : '') . $this->text,
            'author' => $this->author,
            'cached' => $this->cached,
        ];
    }
}
