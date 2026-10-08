import { ref } from 'vue';

const state = ref({
    open: false,
    title: 'Konfirmasi',
    message: '',
    confirmText: 'Ya, lanjutkan',
    cancelText: 'Batal',
    tone: 'default',
});

let resolver = null;

export function useConfirm() {
    function confirm(options = {}) {
        state.value = {
            open: true,
            title: options.title ?? 'Konfirmasi',
            message: options.message ?? '',
            confirmText: options.confirmText ?? 'Ya, lanjutkan',
            cancelText: options.cancelText ?? 'Batal',
            tone: options.tone ?? 'default',
        };
        return new Promise((resolve) => {
            resolver = resolve;
        });
    }

    function resolve(value) {
        state.value.open = false;
        if (resolver) {
            resolver(value);
            resolver = null;
        }
    }

    return { state, confirm, resolve };
}

export function useConfirmState() {
    return { state, resolve };
}
