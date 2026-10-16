import { useToast } from '@/composables/useToast';

// Batas ekspansi agar popup print tidak menggantung browser bila qty jumbo.
const MAX_LABELS = 1000;

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Makin sedikit kolom selembar, makin besar labelnya.
function codeFontPt(cols) {
    if (cols <= 1) {
        return 30;
    }
    if (cols === 2) {
        return 26;
    }
    if (cols === 3) {
        return 22;
    }
    if (cols === 4) {
        return 20;
    }
    if (cols === 5) {
        return 17;
    }
    if (cols === 6) {
        return 15;
    }
    return 13;
}

/**
 * Kembangkan [{ code, name, qty }] menjadi [{ code, name }] per pcs.
 * Bila fillRows true, tiap item digenapkan ke baris penuh (kelipatan kolom):
 * misal qty 1 dengan 5 kolom menghasilkan 5 label identik sebaris.
 * Qty nol dilewati. Hasil dibatasi MAX_LABELS.
 */
export function expandLabelItems(items, cols = 1, fillRows = false) {
    const safeCols = Math.max(1, Math.floor(Number(cols) || 1));
    const labels = [];
    let truncated = false;
    for (const item of items ?? []) {
        const qty = Math.max(0, Math.floor(Number(item?.qty ?? 0)));
        if (qty === 0) {
            continue;
        }
        const count = fillRows ? Math.ceil(qty / safeCols) * safeCols : qty;
        for (let i = 0; i < count; i++) {
            if (labels.length >= MAX_LABELS) {
                truncated = true;
                break;
            }
            labels.push({ code: item.code ?? '', name: item.name ?? '' });
        }
        if (truncated) {
            break;
        }
    }
    return { labels, truncated };
}

/** Total label yang akan dicetak (ikut opsi genapkan sebaris). */
export function countLabelTotal(items, cols = 1, fillRows = false) {
    const safeCols = Math.max(1, Math.floor(Number(cols) || 1));
    return (items ?? []).reduce((sum, item) => {
        const qty = Math.max(0, Math.floor(Number(item?.qty ?? 0)));
        if (qty === 0) {
            return sum;
        }
        return sum + (fillRows ? Math.ceil(qty / safeCols) * safeCols : qty);
    }, 0);
}

/**
 * Cetak tempelan dus: 3 label per lembar A4 portrait.
 * Satu label = { noCtn, kode, nama, po, docType ('RI'/'IT'), docNo,
 *   docDate (string tanggal sudah diformat), qty, akl, lotEd }.
 */
