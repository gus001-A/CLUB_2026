<script setup>
import { reactive, ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

/* ---------------------------------------------------------------
 * Props
 * --------------------------------------------------------------- */
const props = defineProps({
    user: {
        type: Object,
        required: true,
        default: () => ({
            id: null,
            nombre: '',
            apodo: '',
            email: '',
            telefono: '',
            ciudad: '',
            fecha_nacimiento: null,
            estado: 'incompleto',
            rol: 'usuario',
            avatar: '/images/shared/avatar-default.jpg',
            foto_principal: null,
            created_at: null,
        })
    }
});

/* ---------------------------------------------------------------
 * Estado reactivo local (se actualiza sin recargar)
 * --------------------------------------------------------------- */
const userData = reactive({
    nombre: props.user.nombre || '',
    apodo: props.user.apodo || '',
    email: props.user.email || '',
    telefono: props.user.telefono || '',
    ciudad: props.user.ciudad || '',
    fecha_nacimiento: props.user.fecha_nacimiento || '',
    foto_principal: props.user.foto_principal || null,
    estado: props.user.estado || 'incompleto',
    avatar: props.user.avatar || '/images/shared/avatar-default.jpg',
    created_at: props.user.created_at || null,
    rol: props.user.rol || 'usuario',
});

/* ---------------------------------------------------------------
 * Formulario
 * --------------------------------------------------------------- */
const form = reactive({
    nombre: userData.nombre || '',
    apodo: userData.apodo || '',
    email: userData.email || '',
    telefono: userData.telefono || '',
    ciudad: userData.ciudad || '',
    fecha_nacimiento: userData.fecha_nacimiento || '',
});

// Formulario de cambio de contraseña
const passwordForm = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// Estados para mostrar/ocultar contraseñas
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const passwordFocused = ref(false);

function formatearFechaParaInput(fecha) {
    if (!fecha) return '';
    if (typeof fecha === 'string' && fecha.match(/^\d{4}-\d{2}-\d{2}$/)) {
        return fecha;
    }
    try {
        const date = new Date(fecha);
        if (isNaN(date.getTime())) return '';
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    } catch {
        return '';
    }
}

const fechaNacimientoFormateada = computed({
    get: () => formatearFechaParaInput(form.fecha_nacimiento),
    set: (value) => { form.fecha_nacimiento = value; }
});

const isSaving = ref(false);
const isChangingPassword = ref(false);
const isEditing = ref(false);
const fieldErrors = ref({});
const passwordErrors = ref({});

/* ---------------------------------------------------------------
 * Computed - Información del usuario (usando userData reactivo)
 * --------------------------------------------------------------- */
const avatarUrl = computed(() => {
    if (userData.foto_principal) {
        if (userData.foto_principal.startsWith('http://') || 
            userData.foto_principal.startsWith('https://') ||
            userData.foto_principal.startsWith('/storage/')) {
            return userData.foto_principal;
        }
        return '/storage/' + userData.foto_principal.replace(/^\/+/, '');
    }
    return userData.avatar || '/images/shared/avatar-default.jpg';
});

const edad = computed(() => {
    if (!form.fecha_nacimiento) return null;
    const fecha = new Date(form.fecha_nacimiento);
    if (isNaN(fecha.getTime())) return null;
    const hoy = new Date();
    let edad = hoy.getFullYear() - fecha.getFullYear();
    const mes = hoy.getMonth() - fecha.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < fecha.getDate())) edad--;
    return edad;
});

const fechaNacimientoFormateadaMostrar = computed(() => {
    if (!form.fecha_nacimiento) return 'No especificada';
    try {
        const date = new Date(form.fecha_nacimiento);
        if (isNaN(date.getTime())) return 'No especificada';
        return date.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    } catch {
        return 'No especificada';
    }
});

const fechaRegistro = computed(() => {
    if (!userData.created_at) return 'Fecha no disponible';
    try {
        return new Date(userData.created_at).toLocaleDateString('es-ES', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });
    } catch {
        return 'Fecha no disponible';
    }
});

/* ---------------------------------------------------------------
 * Computed - Estados y roles (usando userData reactivo)
 * --------------------------------------------------------------- */
const estadoActual = computed(() => {
    const estados = {
        'verificado': { 
            label: 'Verificado', 
            class: 'verified',
            icon: 'pi pi-check-circle',
            description: 'Tu cuenta está verificada y activa'
        },
        'pendiente': { 
            label: 'Pendiente de verificación', 
            class: 'pending',
            icon: 'pi pi-clock',
            description: 'Tu cuenta está en proceso de verificación'
        },
        'incompleto': { 
            label: 'Perfil incompleto', 
            class: 'incomplete',
            icon: 'pi pi-pencil',
            description: 'Completa tu perfil para activar tu cuenta'
        },
        'completo': { 
            label: 'Perfil completo', 
            class: 'completed',
            icon: 'pi pi-check-circle',
            description: 'Tu perfil está completo y visible'
        },
        'bloqueado': { 
            label: 'Bloqueado', 
            class: 'blocked',
            icon: 'pi pi-ban',
            description: 'Tu cuenta ha sido bloqueada'
        },
    };
    
    let key = userData.estado || 'pendiente';
    if (userData.estado === 'verificado') key = 'verificado';
    else if (userData.estado === 'incompleto' && form.nombre && form.apodo && form.email) key = 'pendiente';
    else if (userData.estado === 'completo') key = 'completo';
    
    return estados[key] || estados['pendiente'];
});

