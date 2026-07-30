import { useToast as usePrimeToast } from 'primevue/usetoast';

/**
 * Wrapper sobre el sistema de toasts de PrimeVue (el mismo que usa
 * ToastNotification.vue) para poder seguir llamando toast.success(...)
 * y toast.error(...) igual que antes, sin tener que tocar cada página.
 *
 * Úsalo SOLO para avisos que no pasan por el servidor (validaciones
 * en el navegador antes de enviar el formulario, copiar al portapapeles,
 * "próximamente", etc). Los mensajes que sí vienen del servidor
 * (flash.success, flash.error, errores de validación) ya los muestra
 * automáticamente <ToastNotification /> en el layout — no los dupliques.
 */
export function useToast() {
    const toast = usePrimeToast();

    function success(message, life = 4000) {
        if (!message) return;
        toast.add({ severity: 'success', summary: 'Éxito', detail: message, life });
    }

    function error(message, life = 5000) {
        if (!message) return;
        toast.add({ severity: 'error', summary: 'Error', detail: message, life });
    }

    return { success, error };
}