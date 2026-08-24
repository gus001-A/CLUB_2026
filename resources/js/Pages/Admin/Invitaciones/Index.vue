<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip } from 'chart.js';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { useFormatters } from '@/composables/useFormatters';

ChartJS.register(ArcElement, Tooltip);

const props = defineProps({
    stats: Object,
    invitaciones: Object,
    filtros: Object,
});

const toast = useToast();
const { confirm } = useConfirm();
const { formatDate } = useFormatters();

const q = ref(props.filtros.q || '');
const estado = ref(props.filtros.estado || '');
const tipo = ref(props.filtros.tipo || '');
const desde = ref(props.filtros.desde || '');
const hasta = ref(props.filtros.hasta || '');

let timeout = null;
watch([q, estado, tipo, desde, hasta], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.invitaciones.index'), {
            q: q.value || undefined,
            estado: estado.value || undefined,
            tipo: tipo.value || undefined,
            desde: desde.value || undefined,
            hasta: hasta.value || undefined,
        }, { preserveState: true, replace: true });
    }, 350);
});

const tipoNombres = { registro: 'Registro', premium: 'Premium', evento: 'Evento' };
const tipoIconos = { registro: 'pi-user', premium: 'pi-star', evento: 'pi-calendar' };
const tipoBadgeClase = { registro: 'admin-invit-tipo-badge--registro', premium: 'admin-invit-tipo-badge--premium', evento: 'admin-invit-tipo-badge--evento' };
const badgeEstado = { aceptada: 'admin-invit-badge--aceptada', pendiente: 'admin-invit-badge--pendiente', expirada: 'admin-invit-badge--expirada', utilizada: 'admin-invit-badge--utilizada', desactivada: 'admin-invit-badge--desactivada' };
const estadoLabel = { aceptada: 'Aceptada', pendiente: 'Pendiente', expirada: 'Expirada', utilizada: 'Utilizada', desactivada: 'Desactivada' };

