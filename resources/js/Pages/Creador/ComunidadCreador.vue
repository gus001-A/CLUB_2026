<template>

    <Head title="Comunidad Creador" />

    <ToastNotification ref="toastRef" :duration="5000" />

    <AppLayout active-nav="comunidad">
        <div class="comunidad-creador-page">
            <!-- ============================================================ -->
            <!-- HERO -->
            <!-- ============================================================ -->
            <section class="hero">
                <div class="hero__grid">
                    <div class="hero__copy">
                        <p class="hero__eyebrow">
                            <template v-if="esCreador">
                                Bienvenida a tu comunidad, <strong>{{ usuarioLocal.nombre }}</strong>
                            </template>
                            <template v-else>
                                Bienvenido a la comunidad de <strong>{{ usuarioLocal.nombre }}</strong>
                            </template>
                            <span v-if="usuarioLocal.verificado" class="hero__verified">
                                <i class="pi pi-check-circle"></i> Verificado
                            </span>
                        </p>
                        <h1 class="hero__title">
                            <template v-if="esCreador">
                                Tu comunidad también es tu <span class="hero__title-highlight">espacio creador</span>
                            </template>
                            <template v-else>
                                Descubre el <span class="hero__title-highlight">contenido exclusivo</span> de tus
                                creadores favoritos
                            </template>
                        </h1>
                        <p class="hero__text">
                            <template v-if="esCreador">
                                Comparte contenido exclusivo, conecta con tu comunidad y gestiona tu presencia como
                                creadora.
                            </template>
                            <template v-else>
                                Explora contenido premium, apoya a tus creadores favoritos y sé parte de su comunidad
                                exclusiva.
                            </template>
                        </p>
                    </div>

                    <div class="hero__media">
                        <img src="/images/comunidad.png" alt="Comunidad creador" class="hero__img" />
                        <div class="hero__fade"></div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- BENEFICIOS -->
            <!-- ============================================================ -->
            <section class="benefits-row">
                <div v-for="b in beneficiosHero" :key="b.titulo" class="benefit-item">
                    <span class="benefit-item__icon"><i class="pi" :class="b.icon"></i></span>
                    <div>
                        <strong>{{ b.titulo }}</strong>
                        <span>{{ b.desc }}</span>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- CONTENIDO PRINCIPAL -->
            <!-- ============================================================ -->
            <div class="content-grid">
                <!-- FEED COLUMN -->
                <div class="feed-column">
                    <!-- Tabs -->
                    <div class="tabs-nav">
                        <button v-for="tab in tabs" :key="tab.key" class="tabs-nav__item"
                            :class="{ active: tabActivo === tab.key }" @click="tabActivo = tab.key">{{ tab.label
                            }}</button>
                    </div>

                    <!-- Publicaciones -->
                    <div v-if="!publicaciones || publicaciones.length === 0" class="empty-state">
                        <i class="pi pi-inbox"></i>
                        <h3>
                            <template v-if="esCreador">No tienes publicaciones aún</template>
                            <template v-else>No hay publicaciones aún</template>
                        </h3>
                        <p>
                            <template v-if="esCreador">Comienza a compartir contenido con tu comunidad.</template>
                            <template v-else>Pronto habrá contenido exclusivo para ti.</template>
                        </p>
                        <button v-if="esCreador" class="btn btn--primary" @click="irAPublicar">
                            <i class="pi pi-plus"></i> Crear primera publicación
                        </button>
                    </div>

                    <article v-for="post in publicaciones" :key="post.id || post.titulo || Math.random()"
                        class="post-card">
                        <div class="post-card__header">
                            <AvatarCustom :image="usuarioLocal.avatar || '/images/shared/avatar-default.jpg'"
                                :label="getInitial(usuarioLocal.nombre)" size="large" />
                            <div class="post-card__author">
                                <strong>
                                    {{ usuarioLocal.nombre }}
                                    <span v-if="creadorEsPremium" class="premium-chip">Premium</span>
                                    <i v-if="usuarioLocal.verificado" class="pi pi-verified"></i>
                                </strong>
                                <span>{{ post.created_at || 'Hace 5 min' }}</span>
                            </div>
                            <span v-if="post.es_premium" class="post-card__badge"><i class="pi pi-lock"></i> Contenido
                                exclusivo</span>
                            <button class="post-card__more"><i class="pi pi-ellipsis-h"></i></button>
                        </div>

                        <h3 v-if="post.titulo">{{ post.titulo }}</h3>
                        <p v-if="post.descripcion" class="post-card__text">{{ post.descripcion }}</p>

                        <!-- Mostrar archivos -->
                        <div v-if="post.archivos && post.archivos.length > 0" class="post-card__media">
                            <img v-if="post.archivos[0]?.url" :src="post.archivos[0].url"
                                :alt="post.titulo || 'Contenido'" @error="handleImageError" />
                            <div v-if="post.es_premium && configuracion?.mostrar_vista_previa" class="premium-overlay">
                                <span class="premium-overlay__lock"><i class="pi pi-lock"></i></span>
                                <strong>Suscríbete para ver</strong>
                                <span>contenido exclusivo</span>
                            </div>
                            <span v-if="post.precio > 0" class="post-card__price">${{ Number(post.precio).toFixed(2) }}
                                USD</span>
                        </div>

                        <div class="post-card__footer">
                            <span><i class="pi pi-heart-fill"></i> {{ post.total_likes || 0 }}</span>
                            <span><i class="pi pi-comment"></i> {{ post.total_comentarios || 0 }}</span>
                            <span><i class="pi pi-share-alt"></i> {{ post.total_compartidos || 0 }}</span>
                            <a href="#" class="post-card__comments-link">Ver {{ post.total_comentarios || 0 }}
                                comentarios</a>
                        </div>
                    </article>
                </div>

                <!-- SIDEBAR -->
                <aside class="sidebar-column">
                    <!-- Tu espacio creador / Perfil del creador -->
                    <div class="sidebar-card creator-space-card">
                        <h3>
                            <i class="pi" :class="esCreador ? 'pi-user-edit' : 'pi-user'"
                                style="color: var(--brand); margin-right: 0.5rem;"></i>
                            {{ esCreador ? 'Tu espacio creador' : 'Perfil del creador' }}
                        </h3>

                        <!-- SOLO FOTO DE PERFIL - SIN PORTADA -->
                        <div class="creator-space-card__avatar-wrapper">
                            <img :src="espacioCreador.avatar || '/images/shared/avatar-default.jpg'" alt="Avatar"
                                class="creator-space-card__avatar" />
                        </div>

                        <div class="creator-space-card__info">
                            <strong>
                                {{ espacioCreador.nombre || usuarioLocal.nombre }}
                                <span v-if="creadorEsPremium" class="premium-chip">Premium</span>
                                <i v-if="usuarioLocal.verificado" class="pi pi-verified"></i>
                            </strong>
                            <span class="creator-space-card__bio">
                                {{ espacioCreador.bio || 'Creador de contenido exclusivo' }}</span>
                            <span class="creator-space-card__status"><i class="pi pi-circle-fill"></i> Perfil
                                verificado</span>
                        </div>

                        <div class="creator-space-card__stats">
                            <div><strong>{{ espacioCreador.seguidores || '0' }}</strong><span>Seguidores</span></div>
                            <div><strong>{{ espacioCreador.suscriptores || '0' }}</strong><span>Suscriptores</span>
                            </div>
                            <div><strong>{{ espacioCreador.publicaciones || 0 }}</strong><span>Publicaciones</span>
                            </div>
                        </div>

                        <!-- SI ES CREADOR: Botón para ver perfil -->
                        <template v-if="esCreador">
                            <PvButton label="Ver mi perfil de creador" icon="pi pi-arrow-right" iconPos="right"
                                class="creator-space-card__btn" @click="verPerfilCreador" />
                            <button class="creator-space-card__share" @click="compartirPerfil">
                                <i class="pi pi-share-alt"></i> Compartir perfil
                            </button>
                        </template>

                        <!-- SI NO ES CREADOR: Botón de suscribirse -->
                        <template v-else>
                            <PvButton label="Suscribirse" icon="pi pi-crown"
                                class="creator-space-card__btn creator-space-card__btn--subscribe"
                                @click="suscribirse" />
                            <p class="subscribe-info">
                                <i class="pi pi-info-circle"></i>
                                Suscríbete para acceder a contenido exclusivo
                            </p>
                        </template>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import AvatarCustom from '@/Components/AvatarCustom.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import ConfirmDialog from 'primevue/confirmdialog';
