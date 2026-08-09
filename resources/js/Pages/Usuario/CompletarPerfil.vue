<script setup>
import { computed, reactive, ref, onMounted, watch, onBeforeMount, onUpdated, onBeforeUpdate } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

/* ---------------------------------------------------------------
 * Props recibidas del controlador
 * --------------------------------------------------------------- */
const props = defineProps({
    user: {
        type: Object,
        default: () => ({
            id: null,
            nickname: '',
            nombre: '',
            apodo: '',
            email: '',
            telefono: '',
            ciudad: '',
            edad: null,
            fecha_nacimiento: null,
            estado: 'incompleto',
            email_verificado_en: null,
            avatar: '/images/shared/avatar-default.jpg',
            foto_principal: null,
        })
    },
    perfil: {
        type: Object,
        default: () => ({
            id: null,
            tipo: 'personal',
            descripcion: '',
            biografia: '',
            intereses: [],
            pasatiempos: [],
            fotos: [],
            privacidad_fotos: 'todos',
            esta_verificado: false,
            puntuacion_compatibilidad: 0,
            ubicacion_ciudad: '',
            metadatos: {},
            pareja: null,
            perfil_completo: false,
            usuario_verificado: false,
        })
    },
    fechaNacimiento: {
        type: String,
        default: null
    }
});

/* ---------------------------------------------------------------
 * Estado del formulario
 * --------------------------------------------------------------- */
const form = useForm({
    nickname: props.user.nickname || '',
    edad: props.user.edad || '',
    ciudad: props.user.ciudad || '',
    ocupacion: props.perfil?.metadatos?.ocupacion || '',
    bio: props.perfil?.descripcion || props.perfil?.biografia || '',
    tipoPerfil: props.perfil?.tipo || 'personal',
    pareja: {
        nombre1: props.perfil?.metadatos?.pareja?.nombre1 || '',
        edad1: props.perfil?.metadatos?.pareja?.edad1 || '',
        nombre2: props.perfil?.metadatos?.pareja?.nombre2 || '',
        edad2: props.perfil?.metadatos?.pareja?.edad2 || '',
        visibleParaAmbos: props.perfil?.metadatos?.pareja?.visibleParaAmbos ?? true,
    },
    intereses: props.perfil?.intereses || [],
    buscando: props.perfil?.pasatiempos || [],
    visibilidadFotos: props.perfil?.privacidad_fotos || 'todos',
    fotos: [],
});

const bioMax = 300;
const bioLength = computed(() => form.bio.length);
const isFormValid = ref(false);
const isSaving = ref(false);

/* ---------------------------------------------------------------
 * Intereses
 * --------------------------------------------------------------- */
const interesesOptions = [
    { label: 'Viajes', selected: false, icon: 'pi pi-compass' },
    { label: 'Fiestas privadas', selected: false, icon: 'pi pi-calendar' },
    { label: 'Cenas', selected: false, icon: 'pi pi-cutlery' },
    { label: 'Conexiones reales', selected: false, icon: 'pi pi-heart' },
    { label: 'Eventos VIP', selected: false, icon: 'pi pi-star' },
    { label: 'Música', selected: false, icon: 'pi pi-volume-up' },
    { label: 'Wellness', selected: false, icon: 'pi pi-heart-fill' },
    { label: 'Streaming', selected: false, icon: 'pi pi-play' },
    { label: 'Socializar', selected: false, icon: 'pi pi-users' },
];

const buscandoOptions = [
    { label: 'Conocer gente nueva', selected: false, icon: 'pi pi-user-plus' },
    { label: 'Amistad con beneficios', selected: false, icon: 'pi pi-handshake' },
    { label: 'Relaciones discretas', selected: false, icon: 'pi pi-lock' },
    { label: 'Explorar fantasías', selected: false, icon: 'pi pi-star-fill' },
    { label: 'Pareja liberal', selected: false, icon: 'pi pi-heart-fill' },
    { label: 'Algo serio', selected: false, icon: 'pi pi-heart' },
];

const intereses = reactive(interesesOptions.map(i => ({
    ...i,
    selected: form.intereses.includes(i.label)
})));

const buscando = reactive(buscandoOptions.map(i => ({
    ...i,
    selected: form.buscando.includes(i.label)
})));

function toggle(list, item) {
    item.selected = !item.selected;
    validarFormulario();
}

const interesesSeleccionados = computed(() => {
    return [...intereses, ...buscando].filter((i) => i.selected);
});

const interesesCount = computed(() => {
    return intereses.filter(i => i.selected).length;
});

const buscandoCount = computed(() => {
    return buscando.filter(i => i.selected).length;
});

/* ---------------------------------------------------------------
 * Fotos - Función para obtener URL correcta
 * --------------------------------------------------------------- */
function getFotoUrl(foto) {
    if (!foto) {
        return '/images/shared/avatar-default.jpg';
    }
    
    if (foto.url) {
        if (foto.url.startsWith('http://') || foto.url.startsWith('https://')) {
            return foto.url;
        }
        if (foto.url.startsWith('/storage/') || foto.url.startsWith('/images/')) {
            return foto.url;
        }
        return '/storage/' + foto.url.replace(/^\/+/, '');
    }
    
    if (foto.ruta_foto) {
        return '/storage/' + foto.ruta_foto.replace(/^\/+/, '');
    }
    
    if (typeof foto === 'string') {
        if (foto.startsWith('http://') || foto.startsWith('https://')) {
            return foto;
        }
        if (foto.startsWith('/storage/') || foto.startsWith('/images/')) {
            return foto;
        }
        return '/storage/' + foto.replace(/^\/+/, '');
    }
    
    return '/images/shared/avatar-default.jpg';
}

/* ---------------------------------------------------------------
 * Fotos
 * --------------------------------------------------------------- */
const fotos = reactive([]);

onBeforeMount(() => {
    // Antes de montar el componente
});

onMounted(() => {
    if (props.perfil?.fotos && props.perfil.fotos.length > 0) {
        props.perfil.fotos.forEach((f) => {
            const url = getFotoUrl(f);
            fotos.push({
                url: url,
                file: null,
                principal: f.principal || f.es_principal || false,
                existente: true,
                ruta_foto: f.ruta_foto || '',
                id: f.id || null
            });
        });
    }
    
    if (fotos.length === 0 && props.user?.foto_principal) {
        const url = getFotoUrl(props.user.foto_principal);
        fotos.push({
            url: url,
            file: null,
            principal: true,
            existente: true,
            ruta_foto: props.user.foto_principal,
            id: null
        });
    }
    
    if (fotos.length > 0 && !fotos.some(f => f.principal)) {
        fotos[0].principal = true;
    }
    
    validarFormulario();
});

onBeforeUpdate(() => {
    // Antes de actualizar el DOM
});

onUpdated(() => {
    // DOM actualizado
});

const maxFotos = 8;

