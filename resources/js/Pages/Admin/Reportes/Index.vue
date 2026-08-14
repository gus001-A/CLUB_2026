<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
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
        router.get(route('admin.reportes.index'), {
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
const tipoColor = { spam: 'bg-gray-100 text-gray-600', inapropiado: 'bg-red-50 text-red-600', falso: 'bg-amber-50 text-amber-600', acoso: 'bg-red-100 text-red-700', otro: 'bg-blue-50 text-blue-600' };
const tipoIcono = { spam: 'pi-ban', inapropiado: 'pi-exclamation-triangle', falso: 'pi-id-card', acoso: 'pi-shield', otro: 'pi-question-circle' };
const tipoIconoColor = { spam: 'text-red-500', inapropiado: 'text-amber-500', falso: 'text-orange-500', acoso: 'text-blue-500', otro: 'text-purple-500' };
const estadoColores = { pendiente: 'bg-amber-100 text-amber-700', revisado: 'bg-blue-100 text-blue-700', resuelto: 'bg-green-100 text-green-700' };
const estadoLabel = { pendiente: 'Pendiente', revisado: 'Revisado', resuelto: 'Resuelto' };

async function marcarRevisado(r) {
    router.post(route('admin.reportes.revisar', r.id), {}, { preserveScroll: true });
}

async function resolver(r) {
    const ok = await confirm(`Se marcará el reporte sobre @${r.reportado?.apodo ?? 'usuario'} como resuelto.`, {
        title: 'Resolver reporte',
        confirmLabel: 'Sí, resolver',
        danger: false,
    });
    if (!ok) return;
    router.post(route('admin.reportes.resolver', r.id), {}, { preserveScroll: true });
}

async function bloquear(r) {
    const ok = await confirm(`Esto bloqueará a @${r.reportado?.apodo ?? 'usuario'} y resolverá el reporte.`, {
        title: 'Bloquear usuario reportado',
        confirmLabel: 'Sí, bloquear',
        danger: true,
    });
    if (!ok) return;
    router.post(route('admin.reportes.bloquear', r.id), {}, { preserveScroll: true });
}

async function descartar(r) {
    const ok = await confirm('Este reporte se eliminará. Esta acción no se puede deshacer.', {
        title: 'Descartar reporte',
        confirmLabel: 'Sí, descartar',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('admin.reportes.destroy', r.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Reportes" />

    <AdminLayout>
        <template #title>Reportes</template>
        <template #breadcrumb>Dashboard &gt; Reportes</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- Fila 1: KPIs -->
            <div class="admin-kpi-grid gap-6 mb-6 w-full">
                <div class="min-w-0 admin-kpi-card">
                    <div>
                        <p class="text-sm text-gray-400">Reportes Totales</p>
                        <p class="text-2xl font-semibold text-gray-800 mt-1">{{ stats.total }}</p>
                    </div>
                    <div class="rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0" style="width:44px;height:44px">
                        <i class="pi pi-flag text-lg"></i>
                    </div>
                </div>
                <div class="min-w-0 admin-kpi-card">
                    <div>
                        <p class="text-sm text-gray-400">Pendientes</p>
                        <p class="text-2xl font-semibold text-gray-800 mt-1">{{ stats.pendientes }}</p>
                    </div>
                    <div class="rounded-full bg-amber-50 text-amber-600 flex items-center justify-center shrink-0" style="width:44px;height:44px">
                        <i class="pi pi-clock text-lg"></i>
                    </div>
                </div>
                <div class="min-w-0 admin-kpi-card">
                    <div>
                        <p class="text-sm text-gray-400">Revisados</p>
                        <p class="text-2xl font-semibold text-gray-800 mt-1">{{ stats.revisados }}</p>
                    </div>
                    <div class="rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0" style="width:44px;height:44px">
                        <i class="pi pi-eye text-lg"></i>
                    </div>
                </div>
                <div class="min-w-0 admin-kpi-card">
                    <div>
                        <p class="text-sm text-gray-400">Resueltos</p>
                        <p class="text-2xl font-semibold text-gray-800 mt-1">{{ stats.resueltos }}</p>
                    </div>
                    <div class="rounded-full bg-green-50 text-green-600 flex items-center justify-center shrink-0" style="width:44px;height:44px">
                        <i class="pi pi-check-circle text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- Fila 2: Cola de Moderación | Reportes por Tipo -->
            <div class="admin-reportes-main-grid gap-6 mb-6 w-full">

                <!-- Cola de moderación -->
                <div class="admin-card overflow-hidden" style="grid-area:cola">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-shield text-brand"></i> Cola de Moderación</span>
                    </div>
                    <p class="text-xs px-6 pt-4" style="color:var(--muted)">Revisa y resuelve los reportes entre usuarios.</p>

                    <div class="flex flex-col sm:flex-row gap-3 mb-4 px-6 pt-4">
                        <div class="relative flex-1 min-w-[160px]">
                            <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-sm pointer-events-none"></i>
                            <input v-model="q" type="text" placeholder="Buscar por usuario..." class="admin-input pl-10 pr-3 py-2" />
                        </div>
                        <select v-model="tipo" class="admin-input w-auto py-2">
                            <option value="">Todos los tipos</option>
                            <option value="spam">Spam</option>
                            <option value="inapropiado">Contenido inapropiado</option>
                            <option value="falso">Perfil falso</option>
                            <option value="acoso">Acoso</option>
                            <option value="otro">Otro</option>
                        </select>
                        <select v-model="estado" class="admin-input w-auto py-2">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="revisado">Revisado</option>
                            <option value="resuelto">Resuelto</option>
                        </select>
                    </div>

                    <ul class="space-y-3 px-6 pb-6">
                        <li v-for="r in reportes.data" :key="r.id" class="border border-gray-100 rounded-xl p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3 flex-1 min-w-0">
                                    <div class="rounded-lg flex items-center justify-center shrink-0" :class="tipoColor[r.tipo]" style="width:36px;height:36px">
                                        <i class="pi text-sm" :class="tipoIcono[r.tipo]"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap mb-1">
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="tipoColor[r.tipo]">{{ r.tipo_nombre }}</span>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="estadoColores[r.estado]">{{ estadoLabel[r.estado] }}</span>
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
                                <div class="flex items-center gap-2 shrink-0">
                                    <button v-if="r.estado === 'pendiente'" @click="marcarRevisado(r)" class="text-xs text-blue-600 border border-blue-200 rounded-lg px-2.5 py-1.5 hover:bg-blue-50">
                                        Revisar
                                    </button>
                                    <button v-if="r.estado !== 'resuelto'" @click="resolver(r)" class="text-xs text-green-600 border border-green-200 rounded-lg px-2.5 py-1.5 hover:bg-green-50">
                                        Resolver
                                    </button>
                                    <button v-if="r.reportado?.estado !== 'bloqueado'" @click="bloquear(r)" class="text-xs text-red-600 border border-red-200 rounded-lg px-2.5 py-1.5 hover:bg-red-50">
                                        Bloquear
                                    </button>
                                    <button @click="descartar(r)" class="text-gray-400 hover:text-red-600 px-1" title="Descartar">
                                        <i class="pi pi-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </li>
                        <li v-if="!reportes.data.length" class="py-8 text-center text-gray-400 text-sm">No hay reportes con esos filtros.</li>
                    </ul>

                    <!-- Paginación -->
                    <div v-if="reportes.last_page > 1" class="border-t border-gray-100 px-6 py-4">
                        <Pagination :data="reportes" />
                    </div>
                </div>

                <!-- Reportes por tipo -->
                <div class="admin-card overflow-hidden" style="grid-area:tipo">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-chart-bar text-brand"></i> Reportes por Tipo</span>
                    </div>
                    <ul class="space-y-3 p-6">
                        <li v-for="(cantidad, key) in porTipo" :key="key" class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2 text-gray-600">
                                <i class="pi text-xs" :class="[tipoIcono[key], tipoIconoColor[key]]"></i>
                                {{ tipoLabel[key] }}
                            </span>
                            <span class="font-medium text-gray-800">{{ cantidad }}</span>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Fila 3: Actividad Reciente de Moderación + Usuarios Más Reportados -->
            <div class="admin-two-col-grid gap-6 w-full">

                <!-- Actividad Reciente de Moderación -->
                <div class="admin-card overflow-hidden">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-history text-brand"></i> Actividad Reciente de Moderación</span>
                    </div>
                    <ul class="space-y-3 p-6">
                        <li v-for="(a, i) in actividadModeracion" :key="i" class="flex items-start gap-3">
                            <div
                                class="rounded-full flex items-center justify-center shrink-0"
                                :class="a.estado === 'resuelto' ? 'bg-green-50 text-green-600' : 'bg-blue-50 text-blue-600'"
                                style="width:32px;height:32px"
                            >
                                <i class="pi text-xs" :class="a.estado === 'resuelto' ? 'pi-check' : 'pi-eye'"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-700">{{ a.texto }}</p>
                                <p class="text-xs text-gray-400">{{ a.tipo }} · {{ formatDate(a.fecha) }}</p>
                            </div>
                        </li>
                        <li v-if="!actividadModeracion?.length" class="text-gray-400 text-sm text-center py-6">
                            Aún no hay actividad de moderación.
                        </li>
                    </ul>
                </div>

                <!-- Usuarios más reportados -->
                <div class="admin-card overflow-hidden">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-users text-brand"></i> Usuarios Más Reportados</span>
                    </div>
                    <ul class="space-y-3 p-6">
                        <li v-for="(item, i) in masReportados" :key="i" class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="rounded-full bg-gray-100 flex items-center justify-center text-gray-400 shrink-0 text-xs font-semibold" style="width:32px;height:32px">
                                    {{ item.usuario.nombre?.charAt(0)?.toUpperCase() || 'U' }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ item.usuario.nombre }}</p>
                                    <p class="text-xs text-gray-400 truncate">@{{ item.usuario.apodo }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-50 text-red-600 shrink-0">
                                {{ item.cantidad }} {{ item.cantidad === 1 ? 'reporte' : 'reportes' }}
                            </span>
                        </li>
                        <li v-if="!masReportados?.length" class="text-gray-400 text-sm text-center py-6">
                            Sin reportes registrados.
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>