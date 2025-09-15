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
        // TODO: minimal canon (trim, collapse whitespace), sha256(canonText|canonAuthor)
        throw new \LogicException('Not implemented: QuoteIdentity::hashFrom');
    }
}
