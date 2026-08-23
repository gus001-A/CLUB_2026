<template>

    <Head title="Mis Suscripciones" />

    <ToastNotification ref="toastRef" :duration="5000" />

    <AppLayout active-nav="suscripciones">
        <div class="suscripciones-page">
            <!-- ============================================================ -->
            <!-- HEADER CON BANNER -->
            <!-- ============================================================ -->
            <section class="header-section">
                <div class="header-section__banner">
                    <div class="header-section__content">
                        <div class="header-section__badge">
                            <i class="pi pi-heart-fill"></i>
                            Suscripciones
                        </div>
                        <h1 class="header-section__title">
                            Gestiona tus <span class="highlight">suscripciones</span>
                        </h1>
                        <p class="header-section__subtitle">
                            Administra tus suscripciones a creadores y contenido exclusivo
                        </p>
                        <div class="header-section__alerts">
                            <span v-if="estadisticas.por_vencer > 0" class="alert-badge alert-badge--warning">
                                <i class="pi pi-exclamation-triangle"></i>
                                {{ estadisticas.por_vencer }} suscripción(es) por vencer
                            </span>
                            <span v-if="estadisticas.activas === 0" class="alert-badge alert-badge--info">
                                <i class="pi pi-info-circle"></i>
                                Aún no tienes suscripciones activas
                            </span>
                        </div>
                    </div>
                    <div class="header-section__actions">
                        <PvButton label="Explorar creadores" icon="pi pi-users" class="header-section__btn"
                            @click="irAExplorar" />
                    </div>
                </div>

                <!-- Stats -->
                <div class="header-section__stats">
                    <div class="stat-card stat-card--activas">
                        <div class="stat-card__icon">
                            <i class="pi pi-check-circle"></i>
                        </div>
                        <div class="stat-card__info">
                            <span class="stat-card__number">{{ estadisticas.activas || 0 }}</span>
                            <span class="stat-card__label">Activas</span>
                        </div>
                    </div>
                    <div class="stat-card stat-card--inactivas">
                        <div class="stat-card__icon">
                            <i class="pi pi-times-circle"></i>
                        </div>
                        <div class="stat-card__info">
                            <span class="stat-card__number">{{ estadisticas.inactivas || 0 }}</span>
                            <span class="stat-card__label">Inactivas</span>
                        </div>
                    </div>
                    <div class="stat-card stat-card--gasto">
                        <div class="stat-card__icon">
                            <i class="pi pi-money-bill"></i>
                        </div>
                        <div class="stat-card__info">
                            <span class="stat-card__number">${{ formatearNumero(estadisticas.gasto_mensual || 0)
                                }}</span>
                            <span class="stat-card__label">Gasto mensual</span>
                        </div>
                    </div>
                    <div class="stat-card stat-card--total">
                        <div class="stat-card__icon">
                            <i class="pi pi-list"></i>
                        </div>
                        <div class="stat-card__info">
                            <span class="stat-card__number">{{ estadisticas.total_suscripciones || 0 }}</span>
                            <span class="stat-card__label">Total</span>
                        </div>
                    </div>
                    <div v-if="estadisticas.por_vencer > 0" class="stat-card stat-card--por-vencer">
                        <div class="stat-card__icon">
                            <i class="pi pi-clock"></i>
                        </div>
                        <div class="stat-card__info">
                            <span class="stat-card__number">{{ estadisticas.por_vencer }}</span>
                            <span class="stat-card__label">Por vencer</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- FILTROS Y BÚSQUEDA -->
            <!-- ============================================================ -->
            <div class="filtros-section">
                <div class="filtros-section__tabs">
                    <button class="tab-btn" :class="{ 'tab-btn--active': filtro === 'activas' }"
                        @click="filtro = 'activas'">
                        <i class="pi pi-check-circle"></i>
                        Activas
                        <span class="tab-btn__badge">{{ suscripcionesActivas.length }}</span>
                    </button>
                    <button class="tab-btn" :class="{ 'tab-btn--active': filtro === 'inactivas' }"
                        @click="filtro = 'inactivas'">
                        <i class="pi pi-times-circle"></i>
                        Inactivas
                        <span class="tab-btn__badge">{{ suscripcionesInactivas.length }}</span>
                    </button>
                    <button class="tab-btn" :class="{ 'tab-btn--active': filtro === 'todas' }"
                        @click="filtro = 'todas'">
                        <i class="pi pi-list"></i>
                        Todas
                        <span class="tab-btn__badge">{{ suscripcionesActivas.length + suscripcionesInactivas.length
                            }}</span>
                    </button>
                </div>

                <div class="filtros-section__search">
                    <i class="pi pi-search search-icon"></i>
                    <input type="text" v-model="busqueda" placeholder="Buscar por nombre o apodo..."
                        class="search-input" />
                    <button v-if="busqueda" class="search-clear" @click="busqueda = ''">
                        <i class="pi pi-times-circle"></i>
                    </button>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- CONTADOR DE RESULTADOS -->
            <!-- ============================================================ -->
            <div v-if="suscripcionesFiltradas.length > 0" class="resultados-counter">
                <span>
                    <strong>{{ suscripcionesFiltradas.length }}</strong>
                    {{ suscripcionesFiltradas.length === 1 ? 'suscripción encontrada' : 'suscripciones encontradas' }}
                </span>
                <span v-if="busqueda" class="resultados-counter__filtro">
                    Filtrando por: <strong>"{{ busqueda }}"</strong>
                </span>
            </div>

            <!-- ============================================================ -->
            <!-- LISTA DE SUSCRIPCIONES - CARDS MEJORADAS -->
            <!-- ============================================================ -->
            <div v-if="suscripcionesFiltradas.length > 0" class="suscripciones-grid">
                <div v-for="suscripcion in suscripcionesFiltradas" :key="suscripcion.id" class="suscripcion-card"
                    :class="{
                        'suscripcion-card--activa': suscripcion.estado === 'activa',
                        'suscripcion-card--inactiva': suscripcion.estado !== 'activa',
                        'suscripcion-card--por-vencer': suscripcion.vence_pronto && suscripcion.estado === 'activa'
                    }">

                    <!-- Header con estado -->
                    <div class="suscripcion-card__header">
                        <div class="suscripcion-card__estado-badge" :class="{
                            'estado-activa': suscripcion.estado === 'activa',
                            'estado-cancelada': suscripcion.estado === 'cancelada',
                            'estado-expirada': suscripcion.estado === 'expirada',
                            'estado-pendiente': suscripcion.estado === 'pendiente'
                        }">
                            <i class="pi" :class="{
                                'pi-check-circle': suscripcion.estado === 'activa',
                                'pi-times-circle': suscripcion.estado === 'cancelada',
                                'pi-exclamation-circle': suscripcion.estado === 'expirada',
                                'pi-clock': suscripcion.estado === 'pendiente'
                            }"></i>
                            {{ suscripcion.estado }}
                        </div>
                        <span v-if="suscripcion.vence_pronto && suscripcion.estado === 'activa'"
                            class="suscripcion-card__vence-pronto">
                            <i class="pi pi-clock"></i>
                            Vence pronto
                        </span>
                    </div>

                    <!-- Creador -->
                    <div class="suscripcion-card__creador">
                        <div class="suscripcion-card__avatar-wrapper">
                            <img :src="getAvatarUrl(suscripcion.creador?.usuario)"
                                :alt="suscripcion.creador?.usuario?.nombre || 'Creador'"
                                class="suscripcion-card__avatar" @error="handleAvatarError" />
                            <div v-if="suscripcion.creador?.esta_verificado" class="suscripcion-card__verified-badge">
                                <i class="pi pi-verified"></i>
                            </div>
                        </div>
                        <div class="suscripcion-card__info">
                            <div class="suscripcion-card__nombre-wrapper">
                                <span class="suscripcion-card__nombre">
                                    {{ suscripcion.creador?.usuario?.nombre || 'Creador' }}
                                </span>
                                <span v-if="suscripcion.creador?.usuario?.apodo" class="suscripcion-card__apodo">
                                    @{{ suscripcion.creador.usuario.apodo }}
                                </span>
                            </div>
                            <div class="suscripcion-card__plan-wrapper">
                                <span class="suscripcion-card__plan">
                                    <i class="pi pi-crown"></i>
                                    {{ suscripcion.plan_nombre || suscripcion.plan }}
                                </span>
                                <span class="suscripcion-card__precio">
                                    ${{ Number(suscripcion.precio).toFixed(2) }}
                                </span>
                            </div>
                            <div class="suscripcion-card__meta">
                                <span v-if="suscripcion.creador?.total_suscriptores"
                                    class="suscripcion-card__suscriptores">
                                    <i class="pi pi-users"></i>
                                    {{ formatearNumero(suscripcion.creador.total_suscriptores) }} seguidores
                                </span>
                                <span v-if="suscripcion.creador?.categorias?.length"
                                    class="suscripcion-card__categorias">
                                    <i class="pi pi-tag"></i>
                                    {{ suscripcion.creador.categorias.slice(0, 2).join(', ') }}
                                    <span v-if="suscripcion.creador.categorias.length > 2">
                                        +{{ suscripcion.creador.categorias.length - 2 }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles -->
                    <div class="suscripcion-card__detalles">
                        <div class="detalle-item">
                            <i class="pi pi-calendar"></i>
                            <div>
                                <span class="detalle-item__label">Inicio</span>
                                <span class="detalle-item__value">{{ formatearFecha(suscripcion.fecha_inicio) }}</span>
                            </div>
                        </div>
                        <div class="detalle-item">
                            <i class="pi pi-calendar-plus"></i>
                            <div>
                                <span class="detalle-item__label">Renovación</span>
                                <span class="detalle-item__value">{{ formatearFecha(suscripcion.fecha_renovacion)
                                    }}</span>
                            </div>
                        </div>
                        <div class="detalle-item"
                            v-if="suscripcion.estado === 'activa' && suscripcion.dias_restantes !== null">
                            <i class="pi pi-clock"></i>
                            <div>
                                <span class="detalle-item__label">Tiempo restante</span>
                                <span class="detalle-item__value detalle-item__value--dias" :class="{
                                    'dias-peligro': suscripcion.dias_restantes <= 3,
                                    'dias-warning': suscripcion.dias_restantes <= 7 && suscripcion.dias_restantes > 3,
                                }">
                                    {{ Math.round(suscripcion.dias_restantes) }} días
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Progress bar -->
                    <div v-if="suscripcion.estado === 'activa' && suscripcion.dias_restantes !== null"
                        class="progress-section">
                        <div class="progress-section__bar">
                            <div class="progress-section__fill" :style="{ width: calcularProgreso(suscripcion) + '%' }"
                                :class="{
                                    'progress--peligro': suscripcion.dias_restantes <= 3,
                                    'progress--warning': suscripcion.dias_restantes <= 7 && suscripcion.dias_restantes > 3,
                                }">
                            </div>
                        </div>
                        <div class="progress-section__info">
                            <span class="progress-section__label">
                                {{ Math.round(suscripcion.dias_restantes) }} días restantes
                            </span>
                            <span class="progress-section__porcentaje">
                                {{ Math.round(calcularProgreso(suscripcion)) }}%
                            </span>
                        </div>
                    </div>

                    <!-- Footer con acciones -->
                    <div class="suscripcion-card__footer">
                        <PvButton label="Ver perfil" icon="pi pi-user"
                            class="suscripcion-card__btn suscripcion-card__btn--secondary"
                            @click="verCreador(suscripcion.creador_id)" />

                        <template v-if="suscripcion.estado === 'activa'">
                            <PvButton label="Cancelar" icon="pi pi-times"
                                class="suscripcion-card__btn suscripcion-card__btn--danger"
                                @click="abrirConfirmacionCancelar(suscripcion)" />
                        </template>

                        <template
                            v-if="suscripcion.estado === 'cancelada' && suscripcion.fecha_renovacion > new Date()">
                            <PvButton label="Reactivar" icon="pi pi-refresh"
                                class="suscripcion-card__btn suscripcion-card__btn--success"
                                @click="abrirConfirmacionReactivar(suscripcion)" />
                        </template>

                        <template v-if="suscripcion.estado === 'expirada'">
                            <PvButton label="Renovar" icon="pi pi-arrow-right"
                                class="suscripcion-card__btn suscripcion-card__btn--primary"
                                @click="renovarSuscripcion(suscripcion)" />
                        </template>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- EMPTY STATE -->
            <!-- ============================================================ -->
            <div v-else class="empty-state">
                <div class="empty-state__icon-wrapper">
                    <i class="pi pi-heart"></i>
                </div>
                <h3 class="empty-state__title">
                    {{ filtro === 'activas' ? 'No tienes suscripciones activas' :
                        filtro === 'inactivas' ? 'No tienes suscripciones inactivas' :
                            'Aún no tienes suscripciones' }}
                </h3>
                <p class="empty-state__text">
                    <template v-if="filtro === 'activas'">
                        Explora creadores y suscríbete para acceder a contenido exclusivo.
                    </template>
                    <template v-else-if="filtro === 'inactivas'">
                        Las suscripciones que canceles o expiren aparecerán aquí.
                    </template>
                    <template v-else>
                        Comienza a explorar creadores y descubre contenido exclusivo.
                    </template>
                </p>
                <PvButton label="Explorar creadores" icon="pi pi-users" class="empty-state__btn" @click="irAExplorar" />
            </div>

            <!-- ============================================================ -->
            <!-- RECOMENDACIONES -->
            <!-- ============================================================ -->
            <section v-if="recomendaciones && recomendaciones.length > 0" class="recomendaciones-section">
                <div class="recomendaciones-section__header">
                    <h2>
                        <i class="pi pi-users"></i>
                        Creadores que te pueden interesar
                    </h2>
                    <a href="#" @click.prevent="irAExplorar" class="see-all">
                        Ver todos <i class="pi pi-chevron-right"></i>
                    </a>
                </div>

                <div class="recomendaciones-grid">
                    <div v-for="creador in recomendaciones" :key="creador.id" class="recomendacion-card">
                        <div class="recomendacion-card__avatar-wrapper">
                            <img :src="getAvatarUrl(creador.usuario)" :alt="creador.usuario?.nombre || 'Creador'"
                                class="recomendacion-card__avatar" @error="handleAvatarError" />
                            <div v-if="creador.esta_verificado" class="recomendacion-card__verified-badge">
                                <i class="pi pi-verified"></i>
                            </div>
                        </div>
                        <div class="recomendacion-card__info">
                            <span class="recomendacion-card__nombre">
                                {{ creador.usuario?.nombre || 'Creador' }}
                            </span>
                            <span v-if="creador.usuario?.apodo" class="recomendacion-card__apodo">
                                @{{ creador.usuario.apodo }}
                            </span>
                            <span class="recomendacion-card__categoria">
                                {{ creador.categoria || 'Creador de contenido' }}
                            </span>
                            <span v-if="creador.total_suscriptores" class="recomendacion-card__suscriptores">
                                <i class="pi pi-users"></i>
                                {{ formatearNumero(creador.total_suscriptores) }} seguidores
                            </span>
                        </div>
                        <PvButton label="Suscribirse" icon="pi pi-heart" class="recomendacion-card__btn"
                            @click="irASuscribirse(creador.id)" />
                    </div>
                </div>
            </section>
        </div>

        <!-- ✅ Confirmación personalizada -->
        <ConfirmarSuscripcion v-model="mostrarConfirmacion" :title="confirmacionData.title"
            :message="confirmacionData.message" :danger="confirmacionData.danger"
            :confirm-label="confirmacionData.confirmLabel" :cancel-label="confirmacionData.cancelLabel"
            @confirm="ejecutarAccionConfirmada" />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import PvButton from 'primevue/button';
