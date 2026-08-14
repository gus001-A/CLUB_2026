<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KpiCard from '@/Components/KpiCard.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip } from 'chart.js';
import { useToast } from '@/composables/useToast';

ChartJS.register(ArcElement, Tooltip);

const props = defineProps({
    stats: Object,
    pedidos: Object,
    filtros: Object,
    resumen: Object,
    masVendidos: Array,
    ventasPorCategoria: Array,
    metodosPago: Array,
    actividadReciente: Array,
});

const toast = useToast();

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

const estadoColores = {
    pagado: 'bg-blue-100 text-blue-700',
    enviado: 'bg-amber-100 text-amber-700',
    entregado: 'bg-green-100 text-green-700',
    cancelado: 'bg-red-100 text-red-700',
};
const estadoLabel = { pagado: 'Procesando', enviado: 'Enviado', entregado: 'Completado', cancelado: 'Cancelado' };
const metodoLabel = { tarjeta_credito: 'Tarjeta de Crédito', tarjeta_debito: 'Tarjeta de Débito', paypal: 'PayPal', transferencia: 'Transferencia', otro: 'Otro' };

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

const accionesRapidas = [
    { label: 'Ver Todos los Pedidos', desc: 'Consulta todos los pedidos realizados', icon: 'pi-shopping-cart' },
    { label: 'Productos', desc: 'Gestiona los productos de la tienda', icon: 'pi-box', comingSoon: true },
    { label: 'Categorías', desc: 'Administra las categorías de productos', icon: 'pi-tags', comingSoon: true },
    { label: 'Cupones de Descuento', desc: 'Crea y gestiona cupones', icon: 'pi-percentage', comingSoon: true },
    { label: 'Configuración de Shop', desc: 'Ajusta las opciones de la tienda', icon: 'pi-cog', comingSoon: true },
];

function irA(a) {
    if (a.comingSoon) {
        toast.success(`"${a.label}" estará disponible próximamente.`);
        return;
    }
    document.getElementById('tabla-pedidos')?.scrollIntoView({ behavior: 'smooth' });
}
</script>

