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

const estadoColores = {
    aprobada: 'bg-green-100 text-green-700',
    pendiente: 'bg-amber-100 text-amber-700',
    rechazada: 'bg-red-100 text-red-700',
    reembolsada: 'bg-gray-100 text-gray-600',
    retirada: 'bg-blue-100 text-blue-700',
};

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

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- Volver -->
            <div class="mb-6">
                <Link :href="route('admin.cobros.index')" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-brand transition">
                    <i class="pi pi-arrow-left text-xs"></i>
                    Volver a Cobros y Pagos
                </Link>
            </div>

            <div class="admin-cobros-show-grid gap-6 w-full">

                <!-- Detalle de la transacción -->
                <div class="min-w-0 admin-card p-6" style="grid-area:detalle">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <div class="flex items-center gap-4">
                            <div class="admin-icon-gradient shrink-0" style="width:52px;height:52px">
                                <i class="pi text-xl" :class="tipoIconos[transaccion.tipo] || 'pi-dollar'"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-mono">TRX-{{ String(transaccion.id).padStart(4, '0') }}</p>
                                <h2 class="text-xl font-semibold text-gray-900 mt-0.5">{{ transaccion.tipo_nombre }}</h2>
                            </div>
                        </div>
                        <span class="px-3 py-1.5 rounded-full text-sm font-semibold self-start" :class="estadoColores[transaccion.estado]">
                            {{ transaccion.estado_nombre }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-x-6 gap-y-5 border-t border-gray-100 pt-6">
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Monto</p>
                            <p class="text-2xl font-bold text-gray-900">{{ money(transaccion.monto) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Comisión</p>
                            <p class="text-lg font-semibold text-red-500">-{{ money(transaccion.comision) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Monto neto</p>
                            <p class="text-lg font-semibold text-brand">{{ money(transaccion.monto_neto) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Moneda</p>
                            <p class="text-sm font-medium text-gray-700">{{ transaccion.moneda }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Método de pago</p>
                            <p class="text-sm font-medium text-gray-700">{{ transaccion.metodo_pago_nombre }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-400 mb-1">ID de pago (pasarela)</p>
                            <button v-if="transaccion.pago_id" @click="copiar(transaccion.pago_id)"
                                class="flex items-center gap-1.5 text-sm font-medium text-gray-700 hover:text-brand transition group max-w-full">
                                <span class="font-mono truncate">{{ transaccion.pago_id }}</span>
                                <i class="pi pi-copy text-xs text-gray-300 group-hover:text-brand shrink-0"></i>
                            </button>
                            <p v-else class="text-sm font-medium text-gray-700">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Fecha de creación</p>
                            <p class="text-sm font-medium text-gray-700">{{ formatDateTime(transaccion.created_at) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Última actualización</p>
                            <p class="text-sm font-medium text-gray-700">{{ formatDateTime(transaccion.updated_at) }}</p>
                        </div>
                    </div>

                    <!-- Metadatos -->
                    <div v-if="transaccion.metadatos" class="border-t border-gray-100 mt-6 pt-6">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-gray-400">Metadatos</p>
                            <button @click="copiar(JSON.stringify(transaccion.metadatos, null, 2))"
                                class="text-xs font-medium text-gray-400 hover:text-brand transition flex items-center gap-1">
                                <i class="pi pi-copy text-[10px]"></i> Copiar
                            </button>
                        </div>
                        <pre class="bg-gray-50 border border-gray-100 rounded-xl p-4 text-xs text-gray-600 overflow-x-auto">{{ JSON.stringify(transaccion.metadatos, null, 2) }}</pre>
                    </div>
                </div>

                <!-- Usuario -->
                <div class="min-w-0 admin-card overflow-hidden" style="grid-area:usuario">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-user text-brand"></i> Usuario</span>
                    </div>
                    <div class="p-6">
                        <Link v-if="transaccion.usuario" :href="route('admin.usuarios.show', transaccion.usuario.id)"
                            class="flex items-center gap-3 group -m-1 p-1 rounded-lg hover:bg-gray-50 transition">
                            <div class="rounded-full bg-brand/10 text-brand font-semibold flex items-center justify-center shrink-0 text-sm" style="width:40px;height:40px">
                                {{ transaccion.usuario.nombre?.charAt(0)?.toUpperCase() ?? 'U' }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-800 text-sm truncate group-hover:text-brand transition">{{ transaccion.usuario.nombre }}</p>
                                <p class="text-xs text-gray-400 truncate">@{{ transaccion.usuario.apodo }}</p>
                            </div>
                            <i class="pi pi-chevron-right text-xs text-gray-300 ml-auto shrink-0 group-hover:text-brand transition"></i>
                        </Link>
                        <p v-else class="text-sm text-gray-400">Usuario no encontrado (id inválido o eliminado).</p>
                    </div>
                </div>

                <!-- Creador (si aplica) -->
                <div v-if="transaccion.creador" class="min-w-0 admin-card overflow-hidden" style="grid-area:creador">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-star text-brand"></i> Creador</span>
                    </div>
                    <div class="p-6">
                        <Link v-if="transaccion.creador.usuario" :href="route('admin.usuarios.show', transaccion.creador.usuario.id)"
                            class="flex items-center gap-3 group -m-1 p-1 rounded-lg hover:bg-gray-50 transition">
                            <div class="rounded-full bg-brand/10 text-brand font-semibold flex items-center justify-center shrink-0 text-sm" style="width:40px;height:40px">
                                {{ transaccion.creador.usuario?.nombre?.charAt(0)?.toUpperCase() ?? 'C' }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-800 text-sm truncate group-hover:text-brand transition">{{ transaccion.creador.usuario?.nombre ?? '—' }}</p>
                                <p class="text-xs text-gray-400 truncate">@{{ transaccion.creador.usuario?.apodo ?? '—' }}</p>
                            </div>
                            <i class="pi pi-chevron-right text-xs text-gray-300 ml-auto shrink-0 group-hover:text-brand transition"></i>
                        </Link>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="min-w-0 admin-card overflow-hidden" style="grid-area:acciones">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-bolt text-brand"></i> Acciones</span>
                    </div>
                    <div class="p-6 flex flex-col gap-2">
                        <button v-if="transaccion.estado === 'pendiente'" @click="aprobar(transaccion)" class="admin-btn-primary w-full">
                            <i class="pi pi-check"></i> Aprobar transacción
                        </button>
                        <button v-if="['pendiente', 'aprobada'].includes(transaccion.estado)" @click="reembolsar(transaccion)"
                            class="w-full flex items-center justify-center gap-2 border border-red-200 text-red-600 hover:bg-red-50 rounded-xl px-4 py-2.5 text-sm font-medium transition">
                            <i class="pi pi-replay"></i> Reembolsar
                        </button>
                        <p v-if="!['pendiente', 'aprobada'].includes(transaccion.estado)" class="text-xs text-gray-400 text-center py-2">
                            No hay acciones disponibles para este estado.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>