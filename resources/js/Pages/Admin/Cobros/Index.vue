<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
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

const badgeEstado = { aprobada: 'admin-cobros-badge--aprobada', pendiente: 'admin-cobros-badge--pendiente', rechazada: 'admin-cobros-badge--rechazada', reembolsada: 'admin-cobros-badge--reembolsada', retirada: 'admin-cobros-badge--retirada' };

// --- KPIs con el mismo lenguaje visual que Productos/Dashboard ---
const kpis = computed(() => [
    {
        label: 'Ingresos Totales', value: money(props.stats.ingresosTotales), icon: 'pi-dollar',
        color: '#059669', iconBg: '#D1FAE5', gradient: 'linear-gradient(135deg, #059669, #047857)', hint: null,
    },
    {
        label: 'Cobros del Mes', value: money(props.stats.cobrosDelMes), icon: 'pi-calendar',
        color: '#2563EB', iconBg: '#DBEAFE', gradient: 'linear-gradient(135deg, #2563EB, #1D4ED8)',
        hint: props.stats.cobrosVariacion !== null ? `${props.stats.cobrosVariacion >= 0 ? '+' : ''}${props.stats.cobrosVariacion}% vs mes anterior` : null,
        hintColor: props.stats.cobrosVariacion >= 0 ? '#059669' : '#DC2626',
    },
    {
        label: 'Reembolsos del Mes', value: money(props.stats.reembolsosDelMes), icon: 'pi-replay',
        color: '#DC2626', iconBg: '#FEE2E2', gradient: 'linear-gradient(135deg, #DC2626, #B91C1C)', hint: null,
    },
    {
        label: 'Pagos Pendientes', value: money(props.stats.pagosPendientesMonto), icon: 'pi-clock',
        color: '#D97706', iconBg: '#FEF3C7', gradient: 'linear-gradient(135deg, #D97706, #B45309)',
        hint: `${props.stats.pagosPendientesCount} transacciones`, hintColor: '#8A8481',
    },
]);

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

