<?php

declare(strict_types=1);

namespace App\Support;

final class QuoteIdentity
{
    public static function hashFrom(string $text, ?string $author = null): array
    {
        $canonText = self::canon($text);
        $canonAuthor = $author !== null ? self::canon($author) : null;

        // NOTE: Canon rules are intentionally simple here; ADR 0004 can expand them (unicode/whitespace rules).
        $hash = hash('sha256', $canonText . '|' . ($canonAuthor ?? ''));
        return [$canonText, $canonAuthor, $hash];
    }

    private static function canon(string $s): string
    {
        // minimal canonicalization: trim + collapse whitespace
        // TODO: tighten canonicalization rules
        $s = trim($s);
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;
        return $s;
    }
}
