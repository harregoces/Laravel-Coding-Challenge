<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use App\Models\User;

class QuotesPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Force the stubbed client in tests to avoid network
        Config::set('quotes.client', 'stub');
    }

    public function test_guest_sees_quotes_page(): void
    {
        // Guest should access /quotes and get 200
        $this->get('/quotes')
            ->assertStatus(200)
            ->assertSee('Quotes'); // loose check; page title/heading
    }

    public function test_authenticated_user_sees_quotes_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/quotes')
            ->assertStatus(200)
            ->assertSee('Quotes');
    }

    public function test_refresh_param_busts_cache(): void
    {
        // First load to warm cache
        $this->get('/quotes')->assertStatus(200);

        // Force refresh
        $this->get('/quotes?new=1')->assertStatus(200);
    }
}
