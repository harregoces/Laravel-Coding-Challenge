<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FavoritesService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\FavoriteStoreRequest;
use App\Http\Requests\FavoriteDeleteRequest;

/**
 * FavoritesApiController (TEMPLATE - API, Sanctum required)
 * JSON responses only; status codes: 200 list, 201 create, 204 delete, 422 validation.
 */
final class FavoritesApiController extends Controller
{
    public function __construct(private FavoritesService $service) {}

    public function index(): JsonResponse
    {
        // TODO: return JSON list for request()->user()
        throw new \LogicException('Not implemented: FavoritesApiController::index');
    }

    public function store(FavoriteStoreRequest $request): JsonResponse
    {
        // TODO: validate, $this->service->add(...), return 201 with JSON {id,text,author}
        // TODO: validate via FormRequest, add favorite via service, return 201 JSON
        throw new \LogicException('Not implemented: FavoritesApiController::store');
    }

    public function destroy(FavoriteStoreRequest $request): JsonResponse
    {
        // TODO: if unique_hash provided -> removeByHash; else require text (+author) -> removeByTextAuthor; return 204
        // TODO: if $request->wantsHash() removeByHash else removeByTextAuthor; return 204
        throw new \LogicException('Not implemented: FavoritesApiController::destroy');
    }
}
