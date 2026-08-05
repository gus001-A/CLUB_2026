<script setup>
import { computed } from 'vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const page = usePage();

const usuario = computed(() => page.props.usuario || {
    id: null,
    nombre: 'Invitado',
    apodo: 'Invitado',
    email: '',
    avatar: '/images/shared/avatar-default.jpg',
    verificado: false,
    rol: 'invitado',
    tiene_perfil: false,
});

const quickStats = computed(() => page.props.quickStats || []);
const panelInteligente = computed(() => page.props.panelInteligente || []);
const eventos = computed(() => page.props.eventos || []);
const mensajesRecientes = computed(() => page.props.mensajesRecientes || []);
const actividadReciente = computed(() => page.props.actividadReciente || []);
const publicacionesRecientes = computed(() => page.props.publicacionesRecientes || []);
</script>

<template>
    <AppLayout activeNav="inicio">
        <Head title="Inicio" />

        <div class="cf-page">
            <!-- ============================================================ -->
            <!-- HERO -->
            <!-- ============================================================ -->
            <section class="hero">
                <div class="hero__content">
                    <p class="hero__eyebrow">
                        Bienvenido de nuevo, <strong>{{ usuario.nombre }}</strong>
                        <span v-if="usuario.verificado" class="hero__verified">
                            <i class="pi pi-check-circle"></i> Verificado
                        </span>
                    </p>
                    <h1>Tu próxima <span>conexión</span><br />comienza hoy.</h1>
                    <p class="hero__desc">Descubre experiencias reales, seguras y compatibles con personas que comparten tus deseos y estilo de vida.</p>
                </div>
                <div class="hero__image">
                    <img src="/images/inicio_header.png" alt="Bienvenido a Club de Fantasías" />
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- QUICK STATS -->
            <!-- ============================================================ -->
            <section class="quick-stats">
                <div v-for="stat in quickStats" :key="stat.titulo" class="stat-card">
                    <span class="stat-card__icon"><i class="pi" :class="stat.icon"></i></span>
                    <div class="stat-card__body">
                        <span class="stat-card__title">
                            {{ stat.titulo }}
                            <PvBadge v-if="stat.badge" :value="stat.badge" severity="danger" />
                            <i v-if="stat.verificado" class="pi pi-check-circle verified-check"></i>
                        </span>
                        <span class="stat-card__desc">{{ stat.desc }}</span>
                    </div>
                    <label v-if="stat.toggle" class="toggle-switch">
                        <input type="checkbox" :checked="stat.activo || false" />
                        <span class="toggle-slider"></span>
                    </label>
                    <i v-else class="pi pi-chevron-right stat-card__chevron"></i>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- PANEL INTELIGENTE -->
            <!-- ============================================================ -->
            <section class="section">
                <div class="section__heading">
                    <h2>Tu panel inteligente</h2>
                    <p>Todo lo que necesitas para conectar mejor.</p>
                </div>

                <div class="panel-grid">
                    <div v-for="item in panelInteligente" :key="item.titulo" class="panel-card">
                        <div class="panel-card__image">
                            <img :src="item.imagen" :alt="item.titulo" />
                        </div>
                        <div class="panel-card__body">
                            <h3>{{ item.titulo }}</h3>
                            <p>{{ item.desc }}</p>
                            <div class="panel-card__footer">
                                <a href="#">{{ item.link }} <i v-if="!item.extra" class="pi pi-chevron-right"></i></a>
                                <strong v-if="item.extra">{{ item.extra }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- COMUNIDAD - ÚLTIMAS 6 PUBLICACIONES -->
            <!-- ============================================================ -->
            <section class="section">
                <div class="section__heading section__heading--row">
                    <div>
                        <h2>Comunidad</h2>
                        <p>Descubre lo que comparten los miembros de Club de Fantasías.</p>
                    </div>
                    <Link :href="route('comunidad.index')" class="see-all">
                        Ver más <i class="pi pi-chevron-right"></i>
                    </Link>
                </div>

                <div class="community-grid">
                    <div v-if="publicacionesRecientes.length === 0" class="empty-state">
                        <div class="empty-state__content">
                            <i class="pi pi-users empty-state__icon"></i>
                            <p>No hay publicaciones en la comunidad aún.</p>
                            <span class="empty-state__sub">¡Sé el primero en compartir algo!</span>
                        </div>
                    </div>

                    <div v-for="publicacion in publicacionesRecientes" :key="publicacion.id" class="community-card">
                        <!-- Cabecera del usuario -->
                        <div class="community-card__header">
                            <div class="community-card__user">
                                <PvAvatar :image="publicacion.usuario.avatar" shape="circle" size="large" />
                                <div class="community-card__user-info">
                                    <div class="community-card__user-name">
                                        <strong>{{ publicacion.usuario.nombre }}</strong>
                                        <i v-if="publicacion.usuario.verificado" class="pi pi-check-circle verified-badge"></i>
                                        <span v-if="publicacion.usuario.es_creador" class="creator-badge">Creador</span>
                                    </div>
                                    <span class="community-card__user-handle">@{{ publicacion.usuario.apodo }}</span>
                                </div>
                            </div>
                            <span class="community-card__time">{{ publicacion.tiempo }}</span>
                        </div>

                        <!-- Contenido -->
                        <div class="community-card__content">
                            <p v-if="publicacion.texto" class="community-card__text">{{ publicacion.texto }}</p>
                            
                            <!-- Imagen -->
                            <div v-if="publicacion.es_imagen && publicacion.imagen" class="community-card__media">
                                <img :src="publicacion.imagen" :alt="publicacion.texto || 'Publicación'" @error="(e) => e.target.src = '/images/shared/image-default.jpg'" />
                            </div>
                            
                            <!-- Video -->
                            <div v-if="publicacion.es_video && publicacion.imagen" class="community-card__media community-card__media--video">
                                <video controls>
                                    <source :src="publicacion.imagen" />
                                    Tu navegador no soporta la reproducción de videos.
                                </video>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="community-card__stats">
                            <span class="community-card__stat">
                                <i class="pi pi-heart"></i> {{ publicacion.likes }}
                            </span>
                            <span class="community-card__stat">
                                <i class="pi pi-comment"></i> {{ publicacion.comentarios_count }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- EVENTOS -->
            <!-- ============================================================ -->
            <section class="section">
                <div class="section__heading section__heading--row">
                    <div>
                        <h2>Próximos eventos exclusivos</h2>
                        <p>Vive experiencias únicas en un ambiente seguro y selecto.</p>
                    </div>
                    <a href="#" class="see-all">Ver todos los eventos <i class="pi pi-chevron-right"></i></a>
                </div>

                <div class="event-grid">
                    <div v-if="eventos.length === 0" class="empty-state">
                        <p>No hay eventos próximos disponibles.</p>
                    </div>
                    <div v-for="e in eventos" :key="e.titulo" class="event-card">
                        <div class="event-card__image">
                            <img :src="e.imagen" :alt="e.titulo" />
                            <div class="event-card__date">
                                <strong>{{ e.dia }}</strong>
                                <span>{{ e.mes }}</span>
                            </div>
                        </div>
                        <div class="event-card__body">
                            <h3>{{ e.titulo }}</h3>
                            <p><i class="pi pi-map-marker"></i> {{ e.ciudad }}</p>
                            <p><i class="pi pi-clock"></i> {{ e.fecha }}</p>
                            <PvButton label="Más información" outlined class="event-card__btn" />
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- MENSAJES + ACTIVIDAD -->
            <!-- ============================================================ -->
            <section class="section two-col">
                <PvCard class="panel-list-card">
                    <template #title>
                        <div class="panel-list-card__title">
                            <div>
                                <h2>Mensajes recientes</h2>
                                <p>Tus conversaciones más recientes.</p>
                            </div>
                            <a href="#" class="see-all">Ver todos <i class="pi pi-chevron-right"></i></a>
                        </div>
                    </template>
                    <template #content>
                        <div class="message-list">
                            <div v-if="mensajesRecientes.length === 0" class="empty-state">
                                <p>No tienes mensajes aún. ¡Conecta con alguien!</p>
                            </div>
                            <div v-for="msg in mensajesRecientes" :key="msg.nombre" class="message-item">
                                <PvAvatar :image="msg.avatar" shape="circle" size="large" />
                                <div class="message-item__body">
                                    <strong>{{ msg.nombre }}</strong>
                                    <span>{{ msg.preview }}</span>
                                </div>
                                <div class="message-item__meta">
                                    <span class="time">{{ msg.hora }}</span>
                                    <PvBadge v-if="msg.noLeidos" :value="msg.noLeidos" severity="danger" />
                                </div>
                            </div>
                        </div>
                    </template>
                </PvCard>

                <PvCard class="panel-list-card">
                    <template #title>
                        <div class="panel-list-card__title">
                            <div>
                                <h2>Actividad reciente</h2>
                                <p>Lo último en tu comunidad.</p>
                            </div>
                        </div>
                    </template>
                    <template #content>
                        <div class="activity-list">
                            <div v-if="actividadReciente.length === 0" class="empty-state">
                                <p>No hay actividad reciente.</p>
                            </div>
                            <div v-for="act in actividadReciente" :key="act.titulo" class="activity-item">
                                <span class="activity-item__icon"><i class="pi" :class="act.icon"></i></span>
                                <div class="activity-item__body">
                                    <strong>{{ act.titulo }}</strong>
                                    <span>{{ act.desc }}</span>
                                </div>
                                <span class="activity-item__time">{{ act.tiempo }}</span>
                            </div>
                        </div>
                    </template>
                </PvCard>
            </section>

            <!-- ============================================================ -->
            <!-- CTA COMPLETAR PERFIL -->
            <!-- ============================================================ -->
            <section v-if="!usuario.tiene_perfil" class="cta">
                <div class="cta__bg">
                    <img src="/images/completa.png" alt="Completa tu perfil" class="cta__bg-image" />
                    <div class="cta__bg-overlay"></div>
                </div>
                <div class="cta__content">
                    <span class="cta__badge">✦ Mejora tu perfil</span>
                    <h2 class="cta__title">Completa tu perfil y <span>mejora tus conexiones</span></h2>
                    <p class="cta__text">Añade más fotos, intereses y preferencias para recibir mejores coincidencias y acceder a eventos exclusivos.</p>
                    <Link :href="route('perfil.completar')" class="cta__btn">
                        COMPLETAR PERFIL
                    </Link>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA
   ========================================================================= */
.cf-page {
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

  --font-serif: 'Fraunces', Georgia, serif;
  --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;

  --container: 1240px;
  --radius-sm: 10px;
  --radius-md: 16px;
  --radius-lg: 24px;
  --radius-full: 999px;

  font-family: var(--font-sans);
  color: var(--ink);
  background: var(--white);
  -webkit-font-smoothing: antialiased;
}

.cf-page * {
  box-sizing: border-box;
}

.cf-page img {
  max-width: 100%;
  display: block;
}

/* =========================================================================
   HERO
   ========================================================================= */
.hero {
    display: grid;
    grid-template-columns: 1fr 1fr;
    max-width: 1400px;
    margin: 1.5rem auto 0;
    border-radius: var(--radius-lg);
    overflow: hidden;
    min-height: 420px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
    background: var(--ink);
}

.hero__content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 3.5rem 3.5rem;
    color: #ffffff;
    background: var(--ink);
}

.hero__eyebrow { 
    font-size: 0.85rem; 
    color: #d8d8dc; 
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
    padding: 0.2rem 0.6rem;
    border-radius: var(--radius-full);
    font-size: 0.7rem;
    font-weight: 600;
}

.hero h1 { 
    font-family: var(--font-serif);
    font-size: 2.8rem; 
    font-weight: 500; 
    line-height: 1.1; 
    margin: 0 0 1rem; 
}

.hero h1 span { 
    color: var(--brand); 
    font-weight: 700; 
    font-style: italic;
}

.hero__desc { 
    color: #d8d8dc; 
    font-size: 0.95rem; 
    max-width: 480px; 
    margin: 0; 
    line-height: 1.6;
}

.hero__image {
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: var(--ink);
}

.hero__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
    transition: transform 0.6s ease;
}

