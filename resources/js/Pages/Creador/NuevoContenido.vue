<template>

    <Head title="Nuevo contenido" />

    <AppLayout active-nav="comunidad" :usuario="usuario">
        <div class="nuevo-contenido-page">
            <!-- Título -->
            <div class="page-heading">
                <div class="page-heading__top">
                    <button class="btn-back" @click="volver">
                        <i class="pi pi-arrow-left"></i>
                        <span>Volver</span>
                    </button>
                </div>
                <h1>Nuevo contenido</h1>
                <p>Comparte contenido exclusivo con tu comunidad y monetiza tu creatividad.</p>
            </div>

            <div class="content-grid">
                <div class="main-column">
                    <form @submit.prevent="guardarContenido" class="form-card">
                        <!-- Tipo de contenido -->
                        <div class="field">
                            <label>Tipo de contenido <span class="required">*</span></label>
                            <div class="content-type-grid">
                                <button v-for="tipo in tiposContenido" :key="tipo.value" class="content-type-card"
                                    :class="{ selected: form.tipo === tipo.value }" @click="form.tipo = tipo.value"
                                    type="button">
                                    <i class="pi" :class="tipo.icon"></i>
                                    <span>{{ tipo.label }}</span>
                                </button>
                            </div>
                            <span v-if="errores.tipo" class="error-message">
                                <i class="pi pi-exclamation-circle"></i> {{ errores.tipo }}
                            </span>
                        </div>

                        <!-- Título -->
                        <div class="field">
                            <label>Título <span class="required">*</span></label>
                            <input type="text" v-model="form.titulo" class="form-input"
                                :class="{ 'input-error': errores.titulo }" placeholder="Ej: Mi experiencia en la playa"
                                maxlength="255" />
                            <span class="char-count">{{ form.titulo.length }}/255</span>
                            <span v-if="errores.titulo" class="error-message">
                                <i class="pi pi-exclamation-circle"></i> {{ errores.titulo }}
                            </span>
                        </div>

                        <!-- Categoría -->
                        <div class="field">
                            <label>Categoría</label>
                            <div class="categoria-selector">
                                <button v-for="cat in categoriasDisponibles" :key="cat" class="categoria-pill"
                                    :class="{ selected: form.categoria === cat }" @click="form.categoria = cat"
                                    type="button">
                                    {{ cat }}
                                </button>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="field">
                            <label>Descripción</label>
                            <textarea v-model="form.descripcion" class="form-textarea"
                                placeholder="Describe tu contenido..." rows="4" maxlength="5000"></textarea>
                            <span class="char-count">{{ form.descripcion.length }}/5000</span>
                        </div>

                        <!-- Archivos -->
                        <div class="field">
                            <label>Archivos <span class="required">*</span></label>
                            <div class="dropzone" :class="{ 'dropzone--dragover': isDragging }"
                                @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop">
                                <div class="dropzone__content">
                                    <i class="pi pi-cloud-upload"></i>
                                    <span>Arrastra tus archivos aquí</span>
                                    <small>Formatos: JPG, PNG, GIF, WEBP, MP4, MOV (Máx. 50MB)</small>
                                </div>
                                <input type="file" ref="fileInput" accept="image/*,video/*" multiple
                                    @change="handleFileSelect" hidden />
                                <button type="button" class="btn-select-files" @click="$refs.fileInput.click()">
                                    <i class="pi pi-folder-open"></i> Seleccionar archivos
                                </button>
                            </div>

                            <div v-if="archivos.length > 0" class="archivos-grid">
                                <div v-for="(archivo, index) in archivos" :key="index" class="archivo-card">
                                    <div class="archivo-card__preview">
                                        <img v-if="archivo.tipo?.startsWith('image/')" :src="archivo.url"
                                            alt="Preview" />
                                        <video v-else-if="archivo.tipo?.startsWith('video/')" :src="archivo.url" muted
                                            preload="metadata"></video>
                                        <i v-else class="pi pi-file"></i>
                                    </div>
                                    <div class="archivo-card__info">
                                        <span class="archivo-card__nombre">{{ archivo.nombre_original }}</span>
                                        <span class="archivo-card__tamano">{{ formatSize(archivo.tamano) }}</span>
                                    </div>
                                    <button type="button" class="archivo-card__delete" @click="eliminarArchivo(index)">
                                        <i class="pi pi-times"></i>
                                    </button>
                                </div>
                            </div>
                            <span v-if="errores.archivos" class="error-message">
                                <i class="pi pi-exclamation-circle"></i> {{ errores.archivos }}
                            </span>
                        </div>

                        <!-- Etiquetas -->
                        <div class="field">
                            <label>Etiquetas</label>
                            <div class="tag-input">
                                <span v-for="tag in form.etiquetas" :key="tag" class="tag-chip">
                                    {{ tag }}
                                    <button type="button" @click="quitarEtiqueta(tag)">
                                        <i class="pi pi-times"></i>
                                    </button>
                                </span>
                                <input type="text" class="tag-input__field"
                                    placeholder="Agrega etiquetas y presiona Enter..."
                                    @keydown.enter.prevent="agregarEtiqueta" v-model="nuevaEtiqueta" />
                            </div>
                            <span class="tag-hint">Presiona Enter para agregar una etiqueta (máx. 10)</span>
                        </div>

                        <!-- Visibilidad y premium -->
                        <div class="field-grid">
                            <div class="field">
                                <label>Visibilidad</label>
                                <div class="visibilidad-options">
                                    <button class="visibilidad-pill"
                                        :class="{ selected: form.visibilidad === 'publico' }"
                                        @click="form.visibilidad = 'publico'" type="button">
                                        <i class="pi pi-globe"></i> Público
                                    </button>
                                    <button class="visibilidad-pill"
                                        :class="{ selected: form.visibilidad === 'suscriptores' }"
                                        @click="form.visibilidad = 'suscriptores'" type="button">
                                        <i class="pi pi-lock"></i> Suscriptores
                                    </button>
                                </div>
                            </div>

                            <div class="field">
                                <label>Contenido premium</label>
                                <div class="premium-toggle">
                                    <span class="premium-toggle__icon"><i class="pi pi-crown"></i></span>
                                    <div class="premium-toggle__text">
                                        <strong>Marcar como premium</strong>
                                        <span>El precio se toma de tu configuración de monetización</span>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" v-model="form.es_premium" />
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div v-if="form.es_premium" class="premium-info">
                                    <i class="pi pi-info-circle"></i>
                                    <span>Este contenido será premium con el precio configurado:
                                        <strong>${{ configuracion?.precio_personalizado || 199.99 }} MXN</strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- SIDEBAR -->
                <aside class="sidebar-column">
                    <!-- Resumen -->
                    <div class="sidebar-card summary-card">
                        <div class="summary-card__header">
                            <i class="pi pi-file-check"></i>
                            <h3>Resumen</h3>
                        </div>
                        <div class="resumen-item">
                            <span>Tipo</span>
                            <strong class="resumen-value">{{ tipoLabel }}</strong>
                        </div>
                        <div class="resumen-item">
                            <span>Visibilidad</span>
                            <strong class="resumen-value">{{ visibilidadLabel }}</strong>
                        </div>
                        <div class="resumen-item">
                            <span>Premium</span>
                            <strong class="resumen-value" :class="{ 'premium-active': form.es_premium }">
                                {{ form.es_premium ? 'Sí' : 'No' }}
                            </strong>
                        </div>
                        <div v-if="form.es_premium" class="resumen-item resumen-item--highlight">
                            <span>Precio</span>
                            <strong class="resumen-value accent">
                                ${{ configuracion?.precio_personalizado || 199.99 }} MXN
                            </strong>
                        </div>
                        <div class="resumen-item">
                            <span>Archivos</span>
                            <strong class="resumen-value">{{ archivos.length }} archivo(s)</strong>
                        </div>
                        <div class="resumen-item">
                            <span>Etiquetas</span>
                            <strong class="resumen-value">{{ form.etiquetas.length }}</strong>
                        </div>
                    </div>

                    <!-- Checklist -->
                    <div class="sidebar-card checklist-card">
                        <div class="checklist-header">
                            <div class="checklist-header__icon">
                                <i class="pi pi-check-circle"></i>
                            </div>
                            <div>
                                <h3>Lista de verificación</h3>
                                <span class="checklist-header__subtitle">
                                    {{ checklistCompletados }}/{{ checklistItems.length }} completados
                                </span>
                            </div>
                            <div class="checklist-progress">
                                <div class="checklist-progress__bar" :style="{ width: porcentajeChecklist + '%' }">
                                </div>
                            </div>
                        </div>
                        <div class="checklist-list">
                            <div v-for="item in checklistItems" :key="item.id" class="checklist-item"
                                :class="{ 'checklist-item--completed': item.completado }">
                                <div class="checklist-item__icon">
                                    <i class="pi" :class="item.completado ? 'pi-check-circle' : 'pi-circle'"></i>
                                </div>
                                <div class="checklist-item__content">
                                    <span class="checklist-item__title">{{ item.label }}</span>
                                    <span class="checklist-item__status"
                                        :class="item.completado ? 'status-ok' : 'status-pending'">
                                        {{ item.completado ? 'Listo' : 'Pendiente' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Consejos -->
                    <div class="sidebar-card tips-card">
                        <div class="tips-card__header">
                            <i class="pi pi-lightbulb"></i>
                            <h3>Consejos para destacar</h3>
                        </div>
                        <div class="tips-list">
                            <div v-for="consejo in consejos" :key="consejo.titulo" class="tip-item">
                                <span class="tip-item__icon"><i class="pi" :class="consejo.icon"></i></span>
                                <div>
                                    <strong>{{ consejo.titulo }}</strong>
                                    <span>{{ consejo.desc }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="sidebar-card actions-card">
                        <button type="button" class="btn-actions btn-actions--primary" @click="guardarContenido"
                            :disabled="isSubmitting">
                            <i class="pi" :class="isSubmitting ? 'pi-spin pi-spinner' : 'pi-send'"></i>
                            {{ isSubmitting ? 'Publicando...' : 'Publicar contenido' }}
                        </button>
                        <button type="button" class="btn-actions btn-actions--secondary" @click="volver">
                            <i class="pi pi-times"></i> Cancelar
                        </button>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    usuario: {
        type: Object,
        default: () => ({})
    },
    configuracion: {
        type: Object,
        default: null
    },
    categoriasDisponibles: {
        type: Array,
        default: () => []
    },
    footerColumnas: {
        type: Object,
        default: () => ({})
    }
});

// Estado
const isSubmitting = ref(false);
const isDragging = ref(false);
const fileInput = ref(null);
const nuevaEtiqueta = ref('');
const errores = reactive({});
const archivos = ref([]);

// Formulario
const form = reactive({
    tipo: 'foto',
    titulo: '',
    categoria: '',
    descripcion: '',
    visibilidad: 'publico',
    es_premium: false,
    etiquetas: [],
});

// Watch para resetear errores cuando cambia el campo
watch(() => form.titulo, () => { delete errores.titulo; });
watch(() => form.tipo, () => { delete errores.tipo; });
watch(archivos, () => { delete errores.archivos; }, { deep: true });

// Tipos de contenido
const tiposContenido = [
    { value: 'foto', label: 'Foto', icon: 'pi-image' },
    { value: 'video', label: 'Video', icon: 'pi-video' },
];

// Consejos
const consejos = [
    { icon: 'pi-star', titulo: 'Títulos atractivos', desc: 'Captura la atención desde el primer momento.' },
    { icon: 'pi-image', titulo: 'Usa contenido de calidad', desc: 'El contenido visual atrae más interacciones.' },
    { icon: 'pi-tag', titulo: 'Etiquetas relevantes', desc: 'Ayudan a que tu contenido sea más visible.' },
    { icon: 'pi-crown', titulo: 'Contenido premium', desc: 'Ofrece valor exclusivo para tus suscriptores.' },
    { icon: 'pi-clock', titulo: 'Publica consistentemente', desc: 'La constancia construye una comunidad fiel.' },
];

// Computed
const tipoLabel = computed(() => {
    const tipo = tiposContenido.find(t => t.value === form.tipo);
    return tipo ? tipo.label : 'No seleccionado';
});

const visibilidadLabel = computed(() => {
    return form.visibilidad === 'publico' ? 'Público' : 'Solo suscriptores';
});

const checklistItems = computed(() => [
    { id: 1, label: 'Seleccionar tipo de contenido', completado: !!form.tipo },
    { id: 2, label: 'Escribir un título (mín. 3 caracteres)', completado: form.titulo.length >= 3 },
    { id: 3, label: 'Agregar al menos un archivo', completado: archivos.value.length > 0 },
    { id: 4, label: 'Configurar visibilidad', completado: !!form.visibilidad },
]);

const checklistCompletados = computed(() => {
    return checklistItems.value.filter(item => item.completado).length;
});

const porcentajeChecklist = computed(() => {
    const total = checklistItems.value.length;
    const completados = checklistCompletados.value;
    return total > 0 ? Math.round((completados / total) * 100) : 0;
});

// Funciones
function handleDrop(event) {
    isDragging.value = false;
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        procesarArchivos(files);
    }
}

