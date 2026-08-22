<template>
    <Head title="Mi perfil de creador" />

    <AppLayout active-nav="comunidad" :usuario="usuario" :notificaciones="5" :favoritos="2" :mensajes="3">
        <div class="perfil-creador-page">
            <!-- ============================================================ -->
            <!-- TÍTULO -->
            <!-- ============================================================ -->
            <div class="page-heading">
                <h1>Mi perfil de creador</h1>
                <p>Gestiona tu perfil, contenido y comunidad.</p>
            </div>

            <div class="content-grid">
                <div class="main-column">
                    <!-- ============================================================ -->
                    <!-- TARJETA DE PERFIL -->
                    <!-- ============================================================ -->
                    <section class="profile-card">
                        <div class="profile-card__body">
                            <div class="profile-card__avatar-wrapper">
                                <img 
                                    :src="perfil.avatar || '/images/shared/avatar-default.jpg'"
                                    :alt="perfil.nombre || 'Creador'" 
                                    class="profile-card__avatar"
                                    @error="handleImageError" 
                                />
                            </div>

                            <div class="profile-card__info">
                                <div class="profile-card__name-row">
                                    <h2>
                                        {{ perfil.nombre || 'Creador' }}
                                        <span v-if="creadorEsPremium" class="premium-chip">Premium</span>
                                        <span v-if="usuario?.verificado" class="verified-chip">
                                            <i class="pi pi-check-circle"></i> Verificado
                                        </span>
                                    </h2>
                                </div>
                                <p class="profile-card__bio">{{ perfil.bio || 'Comparto contenido exclusivo para mi comunidad.' }}
                                </p>
                            </div>

                            <div class="profile-card__actions">
                                <button class="action-btn" @click="editarPerfil">
                                    <i class="pi pi-pencil"></i> Editar perfil
                                </button>
                                <button class="action-btn" @click="compartirPerfil">
                                    <i class="pi pi-share-alt"></i> Compartir
                                </button>
                                <PvButton label="Gestionar suscripción" icon="pi pi-crown" @click="gestionarSuscripcion" />
                            </div>
                        </div>

                        <div class="profile-card__stats">
                            <div class="stat-item">
                                <strong>{{ perfil.seguidores || '0' }}</strong>
                                <span>Seguidores</span>
                            </div>
                            <div class="stat-item">
                                <strong>{{ perfil.suscriptores || '0' }}</strong>
                                <span>Suscriptores</span>
                            </div>
                            <div class="stat-item">
                                <strong>{{ perfil.publicaciones || 0 }}</strong>
                                <span>Publicaciones</span>
                            </div>
                            <div class="stat-item">
                                <strong>{{ perfil.meGusta || '0' }}</strong>
                                <span>Me gusta</span>
                            </div>
                        </div>
                    </section>

                    <!-- ============================================================ -->
                    <!-- TABS -->
                    <!-- ============================================================ -->
                    <div class="tabs-nav">
                        <button v-for="tab in tabs" :key="tab.key" class="tabs-nav__item"
                            :class="{ active: tabActivo === tab.key }" @click="tabActivo = tab.key">
                            {{ tab.label }}
                            <i v-if="tab.icon" class="pi" :class="tab.icon"></i>
                        </button>
                    </div>

                    <!-- ============================================================ -->
                    <!-- FEED DE PUBLICACIONES -->
                    <!-- ============================================================ -->
                    <template v-if="tabActivo === 'publicaciones'">
                        <div v-if="!publicaciones || publicaciones.length === 0" class="empty-state">
                            <i class="pi pi-inbox"></i>
                            <h3>No tienes publicaciones aún</h3>
                            <p>Comienza a compartir contenido con tu comunidad.</p>
                            <button class="btn btn--primary" @click="irAPublicar">
                                <i class="pi pi-plus"></i> Crear primera publicación
                            </button>
                        </div>

                        <article v-for="post in (publicaciones || [])" :key="post.id || post.titulo || Math.random()"
                            class="post-card">
                            <div class="post-card__header">
                                <PvAvatar :image="perfil.avatar || '/images/shared/avatar-default.jpg'" shape="circle"
                                    size="large" />
                                <div class="post-card__author">
                                    <strong>
                                        {{ perfil.nombre || 'Creador' }}
                                        <span v-if="creadorEsPremium" class="premium-chip">Premium</span>
                                    </strong>
                                    <span>{{ post.created_at || 'Hace 5 min' }}</span>
                                </div>
                                <span v-if="post.es_premium" class="post-card__badge">
                                    <i class="pi pi-lock"></i> Exclusivo
                                </span>
                                <button class="post-card__more"><i class="pi pi-ellipsis-h"></i></button>
                            </div>

                            <h3 v-if="post.titulo">{{ post.titulo }}</h3>
                            <p v-if="post.descripcion" class="post-card__text">{{ post.descripcion }}</p>

                            <div v-if="post.archivos && post.archivos.length > 0" class="post-card__media">
                                <img v-if="post.archivos[0]?.url" :src="post.archivos[0].url"
                                    :alt="post.titulo || 'Contenido'" @error="handleImageError" />
                                <div v-if="post.es_premium && configuracion?.mostrar_vista_previa" class="premium-overlay">
                                    <span class="premium-overlay__lock"><i class="pi pi-lock"></i></span>
                                    <strong>Suscríbete para ver</strong>
                                    <span>contenido exclusivo</span>
                                </div>
                                <span v-if="post.precio > 0" class="post-card__price">${{ Number(post.precio).toFixed(2) }} USD</span>
                            </div>

                            <div class="post-card__footer">
                                <button><i class="pi pi-heart"></i> {{ post.total_likes || 0 }}</button>
                                <button><i class="pi pi-comment"></i> {{ post.total_comentarios || 0 }}</button>
                            </div>
                        </article>
                    </template>

                    <template v-else-if="tabActivo === 'galeria'">
                        <section class="section-block">
                            <h3>Galería de fotos</h3>
                            <div v-if="!fotosPerfil || fotosPerfil.length === 0" class="empty-gallery">
                                <i class="pi pi-images"></i>
                                <p>No tienes fotos en tu galería aún.</p>
                            </div>
                            <div v-else class="gallery-grid">
                                <div v-for="foto in fotosPerfil" :key="foto.id" class="gallery-item">
                                    <img :src="foto.url" :alt="'Foto ' + foto.id" @error="handleImageError" />
                                    <span v-if="foto.es_principal" class="gallery-item__badge">Principal</span>
                                </div>
                            </div>
                        </section>
                    </template>

                    <template v-else-if="tabActivo === 'acerca'">
                        <section class="section-block">
                            <h3>Acerca de {{ perfil.nombre || 'Creador' }}</h3>
                            <p class="about-text">{{ perfil.bio || 'Comparto contenido exclusivo para mi comunidad.' }}</p>
                            <h4>Categorías</h4>
                            <div class="chip-group">
                                <span v-for="cat in categoriasCreador" :key="cat" class="chip">{{ cat }}</span>
                            </div>
                            <h4 v-if="configuracion">Información de monetización</h4>
                            <div v-if="configuracion" class="about-stats">
                                <div class="about-stat">
                                    <span>Modelo de ingresos</span>
                                    <strong>{{ configuracion.modelo_ingresos || 'Suscripción' }}</strong>
                                </div>
                                <div class="about-stat">
                                    <span>Precio</span>
                                    <strong>${{ configuracion.precio_personalizado || 199.99 }} MXN</strong>
                                </div>
                                <div class="about-stat">
                                    <span>Frecuencia</span>
                                    <strong>{{ configuracion.frecuencia_pago || 'Mensual' }}</strong>
                                </div>
                            </div>
                        </section>
                    </template>
                </div>

                <!-- SIDEBAR -->
                <aside class="sidebar-column">
                    <!-- Suscripciones activas -->
                    <div v-if="suscripcionesActivas && suscripcionesActivas.length > 0" class="sidebar-card">
                        <div class="sidebar-card__header-row">
                            <h3><i class="pi pi-users"></i> Suscripciones activas</h3>
                            <a href="#" class="see-all" @click.prevent="verTodasSuscripciones">Ver todas <i class="pi pi-arrow-right"></i></a>
                        </div>
                        <div class="subscriber-list">
                            <div v-for="s in suscripcionesActivas" :key="s.nombre || Math.random()" class="subscriber-item">
                                <PvAvatar :image="s.avatar || '/images/shared/avatar-default.jpg'" shape="circle" />
                                <div class="subscriber-item__info">
                                    <strong>{{ s.nombre || 'Usuario' }}</strong>
                                    <span>Renovación: {{ s.renovacion || 'Próximamente' }}</span>
                                </div>
                                <PvTag :value="s.plan || 'Premium'" :severity="s.plan === 'VIP' ? 'danger' : 'secondary'" />
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje cuando no hay suscripciones -->
                    <div v-else class="sidebar-card">
                        <h3><i class="pi pi-users"></i> Suscripciones activas</h3>
                        <p class="empty-subscriptions">Aún no tienes suscriptores activos.</p>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Footer from '@/Components/AppFooter.vue';