.hero:hover .hero__image img {
    transform: scale(1.05);
}

/* =========================================================================
   QUICK STATS
   ========================================================================= */
.quick-stats {
    max-width: 1400px;
    margin: 1.5rem auto 0;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
}

.stat-card {
    background: #ffffff; 
    border-radius: var(--radius-md);
    padding: 1.25rem 1.5rem; 
    display: flex; 
    align-items: flex-start; 
    gap: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: default;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
}

.stat-card__icon {
    width: 42px; 
    height: 42px; 
    border-radius: var(--radius-sm); 
    background: var(--brand-soft); 
    color: var(--brand);
    display: flex; 
    align-items: center; 
    justify-content: center; 
    flex-shrink: 0; 
    font-size: 1.1rem;
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
    gap: 0.25rem; 
    flex: 1; 
}

.stat-card__title { 
    font-weight: 700; 
    font-size: 0.9rem; 
    display: flex; 
    align-items: center; 
    gap: 0.4rem; 
}

.stat-card__desc { 
    font-size: 0.76rem; 
    color: var(--muted); 
    line-height: 1.4;
}

.verified-check { 
    color: #48BB78; 
}

.stat-card__chevron { 
    color: var(--muted-light); 
    align-self: center; 
    transition: all 0.3s ease;
}

