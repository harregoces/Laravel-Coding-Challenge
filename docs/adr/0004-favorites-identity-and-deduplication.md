# ADR 0004: Favorites Identity & De-duplication (Canonicalization & Constraints)

- **Status:** Proposed
- **Date:** <YYYY-MM-DD>

## Context
Quotes come from an external API. We must ensure a user cannot favorite the “same” quote multiple times. The schema includes `quotes(text, author, unique_hash)` and a join table `favorite_quotes(user_id, quote_id)` with a unique pair (user, quote). We currently derive `unique_hash = sha256(text|author)`.

## Decision
Use a **canonicalization step** before hashing:
- `text`: trim, collapse internal whitespace, normalize Unicode quotes/dashes, remove trailing punctuation-only differences.
- `author`: trim; treat empty/`null` consistently.
- Compute `unique_hash = sha256(canonical_text | canonical_author)` and enforce **DB uniqueness** on `unique_hash`.
- Web/API “add to favorites” uses `firstOrCreate` on `unique_hash` and `syncWithoutDetaching` on the user relation to guarantee **idempotency**.

## Consequences
**Pros:** Prevents duplicate rows from superficial variations; keeps favorites stable across API payload changes; simple to test.  
**Cons:** Canon rules must be documented and consistent; rare edge cases may still collide.

## Alternatives Considered
1) **Use raw text as key** — simplest, but duplicates likely with spacing/Unicode differences.  
2) **Use vendor IDs** — ZenQuotes free API may not provide stable IDs; ties us to vendor.  
3) **Full-text similarity** — overkill and non-deterministic for this scope.

## How to Verify
- Unit: canonicalization tests (tabs vs spaces, smart quotes, trailing whitespace).  
- Feature: favoriting the same visible quote twice remains a single entry; DB shows unique `favorite_quotes(user, quote)`.  

## Code anchors 
- `App/Models/Quote`
- creation path in favorites controller(s)
- unique index in migration(s).
