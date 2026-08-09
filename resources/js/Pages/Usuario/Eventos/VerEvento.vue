<script setup>
import { ref, computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import AppLayout from '@/Layouts/AppLayout.vue';

/* ---------------------------------------------------------------
 * Props y datos del evento
 * --------------------------------------------------------------- */
const props = defineProps({
    evento: {
        type: Object,
        required: true
    },
    asistentes: {
        type: Array,
        default: () => []
    },
    eventosRelacionados: {
        type: Array,
        default: () => []
    }
});

/* ---------------------------------------------------------------
 * Toast y notificaciones
 * --------------------------------------------------------------- */
const toast = useToast();
const page = usePage();

/* ---------------------------------------------------------------
 * Usuario autenticado
 * --------------------------------------------------------------- */
const usuario = computed(() => page.props.auth?.user || {
    nombre: 'Invitado',
    avatar: null,
    verificado: false
});

/* ---------------------------------------------------------------
 * Estado local
 * --------------------------------------------------------------- */
const isReservando = ref(false);
const isGuardando = ref(false);
const activeTab = ref('descripcion');

/* ---------------------------------------------------------------
 * Cálculos
 * --------------------------------------------------------------- */
const porcentajeDisponible = computed(() => {
    const disponibles = props.evento.cupos_disponibles || 0;
    const totales = props.evento.cupos_totales || 1;
    return (disponibles / totales) * 100;
});

const esEventoPasado = computed(() => {
    if (!props.evento.fecha) return false;
    return new Date(props.evento.fecha) < new Date();
});

const estaCompleto = computed(() => 
    props.evento.cupos_disponibles === 0
);

/* ---------------------------------------------------------------
 * Datos estáticos
 * --------------------------------------------------------------- */
const confianza = [
    { icon: 'pi-users', texto: 'Solo miembros' },
    { icon: 'pi-check-circle', texto: 'Verificación requerida' },
    { icon: 'pi-lock', texto: 'Privacidad garantizada' },
    { icon: 'pi-star-fill', texto: 'Dress code elegante' },
    { icon: 'pi-ticket', texto: 'Cupo limitado' },
];

const incluye = [
    { icon: 'pi-gift', titulo: 'Cóctel de bienvenida', desc: 'Brindis de cortesía al llegar' },
    { icon: 'pi-palette', titulo: 'Ambientación premium', desc: 'Iluminación y sonido envolvente' },
    { icon: 'pi-home', titulo: 'Áreas privadas', desc: 'Espacios exclusivos para socializar' },
    { icon: 'pi-volume-up', titulo: 'Música en vivo & DJ', desc: 'Experiencia musical única' },
    { icon: 'pi-shield', titulo: 'Seguridad 24/7', desc: 'Validación y protección constante' },
    { icon: 'pi-users', titulo: 'Networking selecto', desc: 'Conexiones con perfiles afines' },
];

const programa = [
    { hora: '21:30', titulo: 'Registro y validación', desc: 'Recepción privada y validación de perfiles.' },
    { hora: '22:00', titulo: 'Cóctel de bienvenida', desc: 'Disfruta de un cóctel de autor y conoce a otros miembros.' },
    { hora: '23:00', titulo: 'Experiencia principal', desc: 'Actividades, música en vivo y sorpresas especiales.' },
    { hora: '01:00', titulo: 'After social', desc: 'Continúa la conexión en nuestras áreas privadas.' },
];

/* ---------------------------------------------------------------
 * Métodos
 * --------------------------------------------------------------- */
function reservarLugar() {
    if (estaCompleto.value) {
        toast.add({
            severity: 'warn',
            summary: 'Cupo agotado',
            detail: 'Lo sentimos, este evento ya no tiene lugares disponibles.',
            life: 4000
        });
        return;
    }

    if (esEventoPasado.value) {
        toast.add({
            severity: 'info',
            summary: 'Evento finalizado',
            detail: 'Este evento ya ha pasado.',
            life: 4000
        });
        return;
    }

    window.location.href = `/eventos/${props.evento.id}/reservar`;
}

function compartirEvento() {
    const url = window.location.href;
    const titulo = props.evento.titulo;
    
    if (navigator.share) {
        navigator.share({
            title: titulo,
            text: `¡No te pierdas "${titulo}"! ${props.evento.descripcion_corta || ''}`,
            url: url
        }).catch(() => {});
    } else {
        navigator.clipboard.writeText(url).then(() => {
            toast.add({
                severity: 'success',
                summary: 'Enlace copiado',
                detail: 'Comparte el enlace con tus amigos',
                life: 3000
            });
        }).catch(() => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo copiar el enlace',
                life: 3000
            });
        });
    }
}

