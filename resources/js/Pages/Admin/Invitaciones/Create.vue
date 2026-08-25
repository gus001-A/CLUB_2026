<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import { useFormatters } from '@/composables/useFormatters';

const toast = useToast();
const page = usePage();
const { formatDate } = useFormatters();

const props = defineProps({
    invitacionesRecientes: Array,
    eventos: { type: Array, default: () => [] },
});

const admin = computed(() => page.props.auth?.admin);

const form = useForm({
    nombre_destinatario: '',
    email: '',
    telefono: '',
    tipo: 'registro',
    evento_id: null,
    vigencia_dias: 7,
    usos_maximos: 1,
    mensaje: '',
    codigo: '',
});

// --- Vista previa del código ---
// IMPORTANTE: este código SÍ se manda al backend (form.codigo) para que
// lo que ves/copias/compartes aquí sea EXACTAMENTE el código que se
// guarda en la BD.
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

// Si cambian el tipo a algo que no sea "evento", limpiamos evento_id —
// que no se quede un evento elegido "fantasma" si luego cambian de opinión.
watch(() => form.tipo, (nuevoTipo) => {
    if (nuevoTipo !== 'evento') {
        form.evento_id = null;
    }
});

const eventoSeleccionado = computed(() => props.eventos.find((e) => e.id === form.evento_id) || null);

function submit() {
    if (!form.nombre_destinatario || !form.email) {
        toast.error('Debes llenar todos los campos obligatorios.');
        return;
    }
    if (form.tipo === 'evento' && !form.evento_id) {
        toast.error('Elige a qué evento estás invitando.');
        return;
    }

    form.post(route('admin.invitaciones.store'), {
        onSuccess: () => toast.success('Invitación enviada correctamente.'),
        onError: (errors) => {
            const primerError = Object.values(errors)[0];
            toast.error(primerError || 'Ocurrió un error al enviar la invitación.');
        }
    });
}

