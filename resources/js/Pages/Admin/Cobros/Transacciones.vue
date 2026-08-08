<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useFormatters } from '@/composables/useFormatters';
import { useCobroAcciones } from '@/composables/useCobroAcciones';

const props = defineProps({
    transacciones: Object,
    filtros: Object,
    porEstado: Array,
    porTipo: Array,
    totalGeneral: Number,
    totalRegistros: Number,
});

const { money, formatDateTime } = useFormatters();
const { aprobar, reembolsar } = useCobroAcciones();

const q = ref(props.filtros.q || '');
const tipo = ref(props.filtros.tipo || '');
const estado = ref(props.filtros.estado || '');
const metodoPago = ref(props.filtros.metodo_pago || '');
const desde = ref(props.filtros.desde || '');
const hasta = ref(props.filtros.hasta || '');

let timeout = null;
function aplicarFiltros() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.cobros.transacciones'), {
            q: q.value || undefined,
            tipo: tipo.value || undefined,
            estado: estado.value || undefined,
            metodo_pago: metodoPago.value || undefined,
            desde: desde.value || undefined,
            hasta: hasta.value || undefined,
        }, { preserveState: true, preserveScroll: true, replace: true });
    }, 350);
}
watch([q, tipo, estado, metodoPago, desde, hasta], aplicarFiltros);

const estadoColores = {
    aprobada: 'bg-green-100 text-green-700',
    pendiente: 'bg-amber-100 text-amber-700',
    rechazada: 'bg-red-100 text-red-700',
    reembolsada: 'bg-gray-100 text-gray-600',
    retirada: 'bg-blue-100 text-blue-700',
};

const estadoDotColores = {
    aprobada: '#10B981',
    pendiente: '#F5A623',
    rechazada: '#EF4444',
    reembolsada: '#9CA3AF',
    retirada: '#2563EB',
};
</script>

