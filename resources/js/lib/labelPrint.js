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

function labelMinHeightMm(cols) {
    if (cols <= 2) {
        return 24;
    }
    if (cols <= 4) {
        return 16;
    }
    return 12;
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
 * Cetak label tempelan: kode barang besar di atas, nama barang kecil di bawah.
 * Satu label per pcs sesuai qty. Jumlah kolom selembar bisa diatur.
 */
export function printProductLabels({ items = [], cols = 5, title = 'Label Produk', fillRows = false, codeOnly = false } = {}) {
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

    const codePt = codeFontPt(safeCols);
    const namePt = Math.max(7, Math.round(codePt * 0.55));
    // Mode kode saja: label dirampingkan (padding + tinggi minimum lebih kecil).
    const minH = codeOnly
        ? Math.max(7, Math.round(labelMinHeightMm(safeCols) * 0.6))
        : labelMinHeightMm(safeCols);
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
            `justify-content:center;text-align:center;padding:${labelPadMm}mm;min-height:${minH}mm;` +
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
