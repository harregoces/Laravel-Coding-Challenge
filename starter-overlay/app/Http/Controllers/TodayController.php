<?php

namespace App\Http\Controllers;

use App\Contracts\QuoteApiClient;
use Illuminate\Http\Request;

class TodayController extends Controller
{
    public function __construct(private QuoteApiClient $client) {}

    public function __invoke(Request $request)
    {
        if ($request->boolean('new')) {
            cache()->forget('qod.current');
        }
        $dto = $this->client->fetchQuoteOfTheDay();

        // pick a random local image
        $files = glob(public_path('images/inspiration/*')) ?: [];
        $image = count($files) ? asset('images/inspiration/' . basename($files[array_rand($files)])) : null;

        return view('today', [
            'quote' => $dto,
            'image' => $image,
        ]);
    }
}
