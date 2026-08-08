<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
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
                        <div class="w-full sm:flex-1 min-w-0 bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 min-h-[120px] flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-400">Pedidos Totales</p>
                                <p class="text-2xl font-semibold text-gray-800 mt-1">{{ stats.pedidosTotales }}</p>
                            </div>
                            <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0" style="width:44px;height:44px">
                                <i class="pi pi-shopping-cart text-lg"></i>
                            </div>
                        </div>
                        <div class="w-full sm:flex-1 min-w-0 bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 min-h-[120px] flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-400">Ventas Totales</p>
                                <p class="text-2xl font-semibold text-gray-800 mt-1">{{ money(stats.ventasTotales) }}</p>
                                <p v-if="stats.variacion !== null" class="text-xs mt-1 font-medium" :class="stats.variacion >= 0 ? 'text-green-600' : 'text-red-500'">
                                    {{ stats.variacion >= 0 ? '+' : '' }}{{ stats.variacion }}% vs mes anterior
                                </p>
                            </div>
                            <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0" style="width:44px;height:44px">
                                <i class="pi pi-dollar text-lg"></i>
                            </div>
                        </div>
                        <div class="w-full sm:flex-1 min-w-0 bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 min-h-[120px] flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-400">Pedidos Completados</p>
                                <p class="text-2xl font-semibold text-gray-800 mt-1">{{ stats.pedidosCompletados }}</p>
                                <p class="text-xs text-gray-400 mt-1 font-medium">{{ stats.porcentajeCompletados }}% del total</p>
                            </div>
                            <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0" style="width:44px;height:44px">
                                <i class="pi pi-clock text-lg"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Pedidos -->
                    <div id="tabla-pedidos" class="flex-1 bg-white rounded-2xl border border-gray-200/80 shadow-sm flex flex-col justify-between">
                        <div class="flex flex-col flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-6 pt-6">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-900">Pedidos</h2>
                                    <p class="text-xs text-gray-500 mt-0.5">Consulta y administra los pedidos de la tienda.</p>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row flex-wrap gap-3 px-6 py-4">
                                <div class="relative flex-1 min-w-[160px]">
                                    <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <input v-model="q" type="text" placeholder="Buscar pedido, usuario..."
                                        class="w-full rounded-xl border-gray-300 pl-10 pr-3 py-2 text-sm focus:border-brand focus:ring-brand">
                                </div>
                                <select v-model="estado" class="rounded-xl border-gray-300 text-sm px-3 py-2 focus:border-brand focus:ring-brand">
                                    <option value="">Todos los estados</option>
                                    <option value="pagado">Procesando</option>
                                    <option value="enviado">Enviado</option>
                                    <option value="entregado">Completado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                                <select v-model="metodo" class="rounded-xl border-gray-300 text-sm px-3 py-2 focus:border-brand focus:ring-brand">
                                    <option value="">Todos los métodos</option>
                                    <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                    <option value="tarjeta_debito">Tarjeta de Débito</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="transferencia">Transferencia</option>
                                </select>
                                <a :href="route('admin.shop.exportar', { estado: estado || undefined })"
                                    class="bg-brand hover:bg-brand-dark text-white text-sm font-medium px-4 py-2.5 rounded-xl flex items-center justify-center gap-2 shrink-0 shadow-sm">
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
                                                <Link :href="route('admin.shop.show', p.id)" title="Ver detalle"
                                                    class="w-8 h-8 min-w-[32px] max-w-[32px] rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 transition flex items-center justify-center">
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
                        <div v-if="pedidos.last_page > 1" class="border-t border-gray-100 px-6 py-4 flex items-center justify-between">
                            <p class="text-xs text-gray-500">Mostrando {{ pedidos.from }}–{{ pedidos.to }} de {{ pedidos.total }}</p>
                            <div class="flex gap-1">
                                <template v-for="(link, i) in pedidos.links" :key="i">
                                    <Link v-if="link.url" :href="link.url" preserve-scroll preserve-state v-html="link.label"
                                        class="px-3 py-1.5 rounded-lg text-xs"
                                        :class="link.active ? 'bg-brand text-white' : 'hover:bg-gray-100 text-gray-600'" />
                                    <span v-else class="px-3 py-1.5 text-gray-300 text-xs" v-html="link.label" />
                                </template>
                            </div>
                        </div>
                        <div v-else class="border-t border-gray-100 py-3.5 text-center">
                            <Link :href="route('admin.shop.index')" class="text-brand font-medium hover:underline text-xs">
                                Ver todos los pedidos
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Resumen de Ventas -->
                <div class="min-w-0 bg-white rounded-2xl border border-gray-200 shadow-sm p-5" style="grid-area:resumen">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold text-gray-900 text-base">Resumen de Ventas</h2>
                        <select v-model="periodo" class="text-xs border border-gray-200 rounded-lg px-2 py-1 text-gray-500 focus:outline-none focus:border-brand">
                            <option value="dia">Hoy</option>
                            <option value="semana">Esta semana</option>
                            <option value="mes">Este mes</option>
                        </select>
                    </div>
                    <div class="space-y-2.5 text-xs">
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
                <div class="min-w-0 bg-white rounded-2xl border border-gray-200 shadow-sm p-4" style="grid-area:acciones">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 px-2 pt-2">Acciones Rápidas</h2>
                    <div class="space-y-3">
                        <button v-for="a in accionesRapidas" :key="a.label" type="button" @click="irA(a)"
                            class="w-full flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 transition group text-left">
                            <div class="rounded-full bg-red-50 text-brand flex items-center justify-center shrink-0" style="width:44px;height:44px">
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

                <div class="min-w-0 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <h2 class="font-semibold text-gray-900 text-lg mb-4">Productos Más Vendidos</h2>
                        <ul class="space-y-3.5">
                            <li v-for="(p, i) in masVendidos" :key="i" class="flex items-center gap-3">
                                <div class="w-9 h-9 min-w-[36px] max-w-[36px] rounded-lg bg-gray-100 flex items-center justify-center text-gray-300 shrink-0 overflow-hidden">
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

                <div class="min-w-0 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <h2 class="font-semibold text-gray-900 text-lg mb-4">Ventas por Categoría</h2>
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

            <!-- Fila 3: Métodos de Pago | Actividad Reciente -->
            <div class="admin-two-col-grid gap-6 w-full">

                <div class="min-w-0 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <h2 class="font-semibold text-gray-900 text-lg mb-5">Métodos de Pago</h2>
                        <div class="space-y-5">
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

                <div class="min-w-0 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <h2 class="font-semibold text-gray-900 text-lg mb-4">Actividad Reciente</h2>
                        <ul class="space-y-3.5">
                            <li v-for="(a, i) in actividadReciente" :key="i" class="flex items-start gap-3">
                                <div class="rounded-full bg-red-50 text-brand flex items-center justify-center shrink-0 text-xs" style="width:36px;height:36px;min-width:36px">
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