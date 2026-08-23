<template>

    <Head :title="`${creador.usuario.nombre} | Club de Fantasías`" />

    <ToastNotification ref="toastRef" :duration="5000" />

    <AppLayout active-nav="comunidad">
        <div class="perfil-creador-page">
            <!-- ============================================================ -->
            <!-- BOTÓN VOLVER -->
            <!-- ============================================================ -->
            <div class="volver-container">
                <button class="btn-volver" @click="volverAtras">
                    <i class="pi pi-arrow-left"></i>
                    <span>Volver</span>
                </button>
            </div>

            <!-- ============================================================ -->
            <!-- PERFIL DEL CREADOR -->
            <!-- ============================================================ -->
            <section class="perfil-section">
                <div class="perfil-container">
                    <!-- Avatar y nombre -->
                    <div class="perfil-header">
                        <div class="perfil-avatar-wrapper">
                            <img :src="getAvatarUrl(creador.usuario)" :alt="creador.usuario.nombre"
                                class="perfil-avatar" />
                            <div v-if="creador.esta_verificado" class="perfil-verified">
                                <i class="pi pi-verified"></i>
                            </div>
                        </div>

                        <div class="perfil-datos">
                            <h1 class="perfil-nombre">
                                {{ creador.usuario.nombre }}
                                <span v-if="creador.usuario.apodo" class="perfil-apodo">
                                    @{{ creador.usuario.apodo }}
                                </span>
                            </h1>

                            <div class="perfil-categorias">
                                <span v-for="cat in creador.categorias.slice(0, 3)" :key="cat" class="categoria-tag">
                                    {{ cat }}
                                </span>
                                <span v-if="creador.categorias.length > 3" class="categoria-tag categoria-tag--more">
                                    +{{ creador.categorias.length - 3 }}
                                </span>
                            </div>

                            <p class="perfil-biografia">
                                {{ creador.biografia || 'Creador de contenido exclusivo' }}
                            </p>

                            <div class="perfil-meta">
                                <span v-if="creador.usuario.ciudad" class="perfil-ubicacion">
                                    <i class="pi pi-map-marker"></i>
                                    {{ creador.usuario.ciudad }}
                                </span>
                                <span class="perfil-miembro">
                                    <i class="pi pi-calendar"></i>
                                    Miembro desde {{ formatearFecha(creador.usuario?.created_at) }}
                                </span>
                            </div>
                        </div>

                        <div class="perfil-acciones">
                            <template v-if="esMiPerfil">
                                <PvButton label="Editar perfil" icon="pi pi-pencil"
                                    class="perfil-btn perfil-btn--secondary" @click="irAEditarPerfil" />
                            </template>
                            <template v-else>
                                <template v-if="estaSuscrito">
                                    <PvButton label="Suscrito" icon="pi pi-check-circle"
                                        class="perfil-btn perfil-btn--success" disabled />
                                    <div class="suscripcion-info">
                                        <i class="pi pi-clock"></i>
                                        <span>Renueva el {{ formatearFecha(suscripcionActiva?.fecha_renovacion)
                                            }}</span>
                                    </div>
                                </template>
                                <template v-else>
                                    <PvButton label="Suscribirse" icon="pi pi-heart"
                                        class="perfil-btn perfil-btn--primary" @click="irASuscribirse" />
                                </template>
                            </template>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="perfil-stats">
                        <div class="stat-item">
                            <span class="stat-number">{{ formatearNumero(estadisticas.total_contenidos) }}</span>
                            <span class="stat-label">Publicaciones</span>
                        </div>
                        <div class="stat-divider"></div>
                        <div class="stat-item">
                            <span class="stat-number">{{ formatearNumero(estadisticas.total_suscriptores) }}</span>
                            <span class="stat-label">Suscriptores</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- CONTENIDO PRINCIPAL -->
            <!-- ============================================================ -->
            <div class="contenido-grid">
                <!-- FEED DE CONTENIDOS -->
                <div class="feed-column">
                    <div class="feed-header">
                        <div class="feed-header-left">
                            <h2>
                                <i class="pi pi-file"></i>
                                Publicaciones
                                <span class="feed-count">{{ contenidos.length }}</span>
                            </h2>
                        </div>
                        <div class="feed-filtros">
                            <button class="filtro-btn" :class="{ active: filtro === 'todos' }"
                                @click="filtro = 'todos'">
                                Todos
                            </button>
                            <button class="filtro-btn" :class="{ active: filtro === 'gratis' }"
                                @click="filtro = 'gratis'">
                                Gratis
                            </button>
                            <button v-if="estaSuscrito" class="filtro-btn" :class="{ active: filtro === 'premium' }"
                                @click="filtro = 'premium'">
                                Premium
                            </button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="contenidosFiltrados.length === 0" class="empty-state">
                        <div class="empty-state__icon">
                            <i class="pi pi-inbox"></i>
                        </div>
                        <h3>No hay publicaciones aún</h3>
                        <p>
                            <template v-if="!estaSuscrito">
                                Suscríbete para acceder al contenido exclusivo de este creador.
                            </template>
                            <template v-else>
                                Este creador aún no ha compartido contenido.
                            </template>
                        </p>
                        <PvButton v-if="!estaSuscrito" label="Suscribirme ahora" icon="pi pi-heart"
                            class="empty-state__btn" @click="irASuscribirse" />
                    </div>

                    <!-- Contenidos -->
                    <div v-else class="contenidos-grid">
                        <article v-for="contenido in contenidosFiltrados" :key="contenido.id" class="contenido-card"
                            :class="{ 'contenido-card--premium': contenido.es_premium }">
                            <div class="contenido-card__imagen" @click="verContenido(contenido.id)">
                                <img :src="getImageUrl(contenido.imagen || contenido.archivos?.[0]?.url)"
                                    :alt="contenido.titulo || 'Contenido'" @error="handleImageError" />
                                <span v-if="contenido.es_premium" class="contenido-card__badge">
                                    <i class="pi pi-crown"></i>
                                    Premium
                                </span>
                                <span v-if="!puedeAcceder(contenido)" class="contenido-card__lock">
                                    <i class="pi pi-lock"></i>
                                </span>
                            </div>

                            <div class="contenido-card__info">
                                <h3 class="contenido-card__titulo">{{ contenido.titulo || 'Sin título' }}</h3>
                                <p class="contenido-card__descripcion" v-if="contenido.descripcion">
                                    {{ truncarTexto(contenido.descripcion, 80) }}
                                </p>
                                <p class="contenido-card__descripcion" v-else-if="contenido.texto">
                                    {{ truncarTexto(contenido.texto, 80) }}
                                </p>
                                <div class="contenido-card__footer">
                                    <span class="contenido-card__fecha">
                                        <i class="pi pi-clock"></i>
                                        {{ contenido.tiempo || formatearFecha(contenido.created_at) }}
                                    </span>
                                    <div class="contenido-card__interacciones">
                                        <span class="interaccion">
                                            <i class="pi pi-heart"></i>
                                            {{ contenido.total_likes }}
                                        </span>
                                        <span class="interaccion">
                                            <i class="pi pi-comment"></i>
                                            {{ contenido.total_comentarios }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>

                    <!-- Paginación -->
                    <div v-if="paginacion.last_page > 1" class="pagination">
                        <button class="pagination__btn" :disabled="paginacion.current_page <= 1"
                            @click="cambiarPagina(paginacion.current_page - 1)">
                            <i class="pi pi-chevron-left"></i>
                        </button>
                        <div class="pagination__numbers">
                            <button v-for="n in paginacion.last_page" :key="n" class="pagination__num"
                                :class="{ active: n === paginacion.current_page }" @click="cambiarPagina(n)">
                                {{ n }}
                            </button>
                        </div>
                        <button class="pagination__btn" :disabled="paginacion.current_page >= paginacion.last_page"
                            @click="cambiarPagina(paginacion.current_page + 1)">
                            <i class="pi pi-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <!-- SIDEBAR -->
                <aside class="sidebar-column">
                    <!-- Planes de suscripción -->
                    <div v-if="!esMiPerfil && !estaSuscrito && creador.planes?.length" class="sidebar-card planes-card">
                        <div class="sidebar-card__header">
                            <h3>
                                <i class="pi pi-crown" style="color: var(--brand);"></i>
                                Planes de suscripción
                            </h3>
                        </div>
                        <div class="planes-list">
                            <div v-for="plan in creador.planes" :key="plan.id" class="plan-item"
                                :class="{ 'plan-item--popular': plan.popular }">
                                <div class="plan-header">
                                    <span class="plan-nombre">{{ plan.nombre }}</span>
                                    <span v-if="plan.popular" class="plan-popular">Popular</span>
                                </div>
                                <div class="plan-precio">
                                    <span class="plan-monto">${{ plan.precio }}</span>
                                    <span class="plan-periodo">/ {{ plan.dias }} días</span>
                                </div>
                                <p class="plan-descripcion">{{ plan.descripcion }}</p>
                                <PvButton label="Suscribirse" icon="pi pi-heart" class="plan-btn"
                                    @click="irASuscribirseConPlan(plan.id)" />
                            </div>
                        </div>
                    </div>

                    <!-- Acerca de - MEJORADO -->
                    <div class="sidebar-card info-card">
                        <div class="sidebar-card__header">
                            <h3>
                                <i class="pi pi-info-circle" style="color: var(--brand);"></i>
                                Acerca de
                            </h3>
                        </div>
                        <div class="info-content">
                            <!-- Biografía extendida -->
                            <div class="info-biografia">
                                <p>{{ creador.biografia || 'Creador de contenido exclusivo en Club de Fantasías.' }}</p>
                            </div>

                            <!-- Datos del creador -->
                            <div class="info-grid">
                                <div class="info-grid-item">
                                    <span class="info-grid-icon"><i class="pi pi-user"></i></span>
                                    <div>
                                        <span class="info-grid-label">Nombre</span>
                                        <strong class="info-grid-value">{{ creador.usuario.nombre }}</strong>
                                    </div>
                                </div>
                                <div class="info-grid-item" v-if="creador.usuario.apodo">
                                    <span class="info-grid-icon"><i class="pi pi-at"></i></span>
                                    <div>
                                        <span class="info-grid-label">Apodo</span>
                                        <strong class="info-grid-value">@{{ creador.usuario.apodo }}</strong>
                                    </div>
                                </div>
                                <div class="info-grid-item" v-if="creador.usuario.ciudad">
                                    <span class="info-grid-icon"><i class="pi pi-map-marker"></i></span>
                                    <div>
                                        <span class="info-grid-label">Ubicación</span>
                                        <strong class="info-grid-value">{{ creador.usuario.ciudad }}</strong>
                                    </div>
                                </div>
                                <div class="info-grid-item">
                                    <span class="info-grid-icon"><i class="pi pi-calendar"></i></span>
                                    <div>
                                        <span class="info-grid-label">Miembro desde</span>
                                        <strong class="info-grid-value">{{ formatearFecha(creador.usuario?.created_at)
                                            }}</strong>
                                    </div>
                                </div>
                                <div class="info-grid-item">
                                    <span class="info-grid-icon"><i class="pi pi-tag"></i></span>
                                    <div>
                                        <span class="info-grid-label">Categorías</span>
                                        <strong class="info-grid-value">{{ creador.categorias?.join(', ') || 'Sin categorías' }}</strong>
                                    </div>
                                </div>
                                <div class="info-grid-item">
                                    <span class="info-grid-icon"><i class="pi pi-shield"></i></span>
                                    <div>
                                        <span class="info-grid-label">Verificación</span>
                                        <strong class="info-grid-value"
                                            :class="{ 'text-success': creador.esta_verificado, 'text-muted': !creador.esta_verificado }">
                                            <i v-if="creador.esta_verificado" class="pi pi-check-circle"></i>
                                            {{ creador.esta_verificado ? 'Verificado' : 'Pendiente' }}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Compartir -->
                    <div class="sidebar-card share-card">
                        <div class="sidebar-card__header">
                            <h3>
                                <i class="pi pi-share-alt" style="color: var(--brand);"></i>
                                Compartir perfil
                            </h3>
                        </div>
                        <div class="share-buttons">
                            <button class="share-btn" @click="compartirPerfil('whatsapp')" title="WhatsApp">
                                <i class="pi pi-whatsapp" style="color: #25D366;"></i>
                            </button>
                            <button class="share-btn" @click="compartirPerfil('facebook')" title="Facebook">
                                <i class="pi pi-facebook" style="color: #1877F2;"></i>
                            </button>
                            <button class="share-btn" @click="compartirPerfil('twitter')" title="Twitter / X">
                                <i class="pi pi-twitter" style="color: #000000;"></i>
                            </button>
                            <button class="share-btn" @click="copiarLinkPerfil" title="Copiar enlace">
                                <i class="pi pi-link" style="color: var(--brand);"></i>
                            </button>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import PvButton from 'primevue/button';

// ============================================================
// PROPS
// ============================================================
const props = defineProps({
    creador: {
        type: Object,
        required: true
    },
    contenidos: {
        type: Array,
        default: () => []
    },
    estadisticas: {
        type: Object,
        default: () => ({})
    },
    estaSuscrito: {
        type: Boolean,
        default: false
    },
    suscripcionActiva: {
        type: Object,
        default: null
    },
    esMiPerfil: {
        type: Boolean,
        default: false
    }
});

// ============================================================
// REFERENCIAS
// ============================================================
const toastRef = ref(null);
const filtro = ref('todos');
const paginacion = ref({
    current_page: 1,
    last_page: 1,
    per_page: 12,
    total: props.contenidos.length
});

// ============================================================
// TOAST
// ============================================================
function showToast(type, title, message) {
    if (toastRef.value) {
        toastRef.value.showToast({
            type: type,
            title: title,
            message: message,
            duration: 3000
        });
    }
}

function showSuccess(message) {
    showToast('success', 'Éxito', message);
}

function showError(message) {
    showToast('error', 'Error', message);
}

function showInfo(message) {
    showToast('info', 'Información', message);
}

// ============================================================
// FUNCIONES DE UTILIDAD
// ============================================================
function getAvatarUrl(usuario) {
    if (!usuario) return '/images/shared/avatar-default.jpg';

    if (usuario.foto_principal) {
        if (usuario.foto_principal.startsWith('http')) return usuario.foto_principal;
        if (usuario.foto_principal.startsWith('/')) return usuario.foto_principal;
        return '/storage/' + usuario.foto_principal;
    }

    if (usuario.avatar) {
        if (usuario.avatar.startsWith('http')) return usuario.avatar;
        if (usuario.avatar.startsWith('/')) return usuario.avatar;
        return '/storage/' + usuario.avatar;
    }

    return '/images/shared/avatar-default.jpg';
}

function getImageUrl(imagen) {
    if (!imagen) return '/images/shared/placeholder-image.jpg';
    if (imagen.startsWith('http')) return imagen;
    if (imagen.startsWith('/')) return imagen;
    return '/storage/' + imagen;
}

function handleImageError(event) {
    event.target.src = '/images/shared/placeholder-image.jpg';
    event.target.onerror = null;
}

function formatearFecha(fecha) {
    if (!fecha) return 'N/A';
    const date = new Date(fecha);
    return date.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

function formatearNumero(numero) {
    if (numero >= 1000000) return (numero / 1000000).toFixed(1) + 'M';
    if (numero >= 1000) return (numero / 1000).toFixed(1) + 'K';
    return numero;
}

function truncarTexto(texto, max) {
    if (!texto) return '';
    return texto.length > max ? texto.substring(0, max) + '...' : texto;
}

function puedeAcceder(contenido) {
    if (!contenido.es_premium) return true;
    return props.estaSuscrito;
}

// ============================================================
// CONTENIDOS FILTRADOS
// ============================================================
const contenidosFiltrados = computed(() => {
    let lista = [...props.contenidos];

    if (filtro.value === 'gratis') {
        lista = lista.filter(c => !c.es_premium);
    } else if (filtro.value === 'premium') {
        lista = lista.filter(c => c.es_premium);
    }

    return lista;
});

// ============================================================
// NAVEGACIÓN
// ============================================================
function volverAtras() {
    window.history.back();
}

function verContenido(id) {
    router.get(`/contenido/${id}`);
}

function irASuscribirse() {
    const slug = props.creador.usuario.apodo || 'perfil';
    router.get(`/creador/${props.creador.id}/${slug}/suscripcion`);
}

function irASuscribirseConPlan(planId) {
    const slug = props.creador.usuario.apodo || 'perfil';
    router.get(`/creador/${props.creador.id}/${slug}/suscripcion?plan=${planId}`);
}

function irAEditarPerfil() {
    router.get('/creador/editar-perfil');
}

function cambiarPagina(pagina) {
    if (pagina < 1 || pagina > paginacion.value.last_page) return;

    router.get(`/creador/${props.creador.id}/${props.creador.usuario.apodo || 'perfil'}?page=${pagina}`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
}

// ============================================================
// COMPARTIR - SIN EMOJIS
// ============================================================
const perfilUrl = computed(() => {
    return window.location.origin + `/creador/${props.creador.id}/${props.creador.usuario.apodo || 'perfil'}`;
});

function compartirPerfil(plataforma) {
    const url = encodeURIComponent(perfilUrl.value);
    const nombre = props.creador.usuario.nombre;

    const mensajes = {
        whatsapp: `Descubre el contenido exclusivo de ${nombre} en Club de Fantasías.\n\nUnete a su comunidad y accede a contenido unico, fotos, videos y mas.\n\n${perfilUrl.value}`,
        facebook: `Descubre el contenido de ${nombre} en Club de Fantasías.\n\nUnete a su comunidad y accede a contenido exclusivo.\n\n${perfilUrl.value}`,
        twitter: `Descubre el contenido de ${nombre} en Club de Fantasías.\n\nUnete a su comunidad y accede a contenido exclusivo.\n\n${perfilUrl.value}`
    };

    const texto = encodeURIComponent(mensajes[plataforma]);

    let link = '';
    switch (plataforma) {
        case 'whatsapp':
            link = `https://wa.me/?text=${texto}`;
            break;
        case 'facebook':
            link = `https://www.facebook.com/sharer/sharer.php?u=${url}&quote=${encodeURIComponent(`Descubre el contenido de ${nombre} en Club de Fantasías`)}`;
            break;
        case 'twitter':
            link = `https://twitter.com/intent/tweet?text=${texto}&url=${url}`;
            break;
        default:
            return;
    }

    window.open(link, '_blank', 'width=600,height=500');
}

async function copiarLinkPerfil() {
    const mensaje = `Descubre el contenido de ${props.creador.usuario.nombre} en Club de Fantasías.\n\nUnete a su comunidad y accede a contenido exclusivo.\n\n${perfilUrl.value}`;

    try {
        await navigator.clipboard.writeText(mensaje);
        showSuccess('Enlace y mensaje copiados al portapapeles');
    } catch {
        const textarea = document.createElement('textarea');
        textarea.value = mensaje;
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showSuccess('Enlace y mensaje copiados al portapapeles');
        } catch {
            showError('No se pudo copiar el enlace');
        }
        document.body.removeChild(textarea);
    }
}

// ============================================================
// LIFECYCLE
// ============================================================
onMounted(() => {
    console.log('=== PERFIL CREADOR ===');
    console.log('Creador:', props.creador);
    console.log('Contenidos:', props.contenidos);
    console.log('Estadísticas:', props.estadisticas);
    console.log('Suscrito:', props.estaSuscrito);
});
</script>

<style scoped>
.perfil-creador-page {
    --brand: #C81E3A;
    --brand-dark: #A6152D;
    --brand-soft: #FBEAEC;
    --success: #2F855A;
    --success-bg: #F0FFF4;
    --danger: #C53030;
    --warning: #DD6B20;
    --ink: #171412;
    --ink-soft: #4B4744;
    --muted: #8A8481;
    --muted-light: #B7B2AF;
    --line: #ECE9E7;
    --surface: #FAF8F7;
    --white: #FFFFFF;
    --shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    --shadow-hover: 0 4px 16px rgba(0, 0, 0, 0.08);
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 20px;
    --radius-full: 999px;

    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: var(--ink);
    background: #f0f2f5;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 2rem 2rem;
}

/* =========================================================================
   VOLVER
   ========================================================================= */
.volver-container {
    padding: 1rem 0 0.5rem;
}

.btn-volver {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.2rem;
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--white);
    color: var(--ink-soft);
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: var(--shadow);
}

.btn-volver:hover {
    border-color: var(--brand);
    color: var(--brand);
    transform: translateX(-3px);
    box-shadow: var(--shadow-hover);
}

/* =========================================================================
   PERFIL
   ========================================================================= */
.perfil-section {
    margin-bottom: 1.5rem;
}

.perfil-container {
    background: var(--white);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow);
    border: 1px solid var(--line);
}

