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
 * Formulario
 * --------------------------------------------------------------- */
const form = reactive({
    nombre: props.user.nombre || '',
    apodo: props.user.apodo || '',
    email: props.user.email || '',
    telefono: props.user.telefono || '',
    ciudad: props.user.ciudad || '',
    fecha_nacimiento: props.user.fecha_nacimiento || '',
});

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
const isEditing = ref(false);
const fieldErrors = ref({});

/* ---------------------------------------------------------------
 * Computed - Información del usuario
 * --------------------------------------------------------------- */
const avatarUrl = computed(() => 
    props.user.foto_principal || props.user.avatar || '/images/shared/avatar-default.jpg'
);

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
    if (!props.user.created_at) return 'Fecha no disponible';
    try {
        return new Date(props.user.created_at).toLocaleDateString('es-ES', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });
    } catch {
        return 'Fecha no disponible';
    }
});

/* ---------------------------------------------------------------
 * Computed - Estados y roles
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
    
    let key = props.user.estado || 'pendiente';
    if (props.user.estado === 'verificado') key = 'verificado';
    else if (props.user.estado === 'incompleto' && form.nombre && form.apodo && form.email) key = 'pendiente';
    else if (props.user.estado === 'completo') key = 'completo';
    
    return estados[key] || estados['pendiente'];
});

const rolActual = computed(() => {
    const roles = {
        'admin': { label: 'Administrador', icon: 'pi pi-shield', color: '#7B1FA2' },
        'creador': { label: 'Creador de contenido', icon: 'pi pi-star', color: '#283593' },
        'usuario': { label: 'Usuario', icon: 'pi pi-user', color: '#4B4744' },
        'invitado': { label: 'Invitado', icon: 'pi pi-user', color: '#8A8481' },
    };
    return roles[props.user.rol] || roles['usuario'];
});

const estaVerificado = computed(() => props.user.estado === 'verificado');

/* ---------------------------------------------------------------
 * Computed - Consejos para el perfil
 * --------------------------------------------------------------- */
const consejos = computed(() => ({
    nombre: form.nombre && form.nombre.length >= 2,
    apodo: form.apodo && form.apodo.length >= 3,
    email: form.email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email),
    foto: props.user.foto_principal && props.user.foto_principal.length > 0,
    ciudad: form.ciudad && form.ciudad.length > 0,
    edad: form.fecha_nacimiento && edad.value !== null && edad.value >= 18,
}));

const consejosTotales = computed(() => Object.keys(consejos.value).length);
const consejosCompletados = computed(() => {
    return Object.values(consejos.value).filter(Boolean).length;
});

const progresoConsejos = computed(() => {
    if (consejosTotales.value === 0) return 0;
    return Math.round((consejosCompletados.value / consejosTotales.value) * 100);
});

/* ---------------------------------------------------------------
 * Validaciones
 * --------------------------------------------------------------- */
function validateForm() {
    const errors = {};
    let isValid = true;

    if (!form.nombre || form.nombre.trim().length < 2) {
        errors.nombre = 'El nombre debe tener al menos 2 caracteres.';
        isValid = false;
    } else if (form.nombre.length > 100) {
        errors.nombre = 'El nombre no puede tener más de 100 caracteres.';
        isValid = false;
    }

    if (!form.apodo || form.apodo.trim().length < 3) {
        errors.apodo = 'El apodo debe tener al menos 3 caracteres.';
        isValid = false;
    } else if (form.apodo.length > 20) {
        errors.apodo = 'El apodo no puede tener más de 20 caracteres.';
        isValid = false;
    } else if (!/^[a-zA-Z0-9_]+$/.test(form.apodo)) {
        errors.apodo = 'El apodo solo puede contener letras, números y guión bajo.';
        isValid = false;
    }

    if (!form.email) {
        errors.email = 'El email es obligatorio.';
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errors.email = 'Ingresa un email válido.';
        isValid = false;
    }

    if (form.telefono && !/^[0-9+\-\s()]{6,15}$/.test(form.telefono)) {
        errors.telefono = 'Ingresa un número de teléfono válido.';
        isValid = false;
    }

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
        }
    }

    fieldErrors.value = errors;
    return isValid;
}

