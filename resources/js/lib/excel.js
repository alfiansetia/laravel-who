// Reader .xlsx berbasis ExcelJS, lazy-loaded agar tidak masuk bundle awal.
// API:
//   const wb = await loadExcelWorkbook(buffer);
//   const names = workbookSheetNames(wb);
//   const matrix = await getSheetMatrix(wb, names[0]);

async function loadExcelJS() {
    const mod = await import('exceljs');
    return mod.default ?? mod;
}

export function normalizeExcelValue(value) {
    if (value === null || value === undefined) {
        return null;
    }
    if (value instanceof Date) {
        return value;
    }
    if (typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') {
        return value;
    }
    if (typeof value === 'object') {
        // Sel rich-text ExcelJS: { richText: [{ text }] }.
        if (Array.isArray(value.richText)) {
            return value.richText.map((r) => r.text ?? '').join('') || null;
        }
        // Sel hyperlink ExcelJS: { text, hyperlink }.
        if (value.text !== undefined && value.hyperlink !== undefined) {
            return value.text ?? null;
        }
        // Sel formula ExcelJS: { formula, result } / { sharedFormula, result }.
        if (value.result !== undefined) {
            return normalizeExcelValue(value.result);
        }
        if (typeof value.text === 'string') {
            return value.text;
        }
        // Sel error ExcelJS: { error } → anggap kosong.
        return null;
    }
    return value;
}

export async function loadExcelWorkbook(buffer) {
    const ExcelJS = await loadExcelJS();
    // `buffer` adalah ArrayBuffer dari file.arrayBuffer().
    // ExcelJS otomatis mem-parse sel tanggal menjadi Date.
    const workbook = new ExcelJS.Workbook();
    await workbook.xlsx.load(buffer);
    return workbook;
}

export function workbookSheetNames(workbook) {
    return workbook?.worksheets?.map((ws) => ws.name) ?? [];
}

function isNonEmptyRow(row) {
    return (
        Array.isArray(row) &&
        row.some((v) => v !== null && v !== undefined && String(v).trim() !== '')
    );
}

export async function getSheetMatrix(workbook, nameOrIndex = 0) {
    const names = workbookSheetNames(workbook);
    const name = typeof nameOrIndex === 'number' ? names[nameOrIndex] : nameOrIndex;
    const ws = name ? workbook.getWorksheet(name) : undefined;
    if (!ws) {
        return [];
    }
    const colCount = ws.columnCount;
    if (!colCount) {
        return [];
    }
    // Samakan perilaku lama (sheet_to_json header:1, defval:null):
    // tiap baris dipad ke lebar sheet, baris yang seluruh selnya kosong dibuang.
    const out = [];
    ws.eachRow({ includeEmpty: false }, (row) => {
        const arr = [];
        for (let c = 1; c <= colCount; c++) {
            arr.push(normalizeExcelValue(row.getCell(c).value));
        }
        if (isNonEmptyRow(arr)) {
            out.push(arr);
        }
    });
    return out;
}