<template>
    <Head title="Todas las Transacciones" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Cobros y Pagos &gt; Todas las transacciones</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- Volver -->
            <div class="mb-6">
                <Link :href="route('admin.cobros.index')" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-brand transition">
                    <i class="pi pi-arrow-left text-xs"></i>
                    Volver a Cobros y Pagos
                </Link>
            </div>

            <!-- Encabezado + totales generales -->
            <div class="admin-card p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">Todas las Transacciones</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ totalRegistros }} registros en total &middot; {{ money(totalGeneral) }} acumulado</p>
                </div>
                <a :href="route('admin.cobros.exportar', { q: q || undefined, tipo: tipo || undefined, desde: desde || undefined, hasta: hasta || undefined })"
                    class="admin-btn-primary self-start sm:self-auto">
                    <i class="pi pi-download"></i> Exportar
                </a>
            </div>

            <!-- Desglose por estado -->
            <div class="admin-estado-grid gap-4 mb-6 w-full">
                <button v-for="e in porEstado" :key="e.estado" type="button" @click="estado = (estado === e.estado ? '' : e.estado)"
                    class="min-w-0 admin-card px-5 py-4 text-left transition"
                    :class="estado === e.estado ? 'ring-2 ring-brand/40' : 'hover:border-gray-300'">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: estadoDotColores[e.estado] }"></span>
                        <span class="text-xs text-gray-500">{{ e.label }}</span>
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ e.cantidad }}</p>
                    <p class="text-xs text-gray-400">{{ money(e.total) }}</p>
                </button>
            </div>

            <!-- Desglose por tipo -->
            <div class="admin-card p-6 mb-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Desglose por tipo</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div v-for="t in porTipo" :key="t.tipo">
                        <p class="text-xs text-gray-400 mb-1">{{ t.label }}</p>
                        <p class="text-base font-bold text-gray-900">{{ money(t.total) }}</p>
                        <p class="text-xs text-gray-400">{{ t.cantidad }} transacciones</p>
                    </div>
                </div>
            </div>

            <!-- Tabla completa -->
            <div class="admin-card flex flex-col justify-between">
                <div class="flex flex-col flex-1">
                    <div class="px-6 pt-6">
                        <h2 class="text-lg font-semibold text-gray-900">Historial completo</h2>
                    </div>

                    <!-- Filtros -->
                    <div class="flex flex-wrap items-center gap-3 px-6 py-5">
                        <div class="relative flex-1 min-w-[180px]">
                            <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input v-model="q" type="text" placeholder="Buscar usuario..." class="admin-input pl-10 py-2.5">
                        </div>
                        <select v-model="tipo" class="admin-input w-auto py-2.5">
                            <option value="">Todos los tipos</option>
                            <option value="suscripcion">Suscripción</option>
                            <option value="compra_contenido">Compra de contenido</option>
                            <option value="propina">Propina</option>
                            <option value="retiro">Retiro</option>
                        </select>
                        <select v-model="estado" class="admin-input w-auto py-2.5">
                            <option value="">Todos los estados</option>
                            <option value="aprobada">Aprobada</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="rechazada">Rechazada</option>
                            <option value="reembolsada">Reembolsada</option>
                            <option value="retirada">Retirada</option>
                        </select>
                        <select v-model="metodoPago" class="admin-input w-auto py-2.5">
                            <option value="">Todos los métodos</option>
                            <option value="tarjeta_credito">Tarjeta de Crédito</option>
                            <option value="tarjeta_debito">Tarjeta de Débito</option>
                            <option value="paypal">PayPal</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="otro">Otro</option>
                        </select>
                        <div class="flex items-center gap-1.5">
                            <input v-model="desde" type="date" class="admin-input w-auto py-2.5">
                            <span class="text-gray-400">—</span>
                            <input v-model="hasta" type="date" class="admin-input w-auto py-2.5">
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto flex-1 flex flex-col">
                        <table class="min-w-full text-sm flex-1">
                            <thead class="bg-gray-50 border-y border-gray-200">
                                <tr class="text-gray-600 uppercase tracking-wide text-xs">
                                    <th class="px-6 py-4 text-left">ID</th>
                                    <th class="px-4 py-4 text-left">Usuario</th>
                                    <th class="px-4 py-4 text-left">Tipo</th>
                                    <th class="px-4 py-4 text-left">Descripción</th>
                                    <th class="px-4 py-4 text-left">Monto</th>
                                    <th class="px-4 py-4 text-left">Comisión</th>
                                    <th class="px-4 py-4 text-left">Neto</th>
                                    <th class="px-4 py-4 text-left">Método</th>
                                    <th class="px-4 py-4 text-left">Fecha</th>
                                    <th class="px-4 py-4 text-left">Estado</th>
                                    <th class="px-6 py-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="t in transacciones.data" :key="t.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">TRX-{{ String(t.id).padStart(4, '0') }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <p class="font-semibold text-gray-800">{{ t.usuario?.nombre ?? '—' }}</p>
                                        <p class="text-xs text-gray-400">@{{ t.usuario?.apodo ?? '—' }}</p>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="font-semibold" :class="t.es_reembolso ? 'text-red-500' : 'text-green-600'">
                                            {{ t.es_reembolso ? 'Reembolso' : 'Cobro' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-gray-600">{{ t.tipo_nombre }}</td>
                                    <td class="px-4 py-4 font-semibold" :class="t.es_reembolso ? 'text-red-500' : 'text-gray-800'">
                                        {{ t.es_reembolso ? '-' : '' }}{{ money(t.monto) }}
                                    </td>
                                    <td class="px-4 py-4 text-gray-500 whitespace-nowrap">-{{ money(t.comision) }}</td>
                                    <td class="px-4 py-4 font-semibold text-brand whitespace-nowrap">{{ money(t.monto_neto) }}</td>
                                    <td class="px-4 py-4 text-gray-600 whitespace-nowrap">{{ t.metodo_pago_nombre }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-gray-500">{{ formatDateTime(t.created_at) }}</td>
                                    <td class="px-4 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="estadoColores[t.estado]">
                                            {{ t.estado_nombre }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center gap-2">
                                            <Link :href="route('admin.cobros.show', t.id)" title="Ver detalle" class="admin-table-action text-gray-600">
                                                <i class="pi pi-eye"></i>
                                            </Link>
                                            <template v-if="t.estado === 'pendiente'">
                                                <button @click="aprobar(t)" title="Aprobar" class="admin-table-action hover:bg-green-50 text-green-600">
                                                    <i class="pi pi-check"></i>
                                                </button>
                                                <button @click="reembolsar(t)" title="Reembolsar" class="admin-table-action hover:bg-red-50 text-red-600">
                                                    <i class="pi pi-replay"></i>
                                                </button>
                                            </template>
                                            <button v-else-if="t.estado === 'aprobada'" @click="reembolsar(t)" title="Reembolsar"
                                                class="admin-table-action hover:bg-red-50 text-red-600">
                                                <i class="pi pi-replay"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!transacciones.data?.length">
                                    <td colspan="11" class="text-center text-gray-400 py-12">No se encontraron transacciones.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="border-t border-gray-200 px-6 py-4">
                    <Pagination :data="transacciones" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>