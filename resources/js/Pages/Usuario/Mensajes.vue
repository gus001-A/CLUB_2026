<script setup>
import { computed, reactive, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

/* ---------------------------------------------------------------
 * Props recibidas del controlador
 * --------------------------------------------------------------- */
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
    conversaciones: {
        type: Array,
        default: () => []
    },
    mensajes: {
        type: Array,
        default: () => []
    }
});

/* ---------------------------------------------------------------
 * Estado
 * --------------------------------------------------------------- */
const busqueda = ref('');
const filtroActivo = ref('todos'); // 'todos' | 'no-leidos' | 'favoritos'

/* ---------------------------------------------------------------
 * Conversaciones (desde props)
 * --------------------------------------------------------------- */
const conversacionesLista = computed(() => props.conversaciones || []);

const conversacionesFiltradas = computed(() => {
    let lista = conversacionesLista.value;
    if (filtroActivo.value === 'no-leidos') {
        lista = lista.filter((c) => c.noLeidos > 0);
    }
    if (busqueda.value.trim()) {
        const q = busqueda.value.toLowerCase();
        lista = lista.filter((c) => c.nombre.toLowerCase().includes(q));
    }
    return lista;
});

const conversacionActivaId = ref(conversacionesLista.value.length > 0 ? conversacionesLista.value[0].id : null);
const conversacionActiva = computed(() => 
    conversacionesLista.value.find((c) => c.id === conversacionActivaId.value)
);

function seleccionarConversacion(id) {
    conversacionActivaId.value = id;
    const conv = conversacionesLista.value.find((c) => c.id === id);
    if (conv) conv.noLeidos = 0;
}

/* ---------------------------------------------------------------
 * Perfil de la conversación activa
 * --------------------------------------------------------------- */
const perfilActivo = computed(() => {
    if (!conversacionActiva.value) return null;
    return {
        nombre: conversacionActiva.value.nombre,
        ciudad: conversacionActiva.value.ciudad || 'Ciudad no especificada',
        distancia: conversacionActiva.value.distancia || 'Cerca de ti',
        compatibilidad: conversacionActiva.value.compatibilidad || 85,
        enLinea: conversacionActiva.value.enLinea || false,
        imagen: conversacionActiva.value.avatar || '/images/shared/avatar-default.jpg',
        intereses: conversacionActiva.value.intereses || [],
        sobre: conversacionActiva.value.sobre || 'Sin descripción disponible.'
    };
});

/* ---------------------------------------------------------------
 * Sugerencias de primer mensaje
 * --------------------------------------------------------------- */
const sugerenciasMensaje = [
    'Hola, me encantó tu perfil 👋',
    '¿Qué tipo de experiencias disfrutas más?',
    'Vimos que te gustan los viajes ✈️',
    '¿Cuál ha sido tu mejor experiencia?',
    'Me gustaría conocerte mejor 😊',
];

const mensaje = ref('');

function usarSugerencia(texto) {
    mensaje.value = texto;
}

function enviarMensaje() {
    if (!mensaje.value.trim()) return;
    // TODO: Implementar envío de mensajes
    mensaje.value = '';
}

/* ---------------------------------------------------------------
 * Consejos y seguridad
 * --------------------------------------------------------------- */
const consejosPrimeraImpresion = [
    'Sé auténtico y muestra interés genuino.',
    'Respeta sus tiempos y límites.',
    'Pregunta sobre sus pasiones y experiencias.',
    'Mantén un tono cordial y positivo.',
];

const seguridad = [
    'Chat privado y cifrado',
    'Perfiles verificados',
    'Puedes bloquear o reportar en cualquier momento',
];

// Función para obtener URL de avatar
function getAvatarUrl(avatar) {
    if (!avatar) return '/images/shared/avatar-default.jpg';
    if (avatar.startsWith('http://') || avatar.startsWith('https://')) return avatar;
    if (avatar.startsWith('/storage/') || avatar.startsWith('/images/')) return avatar;
    return '/storage/' + avatar.replace(/^\/+/, '');
}
</script>

