<template>

    <Head :title="`Pedido #${pedido.numero} | Club de Fantasías`" />

    <AppLayout active-nav="shop">
        <div class="pedido-detalle-page">
            <!-- ============================================================ -->
            <!-- HEADER -->
            <!-- ============================================================ -->
            <div class="page-header">
                <button class="btn-back" @click="volverAPedidos">
                    <i class="pi pi-arrow-left"></i>
                    <span>Volver a mis pedidos</span>
                </button>
                <div class="page-header__right">
                    <span class="pedido-status" :class="`status--${pedido.color_estado || 'gray'}`">
                        <span class="status__dot"></span>
                        {{ pedido.estado_texto }}
                    </span>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- INFO DEL PEDIDO -->
            <!-- ============================================================ -->
            <div class="pedido-info">
                <div class="pedido-info__header">
                    <div>
                        <h1 class="pedido-info__numero">
                            <i class="pi pi-hashtag"></i> {{ pedido.numero }}
                        </h1>
                        <span class="pedido-info__fecha">
                            <i class="pi pi-calendar"></i> {{ formatearFecha(pedido.created_at) }}
                        </span>
                    </div>
                    <div class="pedido-info__metodo">
                        <i class="pi pi-credit-card"></i>
                        <span>{{ pedido.metodo_pago_texto || 'No especificado' }}</span>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- CONTENIDO PRINCIPAL -->
            <!-- ============================================================ -->
            <div class="content-grid">
                <!-- COLUMNA PRINCIPAL -->
                <div class="main-column">
                    <!-- Productos -->
                    <div class="panel panel--productos">
                        <div class="panel__header">
                            <div class="panel__header-left">
                                <i class="pi pi-shopping-bag"></i>
                                <h2>Productos</h2>
                                <span class="panel__badge">{{ pedido.items.length }} items</span>
                            </div>
                            <span class="panel__total-label">Total: <strong>${{ formatoMoneda(pedido.total)
                                    }}</strong></span>
                        </div>
                        <div class="panel__body">
                            <div class="productos-list">
                                <div v-for="item in pedido.items" :key="item.id" class="producto-item">
                                    <div class="producto-item__left">
                                        <img :src="getImageUrl(item.imagen)" :alt="item.nombre" />
                                        <div class="producto-item__info">
                                            <strong>{{ item.nombre }}</strong>
                                            <span class="producto-item__variante" v-if="item.variante">
                                                {{ item.variante }}
                                            </span>
                                            <span class="producto-item__qty">{{ item.cantidad }} unidad{{ item.cantidad
                                                > 1 ? 'es' : '' }}</span>
                                        </div>
                                    </div>
                                    <div class="producto-item__right">
                                        <span class="producto-item__precio-unitario">${{ formatoMoneda(item.precio) }}
                                            c/u</span>
                                        <span class="producto-item__total">${{ formatoMoneda(item.total) }}</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Resumen de totales dentro de productos -->
                            <div class="productos-totales">
                                <div class="total-row">
                                    <span>Subtotal</span>
                                    <span>${{ formatoMoneda(pedido.subtotal) }}</span>
                                </div>
                                <div class="total-row">
                                    <span>Envío</span>
                                    <span>${{ formatoMoneda(pedido.envio) }}</span>
                                </div>
                                <div class="total-row total-row--grand">
                                    <span>Total</span>
                                    <strong>${{ formatoMoneda(pedido.total) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dirección de envío -->
                    <div class="panel panel--direccion">
                        <div class="panel__header">
                            <div class="panel__header-left">
                                <i class="pi pi-map-marker"></i>
                                <h2>Dirección de envío</h2>
                            </div>
                        </div>
                        <div class="panel__body">
                            <div class="direccion-card" v-if="pedido.direccion_envio">
                                <div class="direccion-card__row">
                                    <span class="direccion-card__label">Destinatario:</span>
                                    <strong>{{ pedido.direccion_envio.destinatario || 'No especificado' }}</strong>
                                </div>
                                <div class="direccion-card__row">
                                    <span class="direccion-card__label">Dirección:</span>
                                    <span>{{ pedido.direccion_envio.calle || 'Calle no especificada' }}, {{
                                        pedido.direccion_envio.colonia || 'Colonia no especificada' }}</span>
                                </div>
                                <div class="direccion-card__row">
                                    <span class="direccion-card__label">Ciudad:</span>
                                    <span>{{ pedido.direccion_envio.ciudad || 'Ciudad no especificada' }}, {{
                                        pedido.direccion_envio.estado || 'Estado no especificado' }}</span>
                                </div>
                                <div class="direccion-card__row">
                                    <span class="direccion-card__label">Código postal:</span>
                                    <span>{{ pedido.direccion_envio.codigo_postal || 'No especificado' }}</span>
                                </div>
                                <div class="direccion-card__row" v-if="pedido.direccion_envio.referencias">
                                    <span class="direccion-card__label">Referencias:</span>
                                    <span>{{ pedido.direccion_envio.referencias }}</span>
                                </div>
                                <div class="direccion-card__row">
                                    <span class="direccion-card__label">Teléfono:</span>
                                    <span><i class="pi pi-phone"></i> {{ pedido.direccion_envio.telefono || 'No especificado' }}</span>
                                </div>
                                <div class="direccion-card__row">
                                    <span class="direccion-card__label">País:</span>
                                    <span><i class="pi pi-flag"></i> {{ pedido.direccion_envio.pais || 'México'
                                        }}</span>
                                </div>
                            </div>
                            <div v-else class="direccion-vacia">
                                <i class="pi pi-info-circle"></i>
                                <span>No se encontró dirección de envío</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SIDEBAR -->
                <aside class="sidebar-column">
                    <!-- Resumen rápido -->
                    <div class="sidebar-card">
                        <h3><i class="pi pi-clock" style="color: var(--brand);"></i> Resumen rápido</h3>
                        <div class="resumen-item">
                            <span>Número de pedido</span>
                            <strong>#{{ pedido.numero }}</strong>
                        </div>
                        <div class="resumen-item">
                            <span>Fecha</span>
                            <strong>{{ formatearFechaCorta(pedido.created_at) }}</strong>
                        </div>
                        <div class="resumen-item">
                            <span>Estado</span>
                            <span class="pedido-status" :class="`status--${pedido.color_estado || 'gray'}`">
                                <span class="status__dot"></span>
                                {{ pedido.estado_texto }}
                            </span>
                        </div>
                        <div class="resumen-item">
                            <span>Método de pago</span>
                            <strong>{{ pedido.metodo_pago_texto || 'N/A' }}</strong>
                        </div>
                        <div class="resumen-item resumen-item--total">
                            <span>Total</span>
                            <strong>${{ formatoMoneda(pedido.total) }}</strong>
                        </div>
                    </div>

                    <!-- Items rápidos -->
                    <div class="sidebar-card">
                        <h3><i class="pi pi-shopping-bag" style="color: var(--brand);"></i> Productos</h3>
                        <div class="sidebar-productos">
                            <div v-for="item in pedido.items.slice(0, 4)" :key="item.id" class="sidebar-producto">
                                <img :src="getImageUrl(item.imagen)" :alt="item.nombre" />
                                <div>
                                    <strong>{{ item.nombre }}</strong>
                                    <span>{{ item.cantidad }} × ${{ formatoMoneda(item.precio) }}</span>
                                </div>
                                <span class="sidebar-producto__price">${{ formatoMoneda(item.total) }}</span>
                            </div>
                            <div v-if="pedido.items.length > 4" class="sidebar-producto-more">
                                + {{ pedido.items.length - 4 }} productos más
                            </div>
                        </div>
                    </div>

                    <!-- BOTONES DE ACCIÓN - DONDE ESTABA "¿Necesitas ayuda?" -->
                    <div class="sidebar-card sidebar-card--actions">
                        <h3><i class="pi pi-cog" style="color: var(--brand);"></i> Acciones</h3>
                        <div class="acciones-grid">
                            <button v-if="pedido.estado === 'pagado' || pedido.estado === 'carrito'"
                                class="btn btn--danger" @click="cancelarPedido(pedido.id)">
                                <i class="pi pi-times"></i> Cancelar pedido
                            </button>
                            <button class="btn btn--secondary" @click="volverAPedidos">
                                <i class="pi pi-arrow-left"></i> Volver a mis pedidos
                            </button>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <!-- ConfirmModal -->
        <ConfirmModal />
    </AppLayout>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { useConfirm } from '@/composables/useConfirm';

// ============================================================
// CONFIRM MODAL
// ============================================================
const { confirm } = useConfirm();

// ============================================================
// OBTENER USUARIO DESDE page.props
// ============================================================
const page = usePage();

// ============================================================
// PROPS DESDE EL CONTROLADOR
// ============================================================
const props = defineProps({
    usuario: {
        type: Object,
        default: () => ({
            id: null,
            nombre: 'Invitado',
            avatar: '/images/shared/avatar-default.jpg',
            verificado: false,
            rol: 'usuario'
        })
    },
    notificaciones: {
        type: Number,
        default: 0
    },
    favoritos: {
        type: Number,
        default: 0
    },
    mensajes: {
        type: Number,
        default: 0
    },
    pedido: {
        type: Object,
        required: true,
        default: () => ({
            id: null,
            numero: '',
            subtotal: 0,
            envio: 0,
            total: 0,
            estado: '',
            estado_texto: '',
            color_estado: 'gray',
            metodo_pago: '',
            metodo_pago_texto: '',
            direccion_envio: null,
            items: [],
            created_at: null,
            updated_at: null
        })
    }
});

// ============================================================
// FUNCIÓN PARA OBTENER AVATAR CORRECTAMENTE
// ============================================================
const getAvatarUrl = (avatar) => {
    if (!avatar) return '/images/shared/avatar-default.jpg';
    if (avatar.startsWith('http://') || avatar.startsWith('https://')) return avatar;
    if (avatar.startsWith('/storage/')) return avatar;
    if (!avatar.startsWith('/')) return '/storage/' + avatar;
    return avatar;
};

// ============================================================
// USUARIO CON AVATAR CORREGIDO
// ============================================================
const usuario = computed(() => {
    const user = props.usuario || page.props.usuario || {};
    let avatar = user.avatar || '/images/shared/avatar-default.jpg';
    avatar = getAvatarUrl(avatar);
    return {
        id: user.id || null,
        nombre: user.nombre || 'Invitado',
        avatar: avatar,
        verificado: user.verificado || false,
        rol: user.rol || 'invitado',
        email: user.email || '',
    };
});

// ============================================================
// FUNCIONES DE UTILIDAD
// ============================================================
function formatoMoneda(valor) {
    return new Intl.NumberFormat('es-MX').format(Math.round(valor));
}

function getImageUrl(imagen) {
    if (!imagen) return '/images/shared/placeholder.jpg';
    if (imagen.startsWith('http://') || imagen.startsWith('https://')) return imagen;
    if (imagen.startsWith('/storage/') || imagen.startsWith('/images/')) return imagen;
    return '/storage/' + imagen.replace(/^\/+/, '');
}

function formatearFecha(fecha) {
    if (!fecha) return '';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatearFechaCorta(fecha) {
    if (!fecha) return '';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

// ============================================================
// FUNCIONES DE NAVEGACIÓN
// ============================================================
function volverAPedidos() {
    router.visit('/tienda/mis-pedidos');
}

// ============================================================
// CANCELAR PEDIDO - CON CONFIRM MODAL
// ============================================================
async function cancelarPedido(id) {
    const confirmed = await confirm(
        'Estás a punto de cancelar este pedido. Esta acción no se puede deshacer y el stock de los productos se liberará automáticamente.',
        {
            title: 'Cancelar pedido',
            confirmLabel: 'Sí, cancelar',
            cancelLabel: 'No, mantener',
            danger: true,
        }
    );

    if (confirmed) {
        router.delete(route('tienda.pedido.cancelar', id), {
            preserveScroll: true,
            onSuccess: () => {
                router.reload();
            },
            onError: (errors) => {
                console.error('Error al cancelar pedido:', errors);
            }
        });
    }
}

// ============================================================
// ON MOUNTED
// ============================================================
onMounted(() => {
    console.log('📋 Detalle del pedido montado');
    console.log('📦 Pedido:', props.pedido);
});
</script>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA
   ========================================================================= */
.pedido-detalle-page {
    --brand: #C81E3A;
    --brand-dark: #A6152D;
    --brand-soft: #FBEAEC;
    --brand-red: #C81E3A;
    --ink: #171412;
    --ink-soft: #4B4744;
    --muted: #8A8481;
    --muted-light: #B7B2AF;
    --line: #ECE9E7;
    --surface: #FAF8F7;
    --white: #FFFFFF;
    --shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    --shadow-hover: 0 8px 40px rgba(0, 0, 0, 0.12);

    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: var(--ink);
    background: var(--surface);
    -webkit-font-smoothing: antialiased;
    max-width: 1400px;
    margin: 0 auto;
    padding: 1.25rem 2rem 0;
}

.pedido-detalle-page * {
    box-sizing: border-box;
}

/* =========================================================================
   PAGE HEADER
   ========================================================================= */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.btn-back {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    background: #fff;
    border: 1px solid var(--line);
    padding: 0.5rem 1.2rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--ink-soft);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 10px;
    box-shadow: var(--shadow);
}

.btn-back:hover {
    border-color: var(--brand);
    color: var(--brand);
    transform: translateX(-3px);
    box-shadow: var(--shadow-hover);
}

.page-header__right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* =========================================================================
   PEDIDO INFO
   ========================================================================= */
.pedido-info {
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--line);
    padding: 1.25rem 1.5rem;
    box-shadow: var(--shadow);
    margin-bottom: 1.5rem;
}

.pedido-info__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.pedido-info__numero {
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pedido-info__numero i {
    color: var(--muted-light);
    font-size: 1rem;
}

.pedido-info__fecha {
    font-size: 0.85rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.pedido-info__fecha i {
    font-size: 0.8rem;
}

.pedido-info__metodo {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.85rem;
    color: var(--ink-soft);
    background: var(--surface);
    padding: 0.3rem 0.8rem;
    border-radius: 8px;
}

.pedido-info__metodo i {
    color: var(--brand);
}

/* =========================================================================
   ESTADOS
   ========================================================================= */
.pedido-status {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.3rem 0.9rem;
    border-radius: 999px;
}

.status__dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
}

.status--blue {
    background: #EFF6FF;
    color: #2563EB;
}

.status--blue .status__dot {
    background: #2563EB;
}

.status--orange {
    background: #FFFBEB;
    color: #F59E0B;
}

.status--orange .status__dot {
    background: #F59E0B;
}

.status--green {
    background: #ECFDF5;
    color: #10B981;
}

.status--green .status__dot {
    background: #10B981;
}

.status--red {
    background: #FEF2F2;
    color: #EF4444;
}

.status--red .status__dot {
    background: #EF4444;
}

.status--gray {
    background: #F3F4F6;
    color: #6B7280;
}

.status--gray .status__dot {
    background: #6B7280;
}

/* =========================================================================
   CONTENT GRID
   ========================================================================= */
.content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1100px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

/* =========================================================================
   MAIN COLUMN
   ========================================================================= */
.main-column {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* =========================================================================
   PANEL
   ========================================================================= */
.panel {
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--line);
    overflow: hidden;
    box-shadow: var(--shadow);
}

.panel__header {
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--line);
    background: var(--surface);
}

.panel__header-left {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.panel__header-left i {
    color: var(--brand);
    font-size: 1.1rem;
}

.panel__header-left h2 {
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0;
}

.panel__badge {
    font-size: 0.6rem;
    font-weight: 700;
    background: var(--brand-soft);
    color: var(--brand);
    padding: 0.1rem 0.6rem;
    border-radius: 999px;
}

.panel__total-label {
    font-size: 0.8rem;
    color: var(--muted);
}

.panel__total-label strong {
    color: var(--brand);
    font-size: 1rem;
}

.panel__body {
    padding: 1.25rem 1.5rem;
}

/* =========================================================================
   PRODUCTOS LIST
   ========================================================================= */
.productos-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.producto-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--line);
}

.producto-item:last-child {
    border-bottom: none;
}

.producto-item__left {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.producto-item__left img {
    width: 52px;
    height: 52px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
}

.producto-item__info strong {
    display: block;
    font-size: 0.85rem;
}

.producto-item__variante {
    font-size: 0.7rem;
    color: var(--muted);
}

.producto-item__qty {
    font-size: 0.7rem;
    color: var(--muted-light);
}

.producto-item__right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.1rem;
}

.producto-item__precio-unitario {
    font-size: 0.65rem;
    color: var(--muted-light);
}

.producto-item__total {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--ink);
}

