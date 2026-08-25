<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    codigos: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ total: 0, disponibles: 0, usados: 0 }) },
});

const page = usePage();

// ============================================================
// Modal para enviar invitación
// ============================================================
const mostrarModal = ref(false);
const codigoParaEnviar = ref(null);
const emailDestino = ref('');
const telefonoWhatsApp = ref('');
const enviando = ref(false);
const enviado = ref(false);
const metodoSeleccionado = ref('email');

function abrirModal(codigo) {
    codigoParaEnviar.value = codigo;
    emailDestino.value = '';
    telefonoWhatsApp.value = '';
    enviado.value = false;
    metodoSeleccionado.value = 'email';
    mostrarModal.value = true;
}

function cerrarModal() {
    mostrarModal.value = false;
    codigoParaEnviar.value = null;
    emailDestino.value = '';
    telefonoWhatsApp.value = '';
    enviado.value = false;
}

// ============================================================
// Enviar por correo
// ============================================================
function enviarPorCorreo() {
    if (!emailDestino.value || !emailDestino.value.includes('@')) {
        alert('Por favor, ingresa un correo electrónico válido.');
        return;
    }

    enviando.value = true;

    const subject = encodeURIComponent('¡Te invito a unirte a Club de Fantasías!');
    const body = encodeURIComponent(
        `Hola,\n\n` +
        `Te invito a formar parte de Club de Fantasías, una comunidad exclusiva para conectar con personas que comparten tus intereses y estilo de vida.\n\n` +
        `Usa mi código de invitación: ${codigoParaEnviar.value.codigo}\n\n` +
        `Enlace: ${codigoParaEnviar.value.url}\n\n` +
        `¡Te esperamos!\n\n` +
        `Saludos, ${page.props.usuario?.nombre || 'Un amigo'}`
    );

    window.location.href = `mailto:${emailDestino.value}?subject=${subject}&body=${body}`;

    setTimeout(() => {
        enviando.value = false;
        enviado.value = true;
        setTimeout(() => {
            cerrarModal();
        }, 1500);
    }, 500);
}

// ============================================================
// Enviar por WhatsApp
// ============================================================
function enviarPorWhatsApp() {
    if (!telefonoWhatsApp.value || telefonoWhatsApp.value.length < 8) {
        alert('Por favor, ingresa un número de teléfono válido.');
        return;
    }

    enviando.value = true;

    // Limpiar el número (quitar espacios, guiones, etc.)
    let telefono = telefonoWhatsApp.value.replace(/[\s\-\(\)\.]/g, '');

    // Si no tiene código de país, agregar +52 (México por defecto)
    if (!telefono.startsWith('+')) {
        telefono = '+52' + telefono;
    }

    const mensaje = encodeURIComponent(
        `¡Hola! Te invito a unirte a Club de Fantasías 🎉\n\n` +
        `Usa mi código de invitación: ${codigoParaEnviar.value.codigo}\n` +
        `Enlace: ${codigoParaEnviar.value.url}\n\n` +
        `¡Te esperamos!`
    );

    window.open(`https://wa.me/${telefono}?text=${mensaje}`, '_blank');

    setTimeout(() => {
        enviando.value = false;
        enviado.value = true;
        setTimeout(() => {
            cerrarModal();
        }, 1500);
    }, 500);
}

// ============================================================
// Formulario de generación
// ============================================================
const form = useForm({
    cantidad: 1,
    vigencia_dias: 30,
    usos_maximos: 1,
});

const generando = ref(false);

function generar() {
    generando.value = true;
    form.post(route('invitaciones.index'), {
        preserveScroll: true,
        onFinish: () => {
            generando.value = false;
        },
    });
}

// ============================================================
// Helpers de presentación
// ============================================================
function formatearFecha(fecha) {
    if (!fecha) return 'Sin vencimiento';
    return new Date(fecha).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
}

function calcularDiasRestantes(fecha) {
    if (!fecha) return null;
    const hoy = new Date();
    const expira = new Date(fecha);
    const diff = Math.ceil((expira - hoy) / (1000 * 60 * 60 * 24));
    return diff;
}

function obtenerEstado(c) {
    if (c.estado === 'Disponible') return 'disponible';
    if (c.estado === 'Agotado') return 'agotado';
    if (c.estado === 'Expirado') return 'expirado';
    if (c.estado === 'Desactivado') return 'desactivado';
    return 'desconocido';
}