.perfil-header {
    display: flex;
    padding: 1.5rem 2rem 0.5rem;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.perfil-avatar-wrapper {
    position: relative;
    flex-shrink: 0;
}

.perfil-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--white);
    box-shadow: var(--shadow);
    transition: transform 0.3s ease;
}

.perfil-avatar:hover {
    transform: scale(1.05);
}

.perfil-verified {
    position: absolute;
    bottom: 4px;
    right: 4px;
    background: var(--brand);
    color: var(--white);
    border-radius: 50%;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--white);
    font-size: 0.7rem;
}

.perfil-datos {
    flex: 1;
    padding-top: 0.5rem;
}

.perfil-nombre {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.perfil-apodo {
    font-size: 0.9rem;
    color: var(--muted);
    font-weight: 400;
}

.perfil-categorias {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
    margin-bottom: 0.4rem;
}

.categoria-tag {
    font-size: 0.6rem;
    padding: 0.1rem 0.5rem;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius-full);
    color: var(--ink-soft);
}

.categoria-tag--more {
    background: var(--brand-soft);
    color: var(--brand);
    border-color: var(--brand-soft);
}

.perfil-biografia {
    font-size: 0.85rem;
    color: var(--ink-soft);
    margin: 0 0 0.25rem;
    max-width: 600px;
    line-height: 1.5;
}

