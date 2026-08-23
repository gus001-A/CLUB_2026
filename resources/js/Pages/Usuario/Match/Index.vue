<template>

    <Head title="Descubrir" />

    <AppLayout activeNav="descubrir" :usuario="usuario">
        <div class="descubrir-page">
            <!-- ============================================================ -->
            <!-- ENCABEZADO -->
            <!-- ============================================================ -->
            <section class="page-heading">
                <div>
                    <h1>Descubre conexiones <span>reales y compatibles</span></h1>
                </div>
                <div class="page-heading__trust">
                    <div v-for="item in confianza" :key="item.titulo" class="trust-item">
                        <span class="trust-item__icon"><i class="pi" :class="item.icon"></i></span>
                        <span>{{ item.titulo }}</span>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- BARRA DE FILTROS -->
            <!-- ============================================================ -->
            <section class="filters-bar">
                <label class="filter-toggle">
                    <span class="toggle-switch">
                        <input type="checkbox" v-model="filtros.soloVerificados" />
                        <span class="toggle-slider"></span>
                    </span>
                    Solo verificados
                </label>

                <button class="filter-pill" :class="{ selected: filtros.tipoPerfil === 'personal' }"
                    @click="filtros.tipoPerfil = 'personal'">
                    <i class="pi pi-user"></i> Personal
                </button>
                <button class="filter-pill" :class="{ selected: filtros.tipoPerfil === 'pareja' }"
                    @click="filtros.tipoPerfil = 'pareja'">
                    <i class="pi pi-users"></i> Pareja
                </button>
                <button class="filter-pill" :class="{ selected: filtros.tipoPerfil === 'todos' }"
                    @click="filtros.tipoPerfil = 'todos'">
                    <i class="pi pi-filter"></i> Todos
                </button>

                <button class="filter-btn" @click="panelFiltrosAbierto = !panelFiltrosAbierto">
                    <i class="pi pi-sliders-h"></i> Filtros
                    <span v-if="totalFiltrosActivos > 0" class="filter-badge">{{ totalFiltrosActivos }}</span>
                </button>

                <!-- Panel de filtros -->
                <div v-if="panelFiltrosAbierto" class="filters-popover">
                    <div class="filters-popover__section">
                        <label class="filters-popover__label">
                            Distancia máxima
                            <strong>{{ distanciaMax >= 100 ? 'Sin límite' : distanciaMax + ' km' }}</strong>
                        </label>
                        <input type="range" min="1" max="100" v-model.number="distanciaMax"
                            class="filters-popover__range" />
                    </div>

                    <div class="filters-popover__section">
                        <label class="filters-popover__label">Intereses en común</label>
                        <div class="filters-popover__chips">
                            <button v-for="interes in interesesDisponibles" :key="interes.label" type="button"
                                class="filter-pill filter-pill--small"
                                :class="{ selected: interesesSeleccionados.includes(interes.label) }"
                                @click="alternarInteresFiltro(interes.label)">
                                <i class="pi" :class="interes.icon"></i> {{ interes.label }}
                            </button>
                        </div>
                    </div>

                    <div class="filters-popover__actions">
                        <button class="filters-popover__clear" @click="limpiarFiltros">Limpiar</button>
                        <button class="filters-popover__apply" @click="aplicarFiltros">Aplicar filtros</button>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- STACK DE PERFILES -->
            <!-- ============================================================ -->
            <section class="swipe-area">
                <div v-if="perfilesLista.length === 0 && likesRecibidos.length === 0" class="empty-state">
                    <i class="pi pi-users"></i>
                    <h3>No hay perfiles disponibles</h3>
                    <p>Pronto aparecerán nuevos perfiles para ti.</p>
                </div>

                <div v-else-if="perfilesLista.length === 0 && likesRecibidos.length > 0" class="empty-state">
                    <i class="pi pi-heart"></i>
                    <h3>¡Tienes likes pendientes!</h3>
                    <p>Revisa la sección de abajo para aceptar o rechazar.</p>
                </div>

                <div v-else class="swipe-stack">
                    <div v-if="perfilAnterior" class="swipe-card swipe-card--side swipe-card--left">
                        <img :src="getAvatarUrl(perfilAnterior.imagen)" :alt="perfilAnterior.nombre" />
                        <span class="side-badge">Verificado</span>
                    </div>

                    <div v-if="perfilActual" class="swipe-card swipe-card--main">
                        <img :src="getAvatarUrl(perfilActual.imagen)" :alt="perfilActual.nombre" />
                        <div class="swipe-card__gradient"></div>

                        <span class="verified-badge"><i class="pi pi-check-circle"></i> Verificado</span>
                        <span v-if="perfilActual.enLinea" class="online-badge"><i class="pi pi-circle-fill"></i> En
                            línea</span>

                        <div class="swipe-card__content">
                            <h2>{{ perfilActual.nombre }} <i class="pi pi-verified"></i></h2>
                            <p class="swipe-card__location"><i class="pi pi-map-marker"></i> {{ perfilActual.ciudad }}
                                &nbsp;•&nbsp; <i class="pi pi-users"></i> {{ perfilActual.tipo }}</p>
                            <p class="swipe-card__desc">{{ perfilActual.descripcion }}</p>

                            <div v-if="perfilActual.intereses && perfilActual.intereses.length > 0"
                                class="swipe-card__tags">
                                <span v-for="t in perfilActual.intereses" :key="t.label" class="tag"><i class="pi"
                                        :class="t.icon"></i> {{ t.label }}</span>
                            </div>
                            <div v-if="perfilActual.interesesExtra && perfilActual.interesesExtra.length > 0"
                                class="swipe-card__tags">
                                <span v-for="t in perfilActual.interesesExtra" :key="t.label" class="tag"><i class="pi"
                                        :class="t.icon"></i> {{ t.label }}</span>
                            </div>

                            <div class="swipe-card__footer">
                                <span class="swipe-card__distance"><i class="pi pi-map-marker"></i> {{
                                    perfilActual.distancia || 'Cerca de ti' }}</span>
                                <span class="swipe-card__compat">Compatibilidad <strong>{{ perfilActual.compatibilidad
                                    || 0 }}%</strong></span>
                            </div>

                            <div class="swipe-card__dots">
                                <span v-for="(p, i) in perfilesLista" :key="i" class="dot"
                                    :class="{ active: i === indiceActual }"></span>
                            </div>
                        </div>
                    </div>

                    <div v-if="perfilSiguiente" class="swipe-card swipe-card--side swipe-card--right">
                        <img :src="getAvatarUrl(perfilSiguiente.imagen)" :alt="perfilSiguiente.nombre" />
                        <span class="side-badge side-badge--online"><i class="pi pi-circle-fill"></i> En línea</span>
                    </div>
                </div>

                <div v-if="perfilesLista.length > 0" class="swipe-actions">
                    <button class="swipe-btn swipe-btn--pass" :disabled="enviandoAccion" @click="pasar"><i
                            class="pi pi-times"></i></button>
                    <button class="swipe-btn swipe-btn--star" :disabled="enviandoAccion" @click="destacar"><i
                            class="pi pi-star-fill"></i></button>
                    <button class="swipe-btn swipe-btn--like" :disabled="enviandoAccion" @click="conectar"><i
                            class="pi pi-heart-fill"></i></button>
                </div>

                <div v-if="perfilesLista.length > 0" class="swipe-hints">
                    <span class="swipe-hint"><i class="pi pi-arrow-left"></i> Desliza a la izquierda<br />para
                        pasar</span>
                    <span class="swipe-hint swipe-hint--center">Conexión express<br />Destaca tu interés</span>
                    <span class="swipe-hint swipe-hint--right">Desliza a la derecha<br />para conectar <i
                            class="pi pi-arrow-right"></i></span>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- ACTIVIDAD RECIENTE - MATCHES + LIKES RECIBIDOS SIDE BY SIDE -->
            <!-- ============================================================ -->
            <section class="bottom-grid">
                <!-- MATCHES RECIENTES - VERSIÓN COMPACTA -->
                <div class="matches-card">
                    <div class="matches-card__header">
                        <div class="matches-card__header-left">
                            <div class="matches-card__icon-wrapper">
                                <i class="pi pi-heart-fill"></i>
                            </div>
                            <div>
                                <h3>Matches recientes</h3>
                                <span class="matches-card__count">{{ matches.length }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="matches.length === 0" class="empty-matches">
                        <div class="empty-matches__icon">
                            <i class="pi pi-heart"></i>
                        </div>
                        <span>Sin matches aún</span>
                        <p>Conecta con personas para verlas aquí</p>
                    </div>

                    <div v-else class="matches-grid">
                        <div v-for="m in matches" :key="m.usuario_id || m.nombre" class="mini-match"
                            @click="abrirModalPerfil(m)">
                            <img :src="getAvatarUrl(m.imagen)" :alt="m.nombre" />
                            <span v-if="m.verificado" class="mini-match__verified">
                                <i class="pi pi-check-circle"></i>
                            </span>
                            <div class="mini-match__info">
                                <strong>{{ m.nombre }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ❤️ LIKES RECIBIDOS -->
                <div class="likes-card">
                    <div class="likes-card__header">
                        <div class="likes-card__header-left">
                            <div class="likes-card__icon-wrapper">
                                <i class="pi pi-heart-fill"></i>
                            </div>
                            <div>
                                <h3>Te han dado like</h3>
                                <span class="likes-card__count">{{ likesRecibidos.length }}</span>
                            </div>
                        </div>
                        <span class="likes-card__badge">
                            <i class="pi pi-clock"></i>
                            {{ likesRecibidos.length }} pendiente(s)
                        </span>
                    </div>

                    <div v-if="likesRecibidos.length === 0" class="empty-likes">
                        <div class="empty-likes__icon">
                            <i class="pi pi-inbox"></i>
                        </div>
                        <span>Sin likes aún</span>
                        <p>Cuando alguien te dé like aparecerá aquí</p>
                    </div>

                    <div v-else class="likes-grid">
                        <div v-for="like in likesRecibidos" :key="like.id" class="like-item">
                            <div class="like-item__avatar-wrapper">
                                <img :src="getAvatarUrl(like.imagen)" :alt="like.nombre" class="like-item__avatar"
                                    @error="handleAvatarError" />
                                <span v-if="like.verificado" class="like-item__verified">
                                    <i class="pi pi-verified"></i>
                                </span>
                                <span class="like-item__compat-badge">
                                    {{ like.compatibilidad || 0 }}%
                                </span>
                            </div>
                            <div class="like-item__info">
                                <div class="like-item__nombre">
                                    {{ like.nombre }}
                                    <span v-if="like.apodo" class="like-item__apodo">@{{ like.apodo }}</span>
                                </div>
                                <div class="like-item__detalles">
                                    <span v-if="like.edad" class="like-item__edad">
                                        <i class="pi pi-calendar"></i> {{ like.edad }}a
                                    </span>
                                    <span class="like-item__tipo" v-if="like.tipo">
                                        <i class="pi pi-users"></i> {{ like.tipo }}
                                    </span>
                                    <span class="like-item__fecha">
                                        <i class="pi pi-clock"></i> {{ timeAgo(like.created_at) }}
                                    </span>
                                </div>
                                <div class="like-item__compatibilidad" :class="{
                                    'compat-alta': like.compatibilidad >= 70,
                                    'compat-media': like.compatibilidad >= 40 && like.compatibilidad < 70,
                                    'compat-baja': like.compatibilidad < 40
                                }">
                                    <div class="compat-bar">
                                        <div class="compat-bar__fill"
                                            :style="{ width: (like.compatibilidad || 0) + '%' }"></div>
                                    </div>
                                    <span class="compat-text">{{ like.compatibilidad || 0 }}%</span>
                                </div>
                            </div>
                            <div class="like-item__acciones">
                                <button class="like-btn like-btn--aceptar" @click="aceptarLike(like.id)"
                                    title="Aceptar">
                                    <i class="pi pi-check"></i>
                                </button>
                                <button class="like-btn like-btn--rechazar" @click="rechazarLike(like.id)"
                                    title="Rechazar">
                                    <i class="pi pi-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL "¡ES UN MATCH!" - REDISEÑADO -->
        <!-- ============================================================ -->
        <Transition name="match-modal-fade">
            <div v-if="modalMatch.visible" class="match-modal-overlay" @click.self="cerrarModalMatch">
                <div class="match-modal">
                    <!-- Decorative background elements -->
                    <div class="match-modal__bg-decoration">
                        <div class="match-modal__bg-circle match-modal__bg-circle--1"></div>
                        <div class="match-modal__bg-circle match-modal__bg-circle--2"></div>
                        <div class="match-modal__bg-circle match-modal__bg-circle--3"></div>
                    </div>

                    <!-- Close button -->
                    <button class="match-modal__close" @click="cerrarModalMatch">
                        <i class="pi pi-times"></i>
                    </button>

                    <!-- Header with gradient -->
                    <div class="match-modal__header">
                        <div class="match-modal__header-glow"></div>
                        <div class="match-modal__header-particles">
                            <span class="particle particle-1">✦</span>
                            <span class="particle particle-2">✦</span>
                            <span class="particle particle-3">✦</span>
                            <span class="particle particle-4">✦</span>
                            <span class="particle particle-5">✦</span>
                            <span class="particle particle-6">✦</span>
                        </div>
                    </div>

                    <!-- Avatars -->
                    <div class="match-modal__avatars">
                        <div class="match-modal__avatar-wrapper">
                            <img :src="getAvatarUrl(usuario.avatar)" alt="Tú"
                                class="match-modal__avatar match-modal__avatar--mine" />
                            <span class="match-modal__avatar-label">Tú</span>
                        </div>

                        <div class="match-modal__heart-container">
                            <div class="match-modal__heart-pulse">
                                <div class="match-modal__heart-icon">
                                    <i class="pi pi-heart-fill"></i>
                                </div>
                            </div>
                        </div>

                        <div class="match-modal__avatar-wrapper">
                            <img :src="getAvatarUrl(modalMatch.perfil?.avatar)" :alt="modalMatch.perfil?.nombre"
                                class="match-modal__avatar match-modal__avatar--theirs" />
                            <span class="match-modal__avatar-label">{{ modalMatch.perfil?.nombre }}</span>
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="match-modal__title-wrapper">
                        <span class="match-modal__title-badge">Match</span>
                        <h2 class="match-modal__title">Conexión confirmada</h2>
                        <p class="match-modal__subtitle">
                            Tú y <strong>{{ modalMatch.perfil?.nombre }}</strong> han coincidido.
                            Inicien la conversación con un mensaje.
                        </p>
                    </div>

                    <!-- Mensajes rápidos -->
                    <div v-if="!modalMatch.enviado" class="match-modal__quick-messages">
                        <p class="match-modal__quick-label">Mensajes rápidos</p>
                        <div class="match-modal__chips">
                            <button v-for="msg in mensajesFlash" :key="msg" type="button" class="match-modal__chip"
                                @click="elegirMensajeFlash(msg)">
                                {{ msg }}
                            </button>
                        </div>
                    </div>

                    <!-- Composer -->
                    <div v-if="!modalMatch.enviado" class="match-modal__composer">
                        <div class="match-modal__composer-wrapper">
                            <input v-model="modalMatch.texto" type="text" maxlength="300"
                                placeholder="Escribe tu mensaje..." @keyup.enter="enviarMensajeFlash"
                                class="match-modal__input" />
                            <button class="match-modal__send-btn"
                                :disabled="!modalMatch.texto.trim() || modalMatch.enviando" @click="enviarMensajeFlash">
                                <i v-if="modalMatch.enviando" class="pi pi-spin pi-spinner"></i>
                                <i v-else class="pi pi-send"></i>
                            </button>
                        </div>
                        <button class="match-modal__skip" @click="cerrarModalMatch">
                            Seguir explorando
                        </button>
                    </div>

                    <!-- Enviado -->
                    <div v-else class="match-modal__sent">
                        <div class="match-modal__sent-icon">
                            <i class="pi pi-check-circle"></i>
                        </div>
                        <h3 class="match-modal__sent-title">Mensaje enviado</h3>
                        <p class="match-modal__sent-text">¡La conversación ha comenzado!</p>
                        <button class="match-modal__cta" @click="irAConversacion">
                            <i class="pi pi-comment"></i>
                            Ir a la conversación
                        </button>
                        <button class="match-modal__skip" @click="cerrarModalMatch">
                            Seguir explorando
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- ============================================================ -->
        <!-- MODAL PERFIL MATCH - MEJORADO -->
        <!-- ============================================================ -->
        <Transition name="modal-fade">
            <div v-if="modalPerfilVisible" class="modal-overlay" @click.self="cerrarModalPerfil">
                <div class="modal-perfil">
                    <button class="modal-perfil__close" @click="cerrarModalPerfil">
                        <i class="pi pi-times"></i>
                    </button>

                    <div v-if="cargandoPerfil" class="modal-perfil__loading">
                        <div class="modal-perfil__loading-spinner">
                            <i class="pi pi-spin pi-spinner"></i>
                        </div>
                        <span>Cargando perfil...</span>
                    </div>

                    <template v-else-if="perfilSeleccionado">
                        <!-- Header con foto de portada -->
                        <div class="modal-perfil__cover">
                            <div class="modal-perfil__cover-gradient"></div>
                            <div class="modal-perfil__avatar-wrapper">
                                <div class="modal-perfil__avatar">
                                    <img :src="getAvatarUrl(perfilSeleccionado.imagen)"
                                        :alt="perfilSeleccionado.nombre" />
                                    <span v-if="perfilSeleccionado.verificado" class="modal-perfil__verified">
                                        <i class="pi pi-verified"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Información del perfil -->
                        <div class="modal-perfil__body">
                            <div class="modal-perfil__header">
                                <h2 class="modal-perfil__nombre">{{ perfilSeleccionado.nombre }}</h2>
                                <div class="modal-perfil__badges">
                                    <span v-if="perfilSeleccionado.verificado"
                                        class="modal-perfil__badge modal-perfil__badge--verified">
                                        <i class="pi pi-check-circle"></i> Verificado
                                    </span>
                                    <span class="modal-perfil__badge modal-perfil__badge--compat">
                                        <i class="pi pi-heart"></i> {{ perfilSeleccionado.compatibilidad || 0 }}%
                                    </span>
                                </div>
                            </div>

                            <p class="modal-perfil__ciudad">
                                <i class="pi pi-map-marker"></i> {{ perfilSeleccionado.ciudad }}
                                <span v-if="perfilSeleccionado.edad">• {{ perfilSeleccionado.edad }} años</span>
                            </p>

                            <p class="modal-perfil__desc">{{ perfilSeleccionado.descripcion }}</p>

                            <div v-if="perfilSeleccionado.intereses && perfilSeleccionado.intereses.length > 0"
                                class="modal-perfil__intereses">
                                <span class="modal-perfil__intereses-label">Intereses</span>
                                <div class="modal-perfil__intereses-grid">
                                    <span v-for="interes in perfilSeleccionado.intereses" :key="interes.label"
                                        class="modal-perfil__tag">
                                        <i class="pi" :class="interes.icon"></i> {{ interes.label }}
                                    </span>
                                </div>
                            </div>

                            <div class="modal-perfil__actions">
                                <button class="modal-perfil__btn modal-perfil__btn--primary"
                                    @click="irAMensajes(perfilSeleccionado.chat_id)" :disabled="enviandoMensaje">
                                    <i v-if="enviandoMensaje" class="pi pi-spin pi-spinner"></i>
                                    <i v-else class="pi pi-comment"></i>
                                    {{ enviandoMensaje ? 'Cargando...' : 'Enviar mensaje' }}
                                </button>
                                <button class="modal-perfil__btn modal-perfil__btn--secondary"
                                    @click="cerrarModalPerfil">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </template>

                    <div v-else class="modal-perfil__error">
                        <i class="pi pi-exclamation-circle"></i>
                        <span>No se pudo cargar el perfil</span>
                        <button class="modal-perfil__btn modal-perfil__btn--primary" @click="cerrarModalPerfil">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>

<script setup>
import { computed, reactive, ref, onMounted, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';

// ============================================================
// PROPS
// ============================================================
const props = defineProps({
    usuario: {
        type: Object,
        default: () => ({
            id: null,
            nombre: 'Usuario',
            apodo: 'usuario',
            avatar: '/images/shared/avatar-default.jpg',
            verificado: false,
            rol: 'usuario',
            estado: 'incompleto'
        })
    },
    sugerencias: {
        type: Array,
        default: () => []
    },
    matchesRecientes: {
        type: Array,
        default: () => []
    },
    interesesDisponibles: {
        type: Array,
        default: () => []
    },
    filtrosActivos: {
        type: Object,
        default: () => ({ distancia_max: null, intereses: [] })
    },
    likesRecibidos: {
        type: Array,
        default: () => []
    }
});

// ============================================================
// PUNTOS DE CONFIANZA
// ============================================================
const confianza = [
    { icon: 'pi-shield', titulo: 'Perfiles verificados para una experiencia más confiable.' },
    { icon: 'pi-lock', titulo: 'Tu ubicación exacta nunca se comparte.' },
];

// ============================================================
// FILTROS RÁPIDOS
// ============================================================
const filtros = reactive({
    soloVerificados: true,
    tipoPerfil: 'todos',
});

// ============================================================
// STACK DE PERFILES
// ============================================================
const indiceActual = ref(0);
const perfilesLista = computed(() => props.sugerencias || []);

const perfilActual = computed(() => perfilesLista.value[indiceActual.value] ?? null);
const perfilAnterior = computed(() => perfilesLista.value[indiceActual.value - 1] ?? null);
const perfilSiguiente = computed(() => perfilesLista.value[indiceActual.value + 1] ?? null);

function siguientePerfil() {
    if (indiceActual.value < perfilesLista.value.length - 1) indiceActual.value++;
}

// ============================================================
// ACCIONES
// ============================================================
const enviandoAccion = ref(false);
const procesandoLike = ref(false);

async function pasar() {
    const perfil = perfilActual.value;
    if (!perfil || enviandoAccion.value) return;

    enviandoAccion.value = true;

    try {
        await axios.post('/descubrir/pasar', { perfil_id: perfil.id });
        siguientePerfil();
    } catch (e) {
        console.error('Error al pasar:', e.response?.data || e.message);
        siguientePerfil();
    } finally {
        enviandoAccion.value = false;
    }
}

async function conectar() {
    const perfil = perfilActual.value;
    if (!perfil || enviandoAccion.value) return;

    enviandoAccion.value = true;

    try {
        const { data } = await axios.post('/descubrir/conectar', { perfil_id: perfil.id });

        if (data.match) {
            abrirModalMatch(data.perfil, data.chat_id);
        }

        siguientePerfil();
    } catch (e) {
        console.error('Error al conectar:', e.response?.data || e.message);
        siguientePerfil();
    } finally {
        enviandoAccion.value = false;
    }
}

async function destacar() {
    const perfil = perfilActual.value;
    if (!perfil || enviandoAccion.value) return;

    enviandoAccion.value = true;

    try {
        const { data } = await axios.post('/descubrir/destacar', { perfil_id: perfil.id });

        if (data.match) {
            abrirModalMatch(data.perfil, data.chat_id);
        }

        siguientePerfil();
    } catch (e) {
        console.error('Error al destacar:', e.response?.data || e.message);
        siguientePerfil();
    } finally {
        enviandoAccion.value = false;
    }
}

// ============================================================
// LIKES RECIBIDOS - ACCIONES
// ============================================================
async function aceptarLike(coincidenciaId) {
    if (procesandoLike.value) return;
    procesandoLike.value = true;

    try {
        const { data } = await axios.post('/descubrir/aceptar-like', { coincidencia_id: coincidenciaId });

        if (data.success) {
            const like = props.likesRecibidos.find(l => l.id === coincidenciaId);
            if (like) {
                abrirModalMatch({
                    id: like.usuario_id,
                    nombre: like.nombre,
                    avatar: like.imagen
                }, data.chat_id);
            }

            setTimeout(() => {
                router.reload();
            }, 1000);
        }
    } catch (e) {
        console.error('Error al aceptar like:', e.response?.data || e.message);
        alert('No se pudo aceptar el like. Intenta nuevamente.');
    } finally {
        procesandoLike.value = false;
    }
}

async function rechazarLike(coincidenciaId) {
    if (procesandoLike.value) return;
    procesandoLike.value = true;

    try {
        await axios.post('/descubrir/rechazar-like', { coincidencia_id: coincidenciaId });
        setTimeout(() => {
            router.reload();
        }, 500);
    } catch (e) {
        console.error('Error al rechazar like:', e.response?.data || e.message);
        alert('No se pudo rechazar el like. Intenta nuevamente.');
    } finally {
        procesandoLike.value = false;
    }
}

// ============================================================
// MODAL MATCH
// ============================================================
const modalMatch = reactive({
    visible: false,
    perfil: null,
    chatId: null,
    enviando: false,
    enviado: false,
    texto: '',
});

const mensajesFlash = [
    '¡Hey! 👋 Hola, hay que conocernos',
    '¡Qué buena onda el match! ¿Cómo va tu día?',
    'Me llamó la atención tu perfil, ¿platicamos?',
    '¡Hola! ¿Qué es lo que más te gusta hacer un fin de semana?',
];

function abrirModalMatch(perfil, chatId) {
    modalMatch.perfil = perfil;
    modalMatch.chatId = chatId;
    modalMatch.texto = '';
    modalMatch.enviado = false;
    modalMatch.visible = true;
}

function cerrarModalMatch() {
    modalMatch.visible = false;
}

function elegirMensajeFlash(texto) {
    modalMatch.texto = texto;
}

async function enviarMensajeFlash() {
    const texto = modalMatch.texto.trim();
    if (!texto || !modalMatch.chatId || modalMatch.enviando) return;

    modalMatch.enviando = true;
    try {
        await axios.post('/descubrir/mensaje-flash', {
            chat_id: modalMatch.chatId,
            texto,
        });
        modalMatch.enviado = true;
    } catch (e) {
        console.error('Error al enviar mensaje flash:', e.response?.data || e.message);
    } finally {
        modalMatch.enviando = false;
    }
}

function irAConversacion() {
    if (modalMatch.chatId) {
        router.visit(`/chats/${modalMatch.chatId}/mensajes`);
    }
    cerrarModalMatch();
}

function irAChat(chatId) {
    if (chatId) {
        router.visit(`/chats/${chatId}/mensajes`);
    } else {
        router.visit('/mensajes');
    }
}

// ============================================================
// MODAL PERFIL MATCH - MEJORADO CON FUNCIÓN DE MENSAJE
// ============================================================
const modalPerfilVisible = ref(false);
const perfilSeleccionado = ref(null);
const cargandoPerfil = ref(false);
const enviandoMensaje = ref(false);

async function abrirModalPerfil(match) {
    if (!match || !match.usuario_id) {
        console.error('Match inválido:', match);
        return;
    }

    console.log('Abriendo modal para match:', match);

    cargandoPerfil.value = true;
    modalPerfilVisible.value = true;
    perfilSeleccionado.value = null;

    try {
        const response = await axios.get('/descubrir/perfil', {
            params: {
                usuario_id: match.usuario_id,
                chat_id: match.chat_id || null
            }
        });

        console.log('Respuesta del servidor:', response.data);

        if (response.data.success && response.data.perfil) {
            const perfil = response.data.perfil;
            perfil.compatibilidad = match.compatibilidad || 0;
            perfilSeleccionado.value = perfil;
        } else {
            console.error('Error en la respuesta:', response.data.message || 'Perfil no encontrado');
            perfilSeleccionado.value = null;
        }
    } catch (error) {
        console.error('Error al cargar perfil:', error.response?.data || error.message);
        perfilSeleccionado.value = null;
    } finally {
        cargandoPerfil.value = false;
    }
}

function cerrarModalPerfil() {
    modalPerfilVisible.value = false;
    setTimeout(() => {
        perfilSeleccionado.value = null;
    }, 300);
}

function irAMensajes(chatId) {
    // Si tenemos chat_id, ir directamente a la conversación
    if (chatId) {
        router.visit(`/chats/${chatId}/mensajes`);
        cerrarModalPerfil();
        return;
    }

    // Si no tenemos chat_id, ir a mensajes generales
    router.visit('/mensajes');
    cerrarModalPerfil();
}

// ============================================================
// FILTROS AVANZADOS
// ============================================================
const panelFiltrosAbierto = ref(false);
const distanciaMax = ref(props.filtrosActivos?.distancia_max || 50);
const interesesSeleccionados = ref([...(props.filtrosActivos?.intereses || [])]);

const totalFiltrosActivos = computed(() => {
    let total = 0;
    if (distanciaMax.value && distanciaMax.value < 100) total++;
    if (interesesSeleccionados.value.length > 0) total++;
    return total;
});

function alternarInteresFiltro(label) {
    const idx = interesesSeleccionados.value.indexOf(label);
    if (idx === -1) interesesSeleccionados.value.push(label);
    else interesesSeleccionados.value.splice(idx, 1);
}

function aplicarFiltros() {
    const params = {};

    if (distanciaMax.value < 100) {
        params.distancia_max = distanciaMax.value;
    }

    if (interesesSeleccionados.value.length > 0) {
        params.intereses = interesesSeleccionados.value;
    }

    router.get('/descubrir', params, {
        preserveState: true,
        preserveScroll: true,
        only: ['sugerencias', 'filtrosActivos'],
        onSuccess: () => {
            indiceActual.value = 0;
            panelFiltrosAbierto.value = false;
        },
    });
}

function limpiarFiltros() {
    distanciaMax.value = 100;
    interesesSeleccionados.value = [];
    aplicarFiltros();
}

// ============================================================
// MATCHES RECIENTES
// ============================================================
const matches = computed(() => {
    if (!props.matchesRecientes || props.matchesRecientes.length === 0) {
        return [];
    }
    return props.matchesRecientes.slice(0, 8);
});

// ============================================================
// LIKES RECIBIDOS
// ============================================================
const likesRecibidos = computed(() => props.likesRecibidos || []);

// ============================================================
// FUNCIONES DE UTILIDAD
// ============================================================
function getAvatarUrl(avatar) {
    if (!avatar) return '/images/shared/avatar-default.jpg';
    if (avatar.startsWith('http://') || avatar.startsWith('https://')) return avatar;
    if (avatar.startsWith('/storage/') || avatar.startsWith('/images/')) return avatar;
    if (avatar.startsWith('perfil/')) return '/storage/' + avatar;
    return '/storage/' + avatar.replace(/^\/+/, '');
}

function handleAvatarError(event) {
    event.target.src = '/images/shared/avatar-default.jpg';
    event.target.onerror = null;
}

function timeAgo(fecha) {
    if (!fecha) return 'Recién';

    const ahora = new Date();
    const fechaDate = new Date(fecha);
    const diffMs = ahora - fechaDate;
    const diffMin = Math.floor(diffMs / 60000);
    const diffHoras = Math.floor(diffMs / 3600000);
    const diffDias = Math.floor(diffMs / 86400000);

    if (diffMin < 1) return 'Ahora';
    if (diffMin < 60) return `Hace ${diffMin}m`;
    if (diffHoras < 24) return `Hace ${diffHoras}h`;
    return `Hace ${diffDias}d`;
}

// ============================================================
// LIFECYCLE
// ============================================================
onMounted(() => {
    console.log('=== DESCUBRIR ===');
    console.log('Sugerencias:', props.sugerencias);
    console.log('Likes recibidos:', props.likesRecibidos);
    console.log('Matches recientes:', props.matchesRecientes);
    console.log('Intereses disponibles:', props.interesesDisponibles);
});
</script>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA
   ========================================================================= */
.descubrir-page {
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
    --shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    --shadow-hover: 0 12px 40px rgba(0, 0, 0, 0.1);
    --shadow-card: 0 4px 16px rgba(0, 0, 0, 0.08);

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

.descubrir-page * {
    box-sizing: border-box;
}

.descubrir-page img {
    max-width: 100%;
    display: block;
}

/* =========================================================================
   PAGE HEADING
   ========================================================================= */
.page-heading {
    max-width: 1400px;
    margin: 1.75rem auto 0;
    padding: 0 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.25rem;
}

.page-heading h1 {
    font-family: var(--font-serif);
    font-size: 1.7rem;
    font-weight: 400;
    margin: 0;
    line-height: 1.25;
}

.page-heading h1 span {
    display: block;
    color: var(--brand);
    font-weight: 700;
    font-style: italic;
}

.page-heading__trust {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.trust-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.8rem;
    color: var(--ink-soft);
    max-width: 220px;
}

.trust-item__icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* =========================================================================
   FILTERS BAR
   ========================================================================= */
.filters-bar {
    max-width: 1400px;
    margin: 1.5rem auto 0;
    padding: 0.75rem 1.25rem;
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
    box-shadow: var(--shadow);
    position: relative;
}

.filter-toggle {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--ink);
    cursor: pointer;
}

.toggle-switch {
    position: relative;
    width: 38px;
    height: 21px;
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
    background: #e3e3e7;
    border-radius: var(--radius-full);
    transition: 0.2s;
}

.toggle-switch input:checked+.toggle-slider {
    background: var(--brand);
}

.toggle-slider::before {
    content: '';
    position: absolute;
    width: 15px;
    height: 15px;
    left: 3px;
    top: 3px;
    background: var(--white);
    border-radius: 50%;
    transition: 0.2s;
}

.toggle-switch input:checked+.toggle-slider::before {
    transform: translateX(17px);
}

.filter-pill {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    border: 1.5px solid var(--line);
    border-radius: var(--radius-full);
    padding: 0.4rem 0.9rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink-soft);
    background: var(--white);
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-pill:hover {
    border-color: var(--brand-soft);
}

.filter-pill.selected {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
}

.filter-btn {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border: 1.5px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.45rem 0.9rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink);
    background: var(--white);
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.filter-btn:hover {
    border-color: var(--brand-soft);
}

.filter-badge {
    background: var(--brand);
    color: var(--white);
    font-size: 0.6rem;
    font-weight: 700;
    padding: 0.05rem 0.5rem;
    border-radius: var(--radius-full);
}

.filters-popover {
    position: absolute;
    top: calc(100% + 0.6rem);
    right: 0;
    z-index: 30;
    width: 320px;
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: 16px;
    box-shadow: var(--shadow-hover);
    padding: 1.25rem;
}

.filters-popover__section {
    margin-bottom: 1.1rem;
}

.filters-popover__label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ink-soft);
    margin-bottom: 0.5rem;
}

