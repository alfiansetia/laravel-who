import { useToast } from '@/composables/useToast';

export function rowsToTsv(rows, columns) {
    const header = columns.join('\t');
    const lines = rows.map((row) =>
        columns.map((col) => String(row[col] ?? '').replace(/\t/g, ' ').replace(/\n/g, ' ')).join('\t'),
    );
    return [header, ...lines].join('\n');
}

export async function copyRows(rows, columns, label = 'data') {
    const toast = useToast();
    if (rows.length === 0) {
        toast.warning('Tidak ada data untuk disalin.', undefined, { id: 'copy', duration: 2000 });
        return false;
    }
    const text = rowsToTsv(rows, columns);
    try {
        await navigator.clipboard.writeText(text);
    } catch {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
    }
    toast.success(`${rows.length} baris ${label} disalin ke clipboard.`, undefined, { id: 'copy', duration: 2000 });
    return true;
}

export async function copyText(text, successMessage = 'Berhasil disalin ke clipboard.') {
    const toast = useToast();
    if (!text) {
        toast.warning('Tidak ada teks untuk disalin.', undefined, { id: 'copy', duration: 2000 });
        return false;
    }
    try {
        await navigator.clipboard.writeText(text);
    } catch {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
    }
    toast.success(successMessage, undefined, { id: 'copy', duration: 2000 });
    return true;
}

export function downloadCsv(filename, rows, columns) {
    const toast = useToast();
    if (rows.length === 0) {
        toast.warning('Tidak ada data untuk diunduh.', undefined, { id: 'copy', duration: 2000 });
        return false;
    }
    const csv = rowsToTsv(rows, columns).replace(/\t/g, ';');
    const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename.endsWith('.csv') ? filename : `${filename}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    toast.success(`${rows.length} baris ${filename} diunduh.`);
    return true;
}
