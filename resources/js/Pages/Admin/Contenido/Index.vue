<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KpiCard from '@/Components/KpiCard.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Filler } from 'chart.js';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { useFormatters } from '@/composables/useFormatters';
import { useContenidoMeta } from '@/composables/useContenidoMeta';

ChartJS.register(LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Filler);

const props = defineProps({
    stats: Object,
    contenidos: Object,
    filtros: Object,
    tiposContenido: Object,
    contenidoReciente: Array,
    estadisticas: Object,
});

const toast = useToast();
const { confirm } = useConfirm();
const { formatDate } = useFormatters();
const { tipoLabel, tipoIcono, tipoColor, estadoColores, estadoLabel } = useContenidoMeta();

const q = ref(props.filtros.q || '');
const tipo = ref(props.filtros.tipo || '');
const estado = ref(props.filtros.estado || '');
const periodoEstadisticas = ref(props.filtros.periodo_stats || 'mes');

let timeout = null;
watch([q, tipo, estado], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.contenido.index'), {
            q: q.value || undefined,
            tipo: tipo.value || undefined,
            estado: estado.value || undefined,
            periodo_stats: periodoEstadisticas.value,
        }, { preserveState: true, replace: true });
    }, 350);
});

function cambiarPeriodoEstadisticas() {
    router.get(route('admin.contenido.index'), {
        q: q.value || undefined,
        tipo: tipo.value || undefined,
        estado: estado.value || undefined,
        periodo_stats: periodoEstadisticas.value,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

const tiposIconos = { video: 'pi-video', articulo: 'pi-file-edit', galeria: 'pi-images', audio: 'pi-volume-up', documento: 'pi-file' };
const tiposNombres = { video: 'Videos', articulo: 'Artículos', galeria: 'Galerías', audio: 'Audios', documento: 'Documentos' };

const lineData = computed(() => ({
    labels: props.estadisticas.vistasPorDia.map((d) => new Date(d.fecha).toLocaleDateString('es-MX', { day: '2-digit', month: 'short' })),
    datasets: [{
        label: 'Vistas',
        data: props.estadisticas.vistasPorDia.map((d) => d.total),
        borderColor: '#C81E3A',
        backgroundColor: 'rgba(200, 30, 58, 0.08)',
        fill: true,
        tension: 0.35,
        pointRadius: 2,
    }],
}));
const lineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: {
            ticks: {
                autoSkip: false,
                maxRotation: 0,
                callback: function (value, index) {
                    const label = this.getLabelForValue(value);
                    const total = props.estadisticas.vistasPorDia.length;
                    if (total <= 10) return label;
                    const d = props.estadisticas.vistasPorDia[index];
                    if (!d) return label;
                    const dia = new Date(d.fecha).getDate();
                    return (dia === 1 || (dia % 5 === 0 && dia < 30)) ? label : '';
                },
            },
        },
        y: { beginAtZero: true },
    },
};

const accionesRapidas = [
    { label: 'Nuevo Video', desc: 'Sube y publica un nuevo video', icon: 'pi-video', tipo: 'video' },
    { label: 'Nuevo Artículo', desc: 'Escribe y publica un artículo', icon: 'pi-file-edit', tipo: 'articulo' },
    { label: 'Nueva Galería', desc: 'Crea una nueva galería de fotos', icon: 'pi-images', tipo: 'galeria' },
    { label: 'Administrar Categorías', desc: 'Organiza tus categorías de contenido', icon: 'pi-folder', comingSoon: true },
];

function irA(a) {
    if (a.comingSoon) {
        toast.success(`"${a.label}" estará disponible próximamente.`);
        return;
    }
    router.visit(route('admin.contenido.create', { tipo: a.tipo }));
}

async function eliminarContenido(c) {
    const ok = await confirm(`Esto eliminará "${c.titulo}" permanentemente.`, {
        title: 'Eliminar contenido',
        confirmLabel: 'Sí, eliminar',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('admin.contenido.destroy', c.id), { preserveScroll: true });
}
</script>

<template>
    <Head title="Contenido" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Dashboard &gt; Contenido</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- Fila 1: KPIs -->
            <div class="flex flex-col lg:flex-row gap-6 mb-6 w-full">
                <div class="w-full lg:flex-1 min-w-0">
                    <KpiCard label="Total de Contenidos" :value="stats.total" icon="pi-play"
                        :hint="`+${stats.nuevosEsteMes} nuevos este mes`" />
                </div>
                <div class="w-full lg:flex-1 min-w-0">
                    <KpiCard label="Publicados" :value="stats.publicados" icon="pi-check-circle"
                        :hint="`${stats.total ? Math.round((stats.publicados / stats.total) * 100) : 0}% del total`" hint-color="text-gray-400" />
                </div>
                <div class="w-full lg:flex-1 min-w-0">
                    <KpiCard label="Borradores" :value="stats.borradores" icon="pi-file"
                        :hint="`${stats.total ? Math.round((stats.borradores / stats.total) * 100) : 0}% del total`" hint-color="text-gray-400" />
                </div>
                <div class="w-full lg:flex-1 min-w-0">
                    <KpiCard label="Archivados" :value="stats.archivados" icon="pi-box"
                        :hint="`${stats.total ? Math.round((stats.archivados / stats.total) * 100) : 0}% del total`" hint-color="text-gray-400" />
                </div>
            </div>

            <!-- Fila 2: Gestión de Contenido (2/3) | Tipos + Acciones Rápidas (1/3) -->
            <div class="flex flex-col lg:flex-row gap-6 mb-6 w-full items-stretch">

                <!-- Gestión de Contenido -->
                <div class="w-full lg:flex-[2] min-w-0 bg-white rounded-2xl border border-gray-200/80 shadow-sm flex flex-col justify-between">
                    <div class="flex flex-col flex-1">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-6 pt-6">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Gestión de Contenido</h2>
                                <p class="text-xs text-gray-500 mt-0.5">Administra el contenido publicado en la plataforma.</p>
                            </div>
                            <Link :href="route('admin.contenido.create')"
                                class="bg-brand hover:bg-brand-dark text-white text-sm font-medium px-4 py-2.5 rounded-xl flex items-center justify-center gap-2 transition flex-none shadow-sm">
                                <i class="pi pi-plus text-xs"></i>
                                Nuevo Contenido
                            </Link>
                        </div>

                        <!-- Filtros -->
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 px-6 py-4">
                            <div class="sm:col-span-6 relative">
                                <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input v-model="q" type="text" placeholder="Buscar contenido..."
                                    class="w-full rounded-xl border-gray-300 pl-10 pr-3 py-2 text-sm focus:border-brand focus:ring-brand">
                            </div>
                            <div class="sm:col-span-3">
                                <select v-model="tipo" class="w-full rounded-xl border-gray-300 py-2 text-sm focus:border-brand focus:ring-brand">
                                    <option value="">Todos los tipos</option>
                                    <option value="video">Video</option>
                                    <option value="articulo">Artículo</option>
                                    <option value="galeria">Galería</option>
                                    <option value="audio">Audio</option>
                                    <option value="documento">Documento</option>
                                    <option value="foto">Foto</option>
                                    <option value="exclusivo">Exclusivo</option>
                                </select>
                            </div>
                            <div class="sm:col-span-3">
                                <select v-model="estado" class="w-full rounded-xl border-gray-300 py-2 text-sm focus:border-brand focus:ring-brand">
                                    <option value="">Todos los estados</option>
                                    <option value="publicado">Publicado</option>
                                    <option value="borrador">Borrador</option>
                                    <option value="programado">Programado</option>
                                    <option value="archivado">Archivado</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div class="overflow-x-auto flex-1 flex flex-col">
                            <table class="w-full text-left text-sm min-w-[700px] flex-1">
                            <thead>
                                <tr class="border-y border-gray-100 bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider">
                                    <th class="pl-6 pr-4 py-3 font-semibold">Contenido</th>
                                    <th class="px-3 py-3 font-semibold">Tipo</th>
                                    <th class="px-3 py-3 font-semibold">Categoría</th>
                                    <th class="px-3 py-3 font-semibold">Estado</th>
                                    <th class="px-3 py-3 font-semibold">Fecha</th>
                                    <th class="px-3 py-3 font-semibold">Vistas</th>
                                    <th class="pl-2 pr-6 py-3 text-center font-semibold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="c in contenidos.data" :key="c.id" class="hover:bg-gray-50/50 transition">
                                    <td class="pl-6 pr-4 py-3.5 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 min-w-[36px] max-w-[36px] rounded-lg bg-gray-100 flex items-center justify-center text-gray-300 shrink-0 overflow-hidden">
                                                <img v-if="c.imagen" :src="c.imagen" class="w-full h-full object-cover" />
                                                <i v-else class="pi text-sm" :class="tipoIcono[c.tipo]"></i>
                                            </div>
                                            <p class="font-semibold text-gray-800 text-sm truncate">{{ c.titulo }}</p>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold" :class="tipoColor[c.tipo]">
                                            <i class="pi text-[10px]" :class="tipoIcono[c.tipo]"></i>
                                            {{ tipoLabel[c.tipo] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3.5 text-gray-600 text-xs whitespace-nowrap">{{ c.categoria || '—' }}</td>
                                    <td class="px-3 py-3.5 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold" :class="estadoColores[c.estado]">
                                            {{ estadoLabel[c.estado] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3.5 text-gray-500 text-xs whitespace-nowrap">{{ formatDate(c.created_at, { hour: '2-digit', minute: '2-digit' }) }}</td>
                                    <td class="px-3 py-3.5 text-gray-600 text-xs whitespace-nowrap">{{ c.estado === 'borrador' ? '—' : c.vistas.toLocaleString() }}</td>
                                    <td class="pl-2 pr-6 py-3.5 whitespace-nowrap">
                                        <div class="flex justify-center items-center gap-1.5">
                                            <Link :href="route('admin.contenido.show', c.id)" title="Ver" class="admin-table-action text-gray-600">
                                                <i class="pi pi-eye text-xs"></i>
                                            </Link>
                                            <Link :href="route('admin.contenido.edit', c.id)" title="Editar" class="admin-table-action text-gray-600">
                                                <i class="pi pi-pencil text-xs"></i>
                                            </Link>
                                            <button @click="eliminarContenido(c)" title="Eliminar" class="admin-table-action text-red-600 hover:bg-red-50">
                                                <i class="pi pi-trash text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!contenidos.data.length">
                                    <td colspan="7" class="py-8 text-center text-gray-400 text-xs">No se encontró contenido.</td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </div>

                    <!-- Paginación -->
                    <div v-if="contenidos.last_page > 1" class="border-t border-gray-100 px-6 py-4">
                        <Pagination :data="contenidos" />
                    </div>
                    <div class="border-t border-gray-100 py-3.5 text-center">
                        <Link :href="route('admin.contenido.index')" class="text-brand font-medium hover:underline text-xs">
                            Ver todo el contenido
                        </Link>
                    </div>
                </div>

                <!-- Tipos de Contenido + Acciones Rápidas -->
                <div class="w-full lg:flex-1 min-w-0 flex flex-col gap-6">
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Tipos de Contenido</h2>
                        <ul class="space-y-3">
                            <li v-for="(cantidad, key) in tiposContenido" :key="key" class="flex items-center justify-between text-sm">
                                <span class="flex items-center gap-2 text-gray-600">
                                    <i class="pi text-brand" :class="tiposIconos[key]"></i>
                                    {{ tiposNombres[key] }}
                                </span>
                                <span class="font-semibold text-gray-800">{{ cantidad }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 flex-1">
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
                </div>
            </div>

            <!-- Fila 3: Estadísticas de Contenido (2/3) | Contenido Reciente (1/3) -->
            <div class="flex flex-col lg:flex-row gap-6 w-full items-stretch">

                <!-- Contenido Reciente -->
                <div class="w-full lg:flex-1 min-w-0 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-gray-900 text-lg">Contenido Reciente</h2>
                            <Link :href="route('admin.contenido.index')" class="text-xs font-semibold text-brand hover:underline">Ver todos</Link>
                        </div>
                        <ul class="space-y-3.5">
                            <li v-for="c in contenidoReciente" :key="c.id" class="flex items-center gap-3">
                                <div class="w-9 h-9 min-w-[36px] max-w-[36px] rounded-lg bg-gray-100 flex items-center justify-center text-gray-300 shrink-0 overflow-hidden">
                                    <img v-if="c.imagen" :src="c.imagen" class="w-full h-full object-cover" />
                                    <i v-else class="pi text-sm" :class="tipoIcono[c.tipo]"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold text-gray-800 truncate">{{ c.titulo }}</p>
                                    <p class="text-[10px] text-gray-400">{{ tipoLabel[c.tipo] }} · {{ formatDate(c.created_at, { hour: '2-digit', minute: '2-digit' }) }}</p>
                                </div>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full shrink-0" :class="estadoColores[c.estado]">
                                    {{ estadoLabel[c.estado] }}
                                </span>
                            </li>
                            <li v-if="!contenidoReciente?.length" class="text-gray-400 text-xs text-center py-6">Aún no hay contenido.</li>
                        </ul>
                    </div>
                </div>

                <!-- Estadísticas de Contenido -->
                <div class="w-full lg:flex-[2] min-w-0 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-gray-900 text-lg">Estadísticas de Contenido</h2>
                            <select v-model="periodoEstadisticas" @change="cambiarPeriodoEstadisticas"
                                class="text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 text-gray-500 focus:outline-none focus:border-brand">
                                <option value="semana">Esta semana</option>
                                <option value="mes">Este mes</option>
                                <option value="anio">Este año</option>
                            </select>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1" style="height:240px">
                                <Line :data="lineData" :options="lineOptions" />
                            </div>
                            <div class="flex sm:flex-col gap-4 sm:w-44 justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0" style="width:36px;height:36px">
                                        <i class="pi pi-eye text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">{{ estadisticas.vistasTotales.toLocaleString() }}</p>
                                        <p class="text-[10px] text-gray-400">Vistas totales</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0" style="width:36px;height:36px">
                                        <i class="pi pi-users text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">{{ estadisticas.usuariosUnicos.toLocaleString() }}</p>
                                        <p class="text-[10px] text-gray-400">Usuarios únicos</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0" style="width:36px;height:36px">
                                        <i class="pi pi-heart text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">{{ estadisticas.interaccionesTotales.toLocaleString() }}</p>
                                        <p class="text-[10px] text-gray-400">Interacciones</p>
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