.filters-popover__label strong {
    color: var(--brand);
    font-weight: 700;
}

.filters-popover__range {
    width: 100%;
    accent-color: var(--brand);
}

.filters-popover__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.filter-pill--small {
    padding: 0.4rem 0.7rem;
    font-size: 0.72rem;
}

.filters-popover__actions {
    display: flex;
    gap: 0.6rem;
    margin-top: 0.5rem;
}

.filters-popover__clear {
    flex: 1;
    background: none;
    border: 1.5px solid var(--line);
    border-radius: 10px;
    padding: 0.55rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ink-soft);
    cursor: pointer;
}

.filters-popover__apply {
    flex: 2;
    background: var(--brand);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 0.55rem;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
}

.filters-popover__apply:hover {
    background: var(--brand-dark);
}

/* =========================================================================
   SWIPE AREA
   ========================================================================= */
.swipe-area {
    max-width: 1400px;
    margin: 2.5rem auto 0;
    padding: 0 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.swipe-stack {
    position: relative;
    width: 100%;
    max-width: 500px;
    height: 620px;
    margin-bottom: 2rem;
}

.swipe-card {
    position: absolute;
    border-radius: 20px;
    overflow: hidden;
}

.swipe-card--main {
    inset: 0;
    z-index: 3;
    background: #111;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.18);
}

.swipe-card--main img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 1;
}

