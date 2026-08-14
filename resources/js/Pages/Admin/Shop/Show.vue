<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps({
    pedido: Object,
});

const toast = useToast();
const { confirm } = useConfirm();

const masAccionesAbierto = ref(false);
function toggleMasAcciones() {
    masAccionesAbierto.value = !masAccionesAbierto.value;
}
function cerrarMasAcciones() {
    masAccionesAbierto.value = false;
}

function money(v) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(v ?? 0);
}
function formatDate(v) {
    if (!v) return '—';
    return new Date(v).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' }) +
        ' - ' + new Date(v).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
}

const estadoColores = {
    pagado: 'bg-blue-100 text-blue-700',
    enviado: 'bg-amber-100 text-amber-700',
    entregado: 'bg-green-100 text-green-700',
    cancelado: 'bg-red-100 text-red-700',
};
const estadoLabel = { pagado: 'Procesando', enviado: 'Enviado', entregado: 'Completado', cancelado: 'Cancelado' };
const metodoLabel = { tarjeta_credito: 'Tarjeta de Crédito', tarjeta_debito: 'Tarjeta de Débito', paypal: 'PayPal', transferencia: 'Transferencia', otro: 'Otro' };

// --- Proceso del pedido (según lo que sí podemos saber con el estado actual) ---
const pasos = computed(() => {
    const p = props.pedido;
    const orden = ['pagado', 'enviado', 'entregado'];
    const idxActual = orden.indexOf(p.estado);

    return [
        {
            titulo: 'Pedido realizado',
            texto: 'El cliente realizó el pedido correctamente.',
            fecha: p.created_at,
            hecho: true,
        },
        {
            titulo: 'Pago aprobado',
            texto: 'El pago fue procesado y aprobado.',
            fecha: p.created_at,
            hecho: idxActual >= 0,
        },
        {
            titulo: 'Enviado',
            texto: 'El pedido ha sido enviado.',
            fecha: p.estado === 'enviado' || p.estado === 'entregado' ? p.updated_at : null,
            hecho: idxActual >= 1,
        },
        {
            titulo: 'Entregado',
            texto: 'El pedido fue entregado al cliente.',
            fecha: p.estado === 'entregado' ? p.updated_at : null,
            hecho: idxActual >= 2,
        },
    ];
});

const direccion = computed(() => props.pedido.direccion_envio || {});

async function cambiarEstado(nuevo) {
    cerrarMasAcciones();
    const nombres = { enviado: 'enviado', entregado: 'entregado', cancelado: 'cancelado' };
    const ok = await confirm(`Se marcará el pedido #${props.pedido.numero_pedido} como "${nombres[nuevo]}".`, {
        title: 'Actualizar pedido',
        confirmLabel: 'Sí, actualizar',
        danger: nuevo === 'cancelado',
    });
    if (!ok) return;

    router.post(route('admin.shop.actualizar-estado', props.pedido.id), { estado: nuevo }, { preserveScroll: true });
}

function accionProximamente(nombre) {
    toast.success(`"${nombre}" estará disponible próximamente.`);
}
</script>

