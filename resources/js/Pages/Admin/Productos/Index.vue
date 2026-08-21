<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
    stats: Object,
    productos: Object,
    filtros: Object,
    categorias: Array,
    porCategoria: Array,
});

const { confirm } = useConfirm();
const toast = useToast();
const { money } = useFormatters();

const q = ref(props.filtros.q || '');
const categoria = ref(props.filtros.categoria || '');
const estado = ref(props.filtros.estado || '');

let timeout = null;
watch([q, categoria, estado], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.productos.index'), {
            q: q.value || undefined,
            categoria: categoria.value || undefined,
            estado: estado.value || undefined,
        }, { preserveState: true, replace: true });
    }, 350);
});

async function eliminarProducto(p) {
    const ok = await confirm(`¿Estás seguro de que deseas eliminar "${p.nombre}"?`, {
        title: 'Eliminar producto',
        confirmLabel: 'Sí, eliminar',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('admin.productos.destroy', p.id), {
        preserveScroll: true,
        onSuccess: () => toast.success(`Producto "${p.nombre}" eliminado correctamente.`),
        onError: () => toast.error('No se pudo eliminar el producto.'),
    });
}

const porcentajeActivos = computed(() => {
    if (props.stats.total === 0) return 0;
    return Math.round((props.stats.activos / props.stats.total) * 100);
});

// Colores para KPIs
const kpis = computed(() => [
    {
        label: 'Productos Totales',
        value: props.stats.total,
        icon: 'pi pi-tags',
        color: '#2563EB',
        bg: '#EFF6FF',
        iconBg: '#DBEAFE',
        gradient: 'linear-gradient(135deg, #2563EB, #1D4ED8)'
    },
    {
        label: 'Activos',
        value: props.stats.activos,
        icon: 'pi pi-check-circle',
        color: '#059669',
        bg: '#ECFDF5',
        iconBg: '#D1FAE5',
        gradient: 'linear-gradient(135deg, #059669, #047857)',
        hint: `${porcentajeActivos.value}% del total`
    },
    {
        label: 'Sin Stock',
        value: props.stats.sinStock,
        icon: 'pi pi-exclamation-triangle',
        color: '#DC2626',
        bg: '#FEF2F2',
        iconBg: '#FEE2E2',
        gradient: 'linear-gradient(135deg, #DC2626, #B91C1C)'
    },
    {
        label: 'Valor Inventario',
        value: money(props.stats.valorInventario),
        icon: 'pi pi-wallet',
        color: '#7C3AED',
        bg: '#F5F3FF',
        iconBg: '#EDE9FE',
        gradient: 'linear-gradient(135deg, #7C3AED, #6D28D9)'
    },
]);

// Colores únicos para categorías - paleta más variada
const coloresCategorias = [
    { bg: '#FBEAEC', border: '#C81E3A', dot: '#C81E3A', text: '#C81E3A' },
    { bg: '#EFF6FF', border: '#2563EB', dot: '#2563EB', text: '#2563EB' },
    { bg: '#ECFDF5', border: '#059669', dot: '#059669', text: '#059669' },
    { bg: '#FEF3C7', border: '#D97706', dot: '#D97706', text: '#D97706' },
    { bg: '#F5F3FF', border: '#7C3AED', dot: '#7C3AED', text: '#7C3AED' },
    { bg: '#CFFAFE', border: '#0891B2', dot: '#0891B2', text: '#0891B2' },
    { bg: '#FEE2E2', border: '#DC2626', dot: '#DC2626', text: '#DC2626' },
    { bg: '#F3E8FF', border: '#9333EA', dot: '#9333EA', text: '#9333EA' },
    { bg: '#CCFBF1', border: '#0D9488', dot: '#0D9488', text: '#0D9488' },
    { bg: '#FFEDD5', border: '#EA580C', dot: '#EA580C', text: '#EA580C' },
    { bg: '#E0E7FF', border: '#4F46E5', dot: '#4F46E5', text: '#4F46E5' },
    { bg: '#D1FAE5', border: '#16A34A', dot: '#16A34A', text: '#16A34A' },
];

function getColorCategoria(index) {
    return coloresCategorias[index % coloresCategorias.length];
}
</script>

<template>

    <Head title="Productos" />

    <AdminLayout>
        <template #title>Productos</template>
        <template #breadcrumb>Dashboard / Productos</template>

        <div class="productos-page">
            <!-- ============================================================ -->
            <!-- KPIS CON DISEÑO PREMIUM -->
            <!-- ============================================================ -->
            <div class="kpi-grid">
                <div v-for="kpi in kpis" :key="kpi.label" class="kpi-card" :style="{ background: kpi.bg }">
                    <div class="kpi-card__icon" :style="{ background: kpi.iconBg, color: kpi.color }">
                        <i :class="kpi.icon"></i>
                    </div>
                    <div class="kpi-card__content">
                        <span class="kpi-card__label">{{ kpi.label }}</span>
                        <span class="kpi-card__value" :style="{ color: kpi.color }">{{ kpi.value }}</span>
                        <span v-if="kpi.hint" class="kpi-card__hint">{{ kpi.hint }}</span>
                    </div>
                    <div class="kpi-card__bar" :style="{ background: kpi.gradient }"></div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- GRID PRINCIPAL -->
            <!-- ============================================================ -->
            <div class="productos-grid">
                <!-- TABLA DE PRODUCTOS CON DISEÑO MEJORADO -->
                <div class="table-card">
                    <!-- Header con gradiente -->
                    <div class="table-card__header">
                        <div class="table-card__header-left">
                            <div class="header-icon">
                                <i class="pi pi-tags"></i>
                            </div>
                            <div>
                                <h3>Catálogo de Productos</h3>
                                <p class="header-subtitle">{{ productos.total }} productos en la tienda</p>
                            </div>
                        </div>
                        <Link :href="route('admin.productos.create')" class="btn-create">
                            <i class="pi pi-plus"></i>
                            Nuevo Producto
                        </Link>
                    </div>

                    <!-- Filtros con diseño mejorado -->
                    <div class="filters">
                        <div class="filters__search">
                            <i class="pi pi-search"></i>
                            <input v-model="q" type="text" placeholder="Buscar por nombre o SKU..." />
                            <kbd v-if="q" class="search-clear" @click="q = ''">✕</kbd>
                        </div>
                        <div class="filters__selects">
                            <div class="select-wrapper">
                                <i class="pi pi-folder"></i>
                                <select v-model="categoria">
                                    <option value="">Todas las categorías</option>
                                    <option v-for="c in categorias" :key="c" :value="c">{{ c }}</option>
                                </select>
                            </div>
                            <div class="select-wrapper">
                                <i class="pi pi-flag"></i>
                                <select v-model="estado">
                                    <option value="">Todos los estados</option>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="sin_stock">Sin stock</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla con diseño mejorado -->
                    <div class="table-container">
                        <table class="productos-table">
                            <thead>
                                <tr>
                                    <th style="width:28%">Producto</th>
                                    <th style="width:18%">Categoría</th>
                                    <th style="width:14%">Precio</th>
                                    <th style="width:18%">Stock</th>
                                    <th style="width:12%">Estado</th>
                                    <th style="width:10%" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="p in productos.data" :key="p.id">
                                    <td>
                                        <div class="product-info">
                                            <div class="product-image">
                                                <img v-if="p.imagen" :src="p.imagen" :alt="p.nombre"
                                                    @error="(e) => { e.target.src = '/images/placeholder-image.png' }" />
                                                <div v-else class="product-image__placeholder">
                                                    <i class="pi pi-image"></i>
                                                </div>
                                            </div>
                                            <div class="product-details">
                                                <p class="product-name">{{ p.nombre }}</p>
                                                <p class="product-sku">{{ p.sku }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="category-tag" :style="{
                                            background: getColorCategoria(categorias.indexOf(p.categoria)).bg,
                                            color: getColorCategoria(categorias.indexOf(p.categoria)).text,
                                            borderColor: getColorCategoria(categorias.indexOf(p.categoria)).border
                                        }">
                                            <span class="category-dot-small"
                                                :style="{ background: getColorCategoria(categorias.indexOf(p.categoria)).dot }"></span>
                                            {{ p.categoria }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="product-price">{{ money(p.precio) }}</span>
                                    </td>
                                    <td>
                                        <div class="stock-indicator" :class="{
                                            'stock-indicator--danger': p.stock <= 0,
                                            'stock-indicator--warning': p.stock > 0 && p.stock <= 5,
                                            'stock-indicator--success': p.stock > 5
                                        }">
                                            <div class="stock-bar">
                                                <div class="stock-bar__fill" :style="{
                                                    width: p.stock > 100 ? '100%' : (p.stock / 100) * 100 + '%',
                                                    background: p.stock <= 0 ? '#EF4444' : p.stock <= 5 ? '#F59E0B' : '#10B981'
                                                }"></div>
                                            </div>
                                            <span class="stock-value">{{ p.stock <= 0 ? 'Sin stock' : p.stock }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge" :class="{
                                            'status-badge--active': p.esta_activo,
                                            'status-badge--inactive': !p.esta_activo
                                        }">
                                            <span class="status-dot"></span>
                                            {{ p.esta_activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <Link :href="route('admin.productos.show', p.id)"
                                                class="action-btn action-btn--view" title="Ver detalles">
                                                <i class="pi pi-eye"></i>
                                            </Link>
                                            <Link :href="route('admin.productos.edit', p.id)"
                                                class="action-btn action-btn--edit" title="Editar">
                                                <i class="pi pi-pencil"></i>
                                            </Link>
                                            <button @click="eliminarProducto(p)" class="action-btn action-btn--delete"
                                                title="Eliminar">
                                                <i class="pi pi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!productos.data.length">
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-state__icon">
                                                <i class="pi pi-box"></i>
                                            </div>
                                            <h4>No se encontraron productos</h4>
                                            <p>Prueba ajustando los filtros o crea un nuevo producto</p>
                                            <Link :href="route('admin.productos.create')" class="empty-state__btn">
                                                <i class="pi pi-plus"></i>
                                                Crear producto
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer con paginación -->
                    <div v-if="productos.last_page > 1" class="table-footer">
                        <Pagination :data="productos" />
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- SIDEBAR: POR CATEGORÍA CON DISEÑO PREMIUM -->
                <!-- ============================================================ -->
                <div class="sidebar-card">
                    <div class="sidebar-card__header">
                        <div class="sidebar-card__header-left">
                            <div class="sidebar-icon">
                                <i class="pi pi-th-large"></i>
                            </div>
                            <div>
                                <h3>Categorías</h3>
                                <p class="sidebar-subtitle">{{ porCategoria.length }} categorías</p>
                            </div>
                        </div>
                    </div>
                    <div class="sidebar-card__body">
                        <div v-for="(c, index) in porCategoria" :key="c.categoria" class="category-item" :style="{
                            background: getColorCategoria(index).bg,
                            borderColor: getColorCategoria(index).border
                        }">
                            <div class="category-item__left">
                                <span class="category-dot" :style="{ background: getColorCategoria(index).dot }"></span>
                                <span class="category-name">{{ c.categoria }}</span>
                            </div>
                            <div class="category-item__right">
                                <span class="category-count" :style="{
                                    background: getColorCategoria(index).border,
                                    color: 'white'
                                }">{{ c.cantidad }}</span>
                                <div class="category-bar">
                                    <div class="category-bar__fill" :style="{
                                        width: Math.min((c.cantidad / props.stats.total) * 100, 100) + '%',
                                        background: getColorCategoria(index).dot
                                    }"></div>
                                </div>
                            </div>
                        </div>
                        <div v-if="!porCategoria.length" class="empty-categories">
                            <i class="pi pi-folder-open"></i>
                            <span>No hay categorías</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* =========================================================================
   PÁGINA DE PRODUCTOS
   ========================================================================= */