.swipe-card__gradient {
    position: absolute;
    inset: 0;
    z-index: 2;
    background: linear-gradient(0deg, rgba(0, 0, 0, 0.92) 25%, rgba(0, 0, 0, 0.05) 65%);
}

.swipe-card--side {
    top: 30px;
    bottom: 30px;
    width: 82%;
    z-index: 1;
    opacity: 0.85;
    filter: brightness(0.55);
}

.swipe-card--side img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.swipe-card--left {
    left: -14%;
}

.swipe-card--right {
    right: -14%;
}

.side-badge {
    position: absolute;
    top: 14px;
    left: 14px;
    background: rgba(255, 255, 255, 0.2);
    color: var(--white);
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: var(--radius-full);
    backdrop-filter: blur(2px);
}

.side-badge--online {
    left: auto;
    right: 14px;
    background: rgba(0, 0, 0, 0.4);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.side-badge--online i {
    color: #4ade80;
    font-size: 0.5rem;
}

.verified-badge {
    position: absolute;
    top: 16px;
    left: 16px;
    z-index: 3;
    background: var(--white);
    color: #1c7a3c;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.25rem 0.7rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.online-badge {
    position: absolute;
    top: 16px;
    right: 16px;
    z-index: 3;
    background: rgba(0, 0, 0, 0.5);
    color: var(--white);
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.25rem 0.7rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.online-badge i {
    color: #4ade80;
    font-size: 0.5rem;
}

.swipe-card__content {
    position: absolute;
    z-index: 3;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 1.5rem;
    color: var(--white);
}

.swipe-card__content h2 {
    font-family: var(--font-serif);
    font-size: 1.5rem;
    margin: 0 0 0.4rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.swipe-card__content h2 i {
    color: #4ade80;
    font-size: 1.05rem;
}

.swipe-card__location {
    font-size: 0.85rem;
    color: #e0e0e2;
    margin: 0 0 0.6rem;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    flex-wrap: wrap;
}

.swipe-card__desc {
    font-size: 0.85rem;
    color: #e0e0e2;
    line-height: 1.5;
    margin: 0 0 0.9rem;
}

.swipe-card__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.tag {
    background: rgba(255, 255, 255, 0.14);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: var(--radius-full);
    padding: 0.3rem 0.7rem;
    font-size: 0.72rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.swipe-card__footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 1rem 0 0.9rem;
    font-size: 0.85rem;
}

.swipe-card__distance {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    color: #e0e0e2;
}

.swipe-card__compat {
    color: #e0e0e2;
}

.swipe-card__compat strong {
    color: var(--brand);
    background: var(--white);
    padding: 0.1rem 0.5rem;
    border-radius: 6px;
    margin-left: 0.3rem;
}

.swipe-card__dots {
    display: flex;
    justify-content: center;
    gap: 0.4rem;
}

.dot {
    width: 24px;
    height: 4px;
    border-radius: 4px;
    background: rgba(255, 255, 255, 0.3);
}

.dot.active {
    background: var(--brand);
}

/* =========================================================================
   EMPTY STATE
   ========================================================================= */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--white);
    border-radius: var(--radius-md);
    border: 1px solid var(--line);
    width: 100%;
    max-width: 500px;
}

.empty-state i {
    font-size: 3rem;
    color: var(--muted-light);
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.1rem;
    margin: 0 0 0.5rem;
    color: var(--ink);
}

.empty-state p {
    color: var(--muted);
    margin: 0;
}

/* =========================================================================
   SWIPE ACTIONS
   ========================================================================= */
.swipe-actions {
    display: flex;
    align-items: center;
    gap: 1.75rem;
    margin-bottom: 1rem;
}

.swipe-btn {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    border: none;
    background: var(--white);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.swipe-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.16);
}

