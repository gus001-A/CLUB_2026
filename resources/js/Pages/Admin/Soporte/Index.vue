<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps({
    stats: Object,
    reportes: Object,
    filtros: Object,
    porTipo: Object,
    masReportados: Array,
    actividadModeracion: Array,
});

const { confirm } = useConfirm();

const q = ref(props.filtros.q || '');
const tipo = ref(props.filtros.tipo || '');
const estado = ref(props.filtros.estado || '');

let timeout = null;
watch([q, tipo, estado], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.soporte.index'), {
            q: q.value || undefined,
            tipo: tipo.value || undefined,
            estado: estado.value || undefined,
        }, { preserveState: true, replace: true });
    }, 350);
});

function formatDate(v) {
    if (!v) return '—';
    return new Date(v).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

const tipoLabel = { spam: 'Spam', inapropiado: 'Contenido inapropiado', falso: 'Perfil falso', acoso: 'Acoso', otro: 'Otro' };
const tipoBadgeClase = { spam: 'admin-soporte-badge--spam', inapropiado: 'admin-soporte-badge--inapropiado', falso: 'admin-soporte-badge--falso', acoso: 'admin-soporte-badge--acoso', otro: 'admin-soporte-badge--otro' };
const tipoIconoBg = { spam: '#F3F4F6', inapropiado: '#FEF2F2', falso: '#FFFBEB', acoso: '#FEE2E2', otro: '#EFF6FF' };
const tipoIconoColor = { spam: '#4B5563', inapropiado: '#DC2626', falso: '#D97706', acoso: '#B91C1C', otro: '#2563EB' };
const tipoIcono = { spam: 'pi-ban', inapropiado: 'pi-exclamation-triangle', falso: 'pi-id-card', acoso: 'pi-shield', otro: 'pi-question-circle' };
const estadoBadgeClase = { pendiente: 'admin-soporte-badge--pendiente', revisado: 'admin-soporte-badge--revisado', resuelto: 'admin-soporte-badge--resuelto' };
const estadoLabel = { pendiente: 'Pendiente', revisado: 'Revisado', resuelto: 'Resuelto' };

// KPIs con el mismo lenguaje visual del resto del panel
const kpis = computed(() => [
    { label: 'Reportes Totales', value: props.stats.total, icon: 'pi-flag', color: '#DC2626', iconBg: '#FEE2E2', gradient: 'linear-gradient(135deg, #DC2626, #B91C1C)' },
    { label: 'Pendientes', value: props.stats.pendientes, icon: 'pi-clock', color: '#D97706', iconBg: '#FEF3C7', gradient: 'linear-gradient(135deg, #D97706, #B45309)' },
    { label: 'Revisados', value: props.stats.revisados, icon: 'pi-eye', color: '#2563EB', iconBg: '#DBEAFE', gradient: 'linear-gradient(135deg, #2563EB, #1D4ED8)' },
    { label: 'Resueltos', value: props.stats.resueltos, icon: 'pi-check-circle', color: '#059669', iconBg: '#D1FAE5', gradient: 'linear-gradient(135deg, #059669, #047857)' },
]);

async function marcarRevisado(r) {
    router.post(route('admin.soporte.revisar', r.id), {}, { preserveScroll: true });
}

async function resolver(r) {
    const ok = await confirm(`Se marcará el reporte sobre @${r.reportado?.apodo ?? 'usuario'} como resuelto.`, {
        title: 'Resolver reporte',
        confirmLabel: 'Sí, resolver',
        danger: false,
    });
    if (!ok) return;
    router.post(route('admin.soporte.resolver', r.id), {}, { preserveScroll: true });
}

async function bloquear(r) {
    const ok = await confirm(`Esto bloqueará a @${r.reportado?.apodo ?? 'usuario'} y resolverá el reporte.`, {
        title: 'Bloquear usuario reportado',
        confirmLabel: 'Sí, bloquear',
        danger: true,
    });
    if (!ok) return;
    router.post(route('admin.soporte.bloquear', r.id), {}, { preserveScroll: true });
}

async function descartar(r) {
    const ok = await confirm('Este reporte se eliminará. Esta acción no se puede deshacer.', {
        title: 'Descartar reporte',
        confirmLabel: 'Sí, descartar',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('admin.soporte.destroy', r.id), { preserveScroll: true });
}

function contactarReportante(r) {
    if (!r.reporta?.id) return;
    router.post(route('admin.mensajes.iniciar'), {
        usuario_id: r.reporta.id,
        reporte_id: r.id,
        asunto: `Reporte #${r.id} — ${r.tipo_nombre}`,
    });
}
</script>

<template>
    <Head title="Soporte" />

    <AdminLayout>
        <template #title>Soporte</template>
        <template #breadcrumb>Dashboard &gt; Soporte</template>

        <div class="admin-soporte-page">

            <!-- Fila 1: KPIs -->
            <div class="admin-cobros-kpi-grid">
                <div v-for="kpi in kpis" :key="kpi.label" class="admin-cobros-kpi-card">
                    <div class="admin-cobros-kpi-card__icon" :style="{ background: kpi.iconBg, color: kpi.color }">
                        <i class="pi" :class="kpi.icon"></i>
                    </div>
                    <div class="admin-cobros-kpi-card__content">
                        <span class="admin-cobros-kpi-card__label">{{ kpi.label }}</span>
                        <span class="admin-cobros-kpi-card__value" :style="{ color: kpi.color }">{{ kpi.value }}</span>
                    </div>
                    <div class="admin-cobros-kpi-card__bar" :style="{ background: kpi.gradient }"></div>
                </div>
            </div>

            <!-- Fila 2: Cola de Moderación | Reportes por Tipo -->
            <div class="admin-reportes-main-grid gap-6 mb-6 w-full items-stretch">

                <!-- Cola de moderación -->
                <div class="admin-cobros-card min-w-0" style="grid-area:cola">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-shield"></i></div>
                                <div>
                                    <h3>Cola de Moderación</h3>
                                    <p class="admin-cobros-header-subtitle">Revisa y resuelve los reportes entre usuarios</p>
                                </div>
                            </div>
                        </div>

                        <div class="admin-cobros-filters">
                            <div class="admin-cobros-filters__search">
                                <i class="pi pi-search"></i>
                                <input v-model="q" type="text" placeholder="Buscar por usuario..." />
                            </div>
                            <select v-model="tipo">
                                <option value="">Todos los tipos</option>
                                <option value="spam">Spam</option>
                                <option value="inapropiado">Contenido inapropiado</option>
                                <option value="falso">Perfil falso</option>
                                <option value="acoso">Acoso</option>
                                <option value="otro">Otro</option>
                            </select>
                            <select v-model="estado">
                                <option value="">Todos los estados</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="revisado">Revisado</option>
                                <option value="resuelto">Resuelto</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-3" style="padding:0 1.5rem 1.5rem">
                            <div v-for="r in reportes.data" :key="r.id" class="admin-soporte-report-item">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-3 flex-1 min-w-0">
                                        <div class="admin-soporte-report-icon" :style="{ background: tipoIconoBg[r.tipo], color: tipoIconoColor[r.tipo] }">
                                            <i class="pi text-sm" :class="tipoIcono[r.tipo]"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                                <span class="admin-soporte-badge" :class="tipoBadgeClase[r.tipo]">{{ r.tipo_nombre }}</span>
                                                <span class="admin-soporte-badge" :class="estadoBadgeClase[r.estado]">{{ estadoLabel[r.estado] }}</span>
                                                <span class="text-xs text-gray-400">sobre {{ r.reportable_type }}</span>
                                            </div>
                                            <p class="text-sm text-gray-700">
                                                <span class="font-medium">@{{ r.reporta?.apodo ?? '—' }}</span> reportó a
                                                <span class="font-medium">@{{ r.reportado?.apodo ?? '—' }}</span>
                                            </p>
                                            <p v-if="r.descripcion" class="text-sm text-gray-500 mt-1">"{{ r.descripcion }}"</p>
                                            <p class="text-xs text-gray-400 mt-1.5">{{ formatDate(r.created_at) }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0 flex-wrap justify-end" style="max-width:120px">
                                        <button v-if="r.reporta?.id" @click="contactarReportante(r)" class="admin-soporte-btn admin-soporte-btn--brand" title="Iniciar conversación de soporte con el reportante">
                                            <i class="pi pi-comments"></i> Contactar
                                        </button>
                                        <button v-if="r.estado === 'pendiente'" @click="marcarRevisado(r)" class="admin-soporte-btn admin-soporte-btn--info">
                                            Revisar
                                        </button>
                                        <button v-if="r.estado !== 'resuelto'" @click="resolver(r)" class="admin-soporte-btn admin-soporte-btn--success">
                                            Resolver
                                        </button>
                                        <button v-if="r.reportado?.estado !== 'bloqueado'" @click="bloquear(r)" class="admin-soporte-btn admin-soporte-btn--danger">
                                            Bloquear
                                        </button>
                                        <button @click="descartar(r)" class="text-gray-400 hover:text-red-600 px-1" title="Descartar">
                                            <i class="pi pi-trash text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p v-if="!reportes.data.length" class="admin-cobros-empty">No hay reportes con esos filtros.</p>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div v-if="reportes.last_page > 1" class="admin-cobros-table-footer">
                        <Pagination :data="reportes" />
                    </div>
                </div>

                <!-- Reportes por tipo -->
                <div class="admin-cobros-card min-w-0" style="grid-area:tipo">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-chart-bar"></i></div>
                                <h3>Reportes por Tipo</h3>
                            </div>
                        </div>
                        <ul class="space-y-3" style="padding:1.5rem">
                            <li v-for="(cantidad, key) in porTipo" :key="key" class="admin-cobros-legend-item">
                                <span class="flex items-center gap-2 text-gray-600">
                                    <i class="pi text-xs" :class="tipoIcono[key]" :style="{ color: tipoIconoColor[key] }"></i>
                                    {{ tipoLabel[key] }}
                                </span>
                                <span class="font-semibold text-gray-800">{{ cantidad }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>

            <!-- Fila 3: Actividad Reciente de Moderación + Usuarios Más Reportados -->
            <div class="admin-two-col-grid gap-6 w-full items-stretch">

                <!-- Actividad Reciente de Moderación -->
                <div class="admin-cobros-card min-w-0">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-history"></i></div>
                                <h3>Actividad Reciente de Moderación</h3>
                            </div>
                        </div>
                        <div class="admin-dash-list">
                            <div v-for="(a, i) in actividadModeracion" :key="i" class="admin-dash-list-item" style="align-items:flex-start">
                                <div class="admin-dash-list-item__left" style="align-items:flex-start">
                                    <div class="admin-dash-list-icon" :style="a.estado === 'resuelto' ? 'background:#ECFDF5;color:#059669' : 'background:#EFF6FF;color:#2563EB'">
                                        <i class="pi text-xs" :class="a.estado === 'resuelto' ? 'pi-check' : 'pi-eye'"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-700">{{ a.texto }}</p>
                                        <p class="admin-dash-list-meta">{{ a.tipo }} · {{ formatDate(a.fecha) }}</p>
                                    </div>
                                </div>
                            </div>
                            <p v-if="!actividadModeracion?.length" class="admin-cobros-empty">Aún no hay actividad de moderación.</p>
                        </div>
                    </div>
                </div>

                <!-- Usuarios más reportados -->
                <div class="admin-cobros-card min-w-0">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-users"></i></div>
                                <h3>Usuarios Más Reportados</h3>
                            </div>
                        </div>
                        <div class="admin-dash-list">
                            <div v-for="(item, i) in masReportados" :key="i" class="admin-dash-list-item">
                                <div class="admin-dash-list-item__left">
                                    <div class="admin-dash-avatar">{{ item.usuario.nombre?.charAt(0)?.toUpperCase() || 'U' }}</div>
                                    <div class="min-w-0">
                                        <p class="admin-dash-list-title">{{ item.usuario.nombre }}</p>
                                        <p class="admin-dash-list-meta truncate">@{{ item.usuario.apodo }}</p>
                                    </div>
                                </div>
                                <span class="admin-soporte-count-badge">{{ item.cantidad }} {{ item.cantidad === 1 ? 'reporte' : 'reportes' }}</span>
                            </div>
                            <p v-if="!masReportados?.length" class="admin-cobros-empty">Sin reportes registrados.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>