import ConfirmarSuscripcion from '@/Components/ConfirmarSuscripcion.vue';

// ============================================================
// PROPS
// ============================================================
const props = defineProps({
    suscripcionesActivas: {
        type: Array,
        default: () => []
    },
    suscripcionesInactivas: {
        type: Array,
        default: () => []
    },
    estadisticas: {
        type: Object,
        default: () => ({
            total_suscripciones: 0,
            activas: 0,
            inactivas: 0,
            gasto_mensual: 0,
            por_vencer: 0
        })
    },
    recomendaciones: {
        type: Array,
        default: () => []
    },
    usuarioActual: {
        type: Object,
        default: () => ({
            id: null,
            nombre: 'Usuario',
            foto_principal: null
        })
    }
});

// ============================================================
// REFERENCIAS
// ============================================================
const toastRef = ref(null);
const filtro = ref('activas');
const busqueda = ref('');
const mostrarConfirmacion = ref(false);
const confirmacionData = ref({
    title: 'Confirmar',
    message: '¿Estás seguro?',
    danger: false,
    confirmLabel: 'Confirmar',
    cancelLabel: 'Cancelar'
});
const accionPendiente = ref(null);
const suscripcionPendiente = ref(null);

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
// FUNCIÓN PARA OBTENER AVATAR
// ============================================================
function getAvatarUrl(usuario) {
    if (!usuario) {
        return '/images/shared/avatar-default.jpg';
    }

    if (usuario.foto_principal) {
        if (usuario.foto_principal.startsWith('http://') || usuario.foto_principal.startsWith('https://')) {
            return usuario.foto_principal;
        }
        if (usuario.foto_principal.startsWith('/')) {
            return usuario.foto_principal;
        }
        return '/storage/' + usuario.foto_principal;
    }

    return '/images/shared/avatar-default.jpg';
}

