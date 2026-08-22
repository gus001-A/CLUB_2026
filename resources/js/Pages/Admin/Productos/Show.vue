<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
    producto: Object,
});

const { confirm } = useConfirm();
const toast = useToast();
const { money } = useFormatters();

// ============================================================
// COMPUTADAS
// ============================================================
const imagenPrincipal = computed(() => {
    if (props.producto?.imagenes && props.producto.imagenes.length > 0) {
        return props.producto.imagenes[0];
    }
    return null;
});

const tieneImagenes = computed(() => {
    return props.producto?.imagenes && props.producto.imagenes.length > 0;
});

const estadoColor = computed(() => {
    if (props.producto.esta_activo) {
        return {
            bg: 'bg-emerald-50',
            text: 'text-emerald-700',
            border: 'border-emerald-200',
            dot: 'bg-emerald-500',
            label: 'Activo'
        };
    }
    return {
        bg: 'bg-gray-50',
        text: 'text-gray-500',
        border: 'border-gray-200',
        dot: 'bg-gray-400',
        label: 'Inactivo'
    };
});

const stockColor = computed(() => {
    if (props.producto.stock <= 0) {
        return {
            bg: 'bg-red-50',
            text: 'text-red-700',
            border: 'border-red-200',
            dot: 'bg-red-500',
            label: 'Sin stock'
        };
    }
    if (props.producto.stock <= 5) {
        return {
            bg: 'bg-amber-50',
            text: 'text-amber-700',
            border: 'border-amber-200',
            dot: 'bg-amber-500',
            label: `Stock bajo (${props.producto.stock})`
        };
    }
    return {
        bg: 'bg-emerald-50',
        text: 'text-emerald-700',
        border: 'border-emerald-200',
        dot: 'bg-emerald-500',
        label: `${props.producto.stock} unidades`
    };
});

