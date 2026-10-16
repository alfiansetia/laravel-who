<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TempelanAklUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_tempelan_akl_unit_page_returns_inertia_response(): void
    {
        $response = $this->get(route('tools.tempelan_akl_unit'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('TempelanAklUnit/Index')
            ->where('title', 'Tempelan AKL Unit')
            ->has('products')
        );
    }
}
