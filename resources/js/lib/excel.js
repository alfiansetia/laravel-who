export function normalizeExcelValue(value) {
    if (value === null || value === undefined) {
        return null;
    }
    if (value instanceof Date) {
        return value;
    }
    if (typeof value === 'object') {
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
    const { default: ExcelJS } = await import('exceljs');
    const workbook = new ExcelJS.Workbook();
    await workbook.xlsx.load(buffer);
    return workbook;
}

export function workbookSheetNames(workbook) {
    return workbook.worksheets.map((ws) => ws.name);
}

export function sheetToMatrix(worksheet) {
    const matrix = [];
    worksheet.eachRow({ includeEmpty: false }, (row) => {
        const values = row.values ?? [];
        const cells = [];
        for (let c = 1; c < values.length; c++) {
            cells.push(normalizeExcelValue(values[c]));
        }
        if (cells.some((v) => v !== null && String(v).trim() !== '')) {
            matrix.push(cells);
        }
    });
    return matrix;
}