function handleAvatarError(event) {
    event.target.src = '/images/shared/avatar-default.jpg';
    event.target.onerror = null;
}

// ============================================================
// SUSCRIPCIONES FILTRADAS
// ============================================================
const suscripcionesFiltradas = computed(() => {
    let lista = [];

    if (filtro.value === 'activas') {
        lista = [...props.suscripcionesActivas];
    } else if (filtro.value === 'inactivas') {
        lista = [...props.suscripcionesInactivas];
    } else {
        lista = [...props.suscripcionesActivas, ...props.suscripcionesInactivas];
    }

    if (busqueda.value.trim()) {
        const termino = busqueda.value.toLowerCase().trim();
        lista = lista.filter(s =>
            s.creador?.usuario?.nombre?.toLowerCase().includes(termino) ||
            s.creador?.usuario?.apodo?.toLowerCase().includes(termino) ||
            s.plan?.toLowerCase().includes(termino)
        );
    }

    return lista;
});

// ============================================================
// FUNCIONES DE FORMATO
// ============================================================
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
    if (numero >= 1000000) {
        return (numero / 1000000).toFixed(1) + 'M';
    }
    if (numero >= 1000) {
        return (numero / 1000).toFixed(1) + 'K';
    }
    return numero.toFixed(0);
}

