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
                                Bienvenido a la comunidad de <strong>creadores</strong>
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
                    <div class="feed-header">
                        <h2>Publicaciones</h2>
                        <span class="feed-header__count">{{ publicacionesFiltradas.length }} publicaciones</span>
                    </div>

                    <!-- Publicaciones -->
                    <div v-if="!publicacionesFiltradas || publicacionesFiltradas.length === 0" class="empty-state">
                        <i class="pi pi-inbox"></i>
                        <h3>
                            <template v-if="esCreador">No tienes publicaciones aún</template>
                            <template v-else>No hay publicaciones aún</template>
                        </h3>
                        <p>
                            <template v-if="esCreador">Comienza a compartir contenido con tu comunidad.</template>
                            <template v-else>Pronto habrá contenido exclusivo para ti.</template>
                        </p>
                        <button v-if="esCreador" class="btn btn--primary" @click="irANuevoContenido">
                            <i class="pi pi-plus"></i> Crear nuevo contenido
                        </button>
                    </div>

                    <article v-for="post in publicacionesFiltradas" :key="post.id || post.titulo || Math.random()"
                        class="post-card">
                        <div class="post-card__header">
                            <AvatarCustom :image="post.avatar || '/images/shared/avatar-default.jpg'"
                                :label="getInitial(post.autor || 'U')" size="small" />
                            <div class="post-card__author">
                                <strong>
                                    {{ post.autor || 'Creador' }}
                                    <span v-if="post.es_premium" class="premium-chip">Premium</span>
                                    <i v-if="post.verificado" class="pi pi-verified"></i>
                                </strong>
                                <span>{{ post.tiempo || formatearTiempo(post.created_at) || 'Hace 5 min' }}</span>
                            </div>
                            <span v-if="post.es_premium" class="post-card__badge">
                                <i class="pi pi-lock"></i> Exclusivo
                            </span>
                        </div>

                        <h3 v-if="post.titulo" class="post-card__title">{{ post.titulo }}</h3>
                        <p v-if="post.descripcion" class="post-card__text">{{ post.descripcion }}</p>
                        <p v-else-if="post.texto" class="post-card__text">{{ post.texto }}</p>

                        <!-- MOSTRAR ARCHIVOS CON BLOQUEO SI ES PREMIUM Y NO TIENE ACCESO -->
                        <div v-if="post.archivos && post.archivos.length > 0" class="post-card__media">
                            <img v-if="post.archivos[0]?.url" :src="post.archivos[0].url"
                                :alt="post.titulo || 'Contenido'" @error="handleImageError"
                                :class="{ 'post-card__media--blurred': !puedeAcceder(post) }" />

                            <div v-if="!puedeAcceder(post)" class="premium-overlay">
                                <span class="premium-overlay__lock"><i class="pi pi-lock"></i></span>
                                <strong>Contenido exclusivo</strong>
                                <span>Suscríbete para ver este contenido</span>
                                <PvButton label="SUSCRIBIRSE" icon="pi pi-crown" class="premium-overlay__btn"
                                    @click="irASuscribirse(post)" />
                            </div>
                        </div>

                        <!-- MOSTRAR IMAGEN CON BLOQUEO SI ES PREMIUM Y NO TIENE ACCESO -->
                        <div v-else-if="post.imagen" class="post-card__media">
                            <img :src="post.imagen" :alt="post.autor || 'Contenido'" @error="handleImageError"
                                :class="{ 'post-card__media--blurred': !puedeAcceder(post) }" />

                            <div v-if="!puedeAcceder(post)" class="premium-overlay">
                                <span class="premium-overlay__lock"><i class="pi pi-lock"></i></span>
                                <strong>Contenido exclusivo</strong>
                                <span>Suscríbete para ver este contenido</span>
                                <PvButton label="SUSCRIBIRSE" icon="pi pi-crown" class="premium-overlay__btn"
                                    @click="irASuscribirse(post)" />
                            </div>
                        </div>

                        <!-- FOOTER CON BOTONES -->
                        <div class="post-card__footer">
                            <button type="button" class="footer-action" :class="{
                                'footer-action--active': estadoInteraccion(post).liked,
                                'footer-action--disabled': !puedeInteractuar(post)
                            }" @click="alternarLike(post)" :disabled="!puedeInteractuar(post)">
                                <i :class="estadoInteraccion(post).liked ? 'pi pi-heart-fill' : 'pi pi-heart'"></i>
                                {{ estadoInteraccion(post).total_likes }}
                            </button>

                            <button type="button" class="footer-action"
                                :class="{ 'footer-action--disabled': !puedeInteractuar(post) }"
                                @click="alternarComentarios(post)" :disabled="!puedeInteractuar(post)">
                                <i class="pi pi-comment"></i> {{ estadoInteraccion(post).total_comentarios }}
                            </button>

                            <button type="button" class="footer-action" @click="compartirPublicacion(post)">
                                <i class="pi pi-share-alt"></i>
                            </button>
                        </div>

                        <!-- PANEL DE COMENTARIOS -->
                        <div v-if="estadoInteraccion(post).mostrarComentarios" class="comments-panel">
                            <div v-if="!puedeInteractuar(post)" class="comments-panel__bloqueado">
                                <i class="pi pi-lock"></i>
                                <strong>Contenido exclusivo para suscriptores</strong>
                                <span>Suscríbete para ver y escribir comentarios.</span>
                                <button class="comments-panel__btn-sub" @click="irASuscribirse(post)">
                                    Suscribirme ahora
                                </button>
                            </div>

                            <template v-else>
                                <div v-if="estadoInteraccion(post).cargandoComentarios"
                                    class="comments-panel__cargando">
                                    Cargando comentarios...
                                </div>
                                <div v-else class="comments-panel__lista">
                                    <p v-if="estadoInteraccion(post).comentarios.length === 0"
                                        class="comments-panel__vacio">
                                        Sé el primero en comentar.
                                    </p>
                                    <div v-for="c in estadoInteraccion(post).comentarios" :key="c.id"
                                        class="comment-item">
                                        <AvatarCustom :image="c.usuario?.avatar || '/images/shared/avatar-default.jpg'"
                                            :label="getInitial(c.usuario?.nombre || 'U')" size="small" />
                                        <div class="comment-item__body">
                                            <strong>{{ c.usuario?.nombre || 'Usuario' }}</strong>
                                            <span class="comment-item__tiempo">{{ c.tiempo || 'Recién' }}</span>
                                            <p>{{ c.texto }}</p>
                                        </div>
                                    </div>
                                </div>
                                <form class="comments-panel__form" @submit.prevent="enviarComentario(post)">
                                    <input v-model="estadoInteraccion(post).nuevoComentario" type="text" maxlength="500"
                                        placeholder="Escribe un comentario..."
                                        :disabled="estadoInteraccion(post).enviandoComentario" />
                                    <button type="submit"
                                        :disabled="!estadoInteraccion(post).nuevoComentario?.trim() || estadoInteraccion(post).enviandoComentario">
                                        <i class="pi pi-send"></i>
                                    </button>
                                </form>
                            </template>
                        </div>
                    </article>
                </div>

                <!-- SIDEBAR -->
                <aside class="sidebar-column">
                    <template v-if="esCreador">
                        <div class="sidebar-card creator-space-card">
                            <h3>
                                <i class="pi pi-user-edit" style="color: var(--brand); margin-right: 0.5rem;"></i>
                                Tu espacio creador
                            </h3>

                            <div class="creator-space-card__avatar-wrapper">
                                <img :src="usuarioLocal.avatar || '/images/shared/avatar-default.jpg'" alt="Avatar"
                                    class="creator-space-card__avatar" />
                            </div>

                            <div class="creator-space-card__info">
                                <strong>
                                    {{ usuarioLocal.nombre }}
                                    <span v-if="creadorEsPremium" class="premium-chip">Premium</span>
                                    <i v-if="usuarioLocal.verificado" class="pi pi-verified"></i>
                                </strong>
                                <span class="creator-space-card__bio">
                                    {{ creadorLocal?.biografia || 'Creador de contenido exclusivo' }}
                                </span>
                                <span class="creator-space-card__status">
                                    <i class="pi pi-circle-fill"></i>
                                    {{ creadorLocal?.esta_verificado ? 'Perfil verificado' : 'Pendiente de verificación'
                                    }}
                                </span>
                            </div>

                            <div class="creator-space-card__stats">
                                <div>
                                    <strong>{{ estadisticasLocal.total_publicaciones || 0 }}</strong>
                                    <span>Publicaciones</span>
                                </div>
                                <div>
                                    <strong>{{ estadisticasLocal.total_suscriptores || 0 }}</strong>
                                    <span>Suscriptores</span>
                                </div>
                                <div>
                                    <strong>${{ formatearNumero(estadisticasLocal.total_ganancias || 0) }}</strong>
                                    <span>Ganancias</span>
                                </div>
                            </div>

                            <PvButton label="Ver mi perfil de creador" icon="pi pi-arrow-right" iconPos="right"
                                class="creator-space-card__btn" @click="verPerfilCreador" />
                            <button class="creator-space-card__share" @click="compartirPerfil">
                                <i class="pi pi-share-alt"></i> Compartir perfil
                            </button>

                            <button class="creator-space-card__new-content" @click="irANuevoContenido">
                                <i class="pi pi-plus-circle"></i> Nuevo contenido
                            </button>
                        </div>
                    </template>

                    <template v-if="!esCreador">
                        <div class="sidebar-card creadores-destacados-card">
                            <h3>
                                <i class="pi pi-users" style="color: var(--brand); margin-right: 0.5rem;"></i>
                                Creadores destacados
                            </h3>
                            <div class="creadores-destacados__list">
                                <div v-for="creador in creadoresDestacados" :key="creador.id"
                                    class="creador-destacado-item">
                                    <img :src="creador.avatar || '/images/shared/avatar-default.jpg'"
                                        :alt="creador.nombre" class="creador-destacado-item__avatar" />
                                    <div class="creador-destacado-item__info">
                                        <strong>{{ creador.nombre }}</strong>
                                        <span>{{ creador.categoria || 'Creador' }}</span>
                                    </div>
                                </div>
                            </div>
                            <PvButton label="Ver todos los creadores" icon="pi pi-arrow-right" iconPos="right"
                                class="creadores-destacados__btn" link @click="irAComunidadCreadores" />
                        </div>

                        <div class="sidebar-card suscripciones-card">
                            <h3>
                                <i class="pi pi-heart" style="color: var(--brand); margin-right: 0.5rem;"></i>
                                Mis suscripciones
                            </h3>
                            <p class="suscripciones-card__desc">
                                Gestiona tus suscripciones a creadores y contenido exclusivo.
                            </p>
                            <PvButton label="Ver mis suscripciones" icon="pi pi-arrow-right" iconPos="right"
                                class="suscripciones-card__btn" @click="irAMisSuscripciones" />
                        </div>
                    </template>

                    <div class="sidebar-card eventos-card">
                        <div class="sidebar-card__header">
                            <h3>
                                <i class="pi pi-calendar" style="color: var(--brand); margin-right: 0.5rem;"></i>
                                Próximos eventos
                            </h3>
                            <a href="#" class="see-all">Ver todos <i class="pi pi-chevron-right"></i></a>
                        </div>
                        <div class="event-list">
                            <div v-if="eventos.length === 0" class="empty-state">
                                <i class="pi pi-calendar"
                                    style="font-size: 1.5rem; color: #ccc; margin-bottom: 0.5rem;"></i>
                                <p>No hay eventos próximos.</p>
                            </div>
                            <div v-for="e in eventos" :key="e.id" class="event-item">
                                <div class="event-item__image-wrapper">
                                    <img :src="e.imagen" :alt="e.titulo" class="event-item__image" />
                                </div>

                                <div class="event-item__info">
                                    <div class="event-item__header">
                                        <span class="event-item__date">
                                            <strong>{{ e.dia }}</strong>
                                            <span>{{ e.mes }}</span>
                                        </span>
                                        <span class="event-item__title">{{ e.titulo }}</span>
                                    </div>
                                    <div class="event-item__details">
                                        <span class="event-item__meta">
                                            <i class="pi pi-map-marker"></i> {{ e.lugar }}
                                        </span>
                                        <span class="event-item__meta">
                                            <i class="pi pi-clock"></i> {{ e.nombre_dia }}, {{ e.fecha_completa }} - {{
                                                e.fecha_hora
                                            }} hrs
                                        </span>
                                        <span v-if="e.asistentes !== undefined" class="event-item__asistentes">
                                            <i class="pi pi-users"></i> {{ e.asistentes }} asistentes
                                            <span v-if="e.capacidad > 0"> / {{ e.capacidad }} cupos</span>
                                        </span>
                                    </div>
                                    <PvButton label="Ver evento" icon="pi pi-arrow-right" iconPos="right"
                                        class="event-item__btn" link />
                                </div>
                            </div>
                        </div>
                        <div class="eventos-footer">
                            <a href="#" class="explore-link">Explorar todos los eventos <i
                                    class="pi pi-chevron-right"></i></a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import AvatarCustom from '@/Components/AvatarCustom.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import PvButton from 'primevue/button';