.swipe-btn--pass {
    color: var(--brand);
    width: 60px;
    height: 60px;
}

.swipe-btn--pass:hover {
    background: #fee8ea;
}

.swipe-btn--star {
    color: #f2a33c;
    width: 52px;
    height: 52px;
    font-size: 1.15rem;
}

.swipe-btn--star:hover {
    background: #fef6e8;
}

.swipe-btn--like {
    color: var(--brand);
    width: 60px;
    height: 60px;
}

.swipe-btn--like:hover {
    background: #fee8ea;
}

/* =========================================================================
   SWIPE HINTS
   ========================================================================= */
.swipe-hints {
    display: flex;
    justify-content: center;
    gap: 3.5rem;
    text-align: center;
    font-size: 0.75rem;
    color: var(--muted);
}

.swipe-hint {
    max-width: 130px;
}

.swipe-hint i {
    display: block;
    margin-bottom: 0.2rem;
}

.swipe-hint--center {
    color: #f2a33c;
    font-weight: 600;
}

.swipe-hint--center i {
    color: #f2a33c;
}

/* =========================================================================
   BOTTOM GRID - MATCHES + LIKES SIDE BY SIDE
   ========================================================================= */
.bottom-grid {
    max-width: 1400px;
    margin: 2.5rem auto 0;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1024px) {
    .bottom-grid {
        grid-template-columns: 1fr;
    }
}