function onFileSelected(event) {
    const files = Array.from(event.target.files || []);
    const disponibles = maxFotos - fotos.length;
    
    files.slice(0, disponibles).forEach((file) => {
        if (!file.type.startsWith('image/')) {
            if (window.showToast) {
                window.showToast({
                    type: 'error',
                    title: 'Formato no válido',
                    message: 'Solo se permiten archivos de imagen.',
                    duration: 3000,
                });
            }
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) {
            if (window.showToast) {
                window.showToast({
                    type: 'error',
                    title: 'Archivo muy grande',
                    message: 'La imagen no debe superar los 5MB.',
                    duration: 3000,
                });
            }
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            const nuevaFoto = { 
                url: e.target.result, 
                file: file,
                principal: fotos.length === 0,
                existente: false,
                ruta_foto: '',
                id: null
            };
            fotos.push(nuevaFoto);
            validarFormulario();
        };
        reader.onerror = (e) => {
            console.error('Error leyendo archivo:', e);
        };
        reader.readAsDataURL(file);
    });
    event.target.value = '';
}

function eliminarFoto(index) {
    fotos.splice(index, 1);
    if (fotos.length > 0 && !fotos.some(f => f.principal)) {
        fotos[0].principal = true;
    }
    validarFormulario();
}

function setPrincipal(index) {
    fotos.forEach((f, i) => f.principal = i === index);
}

function getFotoCountText() {
    const count = fotos.length;
    if (count === 0) return 'Sube tus mejores fotos';
    if (count === 1) return '1 foto subida';
    return `${count} fotos subidas`;
}

function getFotoPrincipal() {
    const principal = fotos.find(f => f.principal);
    if (principal) {
        return principal.url;
    }
    if (fotos.length > 0) {
        return fotos[0].url;
    }
    return props.user?.avatar || '/images/shared/avatar-default.jpg';
}

/* ---------------------------------------------------------------
 * VALIDACIONES
 * --------------------------------------------------------------- */
const erroresValidacion = ref([]);
const camposPendientes = ref([]);

function validarFormulario() {
    const errores = [];
    const pendientes = [];
    
    if (!form.nickname || form.nickname.trim() === '') {
        errores.push('El nombre o nickname es obligatorio.');
        pendientes.push('Nombre o nickname');
    }
    
    if (!form.edad || form.edad < 18) {
        errores.push('La edad es obligatoria y debe ser mayor de 18 años.');
        pendientes.push('Edad (mínimo 18)');
    }
    
    if (!form.ciudad || form.ciudad.trim() === '') {
        errores.push('La ciudad es obligatoria.');
        pendientes.push('Ciudad');
    }
    
    if (!form.bio || form.bio.trim().length < 10) {
        errores.push('La descripción es obligatoria y debe tener al menos 10 caracteres.');
        pendientes.push('Descripción (mínimo 10 caracteres)');
    }
    
    if (fotos.length === 0) {
        errores.push('Debes subir al menos una foto.');
        pendientes.push('Foto de perfil');
    }
    
    if (!form.visibilidadFotos) {
        errores.push('Debes seleccionar quién puede ver tus fotos.');
        pendientes.push('Visibilidad de fotos');
    }
    
    if (form.tipoPerfil === 'personal') {
        if (buscandoCount.value < 1) {
            errores.push('Debes seleccionar al menos una opción de "Qué estás buscando".');
            pendientes.push('Qué buscas (mínimo 1)');
        }
    } else if (form.tipoPerfil === 'pareja') {
        if (!form.pareja.nombre1 || form.pareja.nombre1.trim() === '') {
            errores.push('El nombre del integrante 1 es obligatorio.');
            pendientes.push('Nombre integrante 1');
        }
        if (!form.pareja.edad1 || form.pareja.edad1 < 18) {
            errores.push('La edad del integrante 1 es obligatoria y debe ser mayor de 18 años.');
            pendientes.push('Edad integrante 1');
        }
        if (!form.pareja.nombre2 || form.pareja.nombre2.trim() === '') {
            errores.push('El nombre del integrante 2 es obligatorio.');
            pendientes.push('Nombre integrante 2');
        }
        if (!form.pareja.edad2 || form.pareja.edad2 < 18) {
            errores.push('La edad del integrante 2 es obligatoria y debe ser mayor de 18 años.');
            pendientes.push('Edad integrante 2');
        }
    }
    
    erroresValidacion.value = errores;
    camposPendientes.value = pendientes;
    isFormValid.value = errores.length === 0;
    
    return errores.length === 0;
}

function mostrarErroresValidacion() {
    if (erroresValidacion.value.length === 0) return;
    
    if (window.showToast) {
        window.showToast({
            type: 'error',
            title: `${erroresValidacion.value.length} campo${erroresValidacion.value.length > 1 ? 's' : ''} pendiente${erroresValidacion.value.length > 1 ? 's' : ''}`,
            message: erroresValidacion.value[0],
            duration: 5000,
        });
    }
}

/* ---------------------------------------------------------------
 * Progreso y consejos
 * --------------------------------------------------------------- */
const porcentajeCompletado = computed(() => {
    let puntos = 0;
    const total = 6;
    if (form.nickname && form.edad && form.ciudad) puntos++;
    if (form.bio.length >= 10) puntos++;
    if (form.tipoPerfil) puntos++;
    if (fotos.length >= 1) puntos++;
    if (form.tipoPerfil === 'personal' ? buscandoCount.value >= 1 : 
        (form.pareja.nombre1 && form.pareja.edad1 && form.pareja.nombre2 && form.pareja.edad2)) puntos++;
    if (form.visibilidadFotos) puntos++;
    return Math.round((puntos / total) * 100);
});

const circunferencia = 2 * Math.PI * 34;
const progresoOffset = computed(
    () => circunferencia - (Math.min(porcentajeCompletado.value, 100) / 100) * circunferencia
);

const consejos = computed(() => [
    {
        titulo: 'Añade al menos 1 foto',
        detalle: `Tienes ${fotos.length}/8 fotos`,
        ok: fotos.length >= 1,
    },
    {
        titulo: 'Escribe una descripción',
        detalle: 'Mínimo 10 caracteres',
        ok: form.bio.length >= 10,
    },
    {
        titulo: 'Define visibilidad de fotos',
        detalle: 'Selecciona quién puede verlas',
        ok: !!form.visibilidadFotos,
    },
    {
        titulo: 'Datos de pareja completos',
        detalle: form.tipoPerfil === 'pareja' ? 'Nombres y edades de ambos' : 'No aplica',
        ok: form.tipoPerfil !== 'pareja' || (form.pareja.nombre1 && form.pareja.edad1 && form.pareja.nombre2 && form.pareja.edad2),
    },
    {
        titulo: 'Selecciona qué buscas',
        detalle: form.tipoPerfil === 'personal' ? 'Al menos 1 opción' : 'No aplica',
        ok: form.tipoPerfil !== 'personal' || buscandoCount.value >= 1,
    },
]);