function handleFileSelect(event) {
    const files = event.target.files;
    if (files.length > 0) {
        procesarArchivos(files);
    }
    event.target.value = '';
}

function procesarArchivos(files) {
    const maxSize = 50 * 1024 * 1024;
    const fileArray = Array.from(files);

    for (const file of fileArray) {
        if (!file.type.startsWith('image/') && !file.type.startsWith('video/')) {
            window.showErrorToast(`"${file.name}" no es un formato válido. Solo imágenes y videos.`);
            continue;
        }

        if (file.size > maxSize) {
            window.showErrorToast(`"${file.name}" excede el tamaño máximo de 50MB`);
            continue;
        }

        const url = URL.createObjectURL(file);
        archivos.value.push({
            url: url,
            file: file,
            nombre_original: file.name,
            tipo: file.type,
            tamano: file.size,
        });
    }

    if (fileInput.value) {
        fileInput.value = '';
    }
}

function eliminarArchivo(index) {
    if (archivos.value[index]?.url) {
        URL.revokeObjectURL(archivos.value[index].url);
    }
    archivos.value.splice(index, 1);
}

function agregarEtiqueta() {
    const tag = nuevaEtiqueta.value.trim();
    if (tag && !form.etiquetas.includes(tag) && form.etiquetas.length < 10) {
        form.etiquetas.push(tag);
        nuevaEtiqueta.value = '';
    } else if (form.etiquetas.length >= 10) {
        window.showErrorToast('Máximo 10 etiquetas permitidas');
    }
}

