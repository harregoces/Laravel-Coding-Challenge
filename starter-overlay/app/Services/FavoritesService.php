<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Support\Collection;

final class FavoritesService
{
    public function list(User $user): Collection
    {
        return $user->favoriteQuotes()->latest()->get();
    }

    public function add(User $user, string $text, ?string $author = null): Quote
    {
        [$canonText, $canonAuthor, $hash] = \App\Support\QuoteIdentity::hashFrom($text, $author);

        $quote = Quote::firstOrCreate(
            ['unique_hash' => $hash],
            ['text' => $canonText, 'author' => $canonAuthor, 'source_key' => 'zenquotes']
        );

        $user->favoriteQuotes()->syncWithoutDetaching([$quote->id]);

        return $quote;
    }

    public function removeByHash(User $user, string $uniqueHash): void
    {
        $quote = Quote::where('unique_hash', $uniqueHash)->first();
        if ($quote) {
            $user->favoriteQuotes()->detach($quote->id);
        }
    }

    public function removeByTextAuthor(User $user, string $text, ?string $author = null): void
    {
        [, , $hash] = \App\Support\QuoteIdentity::hashFrom($text, $author);
        $this->removeByHash($user, $hash);
    }

    public function removeByQuoteId(User $user, int $quoteId): void
    {
        $user->favoriteQuotes()->detach($quoteId);
    }
}
