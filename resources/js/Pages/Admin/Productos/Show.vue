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

        <div class="product-show-page">
            <!-- Botón volver -->
            <Link :href="route('admin.productos.index')" class="back-link">
                <i class="pi pi-arrow-left"></i>
                <span>Volver a Productos</span>
            </Link>

            <!-- ============================================================ -->
            <!-- HEADER DEL PRODUCTO -->
            <!-- ============================================================ -->
            <div class="product-header">
                <div class="product-header__content">
                    <div class="product-header__left">
                        <!-- Imagen -->
                        <div class="product-header__image-wrapper">
                            <div class="product-header__image-ring">
                                <div class="product-header__image">
                                    <img v-if="imagenPrincipal" :src="imagenPrincipal" :alt="producto.nombre"
                                        @error="(e) => { e.target.src = '/images/placeholder-image.png' }" />
                                    <div v-else class="product-header__placeholder">
                                        <i class="pi pi-box"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="product-header__status-badge"
                                :class="estadoColor.bg + ' ' + estadoColor.text + ' ' + estadoColor.border">
                                <span class="status-dot" :class="estadoColor.dot"></span>
                                {{ estadoColor.label }}
                            </div>
                        </div>

                        <!-- Información -->
                        <div class="product-header__info">
                            <div class="product-header__name-row">
                                <h1>{{ producto.nombre }}</h1>
                                <span class="product-header__sku">{{ producto.sku }}</span>
                            </div>

                            <div class="product-header__meta">
                                <span class="meta-item">
                                    <i class="pi pi-folder"></i>
                                    {{ producto.categoria }}
                                </span>
                                <span class="meta-divider">•</span>
                                <span v-if="producto.marca" class="meta-item">
                                    <i class="pi pi-tag"></i>
                                    {{ producto.marca }}
                                </span>
                                <span v-else class="meta-item meta-item--empty">
                                    <i class="pi pi-tag"></i>
                                    Sin marca
                                </span>
                            </div>

                            <div class="product-header__stats">
                                <div class="stat-item">
                                    <i class="pi pi-box"></i>
                                    <span class="stat-value">{{ producto.stock }}</span>
                                    <span class="stat-label">unidades</span>
                                </div>
                                <div class="stat-divider"></div>
                                <div class="stat-item">
                                    <i class="pi pi-ticket"></i>
                                    <span class="stat-value">{{ money(producto.precio) }}</span>
                                    <span class="stat-label">precio</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="product-header__right">
                        <div class="stock-badge"
                            :class="stockColor.bg + ' ' + stockColor.text + ' ' + stockColor.border">
                            <span class="stock-dot" :class="stockColor.dot"></span>
                            <span class="stock-label">{{ stockColor.label }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- GRID PRINCIPAL -->
            <!-- ============================================================ -->
            <div class="product-show-grid">
                <!-- COLUMNA IZQUIERDA: INFORMACIÓN -->
                <div class="product-show-left">
                    <!-- Descripción -->
                    <div class="info-card info-card--highlight">
                        <div class="info-card__header">
                            <h3><i class="pi pi-align-left"></i> Descripción</h3>
                        </div>
                        <div class="info-card__body">
                            <p v-if="producto.descripcion" class="description-text">
                                {{ producto.descripcion }}
                            </p>
                            <div v-else class="empty-state">
                                <i class="pi pi-file-edit"></i>
                                <span>No hay descripción disponible</span>
                            </div>
                        </div>
                    </div>

                    <!-- Imágenes (galería) -->
                    <div v-if="tieneImagenes" class="info-card">
                        <div class="info-card__header">
                            <h3><i class="pi pi-images"></i> Galería</h3>
                            <span class="count-badge">{{ producto.imagenes.length }} imágenes</span>
                        </div>
                        <div class="info-card__body">
                            <div class="gallery-grid">
                                <div v-for="(img, i) in producto.imagenes" :key="i" class="gallery-item"
                                    :class="{ 'gallery-item--principal': i === 0 }">
                                    <img :src="img" :alt="`${producto.nombre} - Imagen ${i + 1}`" />
                                    <div class="gallery-item__overlay">
                                        <span v-if="i === 0" class="gallery-item__badge">
                                            <i class="pi pi-star-fill"></i> Principal
                                        </span>
                                        <span v-else class="gallery-item__badge gallery-item__badge--secondary">
                                            #{{ i + 1 }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Etiquetas -->
                    <div v-if="producto.etiquetas && producto.etiquetas.length" class="info-card">
                        <div class="info-card__header">
                            <h3><i class="pi pi-hashtag"></i> Etiquetas</h3>
                            <span class="count-badge">{{ producto.etiquetas.length }}</span>
                        </div>
                        <div class="info-card__body">
                            <div class="tags-group">
                                <span v-for="et in producto.etiquetas" :key="et" class="tag">
                                    <i class="pi pi-tag"></i>
                                    {{ et }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Variantes -->
                    <div v-if="producto.variantes && Object.keys(producto.variantes).length" class="info-card">
                        <div class="info-card__header">
                            <h3><i class="pi pi-sliders-h"></i> Variantes</h3>
                            <span class="count-badge">{{ Object.keys(producto.variantes).length }}</span>
                        </div>
                        <div class="info-card__body">
                            <div class="variants-list">
                                <div v-for="(valores, nombre) in producto.variantes" :key="nombre" class="variant-item">
                                    <span class="variant-item__name">{{ nombre }}</span>
                                    <span class="variant-item__values">
                                        {{ Array.isArray(valores) ? valores.join(' · ') : valores }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: ACCIONES -->
                <div class="product-show-right">
                    <div class="actions-card">
                        <div class="actions-card__header">
                            <h3><i class="pi pi-bolt"></i> Acciones</h3>
                        </div>
                        <div class="actions-card__body">
                            <Link :href="route('admin.productos.edit', producto.id)" class="btn-edit">
                                <i class="pi pi-pencil"></i>
                                <span>Editar producto</span>
                            </Link>
                            <button @click="eliminar" class="btn-delete">
                                <i class="pi pi-trash"></i>
                                <span>Eliminar producto</span>
                            </button>
                            <div class="actions-divider"></div>
                            <Link :href="route('admin.productos.index')" class="btn-back">
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

<style scoped>
/* =========================================================================
   PÁGINA DE PRODUCTO
   ========================================================================= */
.product-show-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem 1.5rem 2rem;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

/* =========================================================================
   BOTÓN VOLVER
   ========================================================================= */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #8A8481;
    text-decoration: none;
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
    padding: 0.3rem 0.8rem;
    border-radius: 8px;
    background: #FAF8F7;
    border: 1px solid #EDE9E7;
}

.back-link:hover {
    color: #C81E3A;
    background: #FBEAEC;
    border-color: #C81E3A;
    transform: translateX(-2px);
}

.back-link i {
    font-size: 0.6rem;
}

/* =========================================================================
   HEADER DEL PRODUCTO
   ========================================================================= */
.product-header {
    position: relative;
    background: #FFFFFF;
    border-radius: 16px;
    border: 1px solid #EDE9E7;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    margin-bottom: 1.5rem;
}

.product-header__content {
    padding: 1.5rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .product-header__content {
        flex-direction: column;
        align-items: flex-start;
        padding: 1.25rem;
    }
}

.product-header__left {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex: 1;
    min-width: 0;
}

@media (max-width: 600px) {
    .product-header__left {
        flex-direction: column;
        align-items: flex-start;
        width: 100%;
    }
}

.product-header__image-wrapper {
    position: relative;
    flex-shrink: 0;
}

.product-header__image-ring {
    padding: 3px;
    border-radius: 14px;
    background: linear-gradient(135deg, #C81E3A, #E85A72);
}

.product-header__image {
    width: 100px;
    height: 100px;
    border-radius: 12px;
    overflow: hidden;
    background: #FAF8F7;
    border: 2px solid #FFFFFF;
}

.product-header__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-header__placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #B7B2AF;
    font-size: 2.2rem;
    background: linear-gradient(135deg, #FAF8F7, #F0EEEC);
}

.product-header__status-badge {
    position: absolute;
    bottom: -4px;
    right: -4px;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.6rem;
    font-weight: 700;
    padding: 0.1rem 0.5rem;
    border-radius: 999px;
    border: 2px solid #FFFFFF;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.status-dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    display: inline-block;
}

.product-header__info {
    flex: 1;
    min-width: 0;
}

.product-header__name-row {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    flex-wrap: wrap;
    margin-bottom: 0.25rem;
}

.product-header__name-row h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #171412;
    margin: 0;
}

.product-header__sku {
    font-size: 0.7rem;
    font-weight: 600;
    color: #8A8481;
    background: #F5F5F5;
    padding: 0.1rem 0.6rem;
    border-radius: 999px;
    font-family: monospace;
}

.product-header__meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    font-size: 0.82rem;
    color: #8A8481;
    margin-bottom: 0.6rem;
}

.meta-item {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.meta-item i {
    font-size: 0.7rem;
    color: #C81E3A;
}

.meta-item--empty {
    color: #B7B2AF;
}

.meta-item--empty i {
    color: #D1D5DB;
}

.meta-divider {
    color: #EDE9E7;
}

.product-header__stats {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    background: #FAF8F7;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    font-size: 0.7rem;
}

.stat-item i {
    color: #C81E3A;
    font-size: 0.6rem;
}

.stat-value {
    font-weight: 700;
    color: #171412;
}

.stat-label {
    color: #8A8481;
    font-size: 0.6rem;
}

.stat-divider {
    width: 1px;
    height: 20px;
    background: #EDE9E7;
}

.product-header__right {
    flex-shrink: 0;
}

.stock-badge {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 1rem;
    border-radius: 999px;
    border: 1.5px solid;
    font-weight: 600;
    font-size: 0.75rem;
}

.stock-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
    animation: pulse-dot 2s ease-in-out infinite;
}

@keyframes pulse-dot {

    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }

    50% {
        opacity: 0.5;
        transform: scale(0.8);
    }
}

