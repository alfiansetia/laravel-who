<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TempelanAklKemasanTest extends TestCase
{
    use RefreshDatabase;

    public function test_tempelan_akl_kemasan_page_returns_inertia_response(): void
    {
        $response = $this->get(route('tools.tempelan_akl_kemasan'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('TempelanAklKemasan/Index')
            ->where('title', 'Tempelan AKL Kemasan')
            ->has('products')
        );
    }
}