function calcularProgreso(suscripcion) {
    if (!suscripcion.fecha_inicio || !suscripcion.fecha_renovacion) return 0;

    const inicio = new Date(suscripcion.fecha_inicio);
    const fin = new Date(suscripcion.fecha_renovacion);
    const ahora = new Date();

    const total = fin - inicio;
    const transcurrido = ahora - inicio;

    if (total <= 0) return 0;
    const progreso = (transcurrido / total) * 100;
    return Math.min(Math.max(progreso, 0), 100);
}

// ============================================================
// ACCIONES DE NAVEGACIÓN - CORREGIDAS ✅
// ============================================================
function verCreador(creadorId) {
    // Redirige al perfil público del creador
    router.get(`/creador/${creadorId}`);
}

function irAExplorar() {
    // ✅ CORREGIDO: Usar la ruta correcta para explorar creadores
    // Si tienes una ruta específica para explorar creadores, úsala
    // Por ahora, redirige a la comunidad o al listado de creadores
    router.get('/creador/comunidad');
}

function irASuscribirse(creadorId) {
    // Redirige a la página de suscripción del creador
    router.get(`/creador/${creadorId}/suscripcion`);
}

function renovarSuscripcion(suscripcion) {
    router.get(`/creador/${suscripcion.creador_id}/suscripcion`);
}

