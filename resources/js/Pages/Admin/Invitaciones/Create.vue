<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useToast } from '@/composables/useToast';
import { useFormatters } from '@/composables/useFormatters';

const toast = useToast();
const page = usePage();
const { formatDate } = useFormatters();

const props = defineProps({
    invitacionesRecientes: Array,
});

const admin = computed(() => page.props.auth?.admin);

const form = useForm({
    nombre_destinatario: '',
    email: '',
    telefono: '',
    tipo: 'registro',
    vigencia_dias: 7,
    usos_maximos: 1,
    mensaje: '',
    codigo: '',
});

// --- Vista previa del código ---
// IMPORTANTE: este código SÍ se manda al backend (form.codigo) para que
// lo que ves/copias/compartes aquí sea EXACTAMENTE el código que se
// guarda en la BD. Antes se generaba uno random solo para mostrarlo, y
// el servidor guardaba otro distinto sin que el admin se diera cuenta.
function generarPreview() {
    const chars = '23456789ACDEFGHJKLMNPQRSTUVWXYZ';
    const bloque = () => Array.from({ length: 4 }, () => chars.charAt(Math.floor(Math.random() * chars.length))).join('');
    return `${bloque()}-${bloque()}-${bloque()}`;
}
const codigoPreview = ref(generarPreview());
form.codigo = codigoPreview.value;
function regenerar() {
    codigoPreview.value = generarPreview();
    form.codigo = codigoPreview.value;
}

const vigenciaLabel = computed(() => {
    const opciones = { 1: '1 día', 3: '3 días', 7: '7 días', 15: '15 días', 30: '30 días' };
    return opciones[form.vigencia_dias] ?? `${form.vigencia_dias} días`;
});
const usosLabel = computed(() => {
    const opciones = { 1: '1 uso', 5: '5 usos', 10: '10 usos', 50: '50 usos', 100: '100 usos' };
    return opciones[form.usos_maximos] ?? `${form.usos_maximos} usos`;
});
const tipoLabel = computed(() => ({ registro: 'Registro', premium: 'Premium', evento: 'Evento' }[form.tipo]));

function copiarCodigo() {
    navigator.clipboard.writeText(codigoPreview.value);
    toast.success('Código copiado al portapapeles.');
}

function enviarPorCorreo() {
    if (!form.email) {
        toast.error('Primero escribe el correo del invitado.');
        return;
    }
    const asunto = encodeURIComponent('Tu invitación a Club de Fantasías');
    const cuerpo = encodeURIComponent(
        `Hola ${form.nombre_destinatario || ''},\n\nTe compartimos tu código de invitación: ${codigoPreview.value}\n\n${form.mensaje || ''}`
    );
    window.location.href = `mailto:${form.email}?subject=${asunto}&body=${cuerpo}`;
}

function enviarPorWhatsapp() {
    if (!form.telefono) {
        toast.error('Agrega un teléfono para enviar por WhatsApp.');
        return;
    }
    const numero = form.telefono.replace(/\D/g, '');
    const texto = encodeURIComponent(
        `Hola ${form.nombre_destinatario || ''}, tu código de invitación a Club de Fantasías es: ${codigoPreview.value}`
    );
    window.open(`https://wa.me/${numero}?text=${texto}`, '_blank');
}

function submit() {
    if (!form.nombre_destinatario || !form.email) {
        toast.error('Debes llenar todos los campos obligatorios.');
        return;
    }

    form.post(route('admin.invitaciones.store'), {
        onSuccess: () => {
            toast.success('Invitación enviada correctamente.');
        },
        onError: (errors) => {
            const primerError = Object.values(errors)[0];
            toast.error(primerError || 'Ocurrió un error al enviar la invitación.');
        }
    });
}

const estadoColores = {
    aceptada: 'bg-green-100 text-green-700',
    pendiente: 'bg-amber-100 text-amber-700',
    expirada: 'bg-red-100 text-red-700',
    utilizada: 'bg-blue-100 text-blue-700',
};
const estadoLabel = { aceptada: 'Activa', pendiente: 'Pendiente', expirada: 'Expirada', utilizada: 'Utilizada' };
</script>

