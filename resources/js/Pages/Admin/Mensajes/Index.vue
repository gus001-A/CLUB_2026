<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KpiCard from '@/Components/KpiCard.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps({
    stats: Object,
    conversaciones: Array,
    soporteActivoId: [Number, String, null],
    mensajes: Array,
    filtros: Object,
});

const toast = useToast();
const { confirm } = useConfirm();
const chatBody = ref(null);

const q = ref(props.filtros?.q || '');
const filtro = ref(props.filtros?.filtro || '');
const mensajeTexto = ref('');
const enviando = ref(false);

const origenLabel = {
    reporte: 'Desde reporte',
    manual: 'Consulta manual',
    otro: 'Soporte',
};

function getAvatarUrl(avatar) {
    return avatar || '/images/shared/avatar-default.jpg';
}

function scrollAbajo() {
    nextTick(() => {
        if (chatBody.value) chatBody.value.scrollTop = chatBody.value.scrollHeight;
    });
}
scrollAbajo();

let timeout = null;
function aplicarFiltros() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.mensajes.index'), {
            q: q.value || undefined,
            filtro: filtro.value || undefined,
            soporte: props.soporteActivoId || undefined,
        }, { preserveState: true, replace: true });
    }, 350);
}
watch([q, filtro], aplicarFiltros);

function seleccionarConversacion(id) {
    if (id === props.soporteActivoId) return;
    router.get(route('admin.mensajes.index'), {
        q: q.value || undefined,
        filtro: filtro.value || undefined,
        soporte: id,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: () => scrollAbajo(),
    });
}

const conversacionActiva = computed(() =>
    props.conversaciones.find((c) => c.id === props.soporteActivoId)
);

function enviarMensaje() {
    if (!mensajeTexto.value.trim() || !props.soporteActivoId || enviando.value) return;
    enviando.value = true;
    router.post(route('admin.mensajes.enviar', props.soporteActivoId), { texto: mensajeTexto.value }, {
        preserveScroll: true,
        onSuccess: () => {
            mensajeTexto.value = '';
            toast.success('Mensaje enviado.');
            scrollAbajo();
        },
        onError: () => toast.error('No se pudo enviar el mensaje.'),
        onFinish: () => { enviando.value = false; },
    });
}

async function cerrarConversacion() {
    if (!conversacionActiva.value) return;
    const ok = await confirm('¿Marcar esta conversación como cerrada? Podrás reabrirla si el usuario vuelve a escribir.', {
        title: 'Cerrar conversación',
        confirmLabel: 'Sí, cerrar',
    });
    if (!ok) return;
    router.post(route('admin.mensajes.cerrar', conversacionActiva.value.id), {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Conversación cerrada.'),
    });
}
</script>