.perfil-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    font-size: 0.7rem;
    color: var(--muted);
}

.perfil-ubicacion,
.perfil-miembro {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.perfil-acciones {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-top: 0.5rem;
    flex-wrap: wrap;
}

.perfil-btn {
    font-weight: 600 !important;
}

.perfil-btn--primary {
    background: var(--brand) !important;
    border-color: var(--brand) !important;
    color: var(--white) !important;
}

.perfil-btn--primary:hover {
    background: var(--brand-dark) !important;
    border-color: var(--brand-dark) !important;
}

.perfil-btn--secondary {
    background: var(--surface) !important;
    border-color: var(--line) !important;
    color: var(--ink) !important;
}

.perfil-btn--secondary:hover {
    background: var(--line) !important;
}

.perfil-btn--success {
    background: var(--success) !important;
    border-color: var(--success) !important;
    color: var(--white) !important;
    cursor: default;
}

.suscripcion-info {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.7rem;
    color: var(--muted);
}

/* =========================================================================
   PERFIL STATS
   ========================================================================= */
.perfil-stats {
    display: flex;
    justify-content: center;
    padding: 0.8rem 2rem 1rem;
    border-top: 1px solid var(--line);
    gap: 2rem;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--ink);
}

.stat-label {
    font-size: 0.65rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.stat-divider {
    width: 1px;
    background: var(--line);
}

/* =========================================================================
   CONTENIDO GRID
   ========================================================================= */
.contenido-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1100px) {
    .contenido-grid {
        grid-template-columns: 1fr;
    }
}

