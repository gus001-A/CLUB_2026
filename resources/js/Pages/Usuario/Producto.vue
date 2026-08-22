<script setup>
import { computed, reactive, ref, watch, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import CarritoResumen from '@/Components/CarritoResumen.vue';
import { useCarrito } from '@/composables/useCarrito';

/* ---------------------------------------------------------------
 * Props recibidas del controlador
 * --------------------------------------------------------------- */
const props = defineProps({
    usuario: {
        type: Object,
        default: () => ({
            id: null,
            nombre: 'Usuario',
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
    producto: {
        type: Object,
        default: () => ({
            id: null,
            nombre: 'Producto',
            descripcion: '',
            precio: 0,
            precioOriginal: null,
            descuento: 0,
            moneda: 'MXN',
            imagenes: [],
            rating: 0,
            resenas: 0,
            categoria: '',
            marca: '',
            stock: 0,
            enStock: true,
            etiqueta: null,
            tallas: [],
        })
    },
    relacionados: {
        type: Array,
        default: () => []
    },
    metricas: {
        type: Array,
        default: () => []
    }
});

/* ---------------------------------------------------------------
 * Carrito global con useCarrito
 * --------------------------------------------------------------- */
const {
    carrito,
    agregarAlCarrito: agregarGlobal,
    quitarDelCarrito: quitarGlobal,
    subtotal: subtotalGlobal,
    totalItems,
    recargar
} = useCarrito();

/* ---------------------------------------------------------------
 * Toast (alertas)
 * --------------------------------------------------------------- */
const page = usePage();

function showToast(data) {
    if (typeof data === 'string') {
        window.showToast?.({
            type: 'info',
            title: 'Notificación',
            message: data
        });
        return;
    }
    window.showToast?.(data);
}

function showSuccessToast(message, title = 'Éxito') {
    window.showSuccessToast?.(message, title);
}

function showErrorToast(message, title = 'Error') {
    window.showErrorToast?.(message, title);
}

/* ---------------------------------------------------------------
 * Producto
 * --------------------------------------------------------------- */
const producto = computed(() => props.producto);
const tallas = computed(() => producto.value.tallas || []);

const tallaSeleccionada = ref(tallas.value.length > 0 ? tallas.value[0] : '');
const cantidad = ref(1);
const imagenActiva = ref(0);

function restar() { if (cantidad.value > 1) cantidad.value--; }
function sumar() { cantidad.value++; }

/* ---------------------------------------------------------------
 * Función para agregar al carrito
 * --------------------------------------------------------------- */
function agregarAlCarrito() {
    if (!producto.value.enStock) {
        showErrorToast('Este producto no está disponible en stock.', 'Sin stock');
        return;
    }

    const productoParaCarrito = {
        id: producto.value.id,
        nombre: producto.value.nombre,
        precio: producto.value.precio,
        imagen: producto.value.imagenes?.[0] || '/images/shared/placeholder.jpg',
        cantidad: cantidad.value,
        talla: tallaSeleccionada.value,
        categoria: producto.value.categoria || '',
        marca: producto.value.marca || '',
        precioOriginal: producto.value.precioOriginal || null,
        descuento: producto.value.descuento || 0
    };

    // Usar la función global del composable
    const agregado = agregarGlobal(productoParaCarrito);

    if (agregado) {
        showSuccessToast(
            `${producto.value.nombre} (Talla: ${tallaSeleccionada.value}) x${cantidad.value} agregado al carrito.`,
            '¡Producto agregado!'
        );
    }
}

/* ---------------------------------------------------------------
 * Beneficios
 * --------------------------------------------------------------- */
const beneficiosEnvio = [
    { icon: 'pi-box', titulo: 'Envío discreto', desc: 'Empaque 100% discreto' },
    { icon: 'pi-shield', titulo: 'Pago seguro', desc: 'Transacciones protegidas' },
    { icon: 'pi-lock', titulo: 'Empaque confidencial', desc: 'Sin referencias del contenido' },
    { icon: 'pi-refresh', titulo: 'Devolución fácil', desc: 'Hasta 30 días' },
];

/* ---------------------------------------------------------------
 * Resumen de compra (sidebar) - solo para el producto actual
 * --------------------------------------------------------------- */
const envioGratisDesde = 500;
const costoEnvio = 150;

const subtotal = computed(() => producto.value.precio * cantidad.value);
const envioGratis = computed(() => subtotal.value >= envioGratisDesde);
const total = computed(() => subtotal.value + (envioGratis.value ? 0 : costoEnvio));

function formatoMoneda(valor) {
    return new Intl.NumberFormat('es-MX').format(valor);
}

function getImageUrl(imagen) {
    if (!imagen) return '/images/shared/placeholder.jpg';
    if (imagen.startsWith('http://') || imagen.startsWith('https://')) return imagen;
    if (imagen.startsWith('/storage/') || imagen.startsWith('/images/')) return imagen;
    return '/storage/' + imagen.replace(/^\/+/, '');
}

function getBadgeColor(badge) {
    const colors = {
        'Oferta': '#EF4444',
        'Nuevo': '#3B82F6',
        'Más vendido': '#F59E0B',
        'Envío gratis': '#10B981',
        'Destacado': '#8B5CF6',
        'TOP VENTAS': '#C81E3A',
        'EXCLUSIVO': '#8B5CF6',
    };
    return colors[badge] || '#6B7280';
}

function irADetalle(id) {
    router.get(`/tienda/${id}`);
}

function regresar() {
    router.get('/tienda');
}

function irACheckout() {
    router.get('/tienda/checkout');
}

defineExpose({
    carrito,
    agregarAlCarrito
});
</script>

<template>

    <Head :title="producto.nombre" />

    <AppLayout active-nav="shop" :usuario="usuario" :notificaciones="notificaciones" :favoritos="favoritos"
        :mensajes="mensajes">
        <div class="producto-page">
            <!-- Toast Notification -->
            <ToastNotification position="top-right" :duration="5000" />

            <!-- ============================================================ -->
            <!-- BOTÓN REGRESAR -->
            <!-- ============================================================ -->
            <div class="back-button-wrapper">
                <button class="btn-back" @click="regresar">
                    <span class="btn-back__icon-wrapper">
                        <i class="pi pi-arrow-left btn-back__icon"></i>
                        <i class="pi pi-arrow-left btn-back__icon btn-back__icon--ghost"></i>
                    </span>
                    <span class="btn-back__text">
                        <span class="btn-back__label">REGRESAR</span>
                        <span class="btn-back__sub">a la tienda</span>
                    </span>
                    <span class="btn-back__badge">
                        <i class="pi pi-shopping-bag"></i>
                    </span>
                </button>
            </div>

            <!-- ============================================================ -->
            <!-- CONTENIDO PRINCIPAL -->
            <!-- ============================================================ -->
            <div class="main-grid">
                <!-- ------------------------- GALERÍA ------------------------- -->
                <div class="gallery-column">
                    <div class="gallery-main">
                        <img v-if="producto.imagenes && producto.imagenes.length > 0"
                            :src="getImageUrl(producto.imagenes[imagenActiva])" :alt="producto.nombre" />
                        <img v-else src="/images/shared/placeholder.jpg" :alt="producto.nombre" />
                        <button class="gallery-zoom">
                            <i class="pi pi-window-maximize"></i>
                        </button>
                        <div class="gallery-counter" v-if="producto.imagenes && producto.imagenes.length > 1">
                            <span>{{ imagenActiva + 1 }} / {{ producto.imagenes.length }}</span>
                        </div>
                    </div>
                    <div class="gallery-thumbs" v-if="producto.imagenes && producto.imagenes.length > 1">
                        <button v-for="(img, i) in producto.imagenes" :key="img" class="gallery-thumb"
                            :class="{ active: i === imagenActiva }" @click="imagenActiva = i">
                            <img :src="getImageUrl(img)" :alt="`Vista ${i + 1}`" />
                        </button>
                    </div>
                </div>

                <!-- ------------------------- INFO PRODUCTO ------------------------- -->
                <div class="info-column">
                    <div class="product-header">
                        <span v-if="producto.etiqueta" class="badge-top"
                            :style="{ background: getBadgeColor(producto.etiqueta) }">
                            {{ producto.etiqueta }}
                        </span>
                        <span v-if="producto.descuento" class="badge-discount">
                            -{{ producto.descuento }}% <span class="badge-discount__sub">OFF</span>
                        </span>
                    </div>
                    <h1 class="product-title">{{ producto.nombre }}</h1>

                    <div class="rating-row" v-if="producto.rating > 0">
                        <span class="stars">
                            <i v-for="n in 5" :key="n" class="pi"
                                :class="n <= Math.round(producto.rating) ? 'pi-star-fill' : 'pi-star'"></i>
                        </span>
                        <span class="rating-value">{{ producto.rating }}</span>
                        <span class="rating-count">({{ producto.resenas }} reseñas)</span>
                    </div>

                    <div class="price-row">
                        <span class="price">${{ formatoMoneda(producto.precio) }} <span class="price-currency">{{
                                producto.moneda }}</span></span>
                        <span v-if="producto.precioOriginal && producto.precioOriginal > producto.precio"
                            class="price-original">${{ formatoMoneda(producto.precioOriginal) }}</span>
                        <span v-if="producto.enStock" class="stock stock--ok">
                            <span class="stock-dot"></span> En stock
                        </span>
                        <span v-else class="stock stock--out">
                            <span class="stock-dot"></span> Agotado
                        </span>
                    </div>

                    <!-- DESCRIPCIÓN DEL PRODUCTO -->
                    <div class="product-description" v-if="producto.descripcion">
                        <p>{{ producto.descripcion }}</p>
                    </div>

                    <div class="options-wrapper">
                        <div class="option-block" v-if="tallas.length > 0">
                            <span class="option-label">Selecciona tu talla</span>
                            <div class="size-options">
                                <button v-for="t in tallas" :key="t" class="size-option"
                                    :class="{ active: tallaSeleccionada === t }" @click="tallaSeleccionada = t">{{ t
                                    }}</button>
                            </div>
                        </div>

                        <div class="option-block">
                            <span class="option-label">Cantidad</span>
                            <div class="quantity-stepper">
                                <button @click="restar"><i class="pi pi-minus"></i></button>
                                <span class="quantity-value">{{ cantidad }}</span>
                                <button @click="sumar"><i class="pi pi-plus"></i></button>
                            </div>
                        </div>
                    </div>

                    <button class="btn-add-cart" @click="agregarAlCarrito">
                        <i class="pi pi-shopping-cart"></i>
                        <span>AGREGAR AL CARRITO</span>
                        <span class="btn-add-cart__price">${{ formatoMoneda(subtotal) }}</span>
                    </button>

                    <div class="product-meta">
                        <span class="meta-item" v-if="producto.categoria">
                            <i class="pi pi-tag"></i> {{ producto.categoria }}
                        </span>
                        <span class="meta-item" v-if="producto.marca">
                            <i class="pi pi-building"></i> {{ producto.marca }}
                        </span>
                    </div>
                </div>

                <!-- ------------------------- SIDEBAR ------------------------- -->
                <aside class="sidebar-column">
                    <!-- Usar CarritoResumen en lugar del summary-card manual -->
                    <CarritoResumen @ir-checkout="irACheckout" />

                    <!-- ============================================================ -->
                    <!-- BENEFICIOS DEBAJO DEL CARRITO -->
                    <!-- ============================================================ -->
                    <div class="benefits-card">
                        <div v-for="b in beneficiosEnvio" :key="b.titulo" class="benefit-item">
                            <span class="benefit-item__icon">
                                <i class="pi" :class="b.icon"></i>
                            </span>
                            <div>
                                <strong>{{ b.titulo }}</strong>
                                <span>{{ b.desc }}</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            <!-- ============================================================ -->
            <!-- TAMBIÉN TE PUEDE GUSTAR -->
            <!-- ============================================================ -->
            <section class="related-section" v-if="relacionados && relacionados.length > 0">
                <div class="related-section__header">
                    <h2>También te puede gustar</h2>
                    <span class="related-section__subtitle">Productos seleccionados para ti</span>
                </div>
                <div class="related-grid">
                    <div v-for="r in relacionados" :key="r.id" class="related-card">
                        <div class="related-card__image" @click="irADetalle(r.id)">
                            <img :src="getImageUrl(r.imagen)" :alt="r.nombre" loading="lazy" />
                            <div class="related-card__overlay">
                                <span class="related-card__quick-view">Ver producto</span>
                            </div>
                        </div>
                        <div class="related-card__body">
                            <div class="related-card__category">{{ r.categoria || 'General' }}</div>
                            <strong class="related-card__name" @click="irADetalle(r.id)">{{ r.nombre }}</strong>
                            <div class="related-card__rating">
                                <div class="stars">
                                    <i v-for="n in 5" :key="n" class="pi"
                                        :class="n <= Math.round(r.rating || 0) ? 'pi-star-fill' : 'pi-star'"
                                        :style="{ color: n <= Math.round(r.rating || 0) ? '#F59E0B' : '#D1D5DB' }">
                                    </i>
                                </div>
                                <span class="rating-count">({{ r.resenas || 0 }})</span>
                            </div>
                            <div class="related-card__price-row">
                                <span class="related-card__price">${{ formatoMoneda(r.precio) }}</span>
                                <span v-if="r.precioOriginal && r.precioOriginal > r.precio"
                                    class="related-card__price-original">
                                    ${{ formatoMoneda(r.precioOriginal) }}
                                </span>
                                <span v-if="r.descuento" class="related-card__discount">-{{ r.descuento }}%</span>
                            </div>
                            <button class="add-to-cart-btn" @click="agregarAlCarrito">
                                <i class="pi pi-plus"></i> Agregar
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA
   ========================================================================= */
:root {
    --brand: #C81E3A;
    --brand-dark: #A6152D;
    --brand-soft: #FBEAEC;
    --brand-gradient: linear-gradient(135deg, #C81E3A 0%, #E8456E 100%);
    --ink: #171412;
    --ink-soft: #4B4744;
    --muted: #8A8481;
    --muted-light: #B7B2AF;
    --line: #ECE9E7;
    --surface: #FAF8F7;
    --white: #FFFFFF;
    --shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    --shadow-hover: 0 16px 48px rgba(0, 0, 0, 0.14);
    --shadow-glow: 0 0 40px rgba(200, 30, 58, 0.15);
    --radius: 16px;
    --radius-sm: 10px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.producto-page {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: var(--ink);
    background: var(--surface);
    -webkit-font-smoothing: antialiased;
}

/* =========================================================================
   BOTÓN REGRESAR
   ========================================================================= */
.back-button-wrapper {
    max-width: 1400px;
    margin: 1.25rem auto 0;
    padding: 0 2rem;
    animation: slideDown 0.5s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6rem 1.2rem 0.6rem 0.8rem;
    border: none;
    background: var(--white);
    color: var(--ink-soft);
    border-radius: 50px;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: var(--shadow);
    position: relative;
    overflow: hidden;
    font-family: 'Inter', sans-serif;
}

.btn-back::before {
    content: '';
    position: absolute;
    inset: -2px;
    border-radius: 52px;
    padding: 2px;
    background: var(--brand-gradient);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0;
    transition: var(--transition);
}

.btn-back:hover::before {
    opacity: 1;
}

.btn-back::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50px;
    background: var(--brand-gradient);
    opacity: 0;
    transition: var(--transition);
    z-index: 0;
}

.btn-back:hover::after {
    opacity: 1;
}

.btn-back:hover {
    transform: translateX(-4px) scale(1.02);
    box-shadow: var(--shadow-hover), var(--shadow-glow);
    color: var(--white);
}

.btn-back__icon-wrapper {
    position: relative;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
    flex-shrink: 0;
}

.btn-back__icon {
    font-size: 0.9rem;
    transition: var(--transition);
    position: absolute;
    color: var(--brand);
}

.btn-back:hover .btn-back__icon {
    color: var(--white);
    transform: translateX(-4px);
}

.btn-back__icon--ghost {
    opacity: 0;
    transform: translateX(8px);
}

.btn-back:hover .btn-back__icon--ghost {
    opacity: 1;
    transform: translateX(0);
}

.btn-back:hover .btn-back__icon:not(.btn-back__icon--ghost) {
    opacity: 0;
}

.btn-back__text {
    display: flex;
    flex-direction: column;
    line-height: 1.1;
    z-index: 1;
    position: relative;
}

.btn-back__label {
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    transition: var(--transition);
}

.btn-back__sub {
    font-size: 0.6rem;
    font-weight: 500;
    color: var(--muted);
    letter-spacing: 0.04em;
    transition: var(--transition);
}

.btn-back:hover .btn-back__label {
    color: var(--white);
}

.btn-back:hover .btn-back__sub {
    color: rgba(255, 255, 255, 0.7);
}

.btn-back__badge {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.6rem;
    margin-left: 0.2rem;
    transition: var(--transition);
    z-index: 1;
    position: relative;
    flex-shrink: 0;
}

.btn-back:hover .btn-back__badge {
    background: rgba(255, 255, 255, 0.2);
    color: var(--white);
    transform: rotate(-15deg) scale(1.1);
}

/* =========================================================================
   MAIN GRID
   ========================================================================= */
.main-grid {
    max-width: 1400px;
    margin: 0.5rem auto 0;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: 1.1fr 1fr 340px;
    gap: 2rem;
    align-items: start;
}

@media (max-width: 1200px) {
    .main-grid {
        grid-template-columns: 1fr 1fr;
    }

    .sidebar-column {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }
}

@media (max-width: 800px) {
    .main-grid {
        grid-template-columns: 1fr;
    }

    .sidebar-column {
        grid-template-columns: 1fr;
    }
}

/* =========================================================================
   GALERÍA
   ========================================================================= */
.gallery-column {
    position: relative;
}

.gallery-main {
    position: relative;
    border-radius: var(--radius);
    overflow: hidden;
    aspect-ratio: 1/1;
    background: var(--white);
    box-shadow: var(--shadow);
}

.gallery-main img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.gallery-main:hover img {
    transform: scale(1.03);
}

.gallery-zoom {
    position: absolute;
    bottom: 16px;
    right: 16px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.95);
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    backdrop-filter: blur(8px);
    transition: var(--transition);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    color: var(--ink);
}

.gallery-zoom:hover {
    background: var(--brand);
    color: var(--white);
    transform: scale(1.1);
}

.gallery-counter {
    position: absolute;
    bottom: 16px;
    left: 16px;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(8px);
    color: var(--white);
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    letter-spacing: 0.02em;
}

.gallery-thumbs {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 0.6rem;
    margin-top: 0.9rem;
}

.gallery-thumb {
    border-radius: var(--radius-sm);
    overflow: hidden;
    border: 2px solid transparent;
    padding: 0;
    cursor: pointer;
    aspect-ratio: 1/1;
    transition: var(--transition);
    background: var(--white);
    box-shadow: var(--shadow);
}

.gallery-thumb:hover {
    transform: translateY(-2px);
    border-color: var(--muted-light);
}

.gallery-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.gallery-thumb.active {
    border-color: var(--brand);
    box-shadow: var(--shadow-glow);
}

/* =========================================================================
   INFO PRODUCTO
   ========================================================================= */
.info-column {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    background: var(--white);
    padding: 2rem;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.product-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.badge-top {
    color: var(--white);
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.3rem 0.9rem;
    border-radius: 20px;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    background: var(--brand-gradient);
    animation: pulse-badge 2s infinite;
}

@keyframes pulse-badge {

    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.8;
    }
}

.badge-discount {
    background: linear-gradient(135deg, #EF4444, #DC2626);
    color: var(--white);
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.25rem 0.9rem;
    border-radius: 20px;
    letter-spacing: 0.03em;
    display: flex;
    align-items: center;
    gap: 0.2rem;
}

.badge-discount__sub {
    font-size: 0.55rem;
    font-weight: 400;
    opacity: 0.8;
}

.product-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
    color: var(--ink);
    letter-spacing: -0.02em;
}

.rating-row {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.stars {
    color: #F59E0B;
    font-size: 0.85rem;
    display: flex;
    gap: 0.05rem;
}

.rating-value {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--ink);
}

.rating-count {
    font-size: 0.78rem;
    color: var(--muted);
}

.price-row {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    flex-wrap: wrap;
    padding: 0.5rem 0;
}

.price {
    font-size: 2rem;
    font-weight: 800;
    color: var(--ink);
    letter-spacing: -0.02em;
}

.price-currency {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--muted);
}

.price-original {
    font-size: 1.1rem;
    color: var(--muted-light);
    text-decoration: line-through;
}

.stock {
    font-size: 0.78rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.2rem 0.8rem;
    border-radius: 20px;
}

.stock--ok {
    color: #10B981;
    background: #ECFDF5;
}

.stock--out {
    color: var(--brand);
    background: var(--brand-soft);
}

.stock-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
    background: currentColor;
}

.product-description {
    font-size: 0.9rem;
    color: var(--ink-soft);
    line-height: 1.7;
    padding: 0.5rem 0;
    border-top: 1px solid var(--line);
    border-bottom: 1px solid var(--line);
}

.product-description p {
    margin: 0;
}

.options-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.option-block {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.option-label {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--ink-soft);
}

.size-options {
    display: flex;
    gap: 0.5rem;
}

.size-option {
    width: 44px;
    height: 40px;
    border-radius: var(--radius-sm);
    border: 2px solid var(--line);
    background: var(--white);
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    color: var(--ink-soft);
    transition: var(--transition);
}

.size-option:hover {
    border-color: var(--brand);
    color: var(--brand);
    transform: translateY(-2px);
}

.size-option.active {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
    box-shadow: var(--shadow-glow);
}

.quantity-stepper {
    display: inline-flex;
    align-items: center;
    border: 2px solid var(--line);
    border-radius: var(--radius-sm);
    overflow: hidden;
    background: var(--white);
    width: fit-content;
}

.quantity-stepper button {
    width: 40px;
    height: 40px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--ink-soft);
    transition: var(--transition);
}

.quantity-stepper button:hover {
    background: var(--brand-soft);
    color: var(--brand);
}

.quantity-value {
    width: 44px;
    text-align: center;
    font-weight: 700;
    font-size: 1rem;
    color: var(--ink);
}

.btn-add-cart {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
    padding: 1rem 1.5rem;
    border: none;
    border-radius: var(--radius-sm);
    background: var(--brand-gradient);
    color: var(--white);
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: 0 4px 20px rgba(200, 30, 58, 0.3);
    margin-top: 0.5rem;
}

.btn-add-cart:hover {
    transform: translateY(-2px) scale(1.01);
    box-shadow: 0 8px 32px rgba(200, 30, 58, 0.4);
}

.btn-add-cart__price {
    font-size: 0.8rem;
    font-weight: 600;
    opacity: 0.9;
    margin-left: auto;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.2rem 0.8rem;
    border-radius: 20px;
}

.product-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    padding-top: 0.5rem;
    border-top: 1px solid var(--line);
}

