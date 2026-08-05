<script setup>
import { computed, reactive, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
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
            apodo: 'usuario',
            avatar: '/images/shared/avatar-default.jpg',
            verificado: false,
            rol: 'usuario',
            estado: 'incompleto'
        })
    },
    perfiles: {
        type: Array,
        default: () => []
    },
    matchesRecientes: {
        type: Array,
        default: () => []
    },
    actividadZona: {
        type: Array,
        default: () => []
    }
});

/* ---------------------------------------------------------------
 * Puntos de confianza (estáticos)
 * --------------------------------------------------------------- */
const confianza = [
    { icon: 'pi-shield', titulo: 'Perfiles verificados para una experiencia más confiable.' },
    { icon: 'pi-lock', titulo: 'Tu ubicación exacta nunca se comparte.' },
];

/* ---------------------------------------------------------------
 * Filtros rápidos
 * --------------------------------------------------------------- */
const filtros = reactive({
    soloVerificados: true,
    tipoPerfil: 'todos',
    cercaDeMi: false,
    modoActivo: true,
});

/* ---------------------------------------------------------------
 * Stack de perfiles para descubrir (desde props)
 * --------------------------------------------------------------- */
const indiceActual = ref(0);
const perfilesLista = computed(() => props.perfiles || []);

const perfilActual = computed(() => perfilesLista.value[indiceActual.value] ?? null);
const perfilAnterior = computed(() => perfilesLista.value[indiceActual.value - 1] ?? null);
const perfilSiguiente = computed(() => perfilesLista.value[indiceActual.value + 1] ?? null);

function siguientePerfil() {
    if (indiceActual.value < perfilesLista.value.length - 1) indiceActual.value++;
}

function pasar() {
    siguientePerfil();
}

function conectar() {
    siguientePerfil();
}

function destacar() {
    // TODO: Implementar lógica de destacar
}

/* ---------------------------------------------------------------
 * Matches recientes (desde props) - Los 5 más recientes
 * --------------------------------------------------------------- */
const matches = computed(() => {
    if (!props.matchesRecientes || props.matchesRecientes.length === 0) {
        return [];
    }
    // Tomar los 5 más recientes
    return props.matchesRecientes.slice(0, 5);
});

/* ---------------------------------------------------------------
 * Actividad en tu zona (desde props)
 * --------------------------------------------------------------- */
const actividad = computed(() => props.actividadZona || []);

// Función para obtener la imagen de perfil
function getAvatarUrl(avatar) {
    if (!avatar) return '/images/shared/avatar-default.jpg';
    if (avatar.startsWith('http://') || avatar.startsWith('https://')) return avatar;
    if (avatar.startsWith('/storage/') || avatar.startsWith('/images/')) return avatar;
    return '/storage/' + avatar.replace(/^\/+/, '');
}
</script>