import PvButton from 'primevue/button';

// ============================================================
// OBTENER USUARIO DESDE Inertia (para el dropdown)
// ============================================================
const page = usePage();

// ============================================================
// PROPS DEL CONTROLADOR
// ============================================================
const props = defineProps({
    usuario: {
        type: Object,
        default: () => ({
            nombre: 'Invitado',
            avatar: '/images/shared/avatar-default.jpg',
            verificado: false,
            rol: 'usuario'
        })
    },
    creador: {
        type: Object,
        default: () => ({
            biografia: '',
            categorias: [],
            es_premium: false,
            esta_verificado: false,
            estado_verificacion: 'pendiente'
        })
    },
    estadisticas: {
        type: Object,
        default: () => ({
            total_publicaciones: 0,
            total_suscriptores: 0,
            total_ganancias: 0,
            visitas: 0,
            interacciones: 0
        })
    },
    contenidos_recientes: {
        type: Array,
        default: () => []
    },
    configuracion_monetizacion: {
        type: Object,
        default: null
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
    footerColumnas: {
        type: Object,
        default: () => ({})
    }
});

// ============================================================
// DETECTAR SI ES CREADOR
// ============================================================
const esCreador = computed(() => {
    // Verificar si el usuario tiene rol de creador
    return props.usuario?.rol === 'creador' ||
        (props.creador && Object.keys(props.creador).length > 0);
});

// ============================================================
// USUARIO LOCAL (para usar en la vista)
// ============================================================
const usuarioLocal = computed(() => {
    const user = props.usuario || {};
    let avatar = user.avatar || '/images/shared/avatar-default.jpg';

    // Asegurar que el avatar tenga la ruta correcta
    if (avatar && !avatar.startsWith('http') && !avatar.startsWith('/')) {
        avatar = '/storage/' + avatar;
    }

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
// REFERENCIAS PARA TOAST
// ============================================================
const toastRef = ref(null);

// ============================================================
// FUNCIONES PARA TOAST
// ============================================================
function showToast(type, title, message) {
    if (toastRef.value) {
        toastRef.value.showToast({
            type: type,
            title: title || (type === 'success' ? 'Éxito' : type === 'error' ? 'Error' : 'Información'),
            message: message,
            duration: 3000
        });
    }
}

function showSuccess(message, title = 'Éxito') {
    showToast('success', title, message);
}

function showError(message, title = 'Error') {
    showToast('error', title, message);
}

function showInfo(message, title = 'Información') {
    showToast('info', title, message);
}

// ============================================================
// DATOS COMPUTADOS DESDE PROPS
// ============================================================
const creador = computed(() => props.creador);
const estadisticas = computed(() => props.estadisticas);
const publicaciones = computed(() => props.contenidos_recientes || []);
const configuracion = computed(() => props.configuracion_monetizacion);

const creadorEsPremium = computed(() => {
    return creador.value?.es_premium || false;
});

// ============================================================
// FUNCIONES UTILES
// ============================================================
function getInitial(name) {
    if (!name) return '?';
    return name.charAt(0).toUpperCase();
}

function handleImageError(event) {
    event.target.src = '/images/shared/placeholder-image.jpg';
    event.target.onerror = null;
}

// ============================================================
// ACCIONES DEL MENÚ DE USUARIO
// ============================================================
function irAPerfilCreador() {
    window.location.href = '/creador/perfil';
}

function irAMiPerfil() {
    window.location.href = '/perfil';
}

function irAConfiguracion() {
    window.location.href = '/configuracion';
}

function cerrarSesion() {
    router.post('/logout');
}

// ============================================================
// BENEFICIOS DEL HERO
// ============================================================
const beneficiosHero = [
    { icon: 'pi-users', titulo: 'Conecta', desc: 'con tu comunidad de forma cercana.' },
    { icon: 'pi-upload', titulo: 'Comparte', desc: 'contenido exclusivo y experiencias únicas.' },
    { icon: 'pi-chart-bar', titulo: 'Haz crecer', desc: 'tu comunidad y tus ingresos.' },
    { icon: 'pi-shield', titulo: 'Tú tienes el control', desc: 'de lo que compartes y cómo lo compartes.' },
];

// ============================================================
// TABS DEL FEED
// ============================================================
const tabs = [
    { key: 'para-ti', label: 'Para ti' },
    { key: 'siguiendo', label: 'De creadores que sigues' },
    { key: 'reciente', label: 'Lo más reciente' },
];
const tabActivo = ref('para-ti');

// ============================================================
// SIDEBAR: ESPACIO CREADOR
// ============================================================
const espacioCreador = computed(() => ({
    avatar: usuarioLocal.value?.avatar || '/images/shared/avatar-default.jpg',
    nombre: usuarioLocal.value?.nombre || 'Creador',
    bio: creador.value?.biografia || 'Creador de contenido exclusivo',
    seguidores: formatNumber(estadisticas.value?.total_suscriptores || 0),
    suscriptores: formatNumber(estadisticas.value?.total_suscriptores || 0),
    publicaciones: estadisticas.value?.total_publicaciones || 0,
}));

function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return String(num);
}

// ============================================================
// FUNCIONES DE NAVEGACIÓN
// ============================================================
function verPerfilCreador() {
    window.location.href = '/creador/perfil';
}

function compartirPerfil() {
    const url = window.location.origin + '/creador/perfil';

    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            showSuccess('Link del perfil copiado al portapapeles');
        }).catch(() => {
            copiarAlPortapapeles(url);
        });
    } else {
        copiarAlPortapapeles(url);
    }
}