async function desactivar(inv) {
    const ok = await confirm(`Se desactivará la invitación de ${inv.nombre_destinatario ?? 'este enlace'}.`, {
        title: 'Desactivar invitación',
        confirmLabel: 'Sí, desactivar',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('admin.invitaciones.destroy', inv.id), { preserveScroll: true });
}

function copiarCodigo(valor) {
    navigator.clipboard.writeText(valor);
    toast.success('Copiado al portapapeles.');
}

const doughnutData = computed(() => ({
    labels: ['Aceptadas', 'Pendientes', 'Expiradas', 'Utilizadas'],
    datasets: [{
        data: [props.stats.aceptadas, props.stats.pendientes, props.stats.expiradas, props.stats.utilizadas],
        backgroundColor: ['#22c55e', '#f59e0b', '#ef4444', '#3b82f6'],
        borderWidth: 0,
    }],
}));

// KPIs con el mismo lenguaje visual que Productos/Cobros/Dashboard
const kpis = computed(() => [
    { label: 'Invitaciones Enviadas', value: props.stats.enviadas, icon: 'pi-envelope', color: '#2563EB', iconBg: '#DBEAFE', gradient: 'linear-gradient(135deg, #2563EB, #1D4ED8)', hint: null },
    { label: 'Invitaciones Aceptadas', value: props.stats.aceptadas, icon: 'pi-check-circle', color: '#059669', iconBg: '#D1FAE5', gradient: 'linear-gradient(135deg, #059669, #047857)', hint: `${props.stats.tasaAceptacion}% del total` },
    { label: 'Invitaciones Pendientes', value: props.stats.pendientes, icon: 'pi-clock', color: '#D97706', iconBg: '#FEF3C7', gradient: 'linear-gradient(135deg, #D97706, #B45309)', hint: null },
    { label: 'Invitaciones Expiradas', value: props.stats.expiradas, icon: 'pi-envelope', color: '#DC2626', iconBg: '#FEE2E2', gradient: 'linear-gradient(135deg, #DC2626, #B91C1C)', hint: null },
]);
</script>

<template>
    <Head title="Invitaciones" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Dashboard &gt; Invitaciones</template>

        <div class="admin-invit-page">
            <!-- Fila 1: KPIs -->
            <div class="admin-invit-kpi-grid">
                <div v-for="kpi in kpis" :key="kpi.label" class="admin-invit-kpi-card">
                    <div class="admin-invit-kpi-card__icon" :style="{ background: kpi.iconBg, color: kpi.color }">
                        <i class="pi" :class="kpi.icon"></i>
                    </div>
                    <div class="admin-invit-kpi-card__content">
                        <span class="admin-invit-kpi-card__label">{{ kpi.label }}</span>
                        <span class="admin-invit-kpi-card__value" :style="{ color: kpi.color }">{{ kpi.value }}</span>
                        <span v-if="kpi.hint" class="admin-invit-kpi-card__hint">{{ kpi.hint }}</span>
                    </div>
                    <div class="admin-invit-kpi-card__bar" :style="{ background: kpi.gradient }"></div>
                </div>
            </div>

            <!-- Fila 2: Gestión de Invitaciones | Resumen -->
            <div class="admin-invitaciones-main-grid gap-6 w-full">

                <!-- Gestión de Invitaciones -->
                <div class="admin-invit-card min-w-0">
                    <div class="flex flex-col flex-1">
                        <div class="admin-invit-card__header">
                            <div class="admin-invit-card__header-left">
                                <div class="admin-invit-header-icon"><i class="pi pi-envelope"></i></div>
                                <div>
                                    <h3>Gestión de Invitaciones</h3>
                                    <p class="admin-invit-header-subtitle">Administra las invitaciones enviadas</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link :href="route('admin.invitaciones.codigos')" class="admin-btn-secondary">
                                    <i class="pi pi-list"></i> Ver todas
                                </Link>
                                <Link :href="route('admin.invitaciones.create')" class="admin-invit-btn-create">
                                    <i class="pi pi-plus"></i> Nueva Invitación
                                </Link>
                            </div>
                        </div>

                        <!-- Filtros -->
                        <div class="admin-cobros-filters">
                            <div class="admin-cobros-filters__search">
                                <i class="pi pi-search"></i>
                                <input v-model="q" type="text" placeholder="Buscar por correo o nombre..." />
                            </div>
                            <select v-model="estado">
                                <option value="">Todos los estados</option>
                                <option value="aceptada">Aceptada</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="expirada">Expirada</option>
                                <option value="utilizada">Utilizada</option>
                                <option value="desactivada">Desactivada</option>
                            </select>
                            <select v-model="tipo">
                                <option value="">Todos los tipos</option>
                                <option value="registro">Registro</option>
                                <option value="premium">Premium</option>
                                <option value="evento">Evento</option>
                            </select>
                            <div class="flex items-center gap-1.5">
                                <input v-model="desde" type="date" />
                                <span class="text-gray-400">—</span>
                                <input v-model="hasta" type="date" />
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div class="overflow-x-auto">
                            <table class="admin-invit-table min-w-[700px]">
                                <thead>
                                    <tr>
                                        <th>Invitado</th>
                                        <th>Correo</th>
                                        <th>Tipo</th>
                                        <th>Enviada por</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="inv in invitaciones.data" :key="inv.id">
                                        <td class="whitespace-nowrap">
                                            <p class="admin-invit-name">{{ inv.nombre_destinatario }}</p>
                                            <p class="admin-invit-code">{{ inv.codigo }}</p>
                                        </td>
                                        <td class="text-gray-600 text-xs whitespace-nowrap">{{ inv.email }}</td>
                                        <td class="whitespace-nowrap">
                                            <span class="admin-invit-tipo-badge" :class="tipoBadgeClase[inv.tipo]">
                                                <i class="pi text-[10px]" :class="tipoIconos[inv.tipo]"></i>
                                                {{ tipoNombres[inv.tipo] ?? inv.tipo }}
                                            </span>
                                        </td>
                                        <td class="text-gray-600 text-xs whitespace-nowrap">{{ inv.creado_por }}</td>
                                        <td class="text-gray-500 text-xs whitespace-nowrap">{{ formatDate(inv.created_at) }}</td>
                                        <td class="whitespace-nowrap">
                                            <span class="admin-invit-badge" :class="badgeEstado[inv.estado]">
                                                <span class="admin-invit-badge-dot"></span>{{ estadoLabel[inv.estado] }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex justify-center items-center gap-1.5">
                                                <button @click="copiarCodigo(inv.codigo)" title="Copiar código" class="admin-invit-action-btn admin-invit-action-btn--copy">
                                                    <i class="pi pi-copy"></i>
                                                </button>
                                                <button v-if="!['aceptada', 'desactivada'].includes(inv.estado)" @click="desactivar(inv)" title="Desactivar" class="admin-invit-action-btn admin-invit-action-btn--delete">
                                                    <i class="pi pi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!invitaciones.data.length">
                                        <td colspan="7" class="admin-invit-empty">No se encontraron invitaciones.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div v-if="invitaciones.last_page > 1" class="admin-invit-table-footer">
                        <Pagination :data="invitaciones" />
                    </div>
                </div>

                <!-- Resumen de Invitaciones -->
                <div class="admin-invit-card min-w-0">
                    <div>
                        <div class="admin-invit-card__header">
                            <div class="admin-invit-card__header-left">
                                <div class="admin-invit-header-icon"><i class="pi pi-chart-pie"></i></div>
                                <h3>Resumen de Invitaciones</h3>
                            </div>
                        </div>
                        <div class="p-6 flex flex-col sm:flex-row items-center justify-center gap-8" style="max-width:560px;margin:0 auto">
                            <div v-if="stats.enviadas" class="relative shrink-0" style="height:180px;width:180px">
                                <Doughnut :data="doughnutData" :options="{ maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '70%' }" />
                                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                    <p class="text-[11px] text-gray-400 font-medium uppercase tracking-wider">Total</p>
                                    <p class="font-bold text-gray-800 text-base">{{ stats.enviadas }}</p>
                                </div>
                            </div>
                            <p v-else class="admin-invit-empty">Aún no hay invitaciones.</p>

                            <ul class="space-y-2" style="width:260px;flex-shrink:0">
                                <li class="admin-cobros-legend-item">
                                    <span class="flex items-center gap-1.5 text-gray-600"><span class="admin-cobros-legend-dot" style="background:#22c55e"></span> Aceptadas</span>
                                    <span class="text-gray-800 font-semibold">{{ stats.aceptadas }}</span>
                                </li>
                                <li class="admin-cobros-legend-item">
                                    <span class="flex items-center gap-1.5 text-gray-600"><span class="admin-cobros-legend-dot" style="background:#f59e0b"></span> Pendientes</span>
                                    <span class="text-gray-800 font-semibold">{{ stats.pendientes }}</span>
                                </li>
                                <li class="admin-cobros-legend-item">
                                    <span class="flex items-center gap-1.5 text-gray-600"><span class="admin-cobros-legend-dot" style="background:#ef4444"></span> Expiradas</span>
                                    <span class="text-gray-800 font-semibold">{{ stats.expiradas }}</span>
                                </li>
                                <li class="admin-cobros-legend-item">
                                    <span class="flex items-center gap-1.5 text-gray-600"><span class="admin-cobros-legend-dot" style="background:#3b82f6"></span> Utilizadas</span>
                                    <span class="text-gray-800 font-semibold">{{ stats.utilizadas }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mx-6 mb-6 pt-4 flex items-center justify-between" style="border-top:1px solid var(--line)">
                        <span class="text-sm text-gray-500">Tasa de Aceptación</span>
                        <span class="font-bold" style="color:var(--brand)">{{ stats.tasaAceptacion }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>