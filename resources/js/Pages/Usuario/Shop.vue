<template>

    <Head title="Shop | Club de Fantasías" />

    <AppLayout active-nav="shop">
        <div class="shop-page">
            <!-- ============================================================ -->
            <!-- HERO -->
            <!-- ============================================================ -->
            <section class="hero">
                <div class="hero__grid">
                    <div class="hero__copy">
                        <p class="hero__eyebrow">
                            Bienvenido, <strong>{{ usuario.nombre }}</strong>
                            <span v-if="usuario.verificado" class="hero__verified">
                                <i class="pi pi-check-circle"></i> Verificado
                            </span>
                        </p>
                        <h1 class="hero__title">
                            <span class="hero__title-highlight">Explora</span> productos <br />
                            para tu <span class="hero__title-highlight">bienestar</span>
                        </h1>
                        <p class="hero__text">
                            Encuentra productos seleccionados para tu comodidad y satisfacción.
                            Calidad y confianza en cada compra.
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
                    <div class="stat-card__icon-wrapper" :style="{ background: m.color }">
                        <i class="pi" :class="m.icon"></i>
                    </div>
                    <div class="stat-card__body">
                        <span class="stat-card__value-big">{{ m.valor }}</span>
                        <span class="stat-card__title">{{ m.titulo }}</span>
                        <span class="stat-card__label">{{ m.etiqueta }}</span>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- CATEGORÍAS - SCROLL HORIZONTAL -->
            <!-- ============================================================ -->
            <section class="categories-scroll">
                <div class="categories-scroll__inner">
                    <button class="category-chip" :class="{ active: categoriaActiva === null }"
                        @click="categoriaActiva = null">
                        <i class="pi pi-th-large"></i> Todos
                    </button>
                    <button v-for="c in categoriasData" :key="c.label" class="category-chip"
                        :class="{ active: categoriaActiva === c.label }"
                        @click="categoriaActiva = categoriaActiva === c.label ? null : c.label">
                        <i class="pi" :class="c.icon || 'pi-tag'"></i>
                        {{ c.label }}
                    </button>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- CONTENIDO PRINCIPAL -->
            <!-- ============================================================ -->
            <div class="content-grid">
                <!-- ------------------------- FILTROS ------------------------- -->
                <aside class="filters-column">
                    <div class="filter-block">
                        <div class="search-input">
                            <i class="pi pi-search"></i>
                            <input v-model="busqueda" type="text" placeholder="Buscar productos..." />
                        </div>
                    </div>

                    <div class="filter-block">
                        <h3>Precio</h3>
                        <div class="price-range">
                            <input type="range" :min="200" :max="5000" v-model="rangoPrecio.max" class="price-slider" />
                            <div class="price-labels">
                                <span>${{ formatoMoneda(rangoPrecio.min) }}</span>
                                <span>${{ formatoMoneda(rangoPrecio.max) }}+</span>
                            </div>
                        </div>
                    </div>

                    <button class="clear-filters-btn" @click="limpiarFiltros">
                        <i class="pi pi-filter-slash"></i> Limpiar filtros
                    </button>

                    <!-- 🔥 NUEVO BOTÓN: MIS PEDIDOS -->
                    <button class="btn-mis-pedidos" @click="irAMisPedidos">
                        <i class="pi pi-receipt"></i> Mis pedidos
                    </button>
                </aside>

                <!-- ------------------------- PRODUCTOS ------------------------- -->
                <div class="products-column">
                    <div class="products-header">
                        <span class="products-count">
                            <strong>{{ productosFiltrados.length }}</strong> productos
                            <span class="products-total">de {{ totalProductosData }}</span>
                        </span>
                        <label class="sort-select">
                            <select v-model="ordenarPor">
                                <option v-for="op in opcionesOrden" :key="op.value" :value="op.value">
                                    {{ op.label }}
                                </option>
                            </select>
                        </label>
                    </div>

                    <div v-if="productosFiltrados.length === 0" class="empty-state">
                        <i class="pi pi-inbox"></i>
                        <h3>No hay productos</h3>
                        <p>
                            {{ busqueda ? 'No se encontraron productos con esa búsqueda.' : 'No hay productos disponibles en este momento.' }}
                        </p>
                        <button class="clear-filters-btn" @click="limpiarFiltros" v-if="busqueda || categoriaActiva">
                            <i class="pi pi-filter-slash"></i> Limpiar filtros
                        </button>
                    </div>

                    <div class="product-grid">
                        <div v-for="p in productosFiltrados" :key="p.id" class="product-card">
                            <div class="product-card__image" @click="irADetalle(p.id)">
                                <span v-if="p.badge" class="product-card__badge"
                                    :style="{ background: getBadgeColor(p.badge) }">
                                    {{ p.badge }}
                                </span>
                                <img :src="getImageUrl(p.imagen)" :alt="p.nombre" loading="lazy" />
                            </div>
                            <div class="product-card__body">
                                <div class="product-card__category">{{ p.categoria || 'General' }}</div>
                                <strong class="product-card__name" @click="irADetalle(p.id)">{{ p.nombre }}</strong>
                                <div class="product-card__price-row">
                                    <span class="product-card__price">${{ formatoMoneda(p.precio) }} MXN</span>
                                    <span v-if="p.precioOriginal && p.precioOriginal > p.precio"
                                        class="product-card__price-original">
                                        ${{ formatoMoneda(p.precioOriginal) }}
                                    </span>
                                    <span v-if="p.descuento" class="product-card__discount">-{{ p.descuento }}%</span>
                                </div>
                                <button class="add-to-cart-btn" @click="agregarAlCarrito(p)">
                                    <i class="pi pi-shopping-cart"></i> Agregar al carrito
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ------------------------- CARRITO ------------------------- -->
                <aside class="cart-column">
                    <div class="cart-card">
                        <div class="cart-card__header">
                            <h3><i class="pi pi-shopping-cart"></i> Carrito ({{ totalItems }})</h3>
                        </div>

                        <div class="cart-items">
                            <div v-for="item in carrito" :key="item.id + (item.talla || '')" class="cart-item">
                                <img :src="getImageUrl(item.imagen)" :alt="item.nombre" />
                                <div class="cart-item__info">
                                    <strong>{{ item.nombre }}</strong>
                                    <span>{{ item.cantidad }} × ${{ formatoMoneda(item.precio) }}</span>
                                    <span v-if="item.talla" class="cart-item__talla">Talla: {{ item.talla }}</span>
                                </div>
                                <button class="cart-item__remove" @click="quitarDelCarrito(item.id, item.talla || '')">
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
                                Envío gratis desde ${{ formatoMoneda(envioGratisDesde) }} MXN
                            </p>
                        </div>

                        <div class="cart-total">
                            <span>Total</span>
                            <strong>${{ formatoMoneda(total) }} MXN</strong>
                        </div>

                        <button class="checkout-btn" @click="irACheckout" :disabled="carrito.length === 0">
                            <i class="pi pi-lock"></i> FINALIZAR COMPRA
                        </button>

                        <div class="payment-icons">
                            <i class="pi pi-credit-card"></i>
                            <span>VISA</span>
                            <span>Mastercard</span>
                            <span>AMEX</span>
                            <span>PayPal</span>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, reactive, ref, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useCarrito } from '@/composables/useCarrito';

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

