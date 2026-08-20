<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KpiCard from '@/Components/KpiCard.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
    stats: Object,
    productos: Object,
    filtros: Object,
    categorias: Array,
    porCategoria: Array,
});

const { confirm } = useConfirm();
const toast = useToast();
const { money } = useFormatters();

const q = ref(props.filtros.q || '');
const categoria = ref(props.filtros.categoria || '');
const estado = ref(props.filtros.estado || '');

let timeout = null;
watch([q, categoria, estado], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(route('admin.productos.index'), {
            q: q.value || undefined,
            categoria: categoria.value || undefined,
            estado: estado.value || undefined,
        }, { preserveState: true, replace: true });
    }, 350);
});

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
    <Head title="Productos" />

    <AdminLayout>
        <template #title>Productos</template>
        <template #breadcrumb>Dashboard &gt; Productos</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- KPIs -->
            <div class="admin-kpi-grid gap-6 mb-6 w-full">
                <div class="min-w-0"><KpiCard label="Productos Totales" :value="stats.total" icon="pi-tags" /></div>
                <div class="min-w-0"><KpiCard label="Activos" :value="stats.activos" icon="pi-check-circle" :hint="`${stats.total ? Math.round((stats.activos / stats.total) * 100) : 0}% del total`" hint-color="text-gray-400" /></div>
                <div class="min-w-0"><KpiCard label="Sin Stock" :value="stats.sinStock" icon="pi-exclamation-triangle" hint-color="text-red-500" /></div>
                <div class="min-w-0"><KpiCard :value="money(stats.valorInventario)" label="Valor de Inventario" icon="pi-wallet" /></div>
            </div>

            <div class="admin-productos-grid gap-6 w-full">

                <!-- Tabla de Productos -->
                <div class="min-w-0 admin-card overflow-hidden flex flex-col justify-between" style="grid-area:tabla">
                    <div class="flex flex-col flex-1">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-tags text-brand"></i> Gestión de Productos</span>
                            <Link :href="route('admin.productos.create')" class="admin-btn-primary flex-none" style="padding:0.4rem 0.85rem;font-size:0.75rem">
                                <i class="pi pi-plus text-xs"></i> Nuevo Producto
                            </Link>
                        </div>
                        <p class="text-xs px-6 pt-4" style="color:var(--muted)">Administra el catálogo de la tienda.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 px-6 py-4">
                            <div class="sm:col-span-5 relative">
                                <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input v-model="q" type="text" placeholder="Buscar por nombre o SKU..." class="admin-input pl-10 py-2" />
                            </div>
                            <div class="sm:col-span-4">
                                <select v-model="categoria" class="admin-input py-2">
                                    <option value="">Todas las categorías</option>
                                    <option v-for="c in categorias" :key="c" :value="c">{{ c }}</option>
                                </select>
                            </div>
                            <div class="sm:col-span-3">
                                <select v-model="estado" class="admin-input py-2">
                                    <option value="">Todos los estados</option>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="sin_stock">Sin stock</option>
                                </select>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm min-w-[700px]">
                                <thead>
                                    <tr class="border-y text-xs uppercase tracking-wider" style="border-color:var(--line);background:var(--surface);color:var(--muted)">
                                        <th class="pl-6 pr-4 py-3 font-semibold">Producto</th>
                                        <th class="px-3 py-3 font-semibold">Categoría</th>
                                        <th class="px-3 py-3 font-semibold">Precio</th>
                                        <th class="px-3 py-3 font-semibold">Stock</th>
                                        <th class="px-3 py-3 font-semibold">Estado</th>
                                        <th class="pl-2 pr-6 py-3 text-center font-semibold">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="p in productos.data" :key="p.id" class="hover:bg-gray-50/50 transition">
                                        <td class="pl-6 pr-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="rounded-lg bg-gray-100 flex items-center justify-center text-gray-300 shrink-0 overflow-hidden" style="width:38px;height:38px">
                                                    <img v-if="p.imagen" :src="p.imagen" class="w-full h-full object-cover" />
                                                    <i v-else class="pi pi-image text-sm"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-gray-800 text-sm truncate">{{ p.nombre }}</p>
                                                    <p class="text-[11px] text-gray-400">{{ p.sku }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-gray-600 text-xs whitespace-nowrap">{{ p.categoria }}</td>
                                        <td class="px-3 py-3 text-gray-800 text-xs font-semibold whitespace-nowrap">{{ money(p.precio) }}</td>
                                        <td class="px-3 py-3 text-xs whitespace-nowrap" :class="p.stock <= 0 ? 'text-red-600 font-semibold' : 'text-gray-600'">
                                            {{ p.stock <= 0 ? 'Sin stock' : p.stock }}
                                        </td>
                                        <td class="px-3 py-3 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold" :class="p.esta_activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                                                {{ p.esta_activo ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td class="pl-2 pr-6 py-3 whitespace-nowrap">
                                            <div class="flex justify-center items-center gap-1.5">
                                                <Link :href="route('admin.productos.show', p.id)" title="Ver" class="admin-table-action text-gray-600">
                                                    <i class="pi pi-eye text-xs"></i>
                                                </Link>
                                                <Link :href="route('admin.productos.edit', p.id)" title="Editar" class="admin-table-action text-gray-600">
                                                    <i class="pi pi-pencil text-xs"></i>
                                                </Link>
                                                <button @click="eliminarProducto(p)" title="Eliminar" class="admin-table-action text-red-600 hover:bg-red-50">
                                                    <i class="pi pi-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!productos.data.length">
                                        <td colspan="6" class="py-8 text-center text-gray-400 text-xs">No se encontraron productos.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-if="productos.last_page > 1" class="border-t border-gray-100 px-6 py-4">
                        <Pagination :data="productos" />
                    </div>
                    <div v-else class="border-t border-gray-100 py-3.5 text-center">
                        <Link :href="route('admin.productos.todos')" class="text-brand font-medium hover:underline text-xs">
                            Ver todos los productos
                        </Link>
                    </div>
                </div>

                <!-- Por Categoría -->
                <div class="min-w-0 admin-card overflow-hidden" style="grid-area:categorias">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-th-large text-brand"></i> Por Categoría</span>
                    </div>
                    <ul class="space-y-3 p-6">
                        <li v-for="c in porCategoria" :key="c.categoria" class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">{{ c.categoria }}</span>
                            <span class="font-semibold text-gray-800">{{ c.cantidad }}</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>