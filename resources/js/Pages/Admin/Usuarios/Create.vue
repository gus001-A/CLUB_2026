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

// ============================================================
// EDAD
// ============================================================
function calcularEdad(fechaISO) {
    if (!fechaISO) return null;
    const hoy = new Date();
    const nacimiento = new Date(fechaISO);
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) edad--;
    return edad >= 0 && edad < 130 ? edad : null;
}

const edadPreview = computed(() => calcularEdad(form.fecha_nacimiento));
const esMayorEdad = computed(() => edadPreview.value !== null && edadPreview.value >= 18);

// ============================================================
// VALIDACIONES EN TIEMPO REAL
// ============================================================

const telefonoError = computed(() => {
    if (!form.telefono) return null;
    const soloNumeros = /^\d+$/.test(form.telefono);
    if (!soloNumeros) return 'Solo se permiten números';
    if (form.telefono.length > 10) return 'Máximo 10 dígitos';
    if (form.telefono.length < 10) return 'Mínimo 10 dígitos';
    return null;
});

const fechaMaxima = computed(() => {
    const fecha = new Date();
    fecha.setFullYear(fecha.getFullYear() - 18);
    return fecha.toISOString().split('T')[0];
});

const fechaError = computed(() => {
    if (!form.fecha_nacimiento) return null;
    if (edadPreview.value !== null && edadPreview.value < 18) return 'Debes ser mayor de 18 años';
    return null;
});

// ============================================================
// Fortaleza de la contraseña
// ============================================================
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
    if (f === 0) return { label: 'Sin contraseña', color: 'bg-gray-200', text: 'text-gray-400' };
    if (f <= 2) return { label: 'Débil', color: 'bg-red-500', text: 'text-red-500' };
    if (f === 3) return { label: 'Media', color: 'bg-amber-500', text: 'text-amber-500' };
    return { label: 'Fuerte', color: 'bg-emerald-500', text: 'text-emerald-500' };
});

const passwordsCoinciden = computed(() => {
    if (!confirmarPassword.value) return true;
    return form.password === confirmarPassword.value;
});

// ============================================================
// FUNCIONES
// ============================================================
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

// ============================================================
// Selectores
// ============================================================
const roles = [
    { value: 'usuario', label: 'Usuario' },
    { value: 'admin', label: 'Administrador' },
];

const estados = [
    { value: 'verificado', label: 'Verificado', dot: 'bg-emerald-500' },
    { value: 'pendiente', label: 'Pendiente', dot: 'bg-amber-500' },
    { value: 'incompleto', label: 'Incompleto', dot: 'bg-gray-400' },
];

// ============================================================
// Vista previa
// ============================================================
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

// ============================================================
// Estado del formulario
// ============================================================
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

// ============================================================
// Submit
// ============================================================
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
        onSuccess: () => {
            toast.success('Usuario creado con éxito.');
        },
        onError: (errors) => {
            const primerError = Object.values(errors)[0];
            toast.error(primerError || 'Ocurrió un error al crear el usuario.');
        }
    });
}

