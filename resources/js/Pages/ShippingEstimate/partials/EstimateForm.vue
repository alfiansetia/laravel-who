<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Save, Trash2 } from '@lucide/vue';
import PageHeader from '@/components/PageHeader.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import EstimateSummary from './EstimateSummary.vue';
import { useToast } from '@/composables/useToast';
import web from '@/lib/web';
import { calcEstimate, dimOf, fmtID, num } from '@/lib/shipping';

const props = defineProps({
    title: { type: String, default: 'Shipping Estimate' },
    mode: { type: String, default: 'create' },
    record: { type: Object, default: null },
});

const toast = useToast();
const isEdit = computed(() => props.mode === 'edit');
const saving = ref(false);
const errors = ref({});

const typeOptions = [
    { value: 'DARAT', label: 'DARAT (Laut)' },
    { value: 'REG', label: 'REG (Udara)' },
];

const header = ref({
    no_so: props.record?.no_so ?? '',
    customer_name: props.record?.customer_name ?? '',
    shipping_address: props.record?.shipping_address ?? '',
});

function blankItem() {
    return { item_name: '', quantity: 1, total_price: '0' };
}
function blankPackage() {
    return { package_name: '', quantity: 1, weight_actual: '', dimension_length: '', dimension_width: '', dimension_height: '' };
}
function blankRate() {
    return { shipper_name: '', shipping_type: 'DARAT', rate_per_kg: '0', insurance_percentage: '0.20', packing_cost: '0', admin_fee: '0', ppn_percentage: '0', estimated_days: '' };
}

const items = ref(
    (props.record?.items ?? []).map((r) => ({ item_name: r.item_name ?? '', quantity: r.quantity ?? 1, total_price: fmtID(r.total_price) })),
);
const packages = ref(
    (props.record?.packages ?? []).map((r) => ({
        package_name: r.package_name ?? '',
        quantity: r.quantity ?? 1,
        weight_actual: r.weight_actual ?? '',
        dimension_length: r.dimension_length ?? '',
        dimension_width: r.dimension_width ?? '',
        dimension_height: r.dimension_height ?? '',
    })),
);
const rates = ref(
    (props.record?.rates ?? []).map((r) => ({
        shipper_name: r.shipper_name ?? '',
        shipping_type: r.shipping_type ?? 'DARAT',
        rate_per_kg: fmtID(r.rate_per_kg),
        insurance_percentage: r.insurance_percentage ?? '0',
        packing_cost: fmtID(r.packing_cost),
        admin_fee: fmtID(r.admin_fee),
        ppn_percentage: r.ppn_percentage ?? '0',
        estimated_days: r.estimated_days ?? '',
    })),
);
if (items.value.length === 0) {
    items.value.push(blankItem());
}
if (packages.value.length === 0) {
    packages.value.push(blankPackage());
}
if (rates.value.length === 0) {
    rates.value.push(blankRate());
}

const summary = computed(() => calcEstimate(items.value, packages.value, rates.value));

function pkgDims(p) {
    return { reg: dimOf(p, 6000), darat: dimOf(p, 4000) };
}

function addItem() {
    items.value.push(blankItem());
}
function removeItem(i) {
    items.value.splice(i, 1);
}
function addPackage() {
    packages.value.push(blankPackage());
}
function removePackage(i) {
    packages.value.splice(i, 1);
}
function addRate() {
    rates.value.push(blankRate());
}
function removeRate(i) {
    rates.value.splice(i, 1);
}

function buildPayload() {
    return {
        no_so: header.value.no_so,
        customer_name: header.value.customer_name,
        shipping_address: header.value.shipping_address,
        items: items.value
            .filter((r) => String(r.item_name ?? '').trim() !== '')
            .map((r) => ({ item_name: r.item_name, quantity: Number(num(r.quantity)) || 1, total_price: num(r.total_price) })),
        packages: packages.value
            .filter((r) => String(r.package_name ?? '').trim() !== '' || String(r.weight_actual ?? '').trim() !== '')
            .map((r, i) => ({
                package_name: r.package_name,
                quantity: Number(num(r.quantity)) || 1,
                weight_actual: num(r.weight_actual),
                dimension_length: num(r.dimension_length),
                dimension_width: num(r.dimension_width),
                dimension_height: num(r.dimension_height),
                package_number: i + 1,
            })),
        rates: rates.value
            .filter((r) => String(r.shipper_name ?? '').trim() !== '')
            .map((r) => ({
                shipper_name: r.shipper_name,
                shipping_type: r.shipping_type || 'DARAT',
                rate_per_kg: num(r.rate_per_kg),
                insurance_percentage: num(r.insurance_percentage),
                packing_cost: num(r.packing_cost),
                admin_fee: num(r.admin_fee),
                ppn_percentage: num(r.ppn_percentage),
                estimated_days: r.estimated_days,
            })),
    };
}

