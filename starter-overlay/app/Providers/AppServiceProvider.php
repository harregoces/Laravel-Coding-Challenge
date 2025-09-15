<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\QuoteApiClient;
use App\Services\ZenQuotesClient;
use App\Services\Stubs\ZenQuotesClientStub;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(QuoteApiClient::class, function () {
            return match (config('quotes.client', 'real')) {
                'real' => new ZenQuotesClient(),
                default => new ZenQuotesClientStub(),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
