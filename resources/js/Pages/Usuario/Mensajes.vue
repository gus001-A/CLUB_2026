<template>

    <Head title="Mensajes" />

    <AppLayout active-nav="mensajes" :usuario="usuario" :mensajes="chats.filter(c => c.no_leidos > 0).length"
        hide-footer>
        <div class="mensajes-page">
            <!-- ============================================================ -->
            <!-- COLUMNA: CONVERSACIONES -->
            <!-- ============================================================ -->
            <aside class="conversations-col">
                <div class="conversations-search">
                    <span class="search-input">
                        <i class="pi pi-search"></i>
                        <input v-model="busqueda" type="text" placeholder="Buscar conversaciones..." />
                    </span>
                </div>

                <div class="conversations-tabs">
                    <button class="tab-pill" :class="{ active: filtroActivo === 'todos' }"
                        @click="filtroActivo = 'todos'">Todos</button>
                    <button class="tab-pill" :class="{ active: filtroActivo === 'no-leidos' }"
                        @click="filtroActivo = 'no-leidos'">
                        No leídos <span v-if="chats.filter(c => c.no_leidos > 0).length" class="tab-badge">{{
                            chats.filter(c => c.no_leidos > 0).length}}</span>
                    </button>
                </div>

                <div class="conversations-list" ref="conversacionesList">
                    <p v-if="cargandoChats" class="conversations-empty">Cargando conversaciones...</p>
                    <p v-else-if="!conversacionesFiltradas.length" class="conversations-empty">No hay conversaciones.
                    </p>

                    <button v-for="conv in conversacionesFiltradas" :key="conv.id" class="conversation-item"
                        :class="{ active: conv.id === chatActivoId }" @click="seleccionarConversacion(conv.id)">
                        <div class="conversation-item__avatar">
                            <img :src="getAvatarUrl(conv.usuario?.avatar)" :alt="conv.usuario?.nombre" />
                            <span v-if="conv.usuario?.verificado" class="avatar-verified"><i
                                    class="pi pi-verified"></i></span>
                        </div>
                        <div class="conversation-item__body">
                            <div class="conversation-item__title-row">
                                <strong>{{ conv.usuario?.nombre }}</strong>
                                <span v-if="conv.enLinea" class="conversation-item__online">●</span>
                            </div>
                            <p class="conversation-item__preview">{{ conv.ultimo_mensaje || 'Sin mensajes aún' }}</p>
                        </div>
                        <span v-if="conv.no_leidos" class="conversation-item__badge">{{ conv.no_leidos }}</span>
                    </button>
                </div>
            </aside>

            <!-- ============================================================ -->
            <!-- COLUMNA: CHAT -->
            <!-- ============================================================ -->
            <section class="chat-col" v-if="chatActivoId && otroParticipante">
                <header class="chat-header">
                    <div class="chat-header__avatar-wrapper">
                        <img :src="getAvatarUrl(otroParticipante.avatar)" :alt="otroParticipante.nombre"
                            class="chat-header__avatar" />
                        <span v-if="otroParticipante.verificado" class="chat-header__verified"><i
                                class="pi pi-verified"></i></span>
                    </div>
                    <div class="chat-header__info">
                        <strong>{{ otroParticipante.nombre }}</strong>
                        <span v-if="otroEstaEscribiendo" class="chat-header__typing">escribiendo...</span>
                    </div>
                    <div class="chat-header__actions">
                        <button title="Llamada de audio" @click="llamarAudio"><i class="pi pi-phone"></i></button>
                        <button title="Videollamada" @click="llamarVideo"><i class="pi pi-video"></i></button>

                        <div class="chat-header__menu-wrap">
                            <button title="Más opciones" @click="mostrarMenuOpciones = !mostrarMenuOpciones">
                                <i class="pi pi-ellipsis-v"></i>
                            </button>
                            <Transition name="menu-fade">
                                <div v-if="mostrarMenuOpciones" class="chat-header__menu">
                                    <button class="chat-header__menu-item chat-header__menu-item--danger"
                                        @click="abrirModalReporte">
                                        <i class="pi pi-flag"></i> Reportar a {{ otroParticipante.nombre }}
                                    </button>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </header>

                <div class="chat-body" ref="mensajesEl">
                    <div v-for="msg in mensajes" :key="msg.id" class="message-row"
                        :class="msg.remitente_id === usuario.id ? 'message-row--mine' : 'message-row--theirs'">
                        <div class="message-avatar-wrapper">
                            <img v-if="msg.remitente_id !== usuario.id" :src="getAvatarUrl(msg.remitente?.avatar)"
                                class="message-row__avatar" alt="" />
                            <img v-else :src="getAvatarUrl(usuario.avatar)"
                                class="message-row__avatar message-row__avatar--mine" alt="Tú" />
                        </div>

                        <div class="message-content">
                            <div class="message-bubble"
                                :class="msg.remitente_id === usuario.id ? 'message-bubble--mine' : 'message-bubble--theirs'">
                                <p v-if="msg.tipo === 'texto'">{{ msg.texto }}</p>

                                <img v-else-if="msg.tipo === 'imagen'" :src="msg.archivo_url"
                                    class="message-bubble__imagen"
                                    @click="() => window.open(msg.archivo_url, '_blank')" />

                                <div v-else-if="msg.tipo === 'video'" class="message-bubble__video-wrap">
                                    <video :src="msg.archivo_url" controls preload="metadata"
                                        class="message-bubble__video"></video>
                                    <span class="message-bubble__duracion">{{ msg.duracion_formateada }}</span>
                                </div>

                                <audio v-else-if="msg.tipo === 'audio'" :src="msg.archivo_url" controls
                                    class="message-bubble__audio"></audio>
                            </div>

                            <div class="message-meta">
                                <span class="message-time">
                                    {{ new Date(msg.created_at).toLocaleTimeString('es-MX', {
                                        hour: '2-digit', minute: '2-digit'
                                    }) }}
                                </span>
                                <span v-if="msg.remitente_id === usuario.id" class="message-status">
                                    <i v-if="msg.leido" class="pi pi-check-circle read-check" title="Leído"></i>
                                    <i v-else class="pi pi-check sent-check" title="Enviado"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="progresoSubida !== null" class="upload-progress">
                    <div class="upload-progress__bar" :style="{ width: progresoSubida + '%' }"></div>
                    <span>Subiendo... {{ progresoSubida }}%</span>
                </div>
                <p v-if="errorAdjunto" class="upload-error"><i class="pi pi-exclamation-circle"></i> {{ errorAdjunto }}
                </p>

                <div class="chat-composer">
                    <button class="attach-btn" @click="abrirSelectorArchivo" title="Adjuntar foto o video">
                        <i class="pi pi-paperclip"></i>
                    </button>
                    <input ref="inputArchivo" type="file" accept="image/*,video/*" hidden
                        @change="alSeleccionarArchivo" />

                    <div class="composer-input-wrapper">
                        <input v-model="nuevoMensaje" type="text" placeholder="Escribe un mensaje..."
                            @input="alEscribir" @keyup.enter="enviar" class="composer-input" />
                        <div class="emoji-wrap">
                            <button class="emoji-btn" @click="mostrarEmojis = !mostrarEmojis"><i
                                    class="pi pi-face-smile"></i></button>
                            <div v-if="mostrarEmojis" class="emoji-popover">
                                <EmojiPicker @seleccionar="insertarEmoji" />
                            </div>
                        </div>
                    </div>

                    <button class="send-btn" :class="{ 'send-btn--active': tieneTexto }"
                        :disabled="!tieneTexto || enviando" @click="enviar">
                        <i class="pi" :class="enviando ? 'pi-spin pi-spinner' : 'pi-send'"></i>
                    </button>
                </div>
            </section>

            <section v-else class="chat-col chat-col--empty">
                <i class="pi pi-comments"></i>
                <p>Selecciona una conversación para empezar a chatear.</p>
            </section>
        </div>

        <!-- Modal de llamada -->
        <LlamadaModal :llamada="llamadaActiva" :estado="estadoLocal" :es-video="esVideo()" :stream-local="streamLocal"
            :stream-remoto="streamRemoto" :microfono-activo="microfonoActivo" :camara-activa="camaraActiva"
            :duracion-segundos="duracionSegundos" :otro-nombre="otroParticipante?.nombre ?? 'Usuario'"
            :otro-avatar="otroParticipante?.avatar ?? ''" @contestar="contestarLlamada" @rechazar="rechazarLlamada"
            @colgar="() => colgarLlamada('colgada')" @alternar-microfono="alternarMicrofono"
            @alternar-camara="alternarCamara" />

        <!-- ============================================================ -->
        <!-- MODAL: REPORTAR USUARIO -->
        <!-- ============================================================ -->
        <Transition name="modal-fade">
            <div v-if="modalReporte.visible" class="report-modal-overlay" @click.self="cerrarModalReporte">
                <div class="report-modal">
                    <button class="report-modal__close" @click="cerrarModalReporte"><i class="pi pi-times"></i></button>

                    <div class="report-modal__header">
                        <span class="report-modal__icon"><i class="pi pi-flag"></i></span>
                        <h2>Reportar a {{ otroParticipante?.nombre }}</h2>
                        <p>Selecciona el motivo que mejor describa el problema. Tu reporte es confidencial.</p>
                    </div>

                    <template v-if="!modalReporte.enviado">
                        <div class="report-modal__motivos">
                            <button v-for="motivo in motivosReporte" :key="motivo.valor" type="button"
                                class="report-motivo"
                                :class="{ 'report-motivo--selected': modalReporte.motivoSeleccionado === motivo.valor }"
                                @click="modalReporte.motivoSeleccionado = motivo.valor">
                                <i :class="motivo.icono"></i>
                                <span>{{ motivo.etiqueta }}</span>
                                <i v-if="modalReporte.motivoSeleccionado === motivo.valor"
                                    class="pi pi-check-circle report-motivo__check"></i>
                            </button>
                        </div>

                        <div class="report-modal__detalle">
                            <label>Detalles adicionales (opcional)</label>
                            <textarea v-model="modalReporte.descripcion" maxlength="1000" rows="3"
                                placeholder="Cuéntanos más sobre lo que pasó, si quieres..."></textarea>
                        </div>

                        <p v-if="modalReporte.error" class="report-modal__error">
                            <i class="pi pi-exclamation-circle"></i> {{ modalReporte.error }}
                        </p>

                        <button class="report-modal__submit"
                            :disabled="!modalReporte.motivoSeleccionado || modalReporte.enviando"
                            @click="enviarReporte">
                            <i v-if="modalReporte.enviando" class="pi pi-spin pi-spinner"></i>
                            <i v-else class="pi pi-send"></i>
                            {{ modalReporte.enviando ? 'Enviando...' : 'Enviar reporte' }}
                        </button>
                    </template>

                    <template v-else>
                        <div class="report-modal__exito">
                            <i class="pi pi-check-circle"></i>
                            <p>Reporte enviado. Gracias por ayudarnos a mantener la comunidad segura.</p>
                        </div>
                        <button class="report-modal__submit" @click="cerrarModalReporte">Cerrar</button>
                    </template>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, ref, nextTick, watch, onUnmounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import EmojiPicker from '@/Components/EmojiPicker.vue';
