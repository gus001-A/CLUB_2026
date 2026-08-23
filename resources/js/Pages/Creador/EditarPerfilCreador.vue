<template>

    <Head title="Editar perfil de creador" />

    <AppLayout active-nav="comunidad" :usuario="usuario">
        <div class="editar-perfil-page">
            <!-- Título -->
            <div class="page-heading">
                <h1>✏️ Editar perfil de creador</h1>
                <p>Actualiza tu información, fotos y configuración de monetización.</p>
            </div>

            <div class="content-grid">
                <!-- MAIN COLUMN -->
                <div class="main-column">
                    <!-- Formulario -->
                    <section class="form-card">
                        <h2>
                            <i class="pi pi-user-edit"></i>
                            Información básica
                        </h2>

                        <form @submit.prevent="guardarPerfil">
                            <div class="field-grid">
                                <div class="field">
                                    <label>Nombre para mostrar <span class="required">*</span></label>
                                    <input type="text" v-model="form.nombre" class="form-input"
                                        :class="{ 'input-error': errores.nombre }" placeholder="Tu nombre" />
                                    <span v-if="errores.nombre" class="error-message">
                                        <i class="pi pi-exclamation-circle"></i> {{ errores.nombre }}
                                    </span>
                                </div>

                                <div class="field">
                                    <label>Estado de verificación</label>
                                    <span class="verification-status" :class="`status-${perfil.estado_verificacion}`">
                                        <i class="pi" :class="getVerificationIcon(perfil.estado_verificacion)"></i>
                                        {{ getVerificationLabel(perfil.estado_verificacion) }}
                                    </span>
                                </div>
                            </div>

                            <div class="field">
                                <label>Biografía</label>
                                <textarea v-model="form.biografia" rows="4" class="form-textarea"
                                    placeholder="Cuéntale a tu comunidad sobre ti..." :maxlength="500"></textarea>
                                <span class="char-count">{{ form.biografia.length }}/500</span>
                            </div>

                            <div class="field">
                                <label>Categorías / Intereses</label>
                                <div class="categoria-selector">
                                    <div class="tag-input">
                                        <span v-for="cat in form.categorias" :key="cat" class="tag-chip">
                                            {{ cat }}
                                            <button @click="toggleCategoria(cat)" type="button">
                                                <i class="pi pi-times"></i>
                                            </button>
                                        </span>
                                        <span v-if="!form.categorias.length" class="tag-placeholder">
                                            Selecciona tus categorías
                                        </span>
                                    </div>
                                    <div class="categoria-options">
                                        <button v-for="cat in categoriasDisponibles" :key="cat" class="categoria-option"
                                            :class="{ selected: form.categorias.includes(cat) }"
                                            @click="toggleCategoria(cat)" type="button">
                                            {{ cat }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="field-grid">
                                <div class="field">
                                    <label>Tipo de contenido</label>
                                    <div class="content-type-row">
                                        <button class="content-type-pill"
                                            :class="{ selected: form.tipo_contenido === 'fotos' }"
                                            @click="form.tipo_contenido = 'fotos'" type="button">
                                            <i class="pi pi-image"></i> Fotos
                                        </button>
                                        <button class="content-type-pill"
                                            :class="{ selected: form.tipo_contenido === 'videos' }"
                                            @click="form.tipo_contenido = 'videos'" type="button">
                                            <i class="pi pi-video"></i> Videos
                                        </button>
                                        <button class="content-type-pill"
                                            :class="{ selected: form.tipo_contenido === 'exclusivo' }"
                                            @click="form.tipo_contenido = 'exclusivo'" type="button">
                                            <i class="pi pi-lock"></i> Exclusivo
                                        </button>
                                    </div>
                                </div>

                                <div class="field">
                                    <label>Perfil premium</label>
                                    <div class="premium-toggle">
                                        <span class="premium-toggle__icon"><i class="pi pi-crown"></i></span>
                                        <div class="premium-toggle__text">
                                            <strong>Activar perfil premium</strong>
                                            <span>Destaca tu perfil y obtén más visibilidad</span>
                                        </div>
                                        <label class="toggle-switch">
                                            <input type="checkbox" v-model="form.es_premium" />
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN DE MONETIZACIÓN -->
                            <div class="monetizacion-section">
                                <h3 class="section-title">
                                    <i class="pi pi-dollar" style="color: var(--brand);"></i>
                                    Configuración de monetización
                                </h3>
                                <p class="section-desc">Define cuánto vas a cobrar por tu contenido.</p>

                                <div class="monetizacion-grid">
                                    <div v-for="modelo in modelosIngreso" :key="modelo.key" class="monetizacion-card"
                                        :class="{ selected: monetizacion.modelo === modelo.key }"
                                        @click="seleccionarModelo(modelo.key)">
                                        <div class="monetizacion-card__radio"
                                            :class="{ checked: monetizacion.modelo === modelo.key }"></div>
                                        <i class="pi" :class="modelo.icon"></i>
                                        <strong>{{ modelo.titulo }}</strong>
                                        <p>{{ modelo.desc }}</p>
                                        <div class="monetizacion-card__price">
                                            <span class="price">${{ modelo.precio }}</span>
                                            <span class="unit">MXN</span>
                                        </div>
                                        <span v-if="modelo.popular" class="popular-badge">Popular</span>
                                    </div>
                                </div>

                                <!-- Precio personalizado para exclusivo -->
                                <div v-if="monetizacion.modelo === 'exclusivo'" class="custom-price-field">
                                    <label>Precio personalizado (MXN)</label>
                                    <input type="number" v-model="monetizacion.precio_personalizado" step="0.01"
                                        min="0.99" max="999.99" class="form-input" placeholder="Ej: 199.99" />
                                    <span class="custom-price-hint">Define tu propio precio para contenido
                                        exclusivo</span>
                                </div>

                                <!-- Frecuencia de pago -->
                                <div class="frecuencia-field">
                                    <label>Frecuencia de pago</label>
                                    <div class="frecuencia-options">
                                        <button v-for="f in frecuencias" :key="f.value" class="frecuencia-pill"
                                            :class="{ selected: monetizacion.frecuencia === f.value }"
                                            @click="monetizacion.frecuencia = f.value" type="button">
                                            {{ f.label }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>

                <!-- SIDEBAR -->
                <aside class="sidebar-column">
                    <!-- Fotos de perfil -->
                    <div class="sidebar-card">
                        <h3><i class="pi pi-images"></i> Fotos de perfil</h3>
                        <div class="fotos-grid">
                            <div v-for="foto in fotosPerfil" :key="foto.id" class="foto-item"
                                :class="{ 'foto-item--principal': foto.es_principal }">
                                <img :src="foto.url" :alt="'Foto' + foto.id" />
                                <div class="foto-item__actions">
                                    <button v-if="!foto.es_principal" class="foto-item__btn foto-item__btn--principal"
                                        @click="setFotoPrincipal(foto.id)" title="Establecer como principal">
                                        <i class="pi pi-star"></i>
                                    </button>
                                    <button class="foto-item__btn foto-item__btn--delete" @click="eliminarFoto(foto.id)"
                                        title="Eliminar foto">
                                        <i class="pi pi-trash"></i>
                                    </button>
                                </div>
                                <span v-if="foto.es_principal" class="foto-item__badge">⭐ Principal</span>
                            </div>
                            <label class="foto-item foto-item--add">
                                <i class="pi pi-plus"></i>
                                <span>Agregar</span>
                                <input type="file" accept="image/*" @change="subirFotoPerfil" hidden />
                            </label>
                        </div>
                    </div>

                    <!-- Ganancias rápidas -->
                    <div class="sidebar-card sidebar-card--earnings">
                        <h3><i class="pi pi-wallet"></i> Tus ganancias</h3>
                        <div class="earnings-item">
                            <span><i class="pi pi-users"></i> Suscriptores</span>
                            <strong>{{ suscriptoresEstimados }}</strong>
                        </div>
                        <div class="earnings-item">
                            <span><i class="pi pi-dollar"></i> Ingreso mensual</span>
                            <strong class="accent">${{ ingresoMensualEstimado }} MXN</strong>
                        </div>
                        <div class="earnings-item">
                            <span><i class="pi pi-tag"></i> Precio actual</span>
                            <strong>${{ precioMensual }} MXN</strong>
                        </div>
                        <div class="earnings-divider"></div>
                        <div class="earnings-item earnings-item--frequency">
                            <span><i class="pi pi-calendar"></i> Frecuencia</span>
                            <strong>{{ monetizacion.frecuencia }}</strong>
                        </div>
                    </div>

                    <!-- ✅ BOTONES DE ACCIÓN (AHORA EN EL SIDEBAR) -->
                    <div class="sidebar-card sidebar-card--actions">
                        <button type="button" class="btn btn--secondary btn--full" @click="volver">
                            <i class="pi pi-arrow-left"></i> Volver
                        </button>
                        <button type="submit" class="btn btn--primary btn--full" :disabled="isSubmitting"
                            @click="guardarPerfil">
                            <i class="pi" :class="isSubmitting ? 'pi-spin pi-spinner' : 'pi-save'"></i>
                            {{ isSubmitting ? 'Guardando...' : 'Guardar cambios' }}
                        </button>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    usuario: {
        type: Object,
        default: () => ({})
    },
    perfil: {
        type: Object,
        default: () => ({
            nombre: '',
            avatar: '/images/shared/avatar-default.jpg',
            biografia: '',
            categorias: [],
            es_premium: false,
            tipo_contenido: 'fotos',
            foto_portada: null,
            estado_verificacion: 'pendiente'
        })
    },
    fotosPerfil: {
        type: Array,
        default: () => []
    },
    categoriasDisponibles: {
        type: Array,
        default: () => []
    },
    configuracionMonetizacion: {
        type: Object,
        default: null
    },
    footerColumnas: {
        type: Object,
        default: () => ({})
    }
});