// ============================================================
// CONFIRMACIÓN PERSONALIZADA
// ============================================================
function abrirConfirmacionCancelar(suscripcion) {
    const nombreCreador = suscripcion.creador?.usuario?.nombre || 'este creador';
    const nombreApodo = suscripcion.creador?.usuario?.apodo || '';
    const nombreCompleto = nombreApodo ? `${nombreCreador} (@${nombreApodo})` : nombreCreador;

    confirmacionData.value = {
        title: '¿Cancelar suscripción?',
        message: `¿Estás seguro de que deseas cancelar tu suscripción a <strong>${nombreCompleto}</strong>?<br><br>Podrás reactivarla antes de la fecha de renovación.`,
        danger: true,
        confirmLabel: 'Sí, cancelar',
        cancelLabel: 'No, mantener'
    };
    accionPendiente.value = 'cancelar';
    suscripcionPendiente.value = suscripcion;
    mostrarConfirmacion.value = true;
}

function abrirConfirmacionReactivar(suscripcion) {
    const nombreCreador = suscripcion.creador?.usuario?.nombre || 'este creador';
    const nombreApodo = suscripcion.creador?.usuario?.apodo || '';
    const nombreCompleto = nombreApodo ? `${nombreCreador} (@${nombreApodo})` : nombreCreador;

    confirmacionData.value = {
        title: '¿Reactivar suscripción?',
        message: `¿Deseas reactivar tu suscripción a <strong>${nombreCompleto}</strong>?<br><br>Se mantendrá la fecha de renovación actual.`,
        danger: false,
        confirmLabel: 'Sí, reactivar',
        cancelLabel: 'No, cancelar'
    };
    accionPendiente.value = 'reactivar';
    suscripcionPendiente.value = suscripcion;
    mostrarConfirmacion.value = true;
}

function ejecutarAccionConfirmada() {
    if (accionPendiente.value === 'cancelar') {
        cancelarSuscripcion(suscripcionPendiente.value.id);
    } else if (accionPendiente.value === 'reactivar') {
        reactivarSuscripcion(suscripcionPendiente.value);
    }
    accionPendiente.value = null;
    suscripcionPendiente.value = null;
}

// ============================================================
// CANCELAR SUSCRIPCIÓN
// ============================================================
async function cancelarSuscripcion(id) {
    try {
        const response = await axios.post(`/suscripciones/${id}/cancelar`);

        if (response.data && response.data.ok) {
            showSuccess(response.data.mensaje);
            setTimeout(() => {
                router.reload();
            }, 1500);
        } else {
            showError(response.data?.mensaje || 'Error al cancelar la suscripción');
        }
    } catch (error) {
        console.error('Error al cancelar suscripción:', error);
        showError(error.response?.data?.mensaje || 'Ocurrió un error al cancelar la suscripción');
    }
}

// ============================================================
// REACTIVAR SUSCRIPCIÓN
// ============================================================
async function reactivarSuscripcion(suscripcion) {
    try {
        const response = await axios.post(`/suscripciones/${suscripcion.id}/reactivar`);

        if (response.data && response.data.ok) {
            showSuccess(response.data.mensaje);
            setTimeout(() => {
                router.reload();
            }, 1500);
        } else {
            showError(response.data?.mensaje || 'Error al reactivar la suscripción');
        }
    } catch (error) {
        console.error('Error al reactivar suscripción:', error);
        showError(error.response?.data?.mensaje || 'Ocurrió un error al reactivar la suscripción');
    }
}

// ============================================================
// LIFECYCLE
// ============================================================
onMounted(() => {
    console.log('=== MIS SUSCRIPCIONES ===');
    console.log('Suscripciones activas:', props.suscripcionesActivas);
    console.log('Suscripciones inactivas:', props.suscripcionesInactivas);
    console.log('Estadísticas:', props.estadisticas);
    console.log('Recomendaciones:', props.recomendaciones);
});
</script>

