import axios from 'axios';
import { useAuthModal } from '@/composables/useAuthModal';
import { useBlock } from '@/composables/useBlock';
import { useToast } from '@/composables/useToast';

const { open: openAuthModal } = useAuthModal();
const { block, unblock } = useBlock();
const toast = useToast();

const api = axios.create({
    baseURL: '/api',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
    },
    withCredentials: true,
});

api.interceptors.request.use((config) => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token;
    }
    if (config.block) {
        block();
    }
    return config;
});

api.interceptors.response.use(
    (response) => {
        if (response.config?.block) {
            unblock();
        }
        return response;
    },
    (error) => {
        if (error.config?.block) {
            unblock();
        }
        if (error.response?.status === 401 && !error.config?.url?.includes('auth/verify')) {
            openAuthModal();
            return Promise.reject(error);
        }
        if (!error.config?.silent && error.response?.status !== 422) {
            const message = error.response?.data?.message || 'Terjadi kesalahan. Silakan coba lagi.';
            toast.error(message);
        }
        return Promise.reject(error);
    },
);

export default api;