function quitarEtiqueta(tag) {
    form.etiquetas = form.etiquetas.filter(t => t !== tag);
}

function formatSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function volver() {
    router.get(route('creador.comunidad'));
}

function guardarContenido() {
    // Limpiar errores
    Object.keys(errores).forEach(key => delete errores[key]);

    // Validaciones
    if (!form.tipo) {
        errores.tipo = 'Selecciona un tipo de contenido';
    }
    if (!form.titulo.trim() || form.titulo.length < 3) {
        errores.titulo = 'El título debe tener al menos 3 caracteres';
    }
    if (archivos.value.length === 0) {
        errores.archivos = 'Agrega al menos un archivo';
    }

    if (Object.keys(errores).length > 0) {
        window.showErrorToast('Corrige los errores antes de continuar');
        return;
    }

    isSubmitting.value = true;

    const formData = new FormData();
    formData.append('tipo', form.tipo);
    formData.append('titulo', form.titulo);
    formData.append('categoria', form.categoria || '');
    formData.append('descripcion', form.descripcion || '');
    formData.append('visibilidad', form.visibilidad);
    formData.append('es_premium', form.es_premium ? '1' : '0');
    formData.append('etiquetas', JSON.stringify(form.etiquetas));

    // ✅ FORMA CORRECTA: Enviar archivos con el mismo nombre
    archivos.value.forEach((archivo) => {
        if (archivo.file) {
            // ✅ Usar 'archivos[]' para que Laravel lo reciba como array
            formData.append('archivos[]', archivo.file);
        }
    });

    // Debug - Verificar que los archivos están en FormData
    console.log('=== DEBUG FORMDATA ===');
    console.log('Total archivos:', archivos.value.length);
    for (let pair of formData.entries()) {
        if (pair[1] instanceof File) {
            console.log(pair[0], '->', pair[1].name, 'tamaño:', pair[1].size, 'tipo:', pair[1].type);
        } else {
            console.log(pair[0], '->', pair[1]);
        }
    }

    // Enviar con Inertia
    router.post(route('creador.nuevo-contenido.store'), formData, {
        preserveScroll: true,
        onSuccess: () => {
            isSubmitting.value = false;
            window.showSuccessToast('Contenido publicado exitosamente');
            setTimeout(() => {
                router.get(route('creador.comunidad'));
            }, 1500);
        },
        onError: (errors) => {
            isSubmitting.value = false;
            console.error('Errores del servidor:', errors);
            if (errors && typeof errors === 'object') {
                Object.keys(errors).forEach(key => {
                    errores[key] = errors[key];
                });
                const firstError = Object.values(errors)[0];
                if (firstError) {
                    const msg = Array.isArray(firstError) ? firstError[0] : firstError;
                    window.showErrorToast(msg);
                }
            }
        }
    });
}
</script>

