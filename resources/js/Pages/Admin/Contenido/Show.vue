<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useFormatters } from '@/composables/useFormatters';
import { useContenidoMeta } from '@/composables/useContenidoMeta';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps({
    contenido: Object,
});

const { confirm } = useConfirm();

const { money, formatDate: formatDateBase } = useFormatters();
const formatFecha = (v) => formatDateBase(v, { month: 'long', hour: '2-digit', minute: '2-digit' });

const { tipoLabel, tipoIcono, tipoColor, estadoColores, estadoLabel, visibilidadLabel, visibilidadIcono } = useContenidoMeta();

async function eliminar() {
    const ok = await confirm(`Esto eliminará "${props.contenido.titulo}" permanentemente.`, {
        title: 'Eliminar contenido',
        confirmLabel: 'Sí, eliminar',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('admin.contenido.destroy', props.contenido.id));
}
</script>

<template>
    <Head :title="contenido.titulo || 'Contenido'" />

    <AdminLayout>
        <template #title>{{ contenido.titulo || 'Sin título' }}</template>
        <template #breadcrumb>Dashboard &gt; Contenido &gt; {{ contenido.titulo || 'Ver' }}</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <Link :href="route('admin.contenido.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand mb-4">
                <i class="pi pi-arrow-left text-xs"></i> Volver a Contenido
            </Link>

            <!-- Banner -->
            <div class="admin-card overflow-hidden mb-6">
                <div style="height:8px;background:linear-gradient(90deg,#C81E3A,#E85C74)"></div>
                <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="rounded-2xl bg-brand/10 text-brand flex items-center justify-center shrink-0 overflow-hidden" style="width:64px;height:64px">
                            <img v-if="contenido.archivos?.[0] && (contenido.tipo === 'foto' || contenido.tipo === 'galeria')" :src="contenido.archivos[0]" class="w-full h-full object-cover" />
                            <i v-else class="pi" :class="tipoIcono[contenido.tipo]" style="font-size:1.5rem"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ contenido.titulo || 'Sin título' }}</h1>
                            <p class="text-sm text-gray-400 mt-0.5">{{ contenido.categoria || 'Sin categoría asignada' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-3 py-1.5 rounded-lg text-xs font-semibold" :class="tipoColor[contenido.tipo]">{{ tipoLabel[contenido.tipo] }}</span>
                        <span class="px-3 py-1.5 rounded-lg text-xs font-semibold" :class="estadoColores[contenido.estado]">
                            {{ estadoLabel[contenido.estado] }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="admin-contenido-show-grid gap-6 w-full">

                <!-- Columna izquierda -->
                <div class="min-w-0 flex flex-col gap-6" style="grid-area:izquierda">

                    <!-- Descripción -->
                    <div v-if="contenido.descripcion" class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-align-left text-brand"></i> Descripción</span>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed p-6">{{ contenido.descripcion }}</p>
                    </div>

                    <!-- Galería de archivos -->
                    <div v-if="contenido.archivos?.length" class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-folder text-brand"></i> Archivos ({{ contenido.archivos.length }})</span>
                        </div>
                        <div class="p-6">

                        <!-- Fotos / Galería -->
                        <div v-if="contenido.tipo === 'foto' || contenido.tipo === 'galeria'" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div v-for="(archivo, idx) in contenido.archivos" :key="idx" class="aspect-square rounded-xl overflow-hidden bg-gray-100">
                                <img :src="archivo" class="w-full h-full object-cover" />
                            </div>
                        </div>

                        <!-- Video -->
                        <div v-else-if="contenido.tipo === 'video'" class="space-y-3">
                            <video v-for="(archivo, idx) in contenido.archivos" :key="idx" :src="archivo" controls
                                class="w-full max-w-lg rounded-xl border border-gray-200 bg-black" style="max-height:360px"></video>
                        </div>

                        <!-- Audio -->
                        <div v-else-if="contenido.tipo === 'audio'" class="space-y-3">
                            <div v-for="(archivo, idx) in contenido.archivos" :key="idx" class="flex items-center gap-3 border border-gray-200 rounded-xl p-3 bg-gray-50/60">
                                <div class="admin-icon-circle" style="width:36px;height:36px">
                                    <i class="pi pi-volume-up text-sm"></i>
                                </div>
                                <audio :src="archivo" controls class="w-full"></audio>
                            </div>
                        </div>

                        <!-- Documento / Artículo / Exclusivo -->
                        <div v-else class="space-y-2">
                            <a v-for="(archivo, idx) in contenido.archivos" :key="idx" :href="archivo" target="_blank" rel="noopener"
                                class="flex items-center gap-3 border border-gray-200 rounded-xl p-3 bg-gray-50/60 hover:bg-gray-100 transition">
                                <div class="admin-icon-circle" style="width:36px;height:36px">
                                    <i class="pi pi-file text-sm"></i>
                                </div>
                                <span class="text-sm text-gray-700 truncate flex-1">{{ archivo.split('/').pop() }}</span>
                                <i class="pi pi-external-link text-xs text-gray-400"></i>
                            </a>
                        </div>
                        </div>
                    </div>

                    <!-- Etiquetas -->
                    <div v-if="contenido.etiquetas?.length" class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-tag text-brand"></i> Etiquetas</span>
                        </div>
                        <div class="flex flex-wrap gap-2 p-6">
                            <span v-for="(t, idx) in contenido.etiquetas" :key="idx" class="px-3 py-1 rounded-full text-xs font-medium bg-brand/10 text-brand">{{ t }}</span>
                        </div>
                    </div>

                    <!-- Detalles -->
                    <div class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-info-circle text-brand"></i> Detalles</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div class="admin-icon-circle" style="width:40px;height:40px">
                                    <i class="pi" :class="visibilidadIcono[contenido.visibilidad]" style="font-size:0.875rem"></i>
                                </div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Visibilidad</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ visibilidadLabel[contenido.visibilidad] }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-ticket text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Precio</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ contenido.precio > 0 ? money(contenido.precio) : 'Gratuito' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-star text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Premium</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ contenido.es_premium ? 'Sí' : 'No' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-calendar text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">{{ contenido.estado === 'programado' ? 'Programado para' : 'Publicado el' }}</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ formatFecha(contenido.programado_en || contenido.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha -->
                <div class="min-w-0 flex flex-col gap-6" style="grid-area:derecha">

                    <!-- Estado destacado -->
                    <div class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-info-circle text-brand"></i> Estado actual</span>
                        </div>
                        <div class="p-6 text-center">
                            <div class="mx-auto mb-3 rounded-full flex items-center justify-center" :class="estadoColores[contenido.estado]" style="width:64px;height:64px">
                                <i class="pi" style="font-size:1.5rem" :class="tipoIcono[contenido.tipo]"></i>
                            </div>
                            <p class="text-lg font-bold text-gray-900">{{ estadoLabel[contenido.estado] }}</p>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <div class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-chart-bar text-brand"></i> Estadísticas</span>
                        </div>
                        <div class="grid grid-cols-1 gap-3 p-6">
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div class="admin-icon-circle" style="width:36px;height:36px"><i class="pi pi-eye text-xs"></i></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">{{ contenido.vistas ?? 0 }}</p>
                                    <p class="text-[10px] text-gray-400">Vistas</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div class="admin-icon-circle" style="width:36px;height:36px"><i class="pi pi-heart text-xs"></i></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">{{ contenido.likes ?? 0 }}</p>
                                    <p class="text-[10px] text-gray-400">Likes</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div class="admin-icon-circle" style="width:36px;height:36px"><i class="pi pi-comment text-xs"></i></div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">{{ contenido.comentarios ?? 0 }}</p>
                                    <p class="text-[10px] text-gray-400">Comentarios</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-bolt text-brand"></i> Acciones</span>
                        </div>
                        <div class="flex flex-col gap-2.5 p-6">
                            <Link :href="route('admin.contenido.edit', contenido.id)" class="admin-btn-primary">
                                <i class="pi pi-pencil text-xs"></i> Editar contenido
                            </Link>
                            <button @click="eliminar" class="border border-red-200 text-red-600 hover:bg-red-50 font-medium px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2">
                                <i class="pi pi-trash text-xs"></i> Eliminar contenido
                            </button>
                            <Link :href="route('admin.contenido.index')" class="admin-btn-secondary text-center">
                                Volver al listado
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>