export function printDusLabels({ labels = [], title = 'Tempelan Dus' } = {}) {
    const toast = useToast();
    const list = (labels ?? []).filter(Boolean);
    if (list.length === 0) {
        toast.warning('Belum ada tempelan untuk dicetak. Isi form dulu.');
        return false;
    }

    const MAX_LABELS = 120;
    const truncated = list.length > MAX_LABELS;
    const shown = truncated ? list.slice(0, MAX_LABELS) : list;
    if (truncated) {
        toast.warning(`Maksimal ${MAX_LABELS} tempelan sekali cetak, sisanya diabaikan.`);
    }

    function row(label, value, big = false, cls = '') {
        return `<div class="row${big ? ' big' : ''}${cls ? ` ${cls}` : ''}"><div class="k">${escapeHtml(label)}</div>` +
            `<div class="c">:</div><div class="v">${escapeHtml(value) || '&nbsp;'}</div></div>`;
    }
    function dus(l) {
        const doc = l.docType === 'IT' ? 'IT' : 'RI';
        return `<div class="dus">` +
            `<div class="ctn"><span class="t">NO CTN&nbsp;&nbsp;:</span><span class="v">${escapeHtml(l.noCtn) || '&nbsp;'}</span></div>` +
            row('KODE BARANG', l.kode, true) +
            row('NAMA BARANG', l.nama) +
            row('PO', l.po) +
            row(doc, l.docNo) +
            row(`TGL ${doc}`, l.docDatePrint || l.docDate) +
            row('QTY', l.qty) +
            row('AKL / SUKET', l.akl) +
            row('LOT & ED', l.lotEd) +
            `</div>`;
    }

    let pages = '';
    for (let i = 0; i < shown.length; i += 3) {
        pages += `<div class="page">${shown.slice(i, i + 3).map(dus).join('')}</div>`;
    }

    const win = window.open('', '_blank');
    if (!win) {
        toast.error('Popup diblokir browser. Izinkan popup untuk mencetak.');
        return false;
    }
    win.document.write(
        `<html><head><title>${escapeHtml(title)}</title><style>` +
            `@page{size:A4 portrait;margin:0}` +
            `*{box-sizing:border-box}` +
            `body{margin:0;font-family:Calibri,Arial,sans-serif;color:#000}` +
            `.page{width:210mm;min-height:297mm;padding:5mm 6mm;page-break-after:always}` +
            `.page:last-child{page-break-after:auto}` +
            `.dus{border:0.5mm dashed #333;min-height:93mm;padding:1mm 3mm 1mm;page-break-inside:avoid;margin-bottom:2mm}` +
            `.dus:last-child{margin-bottom:0}` +
            `.ctn{display:flex;align-items:baseline;gap:2mm;color:#f00;border-bottom:0.3mm solid #000;padding-bottom:0.5mm}` +
            `.ctn .t{font-weight:800;font-size:17pt;white-space:nowrap}` +
            `.ctn .v{font-weight:800;font-size:26pt;flex:1;word-break:break-word;line-height:1.1}` +
            `.row{display:flex;align-items:baseline;border-bottom:0.3mm solid #000;padding:0.75mm 0}` +
            `.row:last-child{border-bottom:none}` +
            `.row .k{width:34mm;flex:none;font-size:13pt;white-space:nowrap}` +
            `.row .c{width:3mm;flex:none;font-size:13pt;text-align:center}` +
            `.row .v{flex:1;font-weight:700;font-size:17pt;word-break:break-word;line-height:1.15}` +
            `.row.big .v{font-size:24pt}` +
            `@media print{.dus{border-color:#000}}` +
            `</style></head><body onload="window.onafterprint=function(){window.close()};window.focus();window.print();setTimeout(function(){window.close()},800)">${pages}</body></html>`,
    );
    win.document.close();
    return true;
}

/**
 * Cetak tempelan AKL unit: label kecil 4 baris, grid seperti label produk.
 * Satu label per pcs sesuai qty. Isi per label:
 *   AKL <akl> / ID : <sn> / <phone> / <company>.
 * AKL / SN kosong otomatis jadi '-'.
 * items = [{ kode, nama, akl, sn, qty }] — dikelompokkan per kode,
 * tiap kelompok diawali note merah "AKL UTK : KODE (Nama)".
 */
