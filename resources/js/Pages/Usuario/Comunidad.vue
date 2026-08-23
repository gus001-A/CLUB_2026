<template>

    <Head title="Comunidad" />

    <ToastNotification ref="toastRef" :duration="5000" />

    <AppLayout activeNav="comunidad">
        <div class="comunidad-page">
            <!-- ============================================================ -->
            <!-- HERO -->
            <!-- ============================================================ -->
            <section class="hero">
                <div class="hero__grid">
                    <div class="hero__copy">
                        <p class="hero__eyebrow">
                            Bienvenido a la comunidad, <strong>{{ usuario.nombre }}</strong>
                            <span v-if="usuario.verificado" class="hero__verified">
                                <i class="pi pi-check-circle"></i> Verificado
                            </span>
                        </p>
                        <h1 class="hero__title">
                            <span class="hero__title-highlight">Comparte</span> experiencias<br />
                            y conecta con <span class="hero__title-highlight">personas reales</span>
                        </h1>
                        <p class="hero__text">
                            Conecta con personas reales, comparte experiencias, disfruta contenido exclusivo
                            y vive momentos inolvidables en un entorno seguro y confiable.
                        </p>
                    </div>

                    <div class="hero__media">
                        <img src="/images/comunidad.png" alt="Comunidad Club de Fantasías" class="hero__img" />
                        <div class="hero__fade"></div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- QUICK STATS -->
            <!-- ============================================================ -->
            <section class="quick-stats">
                <div v-for="m in metricas" :key="m.titulo" class="stat-card">
                    <span class="stat-card__icon"><i class="pi" :class="m.icon"></i></span>
                    <div class="stat-card__body">
                        <span class="stat-card__title">{{ m.titulo }}</span>
                        <span class="stat-card__desc">{{ m.desc }}</span>
                    </div>
                    <div class="stat-card__value">
                        <span class="value">{{ m.valor }}</span>
                        <span class="label">{{ m.etiqueta }}</span>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- FILTROS Y BÚSQUEDA -->
            <!-- ============================================================ -->
            <section class="filters-section">
                <div class="filters-container">
                    <div class="search-wrapper">
                        <i class="pi pi-search search-icon"></i>
                        <InputText v-model="filtroBusqueda" placeholder="Buscar publicaciones, autores..."
                            class="search-input" />
                        <button v-if="filtroBusqueda" class="clear-search" @click="filtroBusqueda = ''">
                            <i class="pi pi-times"></i>
                        </button>
                    </div>
                    <div class="order-wrapper">
                        <i class="pi pi-sort-alt order-icon"></i>
                        <Dropdown v-model="filtroOrden" :options="opcionesOrden" optionLabel="label"
                            placeholder="Ordenar por" class="order-dropdown" />
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- CONTENIDO PRINCIPAL - FEED EN 2 COLUMNAS -->
            <!-- ============================================================ -->
            <div class="content-grid">
                <!-- FEED COLUMN -->
                <div class="feed-column">
                    <!-- Crear publicación -->
                    <div class="composer-card">
                        <div class="composer-card__header">
                            <AvatarCustom :image="getAvatarUrl(usuario.avatar)" :label="getInitial(usuario.nombre)"
                                size="large" />
                            <strong>Crear publicación</strong>
                        </div>

                        <textarea v-model="nuevaPublicacion.texto" class="composer-card__input"
                            placeholder="¿Qué quieres compartir con la comunidad?" rows="2"></textarea>

                        <div v-if="nuevaPublicacion.imagen && nuevaPublicacion.tipo_media === 'imagen'"
                            class="composer-card__preview">
                            <img :src="nuevaPublicacion.previewUrl" alt="Preview"
                                class="composer-card__preview-image" />
                            <button @click="eliminarMedia" class="composer-card__remove-image">
                                <i class="pi pi-times"></i>
                            </button>
                        </div>

                        <div v-if="nuevaPublicacion.video && nuevaPublicacion.tipo_media === 'video'"
                            class="composer-card__preview">
                            <video :src="nuevaPublicacion.previewUrl" controls class="composer-card__preview-video"
                                preload="metadata"></video>
                            <button @click="eliminarMedia" class="composer-card__remove-image">
                                <i class="pi pi-times"></i>
                            </button>
                        </div>

                        <div class="composer-card__actions">
                            <button class="composer-action" @click="imageInput.click()">
                                <i class="pi pi-image"></i> Foto
                            </button>
                            <input ref="imageInput" type="file" accept="image/*" style="display: none"
                                @change="handleImageUpload" />

                            <button class="composer-action" @click="videoInput.click()">
                                <i class="pi pi-video"></i> Video
                            </button>
                            <input ref="videoInput" type="file" accept="video/*" style="display: none"
                                @change="handleVideoUpload" />

                            <Button label="Publicar" class="composer-card__submit" @click="publicar" />
                        </div>
                    </div>

                    <!-- Resultados de búsqueda -->
                    <div v-if="filtroBusqueda" class="search-results-info">
                        <span>Resultados para: <strong>"{{ filtroBusqueda }}"</strong></span>
                        <span class="results-count">{{ publicacionesFiltradas.length }} publicaciones</span>
                    </div>

                    <!-- Publicaciones en GRID 2 COLUMNAS -->
                    <div v-if="publicacionesFiltradas.length === 0" class="empty-state">
                        <i class="pi pi-inbox" style="font-size: 2rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <p>
                            {{ filtroBusqueda ? 'No se encontraron publicaciones con esa búsqueda.' : 'No hay publicaciones disponibles. ¡Sé el primero en compartir algo!' }}
                        </p>
                    </div>

                    <div v-else class="posts-grid">
                        <article v-for="(post, index) in publicacionesFiltradas" :key="post.id || index"
                            class="post-card">
                            <!-- Header del post -->
                            <div class="post-card__header">
                                <AvatarCustom :image="getAvatarUrl(post.avatar)" :label="getInitial(post.autor)"
                                    size="large" />
                                <div class="post-card__author">
                                    <span class="name">
                                        {{ post.autor }}
                                        <i v-if="post.verificado" class="pi pi-check-circle"></i>
                                    </span>
                                    <span class="rol">{{ post.rol }}</span>
                                </div>
                                <span class="post-card__time">{{ post.tiempo || formatearTiempo(post.created_at)
                                    }}</span>
                                <span v-if="post.premium" class="post-card__badge"><i class="pi pi-lock"></i></span>
                                <span v-if="post.es_temporal" class="post-card__badge"
                                    style="background: #fbbf24; color: #000;">
                                    <i class="pi pi-spin pi-spinner"></i>
                                </span>
                                <button v-if="post.usuario_id === usuario.id || usuario.rol === 'admin'"
                                    class="post-card__delete" @click="eliminarPublicacion(post.id)" title="Eliminar">
                                    <i class="pi pi-trash"></i>
                                </button>
                            </div>

                            <!-- Texto del post -->
                            <p class="post-card__text">{{ post.texto }}</p>

                            <!-- Media del post -->
                            <div v-if="post.media_url" class="post-card__media-wrapper"
                                :class="{ 'post-card__media-wrapper--premium': post.premium }">
                                <video v-if="post.media_type === 'video'" :src="post.media_url" controls
                                    class="post-card__video" poster="/images/video-placeholder.jpg" preload="metadata">
                                    Tu navegador no soporta la reproducción de videos.
                                </video>

                                <img v-else-if="post.media_type === 'imagen'" :src="post.media_url" :alt="post.autor"
                                    class="post-card__image" loading="lazy" />

                                <div v-if="post.premium" class="premium-overlay">
                                    <span class="premium-overlay__lock"><i class="pi pi-lock"></i></span>
                                    <strong>Exclusivo</strong>
                                    <Button label="SUSCRIBIRSE" icon="pi pi-shopping-cart" iconPos="right" />
                                </div>
                            </div>

                            <!-- Acciones del post -->
                            <div class="post-card__actions">
                                <button @click="darLike(post.id)" :class="{ 'liked': post.liked }">
                                    <i class="pi" :class="post.liked ? 'pi-heart-fill' : 'pi-heart'"></i>
                                    <span>{{ post.likes || 0 }}</span>
                                </button>

                                <button @click="toggleComentarios(post.id)">
                                    <i class="pi pi-comment"></i>
                                    <span>{{ post.comentarios || 0 }}</span>
                                </button>

                                <button @click="compartirPublicacion(post.id)">
                                    <i class="pi pi-share-alt"></i>
                                </button>
                            </div>

                            <!-- Sección de comentarios -->
                            <div v-if="comentariosVisibles[post.id]" class="post-card__comments">
                                <div class="comments-section">
                                    <div class="comment-input-wrapper">
                                        <InputText v-model="nuevoComentario[post.id]"
                                            placeholder="Escribe un comentario..." class="comment-input"
                                            @keyup.enter="comentar(post.id)" />
                                        <Button label="Comentar" class="comment-submit" @click="comentar(post.id)"
                                            size="small" :loading="comentando[post.id]" />
                                    </div>
                                    <div class="comments-list">
                                        <div v-if="post.comentarios_list && post.comentarios_list.length > 0">
                                            <div v-for="com in post.comentarios_list" :key="com.id"
                                                class="comment-item">
                                                <AvatarCustom :image="com.avatar || '/images/shared/avatar-default.jpg'"
                                                    :label="com.usuario ? com.usuario.charAt(0).toUpperCase() : '?'"
                                                    size="small" />
                                                <div class="comment-content">
                                                    <span class="comment-author">{{ com.usuario }}</span>
                                                    <span class="comment-text">{{ com.texto }}</span>
                                                    <span class="comment-time">{{ com.tiempo }}</span>
                                                    <button
                                                        v-if="com.usuario_id === usuario.id || usuario.rol === 'admin'"
                                                        class="comment-delete"
                                                        @click="eliminarComentario(post.id, com.id)"
                                                        title="Eliminar comentario">
                                                        <i class="pi pi-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="no-comments">
                                            <p>Sin comentarios</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

                <!-- SIDEBAR -->
                <aside class="sidebar-column">
                    <!-- 🔥 BOTÓN CREADORES - CON MISMO ESTILO QUE LAS DEMÁS TARJETAS -->
                    <div class="sidebar-card creadores-card">
                        <div class="sidebar-card__header">
                            <h3>
                                <i class="pi pi-users" style="color: var(--brand); margin-right: 0.5rem;"></i>
                                Creadores
                            </h3>
                        </div>
                        <div class="creadores-card__content">
                            <p class="creadores-card__desc">
                                Explora contenido exclusivo de creadores verificados
                            </p>
                            <Button label="Ir a comunidad de creadores" icon="pi pi-arrow-right" iconPos="right"
                                class="creadores-card__btn" @click="irAComunidadCreadores" />
                        </div>
                    </div>

                    <!-- Monetiza tu contenido - SOLO PARA USUARIOS NO CREADORES -->
                    <div v-if="usuario.rol !== 'creador' && usuario.rol !== 'admin'" class="monetize-card">
                        <img src="/images/creador.png" alt="Monetiza tu contenido" class="monetize-card__image" />
                        <div class="monetize-card__overlay"></div>
                        <div class="monetize-card__content">
                            <span class="monetize-card__icon"><i class="pi pi-crown"></i></span>
                            <h3>Monetiza tu contenido</h3>
                            <p>Conviértete en creador y genera ingresos con tus suscriptores.</p>
                            <Button label="SER CREADOR" icon="pi pi-wallet" iconPos="right" @click="irACreador"
                                class="btn-creator" />
                        </div>
                    </div>

                    <!-- Próximos eventos -->
                    <div class="sidebar-card eventos-card">
                        <div class="sidebar-card__header">
                            <h3>
                                <i class="pi pi-calendar" style="color: var(--brand); margin-right: 0.5rem;"></i>
                                Próximos eventos
                            </h3>
                            <a href="#" class="see-all">Ver todos <i class="pi pi-chevron-right"></i></a>
                        </div>
                        <div class="event-list">
                            <div v-if="proximosEventos.length === 0" class="empty-state">
                                <i class="pi pi-calendar"
                                    style="font-size: 1.5rem; color: #ccc; margin-bottom: 0.5rem;"></i>
                                <p>No hay eventos próximos.</p>
                            </div>
                            <div v-for="e in proximosEventos" :key="e.id" class="event-item">
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
                                                e.fecha_hora }} hrs
                                        </span>
                                        <span v-if="e.asistentes !== undefined" class="event-item__asistentes">
                                            <i class="pi pi-users"></i> {{ e.asistentes }} asistentes
                                            <span v-if="e.capacidad > 0"> / {{ e.capacidad }} cupos</span>
                                        </span>
                                    </div>
                                    <Button label="Ver evento" icon="pi pi-arrow-right" iconPos="right"
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
import { reactive, computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import { useConfirm } from '@/composables/useConfirm';

// Importaciones de PrimeVue
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Badge from 'primevue/badge';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';

// Importar componentes
import AvatarCustom from '@/Components/AvatarCustom.vue';
import ToastNotification from '@/Components/ToastNotification.vue';

// Obtener datos desde Inertia
const page = usePage();
const { confirm } = useConfirm();

// ============================================================
// REFERENCIAS PARA TOAST
// ============================================================
const toastRef = ref(null);

// ============================================================
// PUBLICACIONES
// ============================================================
const publicaciones = ref(page.props.publicaciones || []);

// ============================================================
// FILTROS Y BÚSQUEDA
// ============================================================
const filtroBusqueda = ref('');
const filtroOrden = ref({ label: 'Más recientes', value: 'reciente' });

const opcionesOrden = [
    { label: 'Más recientes', value: 'reciente' },
    { label: 'Más antiguos', value: 'antiguo' },
    { label: 'Más populares', value: 'popular' },
    { label: 'Más comentados', value: 'comentado' },
];

// ============================================================
// FUNCIÓN PARA FORMATEAR TIEMPO EN ESPAÑOL
// ============================================================
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

// ============================================================
// PUBLICACIONES FILTRADAS
// ============================================================
const publicacionesFiltradas = computed(() => {
    let resultado = [...publicaciones.value];

    if (filtroBusqueda.value.trim()) {
        const busqueda = filtroBusqueda.value.toLowerCase().trim();
        resultado = resultado.filter(post =>
            post.autor.toLowerCase().includes(busqueda) ||
            post.texto.toLowerCase().includes(busqueda) ||
            post.rol.toLowerCase().includes(busqueda)
        );
    }

    switch (filtroOrden.value.value) {
        case 'reciente':
            resultado.sort((a, b) => {
                if (a.es_temporal && !b.es_temporal) return -1;
                if (!a.es_temporal && b.es_temporal) return 1;
                return new Date(b.created_at || b.id) - new Date(a.created_at || a.id);
            });
            break;
        case 'antiguo':
            resultado.sort((a, b) => new Date(a.created_at || a.id) - new Date(b.created_at || b.id));
            break;
        case 'popular':
            resultado.sort((a, b) => (b.likes || 0) - (a.likes || 0));
            break;
        case 'comentado':
            resultado.sort((a, b) => (b.comentarios || 0) - (a.comentarios || 0));
            break;
        default:
            break;
    }

    return resultado;
});

// ============================================================
// ESTADO PARA COMENTARIOS
// ============================================================
const comentariosVisibles = ref({});
const nuevoComentario = ref({});
const comentando = ref({});

function toggleComentarios(postId) {
    comentariosVisibles.value[postId] = !comentariosVisibles.value[postId];
    if (!nuevoComentario.value[postId]) {
        nuevoComentario.value[postId] = '';
    }
}

// ============================================================
// ✅ SINCRONIZACIÓN EN VIVO — para ver likes/comentarios NUEVOS de
// OTROS usuarios sin recargar la página. No agrega ningún endpoint
// nuevo: reutiliza la misma acción index() del controlador que ya
// carga esta página, pero como un "partial reload" de Inertia que
// solo trae de vuelta la prop 'publicaciones' (no vuelve a montar el
// componente ni pierde el estado de la UI: paneles de comentarios
// abiertos, texto que estás escribiendo, etc. quedan intactos porque
// esos viven en refs aparte, no en el array de publicaciones).
// ============================================================
const INTERVALO_SYNC_MS = 12000; // cada 12s — suficiente para sentirse "en vivo" sin saturar al servidor
let intervaloSync = null;
const sincronizando = ref(false);

function mergePublicacionesServidor(nuevas) {
    if (!Array.isArray(nuevas)) return;

    nuevas.forEach((nueva) => {
        const local = publicaciones.value.find(p => p.id === nueva.id);

        if (!local) {
            // Publicación nueva de otro usuario que no estaba en pantalla:
            // se agrega al principio del feed.
            publicaciones.value.unshift(nueva);
            return;
        }

        // Solo se actualizan los campos que pueden cambiar por la
        // actividad de OTROS usuarios (likes, conteo y lista de
        // comentarios) — todo lo demás del post local se deja intacto,
        // y sobre todo, nunca se pisa mientras el usuario tiene una
        // publicación "es_temporal" en optimista (esa se resuelve sola
        // cuando el POST de crearla responde).
        if (local.es_temporal) return;

        local.likes = nueva.likes;
        // 'liked' (si YO le di like) también se resincroniza — cubre el
        // caso de que hayas dado like desde otra pestaña/dispositivo.
        local.liked = nueva.liked;
        local.comentarios = nueva.comentarios;

        // Comentarios: se agregan los que falten (por id), sin duplicar
        // ni reordenar los que ya tenías cargados/leídos en pantalla.
        if (Array.isArray(nueva.comentarios_list)) {
            const idsLocales = new Set((local.comentarios_list || []).map(c => c.id));
            const nuevosComentarios = nueva.comentarios_list.filter(c => !idsLocales.has(c.id));
            if (nuevosComentarios.length > 0) {
                local.comentarios_list = [...nuevosComentarios, ...(local.comentarios_list || [])];
            }
        }
    });

    // Si alguien eliminó una publicación desde otra sesión, se retira
    // del feed local (a menos que sea una que sigue en estado optimista).
    const idsServidor = new Set(nuevas.map(p => p.id));
    publicaciones.value = publicaciones.value.filter(p => p.es_temporal || idsServidor.has(p.id));
}

function sincronizarPublicaciones() {
    if (sincronizando.value || document.hidden) return; // no pedir de más si la pestaña no está visible

    sincronizando.value = true;
    router.reload({
        only: ['publicaciones'],
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            mergePublicacionesServidor(page.props.publicaciones);
        },
        onFinish: () => {
            sincronizando.value = false;
        },
    });
}

