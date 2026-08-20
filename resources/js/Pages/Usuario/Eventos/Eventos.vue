<script setup>
import { reactive, ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

/* ---------------------------------------------------------------
 * Props recibidas del controlador
 * --------------------------------------------------------------- */
const props = defineProps({
    eventos: {
        type: Array,
        default: () => []
    },
    destacado: {
        type: Object,
        default: null
    },
    categorias: {
        type: Array,
        default: () => []
    },
    proximos: {
        type: Array,
        default: () => []
    }
});

/* ---------------------------------------------------------------
 * Estado
 * --------------------------------------------------------------- */
const filtros = reactive({
    busqueda: '',
    ciudad: 'Todas',
    fecha: '',
    tipo: 'Todos',
});

const isSearchFocused = ref(false);
const mostrarSugerencias = ref(false);

/* ---------------------------------------------------------------
 * Datos
 * --------------------------------------------------------------- */
const beneficios = [
    {
        icon: 'pi-check-circle',
        titulo: 'Comunidad verificada',
        desc: 'Todos los asistentes son perfiles reales y verificados.',
        color: '#22c55e',
        bgColor: '#dcfce7'
    },
    {
        icon: 'pi-shield',
        titulo: 'Privacidad garantizada',
        desc: 'Tu privacidad es nuestra prioridad en cada experiencia.',
        color: '#3b82f6',
        bgColor: '#dbeafe'
    },
    {
        icon: 'pi-lock',
        titulo: 'Acceso exclusivo',
        desc: 'Eventos diseñados solo para miembros seleccionados.',
        color: '#8b5cf6',
        bgColor: '#ede9fe'
    },
    {
        icon: 'pi-sparkles',
        titulo: 'Experiencias reales',
        desc: 'Momentos auténticos que conectan y trascienden.',
        color: '#f59e0b',
        bgColor: '#fef3c7'
    },
];

/* ---------------------------------------------------------------
 * Funciones
 * --------------------------------------------------------------- */
function getImageUrl(path) {
    if (!path) return '/images/shared/avatar-default.jpg';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/') || path.startsWith('/images/')) return path;
    return '/storage/' + path.replace(/^\/+/, '');
}

// Eventos filtrados
const eventosFiltrados = computed(() => {
    let filtered = props.eventos;

    if (filtros.busqueda) {
        const search = filtros.busqueda.toLowerCase().trim();
        filtered = filtered.filter(e =>
            e.titulo?.toLowerCase().includes(search) ||
            e.descripcion?.toLowerCase().includes(search) ||
            e.ciudad?.toLowerCase().includes(search) ||
            e.categoria?.toLowerCase().includes(search)
        );
    }

    if (filtros.ciudad !== 'Todas') {
        filtered = filtered.filter(e => e.ciudad === filtros.ciudad);
    }

    // 🔥 FILTRO POR FECHA - Usar el campo 'fecha' original, no 'fecha_completa'
    if (filtros.fecha) {
        const fechaSeleccionada = new Date(filtros.fecha);
        filtered = filtered.filter(e => {
            if (!e.fecha) return false;
            const fechaEvento = new Date(e.fecha);
            return fechaEvento.toDateString() === fechaSeleccionada.toDateString();
        });
    }

    if (filtros.tipo !== 'Todos') {
        filtered = filtered.filter(e => e.tipo === filtros.tipo);
    }

    return filtered;
});

const eventosCount = computed(() => eventosFiltrados.value.length);

const ciudadesUnicas = computed(() => {
    const ciudades = props.eventos
        .map(e => e.ciudad)
        .filter(Boolean)
        .filter((value, index, self) => self.indexOf(value) === index);
    return ['Todas', ...ciudades];
});

const tiposUnicos = computed(() => {
    const tipos = props.eventos
        .map(e => e.tipo)
        .filter(Boolean)
        .filter((value, index, self) => self.indexOf(value) === index);
    return ['Todos', ...tipos];
});

const sugerencias = computed(() => {
    if (!filtros.busqueda || filtros.busqueda.length < 2) return [];
    const search = filtros.busqueda.toLowerCase().trim();
    const resultados = props.eventos.filter(e =>
        e.titulo?.toLowerCase().includes(search) ||
        e.descripcion?.toLowerCase().includes(search) ||
        e.ciudad?.toLowerCase().includes(search) ||
        e.categoria?.toLowerCase().includes(search)
    );
    return resultados.slice(0, 5).map(e => ({
        id: e.id,
        titulo: e.titulo,
        ciudad: e.ciudad,
        categoria: e.categoria
    }));
});

function limpiarBusqueda() {
    filtros.busqueda = '';
    mostrarSugerencias.value = false;
}

function seleccionarSugerencia(evento) {
    filtros.busqueda = evento.titulo;
    mostrarSugerencias.value = false;
}

function limpiarFecha() {
    filtros.fecha = '';
}

// 🔥 FUNCIÓN PARA FORMATEAR FECHA - Usa el campo 'fecha' original
function formatearFecha(fecha) {
    if (!fecha) return 'Fecha por confirmar';
    try {
        const date = new Date(fecha);
        return date.toLocaleDateString('es-MX', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    } catch {
        return 'Fecha por confirmar';
    }
}

watch(() => filtros.busqueda, (newVal) => {
    if (newVal.length >= 2) {
        mostrarSugerencias.value = true;
    } else {
        mostrarSugerencias.value = false;
    }
});
</script>

<template>
    <AppLayout activeNav="eventos">

        <Head title="Eventos" />

        <div class="eventos-page">
            <!-- ============================================================ -->
            <!-- HERO -->
            <!-- ============================================================ -->
            <section class="hero">
                <div class="hero__content">
                    <span class="hero__eyebrow">✦ EVENTOS EXCLUSIVOS</span>
                    <h1>Eventos y experiencias <span>para vivir tu fantasía</span></h1>
                    <p class="hero__desc">
                        Fiestas privadas, experiencias VIP, viajes temáticos y encuentros exclusivos diseñados
                        para conectar, disfrutar y crear recuerdos inolvidables.
                    </p>
                </div>

                <div v-if="destacado" class="hero__featured">
                    <img :src="getImageUrl(destacado.imagen)" :alt="destacado.titulo" class="hero__featured-image" />
                    <div class="hero__featured-overlay"></div>
                    <span class="hero__featured-badge">✦ EVENTO DESTACADO</span>

                    <div class="hero__featured-content">
                        <div class="hero__featured-date">
                            <strong>{{ destacado.dia }}</strong>
                            <span>{{ destacado.mes }}</span>
                        </div>
                        <div class="hero__featured-info">
                            <h2>{{ destacado.titulo }} <span v-if="destacado.vip" class="vip-tag">VIP</span></h2>
                            <p><i class="pi pi-map-marker"></i> {{ destacado.ciudad }}</p>
                            <p>{{ formatearFecha(destacado.fecha) }}</p>
                        </div>
                        <Link :href="`/eventos/${destacado.id}`" class="hero__featured-cta">
                            VER EVENTO
                        </Link>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- FILTROS -->
            <!-- ============================================================ -->
            <section class="filters-bar">
                <div class="filters-bar__wrap">
                    <!-- Búsqueda -->
                    <div class="search-wrapper" :class="{ 'search-wrapper--focused': isSearchFocused }">
                        <div class="search-input-wrapper">
                            <i class="pi pi-search search-icon"></i>
                            <input v-model="filtros.busqueda" type="text"
                                placeholder="Buscar eventos, lugares, categorías..." class="search-input"
                                @focus="isSearchFocused = true; mostrarSugerencias = true"
                                @blur="setTimeout(() => { isSearchFocused = false; mostrarSugerencias = false }, 200)" />
                            <button v-if="filtros.busqueda" class="search-clear" @click="limpiarBusqueda" type="button">
                                <i class="pi pi-times"></i>
                            </button>
                        </div>

                        <!-- Sugerencias -->
                        <div v-if="mostrarSugerencias && sugerencias.length > 0" class="search-suggestions">
                            <div class="suggestions-header">
                                <span>✦ Sugerencias</span>
                                <span class="suggestions-count">{{ sugerencias.length }} resultados</span>
                            </div>
                            <button v-for="sug in sugerencias" :key="sug.id" class="suggestion-item"
                                @click="seleccionarSugerencia(sug)">
                                <i class="pi pi-search"></i>
                                <div class="suggestion-info">
                                    <strong>{{ sug.titulo }}</strong>
                                    <span>
                                        <i class="pi pi-map-marker"></i> {{ sug.ciudad }}
                                        <span v-if="sug.categoria" class="suggestion-category">
                                            • {{ sug.categoria }}
                                        </span>
                                    </span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="filters-group">
                        <div class="filters-bar__select">
                            <span>Ciudad</span>
                            <select v-model="filtros.ciudad">
                                <option v-for="c in ciudadesUnicas" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>

                        <div class="filters-bar__select filters-bar__select--date">
                            <span>Fecha</span>
                            <div class="date-input-wrapper">
                                <input type="date" v-model="filtros.fecha" class="date-input" />
                                <button v-if="filtros.fecha" class="date-clear" @click="limpiarFecha" type="button">
                                    <i class="pi pi-times"></i>
                                </button>
                                <i class="pi pi-calendar date-icon"></i>
                            </div>
                        </div>

                        <div class="filters-bar__select">
                            <span>Tipo</span>
                            <select v-model="filtros.tipo">
                                <option v-for="t in tiposUnicos" :key="t" :value="t">{{ t }}</option>
                            </select>
                        </div>
                    </div>

                    <span class="results-count">{{ eventosCount }} evento{{ eventosCount !== 1 ? 's' : '' }}</span>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- TODOS LOS EVENTOS -->
            <!-- ============================================================ -->
            <section class="content-grid">
                <div class="events-column">
                    <div class="section__heading section__heading--row">
                        <div class="section__heading-title">
                            <h2>Todos los eventos</h2>
                            <span class="section__heading-count">{{ eventosCount }} disponibles</span>
                        </div>
                        <label class="sort-select">
                            Ordenar por:
                            <select>
                                <option>Próximos primero</option>
                                <option>Más populares</option>
                                <option>Precio</option>
                            </select>
                        </label>
                    </div>

                    <div v-if="eventosFiltrados.length === 0" class="empty-state">
                        <i class="pi pi-calendar"></i>
                        <h3>No hay eventos disponibles</h3>
                        <p>Pronto tendremos nuevos eventos para ti.</p>
                    </div>

                    <div v-else class="event-grid">
                        <article v-for="e in eventosFiltrados" :key="e.id" class="event-card">
                            <div class="event-card__image">
                                <img :src="getImageUrl(e.imagen)" :alt="e.titulo" />
                                <div class="event-card__date">
                                    <strong>{{ e.dia }}</strong>
                                    <span>{{ e.mes }}</span>
                                </div>
                                <span v-if="e.vip" class="event-card__vip-badge">✦ VIP</span>
                                <span v-if="e.esta_completo" class="event-card__agotado-badge">AGOTADO</span>
                            </div>
                            <div class="event-card__body">
                                <h3>{{ e.titulo }}</h3>
                                <p class="event-card__meta">
                                    <i class="pi pi-map-marker"></i> {{ e.ciudad }} &nbsp;
                                    <i class="pi pi-clock"></i> {{ e.hora_formateada || e.hora || '21:00 hrs' }}
                                </p>
                                <p class="event-card__fecha">
                                    <i class="pi pi-calendar"></i> {{ formatearFecha(e.fecha) }}
                                </p>
                                <p class="event-card__desc">{{ e.descripcion || e.descripcion_corta }}</p>
                                <div class="event-card__footer">
                                    <Link :href="`/eventos/${e.id}`" class="event-card__btn">
                                        Más información <i class="pi pi-arrow-right"></i>
                                    </Link>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div v-if="eventosFiltrados.length > 0" class="ver-mas-wrap">
                        <Link href="/eventos/todos" class="ver-mas-btn">
                            VER MÁS EVENTOS
                        </Link>
                    </div>
                </div>

                <aside class="sidebar-column">
                    <!-- ============================================================ -->
                    <!-- BENEFICIOS -->
                    <!-- ============================================================ -->
                    <div class="sidebar-card sidebar-card--benefits">
                        <div class="sidebar-card__header">
                            <h3>
                                <i class="pi pi-star-fill" style="color: #f59e0b;"></i>
                                Beneficios exclusivos
                            </h3>
                        </div>
                        <div class="benefit-list">
                            <div v-for="b in beneficios" :key="b.titulo" class="benefit-item">
                                <div class="benefit-item__icon-wrapper" :style="{ backgroundColor: b.bgColor }">
                                    <span class="benefit-item__icon" :style="{ color: b.color }">
                                        <i class="pi" :class="b.icon"></i>
                                    </span>
                                </div>
                                <div class="benefit-item__content">
                                    <strong>{{ b.titulo }}</strong>
                                    <span>{{ b.desc }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA
   ========================================================================= */
.eventos-page {
    --brand: #C81E3A;
    --brand-dark: #A6152D;
    --brand-soft: #FBEAEC;
    --ink: #171412;
    --ink-soft: #4B4744;
    --muted: #8A8481;
    --muted-light: #B7B2AF;
    --line: #ECE9E7;
    --surface: #FAF8F7;
    --white: #FFFFFF;
    --shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    --shadow-hover: 0 12px 40px rgba(0, 0, 0, 0.1);

    --font-serif: 'Fraunces', Georgia, serif;
    --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;
    --radius-sm: 10px;
    --radius-md: 16px;
    --radius-lg: 24px;
    --radius-full: 999px;

    font-family: var(--font-sans);
    color: var(--ink);
    background: #f7f7f8;
    -webkit-font-smoothing: antialiased;
    min-height: 100vh;
    padding-bottom: 2rem;
}

.eventos-page * {
    box-sizing: border-box;
}

.eventos-page img {
    max-width: 100%;
    display: block;
}

/* =========================================================================
   HERO
   ========================================================================= */
.hero {
    display: grid;
    grid-template-columns: 1fr 1.15fr;
    max-width: 1400px;
    margin: 1.5rem auto 0;
    padding: 0 2rem;
    gap: 2rem;
    align-items: stretch;
}

@media (max-width: 1024px) {
    .hero {
        grid-template-columns: 1fr;
        padding: 0 1rem;
    }
}

.hero__content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: 2.5rem 2.5rem;
    box-shadow: var(--shadow);
}

.hero__eyebrow {
    color: var(--brand);
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
}

.hero h1 {
    font-family: var(--font-serif);
    font-size: 2.1rem;
    font-weight: 400;
    line-height: 1.15;
    margin: 0.6rem 0 1rem;
}

.hero h1 span {
    color: var(--brand);
    font-weight: 700;
    font-style: italic;
}

.hero__desc {
    font-size: 0.9rem;
    color: var(--muted);
    line-height: 1.6;
    margin: 0;
}

.hero__featured {
    position: relative;
    border-radius: var(--radius-lg);
    overflow: hidden;
    min-height: 300px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    box-shadow: var(--shadow);
}

.hero__featured-image {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.hero__featured:hover .hero__featured-image {
    transform: scale(1.05);
}

.hero__featured-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(0deg, rgba(0, 0, 0, 0.85) 25%, rgba(0, 0, 0, 0.15) 70%);
}

.hero__featured-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    z-index: 2;
    background: var(--brand);
    color: var(--white);
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    padding: 0.3rem 0.7rem;
    border-radius: var(--radius-sm);
}

.hero__featured-content {
    position: relative;
    z-index: 2;
    padding: 1.5rem;
    display: flex;
    align-items: flex-end;
    gap: 1rem;
}

.hero__featured-date {
    background: var(--brand);
    color: var(--white);
    border-radius: var(--radius-sm);
    padding: 0.5rem 0.8rem;
    text-align: center;
    line-height: 1.05;
    flex-shrink: 0;
}

.hero__featured-date strong {
    display: block;
    font-size: 1.3rem;
}

.hero__featured-date span {
    font-size: 0.68rem;
    letter-spacing: 0.05em;
}

.hero__featured-info {
    flex: 1;
    color: var(--white);
}

.hero__featured-info h2 {
    font-size: 1.3rem;
    margin: 0 0 0.4rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.vip-tag {
    background: linear-gradient(135deg, #FFD700, #FFA500);
    color: #7a5a00;
    font-size: 0.6rem;
    font-weight: 800;
    padding: 0.15rem 0.6rem;
    border-radius: var(--radius-sm);
    display: inline-block;
}

.hero__featured-info p {
    font-size: 0.8rem;
    color: #d8d8dc;
    margin: 0.15rem 0;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.hero__featured-cta {
    display: inline-block;
    font-weight: 700;
    font-size: 0.8rem;
    background: var(--brand);
    color: var(--white);
    padding: 0.6rem 1.5rem;
    border-radius: var(--radius-sm);
    text-decoration: none;
    transition: all 0.3s ease;
    flex-shrink: 0;
    border: none;
    cursor: pointer;
    font-family: var(--font-sans);
}

.hero__featured-cta:hover {
    background: var(--brand-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(200, 30, 58, 0.3);
}

/* =========================================================================
   FILTROS
   ========================================================================= */
.filters-bar {
    max-width: 1400px;
    margin: 1.75rem auto 0;
    padding: 0 2rem;
}

@media (max-width: 1024px) {
    .filters-bar {
        padding: 0 1rem;
    }
}

.filters-bar__wrap {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 0.75rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}

.filters-bar__wrap:hover {
    box-shadow: var(--shadow-hover);
}

.search-wrapper {
    position: relative;
    flex: 1;
    min-width: 200px;
}

.search-input-wrapper {
    display: flex;
    align-items: center;
    background: var(--surface);
    border: 2px solid var(--line);
    border-radius: var(--radius-full);
    padding: 0.15rem 0.15rem 0.15rem 1rem;
    transition: all 0.3s ease;
}

.search-wrapper--focused .search-input-wrapper {
    border-color: var(--brand);
    box-shadow: 0 0 0 4px rgba(200, 30, 58, 0.1);
    background: var(--white);
}

.search-icon {
    color: var(--muted-light);
    font-size: 0.9rem;
    margin-right: 0.5rem;
    transition: all 0.3s ease;
}

.search-wrapper--focused .search-icon {
    color: var(--brand);
    transform: scale(1.05);
}

.search-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 0.85rem;
    padding: 0.6rem 0;
    background: transparent;
    color: var(--ink);
    font-family: var(--font-sans);
    min-width: 100px;
}

.search-input::placeholder {
    color: var(--muted-light);
    font-weight: 400;
}

.search-clear {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: none;
    background: transparent;
    color: var(--muted-light);
    cursor: pointer;
    border-radius: 50%;
    transition: all 0.2s ease;
    font-size: 0.7rem;
    flex-shrink: 0;
    margin-right: 0.3rem;
}

.search-clear:hover {
    background: var(--line);
    color: var(--ink);
}

.search-suggestions {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    background: var(--white);
    border-radius: var(--radius-md);
    border: 1px solid var(--line);
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    z-index: 100;
    animation: slideDown 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px) scale(0.98);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.suggestions-header {
    display: flex;
    justify-content: space-between;
    padding: 0.6rem 1rem;
    background: var(--surface);
    font-size: 0.65rem;
    color: var(--muted);
    font-weight: 600;
    border-bottom: 1px solid var(--line);
}

.suggestions-count {
    color: var(--muted-light);
    font-weight: 400;
}

.suggestion-item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.6rem 1rem;
    width: 100%;
    border: none;
    background: transparent;
    cursor: pointer;
    text-align: left;
    transition: all 0.2s ease;
    font-family: var(--font-sans);
}

.suggestion-item:hover {
    background: var(--brand-soft);
}

.suggestion-item i {
    color: var(--muted-light);
    font-size: 0.8rem;
}

.suggestion-info {
    flex: 1;
    min-width: 0;
}

.suggestion-info strong {
    display: block;
    font-size: 0.82rem;
    color: var(--ink);
}

.suggestion-info span {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.68rem;
    color: var(--muted);
}

.suggestion-category {
    color: var(--muted-light);
}

.filters-group {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
}

.filters-bar__select {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
    font-size: 0.6rem;
    color: var(--muted-light);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.filters-bar__select select {
    border: 1.5px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.3rem 0.8rem 0.3rem 0.8rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ink);
    background: var(--white);
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: var(--font-sans);
    min-width: 100px;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%238A8481' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 32px;
}

.filters-bar__select select:focus {
    outline: none;
    border-color: var(--brand);
    box-shadow: 0 0 0 4px rgba(200, 30, 58, 0.08);
}

.filters-bar__select select:hover {
    border-color: var(--brand-soft);
}

.filters-bar__select--date {
    position: relative;
}

.date-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.date-input {
    border: 1.5px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.3rem 0.8rem 0.3rem 0.8rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ink);
    background: var(--white);
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: var(--font-sans);
    min-width: 130px;
    appearance: none;
}

.date-input::-webkit-calendar-picker-indicator {
    opacity: 0;
    position: absolute;
    right: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.date-input:focus {
    outline: none;
    border-color: var(--brand);
    box-shadow: 0 0 0 4px rgba(200, 30, 58, 0.08);
}

.date-input:hover {
    border-color: var(--brand-soft);
}

.date-icon {
    position: absolute;
    right: 10px;
    color: var(--muted-light);
    font-size: 0.8rem;
    pointer-events: none;
    transition: color 0.3s ease;
}

.date-input:focus+.date-icon {
    color: var(--brand);
}

.date-clear {
    position: absolute;
    right: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border: none;
    background: transparent;
    color: var(--muted-light);
    cursor: pointer;
    border-radius: 50%;
    transition: all 0.2s ease;
    font-size: 0.6rem;
    padding: 0;
    z-index: 2;
}

.date-clear:hover {
    background: var(--line);
    color: var(--ink);
}

.results-count {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--brand);
    background: var(--brand-soft);
    padding: 0.25rem 1rem;
    border-radius: var(--radius-full);
    white-space: nowrap;
    flex-shrink: 0;
    border: 1px solid rgba(200, 30, 58, 0.1);
}

/* =========================================================================
   CONTENT GRID
   ========================================================================= */
.content-grid {
    max-width: 1400px;
    margin: 2.25rem auto 0;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr;
        padding: 0 1rem;
    }
}

.sort-select {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: var(--ink-soft);
}

.sort-select select {
    border: 1.5px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.4rem 0.7rem;
    font-size: 0.8rem;
    color: var(--ink);
    background: var(--white);
    cursor: pointer;
    font-family: var(--font-sans);
    transition: all 0.3s ease;
}

.sort-select select:focus {
    outline: none;
    border-color: var(--brand);
    box-shadow: 0 0 0 4px rgba(200, 30, 58, 0.08);
}

.empty-state {
    text-align: center;
    padding: 4rem 1rem;
    background: var(--white);
    border-radius: var(--radius-md);
    border: 1px solid var(--line);
}

.empty-state i {
    font-size: 3.5rem;
    color: var(--muted-light);
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.1rem;
    margin: 0 0 0.5rem;
    color: var(--ink);
}

.empty-state p {
    color: var(--muted);
    margin: 0;
}

/* =========================================================================
   EVENTOS GRID
   ========================================================================= */
.event-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}