export function printAklLabels({ items = [], cols = 5, title = 'Tempelan AKL Unit', fillRows = false, fontPt = 0, phone = '', company = '' } = {}) {
    const toast = useToast();
    const safeCols = Math.min(10, Math.max(1, Math.floor(Number(cols) || 5)));

    // Kelompokkan per produk (kode) agar tiap kelompok dapat note merah
    // "AKL UTK : KODE (Nama)" di luar label — penempel tidak tertukar.
    const groups = [];
    const byCode = new Map();
    let total = 0;
    let truncated = false;
    for (const item of items ?? []) {
        const qty = Math.max(0, Math.floor(Number(item?.qty ?? 0)));
        if (qty === 0) {
            continue;
        }
        const kode = String(item?.kode ?? '').trim();
        const nama = String(item?.nama ?? '').trim();
        const akl = String(item?.akl ?? '').trim() || '-';
        const sn = String(item?.sn ?? '').trim() || '-';
        let g = byCode.get(kode);
        if (!g) {
            g = { kode, nama, labels: [] };
            byCode.set(kode, g);
            groups.push(g);
        }
        const count = fillRows ? Math.ceil(qty / safeCols) * safeCols : qty;
        for (let i = 0; i < count; i++) {
            if (total >= MAX_LABELS) {
                truncated = true;
                break;
            }
            g.labels.push({ akl, sn });
            total += 1;
        }
        if (truncated) {
            break;
        }
    }

    if (total === 0) {
        toast.warning('Tidak ada label untuk dicetak (qty nol).');
        return false;
    }
    if (truncated) {
        toast.warning(`Qty melebihi ${MAX_LABELS} label, hanya ${MAX_LABELS} pertama yang dicetak.`);
    }

    const fixedPt = Math.min(48, Math.max(8, Math.floor(Number(fontPt) || 0)));
    const basePt = fixedPt > 0 ? fixedPt : codeFontPt(safeCols);
    // Semua baris label satu ukuran font yang sama (SN ditebelin).
    const labelPt = Math.min(basePt, 20);
    const minPx = Math.max(10, Math.round(labelPt * 1.333 * 0.4));
    const safePhone = String(phone ?? '').trim();
    const safeCompany = String(company ?? '').trim();
    const groupsJson = JSON.stringify(groups).replace(/</g, '\\u003c');
    const phoneJson = JSON.stringify(safePhone).replace(/</g, '\\u003c');
    const companyJson = JSON.stringify(safeCompany).replace(/</g, '\\u003c');

    const win = window.open('', '_blank');
    if (!win) {
        toast.error('Popup diblokir browser. Izinkan popup untuk mencetak.');
        return false;
    }
    win.document.write(
        `<html><head><title>${escapeHtml(title)}</title><style>` +
            `@page{size:A4 portrait;margin:0}` +
            `*{box-sizing:border-box}` +
            `body{margin:0;font-family:Calibri,Arial,sans-serif}` +
            `.page{width:210mm;min-height:296mm;padding:6mm;page-break-after:always}` +
            `.page:last-child{page-break-after:auto}` +
            `.grid{display:grid;grid-template-columns:repeat(${safeCols},1fr);gap:0}` +
            `.note{grid-column:1/-1;color:#c00;font-size:8pt;line-height:1;` +
            `padding:0.3mm 0;margin:0.3mm 0 0;page-break-inside:avoid;page-break-after:avoid;word-break:break-word}` +
            `.label{border:0.4mm dashed #999;display:flex;flex-direction:column;align-items:flex-start;` +
            `justify-content:center;text-align:left;padding:0 0 0 1mm;` +
            `margin:0 -0.4mm -0.4mm 0;page-break-inside:avoid;overflow:hidden}` +
            `.akl,.id,.phone,.comp{font-size:${labelPt}pt;line-height:1;word-break:break-word}` +
            `.akl{white-space:nowrap}` +
            `.id{font-weight:800}` +
            `.phone,.comp{color:#222}` +
            `#measure{position:absolute;left:-9999mm;top:0;width:198mm;visibility:hidden}` +
            `@media print{.label{border-color:#333}.note{color:#c00}#measure{display:none}}` +
            `</style></head><body onload="doPrint()">` +
            `<div id="root"></div>` +
            `<div id="measure"><div class="grid" id="mgrid"></div></div>` +
            `<script>` +
            `var GROUPS=${groupsJson};var COLS=${safeCols};var MINPX=${minPx};var MAXH=Math.round(284*96/25.4);` +
            `var PHONE=${phoneJson};var COMPANY=${companyJson};` +
            `function esc(s){return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}` +
            `function note(g){var t='AKL UTK : '+g.kode+(g.nama?' ('+g.nama+')':'');return '<div class="note" data-t="n">'+esc(t)+'</div>';}` +
            `function cell(l){return '<div class="label" data-t="l"><div class="akl">'+esc(l.akl)+'</div><div class="id">ID : '+esc(l.sn)+'</div><div class="phone">'+esc(PHONE)+'</div><div class="comp">'+esc(COMPANY)+'</div></div>';}` +
            `function fit(scope){var els=scope.querySelectorAll('.akl,.id');for(var i=0;i<els.length;i++){var el=els[i];var size=parseFloat(window.getComputedStyle(el).fontSize);while(el.scrollWidth>el.clientWidth&&size>MINPX){size-=1;el.style.fontSize=size+'px';}}}` +
            `function doPrint(){` +
            `window.onafterprint=function(){window.close();};` +
            `var mgrid=document.getElementById('mgrid');` +
            `var html='';for(var gi=0;gi<GROUPS.length;gi++){html+=note(GROUPS[gi]);for(var li=0;li<GROUPS[gi].labels.length;li++){html+=cell(GROUPS[gi].labels[li]);}}` +
            `mgrid.innerHTML=html;fit(mgrid);` +
            `var root=document.getElementById('root');` +
            `var nodes=Array.prototype.slice.call(mgrid.children);` +
            `var page=null,pgrid=null,acc=0,row=[],rowH=0;` +
            `function newPage(){page=document.createElement('div');page.className='page';pgrid=document.createElement('div');pgrid.className='grid';page.appendChild(pgrid);root.appendChild(page);acc=0;}` +
            `function putRow(){if(page&&acc+rowH>MAXH){page=null;}if(!page){newPage();}for(var q=0;q<row.length;q++){pgrid.appendChild(row[q]);}acc+=rowH;row=[];rowH=0;}` +
            `for(var n=0;n<nodes.length;n++){var nd=nodes[n];` +
            `if(nd.getAttribute('data-t')==='n'){if(row.length){putRow();}var nh=nd.offsetHeight;if(page&&acc+nh>MAXH){page=null;}if(!page){newPage();}pgrid.appendChild(nd);acc+=nh;}` +
            `else{row.push(nd);if(nd.offsetHeight>rowH){rowH=nd.offsetHeight;}if(row.length>=COLS){putRow();}}}` +
            `if(row.length){putRow();}` +
            `var meas=document.getElementById('measure');meas.parentNode.removeChild(meas);` +
            `window.focus();window.print();}` +
            `<\/script></body></html>`,
    );
    win.document.close();
    return true;
}

