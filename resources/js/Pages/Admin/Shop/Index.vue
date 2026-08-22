<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip } from 'chart.js';

ChartJS.register(ArcElement, Tooltip);

const props = defineProps({
    stats: Object,
    pedidos: Object,
    filtros: Object,
    resumen: Object,
    masVendidos: Array,
    ventasPorCategoria: Array,
});

const q = ref(props.filtros.q || '');
const estado = ref(props.filtros.estado || '');
const metodo = ref(props.filtros.metodo || '');
const periodo = ref(props.resumen.periodo || 'mes');

watch(periodo, () => {
    router.get(route('admin.shop.index'), {
        q: q.value || undefined,
        estado: estado.value || undefined,
        metodo: metodo.value || undefined,
        periodo: periodo.value,
    }, { preserveState: true, preserveScroll: true, replace: true });
});

let timeout = null;
watch([q, estado, metodo], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.shop.index'), {
            q: q.value || undefined,
            estado: estado.value || undefined,
            metodo: metodo.value || undefined,
        }, { preserveState: true, replace: true });
    }, 350);
});

function money(v) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(v ?? 0);
}
function formatDate(v) {
    if (!v) return '—';
    return new Date(v).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
}

const estadoBadgeClase = { pagado: 'admin-shop-badge--pagado', enviado: 'admin-shop-badge--enviado', entregado: 'admin-shop-badge--entregado', cancelado: 'admin-shop-badge--cancelado' };
const estadoLabel = { pagado: 'Procesando', enviado: 'Enviado', entregado: 'Completado', cancelado: 'Cancelado' };
const metodoLabel = { tarjeta_credito: 'Tarjeta de Crédito', tarjeta_debito: 'Tarjeta de Débito', paypal: 'PayPal', transferencia: 'Transferencia', otro: 'Otro' };

// KPIs con el mismo lenguaje visual del resto del panel
const kpis = computed(() => [
    { label: 'Pedidos Totales', value: props.stats.pedidosTotales, icon: 'pi-shopping-cart', color: '#2563EB', iconBg: '#DBEAFE', gradient: 'linear-gradient(135deg, #2563EB, #1D4ED8)', hint: null },
    {
        label: 'Ventas Totales', value: money(props.stats.ventasTotales), icon: 'pi-dollar', color: '#059669', iconBg: '#D1FAE5', gradient: 'linear-gradient(135deg, #059669, #047857)',
        hint: props.stats.variacion !== null ? `${props.stats.variacion >= 0 ? '+' : ''}${props.stats.variacion}% vs mes anterior` : 'Sin datos del mes anterior',
    },
    { label: 'Pedidos Completados', value: props.stats.pedidosCompletados, icon: 'pi-check-circle', color: '#D97706', iconBg: '#FEF3C7', gradient: 'linear-gradient(135deg, #D97706, #B45309)', hint: `${props.stats.porcentajeCompletados}% del total` },
]);

const doughnutColors = ['#C81E3A', '#F5A623', '#10B981', '#2563EB', '#8B5CF6', '#EC4899', '#0EA5E9', '#84CC16'];
const doughnutData = computed(() => ({
    labels: props.ventasPorCategoria.map((c) => c.categoria),
    datasets: [{
        data: props.ventasPorCategoria.map((c) => c.total),
        backgroundColor: doughnutColors,
        borderWidth: 0,
    }],
}));
const totalCategorias = computed(() => props.ventasPorCategoria.reduce((s, c) => s + c.total, 0));
</script>