const beneficios = [
    { icon: 'pi pi-shield', titulo: 'Más confianza', desc: 'Los perfiles completos generan más confianza en la comunidad.' },
    { icon: 'pi pi-heart', titulo: 'Mejores conexiones', desc: 'Te mostramos personas más compatibles con tus intereses.' },
    { icon: 'pi pi-eye', titulo: 'Mayor visibilidad', desc: 'Apareces primero en las búsquedas y recomendaciones.' },
    { icon: 'pi pi-users', titulo: 'Experiencias reales', desc: 'Conecta con personas auténticas que buscan lo mismo que tú.' },
];

/* ---------------------------------------------------------------
 * FUNCIONES DE GUARDADO
 * --------------------------------------------------------------- */
function prepararFormulario() {
    form.intereses = intereses.filter(i => i.selected).map(i => i.label);
    form.buscando = buscando.filter(i => i.selected).map(i => i.label);
}

function guardarYContinuar() {
    if (!validarFormulario()) {
        mostrarErroresValidacion();
        const firstErrorField = document.querySelector('.field__error, .validation-error');
        if (firstErrorField) {
            firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }
    
    isSaving.value = true;
    prepararFormulario();
    
    const formData = new FormData();
    
    // Campos básicos
    formData.append('nickname', form.nickname || '');
    formData.append('edad', String(form.edad || ''));
    formData.append('ciudad', form.ciudad || '');
    formData.append('ocupacion', form.ocupacion || '');
    formData.append('bio', form.bio || '');
    formData.append('tipoPerfil', form.tipoPerfil || 'personal');
    formData.append('visibilidadFotos', form.visibilidadFotos || 'todos');
    
    // Intereses
    if (form.intereses && form.intereses.length > 0) {
        form.intereses.forEach((interes, index) => {
            formData.append(`intereses[${index}]`, interes);
        });
    }
    
    if (form.buscando && form.buscando.length > 0) {
        form.buscando.forEach((buscandoItem, index) => {
            formData.append(`buscando[${index}]`, buscandoItem);
        });
    }
    
    // Datos de pareja
    if (form.tipoPerfil === 'pareja') {
        formData.append('pareja[nombre1]', form.pareja.nombre1 || '');
        formData.append('pareja[edad1]', String(form.pareja.edad1 || ''));
        formData.append('pareja[nombre2]', form.pareja.nombre2 || '');
        formData.append('pareja[edad2]', String(form.pareja.edad2 || ''));
        formData.append('pareja[visibleParaAmbos]', form.pareja.visibleParaAmbos ? '1' : '0');
    }
    
    // Fotos
    formData.append('total_fotos', String(fotos.length));
    
    fotos.forEach((foto, index) => {
        if (foto.file && foto.file instanceof File) {
            formData.append(`foto_${index}_file`, foto.file);
            formData.append(`foto_${index}_principal`, foto.principal ? '1' : '0');
            formData.append(`foto_${index}_tipo`, 'nueva');
        } else if (foto.existente && foto.id) {
            formData.append(`foto_${index}_id`, String(foto.id));
            formData.append(`foto_${index}_principal`, foto.principal ? '1' : '0');
            formData.append(`foto_${index}_tipo`, 'existente_id');
        } else if (foto.existente && foto.ruta_foto) {
            formData.append(`foto_${index}_ruta`, foto.ruta_foto);
            formData.append(`foto_${index}_principal`, foto.principal ? '1' : '0');
            formData.append(`foto_${index}_tipo`, 'existente_ruta');
        }
    });
    
    router.post(route('perfil.guardar'), formData, {
        preserveScroll: true,
        preserveState: true,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        onStart: () => {
            // Envío iniciado
        },
        onProgress: (progress) => {
            // Progreso de envío
        },
        onSuccess: (response) => {
            isSaving.value = false;
            if (window.showToast) {
                window.showToast({
                    type: 'success',
                    title: 'Perfil guardado',
                    message: 'Tu perfil ha sido guardado correctamente.',
                    duration: 3000,
                });
            }
            setTimeout(() => {
                router.visit(route('inicio'));
            }, 800);
        },
        onError: (errors) => {
            isSaving.value = false;
            const firstError = Object.values(errors)[0];
            if (window.showToast) {
                window.showToast({
                    type: 'error',
                    title: 'Error',
                    message: firstError || 'Error al guardar los datos.',
                    duration: 5000,
                });
            }
        },
        onFinish: () => {
            // Envío finalizado
        }
    });
}

function irAInicio() {
    if (confirm('¿Estás seguro de que quieres cancelar? Los cambios no guardados se perderán.')) {
        router.visit(route('inicio'));
    }
}

/* ---------------------------------------------------------------
 * Watchers
 * --------------------------------------------------------------- */
watch(() => form.tipoPerfil, (newVal) => {
    if (newVal === 'personal') {
        form.pareja = {
            nombre1: '',
            edad1: '',
            nombre2: '',
            edad2: '',
            visibleParaAmbos: true,
        };
    }
    validarFormulario();
});

watch([() => form.nickname, () => form.edad, () => form.ciudad, () => form.bio, () => form.visibilidadFotos], () => {
    validarFormulario();
});

watch(() => form.pareja, () => {
    validarFormulario();
}, { deep: true });

watch(fotos, () => {
    validarFormulario();
}, { deep: true });

// Debug disponible en consola
window.__debug = {
    fotos,
    form,
    intereses,
    buscando,
    validarFormulario,
    guardarYContinuar,
    props
};
</script>

<template>
    <AppLayout activeNav="perfil">
        <Head title="Completa tu perfil" />

        <div class="completar-page">
            <!-- ============================================================ -->
            <!-- HEADER -->
            <!-- ============================================================ -->
            <header class="page-header">
                <div>
                    <h1>Completa <strong>tu perfil</strong></h1>
                    <p>Cuanto más completo sea tu perfil, más confianza generarás y mejores conexiones tendrás.</p>
                    
                    <div v-if="!isFormValid && camposPendientes.length > 0" class="validation-status">
                        <span class="validation-status__badge">
                            <i class="pi pi-info-circle"></i>
                            {{ camposPendientes.length }} campo{{ camposPendientes.length > 1 ? 's' : '' }} pendiente{{ camposPendientes.length > 1 ? 's' : '' }}
                        </span>
                        <span class="validation-status__list">
                            {{ camposPendientes.slice(0, 3).join(', ') }}{{ camposPendientes.length > 3 ? ` +${camposPendientes.length - 3} más` : '' }}
                        </span>
                    </div>
                    <div v-else class="validation-status validation-status--success">
                        <span class="validation-status__badge validation-status__badge--success">
                            <i class="pi pi-check-circle"></i>
                            Todo listo
                        </span>
                    </div>
                </div>

                <div class="progress-wrapper">
                    <div class="progress-info">
                        <span class="progress-info__label">Progreso</span>
                        <span class="progress-info__value">{{ porcentajeCompletado }}%</span>
                    </div>
                    <div class="progress-ring">
                        <svg width="72" height="72" viewBox="0 0 72 72">
                            <circle cx="36" cy="36" r="30" class="ring-bg" />
                            <circle
                                cx="36" cy="36" r="30"
                                class="ring-fg"
                                :class="{ 'ring-fg--complete': porcentajeCompletado === 100 }"
                                :stroke-dasharray="circunferencia"
                                :stroke-dashoffset="progresoOffset"
                            />
                        </svg>
                        <i class="pi pi-user ring-icon" :class="{ 'ring-icon--complete': porcentajeCompletado === 100 }"></i>
                    </div>
                </div>
            </header>

            <!-- ============================================================ -->
            <!-- CONTENIDO PRINCIPAL -->
            <!-- ============================================================ -->
            <div class="main-grid">
                <!-- COLUMNA IZQUIERDA -->
                <div class="form-column">
                    <!-- SECCIÓN: Información básica -->
                    <div class="card" :class="{ 'card--error': !form.nickname || !form.edad || !form.ciudad }">
                        <div class="card__header">
                            <span class="card__title"><i class="pi pi-user"></i> Información básica</span>
                            <span class="badge">Obligatorio</span>
                        </div>
                        <div class="card__body">
                            <div class="form-row">
                                <div class="field">
                                    <label>Nombre o nickname *</label>
                                    <input 
                                        type="text" 
                                        v-model="form.nickname" 
                                        placeholder="Tu nombre o apodo" 
                                        class="form-input" 
                                        :class="{ 'input-error': !form.nickname && form.nickname !== '' }" 
                                    />
                                    <small v-if="form.errors.nickname" class="field__error">{{ form.errors.nickname }}</small>
                                </div>
                                <div class="field">
                                    <label>Edad *</label>
                                    <input 
                                        type="number" 
                                        v-model="form.edad" 
                                        placeholder="18+" 
                                        class="form-input" 
                                        :class="{ 'input-error': (!form.edad || form.edad < 18) && form.edad !== '' }" 
                                    />
                                    <small v-if="form.errors.edad" class="field__error">{{ form.errors.edad }}</small>
                                </div>
                                <div class="field">
                                    <label>Ciudad *</label>
                                    <input 
                                        type="text" 
                                        v-model="form.ciudad" 
                                        placeholder="Ciudad, país" 
                                        class="form-input" 
                                        :class="{ 'input-error': !form.ciudad && form.ciudad !== '' }" 
                                    />
                                    <small v-if="form.errors.ciudad" class="field__error">{{ form.errors.ciudad }}</small>
                                </div>
                                <div class="field">
                                    <label>Ocupación <span class="optional">(opcional)</span></label>
                                    <input 
                                        type="text" 
                                        v-model="form.ocupacion" 
                                        placeholder="A qué te dedicas" 
                                        class="form-input" 
                                    />
                                </div>
                            </div>

                            <div class="field field--full">
                                <label>Sobre mí / Sobre nosotros *</label>
                                <textarea 
                                    v-model="form.bio" 
                                    :maxlength="bioMax" 
                                    rows="3" 
                                    class="form-textarea" 
                                    :class="{ 'input-error': form.bio.length < 10 && form.bio.length > 0 }"
                                ></textarea>
                                <div class="field__footer">
                                    <span class="char-count">{{ bioLength }}/{{ bioMax }}</span>
                                    <span v-if="form.bio.length < 10 && form.bio.length > 0" class="field__warning">
                                        <i class="pi pi-info-circle"></i> Mínimo 10 caracteres
                                    </span>
                                </div>
                                <small v-if="form.errors.bio" class="field__error">{{ form.errors.bio }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN: Tipo de perfil -->
                    <div class="card">
                        <div class="card__header">
                            <span class="card__title"><i class="pi pi-users"></i> Tipo de perfil</span>
                            <span class="badge">Obligatorio</span>
                        </div>
                        <div class="card__body">
                            <p class="card__hint">Selecciona el tipo de perfil que mejor te representa.</p>

                            <div class="profile-types">
                                <button
                                    class="profile-type"
                                    :class="{ active: form.tipoPerfil === 'personal' }"
                                    @click="form.tipoPerfil = 'personal'"
                                    type="button"
                                >
                                    <div class="profile-type__icon personal">
                                        <i class="pi pi-user"></i>
                                    </div>
                                    <div class="profile-type__info">
                                        <strong>Personal</strong>
                                        <span>Perfil individual</span>
                                    </div>
                                    <div class="profile-type__check" :class="{ checked: form.tipoPerfil === 'personal' }">
                                        <i v-if="form.tipoPerfil === 'personal'" class="pi pi-check"></i>
                                    </div>
                                </button>

                                <button
                                    class="profile-type"
                                    :class="{ active: form.tipoPerfil === 'pareja' }"
                                    @click="form.tipoPerfil = 'pareja'"
                                    type="button"
                                >
                                    <div class="profile-type__icon couple">
                                        <i class="pi pi-heart"></i>
                                    </div>
                                    <div class="profile-type__info">
                                        <strong>Pareja</strong>
                                        <span>Perfil en pareja</span>
                                    </div>
                                    <div class="profile-type__check" :class="{ checked: form.tipoPerfil === 'pareja' }">
                                        <i v-if="form.tipoPerfil === 'pareja'" class="pi pi-check"></i>
                                    </div>
                                </button>
                            </div>

                            <transition name="fade">
                                <div v-if="form.tipoPerfil === 'pareja'" class="couple-fields">
                                    <h4>Información de la pareja *</h4>
                                    <div class="form-row">
                                        <div class="field">
                                            <label>Integrante 1 *</label>
                                            <input 
                                                type="text" 
                                                v-model="form.pareja.nombre1" 
                                                placeholder="Nombre" 
                                                class="form-input" 
                                                :class="{ 'input-error': !form.pareja.nombre1 && form.pareja.nombre1 !== '' }" 
                                            />
                                            <small v-if="form.errors['pareja.nombre1']" class="field__error">{{ form.errors['pareja.nombre1'] }}</small>
                                        </div>
                                        <div class="field">
                                            <label>Edad *</label>
                                            <input 
                                                type="number" 
                                                v-model="form.pareja.edad1" 
                                                placeholder="18+" 
                                                class="form-input" 
                                                :class="{ 'input-error': (!form.pareja.edad1 || form.pareja.edad1 < 18) && form.pareja.edad1 !== '' }" 
                                            />
                                            <small v-if="form.errors['pareja.edad1']" class="field__error">{{ form.errors['pareja.edad1'] }}</small>
                                        </div>
                                        <div class="field">
                                            <label>Integrante 2 *</label>
                                            <input 
                                                type="text" 
                                                v-model="form.pareja.nombre2" 
                                                placeholder="Nombre" 
                                                class="form-input" 
                                                :class="{ 'input-error': !form.pareja.nombre2 && form.pareja.nombre2 !== '' }" 
                                            />
                                            <small v-if="form.errors['pareja.nombre2']" class="field__error">{{ form.errors['pareja.nombre2'] }}</small>
                                        </div>
                                        <div class="field">
                                            <label>Edad *</label>
                                            <input 
                                                type="number" 
                                                v-model="form.pareja.edad2" 
                                                placeholder="18+" 
                                                class="form-input" 
                                                :class="{ 'input-error': (!form.pareja.edad2 || form.pareja.edad2 < 18) && form.pareja.edad2 !== '' }" 
                                            />
                                            <small v-if="form.errors['pareja.edad2']" class="field__error">{{ form.errors['pareja.edad2'] }}</small>
                                        </div>
                                    </div>
                                    
                                    <div class="couple-visibility">
                                        <label class="visibility-toggle">
                                            <input type="checkbox" v-model="form.pareja.visibleParaAmbos" />
                                            <span class="toggle-track">
                                                <span class="toggle-thumb"></span>
                                            </span>
                                            <span class="toggle-label">
                                                <i class="pi pi-heart-fill"></i>
                                                <span>Perfil visible para ambos integrantes</span>
                                                <small>Ambos podrán gestionar y ver el perfil</small>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>

                    <!-- SECCIÓN: Gustos e intereses -->
                    <div class="card">
                        <div class="card__header">
                            <span class="card__title"><i class="pi pi-star"></i> Gustos e intereses</span>
                            <span class="badge">
                                {{ interesesCount }} seleccionados
                            </span>
                        </div>
                        <div class="card__body">
                            <p class="card__hint">Selecciona tus intereses</p>
                            <div class="chip-group">
                                <button
                                    v-for="item in intereses"
                                    :key="item.label"
                                    class="chip"
                                    :class="{ active: item.selected }"
                                    @click="toggle(intereses, item)"
                                    type="button"
                                >
                                    <i v-if="item.icon" :class="item.icon" class="chip__icon"></i>
                                    {{ item.label }}
                                    <i v-if="item.selected" class="pi pi-check chip__check"></i>
                                </button>
                            </div>

                            <p class="card__hint mt-4">Qué estás buscando <span v-if="form.tipoPerfil === 'personal'">*</span></p>
                            <div class="chip-group">
                                <button
                                    v-for="item in buscando"
                                    :key="item.label"
                                    class="chip"
                                    :class="{ active: item.selected }"
                                    @click="toggle(buscando, item)"
                                    type="button"
                                >
                                    <i v-if="item.icon" :class="item.icon" class="chip__icon"></i>
                                    {{ item.label }}
                                    <i v-if="item.selected" class="pi pi-check chip__check"></i>
                                </button>
                            </div>
                            <div v-if="form.tipoPerfil === 'personal' && buscandoCount < 1" class="field__warning">
                                <i class="pi pi-info-circle"></i>
                                Selecciona al menos una opción
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN: Fotos reales -->
                    <div class="card" :class="{ 'card--error': fotos.length === 0 }">
                        <div class="card__header">
                            <span class="card__title"><i class="pi pi-camera"></i> Fotos reales</span>
                            <span class="badge" :class="{ 'badge--warning': fotos.length === 0, 'badge--success': fotos.length >= 1 }">
                                {{ getFotoCountText() }}
                            </span>
                        </div>
                        <div class="card__body">
                            <p class="card__hint"><strong>Mínimo 1 foto</strong> para completar tu perfil</p>
                            <div class="photo-grid">
                                <div v-for="(foto, idx) in fotos" :key="idx" class="photo" @click="setPrincipal(idx)">
                                    <img :src="foto.url" :alt="`Foto ${idx + 1}`" @error="(e) => { e.target.src = '/images/placeholder-image.png' }" />
                                    <span v-if="foto.principal" class="photo__badge">Principal</span>
                                    <span v-if="foto.existente" class="photo__badge photo__badge--existing">Guardada</span>
                                    <button class="photo__delete" @click.stop="eliminarFoto(idx)" type="button">
                                        <i class="pi pi-times"></i>
                                    </button>
                                </div>
                                
                                <label v-if="fotos.length < maxFotos" class="photo photo--upload">
                                    <i class="pi pi-plus"></i>
                                    <span>Subir foto</span>
                                    <small>{{ fotos.length }}/{{ maxFotos }}</small>
                                    <input type="file" accept="image/*" multiple hidden @change="onFileSelected" />
                                </label>
                            </div>
                            <div v-if="fotos.length === 0" class="field__warning">
                                <i class="pi pi-info-circle"></i>
                                Sube al menos una foto
                            </div>

                            <p class="card__hint mt-4">¿Quién puede ver mis fotos? *</p>
                            <div class="radio-group">
                                <label class="radio-option" :class="{ active: form.visibilidadFotos === 'todos' }">
                                    <input type="radio" value="todos" v-model="form.visibilidadFotos" />
                                    <span class="radio-option__content">
                                        <i class="pi pi-globe"></i>
                                        Todos los miembros
                                    </span>
                                </label>
                                <label class="radio-option" :class="{ active: form.visibilidadFotos === 'matches' }">
                                    <input type="radio" value="matches" v-model="form.visibilidadFotos" />
                                    <span class="radio-option__content">
                                        <i class="pi pi-heart"></i>
                                        Solo mis matches
                                    </span>
                                </label>
                                <label class="radio-option" :class="{ active: form.visibilidadFotos === 'nadie' }">
                                    <input type="radio" value="nadie" v-model="form.visibilidadFotos" />
                                    <span class="radio-option__content">
                                        <i class="pi pi-lock"></i>
                                        Nadie
                                    </span>
                                </label>
                            </div>
                            <div v-if="!form.visibilidadFotos" class="field__warning">
                                <i class="pi pi-info-circle"></i>
                                Selecciona una opción de visibilidad
                            </div>

                            <div class="tip">
                                <div class="tip__icon"><i class="pi pi-check-circle"></i></div>
                                <div class="tip__content">
                                    <span class="tip__label">Consejo</span>
                                    <p class="tip__text">Las fotos reales aumentan hasta <strong>5x</strong> más tus posibilidades de conexión.</p>
                                </div>
                            </div>
                        </div>
                    </div>  
                </div>

                <!-- COLUMNA DERECHA - SIDEBAR -->
                <aside class="sidebar">
                    <!-- Vista previa -->
                    <div class="card">
                        <div class="card__header">
                            <span class="card__title">Vista previa</span>
                        </div>
                        <div class="card__body">
                            <div class="preview">
                                <div v-if="fotos.length === 0" class="preview__empty">
                                    <img :src="props.user.avatar || '/images/shared/avatar-default.jpg'" alt="Avatar" class="preview__avatar" />
                                    <div class="preview__empty-text">Sin foto principal</div>
                                </div>
                                <template v-else>
                                    <img :src="getFotoPrincipal()" alt="Foto principal" @error="(e) => { e.target.src = '/images/shared/avatar-default.jpg' }" />
                                    <span class="preview__verified"><i class="pi pi-check-circle"></i> Verificado</span>
                                </template>
                                <div class="preview__overlay">
                                    <h3>
                                        {{ form.tipoPerfil === 'pareja'
                                            ? `${form.pareja.nombre1 || 'Tú'} & ${form.pareja.nombre2 || 'Pareja'}, ${form.pareja.edad1 || '?'} & ${form.pareja.edad2 || '?'}`
                                            : `${form.nickname || 'Usuario'}, ${form.edad || '?'}` }}
                                    </h3>
                                    <p><i class="pi pi-map-marker"></i> {{ form.ciudad || 'Sin ciudad' }} • {{ form.tipoPerfil === 'pareja' ? 'Pareja' : 'Personal' }}</p>
                                    <p class="preview__bio">{{ form.bio.slice(0, 60) }}{{ form.bio.length > 60 ? '…' : '' }}</p>
                                    <div class="preview__tags">
                                        <span v-for="t in interesesSeleccionados.slice(0, 3)" :key="t.label" class="preview__tag">{{ t.label }}</span>
                                        <span v-if="interesesSeleccionados.length > 3" class="preview__tag">+{{ interesesSeleccionados.length - 3 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Beneficios -->
                    <div class="card">
                        <div class="card__header">
                            <span class="card__title">Beneficios</span>
                        </div>
                        <div class="card__body">
                            <div class="benefit-list">
                                <div v-for="b in beneficios" :key="b.titulo" class="benefit">
                                    <span class="benefit__icon"><i class="pi" :class="b.icon"></i></span>
                                    <div>
                                        <strong>{{ b.titulo }}</strong>
                                        <span>{{ b.desc }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="banner">
                                <span>Perfiles completos tienen</span>
                                <strong>5x <i class="pi pi-arrow-up-right"></i></strong>
                                <span>más conexiones</span>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de verificación -->
                    <div class="card">
                        <div class="card__header">
                            <span class="card__title">Lista de verificación</span>
                            <span class="badge" :class="{ 'badge--success': isFormValid }">
                                {{ isFormValid ? 'Completo' : `${camposPendientes.length} pendiente${camposPendientes.length > 1 ? 's' : ''}` }}
                            </span>
                        </div>
                        <div class="card__body">
                            <div class="tip-list">
                                <div v-for="tip in consejos" :key="tip.titulo" class="tip-item" :class="{ 'tip-item--done': tip.ok }">
                                    <i class="pi" :class="tip.ok ? 'pi-check-circle tip-item--ok' : 'pi-info-circle tip-item--pending'"></i>
                                    <div>
                                        <strong>{{ tip.titulo }}</strong>
                                        <span>{{ tip.detalle }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="card">
                        <div class="card__body">
                            <div class="actions">
                                <button class="btn btn--text" @click="irAInicio" type="button" :disabled="isSaving">
                                    <i class="pi pi-times"></i> Cancelar
                                </button>
                                <button 
                                    class="btn btn--primary" 
                                    @click="guardarYContinuar" 
                                    :disabled="isSaving"
                                    :class="{ 'btn--pulse': isFormValid && !isSaving }"
                                    type="button"
                                >
                                    <i class="pi" :class="isSaving ? 'pi-spin pi-spinner' : 'pi-check'"></i> 
                                    {{ isSaving ? 'Guardando...' : 'Completar perfil' }}
                                </button>
                            </div>
                            <div v-if="!isFormValid" class="actions__hint">
                                <i class="pi pi-info-circle"></i>
                                Completa todos los campos obligatorios
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* =========================================================================
   PÁGINA COMPLETAR - (EL MISMO CSS QUE TENÍAS ANTES)
   ========================================================================= */
.completar-page {
  --brand: #C81E3A;
  --brand-dark: #A6152D;
  --brand-soft: #FBEAEC;
  --ink: #171412;
  --ink-soft: #4B4744;
  --muted: #8A8481;
  --muted-light: #B7B2AF;
  --line: #ECE9E7;
  --surface: #FAF8F7;
  --white: #FFFFFF;
  --success: #1fbf5c;
  --success-soft: #E8F5E9;
  --warning: #D69E2E;
  --warning-soft: #FFF8E1;
  --error: #E53E3E;

  --font-serif: 'Fraunces', Georgia, serif;
  --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;

  --radius-sm: 10px;
  --radius-md: 16px;
  --radius-lg: 24px;
  --radius-full: 999px;

  font-family: var(--font-sans);
  color: var(--ink);
  background: #f7f7f8;
  -webkit-font-smoothing: antialiased;
  min-height: 100vh;
  padding: 1rem 2rem 3rem;
}

@media (max-width: 768px) {
  .completar-page {
    padding: 0.5rem 0.5rem 2rem;
  }
}

.completar-page * {
  box-sizing: border-box;
}

.completar-page img {
  max-width: 100%;
  display: block;
}

/* =========================================================================
   PAGE HEADER
   ========================================================================= */
.page-header {
    max-width: 1400px;
    margin: 0 auto 1.5rem;
    padding: 1.5rem 2rem;
    background: var(--white);
    border-radius: var(--radius-md);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 2rem;
    border: 1px solid var(--line);
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
  }
}

.page-header h1 {
    font-family: var(--font-serif);
    font-size: 1.8rem;
    font-weight: 400;
    margin: 0 0 0.3rem;
    color: var(--ink);
}

.page-header h1 strong {
    font-weight: 700;
    color: var(--brand);
    font-style: italic;
}

.page-header p {
    color: var(--muted);
    margin: 0 0 0.75rem;
    font-size: 0.9rem;
}

.validation-status {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.validation-status__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--warning-soft);
    color: var(--warning);
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
}

.validation-status__badge--success {
    background: var(--success-soft);
    color: var(--success);
}

.validation-status__list {
    font-size: 0.78rem;
    color: var(--muted);
}

.validation-status--success .validation-status__badge {
    background: var(--success-soft);
    color: var(--success);
}

.progress-wrapper {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-shrink: 0;
}

.progress-info {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    line-height: 1.2;
}

.progress-info__label {
    font-size: 0.7rem;
    color: var(--muted-light);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.progress-info__value {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--brand);
}

.progress-ring {
    position: relative;
    width: 72px;
    height: 72px;
}

.ring-bg {
    fill: none;
    stroke: var(--line);
    stroke-width: 5;
}

.ring-fg {
    fill: none;
    stroke: var(--brand);
    stroke-width: 5;
    stroke-linecap: round;
    transform: rotate(-90deg);
    transform-origin: center;
    transition: stroke-dashoffset 0.5s ease, stroke 0.5s ease;
}

.ring-fg--complete {
    stroke: var(--success);
}

.ring-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: var(--muted-light);
    font-size: 1.1rem;
    transition: color 0.5s ease;
}

.ring-icon--complete {
    color: var(--success);
}

/* =========================================================================
   MAIN GRID
   ========================================================================= */
.main-grid {
    max-width: 1400px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 2rem;
    align-items: start;
}

@media (max-width: 1100px) {
    .main-grid {
        grid-template-columns: 1fr;
    }
}

/* =========================================================================
   CARDS
   ========================================================================= */
.card {
    background: var(--white);
    border-radius: var(--radius-md);
    border: 1px solid var(--line);
    overflow: hidden;
    margin-bottom: 1.25rem;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    transition: box-shadow 0.3s ease, border-color 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
}

.card--error {
    border-color: var(--error);
    border-width: 2px;
}

.card__header {
    padding: 0.9rem 1.5rem;
    border-bottom: 1px solid var(--line);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    background: #fafafa;
}

@media (max-width: 768px) {
  .card__header {
    flex-wrap: wrap;
  }
}

.card__title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--ink);
}

.card__title i {
    color: var(--brand);
    font-size: 1rem;
}

.card__body {
    padding: 1.25rem 1.5rem;
}

.card__hint {
    font-size: 0.82rem;
    color: var(--muted);
    margin: 0 0 0.75rem;
}

.badge {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--muted);
    background: var(--surface);
    padding: 0.15rem 0.6rem;
    border-radius: var(--radius-full);
}

