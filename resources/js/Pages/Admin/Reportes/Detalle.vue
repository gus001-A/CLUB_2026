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
            backgroundColor: 'rgba(200, 30, 58, 0.08)',
            fill: true,
            tension: 0.35,
            pointRadius: 2,
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

const LIMITE_PREVIA = 8;
const filasPrevia = computed(() => props.filas.slice(0, LIMITE_PREVIA));
const hayMasFilas = computed(() => props.filas.length > LIMITE_PREVIA);
</script>

<template>
    <Head :title="titulo" />

    <AdminLayout>
        <template #title>{{ titulo }}</template>
        <template #breadcrumb>Dashboard &gt; Reportes &gt; {{ titulo }}</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4 space-y-6">

            <Link :href="route('admin.reportes.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand">
                <i class="pi pi-arrow-left text-xs"></i> Volver a Reportes
            </Link>

            <!-- Encabezado + periodo + descargas -->
            <div class="admin-card overflow-hidden">
                <div class="admin-card-header">
                    <span class="admin-card-header-title"><i class="pi" :class="catalogo.icono" style="color:var(--brand)"></i> {{ titulo }}</span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6">
                    <div>
                        <p class="text-sm text-gray-600">{{ catalogo.descripcion }}</p>
                        <p v-if="periodoLabel" class="text-xs mt-0.5" style="color:var(--muted)">Periodo: {{ periodoLabel }} · Generado el {{ generadoEn }}</p>
                        <p v-else class="text-xs mt-0.5" style="color:var(--muted)">Generado el {{ generadoEn }}</p>

                        <div v-if="catalogo.usaPeriodo" class="flex items-center gap-2 mt-3">
                            <button v-for="p in [['dia','Día'],['semana','Semana'],['mes','Mes'],['anio','Año']]" :key="p[0]"
                                type="button" @click="cambiarPeriodo(p[0])"
                                class="px-3 py-1.5 rounded-full text-xs font-semibold transition"
                                :class="periodo === p[0] ? 'text-white' : 'bg-gray-50 text-gray-500 hover:bg-gray-100'"
                                :style="periodo === p[0] ? 'background:var(--brand)' : ''">
                                {{ p[1] }}
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a :href="urlExportar('pdf')" class="admin-btn-secondary">
                            <i class="pi pi-file-pdf text-red-500"></i> PDF
                        </a>
                        <a :href="urlExportar('excel')" class="admin-btn-primary">
                            <i class="pi pi-file-excel"></i> Excel
                        </a>
                    </div>
                </div>
            </div>

            <!-- Resumen -->
            <div v-if="resumen?.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="r in resumen" :key="r.label" class="admin-card px-5 py-4">
                    <p class="text-xs" style="color:var(--muted)">{{ r.label }}</p>
                    <p class="text-lg font-bold text-gray-900 mt-1">{{ r.valor }}</p>
                </div>
            </div>

            <!-- Gráfica (solo si el reporte trae una) -->
            <div v-if="chart" class="admin-card overflow-hidden">
                <div class="admin-card-header">
                    <span class="admin-card-header-title"><i class="pi pi-chart-line text-brand"></i> Tendencia</span>
                </div>
                <div class="p-6" style="height:280px">
                    <Line :data="chartData" :options="chartOptions" />
                </div>
            </div>

            <!-- Tabla (vista previa — el detalle completo va en PDF/Excel) -->
            <div class="admin-card overflow-hidden">
                <div class="admin-card-header">
                    <span class="admin-card-header-title"><i class="pi pi-table text-brand"></i> Vista Previa</span>
                    <span v-if="hayMasFilas" class="text-xs" style="color:var(--muted)">
                        Mostrando {{ filasPrevia.length }} de {{ filas.length }}
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm min-w-[500px]">
                        <thead>
                            <tr class="border-y text-xs uppercase tracking-wider" style="border-color:var(--line);background:var(--surface);color:var(--muted)">
                                <th v-for="(col, i) in columnas" :key="col" class="px-6 py-3 font-semibold" :class="i > 0 ? 'text-right' : 'text-left'">{{ col }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(fila, i) in filasPrevia" :key="i" class="hover:bg-gray-50/50 transition">
                                <td v-for="(valor, j) in fila" :key="j" class="px-6 py-3" :class="j > 0 ? 'text-right text-gray-700' : 'font-medium text-gray-900'">
                                    {{ valor }}
                                </td>
                            </tr>
                            <tr v-if="!filas.length">
                                <td :colspan="columnas.length" class="py-10 text-center text-gray-400 text-xs">No hay datos para este periodo.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Aviso + CTA de descarga si hay más datos de los que se muestran -->
                <div v-if="hayMasFilas" class="border-t px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3" style="border-color:var(--line);background:var(--surface)">
                    <p class="text-sm text-gray-600">
                        Hay <strong>{{ filas.length }}</strong> filas en total — descarga el reporte completo para verlas todas.
                    </p>
                    <div class="flex items-center gap-2 shrink-0">
                        <a :href="urlExportar('pdf')" class="admin-btn-secondary" style="padding:0.4rem 0.85rem;font-size:0.8rem">
                            <i class="pi pi-file-pdf text-red-500"></i> PDF
                        </a>
                        <a :href="urlExportar('excel')" class="admin-btn-primary" style="padding:0.4rem 0.85rem;font-size:0.8rem">
                            <i class="pi pi-file-excel"></i> Excel
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>