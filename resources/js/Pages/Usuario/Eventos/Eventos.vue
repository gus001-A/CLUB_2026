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
    ciudad: 'TODAS',
    fecha: '',
    tipo: 'TODOS',
});

const isSearchFocused = ref(false);
const mostrarSugerencias = ref(false);
const ordenSeleccionado = ref('proximos');
const filtrosActivos = ref(false);

/* ---------------------------------------------------------------
 * Datos - Imágenes de categorías
 * --------------------------------------------------------------- */
const categoriaImagenes = {
    'Fiestas privadas': '/images/categorias_eventos/nocturnos.png',
    'Jacuzzi': '/images/categorias_eventos/nocturnos.png',
    'Club nights': '/images/categorias_eventos/nocturnos.png',
    'Eventos VIP': '/images/categorias_eventos/nocturnos.png',
    'Viajes temáticos': '/images/categorias_eventos/social.png',
    'Cenas': '/images/categorias_eventos/social.png',
    'Cenas exclusivas': '/images/categorias_eventos/social.png',
    'Club': '/images/categorias_eventos/nocturnos.png',
    'Fiesta': '/images/categorias_eventos/nocturnos.png',
    'Concierto': '/images/categorias_eventos/nocturnos.png',
    'After': '/images/categorias_eventos/nocturnos.png',
    'Deportivo': '/images/categorias_eventos/social.png',
    'Cultural': '/images/categorias_eventos/social.png',
    'Social': '/images/categorias_eventos/social.png',
    'Nocturna': '/images/categorias_eventos/nocturnos.png',
    'Nocturnos': '/images/categorias_eventos/nocturnos.png',
};

/* ---------------------------------------------------------------
 * Funciones
 * --------------------------------------------------------------- */
function toggleFavorito(evento) {
    evento.favorito = !evento.favorito;
}

function getImageUrl(path) {
    if (!path) return '/images/shared/avatar-default.jpg';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/') || path.startsWith('/images/')) return path;
    return '/storage/' + path.replace(/^\/+/, '');
}

function getCategoriaImagen(categoria) {
    if (!categoria) return '/images/categorias_eventos/social.png';
    
    if (categoriaImagenes[categoria]) {
        return categoriaImagenes[categoria];
    }
    
    const categoriaLower = categoria.toLowerCase();
    for (const [key, value] of Object.entries(categoriaImagenes)) {
        if (key.toLowerCase().includes(categoriaLower) || categoriaLower.includes(key.toLowerCase())) {
            return value;
        }
    }
    
    return '/images/categorias_eventos/social.png';
}

function toUpperCase(text) {
    if (!text) return '';
    return text.toUpperCase();
}

const eventosFiltrados = computed(() => {
    let filtered = props.eventos;
    
    if (filtros.busqueda) {
        const search = filtros.busqueda.toLowerCase().trim();
        filtered = filtered.filter(e => 
            e.titulo?.toLowerCase().includes(search) || 
            e.desc?.toLowerCase().includes(search) ||
            e.ciudad?.toLowerCase().includes(search) ||
            e.categoria?.toLowerCase().includes(search)
        );
    }
    
    if (filtros.ciudad !== 'TODAS') {
        filtered = filtered.filter(e => e.ciudad?.toUpperCase() === filtros.ciudad);
    }
    
    if (filtros.fecha) {
        const fechaSeleccionada = new Date(filtros.fecha);
        filtered = filtered.filter(e => {
            if (!e.fecha) return false;
            const fechaEvento = new Date(e.fecha);
            return fechaEvento.toDateString() === fechaSeleccionada.toDateString();
        });
    }
    
    if (filtros.tipo !== 'TODOS') {
        filtered = filtered.filter(e => e.tipo?.toUpperCase() === filtros.tipo);
    }
    
    if (ordenSeleccionado.value === 'proximos') {
        filtered = [...filtered].sort((a, b) => new Date(a.fecha) - new Date(b.fecha));
    } else if (ordenSeleccionado.value === 'populares') {
        filtered = [...filtered].sort((a, b) => (b.vip ? 1 : 0) - (a.vip ? 1 : 0));
    } else if (ordenSeleccionado.value === 'precio') {
        filtered = [...filtered].sort((a, b) => (a.precio || 0) - (b.precio || 0));
    }
    
    filtrosActivos.value = !!(filtros.busqueda || filtros.ciudad !== 'TODAS' || filtros.fecha || filtros.tipo !== 'TODOS');
    
    return filtered;
});

