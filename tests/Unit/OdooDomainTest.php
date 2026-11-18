<?php

namespace Tests\Unit;

use App\Services\Odoo\OdooDomain;
use PHPUnit\Framework\TestCase;

class OdooDomainTest extends TestCase
{
    public function test_or_builds_prefix_notation(): void
    {
        $this->assertSame(
            ['|', ['name', 'ilike', 'X'], ['origin', 'ilike', 'X']],
            OdooDomain::or(['name', 'ilike', 'X'], ['origin', 'ilike', 'X']),
        );
    }

    public function test_and_flattens_or_output_to_single_level(): void
    {
        // Regresi: hasil or() sempat masuk sebagai satu elemen nested
        // sehingga Odoo melempar `TypeError: unhashable type: 'list'`.
        $domain = OdooDomain::and(
            ['picking_type_id', '=', 2],
            OdooDomain::or(
                OdooDomain::ilike('name', 'CENT/OUT'),
                OdooDomain::ilike('origin', 'CENT/OUT'),
            ),
            OdooDomain::ilike('note_to_wh', ''),
        );

        $this->assertSame(
            [
                ['picking_type_id', '=', 2],
                '|',
                ['name', 'ilike', 'CENT/OUT'],
                ['origin', 'ilike', 'CENT/OUT'],
                ['note_to_wh', 'ilike', ''],
            ],
            $domain,
        );

        foreach ($domain as $token) {
            $this->assertTrue(
                is_string($token) || (is_array($token) && isset($token[0]) && is_string($token[0])),
                'Domain harus flat: operator string atau kondisi [field, op, value].',
            );
        }
    }

    public function test_and_passes_single_condition_through(): void
    {
        $this->assertSame(
            [['name', 'ilike', 'PO']],
            OdooDomain::and(['name', 'ilike', 'PO']),
        );
    }

    public function test_and_skips_empty_parts(): void
    {
        $this->assertSame(
            [['name', 'ilike', 'X']],
            OdooDomain::and([], ['name', 'ilike', 'X'], ''),
        );
    }
}