function estadoConfig(c) {
    const configs = {
        disponible: { bg: '#E8F5E9', fg: '#2E7D32', icon: 'pi-check-circle', label: 'Disponible' },
        agotado: { bg: '#FFF8E1', fg: '#B8860B', icon: 'pi-exclamation-triangle', label: 'Agotado' },
        expirado: { bg: '#FEE2E2', fg: '#C62828', icon: 'pi-times-circle', label: 'Expirado' },
        desactivado: { bg: '#F1F0EF', fg: '#6B6764', icon: 'pi-ban', label: 'Desactivado' },
        desconocido: { bg: '#F1F0EF', fg: '#6B6764', icon: 'pi-circle', label: 'Desconocido' },
    };
    return configs[obtenerEstado(c)] || configs.desconocido;
}

const tieneCodigos = computed(() => props.codigos.length > 0);
const codigosActivos = computed(() => props.codigos.filter(c => c.estado === 'Disponible'));
const codigosUsados = computed(() => props.codigos.filter(c => c.estado !== 'Disponible'));
</script>

<template>

    <Head title="Invitar amigos" />
    <AppLayout active-nav="" :usuario="page.props.usuario">
        <div class="invit-page">
            <!-- ============================================================ -->
            <!-- HEADER -->
            <!-- ============================================================ -->
            <div class="invit-page__header">
                <div class="invit-page__header-left">
                    <span class="invit-page__badge">✦ Comunidad en crecimiento</span>
                    <h1 class="invit-page__title">
                        <i class="pi pi-send"></i> Invita a tus amigos
                    </h1>
                    <p class="invit-page__subtitle">
                        Haz crecer esta comunidad invitando a personas que compartan tus mismos intereses
                        y estilo de vida. Cada nuevo miembro enriquece nuestra experiencia colectiva.
                    </p>
                    <div class="invit-page__mission">
                        <i class="pi pi-heart-fill"></i>
                        <span>Juntos construimos una comunidad más fuerte y diversa.</span>
                    </div>
                </div>
                <div class="invit-page__header-right">
                    <div class="invit-page__community">
                        <span class="invit-page__community-number">{{ stats.total }}</span>
                        <span class="invit-page__community-label">Códigos generados</span>
                        <div class="invit-page__community-detail">
                            <span class="invit-page__community-detail-item">
                                <span class="dot dot--green"></span>
                                {{ stats.disponibles }} disponibles
                            </span>
                            <span class="invit-page__community-detail-item">
                                <span class="dot dot--gray"></span>
                                {{ stats.usados }} usados
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- FLASH -->
            <!-- ============================================================ -->
            <div v-if="page.props.flash?.success" class="invit-flash">
                <i class="pi pi-check-circle"></i>
                <span>{{ page.props.flash.success }}</span>
            </div>

            <!-- ============================================================ -->
            <!-- LAYOUT DE DOS COLUMNAS -->
            <!-- ============================================================ -->
            <div class="invit-layout">
                <!-- IZQUIERDA -->
                <div class="invit-left">
                    <form class="invit-form" @submit.prevent="generar">
                        <div class="invit-form__header">
                            <h2 class="invit-form__title">
                                <i class="pi pi-plus-circle"></i> Generar nuevo código
                            </h2>
                            <span class="invit-form__subtitle">Configura las opciones de tu invitación</span>
                        </div>

                        <div class="invit-form__grid">
                            <div class="invit-form__group">
                                <label class="invit-form__label">
                                    <i class="pi pi-hashtag"></i> Cantidad
                                </label>
                                <input v-model.number="form.cantidad" type="number" min="1" max="20" required
                                    class="invit-form__input" />
                                <small class="invit-form__hint">Hasta 20 por solicitud</small>
                            </div>
                            <div class="invit-form__group">
                                <label class="invit-form__label">
                                    <i class="pi pi-calendar"></i> Vigencia (días)
                                </label>
                                <input v-model.number="form.vigencia_dias" type="number" min="1" max="365" required
                                    class="invit-form__input" />
                                <small class="invit-form__hint">Días hasta que expire</small>
                            </div>
                            <div class="invit-form__group">
                                <label class="invit-form__label">
                                    <i class="pi pi-users"></i> Usos por código
                                </label>
                                <input v-model.number="form.usos_maximos" type="number" min="1" max="50" required
                                    class="invit-form__input" />
                                <small class="invit-form__hint">Personas que pueden usarlo</small>
                            </div>
                        </div>

                        <button type="submit" class="invit-form__submit" :disabled="generando">
                            <i v-if="generando" class="pi pi-spin pi-spinner"></i>
                            <i v-else class="pi pi-plus-circle"></i>
                            {{ generando ? 'Generando...' : 'Generar código' }}
                        </button>
                    </form>
                </div>

                <!-- DERECHA -->
                <div class="invit-right">
                    <div class="invit-lista">
                        <div class="invit-lista__header">
                            <h2 class="invit-lista__title">
                                <i class="pi pi-list"></i> Códigos activos
                            </h2>
                            <span class="invit-lista__count">{{ codigosActivos.length }} disponibles</span>
                        </div>

                        <div v-if="!tieneCodigos" class="invit-empty">
                            <div class="invit-empty__icon">
                                <i class="pi pi-inbox"></i>
                            </div>
                            <h3 class="invit-empty__title">No hay códigos aún</h3>
                            <p class="invit-empty__desc">Genera tu primer código de invitación usando el formulario.</p>
                        </div>

                        <div v-else class="invit-cards">
                            <!-- Códigos activos -->
                            <div v-for="c in codigosActivos" :key="c.id" class="invit-card">
                                <div class="invit-card__top">
                                    <span class="invit-card__code">{{ c.codigo }}</span>
                                    <span class="invit-card__status" :style="{
                                        background: estadoConfig(c).bg,
                                        color: estadoConfig(c).fg
                                    }">
                                        <i :class="'pi ' + estadoConfig(c).icon"></i>
                                        {{ estadoConfig(c).label }}
                                    </span>
                                </div>

                                <div class="invit-card__middle">
                                    <div class="invit-card__info">
                                        <span class="invit-card__info-label">Usos</span>
                                        <span class="invit-card__info-value">{{ c.usos }} / {{ c.usos_maximos }}</span>
                                        <div class="invit-card__bar">
                                            <div class="invit-card__bar-fill"
                                                :style="{ width: Math.min((c.usos / c.usos_maximos) * 100, 100) + '%' }"
                                                :class="{
                                                    'invit-card__bar-fill--warning': (c.usos / c.usos_maximos) > 0.7 && (c.usos / c.usos_maximos) <= 0.9,
                                                    'invit-card__bar-fill--danger': (c.usos / c.usos_maximos) > 0.9
                                                }">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="invit-card__info">
                                        <span class="invit-card__info-label">Vence</span>
                                        <span class="invit-card__info-value" :class="{
                                            'invit-card__info-value--warning': calcularDiasRestantes(c.expira_en) <= 3 && calcularDiasRestantes(c.expira_en) > 0,
                                            'invit-card__info-value--danger': calcularDiasRestantes(c.expira_en) <= 0
                                        }">
                                            <i class="pi pi-calendar"></i>
                                            {{ formatearFecha(c.expira_en) }}
                                            <span v-if="calcularDiasRestantes(c.expira_en) !== null"
                                                class="invit-card__days">
                                                ({{ calcularDiasRestantes(c.expira_en) }} días)
                                            </span>
                                        </span>
                                    </div>
                                </div>

                                <div class="invit-card__bottom">
                                    <button class="invit-card__btn invit-card__btn--invite" @click="abrirModal(c)">
                                        <i class="pi pi-share-alt"></i> Invitar
                                    </button>
                                </div>
                            </div>

                            <!-- Códigos usados/agotados -->
                            <div v-if="codigosUsados.length > 0" class="invit-card invit-card--used">
                                <details class="invit-details">
                                    <summary class="invit-details__summary">
                                        <i class="pi pi-chevron-down"></i>
                                        <span>Códigos usados o agotados ({{ codigosUsados.length }})</span>
                                    </summary>
                                    <div class="invit-details__content">
                                        <div v-for="c in codigosUsados" :key="c.id" class="invit-card__item">
                                            <span class="invit-card__item-code">{{ c.codigo }}</span>
                                            <span class="invit-card__item-status" :style="{
                                                background: estadoConfig(c).bg,
                                                color: estadoConfig(c).fg
                                            }">
                                                <i :class="'pi ' + estadoConfig(c).icon"></i>
                                                {{ estadoConfig(c).label }}
                                            </span>
                                            <span class="invit-card__item-usos">{{ c.usos }}/{{ c.usos_maximos }}</span>
                                            <span class="invit-card__item-date">{{ formatearFecha(c.expira_en) }}</span>
                                        </div>
                                    </div>
                                </details>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- FOOTER DECORATIVO -->
            <!-- ============================================================ -->
            <div class="invit-footer">
                <div class="invit-footer__decoration">
                    <span class="invit-footer__diamond">◆</span>
                    <span class="invit-footer__line"></span>
                    <span class="invit-footer__diamond">◆</span>
                    <span class="invit-footer__line"></span>
                    <span class="invit-footer__diamond">◆</span>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- MODAL PARA ENVIAR INVITACIÓN MEJORADO -->
            <!-- ============================================================ -->
            <Teleport to="body">
                <Transition name="modal-fade">
                    <div v-if="mostrarModal" class="modal-backdrop" @click.self="cerrarModal">
                        <div class="modal-invite">
                            <!-- Cabecera -->
                            <div class="modal-invite__header">
                                <div class="modal-invite__header-left">
                                    <span class="modal-invite__badge">✦ Compartir invitación</span>
                                    <h3 class="modal-invite__title">
                                        <i class="pi pi-share-alt"></i> Invitar a {{ codigoParaEnviar?.codigo }}
                                    </h3>
                                </div>
                                <button class="modal-invite__close" @click="cerrarModal">
                                    <i class="pi pi-times"></i>
                                </button>
                            </div>

                            <!-- Cuerpo -->
                            <div class="modal-invite__body">
                                <!-- Selector de método -->
                                <div class="modal-invite__methods">
                                    <button class="modal-invite__method"
                                        :class="{ 'modal-invite__method--active': metodoSeleccionado === 'email' }"
                                        @click="metodoSeleccionado = 'email'">
                                        <i class="pi pi-envelope"></i>
                                        <span>Correo</span>
                                    </button>
                                    <button class="modal-invite__method"
                                        :class="{ 'modal-invite__method--active': metodoSeleccionado === 'whatsapp' }"
                                        @click="metodoSeleccionado = 'whatsapp'">
                                        <i class="pi pi-whatsapp"></i>
                                        <span>WhatsApp</span>
                                    </button>
                                </div>

                                <!-- Formulario de correo -->
                                <div v-if="metodoSeleccionado === 'email'" class="modal-invite__field-group">
                                    <label class="modal-invite__label">
                                        <i class="pi pi-user"></i> Correo electrónico
                                    </label>
                                    <input v-model="emailDestino" type="email" class="modal-invite__input"
                                        placeholder="ejemplo@correo.com" :disabled="enviando || enviado"
                                        @keydown.enter="enviarPorCorreo" />
                                    <small class="modal-invite__hint">Ingresa el correo de la persona a invitar</small>
                                </div>

                                <!-- Formulario de WhatsApp -->
                                <div v-if="metodoSeleccionado === 'whatsapp'" class="modal-invite__field-group">
                                    <label class="modal-invite__label">
                                        <i class="pi pi-phone"></i> Número de WhatsApp
                                    </label>
                                    <input v-model="telefonoWhatsApp" type="tel" class="modal-invite__input"
                                        placeholder="+52 55 1234 5678" :disabled="enviando || enviado"
                                        @keydown.enter="enviarPorWhatsApp" />
                                    <small class="modal-invite__hint">Incluye código de país (ej: +52 para
                                        México)</small>
                                </div>

                                <!-- Código a compartir -->
                                <div class="modal-invite__code-display">
                                    <span class="modal-invite__code-label">Código de invitación</span>
                                    <span class="modal-invite__code">{{ codigoParaEnviar?.codigo }}</span>
                                </div>

                                <!-- Mensaje de éxito -->
                                <div v-if="enviado" class="modal-invite__success">
                                    <i class="pi pi-check-circle"></i>
                                    <span>¡Invitación enviada con éxito!</span>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="modal-invite__footer">
                                <button class="modal-invite__btn modal-invite__btn--cancel" @click="cerrarModal"
                                    :disabled="enviando">
                                    Cancelar
                                </button>
                                <button v-if="metodoSeleccionado === 'email'"
                                    class="modal-invite__btn modal-invite__btn--send" @click="enviarPorCorreo"
                                    :disabled="enviando || enviado || !emailDestino">
                                    <i v-if="enviando" class="pi pi-spin pi-spinner"></i>
                                    <i v-else class="pi pi-send"></i>
                                    {{ enviando ? 'Enviando...' : 'Enviar por correo' }}
                                </button>
                                <button v-if="metodoSeleccionado === 'whatsapp'"
                                    class="modal-invite__btn modal-invite__btn--whatsapp" @click="enviarPorWhatsApp"
                                    :disabled="enviando || enviado || !telefonoWhatsApp">
                                    <i v-if="enviando" class="pi pi-spin pi-spinner"></i>
                                    <i v-else class="pi pi-whatsapp"></i>
                                    {{ enviando ? 'Enviando...' : 'Enviar por WhatsApp' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </Teleport>
        </div>
    </AppLayout>
</template>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA
   ========================================================================= */
.invit-page {
    --brand: #C81E3A;
    --brand-dark: #A6152D;
    --brand-soft: #FBEAEC;
    --ink: #171412;
    --ink-soft: #4B4744;
    --muted: #8A8481;
    --muted-light: #B7B2AF;
    --line: #ECE9E7;
    --surface: #FAF8F7;
    --white: #FFFFFF;
    --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.06);
    --shadow-md: 0 8px 30px rgba(0, 0, 0, 0.08);
    --shadow-lg: 0 20px 60px rgba(0, 0, 0, 0.10);
    --font-serif: 'Fraunces', Georgia, serif;
    --font-sans: 'Inter', system-ui, sans-serif;
    --radius-sm: 10px;
    --radius-md: 16px;
    --radius-lg: 24px;
    --radius-full: 999px;

    max-width: 1200px;
    margin: 0 auto;
    padding: 1.5rem 1.5rem 2rem;
    font-family: var(--font-sans);
    color: var(--ink);
}

/* =========================================================================
   HEADER
   ========================================================================= */
.invit-page__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    gap: 2rem;
}

