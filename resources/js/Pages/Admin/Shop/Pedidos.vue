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

const estadoBadgeClase = { pagado: 'admin-shop-badge--pagado', enviado: 'admin-shop-badge--enviado', entregado: 'admin-shop-badge--entregado', cancelado: 'admin-shop-badge--cancelado' };
const estadoLabel = { pagado: 'Procesando', enviado: 'Enviado', entregado: 'Completado', cancelado: 'Cancelado' };
const estadoDotColores = { pagado: '#2563EB', enviado: '#D97706', entregado: '#059669', cancelado: '#DC2626' };
const metodoLabel = { tarjeta_credito: 'Tarjeta de Crédito', tarjeta_debito: 'Tarjeta de Débito', paypal: 'PayPal', transferencia: 'Transferencia', otro: 'Otro' };
</script>

<template>
    <Head title="Todos los Pedidos" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Pedidos &gt; Todos los pedidos</template>

        <div class="admin-reportes-page">

            <Link :href="route('admin.shop.index')" class="admin-user-back-link">
                <i class="pi pi-arrow-left"></i>
                Volver a Pedidos
            </Link>

            <!-- Desglose por estado -->
            <div class="admin-estado-grid gap-4 mb-6 w-full">
                <button v-for="e in porEstado" :key="e.estado" type="button" @click="estado = (estado === e.estado ? '' : e.estado)"
                    class="admin-prod-estado-chip" :class="{ 'admin-prod-estado-chip--active': estado === e.estado }">
                    <span class="admin-prod-estado-chip-dot" :style="{ background: estadoDotColores[e.estado] }"></span>
                    <span>
                        <span class="admin-prod-estado-chip-value">{{ e.cantidad }}</span>
                        <span class="admin-prod-estado-chip-label">{{ e.label }}</span>
                    </span>
                </button>
            </div>

            <!-- Desglose por método de pago -->
            <div class="admin-cobros-card mb-6">
                <div class="admin-cobros-card__header">
                    <div class="admin-cobros-card__header-left">
                        <div class="admin-cobros-header-icon"><i class="pi pi-credit-card"></i></div>
                        <h3>Desglose por método de pago</h3>
                    </div>
                </div>
                <div class="admin-cobros-tipo-grid">
                    <div v-for="m in porMetodo" :key="m.metodo" class="admin-cobros-tipo-tile">
                        <p class="admin-cobros-tipo-label">{{ m.label }}</p>
                        <p class="admin-cobros-tipo-value">{{ m.cantidad }} pedidos</p>
                    </div>
                </div>
            </div>

            <!-- Tabla completa -->
            <div class="admin-cobros-card">
                <div class="flex flex-col flex-1">
                    <div class="admin-cobros-card__header">
                        <div class="admin-cobros-card__header-left">
                            <div class="admin-cobros-header-icon"><i class="pi pi-list"></i></div>
                            <div>
                                <h3>Historial completo</h3>
                                <p class="admin-cobros-header-subtitle">{{ totalGeneral }} pedidos registrados en total</p>
                            </div>
                        </div>
                        <a :href="route('admin.shop.exportar', { estado: estado || undefined })" class="admin-cobros-btn-primary">
                            <i class="pi pi-download"></i> Exportar
                        </a>
                    </div>

                    <!-- Filtros -->
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
                        <div class="flex items-center gap-1.5">
                            <input v-model="desde" type="date" />
                            <span class="text-gray-400">—</span>
                            <input v-model="hasta" type="date" />
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto flex-1 flex flex-col">
                        <table class="admin-cobros-table min-w-full flex-1">
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
                                    <td class="font-semibold whitespace-nowrap" style="color:var(--ink)">#{{ p.numero_pedido }}</td>
                                    <td class="whitespace-nowrap">
                                        <p class="admin-cobros-tx-name">{{ p.usuario?.nombre ?? '—' }}</p>
                                        <p class="admin-cobros-tx-handle">@{{ p.usuario?.apodo }}</p>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-1">
                                            <img v-for="(img, i) in p.miniaturas" :key="i" :src="img" style="width:26px;height:26px;object-fit:cover;border:1px solid var(--line)" class="rounded-md -ml-1.5 first:ml-0" />
                                            <span class="text-xs text-gray-500 ml-1">{{ p.total_items }} artículo{{ p.total_items === 1 ? '' : 's' }}</span>
                                        </div>
                                    </td>
                                    <td class="font-semibold whitespace-nowrap" style="color:var(--ink)">{{ money(p.total) }}</td>
                                    <td class="text-gray-600 text-xs whitespace-nowrap">{{ metodoLabel[p.metodo_pago] ?? p.metodo_pago }}</td>
                                    <td>
                                        <span class="admin-shop-badge" :class="estadoBadgeClase[p.estado]">
                                            <span class="admin-shop-badge-dot"></span>{{ estadoLabel[p.estado] }}
                                        </span>
                                    </td>
                                    <td class="text-gray-500 whitespace-nowrap text-xs">{{ formatDateTime(p.created_at) }}</td>
                                    <td>
                                        <div class="flex justify-center gap-1.5">
                                            <Link :href="route('admin.shop.show', p.id)" class="admin-dash-action-btn admin-dash-action-btn--view">
                                                <i class="pi pi-eye"></i>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!pedidos.data?.length">
                                    <td colspan="8" class="admin-cobros-empty">No se encontraron pedidos.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="admin-cobros-table-footer">
                    <Pagination :data="pedidos" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>