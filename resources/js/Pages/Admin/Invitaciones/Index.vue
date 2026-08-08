<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KpiCard from '@/Components/KpiCard.vue';
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
    enlacesActivos: Array,
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
const tipoColores = {
    registro: 'bg-red-50 text-red-600',
    premium: 'bg-purple-50 text-purple-600',
    evento: 'bg-blue-50 text-blue-600',
};
const estadoColores = {
    aceptada: 'bg-green-100 text-green-700',
    pendiente: 'bg-amber-100 text-amber-700',
    expirada: 'bg-red-100 text-red-700',
    utilizada: 'bg-blue-100 text-blue-700',
};
const estadoLabel = { aceptada: 'Aceptada', pendiente: 'Pendiente', expirada: 'Expirada', utilizada: 'Utilizada' };

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
    labels: ['Aceptadas', 'Pendientes', 'Expiradas'],
    datasets: [{
        data: [props.stats.aceptadas, props.stats.pendientes, props.stats.expiradas],
        backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
        borderWidth: 0,
    }],
}));

const accionesRapidas = [
    { label: 'Nueva Invitación', desc: 'Invita usuarios a la plataforma', icon: 'pi-envelope', route: 'admin.invitaciones.create' },
    { label: 'Invitar a Evento', desc: 'Invita usuarios a un evento específico', icon: 'pi-calendar-plus', route: 'admin.eventos.index' },
    { label: 'Invitación Masiva', desc: 'Envía invitaciones a varios usuarios', icon: 'pi-users', proximamente: true },
    { label: 'Plantillas de Invitación', desc: 'Gestiona tus plantillas personalizadas', icon: 'pi-file', proximamente: true },
    { label: 'Enlaces de Invitación', desc: 'Crea y comparte enlaces de invitación', icon: 'pi-link', anchor: '#enlaces' },
];

function irA(a) {
    if (a.proximamente) {
        toast.success('Próximamente disponible.');
        return;
    }
    if (a.anchor) {
        document.querySelector(a.anchor)?.scrollIntoView({ behavior: 'smooth' });
        return;
    }
    router.visit(route(a.route));
}
</script>