.invit-page__badge {
    display: inline-block;
    font-size: 0.6rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: var(--brand);
    background: var(--brand-soft);
    padding: 0.2rem 1rem;
    border-radius: var(--radius-full);
    margin-bottom: 0.3rem;
}

.invit-page__title {
    font-family: var(--font-serif);
    font-size: 1.8rem;
    font-weight: 500;
    margin: 0 0 0.2rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.invit-page__title i {
    color: var(--brand);
    font-size: 1.5rem;
}

.invit-page__subtitle {
    color: var(--muted);
    margin: 0 0 0.5rem;
    font-size: 0.9rem;
    line-height: 1.6;
    max-width: 550px;
}

.invit-page__mission {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--brand-soft);
    padding: 0.4rem 0.9rem;
    border-radius: var(--radius-full);
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--brand-dark);
    border: 1px solid rgba(200, 30, 58, 0.12);
}

.invit-page__mission i {
    color: var(--brand);
    font-size: 0.8rem;
}

.invit-page__community {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1rem 1.8rem;
    text-align: center;
    min-width: 160px;
    box-shadow: var(--shadow-sm);
}

.invit-page__community-number {
    display: block;
    font-family: var(--font-serif);
    font-size: 1.8rem;
    font-weight: 600;
    color: var(--brand);
}