// ============================================================
// CARRITO GLOBAL
// ============================================================
const {
    carrito,
    agregarAlCarrito: agregarGlobal,
    quitarDelCarrito: quitarGlobal,
    subtotal,
    totalItems,
} = useCarrito();

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
// MÉTRICAS
// ============================================================
const metricasData = computed(() => {
    if (props.metricas && props.metricas.length > 0) {
        return props.metricas;
    }
    return [
        {
            icon: 'pi-box',
            titulo: 'Productos',
            valor: props.totalProductos || '0',
            etiqueta: 'disponibles',
            color: '#C81E3A'
        },
        {
            icon: 'pi-truck',
            titulo: 'Envíos',
            valor: '24h',
            etiqueta: 'entrega exprés',
            color: '#10B981'
        },
        {
            icon: 'pi-shield',
            titulo: 'Compra discreta',
            valor: '100%',
            etiqueta: 'privacidad garantizada',
            color: '#8B5CF6'
        },
    ];
});

// ============================================================
// MAPA DE ICONOS
// ============================================================
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

// ============================================================
// FILTROS
// ============================================================
const busqueda = ref('');
const categoriaActiva = ref(null);
const rangoPrecio = reactive({ min: 200, max: 5000 });
const ordenarPor = ref('relevantes');

const opcionesOrden = [
    { label: 'Más relevantes', value: 'relevantes' },
    { label: 'Precio: menor a mayor', value: 'precio_asc' },
    { label: 'Precio: mayor a menor', value: 'precio_desc' },
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
const totalProductosData = computed(() => props.totalProductos || productosLista.value.length);

// ============================================================
// CARRITO - CÁLCULOS
// ============================================================
const envioGratisDesde = 1000;
const envioGratis = computed(() => subtotal.value >= envioGratisDesde);
const total = computed(() => subtotal.value);

// ============================================================
// FUNCIONES DEL CARRITO
// ============================================================
function agregarAlCarrito(producto) {
    agregarGlobal({
        id: producto.id,
        nombre: producto.nombre,
        precio: producto.precio,
        imagen: producto.imagen,
        cantidad: 1,
        talla: '',
        categoria: producto.categoria || '',
        marca: producto.marca || '',
        precioOriginal: producto.precioOriginal || null,
        descuento: producto.descuento || 0
    });
}

function quitarDelCarrito(id, talla) {
    quitarGlobal(id, talla || '');
}

// ============================================================
// PRODUCTOS FILTRADOS
// ============================================================
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

    switch (ordenarPor.value) {
        case 'precio_asc':
            resultado.sort((a, b) => a.precio - b.precio);
            break;
        case 'precio_desc':
            resultado.sort((a, b) => b.precio - a.precio);
            break;
        default:
            break;
    }

    return resultado;
});