.stat-card:hover .stat-card__chevron {
    color: var(--brand);
    transform: translateX(4px);
}

.toggle-switch { 
    position: relative; 
    width: 40px; 
    height: 22px; 
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
    background: var(--brand); 
    border-radius: var(--radius-full); 
    cursor: pointer; 
    transition: 0.3s ease;
}

.toggle-slider::before {
    content: ''; 
    position: absolute; 
    width: 16px; 
    height: 16px; 
    left: 21px; 
    top: 3px;
    background: #ffffff; 
    border-radius: 50%; 
    transition: 0.3s ease;
}

/* =========================================================================
   SECTIONS
   ========================================================================= */
.section { 
    max-width: 1400px; 
    margin: 2.5rem auto 0; 
    padding: 0 2rem; 
}

.section__heading h2 { 
    font-family: var(--font-serif);
    font-size: 1.5rem; 
    margin: 0 0 0.2rem; 
}

.section__heading p { 
    font-size: 0.85rem; 
    color: var(--muted); 
    margin: 0; 
}

.section__heading--row { 
    display: flex; 
    justify-content: space-between; 
    align-items: flex-end; 
}

.see-all { 
    color: var(--brand); 
    font-size: 0.82rem; 
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
   PANEL INTELIGENTE
   ========================================================================= */
.panel-grid { 
    margin-top: 1.25rem; 
    display: grid; 
    grid-template-columns: repeat(4, 1fr); 
    gap: 1.5rem; 
}

.panel-card {
    background: #ffffff;
    border-radius: var(--radius-md);
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.panel-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
}

.panel-card__image {
    width: 100%;
    aspect-ratio: 16/10;
    overflow: hidden;
    background: var(--surface);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    border-radius: var(--radius-md) var(--radius-md) 0 0;
}

.panel-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: var(--radius-sm);
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.panel-card:hover .panel-card__image img {
    transform: scale(1.06);
}

.panel-card__body {
    padding: 1.25rem 1.5rem 1.5rem;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.panel-card__body h3 {
    font-size: 0.95rem;
    font-weight: 600;
    margin: 0 0 0.4rem;
    color: var(--ink);
}

.panel-card__body p {
    font-size: 0.78rem;
    color: var(--muted);
    line-height: 1.6;
    margin: 0 0 1rem;
    flex: 1;
}

.panel-card__footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 0.75rem;
}

.panel-card__footer a {
    color: var(--brand);
    font-size: 0.78rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    transition: all 0.3s ease;
}

.panel-card__footer a:hover {
    color: var(--brand-dark);
    gap: 0.6rem;
}

.panel-card__footer strong {
    font-size: 0.85rem;
    font-weight: 700;
    background: var(--brand-soft);
    padding: 0.2rem 0.8rem;
    border-radius: var(--radius-full);
    color: var(--brand);
}

/* =========================================================================
   COMUNIDAD - CARDS EN GRID (6 PUBLICACIONES)
   ========================================================================= */
.community-grid {
    margin-top: 1.25rem;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

.community-card {
    background: #ffffff;
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.community-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.08);
}

.community-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1rem 1.25rem 0.5rem 1.25rem;
    gap: 0.5rem;
}