import PvButton from 'primevue/button';
import PvAvatar from 'primevue/avatar';
import PvTag from 'primevue/tag';

const props = defineProps({
    usuario: {
        type: Object,
        default: () => ({
            nombre: 'Invitado',
            avatar: '/images/shared/avatar-default.jpg',
            verificado: false
        })
    },
    perfil: {
        type: Object,
        default: () => ({
            avatar: '/images/shared/avatar-default.jpg',
            nombre: 'Creador',
            bio: 'Comparto contenido exclusivo para mi comunidad.',
            seguidores: '0',
            suscriptores: '0',
            publicaciones: 0,
            meGusta: '0',
            categorias: []
        })
    },
    publicaciones: {
        type: Array,
        default: () => []
    },
    configuracionMonetizacion: {
        type: Object,
        default: null
    },
    fotosPerfil: {
        type: Array,
        default: () => []
    },
    footerColumnas: {
        type: Object,
        default: () => ({})
    },
    estadisticas: {
        type: Object,
        default: () => ({
            total_publicaciones: 0,
            total_suscriptores: 0,
            total_ganancias: 0,
            visitas: 0,
            interacciones: 0,
            suscriptores_nuevos: 0
        })
    },
    suscripcionesActivas: {
        type: Array,
        default: () => []
    },
    categorias: {
        type: Array,
        default: () => []
    }
});

