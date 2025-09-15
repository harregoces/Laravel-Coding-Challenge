<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class QuotesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_gets_five_quotes_by_default(): void
    {
        $this->getJson('/api/quotes')
            ->assertStatus(200)
            ->assertJsonPath('meta.count', 5)
            ->assertJsonCount(5, 'data');
    }

    public function test_guest_requesting_more_than_five_gets_401(): void
    {
        $this->getJson('/api/quotes?count=10')
            ->assertStatus(401)
            ->assertJson(['error' => 'Unauthenticated']);
    }

    public function test_authenticated_can_get_ten(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('x')->plainTextToken;
        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/quotes?count=10')
            ->assertStatus(200)
            ->assertJsonPath('meta.count', 10)
            ->assertJsonCount(10, 'data');
    }

    public function test_refresh_param_works(): void
    {
        $this->getJson('/api/quotes')->assertStatus(200);
        $this->getJson('/api/quotes?new=1')->assertStatus(200);
    }
}
