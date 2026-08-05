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
    { icon: 'pi-check-circle', titulo: 'Comunidad verificada', desc: 'Todos los asistentes son perfiles reales y verificados.' },
    { icon: 'pi-shield', titulo: 'Privacidad garantizada', desc: 'Tu privacidad es nuestra prioridad en cada experiencia.' },
    { icon: 'pi-lock', titulo: 'Acceso exclusivo', desc: 'Eventos diseñados solo para miembros seleccionados.' },
    { icon: 'pi-sparkles', titulo: 'Experiencias reales', desc: 'Momentos auténticos que conectan y trascienden.' },
];

const categoriaImagenes = {
    'Fiestas privadas': '/images/eventos/fiestas.png',
    'Jacuzzi': '/images/eventos/jacuzzi.png',
    'Club nights': '/images/eventos/club.png',
    'Eventos VIP': '/images/eventos/eventos_vip.png',
    'Viajes temáticos': '/images/eventos/viajes.png',
    'Cenas': '/images/eventos/cenas.png',
    'Cenas exclusivas': '/images/eventos/cenas.png',
    'Club': '/images/eventos/club.png',
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
    if (!categoria) return '/images/eventos/default.jpg';
    if (categoriaImagenes[categoria]) return categoriaImagenes[categoria];
    
    const categoriaLower = categoria.toLowerCase();
    for (const [key, value] of Object.entries(categoriaImagenes)) {
        if (key.toLowerCase().includes(categoriaLower) || categoriaLower.includes(key.toLowerCase())) {
            return value;
        }
    }
    return '/images/eventos/default.jpg';
}