<style scoped>
/* ============================================================
   ESTILOS COMPLETOS - IGUALES AL ANTERIOR
   ============================================================ */
.suscripciones-page {
    --brand: #C81E3A;
    --brand-dark: #A6152D;
    --brand-soft: #FBEAEC;
    --success: #2F855A;
    --success-bg: #F0FFF4;
    --danger: #C53030;
    --danger-bg: #FFF5F5;
    --warning: #DD6B20;
    --warning-bg: #FFFAF0;
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
    padding: 1rem 2rem 2rem;
}

/* =========================================================================
   HEADER
   ========================================================================= */
.header-section {
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: 1.5rem 2rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--line);
}

.header-section__banner {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.header-section__content {
    flex: 1;
}

.header-section__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--brand);
    background: var(--brand-soft);
    padding: 0.2rem 0.8rem;
    border-radius: var(--radius-full);
    margin-bottom: 0.5rem;
}

.header-section__badge i {
    font-size: 0.6rem;
}

.header-section__title {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0 0 0.25rem;
    color: var(--ink);
}

.header-section__title .highlight {
    color: var(--brand);
}

.header-section__subtitle {
    color: var(--muted);
    margin: 0;
    font-size: 0.95rem;
}

.header-section__alerts {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.alert-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.15rem 0.6rem;
    border-radius: var(--radius-full);
}

.alert-badge--warning {
    background: var(--warning-bg);
    color: var(--warning);
}

.alert-badge--info {
    background: #EBF8FF;
    color: #2B6CB0;
}

.header-section__btn {
    background: var(--brand) !important;
    border-color: var(--brand) !important;
    color: var(--white) !important;
    font-weight: 600 !important;
    padding: 0.5rem 1.5rem !important;
}

.header-section__btn:hover {
    background: var(--brand-dark) !important;
    border-color: var(--brand-dark) !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.header-section__stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 0.8rem;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.6rem 1rem;
    background: var(--surface);
    border-radius: var(--radius-sm);
    border: 1px solid var(--line);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.stat-card__icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.stat-card--activas .stat-card__icon {
    background: var(--success-bg);
    color: var(--success);
}

.stat-card--inactivas .stat-card__icon {
    background: var(--danger-bg);
    color: var(--danger);
}

.stat-card--gasto .stat-card__icon {
    background: var(--brand-soft);
    color: var(--brand);
}

.stat-card--total .stat-card__icon {
    background: var(--surface);
    color: var(--muted);
}

.stat-card--por-vencer .stat-card__icon {
    background: var(--warning-bg);
    color: var(--warning);
}

.stat-card__info {
    display: flex;
    flex-direction: column;
}

.stat-card__number {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--ink);
    line-height: 1.2;
}

.stat-card__label {
    font-size: 0.6rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* =========================================================================
   FILTROS
   ========================================================================= */
.filtros-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
    background: var(--white);
    padding: 0.8rem 1.5rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
}

.filtros-section__tabs {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.tab-btn {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.8rem;
    border: 1px solid var(--line);
    border-radius: var(--radius-full);
    background: var(--white);
    color: var(--ink-soft);
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.tab-btn:hover {
    border-color: var(--brand);
    color: var(--brand);
}

.tab-btn--active {
    background: var(--brand);
    color: var(--white);
    border-color: var(--brand);
}

.tab-btn--active:hover {
    background: var(--brand-dark);
    border-color: var(--brand-dark);
}

.tab-btn__badge {
    background: var(--brand-soft);
    color: var(--brand);
    padding: 0.05rem 0.4rem;
    border-radius: var(--radius-full);
    font-size: 0.6rem;
    font-weight: 700;
}

.tab-btn--active .tab-btn__badge {
    background: rgba(255, 255, 255, 0.2);
    color: var(--white);
}

.filtros-section__search {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 0.8rem;
    color: var(--muted-light);
    font-size: 0.8rem;
}

.search-input {
    padding: 0.4rem 2rem 0.4rem 2.4rem;
    border: 1px solid var(--line);
    border-radius: var(--radius-full);
    font-size: 0.8rem;
    outline: none;
    min-width: 220px;
    transition: all 0.3s ease;
}

.search-input:focus {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px var(--brand-soft);
}

.search-clear {
    position: absolute;
    right: 0.5rem;
    background: none;
    border: none;
    color: var(--muted-light);
    cursor: pointer;
    padding: 0.2rem;
    font-size: 0.8rem;
}

.search-clear:hover {
    color: var(--danger);
}

/* =========================================================================
   RESULTADOS COUNTER
   ========================================================================= */
.resultados-counter {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.4rem 0;
    font-size: 0.85rem;
    color: var(--muted);
}

.resultados-counter strong {
    color: var(--ink);
}

.resultados-counter__filtro {
    font-size: 0.75rem;
}

/* =========================================================================
   SUSCRIPCIONES GRID
   ========================================================================= */
.suscripciones-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 1.2rem;
    margin-bottom: 2rem;
}

.suscripcion-card {
    background: var(--white);
    border-radius: var(--radius-md);
    padding: 1.2rem 1.5rem;
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.suscripcion-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
}

.suscripcion-card--activa::before {
    background: var(--success);
}

.suscripcion-card--inactiva::before {
    background: var(--muted-light);
}

.suscripcion-card--por-vencer::before {
    background: var(--warning);
}

.suscripcion-card:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-3px);
}