const isSubmitting = ref(false);
const subiendoFoto = ref(false);
const errores = reactive({});

// Modelos de ingreso
const modelosIngreso = [
    { key: 'suscripcion', icon: 'pi-refresh', titulo: 'Suscripción mensual', precio: '199.99', desc: 'Acceso continuo a tu contenido premium', popular: true },
    { key: 'fotos', icon: 'pi-image', titulo: 'Pago por foto', precio: '299.99', desc: 'Gana por cada foto premium que compartas' },
    { key: 'videos', icon: 'pi-play', titulo: 'Pago por video', precio: '499.99', desc: 'Monetiza tus videos exclusivos' },
    { key: 'exclusivo', icon: 'pi-lock', titulo: 'Contenido exclusivo', precio: '0.00', desc: 'Define tu propio precio personalizado' },
];

const frecuencias = [
    { value: 'Mensual', label: 'Mensual' },
    { value: 'Trimestral', label: 'Trimestral' },
    { value: 'Semestral', label: 'Semestral' },
    { value: 'Anual', label: 'Anual' },
];

// Estado de monetización
const configGuardada = props.configuracionMonetizacion || {};

const monetizacion = reactive({
    modelo: configGuardada?.modelo_ingresos || 'suscripcion',
    precio_personalizado: configGuardada?.precio_personalizado || null,
    frecuencia: configGuardada?.frecuencia_pago || 'Mensual',
});

