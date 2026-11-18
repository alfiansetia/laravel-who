<?php

namespace App\Services;

use App\Services\Odoo\InteractsWithPicking;
use App\Services\Odoo\OdooClient;
use App\Services\Odoo\OdooDomain;
use Illuminate\Support\Arr;

class DoServices
{
    use InteractsWithPicking;

    private const LIST_FIELDS = [
        'name', 'x_studio_no_do_manual', 'confirmation_date_so', 'date',
        'scheduled_date', 'force_date', 'partner_id', 'location_dest_id',
        'location_id', 'origin', 'x_studio_tags', 'x_studio_field_0rSR5',
        'priority_so', 'note_to_wh', 'note_itr', 'invoice_state',
        'group_id', 'backorder_id', 'state', 'priority', 'picking_type_id',
        'ekspedisi_id', 'shipping_date', 'tanggal_sampai', 'sale_id',
        'partner_address3',
    ];

    public function __construct(
        private OdooClient $odoo,
        private SoServices $so,
        private int $gudangId = 2,
    ) {}

    /**
     * Gudang = picking_type_id Odoo (outgoing per warehouse).
     * 2 = CENTER. Contoh lain: 757 BAD STOCK, 762 DEMO.
     */
    public function withGudang(int $gudangId): static
    {
        $clone = clone $this;
        $clone->gudangId = $gudangId;

        return $clone;
    }

    public function getAll(
        string $query = 'CENT/OUT',
        int $limit = 80,
        int $offset = 0,
        string $note_search = '',
        string $product_search = '',
        string $date_from = '',
        string $date_to = '',
    ): array {
        $query = trim($query) === '' ? 'CENT/OUT' : $query;
        $limit = $limit <= 0 ? 80 : $limit;

        $domain = OdooDomain::and(
            ['picking_type_id', '=', $this->gudangId],
            OdooDomain::or(
                OdooDomain::ilike('name', $query),
                OdooDomain::ilike('origin', $query),
            ),
            OdooDomain::ilike('note_to_wh', $note_search),
        );

        if (trim($product_search) !== '') {
            $domain[] = ['product_id', 'ilike', trim($product_search)];
        }

        if (($from = static::normalizeDateTime($date_from)) !== null) {
            $domain[] = ['confirmation_date_so', '>=', $from];
        }

        if (($to = static::normalizeDateTime($date_to)) !== null) {
            $domain[] = ['confirmation_date_so', '<=', $to];
        }

        return $this->odoo->searchRead('stock.picking', $domain, self::LIST_FIELDS, $limit, (int) $offset, '', [
            'tz' => 'GMT',
            'active_model' => 'stock.picking.type',
            'active_id' => $this->gudangId,
            'active_ids' => [$this->gudangId],
            'search_default_picking_type_id' => [$this->gudangId],
            'default_picking_type_id' => $this->gudangId,
            'contact_display' => 'partner_address',
            'search_default_available' => 1,
            'search_disable_custom_filters' => true,
        ]);
    }

    /**
     * Normalisasi input tanggal-jam menjadi format Odoo `Y-m-d H:i:s`.
     * Terima `Y-m-d H:i`, `Y-m-d H:i:s`, atau `Y-m-d\TH:i` (datetime-local).
     * Input diasumsikan SUDAH UTC (frontend `toApiDatetime` mengonversi
     * WIB -> UTC), jadi teruskan apa adanya tanpa shift timezone.
     * Return null bila kosong/tidak valid.
     */
    protected static function normalizeDateTime(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        $value = str_replace('T', ' ', $value);
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $value)) {
            $value .= ':00';
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
            return null;
        }

        return $value;
    }

    public function detail(int $id): array
    {
        $res = $this->odoo->readOne('stock.picking', $id, [
            'id', 'cancel_done_picking', 'picking_type_code',
            'show_operations', 'move_line_exist', 'state',
            'picking_type_entire_packs', 'has_scrap_move', 'has_tracking',
            'name', 'x_studio_field_2kd16', 'x_studio_no_do_manual',
            'partner_id', 'partner_address', 'partner_address2',
            'partner_address3', 'partner_address4', 'x_studio_customer',
            'bill_to', 'delivery_manual', 'ekspedisi_id',
            'x_studio_update_eta', 'pp', 'ppk', 'location_id', 'kurir_id',
            'kurir_state', 'tanggal_sampai', 'tanggal_persetujuan',
            'location_dest_id', 'picking_type_id', 'backorder_id', 'date',
            'force_date', 'x_studio_date', 'date_done',
            'confirmation_date_so', 'print_x_studio_date',
            'print_tanggal_sampai', 'tgl_bast', 'origin',
            'x_studio_id_paket', 'x_studio_no_po', 'x_studio_no_ska',
            'itr_id', 'invoice_state', 'cancel_reason', 'note_itr',
            'receipts_date', 'no_po', 'no_aks', 'no_ska', 'no_sph',
            'no_si', 'tgl_akhir_kontrak', 'note_to_wh', 'priority_so',
            'owner_id', 'penanggung_jawab', 'jumlah_hari', 'partner',
            'schedule_id', 'move_line_ids_without_package',
            'package_level_ids_details', 'immediate_transfer',
            'move_ids_without_package', 'package_level_ids', 'move_type',
            'sale_id', 'company_id', 'group_id', 'scheduled_date',
            'priority', 'note', 'move_temp_id', 'message_follower_ids',
            'activity_ids', 'display_name',
        ], [
            'tz' => 'GMT',
            'active_model' => 'stock.picking.type',
            'active_id' => $this->gudangId,
            'active_ids' => [$this->gudangId],
            'search_default_picking_type_id' => [$this->gudangId],
            'default_picking_type_id' => $this->gudangId,
            'contact_display' => 'partner_address',
            'search_default_available' => 1,
            'search_disable_custom_filters' => true,
            'bin_size' => true,
        ]);

        try {
            $res['move_ids_detail'] = $this->getOrderLines($res['move_ids_without_package'] ?? []);
            $res['move_line_detail'] = $this->getOrderLinesDetail($res['move_line_ids_without_package'] ?? []);
        } catch (\Throwable) {
            $res['move_ids_detail'] = [];
            $res['move_line_detail'] = [];
        }

        try {
            $partnerIds = array_values(array_unique(array_filter([
                Arr::get($res, 'bill_to.0'),
                Arr::get($res, 'partner_id.0'),
            ])));
            $partners = $this->getPartners($partnerIds);
            $res['bill_to_detail'] = collect($partners)->where('id', Arr::get($res, 'bill_to.0'))->first();
            $res['partner_detail'] = collect($partners)->where('id', Arr::get($res, 'partner_id.0'))->first();
            $res['partner_details'] = $partners;
        } catch (\Throwable) {
            $res['bill_to_detail'] = [];
            $res['partner_detail'] = [];
            $res['partner_details'] = [];
        }

        try {
            $res['so_detail'] = $this->so->detail((int) Arr::get($res, 'sale_id.0'));
        } catch (\Throwable) {
            $res['so_detail'] = [];
        }

        return $res;
    }
}
