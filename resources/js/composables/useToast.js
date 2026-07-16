import { reactive } from 'vue';

const toasts = reactive([]);
let nextId = 1;

export function useToast() {
    function push(type, message, timeout = 4000) {
        if (!message) return;

        const id = nextId++;
        toasts.push({ id, type, message });

        setTimeout(() => remove(id), timeout);
    }

    function remove(id) {
        const i = toasts.findIndex((t) => t.id === id);
        if (i !== -1) toasts.splice(i, 1);
    }

    return {
        toasts,
        success: (msg, timeout) => push('success', msg, timeout),
        error: (msg, timeout) => push('error', msg, timeout),
        remove,
    };
}