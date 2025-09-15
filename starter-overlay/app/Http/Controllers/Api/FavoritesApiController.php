<?php

namespace App\Http\Controllers\Api;

use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FavoritesApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $favorites = $user->favoriteQuotes()->get()->map(function (Quote $q) {
            return [
                'id' => $q->id,
                'text' => $q->text,
                'author' => $q->author,
            ];
        });

        return response()->json(['data' => $favorites]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'text' => ['required', 'string'],
            'author' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $text = $request->string('text');
        $author = $request->input('author');
        $hash = hash('sha256', $text . '|' . $author);

        $quote = Quote::firstOrCreate(
            ['unique_hash' => $hash],
            ['text' => $text, 'author' => $author, 'source_key' => 'zenquotes']
        );

        $user->favoriteQuotes()->syncWithoutDetaching([$quote->id]);

        return response()->json(['data' => ['id' => $quote->id, 'text' => $quote->text, 'author' => $quote->author]], 201);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'unique_hash' => ['nullable', 'string'],
            'text' => ['nullable', 'string'],
            'author' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $hash = $request->input('unique_hash');

        if (!$hash) {
            $text = $request->input('text');
            $author = $request->input('author');
            if (!$text) {
                return response()->json(['error' => 'Provide unique_hash or text (+optional author)'], 422);
            }
            $hash = hash('sha256', $text . '|' . $author);
        }

        $quote = Quote::where('unique_hash', $hash)->first();
        if ($quote) {
            $user->favoriteQuotes()->detach($quote->id);
        }

        return response()->json([], 204);
    }
}