function guardarEvento() {
    isGuardando.value = true;
    setTimeout(() => {
        isGuardando.value = false;
        toast.add({
            severity: 'success',
            summary: 'Evento guardado',
            detail: 'El evento ha sido guardado en tus favoritos',
            life: 3000
        });
    }, 500);
}

function obtenerIniciales(nombre) {
    if (!nombre) return '?';
    return nombre.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
}

function obtenerColorAvatar(nombre) {
    const colores = ['#c81e3a', '#2d3748', '#4a5568', '#e53e3e', '#d69e2e', '#3182ce', '#38a169'];
    if (!nombre) return colores[0];
    let hash = 0;
    for (let i = 0; i < nombre.length; i++) {
        hash = nombre.charCodeAt(i) + ((hash << 5) - hash);
    }
    return colores[Math.abs(hash) % colores.length];
}

function getImageUrl(path) {
    if (!path) return '/images/eventos/default-hero.jpg';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/') || path.startsWith('/images/')) return path;
    if (path.startsWith('storage/')) return '/' + path;
    return '/storage/' + path.replace(/^\/+/, '');
}

function formatearFecha(fecha) {
    if (!fecha) return 'Por confirmar';
    try {
        const date = new Date(fecha);
        return date.toLocaleDateString('es-MX', { 
            weekday: 'long', 
            day: 'numeric', 
            month: 'long', 
            year: 'numeric' 
        });
    } catch {
        return 'Por confirmar';
    }
}

function obtenerDiaMes(fecha) {
    if (!fecha) return { dia: '--', mes: '---' };
    try {
        const date = new Date(fecha);
        return {
            dia: date.getDate().toString().padStart(2, '0'),
            mes: date.toLocaleString('es', { month: 'short' }).toUpperCase()
        };
    } catch {
        return { dia: '--', mes: '---' };
    }
}
</script>

