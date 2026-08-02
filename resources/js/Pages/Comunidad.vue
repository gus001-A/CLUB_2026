<script setup>
import { reactive } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Footer from '@/Components/Footer.vue';

/* ---------------------------------------------------------------
 * Usuario / cabecera
 * --------------------------------------------------------------- */
const usuario = {
    nombre: 'Alexandra',
    avatar: '/images/comunidad/avatar-alexandra.jpg',
    verificado: true,
};

/* ---------------------------------------------------------------
 * Métricas rápidas
 * --------------------------------------------------------------- */
const metricas = [
    { icon: 'pi-wave-pulse', titulo: 'Feed activo', desc: 'Publicaciones, fotos y conversaciones nuevas cada minuto.', valor: '2.4K', etiqueta: 'nuevas hoy' },
    { icon: 'pi-users', titulo: 'Creadores', desc: 'Comparte y accede a contenido exclusivo de creadores verificados.', valor: '187', etiqueta: 'creadores activos' },
    { icon: 'pi-lock', titulo: 'Grupos privados', desc: 'Únete a comunidades privadas según tus intereses y estilo.', valor: '63', etiqueta: 'grupos activos' },
    { icon: 'pi-crown', titulo: 'Suscripciones', desc: 'Apoya a tus creadores favoritos y disfruta contenido premium.', valor: '5', etiqueta: 'activas' },
];

/* ---------------------------------------------------------------
 * Publicaciones del feed
 * --------------------------------------------------------------- */
const nuevaPublicacion = reactive({ texto: '' });

const publicaciones = [
    {
        autor: 'Isabella',
        rol: 'Creadora',
        avatar: '/images/comunidad/avatar-isabella.jpg',
        tiempo: '2 h',
        texto: 'Nada como una buena conversación, una copa de vino y personas con buena energía.\n¿Planes para el fin de semana? 🍷',
        imagen: '/images/comunidad/post-isabella.jpg',
        likes: 126,
        comentarios: 18,
        premium: false,
    },
    {
        autor: 'Mateo',
        rol: 'Creador',
        avatar: '/images/comunidad/avatar-mateo.jpg',
        tiempo: '5 h',
        texto: 'Nueva galería disponible para mis suscriptores. Momentos que no verás en ningún lado.\nGracias por su apoyo ❤️',
        imagen: '/images/comunidad/post-mateo-blur.jpg',
        likes: 92,
        comentarios: 7,
        premium: true,
    },
];

/* ---------------------------------------------------------------
 * Temas en tendencia
 * --------------------------------------------------------------- */
const temasTendencia = [
    { texto: 'ConexionesReales', fuego: true },
    { texto: 'PlanesDelFin', fuego: false },
    { texto: 'ViajesYExperiencias', fuego: false },
    { texto: 'CharlasSinFiltro', fuego: false },
    { texto: 'EventosCD', fuego: false },
    { texto: 'Lifestyle', fuego: false },
    { texto: 'NuevosAmigos', fuego: false },
    { texto: 'MomentosVip', fuego: false },
    { texto: 'Recomendaciones', fuego: false },
    { texto: 'HistoriasReales', fuego: false },
];

/* ---------------------------------------------------------------
 * Sidebar: creadores sugeridos
 * --------------------------------------------------------------- */
const creadoresSugeridos = [
    { nombre: 'Valeria S.', avatar: '/images/comunidad/avatar-valeria.jpg', suscriptores: '28.4K' },
    { nombre: 'Daniel A.', avatar: '/images/comunidad/avatar-daniel.jpg', suscriptores: '19.7K' },
    { nombre: 'Camila R.', avatar: '/images/comunidad/avatar-camila.jpg', suscriptores: '15.2K' },
];

/* ---------------------------------------------------------------
 * Sidebar: suscripciones activas
 * --------------------------------------------------------------- */
const suscripcionesActivas = [
    { nombre: 'Isabella', avatar: '/images/comunidad/avatar-isabella.jpg', renovacion: '24 May, 2024', enLinea: true },
    { nombre: 'Mateo', avatar: '/images/comunidad/avatar-mateo.jpg', renovacion: '17 May, 2024', enLinea: false },
    { nombre: 'Valeria S.', avatar: '/images/comunidad/avatar-valeria.jpg', renovacion: '10 May, 2024', enLinea: true },
];

