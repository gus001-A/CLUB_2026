<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    stats: Object,
    eventos: Object,
    filtros: Object,
    calendario: Object,
    proximosEventos: Array,
    estadisticas: Object,
});

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

function formatDate(v) {
    if (!v) return '—';
    return new Date(v).toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

const estadoColores = {
    'en_vivo': 'bg-red-50 text-red-600 border border-red-200',
    'programado': 'bg-orange-50 text-orange-600 border border-orange-200',
    'completado': 'bg-emerald-50 text-emerald-600 border border-emerald-200',
    'cancelado': 'bg-gray-50 text-gray-600 border border-gray-200',
    'borrador': 'bg-yellow-50 text-yellow-600 border border-yellow-200',
};

const tipoColores = {
    'vip': 'bg-rose-50 text-rose-600 border border-rose-100',
    'general': 'bg-emerald-50 text-emerald-600 border border-emerald-100',
};
</script>

<template>
    <Head title="Gestión de Eventos" />

    <AdminLayout>
        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4 space-y-6">

            <!-- Fila 1: KPIs (4 columnas) -->
            <div class="flex flex-col lg:flex-row gap-6 mb-6 w-full">
                <!-- KPI 1 -->
                <div class="w-full lg:w-1/4 bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 min-h-[120px] flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">Eventos Totales</p>
                        <p class="text-2xl font-semibold text-gray-800 mt-1">
                            {{ stats?.total ?? 0 }}
                        </p>
                        <p class="text-xs text-red-600 mt-1 font-medium">
                            +{{ stats?.nuevosEsteMes ?? 0 }} nuevos este mes
                        </p>
                    </div>
                    <div class="rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0" style="width:44px;height:44px">
                        <i class="pi pi-calendar text-lg"></i>
                    </div>
                </div>

                <!-- KPI 2 -->
                <div class="w-full lg:w-1/4 bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 min-h-[120px] flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">Eventos Próximos</p>
                        <p class="text-2xl font-semibold text-gray-800 mt-1">
                            {{ stats?.proximos ?? 0 }}
                        </p>
                        <p class="text-xs text-red-600 mt-1 font-medium">
                            {{ stats?.total > 0 ? Math.round((stats.proximos / stats.total) * 100) : 0 }}% del total
                        </p>
                    </div>
                    <div class="rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0" style="width:44px;height:44px">
                        <i class="pi pi-clock text-lg"></i>
                    </div>
                </div>

                <!-- KPI 3 -->
                <div class="w-full lg:w-1/4 bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 min-h-[120px] flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">Eventos en Vivo</p>
                        <p class="text-2xl font-semibold text-gray-800 mt-1">
                            {{ stats?.enVivo ?? 0 }}
                        </p>
                        <p class="text-xs text-red-600 mt-1 font-medium">
                            Ahora mismo
                        </p>
                    </div>
                    <div class="rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0" style="width:44px;height:44px">
                        <i class="pi pi-wifi text-lg"></i>
                    </div>
                </div>

                <!-- KPI 4 -->
                <div class="w-full lg:w-1/4 bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 min-h-[120px] flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">Eventos Completados</p>
                        <p class="text-2xl font-semibold text-gray-800 mt-1">
                            {{ stats?.completados ?? 0 }}
                        </p>
                        <p class="text-xs text-red-600 mt-1 font-medium">
                            {{ stats?.total > 0 ? Math.round((stats.completados / stats.total) * 100) : 0 }}% del total
                        </p>
                    </div>
                    <div class="rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0" style="width:44px;height:44px">
                        <i class="pi pi-check-circle text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- Fila 2: Tabla de Eventos (75%) + Calendario (25%) -->
            <div class="flex flex-col lg:flex-row items-stretch gap-6 mb-6 w-full">
                
                <!-- Columna Izquierda: Tabla de Eventos (75%) -->
                <div class="w-full lg:w-3/4 bg-white rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between self-stretch">
                    <div class="flex flex-col flex-1">
                        <!-- Encabezado y Filtros Superiores -->
                        <div class="px-6 pt-6">
                            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">Gestión de Eventos</h2>
                                    <p class="text-sm text-gray-500 mt-1">Administra y supervisa todos los eventos programados.</p>
                                </div>
                                <Link :href="route('admin.eventos.create')"
                                    class="bg-red-600 hover:bg-red-700 text-white rounded-xl px-5 py-2.5 font-medium text-sm flex items-center justify-center gap-2 whitespace-nowrap shadow-sm">
                                    <i class="pi pi-plus text-xs"></i>
                                    Crear Evento
                                </Link>
                            </div>
                            
                            <div class="flex flex-wrap lg:flex-nowrap items-center gap-3 py-3">
                                <!-- Buscador -->
                                <div class="relative flex-1 min-w-[180px]">
                                    <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input v-model="q" type="text" placeholder="Buscar evento..."
                                        class="w-full rounded-xl border-gray-300 pl-10 py-2.5 text-sm focus:border-red-500 focus:ring-red-500">
                                </div>

                                <!-- Filtro Estado -->
                                <div class="w-full sm:w-auto min-w-[150px]">
                                    <select v-model="estado"
                                        class="w-full rounded-xl border-gray-300 py-2.5 text-sm focus:border-red-500 focus:ring-red-500">
                                        <option value="">Todos los estados</option>
                                        <option value="publicado">Publicado</option>
                                        <option value="borrador">Borrador</option>
                                        <option value="cancelado">Cancelado</option>
                                        <option value="completo">Completo</option>
                                    </select>
                                </div>

                                <!-- Filtro Tipo -->
                                <div class="w-full sm:w-auto min-w-[150px]">
                                    <select v-model="tipo"
                                        class="w-full rounded-xl border-gray-300 py-2.5 text-sm focus:border-red-500 focus:ring-red-500">
                                        <option value="">Todos los tipos</option>
                                        <option value="vip">VIP</option>
                                        <option value="general">General</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Datos -->
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
                                            <div class="flex items-center gap-3">
                                                <img :src="evento.imagen || 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=100&q=80'" 
                                                    class="w-10 h-10 rounded-lg object-cover border border-gray-200 shrink-0" />
                                                <span class="font-semibold text-gray-900">{{ evento.nombre }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="px-2.5 py-1 rounded-md text-xs font-medium uppercase" :class="tipoColores[evento.tipo]">
                                                {{ evento.tipo }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-gray-500 whitespace-nowrap text-xs">{{ formatDate(evento.fecha) }}</td>
                                        <td class="px-4 py-4 text-gray-600 text-xs">{{ evento.ciudad ?? '—' }}</td>
                                        <td class="px-4 py-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold capitalize" :class="estadoColores[evento.estado_display]">
                                                {{ evento.estado_display.replace('_', ' ') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-center gap-2">
                                                <button class="w-9 h-9 rounded-lg border border-gray-200 hover:bg-gray-100 flex items-center justify-center text-gray-600">
                                                    <i class="pi pi-eye"></i>
                                                </button>
                                                <button class="w-9 h-9 rounded-lg border border-gray-200 hover:bg-gray-100 flex items-center justify-center text-gray-600">
                                                    <i class="pi pi-pencil"></i>
                                                </button>
                                                <button class="w-9 h-9 rounded-lg border border-gray-200 hover:bg-gray-100 flex items-center justify-center text-gray-600">
                                                    <i class="pi pi-ellipsis-v"></i>
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
                    <div v-if="eventos?.last_page > 1" class="border-t border-gray-200 px-6 py-4 flex items-center justify-between">
                        <p class="text-sm text-gray-500">Mostrando {{ eventos.from }}–{{ eventos.to }} de {{ eventos.total }}</p>
                        <div class="flex gap-1">
                            <template v-for="(link, i) in eventos.links" :key="i">
                                <Link v-if="link.url" :href="link.url" preserve-scroll preserve-state
                                    v-html="link.label" class="px-3 py-2 rounded-lg text-sm"
                                    :class="link.active ? 'bg-red-600 text-white' : 'hover:bg-gray-100 text-gray-600'" />
                                <span v-else class="px-3 py-2 text-gray-300" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Tarjeta Calendario (25%) -->
                <div class="w-full lg:w-1/4 flex flex-col gap-6 self-stretch">
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-bold text-gray-900 text-sm">Calendario de Eventos</h3>
                                <span class="text-xs font-medium px-2 text-gray-700 capitalize">{{ calendario?.nombreMes }}</span>
                            </div>
                            <div class="grid grid-cols-7 text-center text-[11px] font-semibold text-gray-400 mb-2">
                                <span>DOM</span><span>LUN</span><span>MAR</span><span>MIÉ</span><span>JUE</span><span>VIE</span><span>SÁB</span>
                            </div>
                            <!-- Renderizado simple de días del mes actual -->
                            <div class="grid grid-cols-7 text-center text-xs gap-y-2 text-gray-600">
                                <template v-for="dia in 31" :key="dia">
                                    <span class="py-1" :class="{
                                        'bg-red-600 text-white rounded-full font-bold': calendario?.dias?.[dia]
                                    }">
                                        {{ dia <= 31 ? dia : '' }}
                                    </span>
                                </template>
                            </div>
                        </div>
                        <div class="flex items-center justify-center gap-4 mt-4 pt-3 border-t border-gray-100 text-[11px] text-gray-500">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-orange-400"></span> Programado</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-500"></span> En vivo</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Completado</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Fila 3: Próximos Eventos (33%) + Estadísticas (39%) + Acciones Rápidas (28%) -->
            <div class="flex flex-col lg:flex-row gap-6 items-stretch">
                
                <!-- Próximos Eventos -->
                <div class="w-full lg:w-[33%] bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-gray-900 text-sm">Próximos Eventos</h3>
                            <button class="text-xs font-semibold text-red-600 hover:text-red-700">Ver todos</button>
                        </div>
                        <div class="space-y-3">
                            <div v-for="evento in proximosEventos" :key="evento.id" class="flex items-center justify-between p-3 rounded-xl border border-gray-100 hover:bg-gray-50/50 transition">
                                <div class="flex items-center gap-3">
                                    <img :src="evento.imagen || 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=100&q=80'" class="w-10 h-10 rounded-lg object-cover" />
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">{{ evento.nombre }}</p>
                                        <div class="flex items-center gap-2 text-[10px] text-gray-400 mt-0.5">
                                            <span><i class="pi pi-calendar mr-0.5"></i> {{ evento.fecha }}</span>
                                            <span><i class="pi pi-clock mr-0.5"></i> {{ evento.hora_formateada }}</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold capitalize bg-red-50 text-red-600 border border-red-200 shrink-0">{{ evento.estado_display.replace('_', ' ') }}</span>
                            </div>
                            <div v-if="!proximosEventos?.length" class="text-center text-gray-400 text-xs py-6">
                                No hay próximos eventos.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas de Eventos -->
                <div class="w-full lg:w-[39%] bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-gray-900 text-sm">Estadísticas de Eventos</h3>
                            <select v-model="periodoEstadisticas" @change="cambiarPeriodoStats" class="text-xs rounded-lg border-gray-200 bg-gray-50 py-1 px-2 focus:border-red-500 focus:ring-red-500">
                                <option value="dia">Este día</option>
                                <option value="semana">Esta semana</option>
                                <option value="mes">Este mes</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                            <div class="flex flex-col items-center justify-center p-2">
                                <div class="w-20 h-20 rounded-full border-8 border-emerald-500 border-t-red-500 border-r-orange-400 flex flex-col items-center justify-center text-center">
                                    <span class="text-base font-extrabold text-gray-900 leading-none">{{ estadisticas?.total ?? 0 }}</span>
                                    <span class="text-[9px] text-gray-400 uppercase font-medium">Total</span>
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
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-4 pt-3 border-t border-gray-100">
                        <div class="flex items-center gap-2.5 bg-gray-50/50 p-2.5 rounded-xl">
                            <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center shrink-0"><i class="pi pi-users text-xs"></i></div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ estadisticas?.asistentesTotales ?? 0 }}</p>
                                <p class="text-[10px] text-gray-400">Asistentes totales</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 bg-gray-50/50 p-2.5 rounded-xl">
                            <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center shrink-0"><i class="pi pi-star text-xs"></i></div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ estadisticas?.reservasTotales ?? 0 }}</p>
                                <p class="text-[10px] text-gray-400">Reservas aprobadas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="w-full lg:w-[28%] bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm mb-3">Acciones Rápidas</h3>
                        <div class="space-y-2.5">
                            <Link :href="route('admin.eventos.create')" class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition cursor-pointer group">
                                <div class="rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0" style="width:40px;height:40px"><i class="pi pi-calendar-plus text-xs"></i></div>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">Crear Evento</p>
                                    <p class="text-[10px] text-gray-400">Organiza un nuevo evento</p>
                                </div>
                            </Link>
                            <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition cursor-pointer group">
                                <div class="rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0" style="width:40px;height:40px"><i class="pi pi-file text-xs"></i></div>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">Plantillas de Eventos</p>
                                    <p class="text-[10px] text-gray-400">Usa plantillas prediseñadas</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition cursor-pointer group">
                                <div class="rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0" style="width:40px;height:40px"><i class="pi pi-folder text-xs"></i></div>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">Categorías de Eventos</p>
                                    <p class="text-[10px] text-gray-400">Gestiona categorías disponibles</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition cursor-pointer group">
                                <div class="rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0" style="width:40px;height:40px"><i class="pi pi-cog text-xs"></i></div>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">Configuración de Eventos</p>
                                    <p class="text-[10px] text-gray-400">Ajusta opciones y permisos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </AdminLayout>
</template>