<template>
    <Head :title="evento.titulo" />

    <AppLayout
        active-nav="eventos"
        :usuario="usuario"
        :notificaciones="6"
        :favoritos="12"
        :mensajes="3"
    >
        <div class="ver-evento-page">
            <!-- ============================================================ -->
            <!-- HERO CON BOOKING CARD -->
            <!-- ============================================================ -->
            <section class="hero-section">
                <div class="hero-container">
                    <div class="hero-background">
                        <img 
                            :src="getImageUrl(evento.imagen_url || evento.imagen)" 
                            :alt="evento.titulo" 
                            class="hero-background__image" 
                        />
                        <div class="hero-background__overlay"></div>
                    </div>

                    <div class="hero-badges">
                        <span v-if="evento.destacado" class="hero-badge hero-badge--destacado">
                            <i class="pi pi-star-fill"></i> DESTACADO
                        </span>
                        <span v-if="estaCompleto" class="hero-badge hero-badge--agotado">
                            <i class="pi pi-times-circle"></i> AGOTADO
                        </span>
                        <span v-if="!estaCompleto && !esEventoPasado" class="hero-badge hero-badge--disponible">
                            <i class="pi pi-check-circle"></i> DISPONIBLE
                        </span>
                    </div>

                    <div class="hero-content">
                        <div class="hero-content__left">
                            <div class="hero-date">
                                <strong>{{ evento.dia || obtenerDiaMes(evento.fecha).dia }}</strong>
                                <span>{{ evento.mes || obtenerDiaMes(evento.fecha).mes }}</span>
                                <span class="hero-date__year">{{ evento.fecha ? new Date(evento.fecha).getFullYear() : '' }}</span>
                            </div>
                            <div class="hero-info">
                                <h1>{{ evento.titulo }}</h1>
                                <div class="hero-meta">
                                    <span class="hero-meta__item">
                                        <i class="pi pi-map-marker"></i> {{ evento.ciudad || 'Ciudad de México' }}
                                    </span>
                                    <span class="hero-meta__divider">•</span>
                                    <span class="hero-meta__item">
                                        <i class="pi pi-clock"></i> {{ evento.hora || '23:00 hrs' }}
                                    </span>
                                    <span class="hero-meta__divider">•</span>
                                    <span class="hero-meta__item">
                                        <i class="pi pi-tag"></i> {{ evento.tipo_evento || evento.tipo || 'Fiesta privada' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="hero-actions">
                            <button class="hero-actions__btn-primary" @click="reservarLugar" :disabled="estaCompleto || esEventoPasado">
                                {{ estaCompleto ? 'Cupo agotado' : 'Reservar' }}
                            </button>
                            <button class="hero-actions__btn-secondary" @click="compartirEvento" title="Compartir evento">
                                <i class="pi pi-share-alt"></i>
                            </button>
                            <button class="hero-actions__btn-secondary" @click="guardarEvento" :disabled="isGuardando" title="Guardar en favoritos">
                                <i v-if="isGuardando" class="pi pi-spin pi-spinner"></i>
                                <i v-else class="pi pi-heart"></i>
                            </button>
                        </div>
                    </div>

                    <div class="booking-card-wrapper">
                        <div class="booking-card">
                            <div class="booking-card__header">
                                <span class="booking-card__vip"><i class="pi pi-crown"></i> VIP</span>
                                <span class="booking-card__price">${{ evento.precio || 25 }}</span>
                            </div>
                            <span class="booking-card__per-person">por persona</span>

                            <div class="booking-card__availability">
                                <div class="booking-card__availability-info">
                                    <span><i class="pi pi-users"></i> {{ evento.cupos_disponibles || 150 }} disponibles</span>
                                    <span>{{ Math.round(porcentajeDisponible) }}%</span>
                                </div>
                                <div class="booking-card__availability-bar">
                                    <div class="booking-card__availability-fill" :style="{ width: porcentajeDisponible + '%' }"></div>
                                </div>
                            </div>

                            <button 
                                class="booking-card__btn"
                                :disabled="estaCompleto || esEventoPasado"
                                @click="reservarLugar"
                            >
                                {{ estaCompleto ? 'Cupo agotado' : 'Reservar lugar' }}
                            </button>

                            <div class="booking-card__divider"></div>

                            <div class="booking-card__details">
                                <div class="detail-row">
                                    <i class="pi pi-calendar"></i>
                                    <div>
                                        <span>Fecha</span>
                                        <strong>{{ formatearFecha(evento.fecha) }}</strong>
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <i class="pi pi-clock"></i>
                                    <div>
                                        <span>Hora</span>
                                        <strong>{{ evento.hora || '21:00 hrs' }}</strong>
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <i class="pi pi-map-marker"></i>
                                    <div>
                                        <span>Ubicación</span>
                                        <strong>{{ evento.ubicacion_detalle || evento.ubicacion || 'Locación privada' }}</strong>
                                        <small v-if="evento.ubicacion_nota">{{ evento.ubicacion_nota }}</small>
                                    </div>
                                </div>
                                <div class="detail-row">
                                    <i class="pi pi-tag"></i>
                                    <div>
                                        <span>Vestimenta</span>
                                        <strong>{{ evento.codigo_vestimenta || 'Elegante' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- CONTENIDO PRINCIPAL -->
            <!-- ============================================================ -->
            <div class="content-grid">
                <div class="main-column">
                    <div class="tabs-container">
                        <div class="tabs-header">
                            <button 
                                v-for="tab in ['descripcion', 'incluye', 'programa']" 
                                :key="tab"
                                class="tab-btn"
                                :class="{ active: activeTab === tab }"
                                @click="activeTab = tab"
                            >
                                <i :class="tab === 'descripcion' ? 'pi pi-info-circle' : tab === 'incluye' ? 'pi pi-list' : 'pi pi-clock'"></i>
                                {{ tab === 'descripcion' ? 'Descripción' : tab === 'incluye' ? 'Incluye' : 'Programa' }}
                            </button>
                        </div>

                        <div class="tab-content">
                            <div v-show="activeTab === 'descripcion'" class="tab-panel">
                                <div class="section-block">
                                    <p class="section-block__desc">{{ evento.descripcion || evento.descripcion_larga || 'Descripción no disponible.' }}</p>
                                    <div class="feature-tags">
                                        <span v-for="tag in confianza" :key="tag.texto" class="feature-tag">
                                            <i class="pi" :class="tag.icon"></i> {{ tag.texto }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div v-show="activeTab === 'incluye'" class="tab-panel">
                                <div class="includes-grid">
                                    <div v-for="i in incluye" :key="i.titulo" class="include-item">
                                        <div class="include-item__icon"><i class="pi" :class="i.icon"></i></div>
                                        <strong>{{ i.titulo }}</strong>
                                        <span class="include-item__desc">{{ i.desc }}</span>
                                    </div>
                                </div>
                            </div>

                            <div v-show="activeTab === 'programa'" class="tab-panel">
                                <div class="programa-grid-dos">
                                    <div v-for="(item, index) in programa" :key="item.hora" class="programa-card-dos">
                                        <div class="programa-card-dos__number-circle">
                                            <span>{{ String(index + 1).padStart(2, '0') }}</span>
                                        </div>
                                        <div class="programa-card-dos__time">{{ item.hora }}</div>
                                        <h4>{{ item.titulo }}</h4>
                                        <p>{{ item.desc }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- SUSCRIPCIÓN ELIMINADA - ESTA SECCIÓN FUE REMOVIDA -->
                    <!-- ============================================================ -->

                    <section v-if="eventosRelacionados && eventosRelacionados.length" class="related-section">
                        <div class="related-section__header">
                            <h2>También te puede interesar</h2>
                            <a href="/eventos" class="see-all">
                                Ver todos <i class="pi pi-arrow-right"></i>
                            </a>
                        </div>
                        <div class="related-grid">
                            <div v-for="e in eventosRelacionados.slice(0, 3)" :key="e.id" class="related-card">
                                <div class="related-card__image">
                                    <img :src="getImageUrl(e.imagen_url || e.imagen)" :alt="e.titulo" />
                                    <div class="related-card__date">
                                        <strong>{{ e.dia || obtenerDiaMes(e.fecha).dia }}</strong>
                                        <span>{{ e.mes || obtenerDiaMes(e.fecha).mes }}</span>
                                    </div>
                                    <span v-if="e.vip" class="related-card__vip-badge">VIP</span>
                                </div>
                                <div class="related-card__body">
                                    <h3>{{ e.titulo }}</h3>
                                    <div class="related-card__meta">
                                        <span><i class="pi pi-map-marker"></i> {{ e.ciudad || 'CDMX' }}</span>
                                        <span><i class="pi pi-clock"></i> {{ e.hora || '21:00 hrs' }}</span>
                                    </div>
                                    <div class="related-card__footer">
                                        <span class="related-card__price">${{ e.precio || 0 }}</span>
                                        <a :href="`/eventos/${e.id}`" class="related-card__link">
                                            Ver detalles <i class="pi pi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="sidebar-column">
                    <div v-if="asistentes && asistentes.length" class="sidebar-card sidebar-card--attendees">
                        <div class="sidebar-card__header">
                            <h3><i class="pi pi-users"></i> Asistentes</h3>
                            <span class="sidebar-card__count">{{ asistentes.length }}</span>
                        </div>
                        <div class="attendees">
                            <div class="attendees__avatars">
                                <div 
                                    v-for="(a, i) in asistentes.slice(0, 6)" 
                                    :key="i" 
                                    class="attendees__avatar"
                                    :style="{ backgroundColor: obtenerColorAvatar(a.nombre) }"
                                    :title="a.nombre"
                                >
                                    <span v-if="a.avatar_url" class="attendees__avatar-img" :style="{ backgroundImage: `url(${getImageUrl(a.avatar_url)})` }"></span>
                                    <span v-else class="attendees__avatar-initials">{{ obtenerIniciales(a.nombre) }}</span>
                                </div>
                                <div v-if="asistentes.length > 6" class="attendees__more">
                                    +{{ asistentes.length - 6 }}
                                </div>
                            </div>
                            <div class="attendees__info">
                                <span class="attendees__count">{{ asistentes.length }} personas asistirán</span>
                                <span class="attendees__status">
                                    <span class="status-dot"></span> En vivo
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="sidebar-card sidebar-card--map">
                        <h3><i class="pi pi-map"></i> Ubicación</h3>
                        <div class="map-placeholder">
                            <div class="map-working">
                                <i class="pi pi-map-marker map-working-icon"></i>
                                <span class="map-working-text">Estamos trabajando en el mapa</span>
                                <span class="map-working-sub">Pronto podrás ver la ubicación exacta</span>
                            </div>
                        </div>
                        <p class="map-note">
                            <i class="pi pi-info-circle"></i>
                            La ubicación exacta se comparte al confirmar tu asistencia
                        </p>
                    </div>

                    <!-- ============================================================ -->
                    <!-- SIDEBAR SUSCRIPCIÓN ELIMINADA -->
                    <!-- ============================================================ -->
                </aside>
            </div>

            <section class="cta-banner" v-if="!estaCompleto && !esEventoPasado">
                <div class="cta-banner__background"></div>
                <div class="cta-banner__content">
                    <div class="cta-banner__text">
                        <span class="cta-banner__badge"><i class="pi pi-crown"></i> Experiencia VIP</span>
                        <h2>Reserva tu lugar ahora</h2>
                        <p>No dejes pasar esta oportunidad única</p>
                    </div>
                    <div class="cta-banner__actions">
                        <PvButton label="Reservar mi lugar" @click="reservarLugar" class="cta-banner__btn-primary" />
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700;800&display=swap');

:root {
    --brand-red: #c81e3a;
    --brand-red-dark: #a6152d;
    --brand-red-light: #fdf1f2;
    --brand-gold: #d4a53a;
    --brand-gold-light: #fef7e8;
    --brand-dark: #1a1817;
    --brand-gray: #6b6764;
    --brand-gray-light: #e8e5e3;
    --brand-white: #faf8f7;

    --text-primary: var(--brand-dark);
    --text-secondary: var(--brand-gray);

    --font-display: 'Fraunces', Georgia, serif;
    --font-body: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;

    --shadow-sm: 0 2px 10px rgba(26, 24, 23, 0.05);
    --shadow-md: 0 12px 32px rgba(26, 24, 23, 0.08);
    --shadow-lg: 0 20px 60px rgba(26, 24, 23, 0.18);
}

.ver-evento-page {
    font-family: var(--font-body);
    color: var(--text-primary);
    background: var(--brand-white);
    min-height: 100vh;
}

/* ============================================================
   HERO
   ============================================================ */
.hero-section {
    max-width: 1440px;
    margin: 1.5rem auto 0;
    padding: 0 2rem;
}

@media (max-width: 1100px) {
    .hero-section {
        padding: 0 1rem;
    }
}

.hero-container {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    min-height: 560px;
    background: var(--brand-dark);
}

@media (max-width: 768px) {
    .hero-container {
        min-height: 480px;
        border-radius: 18px;
    }
}

.hero-background {
    position: absolute;
    inset: 0;
}

.hero-background__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: scale(1.02);
}

.hero-background__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(0deg, rgba(0,0,0,0.85) 20%, rgba(0,0,0,0.3) 50%, rgba(0,0,0,0.1) 75%);
}

.hero-badges {
    position: absolute;
    top: 1.5rem;
    left: 1.5rem;
    z-index: 2;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.hero-badge {
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.35rem 0.9rem;
    border-radius: 50px;
    letter-spacing: 0.06em;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    backdrop-filter: blur(10px);
}

.hero-badge i {
    font-size: 0.6rem;
}

.hero-badge--destacado {
    background: linear-gradient(135deg, var(--brand-gold), #b8942a);
    color: #fff;
}

.hero-badge--agotado {
    background: rgba(107, 114, 128, 0.9);
    color: #fff;
}

.hero-badge--disponible {
    background: rgba(34, 197, 94, 0.9);
    color: #fff;
}

.hero-content {
    position: relative;
    z-index: 2;
    padding: 2.5rem;
    color: #fff;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    min-height: 560px;
    justify-content: flex-end;
}

@media (max-width: 768px) {
    .hero-content {
        min-height: 480px;
        padding: 1.5rem;
    }
}

.hero-content__left {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
}

@media (max-width: 1100px) {
    .hero-content__left {
        flex-direction: column;
        gap: 1rem;
    }
}

.hero-date {
    background: rgba(200, 30, 58, 0.9);
    border-radius: 12px;
    padding: 0.6rem 1rem;
    text-align: center;
    line-height: 1.1;
    flex-shrink: 0;
    min-width: 70px;
    backdrop-filter: blur(10px);
}

.hero-date strong {
    display: block;
    font-family: var(--font-display);
    font-size: 1.5rem;
    font-weight: 700;
    color: #fff;
}

.hero-date span {
    font-size: 0.68rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #fff;
}

.hero-date__year {
    display: block;
    font-size: 0.55rem;
    opacity: 0.7;
    margin-top: 0.1rem;
    color: #fff;
}

.hero-info {
    flex: 1;
}

.hero-info h1 {
    font-family: var(--font-display);
    font-size: 2.8rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
    line-height: 1.1;
    letter-spacing: -0.01em;
    color: #fff;
}

@media (max-width: 768px) {
    .hero-info h1 {
        font-size: 1.8rem;
    }
}

.hero-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.hero-meta__item {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.85);
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.hero-meta__item i {
    color: var(--brand-gold);
    font-size: 0.7rem;
}

.hero-meta__divider {
    color: rgba(255,255,255,0.3);
}

.hero-actions {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

@media (max-width: 768px) {
    .hero-actions {
        flex-wrap: wrap;
        justify-content: center;
    }
}

.hero-actions__btn-primary {
    background: #ffffff !important;
    color: #c81e3a !important;
    border: 2px solid #c81e3a !important;
    padding: 0.8rem 2.5rem !important;
    border-radius: 50px !important;
    font-size: 1rem !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    font-family: var(--font-body) !important;
    box-shadow: 0 4px 15px rgba(200, 30, 58, 0.15) !important;
    min-width: 140px;
}

.hero-actions__btn-primary:hover:not(:disabled) {
    background: #c81e3a !important;
    color: #ffffff !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(200, 30, 58, 0.3) !important;
}

.hero-actions__btn-primary:disabled {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
    box-shadow: none !important;
}

.hero-actions__btn-secondary {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.3);
    background: rgba(255,255,255,0.1);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    font-size: 1rem;
}

.hero-actions__btn-secondary:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-2px);
}

/* ============================================================
   BOOKING CARD
   ============================================================ */
.booking-card-wrapper {
    position: absolute;
    right: 2rem;
    bottom: 2rem;
    z-index: 3;
    width: 340px;
}

@media (max-width: 1100px) {
    .booking-card-wrapper {
        position: relative;
        right: auto;
        bottom: auto;
        width: 100%;
        padding: 1rem 1.5rem 1.5rem;
        margin-top: -2rem;
    }
}

@media (max-width: 768px) {
    .booking-card-wrapper {
        padding: 0.75rem 1rem 1rem;
        margin-top: -1rem;
    }
}

.booking-card {
    background: #fff;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.35);
    color: var(--text-primary);
}

.booking-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.3rem;
}

.booking-card__vip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: linear-gradient(135deg, var(--brand-gold), #b8942a);
    color: #fff;
    font-size: 0.6rem;
    font-weight: 800;
    padding: 0.2rem 0.7rem;
    border-radius: 50px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.booking-card__price {
    font-family: var(--font-display);
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
}

.booking-card__per-person {
    display: block;
    font-size: 0.7rem;
    color: var(--text-secondary);
    margin-bottom: 1rem;
}

.booking-card__availability {
    margin-bottom: 1rem;
}

.booking-card__availability-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.7rem;
    color: var(--text-secondary);
    margin-bottom: 0.4rem;
    font-weight: 500;
}

.booking-card__availability-info i {
    color: var(--brand-red);
    margin-right: 0.3rem;
}

.booking-card__availability-bar {
    height: 4px;
    background: var(--brand-gray-light);
    border-radius: 50px;
    overflow: hidden;
}

.booking-card__availability-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--brand-red), var(--brand-red-dark));
    border-radius: 50px;
    transition: width 0.6s ease;
}