<template>
    <Head title="Mensajes" />

    <AppLayout activeNav="mensajes">
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
                    <button class="filter-icon-btn"><i class="pi pi-sliders-h"></i></button>
                </div>

                <div class="conversations-tabs">
                    <button class="tab-pill" :class="{ active: filtroActivo === 'todos' }" @click="filtroActivo = 'todos'">Todos</button>
                    <button class="tab-pill" :class="{ active: filtroActivo === 'no-leidos' }" @click="filtroActivo = 'no-leidos'">
                        No leídos <span class="badge-count">3</span>
                    </button>
                    <button class="tab-pill" :class="{ active: filtroActivo === 'favoritos' }" @click="filtroActivo = 'favoritos'">Favoritos</button>
                </div>

                <div v-if="conversacionesFiltradas.length === 0" class="empty-conversations">
                    <i class="pi pi-comments"></i>
                    <span>No hay conversaciones</span>
                    <p>Conecta con personas para empezar a chatear</p>
                </div>

                <div v-else class="conversations-list">
                    <button
                        v-for="conv in conversacionesFiltradas"
                        :key="conv.id"
                        class="conversation-item"
                        :class="{ active: conv.id === conversacionActivaId }"
                        @click="seleccionarConversacion(conv.id)"
                    >
                        <div class="conversation-item__avatar">
                            <img :src="getAvatarUrl(conv.avatar)" :alt="conv.nombre" />
                            <span v-if="conv.enLinea" class="online-dot"></span>
                        </div>
                        <div class="conversation-item__body">
                            <div class="conversation-item__title-row">
                                <strong>{{ conv.nombre }}</strong>
                                <i v-if="conv.verificado" class="pi pi-verified"></i>
                            </div>
                            <span v-if="conv.enLinea" class="conversation-item__status">En línea</span>
                            <p class="conversation-item__preview">{{ conv.preview || 'Nuevo mensaje' }}</p>
                        </div>
                        <div class="conversation-item__meta">
                            <span class="time">{{ conv.tiempo || 'Recién' }}</span>
                            <span v-if="conv.noLeidos" class="badge-count badge-count--danger">{{ conv.noLeidos }}</span>
                        </div>
                    </button>
                </div>
            </aside>

            <!-- ============================================================ -->
            <!-- COLUMNA: CHAT -->
            <!-- ============================================================ -->
            <section class="chat-col" v-if="conversacionActiva">
                <header class="chat-header">
                    <img :src="getAvatarUrl(conversacionActiva.avatar)" :alt="conversacionActiva.nombre" class="chat-header__avatar" />
                    <div class="chat-header__info">
                        <strong>
                            {{ conversacionActiva.nombre }}
                            <span v-if="conversacionActiva.enLinea" class="online-tag">
                                <i class="pi pi-circle-fill"></i> En línea
                            </span>
                        </strong>
                        <span>
                            {{ perfilActivo?.ciudad || '' }}
                            <span v-if="perfilActivo?.distancia">• {{ perfilActivo.distancia }}</span>
                            <span v-if="perfilActivo?.compatibilidad">• Compatibilidad <strong class="compat">{{ perfilActivo.compatibilidad }}%</strong></span>
                        </span>
                    </div>
                    <div class="chat-header__actions">
                        <button><i class="pi pi-phone"></i></button>
                        <button><i class="pi pi-video"></i></button>
                        <button><i class="pi pi-ellipsis-h"></i></button>
                    </div>
                </header>

                <div class="chat-body">
                    <div class="match-intro">
                        <span class="match-intro__icon"><i class="pi pi-comments"></i></span>
                        <h2>¡Es el momento de romper el hielo!</h2>
                        <div class="match-intro__divider">
                            <span></span>
                            <i class="pi pi-heart-fill"></i>
                            <span></span>
                        </div>
                        <p>Envía un primer mensaje auténtico para empezar a conocerse.</p>
                        <span class="match-chip">
                            <i class="pi pi-heart"></i> Ustedes hicieron match hoy 🎉
                        </span>
                    </div>

                    <div class="message-suggestions">
                        <h4>Sugerencias para tu primer mensaje</h4>
                        <button
                            v-for="s in sugerenciasMensaje"
                            :key="s"
                            class="suggestion-item"
                            @click="usarSugerencia(s)"
                        >
                            <i class="pi pi-send"></i> {{ s }}
                        </button>
                    </div>
                </div>

                <div class="chat-composer">
                    <textarea v-model="mensaje" rows="3" placeholder="Escribe un mensaje..."></textarea>
                    <button class="emoji-btn"><i class="pi pi-face-smile"></i></button>
                    <div class="chat-composer__actions">
                        <button class="attach-btn"><i class="pi pi-paperclip"></i></button>
                        <button class="btn-send" @click="enviarMensaje">
                            <i class="pi pi-send"></i> Enviar
                        </button>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- COLUMNA: PERFIL -->
            <!-- ============================================================ -->
            <aside class="profile-col" v-if="perfilActivo">
                <div class="profile-card">
                    <div class="profile-card__image">
                        <img :src="getAvatarUrl(perfilActivo.imagen)" :alt="perfilActivo.nombre" />
                        <span v-if="perfilActivo.enLinea" class="profile-card__online">
                            <i class="pi pi-circle-fill"></i> En línea
                        </span>
                        <div class="profile-card__overlay-text">
                            <strong>{{ perfilActivo.nombre }} <i class="pi pi-verified"></i></strong>
                            <span><i class="pi pi-map-marker"></i> {{ perfilActivo.distancia }}</span>
                        </div>
                    </div>

                    <div class="profile-card__body">
                        <span class="profile-city">{{ perfilActivo.ciudad }}</span>
                        <div class="profile-tags">
                            <span v-for="t in perfilActivo.intereses" :key="t.label" class="profile-tag">
                                <i class="pi" :class="t.icon"></i> {{ t.label }}
                            </span>
                        </div>

                        <h4>Sobre {{ conversacionActiva?.nombre?.split(',')[0] || 'él/ella' }}</h4>
                        <p class="profile-about">{{ perfilActivo.sobre }}</p>

                        <div class="profile-stats">
                            <div>
                                <span>Disponibilidad</span>
                                <strong class="online-text"><i class="pi pi-circle-fill"></i> En línea</strong>
                                <span class="profile-stats__note">Activos ahora</span>
                            </div>
                            <div>
                                <span>Compatibilidad</span>
                                <strong class="compat-text">
                                    {{ perfilActivo.compatibilidad }}% <i class="pi pi-info-circle"></i>
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tips-card">
                    <h4><i class="pi pi-sparkles"></i> Consejos para una buena primera impresión</h4>
                    <ul>
                        <li v-for="c in consejosPrimeraImpresion" :key="c">
                            <i class="pi pi-check"></i> {{ c }}
                        </li>
                    </ul>
                </div>

                <div class="security-card">
                    <h4><i class="pi pi-shield"></i> Tu seguridad es nuestra prioridad</h4>
                    <ul>
                        <li v-for="s in seguridad" :key="s">
                            <i class="pi pi-check"></i> {{ s }}
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </AppLayout>
</template>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA
   ========================================================================= */
