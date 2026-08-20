<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
    codigos: Object,
    filtros: Object,
});

const toast = useToast();
const { formatDate } = useFormatters();

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
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Invitaciones &gt; Códigos generados</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- Volver -->
            <div class="mb-6">
                <Link :href="route('admin.invitaciones.index')" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-brand transition">
                    <i class="pi pi-arrow-left text-xs"></i>
                    Volver a Invitaciones
                </Link>
            </div>

            <div class="admin-card overflow-hidden flex flex-col justify-between">
                <div class="flex flex-col flex-1">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-list text-brand"></i> Todos los códigos generados</span>
                    </div>
                    <p class="text-sm px-6 pt-4" style="color:var(--muted)">{{ codigos.total }} códigos en total</p>

                    <!-- Filtros -->
                    <div class="flex flex-wrap items-center gap-3 px-6 py-5">
                        <div class="relative flex-1 min-w-[180px]">
                            <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input v-model="q" type="text" placeholder="Buscar por código, nombre o correo..." class="admin-input pl-10 py-2.5">
                        </div>
                        <select v-model="tipo" class="admin-input w-auto py-2.5">
                            <option value="">Todos los tipos</option>
                            <option value="registro">Registro</option>
                            <option value="premium">Premium</option>
                            <option value="evento">Evento</option>
                        </select>
                        <select v-model="estado" class="admin-input w-auto py-2.5">
                            <option value="">Todos los estados</option>
                            <option value="aceptada">Aceptado</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="expirada">Expirado</option>
                            <option value="utilizada">Utilizado</option>
                        </select>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto flex-1 flex flex-col">
                        <table class="min-w-full text-sm flex-1">
                            <thead class="bg-gray-50 border-y border-gray-200">
                                <tr class="text-gray-600 uppercase tracking-wide text-xs">
                                    <th class="px-6 py-4 text-left">Código</th>
                                    <th class="px-4 py-4 text-left">Destinatario</th>
                                    <th class="px-4 py-4 text-left">Tipo</th>
                                    <th class="px-4 py-4 text-left">Usos</th>
                                    <th class="px-4 py-4 text-left">Creado</th>
                                    <th class="px-4 py-4 text-left">Expira</th>
                                    <th class="px-4 py-4 text-left">Estado</th>
                                    <th class="px-6 py-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="c in codigos.data" :key="c.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-mono text-gray-700 whitespace-nowrap">{{ c.codigo }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <p class="font-semibold text-gray-800">{{ c.nombre_destinatario ?? '—' }}</p>
                                        <p class="text-xs text-gray-400">{{ c.email ?? '—' }}</p>
                                    </td>
                                    <td class="px-4 py-4 text-gray-600 text-xs whitespace-nowrap">{{ tipoNombres[c.tipo] ?? c.tipo }}</td>
                                    <td class="px-4 py-4 text-gray-600 text-xs whitespace-nowrap">{{ c.usos }} / {{ c.usos_maximos }}</td>
                                    <td class="px-4 py-4 text-gray-500 text-xs whitespace-nowrap">{{ formatDate(c.created_at) }}</td>
                                    <td class="px-4 py-4 text-gray-500 text-xs whitespace-nowrap">{{ formatDate(c.expira_en) }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold" :class="estadoColores[c.estado]">
                                            {{ estadoLabel[c.estado] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center">
                                            <button @click="copiar(c.codigo)" title="Copiar código" class="admin-table-action text-gray-600">
                                                <i class="pi pi-copy"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!codigos.data.length">
                                    <td colspan="8" class="text-center text-gray-400 py-12">No se encontraron códigos.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="codigos.last_page > 1" class="border-t border-gray-200 px-6 py-4">
                    <Pagination :data="codigos" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>