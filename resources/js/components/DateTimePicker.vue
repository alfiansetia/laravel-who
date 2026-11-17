<script setup>
import { computed, nextTick, onBeforeUnmount, ref } from 'vue';
import { Calendar as CalendarIcon, ChevronLeft, ChevronRight, Minus, Plus, X } from '@lucide/vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';

// Pengganti <input type="datetime-local">: trigger tombol + panel kalender + jam.
// v-model format `YYYY-MM-DDTHH:mm` (sama seperti datetime-local) + emit `change`
// tiap aksi user, jadi bisa langsung menggantikan input lama tanpa ubah logika.
const props = defineProps({
    placeholder: { type: String, default: 'Pilih tanggal...' },
    disabled: { type: Boolean, default: false },
    clearable: { type: Boolean, default: true },
    minuteStep: { type: Number, default: 5 },
});

const model = defineModel({ type: String, default: '' });
const emit = defineEmits(['change']);

const MONTHS = [
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember',
];
const SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
const DAYS = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

const pad = (n) => String(n).padStart(2, '0');

const parsed = computed(() => {
    const m = String(model.value ?? '').match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})$/);
    if (!m) {
        return null;
    }
    return { y: +m[1], mo: +m[2], d: +m[3], h: +m[4], mi: +m[5] };
});

const display = computed(() => {
    const p = parsed.value;
    if (!p) {
        return '';
    }
    return `${p.d} ${SHORT[p.mo - 1]} ${p.y}, ${pad(p.h)}:${pad(p.mi)}`;
});

const today = new Date();
const view = ref({ y: today.getFullYear(), m: today.getMonth() });

const cells = computed(() => {
    const { y, m } = view.value;
    const offset = (new Date(y, m, 1).getDay() + 6) % 7;
    const dim = new Date(y, m + 1, 0).getDate();
    const prevDim = new Date(y, m, 0).getDate();
    const out = [];
    for (let i = 0; i < 42; i++) {
        const n = i - offset + 1;
        if (n < 1) {
            const d = new Date(y, m - 1, prevDim + n);
            out.push({ y: d.getFullYear(), mo: d.getMonth() + 1, d: d.getDate(), inMonth: false });
        } else if (n > dim) {
            const d = new Date(y, m + 1, n - dim);
            out.push({ y: d.getFullYear(), mo: d.getMonth() + 1, d: d.getDate(), inMonth: false });
        } else {
            out.push({ y, mo: m + 1, d: n, inMonth: true });
        }
    }
    return out;
});

function isSelected(c) {
    const p = parsed.value;
    return !!p && p.y === c.y && p.mo === c.mo && p.d === c.d;
}

function isToday(c) {
    return c.y === today.getFullYear() && c.mo === today.getMonth() + 1 && c.d === today.getDate();
}

function stamp(y, mo, d, h, mi) {
    return `${y}-${pad(mo)}-${pad(d)}T${pad(h)}:${pad(mi)}`;
}

function commit(next) {
    model.value = next;
    emit('change', next);
}

function selectDay(c) {
    const p = parsed.value;
    view.value = { y: c.y, m: c.mo - 1 };
    commit(stamp(c.y, c.mo, c.d, p?.h ?? 0, p?.mi ?? 0));
}

function moveMonth(delta) {
    const d = new Date(view.value.y, view.value.m + delta, 1);
    view.value = { y: d.getFullYear(), m: d.getMonth() };
}

function stepHour(delta) {
    const p = parsed.value ?? { y: today.getFullYear(), mo: today.getMonth() + 1, d: today.getDate(), h: 0, mi: 0 };
    commit(stamp(p.y, p.mo, p.d, (p.h + delta + 24) % 24, p.mi));
}

function stepMinute(delta) {
    const p = parsed.value ?? { y: today.getFullYear(), mo: today.getMonth() + 1, d: today.getDate(), h: 0, mi: 0 };
    commit(stamp(p.y, p.mo, p.d, p.h, (p.mi + delta * props.minuteStep + 60) % 60));
}

function commitHour(v) {
    const n = Math.max(0, Math.min(23, parseInt(v, 10) || 0));
    const p = parsed.value ?? { y: today.getFullYear(), mo: today.getMonth() + 1, d: today.getDate(), h: 0, mi: 0 };
    commit(stamp(p.y, p.mo, p.d, n, p.mi));
}

function commitMinute(v) {
    const n = Math.max(0, Math.min(59, parseInt(v, 10) || 0));
    const p = parsed.value ?? { y: today.getFullYear(), mo: today.getMonth() + 1, d: today.getDate(), h: 0, mi: 0 };
    commit(stamp(p.y, p.mo, p.d, p.h, n));
}

function goToday() {
    const p = parsed.value;
    const t = new Date();
    view.value = { y: t.getFullYear(), m: t.getMonth() };
    commit(stamp(t.getFullYear(), t.getMonth() + 1, t.getDate(), p?.h ?? 0, p?.mi ?? 0));
}

function clear() {
    commit('');
    open.value = false;
}

const open = ref(false);
const triggerEl = ref(null);
const panelEl = ref(null);
const panelStyle = ref({});