.mensajes-page {
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
  --shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  --shadow-hover: 0 12px 40px rgba(0, 0, 0, 0.1);

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
}

.mensajes-page * {
  box-sizing: border-box;
}

.mensajes-page img {
  max-width: 100%;
  display: block;
}

/* =========================================================================
   LAYOUT PRINCIPAL
   ========================================================================= */
.mensajes-page {
    max-width: 1500px;
    margin: 0 auto;
    padding: 1.25rem 2rem;
    display: grid;
    grid-template-columns: 320px minmax(0, 1fr) 330px;
    gap: 1.25rem;
    height: calc(100vh - 90px);
}

@media (max-width: 1200px) {
    .mensajes-page {
        grid-template-columns: 300px minmax(0, 1fr);
        height: auto;
    }
    .profile-col {
        display: none;
    }
}

@media (max-width: 860px) {
    .mensajes-page {
        grid-template-columns: 1fr;
    }
    .conversations-col {
        display: none;
    }
}

/* =========================================================================
   CONVERSATIONS COLUMN
   ========================================================================= */
.conversations-col {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.conversations-search {
    display: flex;
    gap: 0.6rem;
    padding: 1rem;
    border-bottom: 1px solid #f0f0f2;
}

.search-input {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border: 1px solid #e3e3e7;
    border-radius: 8px;
    padding: 0.5rem 0.8rem;
    color: var(--muted-light);
    transition: all 0.3s ease;
}

.search-input:focus-within {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.08);
}

