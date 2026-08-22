<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Calendario from '@/Components/Calendario.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { useFormatters } from '@/composables/useFormatters';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    stats: Object,
    eventos: Object,
    filtros: Object,
    calendario: Object,
    estadisticas: Object,
});

const { confirm } = useConfirm();
const { formatDateTime } = useFormatters();
const toast = useToast();

const q = ref(props.filtros.q || '');
const estado = ref(props.filtros.estado || '');
const tipo = ref(props.filtros.tipo || '');
const periodoEstadisticas = ref(props.filtros.periodo_stats || 'mes');

let timeout = null;
function aplicarFiltros() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.eventos.index'), {
            q: q.value || undefined,
            estado: estado.value || undefined,
            tipo: tipo.value || undefined,
            periodo_stats: periodoEstadisticas.value !== 'mes' ? periodoEstadisticas.value : undefined,
        }, { preserveState: true, replace: true });
    }, 350);
}
watch([q, estado, tipo], aplicarFiltros);

function cambiarPeriodoStats() {
    router.get(route('admin.eventos.index'), {
        ...props.filtros,
        periodo_stats: periodoEstadisticas.value,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

const estadoBadgeClase = {
    en_vivo: 'admin-eventos-badge--en_vivo',
    programado: 'admin-eventos-badge--programado',
    completado: 'admin-eventos-badge--completado',
    cancelado: 'admin-eventos-badge--cancelado',
    borrador: 'admin-eventos-badge--borrador',
};

const tipoBadgeClase = { vip: 'admin-eventos-tipo-badge--vip', general: 'admin-eventos-tipo-badge--general' };

// KPIs con el mismo lenguaje visual del resto del panel
const kpis = computed(() => [
    { label: 'Eventos Totales', value: props.stats?.total ?? 0, icon: 'pi-calendar', color: '#2563EB', iconBg: '#DBEAFE', gradient: 'linear-gradient(135deg, #2563EB, #1D4ED8)', hint: `+${props.stats?.nuevosEsteMes ?? 0} nuevos este mes` },
    { label: 'Eventos Próximos', value: props.stats?.proximos ?? 0, icon: 'pi-clock', color: '#D97706', iconBg: '#FEF3C7', gradient: 'linear-gradient(135deg, #D97706, #B45309)', hint: `${props.stats?.total > 0 ? Math.round((props.stats.proximos / props.stats.total) * 100) : 0}% del total` },
    { label: 'Eventos en Vivo', value: props.stats?.enVivo ?? 0, icon: 'pi-wifi', color: '#DC2626', iconBg: '#FEE2E2', gradient: 'linear-gradient(135deg, #DC2626, #B91C1C)', hint: 'Ahora mismo' },
    { label: 'Eventos Completados', value: props.stats?.completados ?? 0, icon: 'pi-check-circle', color: '#059669', iconBg: '#D1FAE5', gradient: 'linear-gradient(135deg, #059669, #047857)', hint: `${props.stats?.total > 0 ? Math.round((props.stats.completados / props.stats.total) * 100) : 0}% del total` },
]);

// --- Calendario: navegación de mes ---
function irMes(delta) {
    let mes = props.calendario.mes + delta;
    let anio = props.calendario.anio;
    if (mes > 12) { mes = 1; anio++; }
    if (mes < 1) { mes = 12; anio--; }
    router.get(route('admin.eventos.index'), { ...props.filtros, mes, anio }, { preserveState: true, preserveScroll: true, replace: true });
}

function irHoy() {
    router.get(route('admin.eventos.index'), { ...props.filtros, mes: undefined, anio: undefined }, { preserveState: true, preserveScroll: true, replace: true });
}

// --- Anillo de Estadísticas de Eventos: gráfica real con conic-gradient ---
const anilloGradiente = computed(() => {
    const en = props.estadisticas?.enVivo ?? 0;
    const prog = props.estadisticas?.programados ?? 0;
    const comp = props.estadisticas?.completados ?? 0;
    const total = en + prog + comp;

    if (!total) return '#e5e7eb'; // gris: sin eventos en este periodo

    const finEnVivo = (en / total) * 360;
    const finProg = finEnVivo + (prog / total) * 360;

    return `conic-gradient(#ef4444 0deg ${finEnVivo}deg, #fb923c ${finEnVivo}deg ${finProg}deg, #10b981 ${finProg}deg 360deg)`;
});

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
    <Head title="Gestión de Eventos" />

    <AdminLayout>
        <div class="admin-reportes-page">

            <!-- Fila 1: KPIs -->
            <div class="admin-cobros-kpi-grid">
                <div v-for="kpi in kpis" :key="kpi.label" class="admin-cobros-kpi-card">
                    <div class="admin-cobros-kpi-card__icon" :style="{ background: kpi.iconBg, color: kpi.color }">
                        <i class="pi" :class="kpi.icon"></i>
                    </div>
                    <div class="admin-cobros-kpi-card__content">
                        <span class="admin-cobros-kpi-card__label">{{ kpi.label }}</span>
                        <span class="admin-cobros-kpi-card__value" :style="{ color: kpi.color }">{{ kpi.value }}</span>
                        <span class="admin-cobros-kpi-card__hint">{{ kpi.hint }}</span>
                    </div>
                    <div class="admin-cobros-kpi-card__bar" :style="{ background: kpi.gradient }"></div>
                </div>
            </div>

            <!-- Fila 2: Tabla de Eventos | Calendario + Estadísticas (2x2) -->
            <div class="admin-eventos-grid gap-6 w-full mt-6">

                <!-- Tabla de Eventos -->
                <div class="admin-cobros-card min-w-0" style="grid-area:tabla">
                    <div class="flex flex-col flex-1 min-w-0">
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-calendar"></i></div>
                                <div>
                                    <h3>Gestión de Eventos</h3>
                                    <p class="admin-cobros-header-subtitle">Administra y supervisa todos los eventos programados</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link :href="route('admin.eventos.todos')" class="admin-btn-secondary">
                                    <i class="pi pi-list"></i> Ver todos
                                </Link>
                                <Link :href="route('admin.eventos.create')" class="admin-cobros-btn-primary">
                                    <i class="pi pi-plus"></i> Crear Evento
                                </Link>
                            </div>
                        </div>

                        <div class="admin-cobros-filters">
                            <div class="admin-cobros-filters__search">
                                <i class="pi pi-search"></i>
                                <input v-model="q" type="text" placeholder="Buscar evento..." />
                            </div>
                            <select v-model="estado">
                                <option value="">Todos los estados</option>
                                <option value="publicado">Publicado</option>
                                <option value="borrador">Borrador</option>
                                <option value="cancelado">Cancelado</option>
                                <option value="completo">Completo</option>
                            </select>
                            <select v-model="tipo">
                                <option value="">Todos los tipos</option>
                                <option value="vip">VIP</option>
                                <option value="general">General</option>
                            </select>
                        </div>

                        <div class="overflow-x-auto flex-1 flex flex-col">
                            <table class="admin-cobros-table min-w-[700px] flex-1">
                                <thead>
                                    <tr>
                                        <th>Evento</th>
                                        <th>Tipo</th>
                                        <th>Fecha y Hora</th>
                                        <th>Ciudad</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="evento in eventos?.data" :key="evento.id">
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
                                        <td class="text-gray-500 whitespace-nowrap text-xs">
                                            {{ formatDateTime(evento.fecha) }} · {{ evento.hora_formateada }}
                                        </td>
                                        <td class="text-gray-600 text-xs">{{ evento.ciudad ?? '—' }}</td>
                                        <td>
                                            <span class="admin-eventos-badge" :class="estadoBadgeClase[evento.estado_display]">
                                                <span class="admin-eventos-badge-dot"></span>{{ evento.estado_display.replace('_', ' ') }}
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
                                    <tr v-if="!eventos?.data?.length">
                                        <td colspan="6" class="admin-cobros-empty">No se encontraron eventos.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="admin-cobros-table-footer">
                        <Pagination :data="eventos" />
                    </div>
                </div>

                <!-- Calendario -->
                <div class="admin-cobros-card min-w-0" style="grid-area:calendario">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-calendar"></i></div>
                                <h3>Calendario de Eventos</h3>
                            </div>
                        </div>
                        <div style="padding:1.5rem">
                            <Calendario
                                :mes="calendario.mes"
                                :anio="calendario.anio"
                                :nombre-mes="calendario.nombreMes"
                                :dias="calendario.dias"
                                @cambiar-mes="irMes"
                                @hoy="irHoy"
                            />
                        </div>
                    </div>
                </div>

                <!-- Estadísticas de Eventos -->
                <div class="admin-cobros-card min-w-0" style="grid-area:estadisticas">
                    <div style="display:flex;flex-direction:column;height:100%">
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-chart-pie"></i></div>
                                <h3>Estadísticas de Eventos</h3>
                            </div>
                            <select v-model="periodoEstadisticas" @change="cambiarPeriodoStats" class="admin-cobros-select">
                                <option value="dia">Este día</option>
                                <option value="semana">Esta semana</option>
                                <option value="mes">Este mes</option>
                                <option value="anio">Este año</option>
                            </select>
                        </div>
                        <div style="padding:1.5rem;flex:1;display:flex;flex-direction:column;justify-content:space-evenly;gap:1.5rem">
                            <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                                <div class="flex flex-col items-center justify-center p-2 shrink-0">
                                    <div :style="{ width: '110px', height: '110px', borderRadius: '9999px', background: anilloGradiente, display: 'flex', alignItems: 'center', justifyContent: 'center' }">
                                        <div style="width:78px;height:78px;background:#fff;border-radius:9999px;display:flex;flex-direction:column;align-items:center;justify-content:center">
                                            <span class="text-xl font-extrabold text-gray-900 leading-none">{{ estadisticas?.total ?? 0 }}</span>
                                            <span class="text-[10px] text-gray-400 uppercase font-medium mt-0.5">Total</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2.5 text-sm" style="width:180px">
                                    <div class="admin-cobros-legend-item">
                                        <span class="flex items-center gap-2 text-gray-600"><span class="admin-cobros-legend-dot" style="background:#ef4444"></span> En vivo</span>
                                        <span class="font-bold text-gray-900">{{ estadisticas?.enVivo ?? 0 }}</span>
                                    </div>
                                    <div class="admin-cobros-legend-item">
                                        <span class="flex items-center gap-2 text-gray-600"><span class="admin-cobros-legend-dot" style="background:#fb923c"></span> Programados</span>
                                        <span class="font-bold text-gray-900">{{ estadisticas?.programados ?? 0 }}</span>
                                    </div>
                                    <div class="admin-cobros-legend-item">
                                        <span class="flex items-center gap-2 text-gray-600"><span class="admin-cobros-legend-dot" style="background:#10b981"></span> Completados</span>
                                        <span class="font-bold text-gray-900">{{ estadisticas?.completados ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-center gap-3 pt-5" style="border-top:1px solid var(--line)">
                                <div class="admin-user-data-item">
                                    <div class="admin-user-data-icon"><i class="pi pi-users"></i></div>
                                    <div>
                                        <p class="admin-user-data-value">{{ estadisticas?.asistentesTotales ?? 0 }}</p>
                                        <p class="admin-user-data-label" style="margin-top:0.1rem">Asistentes</p>
                                    </div>
                                </div>
                                <div class="admin-user-data-item">
                                    <div class="admin-user-data-icon"><i class="pi pi-star"></i></div>
                                    <div>
                                        <p class="admin-user-data-value">{{ estadisticas?.reservasTotales ?? 0 }}</p>
                                        <p class="admin-user-data-label" style="margin-top:0.1rem">Reservas</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>