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

const badgeRol = { creador: 'admin-dash-badge--rol-creador', usuario: 'admin-dash-badge--rol-usuario', admin: 'admin-dash-badge--rol-admin' };
const badgeEstado = { verificado: 'admin-dash-badge--verificado', pendiente: 'admin-dash-badge--pendiente', incompleto: 'admin-dash-badge--incompleto', bloqueado: 'admin-dash-badge--bloqueado' };
</script>

<template>
    <Head title="Usuarios" />

    <AdminLayout>
        <template #title>Usuarios</template>
        <template #breadcrumb>Dashboard / Usuarios</template>

        <div class="admin-user-page">
            <div class="admin-user-table-card">
                <!-- Header con gradiente -->
                <div class="admin-user-table-card__header">
                    <div class="admin-user-table-card__header-left">
                        <div class="admin-user-header-icon">
                            <i class="pi pi-users"></i>
                        </div>
                        <div>
                            <h3>Gestión de Usuarios</h3>
                            <p class="admin-user-header-subtitle">{{ usuarios.total }} usuarios registrados en la plataforma</p>
                        </div>
                    </div>
                    <Link :href="route('admin.usuarios.create')" class="admin-user-btn-create">
                        <i class="pi pi-plus"></i>
                        Agregar Usuario
                    </Link>
                </div>

                <!-- Filtros -->
                <div class="admin-user-filters">
                    <div class="admin-user-filters__search">
                        <i class="pi pi-search"></i>
                        <input v-model="q" type="text" placeholder="Buscar por nombre, apodo o correo..." />
                    </div>
                    <div class="admin-user-filters__selects">
                        <select v-model="rol" class="admin-user-select">
                            <option value="">Todos los roles</option>
                            <option value="usuario">Usuario</option>
                            <option value="creador">Creador</option>
                            <option value="admin">Admin</option>
                        </select>
                        <select v-model="estado" class="admin-user-select">
                            <option value="">Todos los estados</option>
                            <option value="verificado">Verificado</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="incompleto">Incompleto</option>
                            <option value="bloqueado">Bloqueado</option>
                        </select>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="overflow-x-auto">
                    <table class="admin-user-table min-w-[700px]">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Registro</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="u in usuarios.data" :key="u.id">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="admin-dash-avatar">
                                            <img v-if="u.foto_principal" :src="u.foto_principal" :alt="u.nombre" />
                                            <span v-else>{{ u.nombre ? u.nombre.charAt(0).toUpperCase() : 'U' }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="admin-user-name">{{ u.nombre }}</p>
                                            <p class="admin-user-handle">@{{ u.apodo }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-gray-600 text-xs whitespace-nowrap">{{ u.email }}</td>
                                <td class="whitespace-nowrap">
                                    <span class="admin-dash-badge" :class="badgeRol[u.rol]">
                                        <span class="admin-dash-badge-dot"></span>{{ u.rol }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap">
                                    <span class="admin-dash-badge" :class="badgeEstado[u.estado]">
                                        <span class="admin-dash-badge-dot"></span>{{ u.estado }}
                                    </span>
                                </td>
                                <td class="text-gray-500 text-xs whitespace-nowrap">{{ formatDate(u.created_at) }}</td>
                                <td>
                                    <div class="flex justify-center items-center gap-1.5">
                                        <Link :href="route('admin.usuarios.show', u.id)" class="admin-dash-action-btn admin-dash-action-btn--view" title="Ver detalles">
                                            <i class="pi pi-eye"></i>
                                        </Link>
                                        <Link :href="route('admin.usuarios.edit', u.id)" class="admin-dash-action-btn admin-dash-action-btn--edit" title="Editar">
                                            <i class="pi pi-pencil"></i>
                                        </Link>
                                        <button @click="bloquear(u)" class="admin-dash-action-btn admin-dash-action-btn--lock" :title="u.estado === 'bloqueado' ? 'Desbloquear' : 'Bloquear'">
                                            <i class="pi" :class="u.estado === 'bloqueado' ? 'pi-lock-open' : 'pi-lock'"></i>
                                        </button>
                                        <button @click="eliminar(u)" class="admin-dash-action-btn admin-dash-action-btn--delete" title="Eliminar">
                                            <i class="pi pi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!usuarios.data.length">
                                <td colspan="6">
                                    <div class="admin-prod-empty-state">
                                        <div class="admin-prod-empty-state__icon">
                                            <i class="pi pi-users"></i>
                                        </div>
                                        <h4>No se encontraron usuarios</h4>
                                        <p>Prueba ajustando los filtros de búsqueda</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="usuarios.last_page > 1" class="admin-user-table-footer">
                    <Pagination :data="usuarios" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>