.productos-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 1rem 1.5rem 2rem;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

/* =========================================================================
   KPI GRID - DISEÑO PREMIUM
   ========================================================================= */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 1024px) {
    .kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .kpi-grid {
        grid-template-columns: 1fr;
    }
}

.kpi-card {
    border-radius: 14px;
    padding: 1rem 1.2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 1px solid #EDE9E7;
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.kpi-card:hover {
    transform: translateY(-4px) scale(1.01);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
}

.kpi-card__bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    opacity: 0.7;
}

.kpi-card__icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.kpi-card__content {
    flex: 1;
    min-width: 0;
}

.kpi-card__label {
    display: block;
    font-size: 0.68rem;
    font-weight: 600;
    color: #8A8481;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.kpi-card__value {
    display: block;
    font-size: 1.4rem;
    font-weight: 700;
    margin-top: 0.1rem;
}

.kpi-card__hint {
    display: block;
    font-size: 0.6rem;
    font-weight: 500;
    color: #B7B2AF;
    margin-top: 0.05rem;
}

/* =========================================================================
   GRID PRINCIPAL
   ========================================================================= */
.productos-grid {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1024px) {
    .productos-grid {
        grid-template-columns: 1fr;
    }
}

/* =========================================================================
   TABLA DE PRODUCTOS - DISEÑO MEJORADO
   ========================================================================= */