const rolActual = computed(() => {
    const roles = {
        'admin': { label: 'Administrador', icon: 'pi pi-shield', color: '#7B1FA2' },
        'creador': { label: 'Creador de contenido', icon: 'pi pi-star', color: '#283593' },
        'usuario': { label: 'Usuario', icon: 'pi pi-user', color: '#4B4744' },
        'invitado': { label: 'Invitado', icon: 'pi pi-user', color: '#8A8481' },
    };
    return roles[userData.rol] || roles['usuario'];
});

const estaVerificado = computed(() => userData.estado === 'verificado');

/* ---------------------------------------------------------------
 * VALIDACIONES MEJORADAS
 * --------------------------------------------------------------- */

// Validación de email
function isValidEmail(email) {
    // Regex más estricto para email
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return emailRegex.test(email);
}

// Validación de teléfono (10 dígitos, solo números)
function isValidPhone(telefono) {
    if (!telefono) return true; // Opcional
    // Solo números, exactamente 10 dígitos
    const phoneRegex = /^[0-9]{10}$/;
    return phoneRegex.test(telefono.replace(/\s/g, ''));
}

// Validación de nombre (solo letras y espacios)
function isValidName(nombre) {
    const nameRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
    return nameRegex.test(nombre);
}

// Validación de apodo (letras, números y guión bajo)
function isValidApodo(apodo) {
    const apodoRegex = /^[a-zA-Z0-9_]+$/;
    return apodoRegex.test(apodo);
}

function validateForm() {
    const errors = {};
    let isValid = true;

    // Validar nombre
    if (!form.nombre || form.nombre.trim().length < 2) {
        errors.nombre = 'El nombre debe tener al menos 2 caracteres.';
        isValid = false;
    } else if (form.nombre.length > 100) {
        errors.nombre = 'El nombre no puede tener más de 100 caracteres.';
        isValid = false;
    } else if (!isValidName(form.nombre)) {
        errors.nombre = 'El nombre solo puede contener letras y espacios.';
        isValid = false;
    }

    // Validar apodo
    if (!form.apodo || form.apodo.trim().length < 3) {
        errors.apodo = 'El apodo debe tener al menos 3 caracteres.';
        isValid = false;
    } else if (form.apodo.length > 20) {
        errors.apodo = 'El apodo no puede tener más de 20 caracteres.';
        isValid = false;
    } else if (!isValidApodo(form.apodo)) {
        errors.apodo = 'El apodo solo puede contener letras, números y guión bajo.';
        isValid = false;
    }

    // Validar email
    if (!form.email) {
        errors.email = 'El email es obligatorio.';
        isValid = false;
    } else if (!isValidEmail(form.email)) {
        errors.email = 'Ingresa un email válido (ejemplo: usuario@dominio.com).';
        isValid = false;
    }

    // Validar teléfono
    if (form.telefono) {
        const telefonoLimpio = form.telefono.replace(/\s/g, '');
        if (!isValidPhone(telefonoLimpio)) {
            errors.telefono = 'El teléfono debe tener exactamente 10 dígitos numéricos.';
            isValid = false;
        }
    }

    // Validar fecha de nacimiento
    if (form.fecha_nacimiento) {
        const fecha = new Date(form.fecha_nacimiento);
        if (!isNaN(fecha.getTime())) {
            const hoy = new Date();
            let edad = hoy.getFullYear() - fecha.getFullYear();
            const mes = hoy.getMonth() - fecha.getMonth();
            if (mes < 0 || (mes === 0 && hoy.getDate() < fecha.getDate())) edad--;
            if (edad < 18) {
                errors.fecha_nacimiento = 'Debes ser mayor de 18 años.';
                isValid = false;
            }
        } else {
            errors.fecha_nacimiento = 'Ingresa una fecha de nacimiento válida.';
            isValid = false;
        }
    }

    fieldErrors.value = errors;
    return isValid;
}

function validatePassword() {
    const errors = {};
    let isValid = true;

    if (!passwordForm.current_password) {
        errors.current_password = 'La contraseña actual es obligatoria.';
        isValid = false;
    }

    if (!passwordForm.password) {
        errors.password = 'La nueva contraseña es obligatoria.';
        isValid = false;
    } else if (passwordForm.password.length < 8) {
        errors.password = 'La contraseña debe tener al menos 8 caracteres.';
        isValid = false;
    }

    if (!passwordForm.password_confirmation) {
        errors.password_confirmation = 'Debes confirmar la nueva contraseña.';
        isValid = false;
    } else if (passwordForm.password !== passwordForm.password_confirmation) {
        errors.password_confirmation = 'Las contraseñas no coinciden.';
        isValid = false;
    }

    passwordErrors.value = errors;
    return isValid;
}

/* ---------------------------------------------------------------
 * Requisitos de contraseña en tiempo real
 * --------------------------------------------------------------- */
const passwordRequirements = computed(() => {
    const pwd = passwordForm.password || '';
    return [
        { 
            label: 'Mínimo 8 caracteres', 
            met: pwd.length >= 8,
            icon: pwd.length >= 8 ? 'pi pi-check-circle' : 'pi pi-circle'
        },
        { 
            label: 'Al menos una mayúscula', 
            met: /[A-Z]/.test(pwd),
            icon: /[A-Z]/.test(pwd) ? 'pi pi-check-circle' : 'pi pi-circle'
        },
        { 
            label: 'Al menos una minúscula', 
            met: /[a-z]/.test(pwd),
            icon: /[a-z]/.test(pwd) ? 'pi pi-check-circle' : 'pi pi-circle'
        },
        { 
            label: 'Al menos un número', 
            met: /[0-9]/.test(pwd),
            icon: /[0-9]/.test(pwd) ? 'pi pi-check-circle' : 'pi pi-circle'
        },
        { 
            label: 'Al menos un carácter especial', 
            met: /[^A-Za-z0-9]/.test(pwd),
            icon: /[^A-Za-z0-9]/.test(pwd) ? 'pi pi-check-circle' : 'pi pi-circle'
        },
    ];
});

