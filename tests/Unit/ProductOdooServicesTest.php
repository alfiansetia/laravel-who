<?php

namespace Tests\Unit;

use App\Services\Odoo\OdooClient;
use App\Services\ProductOdooServices;
use PHPUnit\Framework\TestCase;

class ProductOdooServicesTest extends TestCase
{
    public function test_detail_reads_product_template_and_attaches_stock(): void
    {
        $odoo = $this->createMock(OdooClient::class);

        $readModels = [];
        $odoo->method('readOne')->willReturnCallback(
            function (string $model, int $id, array $fields = []) use (&$readModels): array {
                $readModels[] = ['model' => $model, 'id' => $id];

                return [
                    'id' => $id,
                    'name' => 'Alat Kesehatan',
                    'default_code' => 'ALK001',
                    'product_variant_id' => [77, '[ALK001] Alat Kesehatan'],
                    'product_variant_ids' => [77],
                    'item_ids' => [15515],
                    'detail_disk' => [17031],
                ];
            }
        );

        $searchModels = [];
        $odoo->method('searchRead')->willReturnCallback(
            function (string $model, array $domain = []) use (&$searchModels): array {
                $searchModels[] = $model;

                return ['length' => 1, 'records' => [['location_id' => [12, 'CENTER/Stock'], 'quantity' => 5]]];
            }
        );

        $readCalls = [];
        $odoo->method('read')->willReturnCallback(
            function (string $model, array $ids = []) use (&$readCalls): array {
                $readCalls[] = ['model' => $model, 'ids' => $ids];

                return $model === 'product.pricelist.item'
                    ? [['id' => 15515, 'fixed_price' => 100000]]
                    : [['id' => 17031, 'metode_disk' => 'M']];
            }
        );

        $res = (new ProductOdooServices($odoo))->detail(42);

        $this->assertSame([['model' => 'product.template', 'id' => 42]], $readModels);
        $this->assertSame(77, $res['variant_id']);
        $this->assertCount(1, $res['on_hand']);
        $this->assertSame(['stock.quant'], $searchModels);
        $this->assertSame(
            [
                ['model' => 'product.pricelist.item', 'ids' => [15515]],
                ['model' => 'tbl_detail_disk', 'ids' => [17031]],
            ],
            $readCalls,
        );
        $this->assertCount(1, $res['pricelist_items']);
        $this->assertCount(1, $res['disk_details']);
    }

    public function test_get_all_type_filter(): void
    {
        $odoo = $this->createMock(OdooClient::class);

        $captured = [];
        $odoo->method('searchRead')->willReturnCallback(
            function (string $model, array $domain = []) use (&$captured): array {
                $captured[] = ['model' => $model, 'domain' => $domain];

                return ['length' => 0, 'records' => []];
            }
        );

        $svc = new ProductOdooServices($odoo);
        $svc->getAll('', 10, 0);
        $svc->getAll('', 10, 0, ['product']);

        $this->assertSame('product.template', $captured[0]['model']);
        $this->assertSame(['type', 'in', ['consu', 'product']], $captured[0]['domain'][0]);
        $this->assertSame(['type', '=', 'product'], $captured[1]['domain'][0]);
    }

    public function test_detail_falls_back_to_first_variant_id(): void
    {
        $odoo = $this->createMock(OdooClient::class);

        $odoo->method('readOne')->willReturn([
            'id' => 9,
            'name' => 'Produk Multi Varian',
            'default_code' => 'MV001',
            'product_variant_id' => false,
            'product_variant_ids' => [101, 102],
            'item_ids' => [],
            'detail_disk' => [],
        ]);
        $odoo->method('searchRead')->willReturn(['length' => 0, 'records' => []]);
        $odoo->method('read')->willReturn([]);

        $res = (new ProductOdooServices($odoo))->detail(9);

        $this->assertSame(101, $res['variant_id']);
        $this->assertSame([], $res['on_hand']);
        $this->assertSame([], $res['pricelist_items']);
        $this->assertSame([], $res['disk_details']);
    }
}
