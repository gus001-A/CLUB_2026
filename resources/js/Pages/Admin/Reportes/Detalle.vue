<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Filler } from 'chart.js';
import { computed } from 'vue';

ChartJS.register(LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Filler);

const props = defineProps({
    catalogo: Object,
    tipo: String,
    titulo: String,
    periodo: String,
    periodoLabel: String,
    generadoEn: String,
    columnas: Array,
    filas: Array,
    resumen: Array,
    chart: Object,
});

function cambiarPeriodo(p) {
    if (p === props.periodo) return;
    router.get(route('admin.reportes.detalle', props.tipo), { periodo: p }, { preserveState: true, preserveScroll: true, replace: true });
}

const chartData = computed(() => props.chart && {
    labels: props.chart.labels,
    datasets: [
        {
            label: props.titulo,
            data: props.chart.data,
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
            pointRadius: 2,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#C81E3A',
            pointBorderWidth: 2,
        },
    ],
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } },
};

function urlExportar(formato) {
    return route(`admin.reportes.exportar-${formato}`, { tipo: props.tipo, periodo: props.periodo });
}

const periodos = [['dia', 'Día'], ['semana', 'Semana'], ['mes', 'Mes'], ['anio', 'Año']];

const LIMITE_PREVIA = 8;
const filasPrevia = computed(() => props.filas.slice(0, LIMITE_PREVIA));
const hayMasFilas = computed(() => props.filas.length > LIMITE_PREVIA);
</script>

<template>
    <Head :title="titulo" />

    <AdminLayout>
        <template #title>{{ titulo }}</template>
        <template #breadcrumb>Dashboard &gt; Reportes &gt; {{ titulo }}</template>

        <div class="admin-reportes-page">
            <Link :href="route('admin.reportes.index')" class="admin-user-back-link">
                <i class="pi pi-arrow-left"></i>
                Volver a Reportes
            </Link>

            <!-- Encabezado + periodo + descargas -->
            <div class="admin-cobros-card mb-6">
                <div class="admin-cobros-card__header">
                    <div class="admin-cobros-card__header-left">
                        <div class="admin-cobros-header-icon"><i class="pi" :class="catalogo.icono"></i></div>
                        <h3>{{ titulo }}</h3>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4" style="padding:1.5rem">
                    <div>
                        <p class="text-sm text-gray-600">{{ catalogo.descripcion }}</p>
                        <p v-if="periodoLabel" class="admin-cobros-header-subtitle" style="margin-top:0.2rem">Periodo: {{ periodoLabel }} · Generado el {{ generadoEn }}</p>
                        <p v-else class="admin-cobros-header-subtitle" style="margin-top:0.2rem">Generado el {{ generadoEn }}</p>

                        <div v-if="catalogo.usaPeriodo" class="admin-user-toggle-group" style="margin-top:0.9rem">
                            <button v-for="p in periodos" :key="p[0]" type="button" @click="cambiarPeriodo(p[0])"
                                class="admin-user-toggle-pill" :class="{ 'admin-user-toggle-pill--active': periodo === p[0] }" style="padding:0.4rem 0.9rem;font-size:0.75rem">
                                {{ p[1] }}
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a :href="urlExportar('pdf')" class="admin-btn-secondary">
                            <i class="pi pi-file-pdf text-red-500"></i> PDF
                        </a>
                        <a :href="urlExportar('excel')" class="admin-cobros-btn-primary">
                            <i class="pi pi-file-excel"></i> Excel
                        </a>
                    </div>
                </div>
            </div>

            <!-- Resumen -->
            <div v-if="resumen?.length" class="admin-cobros-tipo-grid" style="padding:0;margin-bottom:1.5rem">
                <div v-for="r in resumen" :key="r.label" class="admin-cobros-tipo-tile">
                    <p class="admin-cobros-tipo-label">{{ r.label }}</p>
                    <p class="admin-cobros-tipo-value">{{ r.valor }}</p>
                </div>
            </div>

            <!-- Gráfica (solo si el reporte trae una) -->
            <div v-if="chart" class="admin-cobros-card mb-6">
                <div class="admin-cobros-card__header">
                    <div class="admin-cobros-card__header-left">
                        <div class="admin-cobros-header-icon"><i class="pi pi-chart-line"></i></div>
                        <h3>Tendencia</h3>
                    </div>
                </div>
                <div style="padding:1.5rem;height:280px">
                    <Line :data="chartData" :options="chartOptions" />
                </div>
            </div>

            <!-- Tabla (vista previa — el detalle completo va en PDF/Excel) -->
            <div class="admin-cobros-card">
                <div class="admin-cobros-card__header">
                    <div class="admin-cobros-card__header-left">
                        <div class="admin-cobros-header-icon"><i class="pi pi-table"></i></div>
                        <h3>Vista Previa</h3>
                    </div>
                    <span v-if="hayMasFilas" class="admin-cobros-header-subtitle">
                        Mostrando {{ filasPrevia.length }} de {{ filas.length }}
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="admin-cobros-table min-w-[500px]">
                        <thead>
                            <tr>
                                <th v-for="(col, i) in columnas" :key="col" :class="i > 0 ? 'text-right' : 'text-left'">{{ col }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(fila, i) in filasPrevia" :key="i">
                                <td v-for="(valor, j) in fila" :key="j" :class="j > 0 ? 'text-right text-gray-700' : 'font-medium text-gray-900'">
                                    {{ valor }}
                                </td>
                            </tr>
                            <tr v-if="!filas.length">
                                <td :colspan="columnas.length" class="admin-cobros-empty">No hay datos para este periodo.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Aviso + CTA de descarga si hay más datos de los que se muestran -->
                <div v-if="hayMasFilas" class="admin-cobros-table-footer flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <p class="text-sm text-gray-600">
                        Hay <strong>{{ filas.length }}</strong> filas en total — descarga el reporte completo para verlas todas.
                    </p>
                    <div class="flex items-center gap-2 shrink-0">
                        <a :href="urlExportar('pdf')" class="admin-btn-secondary" style="padding:0.4rem 0.85rem;font-size:0.8rem">
                            <i class="pi pi-file-pdf text-red-500"></i> PDF
                        </a>
                        <a :href="urlExportar('excel')" class="admin-cobros-btn-primary" style="padding:0.4rem 0.85rem;font-size:0.8rem">
                            <i class="pi pi-file-excel"></i> Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>