<style scoped>
/* ============================================================
   ESTILOS MEJORADOS
   ============================================================ */
.nuevo-contenido-page {
    --brand: #C81E3A;
    --brand-dark: #A6152D;
    --brand-soft: #FBEAEC;
    --brand-light: #FDE8EB;
    --ink: #1a1a2e;
    --ink-soft: #4a4a6a;
    --muted: #8a8aaa;
    --muted-light: #b8b8d0;
    --line: #e8e8f0;
    --surface: #f8f8fc;
    --white: #ffffff;
    --shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.08);
    --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --radius: 14px;
    --radius-sm: 10px;
    --radius-full: 999px;
    max-width: 1400px;
    margin: 0 auto;
    padding: 1.5rem 2rem 3rem;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: var(--ink);
}

/* ============================================================
   TÍTULO
   ============================================================ */
.page-heading {
    margin-bottom: 2rem;
}

.page-heading__top {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: none;
    border: none;
    color: var(--muted);
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    padding: 0.3rem 0.6rem;
    border-radius: var(--radius-sm);
    transition: all var(--transition);
}

.btn-back:hover {
    color: var(--brand);
    background: var(--brand-soft);
    transform: translateX(-2px);
}

.btn-back i {
    font-size: 0.9rem;
}

.page-heading h1 {
    font-size: 1.8rem;
    margin: 0 0 0.2rem;
    font-weight: 700;
    color: var(--ink);
    background: linear-gradient(135deg, var(--brand), #E74C6F);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.page-heading p {
    font-size: 0.9rem;
    color: var(--muted);
    margin: 0;
}

/* ============================================================
   CONTENT GRID
   ============================================================ */
.content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 360px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1100px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

/* ============================================================
   FORM CARD
   ============================================================ */
.form-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 2rem;
    box-shadow: var(--shadow);
    transition: all var(--transition);
}

