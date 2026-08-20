<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    perfil: Object,
});

const toast = useToast();

const form = useForm({
    nombre: props.perfil.nombre || '',
    nickname: props.perfil.nickname || '',
    telefono: props.perfil.telefono || '',
    foto_perfil: null, // File
    eliminar_foto: false,
});

const fotoActual = ref(props.perfil.foto_perfil_url || null);
const preview = ref(null);

function onFileChange(event) {
    const file = event.target.files?.[0] || null;
    if (preview.value) URL.revokeObjectURL(preview.value);
    form.foto_perfil = file;
    form.eliminar_foto = false;
    preview.value = file ? URL.createObjectURL(file) : null;
    event.target.value = '';
}

function quitarFoto() {
    if (preview.value) URL.revokeObjectURL(preview.value);
    form.foto_perfil = null;
    preview.value = null;
    fotoActual.value = null;
    form.eliminar_foto = true;
}

function guardar() {
    form.transform((data) => ({
        ...data,
        _method: 'patch',
    })).post(route('admin.configuracion.actualizar'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => toast.success('Configuración actualizada correctamente.'),
        onError: () => toast.error('Revisa los datos del formulario.'),
    });
}

const rolLabel = { super_admin: 'Super Admin', admin: 'Admin', moderador: 'Moderador', soporte: 'Soporte' };
</script>

<template>
    <Head title="Configuración" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Dashboard &gt; Configuración</template>

        <div class="max-w-2xl mx-auto">

            <div class="admin-card overflow-hidden">
                <div class="admin-card-header">
                    <span class="admin-card-header-title"><i class="pi pi-user text-brand"></i> Configuración de mi cuenta</span>
                    <span class="text-xs" style="color:var(--muted)">{{ rolLabel[perfil.rol] ?? perfil.rol }}</span>
                </div>

                <form @submit.prevent="guardar" class="p-6 space-y-6">

                    <!-- Foto de perfil -->
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0 overflow-hidden" style="width:72px;height:72px">
                            <img v-if="preview || fotoActual" :src="preview || fotoActual" class="w-full h-full object-cover" />
                            <i v-else class="pi pi-user" style="font-size:1.75rem"></i>
                        </div>
                        <div>
                            <label class="admin-btn-secondary inline-flex items-center gap-2 cursor-pointer">
                                <i class="pi pi-upload text-xs"></i> Cambiar foto
                                <input type="file" class="hidden" accept="image/*" @change="onFileChange" />
                            </label>
                            <button v-if="preview || fotoActual" type="button" @click="quitarFoto" class="ml-2 text-xs font-semibold text-red-500 hover:underline">
                                Quitar
                            </button>
                            <p class="text-xs text-gray-400 mt-1.5">Para que los demás admins te distingan de un vistazo.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre</label>
                            <input v-model="form.nombre" type="text" class="admin-input px-3 py-2.5" />
                            <p v-if="form.errors.nombre" class="text-red-600 text-xs mt-1">{{ form.errors.nombre }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Usuario</label>
                            <input v-model="form.nickname" type="text" placeholder="Ej. gus_admin" class="admin-input px-3 py-2.5" />
                            <p v-if="form.errors.nickname" class="text-red-600 text-xs mt-1">{{ form.errors.nickname }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Teléfono</label>
                            <input v-model="form.telefono" type="text" class="admin-input px-3 py-2.5" />
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing" class="admin-btn-primary disabled:opacity-50">
                        <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-check'"></i>
                        {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                    </button>
                </form>
            </div>

            <p class="text-xs text-gray-400 text-center mt-4">
                ¿Buscas cambiar tu correo, contraseña o activar la verificación en dos pasos?
                <a :href="route('admin.seguridad.index')" class="text-brand font-medium hover:underline">Ve a Seguridad</a>.
            </p>
        </div>
    </AdminLayout>
</template>