// Tabs
const tabs = [
    { key: 'publicaciones', label: 'Publicaciones', icon: null },
    { key: 'galeria', label: 'Galería', icon: 'pi-images' },
    { key: 'acerca', label: 'Acerca de', icon: 'pi-info-circle' },
];
const tabActivo = ref('publicaciones');

// Computed
const configuracion = computed(() => props.configuracionMonetizacion);
const creadorEsPremium = computed(() => {
    return configuracion.value?.modelo_ingresos === 'exclusivo' ||
        (configuracion.value?.precio_personalizado || 0) > 0;
});

const categoriasCreador = ref([]);

function handleImageError(event) {
    event.target.src = '/images/shared/placeholder-image.jpg';
    event.target.onerror = null;
}

function editarPerfil() {
    window.location.href = '/creador';
}

function compartirPerfil() {
    const url = window.location.href;
    if (navigator.share) {
        navigator.share({
            title: `Perfil de ${props.perfil.nombre || 'Creador'}`,
            url: url
        }).catch(() => {});
    } else {
        navigator.clipboard.writeText(url).then(() => {
            alert('Enlace copiado al portapapeles');
        }).catch(() => {});
    }
}

function gestionarSuscripcion() {
    window.location.href = '/creador/monetizacion';
}

function irAPublicar() {
    window.location.href = '/creador/publicar';
}

