<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { useFormatters } from '@/composables/useFormatters';
import { useUsuarioAcciones } from '@/composables/useUsuarioAcciones';

const props = defineProps({
    stats: Object,
    filtros: Object,
    cobrosRecientes: Array,
    eventosProximos: Array,
    gestionUsuarios: Object,
    accionesRapidas: Array,
});

const { money, formatDate } = useFormatters();
const { bloquear: bloquearUsuario, eliminar: eliminarUsuario } = useUsuarioAcciones();

const q = ref(props.filtros?.q || '');
const rol = ref(props.filtros?.rol || '');
const estado = ref(props.filtros?.estado || '');

let timeout = null;
function aplicarFiltros() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.dashboard'), {
            q: q.value || undefined,
            rol: rol.value || undefined,
            estado: estado.value || undefined,
        }, { preserveState: true, replace: true });
    }, 350);
}
watch([q, rol, estado], aplicarFiltros);

function varianzaHint(valor, sinDatosTexto) {
    if (valor === null || valor === undefined) return sinDatosTexto;
    return `${valor >= 0 ? '+' : ''}${valor}% vs mes anterior`;
}

// KPIs con el mismo lenguaje visual que Productos: icono + valor + barra de color
const kpis = computed(() => [
    {
        label: 'Usuarios Totales',
        value: props.stats?.usuariosTotales ?? 0,
        icon: 'pi-users',
        color: '#2563EB',
        iconBg: '#DBEAFE',
        gradient: 'linear-gradient(135deg, #2563EB, #1D4ED8)',
        hint: `+${props.stats?.usuariosNuevosHoy ?? 0} nuevos hoy`,
        hintColor: '#8A8481',
    },
    {
        label: 'Ingresos Totales',
        value: money(props.stats?.ingresosTotales ?? 0),
        icon: 'pi-dollar',
        color: '#059669',
        iconBg: '#D1FAE5',
        gradient: 'linear-gradient(135deg, #059669, #047857)',
        hint: varianzaHint(props.stats?.ingresosVariacion, 'Sin movimientos recientes'),
        hintColor: props.stats?.ingresosVariacion >= 0 ? '#059669' : '#DC2626',
    },
    {
        label: 'Suscripciones Activas',
        value: props.stats?.suscripcionesActivas ?? 0,
        icon: 'pi-crown',
        color: '#D97706',
        iconBg: '#FEF3C7',
        gradient: 'linear-gradient(135deg, #D97706, #B45309)',
        hint: 'En espera de activaciones',
        hintColor: '#B7B2AF',
    },
    {
        label: 'Ventas en Shop',
        value: money(props.stats?.ventasShop ?? 0),
        icon: 'pi-shopping-bag',
        color: '#7C3AED',
        iconBg: '#EDE9FE',
        gradient: 'linear-gradient(135deg, #7C3AED, #6D28D9)',
        hint: varianzaHint(props.stats?.ventasVariacion, 'Sin ventas registradas'),
        hintColor: props.stats?.ventasVariacion >= 0 ? '#059669' : '#DC2626',
    },
]);

const badgeRol = { creador: 'admin-dash-badge--rol-creador', usuario: 'admin-dash-badge--rol-usuario', admin: 'admin-dash-badge--rol-admin' };
const badgeEstado = { verificado: 'admin-dash-badge--verificado', pendiente: 'admin-dash-badge--pendiente', incompleto: 'admin-dash-badge--incompleto', bloqueado: 'admin-dash-badge--bloqueado' };

