<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Quote;
use App\Support\QuoteIdentity;
use Illuminate\Support\Collection;

/**
 * FavoritesService (TEMPLATE)
 * Implement list/add/remove logic once here and call from web+API controllers.
 * Requirements (see CHALLENGE.md):
 *  - Idempotent add.
 *  - Identity via unique_hash from App\Support\QuoteIdentity::hashFrom().
 *  - Remove by hash, by text+author (derive hash), or by quote id.
 */
final class FavoritesService
{
    /** Return the current user's favorites (most recent first). */
    public function list(User $user): Collection
    {
        return $user->favoriteQuotes()
            ->orderBy('favorite_quotes.created_at', 'desc')
            ->get();
    }

    /** Add a favorite idempotently and return the Quote. */
    public function add(User $user, string $text, ?string $author = null): Quote
    {
        // Compute canonical identity and unique hash
        [$canonText, $canonAuthor, $uniqueHash] = QuoteIdentity::hashFrom($text, $author);

        // Find or create the quote
        $quote = Quote::firstOrCreate(
            ['unique_hash' => $uniqueHash],
            [
                'text' => $canonText,
                'author' => $canonAuthor,
            ]
        );

        // Attach to user's favorites (idempotent - won't duplicate if already exists)
        $user->favoriteQuotes()->syncWithoutDetaching([$quote->id]);

        return $quote;
    }

    /** Remove favorite by unique hash (if exists). */
    public function removeByHash(User $user, string $uniqueHash): void
    {
        // Find the quote by its unique hash
        $quote = Quote::where('unique_hash', $uniqueHash)->first();

        if ($quote !== null) {
            // Detach from user's favorites
            $user->favoriteQuotes()->detach($quote->id);
        }
    }

    /** Remove favorite by text/author (derive hash). */
    public function removeByTextAuthor(User $user, string $text, ?string $author = null): void
    {
        // Compute hash from text and author
        [, , $uniqueHash] = QuoteIdentity::hashFrom($text, $author);

        // Delegate to removeByHash
        $this->removeByHash($user, $uniqueHash);
    }

    /** Remove favorite by quote id. */
    public function removeByQuoteId(User $user, int $quoteId): void
    {
        // Detach directly by quote id
        $user->favoriteQuotes()->detach($quoteId);
    }
}
