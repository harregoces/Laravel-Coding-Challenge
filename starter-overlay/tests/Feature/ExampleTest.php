<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Config;

class ExampleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Ensure the quotes pages work offline in CI:
        Config::set('quotes.client', 'stub');
    }

    public function test_home_redirects_and_landing_is_ok(): void
    {
        // Our app intentionally redirects "/" to the landing page (e.g., /quotes or /today).
        $this->get('/')->assertRedirect();  // no path asserted, just that it's a redirect

        // Follow the redirect and assert the final page loads.
        $this->followingRedirects()
             ->get('/')
             ->assertOk();
    }
}
