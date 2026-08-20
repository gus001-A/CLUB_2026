<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
    pedidos: Object,
    filtros: Object,
    porEstado: Array,
    porMetodo: Array,
    totalGeneral: Number,
});

const { money, formatDateTime } = useFormatters();

const q = ref(props.filtros.q || '');
const estado = ref(props.filtros.estado || '');
const metodo = ref(props.filtros.metodo || '');
const desde = ref(props.filtros.desde || '');
const hasta = ref(props.filtros.hasta || '');

let timeout = null;
function aplicarFiltros() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.shop.todos'), {
            q: q.value || undefined,
            estado: estado.value || undefined,
            metodo: metodo.value || undefined,
            desde: desde.value || undefined,
            hasta: hasta.value || undefined,
        }, { preserveState: true, preserveScroll: true, replace: true });
    }, 350);
}
watch([q, estado, metodo, desde, hasta], aplicarFiltros);

const estadoColores = {
    pagado: 'bg-blue-50 text-blue-600 border border-blue-200',
    enviado: 'bg-amber-50 text-amber-600 border border-amber-200',
    entregado: 'bg-emerald-50 text-emerald-600 border border-emerald-200',
    cancelado: 'bg-red-50 text-red-600 border border-red-200',
};
const estadoLabel = { pagado: 'Procesando', enviado: 'Enviado', entregado: 'Completado', cancelado: 'Cancelado' };
const estadoDotColores = { pagado: '#2563EB', enviado: '#F59E0B', entregado: '#10B981', cancelado: '#EF4444' };
const metodoLabel = { tarjeta_credito: 'Tarjeta de Crédito', tarjeta_debito: 'Tarjeta de Débito', paypal: 'PayPal', transferencia: 'Transferencia', otro: 'Otro' };
</script>

<template>
    <Head title="Todos los Pedidos" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Pedidos &gt; Todos los pedidos</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- Volver -->
            <div class="mb-6">
                <Link :href="route('admin.shop.index')" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-brand transition">
                    <i class="pi pi-arrow-left text-xs"></i>
                    Volver a Pedidos
                </Link>
            </div>

            <!-- Encabezado + total general -->
            <div class="admin-card overflow-hidden mb-6">
                <div class="admin-card-header">
                    <span class="admin-card-header-title"><i class="pi pi-shopping-cart text-brand"></i> Todos los Pedidos</span>
                    <a :href="route('admin.shop.exportar', { estado: estado || undefined })" class="admin-btn-primary" style="padding:0.4rem 0.85rem;font-size:0.75rem">
                        <i class="pi pi-download text-xs"></i> Exportar
                    </a>
                </div>
                <p class="text-sm px-6 py-4" style="color:var(--muted)">{{ totalGeneral }} pedidos registrados en total</p>
            </div>

            <!-- Desglose por estado -->
            <div class="admin-estado-grid gap-4 mb-6 w-full">
                <button v-for="e in porEstado" :key="e.estado" type="button" @click="estado = (estado === e.estado ? '' : e.estado)"
                    class="min-w-0 admin-card px-5 py-4 text-left transition"
                    :class="estado === e.estado ? 'ring-2 ring-brand/40' : 'hover:border-gray-300'">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="rounded-full shrink-0" :style="{ backgroundColor: estadoDotColores[e.estado], width: '8px', height: '8px' }"></span>
                        <span class="text-xs text-gray-500">{{ e.label }}</span>
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ e.cantidad }}</p>
                </button>
            </div>

            <!-- Desglose por método de pago -->
            <div class="admin-card overflow-hidden mb-6">
                <div class="admin-card-header">
                    <span class="admin-card-header-title"><i class="pi pi-credit-card text-brand"></i> Desglose por método de pago</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-6">
                    <div v-for="m in porMetodo" :key="m.metodo">
                        <p class="text-xs text-gray-400 mb-1">{{ m.label }}</p>
                        <p class="text-base font-bold text-gray-900">{{ m.cantidad }} pedidos</p>
                    </div>
                </div>
            </div>

            <!-- Tabla completa -->
            <div class="admin-card overflow-hidden flex flex-col justify-between">
                <div class="flex flex-col flex-1">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-list text-brand"></i> Historial completo</span>
                    </div>

                    <!-- Filtros -->
                    <div class="flex flex-wrap items-center gap-3 px-6 py-5">
                        <div class="relative flex-1 min-w-[180px]">
                            <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input v-model="q" type="text" placeholder="Buscar pedido, usuario..." class="admin-input pl-10 py-2.5">
                        </div>
                        <select v-model="estado" class="admin-input w-auto py-2.5">
                            <option value="">Todos los estados</option>
                            <option value="pagado">Procesando</option>
                            <option value="enviado">Enviado</option>
                            <option value="entregado">Completado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                        <select v-model="metodo" class="admin-input w-auto py-2.5">
                            <option value="">Todos los métodos</option>
                            <option value="tarjeta_credito">Tarjeta de Crédito</option>
                            <option value="tarjeta_debito">Tarjeta de Débito</option>
                            <option value="paypal">PayPal</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                        <div class="flex items-center gap-1.5">
                            <input v-model="desde" type="date" class="admin-input w-auto py-2.5">
                            <span class="text-gray-400">—</span>
                            <input v-model="hasta" type="date" class="admin-input w-auto py-2.5">
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto flex-1 flex flex-col">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 border-y border-gray-200">
                                <tr class="text-gray-600 uppercase tracking-wide text-xs">
                                    <th class="px-6 py-4 text-left">Pedido</th>
                                    <th class="px-4 py-4 text-left">Usuario</th>
                                    <th class="px-4 py-4 text-left">Productos</th>
                                    <th class="px-4 py-4 text-left">Total</th>
                                    <th class="px-4 py-4 text-left">Método</th>
                                    <th class="px-4 py-4 text-left">Estado</th>
                                    <th class="px-4 py-4 text-left">Fecha</th>
                                    <th class="px-6 py-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="p in pedidos.data" :key="p.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap">#{{ p.numero_pedido }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <p class="font-medium text-gray-800 text-xs">{{ p.usuario?.nombre ?? '—' }}</p>
                                        <p class="text-[11px] text-gray-400">@{{ p.usuario?.apodo }}</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-1">
                                            <img v-for="(img, i) in p.miniaturas" :key="i" :src="img" style="width:26px;height:26px;object-fit:cover" class="rounded-md border border-gray-200 -ml-1.5 first:ml-0" />
                                            <span class="text-xs text-gray-500 ml-1">{{ p.total_items }} artículo{{ p.total_items === 1 ? '' : 's' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-gray-800 text-xs font-semibold whitespace-nowrap">{{ money(p.total) }}</td>
                                    <td class="px-4 py-4 text-gray-600 text-xs whitespace-nowrap">{{ metodoLabel[p.metodo_pago] ?? p.metodo_pago }}</td>
                                    <td class="px-4 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="estadoColores[p.estado]">
                                            {{ estadoLabel[p.estado] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-gray-500 whitespace-nowrap text-xs">{{ formatDateTime(p.created_at) }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center gap-2">
                                            <Link :href="route('admin.shop.show', p.id)" class="admin-table-action text-gray-600">
                                                <i class="pi pi-eye"></i>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!pedidos.data?.length">
                                    <td colspan="8" class="text-center text-gray-400 py-12">No se encontraron pedidos.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="border-t border-gray-200 px-6 py-4">
                    <Pagination :data="pedidos" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>