<template>
    <Head title="Mensajes" />

    <AdminLayout>
        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4 space-y-6">

            <!-- KPIs -->
            <div class="admin-kpi-grid gap-6 w-full">
                <div class="min-w-0"><KpiCard label="Conversaciones" :value="stats?.total ?? 0" icon="pi-comments" hint="Total de soporte" /></div>
                <div class="min-w-0"><KpiCard label="Mensajes Hoy" :value="stats?.mensajesHoy ?? 0" icon="pi-send" hint="Enviados hoy" /></div>
                <div class="min-w-0"><KpiCard label="Abiertas" :value="stats?.abiertos ?? 0" icon="pi-inbox" hint="Casos activos" /></div>
                <div class="min-w-0"><KpiCard label="Sin Leer" :value="stats?.sinLeer ?? 0" icon="pi-envelope" hint="Con mensajes pendientes" /></div>
            </div>

            <!-- Lista + Chat -->
            <div class="admin-mensajes-grid gap-6 w-full">

                <!-- COLUMNA: CONVERSACIONES -->
                <aside class="admin-mensajes-list-col" style="grid-area:lista">
                    <div class="admin-mensajes-search-wrap">
                        <span class="admin-mensajes-search">
                            <i class="pi pi-search"></i>
                            <input v-model="q" type="text" placeholder="Buscar usuario..." />
                        </span>
                        <button class="admin-mensajes-filter-btn" type="button"><i class="pi pi-sliders-h"></i></button>
                    </div>

                    <div class="admin-mensajes-tabs">
                        <button class="admin-mensajes-tab" :class="{ active: filtro === '' }" @click="filtro = ''">Abiertas</button>
                        <button class="admin-mensajes-tab" :class="{ active: filtro === 'no-leidos' }" @click="filtro = 'no-leidos'">
                            No leídos <span v-if="stats?.sinLeer" class="admin-mensajes-badge">{{ stats.sinLeer }}</span>
                        </button>
                        <button class="admin-mensajes-tab" :class="{ active: filtro === 'cerrados' }" @click="filtro = 'cerrados'">Cerradas</button>
                    </div>

                    <div v-if="!conversaciones?.length" class="admin-mensajes-empty">
                        <i class="pi pi-comments"></i>
                        <span>No hay conversaciones</span>
                        <p>Se crean desde un reporte o al iniciar soporte manual con un usuario</p>
                    </div>

                    <div v-else class="admin-mensajes-list">
                        <button
                            v-for="conv in conversaciones"
                            :key="conv.id"
                            class="admin-mensajes-item"
                            :class="{ active: conv.id === soporteActivoId }"
                            @click="seleccionarConversacion(conv.id)"
                        >
                            <div class="admin-mensajes-item-avatar" style="width:42px;height:42px">
                                <img :src="getAvatarUrl(conv.usuario?.avatar)" :alt="conv.usuario?.nombre" style="width:100%;height:100%;position:static;border:none" />
                            </div>
                            <div class="admin-mensajes-item-body">
                                <div class="admin-mensajes-item-title">
                                    <strong>{{ conv.usuario?.nombre ?? '—' }}</strong>
                                    <i v-if="conv.origen === 'reporte'" class="pi pi-flag" title="Originada por un reporte"></i>
                                </div>
                                <p class="admin-mensajes-item-preview">{{ conv.ultimoMensaje }}</p>
                            </div>
                            <div class="admin-mensajes-item-meta">
                                <span class="time">{{ conv.ultimoMensajeEn || 'Recién' }}</span>
                                <span v-if="conv.noLeidos" class="admin-mensajes-badge admin-mensajes-badge--danger">{{ conv.noLeidos }}</span>
                            </div>
                        </button>
                    </div>
                </aside>

                <!-- COLUMNA: CHAT -->
                <section class="admin-mensajes-chat-col" style="grid-area:chat">
                    <template v-if="conversacionActiva">
                        <header class="admin-mensajes-chat-header">
                            <div class="admin-mensajes-chat-avatar" style="width:48px;height:48px">
                                <img :src="getAvatarUrl(conversacionActiva.usuario?.avatar)" :alt="conversacionActiva.usuario?.nombre" style="width:100%;height:100%;position:static;border:none" />
                            </div>
                            <div class="admin-mensajes-chat-info">
                                <strong>{{ conversacionActiva.usuario?.nombre ?? '—' }}</strong>
                                <span>
                                    {{ conversacionActiva.asunto }}
                                    <span v-if="conversacionActiva.reporteId"> · Reporte #{{ conversacionActiva.reporteId }}</span>
                                    <span> · {{ origenLabel[conversacionActiva.origen] || 'Soporte' }}</span>
                                </span>
                            </div>
                            <button v-if="conversacionActiva.estado === 'abierto'" class="admin-mensajes-filter-btn" type="button" title="Cerrar conversación" @click="cerrarConversacion">
                                <i class="pi pi-check"></i>
                            </button>
                            <span v-else class="admin-mensajes-badge">Cerrada</span>
                        </header>

                        <div ref="chatBody" class="admin-mensajes-messages">
                            <div v-for="msg in mensajes" :key="msg.id" class="admin-mensajes-bubble-row" :class="msg.esAdmin ? 'admin-mensajes-bubble-row--admin' : 'admin-mensajes-bubble-row--usuario'">
                                <img v-if="!msg.esAdmin" :src="getAvatarUrl(msg.avatar)" class="admin-mensajes-bubble-avatar" :alt="msg.remitenteNombre" />
                                <div class="admin-mensajes-bubble-col">
                                    <p class="admin-mensajes-bubble-label">{{ msg.esAdmin ? `Soporte · ${msg.remitenteNombre}` : msg.remitenteNombre }} · {{ msg.tiempo }}</p>
                                    <div class="admin-mensajes-bubble" :class="msg.esAdmin ? 'admin-mensajes-bubble--admin' : 'admin-mensajes-bubble--usuario'">{{ msg.texto }}</div>
                                </div>
                            </div>

                            <div v-if="!mensajes?.length" class="admin-mensajes-empty" style="padding:2rem 1rem">
                                <i class="pi pi-comments"></i>
                                <span>Sin mensajes todavía</span>
                                <p>Escribe el primer mensaje para iniciar la conversación</p>
                            </div>
                        </div>

                        <div class="admin-mensajes-composer">
                            <textarea v-model="mensajeTexto" rows="2" placeholder="Escribir al usuario..." @keydown.enter.exact.prevent="enviarMensaje"></textarea>
                            <div class="admin-mensajes-composer-actions">
                                <button class="admin-mensajes-send-btn" type="button" :disabled="!mensajeTexto.trim() || enviando" @click="enviarMensaje">
                                    <i class="pi pi-send"></i> Enviar
                                </button>
                            </div>
                        </div>
                    </template>

                    <div v-else class="admin-mensajes-empty">
                        <i class="pi pi-comments"></i>
                        <span>Selecciona una conversación</span>
                        <p>O inicia una nueva desde un reporte</p>
                    </div>
                </section>

            </div>
        </div>
    </AdminLayout>
</template>