function copiarAlPortapapeles(texto) {
    const textarea = document.createElement('textarea');
    textarea.value = texto;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand('copy');
        showSuccess('Link del perfil copiado al portapapeles');
    } catch (err) {
        showError('No se pudo copiar el link');
    }
    document.body.removeChild(textarea);
}

function irAPublicar() {
    window.location.href = '/creador/publicar';
}

// ============================================================
// FUNCIÓN DE SUSCRIPCIÓN (PARA NO CREADORES)
// ============================================================
function suscribirse() {
    if (!usuarioLocal.value || !usuarioLocal.value.id) {
        window.location.href = '/login';
        return;
    }

    // Redirigir a la página de suscripción del creador
    window.location.href = '/creador/suscripcion';
}

// ============================================================
// LIFECYCLE
// ============================================================
onMounted(() => {
    console.log('Comunidad creador montada');
    console.log('Usuario props:', props.usuario);
    console.log('Usuario local:', usuarioLocal.value);
    console.log('Es creador:', esCreador.value);
    console.log('Creador:', creador.value);
    console.log('Estadisticas:', estadisticas.value);
    console.log('Publicaciones:', publicaciones.value);
    console.log('📋 Page props usuario:', page.props.usuario);
});
</script>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA
   ========================================================================= */
.comunidad-creador-page {
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
    --shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.08);

    --font-serif: 'Fraunces', Georgia, serif;
    --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;
    --radius-sm: 10px;
    --radius-md: 16px;
    --radius-lg: 24px;
    --radius-full: 999px;

    font-family: var(--font-sans);
    color: var(--ink);
    background: #f0f2f5;
    -webkit-font-smoothing: antialiased;
    max-width: 1500px;
    margin: 0 auto;
    padding: 1.25rem 2rem 0;
}