function iniciarSincronizacion() {
    detenerSincronizacion();
    intervaloSync = setInterval(sincronizarPublicaciones, INTERVALO_SYNC_MS);
}

function detenerSincronizacion() {
    if (intervaloSync) {
        clearInterval(intervaloSync);
        intervaloSync = null;
    }
}

// Pausa el polling si el usuario cambia de pestaña, y sincroniza de
// inmediato al volver (para no hacerlo esperar hasta el próximo tick).
function alCambiarVisibilidad() {
    if (document.hidden) {
        detenerSincronizacion();
    } else {
        sincronizarPublicaciones();
        iniciarSincronizacion();
    }
}

// ============================================================
// REFERENCIAS PARA LOS INPUTS FILE
// ============================================================
const imageInput = ref(null);
const videoInput = ref(null);

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
const usuario = computed(() => {
    const user = page.props.usuario || {};
    let avatar = user.avatar || '/images/shared/avatar-default.jpg';

    if (avatar && !avatar.startsWith('http') && !avatar.startsWith('/')) {
        avatar = '/storage/' + avatar;
    }

    return {
        id: user.id || null,
        nombre: user.nombre || 'Invitado',
        apodo: user.apodo || 'Invitado',
        email: user.email || '',
        avatar: avatar,
        verificado: user.verificado || false,
        rol: user.rol || 'invitado',
        tiene_perfil: user.tiene_perfil || false,
    };
});