import LlamadaModal from '@/Components/LlamadaModal.vue';
import { useChat } from '@/composables/useChat';
import { useLlamada } from '@/composables/useLlamada';

const page = usePage();
const usuario = computed(() => page.props.auth.user);

/* ---------------------------------------------------------------
 * Chat (lista + conversación activa + tiempo real)
 * --------------------------------------------------------------- */
const {
    chats, cargandoChats, cargarChats,
    chatActivoId, mensajes, otroParticipante, cargandoMensajes, enviando, otroEstaEscribiendo,
    abrirChat, cargarMensajesAnteriores, enviarTexto, enviarArchivo, borrarMensaje, notificarEscribiendo,
    marcarComoLeidos,
} = useChat(usuario.value.id);

/* ---------------------------------------------------------------
 * Llamadas
 * --------------------------------------------------------------- */
const {
    llamadaActiva, estadoLocal, streamLocal, streamRemoto,
    microfonoActivo, camaraActiva, duracionSegundos, esVideo,
    escucharLlamadas, dejarDeEscuchar, iniciarLlamada, contestarLlamada,
    colgarLlamada, rechazarLlamada, alternarMicrofono, alternarCamara,
} = useLlamada(usuario.value.id);

// Actualizar escucha de llamadas cuando cambia el chat
watch(chatActivoId, (nuevoId, anteriorId) => {
    if (anteriorId) dejarDeEscuchar(anteriorId);
    if (nuevoId) escucharLlamadas(nuevoId);
});

