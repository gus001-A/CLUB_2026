import { reactive } from 'vue';

const state = reactive({
    show: false,
    title: '',
    message: '',
    confirmLabel: 'Aceptar',
    cancelLabel: 'Cancelar',
    danger: false,
    _resolve: null,
});

export function useConfirm() {
    function confirm(message, options = {}) {
        state.show = true;
        state.title = options.title || '¿Estás seguro?';
        state.message = message;
        state.confirmLabel = options.confirmLabel || 'Aceptar';
        state.cancelLabel = options.cancelLabel || 'Cancelar';
        state.danger = options.danger ?? true;

        return new Promise((resolve) => {
            state._resolve = resolve;
        });
    }

    function respond(value) {
        state.show = false;
        if (state._resolve) state._resolve(value);
        state._resolve = null;
    }

    return { state, confirm, respond };
}