.booking-card__btn {
    width: 100%;
    background: #ffffff !important;
    color: #c81e3a !important;
    border: 2px solid #c81e3a !important;
    padding: 0.9rem 1.5rem !important;
    border-radius: 50px !important;
    font-size: 0.95rem !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    font-family: var(--font-body) !important;
    box-shadow: 0 4px 15px rgba(200, 30, 58, 0.15) !important;
    margin-bottom: 1rem !important;
    display: block !important;
    width: 100% !important;
    text-align: center !important;
}

.booking-card__btn:hover:not(:disabled) {
    background: #c81e3a !important;
    color: #ffffff !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 25px rgba(200, 30, 58, 0.3) !important;
}

.booking-card__btn:disabled {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
    box-shadow: none !important;
}

.booking-card__divider {
    height: 1px;
    background: linear-gradient(to right, transparent, var(--brand-gray-light), transparent);
    margin: 0.5rem 0 1rem 0;
}

.booking-card__details {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.detail-row {
    display: flex;
    gap: 0.65rem;
    align-items: flex-start;
}

.detail-row i {
    color: var(--brand-red);
    font-size: 0.85rem;
    margin-top: 0.1rem;
    width: 18px;
}

.detail-row span {
    display: block;
    font-size: 0.6rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-weight: 600;
}

.detail-row strong {
    display: block;
    font-size: 0.8rem;
    color: var(--text-primary);
    font-weight: 600;
}

.detail-row small {
    display: block;
    font-size: 0.65rem;
    color: var(--text-secondary);
    margin-top: 0.05rem;
}

/* ============================================================
   CONTENIDO PRINCIPAL
   ============================================================ */
.content-grid {
    max-width: 1440px;
    margin: 2.5rem auto 0;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 2rem;
    align-items: start;
}

@media (max-width: 1100px) {
    .content-grid {
        grid-template-columns: 1fr;
        padding: 0 1rem;
        margin-top: 1.5rem;
    }
}

.main-column {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* ============================================================
   TABS
   ============================================================ */
.tabs-container {
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--brand-gray-light);
    overflow: hidden;
}

.tabs-header {
    display: flex;
    border-bottom: 1px solid var(--brand-gray-light);
    background: var(--brand-white);
    overflow-x: auto;
}

.tab-btn {
    flex: 1;
    padding: 0.9rem 1.25rem;
    border: none;
    background: transparent;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.25s ease;
    font-family: inherit;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    white-space: nowrap;
}

.tab-btn i {
    font-size: 0.85rem;
    color: var(--text-secondary);
}

.tab-btn:hover {
    color: var(--text-primary);
}

.tab-btn:hover i {
    color: var(--text-primary);
}

.tab-btn.active {
    color: var(--brand-red);
    border-bottom: 2px solid var(--brand-red);
}

.tab-btn.active i {
    color: var(--brand-red);
}

.tab-content {
    padding: 1.5rem;
}

@media (max-width: 768px) {
    .tab-content {
        padding: 1rem;
    }
}

.tab-panel {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.section-block__desc {
    font-size: 0.95rem;
    color: var(--text-primary);
    line-height: 1.8;
    margin: 0 0 1.5rem;
}

.feature-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.6rem;
}

.feature-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.9rem;
    background: var(--brand-white);
    border: 1px solid var(--brand-gray-light);
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--text-primary);
}

