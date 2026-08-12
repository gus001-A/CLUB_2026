<script setup>
import { computed, reactive, ref, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
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
            intereses: [],
            pasatiempos: [],
            fotos: [],
            privacidad_fotos: 'todos',
            esta_verificado: false,
            ubicacion_ciudad: '',
            metadatos: {},
            pareja: null,
        })
    }
});

/* ---------------------------------------------------------------
 * Estado del formulario
 * --------------------------------------------------------------- */
const form = reactive({
    nickname: props.user.nickname || '',
    edad: props.user.edad || '',
    ciudad: props.user.ciudad || '',
    ocupacion: props.perfil?.metadatos?.ocupacion || '',
    bio: props.perfil?.descripcion || '',
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
});

const isSaving = ref(false);
const isEditing = ref(false);
const bioMax = 300;
const bioLength = computed(() => form.bio.length);

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

// Cargar intereses guardados
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
}

const interesesSeleccionados = computed(() =>
    [...intereses, ...buscando].filter((i) => i.selected)
);

const interesesCount = computed(() => intereses.filter(i => i.selected).length);
const buscandoCount = computed(() => buscando.filter(i => i.selected).length);

/* ---------------------------------------------------------------
 * Fotos
 * --------------------------------------------------------------- */
const fotos = reactive([]);
const fotosEliminar = ref([]);

function getFotoUrl(foto) {
    if (!foto) return '/images/shared/avatar-default.jpg';
    
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
    
    return '/images/shared/avatar-default.jpg';
}

// Cargar fotos existentes
onMounted(() => {
    if (props.perfil?.fotos && props.perfil.fotos.length > 0) {
        props.perfil.fotos.forEach(f => {
            fotos.push({
                id: f.id,
                url: getFotoUrl(f),
                principal: f.principal || false,
                existente: true,
                ruta_foto: f.ruta_foto || '',
                file: null,
                marcadaEliminar: false
            });
        });
    }
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
            fotos.push({ 
                url: e.target.result, 
                file: file,
                principal: fotos.length === 0,
                existente: false,
                ruta_foto: '',
                id: null,
                marcadaEliminar: false
            });
        };
        reader.readAsDataURL(file);
    });
    event.target.value = '';
}

function eliminarFoto(index) {
    const foto = fotos[index];
    
    if (foto.existente && foto.id) {
        foto.marcadaEliminar = true;
        fotosEliminar.value.push(foto.id);
    }
    
    fotos.splice(index, 1);
    
    if (fotos.length > 0 && !fotos.some(f => f.principal)) {
        fotos[0].principal = true;
    }
}

function restaurarFoto(index) {
    const foto = fotos[index];
    if (foto.marcadaEliminar) {
        foto.marcadaEliminar = false;
        const idx = fotosEliminar.value.indexOf(foto.id);
        if (idx > -1) {
            fotosEliminar.value.splice(idx, 1);
        }
    }
}

function setPrincipal(index) {
    fotos.forEach((f, i) => f.principal = i === index);
}

function getFotoPrincipal() {
    const principal = fotos.find(f => f.principal);
    if (principal) return principal.url;
    if (fotos.length > 0) return fotos[0].url;
    return props.user?.avatar || '/images/shared/avatar-default.jpg';
}

/* ---------------------------------------------------------------
 * Guardar cambios con validaciones y toasts
 * --------------------------------------------------------------- */
