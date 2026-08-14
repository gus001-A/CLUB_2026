<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KpiCard from '@/Components/KpiCard.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { useToast } from '@/composables/useToast';
import { useFormatters } from '@/composables/useFormatters';
import { useUsuarioAcciones } from '@/composables/useUsuarioAcciones';

const props = defineProps({
    stats: Object,
    filtros: Object,
    cobrosRecientes: Array,
    eventosProximos: Array,
    actividadReciente: Array,
    gestionUsuarios: Object,
    accionesRapidas: Array,
});

const toast = useToast();
const { money, formatDate, formatTime } = useFormatters();
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
function varianzaColor(valor) {
    if (valor === null || valor === undefined) return 'text-gray-400';
    return valor >= 0 ? 'text-green-600' : 'text-red-600';
}

const ingresosHint = computed(() => varianzaHint(props.stats?.ingresosVariacion, 'Sin movimientos recientes'));
const ingresosHintColor = computed(() => varianzaColor(props.stats?.ingresosVariacion));
const ventasHint = computed(() => varianzaHint(props.stats?.ventasVariacion, 'Sin ventas registradas'));
const ventasHintColor = computed(() => varianzaColor(props.stats?.ventasVariacion));

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

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- Fila 1: KPIs (4 columnas) -->
            <div class="admin-kpi-grid gap-6 mb-6 w-full">
                <div class="min-w-0">
                    <KpiCard label="Usuarios Totales" :value="stats?.usuariosTotales ?? 0" icon="pi-users"
                        :hint="`+${stats?.usuariosNuevosHoy ?? 0} nuevos hoy`" />
                </div>
                <div class="min-w-0">
                    <KpiCard label="Ingresos Totales" :value="money(stats?.ingresosTotales ?? 0)" icon="pi-dollar"
                        :hint="ingresosHint" :hint-color="ingresosHintColor" />
                </div>
                <div class="min-w-0">
                    <KpiCard label="Suscripciones Activas" :value="stats?.suscripcionesActivas ?? 0" icon="pi-crown"
                        hint="En espera de activaciones" hint-color="text-gray-400" />
                </div>
                <div class="min-w-0">
                    <KpiCard label="Ventas en Shop" :value="money(stats?.ventasShop ?? 0)" icon="pi-shopping-bag"
                        :hint="ventasHint" :hint-color="ventasHintColor" />
                </div>
            </div>

            <!-- Fila 2: Gestión de Usuarios + Acciones Rápidas + Cobros + Eventos + Actividad -->
            <div class="admin-dashboard-grid gap-6 mb-6 w-full">
                <!-- Gestión de Usuarios -->
                <div class="min-w-0 admin-card overflow-hidden flex flex-col justify-between" style="grid-area:tabla">
                    <div>
                        <!-- Encabezado -->
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-users text-brand"></i> Gestión de Usuarios</span>
                            <Link :href="route('admin.usuarios.create', { from: 'dashboard' })" class="admin-btn-primary flex-none" style="padding:0.4rem 0.85rem;font-size:0.75rem">
                                <i class="pi pi-plus text-xs"></i>
                                Agregar Usuario
                            </Link>
                        </div>
                        <p class="text-xs px-6 pt-4" style="color:var(--muted)">Administra los usuarios registrados.</p>

                        <!-- Filtros -->
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 px-6 py-4">
                            <div class="sm:col-span-6 xl:col-span-5 relative">
                                <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input v-model="q" type="text" placeholder="Buscar usuario..." class="admin-input pl-10 pr-3 py-2">
                            </div>
                            <div class="sm:col-span-3 xl:col-span-3">
                                <select v-model="rol" class="admin-input py-2">
                                    <option value="">Todos los roles</option>
                                    <option value="usuario">Usuario</option>
                                    <option value="creador">Creador</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="sm:col-span-3 xl:col-span-4">
                                <select v-model="estado" class="admin-input py-2">
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="verificado">Verificado</option>
                                    <option value="incompleto">Incompleto</option>
                                    <option value="bloqueado">Bloqueado</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div class="overflow-x-auto w-full">
                            <table class="w-full text-left text-sm min-w-[650px]">
                                <thead>
                                    <tr class="border-y text-xs uppercase tracking-wider" style="border-color:var(--line);background:var(--surface);color:var(--muted)">
                                        <th class="pl-6 pr-4 py-3 font-semibold">Usuario</th>
                                        <th class="px-3 py-3 font-semibold">Correo</th>
                                        <th class="px-3 py-3 font-semibold">Rol</th>
                                        <th class="px-3 py-3 font-semibold">Estado</th>
                                        <th class="px-3 py-3 font-semibold">Registro</th>
                                        <th class="pl-2 pr-6 py-3 text-center font-semibold">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="u in gestionUsuarios.data" :key="u.id" class="hover:bg-gray-50/50 transition">
                                        <td class="pl-6 pr-4 py-3.5 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 min-w-[36px] max-w-[36px] min-h-[36px] max-h-[36px] flex-none rounded-full flex items-center justify-center font-semibold text-sm" style="background:var(--brand-soft);color:var(--brand)">
                                                    {{ u.nombre ? u.nombre.charAt(0).toUpperCase() : 'U' }}
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-gray-800 text-sm leading-tight truncate">{{ u.nombre }}</p>
                                                    <p class="text-xs text-gray-400 truncate">@{{ u.apodo }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3.5 text-gray-600 text-xs whitespace-nowrap">{{ u.email }}</td>
                                        <td class="px-3 py-3.5 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold capitalize"
                                                :class="u.rol === 'creador' ? 'bg-brand/10 text-brand' : 'bg-gray-100 text-gray-700'">
                                                {{ u.rol }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3.5 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold capitalize"
                                                :class="{
                                                    'bg-green-100 text-green-700': u.estado === 'verificado',
                                                    'bg-amber-100 text-amber-700': u.estado === 'pendiente',
                                                    'bg-gray-100 text-gray-600': u.estado === 'incompleto',
                                                    'bg-red-100 text-red-700': u.estado === 'bloqueado'
                                                }">
                                                {{ u.estado }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3.5 text-gray-500 text-xs whitespace-nowrap">{{ formatDate(u.created_at) }}</td>
                                        <td class="pl-2 pr-6 py-3.5 whitespace-nowrap">
                                            <div class="flex justify-center items-center gap-1.5">
                                                <Link :href="route('admin.usuarios.show', u.id)" class="admin-table-action text-gray-600">
                                                    <i class="pi pi-eye text-xs"></i>
                                                </Link>
                                                <button @click="bloquearUsuario(u)" class="admin-table-action text-gray-600">
                                                    <i class="pi" :class="u.estado === 'bloqueado' ? 'pi-lock-open' : 'pi-lock'"></i>
                                                </button>
                                                <button @click="eliminarUsuario(u)" class="admin-table-action text-red-600 hover:bg-red-50">
                                                    <i class="pi pi-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!gestionUsuarios.data?.length">
                                        <td colspan="6" class="py-8 text-center text-gray-400 text-xs">Aún no hay usuarios registrados.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div v-if="gestionUsuarios.last_page > 1" class="border-t border-gray-100 px-6 py-4">
                        <Pagination :data="gestionUsuarios" />
                    </div>

                    <!-- Footer Tabla -->
                    <div class="border-t border-gray-100 py-3.5 text-center">
                        <Link :href="route('admin.usuarios.index')" class="text-brand font-medium hover:underline text-xs">
                            Ver todos los usuarios
                        </Link>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="min-w-0 admin-card overflow-hidden flex flex-col justify-between" style="grid-area:acciones">
                    <div>
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-bolt text-brand"></i> Acciones Rápidas</span>
                        </div>
                        <div class="space-y-3 p-4">
                            <Link :href="route('admin.usuarios.index')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl border hover:bg-gray-50 transition group" style="border-color:var(--line)">
                                <div class="admin-icon-circle" style="width:40px;height:40px">
                                    <i class="pi pi-lock text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold group-hover:text-brand transition" style="color:var(--ink)">Bloquear Usuario</p>
                                    <p class="text-xs" style="color:var(--muted)">Restringe el acceso de un usuario</p>
                                </div>
                            </Link>
                            <Link :href="route('admin.usuarios.index')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl border hover:bg-gray-50 transition group" style="border-color:var(--line)">
                                <div class="admin-icon-circle" style="width:40px;height:40px">
                                    <i class="pi pi-users text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold group-hover:text-brand transition" style="color:var(--ink)">Ver Usuarios</p>
                                    <p class="text-xs" style="color:var(--muted)">Consulta todos los usuarios registrados</p>
                                </div>
                            </Link>
                            <Link :href="route('admin.cobros.index')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl border hover:bg-gray-50 transition group" style="border-color:var(--line)">
                                <div class="admin-icon-circle" style="width:40px;height:40px">
                                    <i class="pi pi-dollar text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold group-hover:text-brand transition" style="color:var(--ink)">Ver Cobros</p>
                                    <p class="text-xs" style="color:var(--muted)">Revisa pagos y transacciones</p>
                                </div>
                            </Link>
                            <Link :href="route('admin.eventos.create')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl border hover:bg-gray-50 transition group" style="border-color:var(--line)">
                                <div class="admin-icon-circle" style="width:40px;height:40px">
                                    <i class="pi pi-calendar text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold group-hover:text-brand transition" style="color:var(--ink)">Crear Evento</p>
                                    <p class="text-xs" style="color:var(--muted)">Organiza un nuevo evento</p>
                                </div>
                            </Link>
                            <Link :href="route('admin.invitaciones.create')" class="flex items-center gap-3 px-3 py-2.5 rounded-xl border hover:bg-gray-50 transition group" style="border-color:var(--line)">
                                <div class="admin-icon-circle" style="width:40px;height:40px">
                                    <i class="pi pi-envelope text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold group-hover:text-brand transition" style="color:var(--ink)">Enviar Invitación</p>
                                    <p class="text-xs" style="color:var(--muted)">Invita usuarios a la plataforma</p>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Cobros Recientes -->
                <div class="min-w-0 admin-card overflow-hidden flex flex-col justify-between" style="grid-area:cobros">
                    <div>
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-dollar text-brand"></i> Cobros Recientes</span>
                            <Link :href="route('admin.cobros.index')" class="text-xs font-semibold text-brand hover:underline">Ver todos</Link>
                        </div>
                        <div class="space-y-4 p-6">
                            <div v-for="c in (cobrosRecientes || [])" :key="c.id" class="flex items-center justify-between text-sm py-1">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="admin-icon-circle text-xs" style="width:36px;height:36px">
                                        <i class="pi pi-dollar"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-xs truncate" style="color:var(--ink)">@{{ c.usuario?.apodo }}</p>
                                        <p class="text-[11px] truncate" style="color:var(--muted)">{{ c.concepto }}</p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 ml-2">
                                    <p class="font-bold text-xs" style="color:var(--ink)">{{ money(c.monto) }}</p>
                                    <p class="text-[10px]" style="color:var(--muted)">{{ c.tiempo }}</p>
                                </div>
                            </div>
                            <div v-if="!(cobrosRecientes || []).length" class="text-center py-8 text-xs" style="color:var(--muted)">
                                No hay cobros registrados aún.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Eventos Próximos -->
                <div class="min-w-0 admin-card overflow-hidden flex flex-col justify-between" style="grid-area:eventos">
                    <div>
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-calendar text-brand"></i> Eventos Próximos</span>
                            <Link :href="route('admin.eventos.index')" class="text-xs font-semibold text-brand hover:underline">Ver todos</Link>
                        </div>
                        <div class="space-y-3 p-6">
                            <div v-for="e in (eventosProximos || [])" :key="e.id" class="flex items-center justify-between p-2 rounded-xl hover:bg-gray-50 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 min-w-[40px] max-w-[40px] min-h-[40px] max-h-[40px] rounded-xl overflow-hidden shrink-0 bg-gray-100 border" style="border-color:var(--line)">
                                        <img :src="e.imagen || 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=100&q=80'" alt="" class="w-full h-full object-cover">
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-xs truncate" style="color:var(--ink)">{{ e.titulo }}</p>
                                        <p class="text-[10px] mt-0.5" style="color:var(--muted)">{{ e.fecha }}</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-semibold whitespace-nowrap ml-2"
                                    :class="eventoEstadosColores[e.estado] || 'bg-gray-100 text-gray-600'">
                                    {{ e.estado }}
                                </span>
                            </div>
                            <div v-if="!(eventosProximos || []).length" class="text-center py-6 text-xs" style="color:var(--muted)">
                                No hay eventos próximos.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actividad Reciente -->
                <div class="min-w-0 admin-card overflow-hidden flex flex-col justify-between" style="grid-area:actividad">
                    <div>
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-history text-brand"></i> Actividad Reciente</span>
                        </div>
                        <div class="space-y-3.5 p-6">
                            <div v-for="(act, i) in actividadReciente" :key="i" class="flex items-start gap-3">
                                <div class="admin-icon-circle text-xs" style="width:36px;height:36px;min-width:36px">
                                    <i class="pi text-xs font-semibold" :class="act.icon || 'pi-bell'"></i>
                                </div>
                                <div class="text-xs">
                                    <p class="leading-snug" style="color:var(--ink)">
                                        <span>{{ act.titulo || act.texto }}</span>
                                        <span v-if="act.destacado" class="font-semibold text-brand ml-1">{{ act.destacado }}</span>
                                    </p>
                                    <p class="text-[10px] mt-0.5 flex items-center gap-1" style="color:var(--muted)">
                                        <span>{{ act.hace_cuanto || formatDate(act.fecha) }}</span>
                                        <span v-if="act.fecha">• {{ formatTime(act.fecha) }}</span>
                                    </p>
                                </div>
                            </div>
                            <div v-if="!actividadReciente?.length" class="text-xs py-6 text-center" style="color:var(--muted)">
                                Sin actividad todavía.
                            </div>
                        </div>
                    </div>
                    <div v-if="actividadReciente?.length" class="border-t px-6 pt-3 mt-4 pb-4 text-center" style="border-color:var(--line)">
                        <button class="text-xs font-semibold text-brand hover:underline">Ver toda la actividad</button>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>