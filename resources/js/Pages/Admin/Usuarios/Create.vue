<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const form = useForm({
    nombre: '',
    apodo: '',
    email: '',
    password: '',
    telefono: '',
    fecha_nacimiento: '',
    rol: 'usuario',
    estado: 'verificado',
});

function submit() {
    // Validación rápida en el navegador antes de mandar al servidor
    const obligatorios = ['nombre', 'apodo', 'email', 'password', 'fecha_nacimiento'];
    const faltantes = obligatorios.filter((campo) => !form[campo]);

    if (faltantes.length) {
        toast.error('Debes llenar todos los campos obligatorios.');
        return;
    }

    form.post(route('admin.usuarios.store'), {
        onError: (errors) => {
            const primerError = Object.values(errors)[0];
            toast.error(primerError || 'Revisa los datos del formulario.');
        },
        onSuccess: () => {
            toast.success('Usuario creado correctamente.');
        },
    });
}
</script>

<template>
    <Head title="Agregar Usuario" />

    <AdminLayout>
        <template #title>Agregar Usuario</template>
        <template #breadcrumb>Dashboard &gt; Usuarios &gt; Agregar Usuario</template>

        <div class="max-w-3xl">
            <Link :href="route('admin.usuarios.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand mb-4">
                <i class="pi pi-arrow-left text-xs"></i> Volver a Usuarios
            </Link>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Sección: Datos personales -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="rounded-xl bg-brand/10 text-brand flex items-center justify-center shrink-0" style="width:48px;height:48px">
                            <i class="pi pi-user text-lg"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-gray-800">Datos personales</h2>
                            <p class="text-xs text-gray-400">Información básica del usuario</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo *</label>
                            <input v-model="form.nombre" type="text" placeholder="Ej. Ana García" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                            <p v-if="form.errors.nombre" class="text-red-600 text-xs mt-1">{{ form.errors.nombre }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Apodo / usuario *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">@</span>
                                <input v-model="form.apodo" type="text" placeholder="ana_garcia" class="w-full rounded-lg border border-gray-300 text-sm pl-7 pr-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                            </div>
                            <p v-if="form.errors.apodo" class="text-red-600 text-xs mt-1">{{ form.errors.apodo }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input v-model="form.telefono" type="text" placeholder="55 1234 5678" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                            <p v-if="form.errors.telefono" class="text-red-600 text-xs mt-1">{{ form.errors.telefono }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de nacimiento *</label>
                            <input v-model="form.fecha_nacimiento" type="date" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                            <p v-if="form.errors.fecha_nacimiento" class="text-red-600 text-xs mt-1">{{ form.errors.fecha_nacimiento }}</p>
                        </div>
                    </div>
                </div>

                <!-- Sección: Cuenta y acceso -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="rounded-xl bg-brand/10 text-brand flex items-center justify-center shrink-0" style="width:48px;height:48px">
                            <i class="pi pi-lock text-lg"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-gray-800">Cuenta y acceso</h2>
                            <p class="text-xs text-gray-400">Credenciales de inicio de sesión</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico *</label>
                            <input v-model="form.email" type="email" placeholder="correo@ejemplo.com" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                            <p v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña *</label>
                            <input v-model="form.password" type="password" placeholder="Mínimo 8 caracteres" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                            <p v-if="form.errors.password" class="text-red-600 text-xs mt-1">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rol *</label>
                            <select v-model="form.rol" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                                <option value="usuario">Usuario</option>
                                <option value="creador">Creador</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado *</label>
                            <select v-model="form.estado" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                                <option value="verificado">Verificado</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="incompleto">Incompleto</option>
                                <option value="bloqueado">Bloqueado</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-brand hover:bg-brand-dark text-white font-medium px-6 py-2.5 rounded-lg text-sm disabled:opacity-50 flex items-center gap-2"
                    >
                        <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-check'"></i>
                        {{ form.processing ? 'Guardando...' : 'Guardar usuario' }}
                    </button>
                    <Link :href="route('admin.usuarios.index')" class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5">
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>