.feature-tag i {
    color: var(--brand-red);
}

.includes-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

@media (max-width: 768px) {
    .includes-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .includes-grid {
        grid-template-columns: 1fr;
    }
}

.include-item {
    background: var(--brand-white);
    border: 1px solid var(--brand-gray-light);
    border-radius: 12px;
    padding: 1.25rem;
    text-align: center;
    transition: all 0.25s ease;
}

.include-item:hover {
    border-color: var(--brand-red);
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.include-item__icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--brand-red-light);
    color: var(--brand-red);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    font-size: 1.2rem;
}

.include-item strong {
    display: block;
    font-size: 0.85rem;
    margin-bottom: 0.3rem;
    color: var(--text-primary);
}

.include-item__desc {
    font-size: 0.7rem;
    color: var(--text-secondary);
}

/* ============================================================
   PROGRAMA
   ============================================================ */
.programa-grid-dos {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 768px) {
    .programa-grid-dos {
        grid-template-columns: 1fr;
    }
}

.programa-card-dos {
    background: var(--brand-white);
    border: 1px solid var(--brand-gray-light);
    border-radius: 14px;
    padding: 1.25rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.programa-card-dos::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--brand-red), var(--brand-gold));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.programa-card-dos:hover {
    border-color: var(--brand-red);
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}