/* ---------------------------------------------------------------
 * Búsqueda
 * --------------------------------------------------------------- */
const busqueda = ref('');
const filtroActivo = ref('todos');

const conversacionesFiltradas = computed(() => {
    let lista = chats.value;
    if (filtroActivo.value === 'no-leidos') lista = lista.filter((c) => c.no_leidos > 0);
    if (busqueda.value.trim()) {
        const q = busqueda.value.toLowerCase();
        lista = lista.filter((c) => c.usuario?.nombre?.toLowerCase().includes(q));
    }
    return lista;
});

/* ---------------------------------------------------------------
 * Composer mejorado
 * --------------------------------------------------------------- */
const nuevoMensaje = ref('');
const mostrarEmojis = ref(false);
const inputArchivo = ref(null);
const progresoSubida = ref(null);
const errorAdjunto = ref('');
const mensajesEl = ref(null);
const conversacionesList = ref(null);

const MAX_VIDEO_SEGUNDOS = 30;
const tieneTexto = computed(() => nuevoMensaje.value.trim().length > 0);

// ================================================================
// 🔥 CONFIGURACIÓN DE ECHO PARA TIEMPO REAL
// ================================================================
let echoInstance = null;
let echoListeners = [];

function setupEchoListeners(chatId) {
    // Limpiar listeners anteriores
    if (echoInstance) {
        echoListeners.forEach(listener => {
            try {
                window.Echo.leave(listener);
            } catch (e) {
                console.warn('Error al limpiar listener:', e);
            }
        });
        echoListeners = [];
        echoInstance = null;
    }

    if (!window.Echo || !chatId) {
        console.warn('Echo no disponible o chatId inválido');
        return;
    }

    try {
        console.log(`🔌 Conectando al canal chat.${chatId}`);
        echoInstance = window.Echo.private(`chat.${chatId}`);

        // ✅ Escuchar nuevos mensajes - SIN RECARGAR
        echoInstance.listen('mensaje.enviado', (data) => {
            console.log('📩 mensaje.enviado recibido:', data);

            if (data.mensaje && data.mensaje.chat_id === chatId) {
                const existe = mensajes.value.some(m => m.id === data.mensaje.id);
                if (!existe) {
                    // ✅ Agregar mensaje en tiempo real
                    mensajes.value.push(data.mensaje);

                    // ✅ Actualizar lista de conversaciones en tiempo real
                    actualizarListaChats(data.mensaje);

                    // ✅ Scroll al fondo
                    nextTick(() => scrollAlFondo());
                }
            }
        });

        // ✅ Escuchar confirmación de lectura - SIN RECARGAR
        echoInstance.listen('mensaje.leido', (data) => {
            console.log('👁️ mensaje.leido recibido:', data);

            if (data.mensaje_ids && data.mensaje_ids.length > 0) {
                // ✅ Actualizar estado de leído en tiempo real
                mensajes.value = mensajes.value.map(m => {
                    if (data.mensaje_ids.includes(m.id)) {
                        return { ...m, leido: true };
                    }
                    return m;
                });

                // ✅ Actualizar contador de no leídos en tiempo real
                if (data.leido_por_id && data.leido_por_id !== usuario.value.id) {
                    const chat = chats.value.find(c => c.id === chatId);
                    if (chat) {
                        chat.no_leidos = 0;
                    }
                }
            }
        });

        // ✅ Escuchar estado de escritura - SIN RECARGAR
        echoInstance.listen('usuario.escribiendo', (data) => {
            console.log('✍️ usuario.escribiendo recibido:', data);
            if (data.usuario_id && data.usuario_id !== usuario.value.id) {
                otroEstaEscribiendo.value = data.escribiendo;
            }
        });

        echoListeners.push(`chat.${chatId}`);
        console.log(`✅ Escuchando eventos en chat.${chatId}`);

    } catch (error) {
        console.error('❌ Error al configurar Echo:', error);
    }
}

