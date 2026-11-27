/** Helper render field Odoo — cerminan logika Blade (_app scripts). */

export function odooName(value) {
    if (value === false || value === null || value === undefined || value === '') {
        return '-';
    }
    if (Array.isArray(value)) {
        const name = value[1];
        if (name === false || name === null || name === undefined || name === '') {
            return '-';
        }
        return name;
    }
    return value;
}

/**
 * Ambil ID relasi many2one Odoo ([id, nama]).
 * Odoo mengembalikan `false` bila relasi kosong, jadi cek array dulu.
 * Return 0 bila tidak ada ID valid.
 */
export function odooId(value) {
    if (Array.isArray(value)) {
        const id = Number(value[0]);
        if (Number.isInteger(id) && id > 0) {
            return id;
        }
    }
    return 0;
}

export function truncate(text, length = 40) {
    const s = String(text ?? '');
    if (!s) {
        return '-';
    }
    return s.length > length ? s.slice(0, length) + '...' : s;
}

export function formatQtyUS(value) {
    const n = parseInt(value ?? 0, 10);
    if (Number.isNaN(n)) {
        return '0';
    }
    return n.toLocaleString('en-US');
}

export function formatQtyID(value) {
    const n = parseInt(value ?? 0, 10);
    if (Number.isNaN(n)) {
        return '0';
    }
    return n.toLocaleString('id-ID');
}

/** Odoo mengirim naive UTC (`YYYY-MM-DD HH:mm:ss`): parse sebagai UTC lalu +7 jam → DD/MM/YYYY HH:mm:ss. */
export function formatOdooDate(value) {
    if (!value || value === false || value === 'False') {
        return '-';
    }
    try {
        const s = String(value).trim().replace('T', ' ');
        const m = s.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2})(?::(\d{2}))?$/);
        if (!m) {
            return String(value);
        }
        const utc = Date.UTC(+m[1], +m[2] - 1, +m[3], +m[4], +m[5], +(m[6] ?? '00'));
        if (Number.isNaN(utc)) {
            return String(value);
        }
        const d = new Date(utc + 7 * 3600 * 1000);
        const pad = (v) => String(v).padStart(2, '0');
        return `${pad(d.getUTCDate())}/${pad(d.getUTCMonth() + 1)}/${d.getUTCFullYear()} ${pad(d.getUTCHours())}:${pad(d.getUTCMinutes())}:${pad(d.getUTCSeconds())}`;
    } catch {
        return String(value);
    }
}

/** Field date-only Odoo: ambil YYYY-MM-DD langsung tanpa objek Date agar tak kena shift timezone. */
export function formatExpDate(value) {
    if (!value || value === 'False' || value === false) {
        return '';
    }
    const s = String(value).trim();
    const m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (m) {
        return `${m[1]}.${m[2]}.${m[3]}`;
    }
    const d = new Date(s);
    if (Number.isNaN(d.getTime())) {
        return String(value);
    }
    const pad = (v) => String(v).padStart(2, '0');
    return `${d.getFullYear()}.${pad(d.getMonth() + 1)}.${pad(d.getDate())}`;
}

/** Bersihkan penanda unik Odoo di ujung nama lot ("22E2.", "22E2," → "22E2"). */
export function cleanLotName(value) {
    if (value === false || value === null || value === undefined) {
        return '';
    }
    return String(value).trim().replace(/[.,]+$/, '').trim();
}

/**
 * Cerminan BE `odoo_datetime($v, 'd/m/Y')` untuk ED: naive UTC → WIB (+7),
 * hasil ` Ed. dd/mm/yyyy` atau '' bila kosong/invalid. Dipakai di tempat
 * yang harus identik dengan sync BE (KoliManager).
 */
export function formatEdOdoo(value) {
    const s = String(value ?? '').trim();
    if (!s || s === 'False' || s === 'false') {
        return '';
    }
    const m = s.match(/^(\d{4})-(\d{2})-(\d{2})(?:[T ](\d{2}):(\d{2})(?::(\d{2}))?)?$/);
    if (!m) {
        return '';
    }
    const utc = Date.UTC(+m[1], +m[2] - 1, +m[3], +(m[4] ?? '00'), +(m[5] ?? '00'), +(m[6] ?? '00'));
    if (Number.isNaN(utc)) {
        return '';
    }
    const d = new Date(utc + 7 * 3600 * 1000);
    const pad = (v) => String(v).padStart(2, '0');
    return ` Ed. ${pad(d.getUTCDate())}/${pad(d.getUTCMonth() + 1)}/${d.getUTCFullYear()}`;
}

/** Ekstrak [KODE] dari label Odoo "[KODE] Nama". */
export function getCode(label) {
    const s = String(label ?? '');
    const m = s.match(/\[(.*?)\]/);
    return m ? m[1] : '';
}

/** Ekstrak deskripsi setelah "] " dari label Odoo. */
export function getDesc(label) {
    const s = String(label ?? '');
    const m = s.match(/\]\s*(.*)/);
    return m ? m[1] : s;
}

export function isPrinted(note) {
    return String(note ?? '').includes('PRINT OK');
}
