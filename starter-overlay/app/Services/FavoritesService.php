<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Quote;
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
        // TODO: query relation and order (latest first)
        throw new \LogicException('Not implemented: FavoritesService::list');
    }

    /** Add a favorite idempotently and return the Quote. */
    public function add(User $user, string $text, ?string $author = null): Quote
    {
        // TODO: compute canonical identity, firstOrCreate Quote, syncWithoutDetaching
        throw new \LogicException('Not implemented: FavoritesService::add');
    }

    /** Remove favorite by unique hash (if exists). */
    public function removeByHash(User $user, string $uniqueHash): void
    {
        // TODO: look up Quote by hash and detach
        throw new \LogicException('Not implemented: FavoritesService::removeByHash');
    }

    /** Remove favorite by text/author (derive hash). */
    public function removeByTextAuthor(User $user, string $text, ?string $author = null): void
    {
        // TODO: compute hash from text/author and delegate to removeByHash
        throw new \LogicException('Not implemented: FavoritesService::removeByTextAuthor');
    }

    /** Remove favorite by quote id. */
    public function removeByQuoteId(User $user, int $quoteId): void
    {
        // TODO: detach directly
        throw new \LogicException('Not implemented: FavoritesService::removeByQuoteId');
    }
}