.search-input input {
    border: none;
    outline: none;
    font-size: 0.82rem;
    flex: 1;
    color: var(--ink);
    background: transparent;
    font-family: var(--font-sans);
}

.filter-icon-btn {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    border: 1px solid #e3e3e7;
    background: var(--white);
    color: var(--ink-soft);
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.filter-icon-btn:hover {
    border-color: var(--brand);
    color: var(--brand);
}

/* =========================================================================
   TABS
   ========================================================================= */
.conversations-tabs {
    display: flex;
    gap: 0.5rem;
    padding: 0.9rem 1rem;
    border-bottom: 1px solid #f0f0f2;
}

.tab-pill {
    border: 1px solid #e3e3e7;
    border-radius: var(--radius-full);
    padding: 0.4rem 0.8rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--ink-soft);
    background: var(--white);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.35rem;
    transition: all 0.3s ease;
}

.tab-pill:hover {
    border-color: var(--brand-soft);
}

.tab-pill.active {
    background: var(--brand-soft);
    border-color: var(--brand);
    color: var(--brand);
}

.badge-count {
    background: var(--line);
    color: var(--muted);
    font-size: 0.6rem;
    font-weight: 700;
    padding: 0.05rem 0.5rem;
    border-radius: var(--radius-full);
}

.badge-count--danger {
    background: var(--brand);
    color: var(--white);
}

/* =========================================================================
   EMPTY CONVERSATIONS
   ========================================================================= */
.empty-conversations {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    color: var(--muted);
    text-align: center;
}

.empty-conversations i {
    font-size: 2.5rem;
    color: var(--muted-light);
    margin-bottom: 1rem;
}

.empty-conversations span {
    font-size: 0.95rem;
    font-weight: 600;
    display: block;
    color: var(--ink);
}

.empty-conversations p {
    font-size: 0.8rem;
    margin: 0.2rem 0 0;
}

/* =========================================================================
   CONVERSATIONS LIST
   ========================================================================= */
.conversations-list {
    flex: 1;
    overflow-y: auto;
}

.conversation-item {
    width: 100%;
    display: flex;
    gap: 0.7rem;
    padding: 0.9rem 1rem;
    border: none;
    background: none;
    cursor: pointer;
    text-align: left;
    border-bottom: 1px solid #f5f5f6;
    transition: all 0.3s ease;
}

.conversation-item:hover {
    background: var(--surface);
}

.conversation-item.active {
    background: var(--brand-soft);
}

.conversation-item__avatar {
    position: relative;
    width: 46px;
    height: 46px;
    flex-shrink: 0;
}

.conversation-item__avatar img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.online-dot {
    position: absolute;
    bottom: 1px;
    right: 1px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--success);
    border: 2px solid var(--white);
}

