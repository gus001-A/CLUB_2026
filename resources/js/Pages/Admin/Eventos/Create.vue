<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { ref } from 'vue';

const toast = useToast();

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
    imagen: null, // File
    destacado: false,
});

const preview = ref(null);

function onFileChange(event) {
    const file = event.target.files?.[0] || null;
    if (preview.value) URL.revokeObjectURL(preview.value);
    form.imagen = file;
    preview.value = file ? URL.createObjectURL(file) : null;
    event.target.value = '';
}

function quitarImagen() {
    if (preview.value) URL.revokeObjectURL(preview.value);
    form.imagen = null;
    preview.value = null;
}

function submit() {
    const obligatorios = ['nombre', 'fecha', 'hora', 'ciudad', 'tipo', 'estado'];
    const faltantes = obligatorios.filter((c) => !form[c]);
    if (faltantes.length) {
        toast.error('Debes llenar todos los campos obligatorios.');
        return;
    }

    form.post(route('admin.eventos.store'), {
        forceFormData: true,
        onSuccess: () => toast.success('Evento creado correctamente.'),
        onError: () => toast.error('Revisa los datos del formulario.'),
    });
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

            <form @submit.prevent="submit" class="admin-card overflow-hidden">
                <div style="height:6px;background:linear-gradient(90deg,#ef4444,#f97316)"></div>

            <!-- Sección 1: Datos del evento -->
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
                        <input v-model.number="form.precio" type="number" min="0" step="0.01" class="admin-input px-3 py-2.5" />
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
                        <p class="text-xs text-gray-400 mt-1">Cambia a "Completado" manualmente cuando el evento termine.</p>
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

                <div class="space-y-4 admin-file-input">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Imagen</label>
                        <div class="flex items-center gap-2">
                            <label class="admin-input px-3 py-2.5 flex items-center gap-2 cursor-pointer text-gray-500 hover:border-brand transition">
                                <i class="pi pi-upload text-xs shrink-0"></i>
                                <span class="truncate text-sm">{{ form.imagen ? form.imagen.name : 'Seleccionar imagen...' }}</span>
                                <input type="file" class="hidden" accept="image/*" @change="onFileChange" />
                            </label>
                            <button v-if="form.imagen" type="button" @click="quitarImagen" title="Quitar"
                                class="shrink-0 rounded-lg border border-gray-200 text-red-500 hover:bg-red-50 flex items-center justify-center"
                                style="width:36px;height:36px">
                                <i class="pi pi-trash text-xs"></i>
                            </button>
                        </div>
                        <p v-if="form.errors.imagen" class="text-red-600 text-xs mt-1">{{ form.errors.imagen }}</p>

                        <!-- Vista previa -->
                        <div v-if="preview" class="mt-3">
                            <div class="w-full rounded-xl overflow-hidden border border-gray-200 bg-gray-50" style="height:160px">
                                <img :src="preview" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>

                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input v-model="form.destacado" type="checkbox" class="rounded border-gray-300 text-brand focus:ring-brand w-4 h-4" />
                        <span class="text-sm text-gray-700">Marcar como evento destacado</span>
                    </label>
                </div>
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