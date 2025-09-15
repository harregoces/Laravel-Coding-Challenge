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

        // pick a random local image (3 shipped images)
        $images = [];
        $dir = public_path('images/inspiration');
        if (is_dir($dir)) {
            foreach (scandir($dir) as $f) {
                if (preg_match('/\.png$/i', $f)) $images[] = $f;
            }
        }
        $image = $images ? $images[array_rand($images)] : null;

        return view('today', [
            'quote' => $dto,
            'image' => $image,
        ]);
    }
}