.programa-card-dos:hover::before {
    opacity: 1;
}

.programa-card-dos__number-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #c81e3a !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    margin-bottom: 0.4rem;
    box-shadow: 0 2px 12px rgba(200, 30, 58, 0.25);
    flex-shrink: 0;
}

.programa-card-dos__number-circle span {
    font-family: var(--font-display);
    font-size: 1.1rem;
    font-weight: 700;
    color: #ffffff !important;
    line-height: 1;
}

.programa-card-dos__time {
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--brand-red);
    background: var(--brand-red-light);
    padding: 0.15rem 0.7rem;
    border-radius: 50px;
    margin-bottom: 0.5rem;
}

.programa-card-dos h4 {
    font-size: 0.95rem;
    font-weight: 700;
    margin: 0 0 0.3rem;
    color: var(--text-primary);
}

.programa-card-dos p {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin: 0;
    line-height: 1.5;
}

/* ============================================================
   NORMAS Y PRIVACIDAD - ELIMINADA COMPLETAMENTE
   ============================================================ */

/* ============================================================
   SUSCRIPCIÓN - ELIMINADA COMPLETAMENTE
   ============================================================ */

/* ============================================================
   EVENTOS RELACIONADOS
   ============================================================ */
.related-section__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
}

.related-section__header h2 {
    font-family: var(--font-display);
    font-size: 1.3rem;
    font-weight: 600;
    margin: 0;
    color: var(--text-primary);
}

