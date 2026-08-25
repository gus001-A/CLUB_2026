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
const eventos = computed(() => page.props.eventos || []);
const mensajesRecientes = computed(() => page.props.mensajesRecientes || []);
const publicacionesRecientes = computed(() => page.props.publicacionesRecientes || []);
const actividadReciente = computed(() => page.props.actividadReciente || []);

const formatearPrecio = (precio) => {
    if (precio <= 0) return 'GRATIS';
    return '$' + new Intl.NumberFormat('es-MX').format(precio);
};

function rutaSegura(nombre, alternativa) {
    try {
        return route(nombre);
    } catch (e) {
        return alternativa;
    }
}

const totalMensajesNoLeidos = computed(() =>
    mensajesRecientes.value.reduce((total, m) => total + (m.noLeidos || 0), 0)
);

const esCreador = computed(() => usuario.value.rol === 'creador');

// Filtrar estadísticas para mostrar (eliminar "Modo activo")
const quickStatsFiltradas = computed(() => {
    return quickStats.value.filter(stat => stat.titulo !== 'Modo activo');
});

// Obtener coincidencias pendientes para el badge
const coincidenciasPendientes = computed(() => {
    const coincidencias = quickStats.value.find(stat => stat.titulo === 'Coincidencias');
    return coincidencias?.badge || 0;
});