/* ---------------------------------------------------------------
 * Acciones
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
        onSuccess: () => {
            isSaving.value = false;
            isEditing.value = false;
            fieldErrors.value = {};

            Object.assign(props.user, {
                nombre: form.nombre,
                apodo: form.apodo,
                email: form.email,
                telefono: form.telefono,
                ciudad: form.ciudad,
                fecha_nacimiento: form.fecha_nacimiento,
            });

            if (window.showToast) {
                window.showToast({
                    type: 'success',
                    title: 'Datos actualizados',
                    message: 'Tu información personal se ha guardado correctamente.',
                    duration: 3000,
                });
            }
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

function cancelarEdicion() {
    isEditing.value = false;
    fieldErrors.value = {};
    Object.assign(form, {
        nombre: props.user.nombre || '',
        apodo: props.user.apodo || '',
        email: props.user.email || '',
        telefono: props.user.telefono || '',
        ciudad: props.user.ciudad || '',
        fecha_nacimiento: props.user.fecha_nacimiento || '',
    });
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
</script>

<template>
    <AppLayout activeNav="configuracion">
        <Head title="Editar datos de usuario" />

        <div class="profile-edit">
            <!-- ============================================================ -->
            <!-- HEADER -->
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
                                    <label>Nombre completo</label>
                                    <span v-if="!isEditing" class="form-value">{{ form.nombre || 'No especificado' }}</span>
                                    <input 
                                        v-else 
                                        v-model="form.nombre" 
                                        class="form-input"
                                        :class="{ 'is-invalid': fieldErrors.nombre }"
                                        placeholder="Tu nombre"
                                    />
                                    <span v-if="fieldErrors.nombre" class="form-error">{{ fieldErrors.nombre }}</span>
                                </div>

                                <div class="form-group">
                                    <label>Apodo</label>
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
                                    <label>Correo electrónico</label>
                                    <span v-if="!isEditing" class="form-value">{{ form.email || 'No especificado' }}</span>
                                    <input 
                                        v-else 
                                        v-model="form.email" 
                                        class="form-input"
                                        :class="{ 'is-invalid': fieldErrors.email }"
                                        placeholder="tu@email.com"
                                    />
                                    <span v-if="fieldErrors.email" class="form-error">{{ fieldErrors.email }}</span>
                                </div>

                                <div class="form-group">
                                    <label>Teléfono</label>
                                    <span v-if="!isEditing" class="form-value">{{ form.telefono || 'No especificado' }}</span>
                                    <input 
                                        v-else 
                                        v-model="form.telefono" 
                                        class="form-input"
                                        :class="{ 'is-invalid': fieldErrors.telefono }"
                                        placeholder="+34 600 000 000"
                                    />
                                    <span v-if="fieldErrors.telefono" class="form-error">{{ fieldErrors.telefono }}</span>
                                    <small class="form-help">Opcional. Solo números, +, -, espacios y paréntesis</small>
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
                                    <label>Fecha de nacimiento</label>
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
                <!-- COLUMNA DERECHA - Estado y Consejos -->
                <!-- ========================================================== -->
                <div class="profile-edit__column">
                    <!-- Estado de la cuenta -->
                    <div class="card card--status">
                        <div class="card__header">
                            <div class="card__header-left">
                                <i class="pi pi-shield card__header-icon"></i>
                                <h3>Estado de la cuenta</h3>
                            </div>
                        </div>
                        <div class="card__body">
                            <div class="status-grid-3">
                                <div class="status-item status-item--highlight" :class="estadoActual.class">
                                    <span class="status-item__label">Estado</span>
                                    <span class="status-item__value">
                                        <i :class="estadoActual.icon"></i>
                                        {{ estadoActual.label }}
                                    </span>
                                </div>
                                <div class="status-item">
                                    <span class="status-item__label">Rol</span>
                                    <span class="status-item__value" :style="{ color: rolActual.color }">
                                        <i :class="rolActual.icon"></i>
                                        {{ rolActual.label }}
                                    </span>
                                </div>
                                <div class="status-item">
                                    <span class="status-item__label">Verificación</span>
                                    <span class="status-item__value">
                                        <i :class="estaVerificado ? 'pi pi-check-circle' : 'pi pi-clock'"></i>
                                        {{ estaVerificado ? 'Verificado' : 'Pendiente' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Consejos para el perfil - 2 líneas -->
                    <div class="card card--tips">
                        <div class="card__header">
                            <div class="card__header-left">
                                <i class="pi pi-lightbulb card__header-icon"></i>
                                <h3>Mejora tu perfil</h3>
                            </div>
                            <span class="tips-counter">
                                <i class="pi pi-check-circle"></i> {{ consejosCompletados }}/{{ consejosTotales }}
                            </span>
                        </div>
                        <div class="card__body">
                            <!-- Grid de 3 columnas - 2 líneas -->
                            <div class="tips-grid-3">
                                <div class="tip-item" :class="{ 'tip-item--completed': consejos.nombre }">
                                    <div class="tip-item__icon">
                                        <i :class="consejos.nombre ? 'pi pi-check-circle' : 'pi pi-user'"></i>
                                    </div>
                                    <div class="tip-item__content">
                                        <span class="tip-item__title">Nombre real</span>
                                        <span class="tip-item__description">{{ consejos.nombre ? 'Completado' : 'Agrega tu nombre' }}</span>
                                    </div>
                                </div>

                                <div class="tip-item" :class="{ 'tip-item--completed': consejos.apodo }">
                                    <div class="tip-item__icon">
                                        <i :class="consejos.apodo ? 'pi pi-check-circle' : 'pi pi-hashtag'"></i>
                                    </div>
                                    <div class="tip-item__content">
                                        <span class="tip-item__title">Apodo único</span>
                                        <span class="tip-item__description">{{ consejos.apodo ? 'Completado' : 'Elige un apodo' }}</span>
                                    </div>
                                </div>

                                <div class="tip-item" :class="{ 'tip-item--completed': consejos.email }">
                                    <div class="tip-item__icon">
                                        <i :class="consejos.email ? 'pi pi-check-circle' : 'pi pi-envelope'"></i>
                                    </div>
                                    <div class="tip-item__content">
                                        <span class="tip-item__title">Email verificado</span>
                                        <span class="tip-item__description">{{ consejos.email ? 'Completado' : 'Verifica tu email' }}</span>
                                    </div>
                                </div>

                                <div class="tip-item" :class="{ 'tip-item--completed': consejos.foto }">
                                    <div class="tip-item__icon">
                                        <i :class="consejos.foto ? 'pi pi-check-circle' : 'pi pi-image'"></i>
                                    </div>
                                    <div class="tip-item__content">
                                        <span class="tip-item__title">Foto de perfil</span>
                                        <span class="tip-item__description">{{ consejos.foto ? 'Completado' : 'Sube una foto' }}</span>
                                    </div>
                                </div>

                                <div class="tip-item" :class="{ 'tip-item--completed': consejos.ciudad }">
                                    <div class="tip-item__icon">
                                        <i :class="consejos.ciudad ? 'pi pi-check-circle' : 'pi pi-map-marker'"></i>
                                    </div>
                                    <div class="tip-item__content">
                                        <span class="tip-item__title">Ubicación</span>
                                        <span class="tip-item__description">{{ consejos.ciudad ? 'Completado' : 'Indica tu ciudad' }}</span>
                                    </div>
                                </div>

                                <div class="tip-item" :class="{ 'tip-item--completed': consejos.edad }">
                                    <div class="tip-item__icon">
                                        <i :class="consejos.edad ? 'pi pi-check-circle' : 'pi pi-calendar'"></i>
                                    </div>
                                    <div class="tip-item__content">
                                        <span class="tip-item__title">Edad verificada</span>
                                        <span class="tip-item__description">{{ consejos.edad ? 'Completado' : 'Confirma tu edad' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="tips-progress">
                                <div class="tips-progress__bar">
                                    <div class="tips-progress__fill" :style="{ width: progresoConsejos + '%' }"></div>
                                </div>
                                <span class="tips-progress__label">{{ progresoConsejos }}% completado</span>
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
}

.profile-edit__meta span {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.profile-edit__meta i {
  font-size: 0.75rem;
}

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

.card--status {
  border-left: 3px solid var(--brand);
}

.card--form {
  border-left: 3px solid var(--brand);
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

.form-error {
  font-size: 0.7rem;
  color: var(--error);
  font-weight: 500;
  margin-top: 2px;
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
   STATUS - 3 items en una línea
   ========================================================================= */
