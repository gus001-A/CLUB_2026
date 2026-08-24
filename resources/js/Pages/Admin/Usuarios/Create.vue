<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();
const mostrarPassword = ref(false);
const confirmarPassword = ref('');

const props = defineProps({
    origen: String,
});

const form = useForm({
    nombre: '',
    apodo: '',
    email: '',
    password: '',
    telefono: '',
    fecha_nacimiento: '',
    rol: '',
    estado: 'verificado',
});

// --- Edad ---
// OJO: new Date('2003-03-11') lo interpreta como medianoche UTC. Al usar
// getMonth()/getDate() (que son en hora LOCAL) sobre esa fecha, en zonas
// con offset negativo (como Ciudad de México) el día se corre uno hacia
// atrás. Por eso aquí parseamos el string directo, sin pasar por Date.
function calcularEdad(fechaISO) {
    if (!fechaISO) return null;
    const [anio, mes, dia] = fechaISO.split('-').map(Number);
    if (!anio || !mes || !dia) return null;

    const hoy = new Date();
    let edad = hoy.getFullYear() - anio;
    const mesActual = hoy.getMonth() + 1; // getMonth() es 0-indexado
    if (mesActual < mes || (mesActual === mes && hoy.getDate() < dia)) edad--;
    return edad >= 0 && edad < 130 ? edad : null;
}
const edadPreview = computed(() => calcularEdad(form.fecha_nacimiento));
const esMayorEdad = computed(() => edadPreview.value !== null && edadPreview.value >= 18);

// --- Validaciones en tiempo real ---
const telefonoError = computed(() => {
    if (!form.telefono) return null;
    const soloNumeros = /^\d+$/.test(form.telefono);
    if (!soloNumeros) return 'Solo se permiten números';
    if (form.telefono.length > 10) return 'Máximo 10 dígitos';
    if (form.telefono.length < 10) return 'Mínimo 10 dígitos';
    return null;
});

// OJO: toISOString() convierte a UTC — si es de noche en tu zona horaria
// (offset negativo), la fecha resultante ya cae en el día siguiente. Por
// eso armamos el string directo con los getters LOCALES en vez de pasar
// por toISOString().
const fechaMaxima = computed(() => {
    const fecha = new Date();
    fecha.setFullYear(fecha.getFullYear() - 18);
    const anio = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    const dia = String(fecha.getDate()).padStart(2, '0');
    return `${anio}-${mes}-${dia}`;
});

const fechaError = computed(() => {
    if (!form.fecha_nacimiento) return null;
    if (edadPreview.value !== null && edadPreview.value < 18) return 'Debes ser mayor de 18 años';
    return null;
});

// --- Fortaleza de la contraseña ---
const fuerzaPassword = computed(() => {
    const pwd = form.password;
    if (!pwd) return 0;
    let fuerza = 0;
    if (pwd.length >= 8) fuerza++;
    if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) fuerza++;
    if (/\d/.test(pwd)) fuerza++;
    if (/[^a-zA-Z0-9]/.test(pwd)) fuerza++;
    return fuerza;
});

const nivelFuerza = computed(() => {
    const f = fuerzaPassword.value;
    if (f === 0) return { label: 'Sin contraseña', color: '#D1D5DB', text: '#9CA3AF' };
    if (f <= 2) return { label: 'Débil', color: '#EF4444', text: '#EF4444' };
    if (f === 3) return { label: 'Media', color: '#F59E0B', text: '#D97706' };
    return { label: 'Fuerte', color: '#10B981', text: '#059669' };
});

const passwordsCoinciden = computed(() => {
    if (!confirmarPassword.value) return true;
    return form.password === confirmarPassword.value;
});

// --- Funciones ---
function generarPassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%&*';
    let clave = '';
    for (let i = 0; i < 12; i++) {
        clave += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    form.password = clave;
    confirmarPassword.value = clave;
    mostrarPassword.value = true;
    toast.success('Contraseña generada. No olvides copiarla antes de guardar.');
}