/* =========================================================================
   MATCHES CARD
   ========================================================================= */
.matches-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.25rem;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}

.matches-card:hover {
    box-shadow: var(--shadow-hover);
}

.matches-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.8rem;
}

.matches-card__header-left {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.matches-card__icon-wrapper {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #FEE2E2, #FECACA);
    color: #EF4444;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.matches-card__header h3 {
    font-size: 0.9rem;
    font-weight: 700;
    margin: 0;
}

.matches-card__count {
    font-size: 0.65rem;
    color: var(--muted);
    font-weight: 400;
    margin-left: 0.3rem;
}

/* =========================================================================
   MATCHES GRID - VERSIÓN COMPACTA
   ========================================================================= */
.matches-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.4rem;
}

.mini-match {
    position: relative;
    border-radius: var(--radius-sm);
    overflow: hidden;
    aspect-ratio: 1/1;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.mini-match:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    z-index: 5;
}

.mini-match img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.mini-match:hover img {
    transform: scale(1.1);
}

.mini-match__verified {
    position: absolute;
    top: 2px;
    left: 2px;
    background: rgba(255, 255, 255, 0.92);
    color: #1c7a3c;
    font-size: 0.45rem;
    font-weight: 700;
    padding: 0.05rem 0.3rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.1rem;
    backdrop-filter: blur(4px);
}