.see-all {
    color: var(--brand-red);
    font-size: 0.75rem;
    font-weight: 700;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    transition: gap 0.25s ease;
}

.see-all:hover {
    gap: 0.6rem;
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}

@media (max-width: 900px) {
    .related-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 600px) {
    .related-grid {
        grid-template-columns: 1fr;
    }
}

.related-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid var(--brand-gray-light);
    overflow: hidden;
    transition: all 0.25s ease;
}

.related-card:hover {
    border-color: var(--brand-red);
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}

.related-card__image {
    position: relative;
    aspect-ratio: 16/10;
}

.related-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.related-card__date {
    position: absolute;
    top: 10px;
    left: 10px;
    background: var(--brand-red);
    color: #fff;
    border-radius: 10px;
    padding: 0.25rem 0.5rem;
    text-align: center;
    line-height: 1.05;
}

.related-card__date strong {
    display: block;
    font-size: 0.95rem;
    color: #fff;
}

.related-card__date span {
    font-size: 0.5rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #fff;
}

.related-card__vip-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: linear-gradient(135deg, var(--brand-gold), #b8942a);
    color: #fff;
    font-size: 0.5rem;
    font-weight: 800;
    padding: 0.15rem 0.6rem;
    border-radius: 50px;
}

.related-card__body {
    padding: 1rem;
}

.related-card__body h3 {
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0 0 0.4rem;
    color: var(--text-primary);
}

.related-card__meta {
    display: flex;
    gap: 0.75rem;
    font-size: 0.65rem;
    color: var(--text-secondary);
    margin-bottom: 0.75rem;
}

.related-card__meta i {
    font-size: 0.55rem;
    color: var(--brand-red);
}

.related-card__footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.related-card__price {
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--text-primary);
}

.related-card__link {
    font-size: 0.65rem;
    font-weight: 700;
    color: var(--brand-red);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.2rem;
    transition: gap 0.25s ease;
}

