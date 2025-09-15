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
        $count = auth()->check() ? 10 : 5;
        $quotes = $this->client->fetchRandomQuotes($count);

        return view('quotes', [
            'quotes' => $quotes,
        ]);
    }
}