.community-card__user {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    min-width: 0;
    flex: 1;
}

.community-card__user-info {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
    min-width: 0;
}

.community-card__user-name {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    flex-wrap: wrap;
}

.community-card__user-name strong {
    font-size: 0.85rem;
    color: var(--ink);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
}

.verified-badge {
    color: #48BB78;
    font-size: 0.7rem;
    flex-shrink: 0;
}

.creator-badge {
    font-size: 0.5rem;
    font-weight: 700;
    color: #8B5CF6;
    background: #EDE9FE;
    padding: 0.05rem 0.4rem;
    border-radius: var(--radius-full);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    flex-shrink: 0;
}

.community-card__user-handle {
    font-size: 0.7rem;
    color: var(--muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.community-card__time {
    font-size: 0.65rem;
    color: var(--muted-light);
    white-space: nowrap;
    flex-shrink: 0;
    margin-top: 0.1rem;
}

.community-card__content {
    padding: 0 1.25rem 0.5rem 1.25rem;
    flex: 1;
}

.community-card__text {
    font-size: 0.85rem;
    line-height: 1.6;
    color: var(--ink);
    margin: 0 0 0.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-wrap: break-word;
}

.community-card__media {
    border-radius: var(--radius-sm);
    overflow: hidden;
    background: var(--surface);
    margin-top: 0.25rem;
}

.community-card__media img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    transition: transform 0.3s ease;
    background: var(--surface);
}

.community-card__media img:hover {
    transform: scale(1.03);
}

.community-card__media--video video {
    width: 100%;
    height: 180px;
    border-radius: var(--radius-sm);
    background: #000;
}

.community-card__stats {
    display: flex;
    gap: 1.25rem;
    padding: 0.5rem 1.25rem;
    border-top: 1px solid var(--line);
    border-bottom: 1px solid var(--line);
    margin: 0 1.25rem 0.5rem 1.25rem;
}

.community-card__stat {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.75rem;
    color: var(--muted);
}

.community-card__stat i {
    font-size: 0.8rem;
}

/* =========================================================================
   EVENTOS
   ========================================================================= */
.event-grid { 
    margin-top: 1.25rem; 
    display: grid; 
    grid-template-columns: repeat(3, 1fr); 
    gap: 1.5rem; 
}

.event-card {
    background: #ffffff;
    border-radius: var(--radius-md);
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.event-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
}

.event-card__image {
    position: relative;
    width: 100%;
    aspect-ratio: 16/10;
    overflow: hidden;
    background: var(--surface);
}

.event-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.event-card:hover .event-card__image img {
    transform: scale(1.08);
}

.event-card__date {
    position: absolute;
    top: 10px;
    left: 10px;
    background: var(--brand);
    color: #ffffff;
    border-radius: var(--radius-sm);
    padding: 0.35rem 0.6rem;
    text-align: center;
    line-height: 1.05;
    box-shadow: 0 4px 12px rgba(200, 30, 58, 0.3);
}

.event-card__date strong { 
    display: block; 
    font-size: 1.1rem; 
}

.event-card__date span { 
    font-size: 0.62rem; 
    letter-spacing: 0.05em; 
    text-transform: uppercase;
}

.event-card__body { 
    padding: 1rem 1.25rem 1.25rem; 
}

.event-card__body h3 { 
    font-size: 0.95rem; 
    font-weight: 600;
    margin: 0 0 0.5rem; 
}

.event-card__body p { 
    font-size: 0.78rem; 
    color: var(--muted); 
    margin: 0 0 0.35rem; 
    display: flex; 
    align-items: center; 
    gap: 0.35rem; 
}

.event-card__btn { 
    width: 100%; 
    margin-top: 0.6rem; 
    font-weight: 700; 
    font-size: 0.78rem; 
    border-radius: var(--radius-sm); 
}

.event-card__btn :deep(.p-button) {
    border-color: var(--line);
    color: var(--ink-soft);
    background: transparent;
    padding: 0.5rem 0.75rem;
}

.event-card__btn :deep(.p-button:hover) {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
}

/* =========================================================================
   MENSAJES + ACTIVIDAD
   ========================================================================= */
.two-col { 
    display: grid; 
    grid-template-columns: 1fr 1fr; 
    gap: 1.5rem; 
}

.panel-list-card { 
    border-radius: var(--radius-md); 
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.panel-list-card:hover {
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.06);
}

.panel-list-card :deep(.p-card-body) { 
    padding: 1.25rem 1.5rem; 
}

.panel-list-card :deep(.p-card-title) {
    padding: 0;
}

.panel-list-card__title { 
    display: flex; 
    justify-content: space-between; 
    align-items: flex-start; 
}

.panel-list-card__title h2 { 
    font-family: var(--font-serif);
    font-size: 1.05rem; 
    margin: 0 0 0.15rem; 
}

.panel-list-card__title p { 
    font-size: 0.78rem; 
    color: var(--muted); 
    margin: 0; 
}

.message-list { 
    display: flex; 
    flex-direction: column; 
    gap: 0.5rem; 
    margin-top: 0.5rem; 
}

.message-item { 
    display: flex; 
    align-items: center; 
    gap: 0.75rem; 
    padding: 0.6rem 0.75rem;
    border-radius: var(--radius-sm);
    transition: all 0.3s ease;
    cursor: default;
}

.message-item:hover {
    background: var(--surface);
}

.message-item__body { 
    display: flex; 
    flex-direction: column; 
    flex: 1; 
    min-width: 0; 
}

.message-item__body strong { 
    font-size: 0.85rem; 
    color: var(--ink);
}

.message-item__body span { 
    font-size: 0.78rem; 
    color: var(--muted); 
    overflow: hidden; 
    text-overflow: ellipsis; 
    white-space: nowrap; 
}

.message-item__meta { 
    display: flex; 
    flex-direction: column; 
    align-items: flex-end; 
    gap: 0.35rem; 
}

.message-item__meta .time { 
    font-size: 0.7rem; 
    color: var(--muted-light); 
}

.activity-list { 
    display: flex; 
    flex-direction: column; 
    gap: 0.5rem; 
    margin-top: 0.5rem; 
}

.activity-item { 
    display: flex; 
    align-items: flex-start; 
    gap: 0.75rem; 
    padding: 0.6rem 0.75rem;
    border-radius: var(--radius-sm);
    transition: all 0.3s ease;
    cursor: default;
}

.activity-item:hover {
    background: var(--surface);
}

.activity-item__icon {
    width: 32px; 
    height: 32px; 
    border-radius: var(--radius-sm); 
    background: var(--brand-soft); 
    color: var(--brand);
    display: flex; 
    align-items: center; 
    justify-content: center; 
    flex-shrink: 0; 
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.activity-item:hover .activity-item__icon {
    background: var(--brand);
    color: var(--white);
}

.activity-item__body { 
    display: flex; 
    flex-direction: column; 
    flex: 1; 
}

.activity-item__body strong { 
    font-size: 0.85rem; 
    color: var(--ink);
}

.activity-item__body span { 
    font-size: 0.78rem; 
    color: var(--muted); 
}

.activity-item__time { 
    font-size: 0.7rem; 
    color: var(--muted-light); 
    white-space: nowrap; 
}

/* =========================================================================
   EMPTY STATE
   ========================================================================= */
.empty-state {
    grid-column: 1 / -1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 3rem 2rem;
    background: var(--white);
    border-radius: var(--radius-md);
}

.empty-state__content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    text-align: center;
}

.empty-state__icon {
    font-size: 2.5rem;
    color: var(--muted-light);
}

.empty-state p {
    color: var(--muted);
    font-size: 0.95rem;
    margin: 0;
}

.empty-state__sub {
    font-size: 0.8rem;
    color: var(--muted-light);
}

/* =========================================================================
   CTA COMPLETAR PERFIL
   ========================================================================= */
.cta {
    position: relative;
    max-width: 1400px;
    margin: 2.5rem auto 0;
    border-radius: var(--radius-lg);
    overflow: hidden;
    min-height: 280px;
    display: flex;
    align-items: center;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
}

.cta__bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

.cta__bg-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
    transition: transform 0.6s ease;
}