const badgeEstado = { aceptada: 'admin-invit-badge--aceptada', pendiente: 'admin-invit-badge--pendiente', expirada: 'admin-invit-badge--expirada', utilizada: 'admin-invit-badge--utilizada' };
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

        <div class="admin-invit-page">
            <!-- Fila 1: Datos del invitado + Resumen de la invitación -->
            <div class="admin-two-col-grid gap-6 mb-6 w-full items-stretch">

                <!-- Datos del invitado -->
                <div class="admin-invit-card min-w-0">
                    <div class="admin-invit-card__header">
                        <div class="admin-invit-card__header-left">
                            <div class="admin-invit-header-icon"><i class="pi pi-user"></i></div>
                            <h3>Datos del invitado</h3>
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 p-6">
                        <div class="admin-user-field-row">
                            <div class="admin-user-field">
                                <label>Nombre completo <span class="admin-user-required">*</span></label>
                                <input v-model="form.nombre_destinatario" type="text" placeholder="Ej. Juan Pérez"
                                    :class="{ 'admin-user-input-error': form.errors.nombre_destinatario }" />
                                <p v-if="form.errors.nombre_destinatario" class="admin-user-error-text">{{ form.errors.nombre_destinatario }}</p>
                            </div>
                            <div class="admin-user-field">
                                <label>Correo electrónico <span class="admin-user-required">*</span></label>
                                <input v-model="form.email" type="email" placeholder="ejemplo@correo.com"
                                    :class="{ 'admin-user-input-error': form.errors.email }" />
                                <p v-if="form.errors.email" class="admin-user-error-text">{{ form.errors.email }}</p>
                            </div>
                        </div>

                        <div class="admin-user-field-row">
                            <div class="admin-user-field">
                                <label>Teléfono <span class="admin-user-optional">(opcional)</span></label>
                                <input v-model="form.telefono" type="text" placeholder="Ej. 55 1234 5678" />
                            </div>
                            <div class="admin-user-field">
                                <label>
                                    Tipo de invitación <span class="admin-user-required">*</span>
                                    <i class="pi pi-info-circle" style="color:var(--muted-light);font-size:0.65rem"
                                        title="Registro: acceso a la plataforma. Premium: incluye beneficios exclusivos. Evento: acceso a un evento específico."></i>
                                </label>
                                <select v-model="form.tipo">
                                    <option value="registro">Registro</option>
                                    <option value="premium">Premium</option>
                                    <option value="evento">Evento</option>
                                </select>
                            </div>
                        </div>

                        <div v-if="form.tipo === 'evento'" class="admin-user-field" :class="{ 'admin-user-input-error': form.errors.evento_id }">
                            <label>¿A cuál evento invitas? <span class="admin-user-required">*</span></label>
                            <select v-model="form.evento_id">
                                <option :value="null" disabled>Selecciona un evento</option>
                                <option v-for="e in eventos" :key="e.id" :value="e.id">
                                    {{ e.nombre }} — {{ e.fecha }}{{ e.ciudad ? ' · ' + e.ciudad : '' }}
                                </option>
                            </select>
                            <p v-if="form.errors.evento_id" class="admin-user-error-text">{{ form.errors.evento_id }}</p>
                            <p v-else-if="!eventos.length" class="admin-user-hint">No hay eventos próximos publicados para invitar.</p>
                        </div>

                        <div class="admin-user-field-row">
                            <div class="admin-user-field">
                                <label>Vigencia del código <span class="admin-user-required">*</span></label>
                                <select v-model.number="form.vigencia_dias">
                                    <option :value="1">1 día</option>
                                    <option :value="3">3 días</option>
                                    <option :value="7">7 días</option>
                                    <option :value="15">15 días</option>
                                    <option :value="30">30 días</option>
                                </select>
                            </div>
                            <div class="admin-user-field">
                                <label>Número máximo de usos <span class="admin-user-required">*</span></label>
                                <select v-model.number="form.usos_maximos">
                                    <option :value="1">1 uso</option>
                                    <option :value="5">5 usos</option>
                                    <option :value="10">10 usos</option>
                                    <option :value="50">50 usos</option>
                                    <option :value="100">100 usos</option>
                                </select>
                            </div>
                        </div>

                        <div class="admin-user-field">
                            <label>Mensaje personalizado <span class="admin-user-optional">(opcional)</span></label>
                            <textarea v-model="form.mensaje" maxlength="250" rows="3" placeholder="Escribe un mensaje personal para tu invitado..."
                                style="width:100%;padding:0.55rem 0.8rem;border-radius:8px;border:1.5px solid var(--line);font-size:0.85rem;font-family:inherit;resize:none"></textarea>
                            <p class="admin-user-hint" style="text-align:right">{{ form.mensaje.length }}/250</p>
                        </div>
                    </div>
                </div>

                <!-- Resumen de la invitación -->
                <div class="admin-invit-card min-w-0">
                    <div>
                        <div class="admin-invit-card__header">
                            <div class="admin-invit-card__header-left">
                                <div class="admin-invit-header-icon"><i class="pi pi-file"></i></div>
                                <h3>Resumen de la invitación</h3>
                            </div>
                        </div>
                        <div class="admin-cobros-summary">
                            <div class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Tipo de invitación</span>
                                <span class="admin-cobros-summary-value">{{ tipoLabel }}</span>
                            </div>
                            <div v-if="form.tipo === 'evento'" class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Evento</span>
                                <span class="admin-cobros-summary-value" style="text-align:right;max-width:220px">
                                    {{ eventoSeleccionado ? eventoSeleccionado.nombre : '—' }}
                                </span>
                            </div>
                            <div class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Vigencia del código</span>
                                <span class="admin-cobros-summary-value">{{ vigenciaLabel }}</span>
                            </div>
                            <div class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Máximo de usos</span>
                                <span class="admin-cobros-summary-value">{{ usosLabel }}</span>
                            </div>
                            <div class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Código generado</span>
                                <span class="admin-cobros-summary-value" style="color:var(--brand)">{{ codigoPreview }}</span>
                            </div>
                            <div class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Estado</span>
                                <span class="admin-invit-badge admin-invit-badge--aceptada"><span class="admin-invit-badge-dot"></span>Activa</span>
                            </div>
                            <div class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Creación</span>
                                <span class="admin-cobros-summary-value">{{ new Date().toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</span>
                            </div>
                            <div class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Creada por</span>
                                <span class="admin-cobros-summary-value">{{ admin?.nombre || 'Administrador' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila 2: Código de invitación + acciones | Invitaciones recientes -->
            <div class="admin-two-col-grid gap-6 mb-6 w-full items-stretch">

                <!-- Código de invitación + botones -->
                <div class="admin-invit-card min-w-0">
                    <div class="admin-invit-card__header">
                        <div class="admin-invit-card__header-left">
                            <div class="admin-invit-header-icon"><i class="pi pi-key"></i></div>
                            <h3>Código de invitación</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <p class="admin-user-hint" style="margin-bottom:0.9rem">Genera un código único que tu invitado usará para registrarse.</p>

                        <div class="flex items-center gap-3 mb-4">
                            <div class="admin-invit-code-box">
                                <span class="admin-invit-code-value">{{ codigoPreview }}</span>
                            </div>
                            <button type="button" @click="regenerar" class="admin-invit-code-action-btn shrink-0" style="width:auto">
                                <i class="pi pi-refresh"></i> Regenerar
                            </button>
                        </div>

                        <div class="admin-invit-code-actions" style="margin-bottom:1.5rem">
                            <button type="button" @click="copiarCodigo" class="admin-invit-code-action-btn">
                                <i class="pi pi-copy"></i> Copiar código
                            </button>
                            <button type="button" @click="enviarPorCorreo" class="admin-invit-code-action-btn">
                                <i class="pi pi-envelope"></i> Enviar por correo
                            </button>
                            <button type="button" @click="enviarPorWhatsapp" class="admin-invit-code-action-btn">
                                <i class="pi pi-whatsapp"></i> Enviar por WhatsApp
                            </button>
                        </div>

                        <div class="flex items-center justify-between pt-4" style="border-top:1px solid var(--line)">
                            <Link :href="route('admin.invitaciones.index')" class="admin-btn-secondary">Cancelar</Link>
                            <button type="button" @click="submit" :disabled="form.processing" class="admin-invit-btn-create">
                                <i class="pi" :class="form.processing ? 'pi-spin pi-spinner' : 'pi-send'"></i>
                                {{ form.processing ? 'Enviando...' : 'Enviar invitación' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Invitaciones recientes -->
                <div class="admin-invit-card min-w-0">
                    <div>
                        <div class="admin-invit-card__header">
                            <div class="admin-invit-card__header-left">
                                <div class="admin-invit-header-icon"><i class="pi pi-clock"></i></div>
                                <h3>Invitaciones recientes</h3>
                            </div>
                            <Link :href="route('admin.invitaciones.index')" style="color:var(--brand)" class="text-xs font-semibold hover:underline">Ver todas</Link>
                        </div>

                        <div class="admin-dash-list">
                            <div v-for="inv in invitacionesRecientes" :key="inv.id" class="admin-dash-list-item">
                                <div class="admin-dash-list-item__left">
                                    <div class="admin-dash-list-icon"><i class="pi pi-user"></i></div>
                                    <div class="min-w-0">
                                        <p class="admin-dash-list-title">{{ inv.nombre_destinatario }}</p>
                                        <p class="admin-dash-list-meta truncate">{{ inv.email }}</p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="admin-invit-badge" :class="badgeEstado[inv.estado]" style="display:inline-flex">
                                        <span class="admin-invit-badge-dot"></span>{{ estadoLabel[inv.estado] }}
                                    </span>
                                    <p class="admin-dash-list-meta mt-0.5">{{ formatDate(inv.created_at) }}</p>
                                </div>
                            </div>
                            <div v-if="!invitacionesRecientes?.length" class="admin-invit-empty">Aún no hay invitaciones.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>