@media (max-width: 1024px) {
    .event-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 640px) {
    .event-grid {
        grid-template-columns: 1fr;
    }
}

.event-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.event-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-hover);
}

.event-card__image {
    position: relative;
    aspect-ratio: 16/11;
    overflow: hidden;
}

.event-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.event-card:hover .event-card__image img {
    transform: scale(1.06);
}

.event-card__date {
    position: absolute;
    top: 10px;
    left: 10px;
    background: var(--brand);
    color: var(--white);
    border-radius: var(--radius-sm);
    padding: 0.35rem 0.6rem;
    text-align: center;
    line-height: 1.05;
}

.event-card__date strong {
    display: block;
    font-size: 1.1rem;
}

.event-card__date span {
    font-size: 0.62rem;
    letter-spacing: 0.05em;
}

.event-card__vip-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, #FFD700, #FFA500);
    color: #7a5a00;
    font-size: 0.6rem;
    font-weight: 800;
    padding: 0.2rem 0.6rem;
    border-radius: var(--radius-sm);
}

.event-card__agotado-badge {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(4px);
    color: #ffffff;
    font-size: 0.55rem;
    font-weight: 700;
    padding: 0.2rem 0.7rem;
    border-radius: var(--radius-sm);
}

.event-card__body {
    padding: 1rem;
}

