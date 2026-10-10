import { toast as sonner } from 'vue-sonner';

const TITLES = {
    success: 'Success',
    error: 'Error',
    warning: 'Caution',
    info: 'Hello',
};

export function useToast() {
    function show(type, message, title, opts = {}) {
        const heading = title ?? TITLES[type] ?? 'Info';
        sonner[type](heading, {
            description: message,
            duration: opts.duration ?? 4000,
            closeButton: true,
            id: opts.id,
        });
    }

    return {
        success: (message, title, opts) => show('success', message, title, opts),
        error: (message, title, opts) => show('error', message, title, opts),
        warning: (message, title, opts) => show('warning', message, title, opts),
        info: (message, title, opts) => show('info', message, title, opts),
    };
}