<template>
    <Head title="Nueva Invitación" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>
            <span class="inline-flex items-center gap-1">
                <i class="pi pi-home text-xs"></i> Administrador
                <i class="pi pi-angle-right text-[10px] mx-0.5"></i> Invitaciones
                <i class="pi pi-angle-right text-[10px] mx-0.5"></i>
                <span class="text-brand font-medium">Nueva invitación</span>
            </span>
        </template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- Fila 1: Datos del invitado (50%) + Resumen de la invitación (50%) -->
            <div class="flex flex-col lg:flex-row gap-6 mb-6 w-full items-stretch">

                <!-- Datos del invitado -->
                <div class="w-full lg:w-1/2 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                        <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0"
                            style="width:44px;height:44px">
                            <i class="pi pi-user text-sm"></i>
                        </div>
                        <h2 class="font-semibold text-gray-900 text-lg">Datos del invitado</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre completo *</label>
                                <input v-model="form.nombre_destinatario" type="text" placeholder="Ej. Juan Pérez"
                                    class="w-full rounded-xl border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand" />
                                <p v-if="form.errors.nombre_destinatario" class="text-red-600 text-xs mt-1">{{ form.errors.nombre_destinatario }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico *</label>
                                <input v-model="form.email" type="email" placeholder="ejemplo@correo.com"
                                    class="w-full rounded-xl border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand" />
                                <p v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Teléfono (opcional)</label>
                                <input v-model="form.telefono" type="text" placeholder="Ej. 55 1234 5678"
                                    class="w-full rounded-xl border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand" />
                            </div>
                            <div>
                                <label class="flex items-center gap-1 text-sm font-medium text-gray-700 mb-1.5">
                                    Tipo de invitación *
                                    <i class="pi pi-info-circle text-gray-300 text-xs"
                                        title="Registro: acceso a la plataforma. Premium: incluye beneficios exclusivos. Evento: acceso a un evento específico."></i>
                                </label>
                                <select v-model="form.tipo"
                                    class="w-full rounded-xl border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand">
                                    <option value="registro">Registro</option>
                                    <option value="premium">Premium</option>
                                    <option value="evento">Evento</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Vigencia del código *</label>
                                <select v-model.number="form.vigencia_dias"
                                    class="w-full rounded-xl border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand">
                                    <option :value="1">1 día</option>
                                    <option :value="3">3 días</option>
                                    <option :value="7">7 días</option>
                                    <option :value="15">15 días</option>
                                    <option :value="30">30 días</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Número máximo de usos *</label>
                                <select v-model.number="form.usos_maximos"
                                    class="w-full rounded-xl border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand">
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
                            <textarea v-model="form.mensaje" maxlength="250" rows="3"
                                placeholder="Escribe un mensaje personal para tu invitado..."
                                class="w-full rounded-xl border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand resize-none"></textarea>
                            <p class="text-right text-xs text-gray-400 mt-1">{{ form.mensaje.length }}/250</p>
                        </div>
                    </div>
                </div>

                <!-- Resumen de la invitación -->
                <div class="w-full lg:w-1/2 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                        <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0"
                            style="width:44px;height:44px">
                            <i class="pi pi-file text-sm"></i>
                        </div>
                        <h2 class="font-semibold text-gray-900 text-lg">Resumen de la invitación</h2>
                    </div>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-400">Tipo de invitación:</dt>
                            <dd class="text-gray-800 font-medium">{{ tipoLabel }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-400">Vigencia del código:</dt>
                            <dd class="text-gray-800 font-medium">{{ vigenciaLabel }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-400">Máximo de usos:</dt>
                            <dd class="text-gray-800 font-medium">{{ usosLabel }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-400">Código generado:</dt>
                            <dd class="text-brand font-semibold">{{ codigoPreview }}</dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-gray-400">Estado:</dt>
                            <dd><span class="bg-green-100 text-green-700 text-xs font-medium px-2 py-0.5 rounded-full">Activa</span></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-400">Creación:</dt>
                            <dd class="text-gray-800 font-medium">{{ new Date().toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-400">Creada por:</dt>
                            <dd class="text-gray-800 font-medium">{{ admin?.nombre || 'Administrador' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Fila 2: Código de invitación + acciones (50%) + Invitaciones recientes (50%) -->
            <div class="flex flex-col lg:flex-row gap-6 mb-6 w-full items-stretch">

                <!-- Código de invitación + botones (todo en una sola tarjeta) -->
                <div class="w-full lg:w-1/2 bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center gap-3 pb-4 mb-4 border-b border-gray-100">
                        <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0"
                            style="width:44px;height:44px">
                            <i class="pi pi-key text-sm"></i>
                        </div>
                        <h2 class="font-semibold text-gray-900 text-lg">Código de invitación</h2>
                    </div>
                    <p class="text-sm text-gray-500 mb-3">Genera un código único que tu invitado usará para registrarse.</p>

                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex-1 border-2 border-dashed border-gray-200 rounded-xl py-3 text-center">
                            <span class="text-xl font-bold tracking-wider text-brand">{{ codigoPreview }}</span>
                        </div>
                        <button type="button" @click="regenerar"
                            class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand px-3 py-2 border border-gray-200 rounded-xl shrink-0">
                            <i class="pi pi-refresh text-xs"></i> Regenerar
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
                        <button type="button" @click="copiarCodigo"
                            class="flex items-center justify-center gap-2 text-sm text-gray-600 border border-gray-300 rounded-xl px-4 py-3 hover:bg-gray-50 whitespace-nowrap">
                            <i class="pi pi-copy text-xs"></i> Copiar código
                        </button>
                        <button type="button" @click="enviarPorCorreo"
                            class="flex items-center justify-center gap-2 text-sm text-gray-600 border border-gray-300 rounded-xl px-4 py-3 hover:bg-gray-50 whitespace-nowrap">
                            <i class="pi pi-envelope text-xs"></i> Enviar por correo
                        </button>
                        <button type="button" @click="enviarPorWhatsapp"
                            class="flex items-center justify-center gap-2 text-sm text-gray-600 border border-gray-300 rounded-xl px-4 py-3 hover:bg-gray-50 whitespace-nowrap">
                            <i class="pi pi-whatsapp text-xs"></i> Enviar por WhatsApp
                        </button>
                    </div>

                    <!-- Cancelar / Enviar invitación (ahora dentro de la misma tarjeta) -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <Link :href="route('admin.invitaciones.index')"
                            class="text-sm text-gray-500 hover:text-gray-700 px-5 py-2.5 border border-gray-300 rounded-xl">
                            Cancelar
                        </Link>
                        <button type="button" @click="submit" :disabled="form.processing"
                            class="bg-brand hover:bg-brand-dark text-white font-medium px-6 py-2.5 rounded-xl text-sm disabled:opacity-50 flex items-center gap-2 shadow-sm">
                            <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-send'"></i>
                            {{ form.processing ? 'Enviando...' : 'Enviar invitación' }}
                        </button>
                    </div>
                </div>

                <!-- Invitaciones recientes -->
                <div class="w-full lg:w-1/2 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0"
                                    style="width:44px;height:44px">
                                    <i class="pi pi-clock text-sm"></i>
                                </div>
                                <h2 class="font-semibold text-gray-900 text-lg">Invitaciones recientes</h2>
                            </div>
                            <Link :href="route('admin.invitaciones.index')" class="text-xs font-semibold text-brand hover:underline">
                                Ver todas
                            </Link>
                        </div>

                        <ul class="space-y-3">
                            <li v-for="inv in invitacionesRecientes" :key="inv.id" class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-9 h-9 min-w-[36px] max-w-[36px] rounded-full bg-gray-100 flex items-center justify-center text-gray-400 shrink-0">
                                        <i class="pi pi-user text-xs"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ inv.nombre_destinatario }}</p>
                                        <p class="text-xs text-gray-400 truncate">{{ inv.email }}</p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="block text-xs font-medium px-2 py-0.5 rounded-full" :class="estadoColores[inv.estado]">
                                        {{ estadoLabel[inv.estado] }}
                                    </span>
                                    <span class="block text-[11px] text-gray-400 mt-0.5">{{ formatDate(inv.created_at) }}</span>
                                </div>
                            </li>
                            <li v-if="!invitacionesRecientes?.length" class="text-center py-8 text-gray-400 text-xs">
                                Aún no hay invitaciones.
                            </li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>
    </AdminLayout>
</template>