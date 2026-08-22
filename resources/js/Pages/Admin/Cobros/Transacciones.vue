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

const badgeEstado = { aprobada: 'admin-cobros-badge--aprobada', pendiente: 'admin-cobros-badge--pendiente', rechazada: 'admin-cobros-badge--rechazada', reembolsada: 'admin-cobros-badge--reembolsada', retirada: 'admin-cobros-badge--retirada' };
const estadoDotColores = { aprobada: '#059669', pendiente: '#D97706', rechazada: '#DC2626', reembolsada: '#6B7280', retirada: '#2563EB' };
</script>

<template>
    <Head title="Todas las Transacciones" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Cobros y Pagos &gt; Todas las transacciones</template>

        <div class="admin-cobros-page">
            <Link :href="route('admin.cobros.index')" class="admin-user-back-link">
                <i class="pi pi-arrow-left"></i>
                Volver a Cobros y Pagos
            </Link>

            <!-- Desglose por estado -->
            <div class="admin-estado-grid gap-4 mb-6 w-full">
                <button v-for="e in porEstado" :key="e.estado" type="button" @click="estado = (estado === e.estado ? '' : e.estado)"
                    class="admin-prod-estado-chip" :class="{ 'admin-prod-estado-chip--active': estado === e.estado }">
                    <span class="admin-prod-estado-chip-dot" :style="{ background: estadoDotColores[e.estado] }"></span>
                    <span>
                        <span class="admin-prod-estado-chip-value">{{ e.cantidad }}</span>
                        <span class="admin-prod-estado-chip-label">{{ e.label }} · {{ money(e.total) }}</span>
                    </span>
                </button>
            </div>

            <!-- Tabla completa -->
            <div class="admin-cobros-card mb-6" style="margin-bottom:1.5rem">
                <div class="flex flex-col flex-1">
                    <div class="admin-cobros-card__header">
                        <div class="admin-cobros-card__header-left">
                            <div class="admin-cobros-header-icon"><i class="pi pi-list"></i></div>
                            <div>
                                <h3>Historial completo</h3>
                                <p class="admin-cobros-header-subtitle">{{ totalRegistros }} registros · {{ money(totalGeneral) }} acumulado</p>
                            </div>
                        </div>
                        <a :href="route('admin.cobros.exportar', { q: q || undefined, tipo: tipo || undefined, desde: desde || undefined, hasta: hasta || undefined })" class="admin-cobros-btn-primary">
                            <i class="pi pi-download"></i> Exportar
                        </a>
                    </div>

                    <!-- Filtros -->
                    <div class="admin-cobros-filters">
                        <div class="admin-cobros-filters__search">
                            <i class="pi pi-search"></i>
                            <input v-model="q" type="text" placeholder="Buscar usuario..." />
                        </div>
                        <select v-model="tipo">
                            <option value="">Todos los tipos</option>
                            <option value="suscripcion">Suscripción</option>
                            <option value="compra_contenido">Compra de contenido</option>
                            <option value="propina">Propina</option>
                            <option value="retiro">Retiro</option>
                        </select>
                        <select v-model="estado">
                            <option value="">Todos los estados</option>
                            <option value="aprobada">Aprobada</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="rechazada">Rechazada</option>
                            <option value="reembolsada">Reembolsada</option>
                            <option value="retirada">Retirada</option>
                        </select>
                        <select v-model="metodoPago">
                            <option value="">Todos los métodos</option>
                            <option value="tarjeta_credito">Tarjeta de Crédito</option>
                            <option value="tarjeta_debito">Tarjeta de Débito</option>
                            <option value="paypal">PayPal</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="otro">Otro</option>
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
                                    <th>Comisión</th>
                                    <th>Neto</th>
                                    <th>Método</th>
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
                                    <td class="text-gray-500 whitespace-nowrap">-{{ money(t.comision) }}</td>
                                    <td class="font-semibold whitespace-nowrap" style="color:var(--brand)">{{ money(t.monto_neto) }}</td>
                                    <td class="text-gray-600 whitespace-nowrap">{{ t.metodo_pago_nombre }}</td>
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
                                    <td colspan="11" class="admin-cobros-empty">No se encontraron transacciones.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="admin-cobros-table-footer">
                    <Pagination :data="transacciones" />
                </div>
            </div>

            <!-- Desglose por tipo -->
            <div class="admin-cobros-card">
                <div class="admin-cobros-card__header">
                    <div class="admin-cobros-card__header-left">
                        <div class="admin-cobros-header-icon"><i class="pi pi-chart-bar"></i></div>
                        <h3>Desglose por tipo</h3>
                    </div>
                </div>
                <div class="admin-cobros-tipo-grid">
                    <div v-for="t in porTipo" :key="t.tipo" class="admin-cobros-tipo-tile">
                        <p class="admin-cobros-tipo-label">{{ t.label }}</p>
                        <p class="admin-cobros-tipo-value">{{ money(t.total) }}</p>
                        <p class="admin-cobros-tipo-count">{{ t.cantidad }} transacciones</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>