<?php

namespace App\Http\Controllers;

use App\Contracts\QuoteApiClient;
use Illuminate\Http\Request;

class QuotesController extends Controller
{
    public function __construct(private QuoteApiClient $client) {}

    public function index(Request $request)
    {
        if ($request->boolean('new')) {
            cache()->forget('quotes.batch');
        }

        // Get count parameter or use defaults
        $requestedCount = $request->query('count');
        $defaultCount = auth()->check() ? 10 : 5;
        $count = $requestedCount !== null ? (int) $requestedCount : $defaultCount;

        // Validate count bounds
        $count = max(1, min(10, $count));

        // Check authentication requirement for count > 5
        if ($count > 5 && !auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to view more than 5 quotes. Please login to continue.');
        }

        $quotes = $this->client->fetchRandomQuotes($count);

        return view('quotes', [
            'quotes' => $quotes,
            'client' => config('quotes.client'),
            'count' => $count,
        ]);
    }
}