// ============================================================
// OBTENER USUARIO DESDE Inertia
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
    publicacionesCreadores: {
        type: Array,
        default: () => []
    },
    suscripcionesUsuario: {
        type: Array,
        default: () => []
    },
    configuracion_monetizacion: {
        type: Object,
        default: null
    },
    esCreador: {
        type: Boolean,
        default: false
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
// USUARIO LOCAL
// ============================================================
const usuarioLocal = computed(() => {
    const user = props.usuario || {};
    let avatar = user.avatar || '/images/shared/avatar-default.jpg';

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
// DATOS COMPUTADOS DESDE PROPS
// ============================================================
const creadorLocal = computed(() => props.creador);
const estadisticasLocal = computed(() => props.estadisticas);
const publicaciones = computed(() => props.publicacionesCreadores || props.contenidos_recientes || []);
const eventos = computed(() => page.props.proximosEventos || []);
const suscripcionesUsuario = computed(() => props.suscripcionesUsuario || []);

const creadorEsPremium = computed(() => {
    return creadorLocal.value?.es_premium || false;
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

function formatearTiempo(fecha) {
    if (!fecha) return 'Recién';

    const ahora = new Date();
    const fechaDate = new Date(fecha);
    const diffMs = ahora - fechaDate;
    const diffMin = Math.floor(diffMs / 60000);
    const diffHoras = Math.floor(diffMs / 3600000);
    const diffDias = Math.floor(diffMs / 86400000);
    const diffSemanas = Math.floor(diffDias / 7);
    const diffMeses = Math.floor(diffDias / 30);
    const diffAnios = Math.floor(diffDias / 365);

    if (diffMin < 1) return 'Hace un momento';
    if (diffMin < 60) return `Hace ${diffMin} minuto${diffMin > 1 ? 's' : ''}`;
    if (diffHoras < 24) return `Hace ${diffHoras} hora${diffHoras > 1 ? 's' : ''}`;
    if (diffDias < 7) return `Hace ${diffDias} día${diffDias > 1 ? 's' : ''}`;
    if (diffSemanas < 4) return `Hace ${diffSemanas} semana${diffSemanas > 1 ? 's' : ''}`;
    if (diffMeses < 12) return `Hace ${diffMeses} mes${diffMeses > 1 ? 'es' : ''}`;
    return `Hace ${diffAnios} año${diffAnios > 1 ? 's' : ''}`;
}

function formatearNumero(numero) {
    if (numero >= 1000000) {
        return (numero / 1000000).toFixed(1) + 'M';
    }
    if (numero >= 1000) {
        return (numero / 1000).toFixed(1) + 'K';
    }
    return numero;
}

// ============================================================
// COMPARTIR PUBLICACIÓN
// ============================================================
function compartirPublicacion(post) {
    const url = window.location.origin + '/comunidad/publicacion/' + post.id;

    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            showSuccess('Link copiado al portapapeles');
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
        showSuccess('Link copiado al portapapeles');
    } catch (err) {
        showError('No se pudo copiar el link');
    }
    document.body.removeChild(textarea);
}

// ============================================================
// VERIFICAR ACCESO A CONTENIDO
// ============================================================
function puedeAcceder(post) {
    if (!post.es_premium) return true;
    if (props.esCreador) return true;
    return post.usuario_esta_suscrito || false;
}

function puedeInteractuar(post) {
    if (!post.es_premium) return true;
    return puedeAcceder(post);
}

// ============================================================
// IR A SUSCRIBIRSE
// ============================================================
function irASuscribirse(post) {
    if (!usuarioLocal.value || !usuarioLocal.value.id) {
        window.location.href = '/login';
        return;
    }

    if (post.creador_id) {
        const nombreCreador = post.autor?.toLowerCase().replace(/\s+/g, '-') || 'creador';
        window.location.href = `/creador/${post.creador_id}/${nombreCreador}/suscripcion`;
    } else {
        showInfo('Función de suscripción en desarrollo');
    }
}

// ============================================================
// IR A MIS SUSCRIPCIONES
// ============================================================
function irAMisSuscripciones() {
    try {
        if (typeof route !== 'undefined' && route('suscripciones.index')) {
            router.get(route('suscripciones.index'));
            return;
        }
        window.location.href = '/suscripciones';
    } catch (error) {
        console.error('Error al redirigir a suscripciones:', error);
        window.location.href = '/suscripciones';
    }
}

// ============================================================
// LIKES Y COMENTARIOS - CON MANEJO CORREGIDO
// ============================================================
const interacciones = reactive({});

function estadoInteraccion(post) {
    if (!interacciones[post.id]) {
        interacciones[post.id] = {
            liked: !!post.yo_le_di_like,
            total_likes: post.total_likes || post.likes || 0,
            total_comentarios: post.total_comentarios || post.comentarios || 0,
            mostrarComentarios: false,
            cargandoComentarios: false,
            comentarios: [],
            comentariosCargados: false,
            nuevoComentario: '',
            enviandoComentario: false,
            procesandoLike: false,
        };
    }
    return interacciones[post.id];
}

// ✅ FUNCIÓN CORREGIDA PARA LIKES
async function alternarLike(post) {
    if (!usuarioLocal.value?.id) {
        window.location.href = '/login';
        return;
    }

    if (!puedeInteractuar(post)) {
        showInfo(
            'Este contenido es exclusivo para suscriptores. Suscríbete para dar like.',
            'Contenido bloqueado'
        );
        return;
    }

    const estado = estadoInteraccion(post);

    // ✅ Evitar múltiples clics
    if (estado.procesandoLike) return;
    estado.procesandoLike = true;

    const likedAntes = estado.liked;
    const totalAntes = estado.total_likes;

    // ✅ Optimista: actualizar UI inmediatamente
    estado.liked = !likedAntes;
    estado.total_likes = likedAntes ? totalAntes - 1 : totalAntes + 1;

    try {
        const url = `/contenidos/${post.id}/like`;
        console.log('📤 Enviando like a:', url);

        const response = await axios.post(url);
        console.log('✅ Respuesta del servidor:', response.data);

        // ✅ CORREGIDO: Verificar la estructura de la respuesta
        if (response.data) {
            // Si el servidor devuelve ok=true y tiene liked y total_likes
            if (response.data.ok === true) {
                // Usar los valores del servidor (si existen)
                if (response.data.liked !== undefined) {
                    estado.liked = response.data.liked;
                }
                if (response.data.total_likes !== undefined) {
                    estado.total_likes = response.data.total_likes;
                }

                if (response.data.mensaje) {
                    showInfo(response.data.mensaje);
                }
            } else {
                // Si el servidor devuelve ok=false, revertir
                estado.liked = likedAntes;
                estado.total_likes = totalAntes;
                showError(response.data?.mensaje || 'Error al procesar el like');
            }
        } else {
            // Respuesta vacía o inválida
            estado.liked = likedAntes;
            estado.total_likes = totalAntes;
            showError('Respuesta inválida del servidor');
        }
    } catch (e) {
        // ✅ Revertir en caso de error
        estado.liked = likedAntes;
        estado.total_likes = totalAntes;

        console.error('❌ Error al dar like:', e);
        console.error('❌ Respuesta de error:', e.response?.data);

        if (e?.response?.status === 403) {
            showInfo(e.response.data?.mensaje || 'Suscríbete para dar like a este contenido.');
        } else if (e?.response?.status === 401) {
            showInfo('Debes iniciar sesión para dar like.');
        } else if (e?.response?.data?.mensaje) {
            showError(e.response.data.mensaje);
        } else {
            showError('No se pudo procesar tu like. Intenta de nuevo.');
        }
    } finally {
        estado.procesandoLike = false;
    }
}

// ✅ FUNCIÓN PARA COMENTARIOS
async function alternarComentarios(post) {
    const estado = estadoInteraccion(post);
    estado.mostrarComentarios = !estado.mostrarComentarios;

    if (estado.mostrarComentarios && !estado.comentariosCargados && puedeInteractuar(post)) {
        estado.cargandoComentarios = true;
        try {
            const url = `/contenidos/${post.id}/comentarios`;
            console.log('📤 Cargando comentarios desde:', url);

            const response = await axios.get(url);
            console.log('✅ Comentarios cargados:', response.data);

            if (response.data && response.data.ok) {
                estado.comentarios = response.data.comentarios || [];
                estado.total_comentarios = response.data.total_comentarios || 0;
                estado.comentariosCargados = true;
            }
        } catch (e) {
            console.error('❌ Error al cargar comentarios:', e);
            if (e?.response?.status === 403) {
                showInfo(e.response.data?.mensaje || 'Suscríbete para ver los comentarios.');
            } else {
                showError('No se pudieron cargar los comentarios.');
            }
        } finally {
            estado.cargandoComentarios = false;
        }
    }
}

// ✅ FUNCIÓN PARA ENVIAR COMENTARIO
async function enviarComentario(post) {
    if (!usuarioLocal.value?.id) {
        window.location.href = '/login';
        return;
    }

    if (!puedeInteractuar(post)) {
        showInfo(
            'Este contenido es exclusivo para suscriptores. Suscríbete para comentar.',
            'Contenido bloqueado'
        );
        return;
    }

    const estado = estadoInteraccion(post);
    const texto = estado.nuevoComentario.trim();
    if (!texto) return;

    estado.enviandoComentario = true;
    try {
        const url = `/contenidos/${post.id}/comentarios`;
        console.log('📤 Enviando comentario a:', url, texto);

        const response = await axios.post(url, { texto });
        console.log('✅ Comentario enviado:', response.data);

        if (response.data && response.data.ok) {
            estado.comentarios.unshift(response.data.comentario);
            estado.total_comentarios = response.data.total_comentarios;
            estado.nuevoComentario = '';
            showSuccess('Comentario publicado');
        } else {
            showError(response.data?.mensaje || 'Error al publicar comentario');
        }
    } catch (e) {
        console.error('❌ Error al enviar comentario:', e);
        if (e?.response?.status === 403) {
            showInfo(e.response.data?.mensaje || 'Suscríbete para comentar.');
        } else if (e?.response?.data?.mensaje) {
            showError(e.response.data.mensaje);
        } else {
            showError('No se pudo publicar tu comentario. Intenta de nuevo.');
        }
    } finally {
        estado.enviandoComentario = false;
    }
}

// ============================================================
// PUBLICACIONES FILTRADAS
// ============================================================
const publicacionesFiltradas = computed(() => {
    return [...publicaciones.value];
});

// ============================================================
// CREADORES DESTACADOS (solo para NO creadores)
// ============================================================
const creadoresDestacados = computed(() => {
    if (props.esCreador) return [];

    const creadoresMap = new Map();

    publicaciones.value.forEach(post => {
        if (post.autor && !creadoresMap.has(post.autor)) {
            creadoresMap.set(post.autor, {
                id: post.usuario_id || post.creador_id || post.id || Math.random(),
                nombre: post.autor,
                avatar: post.avatar || '/images/shared/avatar-default.jpg',
                categoria: post.rol || 'Creador'
            });
        }
    });

    if (creadoresMap.size === 0) {
        return [
            { id: 1, nombre: 'CreadorEjemplo', avatar: '/images/shared/avatar-default.jpg', categoria: 'Creador' },
            { id: 2, nombre: 'ContenidoExclusivo', avatar: '/images/shared/avatar-default.jpg', categoria: 'Creador Premium' },
        ];
    }

    return Array.from(creadoresMap.values()).slice(0, 5);
});

// ============================================================
// FUNCIONES DE NAVEGACIÓN
// ============================================================
function verPerfilCreador() {
    router.get(route('creador.perfil'));
}

function irANuevoContenido() {
    router.get(route('creador.nuevo-contenido'));
}

function irAComunidadCreadores() {
    try {
        if (typeof route !== 'undefined' && route('creador.comunidad')) {
            router.get(route('creador.comunidad'));
            return;
        }
        window.location.href = '/creador/comunidad';
    } catch (error) {
        console.error('Error al redirigir a comunidad de creadores:', error);
        window.location.href = '/creador/comunidad';
    }
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
// LIFECYCLE
// ============================================================
onMounted(() => {
    console.log('=== COMUNIDAD CREADOR ===');
    console.log('Es creador:', props.esCreador);
    console.log('Usuario:', usuarioLocal.value);
    console.log('Publicaciones:', publicaciones.value);
    console.log('Creador:', creadorLocal.value);
    console.log('Estadísticas:', estadisticasLocal.value);
});
</script>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA - TODOS LOS ESTILOS EXISTENTES SE MANTIENEN
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
    --shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    --shadow-hover: 0 4px 16px rgba(0, 0, 0, 0.08);

    --font-serif: 'Fraunces', Georgia, serif;
    --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 20px;
    --radius-full: 999px;

    font-family: var(--font-sans);
    color: var(--ink);
    background: #f0f2f5;
    -webkit-font-smoothing: antialiased;
    max-width: 1500px;
    margin: 0 auto;
    padding: 1rem 2rem 0;
}

/* =========================================================================
   HERO
   ========================================================================= */
.hero {
    max-width: 1400px;
    margin: 1rem auto 0;
    padding: 0;
}

.hero__grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 280px;
    background: var(--ink);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
}

.hero__copy {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 2rem 2rem;
    color: #ffffff;
}

.hero__eyebrow {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.6);
    margin: 0 0 0.4rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
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
    padding: 0.1rem 0.5rem;
    border-radius: var(--radius-full);
    font-size: 0.55rem;
    font-weight: 600;
}

.hero__title {
    font-family: var(--font-serif);
    font-size: 1.8rem;
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
    line-height: 1.5;
    max-width: 440px;
    margin: 0.5rem 0 0;
    font-size: 0.8rem;
}

.hero__media {
    position: relative;
    min-height: 200px;
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
    padding: 0.8rem 1.2rem;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin: 1rem auto 0;
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
    gap: 0.6rem;
    align-items: center;
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
    font-size: 0.8rem;
}

.benefit-item strong {
    display: block;
    font-size: 0.78rem;
}

.benefit-item span {
    font-size: 0.7rem;
    color: var(--muted);
}

/* =========================================================================
   CONTENT GRID
   ========================================================================= */
.content-grid {
    max-width: 1400px;
    margin: 1rem auto 0;
    padding: 0 0 2rem;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 1.2rem;
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
    gap: 0.8rem;
}

/* =========================================================================
   FEED HEADER
   ========================================================================= */
.feed-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 0.6rem 1rem;
    box-shadow: var(--shadow);
}

.feed-header h2 {
    font-size: 0.9rem;
    margin: 0;
    font-weight: 700;
}

.feed-header__count {
    font-size: 0.7rem;
    color: var(--muted);
}

/* =========================================================================
   EMPTY STATE
   ========================================================================= */
.empty-state {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 2rem 1.5rem;
    text-align: center;
    box-shadow: var(--shadow);
}

.empty-state i {
    font-size: 2.5rem;
    color: var(--muted-light);
    margin-bottom: 0.8rem;
}

.empty-state h3 {
    font-size: 1rem;
    margin: 0 0 0.3rem;
}

.empty-state p {
    color: var(--muted);
    margin: 0 0 1rem;
    font-size: 0.85rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.5rem 1.2rem;
    border: none;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.8rem;
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
   POSTS
   ========================================================================= */
.post-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 0.8rem;
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
    gap: 0.5rem;
    margin-bottom: 0.4rem;
}

.post-card__author {
    display: flex;
    flex-direction: column;
    flex: 1;
    gap: 0.05rem;
}

.post-card__author strong {
    font-size: 0.78rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.post-card__author strong i {
    color: var(--brand);
    font-size: 0.65rem;
}

.premium-chip {
    background: var(--brand-soft);
    color: var(--brand);
    font-size: 0.55rem;
    font-weight: 700;
    padding: 0.1rem 0.4rem;
    border-radius: var(--radius-full);
}

.post-card__author span {
    font-size: 0.6rem;
    color: var(--muted-light);
}

.post-card__badge {
    background: var(--brand-soft);
    color: var(--brand);
    font-size: 0.6rem;
    font-weight: 700;
    padding: 0.15rem 0.5rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.2rem;
    white-space: nowrap;
}

.post-card__title {
    font-size: 0.85rem;
    margin: 0 0 0.2rem;
    font-weight: 600;
}

.post-card__text {
    font-size: 0.78rem;
    color: var(--ink-soft);
    line-height: 1.5;
    margin: 0 0 0.5rem;
    white-space: pre-line;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.post-card__media {
    position: relative;
    border-radius: var(--radius-sm);
    overflow: hidden;
    aspect-ratio: 16/9;
    background: #111;
    margin-bottom: 0.4rem;
}

.post-card__media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.post-card__media--blurred {
    filter: blur(20px) brightness(0.5);
    transform: scale(1.02);
}

.premium-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #fff;
    gap: 0.2rem;
    text-align: center;
    background: rgba(0, 0, 0, 0.6);
    padding: 0.8rem;
}

.premium-overlay__lock {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    margin-bottom: 0.2rem;
}

.premium-overlay strong {
    font-size: 0.85rem;
    font-weight: 700;
}

.premium-overlay span {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.7);
}

.premium-overlay__btn {
    margin-top: 0.3rem;
    font-weight: 700 !important;
    border-radius: 6px !important;
    font-size: 0.65rem !important;
    padding: 0.3rem 1.2rem !important;
    background: var(--brand) !important;
    border-color: var(--brand) !important;
    color: #fff !important;
}

.premium-overlay__btn:hover {
    background: var(--brand-dark) !important;
    border-color: var(--brand-dark) !important;
    transform: scale(1.05);
}

/* =========================================================================
   POST FOOTER
   ========================================================================= */
.post-card__footer {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--line);
    font-size: 0.75rem;
    color: var(--ink-soft);
    flex-wrap: wrap;
}