.cta:hover .cta__bg-image {
    transform: scale(1.05);
}

.cta__bg-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(12, 12, 14, 0.88) 0%, rgba(12, 12, 14, 0.5) 50%, rgba(12, 12, 14, 0.2) 100%);
    z-index: 1;
}

.cta__content {
    position: relative;
    z-index: 2;
    padding: 3rem 3.5rem;
    max-width: 560px;
    color: #ffffff;
}

.cta__badge {
    display: inline-block;
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.15);
    padding: 0.3rem 1rem;
    border-radius: var(--radius-full);
    margin-bottom: 1rem;
}

.cta__title {
    font-family: var(--font-serif);
    font-size: 2rem;
    font-weight: 400;
    margin: 0 0 0.75rem;
    line-height: 1.2;
}

.cta__title span {
    color: var(--brand);
    font-weight: 700;
    font-style: italic;
}

.cta__text {
    font-size: 0.95rem;
    color: #d8d8dc;
    line-height: 1.7;
    margin: 0 0 1.5rem;
}

.cta__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--brand);
    border: none;
    color: var(--white);
    font-weight: 700;
    padding: 0.75rem 2rem;
    border-radius: var(--radius-full);
    text-decoration: none;
    transition: all 0.3s ease;
    font-family: var(--font-sans);
    font-size: 0.88rem;
    cursor: pointer;
}

