<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

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
    productos: {
        type: Array,
        default: () => []
    },
    recomendados: {
        type: Array,
        default: () => []
    },
    colecciones: {
        type: Array,
        default: () => []
    },
    categorias: {
        type: Array,
        default: () => []
    },
    marcas: {
        type: Array,
        default: () => []
    },
    totalProductos: {
        type: Number,
        default: 0
    },
    carritoInicial: {
        type: Array,
        default: () => []
    },
    metricas: {
        type: Array,
        default: () => []
    }
});

/* ---------------------------------------------------------------
 * Confianza (estático)
 * --------------------------------------------------------------- */
const confianza = [
    { icon: 'pi-lock', titulo: 'Compra discreta', desc: 'Tu privacidad es nuestra prioridad en cada paso del proceso.' },
    { icon: 'pi-heart', titulo: 'Productos seleccionados', desc: 'Solo marcas y artículos premium curados para tu bienestar.' },
    { icon: 'pi-check-circle', titulo: 'Pagos seguros', desc: 'Transacciones protegidas con encriptación de última generación.' },
    { icon: 'pi-box', titulo: 'Envío confidencial', desc: 'Empaque discreto y envíos 100% confidenciales.' },
];

/* ---------------------------------------------------------------
 * Métricas - DINÁMICAS desde el controlador
 * --------------------------------------------------------------- */
const metricasData = computed(() => {
    if (props.metricas && props.metricas.length > 0) {
        return props.metricas;
    }
    return [
        { 
            icon: 'pi-shopping-bag', 
            titulo: 'Productos', 
            desc: 'Selección curada de productos premium.', 
            valor: props.totalProductos || '0', 
            etiqueta: 'disponibles' 
        },
        { 
            icon: 'pi-star', 
            titulo: 'Calificación', 
            desc: 'Productos con las mejores reseñas de la comunidad.', 
            valor: '4.8', 
            etiqueta: '⭐ promedio' 
        },
        { 
            icon: 'pi-truck', 
            titulo: 'Envíos', 
            desc: 'Envíos rápidos y discretos a todo el país.', 
            valor: '24h', 
            etiqueta: 'entrega' 
        },
    ];
});

/* ---------------------------------------------------------------
 * MAPA DE ICONOS - USANDO LOS QUE YA FUNCIONAN EN PrivacyPolicy
 * pi-home, pi-info-circle, pi-briefcase, pi-calendar, pi-envelope
 * pi-shield, pi-building, pi-database, pi-bullseye, pi-share-alt
 * pi-cookie, pi-refresh, pi-user, pi-lock, pi-image, pi-users
 * pi-gavel, pi-eye, pi-pencil, pi-trash, pi-times, pi-cog
 * --------------------------------------------------------------- */
const iconMap = {
    'lenceria': 'pi-heart',
    'lencería': 'pi-heart',
    'juguetes sexuales': 'pi-circle-fill',
    'juguetes': 'pi-circle-fill',
    'aceites y masajes': 'pi-droplet',
    'aceites': 'pi-droplet',
    'masajes': 'pi-droplet',
    'juegos para parejas': 'pi-users',
    'juegos': 'pi-users',
    'accesorios': 'pi-cog',
    'bienestar intimo': 'pi-heart-fill',
    'bienestar': 'pi-heart-fill',
    'ropa interior': 'pi-user',
    'ropa': 'pi-user',
    'vibradores': 'pi-circle-fill',
    'consoladores': 'pi-circle-fill',
    'anillos': 'pi-circle',
    'lubricantes': 'pi-droplet',
    'velas': 'pi-star',
    'aromaterapia': 'pi-star',
    'libros': 'pi-book',
    'juegos de cartas': 'pi-book',
    'kits': 'pi-box',
    'regalos': 'pi-gift',
};

/* ---------------------------------------------------------------
 * Filtros - DINÁMICOS
 * --------------------------------------------------------------- */
const busqueda = ref('');
const categoriaActiva = ref(null);
const rangoPrecio = reactive({ min: 200, max: 5000 });
const soloDestacados = ref(false);
const ordenarPor = ref('relevantes');

const opcionesOrden = [
    { label: 'Más relevantes', value: 'relevantes' },
    { label: 'Precio: menor a mayor', value: 'precio_asc' },
    { label: 'Precio: mayor a menor', value: 'precio_desc' },
    { label: 'Mejor calificados', value: 'calificacion' },
];

const categoriasData = computed(() => {
    if (props.categorias && props.categorias.length > 0) {
        return props.categorias.map(cat => {
            if (cat.icon) return cat;
            const lowerLabel = cat.label.toLowerCase().trim();
            const icon = iconMap[lowerLabel] || 'pi-tag';
            return { ...cat, icon };
        });
    }
    return [];
});