/* =========================================================================
   FEED
   ========================================================================= */
.feed-column {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.feed-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    background: var(--white);
    padding: 0.8rem 1.5rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
}

.feed-header-left h2 {
    font-size: 0.9rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.feed-count {
    background: var(--brand-soft);
    color: var(--brand);
    font-size: 0.65rem;
    padding: 0.05rem 0.4rem;
    border-radius: var(--radius-full);
    font-weight: 700;
}

.feed-filtros {
    display: flex;
    gap: 0.3rem;
}

.filtro-btn {
    padding: 0.2rem 0.6rem;
    border: 1px solid var(--line);
    border-radius: var(--radius-full);
    background: var(--white);
    font-size: 0.65rem;
    font-weight: 600;
    color: var(--ink-soft);
    cursor: pointer;
    transition: all 0.2s ease;
}

.filtro-btn:hover {
    border-color: var(--brand);
    color: var(--brand);
}

.filtro-btn.active {
    background: var(--brand);
    color: var(--white);
    border-color: var(--brand);
}

/* =========================================================================
   CONTENIDOS GRID
   ========================================================================= */
.contenidos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}

.contenido-card {
    background: var(--white);
    border-radius: var(--radius-md);
    overflow: hidden;
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}

.contenido-card:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-3px);
}

