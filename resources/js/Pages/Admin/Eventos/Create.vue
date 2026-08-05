<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useToast } from '@/composables/useToast';
import { ref, watch } from 'vue';

const toast = useToast();
const imagenValida = ref(null);

// Estado para la previsualización de imágenes
const imagenPrincipalPreview = ref(null);
const fotosAdicionalesPreview = ref([]);
const imagenPrincipalFile = ref(null);
const fotosAdicionalesFiles = ref([]);

const form = useForm({
    nombre: '',
    descripcion: '',
    fecha: '',
    hora: '',
    ciudad: '',
    zona_ubicacion: '',
    ubicacion_lat: '',
    ubicacion_lng: '',
    precio: 0,
    capacidad: '',
    tipo: 'general',
    categoria: '',
    codigo_vestimenta: '',
    estado: 'borrador',
<<<<<<< HEAD
    imagen: null, // Archivo de imagen principal
    fotos: [], // Array de fotos adicionales
});

// Función para manejar la imagen principal
function handleImagenPrincipal(event) {
    const file = event.target.files[0];
    if (file) {
        // Validar tamaño (2MB)
        if (file.size > 2 * 1024 * 1024) {
            toast.error('La imagen no debe superar los 2MB');
            event.target.value = '';
            return;
        }

        // Validar tipo
        if (!file.type.startsWith('image/')) {
            toast.error('El archivo debe ser una imagen');
            event.target.value = '';
            return;
        }

        imagenPrincipalFile.value = file;
        form.imagen = file;
        
        // Crear URL para previsualización
        const reader = new FileReader();
        reader.onload = (e) => {
            imagenPrincipalPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

// Función para manejar fotos adicionales
function handleFotosAdicionales(event) {
    const files = Array.from(event.target.files);
    const nuevasFotos = [];
    
    // Validar cada archivo
    const filesValidos = files.filter(file => {
        if (file.size > 2 * 1024 * 1024) {
            toast.error(`La imagen "${file.name}" supera los 2MB`);
            return false;
        }
        if (!file.type.startsWith('image/')) {
            toast.error(`El archivo "${file.name}" no es una imagen válida`);
            return false;
        }
        return true;
    });

    if (filesValidos.length === 0) {
        event.target.value = '';
        return;
    }

    // Limitar a 5 fotos adicionales
    const totalFotos = fotosAdicionalesFiles.value.length + filesValidos.length;
    if (totalFotos > 5) {
        toast.error('Solo puedes subir máximo 5 fotos adicionales');
        event.target.value = '';
        return;
    }

    // Procesar archivos válidos
    filesValidos.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            fotosAdicionalesPreview.value.push({
                url: e.target.result,
                nombre: file.name,
                file: file
            });
        };
        reader.readAsDataURL(file);
        
        nuevasFotos.push({
            file: file,
            nombre: file.name
        });
    });

    // Actualizar el array de fotos del formulario
    fotosAdicionalesFiles.value = [...fotosAdicionalesFiles.value, ...filesValidos];
    form.fotos = [...form.fotos, ...nuevasFotos];
    
    // Resetear el input
    event.target.value = '';
}

// Función para eliminar una foto adicional
function eliminarFotoAdicional(index) {
    fotosAdicionalesPreview.value.splice(index, 1);
    fotosAdicionalesFiles.value.splice(index, 1);
    form.fotos.splice(index, 1);
}

// Función para eliminar la imagen principal
function eliminarImagenPrincipal() {
    imagenPrincipalPreview.value = null;
    imagenPrincipalFile.value = null;
    form.imagen = null;
    
    // Resetear el input file
    const input = document.getElementById('imagen_principal');
    if (input) input.value = '';
}

// Función para limpiar fotos adicionales
function limpiarFotosAdicionales() {
    fotosAdicionalesPreview.value = [];
    fotosAdicionalesFiles.value = [];
    form.fotos = [];
    const input = document.getElementById('fotos_adicionales');
    if (input) input.value = '';
}

// Función para verificar si hay campos obligatorios faltantes
function hayCamposFaltantes() {
    const obligatorios = ['nombre', 'fecha', 'hora', 'ciudad', 'tipo', 'estado'];
    return obligatorios.some((c) => !form[c]);
}

// Función para enviar el formulario
function submit() {
    // Validar campos obligatorios
    if (hayCamposFaltantes()) {
=======
    imagen: '',
    destacado: false,
});