.form-card:hover {
    box-shadow: var(--shadow-hover);
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    margin-bottom: 1.5rem;
}

.field:last-child {
    margin-bottom: 0;
}

.field label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--ink-soft);
}

.field label .required {
    color: #EF4444;
    margin-left: 0.2rem;
}

.field-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .field-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}

/* ============================================================
   CONTENT TYPE
   ============================================================ */
.content-type-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.8rem;
}

.content-type-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    border: 2px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--white);
    cursor: pointer;
    transition: all var(--transition);
    color: var(--ink-soft);
}

.content-type-card:hover {
    border-color: var(--brand);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.content-type-card.selected {
    border-color: var(--brand);
    background: var(--brand-soft);
    color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
}

.content-type-card i {
    font-size: 1.8rem;
    color: var(--muted-light);
}

.content-type-card.selected i {
    color: var(--brand);
}

.content-type-card span {
    font-size: 0.82rem;
    font-weight: 600;
}

/* ============================================================
   FORM INPUTS
   ============================================================ */
.form-input,
.form-textarea {
    width: 100%;
    border-radius: var(--radius-sm);
    border: 1.5px solid var(--line);
    transition: all var(--transition);
    padding: 0.7rem 1rem;
    font-size: 0.95rem;
    font-family: inherit;
    background: var(--white);
    color: var(--ink);
}

.form-input:focus,
.form-textarea:focus {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
    outline: none;
}

.form-input.input-error,
.form-textarea.input-error {
    border-color: #EF4444;
}

.form-textarea {
    resize: vertical;
    min-height: 100px;
}

.char-count {
    align-self: flex-end;
    font-size: 0.7rem;
    color: var(--muted-light);
}

.error-message {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    color: #EF4444;
    font-size: 0.8rem;
    margin-top: 0.2rem;
}

/* ============================================================
   CATEGORIA SELECTOR
   ============================================================ */
.categoria-selector {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.categoria-pill {
    padding: 0.4rem 1rem;
    border: 1.5px solid var(--line);
    border-radius: var(--radius-full);
    background: var(--white);
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--ink-soft);
    cursor: pointer;
    transition: all var(--transition);
}

.categoria-pill:hover {
    border-color: var(--brand);
    color: var(--brand);
}

.categoria-pill.selected {
    background: var(--brand);
    border-color: var(--brand);
    color: var(--white);
}

/* ============================================================
   DROPZONE
   ============================================================ */
.dropzone {
    border: 2px dashed var(--line);
    border-radius: var(--radius-sm);
    padding: 2.5rem 2rem;
    text-align: center;
    background: var(--surface);
    transition: all var(--transition);
    cursor: pointer;
}

.dropzone:hover,
.dropzone--dragover {
    border-color: var(--brand);
    background: var(--brand-light);
}

.dropzone__content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.dropzone i {
    font-size: 3rem;
    color: var(--muted-light);
}

.dropzone span {
    font-size: 0.95rem;
    color: var(--ink-soft);
    font-weight: 600;
}

.dropzone small {
    font-size: 0.78rem;
    color: var(--muted-light);
}

.btn-select-files {
    margin-top: 0.8rem;
    padding: 0.5rem 1.5rem;
    border: 1.5px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--white);
    color: var(--ink-soft);
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition);
}