/**
 * Cetak tempelan AKL kemasan (tempel di kemasan/box kecil, bukan dus):
 *   Diimpor dan didistribusikan oleh :
 *   PT MITRA ASA PRATAMA
 *   Jakarta Timur 13330
 *   KEMENKES RI AKL <akl>
 *   ID/SN : <sn>
 * items = [{ kode, nama, akl, sn, qty }] — dikelompokkan per kode,
 * tiap kelompok diawali note merah "AKL UTK : KODE (Nama)".
 */
export function printAklKemasanLabels({ items = [], cols = 4, title = 'Tempelan AKL Kemasan', fillRows = false, fontPt = 0, company = '', address = '', importer = '' } = {}) {
    const toast = useToast();
    const safeCols = Math.min(10, Math.max(1, Math.floor(Number(cols) || 4)));

    const groups = [];
    const byCode = new Map();
    let total = 0;
    let truncated = false;
    for (const item of items ?? []) {
        const qty = Math.max(0, Math.floor(Number(item?.qty ?? 0)));
        if (qty === 0) {
            continue;
        }
        const kode = String(item?.kode ?? '').trim();
        const nama = String(item?.nama ?? '').trim();
        const akl = String(item?.akl ?? '').trim() || '-';
        const sn = String(item?.sn ?? '').trim() || '-';
        let g = byCode.get(kode);
        if (!g) {
            g = { kode, nama, labels: [] };
            byCode.set(kode, g);
            groups.push(g);
        }
        const count = fillRows ? Math.ceil(qty / safeCols) * safeCols : qty;
        for (let i = 0; i < count; i++) {
            if (total >= MAX_LABELS) {
                truncated = true;
                break;
            }
            g.labels.push({ akl, sn });
            total += 1;
        }
        if (truncated) {
            break;
        }
    }

    if (total === 0) {
        toast.warning('Tidak ada label untuk dicetak (qty nol).');
        return false;
    }
    if (truncated) {
        toast.warning(`Qty melebihi ${MAX_LABELS} label, hanya ${MAX_LABELS} pertama yang dicetak.`);
    }

    function kemasanFontPt(c) {
        return codeFontPt(c);
    }

    const fixedPt = Math.min(48, Math.max(8, Math.floor(Number(fontPt) || 0)));
    const basePt = fixedPt > 0 ? fixedPt : kemasanFontPt(safeCols);
    // Sama seperti label unit: semua baris satu ukuran font, tanpa bold, tanpa padding.
    const labelPt = Math.min(basePt, 20);
    const minPx = Math.max(10, Math.round(labelPt * 1.333 * 0.4));
    const safeCompany = String(company ?? '').trim() || 'PT MITRA ASA PRATAMA';
    const safeAddress = String(address ?? '').trim() || 'Jakarta Timur 13330';
    const safeImporter = String(importer ?? '').trim() || 'Diimpor dan didistribusikan oleh :';
    const groupsJson = JSON.stringify(groups).replace(/</g, '\\u003c');
    const companyJson = JSON.stringify(safeCompany).replace(/</g, '\\u003c');
    const addressJson = JSON.stringify(safeAddress).replace(/</g, '\\u003c');
    const importerJson = JSON.stringify(safeImporter).replace(/</g, '\\u003c');

    const win = window.open('', '_blank');
    if (!win) {
        toast.error('Popup diblokir browser. Izinkan popup untuk mencetak.');
        return false;
    }
    win.document.write(
        `<html><head><title>${escapeHtml(title)}</title><style>` +
            `@page{size:A4 portrait;margin:0}` +
            `*{box-sizing:border-box}` +
            `body{margin:0;font-family:Calibri,Arial,sans-serif;color:#000}` +
            `.page{width:210mm;min-height:296mm;padding:6mm;page-break-after:always}` +
            `.page:last-child{page-break-after:auto}` +
            `.grid{display:grid;grid-template-columns:repeat(${safeCols},1fr);gap:0}` +
            `.note{grid-column:1/-1;color:#c00;font-size:8pt;line-height:1;` +
            `padding:0.3mm 0;margin:0.3mm 0 0;page-break-inside:avoid;page-break-after:avoid;word-break:break-word}` +
            `.label{border:0.4mm dashed #999;display:flex;flex-direction:column;align-items:flex-start;` +
            `justify-content:center;text-align:left;padding:0 0 0 1mm;` +
            `margin:0 -0.4mm -0.4mm 0;page-break-inside:avoid;overflow:hidden}` +
            `.imp,.comp,.addr,.akl,.sn{font-size:${labelPt}pt;line-height:1;word-break:break-word}` +
            `.akl{white-space:nowrap}` +
            `.sn{font-weight:800}` +
            `.comp,.addr{color:#222}` +
            `#measure{position:absolute;left:-9999mm;top:0;width:198mm;visibility:hidden}` +
            `@media print{.label{border-color:#333}.note{color:#c00}#measure{display:none}}` +
            `</style></head><body onload="doPrint()">` +
            `<div id="root"></div>` +
            `<div id="measure"><div class="grid" id="mgrid"></div></div>` +
            `<script>` +
            `var GROUPS=${groupsJson};var COLS=${safeCols};var MINPX=${minPx};var MAXH=Math.round(284*96/25.4);` +
            `var COMPANY=${companyJson};var ADDRESS=${addressJson};var IMPORTER=${importerJson};` +
            `function esc(s){return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}` +
            `function note(g){var t='AKL UTK : '+g.kode+(g.nama?' ('+g.nama+')':'');return '<div class="note" data-t="n">'+esc(t)+'</div>';}` +
            `function cell(l){return '<div class="label" data-t="l"><div class="imp">'+esc(IMPORTER)+'</div><div class="comp">'+esc(COMPANY)+'</div><div class="addr">'+esc(ADDRESS)+'</div><div class="akl">KEMENKES RI AKL '+esc(l.akl)+'</div><div class="sn">ID/SN : '+esc(l.sn)+'</div></div>';}` +
            `function fit(scope){var els=scope.querySelectorAll('.akl,.sn');for(var i=0;i<els.length;i++){var el=els[i];var size=parseFloat(window.getComputedStyle(el).fontSize);while(el.scrollWidth>el.clientWidth&&size>MINPX){size-=1;el.style.fontSize=size+'px';}}}` +
            `function doPrint(){` +
            `window.onafterprint=function(){window.close();};` +
            `var mgrid=document.getElementById('mgrid');` +
            `var html='';for(var gi=0;gi<GROUPS.length;gi++){html+=note(GROUPS[gi]);for(var li=0;li<GROUPS[gi].labels.length;li++){html+=cell(GROUPS[gi].labels[li]);}}` +
            `mgrid.innerHTML=html;fit(mgrid);` +
            `var root=document.getElementById('root');` +
            `var nodes=Array.prototype.slice.call(mgrid.children);` +
            `var page=null,pgrid=null,acc=0,row=[],rowH=0;` +
            `function newPage(){page=document.createElement('div');page.className='page';pgrid=document.createElement('div');pgrid.className='grid';page.appendChild(pgrid);root.appendChild(page);acc=0;}` +
            `function putRow(){if(page&&acc+rowH>MAXH){page=null;}if(!page){newPage();}for(var q=0;q<row.length;q++){pgrid.appendChild(row[q]);}acc+=rowH;row=[];rowH=0;}` +
            `for(var n=0;n<nodes.length;n++){var nd=nodes[n];` +
            `if(nd.getAttribute('data-t')==='n'){if(row.length){putRow();}var nh=nd.offsetHeight;if(page&&acc+nh>MAXH){page=null;}if(!page){newPage();}pgrid.appendChild(nd);acc+=nh;}` +
            `else{row.push(nd);if(nd.offsetHeight>rowH){rowH=nd.offsetHeight;}if(row.length>=COLS){putRow();}}}` +
            `if(row.length){putRow();}` +
            `var meas=document.getElementById('measure');meas.parentNode.removeChild(meas);` +
            `window.focus();window.print();}` +
            `<\/script></body></html>`,
    );
    win.document.close();
    return true;
}

