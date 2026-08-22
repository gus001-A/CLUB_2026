<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();
const mostrarPassword = ref(false);

const props = defineProps({
    usuario: Object,
});

const form = useForm({
    nombre: props.usuario.nombre,
    apodo: props.usuario.apodo,
    email: props.usuario.email,
    password: '',
    telefono: props.usuario.telefono || '',
    fecha_nacimiento: props.usuario.fecha_nacimiento?.slice(0, 10) || '',
    rol: props.usuario.rol,
    estado: props.usuario.estado,
});

const roles = [
    { value: 'usuario', label: 'Usuario', icon: 'pi-user' },
    { value: 'creador', label: 'Creador', icon: 'pi-star' },
    { value: 'admin', label: 'Admin', icon: 'pi-shield' },
];
const estados = [
    { value: 'verificado', label: 'Verificado', dot: '#059669' },
    { value: 'pendiente', label: 'Pendiente', dot: '#D97706' },
    { value: 'incompleto', label: 'Incompleto', dot: '#6B7280' },
    { value: 'bloqueado', label: 'Bloqueado', dot: '#DC2626' },
];

function generarPassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$';
    let clave = '';
    for (let i = 0; i < 10; i++) {
        clave += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    form.password = clave;
    mostrarPassword.value = true;
    toast.success('Contraseña generada. No olvides copiarla antes de guardar.');
}

function submit() {
    const obligatorios = ['nombre', 'apodo', 'email', 'fecha_nacimiento', 'rol'];
    const faltantes = obligatorios.filter((campo) => !form[campo]);

    if (faltantes.length) {
        toast.error('Debes llenar todos los campos obligatorios.');
        return;
    }

    form.patch(route('admin.usuarios.update', props.usuario.id), {
        onError: (errors) => {
            const primerError = Object.values(errors)[0];
            toast.error(primerError || 'Revisa los datos del formulario.');
        },
        onSuccess: () => toast.success('Usuario actualizado correctamente.'),
    });
}
</script>

<template>
    <Head title="Editar Usuario" />

    <AdminLayout>
        <template #title>Editar Usuario</template>
        <template #breadcrumb>Usuarios / {{ usuario.nombre }}</template>

        <div class="admin-user-form-page">
            <Link :href="route('admin.usuarios.show', usuario.id)" class="admin-user-back-link">
                <i class="pi pi-arrow-left"></i>
                Volver al usuario
            </Link>

            <form @submit.prevent="submit" class="admin-user-form-grid">
                <!-- COLUMNA IZQUIERDA: FORMULARIO -->
                <div class="admin-user-form">
                    <div class="admin-user-form-header">
                        <div class="admin-user-form-header__icon"><i class="pi pi-pencil"></i></div>
                        <div>
                            <h1>Editar Usuario</h1>
                            <p>{{ usuario.nombre }}</p>
                        </div>
                    </div>

                    <div class="admin-user-form-body">
                        <!-- Datos personales -->
                        <div>
                            <div class="admin-user-form-section-title"><i class="pi pi-user"></i> Datos personales</div>

                            <div class="admin-user-field" style="margin-bottom:0.9rem">
                                <label>Nombre completo <span class="admin-user-required">*</span></label>
                                <input v-model="form.nombre" type="text" placeholder="Ej. Juan Pérez García"
                                    :class="{ 'admin-user-input-error': form.errors.nombre }" />
                                <p v-if="form.errors.nombre" class="admin-user-error-text">{{ form.errors.nombre }}</p>
                            </div>

                            <div class="admin-user-field-row" style="margin-bottom:0.9rem">
                                <div class="admin-user-field">
                                    <label><i class="pi pi-at"></i> Nombre de usuario <span class="admin-user-required">*</span></label>
                                    <input v-model="form.apodo" type="text" placeholder="Ej. jperez"
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

                            <div class="admin-user-field-row" style="margin-bottom:0.9rem">
                                <div class="admin-user-field">
                                    <label><i class="pi pi-phone"></i> Teléfono</label>
                                    <input v-model="form.telefono" type="text" placeholder="7771234567"
                                        :class="{ 'admin-user-input-error': form.errors.telefono }" />
                                    <p v-if="form.errors.telefono" class="admin-user-error-text">{{ form.errors.telefono }}</p>
                                </div>
                                <div class="admin-user-field">
                                    <label><i class="pi pi-calendar"></i> Fecha de nacimiento <span class="admin-user-required">*</span></label>
                                    <input v-model="form.fecha_nacimiento" type="date"
                                        :class="{ 'admin-user-input-error': form.errors.fecha_nacimiento }" />
                                    <p v-if="form.errors.fecha_nacimiento" class="admin-user-error-text">{{ form.errors.fecha_nacimiento }}</p>
                                </div>
                            </div>

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
                                <label>Estado <span class="admin-user-required">*</span></label>
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
                            <p class="admin-user-hint" style="margin-bottom:0.6rem">Deja en blanco si no quieres cambiar la contraseña</p>

                            <div class="admin-user-field">
                                <label><i class="pi pi-key"></i> Nueva contraseña</label>
                                <div class="admin-user-password-wrap" style="display:flex;gap:0.5rem">
                                    <div class="admin-user-password-wrap" style="flex:1">
                                        <input v-model="form.password" :type="mostrarPassword ? 'text' : 'password'" placeholder="Dejar en blanco para no cambiar" />
                                        <button type="button" class="admin-user-password-toggle" @click="mostrarPassword = !mostrarPassword">
                                            <i class="pi" :class="mostrarPassword ? 'pi-eye-slash' : 'pi-eye'"></i>
                                        </button>
                                    </div>
                                    <button type="button" class="admin-user-btn-generate" style="padding:0 0.9rem;border-radius:8px;border:none;color:#fff;font-size:0.75rem;font-weight:600" @click="generarPassword">
                                        <i class="pi pi-refresh"></i> Generar
                                    </button>
                                </div>
                                <p v-if="form.errors.password" class="admin-user-error-text">{{ form.errors.password }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: ACCIONES -->
                <div class="admin-prod-sidebar">
                    <div class="admin-prod-action-card">
                        <button type="submit" :disabled="form.processing" class="admin-prod-btn-save">
                            <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-check'"></i>
                            {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                        </button>
                        <Link :href="route('admin.usuarios.show', usuario.id)" class="admin-prod-btn-cancel">Cancelar</Link>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>