<template>
    <Head title="Invitaciones" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Dashboard &gt; Invitaciones</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- Fila 1: KPIs (4 columnas) -->
            <div class="admin-kpi-grid gap-6 mb-6 w-full">
                <div class="min-w-0">
                    <KpiCard label="Invitaciones Enviadas" :value="stats.enviadas" icon="pi-envelope" />
                </div>
                <div class="min-w-0">
                    <KpiCard label="Invitaciones Aceptadas" :value="stats.aceptadas" icon="pi-check-circle"
                        :hint="`${stats.tasaAceptacion}% del total`" />
                </div>
                <div class="min-w-0">
                    <KpiCard label="Invitaciones Pendientes" :value="stats.pendientes" icon="pi-clock" />
                </div>
                <div class="min-w-0">
                    <KpiCard label="Invitaciones Expiradas" :value="stats.expiradas" icon="pi-envelope" />
                </div>
            </div>

            <!-- Fila 2: Gestión + Enlaces | Acciones Rápidas + Resumen -->
            <div class="admin-invitaciones-main-grid gap-6 w-full">

                <!-- Gestión de Invitaciones -->
                <div class="min-w-0 bg-white rounded-2xl border border-gray-200/80 shadow-sm flex flex-col" style="grid-area:gestion">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-6 pt-6">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Gestión de Invitaciones</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Administra las invitaciones enviadas.</p>
                        </div>
                        <Link :href="route('admin.invitaciones.create')"
                            class="bg-brand hover:bg-brand-dark text-white text-sm font-medium px-4 py-2.5 rounded-xl flex items-center justify-center gap-2 transition flex-none shadow-sm">
                            <i class="pi pi-plus text-xs"></i>
                            Nueva Invitación
                        </Link>
                    </div>

                    <!-- Filtros -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 px-6 py-4">
                        <div class="sm:col-span-4 relative">
                            <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input v-model="q" type="text" placeholder="Buscar por correo o nombre..."
                                class="w-full rounded-xl border-gray-300 pl-10 pr-3 py-2 text-sm focus:border-brand focus:ring-brand">
                        </div>
                        <div class="sm:col-span-2">
                            <select v-model="estado" class="w-full rounded-xl border-gray-300 py-2 text-sm focus:border-brand focus:ring-brand">
                                <option value="">Todos los estados</option>
                                <option value="aceptada">Aceptada</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="expirada">Expirada</option>
                                <option value="utilizada">Utilizada</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <select v-model="tipo" class="w-full rounded-xl border-gray-300 py-2 text-sm focus:border-brand focus:ring-brand">
                                <option value="">Todos los tipos</option>
                                <option value="registro">Registro</option>
                                <option value="premium">Premium</option>
                                <option value="evento">Evento</option>
                            </select>
                        </div>
                        <div class="sm:col-span-4 flex items-center gap-1.5">
                            <input v-model="desde" type="date" class="w-full min-w-0 rounded-xl border-gray-300 py-2 px-2 text-xs xl:text-sm focus:border-brand focus:ring-brand">
                            <span class="text-gray-400">—</span>
                            <input v-model="hasta" type="date" class="w-full min-w-0 rounded-xl border-gray-300 py-2 px-2 text-xs xl:text-sm focus:border-brand focus:ring-brand">
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left text-sm min-w-[700px]">
                            <thead>
                                <tr class="border-y border-gray-100 bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider">
                                    <th class="pl-6 pr-4 py-3 font-semibold">Invitado</th>
                                    <th class="px-3 py-3 font-semibold">Correo</th>
                                    <th class="px-3 py-3 font-semibold">Tipo</th>
                                    <th class="px-3 py-3 font-semibold">Enviada por</th>
                                    <th class="px-3 py-3 font-semibold">Fecha</th>
                                    <th class="px-3 py-3 font-semibold">Estado</th>
                                    <th class="pl-2 pr-6 py-3 text-center font-semibold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="inv in invitaciones.data" :key="inv.id" class="hover:bg-gray-50/50 transition">
                                    <td class="pl-6 pr-4 py-3.5 whitespace-nowrap">
                                        <p class="font-semibold text-gray-800 text-sm leading-tight">{{ inv.nombre_destinatario }}</p>
                                        <p class="text-xs text-gray-400">{{ inv.codigo }}</p>
                                    </td>
                                    <td class="px-3 py-3.5 text-gray-600 text-xs whitespace-nowrap">{{ inv.email }}</td>
                                    <td class="px-3 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold" :class="tipoColores[inv.tipo]">
                                            <i class="pi text-[10px]" :class="tipoIconos[inv.tipo]"></i>
                                            {{ tipoNombres[inv.tipo] ?? inv.tipo }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3.5 text-gray-600 text-xs whitespace-nowrap">{{ inv.creado_por }}</td>
                                    <td class="px-3 py-3.5 text-gray-500 text-xs whitespace-nowrap">{{ formatDate(inv.created_at) }}</td>
                                    <td class="px-3 py-3.5 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold" :class="estadoColores[inv.estado]">
                                            {{ estadoLabel[inv.estado] }}
                                        </span>
                                    </td>
                                    <td class="pl-2 pr-6 py-3.5 whitespace-nowrap">
                                        <div class="flex justify-center items-center gap-1.5">
                                            <button @click="copiarCodigo(inv.codigo)" title="Copiar código"
                                                class="w-8 h-8 min-w-[32px] max-w-[32px] rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 transition flex items-center justify-center">
                                                <i class="pi pi-copy text-xs"></i>
                                            </button>
                                            <button v-if="inv.estado !== 'aceptada'" @click="desactivar(inv)" title="Desactivar"
                                                class="w-8 h-8 min-w-[32px] max-w-[32px] rounded-lg border border-gray-200 text-red-600 hover:bg-red-50 transition flex items-center justify-center">
                                                <i class="pi pi-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!invitaciones.data.length">
                                    <td colspan="7" class="py-8 text-center text-gray-400 text-xs">No se encontraron invitaciones.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación o Enlace Ver todas las invitaciones -->
                    <div class="border-t border-gray-100 px-6 py-4">
                        <Pagination v-if="invitaciones.last_page > 1" :data="invitaciones" />
                        <div v-else class="w-full text-center py-1">
                            <Link :href="route('admin.invitaciones.codigos')" class="text-xs font-semibold text-brand hover:underline">
                                Ver todas las invitaciones
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Enlaces de Invitación Activos -->
                <div id="enlaces" class="min-w-0 bg-white rounded-2xl border border-gray-200/80 shadow-sm px-6 py-6 flex flex-col justify-between" style="grid-area:enlaces">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Enlaces de Invitación Activos</h2>
                        <div class="overflow-x-auto w-full">
                            <table class="w-full text-left text-sm min-w-[600px]">
                                <thead>
                                    <tr class="border-y border-gray-100 bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider">
                                        <th class="pl-2 pr-4 py-3 font-semibold">Enlace</th>
                                        <th class="px-3 py-3 font-semibold">Tipo</th>
                                        <th class="px-3 py-3 font-semibold">Usos</th>
                                        <th class="px-3 py-3 font-semibold">Creado</th>
                                        <th class="px-3 py-3 font-semibold">Estado</th>
                                        <th class="pl-2 pr-2 py-3 text-center font-semibold">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="e in enlacesActivos" :key="e.id" class="hover:bg-gray-50/50 transition">
                                        <td class="pl-2 pr-4 py-3.5">
                                            <span class="flex items-center gap-1.5 text-gray-600 text-xs">
                                                <i class="pi pi-link text-brand text-xs"></i>
                                                <span class="truncate max-w-[220px]">{{ e.url }}</span>
                                            </span>
                                        </td>
                                        <td class="px-3 py-3.5 text-gray-500 text-xs whitespace-nowrap">{{ tipoNombres[e.tipo] ?? e.tipo }}</td>
                                        <td class="px-3 py-3.5 text-gray-500 text-xs whitespace-nowrap">{{ e.usos }} / {{ e.usos_maximos }}</td>
                                        <td class="px-3 py-3.5 text-gray-400 text-xs whitespace-nowrap">{{ formatDate(e.created_at) }}</td>
                                        <td class="px-3 py-3.5 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold" :class="e.activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                                {{ e.activo ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td class="pl-2 pr-2 py-3.5">
                                            <div class="flex justify-center items-center gap-1.5">
                                                <button @click="copiarCodigo(e.url)" title="Copiar enlace"
                                                    class="w-8 h-8 min-w-[32px] max-w-[32px] rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 transition flex items-center justify-center">
                                                    <i class="pi pi-copy text-xs"></i>
                                                </button>
                                                <button v-if="e.activo" @click="desactivar(e)" title="Desactivar"
                                                    class="w-8 h-8 min-w-[32px] max-w-[32px] rounded-lg border border-gray-200 text-red-600 hover:bg-red-50 transition flex items-center justify-center">
                                                    <i class="pi pi-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!enlacesActivos?.length">
                                        <td colspan="6" class="py-6 text-center text-gray-400 text-xs">No hay enlaces activos.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="min-w-0 bg-white rounded-2xl border border-gray-200 shadow-sm p-4" style="grid-area:acciones">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 px-2 pt-2">Acciones Rápidas</h2>
                    <div class="space-y-3">
                        <button v-for="a in accionesRapidas" :key="a.label" type="button" @click="irA(a)"
                            class="w-full flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 transition group text-left">
                            <div class="rounded-full bg-red-50 text-brand flex items-center justify-center shrink-0" style="width:44px;height:44px">
                                <i class="pi text-sm" :class="a.icon"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-800 group-hover:text-brand transition">{{ a.label }}</p>
                                <p class="text-xs text-gray-400">{{ a.desc }}</p>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Resumen de Invitaciones -->
                <div class="min-w-0 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between" style="grid-area:resumen">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-5">Resumen de Invitaciones</h2>

                        <div v-if="stats.enviadas" class="relative mx-auto" style="height:180px;width:180px">
                            <Doughnut :data="doughnutData" :options="{ maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '70%' }" />
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <p class="text-[11px] text-gray-400 font-medium uppercase tracking-wider">Total</p>
                                <p class="font-bold text-gray-800 text-base">{{ stats.enviadas }}</p>
                            </div>
                        </div>
                        <p v-else class="text-gray-400 text-sm text-center py-10">Aún no hay invitaciones.</p>

                        <ul class="mt-5 space-y-2 text-xs">
                            <li class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-gray-600"><span class="rounded-full bg-green-500" style="width:8px;height:8px"></span> Aceptadas</span>
                                <span class="text-gray-800 font-semibold">{{ stats.aceptadas }}</span>
                            </li>
                            <li class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-gray-600"><span class="rounded-full bg-amber-500" style="width:8px;height:8px"></span> Pendientes</span>
                                <span class="text-gray-800 font-semibold">{{ stats.pendientes }}</span>
                            </li>
                            <li class="flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-gray-600"><span class="rounded-full bg-red-500" style="width:8px;height:8px"></span> Expiradas</span>
                                <span class="text-gray-800 font-semibold">{{ stats.expiradas }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-sm text-gray-500">Tasa de Aceptación</span>
                        <span class="font-bold text-brand">{{ stats.tasaAceptacion }}%</span>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>