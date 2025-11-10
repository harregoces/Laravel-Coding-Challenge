<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\QuoteApiClient;
use App\Services\NinjaQuotesClient;
use App\Services\Stubs\ZenQuotesClientStub;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(QuoteApiClient::class, function () {
            return match (config('quotes.client')) {
                'real' => new NinjaQuotesClient(),
                default => new ZenQuotesClientStub(),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
