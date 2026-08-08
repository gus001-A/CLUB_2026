<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useFormatters } from '@/composables/useFormatters';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps({
    eventos: Object,
    filtros: Object,
    porEstado: Array,
    porTipo: Array,
    totalGeneral: Number,
});

const { money, formatDateTime } = useFormatters();
const { confirm } = useConfirm();

const q = ref(props.filtros.q || '');
const estado = ref(props.filtros.estado || '');
const tipo = ref(props.filtros.tipo || '');
const desde = ref(props.filtros.desde || '');
const hasta = ref(props.filtros.hasta || '');

let timeout = null;
function aplicarFiltros() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.eventos.todos'), {
            q: q.value || undefined,
            estado: estado.value || undefined,
            tipo: tipo.value || undefined,
            desde: desde.value || undefined,
            hasta: hasta.value || undefined,
        }, { preserveState: true, preserveScroll: true, replace: true });
    }, 350);
}
watch([q, estado, tipo, desde, hasta], aplicarFiltros);

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

const estadoDotColores = {
    borrador: '#F5C542',
    publicado: '#10B981',
    cancelado: '#9CA3AF',
    completo: '#2563EB',
};

const tipoColores = {
    vip: 'bg-rose-50 text-rose-600 border border-rose-100',
    general: 'bg-emerald-50 text-emerald-600 border border-emerald-100',
};

async function eliminarEvento(evento) {
    const ok = await confirm(`Esto eliminará el evento "${evento.nombre}". Esta acción no se puede deshacer.`, {
        title: 'Eliminar evento',
        confirmLabel: 'Sí, eliminar',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('admin.eventos.destroy', evento.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Todos los Eventos" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Eventos &gt; Todos los eventos</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- Volver -->
            <div class="mb-6">
                <Link :href="route('admin.eventos.index')" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-brand transition">
                    <i class="pi pi-arrow-left text-xs"></i>
                    Volver a Eventos
                </Link>
            </div>

            <!-- Encabezado + total general -->
            <div class="admin-card p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">Todos los Eventos</h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ totalGeneral }} eventos registrados en total</p>
                </div>
                <Link :href="route('admin.eventos.create')" class="admin-btn-primary bg-red-600 hover:bg-red-700 self-start sm:self-auto">
                    <i class="pi pi-plus text-xs"></i> Crear Evento
                </Link>
            </div>

            <!-- Desglose por estado -->
            <div class="admin-estado-grid gap-4 mb-6 w-full">
                <button v-for="e in porEstado" :key="e.estado" type="button" @click="estado = (estado === e.estado ? '' : e.estado)"
                    class="min-w-0 admin-card px-5 py-4 text-left transition"
                    :class="estado === e.estado ? 'ring-2 ring-brand/40' : 'hover:border-gray-300'">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="rounded-full shrink-0" :style="{ backgroundColor: estadoDotColores[e.estado], width: '8px', height: '8px' }"></span>
                        <span class="text-xs text-gray-500">{{ e.label }}</span>
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ e.cantidad }}</p>
                </button>
            </div>

            <!-- Desglose por tipo -->
            <div class="admin-card p-6 mb-6">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">Desglose por tipo</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div v-for="t in porTipo" :key="t.tipo">
                        <p class="text-xs text-gray-400 mb-1">{{ t.label }}</p>
                        <p class="text-base font-bold text-gray-900">{{ t.cantidad }} eventos</p>
                    </div>
                </div>
            </div>

            <!-- Tabla completa -->
            <div class="admin-card flex flex-col justify-between">
                <div class="flex flex-col flex-1">
                    <div class="px-6 pt-6">
                        <h2 class="text-lg font-semibold text-gray-900">Historial completo</h2>
                    </div>

                    <!-- Filtros -->
                    <div class="flex flex-wrap items-center gap-3 px-6 py-5">
                        <div class="relative flex-1 min-w-[180px]">
                            <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input v-model="q" type="text" placeholder="Buscar evento, ciudad..." class="admin-input pl-10 py-2.5">
                        </div>
                        <select v-model="estado" class="admin-input w-auto py-2.5">
                            <option value="">Todos los estados</option>
                            <option value="publicado">Publicado</option>
                            <option value="borrador">Borrador</option>
                            <option value="cancelado">Cancelado</option>
                            <option value="completo">Completado</option>
                        </select>
                        <select v-model="tipo" class="admin-input w-auto py-2.5">
                            <option value="">Todos los tipos</option>
                            <option value="vip">VIP</option>
                            <option value="general">General</option>
                        </select>
                        <div class="flex items-center gap-1.5">
                            <input v-model="desde" type="date" class="admin-input w-auto py-2.5">
                            <span class="text-gray-400">—</span>
                            <input v-model="hasta" type="date" class="admin-input w-auto py-2.5">
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto flex-1 flex flex-col">
                        <table class="min-w-full text-sm flex-1">
                            <thead class="bg-gray-50 border-y border-gray-200">
                                <tr class="text-gray-600 uppercase tracking-wide text-xs">
                                    <th class="px-6 py-4 text-left">Evento</th>
                                    <th class="px-4 py-4 text-left">Tipo</th>
                                    <th class="px-4 py-4 text-left">Fecha y Hora</th>
                                    <th class="px-4 py-4 text-left">Ciudad</th>
                                    <th class="px-4 py-4 text-left">Precio</th>
                                    <th class="px-4 py-4 text-left">Capacidad</th>
                                    <th class="px-4 py-4 text-left">Estado</th>
                                    <th class="px-6 py-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="evento in eventos.data" :key="evento.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <img :src="evento.imagen || 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=100&q=80'"
                                                style="width:40px;height:40px;flex:none;object-fit:cover" class="rounded-lg border border-gray-200" />
                                            <span class="font-semibold text-gray-900 truncate" style="max-width:180px;display:inline-block" :title="evento.nombre">
                                                {{ evento.nombre }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="px-2.5 py-1 rounded-md text-xs font-medium uppercase" :class="tipoColores[evento.tipo]">{{ evento.tipo }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-gray-500 whitespace-nowrap text-xs">{{ formatDateTime(evento.fecha) }} · {{ evento.hora_formateada }}</td>
                                    <td class="px-4 py-4 text-gray-600 text-xs">{{ evento.ciudad ?? '—' }}</td>
                                    <td class="px-4 py-4 text-gray-800 text-xs font-semibold whitespace-nowrap">{{ money(evento.precio) }}</td>
                                    <td class="px-4 py-4 text-gray-600 text-xs">{{ evento.capacidad || 'Ilimitada' }}</td>
                                    <td class="px-4 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold capitalize" :class="estadoColores[evento.estado_display]">
                                            {{ estadoLabel[evento.estado_display] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center gap-2">
                                            <Link :href="route('admin.eventos.show', evento.id)" class="admin-table-action text-gray-600">
                                                <i class="pi pi-eye"></i>
                                            </Link>
                                            <Link :href="route('admin.eventos.edit', evento.id)" class="admin-table-action text-gray-600">
                                                <i class="pi pi-pencil"></i>
                                            </Link>
                                            <button @click="eliminarEvento(evento)" class="admin-table-action text-red-600 hover:bg-red-50">
                                                <i class="pi pi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!eventos.data?.length">
                                    <td colspan="8" class="text-center text-gray-400 py-12">No se encontraron eventos.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="border-t border-gray-200 px-6 py-4">
                    <Pagination :data="eventos" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>