.table-card {
    background: #FFFFFF;
    border-radius: 16px;
    border: 1px solid #EDE9E7;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.table-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.2rem 1.5rem;
    border-bottom: 1px solid #EDE9E7;
    background: linear-gradient(135deg, #FAF8F7, #FFFFFF);
    flex-wrap: wrap;
    gap: 0.5rem;
}

.table-card__header-left {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.header-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #FBEAEC, #FDF3F5);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #C81E3A;
    font-size: 1.1rem;
    box-shadow: 0 2px 8px rgba(200, 30, 58, 0.08);
}

.table-card__header h3 {
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0;
    color: #171412;
}

.header-subtitle {
    font-size: 0.72rem;
    color: #8A8481;
    margin: 0;
}

.btn-create {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 1.1rem;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, #C81E3A, #A6152D);
    color: #FFFFFF;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 12px rgba(200, 30, 58, 0.2);
}

.btn-create:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 6px 24px rgba(200, 30, 58, 0.3);
}

.btn-create i {
    font-size: 0.65rem;
}

/* =========================================================================
   FILTROS MEJORADOS
   ========================================================================= */
.filters {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.8rem 1.5rem;
    border-bottom: 1px solid #F5F5F5;
    flex-wrap: wrap;
    background: #FFFFFF;
}

