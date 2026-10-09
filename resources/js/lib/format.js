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

export function truncate(text, length = 40) {
    const s = String(text ?? '');
    return s.length > length ? s.slice(0, length) + '...' : s;
}
