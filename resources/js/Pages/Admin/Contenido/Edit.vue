<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    contenido: Object,
});

// programado_en llega como "2026-08-10 20:00:00" (o null) — el input
// datetime-local necesita "2026-08-10T20:00".
function paraDatetimeLocal(v) {
    if (!v) return '';
    return v.replace(' ', 'T').slice(0, 16);
}

const form = useForm({
    titulo: props.contenido.titulo || '',
    categoria: props.contenido.categoria || '',
    descripcion: props.contenido.descripcion || '',
    tipo: props.contenido.tipo,
    visibilidad: props.contenido.visibilidad,
    estado: props.contenido.estado,
    precio: props.contenido.precio || 0,
    es_premium: !!props.contenido.es_premium,
    archivos: props.contenido.archivos?.length ? [...props.contenido.archivos] : [''],
    etiquetas: props.contenido.etiquetas?.length ? props.contenido.etiquetas.join(', ') : '',
    programado_en: paraDatetimeLocal(props.contenido.programado_en),
});

// Estado de validez por archivo (null = sin probar, true/false = cargó o no).
// Precargados en 'true' porque ya vienen de un contenido existente.
const archivosValidos = ref(form.archivos.map((a) => (a ? true : null)));

function agregarArchivo() {
    form.archivos.push('');
    archivosValidos.value.push(null);
}

function quitarArchivo(i) {
    form.archivos.splice(i, 1);
    archivosValidos.value.splice(i, 1);
    if (!form.archivos.length) {
        form.archivos.push('');
        archivosValidos.value.push(null);
    }
}

function submit() {
    const archivosLimpios = form.archivos.map((a) => a.trim()).filter(Boolean);

    if (!form.titulo || !form.tipo) {
        toast.error('Debes llenar todos los campos obligatorios.');
        return;
    }
    if (!archivosLimpios.length) {
        toast.error('Agrega al menos un archivo.');
        return;
    }
    if (form.tipo === 'galeria' && archivosLimpios.length < 3) {
        toast.error('Una galería debe tener al menos 3 fotos.');
        return;
    }
    if (form.estado === 'programado' && !form.programado_en) {
        toast.error('Selecciona la fecha y hora de publicación programada.');
        return;
    }

    form.transform((data) => ({
        ...data,
        archivos: archivosLimpios,
        etiquetas: data.etiquetas
            ? data.etiquetas.split(',').map((t) => t.trim()).filter(Boolean)
            : [],
        programado_en: data.estado === 'programado' ? data.programado_en : null,
    })).patch(route('admin.contenido.update', props.contenido.id), {
        onSuccess: () => {
            toast.success('Contenido actualizado correctamente.');
        },
        onError: (errors) => {
            const primerError = Object.values(errors)[0];
            toast.error(primerError || 'Revisa los datos del formulario.');
        }
    });
}
</script>