.badge--warning {
    background: var(--warning-soft);
    color: var(--warning);
}

.badge--success {
    background: var(--success-soft);
    color: var(--success);
}

/* =========================================================================
   FORM FIELDS
   ========================================================================= */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.field--full {
    grid-column: 1 / -1;
}

.field label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--ink-soft);
}

.field .optional {
    font-weight: 400;
    color: var(--muted-light);
}

.form-input,
.form-textarea {
    width: 100%;
    border-radius: var(--radius-sm);
    border: 1.5px solid var(--line);
    padding: 0.6rem 0.9rem;
    font-size: 0.88rem;
    transition: all 0.2s ease;
    background: var(--white);
    color: var(--ink);
    font-family: inherit;
}

.form-input:focus,
.form-textarea:focus {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.08);
    outline: none;
}

.form-input.input-error,
.form-textarea.input-error {
    border-color: var(--error);
}

.field__error {
    font-size: 0.7rem;
    color: var(--error);
    margin-top: 0.15rem;
}

.field__warning {
    font-size: 0.72rem;
    color: var(--warning);
    display: flex;
    align-items: center;
    gap: 0.3rem;
    margin-top: 0.25rem;
}

.field__footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.2rem;
}

.char-count {
    font-size: 0.7rem;
    color: var(--muted-light);
}