// Función segura para enlaces de comunidad
const comunidadShowRoute = (id) => {
    try {
        return route('comunidad.show', id);
    } catch (e) {
        return `/comunidad/${id}`;
    }
};
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
                    <div class="hero__top">
                        <span class="hero__greeting">
                            Bienvenido de nuevo
                        </span>
                        <h1 class="hero__title">
                            {{ usuario.nombre }}
                            <span v-if="usuario.verificado" class="hero__verified">
                                <i class="pi pi-check-circle"></i>
                            </span>
                            <span v-if="esCreador" class="hero__creator-tag">
                                <i class="pi pi-star"></i> Creador
                            </span>
                        </h1>
                    </div>
                    <p class="hero__description">
                        Descubre experiencias reales, seguras y compatibles con personas que comparten
                        tus deseos y estilo de vida.
                    </p>
                    <div class="hero__actions">
                        <Link :href="rutaSegura('descubrir', '/descubrir')" class="hero__cta">
                            <i class="pi pi-compass"></i> Empezar a descubrir
                        </Link>
                        <Link :href="rutaSegura('invitaciones.index', '/invitaciones')"
                            class="hero__cta hero__cta--secondary">
                            <i class="pi pi-send"></i> Invitar a un amigo
                        </Link>
                    </div>
                </div>
                <div class="hero__image">
                    <img src="/images/inicio_header.png" alt="Bienvenido a Club de Fantasías" />
                    <div class="hero__image-overlay"></div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- QUICK STATS (SIN MODO ACTIVO) -->
            <!-- ============================================================ -->
            <section class="quick-stats">
                <Link v-for="stat in quickStatsFiltradas" :key="stat.titulo" :href="stat.href || '#'" class="stat-card">
                    <div class="stat-card__icon-wrapper">
                        <span class="stat-card__icon"><i class="pi" :class="stat.icon"></i></span>
                    </div>
                    <div class="stat-card__body">
                        <span class="stat-card__title">
                            {{ stat.titulo }}
                            <PvBadge v-if="stat.badge" :value="stat.badge" severity="danger" />
                            <i v-if="stat.verificado" class="pi pi-check-circle verified-check"></i>
                        </span>
                        <span class="stat-card__desc">{{ stat.desc }}</span>
                    </div>
                    <div class="stat-card__arrow">
                        <i class="pi pi-chevron-right"></i>
                    </div>
                </Link>
            </section>

            <!-- ============================================================ -->
            <!-- SEPARADOR DECORATIVO -->
            <!-- ============================================================ -->
            <div class="section-divider">
                <div class="divider-line"></div>
                <span class="divider-diamond">◆</span>
                <div class="divider-line"></div>
            </div>

            <!-- ============================================================ -->
            <!-- ACTIVIDAD RECIENTE - MATCHES Y MÁS -->
            <!-- ============================================================ -->
            <section class="section section--activity">
                <div class="section__header">
                    <div class="section__header-left">
                        <span class="section__badge">Actividad reciente</span>
                        <h2 class="section__title">Lo que ha pasado</h2>
                        <p class="section__subtitle">Nuevas coincidencias, mensajes y eventos cercanos.</p>
                    </div>
                    <Link :href="rutaSegura('descubrir', '/descubrir')" class="section__see-all">
                        Ver todas las coincidencias
                        <i class="pi pi-arrow-right"></i>
                    </Link>
                </div>

                <div class="activity-grid">
                    <div v-if="actividadReciente.length === 0" class="empty-state">
                        <div class="empty-state__content">
                            <i class="pi pi-bell empty-state__icon"></i>
                            <p>No hay actividad reciente.</p>
                            <span class="empty-state__sub">¡Conecta con alguien para empezar!</span>
                        </div>
                    </div>

                    <div v-for="actividad in actividadReciente" :key="actividad.titulo" class="activity-card">
                        <div class="activity-card__icon" :class="{
                            'activity-card__icon--match': actividad.icon === 'pi-heart-fill',
                            'activity-card__icon--message': actividad.icon === 'pi-comment',
                            'activity-card__icon--event': actividad.icon === 'pi-calendar'
                        }">
                            <i class="pi" :class="actividad.icon"></i>
                        </div>
                        <div class="activity-card__body">
                            <h4 class="activity-card__title">{{ actividad.titulo }}</h4>
                            <p class="activity-card__desc">{{ actividad.desc }}</p>
                            <span class="activity-card__time">{{ actividad.tiempo }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- SEPARADOR DECORATIVO -->
            <!-- ============================================================ -->
            <div class="section-divider">
                <div class="divider-line"></div>
                <span class="divider-diamond">◆</span>
                <div class="divider-line"></div>
            </div>

            <!-- ============================================================ -->
            <!-- COMUNIDAD -->
            <!-- ============================================================ -->
            <section class="section section--community">
                <div class="section__header">
                    <div class="section__header-left">
                        <span class="section__badge">Comunidad activa</span>
                        <h2 class="section__title">Lo que comparten los miembros</h2>
                        <p class="section__subtitle">Descubre historias, fotos y conversaciones de nuestra comunidad.
                        </p>
                    </div>
                    <Link :href="rutaSegura('comunidad.index', '/comunidad')" class="section__see-all">
                        Ver toda la comunidad
                        <i class="pi pi-arrow-right"></i>
                    </Link>
                </div>

                <div class="community-grid">
                    <div v-if="publicacionesRecientes.length === 0" class="empty-state">
                        <div class="empty-state__content">
                            <i class="pi pi-users empty-state__icon"></i>
                            <p>No hay publicaciones en la comunidad aún.</p>
                            <span class="empty-state__sub">Sé el primero en compartir algo</span>
                        </div>
                    </div>

                    <div v-for="publicacion in publicacionesRecientes" :key="publicacion.id" class="community-card">
                        <div class="community-card__header">
                            <div class="community-card__user">
                                <div class="community-card__avatar">
                                    <PvAvatar :image="publicacion.usuario.avatar" shape="circle" size="large" />
                                    <span v-if="publicacion.usuario.verificado" class="avatar-verified">
                                        <i class="pi pi-check-circle"></i>
                                    </span>
                                </div>
                                <div class="community-card__user-info">
                                    <div class="community-card__user-name">
                                        <strong>{{ publicacion.usuario.nombre }}</strong>
                                        <span v-if="publicacion.usuario.es_creador" class="creator-tag">Creador</span>
                                    </div>
                                    <span class="community-card__user-handle">@{{ publicacion.usuario.apodo }}</span>
                                </div>
                            </div>
                            <span class="community-card__time">{{ publicacion.tiempo }}</span>
                        </div>

                        <div class="community-card__content">
                            <p v-if="publicacion.texto" class="community-card__text">{{ publicacion.texto }}</p>

                            <div v-if="publicacion.es_imagen && publicacion.imagen" class="community-card__media">
                                <img :src="publicacion.imagen" :alt="publicacion.texto || 'Publicación'" loading="lazy"
                                    @error="(e) => e.target.src = '/images/shared/image-default.jpg'" />
                            </div>

                            <div v-if="publicacion.es_video && publicacion.imagen"
                                class="community-card__media community-card__media--video">
                                <video controls>
                                    <source :src="publicacion.imagen" />
                                    Tu navegador no soporta la reproducción de vídeos.
                                </video>
                            </div>
                        </div>

                        <div class="community-card__footer">
                            <div class="community-card__stats">
                                <span class="community-card__stat">
                                    <i class="pi pi-heart"></i> {{ publicacion.likes }}
                                </span>
                                <span class="community-card__stat">
                                    <i class="pi pi-comment"></i> {{ publicacion.comentarios_count }}
                                </span>
                            </div>
                            <Link :href="comunidadShowRoute(publicacion.id)" class="community-card__link">
                                Ver publicación
                                <i class="pi pi-arrow-right"></i>
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- SEPARADOR DECORATIVO -->
            <!-- ============================================================ -->
            <div class="section-divider">
                <div class="divider-line"></div>
                <span class="divider-diamond">◆</span>
                <div class="divider-line"></div>
            </div>

            <!-- ============================================================ -->
            <!-- EVENTOS -->
            <!-- ============================================================ -->
            <section class="section section--events">
                <div class="section__header">
                    <div class="section__header-left">
                        <span class="section__badge">Experiencias exclusivas</span>
                        <h2 class="section__title">Próximos eventos</h2>
                        <p class="section__subtitle">Vive experiencias únicas en un ambiente seguro y selecto.</p>
                    </div>
                    <Link :href="rutaSegura('eventos.index', '/eventos')" class="section__see-all">
                        Ver todos los eventos
                        <i class="pi pi-arrow-right"></i>
                    </Link>
                </div>

                <div class="event-grid">
                    <div v-if="eventos.length === 0" class="empty-state">
                        <div class="empty-state__content">
                            <i class="pi pi-calendar empty-state__icon"></i>
                            <p>No hay eventos próximos disponibles.</p>
                            <span class="empty-state__sub">Pronto tendremos nuevos eventos para ti</span>
                        </div>
                    </div>

                    <div v-for="evento in eventos" :key="evento.id" class="event-card">
                        <div class="event-card__image">
                            <img :src="evento.imagen" :alt="evento.nombre" loading="lazy" />
                            <div class="event-card__date">
                                <span class="event-card__day">{{ evento.dia || '00' }}</span>
                                <span class="event-card__month">{{ evento.mes_abreviado || 'MES' }}</span>
                            </div>
                            <div v-if="evento.casi_lleno" class="event-card__badge event-card__badge--warning">
                                Últimos lugares
                            </div>
                            <div v-if="evento.esta_lleno" class="event-card__badge event-card__badge--danger">
                                Completado
                            </div>
                            <div v-if="evento.es_gratis" class="event-card__badge event-card__badge--free">
                                Gratis
                            </div>
                        </div>
                        <div class="event-card__body">
                            <div class="event-card__header">
                                <h3 class="event-card__title">{{ evento.nombre }}</h3>
                                <span class="event-card__price"
                                    :class="{ 'event-card__price--free': evento.es_gratis }">
                                    {{ evento.precio_formateado || formatearPrecio(evento.precio) }}
                                </span>
                            </div>
                            <div class="event-card__details">
                                <span class="event-card__detail">
                                    <i class="pi pi-map-marker"></i>
                                    {{ evento.ciudad || 'Ciudad no especificada' }}
                                </span>
                                <span class="event-card__detail">
                                    <i class="pi pi-clock"></i>
                                    {{ evento.fecha_completa || evento.fecha_corta || 'Fecha por definir' }}
                                </span>
                            </div>
                            <div v-if="!evento.esta_lleno" class="event-card__availability">
                                <div class="event-card__progress">
                                    <div class="event-card__progress-bar"
                                        :style="{ width: Math.min(evento.porcentaje_ocupado || 0, 100) + '%' }"></div>
                                </div>
                                <span class="event-card__availability-text">
                                    {{ evento.disponibles || 0 }} lugares disponibles
                                </span>
                            </div>
                            <div v-else class="event-card__availability event-card__availability--full">
                                <span class="event-card__availability-text">Sin lugares disponibles</span>
                            </div>
                            <Link :href="rutaSegura('eventos.show', '/eventos/' + evento.id)" class="event-card__btn">
                                Más información
                                <i class="pi pi-chevron-right"></i>
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- SEPARADOR DECORATIVO -->
            <!-- ============================================================ -->
            <div class="section-divider">
                <div class="divider-line"></div>
                <span class="divider-diamond">◆</span>
                <div class="divider-line"></div>
            </div>

            <!-- ============================================================ -->
            <!-- MENSAJES RECIENTES -->
            <!-- ============================================================ -->
            <section class="section section--messages">
                <div class="section__header">
                    <div class="section__header-left">
                        <span class="section__badge">Conversaciones</span>
                        <h2 class="section__title">Mensajes recientes</h2>
                        <p class="section__subtitle">Tus conversaciones más recientes con otros miembros.</p>
                    </div>
                    <Link :href="rutaSegura('mensajes', '/mensajes')" class="section__see-all">
                        Ver todos los mensajes
                        <i class="pi pi-arrow-right"></i>
                    </Link>
                </div>

                <div class="messages-container">
                    <div v-if="mensajesRecientes.length === 0" class="empty-state">
                        <div class="empty-state__content">
                            <i class="pi pi-comment empty-state__icon"></i>
                            <p>No tienes mensajes aún.</p>
                            <span class="empty-state__sub">Conecta con alguien para empezar a conversar</span>
                        </div>
                    </div>
                    <div v-else class="message-list">
                        <div v-for="msg in mensajesRecientes" :key="msg.nombre" class="message-item">
                            <div class="message-item__avatar">
                                <PvAvatar :image="msg.avatar" shape="circle" size="large" />
                            </div>
                            <div class="message-item__body">
                                <div class="message-item__top">
                                    <strong class="message-item__name">{{ msg.nombre }}</strong>
                                    <span class="message-item__time">{{ msg.hora }}</span>
                                </div>
                                <span class="message-item__preview">{{ msg.preview }}</span>
                            </div>
                            <PvBadge v-if="msg.noLeidos" :value="msg.noLeidos" severity="danger"
                                class="message-item__badge" />
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- SEPARADOR DECORATIVO -->
            <!-- ============================================================ -->
            <div class="section-divider">
                <div class="divider-line"></div>
                <span class="divider-diamond">◆</span>
                <div class="divider-line"></div>
            </div>

            <!-- ============================================================ -->
            <!-- CTA COMPLETAR PERFIL -->
            <!-- ============================================================ -->
            <section v-if="!usuario.tiene_perfil" class="cta-section">
                <div class="cta-section__bg">
                    <img src="/images/completa.png" alt="Completa tu perfil" class="cta-section__image"
                        loading="lazy" />
                    <div class="cta-section__overlay"></div>
                </div>
                <div class="cta-section__content">
                    <span class="cta-section__tag">Perfil incompleto</span>
                    <h2 class="cta-section__title">
                        Completa tu perfil y <span>mejora tus conexiones</span>
                    </h2>
                    <p class="cta-section__text">
                        Añade más fotos, intereses y preferencias para recibir mejores coincidencias
                        y acceder a eventos exclusivos.
                    </p>
                    <Link :href="rutaSegura('perfil.completar', '/perfil/completar')" class="cta-section__btn">
                        Completar perfil ahora
                        <i class="pi pi-arrow-right"></i>
                    </Link>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- FOOTER DECORATIVO -->
            <!-- ============================================================ -->
            <div class="page-footer">
                <div class="footer-decoration">
                    <span class="footer-diamond">◆</span>
                    <span class="footer-line"></span>
                    <span class="footer-diamond">◆</span>
                    <span class="footer-line"></span>
                    <span class="footer-diamond">◆</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA - ARREGLADO
   ========================================================================= */
.cf-page {
    --brand: #C81E3A;
    --brand-dark: #A6152D;
    --brand-soft: #FBEAEC;
    --brand-light: #FDE8EB;
    --ink: #171412;
    --ink-soft: #4B4744;
    --muted: #8A8481;
    --muted-light: #B7B2AF;
    --line: #ECE9E7;
    --surface: #FAF8F7;
    --white: #FFFFFF;
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.06);
    --shadow-md: 0 8px 30px rgba(0, 0, 0, 0.08);
    --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.10);
    --shadow-xl: 0 30px 80px rgba(0, 0, 0, 0.12);

    --font-serif: 'Fraunces', Georgia, serif;
    --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;

    --container: 1240px;
    --radius-sm: 10px;
    --radius-md: 16px;
    --radius-lg: 24px;
    --radius-full: 999px;

    font-family: var(--font-sans);
    color: var(--ink);
    background: var(--surface);
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
   SEPARADORES DECORATIVOS
   ========================================================================= */
