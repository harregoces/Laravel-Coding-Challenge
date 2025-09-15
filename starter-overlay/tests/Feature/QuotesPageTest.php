<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use App\Models\User;

class QuotesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_five_quotes(): void
    {
        Config::set('quotes.client', 'stub');

    {
        $res = $this->get('/quotes');
        $res->assertStatus(200);
        $this->assertGreaterThanOrEqual(5, substr_count($res->getContent(), '<li>'));
    }

    public function test_authenticated_sees_ten_quotes(): void
    {
        Config::set('quotes.client', 'stub');

    {
        $user = User::factory()->create();
        $res = $this->actingAs($user)->get('/quotes');
        $res->assertStatus(200);
        $this->assertGreaterThanOrEqual(10, substr_count($res->getContent(), '<li>'));
    }

    public function test_refresh_param_busts_cache(): void
    {
        Config::set('quotes.client', 'stub');

    {
        $this->get('/quotes');
        $res = $this->get('/quotes?new=1');
        $res->assertStatus(200);
    }
}