.conversation-item__body {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.conversation-item__title-row {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.conversation-item__title-row strong {
    font-size: 0.85rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.conversation-item__title-row i {
    color: var(--success);
    font-size: 0.68rem;
    flex-shrink: 0;
}

.conversation-item__status {
    font-size: 0.65rem;
    color: var(--success);
    font-weight: 600;
}

.conversation-item__preview {
    font-size: 0.75rem;
    color: var(--muted);
    margin: 0.15rem 0 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.conversation-item__meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.4rem;
    flex-shrink: 0;
}

.conversation-item__meta .time {
    font-size: 0.65rem;
    color: var(--muted-light);
}

/* =========================================================================
   CHAT COLUMN
   ========================================================================= */
.chat-col {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: var(--shadow);
}

/* =========================================================================
   CHAT HEADER
   ========================================================================= */
.chat-header {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f0f0f2;
}

.chat-header__avatar {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    object-fit: cover;
}

.chat-header__info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.chat-header__info strong {
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.online-tag {
    font-size: 0.68rem;
    color: var(--success);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.online-tag i {
    font-size: 0.45rem;
}

.chat-header__info span {
    font-size: 0.75rem;
    color: var(--muted);
}

.chat-header__info .compat {
    color: var(--brand);
}

.chat-header__actions {
    display: flex;
    gap: 0.6rem;
}

.chat-header__actions button {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    border: 1px solid #e3e3e7;
    background: var(--white);
    color: var(--ink-soft);
    cursor: pointer;
    transition: all 0.3s ease;
}

.chat-header__actions button:hover {
    border-color: var(--brand);
    color: var(--brand);
}

/* =========================================================================
   CHAT BODY
   ========================================================================= */
.chat-body {
    flex: 1;
    overflow-y: auto;
    padding: 2rem 1.5rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2rem;
}

.match-intro {
    text-align: center;
    max-width: 400px;
}

.match-intro__icon {
    width: 62px;
    height: 62px;
    border-radius: 50%;
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 1.1rem;
}

.match-intro h2 {
    font-family: var(--font-serif);
    font-size: 1.25rem;
    margin: 0 0 0.9rem;
}

.match-intro__divider {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.9rem;
}

.match-intro__divider span {
    flex: 1;
    height: 1px;
    background: #e3e3e7;
}

.match-intro__divider i {
    color: var(--brand);
    font-size: 0.8rem;
}

.match-intro p {
    font-size: 0.85rem;
    color: var(--ink-soft);
    margin: 0 0 1.25rem;
}

.match-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--brand-soft);
    color: var(--brand);
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: var(--radius-full);
}

/* =========================================================================
   MESSAGE SUGGESTIONS
   ========================================================================= */
.message-suggestions {
    width: 100%;
    max-width: 460px;
}

.message-suggestions h4 {
    font-size: 0.85rem;
    margin: 0 0 0.9rem;
    text-align: left;
    color: var(--ink-soft);
}

.suggestion-item {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    text-align: left;
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--white);
    padding: 0.8rem 1.1rem;
    font-size: 0.82rem;
    color: var(--ink);
    cursor: pointer;
    margin-bottom: 0.7rem;
    transition: all 0.3s ease;
    font-family: var(--font-sans);
}

.suggestion-item i {
    color: var(--brand);
}

.suggestion-item:hover {
    border-color: var(--brand);
    background: var(--brand-soft);
    transform: translateX(4px);
}

/* =========================================================================
   CHAT COMPOSER
   ========================================================================= */
.chat-composer {
    border-top: 1px solid #f0f0f2;
    padding: 1rem 1.5rem;
    position: relative;
}

.chat-composer textarea {
    width: 100%;
    border: 1px solid #e3e3e7;
    border-radius: var(--radius-sm);
    padding: 0.8rem 2.5rem 0.8rem 1rem;
    font-family: var(--font-sans);
    font-size: 0.85rem;
    resize: none;
    color: var(--ink);
    transition: all 0.3s ease;
}

.chat-composer textarea:focus {
    outline: none;
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.08);
}

.emoji-btn {
    position: absolute;
    right: 1.75rem;
    top: 1.3rem;
    border: none;
    background: none;
    color: var(--muted-light);
    cursor: pointer;
    font-size: 1.1rem;
}

.emoji-btn:hover {
    color: var(--brand);
}

.chat-composer__actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.7rem;
}