// ============================================================
// MÉTRICAS
// ============================================================
const metricas = computed(() => {
    const data = page.props.metricas || [];
    if (data.length > 0) {
        return data.filter(m => m.titulo !== 'Grupos privados');
    }
    return [
        { icon: 'pi-wave-pulse', titulo: 'Feed activo', desc: 'Publicaciones, fotos y conversaciones nuevas cada minuto.', valor: '0', etiqueta: 'cargando...' },
        { icon: 'pi-users', titulo: 'Creadores', desc: 'Comparte y accede a contenido exclusivo de creadores verificados.', valor: '0', etiqueta: 'cargando...' },
        { icon: 'pi-calendar', titulo: 'Eventos próximos', desc: 'Eventos exclusivos para la comunidad.', valor: '0', etiqueta: 'cargando...' },
    ];
});

// ============================================================
// PRÓXIMOS EVENTOS
// ============================================================
const proximosEventos = computed(() => page.props.proximosEventos || []);

// ============================================================
// ESTADO PARA NUEVA PUBLICACIÓN
// ============================================================
const nuevaPublicacion = reactive({
    texto: '',
    imagen: null,
    video: null,
    es_premium: false,
    tipo_media: null,
    previewUrl: null,
});

// ============================================================
// FUNCIONES DE NAVEGACIÓN
// ============================================================
function irACreador() {
    console.log('🔵 Click en SER CREADOR');
    console.log('📋 Usuario:', usuario.value);
    console.log('📋 Rol del usuario:', usuario.value.rol);

    if (!usuario.value.id) {
        showError('Debes iniciar sesión para acceder a esta sección');
        if (typeof route !== 'undefined' && route('login')) {
            router.get(route('login'));
        } else {
            window.location.href = '/login';
        }
        return;
    }

    let destino = '';
    const esCreador = usuario.value.rol === 'creador' || usuario.value.rol === 'admin';

    if (esCreador) {
        destino = '/creador/comunidad';
        console.log('🟢 Usuario es creador/admin, redirigiendo a:', destino);
    } else {
        destino = '/creador';
        console.log('🟡 Usuario no es creador, redirigiendo a:', destino);
    }

    try {
        if (esCreador) {
            if (typeof route !== 'undefined' && route('creador.comunidad')) {
                router.get(route('creador.comunidad'));
                return;
            }
        } else {
            if (typeof route !== 'undefined' && route('creador.index')) {
                router.get(route('creador.index'));
                return;
            }
        }
        console.warn('⚠️ route() no disponible, usando navegación directa');
        window.location.href = destino;
    } catch (error) {
        console.error('❌ Error al redirigir:', error);
        window.location.href = destino;
    }
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

// ============================================================
// WATCH PARA ACTUALIZAR PREVISUALIZACIÓN
// ============================================================
watch(() => nuevaPublicacion.imagen, (newVal) => {
    if (newVal) {
        nuevaPublicacion.previewUrl = URL.createObjectURL(newVal);
        nuevaPublicacion.tipo_media = 'imagen';
    }
});

watch(() => nuevaPublicacion.video, (newVal) => {
    if (newVal) {
        nuevaPublicacion.previewUrl = URL.createObjectURL(newVal);
        nuevaPublicacion.tipo_media = 'video';
    }
});

// ============================================================
// FUNCIÓN PARA PUBLICAR
// ============================================================
function publicar() {
    if (!nuevaPublicacion.texto.trim() && !nuevaPublicacion.imagen && !nuevaPublicacion.video) {
        showError('Escribe algo o adjunta un archivo para publicar');
        return;
    }

    const formData = new FormData();
    formData.append('texto', nuevaPublicacion.texto);
    formData.append('es_premium', nuevaPublicacion.es_premium ? 1 : 0);

    let tempMediaUrl = null;
    let tempMediaType = 'texto';

    if (nuevaPublicacion.imagen) {
        formData.append('imagen', nuevaPublicacion.imagen);
        formData.append('tipo_media', 'imagen');
        tempMediaUrl = URL.createObjectURL(nuevaPublicacion.imagen);
        tempMediaType = 'imagen';
    }

    if (nuevaPublicacion.video) {
        formData.append('video', nuevaPublicacion.video);
        formData.append('tipo_media', 'video');
        tempMediaUrl = URL.createObjectURL(nuevaPublicacion.video);
        tempMediaType = 'video';
    }

    const tempPost = {
        id: Date.now(),
        autor: usuario.value.nombre,
        rol: usuario.value.rol === 'creador' ? 'Creador' : 'Usuario',
        avatar: usuario.value.avatar,
        tiempo: 'Hace un momento',
        texto: nuevaPublicacion.texto,
        media_url: tempMediaUrl,
        media_type: tempMediaType,
        imagen: tempMediaUrl,
        likes: 0,
        liked: false,
        comentarios: 0,
        comentarios_list: [],
        premium: nuevaPublicacion.es_premium,
        verificado: usuario.value.verificado,
        es_temporal: true,
        created_at: new Date().toISOString(),
        usuario_id: usuario.value.id,
    };

    publicaciones.value.unshift(tempPost);

    nuevaPublicacion.texto = '';
    nuevaPublicacion.imagen = null;
    nuevaPublicacion.video = null;
    nuevaPublicacion.tipo_media = null;
    nuevaPublicacion.previewUrl = null;
    if (imageInput.value) imageInput.value = '';
    if (videoInput.value) videoInput.value = '';

    showSuccess('Publicando...');

    router.post(route('comunidad.publicar'), formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
            const realPosts = response.props?.publicaciones;
            if (realPosts && realPosts.length > 0) {
                const realPost = realPosts[0];
                const index = publicaciones.value.findIndex(p => p.id === tempPost.id || p.es_temporal === true);
                if (index !== -1) {
                    publicaciones.value[index] = {
                        ...realPost,
                        id: realPost.id || tempPost.id,
                        es_temporal: false,
                    };
                    showSuccess('Publicación creada correctamente');
                } else {
                    router.reload();
                }
            } else {
                router.reload();
            }
        },
        onError: (errors) => {
            const index = publicaciones.value.findIndex(p => p.id === tempPost.id || p.es_temporal === true);
            if (index !== -1) {
                publicaciones.value.splice(index, 1);
            }
            if (tempMediaUrl) {
                URL.revokeObjectURL(tempMediaUrl);
            }
            console.error('Error al publicar:', errors);
            const errorMsg = errors?.imagen || errors?.video || errors?.texto || 'Error al publicar';
            showError(typeof errorMsg === 'string' ? errorMsg : 'Error al publicar');
        }
    });
}

