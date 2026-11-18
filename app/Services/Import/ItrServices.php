<?php

namespace App\Services\Import;

use App\Services\Odoo\OdooClient;

class ItrServices
{
    private const LIST_FIELDS = [
        'name', 'tanggal', 'ref', 'state_picking', 'state_picking_terima',
        'warehouse', 'state', 'user_id',
    ];

    private const DETAIL_FIELDS = [
        'state', 'name', 'tanggal', 'ref', 'warehouse_src', 'locs_src',
        'warehouse', 'locs', 'ket', 'picking', 'state_picking',
        'picking_terima', 'state_picking_terima', 'user_id', 'detail',
        'display_name',
    ];

    private const DETAIL_LINE_FIELDS = ['nama_produk', 'qty', 'satuan'];

    public function __construct(private OdooClient $odoo) {}

    public function getAll(string $query = '', int $limit = 80, int $offset = 0, string $product_search = ''): array
    {
        $query = trim($query);
        $domain = $query === '' ? [] : [['name', 'ilike', $query]];

        if (trim($product_search) !== '') {
            $domain[] = ['detail.nama_produk', 'ilike', trim($product_search)];
        }

        return $this->odoo->searchRead(
            'tbl_it_request',
            $domain,
            self::LIST_FIELDS,
            $limit <= 0 ? 80 : $limit,
            $offset,
            'name DESC',
        );
    }

    public function detail(int $id): array
    {
        $res = $this->odoo->readOne('tbl_it_request', $id, self::DETAIL_FIELDS, ['bin_size' => true]);

        try {
            $res['detail_lines'] = $this->getDetails((array) ($res['detail'] ?? []));
        } catch (\Throwable) {
            $res['detail_lines'] = [];
        }

        return $res;
    }

    /** @return array<int, array> */
    public function getDetails(array $ids): array
    {
        return $this->odoo->read('tbl_it_request_detail', $ids, self::DETAIL_LINE_FIELDS);
    }
}