// ============================================================
// Watchers
// ============================================================
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

        <form @submit.prevent="submit" class="max-w-7xl mx-auto pb-24 lg:pb-10 px-4 sm:px-6">
            <!-- Botón volver -->
            <Link :href="route('admin.usuarios.index')"
                class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-brand mb-6 transition-colors group">
                <i class="pi pi-arrow-left text-xs group-hover:-translate-x-1 transition-transform duration-200"></i>
                Volver a Usuarios
            </Link>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- ============================================================ -->
                <!-- COLUMNA IZQUIERDA: FORMULARIO (ocupa 7/12) -->
                <!-- ============================================================ -->
                <div class="lg:col-span-7 bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
                    <!-- Cabecera sin icono -->
                    <div class="px-8 py-7 bg-gradient-to-r from-brand to-brand-dark">
                        <div>
                            <h1 class="text-2xl font-bold text-white">Registrar nuevo usuario</h1>
                            <p class="text-sm text-white/80 mt-0.5">Completa el formulario para dar de alta un nuevo
                                usuario en
                                la plataforma</p>
                        </div>
                    </div>

                    <!-- Formulario -->
                    <div class="p-8 space-y-7">

                        <!-- Sección: Información personal -->
                        <div>
                            <h3
                                class="text-sm font-semibold text-gray-800 mb-5 pb-2 border-b border-gray-200 flex items-center gap-2">
                                <i class="pi pi-user text-brand text-base"></i>
                                Información personal
                            </h3>

                            <!-- Nombre completo -->
                            <div class="field mb-5">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Nombre completo <span class="text-red-400">*</span>
                                </label>
                                <input v-model="form.nombre" type="text" name="nombre" autocomplete="name"
                                    placeholder="Ingresa el nombre completo del usuario"
                                    class="w-full px-5 py-4 rounded-xl border-2 border-gray-200 bg-white focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none text-gray-800 placeholder:text-gray-400 text-base"
                                    :class="{ 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/10': form.errors.nombre }" />
                                <p v-if="form.errors.nombre"
                                    class="text-rose-500 text-xs mt-1.5 flex items-center gap-1.5">
                                    <i class="pi pi-exclamation-circle text-[10px]"></i> {{ form.errors.nombre }}
                                </p>
                            </div>

                            <!-- Apodo y Email -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="field">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        <i class="pi pi-at text-brand text-xs mr-1"></i>
                                        Nombre de usuario <span class="text-red-400">*</span>
                                    </label>
                                    <input v-model="form.apodo" type="text" name="apodo" autocomplete="username"
                                        placeholder="Ej. anamaria"
                                        class="w-full px-5 py-4 rounded-xl border-2 border-gray-200 bg-white focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none text-gray-800 placeholder:text-gray-400 text-base"
                                        :class="{ 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/10': form.errors.apodo }" />
                                    <p v-if="form.errors.apodo"
                                        class="text-rose-500 text-xs mt-1.5 flex items-center gap-1.5">
                                        <i class="pi pi-exclamation-circle text-[10px]"></i> {{ form.errors.apodo }}
                                    </p>
                                </div>
                                <div class="field">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        <i class="pi pi-envelope text-brand text-xs mr-1"></i>
                                        Correo electrónico <span class="text-red-400">*</span>
                                    </label>
                                    <input v-model="form.email" type="email" name="email" autocomplete="email"
                                        placeholder="usuario@correo.com"
                                        class="w-full px-5 py-4 rounded-xl border-2 border-gray-200 bg-white focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none text-gray-800 placeholder:text-gray-400 text-base"
                                        :class="{ 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/10': form.errors.email }" />
                                    <p v-if="form.errors.email"
                                        class="text-rose-500 text-xs mt-1.5 flex items-center gap-1.5">
                                        <i class="pi pi-exclamation-circle text-[10px]"></i> {{ form.errors.email }}
                                    </p>
                                </div>
                            </div>

                            <!-- Teléfono y Fecha -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="field">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        <i class="pi pi-phone text-brand text-xs mr-1"></i>
                                        Teléfono <span class="text-gray-400 text-xs font-normal">(opcional)</span>
                                    </label>
                                    <input v-model="form.telefono" type="text" name="telefono" autocomplete="tel"
                                        inputmode="numeric" placeholder="55 1234 5678"
                                        class="w-full px-5 py-4 rounded-xl border-2 border-gray-200 bg-white focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none text-gray-800 placeholder:text-gray-400 text-base"
                                        :class="{ 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/10': telefonoError }" />
                                    <p v-if="telefonoError"
                                        class="text-rose-500 text-xs mt-1.5 flex items-center gap-1.5">
                                        <i class="pi pi-exclamation-circle text-[10px]"></i> {{ telefonoError }}
                                    </p>
                                </div>
                                <div class="field">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        <i class="pi pi-calendar text-brand text-xs mr-1"></i>
                                        Fecha de nacimiento <span class="text-red-400">*</span>
                                    </label>
                                    <input v-model="form.fecha_nacimiento" type="date" name="fecha_nacimiento"
                                        class="w-full px-5 py-4 rounded-xl border-2 border-gray-200 bg-white focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none text-gray-800 text-base"
                                        :class="{
                                            'border-rose-400 focus:border-rose-500 focus:ring-rose-500/10': fechaError,
                                            'border-emerald-400 focus:border-emerald-500': esMayorEdad && form.fecha_nacimiento
                                        }" :max="fechaMaxima" />
                                    <p v-if="fechaError" class="text-rose-500 text-xs mt-1.5 flex items-center gap-1.5">
                                        <i class="pi pi-exclamation-circle text-[10px]"></i> {{ fechaError }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Separador -->
                        <div class="border-t border-gray-200 pt-5"></div>

                        <!-- Sección: Configuración de cuenta -->
                        <div>
                            <h3
                                class="text-sm font-semibold text-gray-800 mb-5 pb-2 border-b border-gray-200 flex items-center gap-2">
                                <i class="pi pi-cog text-brand text-base"></i>
                                Configuración de cuenta
                            </h3>

                            <!-- Tipo de usuario -->
                            <div class="field mb-5">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="pi pi-users text-brand text-xs mr-1"></i>
                                    Tipo de usuario <span class="text-red-400">*</span>
                                </label>
                                <div class="flex gap-3" role="radiogroup" aria-label="Tipo de usuario">
                                    <button v-for="r in roles" :key="r.value" type="button" @click="form.rol = r.value"
                                        role="radio" :aria-checked="form.rol === r.value"
                                        class="flex-1 px-6 py-3.5 rounded-xl border-2 font-medium transition-all text-center"
                                        :class="form.rol === r.value
                                            ? 'border-brand bg-brand/5 text-brand shadow-sm'
                                            : 'border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50'">
                                        {{ r.label }}
                                    </button>
                                </div>
                                <p v-if="form.errors.rol" class="text-rose-500 text-xs mt-2 flex items-center gap-1.5">
                                    <i class="pi pi-exclamation-circle text-[10px]"></i> {{ form.errors.rol }}
                                </p>
                            </div>

                            <!-- Estado -->
                            <div class="field">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="pi pi-verified text-brand text-xs mr-1"></i>
                                    Estado de la cuenta <span class="text-red-400">*</span>
                                </label>
                                <div class="flex flex-wrap gap-2.5" role="radiogroup" aria-label="Estado de la cuenta">
                                    <button v-for="e in estados" :key="e.value" type="button"
                                        @click="form.estado = e.value" role="radio"
                                        :aria-checked="form.estado === e.value"
                                        class="px-5 py-2.5 rounded-full border-2 text-sm font-medium transition-all flex items-center gap-2"
                                        :class="form.estado === e.value
                                            ? 'border-brand bg-brand/5 text-brand shadow-sm'
                                            : 'border-gray-200 text-gray-500 hover:border-gray-300 hover:bg-gray-50'">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="e.dot"></span>
                                        {{ e.label }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Separador -->
                        <div class="border-t border-gray-200 pt-5"></div>

                        <!-- Sección: Credenciales -->
                        <div>
                            <h3
                                class="text-sm font-semibold text-gray-800 mb-5 pb-2 border-b border-gray-200 flex items-center gap-2">
                                <i class="pi pi-lock text-brand text-base"></i>
                                Credenciales de acceso
                            </h3>

                            <div class="space-y-4">
                                <!-- Contraseña -->
                                <div class="field">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        <i class="pi pi-key text-brand text-xs mr-1"></i>
                                        Contraseña <span class="text-red-400">*</span>
                                        <span class="text-gray-400 text-xs font-normal ml-1">(mínimo 8
                                            caracteres)</span>
                                    </label>
                                    <div class="relative">
                                        <input v-model="form.password" :type="mostrarPassword ? 'text' : 'password'"
                                            name="password" autocomplete="new-password"
                                            placeholder="Ingresa una contraseña segura"
                                            class="w-full px-5 py-4 pr-14 rounded-xl border-2 border-gray-200 bg-white focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none text-gray-800 placeholder:text-gray-400 text-base"
                                            :class="{ 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/10': form.errors.password }" />
                                        <button type="button" @click="mostrarPassword = !mostrarPassword"
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                            <i class="pi text-lg"
                                                :class="mostrarPassword ? 'pi-eye-slash' : 'pi-eye'"></i>
                                        </button>
                                    </div>
                                    <p v-if="form.errors.password"
                                        class="text-rose-500 text-xs mt-1.5 flex items-center gap-1.5">
                                        <i class="pi pi-exclamation-circle text-[10px]"></i> {{ form.errors.password }}
                                    </p>

                                    <!-- Indicador de fortaleza -->
                                    <div v-if="form.password"
                                        class="mt-3 space-y-2 p-4 rounded-xl bg-gray-50 border-2 border-gray-100">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1 h-2 rounded-full bg-gray-200 overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-500 ease-out"
                                                    :class="nivelFuerza.color"
                                                    :style="{ width: `${(fuerzaPassword / 4) * 100}%` }">
                                                </div>
                                            </div>
                                            <span class="text-xs font-medium" :class="nivelFuerza.text">
                                                {{ nivelFuerza.label }}
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-gray-400">
                                            <span :class="{ 'text-emerald-600': form.password.length >= 8 }">
                                                <i class="pi"
                                                    :class="form.password.length >= 8 ? 'pi-check-circle' : 'pi-circle'"></i>
                                                8+ caracteres
                                            </span>
                                            <span
                                                :class="{ 'text-emerald-600': /[a-z]/.test(form.password) && /[A-Z]/.test(form.password) }">
                                                <i class="pi"
                                                    :class="/[a-z]/.test(form.password) && /[A-Z]/.test(form.password) ? 'pi-check-circle' : 'pi-circle'"></i>
                                                Mayúsculas y minúsculas
                                            </span>
                                            <span :class="{ 'text-emerald-600': /\d/.test(form.password) }">
                                                <i class="pi"
                                                    :class="/\d/.test(form.password) ? 'pi-check-circle' : 'pi-circle'"></i>
                                                Números
                                            </span>
                                            <span :class="{ 'text-emerald-600': /[^a-zA-Z0-9]/.test(form.password) }">
                                                <i class="pi"
                                                    :class="/[^a-zA-Z0-9]/.test(form.password) ? 'pi-check-circle' : 'pi-circle'"></i>
                                                Caracteres especiales
                                            </span>
                                        </div>
                                        <div class="flex gap-2 pt-2 border-t border-gray-200">
                                            <button type="button" @click="generarPassword"
                                                class="text-xs font-medium px-4 py-2 rounded-lg bg-brand text-white hover:bg-brand-dark transition-all flex items-center gap-2 flex-1 justify-center shadow-sm hover:shadow-md">
                                                <i class="pi pi-refresh text-[10px]"></i>
                                                Generar contraseña
                                            </button>
                                            <button v-if="form.password" type="button" @click="copiarPassword"
                                                class="text-xs font-medium px-4 py-2 rounded-lg border-2 border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition-all flex items-center gap-2 flex-1 justify-center">
                                                <i class="pi pi-copy text-[10px]"></i>
                                                Copiar
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Confirmar contraseña -->
                                <div class="field">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        <i class="pi pi-check-circle text-brand text-xs mr-1"></i>
                                        Confirmar contraseña <span class="text-red-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <input v-model="confirmarPassword" :type="mostrarPassword ? 'text' : 'password'"
                                            name="password_confirmation" autocomplete="new-password"
                                            placeholder="Repite la contraseña"
                                            class="w-full px-5 py-4 pr-14 rounded-xl border-2 border-gray-200 bg-white focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none text-gray-800 placeholder:text-gray-400 text-base"
                                            :class="{
                                                'border-emerald-400 focus:border-emerald-500': confirmarPassword && passwordsCoinciden,
                                                'border-rose-400 focus:border-rose-500': confirmarPassword && !passwordsCoinciden
                                            }" />
                                        <i class="pi absolute right-4 top-1/2 -translate-y-1/2 text-lg"
                                            :class="confirmarPassword ? (passwordsCoinciden ? 'pi-check-circle text-emerald-500' : 'pi-times-circle text-rose-500') : 'pi-lock text-gray-300'"></i>
                                    </div>
                                    <p v-if="!passwordsCoinciden && confirmarPassword"
                                        class="text-rose-500 text-xs mt-1.5 flex items-center gap-1.5">
                                        <i class="pi pi-exclamation-circle text-[10px]"></i> Las contraseñas no
                                        coinciden
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- COLUMNA DERECHA: VISTA PREVIA + BOTONES (ocupa 5/12) -->
                <!-- ============================================================ -->
                <div class="lg:col-span-5">
                    <div class="lg:sticky lg:top-6 space-y-6">
                        <!-- Vista previa -->
                        <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
                            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                                    <i class="pi pi-eye text-brand"></i>
                                    Vista previa del perfil
                                </h3>
                            </div>

                            <div class="p-6">
                                <!-- Avatar y nombre -->
                                <div class="flex items-center gap-4 mb-5 pb-5 border-b border-gray-200">
                                    <div
                                        class="w-20 h-20 rounded-full bg-gradient-to-br from-brand to-brand-dark text-white font-bold flex items-center justify-center text-3xl shrink-0 shadow-md ring-4 ring-brand/20">
                                        {{ inicialesPreview }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xl font-semibold text-gray-900 truncate">
                                            {{ form.nombre || 'Nombre del usuario' }}
                                        </p>
                                        <p class="text-sm text-gray-400 truncate">
                                            {{ form.apodo ? '@' + form.apodo : '@usuario' }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                            <span v-if="rolSeleccionado"
                                                class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-700">
                                                {{ rolSeleccionado.label }}
                                            </span>
                                            <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                            <span
                                                class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-gray-100 text-gray-700">
                                                {{ estadoSeleccionado.label }}
                                            </span>
                                            <span v-if="esMayorEdad"
                                                class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">
                                                <i class="pi pi-check-circle text-[10px]"></i>
                                                Mayor de edad
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Información -->
                                <dl class="space-y-3 text-sm">
                                    <div class="flex items-center justify-between py-1.5">
                                        <dt class="text-gray-400">Correo electrónico</dt>
                                        <dd class="text-gray-800 font-medium truncate max-w-[160px]">{{ form.email ||
                                            '—' }}
                                        </dd>
                                    </div>
                                    <div class="flex items-center justify-between py-1.5 border-t border-gray-100">
                                        <dt class="text-gray-400">Teléfono</dt>
                                        <dd class="text-gray-800 font-medium">{{ form.telefono || '—' }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between py-1.5 border-t border-gray-100">
                                        <dt class="text-gray-400">Fecha de nacimiento</dt>
                                        <dd class="text-gray-800 font-medium">
                                            <span v-if="form.fecha_nacimiento">
                                                {{ new Date(form.fecha_nacimiento).toLocaleDateString('es-MX', {
                                                    day:
                                                        '2-digit',
                                                    month: 'long', year: 'numeric'
                                                }) }}
                                                <span class="text-xs ml-1"
                                                    :class="esMayorEdad ? 'text-emerald-500' : 'text-rose-500'">
                                                    ({{ edadPreview }} años)
                                                </span>
                                            </span>
                                            <span v-else>—</span>
                                        </dd>
                                    </div>
                                    <div class="flex items-center justify-between py-1.5 border-t border-gray-100">
                                        <dt class="text-gray-400">Estado</dt>
                                        <dd class="text-gray-800 font-medium">
                                            <span class="inline-flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 rounded-full"
                                                    :class="estadoSeleccionado.dot"></span>
                                                {{ estadoSeleccionado.label }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>

                                <!-- Progreso -->
                                <div class="mt-5 pt-4 border-t border-gray-200">
                                    <div class="flex items-center justify-between text-xs text-gray-400 mb-1.5">
                                        <span>Completado</span>
                                        <span class="font-medium text-gray-600">{{ porcentajeCompletado }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-brand to-brand-dark transition-all duration-500 rounded-full"
                                            :style="{ width: porcentajeCompletado + '%' }">
                                        </div>
                                    </div>
                                </div>

                                <!-- Estado del formulario -->
                                <div class="mt-4 pt-3 border-t border-gray-200">
                                    <div class="flex items-center gap-2 text-xs"
                                        :class="isFormValid ? 'text-emerald-600' : 'text-amber-600'">
                                        <i class="pi" :class="isFormValid ? 'pi-check-circle' : 'pi-info-circle'"></i>
                                        <span>
                                            {{ isFormValid ? 'Todos los campos están completos' : 'Completa todos los campos obligatorios' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Botones de acción -->
                                <div class="mt-5 pt-4 border-t border-gray-200">
                                    <div class="flex flex-col gap-2.5">
                                        <button type="submit" :disabled="form.processing || !isFormValid"
                                            class="w-full py-3.5 rounded-xl bg-gradient-to-r from-brand to-brand-dark text-white font-semibold hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 text-base">
                                            <i class="pi"
                                                :class="form.processing ? 'pi-spin pi-spinner' : 'pi-save'"></i>
                                            {{ form.processing ? 'Guardando...' : 'Registrar usuario' }}
                                        </button>
                                        <Link :href="route('admin.usuarios.index')"
                                            class="w-full py-3.5 rounded-xl border-2 border-gray-200 text-gray-600 font-semibold hover:bg-gray-50 hover:border-gray-300 transition-all flex items-center justify-center gap-2 text-base">
                                            <i class="pi pi-times"></i>
                                            Cancelar
                                        </Link>
                                    </div>
                                    <div class="text-xs text-gray-400 flex items-center justify-center gap-2 mt-3">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                        Los campos con <span class="text-rose-400">*</span> son obligatorios
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra de acciones fija en móvil -->
            <div class="lg:hidden fixed bottom-0 left-0 right-0 z-20 bg-white/95 backdrop-blur-sm border-t border-gray-200 px-4 py-3 flex items-center gap-3"
                style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
                <Link :href="route('admin.usuarios.index')"
                    class="px-4 py-3 rounded-xl border-2 border-gray-200 text-gray-600 font-semibold text-sm shrink-0">
                    Cancelar
                </Link>
                <button type="submit" :disabled="form.processing || !isFormValid"
                    class="flex-1 py-3 rounded-xl bg-gradient-to-r from-brand to-brand-dark text-white font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-save'"></i>
                    {{ form.processing ? 'Guardando...' : 'Registrar Usuario' }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>