function submit() {
    const obligatorios = ['nombre', 'fecha', 'hora', 'ciudad', 'tipo', 'estado'];
    const faltantes = obligatorios.filter((c) => !form[c]);
    if (faltantes.length) {
>>>>>>> Gabriel
        toast.error('Debes llenar todos los campos obligatorios.');
        return;
    }

<<<<<<< HEAD
    // Verificar que el precio sea válido
    if (form.precio < 0) {
        toast.error('El precio no puede ser negativo');
        return;
    }

    // Verificar que el formulario tenga la imagen principal
    if (!form.imagen) {
        toast.warning('Se recomienda agregar una imagen principal para el evento');
        // No bloqueamos el envío, solo avisamos
    }

    // Crear FormData para enviar archivos
    const formData = new FormData();
    
    // Agregar campos normales
    Object.keys(form.data()).forEach(key => {
        if (key !== 'imagen' && key !== 'fotos') {
            formData.append(key, form[key]);
        }
    });

    // Agregar imagen principal
    if (imagenPrincipalFile.value) {
        formData.append('imagen', imagenPrincipalFile.value);
    }

    // Agregar fotos adicionales
    form.fotos.forEach((foto, index) => {
        if (foto.file) {
            formData.append(`fotos[${index}][file]`, foto.file);
            formData.append(`fotos[${index}][nombre]`, foto.nombre || `foto_${index + 1}`);
        }
    });

    // Enviar con FormData
    form.post(route('admin.eventos.store'), {
        data: formData,
        headers: {
            'Content-Type': 'multipart/form-data',
        },
        onSuccess: () => {
            toast.success('Evento creado con éxito.');
        },
        onError: (errors) => {
            const primerError = Object.values(errors)[0];
            toast.error(primerError || 'Ocurrió un error al crear el evento.');
        }
    });
=======
    form.post(route('admin.eventos.store'));
>>>>>>> Gabriel
}

// Resetear formulario si es necesario
function resetForm() {
    form.reset();
    imagenPrincipalPreview.value = null;
    imagenPrincipalFile.value = null;
    fotosAdicionalesPreview.value = [];
    fotosAdicionalesFiles.value = [];
    form.fotos = [];
}
</script>

<template>
    <Head title="Nuevo Evento" />

    <AdminLayout>
        <template #title>Nuevo evento</template>
        <template #breadcrumb>Dashboard &gt; Eventos &gt; Nuevo evento</template>

        <div class="max-w-3xl mx-auto">
            <Link :href="route('admin.eventos.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand mb-4">
                <i class="pi pi-arrow-left text-xs"></i> Volver a Eventos
            </Link>

<<<<<<< HEAD
        <form @submit.prevent="submit" class="max-w-4xl bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <!-- Datos del evento -->
=======
            <form @submit.prevent="submit" class="admin-card overflow-hidden">
                <div style="height:6px;background:linear-gradient(90deg,#ef4444,#f97316)"></div>

            <!-- Sección 1: Datos del evento -->