.cta__btn:hover {
    background: var(--brand-dark);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(200, 30, 58, 0.3);
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1100px) {
    .panel-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .community-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .event-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .two-col {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 1024px) {
    .hero {
        grid-template-columns: 1fr;
        min-height: auto;
    }
    .hero__content {
        padding: 2.5rem;
    }
    .hero h1 {
        font-size: 2.2rem;
    }
    .hero__image {
        height: 300px;
    }
    .cta__content {
        padding: 2.5rem;
    }
    .cta__title {
        font-size: 1.7rem;
    }
    .quick-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .quick-stats {
        padding: 0 1rem;
        grid-template-columns: 1fr;
    }
    .section {
        padding: 0 1rem;
    }
    .hero__content {
        padding: 2rem 1.5rem;
    }
    .hero h1 {
        font-size: 1.8rem;
    }
    .hero__desc {
        font-size: 0.85rem;
    }
    .hero__image {
        height: 250px;
    }
    .cta {
        min-height: 240px;
    }
    .cta__content {
        padding: 2rem 1.5rem;
    }
    .cta__title {
        font-size: 1.4rem;
    }
    .cta__text {
        font-size: 0.85rem;
    }
    .panel-grid {
        grid-template-columns: 1fr;
    }
    .community-grid {
        grid-template-columns: 1fr;
    }
    .event-grid {
        grid-template-columns: 1fr;
    }
    .community-card__header {
        padding: 0.75rem 1rem 0.25rem 1rem;
    }
    .community-card__content {
        padding: 0 1rem 0.25rem 1rem;
    }
    .community-card__stats {
        margin: 0 1rem 0.25rem 1rem;
        padding: 0.35rem 0;
    }
    .community-card__media img,
    .community-card__media--video video {
        height: 200px;
    }
}

@media (max-width: 480px) {
    .hero__content {
        padding: 1.5rem 1rem;
    }
    .hero h1 {
        font-size: 1.5rem;
    }
    .hero__image {
        height: 200px;
    }
    .cta__content {
        padding: 1.5rem 1rem;
    }
    .cta__title {
        font-size: 1.2rem;
    }
    .cta__btn {
        width: 100% !important;
        justify-content: center !important;
    }
    .section__heading--row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    .stat-card {
        padding: 0.9rem 1rem;
    }
    .panel-card__image {
        padding: 1rem;
    }
    .community-card__user-name strong {
        max-width: 80px;
        font-size: 0.8rem;
    }
    .community-card__text {
        font-size: 0.8rem;
        -webkit-line-clamp: 2;
    }
    .community-card__media img,
    .community-card__media--video video {
        height: 160px;
    }
    .community-card__stat {
        font-size: 0.7rem;
    }
}
</style>