// ============================================================
// FUNCIONES DE UTILIDAD Y NAVEGACIÓN
// ============================================================
function formatoMoneda(valor) {
    return new Intl.NumberFormat('es-MX').format(valor);
}

function irACheckout() {
    if (carrito.value.length === 0) {
        console.warn('⚠️ Carrito vacío, no se puede ir al checkout');
        return;
    }

    console.log('🛒 Ir a checkout con:', carrito.value.length, 'items');
    console.log('📦 Carrito:', carrito.value);

    router.visit('/tienda/checkout', {
        method: 'get',
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            console.log('✅ Redirigido a checkout exitosamente');
        },
        onError: (errors) => {
            console.error('❌ Error al redirigir a checkout:', errors);
        }
    });
}

// 🔥 NUEVA FUNCIÓN: IR A MIS PEDIDOS
function irAMisPedidos() {
    console.log('📋 Ir a mis pedidos');
    router.visit('/tienda/mis-pedidos', {
        method: 'get',
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            console.log('✅ Redirigido a mis pedidos exitosamente');
        },
        onError: (errors) => {
            console.error('❌ Error al redirigir a mis pedidos:', errors);
        }
    });
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
    ordenarPor.value = 'relevantes';
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

// ============================================================
// ON MOUNTED - DEPURACIÓN
// ============================================================
onMounted(() => {
    console.log('🛒 Shop montado');
    console.log('📦 Carrito inicial:', carrito.value);
    console.log('📦 Total items:', totalItems.value);
    console.log('📦 Subtotal:', subtotal.value);
    console.log('👤 Usuario:', usuario.value);
});
</script>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA
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
    --shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    --shadow-hover: 0 8px 40px rgba(0, 0, 0, 0.12);

    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: var(--ink);
    background: var(--surface);
    -webkit-font-smoothing: antialiased;
}

.shop-page * {
    box-sizing: border-box;
}

/* =========================================================================
   HERO
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
    min-height: 380px;
    background: var(--ink);
    border-radius: 20px;
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
    border-radius: 999px;
    font-size: 0.65rem;
    font-weight: 600;
}

.hero__title {
    font-family: 'Fraunces', Georgia, serif;
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
   QUICK STATS
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
    border-radius: 16px;
    padding: 1.5rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: default;
    box-shadow: var(--shadow);
    border: 1px solid var(--line);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover);
}

.stat-card__icon-wrapper {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: #fff;
    font-size: 1.6rem;
    transition: all 0.3s ease;
}

.stat-card:hover .stat-card__icon-wrapper {
    transform: scale(1.08);
}

.stat-card__body {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
    flex: 1;
}

.stat-card__value-big {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--ink);
    line-height: 1.1;
}

.stat-card__title {
    font-weight: 600;
    font-size: 0.82rem;
    color: var(--ink-soft);
}

.stat-card__label {
    font-size: 0.68rem;
    color: var(--muted-light);
}

/* =========================================================================
   CATEGORÍAS - SCROLL
   ========================================================================= */
