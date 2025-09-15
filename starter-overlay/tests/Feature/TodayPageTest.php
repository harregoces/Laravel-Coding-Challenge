<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TodayPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_today_page_caches_quote(): void
    {
        $res1 = $this->get('/today');
        $res1->assertStatus(200);
        $this->assertStringNotContainsString('[cached]', $res1->getContent());

        $res2 = $this->get('/today');
        $res2->assertStatus(200);
        $this->assertStringContainsString('[cached]', $res2->getContent());
    }

    public function test_today_refresh_param_busts_cache(): void
    {
        $this->get('/today');
        $res = $this->get('/today?new=1');
        $res->assertStatus(200);
        $this->assertStringNotContainsString('[cached]', $res->getContent());
    }
}