/* =========================================================================
   HERO
   ========================================================================= */
.hero {
    max-width: 1400px;
    margin: 1.5rem auto 0;
    padding: 0;
}

.hero__grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 380px;
    background: var(--ink);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
}

.hero__copy {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 2.5rem 2.5rem;
    color: #ffffff;
}

.hero__eyebrow {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.6);
    margin: 0 0 0.6rem;
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
    font-size: 0.6rem;
    font-weight: 600;
}

.hero__title {
    font-family: var(--font-serif);
    font-size: 2.2rem;
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
    line-height: 1.6;
    max-width: 440px;
    margin: 0.8rem 0 0;
    font-size: 0.85rem;
}

.hero__media {
    position: relative;
    min-height: 280px;
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
    transform: scale(1.03);
}

.hero__fade {
    position: absolute;
    inset: 0;
    width: 33%;
    background: linear-gradient(to right, var(--ink), rgba(23, 20, 18, 0.05));
}

/* =========================================================================
   BENEFICIOS
   ========================================================================= */
.benefits-row {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.25rem 1.5rem;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin: 1.25rem auto 0;
    max-width: 1400px;
    box-shadow: var(--shadow);
}

@media (max-width: 900px) {
    .benefits-row {
        grid-template-columns: 1fr 1fr;
    }
}