function guardarCambios() {
    // Validación: Verificar que el nickname no esté vacío
    if (!form.nickname || form.nickname.trim() === '') {
        if (window.showToast) {
            window.showToast({
                type: 'error',
                title: 'Nickname requerido',
                message: 'El nickname es obligatorio.',
                duration: 5000,
            });
        }
        return;
    }
    
    // Validación: Verificar que el nickname tenga entre 3 y 20 caracteres
    if (form.nickname.length < 3 || form.nickname.length > 20) {
        if (window.showToast) {
            window.showToast({
                type: 'error',
                title: 'Nickname inválido',
                message: 'El nickname debe tener entre 3 y 20 caracteres.',
                duration: 5000,
            });
        }
        return;
    }
    
    // Validación: Verificar que la edad sea válida
    if (form.edad && (form.edad < 18 || form.edad > 120)) {
        if (window.showToast) {
            window.showToast({
                type: 'error',
                title: 'Edad inválida',
                message: 'La edad debe ser entre 18 y 120 años.',
                duration: 5000,
            });
        }
        return;
    }
    
    // Validación: Verificar que tenga al menos una foto
    if (fotos.length === 0) {
        if (window.showToast) {
            window.showToast({
                type: 'error',
                title: 'Foto requerida',
                message: 'Debes subir al menos una foto para tu perfil.',
                duration: 5000,
            });
        }
        return;
    }
    
    isSaving.value = true;
    
    const formData = new FormData();
    
    formData.append('nickname', form.nickname || '');
    formData.append('edad', String(form.edad || ''));
    formData.append('ciudad', form.ciudad || '');
    formData.append('ocupacion', form.ocupacion || '');
    formData.append('bio', form.bio || '');
    formData.append('tipoPerfil', form.tipoPerfil || 'personal');
    formData.append('visibilidadFotos', form.visibilidadFotos || 'todos');
    
    const interesesSeleccionados = intereses.filter(i => i.selected).map(i => i.label);
    const buscandoSeleccionados = buscando.filter(i => i.selected).map(i => i.label);
    
    interesesSeleccionados.forEach((interes, index) => {
        formData.append(`intereses[${index}]`, interes);
    });
    
    buscandoSeleccionados.forEach((item, index) => {
        formData.append(`buscando[${index}]`, item);
    });
    
    if (form.tipoPerfil === 'pareja') {
        formData.append('pareja[nombre1]', form.pareja.nombre1 || '');
        formData.append('pareja[edad1]', String(form.pareja.edad1 || ''));
        formData.append('pareja[nombre2]', form.pareja.nombre2 || '');
        formData.append('pareja[edad2]', String(form.pareja.edad2 || ''));
        formData.append('pareja[visibleParaAmbos]', form.pareja.visibleParaAmbos ? '1' : '0');
    }
    
    fotos.forEach((foto, index) => {
        if (foto.file) {
            formData.append(`fotos[${index}][file]`, foto.file);
            formData.append(`fotos[${index}][principal]`, foto.principal ? '1' : '0');
        } else if (foto.existente && foto.id && !foto.marcadaEliminar) {
            formData.append(`fotos[${index}][id]`, String(foto.id));
            formData.append(`fotos[${index}][principal]`, foto.principal ? '1' : '0');
        }
    });
    
    fotosEliminar.value.forEach((id, index) => {
        formData.append(`fotos_eliminar[${index}]`, String(id));
    });
    
    router.post(route('perfil.actualizar'), formData, {
        preserveScroll: true,
        preserveState: true,
        headers: {
            'Content-Type': 'multipart/form-data',
        },
        onSuccess: (page) => {
            isSaving.value = false;
            isEditing.value = false;
            
            // Verificar si el controlador envió un toast
            if (page.props.flash?.toast) {
                // El toast ya será mostrado por ToastNotification
                console.log('Toast recibido del controlador:', page.props.flash.toast);
            } else if (window.showToast) {
                // Fallback: mostrar toast directamente
                window.showToast({
                    type: 'success',
                    title: 'Perfil actualizado',
                    message: 'Tu perfil ha sido guardado correctamente.',
                    duration: 3000,
                });
            }
            
            // Recargar la página para ver los cambios
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        },
        onError: (errors) => {
            isSaving.value = false;
            
            if (window.showToast) {
                let errorMsg = 'Error al guardar los datos.';
                const firstError = Object.values(errors)[0];
                if (firstError) {
                    errorMsg = Array.isArray(firstError) ? firstError[0] : firstError;
                }
                
                // Determinar el título del error según el campo
                let errorTitle = 'Error';
                const errorField = Object.keys(errors)[0];
                const fieldTitles = {
                    'nickname': 'Nickname inválido',
                    'edad': 'Edad inválida',
                    'ciudad': 'Ciudad inválida',
                    'bio': 'Descripción inválida',
                    'fotos': 'Error en fotos',
                    'fotos_eliminar': 'Error al eliminar fotos',
                    'intereses': 'Error en intereses',
                    'buscando': 'Error en preferencias',
                    'visibilidadFotos': 'Error en visibilidad',
                };
                errorTitle = fieldTitles[errorField] || errorTitle;
                
                window.showToast({
                    type: 'error',
                    title: errorTitle,
                    message: errorMsg,
                    duration: 5000,
                });
            }
            
            console.error('Errores:', errors);
        }
    });
}

function toggleEdicion() {
    isEditing.value = !isEditing.value;
    if (!isEditing.value) {
        window.location.reload();
    }
}
</script>