.mini-match__verified i {
    font-size: 0.4rem;
}

.mini-match__info {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 0.3rem 0.3rem 0.2rem;
    background: linear-gradient(0deg, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0) 100%);
    color: var(--white);
}

.mini-match__info strong {
    display: block;
    font-size: 0.55rem;
    font-weight: 700;
    line-height: 1.1;
    text-align: center;
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);
}

/* =========================================================================
   EMPTY MATCHES
   ========================================================================= */
.empty-matches,
.empty-likes {
    text-align: center;
    padding: 1.5rem 0.5rem;
    color: var(--muted);
}

.empty-matches__icon,
.empty-likes__icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--surface);
    color: var(--muted-light);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 0.5rem;
}

.empty-matches span,
.empty-likes span {
    font-size: 0.85rem;
    font-weight: 600;
    display: block;
}

.empty-matches p,
.empty-likes p {
    font-size: 0.7rem;
    margin: 0.2rem 0 0;
    color: var(--muted-light);
}

/* =========================================================================
   LIKES CARD
   ========================================================================= */
.likes-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.25rem;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
    border-left: 3px solid #EF4444;
}

.likes-card:hover {
    box-shadow: var(--shadow-hover);
}

.likes-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.likes-card__header-left {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.likes-card__icon-wrapper {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #FEE2E2, #FECACA);
    color: #EF4444;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
}

.likes-card__header h3 {
    font-size: 0.9rem;
    font-weight: 700;
    margin: 0;
}

.likes-card__count {
    font-size: 0.65rem;
    color: var(--muted);
    font-weight: 400;
    margin-left: 0.3rem;
}

.likes-card__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    background: var(--brand-soft);
    color: var(--brand);
    padding: 0.15rem 0.6rem;
    border-radius: var(--radius-full);
    font-size: 0.6rem;
    font-weight: 600;
}

/* =========================================================================
   LIKES GRID
   ========================================================================= */
.likes-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.5rem;
}

.like-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.5rem 0.6rem;
    background: var(--surface);
    border-radius: var(--radius-sm);
    border: 1px solid var(--line);
    transition: all 0.3s ease;
}

.like-item:hover {
    background: var(--white);
    border-color: var(--brand-soft);
    box-shadow: var(--shadow-hover);
}

.like-item__avatar-wrapper {
    position: relative;
    flex-shrink: 0;
}

.like-item__avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--line);
}

.like-item__verified {
    position: absolute;
    bottom: -2px;
    right: -2px;
    background: var(--brand);
    color: var(--white);
    border-radius: 50%;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--white);
    font-size: 0.4rem;
}

.like-item__compat-badge {
    position: absolute;
    bottom: -2px;
    left: -2px;
    background: linear-gradient(135deg, #6366F1, #8B5CF6);
    color: var(--white);
    border-radius: var(--radius-full);
    padding: 0.05rem 0.3rem;
    font-size: 0.45rem;
    font-weight: 700;
    border: 2px solid var(--white);
}

.like-item__info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.like-item__nombre {
    font-size: 0.8rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    flex-wrap: wrap;
}

.like-item__apodo {
    font-size: 0.6rem;
    color: var(--muted);
    font-weight: 400;
}

.like-item__detalles {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    font-size: 0.55rem;
    color: var(--muted);
}

.like-item__detalles i {
    font-size: 0.5rem;
}

.like-item__edad,
.like-item__tipo,
.like-item__fecha {
    display: inline-flex;
    align-items: center;
    gap: 0.15rem;
}

.like-item__compatibilidad {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 0.1rem;
    max-width: 180px;
}

.compat-bar {
    flex: 1;
    height: 3px;
    background: var(--line);
    border-radius: 2px;
    overflow: hidden;
}

.compat-bar__fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.8s ease;
}

.compat-alta .compat-bar__fill {
    background: #10B981;
}

.compat-alta .compat-text {
    color: #10B981;
}

.compat-media .compat-bar__fill {
    background: #F59E0B;
}

.compat-media .compat-text {
    color: #F59E0B;
}

.compat-baja .compat-bar__fill {
    background: #EF4444;
}

.compat-baja .compat-text {
    color: #EF4444;
}

.compat-text {
    font-size: 0.6rem;
    font-weight: 700;
    white-space: nowrap;
    min-width: 32px;
}

.like-item__acciones {
    display: flex;
    gap: 0.2rem;
    flex-shrink: 0;
}

