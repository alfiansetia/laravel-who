<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { Copy, Mic, Square, TriangleAlert } from '@lucide/vue';
import AppLayout from '@/components/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import Button from '@/components/ui/Button.vue';
import Card from '@/components/ui/Card.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { copyText } from '@/lib/export';

const props = defineProps({
    title: { type: String, default: 'Speech To Text' },
});

const SILENCE_TIMEOUT = 4000;

const supported = ref(true);
const isRecording = ref(false);
const transcript = ref('');
const statusText = ref('Klik "Mulai Rekam" lalu berbicara.');
const statusTone = ref('muted');

let recognition = null;
let silenceTimer = null;

const toneClasses = {
    success: 'text-green-600',
    warning: 'text-amber-600',
    danger: 'text-red-600',
    muted: 'text-muted-foreground',
};

function setStatus(text, tone = 'muted') {
    statusText.value = text;
    statusTone.value = tone;
}

function resetSilenceDetection() {
    clearTimeout(silenceTimer);
    silenceTimer = setTimeout(() => {
        stopRecording();
        setStatus('Tidak ada suara, otomatis berhenti.', 'warning');
    }, SILENCE_TIMEOUT);
}

function stopRecording() {
    if (!isRecording.value) {
        return;
    }
    try {
        recognition?.stop();
    } catch {
        // abaikan: recognition mungkin sudah berhenti sendiri
    }
    isRecording.value = false;
    clearTimeout(silenceTimer);
}

async function startRecording() {
    try {
        await navigator.mediaDevices.getUserMedia({ audio: true });
    } catch {
        setStatus('Izin mikrofon ditolak. Izinkan akses mikrofon lalu coba lagi.', 'danger');
        return;
    }
    const SpeechRecognition = window.SpeechRecognition ?? window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.lang = 'id-ID';
    recognition.interimResults = true;
    recognition.continuous = true;
    recognition.onstart = () => {
        setStatus('Mendengarkan...', 'success');
        resetSilenceDetection();
    };
    recognition.onresult = (event) => {
        let text = '';
        for (let i = 0; i < event.results.length; i++) {
            text += `${event.results[i][0].transcript} `;
        }
        text = text.trim();
        transcript.value = text.charAt(0).toUpperCase() + text.slice(1);
        resetSilenceDetection();
    };
    recognition.onend = () => {
        if (isRecording.value) {
            try {
                recognition.start();
            } catch {
                isRecording.value = false;
            }
            return;
        }
        setStatus('Berhenti mendengarkan.', 'muted');
    };
    recognition.onerror = () => {
        stopRecording();
        setStatus('Gagal mengakses mikrofon.', 'danger');
    };
    try {
        recognition.start();
        isRecording.value = true;
    } catch {
        setStatus('Gagal memulai perekaman.', 'danger');
    }
}

function onStop() {
    stopRecording();
    setStatus('Berhenti oleh pengguna.', 'muted');
}

function onCopy() {
    copyText(transcript.value.trim(), 'Teks disalin ke clipboard.');
}

onMounted(() => {
    if (!(window.SpeechRecognition ?? window.webkitSpeechRecognition)) {
        supported.value = false;
        setStatus('Browser tidak mendukung fitur ini. Gunakan Chrome atau Edge.', 'danger');
    }
});

onBeforeUnmount(() => {
    clearTimeout(silenceTimer);
    isRecording.value = false;
    try {
        recognition?.stop();
    } catch {
        // abaikan saat unmount
    }
});
</script>

<template>
    <AppLayout>
        <PageHeader :title="props.title" description="Ucapkan kalimat Bahasa Indonesia, hasilnya tertulis otomatis" />

        <Card class="p-4 sm:p-6">
            <div v-if="!supported" class="mb-4 flex items-start gap-2 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                <TriangleAlert class="mt-0.5 size-4 shrink-0" />
                <span>Peringatan! Browser Anda tidak mendukung Web Speech API. Gunakan Chrome atau Edge versi terbaru.</span>
            </div>

            <div class="flex flex-wrap gap-2">
                <Button :disabled="!supported || isRecording" @click="startRecording"><Mic /> Mulai Rekam</Button>
                <Button variant="destructive" :disabled="!isRecording" @click="onStop"><Square /> Stop</Button>
                <Button variant="outline" :disabled="!supported || !transcript" @click="onCopy"><Copy /> Salin Teks</Button>
            </div>

            <div class="mt-4 space-y-2">
                <label for="stt-result" class="text-sm font-medium">Hasil Transkripsi:</label>
                <Textarea id="stt-result" v-model="transcript" readonly rows="8" class="text-lg" placeholder="Mulai berbicara..." />
            </div>

            <p class="mt-3 text-center text-sm font-semibold" :class="toneClasses[statusTone]">{{ statusText }}</p>
        </Card>
    </AppLayout>
</template>
