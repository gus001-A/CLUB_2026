<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    codigos: Object,
    filtros: Object,
});

const toast = useToast();

const q = ref(props.filtros.q || '');
const tipo = ref(props.filtros.tipo || '');
const estado = ref(props.filtros.estado || '');

let timeout = null;
watch([q, tipo, estado], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.invitaciones.codigos'), {
            q: q.value || undefined,
            tipo: tipo.value || undefined,
            estado: estado.value || undefined,
        }, { preserveState: true, replace: true });
    }, 350);
});

function formatDate(v) {
    if (!v) return '—';
    return new Date(v).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
}

function copiar(codigo) {
    navigator.clipboard.writeText(codigo);
    toast.success(`Código ${codigo} copiado.`);
}

const tipoNombres = { registro: 'Registro', premium: 'Premium', evento: 'Evento' };
const estadoColores = {
    aceptada: 'bg-green-100 text-green-700',
    pendiente: 'bg-amber-100 text-amber-700',
    expirada: 'bg-red-100 text-red-700',
    utilizada: 'bg-blue-100 text-blue-700',
};
const estadoLabel = { aceptada: 'Aceptado', pendiente: 'Pendiente', expirada: 'Expirado', utilizada: 'Utilizado' };
</script>

<template>
    <Head title="Códigos generados" />

    <AdminLayout>
        <template #title>Códigos generados</template>
        <template #breadcrumb>Dashboard &gt; Invitaciones &gt; Códigos generados</template>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-800">Todos los códigos generados</h2>
                <span class="text-sm text-gray-400">{{ codigos.total }} en total</span>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 mb-4">
                <div class="relative flex-1 min-w-[180px]">
                    <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-sm pointer-events-none"></i>
                    <input v-model="q" type="text" placeholder="Buscar por código, nombre o correo..." class="w-full pl-10 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                </div>
                <select v-model="tipo" class="rounded-lg border border-gray-300 text-sm px-3 py-2 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                    <option value="">Todos los tipos</option>
                    <option value="registro">Registro</option>
                    <option value="premium">Premium</option>
                    <option value="evento">Evento</option>
                </select>
                <select v-model="estado" class="rounded-lg border border-gray-300 text-sm px-3 py-2 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                    <option value="">Todos los estados</option>
                    <option value="aceptada">Aceptado</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="expirada">Expirado</option>
                    <option value="utilizada">Utilizado</option>
                </select>
            </div>

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-400 border-b border-gray-100">
                        <th class="pb-2 font-medium">Código</th>
                        <th class="pb-2 font-medium">Destinatario</th>
                        <th class="pb-2 font-medium">Tipo</th>
                        <th class="pb-2 font-medium">Usos</th>
                        <th class="pb-2 font-medium">Creado</th>
                        <th class="pb-2 font-medium">Expira</th>
                        <th class="pb-2 font-medium">Estado</th>
                        <th class="pb-2 font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="c in codigos.data" :key="c.id" class="border-b border-gray-50 last:border-0 hover:bg-gray-50/70">
                        <td class="py-2.5 font-mono text-gray-700">{{ c.codigo }}</td>
                        <td class="py-2.5">
                            <p class="font-medium text-gray-800">{{ c.nombre_destinatario ?? '—' }}</p>
                            <p class="text-gray-400 text-xs">{{ c.email ?? '—' }}</p>
                        </td>
                        <td class="py-2.5 text-gray-500">{{ tipoNombres[c.tipo] ?? c.tipo }}</td>
                        <td class="py-2.5 text-gray-500">{{ c.usos }} / {{ c.usos_maximos }}</td>
                        <td class="py-2.5 text-gray-400">{{ formatDate(c.created_at) }}</td>
                        <td class="py-2.5 text-gray-400">{{ formatDate(c.expira_en) }}</td>
                        <td class="py-2.5">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="estadoColores[c.estado]">
                                {{ estadoLabel[c.estado] }}
                            </span>
                        </td>
                        <td class="py-2.5">
                            <i class="pi pi-copy cursor-pointer text-gray-400 hover:text-gray-700" title="Copiar código" @click="copiar(c.codigo)"></i>
                        </td>
                    </tr>
                    <tr v-if="!codigos.data.length">
                        <td colspan="8" class="py-8 text-center text-gray-400">No se encontraron códigos.</td>
                    </tr>
                </tbody>
            </table>

            <!-- Paginación -->
            <div v-if="codigos.last_page > 1" class="flex items-center justify-between mt-5 text-sm">
                <p class="text-gray-400">Mostrando {{ codigos.from }}–{{ codigos.to }} de {{ codigos.total }}</p>
                <div class="flex items-center gap-1">
                    <template v-for="(link, i) in codigos.links" :key="i">
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
    </AdminLayout>
</template>