/* ---------------------------------------------------------------
 * Sidebar: próximos eventos de la comunidad
 * --------------------------------------------------------------- */
const proximosEventos = [
    { dia: '25', mes: 'MAY', titulo: 'Cócteles & Conexiones', lugar: 'Bogotá, Colombia', fecha: 'Sáb, 25 May · 8:00 PM', imagen: '/images/comunidad/evento-cocteles.jpg' },
    { dia: '01', mes: 'JUN', titulo: 'Sunset Experience', lugar: 'Medellín, Colombia', fecha: 'Sáb, 01 Jun · 6:30 PM', imagen: '/images/comunidad/evento-sunset.jpg' },
    { dia: '08', mes: 'JUN', titulo: 'Noche de conversa', lugar: 'Cali, Colombia', fecha: 'Sáb, 08 Jun · 9:00 PM', imagen: '/images/comunidad/evento-conversa.jpg' },
];

function publicar() {
    if (!nuevaPublicacion.texto.trim()) return;
    // TODO: router.post(route('comunidad.publicar'), { texto: nuevaPublicacion.texto })
    nuevaPublicacion.texto = '';
}
</script>

<template>
    <Head title="Comunidad" />

    <AppLayout
        active-nav="comunidad"
        :usuario="usuario"
        :notificaciones="5"
        :favoritos="2"
        :mensajes="3"
    >
        <div class="comunidad-page">
            <!-- ============================================================ -->
            <!-- HERO -->
            <!-- ============================================================ -->
            <section class="hero">
                <div class="hero__content">
                    <p class="hero__eyebrow">Bienvenido a la comunidad, <strong>{{ usuario.nombre }}</strong></p>
                    <h1>Tu <span>comunidad</span> para conectar, compartir y descubrir.</h1>
                    <p class="hero__desc">Conecta con personas reales, comparte experiencias, disfruta contenido exclusivo y vive momentos inolvidables.</p>
                    <div class="hero__actions">
                        <PvButton label="EXPLORAR COMUNIDAD" icon="pi pi-users" />
                        <PvButton label="CREAR PUBLICACIÓN" icon="pi pi-pencil" iconPos="right" outlined severity="secondary" />
                    </div>
                </div>
                <div class="hero__image">
                    <img src="/images/comunidad/hero-grupo.jpg" alt="Comunidad Club de Fantasías" />
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- MÉTRICAS -->
            <!-- ============================================================ -->
            <section class="metrics-row">
                <div v-for="m in metricas" :key="m.titulo" class="metric-card">
                    <span class="metric-card__icon"><i class="pi" :class="m.icon"></i></span>
                    <strong>{{ m.titulo }}</strong>
                    <p>{{ m.desc }}</p>
                    <div class="metric-card__value">
                        <span class="value">{{ m.valor }}</span>
                        <span class="label">{{ m.etiqueta }}</span>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- CONTENIDO PRINCIPAL -->
            <!-- ============================================================ -->
            <div class="content-grid">
                <!-- ------------------------- FEED ------------------------- -->
                <div class="feed-column">
                    <!-- Crear publicación -->
                    <div class="composer-card">
                        <div class="composer-card__header">
                            <PvAvatar :image="usuario.avatar" shape="circle" size="large" />
                            <strong>Crear publicación</strong>
                        </div>
                        <textarea
                            v-model="nuevaPublicacion.texto"
                            class="composer-card__input"
                            placeholder="¿Qué quieres compartir con la comunidad?"
                            rows="2"
                        ></textarea>
                        <div class="composer-card__actions">
                            <button class="composer-action"><i class="pi pi-image"></i> Foto</button>
                            <button class="composer-action"><i class="pi pi-video"></i> Video</button>
                            <button class="composer-action"><i class="pi pi-chart-bar"></i> Encuesta</button>
                            <button class="composer-action"><i class="pi pi-calendar"></i> Evento</button>
                            <button class="composer-action composer-action--more"><i class="pi pi-ellipsis-h"></i></button>
                            <PvButton label="Publicar" class="composer-card__submit" @click="publicar" />
                        </div>
                    </div>

                    <!-- Destacados para ti -->
                    <div class="feed-heading">
                        <h2>Destacados para ti</h2>
                        <a href="#" class="see-all">Ver todo el feed <i class="pi pi-chevron-right"></i></a>
                    </div>

                    <article v-for="post in publicaciones" :key="post.autor + post.tiempo" class="post-card">
                        <div class="post-card__header">
                            <PvAvatar :image="post.avatar" shape="circle" size="large" />
                            <div class="post-card__author">
                                <span class="name">{{ post.autor }} <i class="pi pi-verified"></i></span>
                                <span class="rol">{{ post.rol }}</span>
                            </div>
                            <span class="post-card__time">{{ post.tiempo }} · <i class="pi pi-globe"></i></span>
                            <span v-if="post.premium" class="post-card__badge"><i class="pi pi-lock"></i> Contenido exclusivo</span>
                            <button v-else class="post-card__more"><i class="pi pi-ellipsis-h"></i></button>
                        </div>

                        <p class="post-card__text">
                            <template v-for="(linea, i) in post.texto.split('\n')" :key="i">
                                {{ linea }}<br v-if="i < post.texto.split('\n').length - 1" />
                            </template>
                        </p>

                        <div class="post-card__media" :class="{ 'post-card__media--premium': post.premium }">
                            <img :src="post.imagen" :alt="post.autor" />
                            <div v-if="post.premium" class="premium-overlay">
                                <span class="premium-overlay__lock"><i class="pi pi-lock"></i></span>
                                <strong>Foto premium</strong>
                                <p>Suscríbete para ver esta foto y más contenido exclusivo.</p>
                                <PvButton label="SUSCRIBIRSE" icon="pi pi-shopping-cart" iconPos="right" />
                            </div>
                        </div>

                        <div class="post-card__footer">
                            <button><i class="pi pi-heart"></i> Me gusta <span>{{ post.likes }}</span></button>
                            <button><i class="pi pi-comment"></i> Comentarios <span>{{ post.comentarios }}</span></button>
                            <button><i class="pi pi-bookmark"></i> Guardar</button>
                            <button><i class="pi pi-share-alt"></i> Compartir</button>
                        </div>
                    </article>

                    <!-- Temas en tendencia -->
                    <div class="trending-card">
                        <div class="feed-heading">
                            <h2>Temas en tendencia</h2>
                            <a href="#" class="see-all">Ver todos</a>
                        </div>
                        <div class="trending-tags">
                            <span v-for="t in temasTendencia" :key="t.texto" class="trending-tag">
                                #{{ t.texto }} <i v-if="t.fuego" class="pi pi-bolt"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- ------------------------- SIDEBAR ------------------------- -->
                <aside class="sidebar-column">
                    <!-- Creadores sugeridos -->
                    <div class="sidebar-card">
                        <div class="sidebar-card__header">
                            <h3>Creadores sugeridos</h3>
                            <a href="#" class="see-all">Ver todos <i class="pi pi-chevron-right"></i></a>
                        </div>
                        <div class="creator-list">
                            <div v-for="c in creadoresSugeridos" :key="c.nombre" class="creator-item">
                                <PvAvatar :image="c.avatar" shape="circle" size="large" />
                                <div class="creator-item__info">
                                    <span class="name">{{ c.nombre }} <i class="pi pi-verified"></i></span>
                                    <span class="subs">{{ c.suscriptores }} suscriptores</span>
                                </div>
                                <div class="creator-item__actions">
                                    <PvButton label="Seguir" outlined class="btn-follow" />
                                    <PvButton label="Ver perfil" text class="btn-profile" />
                                </div>
                            </div>
                        </div>
                        <a href="#" class="explore-link">Explorar todos los creadores <i class="pi pi-chevron-right"></i></a>
                    </div>

                    <!-- Monetiza tu contenido -->
                    <div class="monetize-card">
                        <img src="/images/comunidad/monetiza-creadora.jpg" alt="" class="monetize-card__image" />
                        <div class="monetize-card__overlay"></div>
                        <div class="monetize-card__content">
                            <span class="monetize-card__icon"><i class="pi pi-crown"></i></span>
                            <h3>Monetiza tu contenido</h3>
                            <p>Conviértete en creador, comparte tus fotos y momentos exclusivos, ofrece galerías premium y genera ingresos con tus suscriptores.</p>
                            <PvButton label="SER CREADOR" icon="pi pi-wallet" iconPos="right" />
                        </div>
                    </div>

                    <!-- Suscripciones activas -->
                    <div class="sidebar-card">
                        <div class="sidebar-card__header">
                            <h3>Suscripciones activas</h3>
                            <a href="#" class="see-all">Ver todas <i class="pi pi-chevron-right"></i></a>
                        </div>
                        <div class="subscription-list">
                            <div v-for="s in suscripcionesActivas" :key="s.nombre" class="subscription-item">
                                <div class="subscription-item__avatar">
                                    <PvAvatar :image="s.avatar" shape="circle" size="large" />
                                    <span v-if="s.enLinea" class="online-dot"></span>
                                </div>
                                <div class="subscription-item__info">
                                    <span class="name">{{ s.nombre }} <i class="pi pi-verified"></i></span>
                                    <span class="renew">Renovación: {{ s.renovacion }}</span>
                                </div>
                                <PvTag value="Premium" severity="contrast" />
                            </div>
                        </div>
                    </div>

                    <!-- Próximos eventos -->
                    <div class="sidebar-card">
                        <div class="sidebar-card__header">
                            <h3>Próximos eventos de la comunidad</h3>
                            <a href="#" class="see-all">Ver todos</a>
                        </div>
                        <div class="event-list">
                            <div v-for="e in proximosEventos" :key="e.titulo" class="event-item">
                                <div class="event-item__date">
                                    <strong>{{ e.dia }}</strong>
                                    <span>{{ e.mes }}</span>
                                </div>
                                <div class="event-item__info">
                                    <strong>{{ e.titulo }}</strong>
                                    <span><i class="pi pi-map-marker"></i> {{ e.lugar }}</span>
                                    <span><i class="pi pi-clock"></i> {{ e.fecha }}</span>
                                </div>
                                <img :src="e.imagen" :alt="e.titulo" class="event-item__image" />
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <Footer />
    </AppLayout>