.status-grid-3 {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 0.6rem;
}

@media (max-width: 600px) {
  .status-grid-3 {
    grid-template-columns: 1fr;
  }
}

.status-item {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  padding: 0.6rem 0.8rem;
  background: var(--surface);
  border-radius: var(--radius-sm);
  border: 1px solid var(--line);
  transition: all 0.3s ease;
}

.status-item:hover {
  background: var(--white);
  box-shadow: var(--shadow);
}

.status-item--highlight {
  border-left: 3px solid var(--brand);
  background: var(--brand-soft);
}

.status-item--highlight.verified { border-color: var(--success); background: var(--success-soft); }
.status-item--highlight.pending { border-color: var(--warning); background: var(--warning-soft); }
.status-item--highlight.incomplete { border-color: var(--info); background: var(--info-soft); }
.status-item--highlight.completed { border-color: #2E7D32; background: #E8F5E9; }
.status-item--highlight.blocked { border-color: var(--error); background: #FCE4EC; }

.status-item__label {
  font-size: 0.6rem;
  font-weight: 600;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.status-item__value {
  font-size: 0.8rem;
  font-weight: 500;
  color: var(--ink);
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.status-item__value i {
  font-size: 0.7rem;
}

/* =========================================================================
   TIPS - 2 líneas (3 columnas)
   ========================================================================= */
.card--tips .card__header {
  background: linear-gradient(135deg, #FFF8F6 0%, var(--brand-soft) 100%);
  border-bottom: 2px solid var(--brand);
}

.tips-counter {
  font-size: 0.7rem;
  font-weight: 600;
  color: var(--brand);
  background: var(--white);
  padding: 0.15rem 0.8rem;
  border-radius: var(--radius-full);
  border: 1px solid var(--brand-soft);
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

/* Grid de 3 columnas para los consejos - 2 líneas */
.tips-grid-3 {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 0.6rem;
}

@media (max-width: 1024px) {
  .tips-grid-3 {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 600px) {
  .tips-grid-3 {
    grid-template-columns: 1fr;
  }
}

.tip-item {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.6rem 0.8rem;
  background: var(--surface);
  border-radius: var(--radius-sm);
  border-left: 3px solid var(--line);
  transition: all 0.3s ease;
}

.tip-item:hover {
  background: var(--white);
  box-shadow: var(--shadow);
  transform: translateX(3px);
}

.tip-item--completed {
  border-left-color: var(--success);
  background: var(--success-soft);
}

.tip-item--completed:hover {
  background: #E8F5E9;
}

.tip-item__icon {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  background: var(--white);
  color: var(--muted-light);
  flex-shrink: 0;
  transition: all 0.3s ease;
  border: 1px solid var(--line);
}

.tip-item--completed .tip-item__icon {
  color: var(--success);
  border-color: var(--success);
}

.tip-item:not(.tip-item--completed) .tip-item__icon {
  color: var(--brand);
  border-color: var(--brand-soft);
}

.tip-item__content {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-width: 0;
}

.tip-item__title {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--ink);
}

.tip-item__description {
  font-size: 0.65rem;
  color: var(--muted);
  line-height: 1.3;
}

.tip-item--completed .tip-item__description {
  color: var(--success);
}

/* =========================================================================
   PROGRESS BAR
   ========================================================================= */
.tips-progress {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--line);
  display: flex;
  align-items: center;
  gap: 1rem;
}

.tips-progress__bar {
  flex: 1;
  height: 4px;
  background: var(--line);
  border-radius: 2px;
  overflow: hidden;
}

.tips-progress__fill {
  height: 100%;
  background: var(--brand-gradient);
  border-radius: 2px;
  transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.tips-progress__label {
  font-size: 0.7rem;
  font-weight: 600;
  color: var(--brand);
  white-space: nowrap;
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
  .tips-grid-3 {
    grid-template-columns: 1fr 1fr;
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
  .status-grid-3 {
    grid-template-columns: 1fr;
  }
  .tips-grid-3 {
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