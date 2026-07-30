<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
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
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

ChartJS.register(LineElement, PointElement, LinearScale, CategoryScale, ArcElement, Tooltip, Filler);

const props = defineProps({
    stats: Object,
    transacciones: Object,
    filtros: Object,
    ingresosPorDia: Array,
    tiposTotales: Array,
    metodosPago: Array,
    pagosPendientes: Array,
});

const toast = useToast();
const { confirm } = useConfirm();

const q = ref(props.filtros.q || '');
const tipo = ref(props.filtros.tipo || '');
const desde = ref(props.filtros.desde || '');
const hasta = ref(props.filtros.hasta || '');

let timeout = null;
function aplicarFiltros() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.cobros.index'), {
            q: q.value || undefined,
            tipo: tipo.value || undefined,
            desde: desde.value || undefined,
            hasta: hasta.value || undefined,
        }, { preserveState: true, replace: true });
    }, 350);
}
watch([q, tipo, desde, hasta], aplicarFiltros);

function money(v) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(v ?? 0);
}

function formatDate(v) {
    if (!v) return '—';
    return new Date(v).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
}

const tipoNombres = {
    suscripcion: 'Suscripción',
    compra_contenido: 'Compra de contenido',
    propina: 'Propina',
    retiro: 'Retiro',
};

const estadoColores = {
    aprobada: 'bg-green-100 text-green-700',
    pendiente: 'bg-amber-100 text-amber-700',
    rechazada: 'bg-red-100 text-red-700',
    reembolsada: 'bg-gray-100 text-gray-600',
    retirada: 'bg-blue-100 text-blue-700',
};

// --- Gráfica de línea: ingresos por día ---
const lineData = computed(() => ({
    labels: props.ingresosPorDia.map((d) => new Date(d.fecha).toLocaleDateString('es-MX', { day: '2-digit', month: 'short' })),
    datasets: [
        {
            label: 'Ingresos',
            data: props.ingresosPorDia.map((d) => d.total),
            borderColor: '#C81E3A',
            backgroundColor: 'rgba(200, 30, 58, 0.08)',
            fill: true,
            tension: 0.35,
            pointRadius: 2,
        },
    ],
}));
const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } },
};

// --- Dona: tipos de transacción ---
const doughnutColors = ['#C81E3A', '#E85C74', '#F4A9B5', '#FBD3D9'];
const doughnutData = computed(() => ({
    labels: props.tiposTotales.map((t) => tipoNombres[t.tipo] ?? t.tipo),
    datasets: [
        {
            data: props.tiposTotales.map((t) => t.total),
            backgroundColor: doughnutColors,
            borderWidth: 0,
        },
    ],
}));
const totalTipos = computed(() => props.tiposTotales.reduce((sum, t) => sum + t.total, 0));

async function aprobar(t) {
    const ok = await confirm(`Se marcará la transacción #${t.id} como aprobada.`, {
        title: 'Aprobar transacción',
        confirmLabel: 'Sí, aprobar',
        danger: false,
    });
    if (!ok) return;
    router.post(route('admin.cobros.aprobar', t.id), {}, { preserveScroll: true });
}

async function reembolsar(t) {
    const ok = await confirm(`Se reembolsará ${money(t.monto)} a @${t.usuario?.apodo ?? 'usuario'}.`, {
        title: 'Reembolsar transacción',
        confirmLabel: 'Sí, reembolsar',
        danger: true,
    });
    if (!ok) return;
    router.post(route('admin.cobros.reembolsar', t.id), {}, { preserveScroll: true });
}
</script>