.categories-scroll {
    max-width: 1400px;
    margin: 1.5rem auto 0;
    padding: 0 2rem;
    overflow-x: auto;
}

.categories-scroll__inner {
    display: flex;
    gap: 0.6rem;
    padding: 0.25rem 0 0.5rem;
    flex-wrap: nowrap;
}

.category-chip {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.55rem 1.2rem;
    border: 1.5px solid var(--line);
    border-radius: 999px;
    background: #fff;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink-soft);
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
}

.category-chip:hover {
    border-color: var(--brand);
    color: var(--brand);
}

.category-chip.active {
    background: var(--brand);
    border-color: var(--brand);
    color: #fff;
}

.category-chip i {
    font-size: 0.75rem;
}

/* =========================================================================
   CONTENT GRID
   ========================================================================= */
.content-grid {
    max-width: 1400px;
    margin: 1.5rem auto 0;
    padding: 0 2rem 2.5rem;
    display: grid;
    grid-template-columns: 220px minmax(0, 1fr) 320px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1300px) {
    .content-grid {
        grid-template-columns: 1fr;
    }

    .cart-column {
        grid-column: 1 / -1;
    }

    .filters-column {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr auto;
        gap: 0.75rem;
    }

    .filters-column .filter-block {
        margin-bottom: 0;
    }
}

@media (max-width: 800px) {
    .content-grid {
        grid-template-columns: 1fr;
    }

    .filters-column {
        grid-template-columns: 1fr;
    }
}

/* =========================================================================
   FILTERS
   ========================================================================= */
.filters-column {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.filter-block {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 12px;
    padding: 1rem 1.25rem;
}

.filter-block h3 {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: var(--muted);
    margin: 0 0 0.75rem;
}

.search-input {
    position: relative;
    display: block;
}

.search-input input {
    width: 100%;
    border: 1.5px solid var(--line);
    border-radius: 10px;
    padding: 0.6rem 2.4rem 0.6rem 1rem;
    font-size: 0.82rem;
    outline: none;
    background: var(--surface);
    transition: border-color 0.2s ease;
}

.search-input input:focus {
    border-color: var(--brand);
}

.search-input i {
    position: absolute;
    right: 0.8rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted-light);
    font-size: 0.85rem;
}

.price-slider {
    width: 100%;
    accent-color: var(--brand);
    margin-bottom: 0.4rem;
}

.price-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.72rem;
    color: var(--muted);
}

.clear-filters-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    border: 1.5px solid var(--line);
    border-radius: 10px;
    background: #fff;
    color: var(--muted);
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    width: 100%;
}

.clear-filters-btn:hover {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
}

/* 🔥 NUEVO ESTILO: BOTÓN MIS PEDIDOS */
.btn-mis-pedidos {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    border: 1.5px solid var(--brand);
    border-radius: 10px;
    background: var(--brand);
    color: #fff;
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    width: 100%;
}

.btn-mis-pedidos:hover {
    background: var(--brand-dark);
    border-color: var(--brand-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(200, 30, 58, 0.3);
}

.btn-mis-pedidos i {
    font-size: 0.85rem;
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
    color: var(--muted);
}

.products-count strong {
    color: var(--ink);
    font-weight: 700;
}

.products-total {
    color: var(--muted-light);
}

.sort-select select {
    border: 1.5px solid var(--line);
    border-radius: 10px;
    padding: 0.5rem 0.8rem;
    font-size: 0.82rem;
    color: var(--ink);
    background: #fff;
    cursor: pointer;
    outline: none;
}

.sort-select select:focus {
    border-color: var(--brand);
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--line);
}

.empty-state i {
    font-size: 2.5rem;
    color: var(--muted-light);
    margin-bottom: 1rem;
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
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}

@media (max-width: 1100px) {
    .product-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 600px) {
    .product-grid {
        grid-template-columns: 1fr;
    }
}

.product-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.product-card:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-4px);
}

.product-card__image {
    position: relative;
    aspect-ratio: 1/1;
    background: var(--surface);
    cursor: pointer;
}

.product-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-card__image img {
    transform: scale(1.03);
}

.product-card__badge {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    padding: 0.2rem 0.7rem;
    border-radius: 999px;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    pointer-events: none;
}

