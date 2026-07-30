<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps({
    stats: Object,
    reportes: Object,
    filtros: Object,
    porTipo: Object,
});

const toast = useToast();
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

        <!-- KPIs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Reportes Totales</p>
                    <p class="text-2xl font-semibold text-gray-800 mt-1">{{ stats.total }}</p>
                </div>
                <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0" style="width:44px;height:44px">
                    <i class="pi pi-flag text-lg"></i>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Pendientes</p>
                    <p class="text-2xl font-semibold text-gray-800 mt-1">{{ stats.pendientes }}</p>
                </div>
                <div class="rounded-full bg-amber-50 text-amber-600 flex items-center justify-center shrink-0" style="width:44px;height:44px">
                    <i class="pi pi-clock text-lg"></i>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Revisados</p>
                    <p class="text-2xl font-semibold text-gray-800 mt-1">{{ stats.revisados }}</p>
                </div>
                <div class="rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0" style="width:44px;height:44px">
                    <i class="pi pi-eye text-lg"></i>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Resueltos</p>
                    <p class="text-2xl font-semibold text-gray-800 mt-1">{{ stats.resueltos }}</p>
                </div>
                <div class="rounded-full bg-green-50 text-green-600 flex items-center justify-center shrink-0" style="width:44px;height:44px">
                    <i class="pi pi-check-circle text-lg"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Cola de reportes -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="font-semibold text-gray-800 mb-4">Cola de Moderación</h2>

                <div class="flex flex-col sm:flex-row gap-3 mb-4">
                    <div class="relative flex-1 min-w-[160px]">
                        <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-sm pointer-events-none"></i>
                        <input v-model="q" type="text" placeholder="Buscar por usuario..." class="w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                    </div>
                    <select v-model="tipo" class="rounded-lg border border-gray-300 text-sm px-3 py-2 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                        <option value="">Todos los tipos</option>
                        <option value="spam">Spam</option>
                        <option value="inapropiado">Contenido inapropiado</option>
                        <option value="falso">Perfil falso</option>
                        <option value="acoso">Acoso</option>
                        <option value="otro">Otro</option>
                    </select>
                    <select v-model="estado" class="rounded-lg border border-gray-300 text-sm px-3 py-2 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="revisado">Revisado</option>
                        <option value="resuelto">Resuelto</option>
                    </select>
                </div>

                <ul class="space-y-3">
                    <li v-for="r in reportes.data" :key="r.id" class="border border-gray-100 rounded-lg p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-1.5">
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
                <div v-if="reportes.last_page > 1" class="flex items-center justify-between mt-5 text-sm">
                    <p class="text-gray-400">Mostrando {{ reportes.from }}–{{ reportes.to }} de {{ reportes.total }}</p>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in reportes.links" :key="i">
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

            <!-- Resumen por tipo -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 h-fit">
                <h2 class="font-semibold text-gray-800 mb-4">Reportes por Tipo</h2>
                <ul class="space-y-3">
                    <li v-for="(cantidad, key) in porTipo" :key="key" class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">{{ tipoLabel[key] }}</span>
                        <span class="font-medium text-gray-800">{{ cantidad }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </AdminLayout>
</template>