.section-divider {
    max-width: 1400px;
    margin: 3rem auto;
    padding: 0 2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.divider-line {
    flex: 1;
    height: 1px;
    background: linear-gradient(to right, transparent, var(--line), transparent);
}

.divider-diamond {
    font-size: 0.6rem;
    color: var(--muted-light);
    opacity: 0.5;
    letter-spacing: 0.1em;
}

.page-footer {
    max-width: 1400px;
    margin: 0 auto 2rem;
    padding: 0 2rem;
}

.footer-decoration {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
    opacity: 0.3;
}

.footer-diamond {
    font-size: 0.5rem;
    color: var(--muted-light);
}

.footer-line {
    width: 60px;
    height: 1px;
    background: var(--line);
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
    min-height: 450px;
    box-shadow: var(--shadow-xl);
    background: var(--ink);
    position: relative;
}

.hero__content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 3.5rem 3.5rem;
    color: #ffffff;
    background: var(--ink);
    position: relative;
    z-index: 2;
}

.hero__top {
    margin-bottom: 1rem;
}

.hero__greeting {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: rgba(255, 255, 255, 0.4);
    display: block;
    margin-bottom: 0.5rem;
}

.hero__title {
    font-family: var(--font-serif);
    font-size: 2.8rem;
    font-weight: 500;
    line-height: 1.1;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.hero__verified {
    color: #48BB78;
    font-size: 1.2rem;
}

.hero__creator-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: linear-gradient(135deg, #7C3AED, #8B5CF6);
    padding: 0.2rem 0.8rem;
    border-radius: var(--radius-full);
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.05em;
}

.hero__description {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.95rem;
    max-width: 480px;
    margin: 0.5rem 0 2rem;
    line-height: 1.7;
}

.hero__actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.hero__cta {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    background: linear-gradient(135deg, #C81E3A 0%, #E85A72 100%);
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.88rem;
    padding: 0.85rem 1.8rem;
    border-radius: var(--radius-full);
    box-shadow: 0 8px 24px rgba(200, 30, 58, 0.35);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.hero__cta:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(200, 30, 58, 0.45);
}

.hero__cta--secondary {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    box-shadow: none;
    border: 1px solid rgba(255, 255, 255, 0.15);
}

.hero__cta--secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}

.hero__image {
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: var(--ink);
    position: relative;
}

.hero__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
    transition: transform 0.6s ease;
}