.footer-action {
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    background: none;
    border: none;
    padding: 0.2rem 0.4rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-family: inherit;
    color: var(--ink-soft);
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
}

.footer-action:hover:not(.footer-action--disabled) {
    background: var(--brand-soft);
    color: var(--brand);
}

.footer-action i {
    font-size: 0.8rem;
}

.footer-action--active {
    color: var(--brand);
    font-weight: 600;
}

.footer-action--active i {
    color: var(--brand);
}

.footer-action--disabled {
    opacity: 0.5;
    cursor: not-allowed !important;
}

.footer-action--disabled:hover {
    background: none !important;
    color: var(--ink-soft) !important;
}

/* =========================================================================
   COMENTARIOS
   ========================================================================= */
.comments-panel {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px dashed var(--line);
}

.comments-panel__bloqueado {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
    text-align: center;
    padding: 1rem;
    background: var(--brand-soft);
    border-radius: 8px;
    font-size: 0.8rem;
    color: var(--ink-soft);
}

.comments-panel__bloqueado i {
    color: var(--brand);
    font-size: 1.5rem;
}

.comments-panel__bloqueado strong {
    font-size: 0.9rem;
    color: var(--ink);
}

.comments-panel__bloqueado span {
    font-size: 0.75rem;
}

.comments-panel__btn-sub {
    margin-top: 0.3rem;
    background: var(--brand);
    color: #fff;
    border: none;
    padding: 0.4rem 1.5rem;
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.comments-panel__btn-sub:hover {
    background: var(--brand-dark);
    transform: scale(1.02);
}

.comments-panel__cargando,
.comments-panel__vacio {
    font-size: 0.75rem;
    color: var(--muted);
    padding: 0.3rem 0;
}

.comments-panel__lista {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    max-height: 200px;
    overflow-y: auto;
    margin-bottom: 0.5rem;
}

.comment-item {
    display: flex;
    gap: 0.4rem;
    align-items: flex-start;
}

.comment-item__body {
    background: #f7f7f8;
    border-radius: 8px;
    padding: 0.3rem 0.6rem;
    flex: 1;
    font-size: 0.75rem;
}

.comment-item__body strong {
    font-size: 0.72rem;
}

.comment-item__tiempo {
    font-size: 0.6rem;
    color: var(--muted);
    margin-left: 0.3rem;
}

.comment-item__body p {
    margin: 0.1rem 0 0;
    color: var(--ink);
}

.comments-panel__form {
    display: flex;
    gap: 0.4rem;
}

.comments-panel__form input {
    flex: 1;
    border: 1px solid var(--line);
    border-radius: 999px;
    padding: 0.4rem 0.8rem;
    font-size: 0.75rem;
    outline: none;
}

.comments-panel__form input:focus {
    border-color: var(--brand);
}

.comments-panel__form button {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: var(--brand);
    color: #fff;
    cursor: pointer;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.comments-panel__form button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.comments-panel__form button i {
    font-size: 0.7rem;
}

/* =========================================================================
   SIDEBAR
   ========================================================================= */
.sidebar-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1rem;
    box-shadow: var(--shadow);
}

.sidebar-card h3 {
    font-size: 0.85rem;
    margin: 0 0 0.8rem;
    display: flex;
    align-items: center;
}

.sidebar-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.6rem;
}