// ================================================================
// 🔥 FUNCIONES DE ACTUALIZACIÓN EN TIEMPO REAL
// ================================================================

function actualizarListaChats(nuevoMensaje) {
    if (!nuevoMensaje || !nuevoMensaje.chat_id) return;

    const chatIndex = chats.value.findIndex(c => c.id === nuevoMensaje.chat_id);
    if (chatIndex !== -1) {
        // ✅ Actualizar el último mensaje en tiempo real
        chats.value[chatIndex].ultimo_mensaje = nuevoMensaje.texto || '📎 Archivo';
        chats.value[chatIndex].ultimo_mensaje_en = nuevoMensaje.created_at;

        // ✅ Incrementar contador de no leídos si el mensaje no es del usuario actual
        if (nuevoMensaje.remitente_id !== usuario.value.id) {
            chats.value[chatIndex].no_leidos = (chats.value[chatIndex].no_leidos || 0) + 1;
        }

        // ✅ Reordenar lista (más reciente primero) en tiempo real
        chats.value.sort((a, b) => {
            const dateA = new Date(a.ultimo_mensaje_en || 0);
            const dateB = new Date(b.ultimo_mensaje_en || 0);
            return dateB - dateA;
        });
    }
}

// ================================================================
// FUNCIONES DEL COMPOSER
// ================================================================

function alEscribir() {
    notificarEscribiendo();
}

function insertarEmoji(emoji) {
    nuevoMensaje.value += emoji;
    mostrarEmojis.value = false;
}

async function enviar() {
    if (!nuevoMensaje.value.trim() || enviando.value) return;
    const texto = nuevoMensaje.value;
    nuevoMensaje.value = '';
    await enviarTexto(texto);
    await scrollAlFondo();
}

function abrirSelectorArchivo() {
    errorAdjunto.value = '';
    inputArchivo.value?.click();
}

async function alSeleccionarArchivo(event) {
    const archivo = event.target.files?.[0];
    event.target.value = '';
    if (!archivo) return;

    errorAdjunto.value = '';

    if (archivo.type.startsWith('video/')) {
        const duracion = await obtenerDuracionVideoCliente(archivo);
        if (duracion !== null && duracion > MAX_VIDEO_SEGUNDOS) {
            errorAdjunto.value = `El video dura ${Math.round(duracion)}s. El máximo son ${MAX_VIDEO_SEGUNDOS}s.`;
            return;
        }
    }

    try {
        progresoSubida.value = 0;
        await enviarArchivo(archivo, (pct) => (progresoSubida.value = pct));
        await scrollAlFondo();
    } catch (e) {
        errorAdjunto.value = e?.response?.data?.errors?.archivo?.[0] ?? 'No se pudo enviar el archivo.';
    } finally {
        progresoSubida.value = null;
    }
}

function obtenerDuracionVideoCliente(archivo) {
    return new Promise((resolve) => {
        const video = document.createElement('video');
        video.preload = 'metadata';
        video.onloadedmetadata = () => {
            URL.revokeObjectURL(video.src);
            resolve(video.duration);
        };
        video.onerror = () => resolve(null);
        video.src = URL.createObjectURL(archivo);
    });
}

async function scrollAlFondo() {
    await nextTick();
    if (mensajesEl.value) mensajesEl.value.scrollTop = mensajesEl.value.scrollHeight;
}

// ================================================================
// NAVEGACIÓN Y SELECCIÓN DE CHAT
// ================================================================

async function seleccionarConversacion(chatId) {
    if (chatActivoId.value === chatId) return;

    await abrirChat(chatId);
    await scrollAlFondo();
}

// ================================================================
// LLAMADAS
// ================================================================

function llamarAudio() {
    if (chatActivoId.value) iniciarLlamada(chatActivoId.value, 'audio');
}

function llamarVideo() {
    if (chatActivoId.value) iniciarLlamada(chatActivoId.value, 'video');
}

// ================================================================
// 🚩 REPORTAR USUARIO
// ================================================================

const mostrarMenuOpciones = ref(false);

function alClicFuera(event) {
    if (mostrarMenuOpciones.value && !event.target.closest('.chat-header__menu-wrap')) {
        mostrarMenuOpciones.value = false;
    }
}

const motivosReporte = [
    { valor: 'spam', etiqueta: 'Spam o publicidad no deseada', icono: 'pi pi-envelope' },
    { valor: 'lenguaje_inapropiado', etiqueta: 'Lenguaje inapropiado u ofensivo', icono: 'pi pi-comment' },
    { valor: 'menor_edad', etiqueta: 'Sospecho que es menor de edad', icono: 'pi pi-exclamation-triangle' },
    { valor: 'acoso', etiqueta: 'Acoso o intimidación', icono: 'pi pi-shield' },
    { valor: 'perfil_falso', etiqueta: 'Perfil falso o suplantación de identidad', icono: 'pi pi-user-minus' },
    { valor: 'contenido_no_solicitado', etiqueta: 'Contenido explícito no solicitado', icono: 'pi pi-eye-slash' },
    { valor: 'amenazas', etiqueta: 'Amenazas o violencia', icono: 'pi pi-exclamation-circle' },
    { valor: 'estafa', etiqueta: 'Intento de estafa o fraude', icono: 'pi pi-wallet' },
    { valor: 'informacion_privada', etiqueta: 'Compartió mi información privada sin permiso', icono: 'pi pi-lock' },
    { valor: 'discriminacion', etiqueta: 'Discurso de odio o discriminación', icono: 'pi pi-ban' },
    { valor: 'venta_no_autorizada', etiqueta: 'Venta de productos o servicios no autorizados', icono: 'pi pi-shopping-cart' },
    { valor: 'otro', etiqueta: 'Otro motivo', icono: 'pi pi-flag' },
];