const passwordStrength = computed(() => {
    const met = passwordRequirements.value.filter(r => r.met).length;
    if (met === 0) return { label: 'Muy débil', color: '#E53E3E', width: '0%' };
    if (met <= 2) return { label: 'Débil', color: '#ED6C02', width: '40%' };
    if (met <= 3) return { label: 'Regular', color: '#ED6C02', width: '60%' };
    if (met <= 4) return { label: 'Fuerte', color: '#1fbf5c', width: '80%' };
    return { label: 'Muy fuerte', color: '#1fbf5c', width: '100%' };
});

/* ---------------------------------------------------------------
 * Acciones - SIN RECARGAR LA PÁGINA
 * --------------------------------------------------------------- */
function guardarCambios() {
    if (!validateForm()) {
        if (window.showToast) {
            window.showToast({
                type: 'error',
                title: 'Errores en el formulario',
                message: 'Revisa los campos marcados en rojo.',
                duration: 5000,
            });
        }
        return;
    }

    isSaving.value = true;

    router.put(route('usuario.actualizar'), form, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
            isSaving.value = false;
            isEditing.value = false;
            fieldErrors.value = {};

            // ✅ ACTUALIZAR DATOS LOCALMENTE SIN RECARGAR
            if (page.props?.user) {
                const u = page.props.user;
                userData.nombre = u.nombre || '';
                userData.apodo = u.apodo || '';
                userData.email = u.email || '';
                userData.telefono = u.telefono || '';
                userData.ciudad = u.ciudad || '';
                userData.fecha_nacimiento = u.fecha_nacimiento || '';
                userData.foto_principal = u.foto_principal || null;
                userData.estado = u.estado || 'incompleto';
                userData.avatar = u.avatar || '/images/shared/avatar-default.jpg';
                userData.rol = u.rol || 'usuario';
                
                // Actualizar también el form
                form.nombre = userData.nombre;
                form.apodo = userData.apodo;
                form.email = userData.email;
                form.telefono = userData.telefono;
                form.ciudad = userData.ciudad;
                form.fecha_nacimiento = userData.fecha_nacimiento;
            }

            if (window.showToast) {
                if (page.props.flash?.toast) {
                    window.showToast({
                        type: page.props.flash.toast.type || 'success',
                        title: page.props.flash.toast.title || 'Datos actualizados',
                        message: page.props.flash.toast.message || 'Tu información personal se ha guardado correctamente.',
                        duration: page.props.flash.toast.duration || 3000,
                    });
                } else {
                    window.showToast({
                        type: 'success',
                        title: 'Datos actualizados',
                        message: 'Tu información personal se ha guardado correctamente.',
                        duration: 3000,
                    });
                }
            }
            
            console.log('✅ Datos actualizados sin recargar la página');
        },
        onError: (errors) => {
            isSaving.value = false;
            if (errors && typeof errors === 'object') {
                fieldErrors.value = errors;
            }
            if (window.showToast) {
                const firstError = Object.values(errors)[0];
                window.showToast({
                    type: 'error',
                    title: 'Error al guardar',
                    message: Array.isArray(firstError) ? firstError[0] : firstError || 'Ocurrió un error inesperado.',
                    duration: 5000,
                });
            }
        }
    });
}

function cambiarPassword() {
    if (!validatePassword()) {
        if (window.showToast) {
            window.showToast({
                type: 'error',
                title: 'Errores en el formulario',
                message: 'Revisa los campos marcados en rojo.',
                duration: 5000,
            });
        }
        return;
    }

    isChangingPassword.value = true;

    router.put(route('usuario.cambiar-password'), passwordForm, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (page) => {
            isChangingPassword.value = false;
            passwordErrors.value = {};
            passwordForm.current_password = '';
            passwordForm.password = '';
            passwordForm.password_confirmation = '';

            if (window.showToast) {
                if (page.props.flash?.toast) {
                    window.showToast({
                        type: page.props.flash.toast.type || 'success',
                        title: page.props.flash.toast.title || 'Contraseña actualizada',
                        message: page.props.flash.toast.message || 'Tu contraseña se ha cambiado correctamente.',
                        duration: page.props.flash.toast.duration || 3000,
                    });
                } else {
                    window.showToast({
                        type: 'success',
                        title: 'Contraseña actualizada',
                        message: 'Tu contraseña se ha cambiado correctamente.',
                        duration: 3000,
                    });
                }
            }
            
            console.log('✅ Contraseña actualizada sin recargar la página');
        },
        onError: (errors) => {
            isChangingPassword.value = false;
            if (errors && typeof errors === 'object') {
                passwordErrors.value = errors;
            }
            if (window.showToast) {
                const firstError = Object.values(errors)[0];
                window.showToast({
                    type: 'error',
                    title: 'Error al cambiar contraseña',
                    message: Array.isArray(firstError) ? firstError[0] : firstError || 'Ocurrió un error inesperado.',
                    duration: 5000,
                });
            }
        }
    });
}

function cancelarEdicion() {
    isEditing.value = false;
    fieldErrors.value = {};
    // Restaurar desde userData (que tiene los últimos datos guardados)
    Object.assign(form, {
        nombre: userData.nombre || '',
        apodo: userData.apodo || '',
        email: userData.email || '',
        telefono: userData.telefono || '',
        ciudad: userData.ciudad || '',
        fecha_nacimiento: userData.fecha_nacimiento || '',
    });
}