// Formulario principal
const form = reactive({
    nombre: props.perfil?.nombre || '',
    biografia: props.perfil?.biografia || '',
    categorias: props.perfil?.categorias || [],
    tipo_contenido: props.perfil?.tipo_contenido || 'fotos',
    es_premium: props.perfil?.es_premium || false,
});

const fotosPerfil = ref(props.fotosPerfil || []);

// Computed para precios
const precioBase = computed(() => {
    const modelo = modelosIngreso.find(m => m.key === monetizacion.modelo);
    if (monetizacion.modelo === 'exclusivo' && monetizacion.precio_personalizado) {
        return parseFloat(monetizacion.precio_personalizado) || 0;
    }
    return modelo ? parseFloat(modelo.precio) || 0 : 0;
});

const precioMensual = computed(() => {
    const precio = precioBase.value;
    if (monetizacion.frecuencia === 'Trimestral') return (precio * 3 * 0.9).toFixed(2);
    if (monetizacion.frecuencia === 'Semestral') return (precio * 6 * 0.85).toFixed(2);
    if (monetizacion.frecuencia === 'Anual') return (precio * 12 * 0.8).toFixed(2);
    return precio.toFixed(2);
});

const suscriptoresEstimados = 2500;
const ingresoMensualEstimado = computed(() => {
    return (suscriptoresEstimados * parseFloat(precioMensual.value)).toFixed(0);
});