.suscripcion-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.suscripcion-card__estado-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.15rem 0.6rem;
    border-radius: var(--radius-full);
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: capitalize;
}

.estado-activa {
    background: var(--success-bg);
    color: var(--success);
}

.estado-cancelada {
    background: var(--danger-bg);
    color: var(--danger);
}

.estado-expirada {
    background: var(--warning-bg);
    color: var(--warning);
}

.estado-pendiente {
    background: #EBF8FF;
    color: #2B6CB0;
}

.suscripcion-card__vence-pronto {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.65rem;
    font-weight: 600;
    color: var(--warning);
    background: var(--warning-bg);
    padding: 0.15rem 0.6rem;
    border-radius: var(--radius-full);
}

.suscripcion-card__creador {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.suscripcion-card__avatar-wrapper {
    position: relative;
    flex-shrink: 0;
}

.suscripcion-card__avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--line);
    transition: all 0.3s ease;
}

.suscripcion-card__avatar:hover {
    transform: scale(1.05);
}

.suscripcion-card__verified-badge {
    position: absolute;
    bottom: -2px;
    right: -2px;
    background: var(--brand);
    color: var(--white);
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--white);
    font-size: 0.55rem;
}

.suscripcion-card__info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.suscripcion-card__nombre-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.suscripcion-card__nombre {
    font-size: 1rem;
    font-weight: 700;
    color: var(--ink);
}

.suscripcion-card__apodo {
    font-size: 0.75rem;
    color: var(--muted);
    font-weight: 400;
}

.suscripcion-card__plan-wrapper {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.suscripcion-card__plan {
    font-size: 0.8rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.suscripcion-card__precio {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--brand);
}

.suscripcion-card__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    font-size: 0.65rem;
    color: var(--muted-light);
}

.suscripcion-card__suscriptores,
.suscripcion-card__categorias {
    display: flex;
    align-items: center;
    gap: 0.2rem;
}

.suscripcion-card__detalles {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.5rem;
    padding: 0.8rem 0;
    border-top: 1px solid var(--line);
    border-bottom: 1px solid var(--line);
    margin-bottom: 0.8rem;
}

.detalle-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detalle-item i {
    color: var(--brand);
    font-size: 0.8rem;
    width: 20px;
}

.detalle-item div {
    display: flex;
    flex-direction: column;
}

.detalle-item__label {
    font-size: 0.55rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--muted-light);
}

.detalle-item__value {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ink);
}

.detalle-item__value--dias {
    font-weight: 700;
}

.dias-peligro {
    color: var(--danger);
}

.dias-warning {
    color: var(--warning);
}

/* =========================================================================
   PROGRESS BAR
   ========================================================================= */
.progress-section {
    margin-bottom: 0.8rem;
}

.progress-section__bar {
    width: 100%;
    height: 6px;
    background: var(--line);
    border-radius: 3px;
    overflow: hidden;
}

.progress-section__fill {
    height: 100%;
    background: var(--success);
    border-radius: 3px;
    transition: width 0.8s ease;
}

.progress--warning {
    background: var(--warning);
}

.progress--peligro {
    background: var(--danger);
}

.progress-section__info {
    display: flex;
    justify-content: space-between;
    margin-top: 0.2rem;
}

.progress-section__label {
    font-size: 0.65rem;
    color: var(--muted);
}

.progress-section__porcentaje {
    font-size: 0.65rem;
    font-weight: 600;
    color: var(--ink-soft);
}

/* =========================================================================
   FOOTER
   ========================================================================= */