<template>
    <AppLayout activeNav="perfil">
        <Head title="Mi Perfil" />

        <div class="profile-page">
            <!-- ============================================================ -->
            <!-- HEADER PERFIL -->
            <!-- ============================================================ -->
            <div class="profile-header">
                <div class="profile-header__avatar-wrapper">
                    <div class="profile-header__avatar-ring">
                        <div class="profile-header__avatar">
                            <img 
                                :src="getFotoPrincipal()" 
                                :alt="form.nickname"
                                @error="(e) => { e.target.src = '/images/shared/avatar-default.jpg' }"
                            />
                            <span v-if="props.user.estado === 'verificado'" class="profile-header__badge">
                                <i class="pi pi-check"></i>
                            </span>
                        </div>
                    </div>
                    <div class="profile-header__status">
                        <span class="status-dot"></span>
                        <span class="status-text">Activo ahora</span>
                    </div>
                </div>

                <div class="profile-header__info">
                    <div class="profile-header__name-row">
                        <h1 class="profile-header__name">
                            {{ form.nickname || 'Usuario' }}
                        </h1>
                        <span v-if="props.user.estado === 'verificado'" class="verified-badge">
                            <i class="pi pi-check-circle"></i> Verificado
                        </span>
                        <span class="member-badge">
                            <i class="pi pi-star-fill"></i> Miembro
                        </span>
                    </div>
                    
                    <p class="profile-header__bio">
                        {{ form.bio || 'Sin descripción aún.' }}
                    </p>
                    
                    <div class="profile-header__meta">
                        <span v-if="form.ciudad">
                            <i class="pi pi-map-marker"></i> {{ form.ciudad }}
                        </span>
                        <span v-if="form.edad">
                            <i class="pi pi-calendar"></i> {{ form.edad }} años
                        </span>
                        <span>
                            <i class="pi pi-users"></i> 
                            {{ form.tipoPerfil === 'pareja' ? 'Perfil de pareja' : 'Perfil personal' }}
                        </span>
                    </div>
                </div>

                <div class="profile-header__actions">
                    <button 
                        v-if="!isEditing" 
                        class="btn-edit" 
                        @click="isEditing = true"
                    >
                        <i class="pi pi-pencil"></i> 
                        <span>Editar perfil</span>
                    </button>
                    <div v-else class="profile-header__actions-edit">
                        <button class="btn-edit btn-edit--cancel" @click="toggleEdicion" :disabled="isSaving">
                            <i class="pi pi-times"></i> Cancelar
                        </button>
                        <button class="btn-edit btn-edit--save" @click="guardarCambios" :disabled="isSaving">
                            <i class="pi" :class="isSaving ? 'pi-spin pi-spinner' : 'pi-save'"></i>
                            {{ isSaving ? 'Guardando...' : 'Guardar' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- CONTENIDO PRINCIPAL -->
            <!-- ============================================================ -->
            <div class="profile-content">
                <!-- COLUMNA IZQUIERDA -->
                <div class="profile-left">
                    <!-- Sobre mí -->
                    <div class="profile-card">
                        <div class="profile-card__header">
                            <h3><i class="pi pi-file-text"></i> Sobre mí</h3>
                            <span v-if="isEditing" class="edit-badge">
                                <i class="pi pi-pencil"></i> Editando
                            </span>
                        </div>
                        <div class="profile-card__body">
                            <div v-if="!isEditing" class="profile-bio">
                                {{ form.bio || 'Aún no has agregado una descripción. ¡Completa tu perfil para conectar mejor!' }}
                            </div>
                            <div v-else>
                                <textarea 
                                    v-model="form.bio" 
                                    :maxlength="bioMax" 
                                    rows="4" 
                                    class="form-textarea"
                                    placeholder="Cuéntanos sobre ti..."
                                ></textarea>
                                <div class="char-counter">{{ bioLength }}/{{ bioMax }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Información personal -->
                    <div class="profile-card">
                        <div class="profile-card__header">
                            <h3><i class="pi pi-user"></i> Información personal</h3>
                        </div>
                        <div class="profile-card__body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-item__label">Nickname</span>
                                    <span v-if="!isEditing" class="info-item__value">{{ form.nickname }}</span>
                                    <input v-else v-model="form.nickname" class="form-input" />
                                </div>
                                <div class="info-item">
                                    <span class="info-item__label">Edad</span>
                                    <span v-if="!isEditing" class="info-item__value">{{ form.edad || 'No especificada' }}</span>
                                    <input v-else type="number" v-model="form.edad" class="form-input" min="18" max="120" />
                                </div>
                                <div class="info-item">
                                    <span class="info-item__label">Ciudad</span>
                                    <span v-if="!isEditing" class="info-item__value">{{ form.ciudad || 'No especificada' }}</span>
                                    <input v-else v-model="form.ciudad" class="form-input" />
                                </div>
                                <div class="info-item">
                                    <span class="info-item__label">Ocupación</span>
                                    <span v-if="!isEditing" class="info-item__value">{{ form.ocupacion || 'No especificada' }}</span>
                                    <input v-else v-model="form.ocupacion" class="form-input" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tipo de perfil -->
                    <div class="profile-card">
                        <div class="profile-card__header">
                            <h3><i class="pi pi-users"></i> Tipo de perfil</h3>
                        </div>
                        <div class="profile-card__body">
                            <div class="profile-type-selector">
                                <button
                                    class="type-option"
                                    :class="{ active: form.tipoPerfil === 'personal' }"
                                    @click="isEditing ? form.tipoPerfil = 'personal' : null"
                                    :disabled="!isEditing"
                                >
                                    <div class="type-option__icon personal">
                                        <i class="pi pi-user"></i>
                                    </div>
                                    <div class="type-option__info">
                                        <strong>Personal</strong>
                                        <span>Perfil individual</span>
                                    </div>
                                    <div class="type-option__check" :class="{ checked: form.tipoPerfil === 'personal' }">
                                        <i v-if="form.tipoPerfil === 'personal'" class="pi pi-check"></i>
                                    </div>
                                </button>

                                <button
                                    class="type-option"
                                    :class="{ active: form.tipoPerfil === 'pareja' }"
                                    @click="isEditing ? form.tipoPerfil = 'pareja' : null"
                                    :disabled="!isEditing"
                                >
                                    <div class="type-option__icon couple">
                                        <i class="pi pi-heart"></i>
                                    </div>
                                    <div class="type-option__info">
                                        <strong>Pareja</strong>
                                        <span>Perfil en pareja</span>
                                    </div>
                                    <div class="type-option__check" :class="{ checked: form.tipoPerfil === 'pareja' }">
                                        <i v-if="form.tipoPerfil === 'pareja'" class="pi pi-check"></i>
                                    </div>
                                </button>
                            </div>

                            <transition name="fade">
                                <div v-if="form.tipoPerfil === 'pareja'" class="couple-info">
                                    <div class="couple-info__grid">
                                        <div class="couple-info__item">
                                            <label>Integrante 1</label>
                                            <input 
                                                type="text" 
                                                v-model="form.pareja.nombre1" 
                                                placeholder="Nombre" 
                                                class="form-input" 
                                                :disabled="!isEditing"
                                            />
                                        </div>
                                        <div class="couple-info__item">
                                            <label>Edad</label>
                                            <input 
                                                type="number" 
                                                v-model="form.pareja.edad1" 
                                                placeholder="18+" 
                                                class="form-input" 
                                                :disabled="!isEditing"
                                            />
                                        </div>
                                        <div class="couple-info__item">
                                            <label>Integrante 2</label>
                                            <input 
                                                type="text" 
                                                v-model="form.pareja.nombre2" 
                                                placeholder="Nombre" 
                                                class="form-input" 
                                                :disabled="!isEditing"
                                            />
                                        </div>
                                        <div class="couple-info__item">
                                            <label>Edad</label>
                                            <input 
                                                type="number" 
                                                v-model="form.pareja.edad2" 
                                                placeholder="18+" 
                                                class="form-input" 
                                                :disabled="!isEditing"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA -->
                <div class="profile-right">
                    <!-- Fotos -->
                    <div class="profile-card">
                        <div class="profile-card__header">
                            <h3><i class="pi pi-images"></i> Galería</h3>
                            <span class="photo-count">{{ fotos.length }}/{{ maxFotos }}</span>
                        </div>
                        <div class="profile-card__body">
                            <div class="photo-grid">
                                <div v-for="(foto, idx) in fotos" :key="idx" class="photo-item" @click="isEditing ? setPrincipal(idx) : null">
                                    <img :src="foto.url" :alt="`Foto ${idx + 1}`" @error="(e) => { e.target.src = '/images/placeholder-image.png' }" />
                                    <div v-if="foto.principal" class="photo-item__badge photo-item__badge--principal">Principal</div>
                                    <div v-if="foto.marcadaEliminar" class="photo-item__badge photo-item__badge--deleted">Eliminar</div>
                                    <div v-if="foto.existente && !foto.marcadaEliminar" class="photo-item__badge photo-item__badge--existing">Guardada</div>
                                    
                                    <div v-if="isEditing" class="photo-item__actions">
                                        <button class="photo-item__btn photo-item__btn--delete" @click.stop="eliminarFoto(idx)">
                                            <i class="pi pi-trash"></i>
                                        </button>
                                        <button v-if="foto.marcadaEliminar" class="photo-item__btn photo-item__btn--restore" @click.stop="restaurarFoto(idx)">
                                            <i class="pi pi-undo"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <label v-if="isEditing && fotos.length < maxFotos" class="photo-item photo-item--upload">
                                    <i class="pi pi-plus"></i>
                                    <span>Subir foto</span>
                                    <input type="file" accept="image/*" multiple hidden @change="onFileSelected" />
                                </label>
                            </div>
                            <div v-if="fotos.length === 0" class="empty-photos">
                                <i class="pi pi-camera"></i>
                                <span>Sube tus primeras fotos</span>
                                <p>Mínimo 1 foto para completar tu perfil</p>
                            </div>
                        </div>
                    </div>

                    <!-- Intereses -->
                    <div class="profile-card">
                        <div class="profile-card__header">
                            <h3><i class="pi pi-star"></i> Intereses</h3>
                            <span class="interest-count">{{ interesesCount }} seleccionados</span>
                        </div>
                        <div class="profile-card__body">
                            <p class="interests-label">Tus intereses</p>
                            <div class="chip-group">
                                <button
                                    v-for="item in intereses"
                                    :key="item.label"
                                    class="chip"
                                    :class="{ active: item.selected }"
                                    @click="isEditing ? toggle(intereses, item) : null"
                                    :disabled="!isEditing"
                                >
                                    <i v-if="item.icon" :class="item.icon"></i>
                                    {{ item.label }}
                                </button>
                            </div>

                            <p class="interests-label mt-3">Qué estás buscando</p>
                            <div class="chip-group">
                                <button
                                    v-for="item in buscando"
                                    :key="item.label"
                                    class="chip"
                                    :class="{ active: item.selected }"
                                    @click="isEditing ? toggle(buscando, item) : null"
                                    :disabled="!isEditing"
                                >
                                    <i v-if="item.icon" :class="item.icon"></i>
                                    {{ item.label }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Visibilidad de fotos -->
                    <div class="profile-card">
                        <div class="profile-card__header">
                            <h3><i class="pi pi-eye"></i> Visibilidad de fotos</h3>
                        </div>
                        <div class="profile-card__body">
                            <div class="visibility-options">
                                <label class="visibility-option" :class="{ active: form.visibilidadFotos === 'todos' }">
                                    <input type="radio" value="todos" v-model="form.visibilidadFotos" :disabled="!isEditing" />
                                    <span class="visibility-option__content">
                                        <i class="pi pi-globe"></i>
                                        <span>Todos los miembros</span>
                                    </span>
                                </label>
                                <label class="visibility-option" :class="{ active: form.visibilidadFotos === 'matches' }">
                                    <input type="radio" value="matches" v-model="form.visibilidadFotos" :disabled="!isEditing" />
                                    <span class="visibility-option__content">
                                        <i class="pi pi-heart"></i>
                                        <span>Solo mis matches</span>
                                    </span>
                                </label>
                                <label class="visibility-option" :class="{ active: form.visibilidadFotos === 'nadie' }">
                                    <input type="radio" value="nadie" v-model="form.visibilidadFotos" :disabled="!isEditing" />
                                    <span class="visibility-option__content">
                                        <i class="pi pi-lock"></i>
                                        <span>Nadie</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* =========================================================================
   TOKENS
   ========================================================================= */
.profile-page {
  --brand: #C81E3A;
  --brand-dark: #A6152D;
  --brand-soft: #FBEAEC;
  --brand-gradient: linear-gradient(135deg, #C81E3A 0%, #E85A72 100%);
  --ink: #171412;
  --ink-soft: #4B4744;
  --muted: #8A8481;
  --muted-light: #B7B2AF;
  --line: #ECE9E7;
  --surface: #FAF8F7;
  --white: #FFFFFF;
  --success: #1fbf5c;
  --success-soft: #E8F5E9;
  --error: #E53E3E;
  --shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  --shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.1);
  --shadow-xl: 0 20px 60px rgba(0, 0, 0, 0.15);

  --font-serif: 'Fraunces', Georgia, serif;
  --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;
  --radius-sm: 10px;
  --radius-md: 16px;
  --radius-lg: 24px;
  --radius-full: 999px;

  font-family: var(--font-sans);
  color: var(--ink);
  background: #f0f2f5;
  -webkit-font-smoothing: antialiased;
  min-height: 100vh;
}

/* =========================================================================
   HEADER PERFIL
   ========================================================================= */
.profile-header {
  max-width: 1400px;
  margin: 0 auto 1.5rem;
  padding: 2rem 2.5rem;
  background: var(--white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow);
  display: flex;
  align-items: center;
  gap: 2.5rem;
  flex-wrap: wrap;
  position: relative;
  overflow: hidden;
}

.profile-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--brand-gradient);
}

@media (max-width: 768px) {
  .profile-header {
    flex-direction: column;
    text-align: center;
    padding: 1.5rem 1rem;
    gap: 1.5rem;
  }
}

.profile-header__avatar-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.profile-header__avatar-ring {
  padding: 4px;
  border-radius: 50%;
  background: var(--brand-gradient);
  box-shadow: 0 0 30px rgba(200, 30, 58, 0.15);
  animation: pulse-ring 3s ease-in-out infinite;
}

@keyframes pulse-ring {
  0%, 100% { box-shadow: 0 0 30px rgba(200, 30, 58, 0.15); }
  50% { box-shadow: 0 0 50px rgba(200, 30, 58, 0.25); }
}

.profile-header__avatar {
  position: relative;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid var(--white);
  background: var(--surface);
}

.profile-header__avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-header__badge {
  position: absolute;
  bottom: 4px;
  right: 4px;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1fbf5c, #34d399);
  color: var(--white);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  border: 2px solid var(--white);
}

.profile-header__status {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.7rem;
  color: var(--success);
  font-weight: 600;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--success);
  animation: pulse-dot 2s ease-in-out infinite;
}

@keyframes pulse-dot {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(0.8); }
}