const modalReporte = ref({
    visible: false,
    motivoSeleccionado: null,
    descripcion: '',
    enviando: false,
    enviado: false,
    error: '',
});

function abrirModalReporte() {
    mostrarMenuOpciones.value = false;
    modalReporte.value = {
        visible: true,
        motivoSeleccionado: null,
        descripcion: '',
        enviando: false,
        enviado: false,
        error: '',
    };
}

function cerrarModalReporte() {
    modalReporte.value.visible = false;
}

async function enviarReporte() {
    if (!modalReporte.value.motivoSeleccionado || !otroParticipante.value?.id) return;

    modalReporte.value.enviando = true;
    modalReporte.value.error = '';

    try {
        await axios.post('/reportes', {
            reportado_id: otroParticipante.value.id,
            tipo: modalReporte.value.motivoSeleccionado,
            descripcion: modalReporte.value.descripcion?.trim() || null,
        });
        modalReporte.value.enviado = true;
    } catch (error) {
        modalReporte.value.error = error?.response?.data?.message || 'No se pudo enviar el reporte. Intenta de nuevo.';
    } finally {
        modalReporte.value.enviando = false;
    }
}

function getAvatarUrl(avatar) {
    if (!avatar) return '/images/shared/avatar-default.jpg';
    if (avatar.startsWith('http://') || avatar.startsWith('https://')) return avatar;
    if (avatar.startsWith('/storage/') || avatar.startsWith('/images/')) return avatar;
    if (avatar.startsWith('perfil/')) return '/storage/' + avatar;
    return '/storage/' + avatar.replace(/^\/+/, '');
}

// ================================================================
// LIFECYCLE
// ================================================================

onMounted(async () => {
    console.log('🚀 Chat montado, cargando conversaciones...');
    await cargarChats();
    if (chats.value.length) {
        await seleccionarConversacion(chats.value[0].id);
    }
    document.addEventListener('click', alClicFuera);
});

// 🔥 Cuando cambia el chat activo, reconectar Echo para tiempo real
watch(chatActivoId, (nuevoId, anteriorId) => {
    console.log(`🔄 Chat activo cambió: ${anteriorId} -> ${nuevoId}`);

    if (anteriorId) {
        if (echoInstance) {
            try {
                window.Echo.leave(`chat.${anteriorId}`);
                echoListeners = [];
                echoInstance = null;
                console.log(`✅ Salido del canal chat.${anteriorId}`);
            } catch (e) {
                console.warn('Error al salir del canal:', e);
            }
        }
    }

    if (nuevoId) {
        escucharLlamadas(nuevoId);
        setupEchoListeners(nuevoId);
        if (nuevoId) marcarComoLeidos(nuevoId);
    }
});

onUnmounted(() => {
    console.log('🔌 Desmontando chat, limpiando listeners...');
    document.removeEventListener('click', alClicFuera);
    if (echoInstance) {
        echoListeners.forEach(listener => {
            try {
                window.Echo.leave(listener);
            } catch (e) {
                console.warn('Error al salir del canal:', e);
            }
        });
        echoInstance = null;
        echoListeners = [];
    }
});
</script>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA
   ========================================================================= */
.mensajes-page {
    --brand: #C81E3A;
    --brand-dark: #A6152D;
    --brand-light: #E85A72;
    --brand-soft: #FDF1F2;
    --gold: #D4AF37;
    --ink: #171412;
    --ink-soft: #4B4744;
    --muted: #8A8481;
    --muted-light: #B7B2AF;
    --line: #ECE9E7;
    --surface: #FAF8F7;
    --bg-chat: #F5F3F1;
    --white: #FFFFFF;
    --bubble-mine: linear-gradient(135deg, #C81E3A 0%, #A6152D 100%);
    --bubble-theirs: #FFFFFF;
    --shadow-sm: 0 2px 8px rgba(23, 20, 18, 0.05);
    --shadow-md: 0 8px 28px rgba(23, 20, 18, 0.09);
    --shadow-lg: 0 16px 48px rgba(23, 20, 18, 0.14);
    --radius-sm: 10px;
    --radius-md: 16px;
    --radius-lg: 22px;
    --font-serif: 'Fraunces', Georgia, serif;
    --font-sans: 'Inter', system-ui, -apple-system, sans-serif;

    font-family: var(--font-sans);
    color: var(--ink);
    max-width: 1500px;
    margin: 0 auto;
    padding: 1.5rem 2rem;
    display: grid;
    grid-template-columns: 340px minmax(0, 1fr);
    gap: 1.5rem;
    height: calc(100vh - 72px);
}

@media (max-width: 860px) {
    .mensajes-page {
        grid-template-columns: 1fr;
        padding: 0.75rem;
        height: calc(100vh - 62px);
        gap: 0.75rem;
    }
}

/* =========================================================================
   COLUMNA: CONVERSACIONES
   ========================================================================= */
.conversations-col {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-lg);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

.conversations-search {
    padding: 1.25rem 1.25rem 0.9rem;
}

.search-input {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    background: var(--surface);
    border: 1.5px solid var(--line);
    border-radius: var(--radius-md);
    padding: 0.65rem 1rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.search-input:focus-within {
    border-color: var(--brand);
    box-shadow: 0 0 0 4px rgba(200, 30, 58, 0.08);
}

.search-input i {
    color: var(--muted-light);
    font-size: 0.9rem;
}

.search-input input {
    border: none;
    background: none;
    outline: none;
    font-size: 0.88rem;
    color: var(--ink);
    width: 100%;
    font-family: var(--font-sans);
}

.search-input input::placeholder {
    color: var(--muted-light);
}

.conversations-tabs {
    display: flex;
    gap: 0.5rem;
    padding: 0.9rem 1.25rem;
}

.tab-pill {
    font-family: var(--font-sans);
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink-soft);
    background: var(--surface);
    border: 1.5px solid transparent;
    border-radius: 999px;
    padding: 0.4rem 0.9rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.2s ease;
}

.tab-pill:hover {
    border-color: var(--line);
}

.tab-pill.active {
    background: var(--brand);
    color: var(--white);
    box-shadow: 0 4px 12px rgba(200, 30, 58, 0.25);
}

.tab-badge {
    background: rgba(255, 255, 255, 0.3);
    color: inherit;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.05rem 0.4rem;
    border-radius: 999px;
    min-width: 16px;
    text-align: center;
}

.tab-pill:not(.active) .tab-badge {
    background: var(--brand);
    color: var(--white);
}

.conversations-list {
    flex: 1;
    overflow-y: auto;
    padding: 0.4rem 0.6rem 1rem;
}

.conversations-empty {
    text-align: center;
    color: var(--muted-light);
    font-size: 0.85rem;
    padding: 3rem 1rem;
}

.conversation-item {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0.75rem;
    border: none;
    background: none;
    border-radius: var(--radius-md);
    cursor: pointer;
    text-align: left;
    font-family: var(--font-sans);
    transition: background 0.15s ease, transform 0.1s ease;
    position: relative;
}

.conversation-item:hover {
    background: var(--surface);
}

.conversation-item:active {
    transform: scale(0.99);
}

.conversation-item.active {
    background: var(--brand-soft);
}

.conversation-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 12%;
    bottom: 12%;
    width: 3px;
    border-radius: 0 4px 4px 0;
    background: var(--brand);
}

