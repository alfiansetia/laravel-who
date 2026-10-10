<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Cog, Expand, Flag, Pause, Play, RotateCcw, Trash2, Undo2 } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import AppModal from '@/components/AppModal.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PageHeader from '@/components/PageHeader.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    title: { type: String, default: 'Scoreboard' },
});

const toast = useToast();
const { confirm } = useConfirm();

const homeName = ref('HOME TEAM');
const awayName = ref('AWAY TEAM');
const homeScore = ref(0);
const awayScore = ref(0);
const homeFouls = ref(0);
const awayFouls = ref(0);
const period = ref(1);
const baseTime = ref(720);
const timeLeft = ref(720);
const isRunning = ref(false);
const popTeam = ref(null);
const timeModalOpen = ref(false);
const minutesInput = ref('12');

let timerId = null;
let audioCtx = null;
let popTimer = null;

const timerText = computed(() => {
    const m = Math.floor(timeLeft.value / 60);
    const s = timeLeft.value % 60;
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
});

const timerLow = computed(() => timeLeft.value <= 10);

function playSound(freq, duration) {
    try {
        audioCtx ??= new (window.AudioContext ?? window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'square';
        osc.frequency.value = freq;
        gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + duration);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + duration);
    } catch {
        // audio tidak tersedia, skor tetap jalan
    }
}

function changeScore(team, amount) {
    if (team === 'home') {
        homeScore.value = Math.max(0, homeScore.value + amount);
    } else {
        awayScore.value = Math.max(0, awayScore.value + amount);
    }
    clearTimeout(popTimer);
    popTeam.value = team;
    popTimer = setTimeout(() => {
        popTeam.value = null;
    }, 300);
    if (amount > 0) {
        playSound(team === 'home' ? 800 : 1000, 0.1);
    }
}

function changeFoul(team) {
    if (team === 'home') {
        homeFouls.value += 1;
    } else {
        awayFouls.value += 1;
    }
}

function changePeriod(amount) {
    period.value = Math.max(1, period.value + amount);
}

function updateTimerDisplay() {
    // timeLeft reaktif, tampilan ikut otomatis
}

function stopTick() {
    clearInterval(timerId);
    timerId = null;
    isRunning.value = false;
}

function toggleTimer() {
    if (isRunning.value) {
        stopTick();
        return;
    }
    if (timeLeft.value <= 0) {
        toast.warning('Atur waktu pertandingan dulu.');
        return;
    }
    isRunning.value = true;
    timerId = setInterval(() => {
        timeLeft.value -= 1;
        if (timeLeft.value <= 0) {
            timeLeft.value = 0;
            stopTick();
            playSound(200, 1.5);
            toast.error("Waktu habis!");
        }
    }, 1000);
}

function resetTimer() {
    stopTick();
    timeLeft.value = baseTime.value;
    updateTimerDisplay();
}

function openTimeModal() {
    minutesInput.value = String(baseTime.value / 60);
    timeModalOpen.value = true;
}

function saveTime() {
    const minutes = Number(minutesInput.value);
    if (Number.isNaN(minutes) || minutes < 0) {
        toast.warning('Masukkan menit berupa angka 0 atau lebih.');
        return;
    }
    baseTime.value = Math.floor(minutes * 60);
    timeModalOpen.value = false;
    resetTimer();
}

async function resetBoard() {
    const ok = await confirm({
        title: 'Reset papan skor?',
        message: 'Semua skor, foul, periode, dan timer dikembalikan ke awal.',
        confirmText: 'Ya, reset',
        tone: 'destructive',
    });
    if (!ok) {
        return;
    }
    homeScore.value = 0;
    awayScore.value = 0;
    homeFouls.value = 0;
    awayFouls.value = 0;
    period.value = 1;
    resetTimer();
}

function openFullscreen() {
    const el = document.documentElement;
    try {
        if (el.requestFullscreen) {
            el.requestFullscreen();
        } else if (el.webkitRequestFullscreen) {
            el.webkitRequestFullscreen();
        }
    } catch {
        toast.warning('Fullscreen tidak didukung browser ini.');
    }
}

function onKeydown(e) {
    if (timeModalOpen.value) {
        return;
    }
    const tag = e.target?.tagName ?? '';
    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) {
        return;
    }
    if (e.code === 'Space') {
        e.preventDefault();
        toggleTimer();
    } else if (e.key === 'h' || e.key === 'H') {
        changeScore('home', 1);
    } else if (e.key === 'a' || e.key === 'A') {
        changeScore('away', 1);
    } else if (e.key === 'r' || e.key === 'R') {
        resetTimer();
    }
}

if (typeof window !== 'undefined') {
    window.addEventListener('keydown', onKeydown);
}