const productosLista = computed(() => props.productos || []);
const coleccionesData = computed(() => props.colecciones || []);
const recomendadosData = computed(() => props.recomendados || []);
const totalProductosData = computed(() => props.totalProductos || productosLista.value.length);

/* ---------------------------------------------------------------
 * Carrito - DINÁMICO
 * --------------------------------------------------------------- */
const carrito = reactive(props.carritoInicial.length > 0 
    ? props.carritoInicial.map(item => ({ ...item }))
    : []
);

const envioGratisDesde = 1000;
const subtotal = computed(() => carrito.reduce((acc, item) => acc + item.precio * item.cantidad, 0));
const envioGratis = computed(() => subtotal.value >= envioGratisDesde);
const total = computed(() => subtotal.value);

function quitarDelCarrito(id) {
    const idx = carrito.findIndex((i) => i.id === id);
    if (idx !== -1) carrito.splice(idx, 1);
}

function agregarAlCarrito(producto) {
    const existente = carrito.find((i) => i.id === producto.id);
    if (existente) {
        existente.cantidad++;
    } else {
        carrito.push({ 
            id: producto.id, 
            nombre: producto.nombre, 
            imagen: producto.imagen, 
            cantidad: 1, 
            precio: producto.precio 
        });
    }
}

/* ---------------------------------------------------------------
 * Filtrado
 * --------------------------------------------------------------- */
const productosFiltrados = computed(() => {
    let resultado = [...productosLista.value];

    if (busqueda.value.trim()) {
        const search = busqueda.value.toLowerCase().trim();
        resultado = resultado.filter(p => 
            p.nombre.toLowerCase().includes(search) ||
            (p.descripcion && p.descripcion.toLowerCase().includes(search)) ||
            (p.categoria && p.categoria.toLowerCase().includes(search))
        );
    }

    if (categoriaActiva.value) {
        resultado = resultado.filter(p => p.categoria === categoriaActiva.value);
    }

    resultado = resultado.filter(p => p.precio >= rangoPrecio.min && p.precio <= rangoPrecio.max);

    if (soloDestacados.value) {
        resultado = resultado.filter(p => p.badge && p.badge !== '');
    }

    switch (ordenarPor.value) {
        case 'precio_asc':
            resultado.sort((a, b) => a.precio - b.precio);
            break;
        case 'precio_desc':
            resultado.sort((a, b) => b.precio - a.precio);
            break;
        case 'calificacion':
            resultado.sort((a, b) => (b.rating || 0) - (a.rating || 0));
            break;
        default:
            break;
    }

    return resultado;
});

/* ---------------------------------------------------------------
 * Utilidades
 * --------------------------------------------------------------- */
function formatoMoneda(valor) {
    return new Intl.NumberFormat('es-MX').format(valor);
}

function finalizarCompra() {
    alert('Redirigiendo al checkout...');
}

function getImageUrl(imagen) {
    if (!imagen) return '/images/shared/placeholder.jpg';
    if (imagen.startsWith('http://') || imagen.startsWith('https://')) return imagen;
    if (imagen.startsWith('/storage/') || imagen.startsWith('/images/')) return imagen;
    return '/storage/' + imagen.replace(/^\/+/, '');
}

function limpiarFiltros() {
    busqueda.value = '';
    categoriaActiva.value = null;
    rangoPrecio.min = 200;
    rangoPrecio.max = 5000;
    soloDestacados.value = false;
    ordenarPor.value = 'relevantes';
}
</script>