.filters__search {
    position: relative;
    flex: 1;
    min-width: 220px;
}

.filters__search i {
    position: absolute;
    left: 0.8rem;
    top: 50%;
    transform: translateY(-50%);
    color: #B7B2AF;
    font-size: 0.75rem;
}

.filters__search input {
    width: 100%;
    padding: 0.5rem 0.8rem 0.5rem 2.4rem;
    border-radius: 10px;
    border: 1.5px solid #EDE9E7;
    font-size: 0.82rem;
    font-family: inherit;
    background: #FAF8F7;
    color: #171412;
    transition: all 0.3s ease;
    outline: none;
}

.filters__search input:focus {
    border-color: #C81E3A;
    background: #FFFFFF;
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.08);
}

.search-clear {
    position: absolute;
    right: 0.6rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.55rem;
    color: #B7B2AF;
    cursor: pointer;
    padding: 0.1rem 0.3rem;
    border-radius: 4px;
    background: #F5F5F5;
    transition: all 0.2s ease;
}

.search-clear:hover {
    background: #EDE9E7;
    color: #4B4744;
}

.filters__selects {
    display: flex;
    gap: 0.6rem;
    flex-wrap: wrap;
}

.select-wrapper {
    position: relative;
}

.select-wrapper i {
    position: absolute;
    left: 0.7rem;
    top: 50%;
    transform: translateY(-50%);
    color: #B7B2AF;
    font-size: 0.65rem;
}