.btn-select-files:hover {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
}

/* ============================================================
   ARCHIVOS GRID
   ============================================================ */
.archivos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 0.8rem;
    margin-top: 0.8rem;
}

.archivo-card {
    background: var(--surface);
    border-radius: var(--radius-sm);
    border: 1px solid var(--line);
    overflow: hidden;
    transition: all var(--transition);
    position: relative;
}

.archivo-card:hover {
    border-color: var(--brand);
    box-shadow: var(--shadow);
}

.archivo-card__preview {
    width: 100%;
    aspect-ratio: 1/1;
    overflow: hidden;
    background: #111;
    display: flex;
    align-items: center;
    justify-content: center;
}

.archivo-card__preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.archivo-card__preview video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.archivo-card__preview i {
    font-size: 2.5rem;
    color: var(--muted-light);
}

.archivo-card__info {
    padding: 0.5rem 0.7rem;
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.archivo-card__nombre {
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--ink);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.archivo-card__tamano {
    font-size: 0.65rem;
    color: var(--muted);
}

.archivo-card__delete {
    position: absolute;
    top: 6px;
    right: 6px;
    border: none;
    background: rgba(0, 0, 0, 0.7);
    color: #fff;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition);
}

.archivo-card__delete:hover {
    background: #EF4444;
    transform: scale(1.1);
}

.archivo-card__delete i {
    font-size: 0.7rem;
}

/* ============================================================
   ETIQUETAS
   ============================================================ */
.tag-input {
    border: 1.5px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.4rem 0.7rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    min-height: 48px;
    align-items: center;
    transition: all var(--transition);
    background: var(--white);
}

.tag-input:focus-within {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
}

.tag-input__field {
    flex: 1;
    border: none;
    outline: none;
    padding: 0.3rem 0;
    font-size: 0.85rem;
    font-family: inherit;
    background: transparent;
    min-width: 80px;
}

.tag-chip {
    background: var(--brand-soft);
    color: var(--brand);
    border-radius: 6px;
    padding: 0.25rem 0.7rem;
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.tag-chip button {
    border: none;
    background: none;
    color: var(--brand);
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
    font-size: 0.7rem;
    opacity: 0.7;
    transition: all var(--transition);
}

.tag-chip button:hover {
    opacity: 1;
    transform: scale(1.2);
}

.tag-hint {
    font-size: 0.75rem;
    color: var(--muted-light);
    margin-top: 0.3rem;
}

/* ============================================================
   VISIBILIDAD
   ============================================================ */
.visibilidad-options {
    display: flex;
    gap: 0.8rem;
}

.visibilidad-pill {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    border: 2px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--white);
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--ink-soft);
    transition: all var(--transition);
}