.contenido-card--premium {
    border-top: 3px solid var(--warning);
}

.contenido-card__imagen {
    position: relative;
    aspect-ratio: 16/9;
    background: var(--surface);
    cursor: pointer;
    overflow: hidden;
}

.contenido-card__imagen img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.contenido-card:hover .contenido-card__imagen img {
    transform: scale(1.05);
}

.contenido-card__badge {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    background: var(--warning);
    color: #fff;
    font-size: 0.55rem;
    font-weight: 700;
    padding: 0.1rem 0.5rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.2rem;
}

.contenido-card__lock {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: rgba(0, 0, 0, 0.6);
    color: #fff;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    backdrop-filter: blur(4px);
}

.contenido-card__info {
    padding: 0.8rem 1rem;
}

.contenido-card__titulo {
    font-size: 0.85rem;
    font-weight: 700;
    margin: 0 0 0.2rem;
    line-height: 1.3;
}

.contenido-card__descripcion {
    font-size: 0.75rem;
    color: var(--ink-soft);
    margin: 0 0 0.4rem;
    line-height: 1.4;
}

.contenido-card__footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.65rem;
    color: var(--muted);
    border-top: 1px solid var(--line);
    padding-top: 0.4rem;
}

.contenido-card__fecha {
    display: flex;
    align-items: center;
    gap: 0.2rem;
}

