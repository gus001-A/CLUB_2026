import { router } from '@inertiajs/vue3';
import { useConfirm } from '@/composables/useConfirm';

/**
 * Acciones de usuario reutilizables (bloquear/desbloquear, eliminar).
 * Antes cada vista (Dashboard, Usuarios/Index, etc.) repetía esta
 * misma lógica con su propio modal de confirmación.
 *
 * Uso:
 *   import { useUsuarioAcciones } from '@/composables/useUsuarioAcciones';
 *   const { bloquear, eliminar } = useUsuarioAcciones();
 *   ...
 *   <button @click="bloquear(u)">...
 *   <button @click="eliminar(u)">...
 */
export function useUsuarioAcciones() {
    const { confirm } = useConfirm();

    async function bloquear(usuario) {
        const bloqueando = usuario.estado !== 'bloqueado';
        const ok = await confirm(
            `Esto ${bloqueando ? 'bloqueará' : 'desbloqueará'} a @${usuario.apodo}.`,
            {
                title: bloqueando ? 'Bloquear usuario' : 'Desbloquear usuario',
                confirmLabel: bloqueando ? 'Sí, bloquear' : 'Sí, desbloquear',
                danger: bloqueando,
            }
        );
        if (!ok) return;
        router.post(route('admin.usuarios.toggle-bloqueo', usuario.id), {}, { preserveScroll: true });
    }

    async function eliminar(usuario) {
        const ok = await confirm(
            `¿Estás seguro de eliminar al usuario @${usuario.apodo}?`,
            {
                title: 'Eliminar usuario',
                confirmLabel: 'Sí, eliminar',
                danger: true,
            }
        );
        if (!ok) return;
        router.delete(route('admin.usuarios.destroy', usuario.id), { preserveScroll: true });
    }

    return { bloquear, eliminar };
}