.stock-label {
    white-space: nowrap;
}

/* =========================================================================
   GRID PRINCIPAL
   ========================================================================= */
.product-show-grid {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1024px) {
    .product-show-grid {
        grid-template-columns: 1fr;
    }
}

/* =========================================================================
   COLUMNA IZQUIERDA
   ========================================================================= */
.product-show-left {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-card {
    background: #FFFFFF;
    border-radius: 12px;
    border: 1px solid #EDE9E7;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    transition: all 0.3s ease;
}

.info-card:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
}

.info-card--highlight {
    border-color: #C81E3A;
}

.info-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.7rem 1.2rem;
    border-bottom: 1px solid #EDE9E7;
    background: #FAF8F7;
}

.info-card__header h3 {
    font-size: 0.8rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    color: #171412;
}

.info-card__header h3 i {
    color: #C81E3A;
    font-size: 0.85rem;
}

.count-badge {
    font-size: 0.6rem;
    font-weight: 600;
    color: #8A8481;
    background: #F5F5F5;
    padding: 0.1rem 0.5rem;
    border-radius: 999px;
}

.info-card__body {
    padding: 1rem 1.2rem;
}

.description-text {
    font-size: 0.9rem;
    line-height: 1.8;
    color: #4B4744;
    margin: 0;
}

