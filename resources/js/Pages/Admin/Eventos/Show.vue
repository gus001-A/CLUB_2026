<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useConfirm } from '@/composables/useConfirm';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
    evento: Object,
});

const { confirm } = useConfirm();
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
    router.delete(route('admin.eventos.destroy', props.evento.id));
}
</script>

<template>
    <Head :title="evento.nombre" />

    <AdminLayout>
        <template #title>{{ evento.nombre }}</template>
        <template #breadcrumb>Dashboard &gt; Eventos &gt; {{ evento.nombre }}</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <Link :href="route('admin.eventos.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-red-600 mb-4">
                <i class="pi pi-arrow-left text-xs"></i> Volver a Eventos
            </Link>

            <!-- Banner principal -->
            <div class="relative admin-card overflow-hidden mb-6">
                <!-- Imagen del evento (si tiene) o franja de color como respaldo -->
                <div v-if="evento.imagen" class="relative w-full" style="height:220px">
                    <img :src="evento.imagen" class="w-full h-full object-cover" @error="$event.target.style.display='none'" />
                    <div class="absolute inset-0" style="background:linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(0,0,0,0.55) 100%)"></div>
                    <span v-if="evento.destacado" class="absolute top-4 right-4 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-400 text-amber-900 flex items-center gap-1.5 shadow">
                        <i class="pi pi-star-fill"></i> Destacado
                    </span>
                    <div class="absolute bottom-4 left-6 right-6 flex items-center justify-between gap-4">
                        <div>
                            <h1 class="text-xl font-bold text-white drop-shadow">{{ evento.nombre }}</h1>
                            <p class="text-sm text-white/80 mt-0.5">{{ evento.categoria || 'Sin categoría asignada' }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="px-3 py-1.5 rounded-lg text-xs font-semibold uppercase" :class="tipoColores[evento.tipo]">{{ evento.tipo }}</span>
                            <span class="px-3 py-1.5 rounded-lg text-xs font-semibold" :class="estadoColores[evento.estado_display]">
                                {{ estadoLabel[evento.estado_display] }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Sin imagen: banner con icono como antes -->
                <template v-else>
                    <div style="height:8px;background:linear-gradient(90deg,#ef4444,#f97316)"></div>
                    <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="admin-icon-gradient" style="width:64px;height:64px">
                                <i class="pi pi-calendar" style="font-size:1.5rem"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
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
                </template>
            </div>

            <div class="flex flex-col lg:flex-row gap-6 items-start w-full">

                <!-- Columna izquierda: detalles -->
                <div class="w-full lg:w-2/3 min-w-0 flex flex-col gap-6">

                    <!-- Descripción -->
                    <div v-if="evento.descripcion" class="admin-card p-6">
                        <h2 class="text-sm font-bold text-gray-900 mb-2 flex items-center gap-2">
                            <i class="pi pi-align-left text-red-600 text-xs"></i> {{ evento.nombre }}: Descripción
                        </h2>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ evento.descripcion }}</p>
                    </div>

                    <!-- Info en tarjetas -->
                    <div class="admin-card p-6">
                        <h2 class="text-sm font-bold text-gray-900 mb-4">Detalles del evento</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div class="admin-icon-circle bg-red-50 text-red-600" style="width:40px;height:40px"><i class="pi pi-calendar text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Fecha</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ formatDate(evento.fecha, { month: 'long' }) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div class="admin-icon-circle bg-red-50 text-red-600" style="width:40px;height:40px"><i class="pi pi-clock text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Hora</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ evento.hora?.slice(0, 5) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div class="admin-icon-circle bg-red-50 text-red-600" style="width:40px;height:40px"><i class="pi pi-map-marker text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Ciudad</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ evento.ciudad }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div class="admin-icon-circle bg-red-50 text-red-600" style="width:40px;height:40px"><i class="pi pi-map text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Zona / lugar</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ evento.zona_ubicacion || '—' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div class="admin-icon-circle bg-red-50 text-red-600" style="width:40px;height:40px"><i class="pi pi-ticket text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Precio</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ money(evento.precio) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div class="admin-icon-circle bg-red-50 text-red-600" style="width:40px;height:40px"><i class="pi pi-users text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Capacidad</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ evento.capacidad || 'Ilimitada' }}</p>
                                </div>
                            </div>
                            <div v-if="evento.codigo_vestimenta" class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div class="admin-icon-circle bg-red-50 text-red-600" style="width:40px;height:40px"><i class="pi pi-star text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Código de vestimenta</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ evento.codigo_vestimenta }}</p>
                                </div>
                            </div>
                            <div v-if="evento.organizador" class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div class="admin-icon-circle bg-red-50 text-red-600" style="width:40px;height:40px"><i class="pi pi-user text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Organizado por</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ evento.organizador.nombre }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: estado + acciones -->
                <div class="w-full lg:w-1/3 min-w-0 flex flex-col gap-6">

                    <!-- Estado destacado -->
                    <div class="admin-card p-6 text-center">
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
                        <p class="text-xs text-gray-400 uppercase font-medium mb-1">Estado actual</p>
                        <p class="text-lg font-bold text-gray-900">{{ estadoLabel[evento.estado_display] }}</p>
                        <p v-if="evento.estado_display === 'en_vivo'" class="text-xs text-red-500 mt-1">Este evento está en curso ahora mismo</p>
                        <p v-else-if="evento.estado_display === 'programado'" class="text-xs text-gray-400 mt-1">Aún no llega la hora de inicio</p>
                    </div>

                    <!-- Acciones -->
                    <div class="admin-card p-6">
                        <h2 class="text-sm font-bold text-gray-900 mb-4">Acciones</h2>
                        <div class="flex flex-col gap-2.5">
                            <Link :href="route('admin.eventos.edit', evento.id)" class="admin-btn-primary bg-red-600 hover:bg-red-700">
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