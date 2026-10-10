/** Kalkulasi Shipping Estimate — paritas Blade (_scripts) + Model server. */

export function num(value) {
    if (typeof value === 'number') {
        return Number.isNaN(value) ? 0 : value;
    }
    const s = String(value ?? '').trim();
    if (!s) {
        return 0;
    }
    const parsed = Number(s.replace(/\./g, '').replace(',', '.'));
    return Number.isNaN(parsed) ? 0 : parsed;
}

export function fmtID(value, decimals = 0) {
    const n = Number(value ?? 0);
    if (Number.isNaN(n)) {
        return decimals > 0 ? (0).toFixed(decimals).replace('.', ',') : '0';
    }
    return n.toLocaleString('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
}

export function dimOf(pkg, divisor) {
    const l = num(pkg.dimension_length);
    const w = num(pkg.dimension_width);
    const h = num(pkg.dimension_height);
    const q = num(pkg.quantity) || 0;
    if (l <= 0 || w <= 0 || h <= 0 || q <= 0) {
        return 0;
    }
    return Math.ceil((l * w * h) / divisor) * q;
}

export function calcEstimate(items = [], packages = [], rates = []) {
    const totalQty = items.reduce((s, r) => s + (num(r.quantity) || 0), 0);
    const totalInvoice = items.reduce((s, r) => s + num(r.total_price), 0);
    const totalKoli = packages.reduce((s, r) => s + (num(r.quantity) || 0), 0);
    const totalWeight = packages.reduce((s, r) => s + num(r.weight_actual) * (num(r.quantity) || 0), 0);
    const totalDimReg = packages.reduce((s, r) => s + dimOf(r, 6000), 0);
    const totalDimDarat = packages.reduce((s, r) => s + dimOf(r, 4000), 0);

    const rateRows = rates.map((r) => {
        const divisor = r.shipping_type === 'REG' ? 6000 : 4000;
        const dim = divisor === 6000 ? totalDimReg : totalDimDarat;
        const charged = Math.max(totalWeight, dim);
        const rate = num(r.rate_per_kg);
        const shipping = charged * rate;
        const insurance = (totalInvoice * num(r.insurance_percentage)) / 100;
        const packing = num(r.packing_cost);
        const admin = num(r.admin_fee);
        const ppn = (shipping * num(r.ppn_percentage)) / 100;
        const subtotal = shipping + insurance + packing + admin;
        const total = subtotal + ppn;
        return { ...r, divisor, charged_weight: charged, shipping_cost: shipping, insurance_cost: insurance, subtotal, ppn_cost: ppn, total_cost: total };
    });

    return { totalQty, totalInvoice, totalKoli, totalWeight, totalDimReg, totalDimDarat, rateRows };
}