<template>
    <Head title="Editar Contenido" />

    <AdminLayout>
        <template #title>Editar contenido</template>
        <template #breadcrumb>Dashboard &gt; Contenido &gt; Editar</template>

        <div class="max-w-3xl mx-auto">
            <Link :href="route('admin.contenido.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand mb-4">
                <i class="pi pi-arrow-left text-xs"></i> Volver a Contenido
            </Link>

            <form @submit.prevent="submit" class="admin-card overflow-hidden">
                <div style="height:6px;background:linear-gradient(90deg,#C81E3A,#E85C74)"></div>
                <div class="p-6">
                    <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                        <div class="admin-icon-gradient shrink-0" style="width:48px;height:48px">
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
                            <input v-model="form.titulo" type="text" placeholder="Ej. Bienvenida al Club" class="admin-input px-3 py-2.5" />
                            <p v-if="form.errors.titulo" class="text-red-600 text-xs mt-1">{{ form.errors.titulo }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Descripción</label>
                            <textarea v-model="form.descripcion" rows="3" placeholder="Describe el contenido..." class="admin-input px-3 py-2.5 resize-none"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipo *</label>
                                <select v-model="form.tipo" class="admin-input px-3 py-2.5">
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
                                <input v-model="form.categoria" type="text" placeholder="Ej. Información, Normas..." class="admin-input px-3 py-2.5" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Archivos *</label>
                            <div class="space-y-4">
                                <div v-for="(archivo, i) in form.archivos" :key="i">
                                    <div class="flex items-center gap-2">
                                        <input v-model="form.archivos[i]" type="text" placeholder="https://..."
                                            class="admin-input px-3 py-2.5" />
                                        <button type="button" @click="quitarArchivo(i)" title="Quitar"
                                            class="shrink-0 rounded-lg border border-gray-200 text-red-500 hover:bg-red-50 flex items-center justify-center"
                                            style="width:36px;height:36px">
                                            <i class="pi pi-trash text-xs"></i>
                                        </button>
                                    </div>

                                    <!-- Vista previa según el tipo de contenido -->
                                    <div v-if="archivo" class="mt-2">
                                        <div v-if="form.tipo === 'foto' || form.tipo === 'galeria'"
                                            class="w-full max-w-xs rounded-xl overflow-hidden border border-gray-200 bg-gray-50" style="height:130px">
                                            <img :src="archivo" class="w-full h-full object-cover"
                                                @load="archivosValidos[i] = true" @error="archivosValidos[i] = false" />
                                        </div>
                                        <video v-else-if="form.tipo === 'video'" :src="archivo" controls
                                            class="w-full max-w-xs rounded-xl border border-gray-200 bg-gray-50" style="height:170px"
                                            @loadeddata="archivosValidos[i] = true" @error="archivosValidos[i] = false"></video>
                                        <audio v-else-if="form.tipo === 'audio'" :src="archivo" controls class="w-full max-w-sm"
                                            @loadeddata="archivosValidos[i] = true" @error="archivosValidos[i] = false"></audio>
                                        <div v-else class="flex items-center gap-2 text-xs text-gray-500 border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 max-w-xs">
                                            <i class="pi pi-link text-brand shrink-0"></i>
                                            <span class="truncate">{{ archivo }}</span>
                                        </div>

                                        <p v-if="archivosValidos[i] === false" class="text-red-600 text-xs mt-1.5 flex items-center gap-1">
                                            <i class="pi pi-exclamation-triangle"></i> No se pudo cargar. Revisa que sea el link directo al archivo.
                                        </p>
                                        <p v-else-if="archivosValidos[i] === true" class="text-green-600 text-xs mt-1.5 flex items-center gap-1">
                                            <i class="pi pi-check-circle"></i> Se ve bien.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <button type="button" @click="agregarArchivo" class="mt-2 text-xs font-semibold text-brand hover:underline">
                                <i class="pi pi-plus text-[10px] mr-1"></i>Agregar otro archivo
                            </button>
                            <p v-if="form.errors.archivos" class="text-red-600 text-xs mt-1">{{ form.errors.archivos }}</p>
                            <p v-if="form.tipo === 'galeria'" class="text-xs mt-1" :class="form.archivos.filter(a => a.trim()).length >= 3 ? 'text-green-600' : 'text-amber-500'">
                                <i class="pi" :class="form.archivos.filter(a => a.trim()).length >= 3 ? 'pi-check-circle' : 'pi-info-circle'"></i>
                                Una galería necesita mínimo 3 fotos (llevas {{ form.archivos.filter(a => a.trim()).length }}).
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Etiquetas</label>
                            <input v-model="form.etiquetas" type="text" placeholder="Ej. bienvenida, normas, club"
                                class="admin-input px-3 py-2.5" />
                            <p class="text-xs text-gray-400 mt-1">Sepáralas por comas.</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-gray-50/50 border-t border-gray-100">
                    <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                        <div class="admin-icon-gradient shrink-0" style="width:48px;height:48px">
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
                            <select v-model="form.visibilidad" class="admin-input px-3 py-2.5">
                                <option value="publico">Público</option>
                                <option value="suscriptores">Solo suscriptores</option>
                                <option value="individual">Compra individual</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Estado *</label>
                            <select v-model="form.estado" class="admin-input px-3 py-2.5">
                                <option value="borrador">Borrador</option>
                                <option value="publicado">Publicado</option>
                                <option value="programado">Programado</option>
                                <option value="archivado">Archivado</option>
                            </select>
                        </div>
                        <div v-if="form.estado === 'programado'">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Fecha y hora de publicación *</label>
                            <input v-model="form.programado_en" type="datetime-local" class="admin-input px-3 py-2.5" />
                            <p v-if="form.errors.programado_en" class="text-red-600 text-xs mt-1">{{ form.errors.programado_en }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Precio (MXN)</label>
                            <input v-model.number="form.precio" type="number" min="0" step="0.01" class="admin-input px-3 py-2.5" />
                            <p class="text-xs text-gray-400 mt-1">Deja en 0 si es gratuito.</p>
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input v-model="form.es_premium" type="checkbox" id="premium" class="rounded border-gray-300 text-brand focus:ring-brand" />
                            <label for="premium" class="text-sm text-gray-700">Marcar como contenido Premium</label>
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-100 flex items-center gap-3">
                    <button type="submit" :disabled="form.processing" class="bg-brand hover:bg-brand-dark text-white font-medium px-6 py-2.5 rounded-lg text-sm disabled:opacity-50 flex items-center gap-2">
                        <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-check'"></i>
                        {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                    </button>
                    <Link :href="route('admin.contenido.index')" class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5">
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>