.visibilidad-pill:hover {
    border-color: var(--brand);
}

.visibilidad-pill.selected {
    border-color: var(--brand);
    background: var(--brand-soft);
    color: var(--brand);
}

/* ============================================================
   PREMIUM TOGGLE
   ============================================================ */
.premium-toggle {
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.8rem 1rem;
    background: var(--surface);
    transition: all var(--transition);
}

.premium-toggle:hover {
    border-color: var(--brand);
}

.premium-toggle__icon {
    width: 42px;
    height: 42px;
    border-radius: var(--radius-sm);
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.2rem;
}

.premium-toggle__text {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.premium-toggle__text strong {
    font-size: 0.85rem;
}

.premium-toggle__text span {
    font-size: 0.78rem;
    color: var(--muted);
}

.premium-info {
    margin-top: 0.5rem;
    padding: 0.6rem 0.8rem;
    background: var(--brand-soft);
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    color: var(--brand);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.premium-info strong {
    font-weight: 700;
}

/* ============================================================
   TOGGLE SWITCH
   ============================================================ */
.toggle-switch {
    position: relative;
    width: 48px;
    height: 26px;
    flex-shrink: 0;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    inset: 0;
    background: var(--line);
    border-radius: var(--radius-full);
    transition: all var(--transition);
    cursor: pointer;
}

.toggle-switch input:checked+.toggle-slider {
    background: var(--brand);
}

.toggle-slider::before {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    left: 3px;
    top: 3px;
    background: var(--white);
    border-radius: 50%;
    transition: all var(--transition);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
}

.toggle-switch input:checked+.toggle-slider::before {
    transform: translateX(22px);
}

/* ============================================================
   SIDEBAR
   ============================================================ */
.sidebar-column {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.sidebar-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 1.5rem;
    box-shadow: var(--shadow);
    transition: all var(--transition);
}

.sidebar-card:hover {
    box-shadow: var(--shadow-hover);
}

/* ============================================================
   SUMMARY CARD
   ============================================================ */
.summary-card__header {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 1rem;
}

.summary-card__header i {
    font-size: 1.1rem;
    color: var(--brand);
}

.summary-card h3 {
    font-size: 1rem;
    margin: 0;
    font-weight: 700;
}

.resumen-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    font-size: 0.85rem;
    color: var(--ink-soft);
    border-bottom: 1px solid var(--line);
}

.resumen-item:last-child {
    border-bottom: none;
}

.resumen-item--highlight {
    background: var(--brand-soft);
    border-radius: var(--radius-sm);
    padding: 0.5rem 0.8rem;
    margin: 0.3rem 0;
}

.resumen-value {
    font-weight: 600;
    color: var(--ink);
}

.resumen-value.premium-active {
    color: var(--brand);
}

.resumen-value.accent {
    color: var(--brand);
    font-size: 1rem;
}

/* ============================================================
   CHECKLIST
   ============================================================ */
.checklist-header {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.checklist-header__icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.checklist-header__icon i {
    font-size: 1.1rem;
}

.checklist-header h3 {
    font-size: 0.95rem;
    margin: 0;
}

.checklist-header__subtitle {
    font-size: 0.75rem;
    color: var(--muted);
}

.checklist-progress {
    width: 100%;
    height: 5px;
    background: var(--line);
    border-radius: 3px;
    overflow: hidden;
    flex-basis: 100%;
}

.checklist-progress__bar {
    height: 100%;
    background: linear-gradient(90deg, var(--brand), #10B981);
    border-radius: 3px;
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.checklist-list {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.checklist-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.5rem 0.7rem;
    border-radius: var(--radius-sm);
    background: var(--surface);
    transition: all var(--transition);
}

.checklist-item--completed {
    background: #ECFDF5;
}

.checklist-item__icon {
    font-size: 1rem;
    color: var(--muted-light);
}

.checklist-item--completed .checklist-item__icon {
    color: #10B981;
}

.checklist-item__content {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.checklist-item__title {
    font-size: 0.82rem;
    color: var(--ink-soft);
}

.checklist-item--completed .checklist-item__title {
    color: var(--ink);
}

.checklist-item__status {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.1rem 0.6rem;
    border-radius: var(--radius-full);
}

.status-ok {
    color: #065F46;
    background: #D1FAE5;
}

.status-pending {
    color: var(--muted);
    background: var(--line);
}

/* ============================================================
   TIPS CARD
   ============================================================ */
.tips-card__header {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 1.2rem;
}

.tips-card__header i {
    font-size: 1.1rem;
    color: #F59E0B;
}

.tips-card h3 {
    font-size: 1rem;
    margin: 0;
    font-weight: 700;
}

.tips-list {
    display: flex;
    flex-direction: column;
    gap: 0.9rem;
}

.tip-item {
    display: flex;
    gap: 0.7rem;
    align-items: flex-start;
}

.tip-item__icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-sm);
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.85rem;
}

.tip-item strong {
    display: block;
    font-size: 0.85rem;
}

.tip-item span {
    font-size: 0.78rem;
    color: var(--muted);
}

/* ============================================================
   ACTIONS CARD - BOTONES
   ============================================================ */
.actions-card {
    background: var(--surface);
    border-color: var(--line);
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    padding: 1rem 1.25rem;
}

.btn-actions {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    padding: 0.8rem 1.5rem;
    border: none;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all var(--transition);
    font-family: inherit;
    width: 100%;
}

.btn-actions:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-actions--primary {
    background: linear-gradient(135deg, var(--brand), var(--brand-dark));
    color: var(--white);
    box-shadow: 0 4px 15px rgba(200, 30, 58, 0.3);
}

.btn-actions--primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(200, 30, 58, 0.4);
}

.btn-actions--secondary {
    background: var(--white);
    color: var(--ink-soft);
    border: 1.5px solid var(--line);
}

.btn-actions--secondary:hover:not(:disabled) {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
    transform: translateY(-2px);
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 1024px) {
    .nuevo-contenido-page {
        padding: 1rem 1rem 2rem;
    }
}

@media (max-width: 768px) {
    .nuevo-contenido-page {
        padding: 0.75rem 0.75rem 1.5rem;
    }

    .form-card {
        padding: 1.25rem;
    }

    .content-type-grid {
        grid-template-columns: 1fr 1fr;
    }

    .actions-card {
        flex-direction: row;
    }

    .btn-actions {
        width: 50%;
    }
}

@media (max-width: 480px) {
    .content-type-grid {
        grid-template-columns: 1fr;
    }

    .visibilidad-options {
        flex-direction: column;
    }

    .premium-toggle {
        flex-wrap: wrap;
    }

    .actions-card {
        flex-direction: column;
    }

    .btn-actions {
        width: 100%;
    }

    .archivos-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .field-grid {
        grid-template-columns: 1fr;
    }
}
</style>