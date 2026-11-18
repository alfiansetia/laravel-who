<?php

namespace App\Services;

use App\Services\Odoo\OdooClient;
use App\Services\Odoo\OdooDomain;
use Illuminate\Support\Arr;

class ProductServices
{
    public function __construct(private OdooClient $odoo) {}

    /** @return array<int, array> */
    public function getAll(string $query = '', int $limit = 7000, ?int $offset = 0): array
    {
        $domain = [['type', 'in', ['consu', 'product']]];

        if (trim($query) !== '') {
            array_push($domain, ...OdooDomain::or(
                OdooDomain::ilike('default_code', $query),
                OdooDomain::ilike('product_variant_ids.default_code', $query),
                OdooDomain::ilike('name', $query),
                OdooDomain::ilike('barcode', $query),
            ));
        }

        // NB: versi lama mengabaikan $query dan selalu mencari 'bdf'.
        $result = $this->odoo->searchRead('product.template', $domain, [
            'id', 'img_small', 'sequence', 'default_code', 'name',
            'categ_id', 'akl_id', 'x_studio_valid_to_akl', 'type',
            'qty_available', 'virtual_available', 'uom_id', 'active',
            'x_studio_field_i3XMM', 'description',
        ], $limit, $offset ?? 0, 'default_code ASC');

        return Arr::get($result, 'records', []);
    }
}