<template>
    <Head title="Shop | Club de Fantasías" />

    <AppLayout
        active-nav="shop"
        :usuario="usuario"
        :notificaciones="notificaciones"
        :favoritos="favoritos"
        :mensajes="mensajes"
    >
        <div class="shop-page">
            <!-- ============================================================ -->
            <!-- HERO - MISMO ESTILO QUE COMUNIDAD -->
            <!-- ============================================================ -->
            <section class="hero">
                <div class="hero__grid">
                    <div class="hero__copy">
                        <p class="hero__eyebrow">
                            Bienvenido al shop, <strong>{{ usuario.nombre }}</strong>
                            <span v-if="usuario.verificado" class="hero__verified">
                                <i class="pi pi-check-circle"></i> Verificado
                            </span>
                        </p>
                        <h1 class="hero__title">
                            <span class="hero__title-highlight">Explora</span> tu shop íntimo<br />
                            y descubre <span class="hero__title-highlight">productos premium</span>
                        </h1>
                        <p class="hero__text">
                            Descubre una selección curada de productos premium para parejas y adultos. 
                            Discreción, calidad y placer en cada detalle.
                        </p>
                    </div>

                    <div class="hero__media">
                        <img src="/images/tienda.png" alt="Shop Club de Fantasías" class="hero__img" />
                        <div class="hero__fade"></div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- QUICK STATS - MÉTRICAS -->
            <!-- ============================================================ -->
            <section class="quick-stats">
                <div v-for="m in metricasData" :key="m.titulo" class="stat-card">
                    <span class="stat-card__icon"><i class="pi" :class="m.icon"></i></span>
                    <div class="stat-card__body">
                        <span class="stat-card__title">{{ m.titulo }}</span>
                        <span class="stat-card__desc">{{ m.desc }}</span>
                    </div>
                    <div class="stat-card__value">
                        <span class="value">{{ m.valor }}</span>
                        <span class="label">{{ m.etiqueta }}</span>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- CONFIANZA -->
            <!-- ============================================================ -->
            <section class="trust-row">
                <div v-for="c in confianza" :key="c.titulo" class="trust-item">
                    <span class="trust-item__icon"><i class="pi" :class="c.icon"></i></span>
                    <div>
                        <strong>{{ c.titulo }}</strong>
                        <span>{{ c.desc }}</span>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- CONTENIDO PRINCIPAL -->
            <!-- ============================================================ -->
            <div class="content-grid">
                <!-- ------------------------- FILTROS ------------------------- -->
                <aside class="filters-column">
                    <div class="filter-block">
                        <h3>Buscar productos</h3>
                        <span class="search-input">
                            <input v-model="busqueda" type="text" placeholder="Buscar en el shop..." />
                            <i class="pi pi-search"></i>
                        </span>
                    </div>

                    <div class="filter-block">
                        <h3>Categorías</h3>
                        <button
                            v-for="c in categoriasData"
                            :key="c.label"
                            class="category-row"
                            :class="{ active: categoriaActiva === c.label }"
                            @click="categoriaActiva = categoriaActiva === c.label ? null : c.label"
                        >
                            <i class="pi" :class="c.icon || 'pi-tag'"></i>
                            <span>{{ c.label }}</span>
                            <i class="pi pi-chevron-right chevron"></i>
                        </button>
                        <p v-if="categoriasData.length === 0" class="empty-filter">No hay categorías disponibles</p>
                    </div>

                    <div class="filter-block">
                        <h3>Rango de precio</h3>
                        <input type="range" :min="200" :max="5000" v-model="rangoPrecio.max" class="price-slider" />
                        <div class="price-labels">
                            <span>${{ formatoMoneda(rangoPrecio.min) }} MXN</span>
                            <span>${{ formatoMoneda(rangoPrecio.max) }} MXN+</span>
                        </div>
                    </div>

                    <div class="filter-block">
                        <h3>Solo destacados</h3>
                        <label class="toggle-row">
                            <span>Ver solo productos destacados</span>
                            <span class="toggle-switch">
                                <input type="checkbox" v-model="soloDestacados" />
                                <span class="toggle-slider"></span>
                            </span>
                        </label>
                    </div>

                    <button class="clear-filters-btn" @click="limpiarFiltros">
                        <i class="pi pi-filter-slash"></i> Limpiar filtros
                    </button>
                </aside>

                <!-- ------------------------- PRODUCTOS ------------------------- -->
                <div class="products-column">
                    <div class="products-header">
                        <span>Mostrando {{ productosFiltrados.length }} de {{ totalProductosData }} productos</span>
                        <label class="sort-select">
                            <select v-model="ordenarPor">
                                <option v-for="op in opcionesOrden" :key="op.value" :value="op.value">
                                    {{ op.label }}
                                </option>
                            </select>
                        </label>
                    </div>

                    <div v-if="productosFiltrados.length === 0" class="empty-state">
                        <i class="pi pi-inbox" style="font-size: 2.5rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <h3>No hay productos</h3>
                        <p>{{ busqueda ? 'No se encontraron productos con esa búsqueda.' : 'No hay productos disponibles en este momento.' }}</p>
                        <button class="clear-filters-btn" @click="limpiarFiltros" v-if="busqueda || categoriaActiva || soloDestacados">
                            <i class="pi pi-filter-slash"></i> Limpiar filtros
                        </button>
                    </div>

                    <div class="product-grid">
                        <div v-for="p in productosFiltrados" :key="p.id" class="product-card">
                            <div class="product-card__image">
                                <span v-if="p.badge" class="product-card__badge">{{ p.badge }}</span>
                                <img :src="getImageUrl(p.imagen)" :alt="p.nombre" loading="lazy" />
                            </div>
                            <div class="product-card__body">
                                <strong>{{ p.nombre }}</strong>
                                <div class="product-card__rating">
                                    <i class="pi pi-star-fill"></i> 
                                    {{ p.rating || 0 }} ({{ p.resenas || 0 }})
                                </div>
                                <span class="product-card__price">${{ formatoMoneda(p.precio) }} MXN</span>
                                <button class="add-to-cart-btn" @click="agregarAlCarrito(p)">
                                    <i class="pi pi-shopping-cart"></i> Agregar al carrito
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Recomendados para ti -->
                    <section v-if="recomendadosData.length > 0" class="recommended-section">
                        <div class="recommended-section__header">
                            <div>
                                <h2>Recomendados para ti</h2>
                                <span>Basado en tus intereses y compras anteriores</span>
                            </div>
                            <a href="#" class="see-all">Ver más <i class="pi pi-chevron-right"></i></a>
                        </div>
                        <div class="recommended-track">
                            <div v-for="r in recomendadosData" :key="r.id" class="recommended-card">
                                <img :src="getImageUrl(r.imagen)" :alt="r.nombre" loading="lazy" />
                                <div>
                                    <strong>{{ r.nombre }}</strong>
                                    <span>${{ formatoMoneda(r.precio) }} MXN</span>
                                </div>
                                <button class="recommended-card__add" @click="agregarAlCarrito({ ...r, rating: 0, resenas: 0 })">
                                    <i class="pi pi-plus"></i>
                                </button>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- ------------------------- CARRITO ------------------------- -->
                <aside class="cart-column">
                    <div class="cart-card">
                        <div class="cart-card__header">
                            <h3>Tu carrito ({{ carrito.length }})</h3>
                            <i class="pi pi-chevron-up"></i>
                        </div>

                        <div class="cart-items">
                            <div v-for="item in carrito" :key="item.id" class="cart-item">
                                <img :src="getImageUrl(item.imagen)" :alt="item.nombre" />
                                <div class="cart-item__info">
                                    <strong>{{ item.nombre }}</strong>
                                    <span>{{ item.cantidad }} &times; ${{ formatoMoneda(item.precio) }} MXN</span>
                                </div>
                                <button class="cart-item__remove" @click="quitarDelCarrito(item.id)">
                                    <i class="pi pi-times"></i>
                                </button>
                            </div>
                            <p v-if="carrito.length === 0" class="cart-empty">Tu carrito está vacío.</p>
                        </div>

                        <div class="cart-summary">
                            <div class="cart-summary__row">
                                <span>Subtotal</span>
                                <strong>${{ formatoMoneda(subtotal) }} MXN</strong>
                            </div>
                            <div class="cart-summary__row">
                                <span>Envío</span>
                                <strong :class="{ 'text-success': envioGratis }">
                                    {{ envioGratis ? 'Gratis' : 'Por calcular' }}
                                </strong>
                            </div>
                            <p class="cart-summary__note">
                                Envío gratuito en compras a partir de ${{ formatoMoneda(envioGratisDesde) }} MXN
                            </p>
                        </div>

                        <div class="cart-total">
                            <span>Total</span>
                            <strong>${{ formatoMoneda(total) }} MXN</strong>
                        </div>

                        <PvButton 
                            label="FINALIZAR COMPRA" 
                            icon="pi pi-lock" 
                            class="checkout-btn" 
                            @click="finalizarCompra" 
                            :disabled="carrito.length === 0"
                        />

                        <div class="payment-icons">
                            <i class="pi pi-credit-card"></i>
                            <span>VISA</span>
                            <span>mastercard</span>
                            <span>AMEX</span>
                            <span>PayPal</span>
                        </div>
                        <p class="payment-note"><i class="pi pi-lock"></i> Pagos 100% seguros</p>
                    </div>

                    <div class="privacy-note-card">
                        <span class="privacy-note-card__icon"><i class="pi pi-heart"></i></span>
                        <div>
                            <strong>Tu privacidad, siempre</strong>
                            <p>Empaque discreto sin logos ni referencias al contenido. Nadie sabrá qué hay dentro.</p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA - MISMO QUE COMUNIDAD
   ========================================================================= */
