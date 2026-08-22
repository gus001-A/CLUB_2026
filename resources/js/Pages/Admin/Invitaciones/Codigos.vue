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
const badgeEstado = { aceptada: 'admin-invit-badge--aceptada', pendiente: 'admin-invit-badge--pendiente', expirada: 'admin-invit-badge--expirada', utilizada: 'admin-invit-badge--utilizada' };
const estadoLabel = { aceptada: 'Aceptado', pendiente: 'Pendiente', expirada: 'Expirado', utilizada: 'Utilizado' };
</script>

<template>
    <Head title="Códigos generados" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Invitaciones &gt; Códigos generados</template>

        <div class="admin-invit-page">
            <Link :href="route('admin.invitaciones.index')" class="admin-user-back-link">
                <i class="pi pi-arrow-left"></i>
                Volver a Invitaciones
            </Link>

            <div class="admin-invit-card">
                <div class="flex flex-col flex-1">
                    <div class="admin-invit-card__header">
                        <div class="admin-invit-card__header-left">
                            <div class="admin-invit-header-icon"><i class="pi pi-list"></i></div>
                            <div>
                                <h3>Todos los códigos generados</h3>
                                <p class="admin-invit-header-subtitle">{{ codigos.total }} códigos en total</p>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="admin-cobros-filters">
                        <div class="admin-cobros-filters__search">
                            <i class="pi pi-search"></i>
                            <input v-model="q" type="text" placeholder="Buscar por código, nombre o correo..." />
                        </div>
                        <select v-model="tipo">
                            <option value="">Todos los tipos</option>
                            <option value="registro">Registro</option>
                            <option value="premium">Premium</option>
                            <option value="evento">Evento</option>
                        </select>
                        <select v-model="estado">
                            <option value="">Todos los estados</option>
                            <option value="aceptada">Aceptado</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="expirada">Expirado</option>
                            <option value="utilizada">Utilizado</option>
                        </select>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto">
                        <table class="admin-invit-table min-w-full">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Destinatario</th>
                                    <th>Tipo</th>
                                    <th>Usos</th>
                                    <th>Creado</th>
                                    <th>Expira</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="c in codigos.data" :key="c.id">
                                    <td class="admin-invit-code whitespace-nowrap">{{ c.codigo }}</td>
                                    <td class="whitespace-nowrap">
                                        <p class="admin-invit-name">{{ c.nombre_destinatario ?? '—' }}</p>
                                        <p class="admin-invit-code">{{ c.email ?? '—' }}</p>
                                    </td>
                                    <td class="text-gray-600 text-xs whitespace-nowrap">{{ tipoNombres[c.tipo] ?? c.tipo }}</td>
                                    <td class="text-gray-600 text-xs whitespace-nowrap">{{ c.usos }} / {{ c.usos_maximos }}</td>
                                    <td class="text-gray-500 text-xs whitespace-nowrap">{{ formatDate(c.created_at) }}</td>
                                    <td class="text-gray-500 text-xs whitespace-nowrap">{{ formatDate(c.expira_en) }}</td>
                                    <td class="whitespace-nowrap">
                                        <span class="admin-invit-badge" :class="badgeEstado[c.estado]">
                                            <span class="admin-invit-badge-dot"></span>{{ estadoLabel[c.estado] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex justify-center">
                                            <button @click="copiar(c.codigo)" title="Copiar código" class="admin-invit-action-btn admin-invit-action-btn--copy">
                                                <i class="pi pi-copy"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!codigos.data.length">
                                    <td colspan="8" class="admin-invit-empty">No se encontraron códigos.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-if="codigos.last_page > 1" class="admin-invit-table-footer">
                    <Pagination :data="codigos" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>