/* =========================================================================
   PRODUCTOS TOTALES - DENTRO DE PRODUCTOS
   ========================================================================= */
.productos-totales {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px solid var(--line);
}

.total-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: var(--ink-soft);
    padding: 0.2rem 0;
}

.total-row--grand {
    font-weight: 700;
    font-size: 1rem;
    padding-top: 0.3rem;
    margin-top: 0.2rem;
    border-top: 1px solid var(--line);
}

.total-row--grand strong {
    color: var(--brand);
    font-size: 1.1rem;
}

/* =========================================================================
   DIRECCIÓN
   ========================================================================= */
.direccion-card {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.direccion-card__row {
    display: flex;
    gap: 0.5rem;
    font-size: 0.82rem;
}

.direccion-card__label {
    color: var(--muted);
    min-width: 100px;
    font-weight: 500;
}

.direccion-card__row strong {
    color: var(--ink);
}

.direccion-card__row i {
    color: var(--brand);
    font-size: 0.7rem;
}

.direccion-vacia {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--muted);
    font-size: 0.85rem;
}

.direccion-vacia i {
    color: var(--brand);
}

/* =========================================================================
   SIDEBAR
   ========================================================================= */
.sidebar-column {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.sidebar-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: var(--shadow);
}

.sidebar-card h3 {
    font-size: 0.85rem;
    font-weight: 700;
    margin: 0 0 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--ink);
}