// ============================================================
// FUNCIONES
// ============================================================
async function eliminar() {
    const ok = await confirm(`¿Estás seguro de que deseas eliminar "${props.producto.nombre}"?`, {
        title: 'Eliminar producto',
        confirmLabel: 'Sí, eliminar',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('admin.productos.destroy', props.producto.id), {
        onSuccess: () => toast.success(`Producto "${props.producto.nombre}" eliminado correctamente.`),
        onError: () => toast.error('No se pudo eliminar el producto.'),
    });
}
</script>

<template>

    <Head :title="producto.nombre" />

    <AdminLayout>
        <template #title>{{ producto.nombre }}</template>
        <template #breadcrumb>Dashboard / Productos / {{ producto.nombre }}</template>

        <div class="admin-prod-show-page">
            <!-- Botón volver -->
            <Link :href="route('admin.productos.index')" class="admin-prod-back-link">
                <i class="pi pi-arrow-left"></i>
                <span>Volver a Productos</span>
            </Link>

            <!-- ============================================================ -->
            <!-- HEADER DEL PRODUCTO -->
            <!-- ============================================================ -->
            <div class="admin-prod-header">
                <div class="admin-prod-header-content">
                    <div class="admin-prod-header-left">
                        <!-- Imagen -->
                        <div class="admin-prod-header-image-wrapper">
                            <div class="admin-prod-header-image-ring">
                                <div class="admin-prod-header-image">
                                    <img v-if="imagenPrincipal" :src="imagenPrincipal" :alt="producto.nombre"
                                        @error="(e) => { e.target.src = '/images/placeholder-image.png' }" />
                                    <div v-else class="admin-prod-header-placeholder">
                                        <i class="pi pi-box"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="admin-prod-header-status-badge"
                                :class="estadoColor.bg + ' ' + estadoColor.text + ' ' + estadoColor.border">
                                <span class="admin-prod-header-status-dot" :class="estadoColor.dot"></span>
                                {{ estadoColor.label }}
                            </div>
                        </div>

                        <!-- Información -->
                        <div class="admin-prod-header-info">
                            <div class="admin-prod-header-name-row">
                                <h1>{{ producto.nombre }}</h1>
                                <span class="admin-prod-header-sku">{{ producto.sku }}</span>
                            </div>

                            <div class="admin-prod-header-meta">
                                <span class="admin-prod-meta-item">
                                    <i class="pi pi-folder"></i>
                                    {{ producto.categoria }}
                                </span>
                                <span class="admin-prod-meta-divider">•</span>
                                <span v-if="producto.marca" class="admin-prod-meta-item">
                                    <i class="pi pi-tag"></i>
                                    {{ producto.marca }}
                                </span>
                                <span v-else class="admin-prod-meta-item admin-prod-meta-item--empty">
                                    <i class="pi pi-tag"></i>
                                    Sin marca
                                </span>
                            </div>

                            <div class="admin-prod-header-stats">
                                <div class="admin-prod-stat-item">
                                    <i class="pi pi-ticket"></i>
                                    <span class="admin-prod-stat-value">{{ money(producto.precio) }}</span>
                                    <span class="admin-prod-stat-label">precio</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-prod-header-right">
                        <div class="admin-prod-stock-badge"
                            :class="stockColor.bg + ' ' + stockColor.text + ' ' + stockColor.border">
                            <span class="admin-prod-stock-dot" :class="stockColor.dot"></span>
                            <span class="admin-prod-stock-label">{{ stockColor.label }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- GRID PRINCIPAL -->
            <!-- ============================================================ -->
            <div class="admin-prod-show-grid">
                <!-- COLUMNA IZQUIERDA: INFORMACIÓN -->
                <div class="admin-prod-show-left">
                    <!-- Descripción -->
                    <div class="admin-prod-info-card admin-prod-info-card--highlight">
                        <div class="admin-prod-info-card-header">
                            <h3><i class="pi pi-align-left"></i> Descripción</h3>
                        </div>
                        <div class="admin-prod-info-card-body">
                            <p v-if="producto.descripcion" class="admin-prod-description-text">
                                {{ producto.descripcion }}
                            </p>
                            <div v-else class="admin-prod-inline-empty">
                                <i class="pi pi-file-edit"></i>
                                <span>No hay descripción disponible</span>
                            </div>
                        </div>
                    </div>

                    <!-- Imágenes (galería) -->
                    <div v-if="tieneImagenes" class="admin-prod-info-card">
                        <div class="admin-prod-info-card-header">
                            <h3><i class="pi pi-images"></i> Galería</h3>
                            <span class="admin-prod-count-badge">{{ producto.imagenes.length }} imágenes</span>
                        </div>
                        <div class="admin-prod-info-card-body">
                            <div class="admin-prod-gallery-grid">
                                <div v-for="(img, i) in producto.imagenes" :key="i" class="admin-prod-gallery-item"
                                    :class="{ 'admin-prod-gallery-item--principal': i === 0 }">
                                    <img :src="img" :alt="`${producto.nombre} - Imagen ${i + 1}`" />
                                    <div class="admin-prod-gallery-item-overlay">
                                        <span v-if="i === 0" class="admin-prod-gallery-item-badge">
                                            <i class="pi pi-star-fill"></i> Principal
                                        </span>
                                        <span v-else class="admin-prod-gallery-item-badge admin-prod-gallery-item-badge--secondary">
                                            #{{ i + 1 }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Etiquetas -->
                    <div v-if="producto.etiquetas && producto.etiquetas.length" class="admin-prod-info-card">
                        <div class="admin-prod-info-card-header">
                            <h3><i class="pi pi-hashtag"></i> Etiquetas</h3>
                            <span class="admin-prod-count-badge">{{ producto.etiquetas.length }}</span>
                        </div>
                        <div class="admin-prod-info-card-body">
                            <div class="admin-prod-tags-group">
                                <span v-for="et in producto.etiquetas" :key="et" class="admin-prod-tag">
                                    <i class="pi pi-tag"></i>
                                    {{ et }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Variantes -->
                    <div v-if="producto.variantes && Object.keys(producto.variantes).length" class="admin-prod-info-card">
                        <div class="admin-prod-info-card-header">
                            <h3><i class="pi pi-sliders-h"></i> Variantes</h3>
                            <span class="admin-prod-count-badge">{{ Object.keys(producto.variantes).length }}</span>
                        </div>
                        <div class="admin-prod-info-card-body">
                            <div class="admin-prod-variants-list">
                                <div v-for="(valores, nombre) in producto.variantes" :key="nombre" class="admin-prod-variant-item">
                                    <span class="admin-prod-variant-item-name">{{ nombre }}</span>
                                    <span class="admin-prod-variant-item-values">
                                        {{ Array.isArray(valores) ? valores.join(' · ') : valores }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: ACCIONES -->
                <div class="admin-prod-show-right">
                    <div class="admin-prod-actions-card">
                        <div class="admin-prod-actions-card-header">
                            <h3><i class="pi pi-bolt"></i> Acciones</h3>
                        </div>
                        <div class="admin-prod-actions-card-body">
                            <Link :href="route('admin.productos.edit', producto.id)" class="admin-prod-btn-edit">
                                <i class="pi pi-pencil"></i>
                                <span>Editar producto</span>
                            </Link>
                            <button @click="eliminar" class="admin-prod-btn-delete">
                                <i class="pi pi-trash"></i>
                                <span>Eliminar producto</span>
                            </button>
                            <div class="admin-prod-actions-divider"></div>
                            <Link :href="route('admin.productos.index')" class="admin-prod-btn-back">
                                <i class="pi pi-arrow-left"></i>
                                <span>Volver al listado</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>