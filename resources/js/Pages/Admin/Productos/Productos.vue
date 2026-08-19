<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
    productos: Object,
    filtros: Object,
    categorias: Array,
    porCategoria: Array,
    porEstado: Array,
    totalGeneral: Number,
});

const { confirm } = useConfirm();
const toast = useToast();
const { money } = useFormatters();

const q = ref(props.filtros.q || '');
const categoria = ref(props.filtros.categoria || '');
const estado = ref(props.filtros.estado || '');

let timeout = null;
function aplicarFiltros() {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.productos.todos'), {
            q: q.value || undefined,
            categoria: categoria.value || undefined,
            estado: estado.value || undefined,
        }, { preserveState: true, preserveScroll: true, replace: true });
    }, 350);
}
watch([q, categoria, estado], aplicarFiltros);

const estadoDotColores = { activo: '#10B981', inactivo: '#9CA3AF', sin_stock: '#EF4444' };

async function eliminarProducto(p) {
    const ok = await confirm(`Esto eliminará "${p.nombre}" permanentemente.`, {
        title: 'Eliminar producto',
        confirmLabel: 'Sí, eliminar',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('admin.productos.destroy', p.id), {
        preserveScroll: true,
        onSuccess: () => toast.success(`Producto "${p.nombre}" eliminado correctamente.`),
        onError: () => toast.error('No se pudo eliminar el producto.'),
    });
}
</script>

<template>
    <Head title="Todos los Productos" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Productos &gt; Todos los productos</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- Volver -->
            <div class="mb-6">
                <Link :href="route('admin.productos.index')" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-brand transition">
                    <i class="pi pi-arrow-left text-xs"></i>
                    Volver a Productos
                </Link>
            </div>

            <!-- Encabezado + total general -->
            <div class="admin-card overflow-hidden mb-6">
                <div class="admin-card-header">
                    <span class="admin-card-header-title"><i class="pi pi-tags text-brand"></i> Todos los Productos</span>
                    <Link :href="route('admin.productos.create')" class="admin-btn-primary" style="padding:0.4rem 0.85rem;font-size:0.75rem">
                        <i class="pi pi-plus text-xs"></i> Nuevo Producto
                    </Link>
                </div>
                <p class="text-sm px-6 py-4" style="color:var(--muted)">{{ totalGeneral }} productos registrados en total</p>
            </div>

            <!-- Desglose por estado -->
            <div class="admin-estado-grid gap-4 mb-6 w-full">
                <button v-for="e in porEstado" :key="e.estado" type="button" @click="estado = (estado === e.estado ? '' : e.estado)"
                    class="min-w-0 admin-card px-5 py-4 text-left transition"
                    :class="estado === e.estado ? 'ring-2 ring-brand/40' : 'hover:border-gray-300'">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="rounded-full shrink-0" :style="{ backgroundColor: estadoDotColores[e.estado], width: '8px', height: '8px' }"></span>
                        <span class="text-xs text-gray-500">{{ e.label }}</span>
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ e.cantidad }}</p>
                </button>
            </div>

            <!-- Desglose por categoría -->
            <div class="admin-card overflow-hidden mb-6">
                <div class="admin-card-header">
                    <span class="admin-card-header-title"><i class="pi pi-chart-bar text-brand"></i> Desglose por categoría</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 p-6">
                    <div v-for="c in porCategoria" :key="c.categoria">
                        <p class="text-xs text-gray-400 mb-1">{{ c.label }}</p>
                        <p class="text-base font-bold text-gray-900">{{ c.cantidad }} productos</p>
                    </div>
                </div>
            </div>

            <!-- Tabla completa -->
            <div class="admin-card overflow-hidden flex flex-col justify-between">
                <div class="flex flex-col flex-1">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-list text-brand"></i> Catálogo completo</span>
                    </div>

                    <!-- Filtros -->
                    <div class="flex flex-wrap items-center gap-3 px-6 py-5">
                        <div class="relative flex-1 min-w-[180px]">
                            <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                            <input v-model="q" type="text" placeholder="Buscar por nombre o SKU..." class="admin-input pl-10 py-2.5">
                        </div>
                        <select v-model="categoria" class="admin-input w-auto py-2.5">
                            <option value="">Todas las categorías</option>
                            <option v-for="c in categorias" :key="c" :value="c">{{ c }}</option>
                        </select>
                        <select v-model="estado" class="admin-input w-auto py-2.5">
                            <option value="">Todos los estados</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="sin_stock">Sin stock</option>
                        </select>
                    </div>

                    <!-- Tabla -->
                    <div class="overflow-x-auto flex-1 flex flex-col">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 border-y border-gray-200">
                                <tr class="text-gray-600 uppercase tracking-wide text-xs">
                                    <th class="px-6 py-4 text-left">Producto</th>
                                    <th class="px-4 py-4 text-left">Categoría</th>
                                    <th class="px-4 py-4 text-left">Marca</th>
                                    <th class="px-4 py-4 text-left">Precio</th>
                                    <th class="px-4 py-4 text-left">Stock</th>
                                    <th class="px-4 py-4 text-left">Estado</th>
                                    <th class="px-6 py-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="p in productos.data" :key="p.id" class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="rounded-lg bg-gray-100 flex items-center justify-center text-gray-300 shrink-0 overflow-hidden" style="width:40px;height:40px">
                                                <img v-if="p.imagen" :src="p.imagen" class="w-full h-full object-cover" />
                                                <i v-else class="pi pi-image text-sm"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-900 truncate" style="max-width:180px" :title="p.nombre">{{ p.nombre }}</p>
                                                <p class="text-[11px] text-gray-400">{{ p.sku }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-gray-600 text-xs whitespace-nowrap">{{ p.categoria }}</td>
                                    <td class="px-4 py-4 text-gray-600 text-xs whitespace-nowrap">{{ p.marca || '—' }}</td>
                                    <td class="px-4 py-4 text-gray-800 text-xs font-semibold whitespace-nowrap">{{ money(p.precio) }}</td>
                                    <td class="px-4 py-4 text-xs whitespace-nowrap" :class="p.stock <= 0 ? 'text-red-600 font-semibold' : 'text-gray-600'">
                                        {{ p.stock <= 0 ? 'Sin stock' : p.stock }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold" :class="p.esta_activo ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-gray-50 text-gray-500 border border-gray-200'">
                                            {{ p.esta_activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center gap-2">
                                            <Link :href="route('admin.productos.show', p.id)" class="admin-table-action text-gray-600">
                                                <i class="pi pi-eye"></i>
                                            </Link>
                                            <Link :href="route('admin.productos.edit', p.id)" class="admin-table-action text-gray-600">
                                                <i class="pi pi-pencil"></i>
                                            </Link>
                                            <button @click="eliminarProducto(p)" class="admin-table-action text-red-600 hover:bg-red-50">
                                                <i class="pi pi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!productos.data?.length">
                                    <td colspan="7" class="text-center text-gray-400 py-12">No se encontraron productos.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="border-t border-gray-200 px-6 py-4">
                    <Pagination :data="productos" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>