@media (max-width: 768px) {
  .profile-header__avatar {
    width: 100px;
    height: 100px;
  }
}

.profile-header__info {
  flex: 1;
  min-width: 200px;
}

.profile-header__name-row {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  flex-wrap: wrap;
}

.profile-header__name {
  font-family: var(--font-serif);
  font-size: 2rem;
  font-weight: 600;
  margin: 0;
  background: var(--brand-gradient);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

@media (max-width: 768px) {
  .profile-header__name-row {
    justify-content: center;
  }
}

.verified-badge {
  font-size: 0.7rem;
  font-weight: 600;
  color: var(--success);
  background: var(--success-soft);
  padding: 0.15rem 0.7rem;
  border-radius: var(--radius-full);
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.member-badge {
  font-size: 0.7rem;
  font-weight: 600;
  color: var(--brand);
  background: var(--brand-soft);
  padding: 0.15rem 0.7rem;
  border-radius: var(--radius-full);
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.profile-header__bio {
  font-size: 0.95rem;
  color: var(--ink-soft);
  margin: 0.4rem 0 0.6rem;
  max-width: 500px;
  line-height: 1.6;
}

@media (max-width: 768px) {
  .profile-header__bio {
    max-width: 100%;
  }
}

.profile-header__meta {
  display: flex;
  gap: 1.5rem;
  font-size: 0.8rem;
  color: var(--muted);
  flex-wrap: wrap;
}

.profile-header__meta span {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.profile-header__meta i {
  font-size: 0.75rem;
  color: var(--brand);
}

@media (max-width: 768px) {
  .profile-header__meta {
    justify-content: center;
  }
}

.profile-header__actions {
  margin-left: auto;
}

@media (max-width: 768px) {
  .profile-header__actions {
    margin-left: 0;
    width: 100%;
  }
}

.btn-edit {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.6rem 1.5rem;
  border: none;
  border-radius: var(--radius-full);
  font-weight: 600;
  font-size: 0.82rem;
  font-family: var(--font-sans);
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  background: var(--surface);
  color: var(--ink);
  border: 1.5px solid var(--line);
}

.btn-edit:hover {
  background: var(--white);
  transform: translateY(-2px);
  box-shadow: var(--shadow-hover);
}

.btn-edit i {
  font-size: 0.85rem;
}

.profile-header__actions-edit {
  display: flex;
  gap: 0.6rem;
}

@media (max-width: 768px) {
  .profile-header__actions-edit {
    justify-content: center;
    flex-wrap: wrap;
  }
}

.btn-edit--cancel {
  background: var(--surface);
  color: var(--error);
  border-color: var(--line);
}

.btn-edit--cancel:hover {
  background: #fee8ea;
  border-color: var(--error);
}

.btn-edit--save {
  background: var(--brand);
  color: var(--white);
  border-color: var(--brand);
  box-shadow: 0 4px 15px rgba(200, 30, 58, 0.25);
}

.btn-edit--save:hover {
  background: var(--brand-dark);
  border-color: var(--brand-dark);
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(200, 30, 58, 0.35);
}

.btn-edit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}

/* =========================================================================
   CONTENT
   ========================================================================= */
.profile-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 1.5rem 2rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 1024px) {
  .profile-content {
    grid-template-columns: 1fr;
  }
}

/* =========================================================================
   CARDS
   ========================================================================= */
.profile-card {
  background: var(--white);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow);
  margin-bottom: 1.5rem;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid transparent;
}

.profile-card:hover {
  box-shadow: var(--shadow-hover);
  border-color: var(--line);
}

.profile-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.8rem 1.2rem;
  border-bottom: 1px solid var(--line);
  background: var(--surface);
}