function cancelarPassword() {
    passwordForm.current_password = '';
    passwordForm.password = '';
    passwordForm.password_confirmation = '';
    passwordErrors.value = {};
}

let apodoTimeout = null;

function verificarApodo() {
    if (!form.apodo || form.apodo.length < 3) return;
    clearTimeout(apodoTimeout);
    apodoTimeout = setTimeout(() => {
        axios.get(route('usuario.verificar-apodo'), {
            params: { apodo: form.apodo }
        })
        .then(response => {
            if (!response.data.disponible) {
                fieldErrors.value.apodo = 'Este apodo no está disponible.';
            } else {
                if (fieldErrors.value.apodo === 'Este apodo no está disponible.') {
                    delete fieldErrors.value.apodo;
                }
            }
        })
        .catch(() => {});
    }, 500);
}

// Función para toggle de mostrar contraseña
function togglePassword(field) {
    if (field === 'current') showCurrentPassword.value = !showCurrentPassword.value;
    else if (field === 'new') showNewPassword.value = !showNewPassword.value;
    else if (field === 'confirm') showConfirmPassword.value = !showConfirmPassword.value;
}

// Función para formatear teléfono mientras se escribe
function formatPhoneNumber(event) {
    const input = event.target;
    // Eliminar todo lo que no sea número
    let value = input.value.replace(/\D/g, '');
    // Limitar a 10 dígitos
    if (value.length > 10) {
        value = value.slice(0, 10);
    }
    form.telefono = value;
}
</script>

