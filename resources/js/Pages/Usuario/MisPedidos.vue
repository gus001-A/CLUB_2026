<template>

    <Head title="Mis pedidos | Club de Fantasías" />

    <AppLayout active-nav="shop">
        <div class="mis-pedidos-page">
            <!-- ============================================================ -->
            <!-- HEADER -->
            <!-- ============================================================ -->
            <div class="page-header">
                <button class="btn-back" @click="volverATienda">
                    <i class="pi pi-arrow-left"></i>
                    <span>Volver a la tienda</span>
                </button>
                <div class="page-header__center">
                    <h1 class="page-title">
                        <i class="pi pi-receipt"></i> Mis pedidos
                    </h1>
                    <span class="page-badge">{{ pedidosTotales }} pedidos</span>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- STATS RESUMEN -->
            <!-- ============================================================ -->
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-item__icon" style="background: #EFF6FF; color: #2563EB;">
                        <i class="pi pi-shopping-bag"></i>
                    </div>
                    <div class="stat-item__info">
                        <span class="stat-item__label">Total pedidos</span>
                        <strong class="stat-item__value">{{ pedidosTotales }}</strong>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-item__icon" style="background: #FEF3C7; color: #D97706;">
                        <i class="pi pi-clock"></i>
                    </div>
                    <div class="stat-item__info">
                        <span class="stat-item__label">Pedidos activos</span>
                        <strong class="stat-item__value">{{ contarPorEstado('pagado') + contarPorEstado('enviado')
                            }}</strong>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-item__icon" style="background: #ECFDF5; color: #10B981;">
                        <i class="pi pi-check-circle"></i>
                    </div>
                    <div class="stat-item__info">
                        <span class="stat-item__label">Entregados</span>
                        <strong class="stat-item__value">{{ contarPorEstado('entregado') }}</strong>
                    </div>
                </div>
                <div class="stat-item stat-item--highlight">
                    <div class="stat-item__icon" style="background: #FEF2F2; color: #EF4444;">
                        <i class="pi pi-wallet"></i>
                    </div>
                    <div class="stat-item__info">
                        <span class="stat-item__label">Total gastado</span>
                        <strong class="stat-item__value">${{ formatoMoneda(totalGastado) }}</strong>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- FILTROS DE ESTADO -->
            <!-- ============================================================ -->
            <div class="filters-bar">
                <button class="filter-chip" :class="{ active: filtroEstado === null }" @click="filtroEstado = null">
                    <i class="pi pi-th-large"></i>
                    Todos
                    <span class="filter-chip__count">{{ pedidosTotales }}</span>
                </button>
                <button v-for="estado in estadosDisponibles" :key="estado.value" class="filter-chip"
                    :class="{ active: filtroEstado === estado.value }" @click="filtroEstado = estado.value">
                    <span class="filter-chip__dot" :style="{ background: estado.color }"></span>
                    {{ estado.label }}
                    <span class="filter-chip__count">{{ contarPorEstado(estado.value) }}</span>
                </button>
            </div>

            <!-- ============================================================ -->
            <!-- CONTENIDO PRINCIPAL -->
            <!-- ============================================================ -->
            <div class="content-grid">
                <!-- LISTA DE PEDIDOS EN CARDS GALERÍA -->
                <div class="pedidos-column">
                    <div class="pedidos-header">
                        <div class="pedidos-header__left">
                            <span class="pedidos-count">
                                <strong>{{ pedidosFiltrados.length }}</strong> pedidos encontrados
                            </span>
                            <span class="pedidos-total">de {{ pedidosTotales }} totales</span>
                        </div>
                        <label class="sort-select">
                            <select v-model="ordenarPor">
                                <option v-for="op in opcionesOrden" :key="op.value" :value="op.value">
                                    {{ op.label }}
                                </option>
                            </select>
                            <i class="pi pi-chevron-down"></i>
                        </label>
                    </div>

                    <!-- Empty State -->
                    <div v-if="pedidosFiltrados.length === 0" class="empty-state">
                        <div class="empty-state__icon">
                            <i class="pi pi-inbox"></i>
                        </div>
                        <h3>No tienes pedidos</h3>
                        <p>
                            {{ filtroEstado ? 'No tienes pedidos con este estado.' : 'Aún no has realizado ninguna compra.' }}
                        </p>
                        <button class="btn-shop" @click="volverATienda">
                            <i class="pi pi-shopping-cart"></i> Ir a la tienda
                        </button>
                    </div>

                    <!-- Cards galería mejoradas -->
                    <div v-else class="pedidos-grid">
                        <div v-for="pedido in pedidosFiltrados" :key="pedido.id" class="pedido-card">
                            <!-- Imagen del producto principal -->
                            <div class="pedido-card__image" @click="verDetalle(pedido.id)">
                                <img :src="getImageUrl(pedido.items[0]?.imagen || '/images/shared/placeholder.jpg')"
                                    :alt="pedido.items[0]?.nombre || 'Producto'" />
                                <!-- Badge de estado sobre la imagen -->
                                <span class="pedido-card__estado" :class="`estado--${pedido.color_estado || 'gray'}`">
                                    <span class="estado__dot"></span>
                                    {{ pedido.estado_texto }}
                                </span>
                                <!-- Badge de cantidad de productos -->
                                <span v-if="pedido.items.length > 1" class="pedido-card__count">
                                    +{{ pedido.items.length - 1 }}
                                </span>
                            </div>

                            <!-- Información del pedido - MEJORADA -->
                            <div class="pedido-card__info">
                                <div class="pedido-card__numero">#{{ pedido.numero }}</div>
                                <div class="pedido-card__fecha">
                                    <i class="pi pi-calendar"></i> {{ formatearFechaCompleta(pedido.created_at) }}
                                </div>
                                <div class="pedido-card__total">
                                    <span class="pedido-card__total-label">Total</span>
                                    <strong>${{ formatoMoneda(pedido.total) }}</strong>
                                </div>
                            </div>

                            <!-- Botón de cancelar -->
                            <div class="pedido-card__actions">
                                <button v-if="pedido.estado === 'pagado' || pedido.estado === 'carrito'"
                                    class="btn-cancel" @click="cancelarPedido(pedido.id)">
                                    <i class="pi pi-times"></i> Cancelar pedido
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div v-if="paginacion.total > paginacion.per_page" class="pagination">
                        <button class="pagination__btn" :disabled="paginacion.current_page <= 1"
                            @click="cambiarPagina(paginacion.current_page - 1)">
                            <i class="pi pi-chevron-left"></i> Anterior
                        </button>
                        <div class="pagination__numbers">
                            <button v-for="n in paginacion.last_page" :key="n" class="pagination__num"
                                :class="{ active: n === paginacion.current_page }" @click="cambiarPagina(n)">
                                {{ n }}
                            </button>
                        </div>
                        <button class="pagination__btn" :disabled="paginacion.current_page >= paginacion.last_page"
                            @click="cambiarPagina(paginacion.current_page + 1)">
                            Siguiente <i class="pi pi-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <!-- SIDEBAR -->
                <aside class="sidebar-column">
                    <!-- Resumen de pedidos -->
                    <div class="sidebar-card">
                        <h3><i class="pi pi-chart-bar"></i> Resumen</h3>
                        <div class="resumen-list">
                            <div class="resumen-item">
                                <span><i class="pi pi-shopping-bag"></i> Total</span>
                                <strong>{{ pedidosTotales }}</strong>
                            </div>
                            <div class="resumen-item">
                                <span><i class="pi pi-clock"></i> Activos</span>
                                <strong>{{ contarPorEstado('pagado') + contarPorEstado('enviado') }}</strong>
                            </div>
                            <div class="resumen-item">
                                <span><i class="pi pi-check-circle"></i> Entregados</span>
                                <strong>{{ contarPorEstado('entregado') }}</strong>
                            </div>
                            <div class="resumen-item resumen-item--total">
                                <span><i class="pi pi-wallet"></i> Gastado</span>
                                <strong>${{ formatoMoneda(totalGastado) }}</strong>
                            </div>
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
    pedidos: {
        type: Object,
        default: () => ({
            data: [],
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0
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
// ESTADOS DE PEDIDO
// ============================================================
const estadosDisponibles = [
    { value: 'pagado', label: 'Pagado', color: '#2563EB' },
    { value: 'enviado', label: 'Enviado', color: '#F59E0B' },
    { value: 'entregado', label: 'Entregado', color: '#10B981' },
    { value: 'cancelado', label: 'Cancelado', color: '#EF4444' },
];

// ============================================================
// FILTROS Y ORDENAMIENTO
// ============================================================
const filtroEstado = ref(null);
const ordenarPor = ref('reciente');
const paginacion = ref({
    current_page: props.pedidos.current_page || 1,
    last_page: props.pedidos.last_page || 1,
    per_page: props.pedidos.per_page || 10,
    total: props.pedidos.total || 0
});

const opcionesOrden = [
    { label: 'Más recientes', value: 'reciente' },
    { label: 'Más antiguos', value: 'antiguo' },
    { label: 'Mayor monto', value: 'mayor_monto' },
    { label: 'Menor monto', value: 'menor_monto' },
];

// ============================================================
// PEDIDOS
// ============================================================
const pedidos = ref(props.pedidos.data || []);

// ============================================================
// PEDIDOS FILTRADOS
// ============================================================
const pedidosFiltrados = computed(() => {
    let resultado = [...pedidos.value];

    if (filtroEstado.value) {
        resultado = resultado.filter(p => p.estado === filtroEstado.value);
    }

    switch (ordenarPor.value) {
        case 'reciente':
            resultado.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            break;
        case 'antiguo':
            resultado.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            break;
        case 'mayor_monto':
            resultado.sort((a, b) => b.total - a.total);
            break;
        case 'menor_monto':
            resultado.sort((a, b) => a.total - b.total);
            break;
        default:
            break;
    }

    return resultado;
});

// ============================================================
// TOTAL DE PEDIDOS
// ============================================================
const pedidosTotales = computed(() => paginacion.value.total || pedidos.value.length);

// ============================================================
// TOTAL GASTADO
// ============================================================
const totalGastado = computed(() => {
    return pedidos.value.reduce((acc, p) => acc + parseFloat(p.total), 0);
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

function formatearFechaCompleta(fecha) {
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

function contarPorEstado(estado) {
    return pedidos.value.filter(p => p.estado === estado).length;
}

// ============================================================
// FUNCIONES DE NAVEGACIÓN
// ============================================================
function volverATienda() {
    router.visit('/tienda');
}

function verDetalle(id) {
    console.log('🔍 Ver detalle del pedido:', id);
    router.visit(`/tienda/pedido/${id}`, {
        method: 'get',
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            console.log('✅ Redirigido a detalle del pedido');
        },
        onError: (errors) => {
            console.error('❌ Error al redirigir:', errors);
        }
    });
}

function cambiarPagina(pagina) {
    if (pagina < 1 || pagina > paginacion.value.last_page) return;

    router.visit(`/tienda/mis-pedidos?page=${pagina}`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
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
                const index = pedidos.value.findIndex(p => p.id === id);
                if (index !== -1) {
                    pedidos.value[index].estado = 'cancelado';
                    pedidos.value[index].estado_texto = 'Cancelado';
                    pedidos.value[index].color_estado = 'red';
                }
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
    console.log('📋 Mis pedidos montado');
    console.log('📦 Pedidos:', pedidos.value);
    console.log('📊 Paginación:', paginacion.value);
});
</script>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA
   ========================================================================= */
.mis-pedidos-page {
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

.mis-pedidos-page * {
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

.page-header__center {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.page-title i {
    color: var(--brand);
}

.page-badge {
    font-size: 0.7rem;
    font-weight: 600;
    color: #fff;
    background: var(--brand);
    padding: 0.2rem 0.8rem;
    border-radius: 999px;
}

/* =========================================================================
   STATS ROW
   ========================================================================= */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 700px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    background: #fff;
    padding: 0.8rem 1.2rem;
    border-radius: 12px;
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.stat-item__icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.stat-item__info {
    flex: 1;
}

.stat-item__label {
    display: block;
    font-size: 0.7rem;
    color: var(--muted);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.stat-item__value {
    font-size: 1.2rem;
    font-weight: 800;
    display: block;
    line-height: 1.2;
}

.stat-item--highlight {
    border-color: var(--brand);
    background: linear-gradient(145deg, #fff, var(--brand-soft));
}

/* =========================================================================
   FILTERS BAR
   ========================================================================= */
.filters-bar {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
    background: #fff;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
}

.filter-chip {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.8rem;
    border: 1.5px solid var(--line);
    border-radius: 999px;
    background: #fff;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink-soft);
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-chip:hover {
    border-color: var(--brand);
    color: var(--brand);
}

.filter-chip.active {
    background: var(--brand);
    border-color: var(--brand);
    color: #fff;
}

.filter-chip__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.filter-chip__count {
    font-size: 0.65rem;
    font-weight: 400;
    opacity: 0.7;
    background: rgba(0, 0, 0, 0.05);
    padding: 0.05rem 0.4rem;
    border-radius: 999px;
}

.filter-chip.active .filter-chip__count {
    background: rgba(255, 255, 255, 0.2);
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
   PEDIDOS COLUMN
   ========================================================================= */
.pedidos-column {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.pedidos-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 0.25rem;
}

.pedidos-header__left {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pedidos-count {
    font-size: 0.85rem;
    color: var(--muted);
}

.pedidos-count strong {
    color: var(--ink);
    font-weight: 700;
}

.pedidos-total {
    font-size: 0.75rem;
    color: var(--muted-light);
}

.sort-select {
    position: relative;
    display: inline-block;
}

.sort-select select {
    border: 1.5px solid var(--line);
    border-radius: 10px;
    padding: 0.5rem 2rem 0.5rem 0.8rem;
    font-size: 0.82rem;
    color: var(--ink);
    background: #fff;
    cursor: pointer;
    outline: none;
    appearance: none;
}

.sort-select select:focus {
    border-color: var(--brand);
}

.sort-select i {
    position: absolute;
    right: 0.6rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted-light);
    font-size: 0.6rem;
    pointer-events: none;
}

/* =========================================================================
   EMPTY STATE
   ========================================================================= */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
}

.empty-state__icon {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
    margin: 0 auto 1.25rem;
}

.empty-state h3 {
    font-size: 1.2rem;
    margin: 0 0 0.5rem;
    color: var(--ink);
}

.empty-state p {
    margin: 0 0 1.5rem;
    color: var(--muted);
    font-size: 0.85rem;
}

.btn-shop {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.5rem;
    border: none;
    border-radius: 10px;
    background: var(--brand);
    color: #fff;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-shop:hover {
    background: var(--brand-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(200, 30, 58, 0.3);
}

/* =========================================================================
   PEDIDOS GRID - CARDS GALERÍA MEJORADAS
   ========================================================================= */
.pedidos-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
}

@media (max-width: 1200px) {
    .pedidos-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 900px) {
    .pedidos-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .pedidos-grid {
        grid-template-columns: 1fr;
    }
}

.pedido-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}

.pedido-card:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-4px);
}

/* IMAGEN */
.pedido-card__image {
    position: relative;
    aspect-ratio: 1/1;
    background: var(--surface);
    cursor: pointer;
    overflow: hidden;
}

.pedido-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.pedido-card:hover .pedido-card__image img {
    transform: scale(1.05);
}

/* Badge de estado sobre la imagen */
.pedido-card__estado {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.2rem 0.7rem;
    border-radius: 999px;
    z-index: 2;
    letter-spacing: 0.02em;
}

.estado__dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
}

.estado--blue {
    background: rgba(37, 99, 235, 0.92);
    color: #fff;
}

.estado--blue .estado__dot {
    background: #fff;
}

.estado--orange {
    background: rgba(245, 158, 11, 0.92);
    color: #fff;
}

.estado--orange .estado__dot {
    background: #fff;
}

.estado--green {
    background: rgba(16, 185, 129, 0.92);
    color: #fff;
}

.estado--green .estado__dot {
    background: #fff;
}

.estado--red {
    background: rgba(239, 68, 68, 0.92);
    color: #fff;
}

.estado--red .estado__dot {
    background: #fff;
}

.estado--gray {
    background: rgba(107, 114, 128, 0.92);
    color: #fff;
}

.estado--gray .estado__dot {
    background: #fff;
}

/* Badge de cantidad */
.pedido-card__count {
    position: absolute;
    bottom: 0.75rem;
    right: 0.75rem;
    background: rgba(0, 0, 0, 0.75);
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.15rem 0.6rem;
    border-radius: 999px;
    backdrop-filter: blur(8px);
}

/* INFORMACIÓN DEL PEDIDO - MEJORADA Y MÁS GRANDE */
.pedido-card__info {
    padding: 0.9rem 1rem 0.6rem;
}

.pedido-card__numero {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--ink);
    margin-bottom: 0.15rem;
}

.pedido-card__fecha {
    font-size: 0.7rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 0.3rem;
    margin-bottom: 0.4rem;
}

.pedido-card__fecha i {
    font-size: 0.6rem;
}

.pedido-card__total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 0.4rem;
    border-top: 1px solid var(--line);
}

.pedido-card__total-label {
    font-size: 0.65rem;
    color: var(--muted-light);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.pedido-card__total strong {
    font-size: 1.05rem;
    color: var(--brand);
    font-weight: 800;
}

/* Botón cancelar */
.pedido-card__actions {
    padding: 0 1rem 0.9rem;
}

.btn-cancel {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    width: 100%;
    padding: 0.45rem;
    border: 1.5px solid #EF4444;
    border-radius: 8px;
    background: transparent;
    color: #EF4444;
    font-size: 0.7rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-cancel:hover {
    background: #EF4444;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
}

.btn-cancel i {
    font-size: 0.7rem;
}

/* =========================================================================
   PAGINACIÓN
   ========================================================================= */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0;
}

.pagination__btn {
    padding: 0.4rem 1rem;
    border-radius: 8px;
    border: 1.5px solid var(--line);
    background: #fff;
    color: var(--ink-soft);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.pagination__btn:hover:not(:disabled) {
    border-color: var(--brand);
    color: var(--brand);
}

.pagination__btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.pagination__numbers {
    display: flex;
    gap: 0.25rem;
}

.pagination__num {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    border: 1.5px solid transparent;
    background: transparent;
    color: var(--ink-soft);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.pagination__num:hover {
    border-color: var(--line);
}

.pagination__num.active {
    background: var(--brand);
    color: #fff;
    border-color: var(--brand);
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
    color: var(--brand);
}

.resumen-list {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.resumen-item {
    display: flex;
    justify-content: space-between;
    padding: 0.4rem 0;
    font-size: 0.82rem;
    color: var(--ink-soft);
    border-bottom: 1px solid var(--line);
}

.resumen-item:last-child {
    border-bottom: none;
}

.resumen-item strong {
    color: var(--ink);
    font-weight: 700;
}

.resumen-item i {
    font-size: 0.7rem;
    color: var(--muted);
    margin-right: 0.4rem;
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
    font-size: 1rem;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 768px) {
    .mis-pedidos-page {
        padding: 0.75rem 1rem 0;
    }

    .page-header {
        flex-direction: column;
        align-items: stretch;
    }

    .page-header__center {
        justify-content: center;
    }

    .page-title {
        font-size: 1.2rem;
    }

    .filters-bar {
        padding: 0.5rem;
        gap: 0.3rem;
    }

    .filter-chip {
        font-size: 0.7rem;
        padding: 0.3rem 0.6rem;
    }

    .pedidos-header {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
    }

    .stats-row {
        grid-template-columns: 1fr 1fr;
    }

    .pagination {
        flex-wrap: wrap;
    }

    .pagination__btn {
        padding: 0.3rem 0.6rem;
        font-size: 0.7rem;
    }

    .pedido-card__numero {
        font-size: 0.85rem;
    }

    .pedido-card__total strong {
        font-size: 0.95rem;
    }
}

@media (max-width: 480px) {
    .mis-pedidos-page {
        padding: 0.5rem 0.5rem 0;
    }

    .btn-back {
        justify-content: center;
    }

    .stats-row {
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }

    .stat-item {
        padding: 0.6rem 0.8rem;
    }

    .stat-item__icon {
        width: 36px;
        height: 36px;
        font-size: 1rem;
    }

    .stat-item__value {
        font-size: 1rem;
    }

    .sidebar-card {
        padding: 1rem;
    }

    .pedido-card__info {
        padding: 0.6rem 0.7rem 0.4rem;
    }

    .pedido-card__numero {
        font-size: 0.8rem;
    }

    .pedido-card__total strong {
        font-size: 0.9rem;
    }

    .btn-cancel {
        font-size: 0.65rem;
        padding: 0.35rem;
    }
}
</style>