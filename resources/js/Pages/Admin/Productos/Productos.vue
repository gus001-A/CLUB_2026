<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
    productos: Object,
    filtros: Object,
    categorias: Array,
    porCategoria: Array,
    porEstado: Array,
    totalGeneral: Number,
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
        router.get(route('admin.productos.todos'), {
            q: q.value || undefined,
            categoria: categoria.value || undefined,
            estado: estado.value || undefined,
        }, { preserveState: true, preserveScroll: true, replace: true });
    }, 350);
});

async function eliminarProducto(p) {
    const ok = await confirm(`Esto eliminará "${p.nombre}" permanentemente.`, {
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

// Mismos colores por estado y categoría que en el resto del módulo
const coloresEstado = {
    activo: { dot: '#059669', bg: '#ECFDF5' },
    inactivo: { dot: '#6B7280', bg: '#F3F4F6' },
    sin_stock: { dot: '#DC2626', bg: '#FEF2F2' },
};

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

    <Head title="Todos los Productos" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Productos / Todos los productos</template>

        <div class="admin-prod-list-page">
            <!-- Botón volver -->
            <Link :href="route('admin.productos.index')" class="admin-prod-back-link">
                <i class="pi pi-arrow-left"></i>
                Volver a Productos
            </Link>

            <!-- ============================================================ -->
            <!-- DESGLOSE POR ESTADO (filtros rápidos) -->
            <!-- ============================================================ -->
            <div class="admin-prod-estado-grid">
                <button v-for="e in porEstado" :key="e.estado" type="button"
                    @click="estado = (estado === e.estado ? '' : e.estado)" class="admin-prod-estado-chip"
                    :class="{ 'admin-prod-estado-chip--active': estado === e.estado }">
                    <span class="admin-prod-estado-chip-dot" :style="{ background: coloresEstado[e.estado]?.dot }"></span>
                    <span>
                        <span class="admin-prod-estado-chip-value">{{ e.cantidad }}</span>
                        <span class="admin-prod-estado-chip-label">{{ e.label }}</span>
                    </span>
                </button>
            </div>

            <!-- ============================================================ -->
            <!-- TABLA COMPLETA -->
            <!-- ============================================================ -->
            <div class="admin-prod-table-card" style="margin-bottom:1.5rem">
                <div class="admin-prod-table-card__header">
                    <div class="admin-prod-table-card__header-left">
                        <div class="admin-prod-header-icon">
                            <i class="pi pi-list"></i>
                        </div>
                        <div>
                            <h3>Catálogo completo</h3>
                            <p class="admin-prod-header-subtitle">{{ totalGeneral }} productos registrados en total</p>
                        </div>
                    </div>
                    <Link :href="route('admin.productos.create')" class="admin-prod-btn-create">
                        <i class="pi pi-plus"></i>
                        Nuevo Producto
                    </Link>
                </div>

                <!-- Filtros -->
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

                <!-- Tabla -->
                <div class="admin-prod-table-container">
                    <table class="admin-prod-table">
                        <thead>
                            <tr>
                                <th style="width:24%">Producto</th>
                                <th style="width:14%">Categoría</th>
                                <th style="width:12%">Marca</th>
                                <th style="width:12%">Precio</th>
                                <th style="width:16%">Stock</th>
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
                                <td style="font-size:0.8rem;color:var(--muted)">{{ p.marca || '—' }}</td>
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
                            <tr v-if="!productos.data?.length">
                                <td colspan="7">
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

                <div v-if="productos.last_page > 1" class="admin-prod-table-footer">
                    <Pagination :data="productos" />
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- DESGLOSE POR CATEGORÍA -->
            <!-- ============================================================ -->
            <div class="admin-prod-sidebar-card">
                <div class="admin-prod-sidebar-card__header">
                    <div class="admin-prod-sidebar-card__header-left">
                        <div class="admin-prod-sidebar-icon">
                            <i class="pi pi-th-large"></i>
                        </div>
                        <div>
                            <h3>Desglose por categoría</h3>
                            <p class="admin-prod-sidebar-subtitle">{{ porCategoria.length }} categorías</p>
                        </div>
                    </div>
                </div>
                <div class="admin-prod-category-grid">
                    <div v-for="(c, index) in porCategoria" :key="c.categoria" class="admin-prod-category-item" :style="{
                        background: getColorCategoria(index).bg,
                        borderColor: getColorCategoria(index).border
                    }">
                        <div class="admin-prod-category-item__left">
                            <span class="admin-prod-category-dot" :style="{ background: getColorCategoria(index).dot }"></span>
                            <span class="admin-prod-category-name">{{ c.categoria }}</span>
                        </div>
                        <span class="admin-prod-category-count" :style="{ background: getColorCategoria(index).border, color: 'white' }">
                            {{ c.cantidad }}
                        </span>
                    </div>
                    <div v-if="!porCategoria.length" class="admin-prod-empty-categories">
                        <i class="pi pi-folder-open"></i>
                        <span>No hay categorías</span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>