.mt-4 {
    margin-top: 1rem;
}

/* =========================================================================
   PROFILE TYPES
   ========================================================================= */
.profile-types {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 768px) {
  .profile-types {
    grid-template-columns: 1fr;
  }
}

.profile-type {
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 2px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 1rem 1.25rem;
    background: var(--white);
    cursor: pointer;
    text-align: left;
    transition: all 0.25s ease;
}

.profile-type:hover {
    border-color: var(--brand);
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(200, 30, 58, 0.06);
}

.profile-type.active {
    border-color: var(--brand);
    background: var(--brand-soft);
    box-shadow: 0 4px 16px rgba(200, 30, 58, 0.1);
}

.profile-type__icon {
    width: 44px;
    height: 44px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
    transition: all 0.25s ease;
}

.profile-type__icon.personal {
    background: #EBF5FF;
    color: #2B6CB0;
}

.profile-type__icon.couple {
    background: #FEE2E2;
    color: var(--brand);
}

.profile-type.active .profile-type__icon.personal {
    background: #2B6CB0;
    color: var(--white);
}

.profile-type.active .profile-type__icon.couple {
    background: var(--brand);
    color: var(--white);
}

.profile-type__info {
    flex: 1;
}

.profile-type__info strong {
    display: block;
    font-size: 0.9rem;
    color: var(--ink);
}

