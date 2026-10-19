<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintLabelTest extends TestCase
{
    use RefreshDatabase;

    public function test_print_label_page_returns_inertia_response(): void
    {
        $response = $this->get(route('tools.print_label'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('PrintLabel/Index')
            ->where('title', 'Print Label')
            ->has('products')
        );
    }
}