.hero__image-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to left, transparent 0%, var(--ink) 100%);
    opacity: 0.3;
}

.hero:hover .hero__image img {
    transform: scale(1.05);
}

/* =========================================================================
   QUICK STATS
   ========================================================================= */
.quick-stats {
    max-width: 1400px;
    margin: 2rem auto 0;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}

.stat-card {
    background: var(--white);
    border-radius: var(--radius-md);
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--line);
    text-decoration: none;
    color: inherit;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;
    background: var(--brand);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
    border-color: transparent;
}

.stat-card:hover::before {
    opacity: 1;
}

.stat-card__icon-wrapper {
    flex-shrink: 0;
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

.stat-card__arrow {
    color: var(--muted-light);
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.stat-card:hover .stat-card__arrow {
    color: var(--brand);
    transform: translateX(4px);
}

/* =========================================================================
   SECCIONES
   ========================================================================= */
.section {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 2rem;
}

.section__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 1.5rem;
    gap: 1rem;
}

.section__header-left {
    flex: 1;
}

.section__badge {
    display: inline-block;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: var(--brand);
    background: var(--brand-soft);
    padding: 0.2rem 0.8rem;
    border-radius: var(--radius-full);
    margin-bottom: 0.4rem;
}

.section__title {
    font-family: var(--font-serif);
    font-size: 1.5rem;
    font-weight: 500;
    margin: 0 0 0.2rem;
}

.section__subtitle {
    font-size: 0.85rem;
    color: var(--muted);
    margin: 0;
}

.section__see-all {
    color: var(--brand);
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.section__see-all:hover {
    color: var(--brand-dark);
    gap: 0.7rem;
}

.section--community,
.section--events,
.section--activity {
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: 2rem !important;
    box-shadow: var(--shadow-sm);
    transition: box-shadow 0.3s ease;
}

.section--community:hover,
.section--events:hover,
.section--activity:hover {
    box-shadow: var(--shadow-md);
}

/* =========================================================================
   ACTIVIDAD - MATCHES
   ========================================================================= */
.activity-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.activity-card {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: var(--surface);
    border-radius: var(--radius-sm);
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.activity-card:hover {
    background: var(--white);
    border-color: var(--line);
    box-shadow: var(--shadow-sm);
}

.activity-card__icon {
    width: 38px;
    height: 38px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
    color: var(--white);
}

.activity-card__icon--match {
    background: linear-gradient(135deg, #DC2626, #EF4444);
}

.activity-card__icon--message {
    background: linear-gradient(135deg, #2563EB, #3B82F6);
}

.activity-card__icon--event {
    background: linear-gradient(135deg, #059669, #10B981);
}

.activity-card__body {
    flex: 1;
}

.activity-card__title {
    font-size: 0.85rem;
    font-weight: 600;
    margin: 0 0 0.15rem;
    color: var(--ink);
}

.activity-card__desc {
    font-size: 0.78rem;
    color: var(--muted);
    margin: 0 0 0.25rem;
    line-height: 1.4;
}

.activity-card__time {
    font-size: 0.65rem;
    color: var(--muted-light);
}

/* =========================================================================
   COMUNIDAD
   ========================================================================= */
.community-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

.community-card {
    background: var(--white);
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    border: 1px solid var(--line);
}

.community-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-lg);
    border-color: transparent;
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

.community-card__avatar {
    position: relative;
    flex-shrink: 0;
}

.avatar-verified {
    position: absolute;
    bottom: -2px;
    right: -2px;
    color: #48BB78;
    font-size: 0.6rem;
    background: var(--white);
    border-radius: 50%;
    padding: 1px;
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
    gap: 0.4rem;
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

.creator-tag {
    font-size: 0.5rem;
    font-weight: 700;
    color: #7C3AED;
    background: #EDE9FE;
    padding: 0.05rem 0.5rem;
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

.community-card__footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 1.25rem 0.75rem 1.25rem;
    border-top: 1px solid var(--line);
    margin: 0 1.25rem 0.5rem 1.25rem;
}

.community-card__stats {
    display: flex;
    gap: 1.25rem;
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

.community-card__link {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--brand);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.2rem;
    transition: all 0.3s ease;
}

.community-card__link:hover {
    color: var(--brand-dark);
    gap: 0.5rem;
}

/* =========================================================================
   EVENTOS
   ========================================================================= */
.event-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
}

.event-card {
    background: var(--white);
    border-radius: var(--radius-md);
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    border: 1px solid var(--line);
}

.event-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-lg);
    border-color: transparent;
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
    padding: 0.3rem 0.6rem;
    text-align: center;
    min-width: 44px;
    box-shadow: 0 4px 12px rgba(200, 30, 58, 0.3);
}

.event-card__day {
    display: block;
    font-size: 1.1rem;
    font-weight: 700;
    line-height: 1.1;
}

.event-card__month {
    font-size: 0.6rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    opacity: 0.8;
}

.event-card__badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 0.2rem 0.7rem;
    border-radius: var(--radius-full);
    font-size: 0.6rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #ffffff;
}

.event-card__badge--warning {
    background: #F59E0B;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.event-card__badge--danger {
    background: #EF4444;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.event-card__badge--free {
    background: #48BB78;
    box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
}

.event-card__body {
    padding: 1rem 1.25rem 1.25rem;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.event-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.event-card__title {
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0;
    flex: 1;
    line-height: 1.2;
}

.event-card__price {
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--ink-soft);
    padding: 0.15rem 0.5rem;
    background: var(--surface);
    border-radius: var(--radius-full);
    white-space: nowrap;
    flex-shrink: 0;
}

.event-card__price--free {
    color: #48BB78;
    background: #F0FFF4;
}

.event-card__details {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    margin-bottom: 0.5rem;
}

.event-card__detail {
    font-size: 0.75rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.event-card__detail i {
    font-size: 0.7rem;
    width: 0.9rem;
    text-align: center;
}

.event-card__availability {
    margin-top: 0.3rem;
    margin-bottom: 0.75rem;
}

.event-card__availability--full {
    opacity: 0.6;
}

.event-card__progress {
    width: 100%;
    height: 4px;
    background: var(--line);
    border-radius: var(--radius-full);
    overflow: hidden;
    margin-bottom: 0.25rem;
}

.event-card__progress-bar {
    height: 100%;
    background: var(--brand);
    border-radius: var(--radius-full);
    transition: width 0.6s ease;
}

.event-card__availability-text {
    font-size: 0.65rem;
    color: var(--muted);
}

.event-card__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
    width: 100%;
    margin-top: auto;
    padding: 0.5rem 0.75rem;
    font-weight: 600;
    font-size: 0.78rem;
    border-radius: var(--radius-sm);
    border: 1.5px solid var(--line);
    color: var(--ink-soft);
    background: transparent;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
}

.event-card__btn:hover {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
    transform: translateY(-2px);
}

.event-card__btn i {
    font-size: 0.7rem;
    transition: transform 0.3s ease;
}

.event-card__btn:hover i {
    transform: translateX(4px);
}

/* =========================================================================
   MENSAJES
   ========================================================================= */
.messages-container {
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--line);
    transition: box-shadow 0.3s ease;
}

.messages-container:hover {
    box-shadow: var(--shadow-md);
}

.message-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.message-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6rem 0.75rem;
    border-radius: var(--radius-sm);
    transition: all 0.3s ease;
    cursor: default;
    position: relative;
}