.meta-item {
    font-size: 0.78rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.meta-item i {
    font-size: 0.7rem;
}

/* =========================================================================
   BENEFICIOS
   ========================================================================= */
.benefits-card {
    background: var(--white);
    border-radius: var(--radius);
    padding: 1.25rem;
    box-shadow: var(--shadow);
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.benefit-item {
    display: flex;
    gap: 0.8rem;
    align-items: center;
    padding: 0.5rem 0.8rem;
    border-radius: var(--radius-sm);
    transition: var(--transition);
}

.benefit-item:hover {
    background: var(--surface);
}

.benefit-item__icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--brand-gradient);
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.85rem;
    box-shadow: 0 4px 12px rgba(200, 30, 58, 0.2);
}

.benefit-item strong {
    display: block;
    font-size: 0.82rem;
    margin-bottom: 0.05rem;
}

.benefit-item span {
    font-size: 0.7rem;
    color: var(--muted);
}

/* =========================================================================
   RELACIONADOS
   ========================================================================= */
.related-section {
    max-width: 1400px;
    margin: 3rem auto 3rem;
    padding: 0 2rem;
}

.related-section__header {
    margin-bottom: 1.5rem;
}

.related-section__header h2 {
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0 0 0.2rem;
    color: var(--ink);
}

