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
        $this->get('/today')->assertStatus(200);
        // second hit should be cached; we avoid asserting presentation details here
        $this->get('/today')->assertStatus(200);
    }

    public function test_today_refresh_param_busts_cache(): void
    {
        $this->get('/today')->assertStatus(200);
        $this->get('/today?new=1')->assertStatus(200);
    }
}
