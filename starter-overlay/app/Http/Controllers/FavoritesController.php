<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $favorites = $user->favoriteQuotes()->latest()->get();
        return view('favorites', compact('favorites'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'text' => ['required', 'string'],
            'author' => ['nullable', 'string'],
        ]);

        $user = auth()->user();
        $text = $request->string('text');
        $author = $request->input('author');
        $hash = hash('sha256', $text . '|' . $author);

        $quote = Quote::firstOrCreate(
            ['unique_hash' => $hash],
            ['text' => $text, 'author' => $author, 'source_key' => 'zenquotes']
        );

        $user->favoriteQuotes()->syncWithoutDetaching([$quote->id]);

        return redirect()->back()->with('status', 'Added to favorites');
    }

    public function destroy(Quote $quote)
    {
        $user = auth()->user();
        $user->favoriteQuotes()->detach($quote->id);

        return redirect()->back()->with('status', 'Removed from favorites');
    }
}
