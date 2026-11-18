<?php

namespace Tests\Unit;

use App\Services\GudangServices;
use App\Services\Odoo\OdooClient;
use PHPUnit\Framework\TestCase;

class GudangServicesTest extends TestCase
{
    private function serviceWithCapturedDomain(?array &$captured): GudangServices
    {
        $odoo = $this->createMock(OdooClient::class);
        $odoo->method('searchRead')->willReturnCallback(
            function (string $model, array $domain = []) use (&$captured): array {
                $captured = ['model' => $model, 'domain' => $domain];

                return ['length' => 0, 'records' => []];
            }
        );

        return new GudangServices($odoo);
    }

    public function test_browse_without_query_sends_empty_domain(): void
    {
        $svc = $this->serviceWithCapturedDomain($captured);
        $svc->getAll('', 24, 0);

        $this->assertSame('stock.warehouse', $captured['model']);
        $this->assertSame([], $captured['domain']);
    }

    public function test_search_matches_name_or_code(): void
    {
        $svc = $this->serviceWithCapturedDomain($captured);
        $svc->getAll('CENTER', 24, 0);

        $this->assertSame(
            ['|', ['name', 'ilike', 'CENTER'], ['code', 'ilike', 'CENTER']],
            $captured['domain'],
        );
    }
}