<template>
    <Head title="Descubrir" />

    <AppLayout activeNav="descubrir">
        <div class="descubrir-page">
            <!-- ============================================================ -->
            <!-- ENCABEZADO -->
            <!-- ============================================================ -->
            <section class="page-heading">
                <div>
                    <h1>Descubre conexiones <span>reales y compatibles</span></h1>
                </div>
                <div class="page-heading__trust">
                    <div v-for="item in confianza" :key="item.titulo" class="trust-item">
                        <span class="trust-item__icon"><i class="pi" :class="item.icon"></i></span>
                        <span>{{ item.titulo }}</span>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- BARRA DE FILTROS -->
            <!-- ============================================================ -->
            <section class="filters-bar">
                <label class="filter-toggle">
                    <span class="toggle-switch">
                        <input type="checkbox" v-model="filtros.soloVerificados" />
                        <span class="toggle-slider"></span>
                    </span>
                    Solo verificados
                </label>

                <button
                    class="filter-pill"
                    :class="{ selected: filtros.tipoPerfil === 'personal' }"
                    @click="filtros.tipoPerfil = 'personal'"
                >
                    <i class="pi pi-user"></i> Personal
                </button>
                <button
                    class="filter-pill"
                    :class="{ selected: filtros.tipoPerfil === 'pareja' }"
                    @click="filtros.tipoPerfil = 'pareja'"
                >
                    <i class="pi pi-users"></i> Pareja
                </button>
                <button
                    class="filter-pill"
                    :class="{ selected: filtros.tipoPerfil === 'todos' }"
                    @click="filtros.tipoPerfil = 'todos'"
                >
                    <i class="pi pi-filter"></i> Todos
                </button>
                <button
                    class="filter-pill"
                    :class="{ selected: filtros.cercaDeMi }"
                    @click="filtros.cercaDeMi = !filtros.cercaDeMi"
                >
                    <i class="pi pi-map-marker"></i> Cerca de mí
                </button>

                <label class="filter-toggle">
                    Modo activo
                    <span class="toggle-switch">
                        <input type="checkbox" v-model="filtros.modoActivo" />
                        <span class="toggle-slider"></span>
                    </span>
                    <i class="pi pi-info-circle info-icon"></i>
                </label>

                <button class="filter-btn">
                    <i class="pi pi-sliders-h"></i> Filtros
                    <span class="filter-badge">2</span>
                </button>
            </section>

            <!-- ============================================================ -->
            <!-- STACK DE PERFILES -->
            <!-- ============================================================ -->
            <section class="swipe-area">
                <div v-if="perfilesLista.length === 0" class="empty-state">
                    <i class="pi pi-users"></i>
                    <h3>No hay perfiles disponibles</h3>
                    <p>Pronto aparecerán nuevos perfiles para ti.</p>
                </div>

                <div v-else class="swipe-stack">
                    <div v-if="perfilAnterior" class="swipe-card swipe-card--side swipe-card--left">
                        <img :src="getAvatarUrl(perfilAnterior.imagen)" :alt="perfilAnterior.nombre" />
                        <span class="side-badge">Verificado</span>
                    </div>

                    <div v-if="perfilActual" class="swipe-card swipe-card--main">
                        <img :src="getAvatarUrl(perfilActual.imagen)" :alt="perfilActual.nombre" />
                        <div class="swipe-card__gradient"></div>

                        <span class="verified-badge"><i class="pi pi-check-circle"></i> Verificado</span>
                        <span v-if="perfilActual.enLinea" class="online-badge"><i class="pi pi-circle-fill"></i> En línea</span>

                        <div class="swipe-card__content">
                            <h2>{{ perfilActual.nombre }} <i class="pi pi-verified"></i></h2>
                            <p class="swipe-card__location"><i class="pi pi-map-marker"></i> {{ perfilActual.ciudad }} &nbsp;•&nbsp; <i class="pi pi-users"></i> {{ perfilActual.tipo }}</p>
                            <p class="swipe-card__desc">{{ perfilActual.descripcion }}</p>

                            <div v-if="perfilActual.intereses && perfilActual.intereses.length > 0" class="swipe-card__tags">
                                <span v-for="t in perfilActual.intereses" :key="t.label" class="tag"><i class="pi" :class="t.icon"></i> {{ t.label }}</span>
                            </div>
                            <div v-if="perfilActual.interesesExtra && perfilActual.interesesExtra.length > 0" class="swipe-card__tags">
                                <span v-for="t in perfilActual.interesesExtra" :key="t.label" class="tag"><i class="pi" :class="t.icon"></i> {{ t.label }}</span>
                            </div>

                            <div class="swipe-card__footer">
                                <span class="swipe-card__distance"><i class="pi pi-map-marker"></i> {{ perfilActual.distancia || 'Cerca de ti' }}</span>
                                <span class="swipe-card__compat">Compatibilidad <strong>{{ perfilActual.compatibilidad || 0 }}%</strong></span>
                            </div>

                            <div class="swipe-card__dots">
                                <span
                                    v-for="(p, i) in perfilesLista"
                                    :key="i"
                                    class="dot"
                                    :class="{ active: i === indiceActual }"
                                ></span>
                            </div>
                        </div>
                    </div>

                    <div v-if="perfilSiguiente" class="swipe-card swipe-card--side swipe-card--right">
                        <img :src="getAvatarUrl(perfilSiguiente.imagen)" :alt="perfilSiguiente.nombre" />
                        <span class="side-badge side-badge--online"><i class="pi pi-circle-fill"></i> En línea</span>
                    </div>
                </div>

                <div v-if="perfilesLista.length > 0" class="swipe-actions">
                    <button class="swipe-btn swipe-btn--pass" @click="pasar"><i class="pi pi-times"></i></button>
                    <button class="swipe-btn swipe-btn--star" @click="destacar"><i class="pi pi-star-fill"></i></button>
                    <button class="swipe-btn swipe-btn--like" @click="conectar"><i class="pi pi-heart-fill"></i></button>
                </div>

                <div v-if="perfilesLista.length > 0" class="swipe-hints">
                    <span class="swipe-hint"><i class="pi pi-arrow-left"></i> Desliza a la izquierda<br />para pasar</span>
                    <span class="swipe-hint swipe-hint--center">Conexión express<br />Destaca tu interés</span>
                    <span class="swipe-hint swipe-hint--right">Desliza a la derecha<br />para conectar <i class="pi pi-arrow-right"></i></span>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- MATCHES + ACTIVIDAD -->
            <!-- ============================================================ -->
            <section class="bottom-grid">
                <!-- =========================================== -->
                <!-- TUS MATCHES RECIENTES -->
                <!-- =========================================== -->
                <div class="matches-card">
                    <div class="matches-card__header">
                        <div class="matches-card__header-left">
                            <span class="matches-card__icon"><i class="pi pi-heart-fill"></i></span>
                            <h3>Tus matches recientes</h3>
                        </div>
                        <Link href="/matches" class="see-all">
                            Ver todos <i class="pi pi-chevron-right"></i>
                        </Link>
                    </div>

                    <div v-if="matches.length === 0" class="empty-matches">
                        <i class="pi pi-heart"></i>
                        <span>Sin matches aún</span>
                        <p>Conecta con personas para verlas aquí</p>
                    </div>

                    <div v-else class="matches-grid">
                        <div v-for="m in matches" :key="m.nombre" class="mini-match">
                            <img :src="getAvatarUrl(m.imagen)" :alt="m.nombre" />
                            <span class="mini-match__verified">
                                <i class="pi pi-check-circle"></i> Verificado
                            </span>
                            <div class="mini-match__info">
                                <strong>{{ m.nombre }}</strong>
                                <span>
                                    <i class="pi pi-map-marker"></i> {{ m.distancia || 'Cerca de ti' }}
                                </span>
                            </div>
                            <button class="mini-match__chat">
                                <i class="pi pi-comment"></i>
                            </button>
                        </div>

                        <!-- Botón para ver más -->
                        <button class="mini-match mini-match--cta">
                            <i class="pi pi-plus-circle"></i>
                            <span>Ver más<br />perfiles</span>
                        </button>
                    </div>
                </div>

                <!-- =========================================== -->
                <!-- ACTIVIDAD EN TU ZONA -->
                <!-- =========================================== -->
                <div class="activity-card">
                    <div class="activity-card__header">
                        <h3>Actividad en tu zona</h3>
                        <span class="updated">Actualizado ahora</span>
                    </div>
                    <div v-for="a in actividad" :key="a.titulo" class="activity-row">
                        <span class="activity-row__icon"><i class="pi" :class="a.icon"></i></span>
                        <span class="activity-row__title">{{ a.titulo }}</span>
                        <div class="activity-row__value">
                            <strong>{{ a.valor }}</strong>
                            <span>{{ a.extra }}</span>
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
.descubrir-page {
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
  --shadow-card: 0 4px 16px rgba(0, 0, 0, 0.08);

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

.descubrir-page * {
  box-sizing: border-box;
}

.descubrir-page img {
  max-width: 100%;
  display: block;
}

/* =========================================================================
   PAGE HEADING
   ========================================================================= */
.page-heading {
    max-width: 1400px;
    margin: 1.75rem auto 0;
    padding: 0 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.25rem;
}

.page-heading h1 {
    font-family: var(--font-serif);
    font-size: 1.7rem;
    font-weight: 400;
    margin: 0;
    line-height: 1.25;
}

.page-heading h1 span {
    display: block;
    color: var(--brand);
    font-weight: 700;
    font-style: italic;
}

.page-heading__trust {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.trust-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.8rem;
    color: var(--ink-soft);
    max-width: 220px;
}

.trust-item__icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* =========================================================================
   FILTERS BAR
   ========================================================================= */
.filters-bar {
    max-width: 1400px;
    margin: 1.5rem auto 0;
    padding: 0.75rem 1.25rem;
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    box-shadow: var(--shadow);
}

.filter-toggle {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--ink);
    cursor: pointer;
}

