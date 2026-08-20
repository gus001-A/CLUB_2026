<template>
    <Head title="Comunidad Creador" />

    <ToastNotification ref="toastRef" :duration="5000" />
    <ConfirmDialog />

    <AppLayout 
        active-nav="comunidad"
        :usuario="usuario"
        :es-creador="true"
        :notificaciones="5"
        :favoritos="2"
        :mensajes="3"
        @ir-a-perfil-creador="irAPerfilCreador"
        @ir-a-mi-perfil="irAMiPerfil"
        @ir-a-configuracion="irAConfiguracion"
        @cerrar-sesion="cerrarSesion"
    >
        <div class="comunidad-creador-page">
            <!-- ============================================================ -->
            <!-- HERO -->
            <!-- ============================================================ -->
            <section class="hero">
                <div class="hero__grid">
                    <div class="hero__copy">
                        <p class="hero__eyebrow">
                            Bienvenida a tu comunidad, <strong>{{ usuario.nombre }}</strong>
                            <span v-if="usuario.verificado" class="hero__verified">
                                <i class="pi pi-check-circle"></i> Verificado
                            </span>
                        </p>
                        <h1 class="hero__title">
                            Tu comunidad también es tu <span class="hero__title-highlight">espacio creador</span>
                        </h1>
                        <p class="hero__text">
                            Comparte contenido exclusivo, conecta con tu comunidad y gestiona tu presencia como creadora.
                        </p>
                    </div>

                    <div class="hero__media">
                        <img src="/images/comunidad-creador/hero-creadora.jpg" alt="Creadora" class="hero__img" />
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
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            class="tabs-nav__item"
                            :class="{ active: tabActivo === tab.key }"
                            @click="tabActivo = tab.key"
                        >{{ tab.label }}</button>
                    </div>

                    <!-- Publicaciones -->
                    <article v-for="post in publicaciones" :key="post.tiempo" class="post-card">
                        <div class="post-card__header">
                            <AvatarCustom 
                                :image="usuario.avatar" 
                                :label="getInitial(usuario.nombre)"
                                size="large"
                            />
                            <div class="post-card__author">
                                <strong>
                                    {{ usuario.nombre }} 
                                    <span class="premium-chip">Premium</span> 
                                    <i class="pi pi-verified"></i>
                                </strong>
                                <span>{{ post.tiempo }}</span>
                            </div>
                            <span class="post-card__badge"><i class="pi pi-lock"></i> Contenido exclusivo</span>
                            <button class="post-card__more"><i class="pi pi-ellipsis-h"></i></button>
                        </div>

                        <p class="post-card__text">
                            <template v-for="(linea, i) in post.texto.split('\n')" :key="i">
                                {{ linea }}<br v-if="i < post.texto.split('\n').length - 1" />
                            </template>
                        </p>

                        <div class="post-card__gallery" :class="`post-card__gallery--${post.imagenes.length}`">
                            <div v-for="(img, i) in post.imagenes" :key="i" class="post-card__gallery-item">
                                <img :src="img" alt="" />
                            </div>
                        </div>

                        <div class="post-card__footer">
                            <span><i class="pi pi-heart-fill"></i> {{ post.likes }}</span>
                            <span><i class="pi pi-comment"></i> {{ post.comentarios }}</span>
                            <span><i class="pi pi-share-alt"></i> {{ post.compartidos }}</span>
                            <a href="#" class="post-card__comments-link">Ver {{ post.totalComentarios }} comentarios</a>
                        </div>
                    </article>
                </div>

                <!-- SIDEBAR -->
                <aside class="sidebar-column">
                    <!-- Tu espacio creador -->
                    <div class="sidebar-card creator-space-card">
                        <h3><i class="pi pi-user-edit" style="color: var(--brand); margin-right: 0.5rem;"></i> Tu espacio creador</h3>
                        <div class="creator-space-card__cover">
                            <img :src="espacioCreador.portada" alt="Portada" />
                            <img :src="espacioCreador.avatar" alt="Alexandra" class="creator-space-card__avatar" />
                        </div>

                        <div class="creator-space-card__info">
                            <strong>
                                {{ espacioCreador.nombre }} 
                                <span class="premium-chip">Premium</span> 
                                <i class="pi pi-verified"></i>
                            </strong>
                            <span class="creator-space-card__bio">{{ espacioCreador.bio }}</span>
                            <span class="creator-space-card__status"><i class="pi pi-circle-fill"></i> Perfil verificado</span>
                        </div>

                        <div class="creator-space-card__stats">
                            <div><strong>{{ espacioCreador.seguidores }}</strong><span>Seguidores</span></div>
                            <div><strong>{{ espacioCreador.suscriptores }}</strong><span>Suscriptores</span></div>
                            <div><strong>{{ espacioCreador.publicaciones }}</strong><span>Publicaciones</span></div>
                        </div>

                        <Button 
                            label="Ver mi perfil de creador" 
                            icon="pi pi-arrow-right" 
                            iconPos="right" 
                            class="creator-space-card__btn" 
                            @click="verPerfilCreador" 
                        />
                        <button class="creator-space-card__share" @click="compartirPerfil">
                            <i class="pi pi-share-alt"></i> Compartir perfil
                        </button>
                    </div>

                    <!-- Herramientas de creador -->
                    <div class="sidebar-card">
                        <h3><i class="pi pi-cog" style="color: var(--brand); margin-right: 0.5rem;"></i> Herramientas de creador</h3>
                        <div class="tools-list">
                            <button v-for="h in herramientas" :key="h.titulo" class="tool-item" @click="irAHerramienta(h.titulo)">
                                <span class="tool-item__icon"><i class="pi" :class="h.icon"></i></span>
                                <span class="tool-item__title">{{ h.titulo }}</span>
                                <i class="pi pi-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Resumen rápido -->
                    <div class="sidebar-card">
                        <h3><i class="pi pi-chart-bar" style="color: var(--brand); margin-right: 0.5rem;"></i> Resumen rápido</h3>
                        <div class="summary-grid">
                            <div v-for="r in resumenRapido" :key="r.titulo" class="summary-item">
                                <span class="summary-item__icon"><i class="pi" :class="r.icon"></i></span>
                                <strong>{{ r.valor }}</strong>
                                <span class="summary-item__title">{{ r.titulo }}</span>
                                <span class="summary-item__delta">{{ r.variacion }}</span>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import AvatarCustom from '@/Components/AvatarCustom.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import ConfirmDialog from 'primevue/confirmdialog';