>>>>>>> Gabriel
            <div class="p-6">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                    <div class="admin-icon-gradient" style="width:48px;height:48px">
                        <i class="pi pi-calendar text-lg"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-gray-800">Datos del evento</h2>
                        <p class="text-xs text-gray-400">Información principal</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre del evento *</label>
                        <input v-model="form.nombre" type="text" placeholder="Ej. Noche de Fantasías" class="admin-input px-3 py-2.5" />
                        <p v-if="form.errors.nombre" class="text-red-600 text-xs mt-1">{{ form.errors.nombre }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Descripción</label>
                        <textarea v-model="form.descripcion" rows="3" placeholder="Describe el evento..." class="admin-input px-3 py-2.5 resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Fecha *</label>
                            <input v-model="form.fecha" type="date" class="admin-input px-3 py-2.5" />
                            <p v-if="form.errors.fecha" class="text-red-600 text-xs mt-1">{{ form.errors.fecha }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Hora *</label>
                            <input v-model="form.hora" type="time" class="admin-input px-3 py-2.5" />
                            <p v-if="form.errors.hora" class="text-red-600 text-xs mt-1">{{ form.errors.hora }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección 2: Ubicación -->
            <div class="p-6 bg-gray-50/50 border-t border-gray-100">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                    <div class="admin-icon-gradient" style="width:48px;height:48px">
                        <i class="pi pi-map-marker text-lg"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-gray-800">Ubicación</h2>
                        <p class="text-xs text-gray-400">Dónde se llevará a cabo</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Ciudad *</label>
                            <input v-model="form.ciudad" type="text" placeholder="Ej. Ciudad de México" class="admin-input px-3 py-2.5" />
                            <p v-if="form.errors.ciudad" class="text-red-600 text-xs mt-1">{{ form.errors.ciudad }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Zona / lugar exacto</label>
                            <input v-model="form.zona_ubicacion" type="text" placeholder="Ej. Polanco" class="admin-input px-3 py-2.5" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Latitud (opcional)</label>
                            <input v-model="form.ubicacion_lat" type="number" step="0.00000001" placeholder="Ej. 19.4326" class="admin-input px-3 py-2.5" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Longitud (opcional)</label>
                            <input v-model="form.ubicacion_lng" type="number" step="0.00000001" placeholder="Ej. -99.1332" class="admin-input px-3 py-2.5" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-400">Para ubicar el evento en un mapa. Puedes dejarlo en blanco por ahora.</p>
                </div>
            </div>

            <!-- Sección 3: Precio y capacidad -->
            <div class="p-6 border-t border-gray-100">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                    <div class="admin-icon-gradient" style="width:48px;height:48px">
                        <i class="pi pi-ticket text-lg"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-gray-800">Precio y capacidad</h2>
                        <p class="text-xs text-gray-400">Configura acceso y aforo</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Precio (MXN) *</label>
<<<<<<< HEAD
                        <input v-model.number="form.precio" type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                        <p v-if="form.errors.precio" class="text-red-600 text-xs mt-1">{{ form.errors.precio }}</p>
=======
                        <input v-model.number="form.precio" type="number" min="0" step="0.01" class="admin-input px-3 py-2.5" />
>>>>>>> Gabriel
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Capacidad (opcional)</label>
                        <input v-model.number="form.capacidad" type="number" min="1" placeholder="Ilimitado" class="admin-input px-3 py-2.5" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipo *</label>
                        <select v-model="form.tipo" class="admin-input px-3 py-2.5">
                            <option value="general">General</option>
                            <option value="vip">VIP</option>
                        </select>
                        <p v-if="form.errors.tipo" class="text-red-600 text-xs mt-1">{{ form.errors.tipo }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Estado *</label>
                        <select v-model="form.estado" class="admin-input px-3 py-2.5">
                            <option value="borrador">Borrador</option>
                            <option value="publicado">Publicado</option>
                            <option value="cancelado">Cancelado</option>
                            <option value="completo">Completado</option>
                        </select>
<<<<<<< HEAD
                        <p v-if="form.errors.estado" class="text-red-600 text-xs mt-1">{{ form.errors.estado }}</p>
=======
                        <p class="text-xs text-gray-400 mt-1">Cambia a "Completado" manualmente cuando el evento termine.</p>
>>>>>>> Gabriel
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Categoría</label>
                        <input v-model="form.categoria" type="text" placeholder="Ej. Fiesta temática" class="admin-input px-3 py-2.5" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Código de vestimenta</label>
                        <input v-model="form.codigo_vestimenta" type="text" placeholder="Ej. Elegante / Antifaz" class="admin-input px-3 py-2.5" />
                    </div>
                </div>
            </div>

<<<<<<< HEAD
            <!-- Imagen Principal -->
            <div class="p-6 border-t border-gray-100">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                    <div class="rounded-xl bg-gradient-to-br from-brand to-brand-dark text-white flex items-center justify-center shrink-0" style="width:48px;height:48px">
                        <i class="pi pi-image text-lg"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-gray-800">Imagen principal</h2>
                        <p class="text-xs text-gray-400">Imagen destacada del evento (recomendado 1200x800px)</p>
                    </div>
                </div>

                <div class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl p-6 hover:border-brand transition-colors" :class="{'border-brand': imagenPrincipalPreview}">
                    <div v-if="imagenPrincipalPreview" class="relative w-full max-w-md mx-auto">
                        <img :src="imagenPrincipalPreview" alt="Imagen principal" class="w-full h-auto rounded-lg object-cover max-h-64" />
                        <button 
                            type="button"
                            @click="eliminarImagenPrincipal"
                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg"
                        >
                            <i class="pi pi-times"></i>
                        </button>
                    </div>
                    <div v-else class="text-center py-8">
                        <i class="pi pi-cloud-upload text-5xl text-gray-300"></i>
                        <p class="mt-4 text-sm text-gray-500">Arrastra y suelta una imagen o haz clic para seleccionar</p>
                        <p class="text-xs text-gray-400 mt-1">PNG, JPG, WEBP (máx. 2MB)</p>
                        <input 
                            id="imagen_principal"
                            type="file"
                            accept="image/*"
                            @change="handleImagenPrincipal"
                            class="hidden"
                        />
                        <button 
                            type="button"
                            @click="document.getElementById('imagen_principal').click()"
                            class="mt-4 bg-brand text-white px-6 py-2 rounded-lg text-sm hover:bg-brand-dark transition-colors"
                        >
                            Seleccionar imagen
                        </button>
                    </div>
                    <p v-if="form.errors.imagen" class="text-red-600 text-xs mt-2">{{ form.errors.imagen }}</p>
                </div>
            </div>

            <!-- Fotos adicionales -->
            <div class="p-6 border-t border-gray-100">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                    <div class="rounded-xl bg-gradient-to-br from-brand to-brand-dark text-white flex items-center justify-center shrink-0" style="width:48px;height:48px">
                        <i class="pi pi-images text-lg"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-gray-800">Fotos adicionales</h2>
                        <p class="text-xs text-gray-400">Agrega hasta 5 fotos más (máx. 2MB cada una)</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mb-4">
                    <div v-for="(foto, index) in fotosAdicionalesPreview" :key="index" class="relative group">
                        <img :src="foto.url" :alt="foto.nombre" class="w-full h-32 object-cover rounded-lg border border-gray-200" />
                        <button 
                            type="button"
                            @click="eliminarFotoAdicional(index)"
                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors opacity-0 group-hover:opacity-100"
                        >
                            <i class="pi pi-times text-xs"></i>
                        </button>
                        <p class="text-xs text-gray-500 mt-1 truncate">{{ foto.nombre }}</p>
                    </div>
                    <div v-if="fotosAdicionalesPreview.length < 5" class="border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center h-32 hover:border-brand transition-colors cursor-pointer">
                        <div class="text-center">
                            <i class="pi pi-plus text-2xl text-gray-400"></i>
                            <p class="text-xs text-gray-500 mt-1">Agregar foto</p>
                            <input 
                                id="fotos_adicionales"
                                type="file"
                                accept="image/*"
                                multiple
                                @change="handleFotosAdicionales"
                                class="hidden"
                            />
                            <button 
                                type="button"
                                @click="document.getElementById('fotos_adicionales').click()"
                                class="mt-2 text-xs text-brand hover:underline"
                            >
                                Seleccionar
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="fotosAdicionalesPreview.length > 0" class="flex justify-end">
                    <button 
                        type="button"
                        @click="limpiarFotosAdicionales"
                        class="text-sm text-red-500 hover:text-red-600"
                    >
                        <i class="pi pi-trash mr-1"></i> Limpiar todas
                    </button>
                </div>
                <p v-if="form.errors.fotos" class="text-red-600 text-xs mt-2">{{ form.errors.fotos }}</p>
=======
            <!-- Sección 4: Presentación -->
            <div class="p-6 bg-gray-50/50 border-t border-gray-100">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                    <div class="admin-icon-gradient" style="width:48px;height:48px">
                        <i class="pi pi-image text-lg"></i>
                    </div>
                    <div>
                        <h2 class="font-semibold text-gray-800">Presentación</h2>
                        <p class="text-xs text-gray-400">Cómo se verá en la plataforma</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Imagen (URL)</label>
                        <input v-model="form.imagen" type="text" placeholder="https://..." class="admin-input px-3 py-2.5" />
                        <p class="text-xs text-gray-400 mt-1">
                            Debe ser el link directo al archivo (termina en .jpg, .png, .webp...), no a una página de producto.
                        </p>

                        <!-- Vista previa en vivo -->
                        <div v-if="form.imagen" class="mt-3">
                            <div class="w-full rounded-xl overflow-hidden border border-gray-200 bg-gray-50" style="height:160px">
                                <img
                                    :src="form.imagen"
                                    class="w-full h-full object-cover"
                                    @load="imagenValida = true"
                                    @error="imagenValida = false"
                                />
                            </div>
                            <p v-if="imagenValida === false" class="text-red-600 text-xs mt-1.5 flex items-center gap-1">
                                <i class="pi pi-exclamation-triangle"></i>
                                Esta URL no cargó una imagen. Revisa que sea el link directo al archivo.
                            </p>
                            <p v-else-if="imagenValida === true" class="text-green-600 text-xs mt-1.5 flex items-center gap-1">
                                <i class="pi pi-check-circle"></i> Se ve bien.
                            </p>
                        </div>
                    </div>

                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input v-model="form.destacado" type="checkbox" class="rounded border-gray-300 text-brand focus:ring-brand w-4 h-4" />
                        <span class="text-sm text-gray-700">Marcar como evento destacado</span>
                    </label>
                </div>
>>>>>>> Gabriel
            </div>

            <!-- Acciones -->
            <div class="p-6 border-t border-gray-100 flex items-center gap-3">
                <button type="submit" :disabled="form.processing" class="admin-btn-primary disabled:opacity-50">
                    <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-check'"></i>
                    {{ form.processing ? 'Guardando...' : 'Crear evento' }}
                </button>
                <Link :href="route('admin.eventos.index')" class="admin-btn-secondary">
                    Cancelar
                </Link>
            </div>
        </form>
        </div>
    </AdminLayout>
</template>

<style scoped>
input[type="file"] {
    cursor: pointer;
}

img {
    pointer-events: none;
}
</style>