.sidebar-card__header h3 {
    margin: 0;
}

.see-all {
    color: var(--brand);
    font-size: 0.65rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.15rem;
    transition: all 0.3s ease;
}

.see-all:hover {
    color: var(--brand-dark);
    gap: 0.3rem;
}

/* =========================================================================
   SUSCRIPCIONES CARD
   ========================================================================= */
.suscripciones-card {
    background: linear-gradient(135deg, var(--brand-soft), #fff);
    border: 1px solid var(--brand-soft);
}

.suscripciones-card__desc {
    font-size: 0.75rem;
    color: var(--muted);
    margin: 0 0 0.8rem;
    line-height: 1.4;
}

.suscripciones-card__btn {
    width: 100%;
    font-weight: 700 !important;
    border-radius: 6px !important;
    font-size: 0.75rem !important;
    padding: 0.4rem !important;
    background: var(--brand) !important;
    border-color: var(--brand) !important;
    color: #fff !important;
}

.suscripciones-card__btn:hover {
    background: var(--brand-dark) !important;
    border-color: var(--brand-dark) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(200, 30, 58, 0.3);
}

/* =========================================================================
   ESPACIO CREADOR
   ========================================================================= */
.creator-space-card__avatar-wrapper {
    display: flex;
    justify-content: center;
    margin-bottom: 0.6rem;
}

.creator-space-card__avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--brand);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.creator-space-card__avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.creator-space-card__info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.1rem;
    margin-bottom: 0.8rem;
    text-align: center;
}

