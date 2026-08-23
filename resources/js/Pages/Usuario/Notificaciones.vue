<script setup>
import { ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';

const props = defineProps({
    notificaciones: { type: Array, default: () => [] },
    no_leidas: { type: Number, default: 0 },
});

const page = usePage();
const lista = ref([...props.notificaciones]);
const noLeidas = ref(props.no_leidas);

async function marcarComoLeidas() {
    if (noLeidas.value === 0) return;
    try {
        await axios.post('/notificaciones/marcar-leidas');
        lista.value = lista.value.map(n => ({ ...n, leida: true }));
        noLeidas.value = 0;
    } catch (e) {
        console.error('No se pudieron marcar como leídas:', e);
    }
}

async function clickNotificacion(notificacion) {
    if (!notificacion.leida) {
        try {
            await axios.post(`/notificaciones/${notificacion.id}/marcar-leida`);
            notificacion.leida = true;
            noLeidas.value = Math.max(0, noLeidas.value - 1);
        } catch (e) {
            console.error('No se pudo marcar como leída:', e);
        }
    }

    if (notificacion.tipo === 'like' || notificacion.tipo === 'comentario' || notificacion.tipo === 'contenido_nuevo') {
        router.visit(`/contenido/${notificacion.contenido_id}`);
    } else if (notificacion.tipo === 'suscripcion' || notificacion.tipo === 'suscripcion_vencida') {
        router.visit(notificacion.link || '/suscripciones');
    } else if (notificacion.tipo === 'match' || notificacion.tipo === 'mensaje') {
        router.visit('/mensajes');
    } else if (notificacion.tipo === 'seguidor') {
        router.visit(`/creador/${notificacion.usuario_id}`);
    } else if (notificacion.link) {
        router.visit(notificacion.link);
    }
}

const iconos = {
    like: 'pi pi-heart-fill',
    comentario: 'pi pi-comment',
    suscripcion: 'pi pi-crown',
    match: 'pi pi-heart',
    seguidor: 'pi pi-user-plus',
    mensaje: 'pi pi-envelope',
    suscripcion_vencida: 'pi pi-exclamation-circle',
    contenido_nuevo: 'pi pi-images',
    perfil_like: 'pi pi-sparkles',
};
function iconoDe(tipo) {
    return iconos[tipo] || 'pi pi-bell';
}
function claseDe(tipo) {
    const mapa = {
        like: 'notif-like', comentario: 'notif-comentario', suscripcion: 'notif-suscripcion',
        match: 'notif-match', seguidor: 'notif-seguidor', mensaje: 'notif-mensaje',
        suscripcion_vencida: 'notif-vencida', contenido_nuevo: 'notif-contenido', perfil_like: 'notif-perfil-like',
    };
    return mapa[tipo] || '';
}
function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head title="Notificaciones" />
    <AppLayout active-nav="" :usuario="page.props.usuario">
        <div class="notif-page">
            <div class="notif-page__header">
                <h1>Notificaciones</h1>
                <button v-if="noLeidas > 0" class="notif-page__marcar" @click="marcarComoLeidas">
                    Marcar todas como leídas ({{ noLeidas }})
                </button>
            </div>

            <div v-if="lista.length === 0" class="notif-page__vacio">
                <i class="pi pi-inbox"></i>
                <p>No tienes notificaciones todavía</p>
            </div>

            <div v-else class="notif-page__lista">
                <div
                    v-for="n in lista"
                    :key="n.id"
                    class="notif-row"
                    :class="{ 'notif-row--no-leida': !n.leida }"
                    @click="clickNotificacion(n)"
                >
                    <div class="notif-row__icon" :class="claseDe(n.tipo)">
                        <i :class="iconoDe(n.tipo)"></i>
                    </div>
                    <div class="notif-row__body">
                        <div class="notif-row__mensaje" v-html="n.mensaje"></div>
                        <span class="notif-row__tiempo">{{ formatearFecha(n.created_at) }}</span>
                    </div>
                    <div v-if="!n.leida" class="notif-row__dot"></div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.notif-page {
    --brand: #C81E3A;
    --ink: #171412;
    --ink-soft: #4B4744;
    --muted-light: #B7B2AF;
    --line: #ECE9E7;
    --surface: #FAF8F7;
    max-width: 720px;
    margin: 0 auto;
    padding: 2rem 1.5rem 4rem;
    font-family: 'Inter', system-ui, sans-serif;
}
.notif-page__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.notif-page__header h1 {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.6rem;
    margin: 0;
    color: var(--ink);
}
.notif-page__marcar {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--brand);
    background: none;
    border: 1.5px solid var(--brand);
    border-radius: 999px;
    padding: 0.5rem 1rem;
    cursor: pointer;
}
.notif-page__marcar:hover { background: var(--brand); color: #fff; }

.notif-page__vacio {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 4rem 0;
    color: var(--muted-light);
}
.notif-page__vacio i { font-size: 3rem; margin-bottom: 0.8rem; }

.notif-page__lista {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 16px;
    overflow: hidden;
}
.notif-row {
    display: flex;
    align-items: flex-start;
    gap: 0.9rem;
    padding: 1rem 1.25rem;
    cursor: pointer;
    border-bottom: 1px solid var(--line);
    transition: background 0.15s ease;
}
.notif-row:last-child { border-bottom: none; }
.notif-row:hover { background: var(--surface); }
.notif-row--no-leida { background: rgba(200, 30, 58, 0.04); }

.notif-row__icon {
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 0.9rem;
}
.notif-like { background: #FEE2E2; color: #EF4444; }
.notif-comentario { background: #DBEAFE; color: #3B82F6; }
.notif-suscripcion { background: #FEF3C7; color: #F59E0B; }
.notif-match { background: #FCE4EC; color: #EC407A; }
.notif-seguidor { background: #E8F5E9; color: #4CAF50; }
.notif-mensaje { background: #E0F2FE; color: #0284C7; }
.notif-vencida { background: #FEE2E2; color: #DC2626; }
.notif-contenido { background: #F3E8FF; color: #9333EA; }
.notif-perfil-like { background: #FFE4EC; color: #DB2777; }

.notif-row__body { flex: 1; min-width: 0; }
.notif-row__mensaje { font-size: 0.9rem; color: var(--ink); line-height: 1.45; }
.notif-row__mensaje :deep(strong) { font-weight: 700; }
.notif-row__tiempo { font-size: 0.72rem; color: var(--muted-light); display: block; margin-top: 0.2rem; }
.notif-row__dot { width: 9px; height: 9px; border-radius: 50%; background: var(--brand); flex-shrink: 0; margin-top: 0.45rem; }
</style>
