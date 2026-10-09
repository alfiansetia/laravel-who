<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Home/Index', [
            'title' => 'Home',
            'sections' => $this->sections(),
        ]);
    }

    /**
     * @return array<int, array{title: string, icon: string, accent: string, menus: array<int, array{title: string, desc: string, icon: string, url: string}>}>
     */
    private function sections(): array
    {
        $build = fn (array $menus): array => array_map(fn (array $m): array => [
            'title' => $m['title'],
            'desc' => $m['desc'],
            'icon' => $m['icon'],
            'url' => route($m['route']),
        ], $menus);

        return [
            [
                'title' => 'Menu Utama',
                'icon' => 'LayoutGrid',
                'accent' => '#6366f1',
                'menus' => $build([
                    ['route' => 'products.index', 'icon' => 'Box', 'title' => 'Product', 'desc' => 'Daftar master produk'],
                    ['route' => 'alamats.index', 'icon' => 'MapPin', 'title' => 'Alamat', 'desc' => 'Master data alamat'],
                    ['route' => 'alamat_baru.index', 'icon' => 'Navigation', 'title' => 'Alamat Baru', 'desc' => 'Input alamat baru'],
                    ['route' => 'basts.index', 'icon' => 'FileText', 'title' => 'BASTUF', 'desc' => 'Berita acara serah terima'],
                    ['route' => 'kontaks.index', 'icon' => 'UserPlus', 'title' => 'Kontak', 'desc' => 'Manajemen data kontak'],
                    ['route' => 'packs.index', 'icon' => 'ListChecks', 'title' => 'Packing List', 'desc' => 'Daftar packing list'],
                    ['route' => 'sops.index', 'icon' => 'ShieldCheck', 'title' => 'SOP QC', 'desc' => 'Standard prosedur QC'],
                    ['route' => 'kargans.index', 'icon' => 'Truck', 'title' => 'Kargan', 'desc' => 'Manajemen logistik kargan'],
                    ['route' => 'atk.index', 'icon' => 'PenTool', 'title' => 'ATK', 'desc' => 'Inventaris alat tulis kantor'],
                    ['route' => 'product_images.index', 'icon' => 'Image', 'title' => 'Images', 'desc' => 'Gallery foto produk'],
                    ['route' => 'qc_lots.index', 'icon' => 'PackageSearch', 'title' => 'QC Lot', 'desc' => 'Pengecekan lot QC'],
                    ['route' => 'problems.index', 'icon' => 'CircleAlert', 'title' => 'Problem', 'desc' => 'Manajemen masalah produk'],
                    ['route' => 'izin_edars.index', 'icon' => 'BadgeCheck', 'title' => 'Izin Edar', 'desc' => 'Data izin edar produk'],
                    ['route' => 'akls.index', 'icon' => 'IdCard', 'title' => 'AKL', 'desc' => 'Arsip lampiran AKL'],
                    ['route' => 'shipping_estimate.index', 'icon' => 'Ship', 'title' => 'Estimasi Kirim', 'desc' => 'Estimasi ongkos kirim'],
                ]),
            ],
            [
                'title' => 'Odoo ERP',
                'icon' => 'Cloud',
                'accent' => '#a855f7',
                'menus' => $build([
                    ['route' => 'stock.index', 'icon' => 'Boxes', 'title' => 'Stock', 'desc' => 'Pantau stok barang'],
                    ['route' => 'po.index', 'icon' => 'ShoppingCart', 'title' => 'PO', 'desc' => 'Purchase order'],
                    ['route' => 'ri.index', 'icon' => 'ArrowDownToLine', 'title' => 'RI', 'desc' => 'Receiving inventory'],
                    ['route' => 'it.index', 'icon' => 'RefreshCw', 'title' => 'IT', 'desc' => 'Inventory transfer'],
                    ['route' => 'so.index', 'icon' => 'ShoppingBag', 'title' => 'SO', 'desc' => 'Sales order'],
                    ['route' => 'do.index', 'icon' => 'PackageOpen', 'title' => 'DO', 'desc' => 'Delivery order'],
                    ['route' => 'monitor.do', 'icon' => 'Activity', 'title' => 'Monitor DO', 'desc' => 'Pantau DO berjalan'],
                    ['route' => 'vendors.index', 'icon' => 'Store', 'title' => 'Vendor', 'desc' => 'Daftar vendor aktif'],
                    ['route' => 'lots.index', 'icon' => 'Box', 'title' => 'Lot/SN', 'desc' => 'Daftar Lot/SN'],
                    ['route' => 'product_odoo.index', 'icon' => 'Box', 'title' => 'Product', 'desc' => 'Daftar Product Odoo'],
                ]),
            ],
            [
                'title' => 'Peralatan Kerja',
                'icon' => 'Wrench',
                'accent' => '#f59e0b',
                'menus' => $build([
                    ['route' => 'tools.kalkulator', 'icon' => 'Calculator', 'title' => 'Kalkulator', 'desc' => 'Hitung nilai barang'],
                    ['route' => 'qc.index', 'icon' => 'FileCheck', 'title' => 'Form QC', 'desc' => 'Generate form pengecekan'],
                    ['route' => 'tools.sn', 'icon' => 'Barcode', 'title' => 'SN', 'desc' => 'Generate serial number'],
                    ['route' => 'tools.laporan_pengiriman', 'icon' => 'FileSpreadsheet', 'title' => 'Kiriman', 'desc' => 'Laporan kiriman harian'],
                    ['route' => 'tools.laporan_luarkota', 'icon' => 'Map', 'title' => 'Luarkota', 'desc' => 'Laporan kiriman luar kota'],
                    ['route' => 'tools.print_resi', 'icon' => 'Printer', 'title' => 'Print Resi', 'desc' => 'Cetak resi pengiriman'],
                    ['route' => 'tools.spreadsheet', 'icon' => 'Table2', 'title' => 'PLTBB', 'desc' => 'Spreadsheet PLTBB'],
                    ['route' => 'settings.index', 'icon' => 'Settings', 'title' => 'Setting', 'desc' => 'Pengaturan aplikasi'],
                ]),
            ],
            [
                'title' => 'Teknologi Lainnya',
                'icon' => 'Sparkles',
                'accent' => '#10b981',
                'menus' => $build([
                    ['route' => 'tools.stt', 'icon' => 'Mic', 'title' => 'STT', 'desc' => 'Ucapan ke teks'],
                    ['route' => 'tools.ocr', 'icon' => 'ScanText', 'title' => 'OCR', 'desc' => 'Ekstrak teks gambar'],
                    ['route' => 'tools.scoreboard', 'icon' => 'Trophy', 'title' => 'Scoreboard', 'desc' => 'Papan skor digital'],
                    ['route' => 'tools.file_search', 'icon' => 'Search', 'title' => 'File Search', 'desc' => 'Cari file di server'],
                ]),
            ],
        ];
    }
}