/**
 * Cetak label tempelan: kode barang besar di atas, nama barang kecil di bawah.
 * Satu label per pcs sesuai qty. Jumlah kolom selembar bisa diatur.
 */
export function printProductLabels({ items = [], cols = 5, title = 'Label Produk', fillRows = false, codeOnly = false, fontPt = 0 } = {}) {
    const toast = useToast();
    const safeCols = Math.min(10, Math.max(1, Math.floor(Number(cols) || 5)));
    const { labels, truncated } = expandLabelItems(items, safeCols, fillRows);

    if (labels.length === 0) {
        toast.warning('Tidak ada label untuk dicetak (qty nol).', undefined, { id: 'copy', duration: 2000 });
        return false;
    }
    if (truncated) {
        toast.warning(`Qty melebihi ${MAX_LABELS} label, hanya ${MAX_LABELS} pertama yang dicetak.`);
    }

    // fontPt > 0 = ukuran font dikunci manual (tidak ikut jumlah kolom).
    const fixedPt = Math.min(48, Math.max(8, Math.floor(Number(fontPt) || 0)));
    const codePt = fixedPt > 0 ? fixedPt : codeFontPt(safeCols);
    const namePt = Math.max(7, Math.round(codePt * 0.55));
    // Tinggi label murni mengikuti isi (tanpa min-height) agar tidak ada
    // ruang kosong saat font mengecil oleh auto-fit / opsi Font.
    const labelPadMm = codeOnly ? 0.5 : 1;
    const minPx = Math.max(10, Math.round(codePt * 1.333 * 0.4));
    // JSON aman disisipkan ke <script> dokumen print.
    const dataJson = JSON.stringify(labels).replace(/</g, '\\u003c');
    const cellFn = codeOnly
        ? `function cell(l){return '<div class="label"><div class="code">'+(esc(l.code)||'-')+'</div></div>';}`
        : `function cell(l){return '<div class="label"><div class="code">'+(esc(l.code)||'-')+'</div><div class="name">'+esc(l.name)+'</div></div>';}`;

    const win = window.open('', '_blank');
    if (!win) {
        toast.error('Popup diblokir browser. Izinkan popup untuk mencetak.');
        return false;
    }
    // Paging manual: label diukur di penampung tersembunyi lalu dibagi per
    // halaman, sehingga tiap halaman punya margin identik (tidak mengandalkan
    // potongan grid otomatis browser yang menghilangkan margin halaman 2+).
    win.document.write(
        `<html><head><title>${escapeHtml(title)}</title><style>` +
            `@page{size:A4 portrait;margin:0}` +
            `*{box-sizing:border-box}` +
            `body{margin:0;font-family:Calibri,Arial,sans-serif}` +
            `.page{width:210mm;min-height:296mm;padding:6mm;page-break-after:always}` +
            `.page:last-child{page-break-after:auto}` +
            `.grid{display:grid;grid-template-columns:repeat(${safeCols},1fr);gap:0}` +
            `.label{border:0.4mm dashed #999;display:flex;flex-direction:column;align-items:center;` +
            `justify-content:center;text-align:center;padding:${labelPadMm}mm;` +
            `margin:0 -0.4mm -0.4mm 0;page-break-inside:avoid;overflow:hidden}` +
            `.code{font-weight:800;font-size:${codePt}pt;line-height:1.15;white-space:nowrap}` +
            `.name{font-size:${namePt}pt;line-height:1.25;word-break:break-word;margin-top:1mm;color:#222}` +
            `#measure{position:absolute;left:-9999mm;top:0;width:198mm;visibility:hidden}` +
            `@media print{.label{border-color:#333}#measure{display:none}}` +
            `</style></head><body onload="doPrint()">` +
            `<div id="root"></div>` +
            `<div id="measure"><div class="grid" id="mgrid"></div></div>` +
            `<script>` +
            `var DATA=${dataJson};var COLS=${safeCols};var MINPX=${minPx};var MAXH=Math.round(284*96/25.4);` +
            `function esc(s){return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}` +
            `${cellFn}` +
            `function fit(scope){var codes=scope.querySelectorAll('.code');for(var i=0;i<codes.length;i++){var el=codes[i];var size=parseFloat(window.getComputedStyle(el).fontSize);while(el.scrollWidth>el.clientWidth&&size>MINPX){size-=1;el.style.fontSize=size+'px';}}}` +
            `function doPrint(){` +
            `window.onafterprint=function(){window.close();};` +
            `var mgrid=document.getElementById('mgrid');` +
            `var html='';for(var i=0;i<DATA.length;i++){html+=cell(DATA[i]);}` +
            `mgrid.innerHTML=html;fit(mgrid);` +
            `var root=document.getElementById('root');` +
            `var nodes=Array.prototype.slice.call(mgrid.children);` +
            `var page=null,pgrid=null,acc=0;` +
            `for(var r=0;r<nodes.length;r+=COLS){` +
            `var rh=0;for(var k=r;k<Math.min(r+COLS,nodes.length);k++){rh=Math.max(rh,nodes[k].offsetHeight);}` +
            `if(page&&acc+rh>MAXH){page=null;}` +
            `if(!page){page=document.createElement('div');page.className='page';pgrid=document.createElement('div');pgrid.className='grid';page.appendChild(pgrid);root.appendChild(page);acc=0;}` +
            `for(var m=r;m<Math.min(r+COLS,nodes.length);m++){pgrid.appendChild(nodes[m]);}` +
            `acc+=rh;}` +
            `var meas=document.getElementById('measure');meas.parentNode.removeChild(meas);` +
            `window.focus();window.print();}` +
            `<\/script></body></html>`,
    );
    win.document.close();
    return true;
}
