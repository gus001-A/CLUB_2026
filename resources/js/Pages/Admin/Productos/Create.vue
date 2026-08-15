<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    categorias: Array,
});

const toast = useToast();

const form = useForm({
    sku: '',
    nombre: '',
    descripcion: '',
    categoria: props.categorias[0] || '',
    marca: '',
    precio: '',
    stock: 0,
    esta_activo: true,
    etiquetas: [],
    variantes: {},
    imagenes: [],
});

// --- Etiquetas (chips) ---
const nuevaEtiqueta = ref('');
function agregarEtiqueta() {
    const val = nuevaEtiqueta.value.trim();
    if (val && !form.etiquetas.includes(val)) form.etiquetas.push(val);
    nuevaEtiqueta.value = '';
}
function quitarEtiqueta(i) {
    form.etiquetas.splice(i, 1);
}

// --- Variantes (filas nombre + valores separados por coma) ---
const filasVariantes = ref([]);
function agregarFilaVariante() {
    filasVariantes.value.push({ nombre: '', valores: '' });
}
function quitarFilaVariante(i) {
    filasVariantes.value.splice(i, 1);
}
function construirVariantes() {
    const obj = {};
    for (const fila of filasVariantes.value) {
        const nombre = fila.nombre.trim();
        const valores = fila.valores.split(',').map((v) => v.trim()).filter(Boolean);
        if (nombre && valores.length) obj[nombre] = valores;
    }
    return obj;
}

// --- Imágenes (previews) ---
const previews = ref([]);
function onImagenesChange(e) {
    const archivos = Array.from(e.target.files || []);
    form.imagenes = [...form.imagenes, ...archivos];
    previews.value = [...previews.value, ...archivos.map((f) => URL.createObjectURL(f))];
    e.target.value = '';
}
function quitarImagen(i) {
    form.imagenes.splice(i, 1);
    previews.value.splice(i, 1);
}

function guardar() {
    form.transform((data) => ({ ...data, variantes: construirVariantes() }))
        .post(route('admin.productos.store'), {
            forceFormData: true,
            onSuccess: () => toast.success(`Producto "${form.nombre}" creado correctamente.`),
            onError: () => toast.error('Revisa los campos marcados en rojo.'),
        });
}
</script>