</template>

<style scoped>
:root {
    --brand-red: #c81e3a;
    --brand-red-dark: #a3172f;
}

.comunidad-page {
    font-family: 'Inter', system-ui, sans-serif;
    color: #1f2024;
}

/* ---------------- HERO ---------------- */
.hero {
    position: relative;
    display: flex;
    align-items: center;
    max-width: 1400px;
    margin: 1.5rem auto 0;
    border-radius: 16px;
    overflow: hidden;
    min-height: 320px;
    background: #0c0c0e;
}
.hero__image { position: absolute; inset: 0; z-index: 0; }
.hero__image img { width: 100%; height: 100%; object-fit: cover; }
.hero::before {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(90deg, #fff 0%, #ffffff 38%, rgba(255,255,255,0.55) 60%, rgba(255,255,255,0) 85%);
    z-index: 1;
}
.hero__content { position: relative; z-index: 2; padding: 2.75rem 3rem; max-width: 560px; }
.hero__eyebrow { font-size: 0.85rem; color: #55555a; margin: 0 0 0.6rem; }
.hero__eyebrow strong { color: var(--brand-red); }
.hero h1 { font-size: 2.2rem; font-weight: 400; line-height: 1.15; margin: 0 0 1rem; color: #1f2024; }
.hero h1 span { color: var(--brand-red); font-weight: 700; }
.hero__desc { color: #55555a; font-size: 0.92rem; max-width: 460px; margin: 0 0 1.75rem; }
.hero__actions { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
.hero__actions :deep(.p-button) { font-weight: 700; letter-spacing: 0.02em; border-radius: 8px; }

/* ---------------- MÉTRICAS ---------------- */
.metrics-row {
    max-width: 1400px;
    margin: 1.5rem auto 0;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
}
@media (max-width: 1024px) { .metrics-row { grid-template-columns: 1fr 1fr; } }
.metric-card { background: #fff; border: 1px solid #ececee; border-radius: 12px; padding: 1.25rem; }
.metric-card__icon {
    width: 34px; height: 34px; border-radius: 8px; background: #fdf1f2; color: var(--brand-red);
    display: flex; align-items: center; justify-content: center; margin-bottom: 0.7rem; font-size: 0.95rem;
}
.metric-card strong { display: block; font-size: 0.92rem; margin-bottom: 0.3rem; }
.metric-card p { font-size: 0.76rem; color: #8a8a90; margin: 0 0 0.9rem; min-height: 2.4em; }
.metric-card__value { display: flex; align-items: baseline; gap: 0.4rem; }
.metric-card__value .value { font-size: 1.4rem; font-weight: 800; color: var(--brand-red); }
.metric-card__value .label { font-size: 0.74rem; color: #8a8a90; }

/* ---------------- CONTENT GRID ---------------- */
.content-grid {
    max-width: 1400px;
    margin: 1.75rem auto 0;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 1024px) { .content-grid { grid-template-columns: 1fr; } }

.feed-column, .sidebar-column { display: flex; flex-direction: column; gap: 1.25rem; }

/* Composer */
.composer-card { background: #fff; border: 1px solid #ececee; border-radius: 12px; padding: 1.25rem; }
.composer-card__header { display: flex; align-items: center; gap: 0.7rem; margin-bottom: 0.9rem; }
.composer-card__header strong { font-size: 0.95rem; }
.composer-card__input {
    width: 100%; border: 1px solid #e3e3e7; border-radius: 8px; padding: 0.75rem 1rem;
    font-family: inherit; font-size: 0.85rem; resize: none; color: #1f2024;
}
.composer-card__actions { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.9rem; flex-wrap: wrap; }
.composer-action {
    display: flex; align-items: center; gap: 0.4rem; border: 1px solid #e3e3e7; border-radius: 8px;
    background: #fff; padding: 0.45rem 0.8rem; font-size: 0.78rem; font-weight: 600; color: #55555a; cursor: pointer;
}
.composer-action--more { padding: 0.45rem 0.6rem; }
.composer-card__submit { margin-left: auto; font-weight: 700; border-radius: 8px; }

/* Feed headings */
.feed-heading { display: flex; justify-content: space-between; align-items: center; }
.feed-heading h2 { font-size: 1.05rem; margin: 0; }
.see-all { color: var(--brand-red); font-size: 0.8rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem; }

/* Posts */
.post-card { background: #fff; border: 1px solid #ececee; border-radius: 12px; padding: 1.25rem; }
.post-card__header { display: flex; align-items: center; gap: 0.7rem; margin-bottom: 0.8rem; }
.post-card__author { display: flex; flex-direction: column; line-height: 1.2; }
.post-card__author .name { font-size: 0.9rem; font-weight: 700; display: flex; align-items: center; gap: 0.3rem; }
.post-card__author .name i { color: var(--brand-red); font-size: 0.75rem; }
.post-card__author .rol { font-size: 0.72rem; color: var(--brand-red); font-weight: 600; }
.post-card__time { margin-left: auto; font-size: 0.75rem; color: #a5a5aa; }
.post-card__more { border: none; background: none; color: #a5a5aa; cursor: pointer; font-size: 1rem; }
.post-card__badge {
    background: #fdf1f2; color: var(--brand-red); font-size: 0.72rem; font-weight: 700;
    padding: 0.3rem 0.7rem; border-radius: 999px; display: flex; align-items: center; gap: 0.3rem;
}
.post-card__text { font-size: 0.85rem; color: #2a2a2e; line-height: 1.6; margin: 0 0 1rem; white-space: pre-line; }

.post-card__media { position: relative; border-radius: 10px; overflow: hidden; aspect-ratio: 16/8; background: #111; }
.post-card__media img { width: 100%; height: 100%; object-fit: cover; }
.post-card__media--premium img { filter: blur(18px) brightness(0.7); transform: scale(1.05); }
.premium-overlay {
    position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;
    text-align: center; color: #fff; gap: 0.5rem; padding: 1rem;
}
.premium-overlay__lock {
    width: 44px; height: 44px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.6);
    display: flex; align-items: center; justify-content: center; font-size: 1.1rem; margin-bottom: 0.3rem;
}
.premium-overlay strong { font-size: 1rem; }
.premium-overlay p { font-size: 0.78rem; color: #d8d8dc; margin: 0 0 0.6rem; max-width: 280px; }
.premium-overlay :deep(.p-button) { font-weight: 700; border-radius: 8px; }

.post-card__footer { display: flex; gap: 1.5rem; margin-top: 1rem; padding-top: 0.9rem; border-top: 1px solid #f0f0f2; flex-wrap: wrap; }
.post-card__footer button {
    border: none; background: none; display: flex; align-items: center; gap: 0.4rem;
    font-size: 0.8rem; color: #55555a; cursor: pointer; font-weight: 600;
}
.post-card__footer button span { color: #a5a5aa; font-weight: 400; }

/* Trending */
.trending-card { background: #fff; border: 1px solid #ececee; border-radius: 12px; padding: 1.25rem; }
.trending-tags { display: flex; flex-wrap: wrap; gap: 0.6rem; margin-top: 1rem; }
.trending-tag {
    background: #f7f7f8; border: 1px solid #ececee; border-radius: 999px; padding: 0.45rem 0.9rem;
    font-size: 0.8rem; font-weight: 600; color: #55555a; display: flex; align-items: center; gap: 0.4rem;
}
.trending-tag i { color: var(--brand-red); font-size: 0.7rem; }

/* Sidebar */
.sidebar-card { background: #fff; border: 1px solid #ececee; border-radius: 12px; padding: 1.25rem; }
.sidebar-card__header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.sidebar-card__header h3 { font-size: 0.95rem; margin: 0; }

.creator-list { display: flex; flex-direction: column; gap: 1rem; }
.creator-item { display: flex; align-items: center; gap: 0.7rem; }
.creator-item__info { display: flex; flex-direction: column; flex: 1; min-width: 0; }
.creator-item__info .name { font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 0.3rem; }
.creator-item__info .name i { color: var(--brand-red); font-size: 0.7rem; }
.creator-item__info .subs { font-size: 0.72rem; color: #8a8a90; }
.creator-item__actions { display: flex; gap: 0.4rem; }
.btn-follow { font-size: 0.72rem; padding: 0.35rem 0.7rem; border-radius: 6px; }
.btn-profile { font-size: 0.72rem; padding: 0.35rem 0.5rem; }
.explore-link {
    display: block; text-align: left; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #f0f0f2;
    color: var(--brand-red); font-size: 0.8rem; font-weight: 700; text-decoration: none;
}

/* Monetiza */
.monetize-card { position: relative; border-radius: 12px; overflow: hidden; min-height: 260px; display: flex; align-items: flex-end; }
.monetize-card__image { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.monetize-card__overlay { position: absolute; inset: 0; background: linear-gradient(0deg, rgba(0,0,0,0.92) 30%, rgba(0,0,0,0.2) 100%); }
.monetize-card__content { position: relative; z-index: 2; padding: 1.25rem; color: #fff; }
.monetize-card__icon { color: #f2c94c; font-size: 1.1rem; margin-bottom: 0.4rem; display: block; }
.monetize-card__content h3 { font-size: 1.05rem; margin: 0 0 0.5rem; }
.monetize-card__content p { font-size: 0.78rem; color: #d8d8dc; margin: 0 0 1rem; line-height: 1.5; }
.monetize-card__content :deep(.p-button) { font-weight: 700; border-radius: 8px; }

/* Suscripciones */
.subscription-list { display: flex; flex-direction: column; gap: 1rem; }
.subscription-item { display: flex; align-items: center; gap: 0.7rem; }
.subscription-item__avatar { position: relative; }
.online-dot { position: absolute; bottom: 0; right: 0; width: 10px; height: 10px; border-radius: 50%; background: #1fbf5c; border: 2px solid #fff; }
.subscription-item__info { display: flex; flex-direction: column; flex: 1; min-width: 0; }
.subscription-item__info .name { font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 0.3rem; }
.subscription-item__info .name i { color: var(--brand-red); font-size: 0.7rem; }
.subscription-item__info .renew { font-size: 0.72rem; color: #8a8a90; }

/* Eventos */
.event-list { display: flex; flex-direction: column; gap: 1rem; }
.event-item { display: flex; align-items: center; gap: 0.75rem; }
.event-item__date {
    background: #fdf1f2; color: var(--brand-red); border-radius: 8px; padding: 0.4rem 0.6rem;
    text-align: center; line-height: 1.05; flex-shrink: 0;
}
.event-item__date strong { display: block; font-size: 1rem; }
.event-item__date span { font-size: 0.6rem; letter-spacing: 0.05em; }
.event-item__info { display: flex; flex-direction: column; flex: 1; min-width: 0; }
.event-item__info strong { font-size: 0.85rem; margin-bottom: 0.15rem; }
.event-item__info span { font-size: 0.72rem; color: #8a8a90; display: flex; align-items: center; gap: 0.3rem; }
.event-item__image { width: 52px; height: 52px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
</style>
