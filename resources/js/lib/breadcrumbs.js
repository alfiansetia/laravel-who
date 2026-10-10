/**
 * Resolver breadcrumb murni frontend (pengganti $bcms Blade).
 *
 * Sumber kebenaran: URL Inertia aktif + prop `title` page.
 * Tanpa history, tanpa localStorage — deep-link/refresh/share-link tetap benar.
 *
 * Aturan:
 * - `/` dan `/home` → [] (tidak ada breadcrumb, komponen tidak render).
 * - Index (`/alamats`) → [{ label: title, current: true }].
 * - Nested (`/alamats/create`, `/alamats/5/edit`) → [{ parent link }, { title current }].
 * - Segmen ID numerik diabaikan; label parent dari peta INDEX, fallback humanize.
 *
 * @param {{ url?: string, title?: string }} input
 * @returns {Array<{ label: string, href?: string, current?: boolean }>}
 */
const INDEX = {
    alamats: { label: 'List Alamat', href: '/alamats' },
    'alamat-baru': { label: 'List Alamat Baru', href: '/alamat-baru' },
    koli: { label: 'Koli', href: '/koli' },
    vendors: { label: 'Vendor Odoo', href: '/vendors' },
    products: { label: 'Data Product', href: '/products' },
    stock: { label: 'Data Stock', href: '/stock' },
    'form-qc': { label: 'Form QC', href: '/form-qc' },
    'qc-lots': { label: 'QC Lot', href: '/qc-lots' },
    lots: { label: 'Data Lot', href: '/lots' },
    'product-odoo': { label: 'Product Odoo', href: '/product-odoo' },
    po: { label: 'PO', href: '/po' },
    ri: { label: 'RI', href: '/ri' },
    so: { label: 'SO', href: '/so' },
    do: { label: 'DO', href: '/do' },
    it: { label: 'IT', href: '/it' },
    packs: { label: 'Packing List', href: '/packs' },
    basts: { label: 'List BAST', href: '/basts' },
    sops: { label: 'SOP QC', href: '/sops' },
    kargans: { label: 'List Kargan', href: '/kargans' },
    kontaks: { label: 'Kontak', href: '/kontaks' },
    akls: { label: 'AKL', href: '/akls' },
    'izin-edars': { label: 'Data Izin Edar', href: '/izin-edars' },
    atk: { label: 'Data ATK', href: '/atk' },
    'atk-import': { label: 'Import Data ATK', href: '/atk-import' },
    'atk-trx': { label: 'Transaksi ATK', href: '/atk-trx' },
    problems: { label: 'Data Problem', href: '/problems' },
    product_images: { label: 'Product Images', href: '/product_images' },
    settings: { label: 'App Setting', href: '/settings' },
    'monitor-do': { label: 'Monitor DO', href: '/monitor-do' },
    'shipping-estimate': { label: 'Shipping Estimate', href: '/shipping-estimate' },
    'tools/stt': { label: 'Speech To Text', href: '/tools/stt' },
    'tools/kalkulator': { label: 'Kalkulator Nilai', href: '/tools/kalkulator' },
    'tools/laporan-pengiriman': { label: 'Laporan Pengiriman', href: '/tools/laporan-pengiriman' },
    'tools/laporan-luarkota': { label: 'Laporan Luarkota', href: '/tools/laporan-luarkota' },
    'tools/sn': { label: 'SN Tools', href: '/tools/sn' },
    'tools/scoreboard': { label: 'Scoreboard', href: '/tools/scoreboard' },
    'tools/ocr': { label: 'OCR Tool', href: '/tools/ocr' },
    'tools/spreadsheet': { label: 'Spreadsheet PLTBB', href: '/tools/spreadsheet' },
    'tools/print-resi': { label: 'Print Resi', href: '/tools/print-resi' },
    'tools/file-search': { label: 'File Search Manager', href: '/tools/file-search' },
};

function humanize(segment) {
    return String(segment ?? '')
        .replace(/[_-]+/g, ' ')
        .trim()
        .replace(/\b\w/g, (c) => c.toUpperCase());
}

function isId(segment) {
    return /^\d+$/.test(String(segment ?? ''));
}

export function resolveBreadcrumbs({ url = '/', title = '' } = {}) {
    const path = `/${String(url ?? '/').split(/[?#]/)[0].replace(/^\/+|\/+$/g, '')}`;
    if (path === '/' || path === '/home') {
        return [];
    }

    const segments = path.replace(/^\/+/, '').split('/').filter(Boolean);
    const twoKey = segments.slice(0, 2).join('/');
    let base = null;
    let rest = [];
    if (INDEX[twoKey]) {
        base = INDEX[twoKey];
        rest = segments.slice(2);
    } else if (INDEX[segments[0]]) {
        base = INDEX[segments[0]];
        rest = segments.slice(1);
    } else {
        // Segmen tak dikenal: parent = humanize(segmen pertama), current = title.
        const current = title || humanize(segments[segments.length - 1]);
        if (segments.length === 1) {
            return current ? [{ label: current, current: true }] : [];
        }
        return [
            { label: humanize(segments[0]), href: `/${segments[0]}` },
            { label: current, current: true },
        ];
    }

    const meaningful = rest.filter((seg) => !isId(seg));
    if (rest.length === 0) {
        return [{ label: title || base.label, current: true }];
    }
    return [
        { label: base.label, href: base.href },
        { label: title || humanize(meaningful[meaningful.length - 1]) || base.label, current: true },
    ];
}

export const breadcrumbIndexLabels = INDEX;