.related-section__subtitle {
    font-size: 0.85rem;
    color: var(--muted);
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
}

@media (max-width: 900px) {
    .related-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .related-grid {
        grid-template-columns: 1fr;
    }
}

.related-card {
    background: var(--white);
    border-radius: var(--radius);
    overflow: hidden;
    transition: var(--transition);
    box-shadow: var(--shadow);
}

.related-card:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-6px);
}

.related-card__image {
    position: relative;
    aspect-ratio: 1/1;
    background: var(--surface);
    cursor: pointer;
    overflow: hidden;
}

.related-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.related-card:hover .related-card__image img {
    transform: scale(1.06);
}

.related-card__overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(4px);
    opacity: 0;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
}

.related-card:hover .related-card__overlay {
    opacity: 1;
}

.related-card__quick-view {
    color: var(--white);
    font-size: 0.78rem;
    font-weight: 600;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.4rem 1.2rem;
    border-radius: 20px;
    backdrop-filter: blur(8px);
}

.related-card__body {
    padding: 1rem 1.1rem 1.1rem;
}

.related-card__category {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--muted-light);
    margin-bottom: 0.2rem;
}

.related-card__name {
    display: block;
    font-size: 0.85rem;
    line-height: 1.35;
    margin-bottom: 0.4rem;
    min-height: 2.2em;
    color: var(--ink);
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
}

