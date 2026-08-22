<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useFormatters } from '@/composables/useFormatters';
import { useCobroAcciones } from '@/composables/useCobroAcciones';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    transaccion: Object,
});

const { money, formatDateTime } = useFormatters();
const { aprobar, reembolsar } = useCobroAcciones();
const toast = useToast();

const badgeEstado = { aprobada: 'admin-cobros-badge--aprobada', pendiente: 'admin-cobros-badge--pendiente', rechazada: 'admin-cobros-badge--rechazada', reembolsada: 'admin-cobros-badge--reembolsada', retirada: 'admin-cobros-badge--retirada' };

const tipoIconos = {
    suscripcion: 'pi-refresh',
    compra_contenido: 'pi-shopping-bag',
    propina: 'pi-heart-fill',
    retiro: 'pi-wallet',
};

function copiar(texto) {
    navigator.clipboard.writeText(texto);
    toast.success('Copiado al portapapeles.');
}
</script>

<template>
    <Head :title="`Transacción TRX-${String(transaccion.id).padStart(4, '0')}`" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Cobros y Pagos &gt; Transacción TRX-{{ String(transaccion.id).padStart(4, '0') }}</template>

        <div class="admin-cobros-page">
            <Link :href="route('admin.cobros.index')" class="admin-user-back-link">
                <i class="pi pi-arrow-left"></i>
                Volver a Cobros y Pagos
            </Link>

            <div class="admin-cobros-show-grid gap-6 w-full">
                <!-- Detalle de la transacción -->
                <div class="admin-cobros-card min-w-0" style="grid-area:detalle">
                    <div class="admin-cobros-detail-header">
                        <div class="flex items-center gap-4">
                            <div class="admin-cobros-detail-icon">
                                <i class="pi" :class="tipoIconos[transaccion.tipo] || 'pi-dollar'"></i>
                            </div>
                            <div>
                                <p class="admin-cobros-tx-id">TRX-{{ String(transaccion.id).padStart(4, '0') }}</p>
                                <h2 class="text-xl font-semibold text-gray-900 mt-0.5">{{ transaccion.tipo_nombre }}</h2>
                            </div>
                        </div>
                        <span class="admin-cobros-badge" :class="badgeEstado[transaccion.estado]" style="padding:0.4rem 0.9rem;font-size:0.78rem">
                            <span class="admin-cobros-badge-dot"></span>{{ transaccion.estado_nombre }}
                        </span>
                    </div>

                    <dl class="admin-cobros-detail-grid">
                        <div class="admin-cobros-detail-item">
                            <dt>Monto</dt>
                            <dd class="admin-cobros-detail-item--big">{{ money(transaccion.monto) }}</dd>
                        </div>
                        <div class="admin-cobros-detail-item">
                            <dt>Comisión</dt>
                            <dd style="color:#DC2626">-{{ money(transaccion.comision) }}</dd>
                        </div>
                        <div class="admin-cobros-detail-item">
                            <dt>Monto neto</dt>
                            <dd style="color:var(--brand)">{{ money(transaccion.monto_neto) }}</dd>
                        </div>
                        <div class="admin-cobros-detail-item">
                            <dt>Moneda</dt>
                            <dd>{{ transaccion.moneda }}</dd>
                        </div>
                        <div class="admin-cobros-detail-item">
                            <dt>Método de pago</dt>
                            <dd>{{ transaccion.metodo_pago_nombre }}</dd>
                        </div>
                        <div class="admin-cobros-detail-item min-w-0">
                            <dt>ID de pago (pasarela)</dt>
                            <dd v-if="transaccion.pago_id">
                                <button @click="copiar(transaccion.pago_id)" class="flex items-center gap-1.5 hover:text-brand transition group max-w-full">
                                    <span class="font-mono truncate">{{ transaccion.pago_id }}</span>
                                    <i class="pi pi-copy text-[10px] text-gray-300 group-hover:text-brand shrink-0"></i>
                                </button>
                            </dd>
                            <dd v-else>—</dd>
                        </div>
                        <div class="admin-cobros-detail-item">
                            <dt>Fecha de creación</dt>
                            <dd>{{ formatDateTime(transaccion.created_at) }}</dd>
                        </div>
                        <div class="admin-cobros-detail-item">
                            <dt>Última actualización</dt>
                            <dd>{{ formatDateTime(transaccion.updated_at) }}</dd>
                        </div>
                    </dl>

                    <!-- Metadatos -->
                    <div v-if="transaccion.metadatos" class="admin-cobros-metadata">
                        <div class="flex items-center justify-between mb-2">
                            <p class="admin-cobros-tipo-label" style="margin:0">Metadatos</p>
                            <button @click="copiar(JSON.stringify(transaccion.metadatos, null, 2))" class="text-xs font-medium text-gray-400 hover:text-brand transition flex items-center gap-1">
                                <i class="pi pi-copy text-[10px]"></i> Copiar
                            </button>
                        </div>
                        <pre>{{ JSON.stringify(transaccion.metadatos, null, 2) }}</pre>
                    </div>
                </div>

                <!-- Usuario -->
                <div class="admin-cobros-card min-w-0" style="grid-area:usuario">
                    <div class="admin-cobros-card__header">
                        <div class="admin-cobros-card__header-left">
                            <div class="admin-cobros-header-icon"><i class="pi pi-user"></i></div>
                            <h3>Usuario</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <Link v-if="transaccion.usuario" :href="route('admin.usuarios.show', transaccion.usuario.id)" class="admin-cobros-user-link">
                            <div class="admin-dash-avatar">{{ transaccion.usuario.nombre?.charAt(0)?.toUpperCase() ?? 'U' }}</div>
                            <div class="min-w-0">
                                <p class="admin-cobros-tx-name truncate">{{ transaccion.usuario.nombre }}</p>
                                <p class="admin-cobros-tx-handle truncate">@{{ transaccion.usuario.apodo }}</p>
                            </div>
                            <i class="pi pi-chevron-right text-xs text-gray-300 ml-auto shrink-0"></i>
                        </Link>
                        <p v-else class="admin-cobros-empty" style="padding:0">Usuario no encontrado (id inválido o eliminado).</p>
                    </div>
                </div>

                <!-- Creador -->
                <div v-if="transaccion.creador" class="admin-cobros-card min-w-0" style="grid-area:creador">
                    <div class="admin-cobros-card__header">
                        <div class="admin-cobros-card__header-left">
                            <div class="admin-cobros-header-icon"><i class="pi pi-star"></i></div>
                            <h3>Creador</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <Link v-if="transaccion.creador.usuario" :href="route('admin.usuarios.show', transaccion.creador.usuario.id)" class="admin-cobros-user-link">
                            <div class="admin-dash-avatar">{{ transaccion.creador.usuario?.nombre?.charAt(0)?.toUpperCase() ?? 'C' }}</div>
                            <div class="min-w-0">
                                <p class="admin-cobros-tx-name truncate">{{ transaccion.creador.usuario?.nombre ?? '—' }}</p>
                                <p class="admin-cobros-tx-handle truncate">@{{ transaccion.creador.usuario?.apodo ?? '—' }}</p>
                            </div>
                            <i class="pi pi-chevron-right text-xs text-gray-300 ml-auto shrink-0"></i>
                        </Link>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="admin-prod-actions-card" style="grid-area:acciones">
                    <div class="admin-prod-actions-card-header"><h3><i class="pi pi-bolt"></i> Acciones</h3></div>
                    <div class="admin-prod-actions-card-body">
                        <button v-if="transaccion.estado === 'pendiente'" @click="aprobar(transaccion)" class="admin-prod-btn-edit">
                            <i class="pi pi-check"></i><span>Aprobar transacción</span>
                        </button>
                        <button v-if="['pendiente', 'aprobada'].includes(transaccion.estado)" @click="reembolsar(transaccion)" class="admin-prod-btn-delete">
                            <i class="pi pi-replay"></i><span>Reembolsar</span>
                        </button>
                        <p v-if="!['pendiente', 'aprobada'].includes(transaccion.estado)" class="admin-cobros-empty" style="padding:0.5rem 0">
                            No hay acciones disponibles para este estado.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>