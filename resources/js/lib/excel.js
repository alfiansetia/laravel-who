// Reader .xlsx berbasis SheetJS (xlsx), lazy-loaded agar tidak masuk bundle awal.
// API disengaja mirip pemakaian lama (ExcelJS) supaya caller hampir tidak berubah:
//   const wb = await loadExcelWorkbook(buffer);
//   const names = workbookSheetNames(wb);
//   const matrix = await getSheetMatrix(wb, names[0]);

async function loadXlsx() {
    return await import('xlsx');
}

export function normalizeExcelValue(value) {
    if (value === null || value === undefined) {
        return null;
    }
    if (value instanceof Date) {
        return value;
    }
    if (typeof value === 'object') {
        // Sisa kompatibilitas dengan bentuk sel ExcelJS (richText/hyperlink/formula).
        if (Array.isArray(value.richText)) {
            return value.richText.map((r) => r.text ?? '').join('') || null;
        }
        if (value.text !== undefined && value.hyperlink !== undefined) {
            return value.text ?? null;
        }
        if (value.result !== undefined) {
            return normalizeExcelValue(value.result);
        }
        return null;
    }
    return value;
}

export async function loadExcelWorkbook(buffer) {
    const XLSX = await loadXlsx();
    // `buffer` adalah ArrayBuffer dari file.arrayBuffer().
    // cellDates:true → sel tanggal jadi Date (didukung formatISO/excelDateToString di caller).
    return XLSX.read(buffer, { type: 'array', cellDates: true, sheetStubs: false });
}

export function workbookSheetNames(workbook) {
    return workbook?.SheetNames ?? [];
}

function isNonEmptyRow(row) {
    return (
        Array.isArray(row) &&
        row.some((v) => v !== null && v !== undefined && String(v).trim() !== '')
    );
}

export async function getSheetMatrix(workbook, nameOrIndex = 0) {
    const XLSX = await loadXlsx();
    const names = workbookSheetNames(workbook);
    const name = typeof nameOrIndex === 'number' ? names[nameOrIndex] : nameOrIndex;
    const ws = name ? workbook.Sheets[name] : undefined;
    if (!ws) {
        return [];
    }
    const aoa = XLSX.utils.sheet_to_json(ws, {
        header: 1,
        raw: true,
        defval: null,
        blankrows: false,
    });
    // Samakan perilaku lama: buang baris yang seluruh selnya kosong.
    return (aoa ?? []).filter(isNonEmptyRow).map((row) => row.map(normalizeExcelValue));
}