.related-card__name:hover {
    color: var(--brand);
}

.related-card__rating {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-bottom: 0.6rem;
}

.related-card__rating .stars {
    display: flex;
    gap: 0.05rem;
}

.related-card__rating .stars .pi {
    font-size: 0.7rem;
}

.rating-count {
    font-size: 0.72rem;
    color: var(--muted);
}

.related-card__price-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.6rem;
}

.related-card__price {
    font-size: 1rem;
    font-weight: 800;
    color: var(--ink);
}

.related-card__price-original {
    font-size: 0.78rem;
    color: var(--muted-light);
    text-decoration: line-through;
}

.related-card__discount {
    font-size: 0.7rem;
    font-weight: 700;
    color: #EF4444;
    background: #FEE2E2;
    padding: 0.1rem 0.5rem;
    border-radius: 20px;
}

.add-to-cart-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    border: 2px solid var(--brand);
    border-radius: var(--radius-sm);
    background: transparent;
    color: var(--brand);
    padding: 0.5rem 0.8rem;
    font-size: 0.74rem;
    font-weight: 700;
    cursor: pointer;
    transition: var(--transition);
}

.add-to-cart-btn:hover {
    background: var(--brand-gradient);
    color: var(--white);
    border-color: transparent;
    transform: scale(1.02);
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
    .back-button-wrapper {
        padding: 0 1rem;
    }

    .main-grid {
        padding: 0 1rem;
    }

    .related-section {
        padding: 0 1rem;
    }
}