.shop-page {
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

  --font-serif: 'Fraunces', Georgia, serif;
  --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;
  --radius-sm: 10px;
  --radius-md: 16px;
  --radius-lg: 24px;
  --radius-full: 999px;

  font-family: var(--font-sans);
  color: var(--ink);
  background: var(--white);
  -webkit-font-smoothing: antialiased;
}

.shop-page * {
  box-sizing: border-box;
}

/* =========================================================================
   HERO - MISMO ESTILO QUE COMUNIDAD
   ========================================================================= */
.hero {
  position: relative;
  overflow: hidden;
  max-width: 1400px;
  margin: 1.5rem auto 0;
  padding: 0 2rem;
}

.hero__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 420px;
  background: var(--ink);
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
}

.hero__copy {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 3rem 3rem;
  color: #ffffff;
}

.hero__eyebrow { 
  font-size: 0.78rem; 
  color: rgba(255, 255, 255, 0.6); 
  margin: 0 0 0.75rem; 
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.hero__eyebrow strong { 
  color: var(--brand); 
}

.hero__verified {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  background: rgba(31, 191, 92, 0.2);
  color: #48BB78;
  padding: 0.15rem 0.6rem;
  border-radius: var(--radius-full);
  font-size: 0.65rem;
  font-weight: 600;
}

.hero__title {
  font-family: var(--font-serif);
  font-size: 2.6rem;
  font-weight: 500;
  line-height: 1.1;
  letter-spacing: -0.01em;
  margin: 0;
}

.hero__title-highlight {
  color: var(--brand);
  font-style: italic;
}

.hero__text {
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.7;
  max-width: 480px;
  margin: 1.25rem 0 0;
  font-size: 0.95rem;
}

.hero__media {
  position: relative;
  min-height: 340px;
  overflow: hidden;
  background: var(--ink);
  display: flex;
  align-items: center;
  justify-content: center;
}

.hero__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s ease;
}