<template>

    <Head title="Cobros y Pagos" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Cobros y Pagos</template>


        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- Fila 1: KPIs con layout de 4 columnas idéntico a las filas inferiores para mantener la alineación exacta -->
            <div class="flex flex-col lg:flex-row gap-6 mb-6 w-full">
                <!-- KPI 1 (25%) -->
                <div
                    class="w-full lg:w-1/4 bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 min-h-[120px] flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">
                            Ingresos Totales
                        </p>
                        <p class="text-2xl font-semibold text-gray-800 mt-1">
                            {{ money(stats.ingresosTotales) }}
                        </p>
                    </div>

                    <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0"
                        style="width:44px;height:44px">
                        <i class="pi pi-dollar text-lg"></i>
                    </div>
                </div>
                <!-- KPI 2 (25%) -->
                <div
                    class="w-full lg:w-1/4 bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 min-h-[120px] flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">
                            Cobros del Mes
                        </p>
                        <p class="text-2xl font-semibold text-gray-800 mt-1">
                            {{ money(stats.cobrosDelMes) }}
                        </p>
                        <p v-if="stats.cobrosVariacion !== null" class="text-xs mt-1"
                            :class="stats.cobrosVariacion >= 0 ? 'text-green-600' : 'text-red-500'">
                            {{ stats.cobrosVariacion >= 0 ? '+' : '' }}{{ stats.cobrosVariacion }}% vs mes anterior
                        </p>
                    </div>
                    <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0"
                        style="width:44px;height:44px">
                        <i class="pi pi-calendar text-lg"></i>
                    </div>
                </div>
                <!-- KPI 3 (25%) -->
                <div
                    class="w-full lg:w-1/4 bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 min-h-[120px] flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">
                            Reembolsos del Mes
                        </p>
                        <p class="text-2xl font-semibold text-gray-800 mt-1">
                            {{ money(stats.reembolsosDelMes) }}
                        </p>
                    </div>

                    <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0"
                        style="width:44px;height:44px">
                        <i class="pi pi-replay text-lg"></i>
                    </div>
                </div>
                <!-- KPI 4 (25%) -->
                <div
                    class="w-full lg:w-1/4 bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 min-h-[120px] flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">
                            Pagos Pendientes
                        </p>
                        <p class="text-2xl font-semibold text-gray-800 mt-1">
                            {{ money(stats.pagosPendientesMonto) }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ stats.pagosPendientesCount }} transacciones
                        </p>
                    </div>
                    <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0"
                        style="width:44px;height:44px">
                        <i class="pi pi-clock text-lg"></i>
                    </div>
                </div>
            </div>
            <!-- Fila 2: Tabla de Transacciones + Resumen y Métodos de Pago -->
            <div class="flex flex-col lg:flex-row items-stretch gap-6 mb-6 w-full">
                <!-- Columna Izquierda: Tabla Transacciones (75%) -->
                <div
                    class="w-full lg:w-3/4 bg-white rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between self-stretch">
                    <div class="flex flex-col flex-1">
                        <!-- Encabezado -->
                        <div class="px-6 pt-6">
                            <h2 class="text-xl font-semibold text-gray-900">Transacciones</h2>
                            <p class="text-sm text-gray-500 mt-1">Administra los cobros y pagos registrados.</p>
                        </div>
                        <!-- Filtros -->
                        <div class="flex flex-wrap lg:flex-nowrap items-center gap-3 px-6 py-5">
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
                                        <td class="px-4 py-4 whitespace-nowrap text-gray-500">{{
                                            formatDate(t.created_at) }}
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                                :class="estadoColores[t.estado]">
                                                {{ t.estado_nombre }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-center gap-2">
                                                <button
                                                    class="w-9 h-9 rounded-lg border border-gray-200 hover:bg-gray-100 flex items-center justify-center">
                                                    <i class="pi pi-eye"></i>
                                                </button>
                                                <template v-if="t.estado === 'pendiente'">
                                                    <button @click="aprobar(t)"
                                                        class="w-9 h-9 rounded-lg border border-gray-200 hover:bg-green-50 text-green-600 flex items-center justify-center">
                                                        <i class="pi pi-check"></i>
                                                    </button>
                                                    <button @click="reembolsar(t)"
                                                        class="w-9 h-9 rounded-lg border border-gray-200 hover:bg-red-50 text-red-600 flex items-center justify-center">
                                                        <i class="pi pi-replay"></i>
                                                    </button>
                                                </template>
                                                <button v-else-if="t.estado === 'aprobada'" @click="reembolsar(t)"
                                                    class="w-9 h-9 rounded-lg border border-gray-200 hover:bg-red-50 text-red-600 flex items-center justify-center">
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
                    <div v-if="transacciones.last_page > 1"
                        class="border-t border-gray-200 px-6 py-4 flex items-center justify-between">
                        <p class="text-sm text-gray-500">Mostrando {{ transacciones.from }}–{{ transacciones.to }} de {{
                            transacciones.total }}</p>
                        <div class="flex gap-1">
                            <template v-for="(link, i) in transacciones.links" :key="i">
                                <Link v-if="link.url" :href="link.url" preserve-scroll preserve-state
                                    v-html="link.label" class="px-3 py-2 rounded-lg text-sm"
                                    :class="link.active ? 'bg-brand text-white' : 'hover:bg-gray-100 text-gray-600'" />
                                <span v-else class="px-3 py-2 text-gray-300" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </div>
                <!-- Columna Derecha: Resumen + Métodos de Pago (25%) -->
                <div class="w-full lg:w-1/4 flex flex-col gap-6 self-stretch">
                    <!-- Resumen -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <div class="flex items-center justify-between mb-5">
                            <h2 class="text-lg font-semibold text-gray-900">Resumen de Cobros</h2>
                            <select class="text-xs rounded-lg border-gray-300 focus:border-brand focus:ring-brand">
                                <option>Este mes</option>
                            </select>
                        </div>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Total Cobros</span>
                                <span class="font-semibold text-gray-800">{{ money(stats.cobrosDelMes) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Comisiones</span>
                                <span class="font-semibold text-red-500">-{{ money(stats.comisionesDelMes) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Reembolsos</span>
                                <span class="font-semibold text-red-500">-{{ money(stats.reembolsosDelMes) }}</span>
                            </div>
                            <div class="border-t border-gray-100 pt-3 flex justify-between">
                                <span class="font-semibold text-gray-700">Total Neto</span>
                                <span class="font-bold text-brand">{{ money(stats.cobrosDelMes - stats.comisionesDelMes
                                    -
                                    stats.reembolsosDelMes) }}</span>
                            </div>
                        </div>
                    </div>
                    <!-- Métodos de Pago -->
                    <div
                        class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-5">Métodos de Pago</h2>
                            <div class="space-y-5">
                                <div v-for="m in metodosPago" :key="m.metodo">
                                    <div class="flex items-center justify-between text-sm mb-2">
                                        <span class="text-gray-600 capitalize">{{ m.metodo.replace('_', ' ') }}</span>
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
            </div>
            <!-- Fila 3: Gráficas y Pagos Pendientes (Flexbox con ancho exacto) -->
            <div class="flex flex-col lg:flex-row gap-6 mt-6 w-full">
                <!-- 1. Ingresos (50% de ancho) -->
                <div
                    class="w-full lg:w-1/2 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-gray-800 text-lg">Ingresos</h2>
                            <select
                                class="text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 text-gray-500 focus:outline-none focus:border-brand">
                                <option>Este mes</option>
                            </select>
                        </div>
                        <div style="height:240px">
                            <Line v-if="ingresosPorDia?.length" :data="lineData" :options="lineOptions" />
                            <div v-else class="h-full flex items-center justify-center">
                                <p class="text-gray-400 text-sm">Aún no hay ingresos registrados.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 2. Tipos de Transacción (25% de ancho) -->
                <div
                    class="w-full lg:w-1/4 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-gray-800 text-lg">Tipos de Transacción</h2>
                            <select
                                class="text-xs border border-gray-200 rounded-lg px-2 py-1 text-gray-500 focus:outline-none focus:border-brand">
                                <option>Este mes</option>
                            </select>
                        </div>
                        <!-- Gráfica Dona -->
                        <div v-if="tiposTotales?.length" class="relative my-2" style="height:160px">
                            <Doughnut :data="doughnutData"
                                :options="{ maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '75%' }" />
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <p class="text-[11px] text-gray-400 font-medium uppercase tracking-wider">Total</p>
                                <p class="font-bold text-gray-800 text-base">{{ money(totalTipos) }}</p>
                            </div>
                        </div>
                        <div v-else class="py-12 text-center">
                            <p class="text-gray-400 text-sm">Sin datos aún.</p>
                        </div>
                        <!-- Leyenda / Desglose -->
                        <ul v-if="tiposTotales?.length" class="mt-4 space-y-2">
                            <li v-for="(t, i) in tiposTotales" :key="t.tipo"
                                class="flex items-center justify-between text-xs">
                                <span class="flex items-center gap-2 text-gray-600">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0"
                                        :style="{ backgroundColor: doughnutColors[i % doughnutColors.length] }"></span>
                                    {{ tipoNombres[t.tipo] ?? t.tipo }}
                                </span>
                                <div class="flex items-center gap-3">
                                    <span class="text-gray-400">{{ totalTipos ? Math.round((t.total / totalTipos) * 100)
                                        : 0
                                    }}%</span>
                                    <span class="text-gray-800 font-semibold">{{ money(t.total) }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- 3. Pagos Pendientes (25% de ancho) -->
                <div
                    class="w-full lg:w-1/4 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <!-- Header con Badge estilo diseño -->
                        <div class="flex items-center justify-between mb-5">
                            <h2 class="font-semibold text-gray-800 text-lg">Pagos Pendientes</h2>
                            <span v-if="pagosPendientes?.length"
                                class="bg-red-50 text-red-500 text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">
                                {{ pagosPendientes.length }}
                            </span>
                        </div>
                        <!-- Lista de elementos pendientes -->
                        <ul class="divide-y divide-gray-100">
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
                    <div v-if="pagosPendientes?.length" class="pt-4 border-t border-gray-100 text-center">
                        <a href="#" class="text-xs font-semibold text-red-500 hover:text-red-600 transition">
                            Ver todos los pendientes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>