.profile-type__info span {
    font-size: 0.75rem;
    color: var(--muted);
}

.profile-type__check {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2px solid var(--line);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.25s ease;
}

.profile-type__check.checked {
    background: var(--brand);
    border-color: var(--brand);
    color: var(--white);
    font-size: 0.65rem;
}

.couple-fields {
    border-top: 1px dashed var(--line);
    padding-top: 1rem;
    margin-top: 1rem;
}

.couple-fields h4 {
    font-size: 0.85rem;
    margin: 0 0 0.75rem;
    color: var(--ink-soft);
}

/* =========================================================================
   COUPLE VISIBILITY TOGGLE
   ========================================================================= */
.couple-visibility {
    margin-top: 1.25rem;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, #fdf2f4 0%, #fef7f8 100%);
    border-radius: var(--radius-sm);
    border: 1px solid #fce4e8;
}

.visibility-toggle {
    display: flex;
    align-items: center;
    gap: 1rem;
    cursor: pointer;
    user-select: none;
}

.visibility-toggle input {
    display: none;
}

.toggle-track {
    position: relative;
    width: 48px;
    height: 28px;
    background: #cbd5e1;
    border-radius: var(--radius-full);
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.toggle-track .toggle-thumb {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 22px;
    height: 22px;
    background: var(--white);
    border-radius: 50%;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.visibility-toggle input:checked + .toggle-track {
    background: var(--brand);
}

.visibility-toggle input:checked + .toggle-track .toggle-thumb {
    transform: translateX(20px);
}

.toggle-label {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.toggle-label i {
    color: var(--brand);
    font-size: 0.9rem;
}

.toggle-label span {
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--ink);
}

.toggle-label small {
    font-size: 0.72rem;
    color: var(--muted);
    font-weight: 400;
}

/* =========================================================================
   CHIPS
   ========================================================================= */
.chip-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.chip {
    border: 1.5px solid var(--line);
    border-radius: var(--radius-full);
    padding: 0.4rem 0.9rem;
    background: var(--white);
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink-soft);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.2s ease;
}

.chip:hover {
    border-color: var(--brand);
    transform: translateY(-1px);
}

.chip.active {
    background: var(--brand);
    border-color: var(--brand);
    color: var(--white);
}

.chip__icon {
    font-size: 0.7rem;
}

.chip__check {
    font-size: 0.65rem;
}

/* =========================================================================
   PHOTOS
   ========================================================================= */
.photo-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .photo-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.photo {
    position: relative;
    aspect-ratio: 1;
    border-radius: var(--radius-sm);
    overflow: hidden;
    background: var(--surface);
    cursor: pointer;
    transition: all 0.2s ease;
}

.photo:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo__badge {
    position: absolute;
    bottom: 6px;
    left: 6px;
    background: var(--brand);
    color: var(--white);
    font-size: 0.6rem;
    font-weight: 700;
    padding: 0.1rem 0.5rem;
    border-radius: 4px;
}

.photo__badge--existing {
    background: var(--success);
    left: auto;
    right: 6px;
}

.photo__delete {
    position: absolute;
    top: 4px;
    right: 4px;
    background: rgba(0,0,0,0.6);
    color: var(--white);
    border: none;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.7rem;
    transition: all 0.2s ease;
    opacity: 0;
}

.photo:hover .photo__delete {
    opacity: 1;
}

.photo__delete:hover {
    background: var(--brand);
    transform: scale(1.1);
}

.photo--upload {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    border: 2px dashed var(--line);
    background: var(--surface);
    color: var(--muted-light);
    font-size: 0.7rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.photo--upload:hover {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
    transform: scale(1.02);
}

.photo--upload i {
    font-size: 1.4rem;
}

.photo--upload small {
    font-size: 0.6rem;
    color: var(--muted-light);
}

.photo--upload:hover small {
    color: var(--brand);
}

/* =========================================================================
   RADIO GROUP
   ========================================================================= */
.radio-group {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.radio-option {
    flex: 1;
    min-width: 120px;
    cursor: pointer;
}

.radio-option input {
    display: none;
}

.radio-option__content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1rem;
    border: 2px solid var(--line);
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--ink-soft);
    background: var(--white);
    transition: all 0.2s ease;
}

.radio-option__content i {
    font-size: 0.9rem;
}

.radio-option:hover .radio-option__content {
    border-color: var(--brand);
    background: var(--brand-soft);
}

.radio-option.active .radio-option__content {
    border-color: var(--brand);
    background: var(--brand-soft);
    color: var(--brand);
}

/* =========================================================================
   TIP
   ========================================================================= */
.tip {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    background: linear-gradient(135deg, #eefaf1 0%, #f6fdf8 100%);
    border-radius: var(--radius-sm);
    padding: 1rem 1.25rem;
    border-left: 4px solid var(--success);
    margin-top: 1rem;
}

.tip__icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--success);
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
}

