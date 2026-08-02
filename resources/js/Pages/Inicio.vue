<script setup>
import { computed } from 'vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Obtener datos desde Inertia
const page = usePage();

// Datos del usuario desde el controlador
const usuario = computed(() => page.props.usuario || {
    id: null,
    nombre: 'Invitado',
    apodo: 'Invitado',
    email: '',
    avatar: '/images/shared/avatar-default.jpg',
    verificado: false,
    rol: 'invitado',
    tiene_perfil: false, // Nuevo campo para saber si tiene perfil
});

// Datos dinámicos desde el controlador
const quickStats = computed(() => page.props.quickStats || []);
const panelInteligente = computed(() => page.props.panelInteligente || []);
const coincidencias = computed(() => page.props.coincidencias || []);
const eventos = computed(() => page.props.eventos || []);
const mensajesRecientes = computed(() => page.props.mensajesRecientes || []);
const actividadReciente = computed(() => page.props.actividadReciente || []);
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
            <!-- COINCIDENCIAS -->
            <!-- ============================================================ -->
            <section class="section">
                <div class="section__heading section__heading--row">
                    <div>
                        <h2>Coincidencias para ti</h2>
                        <p>Perfiles seleccionados especialmente para ti.</p>
                    </div>
                    <a href="#" class="see-all">Ver todas <i class="pi pi-chevron-right"></i></a>
                </div>

                <div class="match-grid">
                    <div v-if="coincidencias.length === 0" class="empty-state">
                        <p>No tienes coincidencias aún. ¡Sigue explorando!</p>
                    </div>
                    <div v-for="m in coincidencias" :key="m.nombre" class="match-card">
                        <div class="match-card__image">
                            <img :src="m.imagen" :alt="m.nombre" />
                            <span class="match-card__verified"><i class="pi pi-check-circle"></i> Verificado</span>
                        </div>
                        <div class="match-card__body">
                            <div class="match-card__title-row">
                                <strong>{{ m.nombre }}</strong>
                                <span class="match-card__compat">{{ m.compatibilidad }}% compatible</span>
                            </div>
                            <p class="match-card__location"><i class="pi pi-map-marker"></i> {{ m.ciudad }} &nbsp;•&nbsp; {{ m.distancia }}</p>
                            <div class="match-card__tags">
                                <span v-if="m.disponible" class="tag tag--online"><i class="pi pi-circle-fill"></i> Disponible ahora</span>
                                <span v-if="m.cercano" class="tag"><i class="pi pi-map-marker"></i> Cercano</span>
                            </div>
                            <div class="match-card__actions">
                                <PvButton label="Ver perfil" outlined severity="secondary" />
                                <PvButton label="Conectar" />
                            </div>
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
            <!-- CTA COMPLETAR PERFIL - SOLO SI NO TIENE PERFIL -->
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
   PANEL INTELIGENTE - CON IMÁGENES REDONDEADAS
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
   COINCIDENCIAS
   ========================================================================= */
.match-grid { 
    margin-top: 1.25rem; 
    display: grid; 
    grid-template-columns: repeat(3, 1fr); 
    gap: 1.5rem; 
}

.match-card {
    background: #ffffff;
    border-radius: var(--radius-md);
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.match-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
}

.match-card__image {
    position: relative;
    width: 100%;
    aspect-ratio: 16/11;
    overflow: hidden;
    background: var(--surface);
}

.match-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.match-card:hover .match-card__image img {
    transform: scale(1.08);
}

.match-card__verified {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(255,255,255,0.95);
    color: #1c7a3c;
    font-size: 0.68rem;
    font-weight: 700;
    padding: 0.25rem 0.7rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.3rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    backdrop-filter: blur(4px);
}

.match-card__body { 
    padding: 1rem 1.25rem 1.25rem; 
}

.match-card__title-row { 
    display: flex; 
    justify-content: space-between; 
    align-items: baseline; 
    gap: 0.5rem; 
}

.match-card__title-row strong { 
    font-size: 0.95rem; 
    color: var(--ink);
}

.match-card__compat { 
    color: var(--brand); 
    font-size: 0.78rem; 
    font-weight: 700; 
    white-space: nowrap; 
}

.match-card__location { 
    font-size: 0.78rem; 
    color: var(--muted); 
    margin: 0.3rem 0 0.6rem; 
    display: flex; 
    align-items: center; 
    gap: 0.3rem; 
}

.match-card__tags { 
    display: flex; 
    flex-wrap: wrap;
    gap: 0.5rem; 
    margin-bottom: 0.9rem; 
}

.tag { 
    font-size: 0.68rem; 
    font-weight: 700; 
    padding: 0.2rem 0.6rem; 
    border-radius: var(--radius-full); 
    background: var(--surface); 
    color: var(--ink-soft); 
    display: flex; 
    align-items: center; 
    gap: 0.3rem; 
}

.tag--online { 
    background: #eefaf1; 
    color: #1c7a3c; 
}

.tag--online i { 
    font-size: 0.4rem; 
}

.match-card__actions { 
    display: flex; 
    gap: 0.6rem; 
}

.match-card__actions :deep(.p-button) { 
    flex: 1; 
    font-size: 0.78rem; 
    font-weight: 700; 
    border-radius: var(--radius-sm); 
    padding: 0.5rem 0.75rem;
}

.match-card__actions :deep(.p-button.p-button-outlined) {
    border-color: var(--line);
    color: var(--ink-soft);
    background: transparent;
}

.match-card__actions :deep(.p-button.p-button-outlined:hover) {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
}

.match-card__actions :deep(.p-button:not(.p-button-outlined)) {
    background: var(--brand);
    border-color: var(--brand);
    color: var(--white);
}

.match-card__actions :deep(.p-button:not(.p-button-outlined):hover) {
    background: var(--brand-dark);
    border-color: var(--brand-dark);
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
   CTA COMPLETAR PERFIL - CON LINK
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

.empty-state p {
    color: var(--muted);
    font-size: 0.95rem;
    margin: 0;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1100px) {
    .panel-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .match-grid {
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
    .match-grid {
        grid-template-columns: 1fr;
    }
    .event-grid {
        grid-template-columns: 1fr;
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
}
</style>