<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KpiCard from '@/Components/KpiCard.vue';
import Calendario from '@/Components/Calendario.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { useFormatters } from '@/composables/useFormatters';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    stats: Object,
    eventos: Object,
    filtros: Object,
    calendario: Object,
    proximosEventos: Array,
    estadisticas: Object,
});

const { confirm } = useConfirm();
const { formatDateTime } = useFormatters();
const toast = useToast();

const q = ref(props.filtros.q || '');
const estado = ref(props.filtros.estado || '');
const tipo = ref(props.filtros.tipo || '');
const periodoEstadisticas = ref(props.filtros.periodo_stats || 'mes');

let timeout = null;
function aplicarFiltros() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.eventos.index'), {
            q: q.value || undefined,
            estado: estado.value || undefined,
            tipo: tipo.value || undefined,
            periodo_stats: periodoEstadisticas.value !== 'mes' ? periodoEstadisticas.value : undefined,
        }, { preserveState: true, replace: true });
    }, 350);
}
watch([q, estado, tipo], aplicarFiltros);

function cambiarPeriodoStats() {
    router.get(route('admin.eventos.index'), {
        ...props.filtros,
        periodo_stats: periodoEstadisticas.value,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

const estadoColores = {
    en_vivo: 'bg-red-50 text-red-600 border border-red-200',
    programado: 'bg-orange-50 text-orange-600 border border-orange-200',
    completado: 'bg-emerald-50 text-emerald-600 border border-emerald-200',
    cancelado: 'bg-gray-50 text-gray-600 border border-gray-200',
    borrador: 'bg-yellow-50 text-yellow-600 border border-yellow-200',
};

const tipoColores = {
    vip: 'bg-rose-50 text-rose-600 border border-rose-100',
    general: 'bg-emerald-50 text-emerald-600 border border-emerald-100',
};

// --- Calendario: navegación de mes ---
function irMes(delta) {
    let mes = props.calendario.mes + delta;
    let anio = props.calendario.anio;
    if (mes > 12) { mes = 1; anio++; }
    if (mes < 1) { mes = 12; anio--; }
    router.get(route('admin.eventos.index'), { ...props.filtros, mes, anio }, { preserveState: true, preserveScroll: true, replace: true });
}

function irHoy() {
    router.get(route('admin.eventos.index'), { ...props.filtros, mes: undefined, anio: undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

// --- Anillo de Estadísticas de Eventos: gráfica real con conic-gradient ---
const anilloGradiente = computed(() => {
    const en = props.estadisticas?.enVivo ?? 0;
    const prog = props.estadisticas?.programados ?? 0;
    const comp = props.estadisticas?.completados ?? 0;
    const total = en + prog + comp;

    if (!total) return '#e5e7eb'; // gris: sin eventos en este periodo

    const finEnVivo = (en / total) * 360;
    const finProg = finEnVivo + (prog / total) * 360;

    return `conic-gradient(#ef4444 0deg ${finEnVivo}deg, #fb923c ${finEnVivo}deg ${finProg}deg, #10b981 ${finProg}deg 360deg)`;
});

async function eliminarEvento(evento) {
    const ok = await confirm(`Esto eliminará el evento "${evento.nombre}". Esta acción no se puede deshacer.`, {
        title: 'Eliminar evento',
        confirmLabel: 'Sí, eliminar',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('admin.eventos.destroy', evento.id), {
        preserveScroll: true,
        onSuccess: () => toast.success(`Evento "${evento.nombre}" eliminado correctamente.`),
        onError: () => toast.error('No se pudo eliminar el evento.'),
    });
}
</script>

<template>
    <Head title="Gestión de Eventos" />

    <AdminLayout>
        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4 space-y-6">

            <!-- Fila 1: KPIs -->
            <div class="admin-kpi-grid gap-6 w-full">
                <div class="min-w-0">
                    <KpiCard
                        label="Eventos Totales"
                        :value="stats?.total ?? 0"
                        icon="pi-calendar"
                        :hint="`+${stats?.nuevosEsteMes ?? 0} nuevos este mes`"
                    />
                </div>
                <div class="min-w-0">
                    <KpiCard
                        label="Eventos Próximos"
                        :value="stats?.proximos ?? 0"
                        icon="pi-clock"
                        :hint="`${stats?.total > 0 ? Math.round((stats.proximos / stats.total) * 100) : 0}% del total`"
                    />
                </div>
                <div class="min-w-0">
                    <KpiCard
                        label="Eventos en Vivo"
                        :value="stats?.enVivo ?? 0"
                        icon="pi-wifi"
                        hint="Ahora mismo"
                    />
                </div>
                <div class="min-w-0">
                    <KpiCard
                        label="Eventos Completados"
                        :value="stats?.completados ?? 0"
                        icon="pi-check-circle"
                        :hint="`${stats?.total > 0 ? Math.round((stats.completados / stats.total) * 100) : 0}% del total`"
                    />
                </div>
            </div>

            <!-- Fila 2: Tabla de Eventos + Calendario + Próximos Eventos + Estadísticas + Acciones Rápidas -->
            <div class="admin-eventos-grid gap-6 w-full">

                <!-- Tabla de Eventos -->
                <div class="min-w-0 admin-card overflow-hidden flex flex-col justify-between" style="grid-area:tabla">
                    <div class="flex flex-col flex-1 min-w-0">
                        <!-- Encabezado y Filtros -->
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-calendar text-brand"></i> Gestión de Eventos</span>
                            <Link :href="route('admin.eventos.create')" class="admin-btn-primary whitespace-nowrap" style="padding:0.4rem 0.85rem;font-size:0.75rem">
                                <i class="pi pi-plus text-xs"></i>
                                Crear Evento
                            </Link>
                        </div>
                        <div class="px-6 pt-4">
                            <p class="text-sm" style="color:var(--muted)">Administra y supervisa todos los eventos programados.</p>

                            <div class="flex flex-wrap lg:flex-nowrap items-center gap-3 py-3">
                                <div class="relative flex-1 min-w-[180px]">
                                    <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input v-model="q" type="text" placeholder="Buscar evento..." class="admin-input pl-10 py-2.5" />
                                </div>
                                <div class="w-full sm:w-auto min-w-[150px]">
                                    <select v-model="estado" class="admin-input py-2.5">
                                        <option value="">Todos los estados</option>
                                        <option value="publicado">Publicado</option>
                                        <option value="borrador">Borrador</option>
                                        <option value="cancelado">Cancelado</option>
                                        <option value="completo">Completo</option>
                                    </select>
                                </div>
                                <div class="w-full sm:w-auto min-w-[150px]">
                                    <select v-model="tipo" class="admin-input py-2.5">
                                        <option value="">Todos los tipos</option>
                                        <option value="vip">VIP</option>
                                        <option value="general">General</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div class="overflow-x-auto flex-1 flex flex-col">
                            <table class="min-w-full text-sm flex-1">
                                <thead class="bg-gray-50 border-y border-gray-200">
                                    <tr class="text-gray-600 uppercase tracking-wide text-xs">
                                        <th class="px-6 py-4 text-left">Evento</th>
                                        <th class="px-4 py-4 text-left">Tipo</th>
                                        <th class="px-4 py-4 text-left">Fecha y Hora</th>
                                        <th class="px-4 py-4 text-left">Ciudad</th>
                                        <th class="px-4 py-4 text-left">Estado</th>
                                        <th class="px-6 py-4 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="evento in eventos?.data" :key="evento.id" class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <img :src="evento.imagen || 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=100&q=80'"
                                                    style="width:40px;height:40px;flex:none;object-fit:cover"
                                                    class="rounded-lg border border-gray-200" />
                                                <span class="font-semibold text-gray-900 truncate" style="max-width:180px;display:inline-block" :title="evento.nombre">
                                                    {{ evento.nombre }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="px-2.5 py-1 rounded-md text-xs font-medium uppercase" :class="tipoColores[evento.tipo]">
                                                {{ evento.tipo }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-gray-500 whitespace-nowrap text-xs">
                                            {{ formatDateTime(evento.fecha) }} · {{ evento.hora_formateada }}
                                        </td>
                                        <td class="px-4 py-4 text-gray-600 text-xs">{{ evento.ciudad ?? '—' }}</td>
                                        <td class="px-4 py-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold capitalize" :class="estadoColores[evento.estado_display]">
                                                {{ evento.estado_display.replace('_', ' ') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-center gap-2">
                                                <Link :href="route('admin.eventos.show', evento.id)" class="admin-table-action text-gray-600">
                                                    <i class="pi pi-eye"></i>
                                                </Link>
                                                <Link :href="route('admin.eventos.edit', evento.id)" class="admin-table-action text-gray-600">
                                                    <i class="pi pi-pencil"></i>
                                                </Link>
                                                <button @click="eliminarEvento(evento)" class="admin-table-action text-red-600 hover:bg-red-50">
                                                    <i class="pi pi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!eventos?.data?.length" class="h-full">
                                        <td colspan="6" class="text-center text-gray-400 py-12 align-middle">No se encontraron eventos.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Footer / Paginación -->
                    <div class="border-t border-gray-200 px-6 py-4">
                        <Pagination :data="eventos" />
                    </div>
                    <div class="text-center pb-5">
                        <Link :href="route('admin.eventos.todos')" class="text-brand text-sm font-medium hover:underline">
                            Ver todos los eventos
                        </Link>
                    </div>
                </div>

                <!-- Calendario -->
                <div class="min-w-0 admin-card overflow-hidden flex flex-col justify-between" style="grid-area:calendario">
                    <div>
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-calendar text-brand"></i> Calendario de Eventos</span>
                        </div>
                        <div class="p-6">
                        <Calendario
                            :mes="calendario.mes"
                            :anio="calendario.anio"
                            :nombre-mes="calendario.nombreMes"
                            :dias="calendario.dias"
                            @cambiar-mes="irMes"
                            @hoy="irHoy"
                        />
                        </div>
                    </div>
                </div>

                <!-- Próximos Eventos -->
                <div class="min-w-0 admin-card overflow-hidden flex flex-col justify-between" style="grid-area:proximos">
                    <div>
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-clock text-brand"></i> Próximos Eventos</span>
                            <Link :href="route('admin.eventos.todos')" class="text-xs font-semibold text-brand hover:underline">Ver todos</Link>
                        </div>
                        <div class="space-y-3 p-6">
                            <div v-for="evento in proximosEventos" :key="evento.id" class="flex items-center justify-between gap-2 p-3 rounded-xl border border-gray-100 hover:bg-gray-50/50 transition">
                                <div class="flex items-center gap-3 min-w-0" style="flex:1 1 0%">
                                    <img :src="evento.imagen || 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=100&q=80'"
                                        style="width:40px;height:40px;flex:none;object-fit:cover" class="rounded-lg" />
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-gray-900 truncate" :title="evento.nombre">{{ evento.nombre }}</p>
                                        <div style="display:flex;align-items:center;gap:8px;white-space:nowrap" class="text-[10px] text-gray-400 mt-0.5">
                                            <span><i class="pi pi-calendar mr-0.5"></i>{{ evento.fecha }}</span>
                                            <span><i class="pi pi-clock mr-0.5"></i>{{ evento.hora_formateada }}</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold capitalize" :class="estadoColores[evento.estado_display]" style="flex:none;white-space:nowrap">
                                    {{ evento.estado_display.replace('_', ' ') }}
                                </span>
                            </div>
                            <div v-if="!proximosEventos?.length" class="text-center text-gray-400 text-xs py-6">
                                No hay próximos eventos.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas de Eventos -->
                <div class="min-w-0 admin-card overflow-hidden flex flex-col justify-between" style="grid-area:estadisticas">
                    <div>
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-chart-pie text-brand"></i> Estadísticas de Eventos</span>
                            <select v-model="periodoEstadisticas" @change="cambiarPeriodoStats" class="text-xs rounded-lg border-gray-200 bg-white py-1 px-2 focus:border-brand focus:ring-brand">
                                <option value="dia">Este día</option>
                                <option value="semana">Esta semana</option>
                                <option value="mes">Este mes</option>
                                <option value="anio">Este año</option>
                            </select>
                        </div>
                        <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                            <div class="flex flex-col items-center justify-center p-2">
                                <div :style="{ width: '80px', height: '80px', borderRadius: '9999px', background: anilloGradiente, display: 'flex', alignItems: 'center', justifyContent: 'center' }">
                                    <div style="width:56px;height:56px;background:#fff;border-radius:9999px;display:flex;flex-direction:column;align-items:center;justify-content:center">
                                        <span class="text-base font-extrabold text-gray-900 leading-none">{{ estadisticas?.total ?? 0 }}</span>
                                        <span class="text-[9px] text-gray-400 uppercase font-medium">Total</span>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-1.5 text-xs sm:col-span-2">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-2 text-gray-600"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> En vivo</span>
                                    <span class="font-bold text-gray-900">{{ estadisticas?.enVivo ?? 0 }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-2 text-gray-600"><span class="w-2.5 h-2.5 rounded-full bg-orange-400"></span> Programados</span>
                                    <span class="font-bold text-gray-900">{{ estadisticas?.programados ?? 0 }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-2 text-gray-600"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Completados</span>
                                    <span class="font-bold text-gray-900">{{ estadisticas?.completados ?? 0 }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mt-4 pt-3 border-t border-gray-100">
                            <div class="flex items-center gap-2.5 bg-gray-50/50 p-2.5 rounded-xl">
                                <div class="admin-icon-circle" style="width:32px;height:32px"><i class="pi pi-users text-xs"></i></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ estadisticas?.asistentesTotales ?? 0 }}</p>
                                    <p class="text-[10px] text-gray-400">Asistentes totales</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5 bg-gray-50/50 p-2.5 rounded-xl">
                                <div class="admin-icon-circle" style="width:32px;height:32px"><i class="pi pi-star text-xs"></i></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ estadisticas?.reservasTotales ?? 0 }}</p>
                                    <p class="text-[10px] text-gray-400">Reservas aprobadas</p>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="min-w-0 admin-card overflow-hidden" style="grid-area:acciones">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-bolt text-brand"></i> Acciones Rápidas</span>
                    </div>
                    <div class="space-y-2.5 p-6">
                            <Link :href="route('admin.eventos.create')" class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition group">
                                <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-calendar-plus text-xs"></i></div>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">Crear Evento</p>
                                    <p class="text-[10px] text-gray-400">Organiza un nuevo evento</p>
                                </div>
                            </Link>
                            <button type="button" @click="toast.success('Próximamente disponible.')" class="w-full flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition group text-left">
                                <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-file text-xs"></i></div>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">Plantillas de Eventos</p>
                                    <p class="text-[10px] text-gray-400">Usa plantillas prediseñadas</p>
                                </div>
                            </button>
                            <button type="button" @click="toast.success('Próximamente disponible.')" class="w-full flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition group text-left">
                                <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-folder text-xs"></i></div>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">Categorías de Eventos</p>
                                    <p class="text-[10px] text-gray-400">Gestiona categorías disponibles</p>
                                </div>
                            </button>
                            <button type="button" @click="toast.success('Próximamente disponible.')" class="w-full flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition group text-left">
                                <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-cog text-xs"></i></div>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">Configuración de Eventos</p>
                                    <p class="text-[10px] text-gray-400">Ajusta opciones y permisos</p>
                                </div>
                            </button>
                    </div>
                </div>

            </div>

        </div>
    </AdminLayout>
</template>