import Button from 'primevue/button';

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
// DATOS DEL USUARIO
// ============================================================
const usuario = {
    nombre: 'Alexandra',
    avatar: '/images/comunidad-creador/avatar-alexandra.jpg',
    verificado: true,
};

// ============================================================
// FUNCIONES UTILES
// ============================================================
function getInitial(name) {
    if (!name) return '?';
    return name.charAt(0).toUpperCase();
}

// ============================================================
// ACCIONES DEL MENÚ DE USUARIO
// ============================================================
function irAPerfilCreador() {
    router.visit(route('creador.perfil'));
}

function irAMiPerfil() {
    router.visit(route('perfil'));
}

function irAConfiguracion() {
    router.visit(route('configuracion'));
}

function cerrarSesion() {
    router.post(route('logout'));
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
// PUBLICACIONES DEL FEED
// ============================================================
const publicaciones = [
    {
        tiempo: 'Hace 2 horas',
        texto: 'La magia de una noche especial ✨ ¿Listos para descubrir lo que viene? 🔥\nContenido exclusivo para mis suscriptores.',
        imagenes: [
            '/images/comunidad-creador/post-1-a.jpg',
            '/images/comunidad-creador/post-1-b.jpg',
            '/images/comunidad-creador/post-1-c.jpg',
        ],
        likes: 256,
        comentarios: 41,
        compartidos: 18,
        totalComentarios: 27,
    },
    {
        tiempo: 'Hace 1 día',
        texto: 'Nueva sesión de preguntas y respuestas este viernes.\n¡Deja tus preguntas en los comentarios!',
        imagenes: ['/images/comunidad-creador/post-2-a.jpg'],
        likes: 189,
        comentarios: 34,
        compartidos: 9,
        totalComentarios: 19,
    },
];

// ============================================================
// SIDEBAR: TU ESPACIO CREADOR
// ============================================================
const espacioCreador = {
    portada: '/images/comunidad-creador/portada.jpg',
    avatar: '/images/comunidad-creador/avatar-alexandra.jpg',
    nombre: 'Alexandra',
    bio: 'Creadora de contenido exclusivo',
    seguidores: '12.4K',
    suscriptores: '2.8K',
    publicaciones: 156,
};

// ============================================================
// SIDEBAR: HERRAMIENTAS DE CREADOR
// ============================================================
const herramientas = [
    { icon: 'pi-chart-bar', titulo: 'Ver estadísticas' },
    { icon: 'pi-pencil', titulo: 'Crear publicación' },
    { icon: 'pi-credit-card', titulo: 'Gestionar suscripciones' },
    { icon: 'pi-wallet', titulo: 'Centro de pagos' },
    { icon: 'pi-cog', titulo: 'Configuración de perfil' },
];

function irAHerramienta(titulo) {
    if (titulo === 'Crear publicación') {
        router.visit(route('creador.publicar'));
        return;
    }
    showInfo('Redirigiendo a ' + titulo);
}

// ============================================================
// SIDEBAR: RESUMEN RÁPIDO
// ============================================================
const resumenRapido = [
    { icon: 'pi-dollar', valor: '$24,975 USD', titulo: 'Ingresos estimados del mes', variacion: '+18% vs. mes anterior' },
    { icon: 'pi-refresh', valor: '84%', titulo: 'Renovación', variacion: '+6% vs. mes anterior' },
    { icon: 'pi-user-plus', valor: '+124', titulo: 'Suscriptores nuevos', variacion: '+22% vs. mes anterior' },
];

function verPerfilCreador() {
    router.visit(route('creador.perfil'));
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

// ============================================================
// FUNCIÓN PARA IR A LA COMUNIDAD DE CREADORES
// ============================================================
function irAComunidadCreadores() {
    console.log('🔵 Click en CREADORES');
    try {
        if (typeof route !== 'undefined' && route('creador.comunidad')) {
            router.get(route('creador.comunidad'));
            return;
        }
        window.location.href = '/creador/comunidad';
    } catch (error) {
        console.error('❌ Error al redirigir a comunidad de creadores:', error);
        window.location.href = '/creador/comunidad';
    }
}
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
  transform: scale(1.05);
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
    .benefits-row { grid-template-columns: 1fr 1fr; } 
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
    .content-grid { grid-template-columns: 1fr; } 
}

.feed-column, .sidebar-column { 
    display: flex; 
    flex-direction: column; 
    gap: 1.25rem; 
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

.post-card__text { 
    font-size: 0.85rem; 
    color: var(--ink-soft); 
    line-height: 1.6; 
    margin: 0 0 1rem; 
    white-space: pre-line; 
}

.post-card__gallery { 
    display: grid; 
    gap: 0.6rem; 
}

.post-card__gallery--1 { 
    grid-template-columns: 1fr; 
}

.post-card__gallery--3 { 
    grid-template-columns: repeat(3, 1fr); 
}

.post-card__gallery-item { 
    border-radius: var(--radius-sm); 
    overflow: hidden; 
    aspect-ratio: 4/3; 
}

.post-card__gallery--1 .post-card__gallery-item { 
    aspect-ratio: 16/9; 
}

.post-card__gallery-item img { 
    width: 100%; 
    height: 100%; 
    object-fit: cover; 
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
   TU ESPACIO CREADOR
   ========================================================================= */
.creator-space-card__cover { 
    position: relative; 
    border-radius: var(--radius-sm); 
    overflow: hidden; 
    aspect-ratio: 16/9; 
    margin-bottom: 2.5rem; 
}

.creator-space-card__cover img:first-child { 
    width: 100%; 
    height: 100%; 
    object-fit: cover; 
}

.creator-space-card__avatar {
    position: absolute; 
    left: 1rem; 
    bottom: -2rem; 
    width: 70px; 
    height: 70px; 
    border-radius: 50%;
    object-fit: cover; 
    border: 3px solid #fff; 
}

.creator-space-card__info { 
    display: flex; 
    flex-direction: column; 
    gap: 0.2rem; 
    margin-bottom: 1.1rem; 
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
   HERRAMIENTAS
   ========================================================================= */
.tools-list { 
    display: flex; 
    flex-direction: column; 
    gap: 0.6rem; 
}

.tool-item {
    display: flex; 
    align-items: center; 
    gap: 0.7rem; 
    border: 1px solid var(--line); 
    border-radius: var(--radius-sm);
    background: #fff; 
    padding: 0.75rem 1rem; 
    cursor: pointer; 
    text-align: left;
    transition: all 0.3s ease;
}

.tool-item:hover {
    border-color: var(--brand);
    background: var(--brand-soft);
    transform: translateX(4px);
}

.tool-item__icon {
    width: 32px; 
    height: 32px; 
    border-radius: 8px; 
    background: var(--brand-soft); 
    color: var(--brand);
    display: flex; 
    align-items: center; 
    justify-content: center; 
    flex-shrink: 0;
}

.tool-item__title { 
    flex: 1; 
    font-size: 0.85rem; 
    font-weight: 600; 
    color: var(--ink); 
}

.tool-item i.pi-arrow-right { 
    color: var(--muted-light); 
    font-size: 0.8rem; 
}

/* =========================================================================
   RESUMEN RÁPIDO
   ========================================================================= */
.summary-grid { 
    display: flex; 
    flex-direction: column; 
    gap: 0.9rem; 
}

.summary-item { 
    border: 1px solid var(--line); 
    border-radius: var(--radius-sm); 
    padding: 1rem; 
    display: flex; 
    flex-direction: column; 
    gap: 0.25rem;
    transition: all 0.3s ease;
}

.summary-item:hover {
    border-color: var(--brand);
    box-shadow: var(--shadow-hover);
}

.summary-item__icon {
    width: 32px; 
    height: 32px; 
    border-radius: 8px; 
    background: var(--brand-soft); 
    color: var(--brand);
    display: flex; 
    align-items: center; 
    justify-content: center; 
    margin-bottom: 0.3rem;
}

.summary-item strong { 
    font-size: 1.05rem; 
}

.summary-item__title { 
    font-size: 0.72rem; 
    color: var(--muted); 
}

.summary-item__delta { 
    font-size: 0.68rem; 
    color: #1c7a3c; 
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
    .post-card__gallery--3 {
        grid-template-columns: repeat(2, 1fr);
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
        width: 50px;
        height: 50px;
        bottom: -1.5rem;
        left: 0.5rem;
    }
    .creator-space-card__cover {
        margin-bottom: 2rem;
    }
    .post-card__gallery--3 {
        grid-template-columns: 1fr;
    }
    .post-card__gallery-item {
        aspect-ratio: 16/9;
    }
    .post-card__header {
        flex-wrap: wrap;
    }
    .post-card__badge {
        font-size: 0.6rem;
        padding: 0.15rem 0.5rem;
    }
}
</style>