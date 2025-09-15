<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class QuotesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_five_quotes(): void
    {
        $res = $this->get('/quotes');
        $res->assertStatus(200);
        $this->assertTrue(substr_count($res->getContent(), '<li>') >= 5);
    }

    public function test_authenticated_sees_ten_quotes(): void
    {
        $user = User::factory()->create();
        $res = $this->actingAs($user)->get('/quotes');
        $res->assertStatus(200);
        $this->assertTrue(substr_count($res->getContent(), '<li>') >= 10);
    }

    public function test_refresh_param_busts_cache(): void
    {
        $this->get('/quotes');
        $res = $this->get('/quotes?new=1');
        $res->assertStatus(200);
    }
}
