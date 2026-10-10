import { ref } from 'vue';

const blocking = ref(0);

export function useBlock() {
    function block() {
        blocking.value += 1;
    }

    function unblock() {
        blocking.value = Math.max(0, blocking.value - 1);
    }

    async function withBlock(fn) {
        block();
        const timer = setTimeout(() => unblock(), 60000);
        try {
            return await fn();
        } finally {
            clearTimeout(timer);
            unblock();
        }
    }

    return { blocking, block, unblock, withBlock };
}