// Funciones
function seleccionarModelo(key) {
    monetizacion.modelo = key;
    if (key !== 'exclusivo') {
        monetizacion.precio_personalizado = null;
    }
}

function toggleCategoria(cat) {
    const index = form.categorias.indexOf(cat);
    if (index > -1) {
        form.categorias.splice(index, 1);
    } else if (form.categorias.length < 8) {
        form.categorias.push(cat);
    }
}

function getVerificationIcon(estado) {
    const icons = {
        'aprobado': 'pi-check-circle',
        'pendiente': 'pi-clock',
        'rechazado': 'pi-times-circle',
        'default': 'pi-question-circle'
    };
    return icons[estado] || icons.default;
}

function getVerificationLabel(estado) {
    const labels = {
        'aprobado': 'Verificado',
        'pendiente': 'Pendiente de revisión',
        'rechazado': 'Rechazado',
        'default': 'Sin verificar'
    };
    return labels[estado] || labels.default;
}

function guardarPerfil() {
    isSubmitting.value = true;
    errores.nombre = null;

    if (!form.nombre.trim()) {
        errores.nombre = 'El nombre es requerido';
        isSubmitting.value = false;
        return;
    }

    const data = {
        ...form,
        monetizacion: {
            modelo: monetizacion.modelo,
            precio_personalizado: monetizacion.precio_personalizado,
            frecuencia: monetizacion.frecuencia,
        }
    };

    router.post(route('creador.editar-perfil.update'), data, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isSubmitting.value = false;
            window.showSuccessToast('Perfil actualizado correctamente');
        },
        onError: (errors) => {
            isSubmitting.value = false;
            if (errors && typeof errors === 'object') {
                Object.keys(errors).forEach(key => {
                    errores[key] = errors[key];
                });
                const firstError = Object.values(errors)[0];
                if (firstError) {
                    window.showErrorToast(Array.isArray(firstError) ? firstError[0] : firstError);
                }
            }
        }
    });
}

function volver() {
    router.get(route('creador.perfil'));
}

function subirFotoPerfil(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
        window.showErrorToast('Formato no válido, solo se permiten imágenes');
        event.target.value = '';
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        window.showErrorToast('La imagen no debe superar los 5MB');
        event.target.value = '';
        return;
    }

    subiendoFoto.value = true;
    const formData = new FormData();
    formData.append('foto', file);

    router.post(route('creador.foto-perfil.subir'), formData, {
        preserveScroll: true,
        headers: {
            'Content-Type': 'multipart/form-data'
        },
        onSuccess: (page) => {
            subiendoFoto.value = false;
            if (page.props.flash?.foto) {
                fotosPerfil.value.push(page.props.flash.foto);
                window.showSuccessToast('Foto subida correctamente');
            }
        },
        onError: (errors) => {
            subiendoFoto.value = false;
            const firstError = Object.values(errors)[0];
            if (firstError) {
                window.showErrorToast(Array.isArray(firstError) ? firstError[0] : firstError);
            }
        }
    });
    event.target.value = '';
}

function setFotoPrincipal(fotoId) {
    router.post(route('creador.foto-principal.set'), { foto_id: fotoId }, {
        preserveScroll: true,
        onSuccess: () => {
            fotosPerfil.value = fotosPerfil.value.map(f => ({
                ...f,
                es_principal: f.id === fotoId
            }));
            window.showSuccessToast('Foto principal actualizada');
        },
        onError: () => {
            window.showErrorToast('Error al actualizar foto principal');
        }
    });
}

function eliminarFoto(fotoId) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta foto?')) return;

    router.delete(route('creador.foto-perfil.eliminar', fotoId), {
        preserveScroll: true,
        onSuccess: () => {
            fotosPerfil.value = fotosPerfil.value.filter(f => f.id !== fotoId);
            window.showSuccessToast('Foto eliminada');
        },
        onError: () => {
            window.showErrorToast('Error al eliminar la foto');
        }
    });
}
</script>