.contenido-card__interacciones {
    display: flex;
    gap: 0.5rem;
}

.interaccion {
    display: flex;
    align-items: center;
    gap: 0.15rem;
}

/* =========================================================================
   EMPTY STATE
   ========================================================================= */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    background: var(--white);
    border-radius: var(--radius-md);
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
}

.empty-state__icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto 1rem;
}

.empty-state h3 {
    font-size: 1.1rem;
    margin: 0 0 0.3rem;
}

.empty-state p {
    color: var(--muted);
    font-size: 0.85rem;
    margin: 0 0 1rem;
}

.empty-state__btn {
    background: var(--brand) !important;
    border-color: var(--brand) !important;
    color: var(--white) !important;
}

/* =========================================================================
   PAGINACIÓN
   ========================================================================= */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.3rem;
    padding: 0.5rem 0;
}

.pagination__btn {
    padding: 0.3rem 0.8rem;
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--white);
    color: var(--ink-soft);
    cursor: pointer;
    transition: all 0.2s ease;
}

.pagination__btn:hover:not(:disabled) {
    border-color: var(--brand);
    color: var(--brand);
}

.pagination__btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.pagination__numbers {
    display: flex;
    gap: 0.15rem;
}

.pagination__num {
    width: 34px;
    height: 34px;
    border-radius: var(--radius-sm);
    border: 1px solid transparent;
    background: transparent;
    color: var(--ink-soft);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.pagination__num:hover {
    border-color: var(--line);
}

.pagination__num.active {
    background: var(--brand);
    color: var(--white);
    border-color: var(--brand);
}

/* =========================================================================
   SIDEBAR
   ========================================================================= */
.sidebar-column {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.sidebar-card {
    background: var(--white);
    border-radius: var(--radius-md);
    padding: 1.25rem;
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
}

.sidebar-card__header h3 {
    font-size: 0.85rem;
    margin: 0 0 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Planes */
.planes-list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.plan-item {
    padding: 0.8rem 1rem;
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    transition: all 0.3s ease;
}

.plan-item--popular {
    border-color: var(--brand);
    background: var(--brand-soft);
}

.plan-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.2rem;
}

.plan-nombre {
    font-weight: 700;
    font-size: 0.85rem;
}

.plan-popular {
    font-size: 0.55rem;
    font-weight: 700;
    color: var(--brand);
    background: var(--brand-soft);
    padding: 0.05rem 0.4rem;
    border-radius: var(--radius-full);
}

.plan-precio {
    margin-bottom: 0.2rem;
}

.plan-monto {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--brand);
}

.plan-periodo {
    font-size: 0.7rem;
    color: var(--muted);
}

.plan-descripcion {
    font-size: 0.7rem;
    color: var(--muted);
    margin: 0 0 0.5rem;
}

.plan-btn {
    width: 100%;
    font-size: 0.7rem !important;
    padding: 0.3rem !important;
    background: var(--brand) !important;
    border-color: var(--brand) !important;
    color: var(--white) !important;
}

.plan-btn:hover {
    background: var(--brand-dark) !important;
    border-color: var(--brand-dark) !important;
}

/* Info - MEJORADO */
.info-content {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.info-biografia p {
    font-size: 0.8rem;
    color: var(--ink-soft);
    line-height: 1.6;
    margin: 0;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.5rem;
    border-top: 1px solid var(--line);
    padding-top: 0.75rem;
}

.info-grid-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.info-grid-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--surface);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.6rem;
    color: var(--brand);
    flex-shrink: 0;
}

