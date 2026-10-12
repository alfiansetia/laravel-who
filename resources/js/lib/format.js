export function formatNumber(value) {
    const n = Number(value ?? 0);
    if (Number.isNaN(n)) {
        return '0';
    }
    return n.toLocaleString('id-ID');
}

export function formatQtyID(value) {
    const n = Number(value ?? 0);
    if (Number.isNaN(n)) {
        return '0';
    }
    return n.toLocaleString('id-ID');
}

export function formatBerat(value) {
    const n = Number(value ?? 0);
    if (Number.isNaN(n)) {
        return '0';
    }
    return `${n.toLocaleString('id-ID')} kg`;
}

export function formatDate(value) {
    if (!value) {
        return '-';
    }
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) {
        return String(value);
    }
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

export function formatDateDMY(value) {
    if (value === null || value === undefined || value === '') {
        return '-';
    }
    if (value instanceof Date) {
        if (Number.isNaN(value.getTime())) {
            return '-';
        }
        const dd = String(value.getDate()).padStart(2, '0');
        const mm = String(value.getMonth() + 1).padStart(2, '0');
        return `${dd}/${mm}/${value.getFullYear()}`;
    }
    const s = String(value).trim();
    if (s === '' || s === '-') {
        return '-';
    }
    const m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (m) {
        return `${m[3]}/${m[2]}/${m[1]}`;
    }
    const dm = s.match(/^(\d{1,2})[/-](\d{1,2})[/-](\d{4})/);
    if (dm) {
        return `${dm[1].padStart(2, '0')}/${dm[2].padStart(2, '0')}/${dm[3]}`;
    }
    const d = new Date(s);
    if (Number.isNaN(d.getTime())) {
        return s;
    }
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    return `${dd}/${mm}/${d.getFullYear()}`;
}

export function truncate(text, length = 40) {
    const s = String(text ?? '');
    return s.length > length ? s.slice(0, length) + '...' : s;
}