<template>
    <Head title="Shop" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Dashboard &gt; Shop</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- Fila 1: KPIs + Pedidos | Resumen de Ventas + Acciones Rápidas -->
            <div class="admin-shop-main-grid gap-6 mb-6 w-full">

                <!-- Columna izquierda -->
                <div class="min-w-0 flex flex-col gap-6" style="grid-area:izquierda">

                    <!-- KPIs -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="w-full sm:flex-1 min-w-0">
                            <KpiCard label="Pedidos Totales" :value="stats.pedidosTotales" icon="pi-shopping-cart" />
                        </div>
                        <div class="w-full sm:flex-1 min-w-0">
                            <KpiCard label="Ventas Totales" :value="money(stats.ventasTotales)" icon="pi-dollar"
                                :hint="stats.variacion !== null ? `${stats.variacion >= 0 ? '+' : ''}${stats.variacion}% vs mes anterior` : 'Sin datos del mes anterior'"
                                :hint-color="stats.variacion === null ? 'text-gray-400' : (stats.variacion >= 0 ? 'text-green-600' : 'text-red-500')" />
                        </div>
                        <div class="w-full sm:flex-1 min-w-0">
                            <KpiCard label="Pedidos Completados" :value="stats.pedidosCompletados" icon="pi-clock"
                                :hint="`${stats.porcentajeCompletados}% del total`" hint-color="text-gray-400" />
                        </div>
                    </div>

                    <!-- Pedidos -->
                    <div id="tabla-pedidos" class="flex-1 admin-card overflow-hidden flex flex-col justify-between">
                        <div class="flex flex-col flex-1">
                            <div class="admin-card-header">
                                <span class="admin-card-header-title"><i class="pi pi-shopping-cart text-brand"></i> Pedidos</span>
                            </div>
                            <p class="text-xs px-6 pt-4" style="color:var(--muted)">Consulta y administra los pedidos de la tienda.</p>

                            <div class="flex flex-col sm:flex-row flex-wrap gap-3 px-6 py-4">
                                <div class="relative flex-1 min-w-[160px]">
                                    <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input v-model="q" type="text" placeholder="Buscar pedido, usuario..." class="admin-input pl-10 py-2" />
                                </div>
                                <select v-model="estado" class="admin-input w-auto py-2">
                                    <option value="">Todos los estados</option>
                                    <option value="pagado">Procesando</option>
                                    <option value="enviado">Enviado</option>
                                    <option value="entregado">Completado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                                <select v-model="metodo" class="admin-input w-auto py-2">
                                    <option value="">Todos los métodos</option>
                                    <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                    <option value="tarjeta_debito">Tarjeta de Débito</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="transferencia">Transferencia</option>
                                </select>
                                <a :href="route('admin.shop.exportar', { estado: estado || undefined })" class="admin-btn-primary shrink-0">
                                    <i class="pi pi-download text-xs"></i> Exportar
                                </a>
                            </div>

                            <div class="overflow-x-auto flex-1 flex flex-col">
                                <table class="w-full text-left text-sm min-w-[760px] flex-1">
                                <thead>
                                    <tr class="border-y border-gray-100 bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider">
                                        <th class="pl-6 pr-4 py-3 font-semibold">Pedido</th>
                                        <th class="px-3 py-3 font-semibold">Usuario</th>
                                        <th class="px-3 py-3 font-semibold">Productos</th>
                                        <th class="px-3 py-3 font-semibold">Total</th>
                                        <th class="px-3 py-3 font-semibold">Método</th>
                                        <th class="px-3 py-3 font-semibold">Estado</th>
                                        <th class="px-3 py-3 font-semibold">Fecha</th>
                                        <th class="pl-2 pr-6 py-3 text-center font-semibold">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="p in pedidos.data" :key="p.id" class="hover:bg-gray-50/50 transition">
                                        <td class="pl-6 pr-4 py-3.5 text-gray-500 text-xs whitespace-nowrap">#{{ p.numero_pedido }}</td>
                                        <td class="px-3 py-3.5 whitespace-nowrap">
                                            <p class="font-semibold text-gray-800 text-sm">{{ p.usuario?.nombre ?? '—' }}</p>
                                            <p class="text-gray-400 text-xs">@{{ p.usuario?.apodo ?? '—' }}</p>
                                        </td>
                                        <td class="px-3 py-3.5 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div v-for="(img, idx) in p.miniaturas" :key="idx"
                                                    class="w-7 h-7 rounded-full border-2 border-white bg-gray-100 overflow-hidden -ml-2 first:ml-0">
                                                    <img :src="img" class="w-full h-full object-cover" />
                                                </div>
                                                <span class="text-gray-400 text-xs ml-2">{{ p.total_items }} artículo{{ p.total_items === 1 ? '' : 's' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3.5 font-semibold text-gray-800 text-xs whitespace-nowrap">{{ money(p.total) }}</td>
                                        <td class="px-3 py-3.5 text-gray-600 text-xs whitespace-nowrap">{{ p.metodo_pago ? metodoLabel[p.metodo_pago] : '—' }}</td>
                                        <td class="px-3 py-3.5 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold" :class="estadoColores[p.estado]">
                                                {{ estadoLabel[p.estado] }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3.5 text-gray-500 text-xs whitespace-nowrap">{{ formatDate(p.created_at) }}</td>
                                        <td class="pl-2 pr-6 py-3.5 whitespace-nowrap">
                                            <div class="flex justify-center items-center gap-1.5">
                                                <Link :href="route('admin.shop.show', p.id)" title="Ver detalle" class="admin-table-action text-gray-600">
                                                    <i class="pi pi-eye text-xs"></i>
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!pedidos.data.length">
                                        <td colspan="8" class="py-8 text-center text-gray-400 text-xs">No se encontraron pedidos.</td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                        </div>

                        <!-- Paginación / footer -->
                        <div v-if="pedidos.last_page > 1" class="border-t border-gray-100 px-6 py-4">
                            <Pagination :data="pedidos" />
                        </div>
                        <div v-else class="border-t border-gray-100 py-3.5 text-center">
                            <Link :href="route('admin.shop.index')" class="text-brand font-medium hover:underline text-xs">
                                Ver todos los pedidos
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Resumen de Ventas -->
                <div class="min-w-0 admin-card overflow-hidden" style="grid-area:resumen">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-chart-bar text-brand"></i> Resumen de Ventas</span>
                        <select v-model="periodo" class="text-xs border border-gray-200 rounded-lg px-2 py-1 text-gray-500 bg-white focus:outline-none focus:border-brand">
                            <option value="dia">Hoy</option>
                            <option value="semana">Esta semana</option>
                            <option value="mes">Este mes</option>
                        </select>
                    </div>
                    <div class="space-y-2.5 text-xs p-5">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="font-semibold text-gray-800">{{ money(resumen.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Envíos</span>
                            <span class="font-semibold text-gray-800">{{ money(resumen.envios) }}</span>
                        </div>
                        <div class="border-t border-gray-100 pt-2.5 flex justify-between">
                            <span class="font-semibold text-gray-700 text-sm">Ventas Totales</span>
                            <span class="font-bold text-brand text-sm">{{ money(resumen.ventasTotales) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="min-w-0 admin-card overflow-hidden" style="grid-area:acciones">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-bolt text-brand"></i> Acciones Rápidas</span>
                    </div>
                    <div class="space-y-3 p-4">
                        <button v-for="a in accionesRapidas" :key="a.label" type="button" @click="irA(a)"
                            class="w-full flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 transition group text-left">
                            <div class="admin-icon-circle" style="width:44px;height:44px">
                                <i class="pi text-sm" :class="a.icon"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-800 group-hover:text-brand transition">{{ a.label }}</p>
                                <p class="text-xs text-gray-400">{{ a.desc }}</p>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Fila 2: Productos Más Vendidos | Ventas por Categoría -->
            <div class="admin-two-col-grid gap-6 mb-6 w-full">

                <div class="min-w-0 admin-card overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-star text-brand"></i> Productos Más Vendidos</span>
                        </div>
                        <ul class="space-y-3.5 p-6">
                            <li v-for="(p, i) in masVendidos" :key="i" class="flex items-center gap-3">
                                <div class="rounded-lg bg-gray-100 flex items-center justify-center text-gray-300 shrink-0 overflow-hidden" style="width:36px;height:36px">
                                    <img v-if="p.imagen" :src="p.imagen" class="w-full h-full object-cover" />
                                    <i v-else class="pi pi-box text-sm"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ p.nombre }}</p>
                                    <p class="text-xs text-gray-400">{{ p.unidades }} ventas</p>
                                </div>
                                <span class="text-sm font-semibold text-gray-800 shrink-0">{{ money(p.ingresos) }}</span>
                            </li>
                            <li v-if="!masVendidos?.length" class="text-center py-8 text-gray-400 text-xs">Aún no hay ventas.</li>
                        </ul>
                    </div>
                </div>

                <div class="min-w-0 admin-card overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-chart-pie text-brand"></i> Ventas por Categoría</span>
                        </div>
                        <div class="p-6">
                        <div v-if="ventasPorCategoria.length" class="relative mx-auto" style="height:160px;width:160px">
                            <Doughnut :data="doughnutData" :options="{ maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '70%' }" />
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <p class="text-[11px] text-gray-400 font-medium uppercase tracking-wider">Total</p>
                                <p class="font-bold text-gray-800 text-sm">{{ money(totalCategorias) }}</p>
                            </div>
                        </div>
                        <p v-else class="text-gray-400 text-sm text-center py-10">Aún no hay ventas.</p>

                        <ul class="mt-4 space-y-2">
                            <li v-for="(c, i) in ventasPorCategoria" :key="c.categoria" class="flex items-center justify-between text-xs">
                                <span class="flex items-center gap-2 text-gray-600">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: doughnutColors[i % doughnutColors.length] }"></span>
                                    {{ c.categoria }}
                                </span>
                                <span class="text-gray-800 font-semibold">{{ money(c.total) }}</span>
                            </li>
                        </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila 3: Métodos de Pago | Actividad Reciente -->
            <div class="admin-two-col-grid gap-6 w-full">

                <div class="min-w-0 admin-card overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-credit-card text-brand"></i> Métodos de Pago</span>
                        </div>
                        <div class="space-y-5 p-6">
                            <div v-for="m in metodosPago" :key="m.metodo">
                                <div class="flex items-center justify-between text-sm mb-2">
                                    <span class="text-gray-600">{{ metodoLabel[m.metodo] }}</span>
                                    <span class="font-semibold text-gray-800">{{ m.porcentaje }}%</span>
                                </div>
                                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-brand rounded-full" :style="{ width: m.porcentaje + '%' }"></div>
                                </div>
                            </div>
                            <p v-if="!metodosPago?.length" class="text-sm text-gray-400 text-center py-6">Aún no hay ventas para mostrar.</p>
                        </div>
                    </div>
                </div>

                <div class="min-w-0 admin-card overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-history text-brand"></i> Actividad Reciente</span>
                        </div>
                        <ul class="space-y-3.5 p-6">
                            <li v-for="(a, i) in actividadReciente" :key="i" class="flex items-start gap-3">
                                <div class="admin-icon-circle text-xs" style="width:36px;height:36px;min-width:36px">
                                    <i class="pi" :class="a.icon"></i>
                                </div>
                                <div class="text-xs">
                                    <p class="text-gray-800 leading-snug">{{ a.texto }}</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ formatDate(a.fecha) }}</p>
                                </div>
                            </li>
                            <li v-if="!actividadReciente?.length" class="text-gray-400 text-xs py-6 text-center">Sin actividad todavía.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>