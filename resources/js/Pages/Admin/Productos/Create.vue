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

        <div class="product-page">
            <!-- Botón volver -->
            <Link :href="route('admin.productos.index')" class="back-link">
                <i class="pi pi-arrow-left"></i>
                Volver a Productos
            </Link>

            <!-- ============================================================ -->
            <!-- GRID PRINCIPAL: FORMULARIO | ETIQUETAS + VARIANTES + IMÁGENES -->
            <!-- ============================================================ -->
            <div class="product-grid">
                <!-- COLUMNA IZQUIERDA: FORMULARIO PRINCIPAL -->
                <div class="product-form">
                    <!-- Cabecera -->
                    <div class="form-header">
                        <div class="form-header__icon">
                            <i class="pi pi-box"></i>
                        </div>
                        <div>
                            <h1>Nuevo Producto</h1>
                            <p>Ingresa los datos del producto</p>
                        </div>
                    </div>

                    <!-- Campos -->
                    <div class="form-body">
                        <!-- Nombre -->
                        <div class="field">
                            <label>Nombre del producto <span class="required">*</span></label>
                            <input v-model="form.nombre" type="text" placeholder="Ej. Aceite de masaje relajante"
                                :class="{ 'input-error': form.errors.nombre }" />
                            <p v-if="form.errors.nombre" class="error-text">{{ form.errors.nombre }}</p>
                        </div>

                        <!-- Descripción -->
                        <div class="field">
                            <div class="field-header">
                                <label>Descripción</label>
                                <span class="char-count">{{ form.descripcion.length }}/{{ descripcionMax }}</span>
                            </div>
                            <textarea v-model="form.descripcion" rows="3" :maxlength="descripcionMax"
                                placeholder="Describe el producto..."></textarea>
                            <p v-if="form.errors.descripcion" class="error-text">{{ form.errors.descripcion }}</p>
                        </div>

                        <!-- SKU, Categoría, Marca -->
                        <div class="field-row">
                            <div class="field">
                                <label>SKU <span class="required">*</span></label>
                                <input v-model="form.sku" type="text" placeholder="Ej. ACE-001"
                                    :class="{ 'input-error': form.errors.sku }" />
                                <p v-if="form.errors.sku" class="error-text">{{ form.errors.sku }}</p>
                            </div>
                            <div class="field">
                                <label>Categoría <span class="required">*</span></label>
                                <select v-model="form.categoria" :class="{ 'input-error': form.errors.categoria }">
                                    <option v-for="c in categorias" :key="c" :value="c">{{ c }}</option>
                                </select>
                                <p v-if="form.errors.categoria" class="error-text">{{ form.errors.categoria }}</p>
                            </div>
                            <div class="field">
                                <label>Marca <span class="optional">(opcional)</span></label>
                                <input v-model="form.marca" type="text" placeholder="Ej. L'Occitane" />
                            </div>
                        </div>

                        <!-- Precio y Stock -->
                        <div class="field-row">
                            <div class="field">
                                <label>Precio (MXN) <span class="required">*</span></label>
                                <div class="price-input">
                                    <span class="price-symbol">$</span>
                                    <input v-model="form.precio" type="number" min="0" step="0.01" placeholder="0.00"
                                        :class="{ 'input-error': form.errors.precio }" />
                                    <span v-if="precioFormateado" class="price-format">{{ precioFormateado }}</span>
                                </div>
                                <p v-if="form.errors.precio" class="error-text">{{ form.errors.precio }}</p>
                            </div>
                            <div class="field">
                                <label>Stock</label>
                                <input v-model="form.stock" type="number" min="0"
                                    :class="{ 'input-error': form.errors.stock }" />
                                <p v-if="form.errors.stock" class="error-text">{{ form.errors.stock }}</p>
                                <p v-else-if="Number(form.stock) === 0" class="stock-warning">
                                    <i class="pi pi-info-circle"></i> Producto agotado
                                </p>
                            </div>
                        </div>

                        <!-- Activo -->
                        <div class="field">
                            <label class="toggle-label">
                                <span class="toggle">
                                    <input v-model="form.esta_activo" type="checkbox" />
                                    <span class="toggle-slider"></span>
                                </span>
                                <span class="toggle-text">
                                    <i class="pi pi-eye"></i> Producto activo
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: ETIQUETAS + VARIANTES + IMÁGENES -->
                <div class="product-sidebar">
                    <!-- Etiquetas -->
                    <div class="sidebar-card">
                        <div class="sidebar-card__header">
                            <h3><i class="pi pi-hashtag"></i> Etiquetas</h3>
                        </div>
                        <div class="sidebar-card__body">
                            <div class="tag-input">
                                <input v-model="nuevaEtiqueta" type="text" placeholder="Escribe y presiona Enter..."
                                    @keydown.enter.prevent="agregarEtiqueta" />
                                <button type="button" @click="agregarEtiqueta">
                                    <i class="pi pi-plus"></i>
                                </button>
                            </div>
                            <div v-if="form.etiquetas.length" class="tags">
                                <span v-for="(et, i) in form.etiquetas" :key="i" class="tag">
                                    {{ et }}
                                    <button type="button" @click="quitarEtiqueta(i)">
                                        <i class="pi pi-times"></i>
                                    </button>
                                </span>
                            </div>
                            <p v-else class="hint">Ayudan a que el producto aparezca en más búsquedas</p>
                        </div>
                    </div>

                    <!-- Variantes -->
                    <div class="sidebar-card">
                        <div class="sidebar-card__header">
                            <h3><i class="pi pi-sliders-h"></i> Variantes</h3>
                        </div>
                        <div class="sidebar-card__body">
                            <p class="hint">Opcional. Ej. "Talla" con valores "S, M, L".</p>
                            <div class="variants">
                                <div v-for="(fila, i) in filasVariantes" :key="i" class="variant-row">
                                    <input v-model="fila.nombre" type="text" placeholder="Nombre" />
                                    <input v-model="fila.valores" type="text"
                                        placeholder="Valores (separados por coma)" />
                                    <button type="button" @click="quitarFilaVariante(i)">
                                        <i class="pi pi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="button" @click="agregarFilaVariante" class="add-variant">
                                <i class="pi pi-plus"></i> Agregar variante
                            </button>
                        </div>
                    </div>

                    <!-- Imágenes -->
                    <div class="sidebar-card">
                        <div class="sidebar-card__header">
                            <h3><i class="pi pi-images"></i> Imágenes</h3>
                            <span class="count">{{ previews.length }}/{{ MAX_IMAGENES }}</span>
                        </div>
                        <div class="sidebar-card__body">
                            <label v-if="previews.length < MAX_IMAGENES" class="upload-area">
                                <i class="pi pi-cloud-upload"></i>
                                <span>Subir imágenes</span>
                                <small>JPG o PNG, máx. {{ MAX_TAMANO_MB }}MB c/u</small>
                                <input type="file" accept="image/*" multiple @change="onImagenesChange" />
                            </label>
                            <p v-if="form.errors.imagenes" class="error-text">{{ form.errors.imagenes }}</p>

                            <div v-if="previews.length" class="image-grid">
                                <div v-for="(src, i) in previews" :key="i" class="image-item">
                                    <img :src="src" />
                                    <span v-if="i === 0" class="image-badge">Portada</span>
                                    <button v-else type="button" @click="hacerPortada(i)"
                                        class="image-btn image-btn--portada">
                                        Portada
                                    </button>
                                    <button type="button" @click="quitarImagen(i)" class="image-btn image-btn--delete">
                                        <i class="pi pi-times"></i>
                                    </button>
                                </div>
                            </div>
                            <p v-if="previews.length" class="hint">La primera imagen es la portada</p>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="action-card">
                        <button type="submit" :disabled="form.processing" class="btn-save" @click="guardar">
                            <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-save'"></i>
                            {{ form.processing ? 'Guardando...' : 'Guardar producto' }}
                        </button>
                        <Link :href="route('admin.productos.index')" class="btn-cancel">
                            Cancelar
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.product-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem 1.5rem 2rem;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

