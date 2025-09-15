<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Services\FavoritesService;
use Illuminate\Http\Request;

/**
 * FavoritesController (TEMPLATE - Web)
 * Call FavoritesService; keep pages simple and behavior as per spec.
 */
final class FavoritesController extends Controller
{
    public function __construct(private FavoritesService $service) {}

    public function index()
    {
        // TODO: use $this->service->list(auth()->user())
        throw new \LogicException('Not implemented: FavoritesController::index');
    }

    public function store(Request $request)
    {
        // TODO: validate text/author; $this->service->add(...); redirect back with status
        throw new \LogicException('Not implemented: FavoritesController::store');
    }

    public function destroy(Quote $quote)
    {
        // TODO: $this->service->removeByQuoteId(...); redirect back with status
        throw new \LogicException('Not implemented: FavoritesController::destroy');
    }
}
