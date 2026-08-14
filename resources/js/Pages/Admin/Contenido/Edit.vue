<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useToast } from '@/composables/useToast';
import { useContenidoMeta } from '@/composables/useContenidoMeta';

const toast = useToast();
const { tipoIcono } = useContenidoMeta();

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
    // Archivos que ya están guardados en storage (URLs que manda el backend)
    archivos_existentes: props.contenido.archivos?.length ? [...props.contenido.archivos] : [],
    // Archivos nuevos que se van a subir en esta edición
    archivos_nuevos: [],
    etiquetas: props.contenido.etiquetas?.length ? props.contenido.etiquetas.join(', ') : '',
    programado_en: paraDatetimeLocal(props.contenido.programado_en),
});

// Slots de "nuevo archivo" en el formulario (File | null) y su preview local
const nuevosSlots = ref([null]);
const previews = ref([null]);

const acceptPorTipo = {
    video: 'video/*',
    audio: 'audio/*',
    foto: 'image/*',
    galeria: 'image/*',
    documento: '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt',
    articulo: '.pdf,.doc,.docx,.txt',
    exclusivo: '*',
};
const acceptActual = computed(() => acceptPorTipo[form.tipo] || '*');

function quitarExistente(i) {
    form.archivos_existentes.splice(i, 1);
}

function agregarSlotNuevo() {
    nuevosSlots.value.push(null);
    previews.value.push(null);
}

function quitarSlotNuevo(i) {
    if (previews.value[i]) URL.revokeObjectURL(previews.value[i]);
    nuevosSlots.value.splice(i, 1);
    previews.value.splice(i, 1);
    if (!nuevosSlots.value.length) {
        nuevosSlots.value.push(null);
        previews.value.push(null);
    }
}

function onFileChange(i, event) {
    const file = event.target.files?.[0] || null;
    if (previews.value[i]) URL.revokeObjectURL(previews.value[i]);
    nuevosSlots.value[i] = file;
    previews.value[i] = file ? URL.createObjectURL(file) : null;
    event.target.value = '';
}

function esImagen(file) { return file && file.type.startsWith('image/'); }
function esVideo(file) { return file && file.type.startsWith('video/'); }
function esAudio(file) { return file && file.type.startsWith('audio/'); }
function esArchivoImagen(url) { return /\.(jpe?g|png|gif|webp|avif)$/i.test(url || ''); }
function esArchivoVideo(url) { return /\.(mp4|webm|mov|ogg)$/i.test(url || ''); }
function esArchivoAudio(url) { return /\.(mp3|wav|ogg|m4a)$/i.test(url || ''); }
function nombreArchivo(url) { return (url || '').split('/').pop(); }

function formatoTamano(bytes) {
    if (!bytes) return '';
    const kb = bytes / 1024;
    if (kb < 1024) return `${kb.toFixed(0)} KB`;
    return `${(kb / 1024).toFixed(1)} MB`;
}

const nuevosCargados = computed(() => nuevosSlots.value.filter(Boolean));
const totalArchivos = computed(() => form.archivos_existentes.length + nuevosCargados.value.length);

