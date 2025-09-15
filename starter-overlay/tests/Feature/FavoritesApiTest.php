<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class FavoritesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_auth(): void
    {
        $this->getJson('/api/favorites')->assertStatus(401);
        $this->postJson('/api/favorites', [])->assertStatus(401);
    }

    public function test_add_and_delete_via_api(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('x')->plainTextToken;

        $payload = ['text' => 'API quote', 'author' => 'API'];

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/favorites', $payload)
            ->assertStatus(201);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/favorites')
            ->assertStatus(200)
            ->assertSee('API quote');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/favorites', ['text' => 'API quote', 'author' => 'API'])
            ->assertStatus(204);
    }
}