.profile-card__header h3 {
  font-size: 0.85rem;
  font-weight: 700;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--ink);
}

.profile-card__header h3 i {
  color: var(--brand);
  font-size: 0.95rem;
}

.profile-card__body {
  padding: 1.2rem;
}

/* =========================================================================
   VISIBILITY
   ========================================================================= */
.visibility-options {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.visibility-option {
  flex: 1;
  min-width: 80px;
  cursor: pointer;
}

.visibility-option input {
  display: none;
}

.visibility-option__content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  padding: 0.4rem 0.7rem;
  border: 2px solid var(--line);
  border-radius: var(--radius-sm);
  font-size: 0.72rem;
  font-weight: 500;
  color: var(--ink-soft);
  background: var(--white);
  transition: all 0.3s ease;
}

.visibility-option:hover .visibility-option__content {
  border-color: var(--brand);
  background: var(--brand-soft);
}

.visibility-option.active .visibility-option__content {
  border-color: var(--brand);
  background: var(--brand-soft);
  color: var(--brand);
}

.visibility-option__content i {
  font-size: 0.8rem;
}

.visibility-option__content span {
  font-size: 0.65rem;
}

/* =========================================================================
   RESTO DE ESTILOS
   ========================================================================= */
.edit-badge {
  font-size: 0.6rem;
  font-weight: 600;
  color: var(--brand);
  background: var(--brand-soft);
  padding: 0.15rem 0.6rem;
  border-radius: var(--radius-full);
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.photo-count,
.interest-count {
  font-size: 0.65rem;
  font-weight: 600;
  color: var(--muted);
  background: var(--surface);
  padding: 0.1rem 0.6rem;
  border-radius: var(--radius-full);
}

.profile-bio {
  font-size: 0.9rem;
  line-height: 1.8;
  color: var(--ink-soft);
}

.form-textarea {
  width: 100%;
  border: 1.5px solid var(--line);
  border-radius: var(--radius-sm);
  padding: 0.8rem 1rem;
  font-size: 0.9rem;
  font-family: var(--font-sans);
  resize: vertical;
  transition: all 0.3s ease;
  background: var(--white);
  color: var(--ink);
}

.form-textarea:focus {
  border-color: var(--brand);
  box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.08);
  outline: none;
}

