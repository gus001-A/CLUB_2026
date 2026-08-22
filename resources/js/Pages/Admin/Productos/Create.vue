<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    categorias: Array,
});

const toast = useToast();

const form = useForm({
    sku: '',
    nombre: '',
    descripcion: '',
    categoria: props.categorias?.[0] || '',
    marca: '',
    precio: '',
    stock: 0,
    esta_activo: true,
    etiquetas: [],
    variantes: {},
    imagenes: [],
});

const descripcionMax = 500;

const precioFormateado = computed(() => {
    const valor = Number(form.precio);
    if (!form.precio || Number.isNaN(valor) || valor <= 0) return null;
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(valor);
});

const camposFaltantes = computed(() => {
    const faltan = [];
    if (!form.nombre.trim()) faltan.push('nombre');
    if (!form.sku.trim()) faltan.push('SKU');
    if (!form.categoria) faltan.push('categoría');
    if (form.precio === '' || form.precio === null || Number(form.precio) <= 0) faltan.push('precio');
    return faltan;
});
const formularioValido = computed(() => camposFaltantes.value.length === 0);

// --- Etiquetas ---
const nuevaEtiqueta = ref('');
function agregarEtiqueta() {
    const val = nuevaEtiqueta.value.trim();
    if (val && !form.etiquetas.includes(val)) form.etiquetas.push(val);
    nuevaEtiqueta.value = '';
}
function quitarEtiqueta(i) {
    form.etiquetas.splice(i, 1);
}

// --- Variantes ---
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

// --- Imágenes ---
const MAX_IMAGENES = 8;
const MAX_TAMANO_MB = 5;
const previews = ref([]);

function onImagenesChange(e) {
    const archivos = Array.from(e.target.files || []);
    const validos = [];

    for (const archivo of archivos) {
        if (!archivo.type.startsWith('image/')) {
            toast.error(`"${archivo.name}" no es una imagen válida.`);
            continue;
        }
        if (archivo.size > MAX_TAMANO_MB * 1024 * 1024) {
            toast.error(`"${archivo.name}" supera los ${MAX_TAMANO_MB}MB.`);
            continue;
        }
        validos.push(archivo);
    }

    const espacioDisponible = MAX_IMAGENES - form.imagenes.length;
    if (validos.length > espacioDisponible) {
        toast.error(`Máximo ${MAX_IMAGENES} imágenes por producto.`);
        validos.splice(Math.max(espacioDisponible, 0));
    }

    form.imagenes = [...form.imagenes, ...validos];
    previews.value = [...previews.value, ...validos.map((f) => URL.createObjectURL(f))];
    e.target.value = '';
}

function quitarImagen(i) {
    URL.revokeObjectURL(previews.value[i]);
    form.imagenes.splice(i, 1);
    previews.value.splice(i, 1);
}

function hacerPortada(i) {
    if (i === 0) return;
    const [imagen] = form.imagenes.splice(i, 1);
    form.imagenes.unshift(imagen);
    const [preview] = previews.value.splice(i, 1);
    previews.value.unshift(preview);
    toast.success('Imagen de portada actualizada.');
}