<template>
    <Head title="Shop" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Dashboard &gt; Shop</template>

        <div class="admin-reportes-page">

            <!-- Fila 1: KPIs + Resumen de Ventas -->
            <div class="admin-shop-top-grid mb-6">
                <div class="admin-shop-kpi-group">
                    <div v-for="kpi in kpis" :key="kpi.label" class="admin-cobros-kpi-card">
                        <div class="admin-cobros-kpi-card__icon" :style="{ background: kpi.iconBg, color: kpi.color }">
                            <i class="pi" :class="kpi.icon"></i>
                        </div>
                        <div class="admin-cobros-kpi-card__content">
                            <span class="admin-cobros-kpi-card__label">{{ kpi.label }}</span>
                            <span class="admin-cobros-kpi-card__value" :style="{ color: kpi.color }">{{ kpi.value }}</span>
                            <span v-if="kpi.hint" class="admin-cobros-kpi-card__hint">{{ kpi.hint }}</span>
                        </div>
                        <div class="admin-cobros-kpi-card__bar" :style="{ background: kpi.gradient }"></div>
                    </div>
                </div>

                <!-- Resumen de Ventas -->
                <div class="admin-cobros-card min-w-0">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-chart-bar"></i></div>
                                <h3>Resumen de Ventas</h3>
                            </div>
                            <select v-model="periodo" class="admin-cobros-select">
                                <option value="dia">Hoy</option>
                                <option value="semana">Esta semana</option>
                                <option value="mes">Este mes</option>
                            </select>
                        </div>
                        <div class="admin-cobros-summary" style="padding:1rem 1.2rem">
                            <div class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Subtotal</span>
                                <span class="admin-cobros-summary-value">{{ money(resumen.subtotal) }}</span>
                            </div>
                            <div class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Envíos</span>
                                <span class="admin-cobros-summary-value">{{ money(resumen.envios) }}</span>
                            </div>
                            <div class="admin-cobros-summary-row admin-cobros-summary-row--total">
                                <span class="admin-cobros-summary-label">Ventas Totales</span>
                                <span class="admin-cobros-summary-value admin-cobros-summary-value--total">{{ money(resumen.ventasTotales) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila 2: Tabla de Pedidos (sola) -->
            <div class="admin-cobros-card mb-6">
                <div class="flex flex-col flex-1">
                    <div class="admin-cobros-card__header">
                        <div class="admin-cobros-card__header-left">
                            <div class="admin-cobros-header-icon"><i class="pi pi-shopping-cart"></i></div>
                            <div>
                                <h3>Pedidos</h3>
                                <p class="admin-cobros-header-subtitle">Consulta y administra los pedidos de la tienda</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <Link :href="route('admin.shop.todos')" class="admin-btn-secondary">
                                <i class="pi pi-list"></i> Ver todos
                            </Link>
                            <a :href="route('admin.shop.exportar', { estado: estado || undefined })" class="admin-cobros-btn-primary">
                                <i class="pi pi-download"></i> Exportar
                            </a>
                        </div>
                    </div>

                    <div class="admin-cobros-filters">
                        <div class="admin-cobros-filters__search">
                            <i class="pi pi-search"></i>
                            <input v-model="q" type="text" placeholder="Buscar pedido, usuario..." />
                        </div>
                        <select v-model="estado">
                            <option value="">Todos los estados</option>
                            <option value="pagado">Procesando</option>
                            <option value="enviado">Enviado</option>
                            <option value="entregado">Completado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                        <select v-model="metodo">
                            <option value="">Todos los métodos</option>
                            <option value="tarjeta_credito">Tarjeta de Crédito</option>
                            <option value="tarjeta_debito">Tarjeta de Débito</option>
                            <option value="paypal">PayPal</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto flex-1 flex flex-col">
                        <table class="admin-cobros-table min-w-[800px] flex-1">
                            <thead>
                                <tr>
                                    <th>Pedido</th>
                                    <th>Usuario</th>
                                    <th>Productos</th>
                                    <th>Total</th>
                                    <th>Método</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="p in pedidos.data" :key="p.id">
                                    <td class="text-gray-500 text-xs whitespace-nowrap">#{{ p.numero_pedido }}</td>
                                    <td class="whitespace-nowrap">
                                        <p class="admin-cobros-tx-name">{{ p.usuario?.nombre ?? '—' }}</p>
                                        <p class="admin-cobros-tx-handle">@{{ p.usuario?.apodo ?? '—' }}</p>
                                    </td>
                                    <td class="whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div v-for="(img, idx) in p.miniaturas" :key="idx"
                                                class="rounded-full border-2 border-white bg-gray-100 overflow-hidden -ml-2 first:ml-0" style="width:28px;height:28px">
                                                <img :src="img" class="w-full h-full object-cover" />
                                            </div>
                                            <span class="text-gray-400 text-xs ml-2">{{ p.total_items }} artículo{{ p.total_items === 1 ? '' : 's' }}</span>
                                        </div>
                                    </td>
                                    <td class="font-semibold text-xs whitespace-nowrap" style="color:var(--ink)">{{ money(p.total) }}</td>
                                    <td class="text-gray-600 text-xs whitespace-nowrap">{{ p.metodo_pago ? metodoLabel[p.metodo_pago] : '—' }}</td>
                                    <td class="whitespace-nowrap">
                                        <span class="admin-shop-badge" :class="estadoBadgeClase[p.estado]">
                                            <span class="admin-shop-badge-dot"></span>{{ estadoLabel[p.estado] }}
                                        </span>
                                    </td>
                                    <td class="text-gray-500 text-xs whitespace-nowrap">{{ formatDate(p.created_at) }}</td>
                                    <td>
                                        <div class="flex justify-center items-center gap-1.5">
                                            <Link :href="route('admin.shop.show', p.id)" title="Ver detalle" class="admin-dash-action-btn admin-dash-action-btn--view">
                                                <i class="pi pi-eye"></i>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!pedidos.data.length">
                                    <td colspan="8" class="admin-cobros-empty">No se encontraron pedidos.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="pedidos.last_page > 1" class="admin-cobros-table-footer">
                    <Pagination :data="pedidos" />
                </div>
            </div>

            <!-- Fila 3: Productos Más Vendidos | Ventas por Categoría -->
            <div class="admin-two-col-grid gap-6 w-full items-stretch">

                <div class="admin-cobros-card min-w-0">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-star"></i></div>
                                <h3>Productos Más Vendidos</h3>
                            </div>
                        </div>
                        <div class="admin-dash-list">
                            <div v-for="(p, i) in masVendidos" :key="i" class="admin-dash-list-item">
                                <div class="admin-dash-list-item__left">
                                    <div class="admin-dash-list-thumb">
                                        <img v-if="p.imagen" :src="p.imagen" />
                                        <div v-else class="w-full h-full flex items-center justify-center" style="color:var(--muted-light)"><i class="pi pi-box"></i></div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="admin-dash-list-title">{{ p.nombre }}</p>
                                        <p class="admin-dash-list-meta">{{ p.unidades }} ventas</p>
                                    </div>
                                </div>
                                <span class="admin-dash-list-value">{{ money(p.ingresos) }}</span>
                            </div>
                            <div v-if="!masVendidos?.length" class="admin-cobros-empty">Aún no hay ventas.</div>
                        </div>
                    </div>
                </div>

                <div class="admin-cobros-card min-w-0">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-chart-pie"></i></div>
                                <h3>Ventas por Categoría</h3>
                            </div>
                        </div>
                        <div style="padding:1.5rem">
                            <div v-if="ventasPorCategoria.length" class="relative mx-auto" style="height:160px;width:160px">
                                <Doughnut :data="doughnutData" :options="{ maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '70%' }" />
                                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                    <p class="text-[11px] text-gray-400 font-medium uppercase tracking-wider">Total</p>
                                    <p class="font-bold text-gray-800 text-sm">{{ money(totalCategorias) }}</p>
                                </div>
                            </div>
                            <p v-else class="admin-cobros-empty">Aún no hay ventas.</p>

                            <ul class="mt-4 space-y-2">
                                <li v-for="(c, i) in ventasPorCategoria" :key="c.categoria" class="admin-cobros-legend-item">
                                    <span class="flex items-center gap-2 text-gray-600">
                                        <span class="admin-cobros-legend-dot" :style="{ backgroundColor: doughnutColors[i % doughnutColors.length] }"></span>
                                        {{ c.categoria }}
                                    </span>
                                    <span class="text-gray-800 font-semibold">{{ money(c.total) }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>