.hero:hover .hero__img {
  transform: scale(1.05);
}

.hero__fade {
  position: absolute;
  inset: 0;
  width: 33%;
  background: linear-gradient(to right, var(--ink), rgba(23, 20, 18, 0.05));
}

/* =========================================================================
   QUICK STATS - MISMO QUE COMUNIDAD
   ========================================================================= */
.quick-stats {
    max-width: 1400px;
    margin: 1.5rem auto 0;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}

.stat-card {
    background: #ffffff; 
    border-radius: var(--radius-md);
    padding: 1rem 1.25rem; 
    display: flex; 
    align-items: center; 
    gap: 0.85rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: default;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    border: 1px solid var(--line);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
}

.stat-card__icon {
    width: 38px; 
    height: 38px; 
    border-radius: var(--radius-sm); 
    background: var(--brand-soft); 
    color: var(--brand);
    display: flex; 
    align-items: center; 
    justify-content: center; 
    flex-shrink: 0; 
    font-size: 1rem;
    transition: all 0.3s ease;
}

.stat-card:hover .stat-card__icon {
    background: var(--brand);
    color: var(--white);
    transform: scale(1.05);
}

.stat-card__body { 
    display: flex; 
    flex-direction: column; 
    gap: 0.2rem; 
    flex: 1; 
}

.stat-card__title { 
    font-weight: 700; 
    font-size: 0.85rem; 
    display: flex; 
    align-items: center; 
    gap: 0.4rem; 
}

.stat-card__desc { 
    font-size: 0.72rem; 
    color: var(--muted); 
    line-height: 1.3;
}

.stat-card__value {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    flex-shrink: 0;
}

.stat-card__value .value {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--brand);
}

.stat-card__value .label {
    font-size: 0.6rem;
    color: var(--muted-light);
}

/* =========================================================================
   TRUST ROW
   ========================================================================= */
.trust-row { 
    max-width: 1400px; 
    margin: 1.5rem auto 0; 
    padding: 0 2rem; 
    display: grid; 
    grid-template-columns: repeat(4, 1fr); 
    gap: 1.25rem; 
}
@media (max-width: 900px) { 
    .trust-row { grid-template-columns: 1fr 1fr; } 
}
@media (max-width: 480px) { 
    .trust-row { grid-template-columns: 1fr; } 
}
.trust-item { 
    background: #fff; 
    border: 1px solid #ececee; 
    border-radius: 12px; 
    padding: 1.1rem; 
    display: flex; 
    gap: 0.75rem; 
    align-items: flex-start; 
}
.trust-item__icon { 
    width: 34px; 
    height: 34px; 
    border-radius: 8px; 
    background: #fdf1f2; 
    color: var(--brand-red); 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    flex-shrink: 0; 
}
.trust-item strong { 
    display: block; 
    font-size: 0.85rem; 
}
.trust-item span { 
    font-size: 0.75rem; 
    color: #8a8a90; 
}

/* =========================================================================
   CONTENT GRID
   ========================================================================= */
.content-grid { 
    max-width: 1400px; 
    margin: 1.5rem auto 0; 
    padding: 0 2rem 2.5rem; 
    display: grid; 
    grid-template-columns: 240px minmax(0, 1fr) 320px; 
    gap: 1.5rem; 
    align-items: start; 
}
@media (max-width: 1300px) { 
    .content-grid { grid-template-columns: 220px minmax(0, 1fr); } 
    .cart-column { grid-column: 1 / -1; } 
}
@media (max-width: 800px) { 
    .content-grid { grid-template-columns: 1fr; } 
}