.conversation-item__avatar {
    position: relative;
    flex-shrink: 0;
}

.conversation-item__avatar img {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--surface);
    border: 2px solid var(--white);
    box-shadow: var(--shadow-sm);
}

.avatar-verified {
    position: absolute;
    bottom: -1px;
    right: -1px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1fbf5c 0%, #34d399 100%);
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.5rem;
    border: 2px solid var(--white);
}

.conversation-item__body {
    flex: 1;
    min-width: 0;
}

.conversation-item__title-row {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.15rem;
}

.conversation-item__title-row strong {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--ink);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.conversation-item__online {
    color: #22c55e;
    font-size: 0.6rem;
    animation: pulse-dot-online 1.5s ease-in-out infinite;
}

@keyframes pulse-dot-online {

    0%,
    100% {
        opacity: 0.5;
    }

    50% {
        opacity: 1;
    }
}

.conversation-item__preview {
    font-size: 0.8rem;
    color: var(--muted);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.conversation-item.active .conversation-item__preview {
    color: var(--ink-soft);
}

.conversation-item__badge {
    flex-shrink: 0;
    background: var(--brand);
    color: var(--white);
    font-size: 0.68rem;
    font-weight: 700;
    min-width: 20px;
    height: 20px;
    border-radius: 999px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 0.35rem;
    box-shadow: 0 2px 6px rgba(200, 30, 58, 0.35);
}

/* =========================================================================
   COLUMNA: CHAT
   ========================================================================= */
.chat-col {
    background: var(--bg-chat);
    border: 1px solid var(--line);
    border-radius: var(--radius-lg);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    min-width: 0;
}

.chat-col--empty {
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    color: var(--muted-light);
    background: var(--white);
}

.chat-col--empty i {
    font-size: 3rem;
    opacity: 0.5;
}

.chat-col--empty p {
    font-size: 0.9rem;
    margin: 0;
}

.chat-header {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 1rem 1.5rem;
    background: var(--white);
    border-bottom: 1px solid var(--line);
    flex-shrink: 0;
}

.chat-header__avatar-wrapper {
    position: relative;
    flex-shrink: 0;
}

.chat-header__avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: var(--shadow-sm);
}

.chat-header__verified {
    position: absolute;
    bottom: -1px;
    right: -1px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1fbf5c 0%, #34d399 100%);
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.45rem;
    border: 2px solid var(--white);
}

.chat-header__info {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.chat-header__info strong {
    font-family: var(--font-serif);
    font-size: 1.02rem;
    font-weight: 600;
    color: var(--ink);
}

.chat-header__typing {
    font-size: 0.75rem;
    color: var(--brand);
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.chat-header__typing::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--brand);
    animation: pulse-dot 1s ease-in-out infinite;
}

@keyframes pulse-dot {

    0%,
    100% {
        opacity: 0.3;
        transform: scale(0.85);
    }

    50% {
        opacity: 1;
        transform: scale(1);
    }
}

.chat-header__actions {
    display: flex;
    gap: 0.4rem;
    flex-shrink: 0;
}

.chat-header__actions button {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    border: none;
    background: var(--surface);
    color: var(--ink-soft);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    transition: all 0.2s ease;
}

.chat-header__actions button:hover {
    background: var(--brand);
    color: var(--white);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(200, 30, 58, 0.3);
}

/* =========================================================================
   BODY DEL CHAT / BURBUJAS
   ========================================================================= */
.chat-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
    background-image: radial-gradient(var(--line) 1px, transparent 1px);
    background-size: 20px 20px;
    background-position: -8px -8px;
}

.message-row {
    display: flex;
    align-items: flex-end;
    gap: 0.55rem;
    max-width: 74%;
    animation: message-in 0.2s ease;
}

