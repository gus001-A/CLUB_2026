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

        <div class="admin-prod-list-page">
            <!-- ============================================================ -->
            <!-- KPIS CON DISEÑO PREMIUM -->
            <!-- ============================================================ -->
            <div class="admin-prod-kpi-grid">
                <div v-for="kpi in kpis" :key="kpi.label" class="admin-prod-kpi-card" :style="{ background: kpi.bg }">
                    <div class="admin-prod-kpi-card__icon" :style="{ background: kpi.iconBg, color: kpi.color }">
                        <i :class="kpi.icon"></i>
                    </div>
                    <div class="admin-prod-kpi-card__content">
                        <span class="admin-prod-kpi-card__label">{{ kpi.label }}</span>
                        <span class="admin-prod-kpi-card__value" :style="{ color: kpi.color }">{{ kpi.value }}</span>
                        <span v-if="kpi.hint" class="admin-prod-kpi-card__hint">{{ kpi.hint }}</span>
                    </div>
                    <div class="admin-prod-kpi-card__bar" :style="{ background: kpi.gradient }"></div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- GRID PRINCIPAL -->
            <!-- ============================================================ -->
            <div class="admin-prod-list-grid">
                <!-- TABLA DE PRODUCTOS CON DISEÑO MEJORADO -->
                <div class="admin-prod-table-card">
                    <!-- Header con gradiente -->
                    <div class="admin-prod-table-card__header">
                        <div class="admin-prod-table-card__header-left">
                            <div class="admin-prod-header-icon">
                                <i class="pi pi-tags"></i>
                            </div>
                            <div>
                                <h3>Catálogo de Productos</h3>
                                <p class="admin-prod-header-subtitle">{{ productos.total }} productos en la tienda</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <Link :href="route('admin.productos.todos')" class="admin-btn-secondary">
                                <i class="pi pi-list"></i>
                                Ver todos
                            </Link>
                            <Link :href="route('admin.productos.create')" class="admin-prod-btn-create">
                                <i class="pi pi-plus"></i>
                                Nuevo Producto
                            </Link>
                        </div>
                    </div>

                    <!-- Filtros con diseño mejorado -->
                    <div class="admin-prod-filters">
                        <div class="admin-prod-filters__search">
                            <i class="pi pi-search"></i>
                            <input v-model="q" type="text" placeholder="Buscar por nombre o SKU..." />
                            <kbd v-if="q" class="admin-prod-search-clear" @click="q = ''">✕</kbd>
                        </div>
                        <div class="admin-prod-filters__selects">
                            <div class="admin-prod-select-wrapper">
                                <i class="pi pi-folder"></i>
                                <select v-model="categoria">
                                    <option value="">Todas las categorías</option>
                                    <option v-for="c in categorias" :key="c" :value="c">{{ c }}</option>
                                </select>
                            </div>
                            <div class="admin-prod-select-wrapper">
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
                    <div class="admin-prod-table-container">
                        <table class="admin-prod-table">
                            <thead>
                                <tr>
                                    <th style="width:28%">Producto</th>
                                    <th style="width:18%">Categoría</th>
                                    <th style="width:14%">Precio</th>
                                    <th style="width:18%">Stock</th>
                                    <th style="width:12%">Estado</th>
                                    <th style="width:10%" class="admin-prod-text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="p in productos.data" :key="p.id">
                                    <td>
                                        <div class="admin-prod-info">
                                            <div class="admin-prod-image">
                                                <img v-if="p.imagen" :src="p.imagen" :alt="p.nombre"
                                                    @error="(e) => { e.target.src = '/images/placeholder-image.png' }" />
                                                <div v-else class="admin-prod-image__placeholder">
                                                    <i class="pi pi-image"></i>
                                                </div>
                                            </div>
                                            <div class="admin-prod-details">
                                                <p class="admin-prod-name">{{ p.nombre }}</p>
                                                <p class="admin-prod-sku">{{ p.sku }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="admin-prod-category-tag" :style="{
                                            background: getColorCategoria(categorias.indexOf(p.categoria)).bg,
                                            color: getColorCategoria(categorias.indexOf(p.categoria)).text,
                                            borderColor: getColorCategoria(categorias.indexOf(p.categoria)).border
                                        }">
                                            <span class="admin-prod-category-dot-small"
                                                :style="{ background: getColorCategoria(categorias.indexOf(p.categoria)).dot }"></span>
                                            {{ p.categoria }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="admin-prod-price">{{ money(p.precio) }}</span>
                                    </td>
                                    <td>
                                        <div class="admin-prod-stock-indicator" :class="{
                                            'admin-prod-stock-indicator--danger': p.stock <= 0,
                                            'admin-prod-stock-indicator--warning': p.stock > 0 && p.stock <= 5,
                                            'admin-prod-stock-indicator--success': p.stock > 5
                                        }">
                                            <div class="admin-prod-stock-bar">
                                                <div class="admin-prod-stock-bar__fill" :style="{
                                                    width: p.stock > 100 ? '100%' : (p.stock / 100) * 100 + '%',
                                                    background: p.stock <= 0 ? '#EF4444' : p.stock <= 5 ? '#F59E0B' : '#10B981'
                                                }"></div>
                                            </div>
                                            <span class="admin-prod-stock-value">{{ p.stock <= 0 ? 'Sin stock' : p.stock }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="admin-prod-status-badge" :class="{
                                            'admin-prod-status-badge--active': p.esta_activo,
                                            'admin-prod-status-badge--inactive': !p.esta_activo
                                        }">
                                            <span class="admin-prod-status-dot"></span>
                                            {{ p.esta_activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="admin-prod-actions">
                                            <Link :href="route('admin.productos.show', p.id)"
                                                class="admin-prod-action-btn admin-prod-action-btn--view" title="Ver detalles">
                                                <i class="pi pi-eye"></i>
                                            </Link>
                                            <Link :href="route('admin.productos.edit', p.id)"
                                                class="admin-prod-action-btn admin-prod-action-btn--edit" title="Editar">
                                                <i class="pi pi-pencil"></i>
                                            </Link>
                                            <button @click="eliminarProducto(p)" class="admin-prod-action-btn admin-prod-action-btn--delete"
                                                title="Eliminar">
                                                <i class="pi pi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!productos.data.length">
                                    <td colspan="6">
                                        <div class="admin-prod-empty-state">
                                            <div class="admin-prod-empty-state__icon">
                                                <i class="pi pi-box"></i>
                                            </div>
                                            <h4>No se encontraron productos</h4>
                                            <p>Prueba ajustando los filtros o crea un nuevo producto</p>
                                            <Link :href="route('admin.productos.create')" class="admin-prod-empty-state__btn">
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
                    <div v-if="productos.last_page > 1" class="admin-prod-table-footer">
                        <Pagination :data="productos" />
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- SIDEBAR: POR CATEGORÍA CON DISEÑO PREMIUM -->
                <!-- ============================================================ -->
                <div class="admin-prod-sidebar-card">
                    <div class="admin-prod-sidebar-card__header">
                        <div class="admin-prod-sidebar-card__header-left">
                            <div class="admin-prod-sidebar-icon">
                                <i class="pi pi-th-large"></i>
                            </div>
                            <div>
                                <h3>Categorías</h3>
                                <p class="admin-prod-sidebar-subtitle">{{ porCategoria.length }} categorías</p>
                            </div>
                        </div>
                    </div>
                    <div class="admin-prod-sidebar-card__body">
                        <div v-for="(c, index) in porCategoria" :key="c.categoria" class="admin-prod-category-item" :style="{
                            background: getColorCategoria(index).bg,
                            borderColor: getColorCategoria(index).border
                        }">
                            <div class="admin-prod-category-item__left">
                                <span class="admin-prod-category-dot" :style="{ background: getColorCategoria(index).dot }"></span>
                                <span class="admin-prod-category-name">{{ c.categoria }}</span>
                            </div>
                            <div class="admin-prod-category-item__right">
                                <span class="admin-prod-category-count" :style="{
                                    background: getColorCategoria(index).border,
                                    color: 'white'
                                }">{{ c.cantidad }}</span>
                                <div class="admin-prod-category-bar">
                                    <div class="admin-prod-category-bar__fill" :style="{
                                        width: Math.min((c.cantidad / props.stats.total) * 100, 100) + '%',
                                        background: getColorCategoria(index).dot
                                    }"></div>
                                </div>
                            </div>
                        </div>
                        <div v-if="!porCategoria.length" class="admin-prod-empty-categories">
                            <i class="pi pi-folder-open"></i>
                            <span>No hay categorías</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>