<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FavoriteStoreRequest;
use App\Models\Quote;
use App\Services\FavoritesService;

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
        $user = auth()->user();
        $this->service->add($user, $request->string('text'), $request->input('author'));

        return redirect()->back()->with('status', 'Added to favorites');
    }

    public function destroy(Quote $quote)
    {
        $this->service->removeByQuoteId(auth()->user(), $quote->id);
        return redirect()->back()->with('status', 'Removed from favorites');
    }
}