/* =========================================================================
   BOTÓN VOLVER
   ========================================================================= */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8rem;
    color: #8A8481;
    text-decoration: none;
    margin-bottom: 1.5rem;
    transition: color 0.2s;
}

.back-link:hover {
    color: #C81E3A;
}

.back-link i {
    font-size: 0.6rem;
}

/* =========================================================================
   GRID PRINCIPAL
   ========================================================================= */
.product-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1024px) {
    .product-grid {
        grid-template-columns: 1fr;
    }
}

/* =========================================================================
   FORMULARIO PRINCIPAL
   ========================================================================= */
.product-form {
    background: #FFFFFF;
    border-radius: 12px;
    border: 1px solid #EDE9E7;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.form-header {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #EDE9E7;
    background: #FAF8F7;
}

.form-header__icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #FBEAEC;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #C81E3A;
    font-size: 1.1rem;
}

.form-header h1 {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
    color: #171412;
}

.form-header p {
    font-size: 0.75rem;
    color: #8A8481;
    margin: 0;
}

.form-body {
    padding: 1.25rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* =========================================================================
   CAMPOS
   ========================================================================= */
.field {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.field label {
    font-size: 0.72rem;
    font-weight: 600;
    color: #4B4744;
}

.field .required {
    color: #E53E3E;
}

.field .optional {
    font-weight: 400;
    color: #B7B2AF;
    font-size: 0.65rem;
}

.field input,
.field select,
.field textarea {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    border: 1.5px solid #EDE9E7;
    font-size: 0.82rem;
    font-family: inherit;
    background: #FFFFFF;
    color: #171412;
    transition: all 0.2s;
    outline: none;
}

.field input:focus,
.field select:focus,
.field textarea:focus {
    border-color: #C81E3A;
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.08);
}

.field textarea {
    resize: vertical;
    min-height: 80px;
}

.field .input-error {
    border-color: #E53E3E;
}

.field .input-error:focus {
    border-color: #E53E3E;
    box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.08);
}