<style scoped>
.editar-perfil-page {
    --brand: #C81E3A;
    --brand-dark: #A6152D;
    --brand-soft: #FBEAEC;
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

.page-heading {
    margin-bottom: 1.5rem;
}

.page-heading h1 {
    font-size: 1.6rem;
    margin: 0 0 0.2rem;
    font-weight: 700;
}

.page-heading p {
    font-size: 0.85rem;
    color: var(--muted);
    margin: 0;
}

.content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1100px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

.main-column {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* ============================================================
   FORM CARD
   ============================================================ */
.form-card {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 1.75rem;
    box-shadow: var(--shadow);
}

.form-card:hover {
    box-shadow: var(--shadow-hover);
}

.form-card h2 {
    font-size: 1.1rem;
    margin: 0 0 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 700;
}

.form-card h2 i {
    color: var(--brand);
}

/* ============================================================
   FIELDS
   ============================================================ */
.field-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.25rem;
}

@media (max-width: 700px) {
    .field-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.field label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ink-soft);
}

.field label .required {
    color: #EF4444;
    margin-left: 0.2rem;
}

.form-input,
.form-textarea {
    width: 100%;
    border-radius: var(--radius-sm);
    border: 1.5px solid var(--line);
    transition: all var(--transition);
    padding: 0.6rem 0.9rem;
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
    min-height: 80px;
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
   VERIFICATION STATUS
   ============================================================ */
.verification-status {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 1rem;
    border-radius: var(--radius-full);
    font-size: 0.8rem;
    font-weight: 600;
    width: fit-content;
}

.status-aprobado {
    background: #ECFDF5;
    color: #10B981;
}

.status-pendiente {
    background: #FFF8E1;
    color: #D69E2E;
}

.status-rechazado {
    background: #FEF2F2;
    color: #EF4444;
}

/* ============================================================
   CATEGORIAS
   ============================================================ */
.categoria-selector {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.tag-input {
    border: 1.5px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.4rem 2rem 0.4rem 0.6rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    min-height: 44px;
    align-items: center;
    position: relative;
    transition: all var(--transition);
    background: var(--white);
}

.tag-input:focus-within {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
}

.tag-chip {
    background: var(--brand-soft);
    color: var(--brand);
    border-radius: 6px;
    padding: 0.25rem 0.6rem;
    font-size: 0.78rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.4rem;
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

.tag-placeholder {
    color: var(--muted-light);
    font-size: 0.85rem;
}

.categoria-options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}

.categoria-option {
    padding: 0.3rem 0.8rem;
    border: 1.5px solid var(--line);
    border-radius: 20px;
    background: var(--white);
    font-size: 0.78rem;
    cursor: pointer;
    transition: all var(--transition);
    color: var(--ink-soft);
}

.categoria-option:hover {
    border-color: var(--brand);
    color: var(--brand);
}

.categoria-option.selected {
    background: var(--brand);
    border-color: var(--brand);
    color: var(--white);
}

/* ============================================================
   CONTENT TYPE
   ============================================================ */
.content-type-row {
    display: flex;
    gap: 0.6rem;
}

.content-type-pill {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    border: 1.5px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--white);
    padding: 0.6rem 0.8rem;
    cursor: pointer;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--ink-soft);
    transition: all var(--transition);
}

.content-type-pill:hover {
    border-color: var(--brand);
    color: var(--brand);
}

.content-type-pill.selected {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
}

/* ============================================================
   PREMIUM TOGGLE
   ============================================================ */
.premium-toggle {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.75rem 1rem;
    background: var(--surface);
}

.premium-toggle__icon {
    width: 38px;
    height: 38px;
    border-radius: var(--radius-sm);
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.1rem;
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

/* ============================================================
   TOGGLE SWITCH
   ============================================================ */
.toggle-switch {
    position: relative;
    width: 44px;
    height: 24px;
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
    width: 18px;
    height: 18px;
    left: 3px;
    top: 3px;
    background: var(--white);
    border-radius: 50%;
    transition: all var(--transition);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
}

.toggle-switch input:checked+.toggle-slider::before {
    transform: translateX(20px);
}

/* ============================================================
   MONETIZACIÓN
   ============================================================ */
.monetizacion-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--line);
}

