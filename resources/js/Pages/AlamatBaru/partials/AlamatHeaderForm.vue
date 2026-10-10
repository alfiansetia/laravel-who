<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, Save, Search } from '@lucide/vue';
import PageHeader from '@/components/PageHeader.vue';
import FormField from '@/components/FormField.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { useBlock } from '@/composables/useBlock';
import { useToast } from '@/composables/useToast';
import api from '@/lib/axios';
import web from '@/lib/web';
import { odooName } from '@/lib/odoo';

const props = defineProps({
    title: { type: String, default: 'Alamat Baru' },
    mode: { type: String, default: 'create' },
    record: { type: Object, default: null },
});

const emit = defineEmits(['saved']);

const toast = useToast();
const { withBlock } = useBlock();
const isEdit = props.mode === 'edit';

const form = ref({
    tujuan: props.record?.tujuan ?? '',
    alamat: props.record?.alamat ?? '',
    ekspedisi: props.record?.ekspedisi ?? '',
    total_koli: props.record?.total_koli ?? 1,
    up: props.record?.up ?? '',
    tlp: props.record?.tlp ?? '',
    do: props.record?.do ?? '',
    epur: props.record?.epur ?? '',
    untuk: props.record?.untuk ?? '',
    note: props.record?.note ?? '',
});
const epurKind = ref(
    props.record?.epur === 'Pembelian Offline' ? 'PO' : props.record?.epur === 'Pembelian Reguler' ? 'REG' : 'NULL',
);
const errors = ref({});
const noteWhHtml = ref('');

const doSearchText = ref(props.record?.do ?? 'CENT/OUT/');
const doOptions = ref([]);
const doLoading = ref(false);
const selectedDoId = ref('');

function doLabel(d) {
    let name = d.name ?? d.do ?? String(d.id);
    if (d.group_id && d.group_id !== false) {
        name += ` (${d.group_id[1]})`;
    }
    if (d.partner_id && d.partner_id !== false) {
        name += ` ${d.partner_id[1]}`;
    }
    return name;
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

function wrapAddress(text, firstLimit = 45, otherLimit = 65) {
    if (!text || !String(text).trim()) {
        return '';
    }
    const lines = String(text).split('\n');
    const result = [];
    lines.forEach((line) => {
        let limit = result.length === 0 ? firstLimit : otherLimit;
        line = line.trim();
        if (line.length <= limit) {
            result.push(line);
            return;
        }
        while (line.length > limit) {
            const slice = line.slice(0, limit);
            let lastSpace = slice.lastIndexOf(' ');
            if (lastSpace === -1) {
                lastSpace = limit;
            }
            result.push(line.slice(0, lastSpace).trim());
            line = line.slice(lastSpace).trim();
            limit = otherLimit;
        }
        if (line.length) {
            result.push(line);
        }
    });
    return result.join('\n');
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
            let tujuan = '';
            let ekspedisi = '';
            let up = '';
            let name = '';
            let epur = '';
            let noteToWh = '';
            if (d.partner_id !== false && d.partner_id != null) {
                tujuan = d.partner_id[1] ?? '';
            }
            if (d.ekspedisi_id !== false && d.ekspedisi_id != null) {
                ekspedisi = d.ekspedisi_id[1] ?? '';
            }
            if (d.delivery_manual !== false && d.delivery_manual != null) {
                up = d.delivery_manual;
                if (up === '-') {
                    up = '';
                }
            }
            if (d.name !== false && d.name != null) {
                name = d.name;
            }
            if (d.no_aks !== false && d.no_aks != null) {
                epur = d.no_aks;
                if (epur === '-') {
                    epur = '';
                }
            }
            let alamat = d.partner_address ?? '';
            if (d.partner_address2 !== false && d.partner_address2 != null) {
                alamat += `\n${d.partner_address2}`;
            }
            if (d.partner_address3 !== false && d.partner_address3 != null) {
                alamat += `\n${d.partner_address3}`;
            }
            if (d.partner_address4 !== false && d.partner_address4 != null) {
                alamat += `\n${d.partner_address4}`;
            }
            if (d.note_to_wh !== false && d.note_to_wh != null) {
                noteToWh = String(d.note_to_wh).replace(/\n/g, '<br>');
            }
            form.value.do = name;
            form.value.up = up;
            form.value.alamat = wrapAddress(alamat);
            form.value.tujuan = tujuan;
            form.value.ekspedisi = ekspedisi;
            form.value.epur = epur;
            form.value.tlp = '';
            form.value.untuk = '';
            noteWhHtml.value = noteToWh;
            toast.success('Data DO dimuat.');
        } catch (e) {
            toast.error(e.response?.data?.message ?? 'Gagal memuat DO.');
        }
    });
}

function setEpur(val) {
    form.value.epur = val;
}