// ============================================================
// FUNCIÓN PARA DAR LIKE
// ============================================================
function darLike(publicacionId) {
    const post = publicaciones.value.find(p => p.id === publicacionId);
    if (!post) return;

    const nuevoLike = !post.liked;
    const likesActuales = post.likes;

    post.liked = nuevoLike;
    post.likes = nuevoLike ? post.likes + 1 : post.likes - 1;

    axios.post(route('comunidad.like', publicacionId))
        .then(response => {
            const data = response.data;
            if (data.success) {
                post.likes = data.likes;
                post.liked = data.liked;
            }
        })
        .catch(error => {
            post.liked = !nuevoLike;
            post.likes = likesActuales;
            showError('No se pudo procesar el like');
            console.error('Error al dar like:', error);
        });
}

// ============================================================
// FUNCIÓN PARA ELIMINAR PUBLICACIÓN - CON ConfirmModal
// ============================================================
function eliminarPublicacion(postId) {
    const post = publicaciones.value.find(p => p.id === postId);
    if (!post) return;

    const index = publicaciones.value.findIndex(p => p.id === postId);
    if (index === -1) return;

    confirm(
        '¿Estás seguro de que quieres eliminar esta publicación? Esta acción no se puede deshacer.',
        {
            title: 'Eliminar publicación',
            confirmLabel: 'Sí, eliminar',
            cancelLabel: 'Cancelar',
            danger: true,
        }
    ).then((confirmed) => {
        if (confirmed) {
            const postBackup = { ...post };
            publicaciones.value.splice(index, 1);
            showInfo('Eliminando publicación...');

            axios.delete(route('comunidad.eliminar', postId))
                .then(response => {
                    if (response.data.success) {
                        showSuccess('Publicación eliminada correctamente');
                    } else {
                        publicaciones.value.splice(index, 0, postBackup);
                        showError('No se pudo eliminar la publicación');
                    }
                })
                .catch(error => {
                    publicaciones.value.splice(index, 0, postBackup);
                    showError('Error al eliminar la publicación');
                    console.error('Error al eliminar:', error);
                });
        }
    });
}