async function copiarPassword() {
    if (!form.password) return;
    try {
        await navigator.clipboard.writeText(form.password);
        toast.success('Contraseña copiada al portapapeles.');
    } catch (e) {
        toast.error('No se pudo copiar. Cópiala manualmente.');
    }
}

// --- Selectores ---
const roles = [
    { value: 'usuario', label: 'Usuario', icon: 'pi-user' },
    { value: 'admin', label: 'Administrador', icon: 'pi-shield' },
];
const estados = [
    { value: 'verificado', label: 'Verificado', dot: '#059669' },
    { value: 'pendiente', label: 'Pendiente', dot: '#D97706' },
    { value: 'incompleto', label: 'Incompleto', dot: '#6B7280' },
];

// --- Vista previa ---
const inicialesPreview = computed(() => {
    const nombre = form.nombre.trim();
    if (!nombre) return '?';
    const partes = nombre.split(/\s+/);
    return partes.length > 1
        ? (partes[0][0] + partes[1][0]).toUpperCase()
        : partes[0].slice(0, 2).toUpperCase();
});
const rolSeleccionado = computed(() => roles.find((r) => r.value === form.rol) || null);
const estadoSeleccionado = computed(() => estados.find((e) => e.value === form.estado) || estados[0]);
const badgeRolClases = { usuario: 'admin-dash-badge--rol-usuario', creador: 'admin-dash-badge--rol-creador', admin: 'admin-dash-badge--rol-admin' };
const badgeRolPreview = computed(() => badgeRolClases[form.rol] || 'admin-dash-badge--rol-usuario');

// --- Estado del formulario ---
const isFormValid = computed(() => {
    const camposObligatorios = ['nombre', 'apodo', 'email', 'password', 'fecha_nacimiento', 'rol'];
    const todosLlenos = camposObligatorios.every(campo => form[campo] && form[campo].trim() !== '');
    const passwordValida = form.password.length >= 8;
    const telefonoValido = !form.telefono || (telefonoError.value === null);
    const fechaValida = esMayorEdad.value === true;
    const coinciden = passwordsCoinciden.value;
    return todosLlenos && passwordValida && telefonoValido && fechaValida && coinciden;
});

const porcentajeCompletado = computed(() => {
    const campos = ['nombre', 'apodo', 'email', 'password', 'fecha_nacimiento', 'rol', 'telefono'];
    const llenos = campos.filter(c => form[c] && form[c].trim() !== '').length;
    return Math.round((llenos / campos.length) * 100);
});

function submit() {
    const obligatorios = ['nombre', 'apodo', 'email', 'password', 'fecha_nacimiento', 'rol'];
    const faltantes = obligatorios.filter((campo) => !form[campo]);

    if (faltantes.length) {
        toast.error('Debes llenar todos los campos obligatorios.');
        return;
    }
    if (telefonoError.value) {
        toast.error(telefonoError.value);
        return;
    }
    if (fechaError.value) {
        toast.error(fechaError.value);
        return;
    }
    if (!passwordsCoinciden.value) {
        toast.error('Las contraseñas no coinciden.');
        return;
    }

    form.post(route('admin.usuarios.store'), {
        onSuccess: () => toast.success('Usuario creado con éxito.'),
        onError: (errors) => {
            const primerError = Object.values(errors)[0];
            toast.error(primerError || 'Ocurrió un error al crear el usuario.');
        }
    });
}

watch(() => form.telefono, (newVal) => {
    if (newVal) {
        form.telefono = newVal.replace(/\D/g, '').slice(0, 10);
    }
});
</script>

