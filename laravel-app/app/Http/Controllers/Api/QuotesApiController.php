<?php

namespace App\Http\Controllers\Api;

use App\Contracts\QuoteApiClient;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class QuotesApiController extends Controller
{
    public function __construct(private QuoteApiClient $client) {}

    public function index(Request $request)
    {
        if ($request->boolean('new')) {
            cache()->forget('quotes.batch');
        }

        $count = (int) $request->query('count', 5); // default 5
        $isAuthed = $request->user() !== null;
        if ($count > 5 && !$isAuthed) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        $count = max(1, min(10, $count));

        $quotes = $this->client->fetchRandomQuotes($count);

        return response()->json([
            'data' => array_map(fn($dto) => $dto->toArray(), $quotes),
            'meta' => [
                'count' => $count,
                'client' => config('quotes.client', 'real'),
                'source' => 'api-ninjas',
            ],
        ]);
    }
}
