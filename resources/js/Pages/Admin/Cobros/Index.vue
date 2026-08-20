<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KpiCard from '@/Components/KpiCard.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Line, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    ArcElement,
    Tooltip,
    Filler,
} from 'chart.js';
import { useFormatters } from '@/composables/useFormatters';
import { useCobroAcciones } from '@/composables/useCobroAcciones';

ChartJS.register(LineElement, PointElement, LinearScale, CategoryScale, ArcElement, Tooltip, Filler);

const props = defineProps({
    stats: Object,
    resumen: Object,
    transacciones: Object,
    filtros: Object,
    ingresosPorDia: Array,
    categoriasResumen: Array,
    metodosPago: Array,
    pagosPendientes: Array,
});

const { money, formatDateTime } = useFormatters();
const { aprobar, reembolsar } = useCobroAcciones();

const q = ref(props.filtros.q || '');
const tipo = ref(props.filtros.tipo || '');
const desde = ref(props.filtros.desde || '');
const hasta = ref(props.filtros.hasta || '');
const periodoResumen = ref('mes');
const periodoIngresos = ref('mes');
const periodoTipos = ref('mes');

let timeout = null;
function aplicarFiltros() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.cobros.index'), {
            q: q.value || undefined,
            tipo: tipo.value || undefined,
            desde: desde.value || undefined,
            hasta: hasta.value || undefined,
            periodo_resumen: periodoResumen.value,
            periodo_ingresos: periodoIngresos.value,
            periodo_tipos: periodoTipos.value,
        }, { preserveState: true, replace: true });
    }, 350);
}
watch([q, tipo, desde, hasta], aplicarFiltros);

// Los 3 selectores de periodo disparan la misma recarga (con preserveState,
// así no se pierde el estado de los otros filtros ni el scroll).
function cambiarPeriodo() {
    router.get(route('admin.cobros.index'), {
        q: q.value || undefined,
        tipo: tipo.value || undefined,
        desde: desde.value || undefined,
        hasta: hasta.value || undefined,
        periodo_resumen: periodoResumen.value,
        periodo_ingresos: periodoIngresos.value,
        periodo_tipos: periodoTipos.value,
    }, { preserveState: true, preserveScroll: true, replace: true });
}
watch([periodoResumen, periodoIngresos, periodoTipos], cambiarPeriodo);

const estadoColores = {
    aprobada: 'bg-green-100 text-green-700',
    pendiente: 'bg-amber-100 text-amber-700',
    rechazada: 'bg-red-100 text-red-700',
    reembolsada: 'bg-gray-100 text-gray-600',
    retirada: 'bg-blue-100 text-blue-700',
};

// --- Gráfica de línea: ingresos por día ---
function formatCorto(v) {
    if (v >= 1000) {
        const k = v / 1000;
        return '$' + (Number.isInteger(k) ? k : k.toFixed(1)) + 'k';
    }
    return '$' + v;
}

const lineChartRef = ref(null);

const lineData = computed(() => ({
    labels: props.ingresosPorDia.map((d) => new Date(d.fecha).toLocaleDateString('es-MX', { day: '2-digit', month: 'short' })),
    datasets: [
        {
            label: 'Ingresos',
            data: props.ingresosPorDia.map((d) => d.total),
            borderColor: '#C81E3A',
            backgroundColor: (context) => {
                const { ctx, chartArea } = context.chart;
                if (!chartArea) return 'rgba(200, 30, 58, 0.08)';
                const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                gradient.addColorStop(0, 'rgba(200, 30, 58, 0.28)');
                gradient.addColorStop(1, 'rgba(200, 30, 58, 0)');
                return gradient;
            },
            fill: true,
            tension: 0.35,
            pointRadius: 3,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#C81E3A',
            pointBorderWidth: 2,
            pointHoverRadius: 5,
        },
    ],
}));
const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                title: (items) => {
                    const d = props.ingresosPorDia[items[0].dataIndex];
                    if (!d) return '';
                    const fecha = new Date(d.fecha).toLocaleDateString('es-MX', { day: 'numeric', month: 'long' });
                    return fecha.charAt(0).toUpperCase() + fecha.slice(1);
                },
                label: (ctx) => money(ctx.parsed.y),
            },
        },
    },
    scales: {
        x: {
            ticks: {
                autoSkip: false,
                maxRotation: 0,
                callback: function (value, index) {
                    const label = this.getLabelForValue(value);
                    const total = props.ingresosPorDia.length;
                    if (total <= 10) return label;
                    const d = props.ingresosPorDia[index];
                    if (!d) return label;
                    const dia = new Date(d.fecha).getDate();
                    return (dia === 1 || (dia % 5 === 0 && dia < 30)) ? label : '';
                },
            },
        },
        y: {
            beginAtZero: true,
            ticks: { callback: (v) => formatCorto(v) },
        },
    },
};