function verTodasSuscripciones() {
    window.location.href = '/creador/suscripciones';
}

onMounted(() => {
    if (props.categorias && props.categorias.length > 0) {
        categoriasCreador.value = props.categorias;
    } else if (props.perfil?.categorias) {
        categoriasCreador.value = props.perfil.categorias;
    }
    if (categoriasCreador.value.length === 0) {
        categoriasCreador.value = ['Lifestyle', 'Viajes', 'Bienestar'];
    }
});
</script>

<style scoped>
/* ============================================================
   RESET Y VARIABLES
   ============================================================ */
.perfil-creador-page {
    --brand: #C81E3A;
    --brand-dark: #A6152D;
    --brand-soft: #FBEAEC;
    --ink: #1a1a2e;
    --ink-soft: #4a4a6a;
    --muted: #8a8aaa;
    --muted-light: #b8b8d0;
    --line: #e8e8f0;
    --surface: #f8f8fc;
    --white: #ffffff;
    --shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.10);
    --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --radius: 14px;
    --radius-sm: 10px;
    --radius-full: 999px;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: var(--ink);
    background: #f0f2f8;
    -webkit-font-smoothing: antialiased;
}

.perfil-creador-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 1.5rem 2rem 3rem;
}

/* ============================================================
   TÍTULO
   ============================================================ */
.page-heading {
    margin-bottom: 1.5rem;
}
.page-heading h1 {
    font-size: 1.6rem;
    margin: 0 0 0.2rem;
    font-weight: 700;
}
.page-heading p {
    font-size: 0.85rem;
    color: var(--muted);
    margin: 0;
}

/* ============================================================
   CONTENT GRID
   ============================================================ */
.content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 1.5rem;
    align-items: start;
    padding-bottom: 2.5rem;
}
@media (max-width: 1100px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}
.main-column {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* ============================================================
   PROFILE CARD
   ============================================================ */
.profile-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    padding: 1.75rem 1.75rem 0.5rem;
}

.profile-card__body {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.profile-card__avatar-wrapper {
    flex-shrink: 0;
}

.profile-card__avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--white);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.10);
    background: var(--surface);
}

.profile-card__info {
    flex: 1;
    min-width: 220px;
    padding-top: 0.2rem;
}

.profile-card__name-row h2 {
    font-size: 1.4rem;
    margin: 0 0 0.4rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
    font-weight: 700;
}

.premium-chip {
    background: var(--brand-soft);
    color: var(--brand);
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.2rem 0.7rem;
    border-radius: var(--radius-full);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.verified-chip {
    font-size: 0.7rem;
    color: #16a34a;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}
.verified-chip i {
    font-size: 0.85rem;
}

.profile-card__bio {
    font-size: 0.88rem;
    color: var(--ink-soft);
    margin: 0;
    line-height: 1.6;
    max-width: 540px;
}

.profile-card__actions {
    display: flex;
    gap: 0.6rem;
    flex-wrap: wrap;
    margin-top: 0.2rem;
    flex-shrink: 0;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    border: 1.5px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--white);
    padding: 0.5rem 1rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink-soft);
    cursor: pointer;
    transition: var(--transition);
}
.action-btn:hover {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
}
.profile-card__actions :deep(.p-button) {
    font-weight: 700;
    border-radius: var(--radius-sm);
}

.profile-card__stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
    padding: 1.25rem 0 0.75rem;
    margin-top: 1.25rem;
    border-top: 1.5px solid var(--line);
}

.stat-item {
    text-align: center;
}
.stat-item strong {
    display: block;
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--ink);
}
.stat-item span {
    font-size: 0.75rem;
    color: var(--muted);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

/* ============================================================
   TABS
   ============================================================ */
.tabs-nav {
    display: flex;
    gap: 2rem;
    border-bottom: 2px solid var(--line);
    background: var(--white);
    border-radius: var(--radius) var(--radius) 0 0;
    padding: 0 1.5rem;
    box-shadow: var(--shadow);
}
.tabs-nav__item {
    background: none;
    border: none;
    padding: 0.9rem 0;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--muted);
    cursor: pointer;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    transition: var(--transition);
}
.tabs-nav__item:hover {
    color: var(--ink);
}
.tabs-nav__item.active {
    color: var(--brand);
    border-color: var(--brand);
}
.tabs-nav__item i {
    font-size: 0.85rem;
}