@keyframes message-in {
    from {
        opacity: 0;
        transform: translateY(6px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-row--theirs {
    align-self: flex-start;
}

.message-row--mine {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message-avatar-wrapper {
    flex-shrink: 0;
}

.message-row__avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: var(--shadow-sm);
}

.message-row__avatar--mine {
    border: 2px solid var(--brand-soft);
}

.message-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.message-row--mine .message-content {
    align-items: flex-end;
}

.message-bubble {
    padding: 0.65rem 0.95rem;
    border-radius: var(--radius-md);
    font-size: 0.9rem;
    line-height: 1.45;
    box-shadow: var(--shadow-sm);
    word-break: break-word;
}

.message-bubble--theirs {
    background: var(--bubble-theirs);
    color: var(--ink);
    border-bottom-left-radius: 5px;
}

.message-bubble--mine {
    background: var(--bubble-mine);
    color: var(--white);
    border-bottom-right-radius: 5px;
}

.message-bubble p {
    margin: 0;
    white-space: pre-wrap;
}

.message-bubble__imagen {
    max-width: 260px;
    border-radius: 12px;
    cursor: pointer;
    display: block;
}

.message-bubble__video-wrap {
    position: relative;
}

.message-bubble__video {
    max-width: 260px;
    border-radius: 12px;
    display: block;
}

.message-bubble__duracion {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(0, 0, 0, 0.65);
    color: #fff;
    font-size: 0.68rem;
    font-weight: 600;
    padding: 0.1rem 0.4rem;
    border-radius: 6px;
}

.message-bubble__audio {
    max-width: 240px;
}

.message-meta {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    margin-top: 0.3rem;
    padding: 0 0.2rem;
}

.message-time {
    font-size: 0.68rem;
    color: var(--muted-light);
}

.message-status i {
    font-size: 0.75rem;
}

.sent-check {
    color: var(--muted-light);
}

.read-check {
    color: var(--brand);
}

/* =========================================================================
   COMPOSER
   ========================================================================= */
.upload-progress {
    position: relative;
    height: 4px;
    background: var(--line);
    margin: 0 1.5rem;
    border-radius: 999px;
    overflow: hidden;
}

.upload-progress__bar {
    height: 100%;
    background: var(--brand);
    transition: width 0.2s ease;
}

.upload-progress span {
    position: absolute;
    top: 6px;
    left: 0;
    font-size: 0.7rem;
    color: var(--muted);
}

.upload-error {
    margin: 0.4rem 1.5rem 0;
    font-size: 0.78rem;
    color: #DC2626;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.chat-composer {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 1rem 1.5rem;
    background: var(--white);
    border-top: 1px solid var(--line);
    flex-shrink: 0;
}

.attach-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: var(--surface);
    color: var(--ink-soft);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
    transition: all 0.2s ease;
}

.attach-btn:hover {
    background: var(--brand-soft);
    color: var(--brand);
}

.composer-input-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    background: var(--surface);
    border: 1.5px solid var(--line);
    border-radius: var(--radius-lg);
    padding: 0.3rem 0.4rem 0.3rem 1.1rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    position: relative;
}

.composer-input-wrapper:focus-within {
    border-color: var(--brand);
    box-shadow: 0 0 0 4px rgba(200, 30, 58, 0.08);
    background: var(--white);
}

.composer-input {
    flex: 1;
    border: none;
    background: none;
    outline: none;
    font-size: 0.9rem;
    font-family: var(--font-sans);
    color: var(--ink);
    padding: 0.5rem 0;
}

.composer-input::placeholder {
    color: var(--muted-light);
}

.emoji-wrap {
    position: relative;
    flex-shrink: 0;
}

.emoji-btn {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: none;
    background: none;
    color: var(--muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.emoji-btn:hover {
    background: var(--brand-soft);
    color: var(--brand);
}

.emoji-popover {
    position: absolute;
    bottom: calc(100% + 10px);
    right: 0;
    z-index: 30;
    box-shadow: var(--shadow-lg);
    border-radius: var(--radius-md);
    overflow: hidden;
}

.send-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: none;
    background: var(--muted-light);
    color: var(--white);
    cursor: not-allowed;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.send-btn--active {
    background: var(--bubble-mine);
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(200, 30, 58, 0.35);
}

.send-btn--active:hover {
    transform: translateY(-2px) scale(1.04);
    box-shadow: 0 6px 18px rgba(200, 30, 58, 0.45);
}

.send-btn--active:active {
    transform: scale(0.95);
}

.send-btn:disabled {
    cursor: not-allowed;
}

/* =========================================================================
   SCROLLBAR
   ========================================================================= */
.conversations-list::-webkit-scrollbar,
.chat-body::-webkit-scrollbar {
    width: 7px;
}

.conversations-list::-webkit-scrollbar-track,
.chat-body::-webkit-scrollbar-track {
    background: transparent;
}

.conversations-list::-webkit-scrollbar-thumb,
.chat-body::-webkit-scrollbar-thumb {
    background: var(--muted-light);
    border-radius: 999px;
}

.conversations-list::-webkit-scrollbar-thumb:hover,
.chat-body::-webkit-scrollbar-thumb:hover {
    background: var(--muted);
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 860px) {

    .conversations-col,
    .chat-col {
        border-radius: var(--radius-md);
    }

    .conversations-tabs {
        padding: 0.5rem 0.75rem;
        gap: 0.3rem;
    }

    .tab-pill {
        font-size: 0.7rem;
        padding: 0.2rem 0.6rem;
    }

    .message-row {
        max-width: 88%;
    }

    .chat-composer {
        padding: 0.75rem 1rem;
    }

    .conversation-item__avatar img {
        width: 44px;
        height: 44px;
    }
}

@media (max-width: 480px) {
    .mensajes-page {
        padding: 0.5rem;
        height: calc(100vh - 55px);
    }

    .chat-header__avatar {
        width: 36px;
        height: 36px;
    }

    .chat-header__actions button {
        width: 34px;
        height: 34px;
        font-size: 0.85rem;
    }

    .message-bubble {
        font-size: 0.82rem;
        padding: 0.5rem 0.7rem;
    }

    .message-row {
        max-width: 92%;
    }

    .message-avatar-wrapper {
        width: 26px;
        height: 26px;
    }

    .message-row__avatar {
        width: 26px;
        height: 26px;
    }

    .send-btn {
        width: 38px;
        height: 38px;
        font-size: 0.9rem;
    }

    .composer-input {
        font-size: 0.82rem;
        padding: 0.4rem 0;
    }

    .conversation-item__avatar img {
        width: 40px;
        height: 40px;
    }

    .conversations-tabs {
        padding: 0.4rem 0.6rem;
        gap: 0.3rem;
    }

    .tab-pill {
        font-size: 0.65rem;
        padding: 0.15rem 0.5rem;
    }
}

/* =========================================================================
   ✅ MENÚ "..." DEL HEADER DEL CHAT
   ========================================================================= */
.chat-header__menu-wrap {
    position: relative;
}

.chat-header__menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    z-index: 40;
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: 12px;
    box-shadow: var(--shadow-md);
    min-width: 220px;
    overflow: hidden;
    padding: 0.3rem;
}

.chat-header__menu-item {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.65rem 0.8rem;
    border: none;
    background: none;
    border-radius: 8px;
    font-size: 0.85rem;
    font-family: inherit;
    color: var(--ink);
    cursor: pointer;
    text-align: left;
    transition: background 0.15s ease;
}

.chat-header__menu-item:hover {
    background: var(--surface);
}

.chat-header__menu-item--danger {
    color: #DC2626;
}

.chat-header__menu-item--danger:hover {
    background: #FEE2E2;
}

.menu-fade-enter-active,
.menu-fade-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}

.menu-fade-enter-from,
.menu-fade-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

/* =========================================================================
   ✅ MODAL DE REPORTE
   ========================================================================= */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.report-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 300;
    background: rgba(23, 20, 18, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.report-modal {
    position: relative;
    width: 100%;
    max-width: 460px;
    max-height: 88vh;
    overflow-y: auto;
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: 2rem 1.75rem 1.75rem;
    box-shadow: var(--shadow-lg);
}

.report-modal__close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: var(--surface);
    color: var(--ink-soft);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.report-modal__header {
    text-align: center;
    margin-bottom: 1.4rem;
}

.report-modal__icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #FEE2E2;
    color: #DC2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    margin: 0 auto 0.8rem;
}

.report-modal__header h2 {
    font-family: var(--font-serif);
    font-size: 1.25rem;
    margin: 0 0 0.4rem;
    color: var(--ink);
}

.report-modal__header p {
    font-size: 0.82rem;
    color: var(--muted);
    margin: 0;
}

.report-modal__motivos {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
    margin-bottom: 1.1rem;
    max-height: 320px;
    overflow-y: auto;
    padding-right: 2px;
}

.report-motivo {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.7rem 0.9rem;
    border: 1.5px solid var(--line);
    border-radius: 10px;
    background: var(--white);
    cursor: pointer;
    font-size: 0.85rem;
    font-family: inherit;
    color: var(--ink);
    text-align: left;
    transition: all 0.15s ease;
    position: relative;
}

.report-motivo:hover {
    border-color: var(--muted-light);
    background: var(--surface);
}

.report-motivo i:first-child {
    color: var(--muted);
    font-size: 0.95rem;
    flex-shrink: 0;
    width: 18px;
}

.report-motivo--selected {
    border-color: var(--brand);
    background: var(--brand-soft);
}

.report-motivo--selected i:first-child {
    color: var(--brand);
}

.report-motivo__check {
    margin-left: auto;
    color: var(--brand) !important;
}

.report-modal__detalle {
    margin-bottom: 1rem;
}

.report-modal__detalle label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink-soft);
    margin-bottom: 0.4rem;
}