.message-item:hover {
    background: var(--surface);
}

.message-item__avatar {
    flex-shrink: 0;
}

.message-item__body {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
}

.message-item__top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
}

.message-item__name {
    font-size: 0.85rem;
    color: var(--ink);
}

.message-item__time {
    font-size: 0.65rem;
    color: var(--muted-light);
    flex-shrink: 0;
}

.message-item__preview {
    font-size: 0.78rem;
    color: var(--muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.message-item__badge {
    flex-shrink: 0;
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
   CTA
   ========================================================================= */
.cta-section {
    position: relative;
    max-width: 1400px;
    margin: 0 auto;
    border-radius: var(--radius-lg);
    overflow: hidden;
    min-height: 300px;
    display: flex;
    align-items: center;
    box-shadow: var(--shadow-lg);
}

.cta-section__bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

.cta-section__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
    transition: transform 0.6s ease;
}

.cta-section:hover .cta-section__image {
    transform: scale(1.05);
}

.cta-section__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(12, 12, 14, 0.9) 0%, rgba(12, 12, 14, 0.4) 60%, rgba(12, 12, 14, 0.1) 100%);
    z-index: 1;
}

.cta-section__content {
    position: relative;
    z-index: 2;
    padding: 3rem 3.5rem;
    max-width: 560px;
    color: #ffffff;
}