.toggle-switch {
    position: relative;
    width: 38px;
    height: 21px;
    display: inline-block;
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
    border-radius: var(--radius-full);
    transition: 0.2s;
}

.toggle-switch input:checked + .toggle-slider {
    background: var(--brand);
}

.toggle-slider::before {
    content: '';
    position: absolute;
    width: 15px;
    height: 15px;
    left: 3px;
    top: 3px;
    background: var(--white);
    border-radius: 50%;
    transition: 0.2s;
}

.toggle-switch input:checked + .toggle-slider::before {
    transform: translateX(17px);
}

.info-icon {
    color: var(--muted-light);
    font-size: 0.85rem;
}

.filter-pill {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    border: 1.5px solid var(--line);
    border-radius: var(--radius-full);
    padding: 0.4rem 0.9rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink-soft);
    background: var(--white);
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-pill:hover {
    border-color: var(--brand-soft);
}

.filter-pill.selected {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
}

.filter-btn {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border: 1.5px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.45rem 0.9rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink);
    background: var(--white);
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.filter-btn:hover {
    border-color: var(--brand-soft);
}

.filter-badge {
    background: var(--brand);
    color: var(--white);
    font-size: 0.6rem;
    font-weight: 700;
    padding: 0.05rem 0.5rem;
    border-radius: var(--radius-full);
}

