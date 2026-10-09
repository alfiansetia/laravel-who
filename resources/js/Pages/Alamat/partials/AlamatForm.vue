<script setup>
import { ref } from 'vue';
import { Search } from '@lucide/vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { useBlock } from '@/composables/useBlock';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import web from '@/lib/web';
import { formatNumber } from '@/lib/format';
import { odooName } from '@/lib/odoo';

const props = defineProps({
    mode: { type: String, default: 'create' },
    record: { type: Object, default: null },
});

const emit = defineEmits(['saved']);

const toast = useToast();
const { withBlock } = useBlock();
const isEdit = props.mode === 'edit';

function flag(v) {
    return v === 'yes';
}

const form = ref({
    tujuan: props.record?.tujuan ?? '',
    alamat: props.record?.alamat ?? '',
    ekspedisi: props.record?.ekspedisi ?? '',
    koli: props.record?.koli ?? 1,
    up: props.record?.up ?? '',
    tlp: props.record?.tlp ?? '',
    do: props.record?.do ?? '',
    epur: props.record?.epur ?? '',
    untuk: props.record?.untuk ?? '',
    note: props.record?.note ?? '',
    is_do: flag(props.record?.is_do),
    is_pk: flag(props.record?.is_pk),
    is_banting: flag(props.record?.is_banting),
    is_last_koli: flag(props.record?.is_last_koli),
    is_asuransi: props.record ? flag(props.record?.is_asuransi) : true,
});
const epurKind = ref(
    props.record?.epur === 'Pembelian Offline' ? 'PO' : props.record?.epur === 'Pembelian Reguler' ? 'REG' : 'NULL',
);
const errors = ref({});
const noteWhHtml = ref('');
const nilaiDisp = ref(fmtThousand(String(props.record?.nilai ?? '').replace(/[,.]/g, '')));

function fmtThousand(digits) {
    const d = String(digits ?? '').replace(/\D/g, '');
    if (!d) {
        return '';
    }
    return formatNumber(d);
}

function onNilaiInput(e) {
    nilaiDisp.value = fmtThousand(e.target.value);
}

const doSearchText = ref(props.record?.do ? String(props.record.do) : 'CENT/OUT/');
const doOptions = ref([]);
const doLoading = ref(false);
const selectedDoId = ref('');

function doLabel(d) {
    let name = d.name ?? d.do ?? String(d.id);
    if (Array.isArray(d.group_id)) {
        name += ` (${d.group_id[1]})`;
    }
    if (Array.isArray(d.partner_id)) {
        name += ` ${d.partner_id[1]}`;
    }
    return name;
}

function odooText(v) {
    if (v === false || v == null) {
        return '';
    }
    return Array.isArray(v) ? (v[1] ?? '') : String(v);
}

async function fetchDoList() {
    const keyword = doSearchText.value.trim();
    if (!keyword) {
        toast.error('Isi kata kunci DO dulu.');
        return;
    }
    doLoading.value = true;
    await withBlock(async () => {
        try {
            const res = await api.get('/do', { params: { search: keyword }, block: true, silent: true });
            const body = res.data?.data ?? res.data ?? [];
            const list = Array.isArray(body) ? body : (body.data ?? []);
            doOptions.value = list.map((d) => ({ label: doLabel(d), value: String(d.id) }));
            selectedDoId.value = '';
            if (doOptions.value.length === 0) {
                toast.warning('DO tidak ditemukan.');
            } else {
                toast.success(`${doOptions.value.length} DO ditemukan, pilih salah satu.`);
            }
        } catch (e) {
            doOptions.value = [];
            toast.error(e.response?.data?.message ?? 'Gagal mengambil data DO.');
        } finally {
            doLoading.value = false;
        }
    });
}

async function pickDo(val) {
    selectedDoId.value = val;
    if (!val) {
        return;
    }
    await withBlock(async () => {
        try {
            const res = await api.get(`/do/${val}`, { block: true, silent: true });
            const d = res.data?.data ?? res.data;
            let alamat = odooText(d.partner_address);
            ['partner_address2', 'partner_address3', 'partner_address4'].forEach((k) => {
                const t = odooText(d[k]);
                if (t) {
                    alamat += `\n${t}`;
                }
            });
            form.value.do = odooText(d.name);
            form.value.up = odooText(d.delivery_manual) === '-' ? '' : odooText(d.delivery_manual);
            form.value.tujuan = odooText(d.partner_id);
            form.value.ekspedisi = odooText(d.ekspedisi_id);
            form.value.alamat = alamat;
            form.value.epur = odooText(d.no_aks) === '-' ? '' : odooText(d.no_aks);
            form.value.tlp = '';
            form.value.untuk = '';
            epurKind.value = 'NULL';
            const noteToWh = odooText(d.note_to_wh);
            noteWhHtml.value = noteToWh ? String(noteToWh).replace(/\n/g, '<br>') : '';
            toast.success('Data DO dimuat.');
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal memuat DO.');
        }
    });
}

function onEpurKindChange() {
    if (epurKind.value === 'PO') {
        form.value.epur = 'Pembelian Offline';
    } else if (epurKind.value === 'REG') {
        form.value.epur = 'Pembelian Reguler';
    } else {
        form.value.epur = '';
    }
}

