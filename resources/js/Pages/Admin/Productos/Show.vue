<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useConfirm } from '@/composables/useConfirm';
import { useToast } from '@/composables/useToast';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
    producto: Object,
});

const { confirm } = useConfirm();
const toast = useToast();
const { money } = useFormatters();

async function eliminar() {
    const ok = await confirm(`Esto eliminará "${props.producto.nombre}" permanentemente.`, {
        title: 'Eliminar producto',
        confirmLabel: 'Sí, eliminar',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('admin.productos.destroy', props.producto.id), {
        onSuccess: () => toast.success(`Producto "${props.producto.nombre}" eliminado correctamente.`),
        onError: () => toast.error('No se pudo eliminar el producto.'),
    });
}
</script>

<template>
    <Head :title="producto.nombre" />

    <AdminLayout>
        <template #title>{{ producto.nombre }}</template>
        <template #breadcrumb>Dashboard &gt; Productos &gt; {{ producto.nombre }}</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <Link :href="route('admin.productos.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand mb-4">
                <i class="pi pi-arrow-left text-xs"></i> Volver a Productos
            </Link>

            <!-- Header compacto -->
            <div class="admin-card overflow-hidden mb-6">
                <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="rounded-2xl bg-brand/10 text-brand flex items-center justify-center shrink-0 overflow-hidden" style="width:64px;height:64px">
                            <img v-if="producto.imagenes?.[0]" :src="producto.imagenes[0]" class="w-full h-full object-cover" />
                            <i v-else class="pi pi-tags" style="font-size:1.5rem"></i>
                        </div>
                        <div class="min-w-0">
                            <h1 class="text-xl font-bold text-gray-900">{{ producto.nombre }}</h1>
                            <p class="text-sm text-gray-400 mt-0.5">{{ producto.sku }} · {{ producto.categoria }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-3 py-1.5 rounded-lg text-xs font-semibold" :class="producto.esta_activo ? 'bg-green-50 text-green-600 border border-green-200' : 'bg-gray-50 text-gray-500 border border-gray-200'">
                            {{ producto.esta_activo ? 'Activo' : 'Inactivo' }}
                        </span>
                        <span v-if="producto.stock <= 0" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 border border-red-200">
                            Sin stock
                        </span>
                    </div>
                </div>

                <!-- Datos rápidos -->
                <div class="border-t border-gray-100 admin-evento-detalles-grid p-6">
                    <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                        <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-ticket text-sm"></i></div>
                        <div>
                            <p class="text-[11px] text-gray-400 uppercase font-medium">Precio</p>
                            <p class="text-sm font-semibold text-gray-800">{{ money(producto.precio) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                        <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-box text-sm"></i></div>
                        <div>
                            <p class="text-[11px] text-gray-400 uppercase font-medium">Stock</p>
                            <p class="text-sm font-semibold text-gray-800">{{ producto.stock }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                        <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-tag text-sm"></i></div>
                        <div>
                            <p class="text-[11px] text-gray-400 uppercase font-medium">Marca</p>
                            <p class="text-sm font-semibold text-gray-800">{{ producto.marca || '—' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                        <div class="admin-icon-circle" style="width:40px;height:40px"><i class="pi pi-star text-sm"></i></div>
                        <div>
                            <p class="text-[11px] text-gray-400 uppercase font-medium">Calificación</p>
                            <p class="text-sm font-semibold text-gray-800">{{ producto.calificacion ? `${producto.calificacion} · ${producto.calificacion_texto}` : 'Sin calificar' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-producto-show-grid gap-6 w-full">

                <!-- Columna izquierda -->
                <div class="min-w-0 flex flex-col gap-6" style="grid-area:izquierda">

                    <div v-if="producto.descripcion" class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-align-left text-brand"></i> Descripción</span>
                        </div>
                        <p class="text-sm text-gray-600 leading-relaxed p-6">{{ producto.descripcion }}</p>
                    </div>

                    <div v-if="producto.imagenes?.length" class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-images text-brand"></i> Imágenes ({{ producto.imagenes.length }})</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 p-6">
                            <div v-for="(img, i) in producto.imagenes" :key="i" class="aspect-square rounded-xl overflow-hidden bg-gray-100">
                                <img :src="img" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>

                    <div v-if="producto.etiquetas?.length" class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-hashtag text-brand"></i> Etiquetas</span>
                        </div>
                        <div class="flex flex-wrap gap-2 p-6">
                            <span v-for="et in producto.etiquetas" :key="et" class="px-2.5 py-1 rounded-full text-xs font-medium bg-brand/10 text-brand">{{ et }}</span>
                        </div>
                    </div>

                    <div v-if="producto.variantes && Object.keys(producto.variantes).length" class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-sliders-h text-brand"></i> Variantes</span>
                        </div>
                        <ul class="space-y-2 p-6 text-sm">
                            <li v-for="(valores, nombre) in producto.variantes" :key="nombre">
                                <span class="font-semibold text-gray-700">{{ nombre }}:</span>
                                <span class="text-gray-500"> {{ Array.isArray(valores) ? valores.join(', ') : valores }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Columna derecha -->
                <div class="min-w-0 h-full flex flex-col gap-6" style="grid-area:derecha">
                    <div class="admin-card overflow-hidden flex-1 flex flex-col">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-bolt text-brand"></i> Acciones</span>
                        </div>
                        <div class="flex-1 flex flex-col justify-center gap-2.5 p-6">
                            <Link :href="route('admin.productos.edit', producto.id)" class="admin-btn-primary">
                                <i class="pi pi-pencil text-xs"></i> Editar producto
                            </Link>
                            <button @click="eliminar" class="border border-red-200 text-red-600 hover:bg-red-50 font-medium px-4 py-2.5 rounded-xl text-sm flex items-center justify-center gap-2">
                                <i class="pi pi-trash text-xs"></i> Eliminar producto
                            </button>
                            <Link :href="route('admin.productos.index')" class="admin-btn-secondary text-center">
                                Volver al listado
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>