// --- Dona: Cobros / Reembolsos / Otros (colores fijos por categoría) ---
const categoriaColores = { cobros: '#10B981', reembolsos: '#C81E3A', otros: '#F5A623' };
const doughnutData = computed(() => ({
    labels: props.categoriasResumen.map((c) => c.label),
    datasets: [
        {
            data: props.categoriasResumen.map((c) => c.total),
            backgroundColor: props.categoriasResumen.map((c) => categoriaColores[c.id] || '#9CA3AF'),
            borderWidth: 0,
        },
    ],
}));
const totalCategorias = computed(() => props.categoriasResumen.reduce((sum, c) => sum + c.total, 0));
</script>

<template>

    <Head title="Cobros y Pagos" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Cobros y Pagos</template>


        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- Fila 1: KPIs -->
            <div class="admin-kpi-grid gap-6 mb-6 w-full">
                <div class="min-w-0">
                    <KpiCard label="Ingresos Totales" :value="money(stats.ingresosTotales)" icon="pi-dollar" />
                </div>
                <div class="min-w-0">
                    <KpiCard label="Cobros del Mes" :value="money(stats.cobrosDelMes)" icon="pi-calendar"
                        :hint="stats.cobrosVariacion !== null ? `${stats.cobrosVariacion >= 0 ? '+' : ''}${stats.cobrosVariacion}% vs mes anterior` : ''"
                        :hint-color="stats.cobrosVariacion >= 0 ? 'text-green-600' : 'text-red-500'" />
                </div>
                <div class="min-w-0">
                    <KpiCard label="Reembolsos del Mes" :value="money(stats.reembolsosDelMes)" icon="pi-replay" />
                </div>
                <div class="min-w-0">
                    <KpiCard label="Pagos Pendientes" :value="money(stats.pagosPendientesMonto)" icon="pi-clock"
                        :hint="`${stats.pagosPendientesCount} transacciones`" hint-color="text-gray-400" />
                </div>
            </div>
            <!-- Fila 2: Tabla de Transacciones + Resumen y Métodos de Pago -->
            <div class="admin-cobros-main-grid gap-6 mb-6 w-full">
                <!-- Tabla Transacciones -->
                <div
                    class="min-w-0 admin-card overflow-hidden flex flex-col justify-between" style="grid-area:tabla">
                    <div class="flex flex-col flex-1">
                        <!-- Encabezado -->
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-receipt text-brand"></i> Transacciones</span>
                        </div>
                        <p class="text-sm px-6 pt-4" style="color:var(--muted)">Administra los cobros y pagos registrados.</p>
                        <!-- Filtros -->
                        <div class="flex flex-wrap items-center gap-3 px-6 py-5">
                            <!-- Buscador -->
                            <div class="relative flex-1 min-w-[180px]">
                                <i
                                    class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input v-model="q" type="text" placeholder="Buscar..."
                                    class="w-full rounded-xl border-gray-300 pl-10 py-2.5 text-sm focus:border-brand focus:ring-brand">
                            </div>
                            <!-- Tipo -->
                            <div class="w-full sm:w-auto min-w-[150px]">
                                <select v-model="tipo"
                                    class="w-full rounded-xl border-gray-300 py-2.5 text-sm focus:border-brand focus:ring-brand">
                                    <option value="">Todos los tipos</option>
                                    <option value="suscripcion">Suscripción</option>
                                    <option value="compra_contenido">Compra de contenido</option>
                                    <option value="propina">Propina</option>
                                    <option value="retiro">Retiro</option>
                                </select>
                            </div>
                            <!-- Fechas -->
                            <div class="flex items-center gap-1.5 w-full sm:w-auto">
                                <input v-model="desde" type="date"
                                    class="w-full min-w-0 rounded-xl border-gray-300 py-2.5 px-2 text-xs xl:text-sm focus:border-brand focus:ring-brand">
                                <span class="text-gray-400">—</span>
                                <input v-model="hasta" type="date"
                                    class="w-full min-w-0 rounded-xl border-gray-300 py-2.5 px-2 text-xs xl:text-sm focus:border-brand focus:ring-brand">
                            </div>
                            <!-- Botón Exportar -->
                            <a :href="route('admin.cobros.exportar', { q: q || undefined, tipo: tipo || undefined, desde: desde || undefined, hasta: hasta || undefined })"
                                class="bg-brand hover:bg-brand-dark text-white rounded-xl px-5 py-2.5 font-medium text-sm flex items-center justify-center gap-2 whitespace-nowrap shrink-0">
                                <i class="pi pi-download"></i>
                                Exportar
                            </a>
                        </div>
                        <!-- Tabla en un contenedor con flex-1 h-full para estirarse al fondo -->
                        <div class="overflow-x-auto flex-1 flex flex-col">
                            <table class="min-w-full text-sm flex-1">
                                <thead class="bg-gray-50 border-y border-gray-200">
                                    <tr class="text-gray-600 uppercase tracking-wide text-xs">
                                        <th class="px-6 py-4 text-left">ID</th>
                                        <th class="px-4 py-4 text-left">Usuario</th>
                                        <th class="px-4 py-4 text-left">Tipo</th>
                                        <th class="px-4 py-4 text-left">Descripción</th>
                                        <th class="px-4 py-4 text-left">Monto</th>
                                        <th class="px-4 py-4 text-left">Fecha</th>
                                        <th class="px-4 py-4 text-left">Estado</th>
                                        <th class="px-6 py-4 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="t in transacciones.data" :key="t.id" class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">TRX-{{
                                            String(t.id).padStart(4, '0') }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <p class="font-semibold text-gray-800">{{ t.usuario?.nombre ?? '—' }}</p>
                                            <p class="text-xs text-gray-400">@{{ t.usuario?.apodo ?? '—' }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="font-semibold"
                                                :class="t.es_reembolso ? 'text-red-500' : 'text-green-600'">
                                                {{ t.es_reembolso ? 'Reembolso' : 'Cobro' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-gray-600">{{ t.tipo_nombre }}</td>
                                        <td class="px-4 py-4 font-semibold"
                                            :class="t.es_reembolso ? 'text-red-500' : 'text-gray-800'">
                                            {{ t.es_reembolso ? '-' : '' }}{{ money(t.monto) }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-gray-500">{{ formatDateTime(t.created_at) }}</td>
                                        <td class="px-4 py-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                                :class="estadoColores[t.estado]">
                                                {{ t.estado_nombre }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-center gap-2">
                                                <Link :href="route('admin.cobros.show', t.id)" title="Ver detalle"
                                                    class="admin-table-action text-gray-600">
                                                    <i class="pi pi-eye"></i>
                                                </Link>
                                                <template v-if="t.estado === 'pendiente'">
                                                    <button @click="aprobar(t)" title="Aprobar"
                                                        class="admin-table-action hover:bg-green-50 text-green-600">
                                                        <i class="pi pi-check"></i>
                                                    </button>
                                                    <button @click="reembolsar(t)" title="Reembolsar"
                                                        class="admin-table-action hover:bg-red-50 text-red-600">
                                                        <i class="pi pi-replay"></i>
                                                    </button>
                                                </template>
                                                <button v-else-if="t.estado === 'aprobada'" @click="reembolsar(t)" title="Reembolsar"
                                                    class="admin-table-action hover:bg-red-50 text-red-600">
                                                    <i class="pi pi-replay"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!transacciones.data?.length" class="h-full">
                                        <td colspan="8" class="text-center text-gray-400 py-12 align-middle">No se
                                            encontraron
                                            transacciones.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Footer -->
                    <div v-if="transacciones.last_page > 1" class="border-t border-gray-200 px-6 py-4">
                        <Pagination :data="transacciones" />
                    </div>
                    <div class="border-t border-gray-100 py-3.5 text-center">
                        <Link :href="route('admin.cobros.transacciones')" class="text-brand text-sm font-medium hover:underline">
                            Ver todas las transacciones
                        </Link>
                    </div>
                </div>
                <!-- Resumen de Cobros -->
                <div class="min-w-0 admin-card overflow-hidden" style="grid-area:resumen">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-wallet text-brand"></i> Resumen de Cobros</span>
                        <select v-model="periodoResumen" class="text-xs rounded-lg border-gray-300 bg-white px-2 py-1 focus:border-brand focus:ring-brand">
                            <option value="semana">Esta semana</option>
                            <option value="mes">Este mes</option>
                            <option value="anio">Este año</option>
                        </select>
                    </div>
                    <div class="space-y-3 text-sm p-6">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Cobros</span>
                            <span class="font-semibold text-gray-800">{{ money(resumen.cobrosDelMes) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Comisiones</span>
                            <span class="font-semibold text-red-500">-{{ money(resumen.comisionesDelMes) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Reembolsos</span>
                            <span class="font-semibold text-red-500">-{{ money(resumen.reembolsosDelMes) }}</span>
                        </div>
                        <div class="border-t border-gray-100 pt-3 flex justify-between">
                            <span class="font-semibold text-gray-700">Total Neto</span>
                            <span class="font-bold text-brand">{{ money(resumen.cobrosDelMes - resumen.comisionesDelMes - resumen.reembolsosDelMes) }}</span>
                        </div>
                    </div>
                </div>
                <!-- Métodos de Pago -->
                <div
                    class="min-w-0 admin-card overflow-hidden flex flex-col justify-between" style="grid-area:metodos">
                    <div>
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-credit-card text-brand"></i> Métodos de Pago</span>
                        </div>
                        <div class="space-y-5 p-6">
                            <div v-for="m in metodosPago" :key="m.metodo">
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="text-gray-600">{{ m.metodo_nombre }}</span>
                                    <span class="font-semibold text-gray-800">{{ m.porcentaje }}%</span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-brand rounded-full"
                                        :style="{ width: m.porcentaje + '%' }"></div>
                                </div>
                            </div>
                            <p v-if="!metodosPago?.length" class="text-sm text-gray-400 text-center py-6">Aún no hay
                                cobros
                                para mostrar.</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Fila 3: Gráficas y Pagos Pendientes -->
            <div class="admin-cobros-charts-grid gap-6 mt-6 w-full">
                <!-- 1. Ingresos -->
                <div
                    class="min-w-0 admin-card overflow-hidden flex flex-col justify-between" style="grid-area:ingresos">
                    <div>
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-chart-line text-brand"></i> Ingresos</span>
                            <select v-model="periodoIngresos"
                                class="text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 text-gray-500 bg-white focus:outline-none focus:border-brand">
                                <option value="semana">Esta semana</option>
                                <option value="mes">Este mes</option>
                                <option value="anio">Este año</option>
                            </select>
                        </div>
                        <div class="p-6">
                            <div style="height:240px" class="relative">
                                <template v-if="ingresosPorDia?.length">
                                    <Line ref="lineChartRef" :data="lineData" :options="lineOptions" />
                                </template>
                                <div v-else class="h-full flex items-center justify-center">
                                    <p class="text-gray-400 text-sm">Aún no hay ingresos registrados.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 2. Cobros y Reembolsos -->
                <div
                    class="min-w-0 admin-card overflow-hidden flex flex-col justify-between" style="grid-area:cobros">
                    <div>
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-chart-pie text-brand"></i> Cobros y Reembolsos</span>
                            <select v-model="periodoTipos"
                                class="text-xs border border-gray-200 rounded-lg px-2 py-1 text-gray-500 bg-white focus:outline-none focus:border-brand">
                                <option value="semana">Esta semana</option>
                                <option value="mes">Este mes</option>
                                <option value="anio">Este año</option>
                            </select>
                        </div>
                        <div class="p-6">
                            <!-- Gráfica Dona -->
                            <div v-if="totalCategorias > 0" class="relative my-2" style="height:160px">
                                <Doughnut :data="doughnutData"
                                    :options="{ maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => money(ctx.parsed) } } }, cutout: '65%' }" />
                                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                    <p class="text-[11px] text-gray-400 font-medium uppercase tracking-wider">Total</p>
                                    <p class="font-bold text-gray-800 text-base">{{ money(totalCategorias) }}</p>
                                </div>
                            </div>
                            <div v-else class="py-12 text-center">
                                <p class="text-gray-400 text-sm">Sin datos aún.</p>
                            </div>
                            <!-- Leyenda / Desglose -->
                            <ul v-if="totalCategorias > 0" class="mt-4 space-y-2">
                                <li v-for="c in categoriasResumen" :key="c.id" class="flex items-center justify-between text-xs">
                                    <span class="flex items-center gap-2 text-gray-600">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: categoriaColores[c.id] }"></span>
                                        {{ c.label }}
                                    </span>
                                    <div class="flex items-center gap-3">
                                        <span class="text-gray-400">{{ totalCategorias ? Math.round((c.total / totalCategorias) * 100) : 0 }}%</span>
                                        <span class="text-gray-800 font-semibold">{{ money(c.total) }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- 3. Pagos Pendientes -->
                <div
                    class="min-w-0 admin-card overflow-hidden flex flex-col justify-between" style="grid-area:pendientes">
                    <div>
                        <!-- Header con Badge estilo diseño -->
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-clock text-brand"></i> Pagos Pendientes</span>
                            <span v-if="pagosPendientes?.length"
                                class="bg-red-50 text-red-500 text-xs font-bold rounded-full flex items-center justify-center shrink-0"
                                style="width:24px;height:24px">
                                {{ pagosPendientes.length }}
                            </span>
                        </div>
                        <!-- Lista de elementos pendientes -->
                        <ul class="divide-y divide-gray-100 px-6">
                            <li v-for="p in pagosPendientes" :key="p.id"
                                class="py-3 flex items-center justify-between text-sm">
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-400 font-mono">TRX-{{ String(p.id).padStart(4, '0')
                                    }}</span>
                                    <span class="font-medium text-gray-800 text-xs mt-0.5">@{{ p.usuario?.apodo ??
                                        p.usuario?.nombre ?? '—' }}</span>
                                </div>
                                <span class="font-bold text-red-500 text-sm">{{ money(p.monto) }}</span>
                            </li>

                            <!-- Estado Vacío -->
                            <li v-if="!pagosPendientes?.length" class="py-12 text-center text-gray-400 text-sm">
                                Sin pagos pendientes.
                            </li>
                        </ul>
                    </div>
                    <!-- Enlace al pie -->
                    <div v-if="pagosPendientes?.length" class="pt-4 pb-6 border-t border-gray-100 text-center mx-6">
                        <a href="#" class="text-xs font-semibold text-red-500 hover:text-red-600 transition">
                            Ver todos los pendientes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>