function submit() {
    if (!form.titulo || !form.tipo) {
        toast.error('Debes llenar todos los campos obligatorios.');
        return;
    }
    if (!totalArchivos.value) {
        toast.error('Agrega al menos un archivo.');
        return;
    }
    if (form.tipo === 'galeria' && totalArchivos.value < 3) {
        toast.error('Una galería debe tener al menos 3 fotos.');
        return;
    }
    if (form.estado === 'programado' && !form.programado_en) {
        toast.error('Selecciona la fecha y hora de publicación programada.');
        return;
    }

    form.transform((data) => ({
        ...data,
        archivos_nuevos: nuevosCargados.value,
        etiquetas: data.etiquetas
            ? data.etiquetas.split(',').map((t) => t.trim()).filter(Boolean)
            : [],
        programado_en: data.estado === 'programado' ? data.programado_en : null,
        _method: 'patch',
    })).post(route('admin.contenido.update', props.contenido.id), {
        forceFormData: true,
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
                            <i class="pi text-lg" :class="tipoIcono[form.tipo] || 'pi-video'"></i>
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

                            <!-- Archivos ya guardados -->
                            <div v-if="form.archivos_existentes.length" class="mb-4">
                                <p class="text-xs font-medium text-gray-500 mb-2">Ya guardados</p>
                                <div class="space-y-3">
                                    <div v-for="(archivo, i) in form.archivos_existentes" :key="archivo" class="flex items-center gap-2">
                                        <div v-if="esArchivoImagen(archivo)" class="rounded-lg overflow-hidden border border-gray-200 bg-gray-50 shrink-0" style="width:56px;height:56px">
                                            <img :src="archivo" class="w-full h-full object-cover" />
                                        </div>
                                        <div v-else class="rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center text-gray-400 shrink-0" style="width:56px;height:56px">
                                            <i class="pi" :class="esArchivoVideo(archivo) ? 'pi-video' : esArchivoAudio(archivo) ? 'pi-volume-up' : 'pi-file'"></i>
                                        </div>
                                        <span class="text-xs text-gray-500 truncate flex-1">{{ nombreArchivo(archivo) }}</span>
                                        <button type="button" @click="quitarExistente(i)" title="Quitar"
                                            class="shrink-0 rounded-lg border border-gray-200 text-red-500 hover:bg-red-50 flex items-center justify-center"
                                            style="width:36px;height:36px">
                                            <i class="pi pi-trash text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Agregar archivos nuevos -->
                            <p class="text-xs font-medium text-gray-500 mb-2">Agregar nuevos</p>
                            <div class="space-y-4">
                                <div v-for="(archivo, i) in nuevosSlots" :key="i">
                                    <div class="flex items-center gap-2">
                                        <label class="admin-input px-3 py-2.5 flex items-center gap-2 cursor-pointer text-gray-500 hover:border-brand transition">
                                            <i class="pi pi-upload text-xs shrink-0"></i>
                                            <span class="truncate text-sm">{{ archivo ? archivo.name : 'Seleccionar archivo...' }}</span>
                                            <input type="file" class="hidden" :accept="acceptActual" @change="onFileChange(i, $event)" />
                                        </label>
                                        <button type="button" @click="quitarSlotNuevo(i)" title="Quitar"
                                            class="shrink-0 rounded-lg border border-gray-200 text-red-500 hover:bg-red-50 flex items-center justify-center"
                                            style="width:36px;height:36px">
                                            <i class="pi pi-trash text-xs"></i>
                                        </button>
                                    </div>

                                    <!-- Vista previa del archivo nuevo -->
                                    <div v-if="archivo" class="mt-2">
                                        <div v-if="esImagen(archivo)"
                                            class="w-full max-w-xs rounded-xl overflow-hidden border border-gray-200 bg-gray-50" style="height:130px">
                                            <img :src="previews[i]" class="w-full h-full object-cover" />
                                        </div>
                                        <video v-else-if="esVideo(archivo)" :src="previews[i]" controls
                                            class="w-full max-w-xs rounded-xl border border-gray-200 bg-gray-50" style="height:170px"></video>
                                        <audio v-else-if="esAudio(archivo)" :src="previews[i]" controls class="w-full max-w-sm"></audio>
                                        <div v-else class="flex items-center gap-2 text-xs text-gray-500 border border-gray-200 rounded-lg px-3 py-2 bg-gray-50 max-w-xs">
                                            <i class="pi pi-file text-brand shrink-0"></i>
                                            <span class="truncate">{{ archivo.name }}</span>
                                        </div>

                                        <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
                                            <i class="pi pi-check-circle text-green-600"></i> Listo para subir ({{ formatoTamano(archivo.size) }}).
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <button type="button" @click="agregarSlotNuevo" class="mt-2 text-xs font-semibold text-brand hover:underline">
                                <i class="pi pi-plus text-[10px] mr-1"></i>Agregar otro archivo
                            </button>
                            <p v-if="form.errors.archivos_nuevos" class="text-red-600 text-xs mt-1">{{ form.errors.archivos_nuevos }}</p>
                            <p v-if="form.tipo === 'galeria'" class="text-xs mt-1" :class="totalArchivos >= 3 ? 'text-green-600' : 'text-amber-500'">
                                <i class="pi" :class="totalArchivos >= 3 ? 'pi-check-circle' : 'pi-info-circle'"></i>
                                Una galería necesita mínimo 3 fotos (llevas {{ totalArchivos }}).
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
                    <button type="submit" :disabled="form.processing" class="admin-btn-primary disabled:opacity-50">
                        <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-check'"></i>
                        {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                    </button>
                    <Link :href="route('admin.contenido.index')" class="admin-btn-secondary">
                        Cancelar
                    </Link>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>