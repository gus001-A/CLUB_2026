<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const props = defineProps({
    evento: Object,
});

const form = useForm({
    nombre: props.evento.nombre,
    descripcion: props.evento.descripcion || '',
    fecha: props.evento.fecha?.slice(0, 10) || '',
    hora: props.evento.hora?.length > 5 ? props.evento.hora.slice(0, 5) : props.evento.hora || '',
    ciudad: props.evento.ciudad || '',
    zona_ubicacion: props.evento.zona_ubicacion || '',
    ubicacion_lat: props.evento.ubicacion_lat ?? '',
    ubicacion_lng: props.evento.ubicacion_lng ?? '',
    precio: props.evento.precio || 0,
    capacidad: props.evento.capacidad || '',
    tipo: props.evento.tipo,
    categoria: props.evento.categoria || '',
    codigo_vestimenta: props.evento.codigo_vestimenta || '',
    estado: props.evento.estado,
    imagen: null, // File nuevo (solo si el admin cambia la imagen)
    eliminar_imagen: false,
    destacado: !!props.evento.destacado,
});

const descripcionMax = 500;

// URL de la imagen ya guardada (viene resuelta desde el backend)
const imagenExistente = ref(props.evento.imagen || null);
const preview = ref(null);

function onFileChange(event) {
    const file = event.target.files?.[0] || null;
    if (preview.value) URL.revokeObjectURL(preview.value);
    form.imagen = file;
    form.eliminar_imagen = false;
    preview.value = file ? URL.createObjectURL(file) : null;
    event.target.value = '';
}

function quitarImagen() {
    if (preview.value) URL.revokeObjectURL(preview.value);
    form.imagen = null;
    preview.value = null;
    imagenExistente.value = null;
    form.eliminar_imagen = true;
}

const precioFormateado = computed(() => {
    const valor = Number(form.precio);
    if (!form.precio || Number.isNaN(valor) || valor <= 0) return null;
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(valor);
});

function submit() {
    const obligatorios = ['nombre', 'fecha', 'hora', 'ciudad', 'tipo', 'estado'];
    const faltantes = obligatorios.filter((c) => !form[c]);
    if (faltantes.length) {
        toast.error('Debes llenar todos los campos obligatorios.');
        return;
    }

    form.transform((data) => ({
        ...data,
        _method: 'patch',
    })).post(route('admin.eventos.update', props.evento.id), {
        forceFormData: true,
        onSuccess: () => toast.success('Evento actualizado correctamente.'),
        onError: () => toast.error('Revisa los datos del formulario.'),
    });
}
</script>