// --- Dona: Cobros / Reembolsos / Otros ---
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

        <div class="admin-cobros-page">
            <!-- Fila 1: KPIs -->
            <div class="admin-cobros-kpi-grid">
                <div v-for="kpi in kpis" :key="kpi.label" class="admin-cobros-kpi-card">
                    <div class="admin-cobros-kpi-card__icon" :style="{ background: kpi.iconBg, color: kpi.color }">
                        <i class="pi" :class="kpi.icon"></i>
                    </div>
                    <div class="admin-cobros-kpi-card__content">
                        <span class="admin-cobros-kpi-card__label">{{ kpi.label }}</span>
                        <span class="admin-cobros-kpi-card__value" :style="{ color: kpi.color }">{{ kpi.value }}</span>
                        <span v-if="kpi.hint" class="admin-cobros-kpi-card__hint" :style="{ color: kpi.hintColor || '#8A8481' }">{{ kpi.hint }}</span>
                    </div>
                    <div class="admin-cobros-kpi-card__bar" :style="{ background: kpi.gradient }"></div>
                </div>
            </div>

            <!-- Fila 2: Tabla de Transacciones + Resumen + Métodos de Pago -->
            <div class="admin-cobros-main-grid gap-6 mb-6 w-full">
                <!-- Tabla de Transacciones -->
                <div class="admin-cobros-card min-w-0" style="grid-area:tabla">
                    <div class="flex flex-col flex-1">
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-receipt"></i></div>
                                <div>
                                    <h3>Transacciones</h3>
                                    <p class="admin-cobros-header-subtitle">Administra los cobros y pagos registrados</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link :href="route('admin.cobros.transacciones')" class="admin-btn-secondary">
                                    <i class="pi pi-list"></i> Ver todas
                                </Link>
                                <a :href="route('admin.cobros.exportar', { q: q || undefined, tipo: tipo || undefined, desde: desde || undefined, hasta: hasta || undefined })" class="admin-cobros-btn-primary">
                                    <i class="pi pi-download"></i> Exportar
                                </a>
                            </div>
                        </div>

                        <!-- Filtros -->
                        <div class="admin-cobros-filters">
                            <div class="admin-cobros-filters__search">
                                <i class="pi pi-search"></i>
                                <input v-model="q" type="text" placeholder="Buscar..." />
                            </div>
                            <select v-model="tipo">
                                <option value="">Todos los tipos</option>
                                <option value="suscripcion">Suscripción</option>
                                <option value="compra_contenido">Compra de contenido</option>
                                <option value="propina">Propina</option>
                                <option value="retiro">Retiro</option>
                            </select>
                            <div class="flex items-center gap-1.5">
                                <input v-model="desde" type="date" />
                                <span class="text-gray-400">—</span>
                                <input v-model="hasta" type="date" />
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div class="overflow-x-auto flex-1 flex flex-col">
                            <table class="admin-cobros-table flex-1">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th>Monto</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="t in transacciones.data" :key="t.id">
                                        <td class="admin-cobros-tx-id whitespace-nowrap">TRX-{{ String(t.id).padStart(4, '0') }}</td>
                                        <td class="whitespace-nowrap">
                                            <p class="admin-cobros-tx-name">{{ t.usuario?.nombre ?? '—' }}</p>
                                            <p class="admin-cobros-tx-handle">@{{ t.usuario?.apodo ?? '—' }}</p>
                                        </td>
                                        <td>
                                            <span class="admin-cobros-monto" :class="t.es_reembolso ? 'admin-cobros-monto--egreso' : 'admin-cobros-monto--ingreso'">
                                                {{ t.es_reembolso ? 'Reembolso' : 'Cobro' }}
                                            </span>
                                        </td>
                                        <td class="text-gray-600">{{ t.tipo_nombre }}</td>
                                        <td class="admin-cobros-monto" :class="t.es_reembolso ? 'admin-cobros-monto--egreso' : ''" :style="!t.es_reembolso ? 'color:var(--ink)' : ''">
                                            {{ t.es_reembolso ? '-' : '' }}{{ money(t.monto) }}
                                        </td>
                                        <td class="whitespace-nowrap text-gray-500">{{ formatDateTime(t.created_at) }}</td>
                                        <td>
                                            <span class="admin-cobros-badge" :class="badgeEstado[t.estado]">
                                                <span class="admin-cobros-badge-dot"></span>{{ t.estado_nombre }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex justify-center gap-1.5">
                                                <Link :href="route('admin.cobros.show', t.id)" title="Ver detalle" class="admin-cobros-action-btn admin-cobros-action-btn--view">
                                                    <i class="pi pi-eye"></i>
                                                </Link>
                                                <template v-if="t.estado === 'pendiente'">
                                                    <button @click="aprobar(t)" title="Aprobar" class="admin-cobros-action-btn admin-cobros-action-btn--approve">
                                                        <i class="pi pi-check"></i>
                                                    </button>
                                                    <button @click="reembolsar(t)" title="Reembolsar" class="admin-cobros-action-btn admin-cobros-action-btn--refund">
                                                        <i class="pi pi-replay"></i>
                                                    </button>
                                                </template>
                                                <button v-else-if="t.estado === 'aprobada'" @click="reembolsar(t)" title="Reembolsar" class="admin-cobros-action-btn admin-cobros-action-btn--refund">
                                                    <i class="pi pi-replay"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!transacciones.data?.length">
                                        <td colspan="8" class="admin-cobros-empty">No se encontraron transacciones.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-if="transacciones.last_page > 1" class="admin-cobros-table-footer">
                        <Pagination :data="transacciones" />
                    </div>
                </div>

                <!-- Resumen de Cobros -->
                <div class="admin-cobros-card min-w-0" style="grid-area:resumen">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-wallet"></i></div>
                                <h3>Resumen de Cobros</h3>
                            </div>
                            <select v-model="periodoResumen" class="admin-cobros-select">
                                <option value="semana">Esta semana</option>
                                <option value="mes">Este mes</option>
                                <option value="anio">Este año</option>
                            </select>
                        </div>
                        <div class="admin-cobros-summary">
                            <div class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Total Cobros</span>
                                <span class="admin-cobros-summary-value">{{ money(resumen.cobrosDelMes) }}</span>
                            </div>
                            <div class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Comisiones</span>
                                <span class="admin-cobros-summary-value admin-cobros-summary-value--negative">-{{ money(resumen.comisionesDelMes) }}</span>
                            </div>
                            <div class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Reembolsos</span>
                                <span class="admin-cobros-summary-value admin-cobros-summary-value--negative">-{{ money(resumen.reembolsosDelMes) }}</span>
                            </div>
                            <div class="admin-cobros-summary-row admin-cobros-summary-row--total">
                                <span class="admin-cobros-summary-label">Total Neto</span>
                                <span class="admin-cobros-summary-value admin-cobros-summary-value--total">{{ money(resumen.cobrosDelMes - resumen.comisionesDelMes - resumen.reembolsosDelMes) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Métodos de Pago -->
                <div class="admin-cobros-card min-w-0" style="grid-area:metodos">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-credit-card"></i></div>
                                <h3>Métodos de Pago</h3>
                            </div>
                        </div>
                        <div class="admin-cobros-summary">
                            <div v-for="m in metodosPago" :key="m.metodo" class="admin-cobros-metodo-row">
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="text-gray-600">{{ m.metodo_nombre }}</span>
                                    <span class="font-semibold text-gray-800">{{ m.porcentaje }}%</span>
                                </div>
                                <div class="admin-cobros-metodo-bar">
                                    <div class="admin-cobros-metodo-bar-fill" :style="{ width: m.porcentaje + '%' }"></div>
                                </div>
                            </div>
                            <p v-if="!metodosPago?.length" class="admin-cobros-empty">Aún no hay cobros para mostrar.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila 3: Gráficas y Pagos Pendientes -->
            <div class="admin-cobros-charts-grid gap-6 mt-6 w-full">
                <!-- Ingresos -->
                <div class="admin-cobros-card min-w-0" style="grid-area:ingresos">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-chart-line"></i></div>
                                <h3>Ingresos</h3>
                            </div>
                            <select v-model="periodoIngresos" class="admin-cobros-select">
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
                                    <p class="admin-cobros-empty" style="padding:0">Aún no hay ingresos registrados.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cobros y Reembolsos -->
                <div class="admin-cobros-card min-w-0" style="grid-area:cobros">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-chart-pie"></i></div>
                                <h3>Cobros y Reembolsos</h3>
                            </div>
                            <select v-model="periodoTipos" class="admin-cobros-select">
                                <option value="semana">Esta semana</option>
                                <option value="mes">Este mes</option>
                                <option value="anio">Este año</option>
                            </select>
                        </div>
                        <div class="p-6">
                            <div v-if="totalCategorias > 0" class="relative my-2" style="height:160px">
                                <Doughnut :data="doughnutData"
                                    :options="{ maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => money(ctx.parsed) } } }, cutout: '65%' }" />
                                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                    <p class="text-[11px] text-gray-400 font-medium uppercase tracking-wider">Total</p>
                                    <p class="font-bold text-gray-800 text-base">{{ money(totalCategorias) }}</p>
                                </div>
                            </div>
                            <div v-else class="admin-cobros-empty">Sin datos aún.</div>

                            <ul v-if="totalCategorias > 0" class="mt-4 space-y-2">
                                <li v-for="c in categoriasResumen" :key="c.id" class="admin-cobros-legend-item">
                                    <span class="flex items-center gap-2 text-gray-600">
                                        <span class="admin-cobros-legend-dot" :style="{ backgroundColor: categoriaColores[c.id] }"></span>
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

                <!-- Pagos Pendientes -->
                <div class="admin-cobros-card min-w-0" style="grid-area:pendientes">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-clock"></i></div>
                                <h3>Pagos Pendientes</h3>
                            </div>
                            <span v-if="pagosPendientes?.length" class="admin-cobros-pending-badge">{{ pagosPendientes.length }}</span>
                        </div>
                        <div class="px-6">
                            <div v-for="p in pagosPendientes" :key="p.id" class="admin-cobros-pending-item">
                                <div class="flex flex-col">
                                    <span class="admin-cobros-tx-id">TRX-{{ String(p.id).padStart(4, '0') }}</span>
                                    <span class="admin-cobros-tx-name" style="font-size:0.78rem">@{{ p.usuario?.apodo ?? p.usuario?.nombre ?? '—' }}</span>
                                </div>
                                <span class="admin-cobros-monto admin-cobros-monto--egreso">{{ money(p.monto) }}</span>
                            </div>
                            <p v-if="!pagosPendientes?.length" class="admin-cobros-empty">Sin pagos pendientes.</p>
                        </div>
                    </div>
                    <div v-if="pagosPendientes?.length" class="pt-4 pb-6 border-t text-center mx-6" style="border-color:var(--line)">
                        <a href="#" style="color:var(--brand)" class="text-xs font-semibold hover:underline">Ver todos los pendientes</a>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>