.cta-section__tag {
    display: inline-block;
    font-size: 0.6rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: rgba(255, 255, 255, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.15);
    padding: 0.2rem 1rem;
    border-radius: var(--radius-full);
    margin-bottom: 0.8rem;
}

.cta-section__title {
    font-family: var(--font-serif);
    font-size: 2rem;
    font-weight: 400;
    margin: 0 0 0.75rem;
    line-height: 1.2;
}

.cta-section__title span {
    color: var(--brand);
    font-weight: 700;
    font-style: italic;
}

.cta-section__text {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.7);
    line-height: 1.7;
    margin: 0 0 1.5rem;
}

.cta-section__btn {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
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

.cta-section__btn:hover {
    background: var(--brand-dark);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(200, 30, 58, 0.3);
    gap: 0.8rem;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1300px) {
    .event-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 1100px) {
    .community-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .event-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .activity-grid {
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

    .hero__title {
        font-size: 2.2rem;
    }

    .hero__image {
        height: 300px;
    }

    .cta-section__content {
        padding: 2.5rem;
    }

    .cta-section__title {
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

    .section--community,
    .section--events,
    .section--activity {
        padding: 1.5rem 1rem !important;
    }

    .section__header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .hero__content {
        padding: 2rem 1.5rem;
    }

    .hero__title {
        font-size: 1.8rem;
        flex-direction: column;
        align-items: flex-start;
    }

    .hero__description {
        font-size: 0.85rem;
    }

    .hero__image {
        height: 250px;
    }

    .hero__actions {
        flex-direction: column;
        width: 100%;
    }

    .hero__cta {
        width: 100%;
        justify-content: center;
    }

    .cta-section {
        min-height: 240px;
    }

    .cta-section__content {
        padding: 2rem 1.5rem;
        max-width: 100%;
    }

    .cta-section__title {
        font-size: 1.4rem;
    }

    .cta-section__text {
        font-size: 0.85rem;
    }

    .cta-section__btn {
        width: 100%;
        justify-content: center;
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

    .community-card__footer {
        margin: 0 1rem 0.25rem 1rem;
        padding: 0.35rem 0 0.5rem 0;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .community-card__media img,
    .community-card__media--video video {
        height: 200px;
    }

    .messages-container {
        padding: 1rem;
    }

    .section-divider {
        padding: 0 1rem;
        margin: 2rem auto;
    }

    .page-footer {
        padding: 0 1rem;
    }
}

@media (max-width: 480px) {
    .hero__content {
        padding: 1.5rem 1rem;
    }

    .hero__title {
        font-size: 1.5rem;
    }

    .hero__image {
        height: 200px;
    }

    .cta-section__content {
        padding: 1.5rem 1rem;
    }

    .cta-section__title {
        font-size: 1.2rem;
    }

    .stat-card {
        padding: 0.9rem 1rem;
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

    .message-item__top {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.1rem;
    }

    .message-item__body {
        min-width: 0;
    }

    .activity-card {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>