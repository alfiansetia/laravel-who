<?php

namespace App\Services;

use App\Services\Odoo\OdooClient;
use App\Services\Odoo\OdooDomain;

class GudangServices
{
    private const LIST_FIELDS = [
        'name', 'code', 'company_id', 'lot_stock_id',
        'in_type_id', 'out_type_id', 'int_type_id',
    ];

    public function __construct(private OdooClient $odoo) {}

    public function getAll(string $query = '', int $limit = 24, int $offset = 0): array
    {
        $query = trim($query);
        $limit = $limit <= 0 ? 24 : $limit;

        $domain = $query === ''
            ? []
            : OdooDomain::or(
                OdooDomain::ilike('name', $query),
                OdooDomain::ilike('code', $query),
            );

        return $this->odoo->searchRead('stock.warehouse', $domain, self::LIST_FIELDS, $limit, (int) $offset, 'name ASC');
    }
}