.like-btn {
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 50%;
    font-size: 0.6rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.like-btn--aceptar {
    background: #10B981;
    color: #fff;
}

.like-btn--aceptar:hover {
    background: #059669;
    transform: scale(1.1);
}

.like-btn--rechazar {
    background: #EF4444;
    color: #fff;
}

.like-btn--rechazar:hover {
    background: #DC2626;
    transform: scale(1.1);
}

/* =========================================================================
   MODAL MATCH - REDISEÑADO
   ========================================================================= */
.match-modal-fade-enter-active,
.match-modal-fade-leave-active {
    transition: opacity 0.35s ease;
}

.match-modal-fade-enter-from,
.match-modal-fade-leave-to {
    opacity: 0;
}

.match-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 200;
    background: rgba(23, 20, 18, 0.7);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.match-modal {
    position: relative;
    width: 100%;
    max-width: 440px;
    background: linear-gradient(165deg, #FFFFFF 0%, #FDF2F4 100%);
    border-radius: 28px;
    padding: 0 0 2rem;
    box-shadow: 0 32px 64px rgba(200, 30, 58, 0.18), 0 8px 24px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    animation: match-modal-in 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes match-modal-in {
    0% {
        opacity: 0;
        transform: scale(0.92) translateY(20px);
    }

    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Decoración de fondo */
.match-modal__bg-decoration {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
    border-radius: 28px;
}

.match-modal__bg-circle {
    position: absolute;
    border-radius: 50%;
    opacity: 0.4;
}

.match-modal__bg-circle--1 {
    width: 200px;
    height: 200px;
    top: -60px;
    right: -60px;
    background: radial-gradient(circle, #FCA5A5, transparent 70%);
    animation: float-circle 6s ease-in-out infinite;
}

.match-modal__bg-circle--2 {
    width: 140px;
    height: 140px;
    bottom: -40px;
    left: -40px;
    background: radial-gradient(circle, #FCD34D, transparent 70%);
    animation: float-circle 8s ease-in-out infinite reverse;
}

.match-modal__bg-circle--3 {
    width: 80px;
    height: 80px;
    top: 40%;
    right: -20px;
    background: radial-gradient(circle, #F472B6, transparent 70%);
    animation: float-circle 5s ease-in-out infinite 1s;
}

@keyframes float-circle {

    0%,
    100% {
        transform: translate(0, 0) scale(1);
    }

    50% {
        transform: translate(10px, -15px) scale(1.05);
    }
}

/* Close */
.match-modal__close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 10;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.9);
    color: var(--ink-soft);
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.match-modal__close:hover {
    background: var(--white);
    transform: rotate(90deg);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

/* Header */
.match-modal__header {
    position: relative;
    height: 60px;
    background: linear-gradient(135deg, var(--brand), var(--brand-dark));
    overflow: hidden;
}

.match-modal__header-glow {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at 30% 50%, rgba(255, 255, 255, 0.15) 0%, transparent 60%);
}

.match-modal__header-particles {
    position: absolute;
    inset: 0;
    overflow: hidden;
}

.particle {
    position: absolute;
    color: rgba(255, 255, 255, 0.25);
    font-size: 1rem;
    animation: particle-float 4s ease-in-out infinite;
}

.particle-1 {
    top: 10%;
    left: 5%;
    animation-delay: 0s;
}

.particle-2 {
    top: 30%;
    right: 10%;
    animation-delay: 0.8s;
    font-size: 1.2rem;
}

.particle-3 {
    bottom: 20%;
    left: 15%;
    animation-delay: 1.6s;
}

.particle-4 {
    top: 5%;
    right: 25%;
    animation-delay: 2.2s;
    font-size: 0.8rem;
}

.particle-5 {
    bottom: 5%;
    left: 40%;
    animation-delay: 0.4s;
}

.particle-6 {
    top: 15%;
    left: 60%;
    animation-delay: 1.2s;
    font-size: 0.7rem;
}

@keyframes particle-float {

    0%,
    100% {
        transform: translate(0, 0) rotate(0deg);
    }

    50% {
        transform: translate(10px, -15px) rotate(45deg);
    }
}

/* Avatars */
.match-modal__avatars {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: -1.75rem;
    position: relative;
    z-index: 2;
    padding: 0 1.5rem;
}

.match-modal__avatar-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
}

.match-modal__avatar {
    width: 76px;
    height: 76px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--white);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.match-modal__avatar--mine {
    margin-right: -0.75rem;
    border-color: #E2E8F0;
}

.match-modal__avatar--theirs {
    margin-left: -0.75rem;
    border-color: var(--brand-soft);
}

.match-modal__avatar-label {
    font-size: 0.6rem;
    font-weight: 600;
    color: var(--muted);
    letter-spacing: 0.02em;
}

/* Heart */
.match-modal__heart-container {
    position: relative;
    z-index: 3;
    padding: 0 0.25rem;
}

.match-modal__heart-pulse {
    position: relative;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.match-modal__heart-pulse::before {
    content: '';
    position: absolute;
    inset: -8px;
    border-radius: 50%;
    background: rgba(200, 30, 58, 0.15);
    animation: heart-pulse 1.8s ease-out infinite;
}

.match-modal__heart-pulse::after {
    content: '';
    position: absolute;
    inset: -18px;
    border-radius: 50%;
    background: rgba(200, 30, 58, 0.08);
    animation: heart-pulse 1.8s ease-out infinite 0.3s;
}

@keyframes heart-pulse {
    0% {
        transform: scale(0.8);
        opacity: 1;
    }

    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

.match-modal__heart-icon {
    position: relative;
    z-index: 2;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--brand), #E8506A);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    box-shadow: 0 4px 20px rgba(200, 30, 58, 0.4);
    animation: heart-pop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s both;
}

@keyframes heart-pop {
    0% {
        transform: scale(0);
    }

    60% {
        transform: scale(1.2);
    }

    100% {
        transform: scale(1);
    }
}

.match-modal__heart-icon i {
    animation: heart-beat 1.2s ease-in-out infinite;
}

@keyframes heart-beat {

    0%,
    100% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.1);
    }
}

/* Title */
.match-modal__title-wrapper {
    text-align: center;
    padding: 1rem 1.5rem 0.25rem;
    position: relative;
    z-index: 2;
}

.match-modal__title-badge {
    display: inline-block;
    background: var(--brand-soft);
    color: var(--brand);
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 0.15rem 0.8rem;
    border-radius: var(--radius-full);
    margin-bottom: 0.5rem;
}

.match-modal__title {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.25rem;
    color: var(--ink);
}

.match-modal__subtitle {
    font-size: 0.85rem;
    color: var(--ink-soft);
    margin: 0;
    line-height: 1.5;
}

.match-modal__subtitle strong {
    color: var(--brand);
}

/* Quick Messages */
.match-modal__quick-messages {
    padding: 0.75rem 1.5rem 0.5rem;
    position: relative;
    z-index: 2;
}

.match-modal__quick-label {
    font-size: 0.65rem;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin: 0 0 0.4rem;
}

.match-modal__chips {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.match-modal__chip {
    text-align: left;
    background: var(--white);
    border: 1.5px solid var(--line);
    border-radius: 10px;
    padding: 0.45rem 0.8rem;
    font-size: 0.78rem;
    color: var(--ink);
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: var(--font-sans);
}

.match-modal__chip:hover {
    border-color: var(--brand);
    background: var(--brand-soft);
    transform: translateX(4px);
}

.match-modal__chip:active {
    transform: scale(0.98);
}

/* Composer */
.match-modal__composer {
    padding: 0.5rem 1.5rem 0.25rem;
    position: relative;
    z-index: 2;
}

.match-modal__composer-wrapper {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    background: var(--white);
    border: 1.5px solid var(--line);
    border-radius: 999px;
    padding: 0.15rem 0.15rem 0.15rem 1rem;
    transition: all 0.3s ease;
}

.match-modal__composer-wrapper:focus-within {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.1);
}

.match-modal__input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 0.82rem;
    color: var(--ink);
    font-family: var(--font-sans);
    background: transparent;
    min-height: 38px;
}

.match-modal__input::placeholder {
    color: var(--muted-light);
}

.match-modal__send-btn {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: none;
    background: var(--brand);
    color: #fff;
    cursor: pointer;
    font-size: 0.85rem;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.match-modal__send-btn:hover:not(:disabled) {
    background: var(--brand-dark);
    transform: scale(1.05);
}

.match-modal__send-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* Skip */
.match-modal__skip {
    display: block;
    width: 100%;
    background: none;
    border: none;
    color: var(--muted);
    font-size: 0.78rem;
    font-weight: 500;
    cursor: pointer;
    padding: 0.6rem 0.5rem 0.3rem;
    font-family: var(--font-sans);
    transition: color 0.3s ease;
}

.match-modal__skip:hover {
    color: var(--ink);
}

/* Sent state */
.match-modal__sent {
    text-align: center;
    padding: 0.5rem 1.5rem 0;
    position: relative;
    z-index: 2;
}

.match-modal__sent-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10B981, #059669);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    margin: 0 auto 0.5rem;
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
    animation: sent-pop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes sent-pop {
    0% {
        transform: scale(0);
    }

    60% {
        transform: scale(1.15);
    }

    100% {
        transform: scale(1);
    }
}

.match-modal__sent-title {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.2rem;
    font-weight: 700;
    margin: 0 0 0.15rem;
    color: var(--ink);
}

.match-modal__sent-text {
    font-size: 0.85rem;
    color: var(--ink-soft);
    margin: 0 0 1rem;
}

.match-modal__cta {
    width: 100%;
    background: linear-gradient(135deg, var(--brand), var(--brand-dark));
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 0.7rem;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-family: var(--font-sans);
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(200, 30, 58, 0.3);
}

.match-modal__cta:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(200, 30, 58, 0.4);
}

.match-modal__cta:active {
    transform: scale(0.98);
}

/* =========================================================================
   MODAL PERFIL - MEJORADO
   ========================================================================= */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 300;
    background: rgba(23, 20, 18, 0.7);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.modal-perfil {
    background: var(--white);
    border-radius: 24px;
    max-width: 420px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    animation: modal-in 0.3s ease;
    box-shadow: 0 32px 64px rgba(0, 0, 0, 0.25);
}

.modal-perfil::-webkit-scrollbar {
    width: 4px;
}

.modal-perfil::-webkit-scrollbar-track {
    background: transparent;
}

.modal-perfil::-webkit-scrollbar-thumb {
    background: var(--line);
    border-radius: 4px;
}

@keyframes modal-in {
    0% {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
    }

    100% {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.modal-perfil__close {
    position: absolute;
    top: 0.8rem;
    right: 0.8rem;
    z-index: 10;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.9);
    color: var(--ink-soft);
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.modal-perfil__close:hover {
    background: var(--white);
    transform: rotate(90deg);
}

/* Loading */
.modal-perfil__loading {
    padding: 3rem 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    color: var(--muted);
}

.modal-perfil__loading-spinner {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--brand-soft);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-perfil__loading-spinner i {
    font-size: 1.5rem;
    color: var(--brand);
}

/* Cover */
.modal-perfil__cover {
    position: relative;
    height: 120px;
    background: linear-gradient(135deg, var(--brand), var(--brand-dark));
    border-radius: 24px 24px 0 0;
    overflow: hidden;
}

.modal-perfil__cover-gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(200, 30, 58, 0.3) 0%, rgba(200, 30, 58, 0.8) 100%);
}

.modal-perfil__avatar-wrapper {
    position: absolute;
    bottom: -40px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 2;
}

.modal-perfil__avatar {
    position: relative;
    width: 90px;
    height: 90px;
    border-radius: 50%;
    border: 4px solid var(--white);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

.modal-perfil__avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.modal-perfil__verified {
    position: absolute;
    bottom: 2px;
    right: 2px;
    background: var(--brand);
    color: var(--white);
    border-radius: 50%;
    width: 26px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--white);
    font-size: 0.7rem;
}

/* Body */
.modal-perfil__body {
    padding: 2.5rem 1.5rem 1.5rem;
    text-align: center;
}

.modal-perfil__header {
    margin-bottom: 0.5rem;
}

.modal-perfil__nombre {
    font-family: var(--font-serif);
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.3rem;
    color: var(--ink);
}

.modal-perfil__badges {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.modal-perfil__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.65rem;
    font-weight: 600;
    padding: 0.15rem 0.6rem;
    border-radius: var(--radius-full);
}

.modal-perfil__badge--verified {
    background: #dcfce7;
    color: #16a34a;
}

.modal-perfil__badge--compat {
    background: var(--brand-soft);
    color: var(--brand);
}

.modal-perfil__ciudad {
    font-size: 0.8rem;
    color: var(--muted);
    margin: 0 0 0.8rem;
}

.modal-perfil__ciudad i {
    margin-right: 0.2rem;
}

.modal-perfil__desc {
    font-size: 0.85rem;
    color: var(--ink-soft);
    line-height: 1.6;
    margin: 0 0 1rem;
    text-align: left;
    padding: 0 0.5rem;
}

.modal-perfil__intereses {
    margin-bottom: 1.5rem;
    text-align: left;
}

.modal-perfil__intereses-label {
    display: block;
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
    padding: 0 0.5rem;
}

.modal-perfil__intereses-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    padding: 0 0.5rem;
}

.modal-perfil__tag {
    background: var(--surface);
    color: var(--ink-soft);
    border: 1px solid var(--line);
    border-radius: var(--radius-full);
    padding: 0.25rem 0.7rem;
    font-size: 0.7rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    transition: all 0.2s ease;
}

.modal-perfil__tag:hover {
    border-color: var(--brand-soft);
    background: var(--brand-soft);
    color: var(--brand);
}

.modal-perfil__tag i {
    font-size: 0.6rem;
}

.modal-perfil__actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--line);
}