// ============================================================
// FUNCIÓN PARA ELIMINAR COMENTARIO - CON ConfirmModal
// ============================================================
function eliminarComentario(postId, comentarioId) {
    const post = publicaciones.value.find(p => p.id === postId);
    if (!post) return;

    const index = post.comentarios_list.findIndex(c => c.id === comentarioId);
    if (index === -1) return;

    confirm(
        '¿Estás seguro de que quieres eliminar este comentario?',
        {
            title: 'Eliminar comentario',
            confirmLabel: 'Sí, eliminar',
            cancelLabel: 'Cancelar',
            danger: true,
        }
    ).then((confirmed) => {
        if (confirmed) {
            const comentarioBackup = { ...post.comentarios_list[index] };
            post.comentarios_list.splice(index, 1);
            post.comentarios = post.comentarios_list.length;
            showInfo('Comentario eliminado');

            // Llamada a la API para eliminar el comentario
            axios.delete(route('comunidad.eliminar-comentario', comentarioId))
                .catch(error => {
                    post.comentarios_list.splice(index, 0, comentarioBackup);
                    post.comentarios = post.comentarios_list.length;
                    showError('Error al eliminar el comentario');
                    console.error('Error al eliminar comentario:', error);
                });
        }
    });
}

// ============================================================
// FUNCIÓN PARA COMENTAR
// ============================================================
function comentar(publicacionId) {
    const texto = nuevoComentario.value[publicacionId];
    if (!texto || !texto.trim()) {
        showError('Escribe un comentario');
        return;
    }

    comentando.value[publicacionId] = true;

    axios.post(route('comunidad.comentar', publicacionId), { texto: texto })
        .then(response => {
            const data = response.data;
            if (data.success) {
                const post = publicaciones.value.find(p => p.id === publicacionId);
                if (post) {
                    post.comentarios_list.unshift({
                        ...data.comentario,
                        usuario_id: data.comentario.usuario_id || usuario.value.id
                    });
                    post.comentarios = data.total_comentarios;
                    nuevoComentario.value[publicacionId] = '';
                }
                showSuccess(data.message);
            }
            comentando.value[publicacionId] = false;
        })
        .catch(error => {
            comentando.value[publicacionId] = false;
            showError('No se pudo agregar el comentario');
            console.error('Error al comentar:', error);
        });
}

// ============================================================
// FUNCIÓN PARA COMPARTIR
// ============================================================
function compartirPublicacion(postId) {
    const url = window.location.origin + '/comunidad/publicacion/' + postId;

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
// FUNCIONES PARA MANEJAR ARCHIVOS
// ============================================================
function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 10 * 1024 * 1024) {
        showError('La imagen no debe superar los 10MB');
        event.target.value = '';
        return;
    }
    if (!file.type.startsWith('image/')) {
        showError('El archivo debe ser una imagen');
        event.target.value = '';
        return;
    }

    if (nuevaPublicacion.video) {
        nuevaPublicacion.video = null;
        if (videoInput.value) videoInput.value = '';
    }

    nuevaPublicacion.imagen = file;
    nuevaPublicacion.tipo_media = 'imagen';
}

function handleVideoUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 50 * 1024 * 1024) {
        showError('El video no debe superar los 50MB');
        event.target.value = '';
        return;
    }
    const validTypes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-ms-wmv', 'video/x-flv', 'video/webm', 'video/mkv'];
    if (!validTypes.includes(file.type)) {
        showError('Formato de video no soportado. Usa MP4, AVI, MOV, WMV, FLV, WEBM o MKV');
        event.target.value = '';
        return;
    }

    if (nuevaPublicacion.imagen) {
        nuevaPublicacion.imagen = null;
        if (imageInput.value) imageInput.value = '';
    }

    nuevaPublicacion.video = file;
    nuevaPublicacion.tipo_media = 'video';
}

function eliminarMedia() {
    if (nuevaPublicacion.previewUrl) {
        URL.revokeObjectURL(nuevaPublicacion.previewUrl);
    }
    nuevaPublicacion.imagen = null;
    nuevaPublicacion.video = null;
    nuevaPublicacion.tipo_media = null;
    nuevaPublicacion.previewUrl = null;
    if (imageInput.value) imageInput.value = '';
    if (videoInput.value) videoInput.value = '';
}

// ============================================================
// FUNCIONES UTILES
// ============================================================
function getInitial(name) {
    if (!name) return '?';
    return name.charAt(0).toUpperCase();
}