function guardar() {
    if (!formularioValido.value) {
        toast.error(`Faltan campos obligatorios: ${camposFaltantes.value.join(', ')}.`);
        return;
    }

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
        <template #breadcrumb>Dashboard / Productos / Nuevo</template>

        <div class="admin-prod-form-page">
            <!-- Botón volver -->
            <Link :href="route('admin.productos.index')" class="admin-prod-back-link">
                <i class="pi pi-arrow-left"></i>
                Volver a Productos
            </Link>

            <!-- ============================================================ -->
            <!-- GRID PRINCIPAL: FORMULARIO | ETIQUETAS + VARIANTES + IMÁGENES -->
            <!-- ============================================================ -->
            <div class="admin-prod-form-grid">
                <!-- COLUMNA IZQUIERDA: FORMULARIO PRINCIPAL -->
                <div class="admin-prod-form">
                    <!-- Cabecera -->
                    <div class="admin-prod-form-header">
                        <div class="admin-prod-form-header__icon">
                            <i class="pi pi-box"></i>
                        </div>
                        <div>
                            <h1>Nuevo Producto</h1>
                            <p>Ingresa los datos del producto</p>
                        </div>
                    </div>

                    <!-- Campos -->
                    <div class="admin-prod-form-body">
                        <!-- Nombre -->
                        <div class="admin-prod-field">
                            <label>Nombre del producto <span class="admin-prod-required">*</span></label>
                            <input v-model="form.nombre" type="text" placeholder="Ej. Aceite de masaje relajante"
                                :class="{ 'admin-prod-input-error': form.errors.nombre }" />
                            <p v-if="form.errors.nombre" class="admin-prod-error-text">{{ form.errors.nombre }}</p>
                        </div>

                        <!-- Descripción -->
                        <div class="admin-prod-field">
                            <div class="admin-prod-field-header">
                                <label>Descripción</label>
                                <span class="admin-prod-char-count">{{ form.descripcion.length }}/{{ descripcionMax }}</span>
                            </div>
                            <textarea v-model="form.descripcion" rows="3" :maxlength="descripcionMax"
                                placeholder="Describe el producto..."></textarea>
                            <p v-if="form.errors.descripcion" class="admin-prod-error-text">{{ form.errors.descripcion }}</p>
                        </div>

                        <!-- SKU, Categoría, Marca -->
                        <div class="admin-prod-field-row">
                            <div class="admin-prod-field">
                                <label>SKU <span class="admin-prod-required">*</span></label>
                                <input v-model="form.sku" type="text" placeholder="Ej. ACE-001"
                                    :class="{ 'admin-prod-input-error': form.errors.sku }" />
                                <p v-if="form.errors.sku" class="admin-prod-error-text">{{ form.errors.sku }}</p>
                            </div>
                            <div class="admin-prod-field">
                                <label>Categoría <span class="admin-prod-required">*</span></label>
                                <select v-model="form.categoria" :class="{ 'admin-prod-input-error': form.errors.categoria }">
                                    <option v-for="c in categorias" :key="c" :value="c">{{ c }}</option>
                                </select>
                                <p v-if="form.errors.categoria" class="admin-prod-error-text">{{ form.errors.categoria }}</p>
                            </div>
                            <div class="admin-prod-field">
                                <label>Marca <span class="admin-prod-optional">(opcional)</span></label>
                                <input v-model="form.marca" type="text" placeholder="Ej. L'Occitane" />
                            </div>
                        </div>

                        <!-- Precio y Stock -->
                        <div class="admin-prod-field-row">
                            <div class="admin-prod-field">
                                <label>Precio (MXN) <span class="admin-prod-required">*</span></label>
                                <div class="admin-prod-price-input">
                                    <span class="admin-prod-price-symbol">$</span>
                                    <input v-model="form.precio" type="number" min="0" step="0.01" placeholder="0.00"
                                        :class="{ 'admin-prod-input-error': form.errors.precio }" />
                                    <span v-if="precioFormateado" class="admin-prod-price-format">{{ precioFormateado }}</span>
                                </div>
                                <p v-if="form.errors.precio" class="admin-prod-error-text">{{ form.errors.precio }}</p>
                            </div>
                            <div class="admin-prod-field">
                                <label>Stock</label>
                                <input v-model="form.stock" type="number" min="0"
                                    :class="{ 'admin-prod-input-error': form.errors.stock }" />
                                <p v-if="form.errors.stock" class="admin-prod-error-text">{{ form.errors.stock }}</p>
                                <p v-else-if="Number(form.stock) === 0" class="admin-prod-stock-warning">
                                    <i class="pi pi-info-circle"></i> Producto agotado
                                </p>
                            </div>
                        </div>

                        <!-- Activo -->
                        <div class="admin-prod-field">
                            <label class="admin-prod-toggle-label">
                                <span class="admin-prod-toggle">
                                    <input v-model="form.esta_activo" type="checkbox" />
                                    <span class="admin-prod-toggle-slider"></span>
                                </span>
                                <span class="admin-prod-toggle-text">
                                    <i class="pi pi-eye"></i> Producto activo
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: ETIQUETAS + VARIANTES + IMÁGENES -->
                <div class="admin-prod-sidebar">
                    <!-- Etiquetas -->
                    <div class="admin-prod-sidebar-card">
                        <div class="admin-prod-sidebar-card__header">
                            <h3><i class="pi pi-hashtag"></i> Etiquetas</h3>
                        </div>
                        <div class="admin-prod-sidebar-card__body">
                            <div class="admin-prod-tag-input">
                                <input v-model="nuevaEtiqueta" type="text" placeholder="Escribe y presiona Enter..."
                                    @keydown.enter.prevent="agregarEtiqueta" />
                                <button type="button" @click="agregarEtiqueta">
                                    <i class="pi pi-plus"></i>
                                </button>
                            </div>
                            <div v-if="form.etiquetas.length" class="admin-prod-tags">
                                <span v-for="(et, i) in form.etiquetas" :key="i" class="admin-prod-tag">
                                    {{ et }}
                                    <button type="button" @click="quitarEtiqueta(i)">
                                        <i class="pi pi-times"></i>
                                    </button>
                                </span>
                            </div>
                            <p v-else class="admin-prod-hint">Ayudan a que el producto aparezca en más búsquedas</p>
                        </div>
                    </div>

                    <!-- Variantes -->
                    <div class="admin-prod-sidebar-card">
                        <div class="admin-prod-sidebar-card__header">
                            <h3><i class="pi pi-sliders-h"></i> Variantes</h3>
                        </div>
                        <div class="admin-prod-sidebar-card__body">
                            <p class="admin-prod-hint">Opcional. Ej. "Talla" con valores "S, M, L".</p>
                            <div class="admin-prod-variants">
                                <div v-for="(fila, i) in filasVariantes" :key="i" class="admin-prod-variant-row">
                                    <input v-model="fila.nombre" type="text" placeholder="Nombre" />
                                    <input v-model="fila.valores" type="text"
                                        placeholder="Valores (separados por coma)" />
                                    <button type="button" @click="quitarFilaVariante(i)">
                                        <i class="pi pi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" @click="agregarFilaVariante" class="admin-prod-add-variant">
                                <i class="pi pi-plus"></i> Agregar variante
                            </button>
                        </div>
                    </div>

                    <!-- Imágenes -->
                    <div class="admin-prod-sidebar-card">
                        <div class="admin-prod-sidebar-card__header">
                            <h3><i class="pi pi-images"></i> Imágenes</h3>
                            <span class="admin-prod-count">{{ previews.length }}/{{ MAX_IMAGENES }}</span>
                        </div>
                        <div class="admin-prod-sidebar-card__body">
                            <label v-if="previews.length < MAX_IMAGENES" class="admin-prod-upload-area">
                                <i class="pi pi-cloud-upload"></i>
                                <span>Subir imágenes</span>
                                <small>JPG o PNG, máx. {{ MAX_TAMANO_MB }}MB c/u</small>
                                <input type="file" accept="image/*" multiple @change="onImagenesChange" />
                            </label>
                            <p v-if="form.errors.imagenes" class="admin-prod-error-text">{{ form.errors.imagenes }}</p>

                            <div v-if="previews.length" class="admin-prod-image-grid">
                                <div v-for="(src, i) in previews" :key="i" class="admin-prod-image-item">
                                    <img :src="src" />
                                    <span v-if="i === 0" class="admin-prod-image-badge">Portada</span>
                                    <button v-else type="button" @click="hacerPortada(i)"
                                        class="admin-prod-image-btn admin-prod-image-btn--portada">
                                        Portada
                                    </button>
                                    <button type="button" @click="quitarImagen(i)" class="admin-prod-image-btn admin-prod-image-btn--delete">
                                        <i class="pi pi-times"></i>
                                    </button>
                                </div>
                            </div>
                            <p v-if="previews.length" class="admin-prod-hint">La primera imagen es la portada</p>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="admin-prod-action-card">
                        <button type="submit" :disabled="form.processing" class="admin-prod-btn-save" @click="guardar">
                            <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-save'"></i>
                            {{ form.processing ? 'Guardando...' : 'Guardar producto' }}
                        </button>
                        <Link :href="route('admin.productos.index')" class="admin-prod-btn-cancel">
                            Cancelar
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>