.select-wrapper select {
    padding: 0.5rem 1.8rem 0.5rem 2rem;
    border-radius: 10px;
    border: 1.5px solid #EDE9E7;
    font-size: 0.78rem;
    font-family: inherit;
    background: #FAF8F7 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%238A8481' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 0.6rem center;
    background-size: 10px;
    appearance: none;
    color: #171412;
    transition: all 0.3s ease;
    outline: none;
    cursor: pointer;
    min-width: 150px;
}

.select-wrapper select:focus {
    border-color: #C81E3A;
    background-color: #FFFFFF;
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.08);
}

/* =========================================================================
   TABLA - DISEÑO MEJORADO
   ========================================================================= */
.table-container {
    overflow-x: auto;
    padding: 0 0.5rem;
}

.productos-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.85rem;
}

.productos-table thead {
    background: transparent;
}

.productos-table thead th {
    padding: 0.9rem 1rem;
    text-align: left;
    font-size: 0.68rem;
    font-weight: 700;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    border-bottom: 2px solid #EDE9E7;
    position: sticky;
    top: 0;
    background: #FFFFFF;
    z-index: 10;
}

.productos-table thead th.text-center {
    text-align: center;
}

.productos-table tbody tr {
    transition: all 0.2s ease;
    border-radius: 10px;
}

.productos-table tbody tr:hover {
    background: #FAF8F7;
}

.productos-table tbody td {
    padding: 0.8rem 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #F3F4F6;
}

/* =========================================================================
   PRODUCT INFO
   ========================================================================= */
.product-info {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.product-image {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    overflow: hidden;
    background: #F5F5F5;
    flex-shrink: 0;
    border: 1px solid #EDE9E7;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-image__placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #B7B2AF;
    font-size: 0.9rem;
}

.product-details {
    min-width: 0;
}

.product-name {
    font-weight: 600;
    color: #171412;
    font-size: 0.88rem;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-sku {
    font-size: 0.68rem;
    color: #9CA3AF;
    margin: 0;
    font-family: monospace;
}

/* =========================================================================
   CATEGORY TAG MEJORADA
   ========================================================================= */
.category-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 0.8rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 500;
    border: 1.5px solid;
    transition: all 0.2s ease;
}

.category-tag:hover {
    transform: scale(1.02);
}

.category-dot-small {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
}

/* =========================================================================
   PRODUCT PRICE
   ========================================================================= */
.product-price {
    font-weight: 700;
    color: #171412;
    font-size: 0.88rem;
    background: linear-gradient(135deg, #F9FAFB, #F3F4F6);
    padding: 0.15rem 0.7rem;
    border-radius: 8px;
    border: 1px solid #EDE9E7;
}

/* =========================================================================
   STOCK INDICATOR
   ========================================================================= */
.stock-indicator {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    min-width: 90px;
}

.stock-bar {
    flex: 1;
    height: 6px;
    border-radius: 999px;
    background: #EDE9E7;
    overflow: hidden;
    min-width: 50px;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
}

.stock-bar__fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.stock-value {
    font-size: 0.78rem;
    font-weight: 500;
    color: #4B4744;
    min-width: 25px;
    text-align: right;
}

.stock-indicator--danger .stock-value {
    color: #EF4444;
    font-weight: 700;
}

.stock-indicator--warning .stock-value {
    color: #F59E0B;
    font-weight: 700;
}

.stock-indicator--success .stock-value {
    color: #10B981;
    font-weight: 700;
}

/* =========================================================================
   STATUS BADGE
   ========================================================================= */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.25rem 0.7rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    border: 1px solid transparent;
}

.status-badge .status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.5);
}

.status-badge--active {
    background: #ECFDF5;
    color: #059669;
    border-color: #A7F3D0;
}

.status-badge--active .status-dot {
    background: #059669;
}

.status-badge--inactive {
    background: #F3F4F6;
    color: #6B7280;
    border-color: #E5E7EB;
}

.status-badge--inactive .status-dot {
    background: #6B7280;
}

/* =========================================================================
   ACCIONES CON DISEÑO MEJORADO
   ========================================================================= */
.actions {
    display: flex;
    justify-content: center;
    gap: 0.3rem;
}