function getAvatarUrl(avatar) {
    if (!avatar) return '/images/shared/avatar-default.jpg';
    if (!avatar.startsWith('http') && !avatar.startsWith('/')) {
        return '/storage/' + avatar;
    }
    return avatar;
}

// ============================================================
// ON MOUNTED
// ============================================================
onMounted(() => {
    console.log('=== 🚀 DATOS DE COMUNIDAD ===');
    console.log('Publicaciones:', publicaciones.value);
    console.log('Usuario:', usuario.value);
    console.log('Eventos:', proximosEventos.value);
    console.log('🔍 Route creador.index disponible?', typeof route !== 'undefined' && route('creador.index'));
    console.log('🔍 Route creador.comunidad disponible?', typeof route !== 'undefined' && route('creador.comunidad'));

    // ✅ Arranca la sincronización en vivo de likes/comentarios
    iniciarSincronizacion();
    document.addEventListener('visibilitychange', alCambiarVisibilidad);
});

onUnmounted(() => {
    detenerSincronizacion();
    document.removeEventListener('visibilitychange', alCambiarVisibilidad);
});
</script>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA
   ========================================================================= */
.comunidad-page {
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
}

/* =========================================================================
   HERO
   ========================================================================= */
.hero {
    max-width: 1400px;
    margin: 1.5rem auto 0;
    padding: 0 2rem;
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
   QUICK STATS
   ========================================================================= */
.quick-stats {
    max-width: 1400px;
    margin: 1.25rem auto 0;
    padding: 0 2rem;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

.stat-card {
    background: #ffffff;
    border-radius: var(--radius-md);
    padding: 0.8rem 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    transition: all 0.3s ease;
    cursor: default;
    box-shadow: var(--shadow);
    border: 1px solid var(--line);
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-hover);
}

.stat-card__icon {
    width: 34px;
    height: 34px;
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

.stat-card:hover .stat-card__icon {
    background: var(--brand);
    color: var(--white);
    transform: scale(1.05);
}

.stat-card__body {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    flex: 1;
}

.stat-card__title {
    font-weight: 600;
    font-size: 0.8rem;
}

.stat-card__desc {
    font-size: 0.7rem;
    color: var(--muted);
    line-height: 1.3;
}

.stat-card__value {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    flex-shrink: 0;
}

.stat-card__value .value {
    font-size: 1rem;
    font-weight: 800;
    color: var(--brand);
}

.stat-card__value .label {
    font-size: 0.55rem;
    color: var(--muted-light);
}

/* =========================================================================
   FILTROS
   ========================================================================= */
.filters-section {
    max-width: 1400px;
    margin: 1.25rem auto 0;
    padding: 0 2rem;
}

.filters-container {
    display: flex;
    gap: 0.8rem;
    align-items: center;
    background: #ffffff;
    padding: 0.6rem 1rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
    flex-wrap: wrap;
}

.search-wrapper {
    flex: 1;
    position: relative;
    display: flex;
    align-items: center;
    min-width: 180px;
}

.search-icon {
    position: absolute;
    left: 10px;
    color: var(--muted-light);
    font-size: 0.8rem;
}

.search-input {
    width: 100%;
    padding: 0.4rem 2rem 0.4rem 2.2rem;
    border-radius: 8px;
    border: 1px solid var(--line);
    font-size: 0.8rem;
    transition: all 0.2s ease;
    background: var(--surface);
}

.search-input:focus {
    border-color: var(--brand);
    outline: none;
    box-shadow: 0 0 0 2px rgba(200, 30, 58, 0.1);
}

.clear-search {
    position: absolute;
    right: 8px;
    background: none;
    border: none;
    color: var(--muted-light);
    cursor: pointer;
    padding: 4px;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.clear-search:hover {
    color: var(--brand);
    background: var(--brand-soft);
}

.order-wrapper {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    min-width: 160px;
}

.order-icon {
    color: var(--muted-light);
    font-size: 0.8rem;
}

.order-dropdown {
    width: 100%;
}

.order-dropdown :deep(.p-dropdown) {
    border: 1px solid var(--line);
    border-radius: 8px;
    background: var(--surface);
    padding: 0.3rem 0.6rem;
    font-size: 0.8rem;
}

.order-dropdown :deep(.p-dropdown:hover) {
    border-color: var(--brand);
}

.order-dropdown :deep(.p-dropdown:focus) {
    border-color: var(--brand);
    box-shadow: 0 0 0 2px rgba(200, 30, 58, 0.1);
}

.search-results-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.4rem 0;
    font-size: 0.8rem;
    color: var(--muted);
}

.results-count {
    font-weight: 600;
    color: var(--brand);
}

/* =========================================================================
   CONTENT GRID
   ========================================================================= */
.content-grid {
    max-width: 1400px;
    margin: 1.25rem auto 0;
    padding: 0 2rem 3rem;
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.5rem;
    align-items: start;
}

.feed-column,
.sidebar-column {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* =========================================================================
   POSTS GRID - 2 COLUMNAS
   ========================================================================= */
.posts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

/* =========================================================================
   COMPOSER
   ========================================================================= */
.composer-card {
    background: #ffffff;
    border-radius: var(--radius-md);
    padding: 1rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--line);
    grid-column: 1 / -1;
}

.composer-card__header {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.7rem;
}

.composer-card__header strong {
    font-size: 0.85rem;
}

.composer-card__input {
    width: 100%;
    border: 1px solid #e3e3e7;
    border-radius: 8px;
    padding: 0.6rem 0.8rem;
    font-family: inherit;
    font-size: 0.8rem;
    resize: none;
    color: var(--ink);
    transition: all 0.2s ease;
}

.composer-card__input:focus {
    border-color: var(--brand);
    outline: none;
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.08);
}

.composer-card__preview {
    position: relative;
    margin-top: 0.6rem;
    border-radius: 10px;
    overflow: hidden;
    background: #f0f0f0;
}

.composer-card__preview-image {
    width: 100%;
    height: auto;
    max-height: 350px;
    object-fit: contain;
    display: block;
    background: #f0f0f0;
}

.composer-card__preview-video {
    width: 100%;
    max-height: 350px;
    display: block;
    background: #000;
}

.composer-card__remove-image {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    z-index: 10;
}

.composer-card__remove-image:hover {
    background: rgba(200, 30, 58, 0.9);
    transform: scale(1.1);
}

.composer-card__actions {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 0.7rem;
    flex-wrap: wrap;
}

.composer-action {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    border: 1px solid #e3e3e7;
    border-radius: 8px;
    background: #fff;
    padding: 0.3rem 0.6rem;
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--ink-soft);
    cursor: pointer;
    transition: all 0.2s ease;
}