const eventosCount = computed(() => eventosFiltrados.value.length);

const ciudadesUnicas = computed(() => {
    const ciudades = props.eventos
        .map(e => e.ciudad)
        .filter(Boolean)
        .map(c => c.toUpperCase())
        .filter((value, index, self) => self.indexOf(value) === index);
    return ['TODAS', ...ciudades];
});

const tiposUnicos = computed(() => {
    const tipos = props.eventos
        .map(e => e.tipo)
        .filter(Boolean)
        .map(t => t.toUpperCase())
        .filter((value, index, self) => self.indexOf(value) === index);
    return ['TODOS', ...tipos];
});

const sugerencias = computed(() => {
    if (!filtros.busqueda || filtros.busqueda.length < 2) return [];
    const search = filtros.busqueda.toLowerCase().trim();
    const resultados = props.eventos.filter(e => 
        e.titulo?.toLowerCase().includes(search) ||
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

function limpiarTodosLosFiltros() {
    filtros.busqueda = '';
    filtros.ciudad = 'TODAS';
    filtros.fecha = '';
    filtros.tipo = 'TODOS';
    ordenSeleccionado.value = 'proximos';
    mostrarSugerencias.value = false;
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
                    <img 
                        :src="getImageUrl(destacado.imagen)" 
                        :alt="destacado.titulo" 
                        class="hero__featured-image" 
                    />
                    <div class="hero__featured-overlay"></div>
                    <span class="hero__featured-badge">✦ EVENTO DESTACADO</span>

                    <div class="hero__featured-content">
                        <div class="hero__featured-date">
                            <strong>{{ destacado.dia }}</strong>
                            <span>{{ destacado.mes?.toUpperCase() }}</span>
                        </div>
                        <div class="hero__featured-info">
                            <h2>{{ destacado.titulo }} <span v-if="destacado.vip" class="vip-tag">VIP</span></h2>
                            <p><i class="pi pi-map-marker"></i> {{ destacado.ciudad?.toUpperCase() }}</p>
                            <p>{{ destacado.fecha }}</p>
                        </div>
                        <Link :href="`/eventos/${destacado.id}`" class="hero__featured-cta">
                            VER EVENTO
                        </Link>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- FILTROS MEJORADOS -->
            <!-- ============================================================ -->
            <section class="filters-bar">
                <div class="filters-bar__wrap">
                    <!-- Búsqueda -->
                    <div class="search-wrapper" :class="{ 'search-wrapper--focused': isSearchFocused }">
                        <div class="search-input-wrapper">
                            <i class="pi pi-search search-icon"></i>
                            <input 
                                v-model="filtros.busqueda" 
                                type="text" 
                                placeholder="Buscar eventos, lugares, categorías..." 
                                class="search-input"
                                @focus="isSearchFocused = true; mostrarSugerencias = true"
                                @blur="setTimeout(() => { isSearchFocused = false; mostrarSugerencias = false }, 200)"
                            />
                            <button 
                                v-if="filtros.busqueda" 
                                class="search-clear" 
                                @click="limpiarBusqueda"
                                type="button"
                            >
                                <i class="pi pi-times"></i>
                            </button>
                        </div>
                        
                        <!-- Sugerencias -->
                        <div v-if="mostrarSugerencias && sugerencias.length > 0" class="search-suggestions">
                            <div class="suggestions-header">
                                <span>✦ SUGERENCIAS</span>
                                <span class="suggestions-count">{{ sugerencias.length }} RESULTADOS</span>
                            </div>
                            <button 
                                v-for="sug in sugerencias" 
                                :key="sug.id"
                                class="suggestion-item"
                                @click="seleccionarSugerencia(sug)"
                            >
                                <i class="pi pi-search"></i>
                                <div class="suggestion-info">
                                    <strong>{{ sug.titulo }}</strong>
                                    <span>
                                        <i class="pi pi-map-marker"></i> {{ sug.ciudad?.toUpperCase() }}
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
                            <span>CIUDAD</span>
                            <select v-model="filtros.ciudad">
                                <option v-for="c in ciudadesUnicas" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>

                        <div class="filters-bar__select filters-bar__select--date">
                            <span>FECHA</span>
                            <div class="date-input-wrapper">
                                <input 
                                    type="date" 
                                    v-model="filtros.fecha" 
                                    class="date-input"
                                />
                                <button 
                                    v-if="filtros.fecha" 
                                    class="date-clear" 
                                    @click="limpiarFecha"
                                    type="button"
                                >
                                    <i class="pi pi-times"></i>
                                </button>
                                <i class="pi pi-calendar date-icon"></i>
                            </div>
                        </div>

                        <div class="filters-bar__select">
                            <span>TIPO</span>
                            <select v-model="filtros.tipo">
                                <option v-for="t in tiposUnicos" :key="t" :value="t">{{ t }}</option>
                            </select>
                        </div>

                        <div class="filters-bar__select">
                            <span>ORDENAR</span>
                            <select v-model="ordenSeleccionado">
                                <option value="proximos">PRÓXIMOS</option>
                                <option value="populares">POPULARES</option>
                                <option value="precio">PRECIO</option>
                            </select>
                        </div>

                        <!-- Botón limpiar filtros -->
                        <button 
                            v-if="filtrosActivos" 
                            class="clear-filters-btn"
                            @click="limpiarTodosLosFiltros"
                            type="button"
                        >
                            <i class="pi pi-times-circle"></i>
                            LIMPIAR FILTROS
                        </button>
                    </div>

                    <span class="results-count">{{ eventosCount }} EVENTO{{ eventosCount !== 1 ? 'S' : '' }}</span>
                </div>

                <!-- Filtros activos - badges -->
                <div v-if="filtrosActivos" class="active-filters">
                    <span class="active-filters__label">FILTROS ACTIVOS:</span>
                    <span v-if="filtros.busqueda" class="filter-badge">
                        <i class="pi pi-search"></i> {{ filtros.busqueda }}
                        <button @click="limpiarBusqueda" class="filter-badge__remove">
                            <i class="pi pi-times"></i>
                        </button>
                    </span>
                    <span v-if="filtros.ciudad !== 'TODAS'" class="filter-badge">
                        <i class="pi pi-map-marker"></i> {{ filtros.ciudad }}
                        <button @click="filtros.ciudad = 'TODAS'" class="filter-badge__remove">
                            <i class="pi pi-times"></i>
                        </button>
                    </span>
                    <span v-if="filtros.fecha" class="filter-badge">
                        <i class="pi pi-calendar"></i> {{ new Date(filtros.fecha).toLocaleDateString('es-ES') }}
                        <button @click="limpiarFecha" class="filter-badge__remove">
                            <i class="pi pi-times"></i>
                        </button>
                    </span>
                    <span v-if="filtros.tipo !== 'TODOS'" class="filter-badge">
                        <i class="pi pi-tag"></i> {{ filtros.tipo }}
                        <button @click="filtros.tipo = 'TODOS'" class="filter-badge__remove">
                            <i class="pi pi-times"></i>
                        </button>
                    </span>
                    <button @click="limpiarTodosLosFiltros" class="clear-all-badge">
                        LIMPIAR TODOS
                    </button>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- TODOS LOS EVENTOS + SIDEBAR -->
            <!-- ============================================================ -->
            <section class="content-grid">
                <div class="events-column">
                    <div class="section__heading section__heading--row">
                        <div class="section__heading-title">
                            <h2>TODOS LOS EVENTOS</h2>
                            <span class="section__heading-count">{{ eventosCount }} DISPONIBLES</span>
                        </div>
                    </div>

                    <div v-if="eventosFiltrados.length === 0" class="empty-state">
                        <i class="pi pi-calendar"></i>
                        <h3>NO HAY EVENTOS DISPONIBLES</h3>
                        <p>Pronto tendremos nuevos eventos para ti.</p>
                        <button @click="limpiarTodosLosFiltros" class="empty-state__btn">
                            LIMPIAR FILTROS
                        </button>
                    </div>

                    <div v-else class="event-grid">
                        <article v-for="e in eventosFiltrados" :key="e.id" class="event-card">
                            <div class="event-card__image">
                                <img :src="getImageUrl(e.imagen)" :alt="e.titulo" />
                                <div class="event-card__date">
                                    <strong>{{ e.dia }}</strong>
                                    <span>{{ e.mes?.toUpperCase() }}</span>
                                </div>
                                <span v-if="e.vip" class="event-card__vip-badge">✦ VIP</span>
                                <span v-else-if="e.tipo" class="event-card__type-badge">{{ e.tipo?.toUpperCase() }}</span>
                            </div>
                            <div class="event-card__body">
                                <h3>{{ e.titulo }}</h3>
                                <p class="event-card__meta">
                                    <i class="pi pi-map-marker"></i> {{ e.ciudad?.toUpperCase() }} &nbsp;
                                    <i class="pi pi-clock"></i> {{ e.hora }}
                                </p>
                                <p class="event-card__desc">{{ e.desc }}</p>
                                <div class="event-card__footer">
                                    <Link :href="`/eventos/${e.id}`" class="event-card__btn">
                                        MÁS INFORMACIÓN
                                    </Link>
                                    <button class="favorite-btn" :class="{ active: e.favorito }" @click="toggleFavorito(e)">
                                        <i class="pi" :class="e.favorito ? 'pi-heart-fill' : 'pi-heart'"></i>
                                    </button>
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
                    <!-- PRÓXIMOS EVENTOS -->
                    <!-- ============================================================ -->
                    <div class="sidebar-card sidebar-card--proximos">
                        <div class="sidebar-card__header">
                            <div class="sidebar-card__header-title">
                                <i class="pi pi-calendar sidebar-card__icon"></i>
                                <h3>PRÓXIMOS EVENTOS</h3>
                            </div>
                            <Link href="/eventos/recomendados" class="see-all">
                                VER TODOS <i class="pi pi-chevron-right"></i>
                            </Link>
                        </div>

                        <div v-if="proximos.length === 0" class="empty-proximos">
                            <i class="pi pi-calendar-plus"></i>
                            <span>PRONTO TENDRÁS EVENTOS DISPONIBLES</span>
                        </div>

                        <div v-else class="mini-event-list">
                            <div v-for="(e, index) in proximos" :key="e.id" class="mini-event-item" :style="{ animationDelay: `${index * 0.1}s` }">
                                <div class="mini-event-item__image">
                                    <img :src="getImageUrl(e.imagen)" :alt="e.titulo" />
                                </div>
                                
                                <div class="mini-event-item__date">
                                    <strong>{{ e.dia }}</strong>
                                    <span>{{ e.mes?.toUpperCase() }}</span>
                                </div>
                                
                                <div class="mini-event-item__info">
                                    <strong>{{ e.titulo }}</strong>
                                    <span class="info-location">
                                        <i class="pi pi-map-marker"></i> {{ e.lugar || e.ciudad?.toUpperCase() }}
                                    </span>
                                    <span class="info-time">
                                        <i class="pi pi-clock"></i> {{ e.hora }}
                                    </span>
                                    <span v-if="e.tipo" class="info-category">
                                        <i class="pi pi-tag"></i> {{ e.tipo?.toUpperCase() }}
                                    </span>
                                </div>
                                
                                <button class="favorite-btn mini-fav" @click="toggleFavorito(e)">
                                    <i class="pi" :class="e.favorito ? 'pi-heart-fill' : 'pi-heart'"></i>
                                </button>
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
    background: linear-gradient(0deg, rgba(0,0,0,0.85) 25%, rgba(0,0,0,0.15) 70%);
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
    flex-wrap: wrap;
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
    text-transform: uppercase;
    letter-spacing: 0.05em;
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
    flex-wrap: wrap;
}

.filters-bar__select {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
    font-size: 0.55rem;
    color: var(--muted-light);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.filters-bar__select select {
    border: 1.5px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.3rem 0.8rem 0.3rem 0.8rem;
    font-size: 0.75rem;
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
    text-transform: uppercase;
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
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--ink);
    background: var(--white);
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: var(--font-sans);
    min-width: 130px;
    appearance: none;
    text-transform: uppercase;
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

.date-input:focus + .date-icon {
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

.clear-filters-btn {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.65rem;
    font-weight: 700;
    color: var(--brand);
    background: var(--brand-soft);
    border: none;
    padding: 0.3rem 1rem;
    border-radius: var(--radius-full);
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: var(--font-sans);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    white-space: nowrap;
}

.clear-filters-btn:hover {
    background: var(--brand);
    color: var(--white);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(200, 30, 58, 0.2);
}

.clear-filters-btn i {
    font-size: 0.7rem;
}

.results-count {
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--brand);
    background: var(--brand-soft);
    padding: 0.25rem 1rem;
    border-radius: var(--radius-full);
    white-space: nowrap;
    flex-shrink: 0;
    border: 1px solid rgba(200, 30, 58, 0.1);
    letter-spacing: 0.04em;
}

/* =========================================================================
   FILTROS ACTIVOS - BADGES
   ========================================================================= */
.active-filters {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    padding: 0.5rem 0 0 0;
    margin-top: 0.5rem;
}

.active-filters__label {
    font-size: 0.55rem;
    font-weight: 700;
    color: var(--muted-light);
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.filter-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.5rem 0.2rem 0.7rem;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius-full);
    font-size: 0.65rem;
    font-weight: 600;
    color: var(--ink-soft);
}

.filter-badge i {
    font-size: 0.55rem;
    color: var(--brand);
}

.filter-badge__remove {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    border: none;
    background: transparent;
    color: var(--muted-light);
    cursor: pointer;
    border-radius: 50%;
    transition: all 0.2s ease;
    font-size: 0.5rem;
    padding: 0;
}

.filter-badge__remove:hover {
    background: var(--error);
    color: var(--white);
}

.clear-all-badge {
    font-size: 0.6rem;
    font-weight: 700;
    color: var(--error);
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 0.1rem 0.5rem;
    border-radius: var(--radius-sm);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.clear-all-badge:hover {
    background: #FEE8EA;
}

/* =========================================================================
   SECCIONES
   ========================================================================= */
.section {
    max-width: 1400px;
    margin: 2.25rem auto 0;
    padding: 0 2rem;
}

@media (max-width: 1024px) {
    .section {
        padding: 0 1rem;
    }
}

.section__heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.1rem;
}

.section__heading h2 {
    font-family: var(--font-serif);
    font-size: 1.3rem;
    margin: 0;
    letter-spacing: 0.03em;
}

.section__heading--row {
    align-items: flex-end;
}

.section__heading-count {
    font-size: 0.7rem;
    color: var(--muted-light);
    margin-left: 0.5rem;
    font-weight: 600;
    letter-spacing: 0.04em;
}

.section__heading-title {
    display: flex;
    align-items: baseline;
}

.see-all {
    color: var(--brand);
    font-size: 0.75rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    transition: all 0.3s ease;
    letter-spacing: 0.04em;
}

.see-all:hover {
    color: var(--brand-dark);
    gap: 0.6rem;
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
    letter-spacing: 0.04em;
}

.empty-state p {
    color: var(--muted);
    margin: 0 0 1rem;
}

.empty-state__btn {
    font-weight: 700;
    font-size: 0.78rem;
    color: var(--brand);
    background: var(--brand-soft);
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: var(--font-sans);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.empty-state__btn:hover {
    background: var(--brand);
    color: var(--white);
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
    font-size: 0.58rem;
    letter-spacing: 0.05em;
}

.event-card__vip-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, #FFD700, #FFA500);
    color: #7a5a00;
    font-size: 0.55rem;
    font-weight: 800;
    padding: 0.2rem 0.6rem;
    border-radius: var(--radius-sm);
}

.event-card__type-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0,0,0,0.7);
    color: var(--white);
    font-size: 0.5rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: var(--radius-sm);
    letter-spacing: 0.04em;
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
    margin: 0 0 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.event-card__desc {
    font-size: 0.78rem;
    color: var(--ink-soft);
    margin: 0 0 0.9rem;
    line-height: 1.5;
    min-height: 2.4em;
}

.event-card__footer {
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.event-card__btn {
    flex: 1;
    font-size: 0.7rem;
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
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.event-card__btn:hover {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
}

.favorite-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1.5px solid var(--line);
    background: var(--white);
    color: var(--muted-light);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.favorite-btn:hover {
    border-color: var(--brand);
    color: var(--brand);
    transform: scale(1.05);
}

.favorite-btn.active {
    color: var(--brand);
    border-color: var(--brand);
    background: var(--brand-soft);
}

.ver-mas-wrap {
    display: flex;
    justify-content: center;
    margin-top: 1.75rem;
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
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-size: 0.8rem;
}

.ver-mas-btn:hover {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(200, 30, 58, 0.1);
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
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.25rem;
    transition: all 0.3s ease;
}

.sidebar-card:hover {
    box-shadow: var(--shadow-hover);
}

.sidebar-card--proximos {
    background: linear-gradient(135deg, #fdf2f4 0%, #fef7f8 100%);
    border-color: #fce4e8;
}

.sidebar-card--proximos:hover {
    border-color: var(--brand-soft);
    box-shadow: 0 8px 30px rgba(200, 30, 58, 0.08);
}

.sidebar-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.sidebar-card__header-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sidebar-card__icon {
    font-size: 1.1rem;
    color: var(--brand);
}

.sidebar-card__header h3 {
    font-size: 0.85rem;
    font-weight: 700;
    margin: 0;
    color: var(--ink);
    letter-spacing: 0.04em;
}

.empty-proximos {
    text-align: center;
    padding: 1.5rem 0.5rem;
    color: var(--muted);
}

.empty-proximos i {
    font-size: 2rem;
    color: var(--muted-light);
    display: block;
    margin-bottom: 0.5rem;
}

.empty-proximos span {
    font-size: 0.8rem;
}

/* =========================================================================
   MINI EVENT LIST
   ========================================================================= */
.mini-event-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.mini-event-item {
    display: flex;
    align-items: stretch;
    gap: 0.7rem;
    padding: 0.6rem;
    border-radius: var(--radius-sm);
    background: var(--white);
    transition: all 0.3s ease;
    border: 1px solid transparent;
    animation: fadeInUp 0.4s ease forwards;
    opacity: 0;
}

.mini-event-item:hover {
    background: var(--white);
    border-color: var(--line);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    transform: translateX(4px);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.mini-event-item__image {
    width: 56px;
    height: 56px;
    border-radius: var(--radius-sm);
    overflow: hidden;
    flex-shrink: 0;
}

.mini-event-item__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.mini-event-item__date {
    background: var(--brand-soft);
    color: var(--brand);
    border-radius: var(--radius-sm);
    padding: 0.3rem 0.5rem;
    text-align: center;
    line-height: 1.05;
    flex-shrink: 0;
    min-width: 44px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.mini-event-item__date strong {
    display: block;
    font-size: 0.95rem;
    font-weight: 800;
}

.mini-event-item__date span {
    font-size: 0.55rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    font-weight: 600;
}

.mini-event-item__info {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
    justify-content: center;
}

.mini-event-item__info strong {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--ink);
    margin-bottom: 0.15rem;
    line-height: 1.2;
}

.mini-event-item__info span {
    font-size: 0.65rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 0.25rem;
    line-height: 1.4;
}

.mini-event-item__info .info-location i {
    color: var(--brand);
    font-size: 0.55rem;
}

.mini-event-item__info .info-time i {
    color: var(--muted-light);
    font-size: 0.55rem;
}

.mini-event-item__info .info-category {
    background: var(--surface);
    padding: 0.05rem 0.4rem;
    border-radius: var(--radius-full);
    display: inline-flex;
    font-size: 0.55rem;
    color: var(--muted);
    width: fit-content;
    margin-top: 0.1rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.mini-event-item__info .info-category i {
    font-size: 0.5rem;
}

.mini-fav {
    width: 30px;
    height: 30px;
    align-self: center;
    flex-shrink: 0;
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
    
    .mini-event-item {
        flex-wrap: wrap;
        padding: 0.5rem;
    }
    
    .mini-event-item__image {
        width: 48px;
        height: 48px;
    }
    
    .mini-event-item__info strong {
        font-size: 0.78rem;
    }

    .active-filters {
        gap: 0.3rem;
    }
    
    .clear-filters-btn {
        width: 100%;
        justify-content: center;
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
    
    .mini-event-item__date {
        min-width: 36px;
    }
    
    .mini-event-item__date strong {
        font-size: 0.8rem;
    }
}
</style>