.section-title {
    font-size: 1rem;
    margin: 0 0 0.2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 700;
}

.section-desc {
    font-size: 0.82rem;
    color: var(--muted);
    margin: 0 0 1.25rem;
}

.monetizacion-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

@media (max-width: 900px) {
    .monetizacion-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 500px) {
    .monetizacion-grid {
        grid-template-columns: 1fr;
    }
}

.monetizacion-card {
    border: 2px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 1rem;
    background: var(--white);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    position: relative;
    transition: all var(--transition);
}

.monetizacion-card:hover {
    border-color: var(--brand);
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.monetizacion-card.selected {
    border-color: var(--brand);
    background: var(--brand-soft);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15), var(--shadow);
}

.monetizacion-card__radio {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 2px solid var(--line);
    position: absolute;
    top: 0.7rem;
    right: 0.7rem;
    transition: all var(--transition);
}

.monetizacion-card__radio.checked {
    border-color: var(--brand);
    background: var(--brand);
    box-shadow: inset 0 0 0 3px var(--white);
}

.popular-badge {
    position: absolute;
    top: -8px;
    right: 12px;
    background: #D69E2E;
    color: white;
    font-size: 0.55rem;
    font-weight: 700;
    padding: 0.1rem 0.6rem;
    border-radius: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.monetizacion-card i {
    font-size: 1.1rem;
    color: var(--ink-soft);
}

.monetizacion-card.selected i {
    color: var(--brand);
}

.monetizacion-card strong {
    font-size: 0.85rem;
    padding-right: 1.5rem;
}

.monetizacion-card p {
    font-size: 0.72rem;
    color: var(--muted);
    margin: 0;
    line-height: 1.4;
    min-height: 2.8em;
}

.monetizacion-card__price {
    display: flex;
    align-items: baseline;
    gap: 0.3rem;
    margin-top: 0.3rem;
}

.monetizacion-card__price .price {
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--ink);
}

.monetizacion-card__price .unit {
    font-size: 0.65rem;
    color: var(--muted);
}

/* ============================================================
   PRECIO PERSONALIZADO
   ============================================================ */
.custom-price-field {
    margin-top: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: var(--surface);
    border-radius: var(--radius-sm);
    border: 1px solid var(--line);
    flex-wrap: wrap;
}

.custom-price-field label {
    font-weight: 600;
    color: var(--ink-soft);
    white-space: nowrap;
}

.custom-price-field .form-input {
    max-width: 150px;
}

.custom-price-hint {
    font-size: 0.7rem;
    color: var(--muted);
}

/* ============================================================
   FRECUENCIA
   ============================================================ */
.frecuencia-field {
    margin-top: 1.25rem;
}

.frecuencia-field label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--ink-soft);
    display: block;
    margin-bottom: 0.6rem;
}

.frecuencia-options {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.6rem;
}

@media (max-width: 600px) {
    .frecuencia-options {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 400px) {
    .frecuencia-options {
        grid-template-columns: 1fr;
    }
}

.frecuencia-pill {
    padding: 0.6rem 0.8rem;
    border: 2px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--white);
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--ink-soft);
    cursor: pointer;
    transition: all var(--transition);
    text-align: center;
}

.frecuencia-pill:hover {
    border-color: var(--brand);
    color: var(--brand);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.frecuencia-pill.selected {
    border-color: var(--brand);
    color: var(--white);
    background: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15), var(--shadow);
}

.frecuencia-pill.selected:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
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

.sidebar-card h3 {
    font-size: 1rem;
    margin: 0 0 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-weight: 700;
}

.sidebar-card h3 i {
    font-size: 0.9rem;
    color: var(--brand);
}

/* ============================================================
   FOTOS PERFIL
   ============================================================ */
.fotos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.6rem;
}

.foto-item {
    position: relative;
    aspect-ratio: 1/1;
    border-radius: var(--radius-sm);
    overflow: hidden;
    border: 2px solid var(--line);
    background: var(--surface);
    transition: all var(--transition);
}

.foto-item:hover {
    border-color: var(--brand);
}

.foto-item--principal {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
}