.invit-page__community-label {
    font-size: 0.65rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.invit-page__community-detail {
    display: flex;
    gap: 0.8rem;
    justify-content: center;
    margin-top: 0.4rem;
    padding-top: 0.4rem;
    border-top: 1px solid var(--line);
}

.invit-page__community-detail-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.68rem;
    color: var(--muted);
}

.dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
}

.dot--green {
    background: #2E7D32;
}

.dot--gray {
    background: #B7B2AF;
}

/* =========================================================================
   FLASH
   ========================================================================= */
.invit-flash {
    background: #E8F5E9;
    color: #2E7D32;
    border-radius: var(--radius-sm);
    padding: 0.6rem 1rem;
    margin-bottom: 1.2rem;
    font-size: 0.82rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-left: 3px solid #2E7D32;
}

/* =========================================================================
   LAYOUT
   ========================================================================= */
.invit-layout {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 1.5rem;
    align-items: start;
}

/* =========================================================================
   FORM
   ========================================================================= */
.invit-form {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.2rem 1.5rem;
    transition: box-shadow 0.3s ease;
}

.invit-form:hover {
    box-shadow: var(--shadow-sm);
}

.invit-form__header {
    margin-bottom: 1rem;
}

.invit-form__title {
    font-family: var(--font-serif);
    font-size: 1rem;
    margin: 0 0 0.1rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.invit-form__title i {
    color: var(--brand);
}

.invit-form__subtitle {
    font-size: 0.78rem;
    color: var(--muted);
}

.invit-form__grid {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    margin-bottom: 1rem;
}

.invit-form__group {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.invit-form__label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--ink-soft);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.invit-form__label i {
    font-size: 0.7rem;
}

.invit-form__input {
    border: 1.5px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.45rem 0.7rem;
    font-size: 0.85rem;
    font-family: inherit;
    color: var(--ink);
    outline: none;
    transition: all 0.2s ease;
    width: 100%;
}

.invit-form__input:focus {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.08);
}