.tip__content {
    flex: 1;
}

.tip__label {
    display: inline-block;
    font-size: 0.6rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--success);
    background: rgba(31, 191, 92, 0.1);
    padding: 0.1rem 0.6rem;
    border-radius: var(--radius-full);
    margin-bottom: 0.2rem;
}

.tip__text {
    font-size: 0.85rem;
    color: #1a3a2a;
    margin: 0;
    line-height: 1.5;
}

.tip__text strong {
    color: var(--success);
    font-weight: 800;
}

/* =========================================================================
   SIDEBAR
   ========================================================================= */
.sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

/* =========================================================================
   PREVIEW
   ========================================================================= */
.preview {
    position: relative;
    border-radius: var(--radius-sm);
    overflow: hidden;
    aspect-ratio: 4/5;
    background: var(--ink);
}

.preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview__empty {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #2a2a2e, #1a1a1e);
    padding: 1rem;
    position: relative;
}

.preview__avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.15;
    position: absolute;
    inset: 0;
}

.preview__empty-text {
    position: relative;
    z-index: 2;
    color: var(--white);
    opacity: 0.6;
    font-size: 0.8rem;
}

.preview__verified {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(20,20,20,0.6);
    color: #4ade80;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    gap: 0.3rem;
    z-index: 2;
}