.attach-btn {
    border: none;
    background: none;
    color: var(--ink-soft);
    font-size: 1.05rem;
    cursor: pointer;
    padding: 0.3rem;
    transition: all 0.3s ease;
}

.attach-btn:hover {
    color: var(--brand);
}

.btn-send {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.2rem;
    border: none;
    border-radius: var(--radius-sm);
    background: var(--brand);
    color: var(--white);
    font-weight: 700;
    font-size: 0.82rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: var(--font-sans);
}

.btn-send:hover {
    background: var(--brand-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(200, 30, 58, 0.3);
}

/* =========================================================================
   PROFILE COLUMN
   ========================================================================= */
.profile-col {
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
    overflow-y: auto;
}

.profile-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: var(--shadow);
}

.profile-card__image {
    position: relative;
    aspect-ratio: 16/11;
}

.profile-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-card__online {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0,0,0,0.55);
    color: #4ade80;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.profile-card__overlay-text {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 0.9rem;
    background: linear-gradient(0deg, rgba(0,0,0,0.85), rgba(0,0,0,0));
    color: var(--white);
}

.profile-card__overlay-text strong {
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.profile-card__overlay-text i {
    color: #4ade80;
    font-size: 0.75rem;
}

.profile-card__overlay-text span {
    font-size: 0.72rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    opacity: 0.9;
}

.profile-card__body {
    padding: 1.1rem;
}

.profile-city {
    font-size: 0.75rem;
    color: var(--muted);
    display: block;
    margin-bottom: 0.8rem;
}

.profile-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.1rem;
}

.profile-tag {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius-full);
    padding: 0.3rem 0.7rem;
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--ink-soft);
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.profile-card__body h4 {
    font-size: 0.85rem;
    margin: 0 0 0.4rem;
}

.profile-about {
    font-size: 0.78rem;
    color: var(--ink-soft);
    line-height: 1.55;
    margin: 0 0 1.1rem;
}

.profile-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    border-top: 1px solid #f0f0f2;
    padding-top: 1rem;
}

.profile-stats div {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.profile-stats span {
    font-size: 0.68rem;
    color: var(--muted);
}

.online-text {
    color: var(--success);
    font-size: 0.82rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.online-text i {
    font-size: 0.45rem;
}

.profile-stats__note {
    font-size: 0.65rem !important;
    color: var(--muted-light) !important;
}

.compat-text {
    color: var(--brand);
    font-size: 1.1rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.compat-text i {
    font-size: 0.65rem;
    color: var(--muted-light);
}

/* =========================================================================
   TIPS & SECURITY CARDS
   ========================================================================= */
.tips-card,
.security-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.25rem;
    box-shadow: var(--shadow);
}

.tips-card h4,
.security-card h4 {
    font-size: 0.85rem;
    margin: 0 0 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tips-card h4 i,
.security-card h4 i {
    color: var(--brand);
}

.tips-card ul,
.security-card ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.tips-card li,
.security-card li {
    font-size: 0.78rem;
    color: var(--ink);
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.tips-card li i,
.security-card li i {
    color: var(--brand);
    margin-top: 0.15rem;
    font-size: 0.7rem;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 480px) {
    .mensajes-page {
        padding: 0.75rem;
    }
    
    .chat-header {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .chat-header__actions {
        width: 100%;
        justify-content: flex-end;
    }
    
    .chat-body {
        padding: 1rem;
    }
    
    .chat-composer {
        padding: 0.75rem 1rem;
    }
    
    .match-intro h2 {
        font-size: 1rem;
    }
}
</style>