.creator-space-card__info strong {
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.creator-space-card__info strong i {
    color: var(--brand);
    font-size: 0.7rem;
}

.creator-space-card__bio {
    font-size: 0.75rem;
    color: var(--ink-soft);
}

.creator-space-card__status {
    font-size: 0.7rem;
    color: #1c7a3c;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.creator-space-card__status i {
    color: #1fbf5c;
    font-size: 0.4rem;
}

.creator-space-card__stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    text-align: center;
    border-top: 1px solid var(--line);
    border-bottom: 1px solid var(--line);
    padding: 0.6rem 0;
    margin-bottom: 0.8rem;
}

.creator-space-card__stats strong {
    display: block;
    font-size: 0.9rem;
}

.creator-space-card__stats span {
    font-size: 0.65rem;
    color: var(--muted);
}

.creator-space-card__btn {
    width: 100%;
    font-weight: 700 !important;
    border-radius: 6px !important;
    margin-bottom: 0.5rem !important;
    font-size: 0.75rem !important;
    padding: 0.4rem !important;
}

.creator-space-card__share {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    border: 1px solid var(--line);
    border-radius: 6px;
    background: #fff;
    padding: 0.5rem;
    font-size: 0.75rem;
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

.creator-space-card__new-content {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    border: 1px solid var(--brand);
    border-radius: 6px;
    background: var(--brand);
    color: #fff;
    padding: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 0.5rem;
}

.creator-space-card__new-content:hover {
    background: var(--brand-dark);
    border-color: var(--brand-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(200, 30, 58, 0.3);
}

.creator-space-card__new-content i {
    font-size: 0.9rem;
}

/* =========================================================================
   CREADORES DESTACADOS
   ========================================================================= */
.creadores-destacados__list {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    margin-bottom: 0.6rem;
}

.creador-destacado-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.3rem 0.5rem;
    border-radius: 6px;
    background: var(--surface);
    transition: all 0.3s ease;
}

.creador-destacado-item:hover {
    background: var(--brand-soft);
    transform: translateX(2px);
}

.creador-destacado-item__avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--line);
}