function goBack() {
    router.visit('/shipping-estimate');
}

async function save() {
    errors.value = {};
    if (!String(header.value.no_so ?? '').trim() || !String(header.value.customer_name ?? '').trim() || !String(header.value.shipping_address ?? '').trim()) {
        toast.error('No SO, customer, dan alamat wajib diisi.');
        return;
    }
    saving.value = true;
    try {
        if (isEdit.value) {
            await web.put(`/shipping-estimate/${props.record.id}`, buildPayload(), { block: true });
            toast.success('Data berhasil diupdate.');
            router.reload();
        } else {
            const res = await web.post('/shipping-estimate', buildPayload(), { block: true });
            const id = res.data?.data?.id ?? res.data?.id;
            toast.success('Data berhasil disimpan.');
            if (id) {
                router.visit(`/shipping-estimate/${id}/edit`);
            } else {
                router.visit('/shipping-estimate');
            }
        }
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data?.errors ?? {};
        }
        toast.error(e.response?.data?.message ?? 'Gagal menyimpan data.');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div class="space-y-4">
        <PageHeader :title="title" :description="isEdit ? `SO ${record?.no_so ?? ''}` : 'Buat estimasi ongkos kirim baru'">
            <template #actions>
                <Button variant="outline" size="sm" @click="goBack"><ArrowLeft /> Kembali</Button>
                <Button size="sm" :disabled="saving" @click="save"><Save /> Simpan</Button>
            </template>
        </PageHeader>

        <div class="grid gap-4 rounded-lg border bg-white p-4 sm:grid-cols-2">
            <FormField label="No SO" required :error="errors.no_so?.[0]">
                <Input v-model="header.no_so" placeholder="No SO..." />
            </FormField>
            <FormField label="Customer" required :error="errors.customer_name?.[0]">
                <Input v-model="header.customer_name" placeholder="Nama customer..." />
            </FormField>
            <FormField label="Alamat Kirim" required :error="errors.shipping_address?.[0]" class="sm:col-span-2">
                <Textarea v-model="header.shipping_address" rows="2" placeholder="Alamat pengiriman..." />
            </FormField>
        </div>

        <div class="rounded-lg border bg-white">
            <div class="flex flex-wrap items-center gap-2 border-b px-3 py-2">
                <span class="text-sm font-semibold">Items</span>
                <span class="ml-auto text-xs text-slate-500">{{ summary.totalQty }} pcs · Rp {{ fmtID(summary.totalInvoice) }}</span>
                <Button size="sm" @click="addItem"><Plus /> Baris</Button>
            </div>
            <div v-if="items.length === 0" class="p-6 text-center text-sm text-slate-500">Belum ada baris.</div>
            <div v-else class="divide-y">
                <div v-for="(r, i) in items" :key="i" class="grid items-end gap-2 px-3 py-2 sm:grid-cols-[1fr_100px_160px_40px]">
                    <FormField label="Nama Item">
                        <Input v-model="r.item_name" placeholder="Nama item..." />
                    </FormField>
                    <FormField label="Qty">
                        <Input v-model.number="r.quantity" type="number" min="1" />
                    </FormField>
                    <FormField label="Total Harga">
                        <Input v-model="r.total_price" inputmode="decimal" placeholder="0" />
                    </FormField>
                    <Button variant="outline" size="sm" title="Hapus baris" @click="removeItem(i)"><Trash2 /></Button>
                </div>
            </div>
        </div>

        <div class="rounded-lg border bg-white">
            <div class="flex flex-wrap items-center gap-2 border-b px-3 py-2">
                <span class="text-sm font-semibold">Packages</span>
                <span class="ml-auto text-xs text-slate-500">{{ summary.totalKoli }} koli · {{ fmtID(summary.totalWeight, 2) }} kg</span>
                <Button size="sm" @click="addPackage"><Plus /> Koli</Button>
            </div>
            <div v-if="packages.length === 0" class="p-6 text-center text-sm text-slate-500">Belum ada koli.</div>
            <div v-else class="divide-y">
                <div v-for="(p, i) in packages" :key="i" class="space-y-2 px-3 py-2">
                    <div class="grid items-end gap-2 sm:grid-cols-[1fr_90px_110px_40px]">
                        <FormField label="Nama Koli / Isi">
                            <Input v-model="p.package_name" placeholder="Nama koli..." />
                        </FormField>
                        <FormField label="Qty">
                            <Input v-model.number="p.quantity" type="number" min="1" />
                        </FormField>
                        <FormField label="Berat (kg)">
                            <Input v-model="p.weight_actual" type="number" step="0.01" min="0" />
                        </FormField>
                        <Button variant="outline" size="sm" title="Hapus koli" @click="removePackage(i)"><Trash2 /></Button>
                    </div>
                    <div class="grid items-end gap-2 sm:grid-cols-[110px_110px_110px_1fr_1fr]">
                        <FormField label="P (cm)">
                            <Input v-model="p.dimension_length" type="number" step="0.01" min="0" />
                        </FormField>
                        <FormField label="L (cm)">
                            <Input v-model="p.dimension_width" type="number" step="0.01" min="0" />
                        </FormField>
                        <FormField label="T (cm)">
                            <Input v-model="p.dimension_height" type="number" step="0.01" min="0" />
                        </FormField>
                        <FormField label="Dim REG (kg)">
                            <Input :model-value="fmtID(pkgDims(p).reg, 2)" disabled />
                        </FormField>
                        <FormField label="Dim DARAT (kg)">
                            <Input :model-value="fmtID(pkgDims(p).darat, 2)" disabled />
                        </FormField>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-lg border bg-white">
            <div class="flex flex-wrap items-center gap-2 border-b px-3 py-2">
                <span class="text-sm font-semibold">Rates</span>
                <span class="ml-auto text-xs text-slate-500">{{ rates.length }} shipper</span>
                <Button size="sm" @click="addRate"><Plus /> Rate</Button>
            </div>
            <div v-if="rates.length === 0" class="p-6 text-center text-sm text-slate-500">Belum ada rate.</div>
            <div v-else class="divide-y">
                <div v-for="(r, i) in rates" :key="i" class="space-y-2 px-3 py-2">
                    <div class="grid items-end gap-2 sm:grid-cols-[1fr_150px_40px]">
                        <FormField label="Shipper">
                            <Input v-model="r.shipper_name" placeholder="TIKI, JNE, dll..." />
                        </FormField>
                        <FormField label="Tipe">
                            <SearchableSelect v-model="r.shipping_type" :options="typeOptions" :clearable="false" placeholder="Tipe..." />
                        </FormField>
                        <Button variant="outline" size="sm" title="Hapus rate" @click="removeRate(i)"><Trash2 /></Button>
                    </div>
                    <div class="grid items-end gap-2 sm:grid-cols-3 lg:grid-cols-5">
                        <FormField label="Tarif / kg">
                            <Input v-model="r.rate_per_kg" inputmode="decimal" placeholder="0" />
                        </FormField>
                        <FormField label="Asuransi %">
                            <Input v-model="r.insurance_percentage" type="number" step="0.01" min="0" />
                        </FormField>
                        <FormField label="P. Kayu">
                            <Input v-model="r.packing_cost" inputmode="decimal" placeholder="0" />
                        </FormField>
                        <FormField label="Admin">
                            <Input v-model="r.admin_fee" inputmode="decimal" placeholder="0" />
                        </FormField>
                        <FormField label="PPN %">
                            <Input v-model="r.ppn_percentage" type="number" step="0.01" min="0" />
                        </FormField>
                    </div>
                    <div class="grid items-end gap-2 sm:grid-cols-2">
                        <FormField label="Estimasi Hari">
                            <Input v-model="r.estimated_days" placeholder="2-3" />
                        </FormField>
                    </div>
                </div>
            </div>
        </div>

        <EstimateSummary :summary="summary" />
    </div>
</template>