.info-grid-item div {
    display: flex;
    flex-direction: column;
}

.info-grid-label {
    font-size: 0.55rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--muted-light);
}

.info-grid-value {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ink);
}

.text-success {
    color: var(--success);
}

.text-muted {
    color: var(--muted);
}

/* Share */
.share-buttons {
    display: flex;
    gap: 0.5rem;
}

.share-btn {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: 1px solid var(--line);
    background: var(--white);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.share-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
    border-color: var(--brand);
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
    .perfil-creador-page {
        padding: 0 1rem 1rem;
    }
}

@media (max-width: 768px) {
    .perfil-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1rem 1rem 0.5rem;
    }

    .perfil-datos {
        padding-top: 0;
        text-align: center;
    }

    .perfil-nombre {
        justify-content: center;
    }

    .perfil-categorias {
        justify-content: center;
    }

    .perfil-biografia {
        text-align: center;
        margin: 0 auto 0.25rem;
    }

    .perfil-meta {
        justify-content: center;
    }

    .perfil-acciones {
        justify-content: center;
        width: 100%;
    }

    .perfil-acciones .p-button {
        width: 100%;
        justify-content: center;
    }

    .perfil-stats {
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.8rem 1rem;
    }

    .stat-divider {
        display: none;
    }

    .contenidos-grid {
        grid-template-columns: 1fr;
    }

    .feed-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
        gap: 0.5rem;
    }

    .feed-header-left {
        text-align: center;
    }

    .feed-filtros {
        justify-content: center;
    }

    .info-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .perfil-creador-page {
        padding: 0 0.5rem 0.5rem;
    }

    .perfil-avatar {
        width: 80px;
        height: 80px;
    }

    .perfil-verified {
        width: 22px;
        height: 22px;
        font-size: 0.55rem;
        bottom: 2px;
        right: 2px;
    }

    .perfil-nombre {
        font-size: 1.2rem;
    }

    .perfil-stats {
        gap: 0.8rem;
    }

    .stat-number {
        font-size: 1rem;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .planes-list {
        gap: 0.4rem;
    }

    .plan-item {
        padding: 0.6rem 0.8rem;
    }

    .sidebar-card {
        padding: 1rem;
    }

    .volver-container {
        padding: 0.5rem 0;
    }

    .btn-volver {
        padding: 0.4rem 0.8rem;
        font-size: 0.7rem;
    }
}
</style>