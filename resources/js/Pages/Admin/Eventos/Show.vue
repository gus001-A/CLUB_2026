<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
    evento: Object,
});

const { confirm } = useConfirm();
const toast = useToast();
const { money, formatDate } = useFormatters();

const estadoLabel = { en_vivo: 'En vivo', programado: 'Programado', completado: 'Completado', cancelado: 'Cancelado', borrador: 'Borrador' };
const tipoLabel = { vip: 'VIP', general: 'General' };

// ============================================================
// COMPUTADAS
// ============================================================
const publicacionColor = computed(() => {
    const colores = {
        publicado: { bg: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-200', dot: 'bg-emerald-500', label: 'Publicado' },
        borrador: { bg: 'bg-amber-50', text: 'text-amber-700', border: 'border-amber-200', dot: 'bg-amber-500', label: 'Borrador' },
        cancelado: { bg: 'bg-gray-50', text: 'text-gray-500', border: 'border-gray-200', dot: 'bg-gray-400', label: 'Cancelado' },
        completo: { bg: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-200', dot: 'bg-blue-500', label: 'Completado' },
    };
    return colores[props.evento.estado] ?? colores.borrador;
});

const estadoActualColor = computed(() => {
    const colores = {
        en_vivo: { bg: 'bg-red-50', text: 'text-red-700', border: 'border-red-200', dot: 'bg-red-500', label: 'En vivo ahora' },
        programado: { bg: 'bg-orange-50', text: 'text-orange-700', border: 'border-orange-200', dot: 'bg-orange-500', label: 'Programado' },
        completado: { bg: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-200', dot: 'bg-emerald-500', label: 'Completado' },
    };
    return colores[props.evento.estado_display] ?? colores.programado;
});

async function eliminar() {
    const ok = await confirm(`Esto eliminará el evento "${props.evento.nombre}". Esta acción no se puede deshacer.`, {
        title: 'Eliminar evento',
        confirmLabel: 'Sí, eliminar',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('admin.eventos.destroy', props.evento.id), {
        onSuccess: () => toast.success(`Evento "${props.evento.nombre}" eliminado correctamente.`),
        onError: () => toast.error('No se pudo eliminar el evento.'),
    });
}
</script>

<template>
    <Head :title="evento.nombre" />

    <AdminLayout>
        <template #title>{{ evento.nombre }}</template>
        <template #breadcrumb>Dashboard / Eventos / {{ evento.nombre }}</template>

        <div class="admin-prod-show-page">
            <!-- Botón volver -->
            <Link :href="route('admin.eventos.index')" class="admin-prod-back-link">
                <i class="pi pi-arrow-left"></i>
                <span>Volver a Eventos</span>
            </Link>

            <!-- ============================================================ -->
            <!-- HEADER DEL EVENTO -->
            <!-- ============================================================ -->
            <div class="admin-prod-header">
                <div class="admin-prod-header-content">
                    <div class="admin-prod-header-left">
                        <!-- Imagen -->
                        <div class="admin-prod-header-image-wrapper">
                            <div class="admin-prod-header-image-ring">
                                <div class="admin-prod-header-image">
                                    <img v-if="evento.imagen" :src="evento.imagen" :alt="evento.nombre"
                                        @error="(e) => { e.target.src = '/images/placeholder-image.png' }" />
                                    <div v-else class="admin-prod-header-placeholder">
                                        <i class="pi pi-calendar"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="admin-prod-header-status-badge"
                                :class="publicacionColor.bg + ' ' + publicacionColor.text + ' ' + publicacionColor.border">
                                <span class="admin-prod-header-status-dot" :class="publicacionColor.dot"></span>
                                {{ publicacionColor.label }}
                            </div>
                        </div>

                        <!-- Información -->
                        <div class="admin-prod-header-info">
                            <div class="admin-prod-header-name-row">
                                <h1>
                                    {{ evento.nombre }}
                                    <i v-if="evento.destacado" class="pi pi-star-fill" style="color:#F59E0B;font-size:0.9rem;margin-left:0.3rem" title="Evento destacado"></i>
                                </h1>
                                <span class="admin-prod-header-sku">{{ tipoLabel[evento.tipo] }}</span>
                            </div>

                            <div class="admin-prod-header-meta">
                                <span class="admin-prod-meta-item">
                                    <i class="pi pi-map-marker"></i>
                                    {{ evento.ciudad }}
                                </span>
                                <span class="admin-prod-meta-divider">•</span>
                                <span v-if="evento.categoria" class="admin-prod-meta-item">
                                    <i class="pi pi-folder"></i>
                                    {{ evento.categoria }}
                                </span>
                                <span v-else class="admin-prod-meta-item admin-prod-meta-item--empty">
                                    <i class="pi pi-folder"></i>
                                    Sin categoría
                                </span>
                            </div>

                            <div class="admin-prod-header-stats">
                                <div class="admin-prod-stat-item">
                                    <i class="pi pi-calendar"></i>
                                    <span class="admin-prod-stat-value">{{ formatDate(evento.fecha, { month: 'long' }) }}</span>
                                    <span class="admin-prod-stat-label">{{ evento.hora?.slice(0, 5) }}</span>
                                </div>
                                <div class="admin-prod-stat-divider"></div>
                                <div class="admin-prod-stat-item">
                                    <i class="pi pi-ticket"></i>
                                    <span class="admin-prod-stat-value">{{ money(evento.precio) }}</span>
                                    <span class="admin-prod-stat-label">precio</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-prod-header-right">
                        <div class="admin-prod-stock-badge"
                            :class="estadoActualColor.bg + ' ' + estadoActualColor.text + ' ' + estadoActualColor.border">
                            <span class="admin-prod-stock-dot" :class="estadoActualColor.dot"></span>
                            <span class="admin-prod-stock-label">{{ estadoActualColor.label }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- GRID PRINCIPAL -->
            <!-- ============================================================ -->
            <div class="admin-prod-show-grid">
                <!-- COLUMNA IZQUIERDA: INFORMACIÓN -->
                <div class="admin-prod-show-left">
                    <!-- Descripción -->
                    <div class="admin-prod-info-card admin-prod-info-card--highlight">
                        <div class="admin-prod-info-card-header">
                            <h3><i class="pi pi-align-left"></i> Descripción</h3>
                        </div>
                        <div class="admin-prod-info-card-body">
                            <p v-if="evento.descripcion" class="admin-prod-description-text">
                                {{ evento.descripcion }}
                            </p>
                            <div v-else class="admin-prod-inline-empty">
                                <i class="pi pi-file-edit"></i>
                                <span>No hay descripción disponible</span>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles del evento -->
                    <div class="admin-prod-info-card">
                        <div class="admin-prod-info-card-header">
                            <h3><i class="pi pi-list"></i> Detalles</h3>
                        </div>
                        <div class="admin-prod-info-card-body">
                            <div class="admin-evento-detalles-grid">
                                <div class="admin-user-data-item">
                                    <div class="admin-user-data-icon"><i class="pi pi-map"></i></div>
                                    <div>
                                        <p class="admin-user-data-label">Zona / lugar</p>
                                        <p class="admin-user-data-value">{{ evento.zona_ubicacion || '—' }}</p>
                                    </div>
                                </div>
                                <div class="admin-user-data-item">
                                    <div class="admin-user-data-icon"><i class="pi pi-users"></i></div>
                                    <div>
                                        <p class="admin-user-data-label">Capacidad</p>
                                        <p class="admin-user-data-value">{{ evento.capacidad || 'Ilimitada' }}</p>
                                    </div>
                                </div>
                                <div v-if="evento.codigo_vestimenta" class="admin-user-data-item">
                                    <div class="admin-user-data-icon"><i class="pi pi-star"></i></div>
                                    <div>
                                        <p class="admin-user-data-label">Código de vestimenta</p>
                                        <p class="admin-user-data-value">{{ evento.codigo_vestimenta }}</p>
                                    </div>
                                </div>
                                <div v-if="evento.organizador" class="admin-user-data-item">
                                    <div class="admin-user-data-icon"><i class="pi pi-user"></i></div>
                                    <div>
                                        <p class="admin-user-data-label">Organizado por</p>
                                        <p class="admin-user-data-value">{{ evento.organizador.nombre }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Imagen -->
                    <div v-if="evento.imagen" class="admin-prod-info-card">
                        <div class="admin-prod-info-card-header">
                            <h3><i class="pi pi-image"></i> Imagen</h3>
                        </div>
                        <div class="admin-prod-info-card-body">
                            <div class="admin-prod-gallery-grid">
                                <div class="admin-prod-gallery-item admin-prod-gallery-item--principal">
                                    <img :src="evento.imagen" :alt="evento.nombre" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: ESTADO + ACCIONES -->
                <div class="admin-prod-show-right">
                    <!-- Estado actual -->
                    <div class="admin-prod-info-card" style="text-align:center;padding:1.5rem">
                        <div :class="estadoActualColor.bg + ' ' + estadoActualColor.text"
                            style="width:64px;height:64px;border-radius:50%;margin:0 auto 0.8rem;display:flex;align-items:center;justify-content:center;font-size:1.5rem">
                            <i class="pi" :class="{
                                'pi-wifi': evento.estado_display === 'en_vivo',
                                'pi-clock': evento.estado_display === 'programado',
                                'pi-check-circle': evento.estado_display === 'completado',
                            }"></i>
                        </div>
                        <p style="font-size:1.05rem;font-weight:700;color:var(--ink)">{{ estadoLabel[evento.estado_display] }}</p>
                        <p v-if="evento.estado_display === 'en_vivo'" class="text-xs mt-1" style="color:#DC2626">Este evento está en curso ahora mismo</p>
                        <p v-else-if="evento.estado_display === 'programado'" class="admin-user-hint mt-1">Aún no llega la hora de inicio</p>
                    </div>

                    <!-- Acciones -->
                    <div class="admin-prod-actions-card">
                        <div class="admin-prod-actions-card-header"><h3><i class="pi pi-bolt"></i> Acciones</h3></div>
                        <div class="admin-prod-actions-card-body">
                            <Link :href="route('admin.eventos.edit', evento.id)" class="admin-prod-btn-edit">
                                <i class="pi pi-pencil"></i><span>Editar evento</span>
                            </Link>
                            <button @click="eliminar" class="admin-prod-btn-delete">
                                <i class="pi pi-trash"></i><span>Eliminar evento</span>
                            </button>
                            <div class="admin-prod-actions-divider"></div>
                            <Link :href="route('admin.eventos.index')" class="admin-prod-btn-back">
                                <i class="pi pi-arrow-left"></i><span>Volver al listado</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>