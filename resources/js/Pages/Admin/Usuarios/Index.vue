<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const toast = useToast();
const { confirm } = useConfirm();

const props = defineProps({
    usuarios: Object,
    filtros: Object,
});

const q = ref(props.filtros.q || '');
const rol = ref(props.filtros.rol || '');
const estado = ref(props.filtros.estado || '');

let timeout = null;
function buscar() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.usuarios.index'), {
            q: q.value || undefined,
            rol: rol.value || undefined,
            estado: estado.value || undefined,
        }, { preserveState: true, replace: true });
    }, 350);
}

watch([q, rol, estado], buscar);

function formatDate(v) {
    if (!v) return '—';
    return new Date(v).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
}

async function toggleBloqueo(u) {
    const bloqueando = u.estado !== 'bloqueado';
    const ok = await confirm(
        `Esto ${bloqueando ? 'bloqueará' : 'desbloqueará'} a @${u.apodo} y ${bloqueando ? 'perderá' : 'recuperará'} el acceso a la plataforma.`,
        {
            title: bloqueando ? 'Bloquear usuario' : 'Desbloquear usuario',
            confirmLabel: bloqueando ? 'Sí, bloquear' : 'Sí, desbloquear',
            danger: bloqueando,
        }
    );
    if (!ok) return;

    router.post(route('admin.usuarios.toggle-bloqueo', u.id), {}, {
        preserveScroll: true,
        onError: () => toast.error('No se pudo actualizar el estado del usuario.'),
    });
}

async function eliminar(u) {
    const ok = await confirm(`Esto eliminará a @${u.apodo} de la plataforma. Esta acción no se puede deshacer.`, {
        title: 'Eliminar usuario',
        confirmLabel: 'Sí, eliminar',
        danger: true,
    });
    if (!ok) return;

    router.delete(route('admin.usuarios.destroy', u.id), {
        preserveScroll: true,
        onError: () => toast.error('No se pudo eliminar al usuario.'),
    });
}
</script>

<template>
    <Head title="Usuarios" />

    <AdminLayout>
        <template #title>Usuarios</template>
        <template #breadcrumb>Dashboard &gt; Usuarios</template>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-800">Todos los usuarios</h2>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-400">{{ usuarios.total }} en total</span>
                    <Link :href="route('admin.usuarios.create')" class="bg-brand hover:bg-brand-dark text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                        <i class="pi pi-plus text-xs"></i> Agregar Usuario
                    </Link>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 mb-4">
                <div class="relative flex-1 min-w-[220px]">
                    <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-sm pointer-events-none"></i>
                    <input
                        v-model="q"
                        type="text"
                        placeholder="Buscar por nombre, apodo o correo..."
                        class="w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none"
                    />
                </div>
                <select v-model="rol" class="rounded-lg border border-gray-300 text-sm px-3 py-2 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                    <option value="">Todos los roles</option>
                    <option value="usuario">Usuario</option>
                    <option value="creador">Creador</option>
                    <option value="admin">Admin</option>
                </select>
                <select v-model="estado" class="rounded-lg border border-gray-300 text-sm px-3 py-2 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                    <option value="">Todos los estados</option>
                    <option value="verificado">Verificado</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="incompleto">Incompleto</option>
                    <option value="bloqueado">Bloqueado</option>
                </select>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-400 border-b border-gray-100">
                        <th class="pb-2 font-medium">Usuario</th>
                        <th class="pb-2 font-medium">Correo</th>
                        <th class="pb-2 font-medium">Rol</th>
                        <th class="pb-2 font-medium">Estado</th>
                        <th class="pb-2 font-medium">Registro</th>
                        <th class="pb-2 font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="u in usuarios.data" :key="u.id" class="border-b border-gray-50 last:border-0 hover:bg-gray-50/70 transition-colors">
                        <td class="py-2.5">
                            <p class="font-medium text-gray-800">{{ u.nombre }}</p>
                            <p class="text-gray-400 text-xs">@{{ u.apodo }}</p>
                        </td>
                        <td class="py-2.5 text-gray-500">{{ u.email }}</td>
                        <td class="py-2.5">
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-medium capitalize"
                                :class="u.rol === 'creador' ? 'bg-brand/10 text-brand' : 'bg-gray-100 text-gray-600'"
                            >
                                {{ u.rol }}
                            </span>
                        </td>
                        <td class="py-2.5">
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-medium capitalize"
                                :class="{
                                    'bg-green-100 text-green-700': u.estado === 'verificado',
                                    'bg-amber-100 text-amber-700': u.estado === 'pendiente' || u.estado === 'incompleto',
                                    'bg-red-100 text-red-700': u.estado === 'bloqueado',
                                }"
                            >
                                {{ u.estado }}
                            </span>
                        </td>
                        <td class="py-2.5 text-gray-400">{{ formatDate(u.created_at) }}</td>
                        <td class="py-2.5">
                            <div class="flex items-center gap-3 text-gray-400">
                                <i
                                    class="pi cursor-pointer hover:text-gray-700"
                                    :class="u.estado === 'bloqueado' ? 'pi-lock-open' : 'pi-lock'"
                                    :title="u.estado === 'bloqueado' ? 'Desbloquear' : 'Bloquear'"
                                    @click="toggleBloqueo(u)"
                                ></i>
                                <i
                                    class="pi pi-trash cursor-pointer hover:text-red-600"
                                    title="Eliminar"
                                    @click="eliminar(u)"
                                ></i>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!usuarios.data.length">
                        <td colspan="6" class="py-8 text-center text-gray-400">No se encontraron usuarios con esos filtros.</td>
                    </tr>
                </tbody>
            </table>

            <!-- Paginación -->
            <div v-if="usuarios.last_page > 1" class="flex items-center justify-between mt-5 text-sm">
                <p class="text-gray-400">
                    Mostrando {{ usuarios.from }}–{{ usuarios.to }} de {{ usuarios.total }}
                </p>
                <div class="flex items-center gap-1">
                    <template v-for="(link, i) in usuarios.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            preserve-scroll
                            preserve-state
                            class="px-3 py-1.5 rounded-lg"
                            :class="link.active ? 'bg-brand text-white' : 'text-gray-500 hover:bg-gray-100'"
                            v-html="link.label"
                        />
                        <span v-else class="px-3 py-1.5 text-gray-300" v-html="link.label"></span>
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>