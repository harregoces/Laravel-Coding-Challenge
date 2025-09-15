<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

class TodayPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_today_page_caches_quote(): void
    {
        Config::set('quotes.client', 'stub');

    {
        // First request: not cached
        $res1 = $this->get('/today');
        $res1->assertStatus(200);
        $this->assertStringNotContainsString('[cached]', $res1->getContent());

        // Second request: cached
        $res2 = $this->get('/today');
        $res2->assertStatus(200);
        $this->assertStringContainsString('[cached]', $res2->getContent());
    }

    public function test_today_refresh_param_busts_cache(): void
    {
        Config::set('quotes.client', 'stub');

    {
        $this->get('/today'); // warm cache
        $res = $this->get('/today?new=1'); // bust cache
        $res->assertStatus(200);
        $this->assertStringNotContainsString('[cached]', $res->getContent());
    }
}
