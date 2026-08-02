<script setup>
import { reactive, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

/* ---------------------------------------------------------------
 * Datos mock para eventos
 * --------------------------------------------------------------- */
const confianza = [
    { icon: 'pi-shield', titulo: 'Seguridad y privacidad', desc: 'Entorno 100% verificado' },
    { icon: 'pi-users', titulo: 'Comunidad selecta', desc: 'Perfiles reales y verificados' },
    { icon: 'pi-star', titulo: 'Experiencias únicas', desc: 'Momentos que recordarás' },
];

const eventoDestacado = {
    imagen: '/images/eventos/destacado-noche-seduccion.jpg',
    titulo: 'Noche de Seducción',
    vip: true,
    ciudad: 'Ciudad de México',
    fecha: 'Sábado, 31 de mayo · 23:00 hrs',
    dia: '31',
    mes: 'MAY',
};

const filtros = reactive({
    busqueda: '',
    ciudad: 'Todas',
    fecha: 'Todas',
    tipo: 'Todos',
    categoria: 'Todas',
    soloVip: false,
});

const categorias = [
    { icon: 'pi-crown', titulo: 'Fiestas privadas', imagen: '/images/eventos/cat-fiestas-privadas.jpg' },
    { icon: 'pi-circle', titulo: 'Jacuzzi Nights', imagen: '/images/eventos/cat-jacuzzi.jpg' },
    { icon: 'pi-volume-up', titulo: 'Club Nights', imagen: '/images/eventos/cat-club-nights.jpg' },
    { icon: 'pi-crown', titulo: 'Eventos VIP', imagen: '/images/eventos/cat-eventos-vip.jpg' },
    { icon: 'pi-send', titulo: 'Viajes temáticos', imagen: '/images/eventos/cat-viajes.jpg' },
    { icon: 'pi-bookmark', titulo: 'Cenas exclusivas', imagen: '/images/eventos/cat-cenas.jpg' },
];

const eventos = reactive([
    { dia: '24', mes: 'MAY', imagen: '/images/eventos/evento-noche-seduccion.jpg', titulo: 'Noche de Seducción', vip: true, ciudad: 'Ciudad de México', hora: '23:00 hrs', desc: 'Una noche para romper la rutina y dejarte llevar por la pasión.', favorito: false },
    { dia: '07', mes: 'JUN', imagen: '/images/eventos/evento-jacuzzi.jpg', titulo: 'Jacuzzi Experience', vip: true, ciudad: 'Guadalajara', hora: '20:00 hrs', desc: 'Relájate, conecta y disfruta de una experiencia única bajo las estrellas.', favorito: false },
    { dia: '21', mes: 'JUN', imagen: '/images/eventos/evento-club-fantasias.jpg', titulo: 'Club Fantasías', vip: true, ciudad: 'Monterrey', hora: '22:00 hrs', desc: 'Música, show y mucha energía para vivir una noche que recordarás siempre.', favorito: false },
    { dia: '28', mes: 'JUN', imagen: '/images/eventos/evento-sunset-lounge.jpg', titulo: 'Sunset Lounge', vip: true, ciudad: 'Cancún', hora: '19:00 hrs', desc: 'Atardecer, cocktails y buena compañía en un ambiente exclusivo.', favorito: false },
    { dia: '05', mes: 'JUL', imagen: '/images/eventos/evento-luxury-dinner.jpg', titulo: 'Luxury Dinner', vip: true, ciudad: 'Ciudad de México', hora: '21:00 hrs', desc: 'Cena gourmet, maridaje y conversaciones que despiertan los sentidos.', favorito: false },
    { dia: '12', mes: 'JUL', imagen: '/images/eventos/evento-masquerade.jpg', titulo: 'Masquerade Night', vip: true, ciudad: 'Guadalajara', hora: '23:00 hrs', desc: 'Una noche de misterio, máscaras y fantasías sin límites.', favorito: false },
]);

function toggleFavorito(evento) {
    evento.favorito = !evento.favorito;
}

const proximosParaTi = [
    { dia: '14', mes: 'JUN', imagen: '/images/eventos/mini-pool-party.jpg', titulo: 'Pool Party Privada', lugar: 'Tulum', hora: '16:00 hrs' },
    { dia: '19', mes: 'JUN', imagen: '/images/eventos/mini-noche-juegos.jpg', titulo: 'Noche de Juegos', lugar: 'Ciudad de México', hora: '22:00 hrs' },
    { dia: '26', mes: 'JUN', imagen: '/images/eventos/mini-velada-privada.jpg', titulo: 'Velada Privada', lugar: 'Monterrey', hora: '20:30 hrs' },
];

const beneficios = [
    { icon: 'pi-check-circle', titulo: 'Comunidad verificada', desc: 'Todos los asistentes son perfiles reales y verificados.' },
    { icon: 'pi-shield', titulo: 'Privacidad garantizada', desc: 'Tu privacidad es nuestra prioridad en cada experiencia.' },
    { icon: 'pi-lock', titulo: 'Acceso exclusivo', desc: 'Eventos diseñados solo para miembros seleccionados.' },
    { icon: 'pi-sparkles', titulo: 'Experiencias reales', desc: 'Momentos auténticos que conectan y trascienden.' },
];
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
                    <span class="hero__eyebrow">EVENTOS EXCLUSIVOS</span>
                    <h1>Eventos y experiencias <span>para vivir tu fantasía</span></h1>
                    <p class="hero__desc">
                        Fiestas privadas, experiencias VIP, viajes temáticos y encuentros exclusivos diseñados
                        para conectar, disfrutar y crear recuerdos inolvidables.
                    </p>
                    <div class="hero__trust">
                        <div v-for="item in confianza" :key="item.titulo" class="trust-item">
                            <span class="trust-item__icon"><i class="pi" :class="item.icon"></i></span>
                            <strong>{{ item.titulo }}</strong>
                            <span>{{ item.desc }}</span>
                        </div>
                    </div>
                </div>

                <div class="hero__featured">
                    <img :src="eventoDestacado.imagen" :alt="eventoDestacado.titulo" class="hero__featured-image" />
                    <div class="hero__featured-overlay"></div>
                    <span class="hero__featured-badge">EVENTO DESTACADO</span>

                    <div class="hero__featured-content">
                        <div class="hero__featured-date">
                            <strong>{{ eventoDestacado.dia }}</strong>
                            <span>{{ eventoDestacado.mes }}</span>
                        </div>
                        <div class="hero__featured-info">
                            <h2>{{ eventoDestacado.titulo }} <PvTag v-if="eventoDestacado.vip" value="VIP" /></h2>
                            <p><i class="pi pi-map-marker"></i> {{ eventoDestacado.ciudad }}</p>
                            <p>{{ eventoDestacado.fecha }}</p>
                        </div>
                        <PvButton label="VER EVENTO" class="hero__featured-cta" />
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- FILTROS -->
            <!-- ============================================================ -->
            <section class="filters-bar">
                <span class="filters-bar__search">
                    <i class="pi pi-search"></i>
                    <input v-model="filtros.busqueda" type="text" placeholder="Buscar eventos..." />
                </span>

                <label class="filters-bar__select">
                    <span>Ciudad</span>
                    <select v-model="filtros.ciudad">
                        <option>Todas</option>
                        <option>Ciudad de México</option>
                        <option>Guadalajara</option>
                        <option>Monterrey</option>
                        <option>Cancún</option>
                    </select>
                </label>

                <label class="filters-bar__select">
                    <span>Fecha</span>
                    <select v-model="filtros.fecha">
                        <option>Todas</option>
                        <option>Este fin de semana</option>
                        <option>Este mes</option>
                        <option>Próximo mes</option>
                    </select>
                </label>

                <label class="filters-bar__select">
                    <span>Tipo de evento</span>
                    <select v-model="filtros.tipo">
                        <option>Todos</option>
                        <option>Fiesta privada</option>
                        <option>Viaje temático</option>
                        <option>Cena exclusiva</option>
                    </select>
                </label>

                <label class="filters-bar__select">
                    <span>Categoría</span>
                    <select v-model="filtros.categoria">
                        <option>Todas</option>
                        <option v-for="c in categorias" :key="c.titulo">{{ c.titulo }}</option>
                    </select>
                </label>

                <label class="filters-bar__toggle">
                    <span>Solo eventos VIP</span>
                    <span class="toggle-switch">
                        <input type="checkbox" v-model="filtros.soloVip" />
                        <span class="toggle-slider"></span>
                    </span>
                </label>
            </section>

            <!-- ============================================================ -->
            <!-- CATEGORÍAS -->
            <!-- ============================================================ -->
            <section class="section">
                <div class="section__heading">
                    <h2>Explora por categoría</h2>
                    <a href="#" class="see-all">Ver todas las categorías <i class="pi pi-chevron-right"></i></a>
                </div>

                <div class="category-grid">
                    <button v-for="cat in categorias" :key="cat.titulo" class="category-card">
                        <img :src="cat.imagen" :alt="cat.titulo" />
                        <div class="category-card__overlay"></div>
                        <div class="category-card__content">
                            <i class="pi" :class="cat.icon"></i>
                            <span>{{ cat.titulo }}</span>
                        </div>
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
                            <h2>Todos los eventos</h2>
                            <span class="section__heading-count">24 eventos disponibles</span>
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

                    <div class="event-grid">
                        <article v-for="e in eventos" :key="e.titulo" class="event-card">
                            <div class="event-card__image">
                                <img :src="e.imagen" :alt="e.titulo" />
                                <div class="event-card__date">
                                    <strong>{{ e.dia }}</strong>
                                    <span>{{ e.mes }}</span>
                                </div>
                                <span v-if="e.vip" class="event-card__vip-badge">VIP</span>
                            </div>
                            <div class="event-card__body">
                                <h3>{{ e.titulo }}</h3>
                                <p class="event-card__meta">
                                    <i class="pi pi-map-marker"></i> {{ e.ciudad }} &nbsp;
                                    <i class="pi pi-clock"></i> {{ e.hora }}
                                </p>
                                <p class="event-card__desc">{{ e.desc }}</p>
                                <div class="event-card__footer">
                                    <PvButton label="Más información" outlined class="event-card__btn" />
                                    <button class="favorite-btn" :class="{ active: e.favorito }" @click="toggleFavorito(e)">
                                        <i class="pi" :class="e.favorito ? 'pi-heart-fill' : 'pi-heart'"></i>
                                    </button>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="ver-mas-wrap">
                        <PvButton label="VER MÁS EVENTOS" outlined />
                    </div>
                </div>

                <aside class="sidebar-column">
                    <div class="sidebar-card">
                        <div class="sidebar-card__header">
                            <h3>Próximos eventos para ti</h3>
                            <a href="#" class="see-all">Ver todos <i class="pi pi-chevron-right"></i></a>
                        </div>
                        <div class="mini-event-list">
                            <div v-for="e in proximosParaTi" :key="e.titulo" class="mini-event-item">
                                <img :src="e.imagen" :alt="e.titulo" />
                                <div class="mini-event-item__date">
                                    <strong>{{ e.dia }}</strong>
                                    <span>{{ e.mes }}</span>
                                </div>
                                <div class="mini-event-item__info">
                                    <strong>{{ e.titulo }}</strong>
                                    <span><i class="pi pi-map-marker"></i> {{ e.lugar }}</span>
                                    <span><i class="pi pi-clock"></i> {{ e.hora }}</span>
                                </div>
                                <button class="favorite-btn mini-fav"><i class="pi pi-heart"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <h3>Beneficios de nuestros eventos</h3>
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

            <!-- ============================================================ -->
            <!-- CTA PUBLICAR EVENTO -->
            <!-- ============================================================ -->
            <section class="cta-banner">
                <div class="cta-banner__bg">
                    <img src="/images/eventos/cta-pareja.jpg" alt="" class="cta-banner__bg-image" />
                    <div class="cta-banner__overlay"></div>
                </div>
                <div class="cta-banner__content">
                    <span class="cta-banner__badge">✦ PUBLICAR EVENTO</span>
                    <h2>¿Tienes un lugar o experiencia que quieres compartir?</h2>
                    <p>Publica tu evento y conecta con nuestra comunidad exclusiva.</p>
                    <PvButton label="PUBLICAR MI EVENTO" class="cta-banner__btn" />
                </div>

                <div class="cta-banner__features">
                    <div class="cta-feature">
                        <i class="pi pi-eye"></i>
                        <strong>Mayor visibilidad</strong>
                        <span>Llega a una audiencia selecta y real.</span>
                    </div>
                    <div class="cta-feature">
                        <i class="pi pi-cog"></i>
                        <strong>Administración sencilla</strong>
                        <span>Gestiona tus eventos de forma fácil y segura.</span>
                    </div>
                    <div class="cta-feature">
                        <i class="pi pi-users"></i>
                        <strong>Comunidad activa</strong>
                        <span>Conecta con personas que comparten tu estilo de vida.</span>
                    </div>
                </div>
            </section>

            <p class="security-note">
                <i class="pi pi-shield"></i>
                Todos nuestros eventos cumplen con nuestros estándares de seguridad y privacidad.
            </p>
        </div>
    </AppLayout>
</template>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA - UNIFICADOS CON LAS VISTAS ANTERIORES
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
  --success: #1fbf5c;
  --success-soft: #E8F5E9;
  --warning: #D69E2E;
  --warning-soft: #FFF8E1;
  --error: #E53E3E;

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
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.hero__eyebrow {
    color: var(--brand);
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
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
    margin: 0 0 1.75rem;
    max-width: 460px;
}

.hero__trust {
    display: flex;
    gap: 1.75rem;
    flex-wrap: wrap;
}

.trust-item {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.35rem;
    max-width: 150px;
}

.trust-item__icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1.5px solid var(--brand);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.trust-item:hover .trust-item__icon {
    background: var(--brand);
    color: var(--white);
}

.trust-item strong {
    font-size: 0.82rem;
    color: var(--ink);
}

.trust-item span {
    font-size: 0.74rem;
    color: var(--muted);
}

.hero__featured {
    position: relative;
    border-radius: var(--radius-lg);
    overflow: hidden;
    min-height: 300px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
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

.hero__featured-info p {
    font-size: 0.8rem;
    color: #d8d8dc;
    margin: 0.15rem 0;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.hero__featured-cta {
    font-weight: 700;
    border-radius: var(--radius-sm);
    align-self: flex-end;
    flex-shrink: 0;
}

.hero__featured-cta :deep(.p-button) {
    background: var(--brand);
    border-color: var(--brand);
    color: var(--white);
    padding: 0.6rem 1.5rem;
}

.hero__featured-cta :deep(.p-button:hover) {
    background: var(--brand-dark);
    border-color: var(--brand-dark);
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
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
}

.filters-bar__search {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.55rem 0.9rem;
    flex: 1 1 220px;
    color: var(--muted-light);
}

.filters-bar__search input {
    border: none;
    outline: none;
    font-size: 0.85rem;
    flex: 1;
    color: var(--ink);
    background: transparent;
}

.filters-bar__select {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    font-size: 0.7rem;
    color: var(--muted-light);
}

.filters-bar__select select {
    border: none;
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--ink);
    background: transparent;
    cursor: pointer;
    padding: 0.2rem 0;
}

.filters-bar__select select:focus {
    outline: none;
}

.filters-bar__toggle {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.8rem;
    color: var(--ink-soft);
    margin-left: auto;
}

.toggle-switch {
    position: relative;
    width: 38px;
    height: 20px;
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
    background: var(--line);
    border-radius: var(--radius-full);
    transition: 0.2s;
    cursor: pointer;
}

.toggle-switch input:checked + .toggle-slider {
    background: var(--brand);
}

.toggle-slider::before {
    content: '';
    position: absolute;
    width: 14px;
    height: 14px;
    left: 3px;
    top: 3px;
    background: var(--white);
    border-radius: 50%;
    transition: 0.2s;
}

.toggle-switch input:checked + .toggle-slider::before {
    transform: translateX(18px);
}

/* =========================================================================
   SECTIONS
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
    border: none;
    border-radius: var(--radius-md);
    overflow: hidden;
    aspect-ratio: 4/5;
    cursor: pointer;
    padding: 0;
    transition: all 0.3s ease;
}

.category-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.category-card img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.category-card:hover img {
    transform: scale(1.08);
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
    padding: 1rem;
    color: var(--white);
    text-align: center;
}

.category-card__content i {
    font-size: 1.2rem;
}

.category-card__content span {
    font-size: 0.8rem;
    font-weight: 700;
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
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.4rem 0.7rem;
    font-size: 0.8rem;
    color: var(--ink);
    background: var(--white);
    cursor: pointer;
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
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
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
}

.event-card__btn :deep(.p-button) {
    width: 100%;
    font-size: 0.78rem;
    font-weight: 700;
    border-radius: var(--radius-sm);
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

.favorite-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid var(--line);
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
}

.favorite-btn.active {
    color: var(--brand);
    border-color: var(--brand);
}

.ver-mas-wrap {
    display: flex;
    justify-content: center;
    margin-top: 1.75rem;
}

.ver-mas-wrap :deep(.p-button) {
    font-weight: 700;
    border-radius: var(--radius-sm);
    padding: 0.75rem 2rem;
    border-color: var(--line);
    color: var(--ink-soft);
    background: transparent;
}

.ver-mas-wrap :deep(.p-button:hover) {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
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
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
}

.sidebar-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.sidebar-card > h3,
.sidebar-card__header h3 {
    font-size: 0.95rem;
    font-weight: 600;
    margin: 0;
}

.sidebar-card__header h3 {
    margin: 0;
}

.mini-event-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.mini-event-item {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.4rem;
    border-radius: var(--radius-sm);
    transition: all 0.3s ease;
}

.mini-event-item:hover {
    background: var(--surface);
}

.mini-event-item img {
    width: 44px;
    height: 44px;
    border-radius: var(--radius-sm);
    object-fit: cover;
    flex-shrink: 0;
}

.mini-event-item__date {
    background: var(--brand-soft);
    color: var(--brand);
    border-radius: var(--radius-sm);
    padding: 0.3rem 0.5rem;
    text-align: center;
    line-height: 1.05;
    flex-shrink: 0;
}

.mini-event-item__date strong {
    display: block;
    font-size: 0.95rem;
}

.mini-event-item__date span {
    font-size: 0.58rem;
    letter-spacing: 0.05em;
}

.mini-event-item__info {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
}

.mini-event-item__info strong {
    font-size: 0.83rem;
    margin-bottom: 0.1rem;
}

.mini-event-item__info span {
    font-size: 0.7rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.mini-fav {
    width: 30px;
    height: 30px;
}

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
   CTA BANNER
   ========================================================================= */
.cta-banner {
    position: relative;
    max-width: 1400px;
    margin: 2.5rem auto 0;
    padding: 0 2rem;
    border-radius: var(--radius-lg);
    overflow: hidden;
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 2rem;
    align-items: center;
    min-height: 280px;
}

@media (max-width: 1024px) {
    .cta-banner {
        grid-template-columns: 1fr;
        padding: 0 1rem;
    }
}

.cta-banner__bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

.cta-banner__bg-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
    transition: transform 0.6s ease;
}

.cta-banner:hover .cta-banner__bg-image {
    transform: scale(1.05);
}

.cta-banner__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(12, 12, 14, 0.92) 0%, rgba(12, 12, 14, 0.5) 50%, rgba(12, 12, 14, 0.2) 100%);
    z-index: 1;
}

