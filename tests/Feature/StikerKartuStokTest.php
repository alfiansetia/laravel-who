<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StikerKartuStokTest extends TestCase
{
    use RefreshDatabase;

    public function test_stiker_kartu_stok_page_returns_inertia_response(): void
    {
        $response = $this->get(route('tools.stiker_kartu_stok'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('StikerKartuStok/Index')
            ->where('title', 'Stiker Kartu Stok')
            ->has('products')
        );
    }
}
