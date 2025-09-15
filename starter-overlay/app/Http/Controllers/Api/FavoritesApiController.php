<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FavoriteStoreRequest;
use App\Http\Requests\FavoriteDeleteRequest;
use App\Http\Resources\QuoteResource;
use App\Services\FavoritesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class FavoritesApiController extends Controller
{
    public function __construct(private FavoritesService $service) {}

    public function index(): AnonymousResourceCollection
    {
        return QuoteResource::collection($this->service->list(request()->user()));
    }

    public function store(FavoriteStoreRequest $request): JsonResponse
    {
        $quote = $this->service->add($request->user(), $request->string('text'), $request->input('author'));
        return (new QuoteResource($quote))->response()->setStatusCode(201);
    }

    public function destroy(FavoriteDeleteRequest $request): JsonResponse
    {
        if ($request->wantsHash()) {
            $this->service->removeByHash($request->user(), $request->string('unique_hash'));
        } else {
            $text = $request->input('text');
            if (!filled($text)) {
                return response()->json(['error' => 'Provide unique_hash or text (+optional author)'], 422);
            }
            $this->service->removeByTextAuthor($request->user(), $text, $request->input('author'));
        }

        return response()->json([], 204);
    }
}