// Eventos filtrados
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
    
    if (filtros.ciudad !== 'Todas') {
        filtered = filtered.filter(e => e.ciudad === filtros.ciudad);
    }
    
    if (filtros.fecha) {
        const fechaSeleccionada = new Date(filtros.fecha);
        filtered = filtered.filter(e => {
            if (!e.fecha_completa) return false;
            const fechaEvento = new Date(e.fecha_completa);
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
                            <span>{{ destacado.mes }}</span>
                        </div>
                        <div class="hero__featured-info">
                            <h2>{{ destacado.titulo }} <span v-if="destacado.vip" class="vip-tag">VIP</span></h2>
                            <p><i class="pi pi-map-marker"></i> {{ destacado.ciudad }}</p>
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
                                <span>✦ Sugerencias</span>
                                <span class="suggestions-count">{{ sugerencias.length }} resultados</span>
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
            <!-- CATEGORÍAS CON IMÁGENES -->
            <!-- ============================================================ -->
            <section v-if="categorias.length > 0" class="section">
                <div class="section__heading">
                    <h2>Explora por categoría</h2>
                    <Link href="/eventos/categorias" class="see-all">
                        Ver todas las categorías <i class="pi pi-chevron-right"></i>
                    </Link>
                </div>

                <div class="category-grid">
                    <Link 
                        v-for="cat in categorias" 
                        :key="cat.titulo" 
                        :href="`/eventos/categoria/${cat.titulo.toLowerCase().replace(/\s+/g, '-')}`"
                        class="category-card"
                    >
                        <img 
                            :src="getCategoriaImagen(cat.titulo)" 
                            :alt="cat.titulo"
                            @error="(e) => { e.target.src = '/images/eventos/default.jpg' }"
                        />
                        <div class="category-card__overlay"></div>
                        <div class="category-card__content">
                            <i class="pi" :class="cat.icon || 'pi-tag'"></i>
                            <span>{{ cat.titulo }}</span>
                        </div>
                    </Link>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- TODOS LOS EVENTOS + SIDEBAR -->
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
                            </div>
                            <div class="event-card__body">
                                <h3>{{ e.titulo }}</h3>
                                <p class="event-card__meta">
                                    <i class="pi pi-map-marker"></i> {{ e.ciudad }} &nbsp;
                                    <i class="pi pi-clock"></i> {{ e.hora }}
                                </p>
                                <p class="event-card__desc">{{ e.desc }}</p>
                                <div class="event-card__footer">
                                    <Link :href="`/eventos/${e.id}`" class="event-card__btn">
                                        Más información
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
                    <!-- PRÓXIMOS EVENTOS - MEJORADO -->
                    <!-- ============================================================ -->
                    <div class="sidebar-card sidebar-card--proximos">
                        <div class="sidebar-card__header">
                            <div class="sidebar-card__header-title">
                                <h3>Próximos eventos</h3>
                            </div>
                            <Link href="/eventos/recomendados" class="see-all">
                                Ver todos <i class="pi pi-chevron-right"></i>
                            </Link>
                        </div>

                        <div v-if="proximos.length === 0" class="empty-proximos">
                            <i class="pi pi-calendar-plus"></i>
                            <span>Pronto tendrás eventos disponibles</span>
                        </div>

                        <div v-else class="mini-event-list">
                            <div v-for="(e, index) in proximos" :key="e.id" class="mini-event-item" :style="{ animationDelay: `${index * 0.1}s` }">
                                <div class="mini-event-item__image">
                                    <img :src="getImageUrl(e.imagen)" :alt="e.titulo" />
                                    <div class="mini-event-item__status" :class="{ 'status--soon': e.proximo }">
                                        <span v-if="e.proximo">🔥 Pronto</span>
                                        <span v-else>📌</span>
                                    </div>
                                </div>
                                
                                <div class="mini-event-item__date">
                                    <strong>{{ e.dia }}</strong>
                                    <span>{{ e.mes }}</span>
                                </div>
                                
                                <div class="mini-event-item__info">
                                    <strong>{{ e.titulo }}</strong>
                                    <span class="info-location">
                                        <i class="pi pi-map-marker"></i> {{ e.lugar || e.ciudad }}
                                    </span>
                                    <span class="info-time">
                                        <i class="pi pi-clock"></i> {{ e.hora }}
                                    </span>
                                    <span v-if="e.categoria" class="info-category">
                                        <i class="pi pi-tag"></i> {{ e.categoria }}
                                    </span>
                                </div>
                                
                                <button class="favorite-btn mini-fav" @click="toggleFavorito(e)">
                                    <i class="pi" :class="e.favorito ? 'pi-heart-fill' : 'pi-heart'"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- BENEFICIOS -->
                    <!-- ============================================================ -->
                    <div class="sidebar-card">
                        <h3>✦ Beneficios</h3>
                        <div class="benefit-list">
                            <div v-for="b in beneficios" :key="b.titulo" class="benefit-item">
                                <span class="benefit-item__icon"><i class="pi" :class="b.icon"></i></span>
                                <div>
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
}

.section__heading--row {
    align-items: flex-end;
}

.section__heading-count {
    font-size: 0.78rem;
    color: var(--muted-light);
    margin-left: 0.5rem;
    font-weight: 400;
}

.section__heading-title {
    display: flex;
    align-items: baseline;
}

.see-all {
    color: var(--brand);
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    transition: all 0.3s ease;
}

.see-all:hover {
    color: var(--brand-dark);
    gap: 0.6rem;
}

/* =========================================================================
   CATEGORÍAS
   ========================================================================= */
.category-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 1rem;
}

@media (max-width: 1024px) {
    .category-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 640px) {
    .category-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.category-card {
    position: relative;
    border-radius: var(--radius-md);
    overflow: hidden;
    aspect-ratio: 4/5;
    cursor: pointer;
    display: block;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
}

.category-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.category-card img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.category-card:hover img {
    transform: scale(1.1);
}

.category-card__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(0deg, rgba(0,0,0,0.75) 20%, rgba(0,0,0,0.15) 70%);
}

.category-card__content {
    position: relative;
    z-index: 2;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    gap: 0.4rem;
    padding: 1.2rem;
    color: var(--white);
    text-align: center;
}

.category-card__content i {
    font-size: 1.3rem;
    opacity: 0.8;
}

.category-card__content span {
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.02em;
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
}

.sidebar-card__header h3 {
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0;
    color: var(--ink);
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
    font-size: 0.85rem;
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
    position: relative;
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

.mini-event-item__status {
    position: absolute;
    top: -4px;
    right: -4px;
    background: var(--brand);
    color: var(--white);
    font-size: 0.5rem;
    font-weight: 700;
    padding: 0.1rem 0.4rem;
    border-radius: var(--radius-full);
    border: 2px solid var(--white);
    white-space: nowrap;
}

.mini-event-item__status.status--soon {
    background: linear-gradient(135deg, #FF6B6B, #EE5A24);
    animation: pulse-badge 2s infinite;
}

@keyframes pulse-badge {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
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
    font-size: 0.58rem;
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
    font-size: 0.68rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 0.25rem;
    line-height: 1.4;
}

.mini-event-item__info .info-location i {
    color: var(--brand);
    font-size: 0.6rem;
}

.mini-event-item__info .info-time i {
    color: var(--muted-light);
    font-size: 0.6rem;
}

.mini-event-item__info .info-category {
    background: var(--surface);
    padding: 0.05rem 0.4rem;
    border-radius: var(--radius-full);
    display: inline-flex;
    font-size: 0.6rem;
    color: var(--muted);
    width: fit-content;
    margin-top: 0.1rem;
}

.mini-event-item__info .info-category i {
    font-size: 0.55rem;
}

.mini-fav {
    width: 30px;
    height: 30px;
    align-self: center;
    flex-shrink: 0;
}

/* =========================================================================
   BENEFITS
   ========================================================================= */
.benefit-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.benefit-item {
    display: flex;
    gap: 0.7rem;
    align-items: flex-start;
}

.benefit-item__icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-sm);
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.85rem;
    transition: all 0.3s ease;
}

.benefit-item:hover .benefit-item__icon {
    background: var(--brand);
    color: var(--white);
    transform: scale(1.05);
}

.benefit-item strong {
    display: block;
    font-size: 0.85rem;
}

.benefit-item span {
    font-size: 0.75rem;
    color: var(--muted);
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
    
    .category-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .mini-event-item__date {
        min-width: 36px;
    }
    
    .mini-event-item__date strong {
        font-size: 0.8rem;
    }
}
</style>