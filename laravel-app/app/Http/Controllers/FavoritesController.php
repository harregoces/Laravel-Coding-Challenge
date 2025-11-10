<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Services\FavoritesService;
use App\Http\Requests\FavoriteStoreRequest;

/**
 * FavoritesController (TEMPLATE - Web)
 * Call FavoritesService; keep pages simple and behavior as per spec.
 * Routes are behind auth middleware.
 */
final class FavoritesController extends Controller
{
    public function __construct(private FavoritesService $service) {}

    public function index()
    {
        $favorites = $this->service->list(auth()->user());

        return view('favorites', compact('favorites'));
    }

    public function store(FavoriteStoreRequest $request)
    {
        $this->service->add(
            auth()->user(),
            $request->validated('text'),
            $request->validated('author')
        );

        return redirect()->back()->with('status', 'Quote added to favorites!');
    }

    public function destroy(Quote $quote)
    {
        $this->service->removeByQuoteId(auth()->user(), $quote->id);

        return redirect()->back()->with('status', 'Quote removed from favorites!');
    }
}