.creador-destacado-item__info {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.creador-destacado-item__info strong {
    font-size: 0.75rem;
}

.creador-destacado-item__info span {
    font-size: 0.6rem;
    color: var(--muted);
}

.creadores-destacados__btn {
    width: 100%;
    font-weight: 700 !important;
    color: var(--brand) !important;
    justify-content: center !important;
    font-size: 0.75rem !important;
}

/* =========================================================================
   EVENTOS
   ========================================================================= */
.event-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.event-item {
    display: flex;
    gap: 0.6rem;
    padding: 0.4rem;
    border-radius: 8px;
    background: var(--surface);
    border: 1px solid var(--line);
    transition: all 0.3s ease;
    align-items: stretch;
}

.event-item:hover {
    background: #ffffff;
    border-color: var(--brand);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    transform: translateY(-2px);
}

.event-item__image-wrapper {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--line);
}

.event-item__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.event-item__info {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
    gap: 0.1rem;
}

.event-item__header {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.event-item__date {
    background: var(--brand-soft);
    color: var(--brand);
    border-radius: 4px;
    padding: 0.15rem 0.3rem;
    text-align: center;
    line-height: 1.05;
    flex-shrink: 0;
    min-width: 32px;
}

.event-item__date strong {
    display: block;
    font-size: 0.65rem;
}

.event-item__date span {
    font-size: 0.4rem;
    letter-spacing: 0.05em;
    font-weight: 600;
}

.event-item__title {
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--ink);
}

