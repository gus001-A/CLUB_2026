<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useFormatters } from '@/composables/useFormatters';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    eventos: Object,
    filtros: Object,
    porEstado: Array,
    porTipo: Array,
    totalGeneral: Number,
});

const { money, formatDateTime } = useFormatters();
const { confirm } = useConfirm();
const toast = useToast();

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

const estadoBadgeClase = {
    en_vivo: 'admin-eventos-badge--en_vivo',
    programado: 'admin-eventos-badge--programado',
    completado: 'admin-eventos-badge--completado',
    cancelado: 'admin-eventos-badge--cancelado',
    borrador: 'admin-eventos-badge--borrador',
};
const estadoLabel = {
    en_vivo: 'En vivo',
    programado: 'Programado',
    completado: 'Completado',
    cancelado: 'Cancelado',
    borrador: 'Borrador',
};

const estadoDotColores = {
    borrador: '#D97706',
    publicado: '#059669',
    cancelado: '#6B7280',
    completo: '#2563EB',
};

const tipoBadgeClase = { vip: 'admin-eventos-tipo-badge--vip', general: 'admin-eventos-tipo-badge--general' };

async function eliminarEvento(evento) {
    const ok = await confirm(`Esto eliminará el evento "${evento.nombre}". Esta acción no se puede deshacer.`, {
        title: 'Eliminar evento',
        confirmLabel: 'Sí, eliminar',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('admin.eventos.destroy', evento.id), {
        preserveScroll: true,
        onSuccess: () => toast.success(`Evento "${evento.nombre}" eliminado correctamente.`),
        onError: () => toast.error('No se pudo eliminar el evento.'),
    });
}
</script>

<template>
    <Head title="Todos los Eventos" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Eventos &gt; Todos los eventos</template>

        <div class="admin-reportes-page">

            <Link :href="route('admin.eventos.index')" class="admin-user-back-link">
                <i class="pi pi-arrow-left"></i>
                Volver a Eventos
            </Link>

            <!-- Desglose por estado -->
            <div class="admin-estado-grid gap-4 mb-6 w-full">
                <button v-for="e in porEstado" :key="e.estado" type="button" @click="estado = (estado === e.estado ? '' : e.estado)"
                    class="admin-prod-estado-chip" :class="{ 'admin-prod-estado-chip--active': estado === e.estado }">
                    <span class="admin-prod-estado-chip-dot" :style="{ background: estadoDotColores[e.estado] }"></span>
                    <span>
                        <span class="admin-prod-estado-chip-value">{{ e.cantidad }}</span>
                        <span class="admin-prod-estado-chip-label">{{ e.label }}</span>
                    </span>
                </button>
            </div>

            <!-- Desglose por tipo -->
            <div class="admin-cobros-card mb-6">
                <div class="admin-cobros-card__header">
                    <div class="admin-cobros-card__header-left">
                        <div class="admin-cobros-header-icon"><i class="pi pi-chart-bar"></i></div>
                        <h3>Desglose por tipo</h3>
                    </div>
                </div>
                <div class="admin-cobros-tipo-grid" style="grid-template-columns:repeat(2, 1fr)">
                    <div v-for="t in porTipo" :key="t.tipo" class="admin-cobros-tipo-tile">
                        <p class="admin-cobros-tipo-label">{{ t.label }}</p>
                        <p class="admin-cobros-tipo-value">{{ t.cantidad }} eventos</p>
                    </div>
                </div>
            </div>

            <!-- Tabla completa -->
            <div class="admin-cobros-card">
                <div class="flex flex-col flex-1">
                    <div class="admin-cobros-card__header">
                        <div class="admin-cobros-card__header-left">
                            <div class="admin-cobros-header-icon"><i class="pi pi-list"></i></div>
                            <div>
                                <h3>Historial completo</h3>
                                <p class="admin-cobros-header-subtitle">{{ totalGeneral }} eventos registrados en total</p>
                            </div>
                        </div>
                        <Link :href="route('admin.eventos.create')" class="admin-cobros-btn-primary">
                            <i class="pi pi-plus"></i> Crear Evento
                        </Link>
                    </div>

                    <!-- Filtros -->
                    <div class="admin-cobros-filters">
                        <div class="admin-cobros-filters__search">
                            <i class="pi pi-search"></i>
                            <input v-model="q" type="text" placeholder="Buscar evento, ciudad..." />
                        </div>
                        <select v-model="estado">
                            <option value="">Todos los estados</option>
                            <option value="publicado">Publicado</option>
                            <option value="borrador">Borrador</option>
                            <option value="cancelado">Cancelado</option>
                            <option value="completo">Completado</option>
                        </select>
                        <select v-model="tipo">
                            <option value="">Todos los tipos</option>
                            <option value="vip">VIP</option>
                            <option value="general">General</option>
                        </select>
                        <div class="flex items-center gap-1.5">
                            <input v-model="desde" type="date" />
                            <span class="text-gray-400">—</span>
                            <input v-model="hasta" type="date" />
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto flex-1 flex flex-col">
                        <table class="admin-cobros-table min-w-full flex-1">
                            <thead>
                                <tr>
                                    <th>Evento</th>
                                    <th>Tipo</th>
                                    <th>Fecha y Hora</th>
                                    <th>Ciudad</th>
                                    <th>Precio</th>
                                    <th>Capacidad</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="evento in eventos.data" :key="evento.id">
                                    <td>
                                        <div class="flex items-center gap-3 min-w-0">
                                            <img :src="evento.imagen || 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=100&q=80'"
                                                style="width:40px;height:40px;flex:none;object-fit:cover;border-radius:10px;border:1px solid var(--line)" />
                                            <span class="admin-cobros-tx-name truncate" style="max-width:180px;display:inline-block" :title="evento.nombre">
                                                {{ evento.nombre }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="admin-eventos-tipo-badge" :class="tipoBadgeClase[evento.tipo]">{{ evento.tipo }}</span>
                                    </td>
                                    <td class="text-gray-500 whitespace-nowrap text-xs">{{ formatDateTime(evento.fecha) }} · {{ evento.hora_formateada }}</td>
                                    <td class="text-gray-600 text-xs">{{ evento.ciudad ?? '—' }}</td>
                                    <td class="font-semibold whitespace-nowrap" style="color:var(--ink)">{{ money(evento.precio) }}</td>
                                    <td class="text-gray-600 text-xs">{{ evento.capacidad || 'Ilimitada' }}</td>
                                    <td>
                                        <span class="admin-eventos-badge" :class="estadoBadgeClase[evento.estado_display]">
                                            <span class="admin-eventos-badge-dot"></span>{{ estadoLabel[evento.estado_display] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex justify-center gap-1.5">
                                            <Link :href="route('admin.eventos.show', evento.id)" class="admin-dash-action-btn admin-dash-action-btn--view">
                                                <i class="pi pi-eye"></i>
                                            </Link>
                                            <Link :href="route('admin.eventos.edit', evento.id)" class="admin-dash-action-btn admin-dash-action-btn--edit">
                                                <i class="pi pi-pencil"></i>
                                            </Link>
                                            <button @click="eliminarEvento(evento)" class="admin-dash-action-btn admin-dash-action-btn--delete">
                                                <i class="pi pi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!eventos.data?.length">
                                    <td colspan="8" class="admin-cobros-empty">No se encontraron eventos.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="admin-cobros-table-footer">
                    <Pagination :data="eventos" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>