.benefit-item {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.benefit-item__icon {
    width: 38px;
    height: 38px;
    border-radius: var(--radius-sm);
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.benefit-item strong {
    display: block;
    font-size: 0.85rem;
}

.benefit-item span {
    font-size: 0.78rem;
    color: var(--muted);
}

/* =========================================================================
   CONTENT GRID
   ========================================================================= */
.content-grid {
    max-width: 1400px;
    margin: 1.25rem auto 0;
    padding: 0 0 3rem;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1100px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

.feed-column,
.sidebar-column {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* =========================================================================
   EMPTY STATE
   ========================================================================= */
.empty-state {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
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

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.6rem 1.4rem;
    border: none;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
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

/* =========================================================================
   TABS
   ========================================================================= */
.tabs-nav {
    display: flex;
    gap: 2rem;
    border-bottom: 1px solid var(--line);
    background: #fff;
    border-radius: var(--radius-md) var(--radius-md) 0 0;
    padding: 0 1.5rem;
    box-shadow: var(--shadow);
}

.tabs-nav__item {
    background: none;
    border: none;
    padding: 1rem 0;
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--muted);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: all 0.3s ease;
}

.tabs-nav__item.active {
    color: var(--brand);
    border-color: var(--brand);
}

.tabs-nav__item:hover {
    color: var(--brand);
}

/* =========================================================================
   POSTS
   ========================================================================= */
.post-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.25rem;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}

.post-card:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-2px);
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
    gap: 0.5rem;
}

.post-card__author strong i {
    color: var(--brand);
    font-size: 0.75rem;
}

.premium-chip {
    background: var(--brand-soft);
    color: var(--brand);
    font-size: 0.66rem;
    font-weight: 700;
    padding: 0.15rem 0.5rem;
    border-radius: var(--radius-full);
}

.post-card__author span {
    font-size: 0.74rem;
    color: var(--muted-light);
}

.post-card__badge {
    background: var(--brand-soft);
    color: var(--brand);
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.25rem 0.65rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.3rem;
    white-space: nowrap;
}

.post-card__more {
    border: none;
    background: none;
    color: var(--muted-light);
    cursor: pointer;
    font-size: 1rem;
    padding: 0.2rem;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.post-card__more:hover {
    background: var(--surface);
    color: var(--ink);
}

.post-card h3 {
    font-size: 0.95rem;
    margin: 0 0 0.4rem;
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

.post-card__footer {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    margin-top: 1rem;
    padding-top: 0.9rem;
    border-top: 1px solid var(--line);
    font-size: 0.82rem;
    color: var(--ink-soft);
    flex-wrap: wrap;
}

.post-card__footer i {
    color: var(--brand);
    margin-right: 0.3rem;
}

.post-card__comments-link {
    margin-left: auto;
    color: var(--brand);
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
}

.post-card__comments-link:hover {
    color: var(--brand-dark);
    text-decoration: underline;
}

/* =========================================================================
   SIDEBAR
   ========================================================================= */
.sidebar-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    box-shadow: var(--shadow);
}

.sidebar-card h3 {
    font-size: 1rem;
    margin: 0 0 1.1rem;
    display: flex;
    align-items: center;
}

/* =========================================================================
   ESPACIO CREADOR - SIN PORTADA
   ========================================================================= */
.creator-space-card__avatar-wrapper {
    display: flex;
    justify-content: center;
    margin-bottom: 1rem;
}

.creator-space-card__avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--brand);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
    transition: all 0.3s ease;
}

