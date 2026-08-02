<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const form = useForm({
    nombre: '',
    descripcion: '',
    fecha: '',
    hora: '',
    ciudad: '',
    zona_ubicacion: '',
    precio: 0,
    capacidad: '',
    tipo: 'general',
    categoria: '',
    codigo_vestimenta: '',
    estado: 'borrador',
});

function submit() {
    const obligatorios = ['nombre', 'fecha', 'hora', 'ciudad', 'tipo', 'estado'];
    const faltantes = obligatorios.filter((c) => !form[c]);
    
    if (faltantes.length) {
        toast.error('Debes llenar todos los campos obligatorios.');
        return;
    }

    form.post(route('admin.eventos.store'), {
        onSuccess: () => {
            toast.success('Evento creado con éxito.');
        },
        onError: (errors) => {
            const primerError = Object.values(errors)[0];
            toast.error(primerError || 'Ocurrió un error al crear el evento.');
        }
    });
}
</script>

<template>
    <Head title="Nuevo Evento" />

    <AdminLayout>
        <template #title>Nuevo evento</template>
        <template #breadcrumb>Dashboard &gt; Eventos &gt; Nuevo evento</template>

        <Link :href="route('admin.eventos.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand mb-4">
            <i class="pi pi-arrow-left text-xs"></i> Volver a Eventos
        </Link>

        <form @submit.prevent="submit" class="max-w-3xl bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <!-- Datos del evento -->
            <div class="p-6">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                    <div class="rounded-xl bg-gradient-to-br from-brand to-brand-dark text-white flex items-center justify-center shrink-0" style="width:48px;height:48px">
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
                        <input v-model="form.nombre" type="text" placeholder="Ej. Noche de Fantasías" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                        <p v-if="form.errors.nombre" class="text-red-600 text-xs mt-1">{{ form.errors.nombre }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Descripción</label>
                        <textarea v-model="form.descripcion" rows="3" placeholder="Describe el evento..." class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Fecha *</label>
                            <input v-model="form.fecha" type="date" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                            <p v-if="form.errors.fecha" class="text-red-600 text-xs mt-1">{{ form.errors.fecha }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Hora *</label>
                            <input v-model="form.hora" type="time" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                            <p v-if="form.errors.hora" class="text-red-600 text-xs mt-1">{{ form.errors.hora }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Ciudad *</label>
                            <input v-model="form.ciudad" type="text" placeholder="Ej. Ciudad de México" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                            <p v-if="form.errors.ciudad" class="text-red-600 text-xs mt-1">{{ form.errors.ciudad }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Zona / lugar exacto</label>
                            <input v-model="form.zona_ubicacion" type="text" placeholder="Ej. Polanco" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Precio, cupo y tipo -->
            <div class="p-6 bg-gray-50/50 border-t border-gray-100">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                    <div class="rounded-xl bg-gradient-to-br from-brand to-brand-dark text-white flex items-center justify-center shrink-0" style="width:48px;height:48px">
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
                        <input v-model.number="form.precio" type="number" min="0" step="0.01" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Capacidad (opcional)</label>
                        <input v-model.number="form.capacidad" type="number" min="1" placeholder="Ilimitado" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipo *</label>
                        <select v-model="form.tipo" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                            <option value="general">General</option>
                            <option value="vip">VIP</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Estado *</label>
                        <select v-model="form.estado" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                            <option value="borrador">Borrador</option>
                            <option value="publicado">Publicado</option>
                            <option value="cancelado">Cancelado</option>
                            <option value="completo">Completado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Categoría</label>
                        <input v-model="form.categoria" type="text" placeholder="Ej. Fiesta temática" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Código de vestimenta</label>
                        <input v-model="form.codigo_vestimenta" type="text" placeholder="Ej. Elegante / Antifaz" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="p-6 border-t border-gray-100 flex items-center gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-brand hover:bg-brand-dark text-white font-medium px-6 py-2.5 rounded-lg text-sm disabled:opacity-50 flex items-center gap-2"
                >
                    <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-check'"></i>
                    {{ form.processing ? 'Guardando...' : 'Crear evento' }}
                </button>
                <Link :href="route('admin.eventos.index')" class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5">
                    Cancelar
                </Link>
            </div>
        </form>
    </AdminLayout>
</template>