function buildPayload() {
    const yn = (v) => (v ? 'yes' : 'no');
    return {
        tujuan: form.value.tujuan,
        alamat: form.value.alamat,
        ekspedisi: form.value.ekspedisi,
        koli: form.value.koli,
        up: form.value.up,
        tlp: form.value.tlp,
        do: form.value.do,
        epur: form.value.epur,
        untuk: form.value.untuk,
        nilai: String(nilaiDisp.value ?? '').replace(/\D/g, ''),
        note: form.value.note,
        is_do: yn(form.value.is_do),
        is_pk: yn(form.value.is_pk),
        is_banting: yn(form.value.is_banting),
        is_last_koli: yn(form.value.is_last_koli),
        is_asuransi: yn(form.value.is_asuransi),
    };
}

async function save() {
    errors.value = {};
    if (!String(form.value.tujuan ?? '').trim() || !String(form.value.alamat ?? '').trim() || !String(form.value.do ?? '').trim()) {
        toast.error('Tujuan, alamat, dan DO wajib diisi.');
        return null;
    }
    let id = null;
    await withBlock(async () => {
        try {
            if (isEdit.value) {
                await web.put(`/alamats/${props.record.id}`, buildPayload(), { block: true });
                toast.success('Alamat disimpan.');
                id = props.record.id;
                emit('saved', id);
            } else {
                const res = await web.post('/alamats', buildPayload(), { block: true });
                const created = res.data?.data ?? res.data;
                toast.success('Alamat dibuat.');
                id = created?.id ?? null;
                emit('saved', id);
            }
        } catch (e) {
            if (e.response?.status === 422) {
                errors.value = e.response.data?.errors ?? {};
            }
        }
    });
    return id;
}

defineExpose({ form, save });
</script>

<template>
    <div class="rounded-lg border bg-white p-4">
        <div class="grid gap-4 sm:grid-cols-2">
            <FormField label="Cari DO (Odoo)" class="sm:col-span-2">
                <div class="flex flex-wrap gap-2">
                    <Input v-model="doSearchText" placeholder="Cari No DO..." class="max-w-xs" @keyup.enter="fetchDoList" />
                    <Button variant="outline" size="sm" :disabled="doLoading" @click="fetchDoList"><Search /> Ambil Data</Button>
                    <SearchableSelect :model-value="selectedDoId" :options="doOptions" :loading="doLoading" placeholder="Pilih Hasil Pencarian DO" class="min-w-52 flex-1" @update:model-value="pickDo" />
                </div>
            </FormField>
            <FormField label="Tujuan" required :error="errors.tujuan?.[0]">
                <Textarea v-model="form.tujuan" rows="3" placeholder="Tujuan" />
            </FormField>
            <FormField label="Alamat" required :error="errors.alamat?.[0]">
                <Textarea v-model="form.alamat" rows="3" placeholder="Alamat" />
            </FormField>
            <FormField label="Ekspedisi">
                <Input v-model="form.ekspedisi" placeholder="Ekspedisi" />
            </FormField>
            <FormField label="Koli">
                <div class="flex items-center gap-3">
                    <Input v-model.number="form.koli" type="number" min="1" class="max-w-32" />
                    <label class="flex items-center gap-1 text-sm"><input v-model="form.is_last_koli" type="checkbox" /> Koli terakhir</label>
                </div>
            </FormField>
            <FormField label="UP">
                <Input v-model="form.up" placeholder="UP" />
            </FormField>
            <FormField label="Tlp">
                <Input v-model="form.tlp" placeholder="Tlp" />
            </FormField>
            <FormField label="No DO" required :error="errors.do?.[0]">
                <Input v-model="form.do" placeholder="No DO" />
            </FormField>
            <FormField label="Epurchasing">
                <Input v-model="form.epur" placeholder="Epurchasing" />
                <div class="mt-2 flex gap-3 text-sm">
                    <label class="flex items-center gap-1"><input v-model="epurKind" type="radio" value="PO" @change="onEpurKindChange" /> PO</label>
                    <label class="flex items-center gap-1"><input v-model="epurKind" type="radio" value="REG" @change="onEpurKindChange" /> REG</label>
                    <label class="flex items-center gap-1"><input v-model="epurKind" type="radio" value="NULL" @change="onEpurKindChange" /> NULL</label>
                </div>
            </FormField>
            <FormField label="Untuk">
                <Input v-model="form.untuk" placeholder="Untuk" />
            </FormField>
            <FormField label="Nilai (Rp)">
                <Input :model-value="nilaiDisp" inputmode="numeric" placeholder="0" @input="onNilaiInput" />
            </FormField>
            <FormField label="Note">
                <Textarea v-model="form.note" rows="3" maxlength="250" placeholder="Note" />
            </FormField>
            <FormField label="Note WH">
                <div class="min-h-24 rounded border bg-slate-50 p-2 text-sm font-bold text-red-600" v-html="noteWhHtml || ''" />
            </FormField>
            <div class="flex flex-wrap gap-4 text-sm sm:col-span-2">
                <label class="flex items-center gap-1"><input v-model="form.is_do" type="checkbox" /> Surat Jalan / DO</label>
                <label class="flex items-center gap-1"><input v-model="form.is_pk" type="checkbox" /> Packing Kayu</label>
                <label class="flex items-center gap-1"><input v-model="form.is_banting" type="checkbox" /> Jangan Dibanting</label>
                <label class="flex items-center gap-1"><input v-model="form.is_asuransi" type="checkbox" /> Asuransi</label>
            </div>
        </div>
    </div>
</template>