.creator-space-card__avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
}

.creator-space-card__info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.2rem;
    margin-bottom: 1.1rem;
    text-align: center;
}

.creator-space-card__info strong {
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.creator-space-card__info strong i {
    color: var(--brand);
    font-size: 0.8rem;
}

.creator-space-card__bio {
    font-size: 0.8rem;
    color: var(--ink-soft);
}

.creator-space-card__status {
    font-size: 0.75rem;
    color: #1c7a3c;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.creator-space-card__status i {
    color: #1fbf5c;
    font-size: 0.45rem;
}

.creator-space-card__stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    text-align: center;
    border-top: 1px solid var(--line);
    border-bottom: 1px solid var(--line);
    padding: 1rem 0;
    margin-bottom: 1.1rem;
}

.creator-space-card__stats strong {
    display: block;
    font-size: 1rem;
}

.creator-space-card__stats span {
    font-size: 0.7rem;
    color: var(--muted);
}

.creator-space-card__btn {
    width: 100%;
    font-weight: 700 !important;
    border-radius: 8px !important;
    margin-bottom: 0.7rem !important;
}

.creator-space-card__btn--subscribe {
    background: linear-gradient(135deg, var(--brand), var(--brand-dark)) !important;
    color: #fff !important;
    box-shadow: 0 4px 16px rgba(200, 30, 58, 0.3) !important;
}

.creator-space-card__btn--subscribe:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 24px rgba(200, 30, 58, 0.4) !important;
}

.subscribe-info {
    font-size: 0.75rem;
    color: var(--muted);
    text-align: center;
    margin: 0.5rem 0 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
}

.subscribe-info i {
    color: var(--brand);
}

.creator-space-card__share {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    border: 1px solid var(--line);
    border-radius: 8px;
    background: #fff;
    padding: 0.75rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--ink);
    cursor: pointer;
    transition: all 0.3s ease;
}

.creator-space-card__share:hover {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
    .comunidad-creador-page {
        padding: 1rem 1rem 0;
    }

    .hero__grid {
        grid-template-columns: 1fr;
        min-height: auto;
    }

    .hero__copy {
        padding: 2rem 1.5rem;
    }

    .hero__title {
        font-size: 1.8rem;
    }

    .hero__media {
        min-height: 200px;
        order: -1;
    }

    .hero__fade {
        display: none;
    }

    .benefits-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .comunidad-creador-page {
        padding: 0.75rem 0.75rem 0;
    }

    .hero__copy {
        padding: 1.5rem 1rem;
    }

    .hero__title {
        font-size: 1.4rem;
    }

    .hero__text {
        font-size: 0.8rem;
    }

    .hero__media {
        min-height: 160px;
    }

    .benefits-row {
        grid-template-columns: 1fr;
    }

    .tabs-nav {
        gap: 1rem;
        padding: 0 1rem;
        overflow-x: auto;
    }

    .tabs-nav__item {
        font-size: 0.8rem;
        padding: 0.8rem 0;
        white-space: nowrap;
    }

    .post-card__footer {
        gap: 0.8rem;
    }

    .post-card__comments-link {
        margin-left: 0;
        width: 100%;
    }

    .creator-space-card__stats {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 480px) {
    .post-card {
        padding: 0.8rem;
    }

    .sidebar-card {
        padding: 1rem;
    }

    .hero__title {
        font-size: 1.2rem;
    }

    .benefits-row {
        padding: 1rem;
    }

    .creator-space-card__avatar {
        width: 80px;
        height: 80px;
    }

    .post-card__header {
        flex-wrap: wrap;
    }

    .post-card__badge {
        font-size: 0.6rem;
        padding: 0.15rem 0.5rem;
    }

    .post-card__media {
        aspect-ratio: 16/9;
    }
}
</style>