const eventoEstadosColores = {
    publicado: 'bg-green-100 text-green-700 border border-green-200',
    cancelado: 'bg-red-100 text-red-700 border border-red-200',
    programado: 'bg-orange-100 text-orange-700 border border-orange-200',
    borrador: 'bg-gray-100 text-gray-600 border border-gray-200',
};
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Dashboard</template>

        <div class="admin-dash-page">
            <!-- Fila 1: KPIs -->
            <div class="admin-dash-kpi-grid">
                <div v-for="kpi in kpis" :key="kpi.label" class="admin-dash-kpi-card">
                    <div class="admin-dash-kpi-card__icon" :style="{ background: kpi.iconBg, color: kpi.color }">
                        <i class="pi" :class="kpi.icon"></i>
                    </div>
                    <div class="admin-dash-kpi-card__content">
                        <span class="admin-dash-kpi-card__label">{{ kpi.label }}</span>
                        <span class="admin-dash-kpi-card__value" :style="{ color: kpi.color }">{{ kpi.value }}</span>
                        <span class="admin-dash-kpi-card__hint" :style="{ color: kpi.hintColor }">{{ kpi.hint }}</span>
                    </div>
                    <div class="admin-dash-kpi-card__bar" :style="{ background: kpi.gradient }"></div>
                </div>
            </div>

            <!-- Fila 2: Gestión de Usuarios + Acciones Rápidas + Cobros + Eventos + Actividad -->
            <div class="admin-dashboard-grid gap-6 mb-6 w-full">
                <!-- Gestión de Usuarios -->
                <div class="admin-dash-card min-w-0" style="grid-area:tabla">
                    <div>
                        <div class="admin-dash-card__header">
                            <div class="admin-dash-card__header-left">
                                <div class="admin-dash-header-icon"><i class="pi pi-users"></i></div>
                                <div>
                                    <h3>Gestión de Usuarios</h3>
                                    <p class="admin-dash-header-subtitle">Administra los usuarios registrados</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link :href="route('admin.usuarios.index')" class="admin-btn-secondary">
                                    <i class="pi pi-list"></i> Ver todos
                                </Link>
                                <Link :href="route('admin.usuarios.create', { from: 'dashboard' })" class="admin-dash-btn-create">
                                    <i class="pi pi-plus"></i> Agregar Usuario
                                </Link>
                            </div>
                        </div>

                        <!-- Filtros -->
                        <div class="admin-dash-filters">
                            <div class="admin-dash-filters__search">
                                <i class="pi pi-search"></i>
                                <input v-model="q" type="text" placeholder="Buscar usuario..." />
                            </div>
                            <select v-model="rol" class="admin-dash-select">
                                <option value="">Todos los roles</option>
                                <option value="usuario">Usuario</option>
                                <option value="creador">Creador</option>
                                <option value="admin">Admin</option>
                            </select>
                            <select v-model="estado" class="admin-dash-select">
                                <option value="">Todos los estados</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="verificado">Verificado</option>
                                <option value="incompleto">Incompleto</option>
                                <option value="bloqueado">Bloqueado</option>
                            </select>
                        </div>

                        <!-- Tabla -->
                        <div class="overflow-x-auto w-full">
                            <table class="admin-dash-table min-w-[650px]">
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
                                    <tr v-for="u in gestionUsuarios.data" :key="u.id">
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="admin-dash-avatar">{{ u.nombre ? u.nombre.charAt(0).toUpperCase() : 'U' }}</div>
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-gray-800 text-sm leading-tight truncate">{{ u.nombre }}</p>
                                                    <p class="text-xs text-gray-400 truncate">@{{ u.apodo }}</p>
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
                                        <td class="whitespace-nowrap">
                                            <div class="flex justify-center items-center gap-1.5">
                                                <Link :href="route('admin.usuarios.show', u.id)" class="admin-dash-action-btn admin-dash-action-btn--view" title="Ver">
                                                    <i class="pi pi-eye"></i>
                                                </Link>
                                                <button @click="bloquearUsuario(u)" class="admin-dash-action-btn admin-dash-action-btn--lock" :title="u.estado === 'bloqueado' ? 'Desbloquear' : 'Bloquear'">
                                                    <i class="pi" :class="u.estado === 'bloqueado' ? 'pi-lock-open' : 'pi-lock'"></i>
                                                </button>
                                                <button @click="eliminarUsuario(u)" class="admin-dash-action-btn admin-dash-action-btn--delete" title="Eliminar">
                                                    <i class="pi pi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!gestionUsuarios.data?.length">
                                        <td colspan="6" class="text-center text-gray-400 text-xs py-8">Aún no hay usuarios registrados.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="gestionUsuarios.last_page > 1" class="border-t px-6 py-4" style="border-color:var(--line)">
                            <Pagination :data="gestionUsuarios" />
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="admin-dash-card min-w-0" style="grid-area:acciones">
                    <div>
                        <div class="admin-dash-card__header">
                            <div class="admin-dash-card__header-left">
                                <div class="admin-dash-header-icon"><i class="pi pi-bolt"></i></div>
                                <h3>Acciones Rápidas</h3>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2.5 p-4">
                            <Link :href="route('admin.usuarios.index')" class="admin-dash-quick-link group">
                                <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-lock text-sm"></i></div>
                                <div>
                                    <p class="text-sm font-semibold group-hover:text-brand transition" style="color:var(--ink)">Bloquear Usuario</p>
                                    <p class="text-xs" style="color:var(--muted)">Restringe el acceso de un usuario</p>
                                </div>
                            </Link>
                            <Link :href="route('admin.usuarios.index')" class="admin-dash-quick-link group">
                                <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-users text-sm"></i></div>
                                <div>
                                    <p class="text-sm font-semibold group-hover:text-brand transition" style="color:var(--ink)">Ver Usuarios</p>
                                    <p class="text-xs" style="color:var(--muted)">Consulta todos los usuarios registrados</p>
                                </div>
                            </Link>
                            <Link :href="route('admin.cobros.index')" class="admin-dash-quick-link group">
                                <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-dollar text-sm"></i></div>
                                <div>
                                    <p class="text-sm font-semibold group-hover:text-brand transition" style="color:var(--ink)">Ver Cobros</p>
                                    <p class="text-xs" style="color:var(--muted)">Revisa pagos y transacciones</p>
                                </div>
                            </Link>
                            <Link :href="route('admin.eventos.create')" class="admin-dash-quick-link group">
                                <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-calendar text-sm"></i></div>
                                <div>
                                    <p class="text-sm font-semibold group-hover:text-brand transition" style="color:var(--ink)">Crear Evento</p>
                                    <p class="text-xs" style="color:var(--muted)">Organiza un nuevo evento</p>
                                </div>
                            </Link>
                            <Link :href="route('admin.invitaciones.create')" class="admin-dash-quick-link group">
                                <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-envelope text-sm"></i></div>
                                <div>
                                    <p class="text-sm font-semibold group-hover:text-brand transition" style="color:var(--ink)">Enviar Invitación</p>
                                    <p class="text-xs" style="color:var(--muted)">Invita usuarios a la plataforma</p>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Cobros Recientes -->
                <div class="admin-dash-card min-w-0" style="grid-area:cobros">
                    <div>
                        <div class="admin-dash-card__header">
                            <div class="admin-dash-card__header-left">
                                <div class="admin-dash-header-icon"><i class="pi pi-dollar"></i></div>
                                <h3>Cobros Recientes</h3>
                            </div>
                            <Link :href="route('admin.cobros.index')" style="color:var(--brand)" class="text-xs font-semibold hover:underline">Ver todos</Link>
                        </div>
                        <div class="admin-dash-list">
                            <div v-for="c in (cobrosRecientes || [])" :key="c.id" class="admin-dash-list-item">
                                <div class="admin-dash-list-item__left">
                                    <div class="admin-dash-list-icon"><i class="pi pi-dollar"></i></div>
                                    <div class="min-w-0">
                                        <p class="admin-dash-list-title">@{{ c.usuario?.apodo }}</p>
                                        <p class="admin-dash-list-meta truncate">{{ c.concepto }}</p>
                                    </div>
                                </div>
                                <div class="shrink-0 ml-2">
                                    <p class="admin-dash-list-value">{{ money(c.monto) }}</p>
                                    <p class="admin-dash-list-meta text-right">{{ c.tiempo }}</p>
                                </div>
                            </div>
                            <div v-if="!(cobrosRecientes || []).length" class="admin-dash-empty">No hay cobros registrados aún.</div>
                        </div>
                    </div>
                </div>

                <!-- Eventos Próximos -->
                <div class="admin-dash-card min-w-0" style="grid-area:eventos">
                    <div>
                        <div class="admin-dash-card__header">
                            <div class="admin-dash-card__header-left">
                                <div class="admin-dash-header-icon"><i class="pi pi-calendar"></i></div>
                                <h3>Eventos Próximos</h3>
                            </div>
                            <Link :href="route('admin.eventos.index')" style="color:var(--brand)" class="text-xs font-semibold hover:underline">Ver todos</Link>
                        </div>
                        <div class="admin-dash-list">
                            <div v-for="e in (eventosProximos || [])" :key="e.id" class="admin-dash-list-item">
                                <div class="admin-dash-list-item__left">
                                    <div class="admin-dash-list-thumb">
                                        <img :src="e.imagen || 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=100&q=80'" alt="" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="admin-dash-list-title">{{ e.titulo }}</p>
                                        <p class="admin-dash-list-meta">{{ e.fecha }}</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-semibold whitespace-nowrap ml-2"
                                    :class="eventoEstadosColores[e.estado] || 'bg-gray-100 text-gray-600'">
                                    {{ e.estado }}
                                </span>
                            </div>
                            <div v-if="!(eventosProximos || []).length" class="admin-dash-empty">No hay eventos próximos.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>