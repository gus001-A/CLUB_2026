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
            <div class="admin-cobros-card">
                <div class="admin-cobros-card__header">
                    <div class="admin-cobros-card__header-left">
                        <div class="admin-cobros-header-icon"><i class="pi pi-user"></i></div>
                        <h3>Configuración de mi cuenta</h3>
                    </div>
                    <span class="admin-dash-badge admin-dash-badge--rol-admin">
                        <span class="admin-dash-badge-dot"></span>{{ rolLabel[perfil.rol] ?? perfil.rol }}
                    </span>
                </div>

                <form @submit.prevent="guardar" class="flex flex-col gap-5" style="padding:1.5rem">

                    <!-- Foto de perfil -->
                    <div class="flex items-center gap-4">
                        <div class="admin-icon-circle" style="width:72px;height:72px;overflow:hidden;font-size:1.75rem">
                            <img v-if="preview || fotoActual" :src="preview || fotoActual" class="w-full h-full object-cover" />
                            <i v-else class="pi pi-user"></i>
                        </div>
                        <div>
                            <label class="admin-btn-secondary inline-flex items-center gap-2 cursor-pointer">
                                <i class="pi pi-upload text-xs"></i> Cambiar foto
                                <input type="file" class="hidden" accept="image/*" @change="onFileChange" />
                            </label>
                            <button v-if="preview || fotoActual" type="button" @click="quitarFoto" style="color:#EF4444" class="ml-2 text-xs font-semibold hover:underline">
                                Quitar
                            </button>
                            <p class="admin-user-hint" style="margin-top:0.4rem">Para que los demás admins te distingan de un vistazo.</p>
                        </div>
                    </div>

                    <div class="admin-user-field-row">
                        <div class="admin-user-field">
                            <label>Nombre</label>
                            <input v-model="form.nombre" type="text" :class="{ 'admin-user-input-error': form.errors.nombre }" />
                            <p v-if="form.errors.nombre" class="admin-user-error-text">{{ form.errors.nombre }}</p>
                        </div>
                        <div class="admin-user-field">
                            <label><i class="pi pi-at"></i> Usuario</label>
                            <input v-model="form.nickname" type="text" placeholder="Ej. gus_admin" :class="{ 'admin-user-input-error': form.errors.nickname }" />
                            <p v-if="form.errors.nickname" class="admin-user-error-text">{{ form.errors.nickname }}</p>
                        </div>
                    </div>
                    <div class="admin-user-field">
                        <label><i class="pi pi-phone"></i> Teléfono</label>
                        <input v-model="form.telefono" type="text" />
                    </div>

                    <button type="submit" :disabled="form.processing" class="admin-cobros-btn-primary" style="align-self:flex-start">
                        <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-check'"></i>
                        {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                    </button>
                </form>
            </div>

            <p class="admin-user-hint text-center mt-4">
                ¿Buscas cambiar tu correo, contraseña o activar la verificación en dos pasos?
                <a :href="route('admin.seguridad.index')" style="color:var(--brand)" class="font-medium hover:underline">Ve a Seguridad</a>.
            </p>
        </div>
    </AdminLayout>
</template>