.product-card__body {
    padding: 1rem 1.1rem 1.1rem;
}

.product-card__category {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--muted-light);
    margin-bottom: 0.2rem;
}

.product-card__name {
    display: block;
    font-size: 0.85rem;
    line-height: 1.35;
    margin-bottom: 0.6rem;
    min-height: 2.2em;
    color: var(--ink);
    font-weight: 600;
    cursor: pointer;
    transition: color 0.2s ease;
}

.product-card__name:hover {
    color: var(--brand);
}

.product-card__price-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.75rem;
}

.product-card__price {
    font-size: 1rem;
    font-weight: 800;
    color: var(--ink);
}

.product-card__price-original {
    font-size: 0.78rem;
    color: var(--muted-light);
    text-decoration: line-through;
}

.product-card__discount {
    font-size: 0.7rem;
    font-weight: 700;
    color: #EF4444;
    background: #FEE2E2;
    padding: 0.1rem 0.4rem;
    border-radius: 999px;
}

.add-to-cart-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    border: none;
    border-radius: 10px;
    background: var(--brand);
    color: #fff;
    padding: 0.6rem 0.8rem;
    font-size: 0.74rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

.add-to-cart-btn:hover {
    background: var(--brand-dark);
    transform: scale(1.02);
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
    border: 1px solid var(--line);
    border-radius: 16px;
    padding: 1.5rem;
    position: sticky;
    top: 2rem;
}

.cart-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.cart-card__header h3 {
    font-size: 1rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.cart-items {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    margin-bottom: 1.25rem;
    max-height: 300px;
    overflow-y: auto;
}

.cart-item {
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.cart-item img {
    width: 44px;
    height: 44px;
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
    color: var(--muted);
}

.cart-item__talla {
    font-size: 0.65rem;
    color: var(--muted-light);
}

.cart-item__remove {
    border: none;
    background: none;
    color: var(--muted-light);
    cursor: pointer;
    flex-shrink: 0;
    padding: 0.2rem;
    transition: color 0.2s ease;
}

.cart-item__remove:hover {
    color: #EF4444;
}

.cart-empty {
    font-size: 0.82rem;
    color: var(--muted-light);
    text-align: center;
    padding: 1.5rem 0;
}

.cart-summary {
    border-top: 1px solid var(--line);
    padding-top: 0.8rem;
    margin-bottom: 0.8rem;
}

.cart-summary__row {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: var(--ink-soft);
    padding: 0.2rem 0;
}

.cart-summary__row strong {
    color: var(--ink);
}

.text-success {
    color: #10B981 !important;
}

.cart-summary__note {
    font-size: 0.7rem;
    color: var(--muted-light);
    margin: 0.3rem 0 0;
}

.cart-total {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    border-top: 1px solid var(--line);
    padding-top: 0.8rem;
    margin-bottom: 1rem;
}

.cart-total span {
    font-weight: 700;
    font-size: 0.9rem;
}

.cart-total strong {
    color: var(--brand);
    font-size: 1.4rem;
}

.checkout-btn {
    width: 100%;
    font-weight: 700;
    border-radius: 10px;
    padding: 0.8rem;
    border: none;
    background: var(--brand);
    color: #fff;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.checkout-btn:hover:not(:disabled) {
    background: var(--brand-dark);
    transform: scale(1.02);
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
    color: var(--muted);
    margin-top: 0.75rem;
    justify-content: center;
}

.payment-icons i {
    color: var(--ink-soft);
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
}

@media (max-width: 768px) {
    .hero {
        padding: 0 1rem;
    }

    .quick-stats {
        padding: 0 1rem;
        grid-template-columns: 1fr 1fr;
    }

    .categories-scroll {
        padding: 0 1rem;
    }

    .content-grid {
        padding: 0 1rem 2rem;
    }

    .hero__copy {
        padding: 2rem 1.5rem;
    }

    .hero__title {
        font-size: 1.8rem;
    }

    .hero__media {
        min-height: 200px;
    }

    .hero__text {
        font-size: 0.85rem;
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

    .quick-stats {
        grid-template-columns: 1fr;
    }

    .stat-card {
        padding: 0.75rem 1rem;
    }

    .stat-card__icon-wrapper {
        width: 44px;
        height: 44px;
        font-size: 1.2rem;
    }

    .stat-card__value-big {
        font-size: 1.4rem;
    }
}
</style>