/* =========================================================================
   EMPTY STATE
   ========================================================================= */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.5rem 0.5rem;
    gap: 0.3rem;
    color: #B7B2AF;
}

.empty-state i {
    font-size: 1.8rem;
    color: #D1D5DB;
}

.empty-state span {
    font-size: 0.82rem;
}

/* =========================================================================
   GALERÍA DE IMÁGENES
   ========================================================================= */
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
}

@media (max-width: 600px) {
    .gallery-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.gallery-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
    background: #FAF8F7;
    border: 1px solid #EDE9E7;
    transition: all 0.3s ease;
}

.gallery-item:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.gallery-item--principal {
    border-color: #C81E3A;
    border-width: 2px;
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.gallery-item__overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 0.3rem;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.5), transparent);
    display: flex;
    justify-content: flex-start;
}

.gallery-item__badge {
    font-size: 0.4rem;
    font-weight: 700;
    padding: 0.05rem 0.4rem;
    border-radius: 4px;
    background: rgba(200, 30, 58, 0.9);
    color: #FFFFFF;
    display: inline-flex;
    align-items: center;
    gap: 0.15rem;
}

.gallery-item__badge i {
    font-size: 0.35rem;
}

.gallery-item__badge--secondary {
    background: rgba(0, 0, 0, 0.6);
}

/* =========================================================================
   ETIQUETAS
   ========================================================================= */
.tags-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.tag {
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    padding: 0.15rem 0.7rem;
    border-radius: 999px;
    font-size: 0.65rem;
    font-weight: 500;
    background: #FBEAEC;
    color: #C81E3A;
    border: 1px solid rgba(200, 30, 58, 0.1);
}

.tag i {
    font-size: 0.5rem;
}

/* =========================================================================
   VARIANTES
   ========================================================================= */
.variants-list {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.variant-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.4rem 0.7rem;
    background: #FAF8F7;
    border-radius: 6px;
    font-size: 0.78rem;
    border-left: 3px solid #C81E3A;
}

.variant-item__name {
    font-weight: 600;
    color: #171412;
}

.variant-item__values {
    color: #4B4744;
}

/* =========================================================================
   COLUMNA DERECHA - ACCIONES
   ========================================================================= */
.product-show-right {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.actions-card {
    background: #FFFFFF;
    border-radius: 12px;
    border: 1px solid #EDE9E7;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.actions-card__header {
    padding: 0.7rem 1.2rem;
    border-bottom: 1px solid #EDE9E7;
    background: #FAF8F7;
}

.actions-card__header h3 {
    font-size: 0.8rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    color: #171412;
}

.actions-card__header h3 i {
    color: #C81E3A;
    font-size: 0.85rem;
}

.actions-card__body {
    padding: 1rem 1.2rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.actions-divider {
    border-top: 1px solid #EDE9E7;
    margin: 0.2rem 0;
}

.btn-edit {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    border-radius: 8px;
    border: none;
    background: #C81E3A;
    color: #FFFFFF;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-edit:hover {
    background: #A6152D;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(200, 30, 58, 0.3);
}

.btn-edit i {
    font-size: 0.75rem;
}

.btn-delete {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    border-radius: 8px;
    border: 1.5px solid #FEE8EA;
    background: transparent;
    color: #E53E3E;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-delete:hover {
    background: #FEE8EA;
    border-color: #E53E3E;
    transform: translateY(-2px);
}

.btn-delete i {
    font-size: 0.75rem;
}

.btn-back {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    border-radius: 8px;
    border: 1.5px solid #EDE9E7;
    background: transparent;
    color: #8A8481;
    font-size: 0.78rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-back:hover {
    border-color: #B7B2AF;
    background: #FAF8F7;
    color: #4B4744;
    transform: translateY(-2px);
}

.btn-back i {
    font-size: 0.7rem;
}
</style>