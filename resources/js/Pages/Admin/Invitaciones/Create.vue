<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();

const form = useForm({
    nombre_destinatario: '',
    email: '',
    telefono: '',
    tipo: 'registro',
    vigencia_dias: 7,
    usos_maximos: 1,
    mensaje: '',
});

const vigenciaLabel = computed(() => {
    const opciones = { 1: '1 día', 3: '3 días', 7: '7 días', 15: '15 días', 30: '30 días' };
    return opciones[form.vigencia_dias] ?? `${form.vigencia_dias} días`;
});

function submit() {
    if (!form.nombre_destinatario || !form.email) {
        toast.error('Debes llenar todos los campos obligatorios.');
        return;
    }

    form.post(route('admin.invitaciones.store'), {
        onError: () => toast.error(Object.values(form.errors)[0] || 'Revisa los datos del formulario.'),
        onSuccess: () => toast.success('Invitación creada correctamente.'),
    });
}
</script>

<template>
    <Head title="Nueva Invitación" />

    <AdminLayout>
        <template #title>Nueva invitación</template>
        <template #breadcrumb>Administrador &gt; Invitaciones &gt; Nueva invitación</template>

        <Link :href="route('admin.invitaciones.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand mb-4">
            <i class="pi pi-arrow-left text-xs"></i> Volver a Invitaciones
        </Link>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulario -->
            <form @submit.prevent="submit" class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                    <div class="rounded-xl bg-gradient-to-br from-brand to-brand-dark text-white flex items-center justify-center shrink-0" style="width:44px;height:44px">
                        <i class="pi pi-user text-base"></i>
                    </div>
                    <h2 class="font-semibold text-gray-800">Datos del invitado</h2>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre completo *</label>
                            <input v-model="form.nombre_destinatario" type="text" placeholder="Ej. Juan Pérez" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                            <p v-if="form.errors.nombre_destinatario" class="text-red-600 text-xs mt-1">{{ form.errors.nombre_destinatario }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico *</label>
                            <input v-model="form.email" type="email" placeholder="ejemplo@correo.com" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                            <p v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Teléfono (opcional)</label>
                            <input v-model="form.telefono" type="text" placeholder="Ej. 55 1234 5678" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipo de invitación *</label>
                            <select v-model="form.tipo" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                                <option value="registro">Registro</option>
                                <option value="premium">Premium</option>
                                <option value="evento">Evento</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Vigencia del código *</label>
                            <select v-model.number="form.vigencia_dias" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                                <option :value="1">1 día</option>
                                <option :value="3">3 días</option>
                                <option :value="7">7 días</option>
                                <option :value="15">15 días</option>
                                <option :value="30">30 días</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Número máximo de usos *</label>
                            <select v-model.number="form.usos_maximos" class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none">
                                <option :value="1">1 uso</option>
                                <option :value="5">5 usos</option>
                                <option :value="10">10 usos</option>
                                <option :value="50">50 usos</option>
                                <option :value="100">100 usos</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Mensaje personalizado (opcional)</label>
                        <textarea
                            v-model="form.mensaje"
                            maxlength="250"
                            rows="3"
                            placeholder="Escribe un mensaje personal para tu invitado..."
                            class="w-full rounded-lg border border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand focus:ring-1 focus:outline-none resize-none"
                        ></textarea>
                        <p class="text-right text-xs text-gray-400 mt-1">{{ form.mensaje.length }}/250</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4 mt-2 border-t border-gray-100">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-brand hover:bg-brand-dark text-white font-medium px-6 py-2.5 rounded-lg text-sm disabled:opacity-50 flex items-center gap-2"
                    >
                        <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-send'"></i>
                        {{ form.processing ? 'Enviando...' : 'Enviar invitación' }}
                    </button>
                    <Link :href="route('admin.invitaciones.index')" class="text-sm text-gray-500 hover:text-gray-700 px-4 py-2.5">
                        Cancelar
                    </Link>
                </div>
            </form>

            <!-- Resumen en vivo -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 h-fit">
                <h2 class="font-semibold text-gray-800 mb-4">Resumen de la invitación</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Tipo de invitación:</dt>
                        <dd class="text-gray-800 font-medium capitalize">{{ form.tipo }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Vigencia del código:</dt>
                        <dd class="text-gray-800 font-medium">{{ vigenciaLabel }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Máximo de usos:</dt>
                        <dd class="text-gray-800 font-medium">{{ form.usos_maximos }} uso{{ form.usos_maximos > 1 ? 's' : '' }}</dd>
                    </div>
                    <div class="flex justify-between items-start">
                        <dt class="text-gray-400 pt-0.5">Destinatario:</dt>
                        <dd class="text-gray-800 font-medium text-right">{{ form.nombre_destinatario || '—' }}</dd>
                    </div>
                </dl>
                <p class="text-xs text-gray-400 mt-4 pt-4 border-t border-gray-100">
                    El código se genera automáticamente al guardar la invitación.
                </p>
            </div>
        </div>
    </AdminLayout>
</template>