.error-text {
    font-size: 0.65rem;
    color: #E53E3E;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.error-text::before {
    content: '⚠';
    font-size: 0.55rem;
}

.field-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.char-count {
    font-size: 0.6rem;
    color: #B7B2AF;
}

.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 0.8rem;
}

@media (max-width: 768px) {
    .field-row {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 500px) {
    .field-row {
        grid-template-columns: 1fr;
    }
}

.price-input {
    position: relative;
}

.price-symbol {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #B7B2AF;
    font-size: 0.8rem;
}

.price-input input {
    padding-left: 1.5rem;
}

.price-format {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.7rem;
    font-weight: 600;
    color: #1fbf5c;
}

.stock-warning {
    font-size: 0.65rem;
    color: #D69E2E;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

/* =========================================================================
   TOGGLE
   ========================================================================= */
.toggle-label {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    cursor: pointer;
}

.toggle {
    position: relative;
    width: 36px;
    height: 20px;
}

.toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #D1D5DB;
    border-radius: 999px;
    transition: 0.3s;
}

.toggle-slider::before {
    content: '';
    position: absolute;
    height: 14px;
    width: 14px;
    left: 3px;
    bottom: 3px;
    background: #FFFFFF;
    border-radius: 50%;
    transition: 0.3s;
}

.toggle input:checked+.toggle-slider {
    background: #C81E3A;
}

.toggle input:checked+.toggle-slider::before {
    transform: translateX(16px);
}

.toggle-text {
    font-size: 0.78rem;
    font-weight: 500;
    color: #4B4744;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.toggle-text i {
    color: #C81E3A;
    font-size: 0.7rem;
}

/* =========================================================================
   SIDEBAR - ETIQUETAS + VARIANTES + IMÁGENES
   ========================================================================= */
.product-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.sidebar-card {
    background: #FFFFFF;
    border-radius: 12px;
    border: 1px solid #EDE9E7;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.sidebar-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.6rem 1rem;
    border-bottom: 1px solid #EDE9E7;
    background: #FAF8F7;
}

.sidebar-card__header h3 {
    font-size: 0.78rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    color: #171412;
}

.sidebar-card__header h3 i {
    color: #C81E3A;
    font-size: 0.8rem;
}

.sidebar-card__header .count {
    font-size: 0.6rem;
    font-weight: 600;
    color: #8A8481;
    background: #FAF8F7;
    padding: 0.1rem 0.5rem;
    border-radius: 999px;
}

.sidebar-card__body {
    padding: 0.8rem 1rem;
}

.hint {
    font-size: 0.7rem;
    color: #B7B2AF;
    margin: 0;
}

/* =========================================================================
   ETIQUETAS
   ========================================================================= */
.tag-input {
    display: flex;
    gap: 0.4rem;
}

.tag-input input {
    flex: 1;
    padding: 0.4rem 0.6rem;
    border-radius: 6px;
    border: 1.5px solid #EDE9E7;
    font-size: 0.75rem;
    font-family: inherit;
    outline: none;
    transition: all 0.2s;
}

.tag-input input:focus {
    border-color: #C81E3A;
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.08);
}

.tag-input button {
    padding: 0.4rem 0.7rem;
    border-radius: 6px;
    border: none;
    background: #C81E3A;
    color: #FFFFFF;
    font-size: 0.7rem;
    cursor: pointer;
    transition: background 0.2s;
}

.tag-input button:hover {
    background: #A6152D;
}

.tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    margin-top: 0.6rem;
}

.tag {
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
    font-size: 0.65rem;
    font-weight: 500;
    background: #FBEAEC;
    color: #C81E3A;
}

.tag button {
    border: none;
    background: none;
    color: inherit;
    cursor: pointer;
    padding: 0;
    font-size: 0.5rem;
    opacity: 0.6;
    transition: opacity 0.2s;
}

.tag button:hover {
    opacity: 1;
}

/* =========================================================================
   VARIANTES
   ========================================================================= */
.variants {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    margin-top: 0.4rem;
}

.variant-row {
    display: flex;
    gap: 0.4rem;
    align-items: center;
}

.variant-row input {
    padding: 0.35rem 0.6rem;
    border-radius: 6px;
    border: 1.5px solid #EDE9E7;
    font-size: 0.72rem;
    font-family: inherit;
    outline: none;
    transition: all 0.2s;
}

.variant-row input:focus {
    border-color: #C81E3A;
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.08);
}

.variant-row input:first-child {
    flex: 1;
}

.variant-row input:last-child {
    flex: 2;
}

.variant-row button {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: 1.5px solid #EDE9E7;
    background: transparent;
    color: #B7B2AF;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.variant-row button:hover {
    border-color: #E53E3E;
    color: #E53E3E;
    background: #FEE8EA;
}

.add-variant {
    border: none;
    background: transparent;
    color: #C81E3A;
    font-size: 0.72rem;
    font-weight: 600;
    cursor: pointer;
    padding: 0.3rem 0;
    display: flex;
    align-items: center;
    gap: 0.3rem;
    transition: color 0.2s;
}

.add-variant:hover {
    color: #A6152D;
}

.add-variant i {
    font-size: 0.6rem;
}

/* =========================================================================
   IMÁGENES
   ========================================================================= */
.upload-area {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.2rem;
    padding: 1rem;
    border: 2px dashed #EDE9E7;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    color: #B7B2AF;
}

.upload-area:hover {
    border-color: #C81E3A;
    color: #C81E3A;
    background: #FBEAEC;
}

.upload-area i {
    font-size: 1.2rem;
}

.upload-area span {
    font-size: 0.75rem;
    font-weight: 500;
}

.upload-area small {
    font-size: 0.6rem;
}

.upload-area input {
    display: none;
}

.image-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.3rem;
    margin-top: 0.6rem;
}

.image-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 6px;
    overflow: hidden;
    background: #FAF8F7;
}

.image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-badge {
    position: absolute;
    bottom: 2px;
    left: 2px;
    font-size: 0.4rem;
    font-weight: 700;
    padding: 0.05rem 0.3rem;
    border-radius: 3px;
    background: rgba(200, 30, 58, 0.9);
    color: #FFFFFF;
}

.image-btn {
    position: absolute;
    border: none;
    border-radius: 50%;
    font-size: 0.4rem;
    cursor: pointer;
    transition: all 0.2s;
    opacity: 0;
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #FFFFFF;
}

.image-item:hover .image-btn {
    opacity: 1;
}

.image-btn--portada {
    bottom: 2px;
    right: 20px;
    background: rgba(0, 0, 0, 0.6);
    font-size: 0.35rem;
    padding: 0 0.3rem;
    border-radius: 3px;
    width: auto;
}

.image-btn--portada:hover {
    background: #C81E3A;
}

.image-btn--delete {
    top: 2px;
    right: 2px;
    background: rgba(0, 0, 0, 0.6);
}

.image-btn--delete:hover {
    background: #E53E3E;
}

/* =========================================================================
   BOTONES DE ACCIÓN
   ========================================================================= */
.action-card {
    background: #FFFFFF;
    border-radius: 12px;
    border: 1px solid #EDE9E7;
    padding: 0.8rem 1rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.btn-save {
    width: 100%;
    padding: 0.5rem 1.5rem;
    border-radius: 8px;
    border: none;
    background: #C81E3A;
    color: #FFFFFF;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
}

.btn-save:hover {
    background: #A6152D;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(200, 30, 58, 0.25);
}

.btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-cancel {
    width: 100%;
    padding: 0.5rem 1.5rem;
    border-radius: 8px;
    border: 1.5px solid #EDE9E7;
    background: transparent;
    color: #8A8481;
    font-size: 0.78rem;
    font-weight: 500;
    text-decoration: none;
    text-align: center;
    transition: all 0.2s;
}

.btn-cancel:hover {
    border-color: #B7B2AF;
    background: #FAF8F7;
}
</style>