/* ============================================================
   EMPTY STATE
   ============================================================ */
.empty-state {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 3rem 2rem;
    text-align: center;
    box-shadow: var(--shadow);
}
.empty-state i {
    font-size: 3rem;
    color: var(--muted-light);
    margin-bottom: 1rem;
}
.empty-state h3 {
    font-size: 1.1rem;
    margin: 0 0 0.5rem;
}
.empty-state p {
    color: var(--muted);
    margin: 0 0 1.5rem;
}

.empty-gallery {
    text-align: center;
    padding: 2rem 1rem;
}
.empty-gallery i {
    font-size: 2.5rem;
    color: var(--muted-light);
    margin-bottom: 0.5rem;
}
.empty-gallery p {
    color: var(--muted);
    margin: 0;
}

.empty-subscriptions {
    color: var(--muted);
    font-size: 0.85rem;
    text-align: center;
    padding: 0.5rem 0;
}

/* ============================================================
   POST CARD
   ============================================================ */
.post-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 1.25rem 1.5rem;
    box-shadow: var(--shadow);
}
.post-card__header {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    margin-bottom: 0.8rem;
}
.post-card__author {
    display: flex;
    flex-direction: column;
    flex: 1;
}
.post-card__author strong {
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.post-card__author span {
    font-size: 0.72rem;
    color: var(--muted-light);
}
.post-card__badge {
    background: var(--brand-soft);
    color: var(--brand);
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}
.post-card__more {
    border: none;
    background: none;
    color: var(--muted-light);
    cursor: pointer;
    font-size: 1rem;
}
.post-card h3 {
    font-size: 0.95rem;
    margin: 0 0 0.3rem;
}
.post-card__text {
    font-size: 0.85rem;
    color: var(--ink-soft);
    line-height: 1.6;
    margin: 0 0 1rem;
    white-space: pre-line;
}
.post-card__media {
    position: relative;
    border-radius: var(--radius-sm);
    overflow: hidden;
    aspect-ratio: 16/8;
    background: #111;
}
.post-card__media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.post-card__footer {
    display: flex;
    gap: 1.5rem;
    margin-top: 1rem;
    padding-top: 0.8rem;
    border-top: 1px solid var(--line);
}
.post-card__footer button {
    border: none;
    background: none;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8rem;
    color: var(--ink-soft);
    cursor: pointer;
    font-weight: 600;
    transition: var(--transition);
}
.post-card__footer button:hover {
    color: var(--brand);
}
.post-card__footer button i {
    color: var(--brand);
}

.premium-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #fff;
    gap: 0.3rem;
    text-align: center;
    background: rgba(0, 0, 0, 0.55);
}
.premium-overlay__lock {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}
.premium-overlay strong {
    font-size: 0.9rem;
}
.premium-overlay span {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.7);
}
.post-card__price {
    position: absolute;
    bottom: 0.8rem;
    right: 0.8rem;
    background: rgba(255, 255, 255, 0.95);
    color: var(--brand);
    font-weight: 700;
    padding: 0.2rem 0.7rem;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    border: 1.5px solid var(--brand);
}

/* ============================================================
   SECTION BLOCK
   ============================================================ */