/* =========================================================================
   FILTERS
   ========================================================================= */
.filters-column { 
    display: flex; 
    flex-direction: column; 
    gap: 1.5rem; 
}
.filter-block { 
    background: #fff; 
    border: 1px solid #ececee; 
    border-radius: 12px; 
    padding: 1.25rem; 
}
.filter-block h3 { 
    font-size: 0.85rem; 
    margin: 0 0 1rem; 
}
.search-input { 
    position: relative; 
    display: block; 
}
.search-input input { 
    width: 100%; 
    border: 1px solid #e3e3e7; 
    border-radius: 8px; 
    padding: 0.55rem 2rem 0.55rem 0.8rem; 
    font-size: 0.82rem; 
    outline: none; 
}
.search-input i { 
    position: absolute; 
    right: 0.7rem; 
    top: 50%; 
    transform: translateY(-50%); 
    color: #a5a5aa; 
    font-size: 0.8rem; 
}

.category-row {
    width: 100%; 
    display: flex; 
    align-items: center; 
    gap: 0.6rem; 
    border: none; 
    background: none;
    padding: 0.55rem 0; 
    font-size: 0.82rem; 
    color: #2a2a2e; 
    cursor: pointer; 
    text-align: left;
}
.category-row i:first-child { 
    color: #a5a5aa; 
    width: 16px; 
}
.category-row.active { 
    color: var(--brand-red); 
    font-weight: 700; 
}
.category-row.active i:first-child { 
    color: var(--brand-red); 
}
.category-row span { 
    flex: 1; 
}
.category-row .chevron { 
    font-size: 0.65rem; 
    color: #c4c4c8; 
}

.price-slider { 
    width: 100%; 
    accent-color: var(--brand-red); 
    margin-bottom: 0.6rem; 
}
.price-labels { 
    display: flex; 
    justify-content: space-between; 
    font-size: 0.72rem; 
    color: #8a8a90; 
}

.toggle-row { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    gap: 1rem; 
}
.toggle-row span { 
    font-size: 0.8rem; 
    color: #55555a; 
}
.toggle-switch { 
    position: relative; 
    width: 38px; 
    height: 21px; 
    flex-shrink: 0; 
}
.toggle-switch input { 
    opacity: 0; 
    width: 0; 
    height: 0; 
}
.toggle-slider { 
    position: absolute; 
    inset: 0; 
    background: #e3e3e7; 
    border-radius: 999px; 
    transition: 0.2s; 
    cursor: pointer; 
}
.toggle-switch input:checked + .toggle-slider { 
    background: var(--brand-red); 
}
.toggle-slider::before { 
    content: ''; 
    position: absolute; 
    width: 15px; 
    height: 15px; 
    left: 3px; 
    top: 3px; 
    background: #fff; 
    border-radius: 50%; 
    transition: 0.2s; 
}
.toggle-switch input:checked + .toggle-slider::before { 
    transform: translateX(17px); 
}

.empty-filter {
    color: var(--muted-light);
    font-size: 0.78rem;
    margin: 0.5rem 0 0;
    text-align: center;
}

.clear-filters-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    border: 1px solid var(--line);
    border-radius: 8px;
    background: #fff;
    color: var(--muted);
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    width: 100%;
}

.clear-filters-btn:hover {
    border-color: var(--brand-red);
    color: var(--brand-red);
    background: var(--brand-soft);
}

/* =========================================================================
   PRODUCTS
   ========================================================================= */
.products-column { 
    display: flex; 
    flex-direction: column; 
    gap: 1.5rem; 
}
.products-header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    font-size: 0.82rem; 
    color: #55555a; 
}
.sort-select select { 
    border: 1px solid #e3e3e7; 
    border-radius: 8px; 
    padding: 0.5rem 0.8rem; 
    font-size: 0.82rem; 
    color: #1f2024; 
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    background: #fff;
    border-radius: 12px;
    border: 1px solid var(--line);
}

.empty-state h3 {
    margin: 0 0 0.5rem;
    font-size: 1.1rem;
    color: var(--ink-soft);
}

.empty-state p {
    margin: 0 0 1rem;
    color: var(--muted);
    font-size: 0.85rem;
}