<template>
    <Head title="Nuevo Producto" />

    <AdminLayout>
        <template #title>Nuevo Producto</template>
        <template #breadcrumb>Dashboard &gt; Productos &gt; Nuevo</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <Link :href="route('admin.productos.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand mb-4">
                <i class="pi pi-arrow-left text-xs"></i> Volver a Productos
            </Link>

            <form @submit.prevent="guardar" class="admin-producto-show-grid gap-6 w-full">

                <!-- Columna izquierda: datos del producto -->
                <div class="min-w-0 flex flex-col gap-6" style="grid-area:izquierda">

                    <div class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-info-circle text-brand"></i> Información básica</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-6">
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold text-gray-600">Nombre</label>
                                <input v-model="form.nombre" type="text" class="admin-input mt-1" placeholder="Ej. Aceite de masaje relajante" />
                                <p v-if="form.errors.nombre" class="text-xs text-red-500 mt-1">{{ form.errors.nombre }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600">SKU</label>
                                <input v-model="form.sku" type="text" class="admin-input mt-1" placeholder="Ej. ACE-001" />
                                <p v-if="form.errors.sku" class="text-xs text-red-500 mt-1">{{ form.errors.sku }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600">Marca</label>
                                <input v-model="form.marca" type="text" class="admin-input mt-1" placeholder="Opcional" />
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600">Categoría</label>
                                <select v-model="form.categoria" class="admin-input mt-1">
                                    <option v-for="c in categorias" :key="c" :value="c">{{ c }}</option>
                                </select>
                                <p v-if="form.errors.categoria" class="text-xs text-red-500 mt-1">{{ form.errors.categoria }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600">Precio (MXN)</label>
                                <input v-model="form.precio" type="number" min="0" step="0.01" class="admin-input mt-1" placeholder="0.00" />
                                <p v-if="form.errors.precio" class="text-xs text-red-500 mt-1">{{ form.errors.precio }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-600">Stock</label>
                                <input v-model="form.stock" type="number" min="0" class="admin-input mt-1" />
                                <p v-if="form.errors.stock" class="text-xs text-red-500 mt-1">{{ form.errors.stock }}</p>
                            </div>
                            <div class="flex items-center gap-2 pt-6">
                                <input id="esta_activo" v-model="form.esta_activo" type="checkbox" class="w-4 h-4 rounded text-brand focus:ring-brand" />
                                <label for="esta_activo" class="text-sm text-gray-700">Producto activo (visible en la tienda)</label>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold text-gray-600">Descripción</label>
                                <textarea v-model="form.descripcion" rows="4" class="admin-input mt-1 resize-none" placeholder="Describe el producto..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Etiquetas -->
                    <div class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-hashtag text-brand"></i> Etiquetas</span>
                        </div>
                        <div class="p-6">
                            <div class="flex gap-2">
                                <input v-model="nuevaEtiqueta" type="text" class="admin-input" placeholder="Escribe y presiona Enter..." @keydown.enter.prevent="agregarEtiqueta" />
                                <button type="button" @click="agregarEtiqueta" class="admin-btn-secondary flex-none" style="padding:0.5rem 1rem">Agregar</button>
                            </div>
                            <div v-if="form.etiquetas.length" class="flex flex-wrap gap-2 mt-3">
                                <span v-for="(et, i) in form.etiquetas" :key="i" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-brand/10 text-brand">
                                    {{ et }}
                                    <button type="button" @click="quitarEtiqueta(i)"><i class="pi pi-times text-[10px]"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Variantes -->
                    <div class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-sliders-h text-brand"></i> Variantes</span>
                        </div>
                        <div class="p-6 space-y-3">
                            <p class="text-xs" style="color:var(--muted)">Opcional. Ej. "Talla" con valores "S, M, L".</p>
                            <div v-for="(fila, i) in filasVariantes" :key="i" class="flex gap-2 items-start">
                                <input v-model="fila.nombre" type="text" class="admin-input" style="flex:1" placeholder="Nombre (ej. Talla)" />
                                <input v-model="fila.valores" type="text" class="admin-input" style="flex:2" placeholder="Valores separados por coma" />
                                <button type="button" @click="quitarFilaVariante(i)" class="admin-table-action text-red-600 flex-none"><i class="pi pi-trash text-xs"></i></button>
                            </div>
                            <button type="button" @click="agregarFilaVariante" class="text-xs font-semibold text-brand hover:underline">
                                <i class="pi pi-plus text-[10px]"></i> Agregar variante
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: imágenes + guardar -->
                <div class="min-w-0 flex flex-col gap-6" style="grid-area:derecha">

                    <div class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-images text-brand"></i> Imágenes</span>
                        </div>
                        <div class="p-6">
                            <label class="flex flex-col items-center justify-center gap-2 border-2 border-dashed rounded-xl py-6 cursor-pointer hover:bg-gray-50 transition" style="border-color:var(--line)">
                                <i class="pi pi-cloud-upload text-2xl text-gray-400"></i>
                                <span class="text-xs text-gray-500">Haz clic para subir imágenes</span>
                                <input type="file" accept="image/*" multiple class="hidden" @change="onImagenesChange" />
                            </label>
                            <p v-if="form.errors.imagenes" class="text-xs text-red-500 mt-2">{{ form.errors.imagenes }}</p>

                            <div v-if="previews.length" class="grid grid-cols-3 gap-2 mt-4">
                                <div v-for="(src, i) in previews" :key="i" class="relative aspect-square rounded-lg overflow-hidden bg-gray-100 group">
                                    <img :src="src" class="w-full h-full object-cover" />
                                    <button type="button" @click="quitarImagen(i)" class="absolute top-1 right-1 w-5 h-5 rounded-full bg-black/60 text-white flex items-center justify-center text-[10px]">
                                        <i class="pi pi-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-card overflow-hidden">
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-bolt text-brand"></i> Acciones</span>
                        </div>
                        <div class="flex flex-col gap-2.5 p-6">
                            <button type="submit" :disabled="form.processing" class="admin-btn-primary justify-center">
                                <i class="pi pi-check text-xs"></i> Guardar producto
                            </button>
                            <Link :href="route('admin.productos.index')" class="admin-btn-secondary text-center">Cancelar</Link>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </AdminLayout>
</template>