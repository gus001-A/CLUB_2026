<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useUsuarioAcciones } from '@/composables/useUsuarioAcciones';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
    usuarios: Object,
    filtros: Object,
});

// Importamos las acciones desde tu composable reutilizable
const { bloquear, eliminar } = useUsuarioAcciones();
const { formatDate } = useFormatters();

const q = ref(props.filtros?.q || '');
const rol = ref(props.filtros?.rol || '');
const estado = ref(props.filtros?.estado || '');

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
</script>

<template>
    <Head title="Usuarios" />

    <AdminLayout>
        <template #title>Usuarios</template>
        <template #breadcrumb>Dashboard &gt; Usuarios</template>

        <div class="admin-card overflow-hidden">
            <!-- Encabezado Principal -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 border-b border-gray-100">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Gestión de Usuarios</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Administra todos los usuarios registrados en la plataforma.</p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-xs font-medium text-gray-400 bg-gray-100 px-3 py-1.5 rounded-lg">
                        Total: <strong class="text-gray-700">{{ usuarios.total }}</strong>
                    </span>
                    <Link :href="route('admin.usuarios.create')" class="admin-btn-primary flex items-center gap-2 text-xs">
                        <i class="pi pi-plus text-xs"></i>
                        <span>Agregar Usuario</span>
                    </Link>
                </div>
            </div>

            <!-- Filtros -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 p-6 bg-gray-50/50 border-b border-gray-100">
                <div class="sm:col-span-6 xl:col-span-6 relative">
                    <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input
                        v-model="q"
                        type="text"
                        placeholder="Buscar por nombre, apodo o correo..."
                        class="admin-input pl-10 pr-3 py-2 w-full text-xs"
                    />
                </div>
                <div class="sm:col-span-3 xl:col-span-3">
                    <select v-model="rol" class="admin-input py-2 w-full text-xs">
                        <option value="">Todos los roles</option>
                        <option value="usuario">Usuario</option>
                        <option value="creador">Creador</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="sm:col-span-3 xl:col-span-3">
                    <select v-model="estado" class="admin-input py-2 w-full text-xs">
                        <option value="">Todos los estados</option>
                        <option value="verificado">Verificado</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="incompleto">Incompleto</option>
                        <option value="bloqueado">Bloqueado</option>
                    </select>
                </div>
            </div>

            <!-- Tabla de Usuarios -->
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left text-sm min-w-[700px]">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="pl-6 pr-4 py-3.5 font-semibold">Usuario</th>
                            <th class="px-4 py-3.5 font-semibold">Correo</th>
                            <th class="px-4 py-3.5 font-semibold">Rol</th>
                            <th class="px-4 py-3.5 font-semibold">Estado</th>
                            <th class="px-4 py-3.5 font-semibold">Registro</th>
                            <th class="pl-2 pr-6 py-3.5 text-center font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="u in usuarios.data" :key="u.id" class="hover:bg-gray-50/50 transition">
                            <!-- Usuario con Avatar -->
                            <td class="pl-6 pr-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 min-w-[36px] max-w-[36px] min-h-[36px] max-h-[36px] flex-none rounded-full bg-brand/10 text-brand flex items-center justify-center font-semibold text-sm">
                                        {{ u.nombre ? u.nombre.charAt(0).toUpperCase() : 'U' }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-800 text-sm leading-tight truncate">{{ u.nombre }}</p>
                                        <p class="text-xs text-gray-400 truncate">@{{ u.apodo }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Correo -->
                            <td class="px-4 py-3.5 text-gray-600 text-xs whitespace-nowrap">{{ u.email }}</td>

                            <!-- Rol Badge -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold capitalize"
                                    :class="u.rol === 'creador' ? 'bg-brand/10 text-brand' : 'bg-gray-100 text-gray-700'">
                                    {{ u.rol }}
                                </span>
                            </td>

                            <!-- Estado Badge -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold capitalize"
                                    :class="{
                                        'bg-green-100 text-green-700': u.estado === 'verificado',
                                        'bg-yellow-100 text-yellow-700': u.estado === 'pendiente' || u.estado === 'incompleto',
                                        'bg-red-100 text-red-700': u.estado === 'bloqueado'
                                    }">
                                    {{ u.estado }}
                                </span>
                            </td>

                            <!-- Fecha Registro -->
                            <td class="px-4 py-3.5 text-gray-500 text-xs whitespace-nowrap">{{ formatDate(u.created_at) }}</td>

                            <!-- Acciones -->
                            <td class="pl-2 pr-6 py-3.5 whitespace-nowrap">
                                <div class="flex justify-center items-center gap-1.5">
                                    <!-- Ver detalles -->
                                    <Link :href="route('admin.usuarios.show', u.id)" class="admin-table-action text-gray-600" title="Ver detalles">
                                        <i class="pi pi-eye text-xs"></i>
                                    </Link>

                                    <!-- Editar -->
                                    <Link :href="route('admin.usuarios.edit', u.id)" class="admin-table-action text-gray-600" title="Editar">
                                        <i class="pi pi-pencil text-xs"></i>
                                    </Link>

                                    <!-- Bloquear / Desbloquear -->
                                    <button @click="bloquear(u)" class="admin-table-action text-gray-600" :title="u.estado === 'bloqueado' ? 'Desbloquear' : 'Bloquear'">
                                        <i class="pi text-xs" :class="u.estado === 'bloqueado' ? 'pi-lock-open' : 'pi-lock'"></i>
                                    </button>

                                    <!-- Eliminar -->
                                    <button @click="eliminar(u)" class="admin-table-action text-red-600 hover:bg-red-50" title="Eliminar">
                                        <i class="pi pi-trash text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Estado Vacío -->
                        <tr v-if="!usuarios.data.length">
                            <td colspan="6" class="py-12 text-center text-gray-400 text-xs">
                                <i class="pi pi-users text-2xl text-gray-300 mb-2 block"></i>
                                No se encontraron usuarios con los criterios de búsqueda.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div v-if="usuarios.last_page > 1" class="p-4 border-t border-gray-100">
                <Pagination :data="usuarios" />
            </div>
        </div>
    </AdminLayout>
</template>