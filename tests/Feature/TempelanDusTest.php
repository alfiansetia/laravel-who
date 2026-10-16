<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TempelanDusTest extends TestCase
{
    use RefreshDatabase;

    public function test_tempelan_dus_page_returns_inertia_response(): void
    {
        $response = $this->get(route('tools.tempelan_dus'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('TempelanDus/Index')
            ->where('title', 'Tempelan Dus')
            ->has('products')
        );
    }

    public function test_product_search_returns_matches(): void
    {
        DB::table('products')->insert([
            'code' => 'PDL.NLC-G50',
            'name' => 'Nubion LifeCheck GLU 2x25',
            'akl' => 'AKD 20101324087',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson('/api/products/search?q=NLC');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'message']);
        $response->assertJsonFragment(['code' => 'PDL.NLC-G50']);
    }

    public function test_product_search_requires_keyword(): void
    {
        $response = $this->getJson('/api/products/search?q=');

        $response->assertStatus(200);
        $response->assertJsonPath('data', []);
    }
}
