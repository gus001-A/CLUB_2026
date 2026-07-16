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
        <template #title>Cobros y Pagos</template>
        <template #breadcrumb>Dashboard &gt; Cobros y Pagos</template>

        <!-- KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-sm text-gray-400">Ingresos Totales</p>
                <p class="text-2xl font-semibold text-gray-800 mt-1">{{ money(stats.ingresosTotales) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-sm text-gray-400">Cobros del Mes</p>
                <p class="text-2xl font-semibold text-gray-800 mt-1">{{ money(stats.cobrosDelMes) }}</p>
                <p v-if="stats.cobrosVariacion !== null" class="text-xs mt-1" :class="stats.cobrosVariacion >= 0 ? 'text-green-600' : 'text-red-500'">
                    {{ stats.cobrosVariacion >= 0 ? '+' : '' }}{{ stats.cobrosVariacion }}% vs mes anterior
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-sm text-gray-400">Reembolsos del Mes</p>
                <p class="text-2xl font-semibold text-gray-800 mt-1">{{ money(stats.reembolsosDelMes) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-sm text-gray-400">Pagos Pendientes</p>
                <p class="text-2xl font-semibold text-gray-800 mt-1">{{ money(stats.pagosPendientesMonto) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ stats.pagosPendientesCount }} transacciones</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Transacciones -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="font-semibold text-gray-800 mb-4">Transacciones</h2>

                <div class="flex flex-col sm:flex-row gap-3 mb-4">
                    <div class="relative flex-1 min-w-[160px]">
                        <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-sm pointer-events-none"></i>
                        <input v-model="q" type="text" placeholder="Buscar transacción..." class="w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                    </div>
                    <select v-model="tipo" class="rounded-lg border border-gray-300 text-sm px-3 py-2 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                        <option value="">Todos los tipos</option>
                        <option value="suscripcion">Suscripción</option>
                        <option value="compra_contenido">Compra de contenido</option>
                        <option value="propina">Propina</option>
                        <option value="retiro">Retiro</option>
                    </select>
                    <div class="flex items-center gap-1.5">
                        <input v-model="desde" type="date" class="rounded-lg border border-gray-300 text-sm px-2.5 py-2 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                        <span class="text-gray-300 text-sm">–</span>
                        <input v-model="hasta" type="date" class="rounded-lg border border-gray-300 text-sm px-2.5 py-2 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                    </div>
                    <a
                        :href="route('admin.cobros.exportar', { q: q || undefined, tipo: tipo || undefined, desde: desde || undefined, hasta: hasta || undefined })"
                        class="bg-brand hover:bg-brand-dark text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5 shrink-0"
                    >
                        <i class="pi pi-download text-xs"></i> Exportar
                    </a>
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-400 border-b border-gray-100">
                            <th class="pb-2 font-medium">ID Transacción</th>
                            <th class="pb-2 font-medium">Usuario</th>
                            <th class="pb-2 font-medium">Tipo</th>
                            <th class="pb-2 font-medium">Descripción</th>
                            <th class="pb-2 font-medium">Monto</th>
                            <th class="pb-2 font-medium">Fecha</th>
                            <th class="pb-2 font-medium">Estado</th>
                            <th class="pb-2 font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="t in transacciones.data" :key="t.id" class="border-b border-gray-50 last:border-0 hover:bg-gray-50/70">
                            <td class="py-2.5 text-gray-500">TRX-{{ String(t.id).padStart(4, '0') }}</td>
                            <td class="py-2.5">
                                <p class="font-medium text-gray-800">{{ t.usuario?.nombre ?? '—' }}</p>
                                <p class="text-gray-400 text-xs">@{{ t.usuario?.apodo ?? '—' }}</p>
                            </td>
                            <td class="py-2.5">
                                <span class="flex items-center gap-1.5" :class="t.es_reembolso ? 'text-red-500' : 'text-green-600'">
                                    <i class="pi text-xs" :class="t.es_reembolso ? 'pi-arrow-up' : 'pi-arrow-down'"></i>
                                    {{ t.es_reembolso ? 'Reembolso' : 'Cobro' }}
                                </span>
                            </td>
                            <td class="py-2.5 text-gray-500">{{ t.tipo_nombre }}</td>
                            <td class="py-2.5 font-medium" :class="t.es_reembolso ? 'text-red-500' : 'text-gray-800'">
                                {{ t.es_reembolso ? '-' : '' }}{{ money(t.monto) }}
                            </td>
                            <td class="py-2.5 text-gray-400">{{ formatDate(t.created_at) }}</td>
                            <td class="py-2.5">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="estadoColores[t.estado]">
                                    {{ t.estado_nombre }}
                                </span>
                            </td>
                            <td class="py-2.5">
                                <div class="flex items-center gap-3 text-gray-400">
                                    <i class="pi pi-eye cursor-pointer hover:text-gray-700" title="Ver detalle"></i>
                                    <template v-if="t.estado === 'pendiente'">
                                        <i class="pi pi-check-circle cursor-pointer hover:text-green-600" title="Aprobar" @click="aprobar(t)"></i>
                                        <i class="pi pi-replay cursor-pointer hover:text-red-600" title="Reembolsar" @click="reembolsar(t)"></i>
                                    </template>
                                    <i
                                        v-else-if="t.estado === 'aprobada'"
                                        class="pi pi-replay cursor-pointer hover:text-red-600"
                                        title="Reembolsar"
                                        @click="reembolsar(t)"
                                    ></i>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!transacciones.data.length">
                            <td colspan="8" class="py-8 text-center text-gray-400">No se encontraron transacciones.</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div v-if="transacciones.last_page > 1" class="flex items-center justify-between mt-5 text-sm">
                    <p class="text-gray-400">Mostrando {{ transacciones.from }}–{{ transacciones.to }} de {{ transacciones.total }}</p>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in transacciones.links" :key="i">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                                preserve-state
                                class="px-3 py-1.5 rounded-lg"
                                :class="link.active ? 'bg-brand text-white' : 'text-gray-500 hover:bg-gray-100'"
                                v-html="link.label"
                            />
                            <span v-else class="px-3 py-1.5 text-gray-300" v-html="link.label"></span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Resumen + pendientes -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">Resumen de Cobros (mes)</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Cobros</span>
                            <span class="font-medium text-gray-800">{{ money(stats.cobrosDelMes) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Comisiones de Plataforma</span>
                            <span class="font-medium text-red-500">-{{ money(stats.comisionesDelMes) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Reembolsos</span>
                            <span class="font-medium text-red-500">-{{ money(stats.reembolsosDelMes) }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-100">
                            <span class="text-gray-700 font-medium">Total Neto</span>
                            <span class="font-semibold text-brand">
                                {{ money(stats.cobrosDelMes - stats.comisionesDelMes - stats.reembolsosDelMes) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">Métodos de Pago</h2>
                    <div class="space-y-3">
                        <div v-for="m in metodosPago" :key="m.metodo">
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="text-gray-600 capitalize">{{ m.metodo.replace('_', ' ') }}</span>
                                <span class="text-gray-800 font-medium">{{ m.porcentaje }}%</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-brand rounded-full" :style="{ width: m.porcentaje + '%' }"></div>
                            </div>
                        </div>
                        <p v-if="!metodosPago?.length" class="text-gray-400 text-sm">Aún no hay cobros para mostrar.</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">Pagos Pendientes</h2>
                    <ul class="space-y-3">
                        <li v-for="p in pagosPendientes" :key="p.id" class="flex items-center justify-between text-sm">
                            <div>
                                <p class="font-medium text-gray-800">@{{ p.usuario?.apodo ?? '—' }}</p>
                                <p class="text-gray-400 text-xs">TRX-{{ String(p.id).padStart(4, '0') }}</p>
                            </div>
                            <span class="font-medium text-amber-600">{{ money(p.monto) }}</span>
                        </li>
                        <li v-if="!pagosPendientes?.length" class="text-gray-400 text-sm">Sin pagos pendientes.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="font-semibold text-gray-800 mb-4">Ingresos (últimos 30 días)</h2>
                <div style="height: 260px">
                    <Line v-if="ingresosPorDia.length" :data="lineData" :options="lineOptions" />
                    <p v-else class="text-gray-400 text-sm text-center py-16">Aún no hay ingresos registrados.</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="font-semibold text-gray-800 mb-4">Tipos de Transacción</h2>
                <div v-if="tiposTotales.length" class="relative" style="height: 200px">
                    <Doughnut :data="doughnutData" :options="{ maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '70%' }" />
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <p class="text-xs text-gray-400">Total</p>
                        <p class="font-semibold text-gray-800">{{ money(totalTipos) }}</p>
                    </div>
                </div>
                <p v-else class="text-gray-400 text-sm text-center py-16">Sin datos aún.</p>

                <ul class="mt-4 space-y-1.5">
                    <li v-for="(t, i) in tiposTotales" :key="t.tipo" class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-1.5 text-gray-600">
                            <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: doughnutColors[i % doughnutColors.length] }"></span>
                            {{ tipoNombres[t.tipo] ?? t.tipo }}
                        </span>
                        <span class="text-gray-800 font-medium">{{ money(t.total) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </AdminLayout>
</template>