.product-grid { 
    display: grid; 
    grid-template-columns: repeat(4, 1fr); 
    gap: 1.1rem; 
}
@media (max-width: 1300px) { 
    .product-grid { grid-template-columns: repeat(3, 1fr); } 
}
@media (max-width: 700px) { 
    .product-grid { grid-template-columns: 1fr 1fr; } 
}
@media (max-width: 480px) { 
    .product-grid { grid-template-columns: 1fr; } 
}
.product-card { 
    background: #fff; 
    border: 1px solid #ececee; 
    border-radius: 12px; 
    overflow: hidden; 
}
.product-card__image { 
    position: relative; 
    aspect-ratio: 1/1; 
}
.product-card__image img { 
    width: 100%; 
    height: 100%; 
    object-fit: cover; 
}
.product-card__badge { 
    position: absolute; 
    top: 0.6rem; 
    left: 0.6rem; 
    background: var(--brand-red); 
    color: #fff; 
    font-size: 0.6rem; 
    font-weight: 700; 
    padding: 0.2rem 0.5rem; 
    border-radius: 4px; 
    letter-spacing: 0.02em; 
}
.product-card__body { 
    padding: 0.9rem; 
}
.product-card__body strong { 
    display: block; 
    font-size: 0.8rem; 
    line-height: 1.35; 
    margin-bottom: 0.45rem; 
    min-height: 2.1em; 
}
.product-card__rating { 
    font-size: 0.72rem; 
    color: #55555a; 
    display: flex; 
    align-items: center; 
    gap: 0.3rem; 
    margin-bottom: 0.5rem; 
}
.product-card__rating i { 
    color: #f2a33c; 
}
.product-card__price { 
    display: block; 
    font-size: 0.9rem; 
    font-weight: 800; 
    margin-bottom: 0.7rem; 
}
.add-to-cart-btn {
    width: 100%; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    gap: 0.4rem;
    border: 1.5px solid var(--brand-red); 
    border-radius: 8px; 
    background: #fff; 
    color: var(--brand-red);
    padding: 0.55rem; 
    font-size: 0.74rem; 
    font-weight: 700; 
    cursor: pointer;
}
.add-to-cart-btn:hover { 
    background: #fdf1f2; 
}

/* =========================================================================
   RECOMMENDED
   ========================================================================= */
.recommended-section__header { 
    display: flex; 
    justify-content: space-between; 
    align-items: flex-end; 
    margin-bottom: 1.1rem; 
}
.recommended-section__header h2 { 
    font-size: 1.1rem; 
    margin: 0 0 0.2rem; 
}
.recommended-section__header span { 
    font-size: 0.78rem; 
    color: #8a8a90; 
}
.see-all { 
    color: var(--brand-red); 
    font-size: 0.8rem; 
    font-weight: 700; 
    text-decoration: none; 
    display: flex; 
    align-items: center; 
    gap: 0.3rem; 
}
.recommended-track { 
    display: grid; 
    grid-template-columns: repeat(5, 1fr); 
    gap: 1rem; 
}
@media (max-width: 1300px) { 
    .recommended-track { grid-template-columns: repeat(3, 1fr); } 
}
@media (max-width: 700px) { 
    .recommended-track { grid-template-columns: 1fr 1fr; } 
}
@media (max-width: 480px) { 
    .recommended-track { grid-template-columns: 1fr; } 
}
.recommended-card { 
    background: #fff; 
    border: 1px solid #ececee; 
    border-radius: 10px; 
    padding: 0.75rem; 
    display: flex; 
    align-items: center; 
    gap: 0.7rem; 
    position: relative; 
}
.recommended-card img { 
    width: 44px; 
    height: 44px; 
    border-radius: 8px; 
    object-fit: cover; 
    flex-shrink: 0; 
}
.recommended-card strong { 
    display: block; 
    font-size: 0.74rem; 
    line-height: 1.3; 
    margin-bottom: 0.2rem; 
}
.recommended-card span { 
    font-size: 0.76rem; 
    font-weight: 700; 
}
.recommended-card__add {
    width: 26px; 
    height: 26px; 
    border-radius: 50%; 
    border: 1.5px solid var(--brand-red); 
    background: #fff;
    color: var(--brand-red); 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    cursor: pointer; 
    flex-shrink: 0; 
    margin-left: auto; 
}

/* =========================================================================
   CART
   ========================================================================= */
.cart-column { 
    display: flex; 
    flex-direction: column; 
    gap: 1.25rem; 
}
.cart-card { 
    background: #fff; 
    border: 1px solid #ececee; 
    border-radius: 14px; 
    padding: 1.5rem; 
}
.cart-card__header { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 1.1rem; 
}
.cart-card__header h3 { 
    font-size: 1rem; 
    margin: 0; 
}
.cart-card__header i { 
    color: #a5a5aa; 
}