<template>

    <Head title="Agregar Usuario" />

    <AdminLayout>
        <template #title>Agregar Usuario</template>
        <template #breadcrumb>
            <span v-if="origen === 'dashboard'">Dashboard / Usuarios / Agregar Usuario</span>
            <span v-else>Usuarios / Agregar Usuario</span>
        </template>

        <div class="admin-user-form-page">
            <Link :href="route('admin.usuarios.index')" class="admin-user-back-link">
                <i class="pi pi-arrow-left"></i>
                Volver a Usuarios
            </Link>

            <form @submit.prevent="submit" class="admin-user-form-grid">
                <!-- COLUMNA IZQUIERDA: FORMULARIO -->
                <div class="admin-user-form">
                    <div class="admin-user-form-header">
                        <div class="admin-user-form-header__icon"><i class="pi pi-user-plus"></i></div>
                        <div>
                            <h1>Registrar nuevo usuario</h1>
                            <p>Completa el formulario para darlo de alta en la plataforma</p>
                        </div>
                    </div>

                    <div class="admin-user-form-body">
                        <!-- Información personal -->
                        <div>
                            <div class="admin-user-form-section-title"><i class="pi pi-user"></i> Información personal</div>

                            <div class="admin-user-field" style="margin-bottom:0.9rem">
                                <label>Nombre completo <span class="admin-user-required">*</span></label>
                                <input v-model="form.nombre" type="text" placeholder="Ingresa el nombre completo del usuario"
                                    :class="{ 'admin-user-input-error': form.errors.nombre }" />
                                <p v-if="form.errors.nombre" class="admin-user-error-text">{{ form.errors.nombre }}</p>
                            </div>

                            <div class="admin-user-field-row" style="margin-bottom:0.9rem">
                                <div class="admin-user-field">
                                    <label><i class="pi pi-at"></i> Nombre de usuario <span class="admin-user-required">*</span></label>
                                    <input v-model="form.apodo" type="text" placeholder="Ej. anamaria"
                                        :class="{ 'admin-user-input-error': form.errors.apodo }" />
                                    <p v-if="form.errors.apodo" class="admin-user-error-text">{{ form.errors.apodo }}</p>
                                </div>
                                <div class="admin-user-field">
                                    <label><i class="pi pi-envelope"></i> Correo electrónico <span class="admin-user-required">*</span></label>
                                    <input v-model="form.email" type="email" placeholder="usuario@correo.com"
                                        :class="{ 'admin-user-input-error': form.errors.email }" />
                                    <p v-if="form.errors.email" class="admin-user-error-text">{{ form.errors.email }}</p>
                                </div>
                            </div>

                            <div class="admin-user-field-row">
                                <div class="admin-user-field">
                                    <label><i class="pi pi-phone"></i> Teléfono <span class="admin-user-optional">(opcional)</span></label>
                                    <input v-model="form.telefono" type="text" inputmode="numeric" placeholder="55 1234 5678"
                                        :class="{ 'admin-user-input-error': telefonoError }" />
                                    <p v-if="telefonoError" class="admin-user-error-text">{{ telefonoError }}</p>
                                </div>
                                <div class="admin-user-field">
                                    <label><i class="pi pi-calendar"></i> Fecha de nacimiento <span class="admin-user-required">*</span></label>
                                    <input v-model="form.fecha_nacimiento" type="date" :max="fechaMaxima"
                                        :class="{ 'admin-user-input-error': fechaError }" />
                                    <p v-if="fechaError" class="admin-user-error-text">{{ fechaError }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración de cuenta -->
                        <div>
                            <div class="admin-user-form-section-title"><i class="pi pi-cog"></i> Configuración de cuenta</div>

                            <div class="admin-user-field" style="margin-bottom:0.9rem">
                                <label>Tipo de usuario <span class="admin-user-required">*</span></label>
                                <div class="admin-user-toggle-group">
                                    <button v-for="r in roles" :key="r.value" type="button" @click="form.rol = r.value"
                                        class="admin-user-toggle-pill" :class="{ 'admin-user-toggle-pill--active': form.rol === r.value }">
                                        <i class="pi" :class="r.icon"></i> {{ r.label }}
                                    </button>
                                </div>
                                <p v-if="form.errors.rol" class="admin-user-error-text">{{ form.errors.rol }}</p>
                            </div>

                            <div class="admin-user-field">
                                <label>Estado de la cuenta <span class="admin-user-required">*</span></label>
                                <div class="admin-user-toggle-group">
                                    <button v-for="e in estados" :key="e.value" type="button" @click="form.estado = e.value"
                                        class="admin-user-toggle-pill" :class="{ 'admin-user-toggle-pill--active': form.estado === e.value }">
                                        <span class="admin-user-toggle-pill-dot" :style="{ background: e.dot }"></span> {{ e.label }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Credenciales -->
                        <div>
                            <div class="admin-user-form-section-title"><i class="pi pi-lock"></i> Credenciales de acceso</div>

                            <div class="admin-user-field" style="margin-bottom:0.9rem">
                                <label><i class="pi pi-key"></i> Contraseña <span class="admin-user-required">*</span> <span class="admin-user-optional">(mínimo 8 caracteres)</span></label>
                                <div class="admin-user-password-wrap">
                                    <input v-model="form.password" :type="mostrarPassword ? 'text' : 'password'" placeholder="Ingresa una contraseña segura"
                                        :class="{ 'admin-user-input-error': form.errors.password }" />
                                    <button type="button" class="admin-user-password-toggle" @click="mostrarPassword = !mostrarPassword">
                                        <i class="pi" :class="mostrarPassword ? 'pi-eye-slash' : 'pi-eye'"></i>
                                    </button>
                                </div>
                                <p v-if="form.errors.password" class="admin-user-error-text">{{ form.errors.password }}</p>

                                <div v-if="form.password" class="admin-user-password-meter">
                                    <div class="flex items-center gap-3">
                                        <div class="admin-user-password-meter-bar">
                                            <div class="admin-user-password-meter-fill" :style="{ width: `${(fuerzaPassword / 4) * 100}%`, background: nivelFuerza.color }"></div>
                                        </div>
                                        <span class="text-xs font-medium" :style="{ color: nivelFuerza.text }">{{ nivelFuerza.label }}</span>
                                    </div>
                                    <div class="admin-user-password-checklist">
                                        <span :class="{ 'admin-user-check-ok': form.password.length >= 8 }">
                                            <i class="pi" :class="form.password.length >= 8 ? 'pi-check-circle' : 'pi-circle'"></i> 8+ caracteres
                                        </span>
                                        <span :class="{ 'admin-user-check-ok': /[a-z]/.test(form.password) && /[A-Z]/.test(form.password) }">
                                            <i class="pi" :class="/[a-z]/.test(form.password) && /[A-Z]/.test(form.password) ? 'pi-check-circle' : 'pi-circle'"></i> Mayúsculas y minúsculas
                                        </span>
                                        <span :class="{ 'admin-user-check-ok': /\d/.test(form.password) }">
                                            <i class="pi" :class="/\d/.test(form.password) ? 'pi-check-circle' : 'pi-circle'"></i> Números
                                        </span>
                                        <span :class="{ 'admin-user-check-ok': /[^a-zA-Z0-9]/.test(form.password) }">
                                            <i class="pi" :class="/[^a-zA-Z0-9]/.test(form.password) ? 'pi-check-circle' : 'pi-circle'"></i> Caracteres especiales
                                        </span>
                                    </div>
                                    <div class="admin-user-password-actions">
                                        <button type="button" class="admin-user-btn-generate" @click="generarPassword">
                                            <i class="pi pi-refresh"></i> Generar contraseña
                                        </button>
                                        <button v-if="form.password" type="button" class="admin-user-btn-copy" @click="copiarPassword">
                                            <i class="pi pi-copy"></i> Copiar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="admin-user-field">
                                <label><i class="pi pi-check-circle"></i> Confirmar contraseña <span class="admin-user-required">*</span></label>
                                <div class="admin-user-password-wrap">
                                    <input v-model="confirmarPassword" :type="mostrarPassword ? 'text' : 'password'" placeholder="Repite la contraseña"
                                        :class="{ 'admin-user-input-error': confirmarPassword && !passwordsCoinciden }" />
                                    <i class="pi admin-user-password-toggle" style="pointer-events:none"
                                        :class="confirmarPassword ? (passwordsCoinciden ? 'pi-check-circle' : 'pi-times-circle') : 'pi-lock'"
                                        :style="{ color: confirmarPassword ? (passwordsCoinciden ? '#059669' : '#E53E3E') : undefined }"></i>
                                </div>
                                <p v-if="!passwordsCoinciden && confirmarPassword" class="admin-user-error-text">Las contraseñas no coinciden</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: VISTA PREVIA -->
                <div class="admin-prod-sidebar">
                    <div class="admin-prod-sidebar-card">
                        <div class="admin-prod-sidebar-card__header">
                            <h3><i class="pi pi-eye"></i> Vista previa del perfil</h3>
                        </div>
                        <div class="admin-prod-sidebar-card__body">
                            <div class="flex items-center gap-4 pb-4" style="border-bottom:1px solid var(--line)">
                                <div class="admin-user-preview-avatar">{{ inicialesPreview }}</div>
                                <div class="min-w-0">
                                    <p class="admin-user-preview-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ form.nombre || 'Nombre del usuario' }}</p>
                                    <p class="admin-user-preview-handle">{{ form.apodo ? '@' + form.apodo : '@usuario' }}</p>
                                    <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                        <span v-if="rolSeleccionado" class="admin-dash-badge" :class="badgeRolPreview">
                                            <span class="admin-dash-badge-dot"></span>{{ rolSeleccionado.label }}
                                        </span>
                                        <span v-if="esMayorEdad" class="admin-dash-badge admin-dash-badge--verificado">
                                            <span class="admin-dash-badge-dot"></span>Mayor de edad
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <dl class="admin-user-preview-dl">
                                <div class="admin-user-preview-dl-row">
                                    <dt>Correo</dt>
                                    <dd style="max-width:170px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ form.email || '—' }}</dd>
                                </div>
                                <div class="admin-user-preview-dl-row">
                                    <dt>Teléfono</dt>
                                    <dd>{{ form.telefono || '—' }}</dd>
                                </div>
                                <div class="admin-user-preview-dl-row">
                                    <dt>Nacimiento</dt>
                                    <dd v-if="form.fecha_nacimiento">
                                        {{ new Date(form.fecha_nacimiento).toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric', timeZone: 'UTC' }) }}
                                        <span :style="{ color: esMayorEdad ? '#059669' : '#E11D48' }">({{ edadPreview }} años)</span>
                                    </dd>
                                    <dd v-else>—</dd>
                                </div>
                                <div class="admin-user-preview-dl-row">
                                    <dt>Estado</dt>
                                    <dd class="flex items-center gap-1.5 justify-end">
                                        <span class="admin-user-toggle-pill-dot" :style="{ background: estadoSeleccionado.dot }"></span>{{ estadoSeleccionado.label }}
                                    </dd>
                                </div>
                            </dl>

                            <div style="margin-top:1.1rem;padding-top:0.9rem;border-top:1px solid var(--line)">
                                <div class="flex items-center justify-between text-xs mb-1.5" style="color:var(--muted-light)">
                                    <span>Completado</span>
                                    <span style="color:var(--ink-soft);font-weight:600">{{ porcentajeCompletado }}%</span>
                                </div>
                                <div class="admin-user-progress-track">
                                    <div class="admin-user-progress-fill" :style="{ width: porcentajeCompletado + '%' }"></div>
                                </div>
                            </div>

                            <div class="admin-user-form-status" :class="isFormValid ? 'admin-user-form-status--ok' : 'admin-user-form-status--warn'">
                                <i class="pi" :class="isFormValid ? 'pi-check-circle' : 'pi-info-circle'"></i>
                                <span>{{ isFormValid ? 'Todos los campos están completos' : 'Completa todos los campos obligatorios' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="admin-prod-action-card">
                        <button type="submit" :disabled="form.processing || !isFormValid" class="admin-prod-btn-save">
                            <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-save'"></i>
                            {{ form.processing ? 'Guardando...' : 'Registrar usuario' }}
                        </button>
                        <Link :href="route('admin.usuarios.index')" class="admin-prod-btn-cancel">Cancelar</Link>
                        <p class="admin-user-hint" style="text-align:center">Los campos con <span class="admin-user-required">*</span> son obligatorios</p>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>