function placePanel() {
    const el = triggerEl.value;
    if (!el) {
        return;
    }
    const rect = el.getBoundingClientRect();
    const panelHeight = 400;
    const spaceBelow = window.innerHeight - rect.bottom;
    const above = spaceBelow < panelHeight + 8 && rect.top > panelHeight + 8;
    panelStyle.value = {
        position: 'fixed',
        left: `${Math.max(8, Math.min(rect.left, window.innerWidth - 336))}px`,
        width: '320px',
        zIndex: 200,
        ...(above
            ? { bottom: `${Math.max(8, window.innerHeight - rect.top + 4)}px` }
            : { top: `${rect.bottom + 4}px` }),
    };
}

async function toggle() {
    if (props.disabled) {
        return;
    }
    open.value = !open.value;
    if (open.value) {
        const p = parsed.value;
        view.value = p ? { y: p.y, m: p.mo - 1 } : { y: today.getFullYear(), m: today.getMonth() };
        await nextTick();
        placePanel();
        bind();
    } else {
        unbind();
    }
}

function onOutside(e) {
    if (!open.value) {
        return;
    }
    const t = e.target;
    if (panelEl.value?.contains(t) || triggerEl.value?.contains(t)) {
        return;
    }
    open.value = false;
    unbind();
}

function onKey(e) {
    if (e.key === 'Escape' && open.value) {
        open.value = false;
        unbind();
    }
}

let bound = false;
function bind() {
    if (bound) {
        return;
    }
    bound = true;
    document.addEventListener('pointerdown', onOutside, true);
    document.addEventListener('keydown', onKey);
}

function unbind() {
    if (!bound) {
        return;
    }
    bound = false;
    document.removeEventListener('pointerdown', onOutside, true);
    document.removeEventListener('keydown', onKey);
}

onBeforeUnmount(unbind);
</script>

<template>
    <div ref="triggerEl">
        <Button
            type="button"
            variant="outline"
            class="w-full justify-start font-normal"
            :disabled="disabled"
            @click="toggle"
        >
            <CalendarIcon class="mr-2 size-4 shrink-0 opacity-60" />
            <span :class="['flex-1 truncate text-left', display ? '' : 'text-muted-foreground']">
                {{ display || placeholder }}
            </span>
            <span
                v-if="clearable && display"
                role="button"
                tabindex="0"
                aria-label="Hapus tanggal"
                class="rounded p-0.5 hover:bg-accent"
                @click.stop="clear"
                @keydown.enter.stop="clear"
            >
                <X class="size-4 opacity-60" />
            </span>
        </Button>
        <Teleport to="body">
            <div
                v-if="open"
                ref="panelEl"
                data-datetime-panel
                class="rounded-lg border bg-white p-3 shadow-lg"
                :style="panelStyle"
            >
                <div class="mb-2 flex items-center justify-between">
                    <Button variant="ghost" size="icon" @click="moveMonth(-1)" aria-label="Bulan lalu">
                        <ChevronLeft class="size-4" />
                    </Button>
                    <p class="text-sm font-semibold">{{ MONTHS[view.m] }} {{ view.y }}</p>
                    <Button variant="ghost" size="icon" @click="moveMonth(1)" aria-label="Bulan depan">
                        <ChevronRight class="size-4" />
                    </Button>
                </div>
                <div class="mb-1 grid grid-cols-7 gap-1 text-center text-[11px] font-medium text-muted-foreground">
                    <span v-for="d in DAYS" :key="d">{{ d }}</span>
                </div>
                <div class="grid grid-cols-7 gap-1">
                    <button
                        v-for="(c, i) in cells"
                        :key="i"
                        type="button"
                        class="flex size-8 items-center justify-center rounded-md text-sm transition-colors hover:bg-accent"
                        :class="[
                            isSelected(c) ? 'bg-primary font-semibold text-primary-foreground hover:bg-primary' : '',
                            !isSelected(c) && isToday(c) ? 'ring-1 ring-inset ring-primary' : '',
                            !c.inMonth ? 'opacity-40' : '',
                        ]"
                        @click="selectDay(c)"
                    >
                        {{ c.d }}
                    </button>
                </div>
                <div class="mt-3 flex items-center justify-center gap-1 border-t pt-3">
                    <Button variant="ghost" size="icon" aria-label="Kurangi jam" @click="stepHour(-1)">
                        <Minus class="size-4" />
                    </Button>
                    <Input
                        :model-value="parsed ? pad(parsed.h) : '00'"
                        class="w-12 px-1 text-center"
                        inputmode="numeric"
                        aria-label="Jam"
                        @update:model-value="commitHour"
                    />
                    <span class="font-semibold">:</span>
                    <Input
                        :model-value="parsed ? pad(parsed.mi) : '00'"
                        class="w-12 px-1 text-center"
                        inputmode="numeric"
                        aria-label="Menit"
                        @update:model-value="commitMinute"
                    />
                    <Button variant="ghost" size="icon" aria-label="Tambah jam" @click="stepHour(1)">
                        <Plus class="size-4" />
                    </Button>
                </div>
                <p class="mt-1 text-center text-[11px] text-muted-foreground">Jam : menit (WIB)</p>
                <div class="mt-2 flex items-center justify-between">
                    <Button variant="ghost" size="sm" @click="goToday()">Hari ini</Button>
                    <Button variant="outline" size="sm" @click="open = false; unbind()">Tutup</Button>
                </div>
            </div>
        </Teleport>
    </div>
</template>