.cart-items { 
    display: flex; 
    flex-direction: column; 
    gap: 1rem; 
    margin-bottom: 1.25rem; 
}
.cart-item { 
    display: flex; 
    align-items: center; 
    gap: 0.7rem; 
}
.cart-item img { 
    width: 46px; 
    height: 46px; 
    border-radius: 8px; 
    object-fit: cover; 
    flex-shrink: 0; 
}
.cart-item__info { 
    flex: 1; 
    min-width: 0; 
    display: flex; 
    flex-direction: column; 
}
.cart-item__info strong { 
    font-size: 0.78rem; 
    line-height: 1.3; 
    overflow: hidden; 
    text-overflow: ellipsis; 
    white-space: nowrap; 
}
.cart-item__info span { 
    font-size: 0.72rem; 
    color: #8a8a90; 
}
.cart-item__remove { 
    border: none; 
    background: none; 
    color: #a5a5aa; 
    cursor: pointer; 
    flex-shrink: 0; 
}
.cart-empty { 
    font-size: 0.82rem; 
    color: #a5a5aa; 
    text-align: center; 
    padding: 1rem 0; 
}

.cart-summary { 
    border-top: 1px solid #f0f0f2; 
    padding-top: 1rem; 
    margin-bottom: 0.9rem; 
}
.cart-summary__row { 
    display: flex; 
    justify-content: space-between; 
    font-size: 0.85rem; 
    color: #55555a; 
    padding: 0.3rem 0; 
}
.cart-summary__row strong { 
    color: #1f2024; 
}
.text-success { 
    color: #1c7a3c !important; 
}
.cart-summary__note { 
    font-size: 0.7rem; 
    color: #a5a5aa; 
    margin: 0.3rem 0 0; 
}

.cart-total { 
    display: flex; 
    justify-content: space-between; 
    align-items: baseline; 
    border-top: 1px solid #f0f0f2; 
    padding-top: 1rem; 
    margin-bottom: 1.1rem; 
}
.cart-total span { 
    font-weight: 700; 
    font-size: 0.92rem; 
}
.cart-total strong { 
    color: var(--brand-red); 
    font-size: 1.4rem; 
}

.checkout-btn { 
    width: 100%; 
    font-weight: 700; 
    border-radius: 8px; 
    padding: 0.85rem; 
    margin-bottom: 1rem; 
}
.checkout-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.payment-icons { 
    display: flex; 
    align-items: center; 
    gap: 0.6rem; 
    flex-wrap: wrap; 
    font-size: 0.7rem; 
    color: #8a8a90; 
    margin-bottom: 0.5rem; 
}
.payment-icons i { 
    color: #55555a; 
}
.payment-note { 
    font-size: 0.72rem; 
    color: #a5a5aa; 
    display: flex; 
    align-items: center; 
    gap: 0.3rem; 
    margin: 0; 
}

.privacy-note-card { 
    background: #fff; 
    border: 1px solid #ececee; 
    border-radius: 14px; 
    padding: 1.25rem; 
    display: flex; 
    gap: 0.8rem; 
    align-items: flex-start; 
}
.privacy-note-card__icon { 
    width: 36px; 
    height: 36px; 
    border-radius: 10px; 
    background: #fdf1f2; 
    color: var(--brand-red); 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    flex-shrink: 0; 
}
.privacy-note-card strong { 
    display: block; 
    font-size: 0.85rem; 
    margin-bottom: 0.3rem; 
}
.privacy-note-card p { 
    font-size: 0.78rem; 
    color: #8a8a90; 
    margin: 0; 
    line-height: 1.5; 
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
    .hero__grid {
        grid-template-columns: 1fr;
        min-height: auto;
    }
    .hero__copy {
        padding: 2.5rem 2rem;
    }
    .hero__title {
        font-size: 2.2rem;
    }
    .hero__media {
        min-height: 260px;
        order: -1;
    }
    .hero__fade {
        display: none;
    }
    .quick-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    .hero {
        padding: 0 1rem;
    }
}

@media (max-width: 768px) {
    .quick-stats {
        padding: 0 1rem;
        grid-template-columns: 1fr;
    }
    .content-grid {
        padding: 0 1rem 2rem;
    }
    .hero {
        padding: 0 1rem;
    }
    .hero__copy {
        padding: 2rem 1.5rem;
    }
    .hero__title {
        font-size: 1.8rem;
    }
    .hero__text {
        font-size: 0.85rem;
    }
    .hero__media {
        min-height: 200px;
    }
    .trust-row {
        padding: 0 1rem;
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .hero__copy {
        padding: 1.5rem 1rem;
    }
    .hero__title {
        font-size: 1.4rem;
    }
    .hero__media {
        min-height: 150px;
    }
    .trust-row {
        grid-template-columns: 1fr;
    }
    .stat-card {
        padding: 0.75rem 1rem;
    }
}
</style>