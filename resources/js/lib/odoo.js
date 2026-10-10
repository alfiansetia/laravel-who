/** Helper render field Odoo — cerminan logika Blade (_app scripts). */

export function odooName(value) {
    if (Array.isArray(value)) {
        return value[1] ?? '-';
    }
    return value ?? '-';
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

/** Cerminan formatDateDisplay() Blade: +7 jam → DD/MM/YYYY HH:mm:ss. */
export function formatOdooDate(value) {
    if (!value) {
        return '-';
    }
    try {
        const d = new Date(String(value).replace(' ', 'T'));
        d.setHours(d.getHours() + 7);
        const pad = (v) => String(v).padStart(2, '0');
        return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
    } catch {
        return String(value);
    }
}

/** Cerminan moment(expired).format('YYYY.MM.DD') Blade. */
export function formatExpDate(value) {
    if (!value || value === 'False' || value === false) {
        return '';
    }
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) {
        return String(value);
    }
    const pad = (v) => String(v).padStart(2, '0');
    return `${d.getFullYear()}.${pad(d.getMonth() + 1)}.${pad(d.getDate())}`;
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