.suscripcion-card__footer {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.suscripcion-card__btn {
    font-size: 0.7rem !important;
    padding: 0.3rem 0.8rem !important;
}

.suscripcion-card__btn--primary {
    background: var(--brand) !important;
    border-color: var(--brand) !important;
    color: var(--white) !important;
}

.suscripcion-card__btn--secondary {
    background: var(--surface) !important;
    border-color: var(--line) !important;
    color: var(--ink) !important;
}

.suscripcion-card__btn--danger {
    background: var(--danger) !important;
    border-color: var(--danger) !important;
    color: var(--white) !important;
}

.suscripcion-card__btn--success {
    background: var(--success) !important;
    border-color: var(--success) !important;
    color: var(--white) !important;
}

/* =========================================================================
   EMPTY STATE
   ========================================================================= */
.empty-state {
    background: var(--white);
    border-radius: var(--radius-md);
    padding: 3rem 2rem;
    text-align: center;
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
}

.empty-state__icon-wrapper {
    width: 80px;
    height: 80px;
    margin: 0 auto 1rem;
    background: var(--brand-soft);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-state__icon-wrapper i {
    font-size: 2.5rem;
    color: var(--brand);
}

.empty-state__title {
    font-size: 1.2rem;
    margin: 0 0 0.5rem;
    color: var(--ink);
}

.empty-state__text {
    color: var(--muted);
    font-size: 0.9rem;
    margin: 0 0 1.5rem;
}

.empty-state__btn {
    background: var(--brand) !important;
    border-color: var(--brand) !important;
    color: var(--white) !important;
    font-weight: 600 !important;
}

.empty-state__btn:hover {
    background: var(--brand-dark) !important;
    border-color: var(--brand-dark) !important;
}

/* =========================================================================
   RECOMENDACIONES
   ========================================================================= */
.recomendaciones-section {
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: 1.5rem 2rem;
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
}

.recomendaciones-section__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.recomendaciones-section__header h2 {
    font-size: 1.1rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.see-all {
    color: var(--brand);
    font-size: 0.8rem;
    font-weight: 600;
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

.recomendaciones-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.recomendacion-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem 1rem;
    background: var(--surface);
    border-radius: var(--radius-md);
    border: 1px solid var(--line);
    text-align: center;
    transition: all 0.3s ease;
}

.recomendacion-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover);
}

.recomendacion-card__avatar-wrapper {
    position: relative;
    margin-bottom: 0.5rem;
}

.recomendacion-card__avatar {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--line);
}

.recomendacion-card__verified-badge {
    position: absolute;
    bottom: -2px;
    right: -2px;
    background: var(--brand);
    color: var(--white);
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--white);
    font-size: 0.6rem;
}

.recomendacion-card__info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.1rem;
    margin-bottom: 0.5rem;
}

.recomendacion-card__nombre {
    font-size: 0.95rem;
    font-weight: 700;
}

.recomendacion-card__apodo {
    font-size: 0.7rem;
    color: var(--muted);
}

.recomendacion-card__categoria {
    font-size: 0.75rem;
    color: var(--muted);
}

.recomendacion-card__suscriptores {
    font-size: 0.65rem;
    color: var(--muted-light);
    display: flex;
    align-items: center;
    gap: 0.2rem;
}

.recomendacion-card__btn {
    width: 100%;
    font-size: 0.7rem !important;
    padding: 0.4rem !important;
    background: var(--brand) !important;
    border-color: var(--brand) !important;
    color: var(--white) !important;
}

.recomendacion-card__btn:hover {
    background: var(--brand-dark) !important;
    border-color: var(--brand-dark) !important;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
    .suscripciones-page {
        padding: 1rem;
    }

    .suscripciones-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .header-section {
        padding: 1rem;
    }

    .header-section__banner {
        flex-direction: column;
        align-items: stretch;
    }

    .header-section__title {
        font-size: 1.4rem;
    }

    .header-section__stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .suscripciones-grid {
        grid-template-columns: 1fr;
    }

    .filtros-section {
        flex-direction: column;
        align-items: stretch;
        gap: 0.8rem;
    }

    .filtros-section__tabs {
        justify-content: center;
    }

    .filtros-section__search {
        width: 100%;
    }

    .search-input {
        width: 100%;
        min-width: unset;
    }

    .suscripcion-card__detalles {
        grid-template-columns: 1fr;
    }

    .suscripcion-card__footer {
        flex-direction: column;
    }

    .suscripcion-card__btn {
        width: 100% !important;
        justify-content: center !important;
    }

    .recomendaciones-grid {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    }

    .recomendaciones-section {
        padding: 1rem;
    }

    .resultados-counter {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .suscripciones-page {
        padding: 0.5rem;
    }

    .header-section__stats {
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }

    .stat-card {
        padding: 0.4rem 0.6rem;
    }

    .stat-card__number {
        font-size: 1rem;
    }

    .suscripcion-card {
        padding: 0.8rem 1rem;
    }

    .suscripcion-card__creador {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .suscripcion-card__info {
        align-items: center;
    }

    .suscripcion-card__nombre-wrapper {
        justify-content: center;
    }

    .suscripcion-card__meta {
        justify-content: center;
    }

    .suscripcion-card__header {
        flex-direction: column;
        align-items: stretch;
        gap: 0.3rem;
    }

    .recomendaciones-grid {
        grid-template-columns: 1fr 1fr;
    }
}
</style>