.invit-form__hint {
    font-weight: 400;
    color: var(--muted-light);
    font-size: 0.65rem;
}

.invit-form__submit {
    background: linear-gradient(135deg, #C81E3A 0%, #E85A72 100%);
    color: #fff;
    border: none;
    border-radius: var(--radius-full);
    padding: 0.6rem 1.5rem;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(200, 30, 58, 0.25);
    width: 100%;
    justify-content: center;
}

.invit-form__submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(200, 30, 58, 0.35);
}

.invit-form__submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* =========================================================================
   LISTA DE CÓDIGOS
   ========================================================================= */
.invit-right {
    min-width: 0;
}

.invit-lista {
    background: var(--white);
    border: 1px solid var(--line);
    border-radius: var(--radius-md);
    padding: 1.2rem 1.5rem;
}

.invit-lista__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.6rem;
    border-bottom: 1px solid var(--line);
}

.invit-lista__title {
    font-family: var(--font-serif);
    font-size: 1rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.invit-lista__title i {
    color: var(--brand);
}

.invit-lista__count {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--muted);
    background: var(--surface);
    padding: 0.15rem 0.7rem;
    border-radius: var(--radius-full);
}

/* =========================================================================
   CARDS MEJORADAS
   ========================================================================= */
.invit-cards {
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
}

