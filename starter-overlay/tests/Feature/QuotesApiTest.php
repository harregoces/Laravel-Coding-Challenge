<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use App\Models\User;

class QuotesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_gets_five_quotes_and_meta_present(): void
    {
        // Force stubbed client to avoid network in tests
        Config::set('quotes.client', 'stub');

        $this->getJson('/api/quotes')
            ->assertStatus(200)
            ->assertJsonPath('meta.count', 5)
            ->assertJsonStructure(['meta' => ['count','client','source']])
            ->assertJsonCount(5, 'data');
    }

    public function test_requesting_more_than_five_requires_auth(): void
    {
        Config::set('quotes.client', 'stub');

        $this->getJson('/api/quotes?count=10')
            ->assertStatus(401)
            ->assertJson(['error' => 'Unauthenticated']);
    }

    public function test_authenticated_can_get_ten_and_refresh(): void
    {
        Config::set('quotes.client', 'stub');

        $user = User::factory()->create();
        $token = $user->createToken('x')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/quotes?count=10')
            ->assertStatus(200)
            ->assertJsonPath('meta.count', 10)
            ->assertJsonCount(10, 'data');

        // refresh
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/quotes?new=1')
            ->assertStatus(200);
    }
}