/* =========================================================================
   SWIPE AREA
   ========================================================================= */
.swipe-area {
    max-width: 1400px;
    margin: 2.5rem auto 0;
    padding: 0 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.swipe-stack {
    position: relative;
    width: 100%;
    max-width: 500px;
    height: 620px;
    margin-bottom: 2rem;
}

.swipe-card {
    position: absolute;
    border-radius: 20px;
    overflow: hidden;
}

.swipe-card--main {
    inset: 0;
    z-index: 3;
    background: #111;
    box-shadow: 0 20px 40px rgba(0,0,0,0.18);
}

.swipe-card--main img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 1;
}

.swipe-card__gradient {
    position: absolute;
    inset: 0;
    z-index: 2;
    background: linear-gradient(0deg, rgba(0,0,0,0.92) 25%, rgba(0,0,0,0.05) 65%);
}

.swipe-card--side {
    top: 30px;
    bottom: 30px;
    width: 82%;
    z-index: 1;
    opacity: 0.85;
    filter: brightness(0.55);
}

.swipe-card--side img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.swipe-card--left {
    left: -14%;
}

.swipe-card--right {
    right: -14%;
}

.side-badge {
    position: absolute;
    top: 14px;
    left: 14px;
    background: rgba(255,255,255,0.2);
    color: var(--white);
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: var(--radius-full);
    backdrop-filter: blur(2px);
}

