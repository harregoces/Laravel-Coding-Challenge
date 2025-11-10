<?php

declare(strict_types=1);

namespace App\Support;

/**
 * QuoteIdentity (TEMPLATE)
 * Centralize canonicalization + unique_hash creation.
 * Document your rules in ADR 0004.
 */
final class QuoteIdentity
{
    /** @return array{0:string,1:?string,2:string} [canonText, canonAuthor, uniqueHash] */
    public static function hashFrom(string $text, ?string $author = null): array
    {
        // Canonicalize text: trim and collapse whitespace
        $canonText = trim($text);
        $canonText = preg_replace('/\s+/', ' ', $canonText);

        // Canonicalize author: trim, collapse whitespace, null if empty
        $canonAuthor = $author !== null ? trim($author) : null;
        if ($canonAuthor !== null) {
            $canonAuthor = preg_replace('/\s+/', ' ', $canonAuthor);
            $canonAuthor = $canonAuthor === '' ? null : $canonAuthor;
        }

        // Create unique hash from canonicalized values
        $hashInput = $canonText . '|' . ($canonAuthor ?? '');
        $uniqueHash = hash('sha256', $hashInput);

        return [$canonText, $canonAuthor, $uniqueHash];
    }
}
