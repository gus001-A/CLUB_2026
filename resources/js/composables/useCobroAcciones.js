import { router } from '@inertiajs/vue3';
import { useConfirm } from '@/composables/useConfirm';
import { useFormatters } from '@/composables/useFormatters';

/**
 * Acciones de transacción reutilizables (aprobar, reembolsar).
 * Antes Cobros/Index.vue y Cobros/Show.vue repetían la misma lógica
 * con su propio modal de confirmación.
 *
 * Uso:
 *   import { useCobroAcciones } from '@/composables/useCobroAcciones';
 *   const { aprobar, reembolsar } = useCobroAcciones();
 *   ...
 *   <button @click="aprobar(t)">...
 *   <button @click="reembolsar(t)">...
 */
export function useCobroAcciones() {
    const { confirm } = useConfirm();
    const { money } = useFormatters();

    async function aprobar(transaccion) {
        const ok = await confirm(`Se marcará la transacción #${transaccion.id} como aprobada.`, {
            title: 'Aprobar transacción',
            confirmLabel: 'Sí, aprobar',
            danger: false,
        });
        if (!ok) return;
        router.post(route('admin.cobros.aprobar', transaccion.id), {}, { preserveScroll: true });
    }

    async function reembolsar(transaccion) {
        const ok = await confirm(`Se reembolsará ${money(transaccion.monto)} a @${transaccion.usuario?.apodo ?? 'usuario'}.`, {
            title: 'Reembolsar transacción',
            confirmLabel: 'Sí, reembolsar',
            danger: true,
        });
        if (!ok) return;
        router.post(route('admin.cobros.reembolsar', transaccion.id), {}, { preserveScroll: true });
    }

    return { aprobar, reembolsar };
}