.related-card__link:hover {
    gap: 0.5rem;
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
    background: #fff;
    border-radius: 16px;
    padding: 1.5rem;
    border: 1px solid var(--brand-gray-light);
    transition: border-color 0.25s ease;
}

.sidebar-card:hover {
    border-color: var(--brand-red-light);
}

.sidebar-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.sidebar-card__header h3 {
    font-size: 0.9rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-primary);
}

.sidebar-card__header h3 i {
    color: var(--brand-red);
}

.sidebar-card__count {
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--brand-red);
    background: var(--brand-red-light);
    padding: 0.15rem 0.7rem;
    border-radius: 50px;
}

.attendees__avatars {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.1rem;
    margin-bottom: 0.75rem;
}

.attendees__avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 2px solid #fff;
    overflow: hidden;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.25s ease;
    box-shadow: 0 0 0 1px var(--brand-gray-light);
}

.attendees__avatar:hover {
    transform: translateY(-3px);
    z-index: 2;
}

.attendees__avatar-img {
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
}

.attendees__avatar-initials {
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
}

.attendees__more {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 2px solid #fff;
    background: var(--brand-gray-light);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    font-weight: 700;
    color: var(--text-primary);
}

.attendees__info {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.attendees__count {
    font-size: 0.75rem;
    color: var(--text-secondary);
}

.attendees__status {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.65rem;
    font-weight: 600;
    color: #15803d;
}

.status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #22c55e;
    animation: pulseDot 1.5s infinite;
}

.sidebar-card--map h3 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    font-weight: 700;
    margin: 0 0 1rem;
    color: var(--text-primary);
}

.sidebar-card--map h3 i {
    color: var(--brand-red);
}

.map-placeholder {
    border-radius: 12px;
    overflow: hidden;
    aspect-ratio: 16/10;
    margin-bottom: 0.75rem;
    background: #f5f5f5;
}

.map-working {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f5f5f5, #e8e5e3);
    padding: 1rem;
}

.map-working-icon {
    font-size: 2.5rem;
    color: var(--brand-red);
    margin-bottom: 0.75rem;
    opacity: 0.5;
}

.map-working-text {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-primary);
}

.map-working-sub {
    font-size: 0.7rem;
    color: var(--text-secondary);
    margin-top: 0.25rem;
}

.map-note {
    font-size: 0.65rem;
    color: var(--text-secondary);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.map-note i {
    color: var(--brand-red);
}

/* ============================================================
   SIDEBAR SUSCRIPCIÓN - ELIMINADA COMPLETAMENTE
   ============================================================ */

/* ============================================================
   CTA BANNER
   ============================================================ */
.cta-banner {
    max-width: 1440px;
    margin: 2.5rem auto 0;
    padding: 0 2rem;
    position: relative;
}

.cta-banner__background {
    position: absolute;
    inset: 0 2rem 0 2rem;
    border-radius: 20px;
    background: linear-gradient(135deg, var(--brand-dark), #2d2522);
}

.cta-banner__content {
    position: relative;
    z-index: 2;
    padding: 2.5rem 3rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.5rem;
    color: #fff;
}

.cta-banner__text {
    flex: 1;
}

.cta-banner__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(255,255,255,0.1);
    padding: 0.2rem 0.8rem;
    border-radius: 50px;
    font-size: 0.65rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    backdrop-filter: blur(10px);
}

.cta-banner__badge i {
    color: var(--brand-gold);
}

.cta-banner__text h2 {
    font-family: var(--font-display);
    font-size: 1.6rem;
    font-weight: 600;
    margin: 0 0 0.3rem;
    color: #fff;
}

.cta-banner__text p {
    font-size: 0.85rem;
    opacity: 0.8;
    margin: 0;
    color: #fff;
}

.cta-banner__actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.cta-banner__btn-primary {
    background: var(--brand-red) !important;
    border: none !important;
    font-weight: 700 !important;
    padding: 0.7rem 2rem !important;
    border-radius: 50px !important;
}

.cta-banner__btn-primary:hover {
    background: var(--brand-red-dark) !important;
}

@media (max-width: 1100px) {
    .cta-banner {
        padding: 0 1rem;
    }

    .cta-banner__background {
        inset: 0 1rem 0 1rem;
    }
}

@media (max-width: 768px) {
    .cta-banner__content {
        flex-direction: column;
        text-align: center;
        padding: 2rem 1.5rem;
    }

    .cta-banner__actions {
        width: 100%;
        justify-content: center;
    }

    .cta-banner__text h2 {
        font-size: 1.3rem;
    }
}

@keyframes pulseDot {
    0%, 100% { box-shadow: 0 0 0 2px var(--brand-red); }
    50% { box-shadow: 0 0 0 5px rgba(200, 30, 58, 0.2); }
}
</style>