.event-card__body h3 {
    font-size: 0.95rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
}

.event-card__meta {
    font-size: 0.75rem;
    color: var(--muted);
    margin: 0 0 0.3rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.event-card__meta i {
    color: var(--brand);
}

.event-card__fecha {
    font-size: 0.7rem;
    color: var(--muted);
    margin: 0 0 0.3rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.event-card__fecha i {
    color: var(--brand);
    font-size: 0.65rem;
}

.event-card__desc {
    font-size: 0.78rem;
    color: var(--ink-soft);
    margin: 0 0 0.9rem;
    line-height: 1.5;
    min-height: 2.4em;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.event-card__footer {
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.event-card__btn {
    flex: 1;
    font-size: 0.78rem;
    font-weight: 700;
    border: 1.5px solid var(--line);
    border-radius: var(--radius-sm);
    color: var(--ink-soft);
    background: transparent;
    padding: 0.5rem 0.75rem;
    text-decoration: none;
    text-align: center;
    transition: all 0.3s ease;
    font-family: var(--font-sans);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
}

.event-card__btn:hover {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
}

.event-card__btn i {
    transition: transform 0.3s ease;
}

.event-card__btn:hover i {
    transform: translateX(4px);
}

/* =========================================================================
   VER MÁS
   ========================================================================= */
.ver-mas-wrap {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

.ver-mas-btn {
    font-weight: 700;
    border-radius: var(--radius-sm);
    padding: 0.75rem 2.5rem;
    border: 1.5px solid var(--line);
    color: var(--ink-soft);
    background: transparent;
    text-decoration: none;
    transition: all 0.3s ease;
    font-family: var(--font-sans);
    cursor: pointer;
    display: inline-block;
}

.ver-mas-btn:hover {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(200, 30, 58, 0.1);
}

/* =========================================================================
   SIDEBAR - BENEFICIOS
   ========================================================================= */
.sidebar-column {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.sidebar-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.25rem;
    transition: all 0.3s ease;
}

.sidebar-card:hover {
    box-shadow: var(--shadow-hover);
}

.sidebar-card--benefits {
    background: linear-gradient(135deg, #ffffff 0%, #faf8f7 100%);
}

.sidebar-card__header {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.sidebar-card__header h3 {
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    color: var(--ink);
}

.sidebar-card__header h3 i {
    font-size: 0.9rem;
}

.benefit-list {
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
}

.benefit-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.7rem 0.8rem;
    border-radius: var(--radius-sm);
    transition: all 0.3s ease;
    background: var(--white);
    border: 1px solid transparent;
}

.benefit-item:hover {
    border-color: var(--line);
    box-shadow: var(--shadow);
    transform: translateX(4px);
}

.benefit-item__icon-wrapper {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.benefit-item:hover .benefit-item__icon-wrapper {
    transform: scale(1.05);
}

.benefit-item__icon {
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.benefit-item__content {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
    flex: 1;
    min-width: 0;
}

.benefit-item__content strong {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--ink);
}

.benefit-item__content span {
    font-size: 0.72rem;
    color: var(--muted);
    line-height: 1.3;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
    .filters-bar__wrap {
        flex-wrap: wrap;
        padding: 0.75rem 1rem;
    }

    .search-wrapper {
        min-width: 100%;
        flex: 1 1 100%;
    }

    .filters-group {
        flex: 1;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .filters-bar__select select,
    .date-input {
        min-width: 80px;
    }

    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .filters-bar__wrap {
        flex-direction: column;
        align-items: stretch;
        padding: 0.75rem;
    }

    .filters-group {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem;
        width: 100%;
    }

    .filters-bar__select select,
    .date-input {
        width: 100%;
        min-width: unset;
    }

    .results-count {
        text-align: center;
        width: 100%;
    }

    .date-input-wrapper {
        width: 100%;
    }

    .hero {
        padding: 0 1rem;
    }

    .hero__content {
        padding: 1.5rem;
    }

    .hero h1 {
        font-size: 1.6rem;
    }

    .hero__featured-content {
        flex-wrap: wrap;
        padding: 1rem;
    }

    .hero__featured-info h2 {
        font-size: 1rem;
    }

    .section__heading {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .section__heading--row {
        flex-direction: column;
        align-items: flex-start;
    }

    .event-card__btn {
        width: 100%;
        justify-content: center;
    }

    .benefit-item {
        padding: 0.5rem 0.6rem;
    }

    .benefit-item__icon-wrapper {
        width: 34px;
        height: 34px;
    }

    .benefit-item__icon {
        font-size: 0.8rem;
    }
}

@media (max-width: 480px) {
    .hero__content {
        padding: 1rem;
    }

    .hero h1 {
        font-size: 1.3rem;
    }

    .hero__featured-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .hero__featured-cta {
        width: 100%;
        text-align: center;
    }

    .ver-mas-btn {
        width: 100%;
        text-align: center;
    }

    .benefit-item__content strong {
        font-size: 0.78rem;
    }

    .benefit-item__content span {
        font-size: 0.68rem;
    }
}
</style>