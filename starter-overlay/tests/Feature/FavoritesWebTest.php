<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Quote;

class FavoritesWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_favorites(): void
    {
        $this->get('/favorites')->assertRedirect('/login');
    }

    public function test_add_and_delete_favorite_via_web(): void
    {
        $user = User::factory()->create();
        $payload = ['text' => 'Test quote', 'author' => 'Tester'];

        $this->actingAs($user)->post('/favorites', $payload)->assertRedirect();
        $this->actingAs($user)->get('/favorites')->assertSee('Test quote');

        $quote = Quote::where('text', 'Test quote')->first();
        $this->actingAs($user)->delete('/favorites/'.$quote->id)->assertRedirect();
        $this->actingAs($user)->get('/favorites')->assertDontSee('Test quote');
    }
}
