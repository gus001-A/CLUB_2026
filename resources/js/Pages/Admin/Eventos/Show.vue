<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
    evento: Object,
});

const { confirm } = useConfirm();
const toast = useToast();
const { money, formatDate } = useFormatters();

const estadoColores = {
    en_vivo: 'bg-red-50 text-red-600 border border-red-200',
    programado: 'bg-orange-50 text-orange-600 border border-orange-200',
    completado: 'bg-emerald-50 text-emerald-600 border border-emerald-200',
    cancelado: 'bg-gray-50 text-gray-600 border border-gray-200',
    borrador: 'bg-yellow-50 text-yellow-600 border border-yellow-200',
};
const estadoLabel = {
    en_vivo: 'En vivo',
    programado: 'Programado',
    completado: 'Completado',
    cancelado: 'Cancelado',
    borrador: 'Borrador',
};

const tipoColores = {
    vip: 'bg-rose-50 text-rose-600 border border-rose-100',
    general: 'bg-emerald-50 text-emerald-600 border border-emerald-100',
};

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
        <template #breadcrumb>Dashboard &gt; Eventos &gt; {{ evento.nombre }}</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <Link :href="route('admin.eventos.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand mb-4">
                <i class="pi pi-arrow-left text-xs"></i> Volver a Eventos
            </Link>

            <!-- Header compacto: la imagen real ya vive en la tarjeta "Archivos" de abajo,
                 así que aquí solo un thumbnail chico + título + chips de datos rápidos. -->
            <div class="admin-card overflow-hidden mb-6">
                <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="rounded-2xl bg-brand/10 text-brand flex items-center justify-center shrink-0 overflow-hidden" style="width:64px;height:64px">
                            <img v-if="evento.imagen" :src="evento.imagen" class="w-full h-full object-cover" />
                            <i v-else class="pi pi-calendar" style="font-size:1.5rem"></i>
                        </div>
                        <div class="min-w-0">
                            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2 flex-wrap">
                                {{ evento.nombre }}
                                <span v-if="evento.destacado" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 flex items-center gap-1">
                                    <i class="pi pi-star-fill"></i> Destacado
                                </span>
                            </h1>
                            <p class="text-sm text-gray-400 mt-0.5">{{ evento.categoria || 'Sin categoría asignada' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-3 py-1.5 rounded-lg text-xs font-semibold uppercase" :class="tipoColores[evento.tipo]">{{ evento.tipo }}</span>
                        <span class="px-3 py-1.5 rounded-lg text-xs font-semibold" :class="estadoColores[evento.estado_display]">
                            {{ estadoLabel[evento.estado_display] }}
                        </span>
                    </div>
                </div>

                <!-- Detalles del evento -->
                <div class="border-t border-gray-100 admin-evento-detalles-grid p-6">
                    <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                        <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-calendar text-sm"></i></div>
                        <div>
                            <p class="text-[11px] text-gray-400 uppercase font-medium">Fecha</p>
                            <p class="text-sm font-semibold text-gray-800">{{ formatDate(evento.fecha, { month: 'long' }) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                        <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-clock text-sm"></i></div>
                        <div>
                            <p class="text-[11px] text-gray-400 uppercase font-medium">Hora</p>
                            <p class="text-sm font-semibold text-gray-800">{{ evento.hora?.slice(0, 5) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                        <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-map-marker text-sm"></i></div>
                        <div>
                            <p class="text-[11px] text-gray-400 uppercase font-medium">Ciudad</p>
                            <p class="text-sm font-semibold text-gray-800">{{ evento.ciudad }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                        <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-map text-sm"></i></div>
                        <div>
                            <p class="text-[11px] text-gray-400 uppercase font-medium">Zona / lugar</p>
                            <p class="text-sm font-semibold text-gray-800">{{ evento.zona_ubicacion || '—' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                        <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-ticket text-sm"></i></div>
                        <div>
                            <p class="text-[11px] text-gray-400 uppercase font-medium">Precio</p>
                            <p class="text-sm font-semibold text-gray-800">{{ money(evento.precio) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                        <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-users text-sm"></i></div>
                        <div>
                            <p class="text-[11px] text-gray-400 uppercase font-medium">Capacidad</p>
                            <p class="text-sm font-semibold text-gray-800">{{ evento.capacidad || 'Ilimitada' }}</p>
                        </div>
                    </div>
                    <div v-if="evento.codigo_vestimenta" class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                        <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-star text-sm"></i></div>
                        <div>
                            <p class="text-[11px] text-gray-400 uppercase font-medium">Código de vestimenta</p>
                            <p class="text-sm font-semibold text-gray-800">{{ evento.codigo_vestimenta }}</p>
                        </div>
                    </div>
                    <div v-if="evento.organizador" class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                        <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-user text-sm"></i></div>
                        <div>
                            <p class="text-[11px] text-gray-400 uppercase font-medium">Organizado por</p>
                            <p class="text-sm font-semibold text-gray-800">{{ evento.organizador.nombre }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-evento-show-grid gap-6 w-full">

                <!-- Columna izquierda: detalles -->
                <div class="min-w-0 flex flex-col gap-6" style="grid-area:izquierda">

                    <!-- Descripción -->
                    <div v-if="evento.descripcion" class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-align-left text-brand"></i> Descripción</span>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed p-6">{{ evento.descripcion }}</p>
                    </div>

                    <!-- Archivos (imagen promocional, para verificar qué se subió de verdad) -->
                    <div v-if="evento.imagen" class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-folder text-brand"></i> Archivos (1)</span>
                        </div>
                        <div class="p-6">
                            <div class="rounded-xl overflow-hidden border border-gray-100 mx-auto" style="max-width:320px">
                                <img :src="evento.imagen" class="w-full h-auto object-cover" style="max-height:320px" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: estado + detalles + acciones -->
                <div class="min-w-0 h-full flex flex-col gap-6" style="grid-area:derecha">

                    <!-- Estado destacado -->
                    <div class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-info-circle text-brand"></i> Estado actual</span>
                        </div>
                        <div class="p-6 text-center">
                            <div
                                class="mx-auto mb-3 rounded-full flex items-center justify-center"
                                :class="estadoColores[evento.estado_display]"
                                style="width:64px;height:64px"
                            >
                                <i
                                    class="pi"
                                    style="font-size:1.5rem"
                                    :class="{
                                        'pi-wifi': evento.estado_display === 'en_vivo',
                                        'pi-clock': evento.estado_display === 'programado',
                                        'pi-check-circle': evento.estado_display === 'completado',
                                        'pi-ban': evento.estado_display === 'cancelado',
                                        'pi-file': evento.estado_display === 'borrador',
                                    }"
                                ></i>
                            </div>
                            <p class="text-lg font-bold text-gray-900">{{ estadoLabel[evento.estado_display] }}</p>
                            <p v-if="evento.estado_display === 'en_vivo'" class="text-xs text-red-500 mt-1">Este evento está en curso ahora mismo</p>
                            <p v-else-if="evento.estado_display === 'programado'" class="text-xs text-gray-400 mt-1">Aún no llega la hora de inicio</p>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="admin-card overflow-hidden flex-1 flex flex-col">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-bolt text-brand"></i> Acciones</span>
                        </div>
                        <div class="flex-1 flex flex-col justify-center gap-2.5 p-6">
                            <Link :href="route('admin.eventos.edit', evento.id)" class="admin-btn-primary">
                                <i class="pi pi-pencil text-xs"></i> Editar evento
                            </Link>
                            <button @click="eliminar" class="border border-red-200 text-red-600 hover:bg-red-50 font-medium px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2">
                                <i class="pi pi-trash text-xs"></i> Eliminar evento
                            </button>
                            <Link :href="route('admin.eventos.index')" class="admin-btn-secondary text-center">
                                Volver al listado
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>