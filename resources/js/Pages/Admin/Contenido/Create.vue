<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    tipoPreseleccionado: String,
});

const form = useForm({
    titulo: '',
    categoria: '',
    descripcion: '',
    tipo: props.tipoPreseleccionado || 'video',
    visibilidad: 'publico',
    estado: 'borrador',
    precio: 0,
    es_premium: false,
    url_archivo: '',
});

function submit() {
    if (!form.titulo || !form.tipo) {
        toast.error('Debes llenar todos los campos obligatorios.');
        return;
    }

    form.post(route('admin.contenido.store'), {
        onSuccess: () => {
            toast.success('Contenido creado correctamente.');
        },
        onError: (errors) => {
            const primerError = Object.values(errors)[0];
            toast.error(primerError || 'Revisa los datos del formulario.');
        }
    });
}
</script>

<template>
    <Head title="Nuevo Contenido" />

    <AdminLayout>
        <template #title>Nuevo contenido</template>
        <template #breadcrumb>Dashboard &gt; Contenido &gt; Nuevo contenido</template>

        <Link :href="route('admin.contenido.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand mb-4">
            <i class="pi pi-arrow-left text-xs"></i> Volver a Contenido
        </Link>

        <form @submit.prevent="submit" class="max-w-3xl bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                    <div class="rounded-xl bg-gradient-to-br from-brand to-brand-dark text-white flex items-center justify-center shrink-0" style="width:48px;height:48px">
                        <i class="pi pi-video text-lg"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-gray-800">Datos del contenido</h2>
                        <p class="text-xs text-gray-400">Información principal</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Título *</label>
                        <input v-model="form.titulo" type="text" placeholder="Ej. Bienvenida al Club" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                        <p v-if="form.errors.titulo" class="text-red-600 text-xs mt-1">{{ form.errors.titulo }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Descripción</label>
                        <textarea v-model="form.descripcion" rows="3" placeholder="Describe el contenido..." class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipo *</label>
                            <select v-model="form.tipo" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                                <option value="video">Video</option>
                                <option value="articulo">Artículo</option>
                                <option value="galeria">Galería</option>
                                <option value="audio">Audio</option>
                                <option value="documento">Documento</option>
                                <option value="foto">Foto</option>
                                <option value="exclusivo">Exclusivo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Categoría</label>
                            <input v-model="form.categoria" type="text" placeholder="Ej. Información, Normas..." class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">URL del archivo (imagen, video, etc.)</label>
                        <input v-model="form.url_archivo" type="text" placeholder="https://..." class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                        <p class="text-xs text-gray-400 mt-1">La subida de archivos directa se agregará más adelante; por ahora usa una URL.</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-gray-50/50 border-t border-gray-100">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                    <div class="rounded-xl bg-gradient-to-br from-brand to-brand-dark text-white flex items-center justify-center shrink-0" style="width:48px;height:48px">
                        <i class="pi pi-lock text-lg"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-gray-800">Visibilidad y publicación</h2>
                        <p class="text-xs text-gray-400">Define quién puede verlo y cuándo</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Visibilidad *</label>
                        <select v-model="form.visibilidad" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                            <option value="publico">Público</option>
                            <option value="suscriptores">Solo suscriptores</option>
                            <option value="individual">Compra individual</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Estado *</label>
                        <select v-model="form.estado" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                            <option value="borrador">Borrador</option>
                            <option value="publicado">Publicado</option>
                            <option value="programado">Programado</option>
                            <option value="archivado">Archivado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Precio (MXN)</label>
                        <input v-model.number="form.precio" type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                        <p class="text-xs text-gray-400 mt-1">Deja en 0 si es gratuito.</p>
                    </div>
                    <div class="flex items-center gap-2 pt-6">
                        <input v-model="form.es_premium" type="checkbox" id="premium" class="rounded border-gray-300 text-brand focus:ring-brand" />
                        <label for="premium" class="text-sm text-gray-700">Marcar como contenido Premium</label>
                    </div>
                </div>
            </div>

            <div class="p-6 border-t border-gray-100 flex items-center gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-brand hover:bg-brand-dark text-white font-medium px-6 py-2.5 rounded-lg text-sm disabled:opacity-50 flex items-center gap-2"
                >
                    <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-check'"></i>
                    {{ form.processing ? 'Guardando...' : 'Crear contenido' }}
                </button>
                <Link :href="route('admin.contenido.index')" class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5">
                    Cancelar
                </Link>
            </div>
        </form>
    </AdminLayout>
</template>