.foto-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.foto-item__badge {
    position: absolute;
    top: 4px;
    left: 4px;
    background: var(--brand);
    color: var(--white);
    font-size: 0.55rem;
    font-weight: 700;
    padding: 0.1rem 0.5rem;
    border-radius: var(--radius-full);
}

.foto-item__actions {
    position: absolute;
    bottom: 4px;
    right: 4px;
    display: flex;
    gap: 0.3rem;
    opacity: 0;
    transition: all var(--transition);
}

.foto-item:hover .foto-item__actions {
    opacity: 1;
}

.foto-item__btn {
    border: none;
    border-radius: 50%;
    width: 26px;
    height: 26px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition);
    font-size: 0.65rem;
}

.foto-item__btn--principal {
    background: #FBBF24;
    color: var(--white);
}

.foto-item__btn--principal:hover {
    transform: scale(1.1);
    background: #F59E0B;
}

.foto-item__btn--delete {
    background: rgba(239, 68, 68, 0.9);
    color: var(--white);
}

.foto-item__btn--delete:hover {
    transform: scale(1.1);
    background: #EF4444;
}

.foto-item--add {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border: 2px dashed var(--line);
    cursor: pointer;
    transition: all var(--transition);
    color: var(--muted-light);
    gap: 0.2rem;
}

.foto-item--add:hover {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
}

.foto-item--add i {
    font-size: 1.2rem;
}

.foto-item--add span {
    font-size: 0.65rem;
    font-weight: 600;
}

.foto-item--add input {
    display: none;
}

/* ============================================================
   EARNINGS SIDEBAR
   ============================================================ */
.sidebar-card--earnings {
    border-color: #FBEAEC;
}

.sidebar-card--earnings h3 i {
    color: #F59E0B;
}

.earnings-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    font-size: 0.82rem;
    color: var(--ink-soft);
    border-bottom: 1px solid var(--line);
}

.earnings-item:last-child {
    border-bottom: none;
}

.earnings-item strong {
    color: var(--ink);
}

.earnings-item .accent {
    color: var(--brand);
    font-size: 1rem;
}

.earnings-item i {
    color: var(--muted-light);
    width: 1.2rem;
    font-size: 0.75rem;
}

.earnings-divider {
    height: 1px;
    background: var(--line);
    margin: 0.25rem 0;
}

.earnings-item--frequency {
    padding-top: 0.5rem;
    font-weight: 600;
}

.earnings-item--frequency strong {
    color: var(--brand);
}

/* ============================================================
   ACCIONES - BOTONES EN SIDEBAR
   ============================================================ */
.sidebar-card--actions {
    background: var(--surface);
    border-color: var(--line);
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.7rem 1.4rem;
    border: none;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all var(--transition);
    font-family: inherit;
    width: 100%;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn--full {
    width: 100%;
}

.btn--primary {
    background: var(--brand);
    color: var(--white);
}

.btn--primary:hover:not(:disabled) {
    background: var(--brand-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.btn--secondary {
    background: var(--white);
    color: var(--ink-soft);
    border: 1.5px solid var(--line);
}

.btn--secondary:hover:not(:disabled) {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 1024px) {
    .editar-perfil-page {
        padding: 1rem 1rem 2rem;
    }
}

@media (max-width: 768px) {
    .editar-perfil-page {
        padding: 0.75rem 0.75rem 1.5rem;
    }

    .form-card {
        padding: 1.25rem;
    }

    .fotos-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .custom-price-field {
        flex-direction: column;
        align-items: stretch;
    }

    .custom-price-field .form-input {
        max-width: 100%;
    }

    .sidebar-card--actions {
        flex-direction: row;
    }

    .sidebar-card--actions .btn {
        width: 50%;
    }
}

@media (max-width: 480px) {
    .content-type-row {
        flex-direction: column;
    }

    .content-type-pill {
        justify-content: center;
    }

    .premium-toggle {
        flex-wrap: wrap;
    }

    .fotos-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .monetizacion-grid {
        grid-template-columns: 1fr;
    }

    .frecuencia-options {
        grid-template-columns: 1fr 1fr;
    }

    .sidebar-card--actions {
        flex-direction: column;
    }

    .sidebar-card--actions .btn {
        width: 100%;
    }
}
</style>