.invit-card {
    background: var(--surface);
    border-radius: var(--radius-sm);
    padding: 0.9rem 1.1rem;
    border: 1px solid var(--line);
    transition: all 0.3s ease;
}

.invit-card:hover {
    border-color: var(--brand);
    box-shadow: var(--shadow-sm);
}

.invit-card__top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.6rem;
}

.invit-card__code {
    font-family: var(--font-serif);
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--ink);
    letter-spacing: 0.02em;
}

.invit-card__status {
    font-size: 0.6rem;
    font-weight: 700;
    padding: 0.1rem 0.6rem;
    border-radius: var(--radius-full);
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
}

.invit-card__middle {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.8rem;
    margin-bottom: 0.6rem;
}

.invit-card__info {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
}

.invit-card__info-label {
    font-size: 0.6rem;
    color: var(--muted-light);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.invit-card__info-value {
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.invit-card__info-value i {
    font-size: 0.7rem;
}

.invit-card__info-value--warning {
    color: #B8860B;
}

.invit-card__info-value--danger {
    color: #C62828;
}

.invit-card__days {
    font-weight: 400;
    font-size: 0.65rem;
    color: var(--muted);
}

.invit-card__bar {
    width: 100%;
    height: 3px;
    background: var(--line);
    border-radius: var(--radius-full);
    overflow: hidden;
    margin-top: 0.1rem;
}

.invit-card__bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--brand), #E85A72);
    border-radius: var(--radius-full);
    transition: width 0.4s ease;
}