onBeforeUnmount(() => {
    stopTick();
    clearTimeout(popTimer);
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <AppLayout>
        <Head>
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Orbitron:wght@400;700;900&display=swap" />
        </Head>
        <PageHeader :title="props.title" />

        <div class="rounded-2xl bg-[#0c0d12] p-4 text-white sm:p-8">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-widest text-slate-400">Period</span>
                    <span class="font-['Bebas_Neue'] text-3xl text-[#ffd700]">{{ period }}</span>
                    <div class="flex gap-1">
                        <Button variant="outline" size="sm" class="border-white/20 bg-transparent text-white hover:bg-white/10" @click="changePeriod(-1)">-</Button>
                        <Button variant="outline" size="sm" class="border-white/20 bg-transparent text-white hover:bg-white/10" @click="changePeriod(1)">+</Button>
                    </div>
                </div>
                <button
                    type="button"
                    :title="'Klik untuk atur waktu'"
                    class="order-first w-full cursor-pointer text-center font-['Orbitron'] text-6xl font-black tabular-nums sm:order-none sm:w-auto sm:text-7xl"
                    :class="[timerLow ? 'text-[#ff003c] [text-shadow:0_0_30px_#ff003c]' : 'text-white [text-shadow:0_0_30px_rgba(255,255,255,0.35)]', isRunning && 'animate-pulse']"
                    @click="openTimeModal"
                >
                    {{ timerText }}
                </button>
                <div class="flex items-center gap-4 text-sm">
                    <span><Flag class="mr-1 inline size-4 text-[#00f2ff]" /> {{ homeFouls }}</span>
                    <span><Flag class="mr-1 inline size-4 text-[#ff003c]" /> {{ awayFouls }}</span>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                <Button :variant="isRunning ? 'secondary' : 'default'" @click="toggleTimer">
                    <Pause v-if="isRunning" /> <Play v-else /> {{ isRunning ? 'PAUSE' : 'START' }}
                </Button>
                <Button variant="outline" class="border-white/20 bg-transparent text-white hover:bg-white/10" @click="resetTimer"><Undo2 /> Reset Timer</Button>
                <Button variant="ghost" class="text-slate-300 hover:bg-white/10 hover:text-white" @click="openTimeModal"><Cog /> Atur Waktu</Button>
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-2">
                <div class="rounded-xl border border-[#00f2ff]/30 bg-white/5 p-4 text-center sm:p-6">
                    <input
                        v-model="homeName"
                        :spellcheck="false"
                        maxlength="24"
                        class="w-full bg-transparent text-center text-lg font-bold uppercase tracking-widest text-[#00f2ff] focus:outline-none"
                    />
                    <div
                        class="font-['Orbitron'] text-8xl font-black tabular-nums transition-transform duration-150 [text-shadow:0_0_40px_#00f2ff] sm:text-9xl"
                        :class="popTeam === 'home' ? 'scale-110 text-[#00f2ff]' : 'text-[#00f2ff]'"
                    >
                        {{ homeScore }}
                    </div>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        <Button variant="outline" class="border-white/20 bg-transparent text-white hover:bg-white/10" @click="changeScore('home', -1)">-1</Button>
                        <Button @click="changeScore('home', 1)">+1</Button>
                        <Button variant="secondary" @click="changeScore('home', 2)">+2</Button>
                        <Button variant="secondary" @click="changeScore('home', 3)">+3</Button>
                    </div>
                </div>
                <div class="rounded-xl border border-[#ff003c]/30 bg-white/5 p-4 text-center sm:p-6">
                    <input
                        v-model="awayName"
                        :spellcheck="false"
                        maxlength="24"
                        class="w-full bg-transparent text-center text-lg font-bold uppercase tracking-widest text-[#ff003c] focus:outline-none"
                    />
                    <div
                        class="font-['Orbitron'] text-8xl font-black tabular-nums transition-transform duration-150 [text-shadow:0_0_40px_#ff003c] sm:text-9xl"
                        :class="popTeam === 'away' ? 'scale-110 text-[#ff003c]' : 'text-[#ff003c]'"
                    >
                        {{ awayScore }}
                    </div>
                    <div class="mt-4 flex flex-wrap justify-center gap-2">
                        <Button variant="outline" class="border-white/20 bg-transparent text-white hover:bg-white/10" @click="changeScore('away', -1)">-1</Button>
                        <Button variant="destructive" @click="changeScore('away', 1)">+1</Button>
                        <Button variant="secondary" @click="changeScore('away', 2)">+2</Button>
                        <Button variant="secondary" @click="changeScore('away', 3)">+3</Button>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-2 border-t border-white/10 pt-4">
                <Button variant="outline" class="border-[#00f2ff]/40 bg-transparent text-[#00f2ff] hover:bg-[#00f2ff]/10" @click="changeFoul('home')"><Flag /> Home Foul</Button>
                <Button variant="outline" class="border-white/20 bg-transparent text-slate-300 hover:bg-white/10" @click="resetBoard"><Trash2 /> Reset</Button>
                <Button variant="outline" class="border-[#ff003c]/40 bg-transparent text-[#ff003c] hover:bg-[#ff003c]/10" @click="changeFoul('away')"><Flag /> Away Foul</Button>
            </div>

            <p class="mt-4 text-center text-xs text-slate-500">Spasi: mulai/jeda timer | H: +1 home | A: +1 away | R: reset timer</p>
        </div>

        <button
            type="button"
            title="Fullscreen"
            class="fixed bottom-6 right-6 rounded-full border border-input bg-white p-3 opacity-40 shadow-lg transition-opacity hover:opacity-100"
            @click="openFullscreen"
        >
            <Expand class="size-5" />
        </button>

        <AppModal v-model:open="timeModalOpen" title="Atur Waktu Pertandingan" size="sm">
            <div class="space-y-2">
                <label for="sb-minutes" class="text-sm font-medium">Durasi (menit)</label>
                <Input id="sb-minutes" v-model="minutesInput" type="number" min="0" placeholder="cth: 12" />
            </div>
            <template #footer>
                <Button variant="ghost" @click="timeModalOpen = false">Batal</Button>
                <Button @click="saveTime"><RotateCcw /> Terapkan</Button>
            </template>
        </AppModal>

        <ConfirmDialog />
    </AppLayout>
</template>
