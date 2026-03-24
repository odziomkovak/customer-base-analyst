<?php

namespace Tests\Feature;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('app');
    }

    public function test_dashboard_shows_no_stores_when_empty(): void
    {
        $response = $this->get('/');

        $response->assertViewHas('hasStores', false);
    }

    public function test_dashboard_shows_has_stores_when_stores_exist(): void
    {
        Store::factory()->create();

        $response = $this->get('/');

        $response->assertViewHas('hasStores', true);
    }
}
