/**
 * Normalisasi nama tujuan dari display name Odoo (`partner_id[1]`).
 *
 * Raw Odoo sering mengulang segmen: `Arzia Karya Revolution, PT, Arzia Karya
 * Revolution, PT`, atau menaruh badan usaha di belakang: `Taopa, PKM`.
 * Aturan: split koma → trim → dedupe (case-insensitive) → bila tepat 2 segmen
 * dan salah satunya token badan usaha/institusi, letakkan token di depan:
 * `PT Arzia Karya Revolution`, `PKM Taopa`. Selain itu gabung rapi dgn `, `.
 *
 * Dipakai saat onchange pilihan DO (auto-fill), bukan saat ketik manual.
 */
const TYPE_TOKENS = new Set(
    [
        'PT',
        'CV',
        'UD',
        'PD',
        'FA',
        'KOPERASI',
        'YAYASAN',
        'PERUM',
        'PKM',
        'PUSKESMAS',
        'PUSTU',
        'POSKESDES',
        'POSYANDU',
        'POLINDES',
        'RS',
        'RSU',
        'RSUD',
        'RSIA',
        'RSI',
        'RSK',
        'KLINIK',
        'APOTEK',
        'TOKO',
        'DINKES',
        'DINAS',
        'UPTD',
        'UPT',
        'LAB',
        'LABORATORIUM',
        'BALAI',
    ].map((t) => t.toUpperCase()),
);

function canonToken(segment) {
    return String(segment ?? '').replace(/\./g, '').trim().toUpperCase();
}

export function parseTujuan(raw) {
    const parts = String(raw ?? '')
        .split(',')
        .map((s) => s.trim())
        .filter(Boolean);

    const seen = new Set();
    const uniq = [];
    for (const p of parts) {
        const key = p.toLowerCase();
        if (!seen.has(key)) {
            seen.add(key);
            uniq.push(p);
        }
    }

    if (uniq.length === 0) {
        return '';
    }

    if (uniq.length === 2) {
        const [a, b] = uniq;
        if (TYPE_TOKENS.has(canonToken(b))) {
            return `${canonToken(b)} ${a}`;
        }
        if (TYPE_TOKENS.has(canonToken(a))) {
            return `${canonToken(a)} ${b}`;
        }
    }

    return uniq.join(', ');
}