.cta-banner__content {
    position: relative;
    z-index: 2;
    padding: 2.5rem 0;
    color: var(--white);
}

.cta-banner__badge {
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

.cta-banner__content h2 {
    font-family: var(--font-serif);
    font-size: 1.5rem;
    font-weight: 400;
    margin: 0 0 0.5rem;
    line-height: 1.3;
}

.cta-banner__content p {
    font-size: 0.9rem;
    color: #d8d8dc;
    margin: 0 0 1.2rem;
}

.cta-banner__btn :deep(.p-button) {
    font-weight: 700;
    border-radius: var(--radius-sm);
    background: var(--brand);
    border-color: var(--brand);
    color: var(--white);
    padding: 0.6rem 2rem;
}

.cta-banner__btn :deep(.p-button:hover) {
    background: var(--brand-dark);
    border-color: var(--brand-dark);
}

.cta-banner__features {
    position: relative;
    z-index: 2;
    display: flex;
    gap: 1.75rem;
    flex-wrap: wrap;
}

@media (max-width: 1024px) {
    .cta-banner__features {
        padding-bottom: 2rem;
    }
}

.cta-feature {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    color: var(--white);
    max-width: 160px;
}

.cta-feature i {
    color: var(--brand);
    font-size: 1.1rem;
    margin-bottom: 0.2rem;
}

.cta-feature strong {
    font-size: 0.82rem;
}

.cta-feature span {
    font-size: 0.72rem;
    color: #b5b5ba;
}

/* =========================================================================
   SECURITY NOTE
   ========================================================================= */
.security-note {
    max-width: 1400px;
    margin: 1.5rem auto 0;
    padding: 0 2rem;
    text-align: center;
    font-size: 0.78rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
}

@media (max-width: 1024px) {
    .security-note {
        padding: 0 1rem;
    }
}

.security-note i {
    color: var(--brand);
}

/* =========================================================================
   RESPONSIVE AJUSTES
   ========================================================================= */
@media (max-width: 768px) {
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
    
    .filters-bar__wrap {
        flex-direction: column;
        align-items: stretch;
        padding: 1rem;
    }
    
    .filters-bar__search {
        flex: 1;
    }
    
    .filters-bar__toggle {
        margin-left: 0;
        justify-content: space-between;
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
    
    .cta-banner__content h2 {
        font-size: 1.2rem;
    }
    
    .cta-banner__features {
        gap: 1rem;
    }
    
    .cta-feature {
        max-width: 100%;
    }
}

@media (max-width: 480px) {
    .hero__content {
        padding: 1rem;
    }
    
    .hero h1 {
        font-size: 1.3rem;
    }
    
    .hero__trust {
        gap: 1rem;
    }
    
    .trust-item {
        max-width: 100%;
    }
    
    .hero__featured-content {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .hero__featured-cta {
        width: 100%;
    }
    
    .hero__featured-cta :deep(.p-button) {
        width: 100%;
        justify-content: center;
    }
    
    .category-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .cta-banner__content h2 {
        font-size: 1rem;
    }
    
    .cta-banner__btn :deep(.p-button) {
        width: 100%;
        justify-content: center;
    }
}
</style>