.invit-card__bar-fill--warning {
    background: linear-gradient(90deg, #F59E0B, #FCD34D);
}

.invit-card__bar-fill--danger {
    background: linear-gradient(90deg, #EF4444, #F87171);
}

.invit-card__bottom {
    display: flex;
    gap: 0.4rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--line);
}

.invit-card__btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    border: 1.5px solid var(--line);
    background: var(--white);
    border-radius: var(--radius-full);
    padding: 0.25rem 0.8rem;
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--ink-soft);
    cursor: pointer;
    transition: all 0.2s ease;
    width: 100%;
    justify-content: center;
}

.invit-card__btn:hover {
    transform: translateY(-1px);
}

.invit-card__btn--invite {
    background: linear-gradient(135deg, var(--brand), #E85A72);
    border-color: var(--brand);
    color: white;
}

.invit-card__btn--invite:hover {
    background: linear-gradient(135deg, var(--brand-dark), var(--brand));
    border-color: var(--brand-dark);
    color: white;
}

/* =========================================================================
   EMPTY
   ========================================================================= */
.invit-empty {
    text-align: center;
    padding: 1.5rem 1rem;
}

.invit-empty__icon {
    font-size: 2rem;
    color: var(--muted-light);
    margin-bottom: 0.4rem;
}

.invit-empty__title {
    font-family: var(--font-serif);
    font-size: 0.95rem;
    margin: 0 0 0.1rem;
}

.invit-empty__desc {
    color: var(--muted);
    margin: 0;
    font-size: 0.82rem;
}

/* =========================================================================
   DETALLES (CÓDIGOS USADOS)
   ========================================================================= */
.invit-card--used {
    background: var(--white);
    border-color: var(--line);
}

.invit-card--used:hover {
    border-color: var(--muted-light);
    box-shadow: none;
}

.invit-details {
    border: none;
}

.invit-details__summary {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--muted);
    padding: 0.1rem 0;
    user-select: none;
    list-style: none;
}

.invit-details__summary::-webkit-details-marker {
    display: none;
}

.invit-details__summary i {
    transition: transform 0.3s ease;
    font-size: 0.7rem;
}

.invit-details[open] .invit-details__summary i {
    transform: rotate(180deg);
}

.invit-details__content {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    padding-top: 0.4rem;
}

.invit-card__item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.5rem;
    background: var(--surface);
    border-radius: var(--radius-sm);
    flex-wrap: wrap;
}

.invit-card__item-code {
    font-family: var(--font-serif);
    font-size: 0.8rem;
    font-weight: 500;
}

.invit-card__item-status {
    font-size: 0.55rem;
    font-weight: 700;
    padding: 0.05rem 0.5rem;
    border-radius: var(--radius-full);
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
}

.invit-card__item-usos {
    font-size: 0.7rem;
    color: var(--muted);
}

.invit-card__item-date {
    font-size: 0.65rem;
    color: var(--muted-light);
}

/* =========================================================================
   FOOTER
   ========================================================================= */
.invit-footer {
    margin-top: 1.5rem;
}

.invit-footer__decoration {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
    opacity: 0.3;
}

.invit-footer__diamond {
    font-size: 0.5rem;
    color: var(--muted-light);
}

.invit-footer__line {
    width: 50px;
    height: 1px;
    background: var(--line);
}

/* =========================================================================
   MODAL MEJORADO
   ========================================================================= */
.modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.modal-invite {
    background: var(--white);
    border-radius: var(--radius-lg);
    max-width: 480px;
    width: 100%;
    padding: 1.5rem;
    box-shadow: var(--shadow-lg);
    animation: modalSlideUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes modalSlideUp {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Cabecera */
.modal-invite__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.2rem;
}

.modal-invite__header-left {
    flex: 1;
}

.modal-invite__badge {
    display: inline-block;
    font-size: 0.55rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: var(--brand);
    background: var(--brand-soft);
    padding: 0.1rem 0.7rem;
    border-radius: var(--radius-full);
    margin-bottom: 0.3rem;
}

.modal-invite__title {
    font-family: var(--font-serif);
    font-size: 1.1rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modal-invite__title i {
    color: var(--brand);
}

.modal-invite__close {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    color: var(--muted-light);
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.modal-invite__close:hover {
    background: var(--surface);
    color: var(--ink);
}

/* Métodos */
.modal-invite__methods {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    margin-bottom: 1.2rem;
}

.modal-invite__method {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.5rem 0.8rem;
    border: 2px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--white);
    color: var(--muted);
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.modal-invite__method:hover {
    border-color: var(--muted-light);
}

.modal-invite__method i {
    font-size: 0.9rem;
}

.modal-invite__method--active {
    border-color: var(--brand);
    color: var(--brand);
    background: var(--brand-soft);
}

/* Campos */
.modal-invite__field-group {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    margin-bottom: 1rem;
}

.modal-invite__label {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink-soft);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.modal-invite__label i {
    font-size: 0.7rem;
}

.modal-invite__input {
    border: 1.5px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.55rem 0.75rem;
    font-size: 0.9rem;
    font-family: inherit;
    color: var(--ink);
    outline: none;
    transition: all 0.2s ease;
    width: 100%;
}

.modal-invite__input:focus {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.08);
}

.modal-invite__input:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.modal-invite__hint {
    font-size: 0.65rem;
    color: var(--muted-light);
}

/* Código display */
.modal-invite__code-display {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0.8rem;
    background: var(--surface);
    border-radius: var(--radius-sm);
    margin-bottom: 0.8rem;
}

.modal-invite__code-label {
    font-size: 0.7rem;
    color: var(--muted);
}

.modal-invite__code {
    font-family: var(--font-serif);
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--brand);
    letter-spacing: 0.02em;
}

/* Éxito */
.modal-invite__success {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.8rem;
    background: #E8F5E9;
    color: #2E7D32;
    border-radius: var(--radius-sm);
    font-size: 0.85rem;
    font-weight: 500;
    margin-bottom: 0.8rem;
}

.modal-invite__success i {
    font-size: 1.1rem;
}

/* Footer */
.modal-invite__footer {
    display: flex;
    gap: 0.5rem;
    padding-top: 0.8rem;
    border-top: 1px solid var(--line);
}

.modal-invite__btn {
    flex: 1;
    padding: 0.6rem 1rem;
    border: none;
    border-radius: var(--radius-sm);
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
}

.modal-invite__btn--cancel {
    background: var(--surface);
    color: var(--ink-soft);
}

.modal-invite__btn--cancel:hover:not(:disabled) {
    background: var(--line);
}

.modal-invite__btn--send {
    background: linear-gradient(135deg, #1565C0, #1E88E5);
    color: white;
    box-shadow: 0 4px 15px rgba(21, 101, 192, 0.3);
}

.modal-invite__btn--send:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(21, 101, 192, 0.4);
}

.modal-invite__btn--whatsapp {
    background: linear-gradient(135deg, #25D366, #128C7E);
    color: white;
    box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
}

.modal-invite__btn--whatsapp:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4);
}

.modal-invite__btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

/* =========================================================================
   TRANSITIONS
   ========================================================================= */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.modal-fade-enter-from .modal-invite,
.modal-fade-leave-to .modal-invite {
    transform: translateY(20px) scale(0.95);
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
    .invit-layout {
        grid-template-columns: 1fr;
        gap: 1.2rem;
    }
}

@media (max-width: 768px) {
    .invit-page {
        padding: 1rem 1rem 1.5rem;
    }

    .invit-page__header {
        flex-direction: column;
        gap: 0.8rem;
    }

    .invit-page__title {
        font-size: 1.4rem;
    }

    .invit-page__community {
        width: 100%;
        min-width: auto;
    }

    .invit-lista {
        padding: 1rem;
    }

    .invit-card {
        padding: 0.8rem;
    }

    .invit-card__middle {
        grid-template-columns: 1fr;
        gap: 0.4rem;
    }

    .invit-card__top {
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    .invit-page__community-detail {
        flex-direction: column;
        gap: 0.1rem;
    }

    .modal-invite {
        margin: 1rem;
        padding: 1.2rem;
    }

    .modal-invite__footer {
        flex-direction: column;
    }

    .modal-invite__btn {
        width: 100%;
    }

    .modal-invite__methods {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 480px) {
    .invit-page__title {
        font-size: 1.2rem;
    }

    .invit-page__title i {
        font-size: 1.1rem;
    }

    .invit-card__code {
        font-size: 0.85rem;
    }

    .invit-form {
        padding: 1rem;
    }

    .invit-card__item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.2rem;
    }

    .modal-invite {
        padding: 1rem;
    }

    .modal-invite__methods {
        grid-template-columns: 1fr;
    }
}
</style>