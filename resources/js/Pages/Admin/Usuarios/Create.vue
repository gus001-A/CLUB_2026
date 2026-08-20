<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();
const mostrarPassword = ref(false);

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

function generarPassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$';
    let clave = '';
    for (let i = 0; i < 10; i++) {
        clave += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    form.password = clave;
    mostrarPassword.value = true;
}

function submit() {
    const obligatorios = ['nombre', 'apodo', 'email', 'password', 'fecha_nacimiento', 'rol'];
    const faltantes = obligatorios.filter((campo) => !form[campo]);

    if (faltantes.length) {
        toast.error('Debes llenar todos los campos obligatorios.');
        return;
    }

    form.post(route('admin.usuarios.store'), {
        onSuccess: () => {
            // Se dispara si el backend responde con 200/302 éxitoso
            toast.success('Usuario creado con éxito.');
        },
        onError: (errors) => {
            // Se dispara si la validación falla en Laravel
            const primerError = Object.values(errors)[0];
            toast.error(primerError || 'Ocurrió un error al crear el usuario.');
        }
    });
}
</script>

<template>

    <Head title="Agregar Usuario" />

    <AdminLayout>
        <template #title>Agregar Usuario</template>
        <template #breadcrumb>
            <span v-if="origen === 'dashboard'">Dashboard &gt; Usuarios &gt; Agregar Usuario</span>
            <span v-else>Usuarios &gt; Agregar Usuario</span>
        </template>

        <div class="max-w-3xl mx-auto">
            <Link :href="route('admin.usuarios.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand mb-4">
                <i class="pi pi-arrow-left text-xs"></i> Volver a Usuarios
            </Link>

            <form @submit.prevent="submit" class="admin-card overflow-hidden">
                <div style="height:6px;background:linear-gradient(90deg,#C81E3A,#E85C74)"></div>

                <!-- Sección: Datos personales -->
                <div class="p-6">
                    <div class="flex items-center gap-3 pb-4 mb-5 border-b" style="border-color:var(--line)">
                        <div class="admin-icon-gradient shrink-0" style="width:48px;height:48px">
                            <i class="pi pi-user text-lg"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-gray-800">Datos Personales</h2>
                            <p class="text-xs text-gray-400">Información principal del usuario</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="flex items-center gap-1.5 text-sm font-medium text-gray-700 mb-1.5">
                                <i class="pi pi-user text-brand text-xs"></i> Nombre completo <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input v-model="form.nombre" type="text" placeholder="Ej: Juan Pérez García" class="admin-input pl-3 pr-9 py-2.5" />
                                <i class="pi pi-user absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                            </div>
                            <p v-if="form.errors.nombre" class="text-red-600 text-xs mt-1">{{ form.errors.nombre }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="flex items-center gap-1.5 text-sm font-medium text-gray-700 mb-1.5">
                                    <i class="pi pi-at text-brand text-xs"></i> Nombre de usuario <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input v-model="form.apodo" type="text" placeholder="Ej: jperez" class="admin-input pl-3 pr-9 py-2.5" />
                                    <i class="pi pi-at absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                                </div>
                                <p v-if="form.errors.apodo" class="text-red-600 text-xs mt-1">{{ form.errors.apodo }}</p>
                            </div>
                            <div>
                                <label class="flex items-center gap-1.5 text-sm font-medium text-gray-700 mb-1.5">
                                    <i class="pi pi-envelope text-brand text-xs"></i> Correo electrónico <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input v-model="form.email" type="email" placeholder="usuario@correo.com" class="admin-input pl-3 pr-9 py-2.5" />
                                    <i class="pi pi-envelope absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                                </div>
                                <p v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="flex items-center gap-1.5 text-sm font-medium text-gray-700 mb-1.5">
                                    <i class="pi pi-phone text-brand text-xs"></i> Teléfono
                                </label>
                                <div class="relative">
                                    <input v-model="form.telefono" type="text" placeholder="7771234567" class="admin-input pl-3 pr-9 py-2.5" />
                                    <i class="pi pi-phone absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                                </div>
                                <p v-if="form.errors.telefono" class="text-red-600 text-xs mt-1">{{ form.errors.telefono }}</p>
                            </div>
                            <div>
                                <label class="flex items-center gap-1.5 text-sm font-medium text-gray-700 mb-1.5">
                                    <i class="pi pi-calendar text-brand text-xs"></i> Fecha de nacimiento <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.fecha_nacimiento" type="date" class="admin-input px-3 py-2.5" />
                                <p v-if="form.errors.fecha_nacimiento" class="text-red-600 text-xs mt-1">{{ form.errors.fecha_nacimiento }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="flex items-center gap-1.5 text-sm font-medium text-gray-700 mb-1.5">
                                    <i class="pi pi-sliders-h text-brand text-xs"></i> Tipo de usuario <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select v-model="form.rol" class="admin-input appearance-none pl-3 pr-9 py-2.5">
                                        <option value="" disabled>Selecciona un tipo</option>
                                        <option value="usuario">Usuario</option>
                                        <option value="creador">Creador</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                    <i class="pi pi-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 text-xs pointer-events-none"></i>
                                </div>
                                <p v-if="form.errors.rol" class="text-red-600 text-xs mt-1">{{ form.errors.rol }}</p>
                            </div>
                            <div>
                                <label class="flex items-center gap-1.5 text-sm font-medium text-gray-700 mb-1.5">
                                    <i class="pi pi-verified text-brand text-xs"></i> Estado <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select v-model="form.estado" class="admin-input appearance-none pl-3 pr-9 py-2.5">
                                        <option value="verificado">Verificado</option>
                                        <option value="pendiente">Pendiente</option>
                                        <option value="incompleto">Incompleto</option>
                                        <option value="bloqueado">Bloqueado</option>
                                    </select>
                                    <i class="pi pi-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 text-xs pointer-events-none"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección: Credenciales de acceso -->
                <div class="p-6 border-t" style="background:var(--surface);border-color:var(--line)">
                    <div class="flex items-center justify-between gap-3 pb-4 mb-5 border-b" style="border-color:var(--line)">
                        <div class="flex items-center gap-3">
                            <div class="admin-icon-gradient shrink-0" style="width:48px;height:48px">
                                <i class="pi pi-lock text-lg"></i>
                            </div>
                            <div>
                                <h2 class="font-semibold text-gray-800">Credenciales de Acceso</h2>
                                <p class="text-xs text-gray-400">Configure la contraseña del usuario</p>
                            </div>
                        </div>
                        <span class="bg-brand text-white text-xs font-semibold px-3 py-1 rounded-full shrink-0">Obligatorio</span>
                    </div>

                    <label class="flex items-center gap-1.5 text-sm font-medium text-gray-700 mb-1.5">
                        <i class="pi pi-lock text-brand text-xs"></i> Contraseña <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input v-model="form.password" :type="mostrarPassword ? 'text' : 'password'" placeholder="Contraseña (mínimo 8 caracteres)" class="admin-input pl-3 pr-32 py-2.5" />
                        <div class="absolute right-1.5 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                            <button type="button" @click="generarPassword" class="bg-brand hover:bg-brand-dark text-white text-xs font-medium px-2.5 py-1.5 rounded-md flex items-center gap-1">
                                <i class="pi pi-refresh text-[10px]"></i> Generar
                            </button>
                            <button type="button" @click="mostrarPassword = !mostrarPassword" class="text-gray-400 hover:text-gray-600 px-1.5">
                                <i class="pi" :class="mostrarPassword ? 'pi-eye-slash' : 'pi-eye'"></i>
                            </button>
                        </div>
                    </div>
                    <p v-if="form.errors.password" class="text-red-600 text-xs mt-1">{{ form.errors.password }}</p>
                </div>

                <!-- Acciones -->
                <div class="p-6 border-t flex items-center gap-3" style="border-color:var(--line)">
                    <button type="submit" :disabled="form.processing" class="admin-btn-primary disabled:opacity-50">
                        <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-check'"></i>
                        {{ form.processing ? 'Guardando...' : 'Guardar usuario' }}
                    </button>
                    <Link :href="route('admin.usuarios.index')" class="admin-btn-secondary" style="border:none">
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>