.event-item__details {
    display: flex;
    flex-direction: column;
    gap: 0.05rem;
}

.event-item__meta {
    font-size: 0.55rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 0.15rem;
}

.event-item__meta i {
    font-size: 0.5rem;
    color: var(--brand);
}

.event-item__asistentes {
    font-size: 0.5rem;
    color: var(--muted);
    display: inline-flex;
    align-items: center;
    gap: 0.15rem;
}

.event-item__asistentes i {
    font-size: 0.45rem;
    color: var(--brand);
}

.event-item__btn {
    font-size: 0.55rem !important;
    padding: 0.1rem 0 !important;
    color: var(--brand) !important;
    font-weight: 600 !important;
    align-self: flex-start;
}

.event-item__btn:hover {
    color: var(--brand-dark) !important;
    gap: 0.4rem !important;
}

.eventos-footer {
    margin-top: 0.4rem;
    padding-top: 0.4rem;
    border-top: 1px solid var(--line);
}

.explore-link {
    display: inline-flex;
    align-items: center;
    gap: 0.15rem;
    color: var(--brand);
    font-size: 0.65rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
}

.explore-link:hover {
    color: var(--brand-dark);
    gap: 0.3rem;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
    .comunidad-creador-page {
        padding: 0.8rem 1rem 0;
    }

    .hero__grid {
        grid-template-columns: 1fr;
        min-height: auto;
    }

    .hero__copy {
        padding: 1.5rem 1.5rem;
    }

    .hero__title {
        font-size: 1.5rem;
    }

    .hero__media {
        min-height: 160px;
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
        padding: 0.5rem 0.5rem 0;
    }

    .hero__copy {
        padding: 1rem 1rem;
    }

    .hero__title {
        font-size: 1.2rem;
    }

    .hero__text {
        font-size: 0.75rem;
    }

    .hero__media {
        min-height: 120px;
    }

    .benefits-row {
        grid-template-columns: 1fr;
        padding: 0.6rem 0.8rem;
    }

    .post-card__footer {
        gap: 0.5rem;
    }

    .creator-space-card__stats {
        grid-template-columns: repeat(3, 1fr);
    }

    .feed-header {
        flex-direction: column;
        gap: 0.2rem;
        text-align: center;
    }

    .event-item {
        flex-direction: column;
        align-items: stretch;
    }

    .event-item__image-wrapper {
        width: 100%;
        height: 100px;
    }
}

@media (max-width: 480px) {
    .post-card {
        padding: 0.5rem;
    }

    .sidebar-card {
        padding: 0.6rem;
    }

    .hero__title {
        font-size: 1rem;
    }

    .benefits-row {
        padding: 0.5rem;
    }

    .creator-space-card__avatar {
        width: 60px;
        height: 60px;
    }

    .post-card__header {
        flex-wrap: wrap;
    }

    .post-card__badge {
        font-size: 0.55rem;
        padding: 0.1rem 0.4rem;
    }

    .post-card__media {
        aspect-ratio: 16/9;
    }

    .premium-overlay__lock {
        width: 32px;
        height: 32px;
        font-size: 0.8rem;
    }

    .premium-overlay strong {
        font-size: 0.75rem;
    }

    .premium-overlay span {
        font-size: 0.6rem;
    }
}
</style>