@media (max-width: 768px) {
    .info-column {
        padding: 1.5rem;
    }

    .product-title {
        font-size: 1.4rem;
    }

    .price {
        font-size: 1.6rem;
    }

    .gallery-thumbs {
        grid-template-columns: repeat(3, 1fr);
    }

    .btn-back {
        padding: 0.5rem 1rem 0.5rem 0.6rem;
    }

    .btn-back__label {
        font-size: 0.65rem;
    }

    .btn-back__sub {
        font-size: 0.55rem;
    }

    .btn-back__badge {
        width: 20px;
        height: 20px;
        font-size: 0.5rem;
    }

    .btn-back__icon-wrapper {
        width: 28px;
        height: 28px;
    }

    .btn-back__icon {
        font-size: 0.75rem;
    }
}

@media (max-width: 480px) {
    .info-column {
        padding: 1rem;
    }

    .product-title {
        font-size: 1.2rem;
    }

    .price {
        font-size: 1.4rem;
    }

    .btn-back {
        font-size: 0.7rem;
        padding: 0.4rem 0.8rem 0.4rem 0.5rem;
    }

    .btn-back__label {
        font-size: 0.6rem;
    }

    .btn-back__sub {
        display: none;
    }

    .btn-back__badge {
        display: none;
    }

    .btn-back__icon-wrapper {
        width: 24px;
        height: 24px;
    }

    .btn-back__icon {
        font-size: 0.65rem;
    }
}
</style>