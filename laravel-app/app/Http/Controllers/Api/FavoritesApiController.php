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
        $favorites = $this->service->list(request()->user());

        return response()->json([
            'data' => $favorites->map(fn($quote) => [
                'id' => $quote->id,
                'text' => $quote->text,
                'author' => $quote->author,
                'unique_hash' => $quote->unique_hash,
            ])
        ], 200);
    }

    public function store(FavoriteStoreRequest $request): JsonResponse
    {
        $quote = $this->service->add(
            $request->user(),
            $request->validated('text'),
            $request->validated('author')
        );

        return response()->json([
            'data' => [
                'id' => $quote->id,
                'text' => $quote->text,
                'author' => $quote->author,
                'unique_hash' => $quote->unique_hash,
            ]
        ], 201);
    }

    public function destroy(FavoriteDeleteRequest $request): JsonResponse
    {
        if ($request->wantsHash()) {
            $this->service->removeByHash(
                $request->user(),
                $request->validated('unique_hash')
            );
        } else {
            $this->service->removeByTextAuthor(
                $request->user(),
                $request->validated('text'),
                $request->validated('author')
            );
        }

        return response()->json(null, 204);
    }
}