.action-btn {
    width: 34px;
    height: 34px;
    border: none;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 0.78rem;
    color: #6B7280;
    background: #F9FAFB;
    text-decoration: none;
    border: 1px solid #EDE9E7;
}

.action-btn:hover {
    transform: scale(1.12);
}

.action-btn--view {
    color: #3B82F6;
}

.action-btn--view:hover {
    background: #EFF6FF;
    border-color: #3B82F6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.action-btn--edit {
    color: #C81E3A;
}

.action-btn--edit:hover {
    background: #FBEAEC;
    border-color: #C81E3A;
    box-shadow: 0 4px 12px rgba(200, 30, 58, 0.2);
}

.action-btn--delete {
    color: #EF4444;
}

.action-btn--delete:hover {
    background: #FEF2F2;
    border-color: #EF4444;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
}

/* =========================================================================
   EMPTY STATE
   ========================================================================= */
.empty-state {
    text-align: center;
    padding: 3.5rem 0.5rem;
}

.empty-state__icon {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #FBEAEC, #FDF3F5);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2rem;
    color: #C81E3A;
    box-shadow: 0 4px 16px rgba(200, 30, 58, 0.08);
}

.empty-state h4 {
    font-size: 1rem;
    font-weight: 600;
    color: #171412;
    margin: 0 0 0.3rem;
}

.empty-state p {
    font-size: 0.82rem;
    color: #8A8481;
    margin: 0 0 1rem;
}

.empty-state__btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 1.2rem;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, #C81E3A, #A6152D);
    color: #FFFFFF;
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 2px 12px rgba(200, 30, 58, 0.2);
}

.empty-state__btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(200, 30, 58, 0.3);
}

/* =========================================================================
   TABLE FOOTER
   ========================================================================= */
.table-footer {
    padding: 0.8rem 1.5rem;
    border-top: 1px solid #EDE9E7;
    background: #FAF8F7;
    display: flex;
    justify-content: center;
}

/* =========================================================================
   SIDEBAR: POR CATEGORÍA CON DISEÑO PREMIUM
   ========================================================================= */
.sidebar-card {
    background: #FFFFFF;
    border-radius: 16px;
    border: 1px solid #EDE9E7;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.sidebar-card__header {
    padding: 1rem 1.2rem;
    border-bottom: 1px solid #EDE9E7;
    background: linear-gradient(135deg, #FAF8F7, #FFFFFF);
}

.sidebar-card__header-left {
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.sidebar-icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: linear-gradient(135deg, #FBEAEC, #FDF3F5);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #C81E3A;
    font-size: 0.95rem;
    box-shadow: 0 2px 8px rgba(200, 30, 58, 0.08);
}

.sidebar-card__header h3 {
    font-size: 0.85rem;
    font-weight: 700;
    margin: 0;
    color: #171412;
}

.sidebar-subtitle {
    font-size: 0.65rem;
    color: #8A8481;
    margin: 0;
}

.sidebar-card__body {
    padding: 0.6rem 1rem;
}

.category-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.6rem 0.8rem;
    border-radius: 12px;
    transition: all 0.2s ease;
    border: 1.5px solid transparent;
    margin-bottom: 0.4rem;
}

.category-item:hover {
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.category-item__left {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.category-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    border: 1px solid rgba(255, 255, 255, 0.5);
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.category-name {
    font-size: 0.8rem;
    color: #171412;
    font-weight: 500;
}

.category-item__right {
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.category-count {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.1rem 0.6rem;
    border-radius: 999px;
    min-width: 24px;
    text-align: center;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.category-bar {
    width: 45px;
    height: 5px;
    border-radius: 999px;
    background: #EDE9E7;
    overflow: hidden;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
}

.category-bar__fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.empty-categories {
    text-align: center;
    padding: 1.5rem 0.5rem;
    color: #B7B2AF;
}

.empty-categories i {
    font-size: 1.5rem;
    color: #D1D5DB;
    display: block;
    margin-bottom: 0.3rem;
}

.empty-categories span {
    font-size: 0.78rem;
}
</style>