.char-counter {
  text-align: right;
  font-size: 0.65rem;
  color: var(--muted-light);
  margin-top: 0.2rem;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.8rem;
}

@media (max-width: 600px) {
  .info-grid {
    grid-template-columns: 1fr;
  }
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.info-item__label {
  font-size: 0.65rem;
  font-weight: 600;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.info-item__value {
  font-size: 0.85rem;
  color: var(--ink);
  font-weight: 500;
}

.form-input {
  width: 100%;
  border: 1.5px solid var(--line);
  border-radius: var(--radius-sm);
  padding: 0.4rem 0.7rem;
  font-size: 0.82rem;
  font-family: var(--font-sans);
  transition: all 0.3s ease;
  background: var(--white);
  color: var(--ink);
}

.form-input:focus {
  border-color: var(--brand);
  box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.08);
  outline: none;
}

/* =========================================================================
   PROFILE TYPE
   ========================================================================= */
.profile-type-selector {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.6rem;
}

@media (max-width: 600px) {
  .profile-type-selector {
    grid-template-columns: 1fr;
  }
}

.type-option {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.6rem 0.8rem;
  border: 2px solid var(--line);
  border-radius: var(--radius-sm);
  background: var(--white);
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.type-option:hover:not(:disabled) {
  border-color: var(--brand);
  transform: translateY(-2px);
  box-shadow: var(--shadow);
}

.type-option:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.type-option.active {
  border-color: var(--brand);
  background: var(--brand-soft);
  box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.08);
}

.type-option__icon {
  width: 34px;
  height: 34px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  flex-shrink: 0;
}

.type-option__icon.personal {
  background: #EBF5FF;
  color: #2B6CB0;
}

.type-option__icon.couple {
  background: #FEE2E2;
  color: var(--brand);
}

.type-option.active .type-option__icon.personal {
  background: #2B6CB0;
  color: var(--white);
}

.type-option.active .type-option__icon.couple {
  background: var(--brand);
  color: var(--white);
}

.type-option__info {
  flex: 1;
}

.type-option__info strong {
  display: block;
  font-size: 0.8rem;
}

.type-option__info span {
  font-size: 0.65rem;
  color: var(--muted);
}

.type-option__check {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 2px solid var(--line);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: all 0.3s ease;
}

.type-option__check.checked {
  background: var(--brand);
  border-color: var(--brand);
  color: var(--white);
  font-size: 0.55rem;
}

.couple-info {
  border-top: 1px dashed var(--line);
  padding-top: 0.8rem;
  margin-top: 0.8rem;
}

.couple-info__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.6rem;
}