<template>
    <AppLayout activeNav="configuracion">
        <Head title="Editar datos de usuario" />

        <div class="profile-edit">
            <!-- ============================================================ -->
            <!-- HEADER - CON ESTADO DE CUENTA -->
            <!-- ============================================================ -->
            <div class="profile-edit__header">
                <div class="profile-edit__avatar">
                    <div class="profile-edit__avatar-ring">
                        <div class="profile-edit__avatar-image">
                            <img 
                                :src="avatarUrl" 
                                :alt="form.nombre"
                                @error="(e) => { e.target.src = '/images/shared/avatar-default.jpg' }"
                            />
                            <span v-if="estaVerificado" class="profile-edit__avatar-badge">
                                <i class="pi pi-check"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="profile-edit__info">
                    <div class="profile-edit__name">
                        <h1>{{ form.nombre || 'Usuario' }}</h1>
                        <span class="badge" :class="estadoActual.class">
                            <i :class="estadoActual.icon"></i>
                            {{ estadoActual.label }}
                        </span>
                    </div>
                    
                    <p class="profile-edit__subtitle">
                        <i class="pi pi-user-edit"></i> 
                        {{ isEditing ? 'Editando información personal' : 'Gestiona tu información personal' }}
                    </p>
                    
                    <div class="profile-edit__meta">
                        <span>
                            <i :class="rolActual.icon" :style="{ color: rolActual.color }"></i>
                            {{ rolActual.label }}
                        </span>
                        <span v-if="edad !== null">
                            <i class="pi pi-calendar"></i> {{ edad }} años
                        </span>
                        <span>
                            <i class="pi pi-calendar-plus"></i> Desde {{ fechaRegistro }}
                        </span>
                        <span :class="'status-badge status-badge--' + estadoActual.class">
                            <i :class="estadoActual.icon"></i>
                            {{ estadoActual.label }}
                        </span>
                    </div>
                </div>

                <div class="profile-edit__actions">
                    <button 
                        v-if="!isEditing" 
                        class="btn btn--primary" 
                        @click="isEditing = true"
                    >
                        <i class="pi pi-pencil"></i> Editar
                    </button>
                    <div v-else class="profile-edit__actions-group">
                        <button class="btn btn--secondary" @click="cancelarEdicion" :disabled="isSaving">
                            <i class="pi pi-times"></i> Cancelar
                        </button>
                        <button class="btn btn--primary" @click="guardarCambios" :disabled="isSaving">
                            <i class="pi" :class="isSaving ? 'pi-spin pi-spinner' : 'pi-save'"></i>
                            {{ isSaving ? 'Guardando...' : 'Guardar' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- CONTENIDO - 2 COLUMNAS -->
            <!-- ============================================================ -->
            <div class="profile-edit__grid">
                <!-- ========================================================== -->
                <!-- COLUMNA IZQUIERDA - Información personal -->
                <!-- ========================================================== -->
                <div class="profile-edit__column">
                    <div class="card card--form">
                        <div class="card__header">
                            <div class="card__header-left">
                                <i class="pi pi-user card__header-icon"></i>
                                <h3>Información personal</h3>
                            </div>
                            <span v-if="isEditing" class="card__badge">
                                <i class="pi pi-pencil"></i> Editando
                            </span>
                        </div>
                        <div class="card__body">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Nombre completo *</label>
                                    <span v-if="!isEditing" class="form-value">{{ form.nombre || 'No especificado' }}</span>
                                    <input 
                                        v-else 
                                        v-model="form.nombre" 
                                        class="form-input"
                                        :class="{ 'is-invalid': fieldErrors.nombre }"
                                        placeholder="Tu nombre (solo letras)"
                                    />
                                    <span v-if="fieldErrors.nombre" class="form-error">{{ fieldErrors.nombre }}</span>
                                    <small class="form-help">Solo letras y espacios</small>
                                </div>

                                <div class="form-group">
                                    <label>Apodo *</label>
                                    <span v-if="!isEditing" class="form-value">@{{ form.apodo || 'No especificado' }}</span>
                                    <input 
                                        v-else 
                                        v-model="form.apodo" 
                                        class="form-input"
                                        :class="{ 'is-invalid': fieldErrors.apodo }"
                                        placeholder="@usuario"
                                        @input="verificarApodo"
                                    />
                                    <span v-if="fieldErrors.apodo" class="form-error">{{ fieldErrors.apodo }}</span>
                                    <small class="form-help">3-20 caracteres, solo letras, números y guión bajo</small>
                                </div>

                                <div class="form-group">
                                    <label>Correo electrónico *</label>
                                    <span v-if="!isEditing" class="form-value">{{ form.email || 'No especificado' }}</span>
                                    <input 
                                        v-else 
                                        v-model="form.email" 
                                        class="form-input"
                                        :class="{ 'is-invalid': fieldErrors.email }"
                                        placeholder="tu@email.com"
                                        type="email"
                                    />
                                    <span v-if="fieldErrors.email" class="form-error">{{ fieldErrors.email }}</span>
                                    <small class="form-help">Formato: usuario@dominio.com</small>
                                </div>

                                <div class="form-group">
                                    <label>Teléfono</label>
                                    <span v-if="!isEditing" class="form-value">{{ form.telefono || 'No especificado' }}</span>
                                    <input 
                                        v-else 
                                        v-model="form.telefono" 
                                        class="form-input"
                                        :class="{ 'is-invalid': fieldErrors.telefono }"
                                        placeholder="10 dígitos numéricos"
                                        maxlength="10"
                                        @input="formatPhoneNumber"
                                    />
                                    <span v-if="fieldErrors.telefono" class="form-error">{{ fieldErrors.telefono }}</span>
                                    <small class="form-help">Opcional. Exactamente 10 dígitos numéricos</small>
                                </div>

                                <div class="form-group">
                                    <label>Ciudad</label>
                                    <span v-if="!isEditing" class="form-value">{{ form.ciudad || 'No especificada' }}</span>
                                    <input 
                                        v-else 
                                        v-model="form.ciudad" 
                                        class="form-input"
                                        placeholder="Madrid, Barcelona, ..."
                                    />
                                </div>

                                <div class="form-group">
                                    <label>Fecha de nacimiento *</label>
                                    <span v-if="!isEditing" class="form-value">
                                        {{ fechaNacimientoFormateadaMostrar }}
                                        <span v-if="edad !== null" class="edad-tag">{{ edad }} años</span>
                                    </span>
                                    <input 
                                        v-else 
                                        type="date" 
                                        v-model="fechaNacimientoFormateada"
                                        class="form-input"
                                        :class="{ 'is-invalid': fieldErrors.fecha_nacimiento }"
                                    />
                                    <span v-if="fieldErrors.fecha_nacimiento" class="form-error">{{ fieldErrors.fecha_nacimiento }}</span>
                                    <small class="form-help">Debes ser mayor de 18 años</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================== -->
                <!-- COLUMNA DERECHA - Cambiar contraseña -->
                <!-- ========================================================== -->
                <div class="profile-edit__column">
                    <div class="card card--password">
                        <div class="card__header">
                            <div class="card__header-left">
                                <i class="pi pi-lock card__header-icon"></i>
                                <h3>Cambiar contraseña</h3>
                            </div>
                            <span class="card__badge card__badge--password">
                                <i class="pi pi-shield"></i> Seguridad
                            </span>
                        </div>
                        <div class="card__body">
                            <div class="password-form">
                                <!-- Contraseña actual -->
                                <div class="form-group">
                                    <label>Contraseña actual *</label>
                                    <div class="password-input-wrapper">
                                        <input 
                                            :type="showCurrentPassword ? 'text' : 'password'" 
                                            v-model="passwordForm.current_password" 
                                            class="form-input"
                                            :class="{ 'is-invalid': passwordErrors.current_password }"
                                            placeholder="Ingresa tu contraseña actual"
                                        />
                                        <button 
                                            type="button" 
                                            class="password-toggle"
                                            @click="togglePassword('current')"
                                            :title="showCurrentPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                        >
                                            <i :class="showCurrentPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"></i>
                                        </button>
                                    </div>
                                    <span v-if="passwordErrors.current_password" class="form-error">{{ passwordErrors.current_password }}</span>
                                </div>

                                <!-- Nueva contraseña -->
                                <div class="form-group">
                                    <label>Nueva contraseña *</label>
                                    <div class="password-input-wrapper">
                                        <input 
                                            :type="showNewPassword ? 'text' : 'password'" 
                                            v-model="passwordForm.password" 
                                            class="form-input"
                                            :class="{ 'is-invalid': passwordErrors.password }"
                                            placeholder="Nueva contraseña (mínimo 8 caracteres)"
                                            @focus="passwordFocused = true"
                                            @blur="passwordFocused = false"
                                        />
                                        <button 
                                            type="button" 
                                            class="password-toggle"
                                            @click="togglePassword('new')"
                                            :title="showNewPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                        >
                                            <i :class="showNewPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"></i>
                                        </button>
                                    </div>
                                    <span v-if="passwordErrors.password" class="form-error">{{ passwordErrors.password }}</span>
                                    
                                    <!-- Barra de fortaleza de contraseña -->
                                    <div v-if="passwordForm.password" class="password-strength">
                                        <div class="password-strength__bar">
                                            <div 
                                                class="password-strength__fill" 
                                                :style="{ width: passwordStrength.width, background: passwordStrength.color }"
                                            ></div>
                                        </div>
                                        <span class="password-strength__label" :style="{ color: passwordStrength.color }">
                                            {{ passwordStrength.label }}
                                        </span>
                                    </div>

                                    <!-- Requisitos de contraseña -->
                                    <div v-if="passwordForm.password" class="password-requirements">
                                        <div class="password-requirements__title">
                                            <i class="pi pi-info-circle"></i>
                                            Requisitos de la contraseña:
                                        </div>
                                        <ul class="password-requirements__list">
                                            <li 
                                                v-for="req in passwordRequirements" 
                                                :key="req.label"
                                                :class="{ 'requirement-met': req.met }"
                                            >
                                                <i :class="req.icon"></i>
                                                {{ req.label }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Confirmar nueva contraseña -->
                                <div class="form-group">
                                    <label>Confirmar nueva contraseña *</label>
                                    <div class="password-input-wrapper">
                                        <input 
                                            :type="showConfirmPassword ? 'text' : 'password'" 
                                            v-model="passwordForm.password_confirmation" 
                                            class="form-input"
                                            :class="{ 
                                                'is-invalid': passwordErrors.password_confirmation,
                                                'is-valid': passwordForm.password_confirmation && passwordForm.password === passwordForm.password_confirmation && passwordForm.password.length >= 8
                                            }"
                                            placeholder="Confirma tu nueva contraseña"
                                        />
                                        <button 
                                            type="button" 
                                            class="password-toggle"
                                            @click="togglePassword('confirm')"
                                            :title="showConfirmPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                        >
                                            <i :class="showConfirmPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"></i>
                                        </button>
                                    </div>
                                    <span v-if="passwordErrors.password_confirmation" class="form-error">{{ passwordErrors.password_confirmation }}</span>
                                    <span 
                                        v-else-if="passwordForm.password_confirmation && passwordForm.password === passwordForm.password_confirmation && passwordForm.password.length >= 8" 
                                        class="form-success"
                                    >
                                        <i class="pi pi-check-circle"></i> Las contraseñas coinciden
                                    </span>
                                    <span 
                                        v-else-if="passwordForm.password_confirmation && passwordForm.password !== passwordForm.password_confirmation" 
                                        class="form-error"
                                    >
                                        <i class="pi pi-times-circle"></i> Las contraseñas no coinciden
                                    </span>
                                </div>

                                <div class="password-actions">
                                    <button 
                                        class="btn btn--primary btn--full" 
                                        @click="cambiarPassword" 
                                        :disabled="isChangingPassword"
                                    >
                                        <i class="pi" :class="isChangingPassword ? 'pi-spin pi-spinner' : 'pi-save'"></i>
                                        {{ isChangingPassword ? 'Cambiando...' : 'Cambiar contraseña' }}
                                    </button>
                                </div>
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
   VARIABLES
   ========================================================================= */
.profile-edit {
  --brand: #C81E3A;
  --brand-dark: #A6152D;
  --brand-soft: #FBEAEC;
  --brand-gradient: linear-gradient(135deg, #C81E3A 0%, #E85A72 100%);
  --ink: #1A1A1A;
  --ink-soft: #4A4A4A;
  --muted: #888888;
  --muted-light: #B8B8B8;
  --line: #EAEAEA;
  --surface: #F8F8F8;
  --white: #FFFFFF;
  --success: #1fbf5c;
  --success-soft: #E8F5E9;
  --error: #E53E3E;
  --warning: #ED6C02;
  --warning-soft: #FFF3E0;
  --info: #0D6EFD;
  --info-soft: #E3F2FD;
  --shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  --shadow-hover: 0 4px 20px rgba(0, 0, 0, 0.06);
  --font-serif: 'Fraunces', Georgia, serif;
  --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;
  --radius-sm: 6px;
  --radius-md: 10px;
  --radius-lg: 14px;
  --radius-full: 999px;

  font-family: var(--font-sans);
  color: var(--ink);
  background: var(--white);
  -webkit-font-smoothing: antialiased;
  min-height: 100vh;
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.profile-edit * {
  box-sizing: border-box;
}

/* =========================================================================
   HEADER
   ========================================================================= */
.profile-edit__header {
  background: var(--white);
  border-radius: var(--radius-lg);
  padding: 2rem 2.5rem;
  display: flex;
  align-items: center;
  gap: 2rem;
  flex-wrap: wrap;
  position: relative;
  margin-bottom: 1.5rem;
  border: 1px solid var(--line);
  transition: all 0.3s ease;
}

.profile-edit__header:hover {
  box-shadow: var(--shadow-hover);
}

.profile-edit__header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: var(--brand-gradient);
  border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}

@media (max-width: 768px) {
  .profile-edit__header {
    flex-direction: column;
    text-align: center;
    padding: 1.5rem 1rem;
    gap: 1.5rem;
  }
}

/* =========================================================================
   AVATAR
   ========================================================================= */
.profile-edit__avatar {
  flex-shrink: 0;
}

.profile-edit__avatar-ring {
  padding: 3px;
  border-radius: 50%;
  background: var(--brand-gradient);
  box-shadow: 0 0 20px rgba(200, 30, 58, 0.12);
}

.profile-edit__avatar-image {
  position: relative;
  width: 110px;
  height: 110px;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid var(--white);
  background: var(--surface);
}

.profile-edit__avatar-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.profile-edit__avatar-badge {
  position: absolute;
  bottom: 4px;
  right: 4px;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1fbf5c, #34d399);
  color: var(--white);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.65rem;
  border: 2px solid var(--white);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

@media (max-width: 768px) {
  .profile-edit__avatar-image {
    width: 90px;
    height: 90px;
  }
  .profile-edit__avatar-badge {
    width: 22px;
    height: 22px;
    font-size: 0.55rem;
  }
}

/* =========================================================================
   INFO
   ========================================================================= */
.profile-edit__info {
  flex: 1;
  min-width: 200px;
}

.profile-edit__name {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  flex-wrap: wrap;
}

.profile-edit__name h1 {
  font-family: var(--font-serif);
  font-size: 1.8rem;
  font-weight: 600;
  margin: 0;
  background: var(--brand-gradient);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: -0.02em;
}

@media (max-width: 768px) {
  .profile-edit__name {
    justify-content: center;
  }
  .profile-edit__name h1 {
    font-size: 1.4rem;
  }
}

.badge {
  font-size: 0.7rem;
  font-weight: 600;
  padding: 0.2rem 0.9rem;
  border-radius: var(--radius-full);
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  white-space: nowrap;
  letter-spacing: 0.02em;
}

.badge.verified { background: var(--success-soft); color: var(--success); }
.badge.pending { background: var(--warning-soft); color: var(--warning); }
.badge.incomplete { background: var(--info-soft); color: var(--info); }
.badge.completed { background: #E8F5E9; color: #2E7D32; }
.badge.blocked { background: #FCE4EC; color: var(--error); }

.profile-edit__subtitle {
  font-size: 0.9rem;
  color: var(--ink-soft);
  margin: 0.3rem 0 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.profile-edit__subtitle i {
  color: var(--brand);
}

.profile-edit__meta {
  display: flex;
  gap: 1.5rem;
  font-size: 0.8rem;
  color: var(--muted);
  flex-wrap: wrap;
  align-items: center;
}

.profile-edit__meta span {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.profile-edit__meta i {
  font-size: 0.75rem;
}

.status-badge {
  font-size: 0.65rem;
  font-weight: 600;
  padding: 0.15rem 0.7rem;
  border-radius: var(--radius-full);
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.status-badge--verified { background: var(--success-soft); color: var(--success); }
.status-badge--pending { background: var(--warning-soft); color: var(--warning); }
.status-badge--incomplete { background: var(--info-soft); color: var(--info); }
.status-badge--completed { background: #E8F5E9; color: #2E7D32; }
.status-badge--blocked { background: #FCE4EC; color: var(--error); }

@media (max-width: 768px) {
  .profile-edit__meta {
    justify-content: center;
  }
}

/* =========================================================================
   ACTIONS
   ========================================================================= */
.profile-edit__actions {
  margin-left: auto;
}

@media (max-width: 768px) {
  .profile-edit__actions {
    margin-left: 0;
    width: 100%;
  }
}

.profile-edit__actions-group {
  display: flex;
  gap: 0.6rem;
}

@media (max-width: 768px) {
  .profile-edit__actions-group {
    justify-content: center;
    flex-wrap: wrap;
  }
}

/* =========================================================================
   BUTTONS
   ========================================================================= */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.6rem 1.6rem;
  border: none;
  border-radius: var(--radius-sm);
  font-weight: 600;
  font-size: 0.8rem;
  font-family: var(--font-sans);
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  text-decoration: none;
  white-space: nowrap;
  letter-spacing: 0.02em;
}

.btn--primary {
  background: var(--brand);
  color: var(--white);
  border: 1px solid var(--brand);
}

.btn--primary:hover:not(:disabled) {
  background: var(--brand-dark);
  border-color: var(--brand-dark);
  transform: translateY(-1px);
  box-shadow: 0 4px 20px rgba(200, 30, 58, 0.25);
}

.btn--secondary {
  background: var(--surface);
  color: var(--error);
  border: 1px solid var(--line);
}

.btn--secondary:hover:not(:disabled) {
  background: #FEE8EA;
  border-color: var(--error);
}

.btn--full {
  width: 100%;
  justify-content: center;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

/* =========================================================================
   GRID - 2 COLUMNAS
   ========================================================================= */
.profile-edit__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 1024px) {
  .profile-edit__grid {
    grid-template-columns: 1fr;
  }
}

.profile-edit__column {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* =========================================================================
   CARDS
   ========================================================================= */
.card {
  background: var(--white);
  border-radius: var(--radius-lg);
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid var(--line);
}

.card:hover {
  box-shadow: var(--shadow-hover);
}

.card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.9rem 1.5rem;
  border-bottom: 1px solid var(--line);
  background: var(--surface);
}

.card__header-left {
  display: flex;
  align-items: center;
  gap: 0.7rem;
}

.card__header-icon {
  color: var(--brand);
  font-size: 1rem;
}

.card__header h3 {
  font-size: 0.85rem;
  font-weight: 600;
  margin: 0;
  color: var(--ink);
  letter-spacing: 0.02em;
}

.card__body {
  padding: 1.5rem;
}

.card__badge {
  font-size: 0.6rem;
  font-weight: 600;
  color: var(--brand);
  background: var(--brand-soft);
  padding: 0.15rem 0.7rem;
  border-radius: var(--radius-full);
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  letter-spacing: 0.03em;
}

.card__badge--password {
  color: var(--warning);
  background: var(--warning-soft);
}

.card--form {
  border-left: 3px solid var(--brand);
}

.card--password {
  border-left: 3px solid var(--warning);
}

/* =========================================================================
   FORM
   ========================================================================= */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.2rem;
}

@media (max-width: 600px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.form-group label {
  font-size: 0.65rem;
  font-weight: 600;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.form-value {
  font-size: 0.9rem;
  color: var(--ink);
  font-weight: 500;
  padding: 0.3rem 0;
  word-break: break-word;
}

.form-input {
  width: 100%;
  border: 1px solid var(--line);
  border-radius: var(--radius-sm);
  padding: 0.5rem 0.8rem;
  font-size: 0.85rem;
  font-family: var(--font-sans);
  transition: all 0.3s ease;
  background: var(--white);
  color: var(--ink);
}

.form-input:focus {
  border-color: var(--brand);
  box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.06);
  outline: none;
}

.form-input.is-invalid {
  border-color: var(--error);
}

.form-input.is-invalid:focus {
  box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.08);
}

.form-input.is-valid {
  border-color: var(--success);
}

.form-input.is-valid:focus {
  box-shadow: 0 0 0 3px rgba(31, 191, 92, 0.08);
}

.form-error {
  font-size: 0.7rem;
  color: var(--error);
  font-weight: 500;
  margin-top: 2px;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.form-success {
  font-size: 0.7rem;
  color: var(--success);
  font-weight: 500;
  margin-top: 2px;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.form-help {
  font-size: 0.65rem;
  color: var(--muted-light);
  margin-top: 2px;
}

.edad-tag {
  display: inline-block;
  font-size: 0.6rem;
  font-weight: 600;
  background: var(--brand-soft);
  color: var(--brand);
  padding: 0.1rem 0.5rem;
  border-radius: var(--radius-full);
  margin-left: 0.5rem;
}

/* =========================================================================
   PASSWORD
   ========================================================================= */
.password-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.password-input-wrapper {
  position: relative;
  width: 100%;
}

.password-input-wrapper .form-input {
  padding-right: 2.5rem;
}

.password-toggle {
  position: absolute;
  right: 0.5rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--muted-light);
  cursor: pointer;
  padding: 0.3rem;
  border-radius: var(--radius-sm);
  transition: all 0.3s ease;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.password-toggle:hover {
  color: var(--brand);
  background: var(--brand-soft);
}

.password-actions {
  margin-top: 0.5rem;
}

/* =========================================================================
   PASSWORD STRENGTH
   ========================================================================= */
.password-strength {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  margin-top: 0.3rem;
}

.password-strength__bar {
  flex: 1;
  height: 4px;
  background: var(--line);
  border-radius: 2px;
  overflow: hidden;
}

.password-strength__fill {
  height: 100%;
  border-radius: 2px;
  transition: width 0.3s ease;
}

.password-strength__label {
  font-size: 0.65rem;
  font-weight: 600;
  white-space: nowrap;
}

/* =========================================================================
   PASSWORD REQUIREMENTS
   ========================================================================= */
.password-requirements {
  margin-top: 0.3rem;
  padding: 0.6rem 0.8rem;
  background: var(--surface);
  border-radius: var(--radius-sm);
  border: 1px solid var(--line);
}

.password-requirements__title {
  font-size: 0.65rem;
  font-weight: 600;
  color: var(--muted);
  margin: 0 0 0.2rem 0;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.password-requirements__list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.1rem 0.8rem;
}

@media (max-width: 600px) {
  .password-requirements__list {
    grid-template-columns: 1fr;
  }
}

.password-requirements__list li {
  font-size: 0.6rem;
  color: var(--muted);
  display: flex;
  align-items: center;
  gap: 0.3rem;
  transition: all 0.3s ease;
  padding: 0.1rem 0;
}

.password-requirements__list li i {
  font-size: 0.5rem;
  color: var(--muted-light);
  transition: all 0.3s ease;
}

.password-requirements__list li.requirement-met {
  color: var(--success);
}

.password-requirements__list li.requirement-met i {
  color: var(--success);
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
  .profile-edit__grid {
    grid-template-columns: 1fr;
  }
  .profile-edit__column {
    gap: 1.2rem;
  }
}

@media (max-width: 768px) {
  .profile-edit {
    padding: 1rem;
  }
  .card__body {
    padding: 1rem;
  }
  .card__header {
    padding: 0.7rem 1rem;
  }
  .profile-edit__header {
    padding: 1rem;
  }
}

@media (max-width: 480px) {
  .profile-edit {
    padding: 0.5rem;
  }
  .card__body {
    padding: 0.8rem;
  }
  .profile-edit__meta {
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
  }
  .profile-edit__name h1 {
    font-size: 1.2rem;
  }
  .form-grid {
    grid-template-columns: 1fr;
  }
  .password-requirements__list {
    grid-template-columns: 1fr;
  }
}

/* =========================================================================
   ANIMACIONES
   ========================================================================= */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.card {
  animation: fadeIn 0.4s ease forwards;
}

.card:nth-child(2) {
  animation-delay: 0.05s;
}

/* =========================================================================
   SCROLLBAR
   ========================================================================= */
.profile-edit::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.profile-edit::-webkit-scrollbar-track {
  background: var(--surface);
  border-radius: 3px;
}

.profile-edit::-webkit-scrollbar-thumb {
  background: var(--muted-light);
  border-radius: 3px;
}

.profile-edit::-webkit-scrollbar-thumb:hover {
  background: var(--muted);
}

/* =========================================================================
   ICONOS
   ========================================================================= */
.pi-spin {
  animation: pi-spin 1s linear infinite;
}

@keyframes pi-spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* =========================================================================
   INPUT DATE
   ========================================================================= */
input[type="date"] {
  appearance: none;
  -webkit-appearance: none;
  min-height: 38px;
}

input[type="date"]::-webkit-calendar-picker-indicator {
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

input[type="date"]::-webkit-calendar-picker-indicator:hover {
  background: var(--brand-soft);
}

input[type="date"]::-webkit-datetime-edit {
  padding: 0.2rem 0;
}

input[type="date"]:disabled::-webkit-calendar-picker-indicator {
  opacity: 0.5;
  cursor: not-allowed;
}

input[type="date"]::-moz-calendar-picker-indicator {
  cursor: pointer;
}

input[type="date"]:disabled::-moz-calendar-picker-indicator {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>