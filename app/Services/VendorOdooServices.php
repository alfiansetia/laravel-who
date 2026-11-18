<?php

namespace App\Services;

use App\Services\Odoo\OdooClient;
use App\Services\Odoo\OdooDomain;

/**
 * Master vendor Odoo (res.partner). Baca langsung dari ERP,
 * tanpa tabel lokal (lihat ADR-010).
 */
class VendorOdooServices
{
    private const MODEL = 'res.partner';

    private const LIST_FIELDS = [
        'id', 'name', 'display_name', 'email', 'phone', 'mobile',
        'website', 'street', 'city', 'country_id', 'vat',
        'supplier', 'is_company',
    ];

    private const DETAIL_FIELDS = [
        'id', 'name', 'display_name', 'email', 'phone', 'mobile',
        'website', 'street', 'street2', 'city', 'zip', 'state_id',
        'country_id', 'vat', 'supplier', 'customer', 'is_company',
        'is_seller', 'company_type', 'parent_id', 'active',
    ];

    public function __construct(private OdooClient $odoo) {}

    public function getAll(string $query = '', int $limit = 80, int $offset = 0): array
    {
        $domain = OdooDomain::and(
            ['supplier', '=', true],
            OdooDomain::or(
                OdooDomain::ilike('name', $query),
                OdooDomain::ilike('email', $query),
                OdooDomain::ilike('phone', $query),
            ),
        );

        return $this->odoo->searchRead(
            self::MODEL,
            $domain,
            self::LIST_FIELDS,
            $limit,
            $offset,
            'name ASC',
        );
    }

    public function detail(int $id): array
    {
        return $this->odoo->readOne(self::MODEL, $id, self::DETAIL_FIELDS);
    }
}