.modal-perfil__btn {
    width: 100%;
    padding: 0.7rem;
    border: none;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    font-family: var(--font-sans);
}

.modal-perfil__btn--primary {
    background: linear-gradient(135deg, var(--brand), var(--brand-dark));
    color: var(--white);
    box-shadow: 0 4px 16px rgba(200, 30, 58, 0.3);
}

.modal-perfil__btn--primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(200, 30, 58, 0.4);
}

.modal-perfil__btn--primary:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.modal-perfil__btn--secondary {
    background: var(--surface);
    color: var(--ink-soft);
}

.modal-perfil__btn--secondary:hover {
    background: var(--line);
    color: var(--ink);
}

.modal-perfil__btn:active {
    transform: scale(0.98);
}

/* Error */
.modal-perfil__error {
    padding: 3rem 2rem;
    text-align: center;
    color: var(--muted);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.8rem;
}

.modal-perfil__error i {
    font-size: 2.5rem;
    color: var(--brand);
}

.modal-perfil__error .modal-perfil__btn {
    max-width: 200px;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
    .bottom-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .page-heading {
        flex-direction: column;
        align-items: flex-start;
        padding: 0 1rem;
    }

    .page-heading__trust {
        gap: 1rem;
    }

    .filters-bar {
        padding: 0.75rem 1rem;
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }

    .filter-toggle {
        justify-content: space-between;
    }

    .swipe-area {
        padding: 0 1rem;
    }

    .swipe-stack {
        height: 480px;
    }

    .swipe-card--side {
        display: none;
    }

    .swipe-actions {
        gap: 1rem;
    }

    .swipe-btn {
        width: 54px;
        height: 54px;
        font-size: 1.1rem;
    }

    .swipe-btn--pass,
    .swipe-btn--like {
        width: 50px;
        height: 50px;
    }

    .swipe-btn--star {
        width: 44px;
        height: 44px;
        font-size: 1rem;
    }

    .swipe-hints {
        flex-direction: column;
        gap: 0.5rem;
        align-items: center;
    }

    .bottom-grid {
        padding: 0 1rem;
    }

    .matches-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 0.3rem;
    }

    .likes-grid {
        grid-template-columns: 1fr;
    }

    .like-item {
        flex-wrap: wrap;
    }

    .like-item__info {
        min-width: 100%;
    }

    .like-item__compatibilidad {
        max-width: 100%;
    }

    .like-item__acciones {
        width: 100%;
        justify-content: flex-end;
    }

    .likes-card__header {
        flex-direction: column;
        align-items: stretch;
        gap: 0.3rem;
    }

    .likes-card__badge {
        align-self: flex-start;
    }

    .mini-match__info strong {
        font-size: 0.5rem;
    }

    /* Modal responsive */
    .match-modal {
        max-width: 100%;
        border-radius: 20px;
        margin: 0 0.5rem;
        padding: 0 0 1.5rem;
    }

    .match-modal__avatar {
        width: 60px;
        height: 60px;
    }

    .match-modal__heart-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .match-modal__heart-pulse::before {
        inset: -4px;
    }

    .match-modal__heart-pulse::after {
        inset: -12px;
    }

    .match-modal__title {
        font-size: 1.25rem;
    }

    .match-modal__chips {
        gap: 0.2rem;
    }

    .match-modal__chip {
        font-size: 0.72rem;
        padding: 0.35rem 0.7rem;
    }

    .match-modal__composer-wrapper {
        padding-left: 0.75rem;
    }

    .match-modal__input {
        font-size: 0.78rem;
        min-height: 34px;
    }

    .match-modal__send-btn {
        width: 34px;
        height: 34px;
        font-size: 0.75rem;
    }

    /* Modal perfil responsive */
    .modal-perfil {
        max-width: 100%;
        border-radius: 20px;
        margin: 0 0.5rem;
        max-height: 95vh;
    }

    .modal-perfil__cover {
        height: 100px;
    }

    .modal-perfil__avatar {
        width: 75px;
        height: 75px;
    }

    .modal-perfil__avatar-wrapper {
        bottom: -35px;
    }

    .modal-perfil__body {
        padding: 2rem 1rem 1rem;
    }

    .modal-perfil__nombre {
        font-size: 1.3rem;
    }
}

@media (max-width: 480px) {
    .swipe-card__content {
        padding: 1rem;
    }

    .swipe-card__content h2 {
        font-size: 1.2rem;
    }

    .swipe-card__desc {
        font-size: 0.78rem;
    }

    .swipe-card__footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.3rem;
    }

    .swipe-hints {
        font-size: 0.7rem;
    }

    .matches-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 0.2rem;
    }

    .mini-match__info strong {
        font-size: 0.4rem;
    }

    .mini-match__verified {
        font-size: 0.35rem;
        padding: 0.05rem 0.2rem;
    }

    .mini-match__verified i {
        font-size: 0.3rem;
    }

    .like-item__avatar {
        width: 40px;
        height: 40px;
    }

    .like-item__detalles {
        font-size: 0.5rem;
        gap: 0.3rem;
    }

    /* Match modal responsive */
    .match-modal {
        border-radius: 16px;
    }

    .match-modal__avatar {
        width: 50px;
        height: 50px;
        border-width: 3px;
    }

    .match-modal__heart-icon {
        width: 36px;
        height: 36px;
        font-size: 0.9rem;
    }

    .match-modal__title {
        font-size: 1.1rem;
    }

    .match-modal__subtitle {
        font-size: 0.78rem;
    }

    .match-modal__chip {
        font-size: 0.68rem;
        padding: 0.3rem 0.6rem;
    }

    .match-modal__composer-wrapper {
        padding-left: 0.6rem;
    }

    .match-modal__input {
        font-size: 0.72rem;
        min-height: 32px;
    }

    .match-modal__send-btn {
        width: 32px;
        height: 32px;
        font-size: 0.7rem;
    }

    /* Modal perfil responsive */
    .modal-perfil {
        border-radius: 16px;
        margin: 0 0.25rem;
    }

    .modal-perfil__cover {
        height: 80px;
    }

    .modal-perfil__avatar {
        width: 65px;
        height: 65px;
        border-width: 3px;
    }

    .modal-perfil__avatar-wrapper {
        bottom: -30px;
    }

    .modal-perfil__verified {
        width: 22px;
        height: 22px;
        font-size: 0.6rem;
    }

    .modal-perfil__body {
        padding: 1.5rem 0.75rem 0.75rem;
    }

    .modal-perfil__nombre {
        font-size: 1.1rem;
    }

    .modal-perfil__desc {
        font-size: 0.78rem;
    }

    .modal-perfil__tag {
        font-size: 0.6rem;
        padding: 0.15rem 0.5rem;
    }

    .modal-perfil__btn {
        font-size: 0.78rem;
        padding: 0.6rem;
    }
}
</style>