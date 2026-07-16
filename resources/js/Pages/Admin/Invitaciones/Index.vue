<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, ArcElement, Tooltip } from 'chart.js';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

ChartJS.register(ArcElement, Tooltip);

const props = defineProps({
    stats: Object,
    invitaciones: Object,
    filtros: Object,
});

const toast = useToast();
const { confirm } = useConfirm();

const q = ref(props.filtros.q || '');
const estado = ref(props.filtros.estado || '');

let timeout = null;
watch([q, estado], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.invitaciones.index'), {
            q: q.value || undefined,
            estado: estado.value || undefined,
        }, { preserveState: true, replace: true });
    }, 350);
});

function formatDate(v) {
    if (!v) return '—';
    return new Date(v).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
}

const tipoNombres = { registro: 'Registro', premium: 'Premium', evento: 'Evento' };
const estadoColores = {
    aceptada: 'bg-green-100 text-green-700',
    pendiente: 'bg-amber-100 text-amber-700',
    expirada: 'bg-red-100 text-red-700',
    utilizada: 'bg-blue-100 text-blue-700',
};
const estadoLabel = { aceptada: 'Aceptada', pendiente: 'Pendiente', expirada: 'Expirada', utilizada: 'Utilizada' };

async function desactivar(inv) {
    const ok = await confirm(`Se desactivará la invitación de ${inv.nombre_destinatario}.`, {
        title: 'Desactivar invitación',
        confirmLabel: 'Sí, desactivar',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('admin.invitaciones.destroy', inv.id), { preserveScroll: true });
}

function copiarCodigo(codigo) {
    navigator.clipboard.writeText(codigo);
    toast.success(`Código ${codigo} copiado.`);
}

const doughnutData = computed(() => ({
    labels: ['Aceptadas', 'Pendientes', 'Expiradas'],
    datasets: [{
        data: [props.stats.aceptadas, props.stats.pendientes, props.stats.expiradas],
        backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
        borderWidth: 0,
    }],
}));
</script>

<template>
    <Head title="Invitaciones" />

    <AdminLayout>
        <template #title>Invitaciones</template>
        <template #breadcrumb>Dashboard &gt; Invitaciones</template>

        <!-- KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-sm text-gray-400">Invitaciones Enviadas</p>
                <p class="text-2xl font-semibold text-gray-800 mt-1">{{ stats.enviadas }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-sm text-gray-400">Invitaciones Aceptadas</p>
                <p class="text-2xl font-semibold text-gray-800 mt-1">{{ stats.aceptadas }}</p>
                <p class="text-xs text-brand mt-1">{{ stats.tasaAceptacion }}% del total</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-sm text-gray-400">Invitaciones Pendientes</p>
                <p class="text-2xl font-semibold text-gray-800 mt-1">{{ stats.pendientes }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <p class="text-sm text-gray-400">Invitaciones Expiradas</p>
                <p class="text-2xl font-semibold text-gray-800 mt-1">{{ stats.expiradas }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Gestión -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-800">Gestión de Invitaciones</h2>
                    <Link :href="route('admin.invitaciones.create')" class="bg-brand hover:bg-brand-dark text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                        <i class="pi pi-plus text-xs"></i> Nueva Invitación
                    </Link>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 mb-4">
                    <div class="relative flex-1 min-w-[180px]">
                        <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-sm pointer-events-none"></i>
                        <input v-model="q" type="text" placeholder="Buscar por correo o nombre..." class="w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                    </div>
                    <select v-model="estado" class="rounded-lg border border-gray-300 text-sm px-3 py-2 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                        <option value="">Todos los estados</option>
                        <option value="aceptada">Aceptada</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="expirada">Expirada</option>
                        <option value="utilizada">Utilizada</option>
                    </select>
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-400 border-b border-gray-100">
                            <th class="pb-2 font-medium">Invitado</th>
                            <th class="pb-2 font-medium">Correo</th>
                            <th class="pb-2 font-medium">Tipo</th>
                            <th class="pb-2 font-medium">Enviada por</th>
                            <th class="pb-2 font-medium">Fecha</th>
                            <th class="pb-2 font-medium">Estado</th>
                            <th class="pb-2 font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="inv in invitaciones.data" :key="inv.id" class="border-b border-gray-50 last:border-0 hover:bg-gray-50/70">
                            <td class="py-2.5">
                                <p class="font-medium text-gray-800">{{ inv.nombre_destinatario }}</p>
                                <p class="text-gray-400 text-xs">{{ inv.codigo }}</p>
                            </td>
                            <td class="py-2.5 text-gray-500">{{ inv.email }}</td>
                            <td class="py-2.5">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    {{ tipoNombres[inv.tipo] ?? inv.tipo }}
                                </span>
                            </td>
                            <td class="py-2.5 text-gray-500">{{ inv.creado_por }}</td>
                            <td class="py-2.5 text-gray-400">{{ formatDate(inv.created_at) }}</td>
                            <td class="py-2.5">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="estadoColores[inv.estado]">
                                    {{ estadoLabel[inv.estado] }}
                                </span>
                            </td>
                            <td class="py-2.5">
                                <div class="flex items-center gap-3 text-gray-400">
                                    <i class="pi pi-copy cursor-pointer hover:text-gray-700" title="Copiar código" @click="copiarCodigo(inv.codigo)"></i>
                                    <i
                                        v-if="inv.estado !== 'aceptada'"
                                        class="pi pi-trash cursor-pointer hover:text-red-600"
                                        title="Desactivar"
                                        @click="desactivar(inv)"
                                    ></i>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!invitaciones.data.length">
                            <td colspan="7" class="py-8 text-center text-gray-400">No se encontraron invitaciones.</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div v-if="invitaciones.last_page > 1" class="flex items-center justify-between mt-5 text-sm">
                    <p class="text-gray-400">Mostrando {{ invitaciones.from }}–{{ invitaciones.to }} de {{ invitaciones.total }}</p>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in invitaciones.links" :key="i">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                                preserve-state
                                class="px-3 py-1.5 rounded-lg"
                                :class="link.active ? 'bg-brand text-white' : 'text-gray-500 hover:bg-gray-100'"
                                v-html="link.label"
                            />
                            <span v-else class="px-3 py-1.5 text-gray-300" v-html="link.label"></span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas + resumen -->
            <div class="space-y-6">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-800 mb-3">Acciones Rápidas</h2>
                    <Link :href="route('admin.invitaciones.create')" class="flex items-center gap-3 py-2 rounded-lg hover:bg-gray-50">
                        <div class="rounded-lg bg-brand/10 text-brand flex items-center justify-center shrink-0" style="width:36px;height:36px">
                            <i class="pi pi-envelope text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800 leading-tight">Nueva Invitación</p>
                            <p class="text-xs text-gray-400">Invita usuarios a la plataforma</p>
                        </div>
                    </Link>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">Resumen de Invitaciones</h2>
                    <div v-if="stats.enviadas" class="relative mx-auto" style="height: 180px; width: 180px">
                        <Doughnut :data="doughnutData" :options="{ maintainAspectRatio: false, plugins: { legend: { display: false } }, cutout: '70%' }" />
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <p class="text-xs text-gray-400">Total</p>
                            <p class="font-semibold text-gray-800">{{ stats.enviadas }}</p>
                        </div>
                    </div>
                    <p v-else class="text-gray-400 text-sm text-center py-10">Aún no hay invitaciones.</p>

                    <ul class="mt-4 space-y-1.5 text-xs">
                        <li class="flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-gray-600"><span class="w-2 h-2 rounded-full bg-green-500"></span> Aceptadas</span>
                            <span class="text-gray-800 font-medium">{{ stats.aceptadas }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-gray-600"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Pendientes</span>
                            <span class="text-gray-800 font-medium">{{ stats.pendientes }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-gray-600"><span class="w-2 h-2 rounded-full bg-red-500"></span> Expiradas</span>
                            <span class="text-gray-800 font-medium">{{ stats.expiradas }}</span>
                        </li>
                    </ul>

                    <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-sm text-gray-500">Tasa de Aceptación</span>
                        <span class="font-semibold text-brand">{{ stats.tasaAceptacion }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>