.preview__overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 1rem;
    background: linear-gradient(to top, rgba(0,0,0,0.85), rgba(0,0,0,0));
    color: var(--white);
    z-index: 2;
}

.preview__overlay h3 {
    margin: 0 0 0.2rem;
    font-size: 1rem;
    font-weight: 600;
}

.preview__overlay p {
    margin: 0 0 0.2rem;
    font-size: 0.75rem;
    opacity: 0.85;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.preview__bio {
    opacity: 0.7 !important;
    font-size: 0.7rem !important;
}

.preview__tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    margin-top: 0.3rem;
}

.preview__tag {
    background: rgba(255,255,255,0.15);
    font-size: 0.6rem;
    padding: 0.15rem 0.5rem;
    border-radius: var(--radius-full);
}

/* =========================================================================
   TIPS LIST
   ========================================================================= */
.tip-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.tip-item {
    display: flex;
    gap: 0.6rem;
    align-items: flex-start;
    font-size: 0.8rem;
    padding: 0.4rem 0.6rem;
    border-radius: var(--radius-sm);
    transition: all 0.2s ease;
}

.tip-item--done {
    background: var(--success-soft);
}

.tip-item strong {
    display: block;
    color: var(--ink);
}

.tip-item span {
    color: var(--muted);
    font-size: 0.72rem;
}

.tip-item--ok {
    color: var(--success);
    margin-top: 0.15rem;
}

.tip-item--pending {
    color: var(--warning);
    margin-top: 0.15rem;
}

/* =========================================================================
   BENEFITS
   ========================================================================= */
.benefit-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.benefit {
    display: flex;
    gap: 0.7rem;
    align-items: flex-start;
}

.benefit__icon {
    width: 30px;
    height: 30px;
    border-radius: var(--radius-sm);
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.85rem;
}

.benefit strong {
    display: block;
    font-size: 0.82rem;
    color: var(--ink);
}

.benefit span {
    font-size: 0.72rem;
    color: var(--muted);
}

.banner {
    border-radius: var(--radius-sm);
    background: var(--brand-soft);
    padding: 0.8rem;
    text-align: center;
}

.banner span {
    font-size: 0.72rem;
    color: var(--ink-soft);
}

.banner strong {
    color: var(--brand);
    font-size: 1.4rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.2rem;
}

/* =========================================================================
   ACTIONS
   ========================================================================= */
.actions {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.actions__hint {
    text-align: center;
    font-size: 0.78rem;
    color: var(--warning);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.3rem;
}

.actions__hint--success {
    color: var(--success);
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.65rem 1.25rem;
    border-radius: var(--radius-sm);
    font-weight: 700;
    font-size: 0.82rem;
    font-family: var(--font-sans);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    width: 100%;
    letter-spacing: 0.02em;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn i {
    font-size: 0.95rem;
}

.btn--primary {
    background: var(--brand);
    color: var(--white);
    box-shadow: 0 4px 14px rgba(200, 30, 58, 0.25);
}

.btn--primary:hover:not(:disabled) {
    background: var(--brand-dark);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(200, 30, 58, 0.35);
}

.btn--pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 4px 14px rgba(200, 30, 58, 0.25);
    }
    50% {
        box-shadow: 0 4px 30px rgba(200, 30, 58, 0.45);
    }
}

.btn--text {
    background: transparent;
    color: var(--muted);
}

.btn--text:hover:not(:disabled) {
    color: var(--error);
    background: rgba(229, 62, 62, 0.06);
}

/* =========================================================================
   ANIMATIONS
   ========================================================================= */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>