@media (max-width: 600px) {
  .couple-info__grid {
    grid-template-columns: 1fr;
  }
}

.couple-info__item label {
  display: block;
  font-size: 0.65rem;
  font-weight: 600;
  color: var(--muted);
  margin-bottom: 0.15rem;
}

/* =========================================================================
   PHOTOS
   ========================================================================= */
.photo-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.5rem;
}

@media (max-width: 600px) {
  .photo-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.photo-item {
  position: relative;
  aspect-ratio: 1;
  border-radius: var(--radius-sm);
  overflow: hidden;
  background: var(--surface);
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.photo-item:hover {
  transform: scale(1.03);
  box-shadow: var(--shadow-hover);
  z-index: 2;
}

.photo-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.photo-item__badge {
  position: absolute;
  bottom: 4px;
  left: 4px;
  font-size: 0.45rem;
  font-weight: 700;
  padding: 0.1rem 0.4rem;
  border-radius: 4px;
  color: var(--white);
  backdrop-filter: blur(4px);
}

.photo-item__badge--principal {
  background: rgba(200, 30, 58, 0.9);
}

.photo-item__badge--existing {
  background: rgba(31, 191, 92, 0.9);
}

.photo-item__badge--deleted {
  background: rgba(229, 62, 62, 0.9);
}

.photo-item__actions {
  position: absolute;
  top: 3px;
  right: 3px;
  display: flex;
  gap: 3px;
  opacity: 0;
  transition: all 0.3s ease;
}

.photo-item:hover .photo-item__actions {
  opacity: 1;
}

.photo-item__btn {
  width: 22px;
  height: 22px;
  border: none;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 0.55rem;
  transition: all 0.2s ease;
  color: var(--white);
  backdrop-filter: blur(4px);
}

.photo-item__btn--delete {
  background: rgba(229, 62, 62, 0.85);
}

.photo-item__btn--delete:hover {
  background: var(--error);
  transform: scale(1.1);
}

.photo-item__btn--restore {
  background: rgba(31, 191, 92, 0.85);
}

.photo-item__btn--restore:hover {
  background: var(--success);
  transform: scale(1.1);
}

.photo-item--upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.2rem;
  border: 2px dashed var(--line);
  background: var(--surface);
  color: var(--muted-light);
  font-size: 0.65rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.photo-item--upload:hover {
  border-color: var(--brand);
  color: var(--brand);
  background: var(--brand-soft);
  transform: scale(1.03);
}

.photo-item--upload i {
  font-size: 1.2rem;
}

.photo-item--upload input {
  display: none;
}

.empty-photos {
  text-align: center;
  padding: 2rem 0.5rem;
  color: var(--muted);
}

.empty-photos i {
  font-size: 2rem;
  color: var(--muted-light);
  display: block;
  margin-bottom: 0.4rem;
}

.empty-photos span {
  font-size: 0.85rem;
  font-weight: 600;
  display: block;
}

.empty-photos p {
  font-size: 0.75rem;
  margin: 0.2rem 0 0;
}

/* =========================================================================
   INTERESTS
   ========================================================================= */
.interests-label {
  font-size: 0.72rem;
  color: var(--muted);
  margin: 0 0 0.4rem;
  font-weight: 500;
}

.mt-3 {
  margin-top: 0.8rem;
}

.chip-group {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
}

.chip {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.3rem 0.8rem;
  border: 1.5px solid var(--line);
  border-radius: var(--radius-full);
  background: var(--white);
  font-size: 0.7rem;
  font-weight: 500;
  color: var(--ink-soft);
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.chip:hover:not(:disabled) {
  border-color: var(--brand);
  transform: translateY(-2px);
  box-shadow: var(--shadow);
}

.chip:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.chip.active {
  background: var(--brand);
  border-color: var(--brand);
  color: var(--white);
  box-shadow: 0 4px 15px rgba(200, 30, 58, 0.25);
}

.chip i {
  font-size: 0.6rem;
}

/* =========================================================================
   ANIMATIONS
   ========================================================================= */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>