.report-modal__detalle textarea {
    width: 100%;
    border: 1.5px solid var(--line);
    border-radius: 10px;
    padding: 0.7rem 0.9rem;
    font-size: 0.85rem;
    font-family: inherit;
    color: var(--ink);
    resize: vertical;
    outline: none;
    transition: border-color 0.2s ease;
}

.report-modal__detalle textarea:focus {
    border-color: var(--brand);
}

.report-modal__error {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8rem;
    color: #DC2626;
    background: #FEE2E2;
    padding: 0.6rem 0.8rem;
    border-radius: 8px;
    margin-bottom: 0.9rem;
}

.report-modal__submit {
    width: 100%;
    background: var(--brand);
    color: var(--white);
    border: none;
    border-radius: 12px;
    padding: 0.85rem;
    font-size: 0.88rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
}

.report-modal__submit:hover:not(:disabled) {
    background: var(--brand-dark);
    transform: translateY(-1px);
}

.report-modal__submit:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.report-modal__exito {
    text-align: center;
    padding: 1rem 0 1.4rem;
}

.report-modal__exito i {
    font-size: 2.6rem;
    color: #22C55E;
    margin-bottom: 0.8rem;
    display: block;
}

.report-modal__exito p {
    font-size: 0.9rem;
    color: var(--ink-soft);
    margin: 0;
    line-height: 1.5;
}
</style>