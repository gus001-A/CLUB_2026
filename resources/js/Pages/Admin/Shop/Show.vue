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

const estadoBadgeClase = { pagado: 'admin-shop-badge--pagado', enviado: 'admin-shop-badge--enviado', entregado: 'admin-shop-badge--entregado', cancelado: 'admin-shop-badge--cancelado' };
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

        <div class="admin-reportes-page">

            <Link :href="route('admin.shop.index')" class="admin-user-back-link">
                <i class="pi pi-arrow-left"></i> Volver a Shop
            </Link>

            <!-- Barra de resumen -->
            <div class="admin-cobros-card mb-6 flex flex-wrap items-center justify-between gap-4" style="padding:1.25rem 1.5rem;overflow:visible">
                <div class="flex flex-wrap gap-8">
                    <div>
                        <p class="admin-user-hint">Pedido</p>
                        <p class="font-semibold" style="color:var(--ink)">#{{ pedido.numero_pedido }}</p>
                        <p class="admin-user-hint" style="margin-top:0.15rem">Realizado el {{ formatDate(pedido.created_at) }}</p>
                    </div>
                    <div>
                        <p class="admin-user-hint">Estado Actual</p>
                        <span class="admin-shop-badge" :class="estadoBadgeClase[pedido.estado]" style="margin-top:0.25rem">
                            <span class="admin-shop-badge-dot"></span>{{ estadoLabel[pedido.estado] }}
                        </span>
                        <p class="admin-user-hint" style="margin-top:0.25rem">Actualizado el {{ formatDate(pedido.updated_at) }}</p>
                    </div>
                    <div>
                        <p class="admin-user-hint">Total</p>
                        <p class="font-semibold" style="color:var(--ink)">{{ money(pedido.total) }}</p>
                        <p class="admin-user-hint" style="margin-top:0.15rem">{{ pedido.items.length }} artículos</p>
                    </div>
                    <div>
                        <p class="admin-user-hint">Método de Pago</p>
                        <p class="font-semibold" style="color:var(--ink)">{{ pedido.metodo_pago ? metodoLabel[pedido.metodo_pago] : '—' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="accionProximamente('Imprimir')" class="admin-btn-secondary inline-flex items-center gap-1.5">
                        <i class="pi pi-print text-xs"></i> Imprimir
                    </button>
                    <div class="relative">
                        <button @click="toggleMasAcciones" class="text-sm text-brand border rounded-xl px-4 py-2.5 hover:bg-brand/5 flex items-center gap-1.5 transition" style="border-color:rgba(200,30,58,0.4)">
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
                <div class="admin-prod-info-card" style="grid-area:productos">
                    <div class="admin-prod-info-card-header">
                        <h3><i class="pi pi-box"></i> Productos del Pedido</h3>
                    </div>
                    <div class="admin-prod-info-card-body">
                        <table class="admin-cobros-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-right">Precio</th>
                                    <th class="text-right">Cantidad</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in pedido.items" :key="item.id">
                                    <td>
                                        <div class="flex items-center gap-2.5">
                                            <div class="admin-dash-list-thumb">
                                                <img v-if="item.producto.imagen" :src="item.producto.imagen" />
                                                <div v-else class="w-full h-full flex items-center justify-center" style="color:var(--muted-light)"><i class="pi pi-box"></i></div>
                                            </div>
                                            <div>
                                                <p class="font-medium" style="color:var(--ink)">{{ item.producto.nombre }}</p>
                                                <p class="admin-user-hint">SKU: {{ item.producto.sku }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-right text-gray-600">{{ money(item.precio) }}</td>
                                    <td class="text-right text-gray-600">{{ item.cantidad }}</td>
                                    <td class="text-right font-medium" style="color:var(--ink)">{{ money(item.total) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="admin-cobros-summary" style="max-width:280px;margin-left:auto;padding:1rem 0 0">
                            <div class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Subtotal</span>
                                <span class="admin-cobros-summary-value">{{ money(pedido.subtotal) }}</span>
                            </div>
                            <div class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Envío</span>
                                <span class="admin-cobros-summary-value">{{ money(pedido.envio) }}</span>
                            </div>
                            <div class="admin-cobros-summary-row admin-cobros-summary-row--total">
                                <span class="admin-cobros-summary-label">Total</span>
                                <span class="admin-cobros-summary-value admin-cobros-summary-value--total">{{ money(pedido.total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha -->
                <div class="flex flex-col gap-6" style="grid-area:derecha">
                    <!-- Información del Cliente -->
                    <div class="admin-prod-info-card">
                        <div class="admin-prod-info-card-header">
                            <h3><i class="pi pi-user"></i> Información del Cliente</h3>
                        </div>
                        <div class="admin-prod-info-card-body">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="admin-dash-avatar">{{ pedido.usuario?.nombre?.charAt(0)?.toUpperCase() ?? 'U' }}</div>
                                <div>
                                    <p class="font-medium text-sm" style="color:var(--ink)">{{ pedido.usuario?.nombre ?? '—' }}</p>
                                    <p class="admin-user-hint">{{ pedido.usuario?.email ?? '—' }}</p>
                                </div>
                            </div>
                            <p class="admin-user-hint mb-2">{{ pedido.usuario?.telefono ?? 'Sin teléfono registrado' }}</p>
                            <Link v-if="pedido.usuario" :href="route('admin.usuarios.index', { q: pedido.usuario.apodo })" style="color:var(--brand)" class="text-xs font-medium hover:underline">
                                Ver perfil del usuario
                            </Link>
                        </div>
                    </div>

                    <!-- Información de Envío -->
                    <div class="admin-prod-info-card">
                        <div class="admin-prod-info-card-header">
                            <h3><i class="pi pi-truck"></i> Información de Envío</h3>
                            <span class="admin-shop-badge" :class="estadoBadgeClase[pedido.estado]"><span class="admin-shop-badge-dot"></span>{{ estadoLabel[pedido.estado] }}</span>
                        </div>
                        <div class="admin-prod-info-card-body">
                            <div class="text-sm text-gray-600 space-y-1">
                                <template v-if="direccion.calle">
                                    <p>{{ direccion.calle }}</p>
                                    <p>{{ direccion.colonia }}</p>
                                    <p>{{ direccion.ciudad }}, {{ direccion.cp }}</p>
                                </template>
                                <p v-else style="color:var(--muted-light)">Sin dirección registrada.</p>
                            </div>
                            <div class="admin-cobros-summary" style="padding:0.8rem 0 0">
                                <div class="admin-cobros-summary-row">
                                    <span class="admin-cobros-summary-label">Número de guía</span>
                                    <span class="admin-cobros-summary-value">{{ pedido.numero_seguimiento || '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Pago -->
                    <div class="admin-prod-info-card">
                        <div class="admin-prod-info-card-header">
                            <h3><i class="pi pi-credit-card"></i> Información de Pago</h3>
                        </div>
                        <div class="admin-prod-info-card-body">
                            <div class="admin-cobros-summary" style="padding:0">
                                <div class="admin-cobros-summary-row">
                                    <span class="admin-cobros-summary-label">Método de Pago</span>
                                    <span class="admin-cobros-summary-value">{{ pedido.metodo_pago ? metodoLabel[pedido.metodo_pago] : '—' }}</span>
                                </div>
                                <div class="admin-cobros-summary-row">
                                    <span class="admin-cobros-summary-label">Referencia</span>
                                    <span class="admin-cobros-summary-value">{{ pedido.pago_id || '—' }}</span>
                                </div>
                                <div class="admin-cobros-summary-row">
                                    <span class="admin-cobros-summary-label">Pagado el</span>
                                    <span class="admin-cobros-summary-value">{{ formatDate(pedido.created_at) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-pedido-resumen-grid gap-6 mt-6">
                <!-- Proceso del Pedido -->
                <div class="admin-cobros-card" style="grid-area:proceso">
                    <div class="admin-cobros-card__header">
                        <div class="admin-cobros-card__header-left">
                            <div class="admin-cobros-header-icon"><i class="pi pi-list-check"></i></div>
                            <h3>Proceso del Pedido</h3>
                        </div>
                    </div>
                    <div style="padding:1.25rem 1.5rem">
                        <ul class="space-y-4">
                            <li v-for="(paso, i) in pasos" :key="i" class="flex items-start gap-3">
                                <div class="rounded-full flex items-center justify-center shrink-0 mt-0.5"
                                    :style="paso.hecho ? 'background:#059669;color:#fff' : 'background:#F3F4F6;color:#D1D5DB'"
                                    style="width:22px;height:22px">
                                    <i class="pi pi-check text-[10px]"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium" :style="paso.hecho ? 'color:var(--ink)' : 'color:var(--muted-light)'">{{ paso.titulo }}</p>
                                    <p class="admin-user-hint">{{ paso.texto }}</p>
                                    <p v-if="paso.fecha" class="admin-user-hint" style="margin-top:0.15rem">{{ formatDate(paso.fecha) }}</p>
                                </div>
                            </li>
                        </ul>

                        <div v-if="pedido.estado === 'entregado'" class="mt-5 pt-4 text-center" style="border-top:1px solid var(--line)">
                            <i class="pi pi-heart-fill" style="color:var(--brand)"></i>
                            <p class="text-sm font-serif font-semibold mt-1" style="color:var(--ink)">Gracias por confiar en Club de Fantasías</p>
                        </div>
                    </div>
                </div>

                <!-- Resumen del Pedido -->
                <div class="admin-cobros-card" style="grid-area:resumen">
                    <div class="admin-cobros-card__header">
                        <div class="admin-cobros-card__header-left">
                            <div class="admin-cobros-header-icon"><i class="pi pi-receipt"></i></div>
                            <h3>Resumen del Pedido</h3>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3" style="padding:1.25rem 1.5rem">
                        <div class="admin-user-data-item">
                            <div class="admin-user-data-icon"><i class="pi pi-box"></i></div>
                            <div>
                                <p class="admin-user-data-value">{{ pedido.items.length }}</p>
                                <p class="admin-user-data-label" style="margin-top:0.1rem">Artículos</p>
                            </div>
                        </div>
                        <div class="admin-user-data-item">
                            <div class="admin-user-data-icon"><i class="pi pi-tag"></i></div>
                            <div>
                                <p class="admin-user-data-value">{{ money(pedido.subtotal) }}</p>
                                <p class="admin-user-data-label" style="margin-top:0.1rem">Subtotal</p>
                            </div>
                        </div>
                        <div class="admin-user-data-item">
                            <div class="admin-user-data-icon"><i class="pi pi-truck"></i></div>
                            <div>
                                <p class="admin-user-data-value">{{ money(pedido.envio) }}</p>
                                <p class="admin-user-data-label" style="margin-top:0.1rem">Envío</p>
                            </div>
                        </div>
                        <div class="admin-user-data-item">
                            <div class="admin-user-data-icon"><i class="pi pi-dollar"></i></div>
                            <div>
                                <p class="admin-user-data-value" style="color:var(--brand)">{{ money(pedido.total) }}</p>
                                <p class="admin-user-data-label" style="margin-top:0.1rem">Total</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones del Pedido -->
                <div class="admin-prod-actions-card" style="grid-area:acciones">
                    <div class="admin-prod-actions-card-header"><h3><i class="pi pi-bolt"></i> Acciones del Pedido</h3></div>
                    <div class="admin-prod-actions-card-body">
                        <button @click="accionProximamente('Reenviar Confirmación')" class="admin-prod-btn-back" style="justify-content:flex-start">
                            <i class="pi pi-envelope"></i><span>Reenviar Confirmación</span>
                        </button>
                        <button @click="accionProximamente('Generar Nota de Crédito')" class="admin-prod-btn-back" style="justify-content:flex-start">
                            <i class="pi pi-file"></i><span>Generar Nota de Crédito</span>
                        </button>
                        <button @click="accionProximamente('Reportar un Problema')" class="admin-prod-btn-delete">
                            <i class="pi pi-exclamation-triangle"></i><span>Reportar un Problema</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>