.composer-action:hover {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
}

.composer-card__submit {
    margin-left: auto;
    font-weight: 700;
    border-radius: 8px;
    font-size: 0.75rem;
    padding: 0.4rem 1rem;
}

/* =========================================================================
   POST CARD
   ========================================================================= */
.post-card {
    background: #ffffff;
    border-radius: var(--radius-md);
    padding: 0.8rem;
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
    border: 1px solid var(--line);
    display: flex;
    flex-direction: column;
}

.post-card:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-2px);
}

.post-card__header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.post-card__author {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
    flex: 1;
    min-width: 0;
}

.post-card__author .name {
    font-size: 0.8rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.post-card__author .name i {
    color: var(--brand);
    font-size: 0.65rem;
}

.post-card__author .rol {
    font-size: 0.6rem;
    color: var(--brand);
    font-weight: 600;
}

.post-card__time {
    font-size: 0.6rem;
    color: var(--muted-light);
    flex-shrink: 0;
}

.post-card__badge {
    background: var(--brand-soft);
    color: var(--brand);
    font-size: 0.55rem;
    font-weight: 700;
    padding: 0.1rem 0.4rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.2rem;
    flex-shrink: 0;
}

.post-card__delete {
    border: none;
    background: none;
    color: var(--muted-light);
    cursor: pointer;
    padding: 2px 4px;
    border-radius: 4px;
    transition: all 0.2s ease;
    font-size: 0.65rem;
    flex-shrink: 0;
}

.post-card__delete:hover {
    color: #dc2626;
    background: #FEE2E2;
}

.post-card__text {
    font-size: 0.8rem;
    color: var(--ink-soft);
    line-height: 1.5;
    margin: 0 0 0.6rem;
    white-space: pre-wrap;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.post-card__media-wrapper {
    position: relative;
    margin: 0 -0.8rem;
    border-radius: 0;
    overflow: hidden;
    background: #f0f0f0;
}

.post-card__media-wrapper--premium {
    position: relative;
}

.post-card__image {
    width: 100%;
    height: auto;
    max-height: 300px;
    object-fit: cover;
    display: block;
    background: #f0f0f0;
}

.post-card__video {
    width: 100%;
    max-height: 300px;
    display: block;
    background: #000;
}

.post-card__media-wrapper--premium .post-card__image,
.post-card__media-wrapper--premium .post-card__video {
    filter: blur(18px) brightness(0.7);
    transform: scale(1.02);
}

.premium-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #fff;
    gap: 0.2rem;
    padding: 0.8rem;
    background: rgba(0, 0, 0, 0.3);
}

.premium-overlay__lock {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 2px solid rgba(255, 255, 255, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    margin-bottom: 0.2rem;
}

.premium-overlay strong {
    font-size: 0.85rem;
}

.premium-overlay :deep(.p-button) {
    font-weight: 700;
    border-radius: 8px;
    font-size: 0.6rem;
    padding: 0.3rem 0.8rem;
}

/* =========================================================================
   POST ACTIONS
   ========================================================================= */
.post-card__actions {
    display: flex;
    gap: 1rem;
    margin-top: 0.6rem;
    padding-top: 0.5rem;
    border-top: 1px solid #f0f0f2;
}

.post-card__actions button {
    border: none;
    background: none;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.7rem;
    color: var(--ink-soft);
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s ease;
    padding: 0.15rem 0.3rem;
    border-radius: 4px;
}

.post-card__actions button:hover {
    color: var(--brand);
    background: var(--brand-soft);
}

.post-card__actions button span {
    color: var(--muted-light);
    font-weight: 400;
}

.post-card__actions button.liked {
    color: var(--brand);
}

.post-card__actions button.liked i.pi-heart-fill {
    color: var(--brand);
}

.post-card__actions button i.pi-heart-fill {
    color: var(--brand);
}

/* =========================================================================
   COMENTARIOS
   ========================================================================= */
.post-card__comments {
    margin-top: 0.6rem;
    padding-top: 0.6rem;
    border-top: 1px solid #f0f0f2;
}

.comments-section {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.comment-input-wrapper {
    display: flex;
    gap: 0.3rem;
    align-items: center;
}

.comment-input {
    flex: 1;
    padding: 0.3rem 0.5rem;
    border-radius: 6px;
    border: 1px solid var(--line);
    font-size: 0.7rem;
    transition: all 0.2s ease;
}

.comment-input:focus {
    border-color: var(--brand);
    outline: none;
    box-shadow: 0 0 0 2px rgba(200, 30, 58, 0.08);
}

.comment-submit {
    font-weight: 600;
    font-size: 0.6rem;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    background: var(--brand);
    color: #fff;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.comment-submit:hover {
    background: var(--brand-dark);
}

.comment-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.comments-list {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.comment-item {
    display: flex;
    gap: 0.3rem;
    align-items: flex-start;
    padding: 0.3rem 0.4rem;
    background: var(--surface);
    border-radius: 6px;
}

.comment-content {
    display: flex;
    flex-direction: column;
    flex: 1;
    position: relative;
}

.comment-author {
    font-weight: 700;
    font-size: 0.65rem;
    color: var(--ink);
}

.comment-text {
    font-size: 0.7rem;
    color: var(--ink-soft);
}

.comment-time {
    font-size: 0.55rem;
    color: var(--muted-light);
    margin-top: 0.1rem;
}

.comment-delete {
    border: none;
    background: none;
    color: var(--muted-light);
    cursor: pointer;
    padding: 2px 4px;
    border-radius: 4px;
    transition: all 0.2s ease;
    font-size: 0.55rem;
    position: absolute;
    top: 0;
    right: 0;
}

.comment-delete:hover {
    color: #dc2626;
    background: #FEE2E2;
}

.no-comments {
    text-align: center;
    color: var(--muted);
    font-size: 0.65rem;
    padding: 0.3rem;
}

/* =========================================================================
   SIDEBAR
   ========================================================================= */
.sidebar-card {
    background: #ffffff;
    border-radius: var(--radius-md);
    padding: 0.8rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--line);
}

.sidebar-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.6rem;
}

.sidebar-card__header h3 {
    font-size: 0.85rem;
    margin: 0;
    display: flex;
    align-items: center;
}

.see-all {
    color: var(--brand);
    font-size: 0.7rem;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    transition: all 0.3s ease;
}

.see-all:hover {
    color: var(--brand-dark);
    gap: 0.4rem;
}

/* =========================================================================
   CREADORES CARD - MISMO ESTILO QUE LAS DEMÁS
   ========================================================================= */
.creadores-card {
    background: #ffffff;
    border: 1px solid var(--line);
}

.creadores-card__content {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.creadores-card__desc {
    font-size: 0.7rem;
    color: var(--muted);
    margin: 0;
    line-height: 1.4;
}

.creadores-card__btn-link {
    color: var(--brand);
    font-size: 0.7rem;
    font-weight: 700;
    background: none;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    transition: all 0.3s ease;
    padding: 0;
}

.creadores-card__btn-link:hover {
    color: var(--brand-dark);
    gap: 0.4rem;
}

.creadores-card__btn {
    width: 100%;
    font-weight: 700 !important;
    border-radius: 8px !important;
    font-size: 0.7rem !important;
    padding: 0.4rem !important;
    background: var(--brand) !important;
    border-color: var(--brand) !important;
    color: #fff !important;
}

.creadores-card__btn:hover {
    background: var(--brand-dark) !important;
    border-color: var(--brand-dark) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(200, 30, 58, 0.3);
}

/* =========================================================================
   MONETIZE CARD
   ========================================================================= */
.monetize-card {
    position: relative;
    border-radius: var(--radius-md);
    overflow: hidden;
    min-height: 180px;
    display: flex;
    align-items: flex-end;
}

.monetize-card__image {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.monetize-card__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(0deg, rgba(0, 0, 0, 0.92) 30%, rgba(0, 0, 0, 0.2) 100%);
}

.monetize-card__content {
    position: relative;
    z-index: 2;
    padding: 0.8rem;
    color: #fff;
}

.monetize-card__icon {
    color: #f2c94c;
    font-size: 0.8rem;
    margin-bottom: 0.2rem;
    display: block;
}

.monetize-card__content h3 {
    font-size: 0.85rem;
    margin: 0 0 0.2rem;
}

.monetize-card__content p {
    font-size: 0.65rem;
    color: #d8d8dc;
    margin: 0 0 0.4rem;
    line-height: 1.4;
}

.monetize-card__content :deep(.p-button) {
    font-weight: 700;
    border-radius: 8px;
    font-size: 0.65rem;
    padding: 0.3rem 0.8rem;
}

.btn-creator :deep(.p-button) {
    background: var(--brand) !important;
    border-color: var(--brand) !important;
    color: #fff !important;
}

.btn-creator :deep(.p-button):hover {
    background: var(--brand-dark) !important;
    border-color: var(--brand-dark) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(200, 30, 58, 0.3);
}

/* =========================================================================
   EVENTOS
   ========================================================================= */
.eventos-card {
    background: #ffffff;
    border-radius: var(--radius-md);
    padding: 0.8rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--line);
}

.event-list {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.event-item {
    display: flex;
    gap: 0.8rem;
    padding: 0.6rem;
    border-radius: 10px;
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
    width: 80px;
    height: 80px;
    border-radius: 10px;
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
    gap: 0.2rem;
}

.event-item__header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.event-item__date {
    background: var(--brand-soft);
    color: var(--brand);
    border-radius: 6px;
    padding: 0.2rem 0.4rem;
    text-align: center;
    line-height: 1.05;
    flex-shrink: 0;
    min-width: 40px;
}

.event-item__date strong {
    display: block;
    font-size: 0.8rem;
}

.event-item__date span {
    font-size: 0.45rem;
    letter-spacing: 0.05em;
    font-weight: 600;
}

.event-item__title {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--ink);
}

.event-item__details {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.event-item__meta {
    font-size: 0.6rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 0.2rem;
}

.event-item__meta i {
    font-size: 0.55rem;
    color: var(--brand);
}

.event-item__asistentes {
    font-size: 0.55rem;
    color: var(--muted);
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
}

.event-item__asistentes i {
    font-size: 0.5rem;
    color: var(--brand);
}

.event-item__btn {
    font-size: 0.6rem !important;
    padding: 0.15rem 0 !important;
    color: var(--brand) !important;
    font-weight: 600 !important;
    align-self: flex-start;
}

.event-item__btn:hover {
    color: var(--brand-dark) !important;
    gap: 0.5rem !important;
}

.eventos-footer {
    margin-top: 0.6rem;
    padding-top: 0.6rem;
    border-top: 1px solid var(--line);
}

.explore-link {
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    color: var(--brand);
    font-size: 0.7rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
}

.explore-link:hover {
    color: var(--brand-dark);
    gap: 0.4rem;
}

/* =========================================================================
   EMPTY STATE
   ========================================================================= */
.empty-state {
    text-align: center;
    padding: 1.5rem 1rem;
    color: var(--muted);
    font-size: 0.8rem;
    background: #ffffff;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow);
    border: 1px solid var(--line);
}

.empty-state p {
    margin: 0;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
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

    .quick-stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .hero {
        padding: 0 1rem;
    }

    .filters-section {
        padding: 0 1rem;
    }

    .quick-stats {
        padding: 0 1rem;
    }

    .content-grid {
        padding: 0 1rem 2rem;
        grid-template-columns: 1fr;
    }

    .posts-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .event-item__image-wrapper {
        width: 70px;
        height: 70px;
    }
}

@media (max-width: 768px) {
    .quick-stats {
        grid-template-columns: 1fr;
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

    .filters-container {
        flex-direction: column;
        align-items: stretch;
    }

    .order-wrapper {
        min-width: 100%;
    }

    .composer-card__actions {
        flex-wrap: wrap;
    }

    .composer-card__submit {
        width: 100%;
        justify-content: center;
        margin-left: 0;
    }

    .post-card__actions {
        gap: 0.8rem;
    }

    .post-card__actions button {
        font-size: 0.65rem;
    }

    .comment-input-wrapper {
        flex-direction: column;
    }

    .comment-submit {
        width: 100%;
        justify-content: center;
    }

    .post-card__image,
    .post-card__video {
        max-height: 280px;
    }

    .composer-card__preview-image,
    .composer-card__preview-video {
        max-height: 220px;
    }

    .search-results-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.2rem;
    }

    .posts-grid {
        grid-template-columns: 1fr;
    }

    .event-item {
        flex-direction: column;
        align-items: stretch;
    }

    .event-item__image-wrapper {
        width: 100%;
        height: 140px;
    }

    .event-item__header {
        flex-wrap: wrap;
    }
}

@media (max-width: 480px) {
    .stat-card {
        padding: 0.6rem 0.8rem;
    }

    .post-card {
        padding: 0.6rem;
    }

    .sidebar-card {
        padding: 0.6rem;
    }

    .post-card__image,
    .post-card__video {
        max-height: 220px;
    }

    .composer-card__preview-image,
    .composer-card__preview-video {
        max-height: 180px;
    }

    .hero__title {
        font-size: 1.2rem;
    }

    .event-item__image-wrapper {
        height: 120px;
    }
}
</style>