<template>
    <Head :title="`Pedido #${pedido.numero_pedido}`" />

    <AdminLayout>
        <template #title>Detalle del Pedido #{{ pedido.numero_pedido }}</template>
        <template #breadcrumb>
            <span class="inline-flex items-center gap-1">
                Dashboard <i class="pi pi-angle-right text-[10px] mx-0.5"></i> Shop
                <i class="pi pi-angle-right text-[10px] mx-0.5"></i> Pedidos
                <i class="pi pi-angle-right text-[10px] mx-0.5"></i>
                <span class="text-brand font-medium">#{{ pedido.numero_pedido }}</span>
            </span>
        </template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <Link :href="route('admin.shop.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand mb-4">
                <i class="pi pi-arrow-left text-xs"></i> Volver a Shop
            </Link>

            <!-- Barra de resumen -->
            <div class="admin-card p-5 mb-6 flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap gap-8">
                    <div>
                        <p class="text-xs text-gray-400">Pedido</p>
                        <p class="font-semibold text-gray-800">#{{ pedido.numero_pedido }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Realizado el {{ formatDate(pedido.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Estado Actual</p>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-medium" :class="estadoColores[pedido.estado]">
                            {{ estadoLabel[pedido.estado] }}
                        </span>
                        <p class="text-xs text-gray-400 mt-1">Actualizado el {{ formatDate(pedido.updated_at) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Total</p>
                        <p class="font-semibold text-gray-800">{{ money(pedido.total) }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ pedido.items.length }} artículos</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Método de Pago</p>
                        <p class="font-semibold text-gray-800">{{ pedido.metodo_pago ? metodoLabel[pedido.metodo_pago] : '—' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="accionProximamente('Imprimir')" class="admin-btn-secondary inline-flex items-center gap-1.5">
                        <i class="pi pi-print text-xs"></i> Imprimir
                    </button>
                    <div class="relative">
                        <button @click="toggleMasAcciones" class="text-sm text-brand border border-brand/40 rounded-xl px-4 py-2.5 hover:bg-brand/5 flex items-center gap-1.5 transition">
                            Más acciones <i class="pi text-[10px] transition-transform" :class="masAccionesAbierto ? 'pi-chevron-up' : 'pi-chevron-down'"></i>
                        </button>
                        <div v-if="masAccionesAbierto" @click="cerrarMasAcciones" class="fixed inset-0 z-30"></div>
                        <div v-if="masAccionesAbierto" class="admin-dropdown" style="width:220px;padding:0.375rem 0">
                            <button v-if="pedido.estado === 'pagado'" @click="cambiarEstado('enviado')" class="admin-dropdown-item">Marcar como enviado</button>
                            <button v-if="pedido.estado === 'enviado'" @click="cambiarEstado('entregado')" class="admin-dropdown-item">Marcar como entregado</button>
                            <button v-if="!['entregado', 'cancelado'].includes(pedido.estado)" @click="cambiarEstado('cancelado')" class="admin-dropdown-item admin-dropdown-item--danger">Cancelar pedido</button>
                            <p v-if="['entregado', 'cancelado'].includes(pedido.estado)" class="px-5 py-2.5 text-xs text-gray-400">No hay más acciones disponibles.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-pedido-main-grid gap-6">
                <!-- Productos del Pedido -->
                <div class="admin-card overflow-hidden" style="grid-area:productos">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-box text-brand"></i> Productos del Pedido</span>
                    </div>
                    <div class="p-5">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-400 border-b border-gray-100">
                                <th class="pb-2 font-medium">Producto</th>
                                <th class="pb-2 font-medium text-right">Precio</th>
                                <th class="pb-2 font-medium text-right">Cantidad</th>
                                <th class="pb-2 font-medium text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in pedido.items" :key="item.id" class="border-b border-gray-50 last:border-0">
                                <td class="py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="rounded-lg bg-gray-100 flex items-center justify-center text-gray-300 shrink-0 overflow-hidden" style="width:40px;height:40px">
                                            <img v-if="item.producto.imagen" :src="item.producto.imagen" class="w-full h-full object-cover" />
                                            <i v-else class="pi pi-box text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800">{{ item.producto.nombre }}</p>
                                            <p class="text-xs text-gray-400">SKU: {{ item.producto.sku }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-right text-gray-600">{{ money(item.precio) }}</td>
                                <td class="py-3 text-right text-gray-600">{{ item.cantidad }}</td>
                                <td class="py-3 text-right font-medium text-gray-800">{{ money(item.total) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4 pt-4 border-t border-gray-100 space-y-2 text-sm max-w-xs ml-auto">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="text-gray-800">{{ money(pedido.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Envío</span>
                            <span class="text-gray-800">{{ money(pedido.envio) }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-100 font-semibold">
                            <span class="text-gray-800">Total</span>
                            <span class="text-brand">{{ money(pedido.total) }}</span>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Columna derecha -->
                <div class="space-y-6" style="grid-area:derecha">
                    <!-- Información del Cliente -->
                    <div class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-user text-brand"></i> Información del Cliente</span>
                        </div>
                        <div class="p-5">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="admin-icon-circle" style="width:40px;height:40px">
                                <i class="pi pi-user"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800 text-sm">{{ pedido.usuario?.nombre ?? '—' }}</p>
                                <p class="text-xs text-gray-400">{{ pedido.usuario?.email ?? '—' }}</p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">{{ pedido.usuario?.telefono ?? 'Sin teléfono registrado' }}</p>
                        <Link v-if="pedido.usuario" :href="route('admin.usuarios.index', { q: pedido.usuario.apodo })" class="text-brand text-xs font-medium hover:underline">
                            Ver perfil del usuario
                        </Link>
                        </div>
                    </div>

                    <!-- Información de Envío -->
                    <div class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-truck text-brand"></i> Información de Envío</span>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full" :class="estadoColores[pedido.estado]">{{ estadoLabel[pedido.estado] }}</span>
                        </div>
                        <div class="p-5">
                        <div class="text-sm text-gray-600 space-y-1">
                            <template v-if="direccion.calle">
                                <p>{{ direccion.calle }}</p>
                                <p>{{ direccion.colonia }}</p>
                                <p>{{ direccion.ciudad }}, {{ direccion.cp }}</p>
                            </template>
                            <p v-else class="text-gray-400">Sin dirección registrada.</p>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-100 text-sm space-y-1.5">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Número de guía</span>
                                <span class="text-gray-700">{{ pedido.numero_seguimiento || '—' }}</span>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- Información de Pago -->
                    <div class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-credit-card text-brand"></i> Información de Pago</span>
                        </div>
                        <div class="text-sm space-y-1.5 p-5">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Método de Pago</span>
                                <span class="text-gray-700">{{ pedido.metodo_pago ? metodoLabel[pedido.metodo_pago] : '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Referencia</span>
                                <span class="text-gray-700">{{ pedido.pago_id || '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Pagado el</span>
                                <span class="text-gray-700">{{ formatDate(pedido.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-pedido-resumen-grid gap-6 mt-6">
                <!-- Proceso del Pedido -->
                <div class="admin-card overflow-hidden" style="grid-area:proceso">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-list-check text-brand"></i> Proceso del Pedido</span>
                    </div>
                    <div class="p-5">
                    <ul class="space-y-4">
                        <li v-for="(paso, i) in pasos" :key="i" class="flex items-start gap-3">
                            <div
                                class="rounded-full flex items-center justify-center shrink-0 mt-0.5"
                                :class="paso.hecho ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-300'"
                                style="width:22px;height:22px"
                            >
                                <i class="pi pi-check text-[10px]"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium" :class="paso.hecho ? 'text-gray-800' : 'text-gray-400'">{{ paso.titulo }}</p>
                                <p class="text-xs text-gray-400">{{ paso.texto }}</p>
                                <p v-if="paso.fecha" class="text-[11px] text-gray-400 mt-0.5">{{ formatDate(paso.fecha) }}</p>
                            </div>
                        </li>
                    </ul>

                    <div v-if="pedido.estado === 'entregado'" class="mt-5 pt-4 border-t border-gray-100 text-center">
                        <i class="pi pi-heart-fill text-brand"></i>
                        <p class="text-sm font-serif font-semibold text-gray-800 mt-1">Gracias por confiar en Club de Fantasías</p>
                    </div>
                    </div>
                </div>

                <!-- Resumen del Pedido -->
                <div class="admin-card overflow-hidden" style="grid-area:resumen">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-receipt text-brand"></i> Resumen del Pedido</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 p-5">
                        <div class="flex items-center gap-2">
                            <div class="admin-icon-circle" style="width:36px;height:36px"><i class="pi pi-box text-xs"></i></div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ pedido.items.length }}</p>
                                <p class="text-[10px] text-gray-400">Artículos</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="admin-icon-circle" style="width:36px;height:36px"><i class="pi pi-tag text-xs"></i></div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ money(pedido.subtotal) }}</p>
                                <p class="text-[10px] text-gray-400">Subtotal</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="admin-icon-circle" style="width:36px;height:36px"><i class="pi pi-truck text-xs"></i></div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ money(pedido.envio) }}</p>
                                <p class="text-[10px] text-gray-400">Envío</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="admin-icon-circle" style="width:36px;height:36px"><i class="pi pi-dollar text-xs"></i></div>
                            <div>
                                <p class="text-sm font-semibold text-brand">{{ money(pedido.total) }}</p>
                                <p class="text-[10px] text-gray-400">Total</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones del Pedido -->
                <div class="admin-card overflow-hidden" style="grid-area:acciones">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-bolt text-brand"></i> Acciones del Pedido</span>
                    </div>
                    <div class="space-y-2 p-5">
                        <button @click="accionProximamente('Reenviar Confirmación')" class="w-full text-left text-sm text-gray-600 border border-gray-300 rounded-xl px-4 py-2.5 hover:bg-gray-50 flex items-center gap-2">
                            <i class="pi pi-envelope text-xs"></i> Reenviar Confirmación
                        </button>
                        <button @click="accionProximamente('Generar Nota de Crédito')" class="w-full text-left text-sm text-gray-600 border border-gray-300 rounded-xl px-4 py-2.5 hover:bg-gray-50 flex items-center gap-2">
                            <i class="pi pi-file text-xs"></i> Generar Nota de Crédito
                        </button>
                        <button @click="accionProximamente('Reportar un Problema')" class="w-full text-left text-sm text-red-600 border border-red-200 rounded-xl px-4 py-2.5 hover:bg-red-50 flex items-center gap-2">
                            <i class="pi pi-exclamation-triangle text-xs"></i> Reportar un Problema
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>