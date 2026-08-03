/**
 * Funciones de formato usadas en casi todas las vistas del panel admin
 * (dinero, fechas, hora). Antes cada .vue las repetía a mano.
 *
 * Uso:
 *   import { useFormatters } from '@/composables/useFormatters';
 *   const { money, formatDate, formatTime } = useFormatters();
 */
export function useFormatters() {
    function money(v) {
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
        }).format(v ?? 0);
    }

    function formatDate(v, opciones = {}) {
        if (!v) return '—';
        const fecha = new Date(v);
        if (isNaN(fecha.getTime())) return v;
        return fecha.toLocaleDateString('es-MX', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            ...opciones,
        });
    }

    function formatDateTime(v) {
        if (!v) return '—';
        const fecha = new Date(v);
        if (isNaN(fecha.getTime())) return v;
        return (
            fecha.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' }) +
            ' · ' +
            fecha.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })
        );
    }

    function formatTime(v) {
        if (!v) return '';
        const fecha = new Date(v);
        return isNaN(fecha.getTime())
            ? ''
            : fecha.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
    }

    return { money, formatDate, formatDateTime, formatTime };
}