.side-badge--online {
    left: auto;
    right: 14px;
    background: rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.side-badge--online i {
    color: #4ade80;
    font-size: 0.5rem;
}

.verified-badge {
    position: absolute;
    top: 16px;
    left: 16px;
    z-index: 3;
    background: var(--white);
    color: #1c7a3c;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.25rem 0.7rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.online-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    z-index: 3;
    background: rgba(0,0,0,0.5);
    color: var(--white);
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.25rem 0.7rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.online-badge i {
    color: #4ade80;
    font-size: 0.5rem;
}

.swipe-card__content {
    position: absolute;
    z-index: 3;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 1.5rem;
    color: var(--white);
}

.swipe-card__content h2 {
    font-family: var(--font-serif);
    font-size: 1.5rem;
    margin: 0 0 0.4rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.swipe-card__content h2 i {
    color: #4ade80;
    font-size: 1.05rem;
}

.swipe-card__location {
    font-size: 0.85rem;
    color: #e0e0e2;
    margin: 0 0 0.6rem;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.swipe-card__desc {
    font-size: 0.85rem;
    color: #e0e0e2;
    line-height: 1.5;
    margin: 0 0 0.9rem;
}

.swipe-card__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.tag {
    background: rgba(255,255,255,0.14);
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: var(--radius-full);
    padding: 0.3rem 0.7rem;
    font-size: 0.72rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.swipe-card__footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 1rem 0 0.9rem;
    font-size: 0.85rem;
}

.swipe-card__distance {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    color: #e0e0e2;
}

.swipe-card__compat {
    color: #e0e0e2;
}

.swipe-card__compat strong {
    color: var(--brand);
    background: var(--white);
    padding: 0.1rem 0.5rem;
    border-radius: 6px;
    margin-left: 0.3rem;
}

.swipe-card__dots {
    display: flex;
    justify-content: center;
    gap: 0.4rem;
}

.dot {
    width: 24px;
    height: 4px;
    border-radius: 4px;
    background: rgba(255,255,255,0.3);
}

.dot.active {
    background: var(--brand);
}

/* =========================================================================
   EMPTY STATE
   ========================================================================= */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--white);
    border-radius: var(--radius-md);
    border: 1px solid var(--line);
    width: 100%;
    max-width: 500px;
}

.empty-state i {
    font-size: 3rem;
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
   SWIPE ACTIONS
   ========================================================================= */
.swipe-actions {
    display: flex;
    align-items: center;
    gap: 1.75rem;
    margin-bottom: 1rem;
}

.swipe-btn {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    border: none;
    background: var(--white);
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.swipe-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 24px rgba(0,0,0,0.16);
}

.swipe-btn--pass {
    color: var(--brand);
    width: 60px;
    height: 60px;
}

.swipe-btn--pass:hover {
    background: #fee8ea;
}

.swipe-btn--star {
    color: #f2a33c;
    width: 52px;
    height: 52px;
    font-size: 1.15rem;
}

.swipe-btn--star:hover {
    background: #fef6e8;
}

.swipe-btn--like {
    color: var(--brand);
    width: 60px;
    height: 60px;
}

.swipe-btn--like:hover {
    background: #fee8ea;
}

/* =========================================================================
   SWIPE HINTS
   ========================================================================= */
.swipe-hints {
    display: flex;
    justify-content: center;
    gap: 3.5rem;
    text-align: center;
    font-size: 0.75rem;
    color: var(--muted);
}

.swipe-hint {
    max-width: 130px;
}

.swipe-hint i {
    display: block;
    margin-bottom: 0.2rem;
}

.swipe-hint--center {
    color: #f2a33c;
    font-weight: 600;
}

.swipe-hint--center i {
    color: #f2a33c;
}

/* =========================================================================
   BOTTOM GRID
   ========================================================================= */
.bottom-grid {
    max-width: 1400px;
    margin: 2.5rem auto 0;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: 1.4fr 1fr;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1024px) {
    .bottom-grid {
        grid-template-columns: 1fr;
    }
}

/* =========================================================================
   MATCHES CARD MEJORADA
   ========================================================================= */
.matches-card,
.activity-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}

.matches-card:hover,
.activity-card:hover {
    box-shadow: var(--shadow-hover);
}

.matches-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
}

.matches-card__header-left {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.matches-card__icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.matches-card__header h3 {
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0;
}

.see-all {
    color: var(--brand);
    font-size: 0.78rem;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    transition: all 0.3s ease;
}

.see-all:hover {
    color: var(--brand-dark);
    gap: 0.6rem;
}

/* =========================================================================
   EMPTY MATCHES
   ========================================================================= */
.empty-matches {
    text-align: center;
    padding: 2.5rem 0.5rem;
    color: var(--muted);
}

.empty-matches i {
    font-size: 2.5rem;
    color: var(--muted-light);
    display: block;
    margin-bottom: 0.5rem;
}

.empty-matches span {
    font-size: 0.95rem;
    font-weight: 600;
    display: block;
}

.empty-matches p {
    font-size: 0.8rem;
    margin: 0.2rem 0 0;
}

/* =========================================================================
   MATCHES GRID
   ========================================================================= */
.matches-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

@media (max-width: 640px) {
    .matches-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.mini-match {
    position: relative;
    border-radius: var(--radius-sm);
    overflow: hidden;
    aspect-ratio: 3/4;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.mini-match:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.mini-match img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.mini-match:hover img {
    transform: scale(1.05);
}

.mini-match__verified {
    position: absolute;
    top: 8px;
    left: 8px;
    background: rgba(255,255,255,0.92);
    color: #1c7a3c;
    font-size: 0.55rem;
    font-weight: 700;
    padding: 0.15rem 0.45rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.2rem;
    backdrop-filter: blur(4px);
}

.mini-match__info {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 0.7rem 0.6rem 0.6rem;
    background: linear-gradient(0deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 100%);
    color: var(--white);
}

.mini-match__info strong {
    display: block;
    font-size: 0.78rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 0.15rem;
}

.mini-match__info span {
    font-size: 0.62rem;
    color: #d8d8dc;
    display: flex;
    align-items: center;
    gap: 0.2rem;
}

.mini-match__chat {
    position: absolute;
    bottom: 8px;
    right: 8px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--brand);
    color: var(--white);
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    opacity: 0;
    transform: scale(0.8);
}

.mini-match:hover .mini-match__chat {
    opacity: 1;
    transform: scale(1);
}

.mini-match__chat:hover {
    background: var(--brand-dark);
    transform: scale(1.1) !important;
}

.mini-match__chat i {
    font-size: 0.75rem;
}

/* =========================================================================
   MINI MATCH CTA
   ========================================================================= */
.mini-match--cta {
    border: 2px dashed var(--line);
    background: var(--surface);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    cursor: pointer;
    color: var(--brand);
    transition: all 0.3s ease;
    aspect-ratio: 3/4;
    border-radius: var(--radius-sm);
}

.mini-match--cta:hover {
    border-color: var(--brand);
    background: var(--brand-soft);
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(200, 30, 58, 0.1);
}

.mini-match--cta i {
    font-size: 1.5rem;
    color: var(--brand);
}

.mini-match--cta span {
    font-size: 0.7rem;
    color: var(--ink-soft);
    text-align: center;
    line-height: 1.3;
}

/* =========================================================================
   ACTIVITY CARD
   ========================================================================= */
.activity-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.1rem;
}

.activity-card__header h3 {
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0;
}

.updated {
    font-size: 0.72rem;
    color: var(--muted-light);
}

.activity-row {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f2;
    transition: all 0.3s ease;
}

.activity-row:last-child {
    border-bottom: none;
}

.activity-row:hover {
    background: var(--surface);
    margin: 0 -0.5rem;
    padding: 0.75rem 0.5rem;
    border-radius: var(--radius-sm);
}

.activity-row__icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.activity-row:hover .activity-row__icon {
    background: var(--brand);
    color: var(--white);
}

.activity-row__title {
    flex: 1;
    font-size: 0.82rem;
    color: var(--ink);
}

.activity-row__value {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.activity-row__value strong {
    font-size: 0.92rem;
    color: var(--brand);
}

.activity-row__value span {
    font-size: 0.68rem;
    color: var(--muted-light);
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
    .filters-bar {
        flex-wrap: wrap;
    }
    
    .filter-btn {
        margin-left: 0;
    }
}

@media (max-width: 768px) {
    .page-heading {
        flex-direction: column;
        align-items: flex-start;
        padding: 0 1rem;
    }
    
    .page-heading__trust {
        gap: 1rem;
    }
    
    .filters-bar {
        padding: 0.75rem 1rem;
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }
    
    .filter-toggle {
        justify-content: space-between;
    }
    
    .swipe-area {
        padding: 0 1rem;
    }
    
    .swipe-stack {
        height: 480px;
    }
    
    .swipe-card--side {
        display: none;
    }
    
    .swipe-actions {
        gap: 1rem;
    }
    
    .swipe-btn {
        width: 54px;
        height: 54px;
        font-size: 1.1rem;
    }
    
    .swipe-btn--pass,
    .swipe-btn--like {
        width: 50px;
        height: 50px;
    }
    
    .swipe-btn--star {
        width: 44px;
        height: 44px;
        font-size: 1rem;
    }
    
    .swipe-hints {
        flex-direction: column;
        gap: 0.5rem;
        align-items: center;
    }
    
    .bottom-grid {
        padding: 0 1rem;
    }
    
    .matches-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .swipe-card__content {
        padding: 1rem;
    }
    
    .swipe-card__content h2 {
        font-size: 1.2rem;
    }
    
    .swipe-card__desc {
        font-size: 0.78rem;
    }
    
    .swipe-card__footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.3rem;
    }
    
    .swipe-hints {
        font-size: 0.7rem;
    }
    
    .matches-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .mini-match__chat {
        opacity: 1;
        transform: scale(1);
        width: 28px;
        height: 28px;
    }
    
    .mini-match__chat i {
        font-size: 0.65rem;
    }
}
</style>