<template>
    <Head title="Editar Evento" />

    <AdminLayout>
        <template #title>Editar evento</template>
        <template #breadcrumb>Dashboard / Eventos / Editar</template>

        <div class="admin-prod-form-page">
            <Link :href="route('admin.eventos.show', evento.id)" class="admin-prod-back-link">
                <i class="pi pi-arrow-left"></i>
                Volver al evento
            </Link>

            <div class="admin-prod-form-grid">
                <!-- COLUMNA IZQUIERDA: FORMULARIO PRINCIPAL -->
                <div class="admin-prod-form">
                    <div class="admin-prod-form-header">
                        <div class="admin-prod-form-header__icon">
                            <i class="pi pi-pencil"></i>
                        </div>
                        <div>
                            <h1>Editar Evento</h1>
                            <p>{{ evento.nombre }}</p>
                        </div>
                    </div>

                    <div class="admin-prod-form-body">
                        <!-- Nombre -->
                        <div class="admin-prod-field">
                            <label>Nombre del evento <span class="admin-prod-required">*</span></label>
                            <input v-model="form.nombre" type="text" placeholder="Ej. Noche de Fantasías"
                                :class="{ 'admin-prod-input-error': form.errors.nombre }" />
                            <p v-if="form.errors.nombre" class="admin-prod-error-text">{{ form.errors.nombre }}</p>
                        </div>

                        <!-- Descripción -->
                        <div class="admin-prod-field">
                            <div class="admin-prod-field-header">
                                <label>Descripción</label>
                                <span class="admin-prod-char-count">{{ form.descripcion.length }}/{{ descripcionMax }}</span>
                            </div>
                            <textarea v-model="form.descripcion" rows="3" :maxlength="descripcionMax"
                                placeholder="Describe el evento..."></textarea>
                        </div>

                        <!-- Fecha y Hora -->
                        <div class="admin-prod-field-row">
                            <div class="admin-prod-field">
                                <label>Fecha <span class="admin-prod-required">*</span></label>
                                <input v-model="form.fecha" type="date"
                                    :class="{ 'admin-prod-input-error': form.errors.fecha }" />
                                <p v-if="form.errors.fecha" class="admin-prod-error-text">{{ form.errors.fecha }}</p>
                            </div>
                            <div class="admin-prod-field">
                                <label>Hora <span class="admin-prod-required">*</span></label>
                                <input v-model="form.hora" type="time"
                                    :class="{ 'admin-prod-input-error': form.errors.hora }" />
                                <p v-if="form.errors.hora" class="admin-prod-error-text">{{ form.errors.hora }}</p>
                            </div>
                        </div>

                        <!-- Ciudad y Zona -->
                        <div class="admin-prod-field-row">
                            <div class="admin-prod-field">
                                <label>Ciudad <span class="admin-prod-required">*</span></label>
                                <input v-model="form.ciudad" type="text" placeholder="Ej. Ciudad de México"
                                    :class="{ 'admin-prod-input-error': form.errors.ciudad }" />
                                <p v-if="form.errors.ciudad" class="admin-prod-error-text">{{ form.errors.ciudad }}</p>
                            </div>
                            <div class="admin-prod-field">
                                <label>Zona / lugar exacto <span class="admin-prod-optional">(opcional)</span></label>
                                <input v-model="form.zona_ubicacion" type="text" placeholder="Ej. Polanco" />
                            </div>
                        </div>

                        <!-- Precio y Capacidad -->
                        <div class="admin-prod-field-row">
                            <div class="admin-prod-field">
                                <label>Precio (MXN) <span class="admin-prod-required">*</span></label>
                                <div class="admin-prod-price-input">
                                    <span class="admin-prod-price-symbol">$</span>
                                    <input v-model="form.precio" type="number" min="0" step="0.01" placeholder="0.00"
                                        :class="{ 'admin-prod-input-error': form.errors.precio }" />
                                    <span v-if="precioFormateado" class="admin-prod-price-format">{{ precioFormateado }}</span>
                                </div>
                                <p v-if="form.errors.precio" class="admin-prod-error-text">{{ form.errors.precio }}</p>
                            </div>
                            <div class="admin-prod-field">
                                <label>Capacidad <span class="admin-prod-optional">(opcional)</span></label>
                                <input v-model="form.capacidad" type="number" min="1" placeholder="Ilimitada" />
                            </div>
                        </div>

                        <!-- Tipo y Estado -->
                        <div class="admin-prod-field-row">
                            <div class="admin-prod-field">
                                <label>Tipo <span class="admin-prod-required">*</span></label>
                                <select v-model="form.tipo">
                                    <option value="general">General</option>
                                    <option value="vip">VIP</option>
                                </select>
                            </div>
                            <div class="admin-prod-field">
                                <label>Estado <span class="admin-prod-required">*</span></label>
                                <select v-model="form.estado">
                                    <option value="borrador">Borrador</option>
                                    <option value="publicado">Publicado</option>
                                    <option value="cancelado">Cancelado</option>
                                    <option value="completo">Completado</option>
                                </select>
                                <p class="admin-prod-hint">Cambia a "Completado" manualmente cuando el evento termine.</p>
                            </div>
                        </div>

                        <!-- Categoría y Código de vestimenta -->
                        <div class="admin-prod-field-row">
                            <div class="admin-prod-field">
                                <label>Categoría <span class="admin-prod-optional">(opcional)</span></label>
                                <input v-model="form.categoria" type="text" placeholder="Ej. Fiesta temática" />
                            </div>
                            <div class="admin-prod-field">
                                <label>Código de vestimenta <span class="admin-prod-optional">(opcional)</span></label>
                                <input v-model="form.codigo_vestimenta" type="text" placeholder="Ej. Elegante / Antifaz" />
                            </div>
                        </div>

                        <!-- Destacado -->
                        <div class="admin-prod-field">
                            <label class="admin-prod-toggle-label">
                                <span class="admin-prod-toggle">
                                    <input v-model="form.destacado" type="checkbox" />
                                    <span class="admin-prod-toggle-slider"></span>
                                </span>
                                <span class="admin-prod-toggle-text">
                                    <i class="pi pi-star"></i> Evento destacado
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: UBICACIÓN + IMAGEN -->
                <div class="admin-prod-sidebar">
                    <!-- Ubicación en mapa -->
                    <div class="admin-prod-sidebar-card">
                        <div class="admin-prod-sidebar-card__header">
                            <h3><i class="pi pi-map-marker"></i> Ubicación en mapa</h3>
                        </div>
                        <div class="admin-prod-sidebar-card__body">
                            <p class="admin-prod-hint">Opcional. Para ubicar el evento en un mapa.</p>
                            <div class="admin-prod-field-row" style="margin-top:0.6rem">
                                <div class="admin-prod-field">
                                    <label>Latitud</label>
                                    <input v-model="form.ubicacion_lat" type="number" step="0.00000001" placeholder="Ej. 19.4326" />
                                </div>
                                <div class="admin-prod-field">
                                    <label>Longitud</label>
                                    <input v-model="form.ubicacion_lng" type="number" step="0.00000001" placeholder="Ej. -99.1332" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Imagen -->
                    <div class="admin-prod-sidebar-card">
                        <div class="admin-prod-sidebar-card__header">
                            <h3><i class="pi pi-image"></i> Imagen</h3>
                            <span class="admin-prod-count">{{ (imagenExistente || preview) ? 1 : 0 }}/1</span>
                        </div>
                        <div class="admin-prod-sidebar-card__body">
                            <!-- Ya guardada -->
                            <div v-if="imagenExistente && !preview" class="admin-prod-image-grid">
                                <div class="admin-prod-image-item">
                                    <img :src="imagenExistente" />
                                    <span class="admin-prod-image-badge">Actual</span>
                                    <button type="button" @click="quitarImagen" class="admin-prod-image-btn admin-prod-image-btn--delete">
                                        <i class="pi pi-times"></i>
                                    </button>
                                </div>
                            </div>

                            <label v-if="!imagenExistente && !preview" class="admin-prod-upload-area">
                                <i class="pi pi-cloud-upload"></i>
                                <span>Subir imagen</span>
                                <small>JPG o PNG, máx. 10MB</small>
                                <input type="file" accept="image/*" @change="onFileChange" />
                            </label>
                            <label v-else-if="!preview" class="admin-prod-hint" style="display:inline-flex;align-items:center;gap:0.4rem;cursor:pointer;color:var(--brand);margin-top:0.6rem">
                                <i class="pi pi-upload"></i> Reemplazar imagen
                                <input type="file" accept="image/*" class="hidden" style="display:none" @change="onFileChange" />
                            </label>
                            <p v-if="form.errors.imagen" class="admin-prod-error-text">{{ form.errors.imagen }}</p>

                            <!-- Vista previa del archivo nuevo -->
                            <div v-if="preview" class="admin-prod-image-grid">
                                <div class="admin-prod-image-item">
                                    <img :src="preview" />
                                    <button type="button" @click="quitarImagen" class="admin-prod-image-btn admin-prod-image-btn--delete">
                                        <i class="pi pi-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="admin-prod-action-card">
                        <button type="submit" :disabled="form.processing" class="admin-prod-btn-save" @click="submit">
                            <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-save'"></i>
                            {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                        </button>
                        <Link :href="route('admin.eventos.show', evento.id)" class="admin-prod-btn-cancel">
                            Cancelar
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>