.section-block {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 1.25rem 1.5rem;
    box-shadow: var(--shadow);
}
.section-block h3 {
    font-size: 0.9rem;
    margin: 0 0 0.8rem;
    font-weight: 700;
}
.section-block h4 {
    font-size: 0.8rem;
    margin: 1rem 0 0.5rem;
    font-weight: 600;
    color: var(--ink-soft);
}
.chip-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.chip {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius-full);
    padding: 0.25rem 0.8rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--ink-soft);
}
.about-text {
    font-size: 0.85rem;
    color: var(--ink-soft);
    line-height: 1.6;
    margin: 0;
}
.about-stats {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 0.5rem;
    margin-top: 0.5rem;
}
.about-stat {
    background: var(--surface);
    border-radius: var(--radius-sm);
    padding: 0.6rem;
    text-align: center;
}
.about-stat span {
    display: block;
    font-size: 0.65rem;
    color: var(--muted);
    font-weight: 500;
}
.about-stat strong {
    font-size: 0.85rem;
    font-weight: 700;
}

/* ============================================================
   SIDEBAR
   ============================================================ */
.sidebar-column {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.sidebar-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 1.25rem 1.5rem;
    box-shadow: var(--shadow);
    transition: var(--transition);
}
.sidebar-card:hover {
    box-shadow: var(--shadow-hover);
}
.sidebar-card h3 {
    font-size: 0.9rem;
    font-weight: 700;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.sidebar-card h3 i {
    color: var(--brand);
    font-size: 0.9rem;
}
.sidebar-card__header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.8rem;
}
.sidebar-card__header-row h3 {
    margin: 0;
}
.see-all {
    color: var(--brand);
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.2rem;
}
.see-all:hover {
    text-decoration: underline;
}

.subscriber-list {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}
.subscriber-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}
.subscriber-item__info {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
}
.subscriber-item__info strong {
    font-size: 0.82rem;
    font-weight: 600;
}
.subscriber-item__info span {
    font-size: 0.7rem;
    color: var(--muted);
}

/* ============================================================
   BUTTON
   ============================================================ */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    border: none;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: var(--transition);
    font-family: inherit;
}
.btn--primary {
    background: var(--brand);
    color: var(--white);
}
.btn--primary:hover {
    background: var(--brand-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

/* ============================================================
   GALLERY
   ============================================================ */
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.6rem;
}
@media (max-width: 640px) {
    .gallery-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
.gallery-item {
    position: relative;
    aspect-ratio: 1/1;
    border-radius: var(--radius-sm);
    overflow: hidden;
    border: 2px solid var(--line);
}
.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.gallery-item__badge {
    position: absolute;
    top: 0.4rem;
    left: 0.4rem;
    background: var(--brand);
    color: white;
    font-size: 0.55rem;
    font-weight: 700;
    padding: 0.1rem 0.5rem;
    border-radius: var(--radius-full);
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 1024px) {
    .perfil-creador-page {
        padding: 1rem 1rem 2rem;
    }
}

@media (max-width: 768px) {
    .profile-card__body {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .profile-card__info {
        min-width: 0;
    }
    .profile-card__name-row h2 {
        justify-content: center;
    }
    .profile-card__bio {
        max-width: 100%;
    }
    .profile-card__actions {
        justify-content: center;
        width: 100%;
    }
    .profile-card__stats {
        grid-template-columns: repeat(2, 1fr);
    }
    .profile-card__avatar {
        width: 90px;
        height: 90px;
    }
    .tabs-nav {
        gap: 0.5rem;
        overflow-x: auto;
        padding: 0 1rem;
    }
    .tabs-nav__item {
        font-size: 0.75rem;
        padding: 0.7rem 0.3rem;
        white-space: nowrap;
    }
    .about-stats {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .perfil-creador-page {
        padding: 0.5rem 0.5rem 1rem;
    }
    .profile-card {
        padding: 1rem 1rem 0.25rem;
    }
    .profile-card__stats {
        grid-template-columns: 1fr 1fr;
        padding: 0.75rem 0 0.5rem;
    }
    .profile-card__stats strong {
        font-size: 1rem;
    }
    .profile-card__actions {
        flex-direction: column;
    }
    .profile-card__actions .action-btn,
    .profile-card__actions :deep(.p-button) {
        width: 100%;
        justify-content: center;
    }
    .post-card {
        padding: 0.75rem 1rem;
    }
    .gallery-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .sidebar-card {
        padding: 1rem;
    }
}
</style>