.sidebar-card h3 i {
    font-size: 1rem;
}

/* Resumen */
.resumen-item {
    display: flex;
    justify-content: space-between;
    padding: 0.35rem 0;
    font-size: 0.82rem;
    color: var(--ink-soft);
    border-bottom: 1px solid var(--line);
    align-items: center;
}

.resumen-item:last-child {
    border-bottom: none;
}

.resumen-item strong {
    color: var(--ink);
    font-weight: 700;
}

.resumen-item--total {
    font-weight: 700;
    padding-top: 0.5rem;
    margin-top: 0.25rem;
    border-top: 2px solid var(--brand);
    border-bottom: none;
}

.resumen-item--total strong {
    color: var(--brand);
    font-size: 1.05rem;
}

.resumen-item .pedido-status {
    font-size: 0.7rem;
    padding: 0.15rem 0.6rem;
}

/* Sidebar productos */
.sidebar-productos {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.sidebar-producto {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.4rem 0;
    border-bottom: 1px solid var(--line);
}

.sidebar-producto:last-child {
    border-bottom: none;
}

.sidebar-producto img {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    object-fit: cover;
    flex-shrink: 0;
}

.sidebar-producto div {
    flex: 1;
    min-width: 0;
}

.sidebar-producto strong {
    display: block;
    font-size: 0.75rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.sidebar-producto span {
    font-size: 0.65rem;
    color: var(--muted);
}

.sidebar-producto__price {
    font-size: 0.75rem;
    font-weight: 700;
    white-space: nowrap;
}

.sidebar-producto-more {
    font-size: 0.7rem;
    color: var(--muted);
    text-align: center;
    padding: 0.3rem 0;
    font-weight: 600;
}

/* =========================================================================
   ACCIONES - SIDEBAR CARD
   ========================================================================= */
.sidebar-card--actions {
    border-color: var(--brand);
    background: linear-gradient(145deg, #fff, var(--brand-soft));
}

.sidebar-card--actions h3 {
    color: var(--brand);
}

.sidebar-card--actions h3 i {
    color: var(--brand);
}

.acciones-grid {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.7rem 1.2rem;
    border: none;
    border-radius: 10px;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    width: 100%;
}

.btn--danger {
    background: #FEF2F2;
    color: #EF4444;
    border: 1.5px solid #EF4444;
}

.btn--danger:hover {
    background: #EF4444;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
}

.btn--secondary {
    background: var(--surface);
    color: var(--ink-soft);
    border: 1.5px solid var(--line);
}

.btn--secondary:hover {
    background: var(--line);
    transform: translateY(-2px);
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 768px) {
    .pedido-detalle-page {
        padding: 0.75rem 1rem 0;
    }

    .page-header {
        flex-direction: column;
        align-items: stretch;
    }

    .btn-back {
        justify-content: center;
    }

    .page-header__right {
        justify-content: center;
    }

    .pedido-info__header {
        flex-direction: column;
        align-items: flex-start;
    }

    .pedido-info__metodo {
        width: 100%;
        justify-content: center;
    }

    .producto-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.3rem;
    }

    .producto-item__right {
        align-items: flex-start;
        flex-direction: row;
        gap: 1rem;
    }

    .direccion-card__row {
        flex-direction: column;
        gap: 0.1rem;
    }

    .direccion-card__label {
        min-width: auto;
    }

    .sidebar-card {
        padding: 1rem;
    }
}

@media (max-width: 480px) {
    .pedido-detalle-page {
        padding: 0.5rem 0.5rem 0;
    }

    .panel__header {
        padding: 0.75rem 1rem;
    }

    .panel__body {
        padding: 0.75rem 1rem;
    }

    .producto-item__left img {
        width: 44px;
        height: 44px;
    }

    .producto-item__total {
        font-size: 0.82rem;
    }

    .pedido-info__numero {
        font-size: 1.1rem;
    }

    .sidebar-producto img {
        width: 32px;
        height: 32px;
    }
}
</style>