function onEpurKindChange() {
    if (epurKind.value === 'PO') {
        setEpur('Pembelian Offline');
    } else if (epurKind.value === 'REG') {
        setEpur('Pembelian Reguler');
    } else {
        setEpur('');
    }
}

async function save() {
    errors.value = {};
    if (!form.value.tujuan.trim() || !form.value.alamat.trim() || !form.value.do.trim()) {
        toast.error('Tujuan, alamat, dan DO wajib diisi.');
        return;
    }
    const payload = {
        tujuan: form.value.tujuan,
        alamat: form.value.alamat,
        ekspedisi: form.value.ekspedisi,
        total_koli: form.value.total_koli,
        up: form.value.up,
        tlp: form.value.tlp,
        do: form.value.do,
        epur: form.value.epur,
        untuk: form.value.untuk,
        note: form.value.note,
    };
    await withBlock(async () => {
        try {
            if (isEdit.value) {
                await web.put(`/alamat-baru/${props.record.id}`, payload, { block: true });
                toast.success('Alamat disimpan.');
                emit('saved', props.record.id);
            } else {
                const res = await web.post('/alamat-baru', payload, { block: true });
                const created = res.data?.data ?? res.data;
                toast.success('Alamat dibuat.');
                emit('saved', created?.id ?? null);
            }
        } catch (e) {
            if (e.response?.status === 422) {
                errors.value = e.response.data?.errors ?? {};
            }
        }
    });
}

function goBack() {
    router.visit('/alamat-baru');
}

defineExpose({ form, save });
</script>

<template>
    <div class="space-y-4">
        <PageHeader :title="title">
            <template #actions>
                <Button variant="outline" size="sm" @click="goBack"><ArrowLeft /> Kembali</Button>
                <Button size="sm" @click="save"><Save /> Simpan{{ isEdit ? '' : ' Data' }}</Button>
            </template>
        </PageHeader>
        <div class="rounded-lg border bg-white p-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <FormField label="Cari DO (Odoo)" class="sm:col-span-2">
                    <div class="flex flex-wrap gap-2">
                        <Input v-model="doSearchText" placeholder="CARI No DO..." class="max-w-xs" @keyup.enter="fetchDoList" />
                        <Button variant="outline" size="sm" :disabled="doLoading" @click="fetchDoList"><Search /> Ambil Data</Button>
                        <SearchableSelect :model-value="selectedDoId" :options="doOptions" :loading="doLoading" placeholder="Pilih Hasil Pencarian DO" class="min-w-52 flex-1" @update:model-value="pickDo" />
                    </div>
                </FormField>
                <FormField label="Tujuan" required :error="errors.tujuan?.[0]">
                    <Textarea v-model="form.tujuan" rows="4" placeholder="Tujuan" />
                </FormField>
                <FormField label="Alamat" required :error="errors.alamat?.[0]">
                    <Textarea v-model="form.alamat" rows="4" placeholder="Alamat" />
                </FormField>
                <FormField label="Ekspedisi">
                    <Input v-model="form.ekspedisi" placeholder="Ekspedisi" />
                </FormField>
                <FormField label="Total Koli (Manual)">
                    <Input v-model="form.total_koli" type="number" min="0" placeholder="Total Koli" />
                </FormField>
                <FormField label="UP">
                    <Input v-model="form.up" placeholder="UP" />
                </FormField>
                <FormField label="Tlp">
                    <Input v-model="form.tlp" placeholder="Tlp" />
                </FormField>
                <FormField label="No DO" required class="sm:col-span-2" :error="errors.do?.[0]">
                    <Input v-model="form.do" placeholder="No DO" />
                </FormField>
                <FormField>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-sm font-medium">Epurchasing</span>
                        <div class="flex gap-2 text-xs text-muted-foreground">
                            <label class="flex cursor-pointer items-center gap-1"><input v-model="epurKind" type="radio" value="PO" @change="onEpurKindChange" /> PO</label>
                            <label class="flex cursor-pointer items-center gap-1"><input v-model="epurKind" type="radio" value="REG" @change="onEpurKindChange" /> REG</label>
                            <label class="flex cursor-pointer items-center gap-1"><input v-model="epurKind" type="radio" value="NULL" @change="onEpurKindChange" /> NULL</label>
                        </div>
                    </div>
                    <Input v-model="form.epur" placeholder="Epurchasing" />
                </FormField>
                <FormField label="Untuk">
                    <Input v-model="form.untuk" placeholder="Untuk" />
                </FormField>
                <FormField label="Note">
                    <Textarea v-model="form.note" rows="4" maxlength="250" placeholder="note" />
                </FormField>
                <FormField label="Note WH">
                    <div class="min-h-24 rounded border bg-slate-50 p-2 text-sm font-bold text-red-600" v-html="noteWhHtml || ''" />
                </FormField>
            </div>
        </div>
    </div>
</template>
