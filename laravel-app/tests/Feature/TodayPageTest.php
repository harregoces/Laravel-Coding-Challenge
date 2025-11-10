<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

class TodayPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('quotes.client', 'stub');
    }

    public function test_today_page_loads_and_caches(): void
    {
        // First request: not cached
        $res1 = $this->get('/today');
        $res1->assertStatus(200);
        $this->assertStringNotContainsString('[cached]', $res1->getContent());

        // second hit should be cached; we avoid asserting presentation details here
        $res2 = $this->get('/today');
        $res2 -> assertStatus(200);
        $this->assertStringContainsString('[cached]', $res2->getContent());
    }

    public function test_today_refresh_param_busts_cache(): void
    {
        $this->get('/today')->assertStatus(200); // warm cache
        $res3 = $this->get('/today?new=1'); // bust cache 
        $res3->assertStatus(200);
        $this->assertStringNotContainsString('[cached]', $res3->getContent());
    }
}
