<template>

    <Head :title="'Comprobante - ' + evento.titulo" />

    <AppLayout active-nav="eventos">
        <div class="comprobante-page">
            <!-- Barra de navegación superior -->
            <div class="top-nav no-print">
                <Link :href="route('eventos.show', evento.id)" class="btn-back">
                    <i class="pi pi-arrow-left"></i>
                    Volver al evento
                </Link>
                <div class="top-nav-actions">
                    <button class="btn btn--primary" @click="descargarPDF">
                        <i class="pi pi-file-pdf"></i> Descargar PDF
                    </button>
                </div>
            </div>

            <div class="voucher-wrapper">
                <div class="voucher-container">
                    <div class="voucher-page">
                        <div class="voucher">
                            <header class="voucher__header">
                                <div class="voucher__brand">
                                    <img src="/images/LOGO.png" alt="Logo" class="voucher__logo" />
                                    <span class="tagline">PRIVADO · EXCLUSIVO · DISCRETO</span>
                                </div>
                                <div class="voucher__title">
                                    <span class="voucher__badge">✓ RESERVA CONFIRMADA</span>
                                    <h1>COMPROBANTE DE RESERVA</h1>
                                    <p>Tu lugar está asegurado para este evento exclusivo.</p>
                                </div>
                            </header>

                            <section class="event-card">
                                <div class="event-card__image">
                                    <img :src="getImageUrl(evento.imagen)" :alt="evento.titulo" />
                                </div>
                                <div class="event-card__body">
                                    <h2>{{ evento.titulo }} <span v-if="evento.tipo === 'vip'"
                                            class="vip-tag">VIP</span></h2>
                                    <ul class="event-card__meta">
                                        <li><i class="pi pi-calendar"></i> {{ evento.fecha || 'Fecha por confirmar' }}
                                        </li>
                                        <li><i class="pi pi-clock"></i> {{ evento.hora || '21:00 hrs' }}</li>
                                        <li><i class="pi pi-map-marker"></i> {{ evento.ciudad || 'Ciudad de México' }}
                                        </li>
                                        <li><i class="pi pi-home"></i> <strong>Dirección:</strong> {{ direccionCompleta
                                            }}</li>
                                    </ul>
                                    <hr />
                                    <p class="event-card__desc">{{ evento.descripcion || 'Sin descripción del evento' }}
                                    </p>
                                </div>
                            </section>

                            <section class="details-grid">
                                <div class="detail-card">
                                    <h3>DETALLES DE LA RESERVA</h3>
                                    <dl>
                                        <div class="detail-row">
                                            <dt><i class="pi pi-crown"></i> Tipo de acceso:</dt>
                                            <dd><span class="badge-acceso">{{ reserva.tipo_acceso?.toUpperCase() ||
                                                'VIP' }}</span></dd>
                                        </div>
                                        <div class="detail-row">
                                            <dt><i class="pi pi-users"></i> Perfil:</dt>
                                            <dd>{{ perfilAcompanante }}</dd>
                                        </div>
                                        <div class="detail-row">
                                            <dt><i class="pi pi-user"></i> Titular:</dt>
                                            <dd>{{ titularNombre }}</dd>
                                        </div>
                                        <div v-if="tieneAcompanantes" class="detail-row">
                                            <dt><i class="pi pi-user-plus"></i> Acompañante{{ reserva.asistentes > 1 ?
                                                's' : '' }}:</dt>
                                            <dd>{{ nombresAcompanantes }}</dd>
                                        </div>
                                        <div class="detail-row">
                                            <dt><i class="pi pi-users"></i> Total asistentes:</dt>
                                            <dd><strong>{{ reserva.asistentes }}</strong></dd>
                                        </div>
                                        <div class="detail-row">
                                            <dt><i class="pi pi-credit-card"></i> Método de pago:</dt>
                                            <dd>{{ metodoPago }}</dd>
                                        </div>
                                    </dl>
                                </div>

                                <div class="detail-card">
                                    <h3>RESUMEN DE PAGO</h3>
                                    <div class="payment-row">
                                        <span>Precio por persona</span>
                                        <strong>${{ formatoMoneda(precioUnitario) }} MXN</strong>
                                    </div>
                                    <div class="payment-row">
                                        <span>Cantidad</span>
                                        <strong>{{ reserva.asistentes }}</strong>
                                    </div>
                                    <hr />
                                    <div class="payment-row">
                                        <span>Subtotal</span>
                                        <strong>${{ formatoMoneda(subtotal) }} MXN</strong>
                                    </div>
                                    <div class="payment-row">
                                        <span>Cargo por servicio</span>
                                        <strong>${{ formatoMoneda(cargoServicio) }} MXN</strong>
                                    </div>
                                    <hr />
                                    <div class="payment-row payment-row--total">
                                        <span>TOTAL PAGADO</span>
                                        <strong>${{ formatoMoneda(reserva.total) }} <small>MXN</small></strong>
                                    </div>
                                </div>
                            </section>

                            <!-- QR CODE usando el campo codigo_qr -->
                            <section class="qr-section">
                                <div class="qr-container">
                                    <div class="qr-code-wrapper">
                                        <img :src="qrUrl" alt="Código QR de acceso" class="qr-code" />
                                        <span class="qr-label">CÓDIGO DE ACCESO</span>
                                    </div>
                                    <div class="qr-info">
                                        <h3>ACCESO AL EVENTO</h3>
                                        <p>Presenta este código QR en la entrada del evento.</p>
                                        <div class="qr-hint">
                                            <i class="pi pi-mobile"></i>
                                            <span>Muestra este código desde tu teléfono o imprime este
                                                comprobante.</span>
                                        </div>
                                        <div class="qr-folio">
                                            <span class="folio-label">FOLIO DE RESERVA</span>
                                            <strong class="folio-value">{{ reserva.folio }}</strong>
                                        </div>
                                        <div class="qr-direccion">
                                            <i class="pi pi-map-marker"></i>
                                            <span>{{ direccionCompleta }}</span>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    reserva: {
        type: Object,
        required: true
    },
    evento: {
        type: Object,
        required: true
    }
});

const toast = useToast();

const direccionCompleta = computed(() => {
    const direccion = props.evento.ubicacion_detalle || props.evento.ubicacion || '';
    const ciudad = props.evento.ciudad || 'Ciudad de México';
    const codigo_postal = props.evento.codigo_postal || '';
    const colonia = props.evento.colonia || '';

    let parts = [];
    if (direccion) parts.push(direccion);
    if (colonia) parts.push(colonia);
    if (codigo_postal) parts.push('CP ' + codigo_postal);
    if (ciudad) parts.push(ciudad);

    return parts.length > 0 ? parts.join(', ') : 'Locación privada - Se comparte al confirmar asistencia';
});

const titularNombre = computed(() => {
    return props.reserva.metadatos?.titular?.nombre || 'No especificado';
});

const nombresAcompanantes = computed(() => {
    const acompanantes = props.reserva.metadatos?.acompanantes || [];
    if (acompanantes.length === 0) return '';
    return acompanantes.map(a => a.nombre || 'Sin nombre').join(', ');
});

const tieneAcompanantes = computed(() => {
    const acompanantes = props.reserva.metadatos?.acompanantes || [];
    return acompanantes.length > 0;
});

const perfilAcompanante = computed(() => {
    const asistentes = props.reserva.asistentes || 1;
    if (asistentes === 1) return 'Individual';
    if (asistentes === 2) return 'Pareja';
    return 'Grupo';
});

const metodoPago = computed(() => {
    const pago = props.reserva.metadatos?.pago || {};
    // Mostrar solo OXXO o Tarjeta
    if (pago.tipo === 'oxxo') return 'OXXO';
    if (pago.tipo === 'paypal') return 'PayPal';
    if (pago.ultimos_digitos) return 'Tarjeta';
    return 'Tarjeta';
});

const precioUnitario = computed(() => {
    return props.reserva.metadatos?.precio_unitario || 0;
});

const cargoServicio = computed(() => {
    return props.reserva.metadatos?.cargo_servicio || 0;
});

const subtotal = computed(() => {
    return precioUnitario.value * props.reserva.asistentes;
});

// 🔥 QR usando el campo codigo_qr de la reserva
const qrUrl = computed(() => {
    // Usar directamente el campo codigo_qr de la reserva
    if (props.reserva.codigo_qr) {
        // Si es una URL completa, la usamos
        if (props.reserva.codigo_qr.startsWith('http://') || props.reserva.codigo_qr.startsWith('https://')) {
            return props.reserva.codigo_qr;
        }
        // Si es un código base64, lo mostramos como imagen
        if (props.reserva.codigo_qr.startsWith('data:image')) {
            return props.reserva.codigo_qr;
        }
        // Si es un texto, generamos QR con ese texto
        return `https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=0&data=${encodeURIComponent(props.reserva.codigo_qr)}`;
    }
    // Fallback: usar el folio
    return `https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=0&data=${encodeURIComponent(props.reserva.folio)}`;
});

function getImageUrl(path) {
    if (!path) return '/images/eventos/default-hero.jpg';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/') || path.startsWith('/images/')) return path;
    if (path.startsWith('storage/')) return '/' + path;
    return '/storage/' + path.replace(/^\/+/, '');
}

function formatoMoneda(valor) {
    return new Intl.NumberFormat('es-MX').format(Math.round(valor));
}

function descargarPDF() {
    if (props.reserva.id) {
        window.open(route('eventos.reserva.pdf', props.reserva.id), '_blank');
    } else {
        toast.add({
            severity: 'warn',
            summary: 'No disponible',
            detail: 'El PDF no está disponible en este momento.',
            life: 3000
        });
    }
}
</script>

<style scoped>
.comprobante-page {
    background: #f0f2f5;
    min-height: 100vh;
    padding: 1rem 2rem 3rem;
}

.top-nav {
    max-width: 1100px;
    margin: 0 auto 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.6rem 1.2rem;
    background: #fff;
    border: 2px solid #e8e8ea;
    border-radius: 10px;
    color: #1f2024;
    font-weight: 600;
    font-size: 0.85rem;
    text-decoration: none;
    transition: all 0.3s ease;
    font-family: 'Inter', system-ui, sans-serif;
}

.btn-back:hover {
    border-color: #C81E3A;
    color: #C81E3A;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.top-nav-actions {
    display: flex;
    gap: 0.75rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    padding: 0.7rem 1.6rem;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-family: 'Inter', system-ui, sans-serif;
    text-decoration: none;
}

.btn--primary {
    background: linear-gradient(135deg, #C81E3A, #A6152D);
    color: #fff;
    box-shadow: 0 4px 16px rgba(200, 30, 58, 0.25);
}

.btn--primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(200, 30, 58, 0.35);
}

.voucher-wrapper {
    max-width: 1100px;
    margin: 0 auto;
}

.voucher-container {
    background: #f0f0f2;
    border-radius: 16px;
    padding: 1.5rem;
}

.voucher-page {
    font-family: 'Inter', system-ui, sans-serif;
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
}

.voucher {
    padding: 2.5rem 3rem;
}

/* ========================================================================
   HEADER - Logo gigante a la izquierda con tagline debajo
   ======================================================================== */
.voucher__header {
    display: flex;
    justify-content: space-between;
    align-items: stretch;
    gap: 3rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #f0f0f2;
    min-height: 200px;
}

.voucher__brand {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    gap: 0.5rem;
    flex-shrink: 0;
}

.voucher__logo {
    width: 220px;
    height: 220px;
    object-fit: contain;
    display: block;
    background: transparent;
}

.tagline {
    font-size: 0.8rem;
    letter-spacing: 0.15em;
    color: #C81E3A;
    font-weight: 700;
    text-align: left;
}

.voucher__title {
    text-align: right;
    display: flex;
    flex-direction: column;
    justify-content: center;
    flex: 1;
}

.voucher__badge {
    display: inline-block;
    background: #22c55e;
    color: #fff;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.3rem 1.2rem;
    border-radius: 50px;
    letter-spacing: 0.06em;
    margin-bottom: 0.5rem;
    align-self: flex-end;
}

.voucher__title h1 {
    font-family: Georgia, serif;
    font-size: 2.2rem;
    letter-spacing: 0.03em;
    margin: 0 0 0.3rem;
    color: #1f2024;
}

.voucher__title p {
    font-size: 0.95rem;
    color: #6b6b70;
    margin: 0;
}

/* ========================================================================
   EVENT CARD
   ======================================================================== */
.event-card {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    border: 1px solid #ececee;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 1.75rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.event-card__image {
    min-height: 280px;
}

.event-card__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.event-card__body {
    padding: 1.5rem 2rem;
    background: #fafafa;
}

.event-card__body h2 {
    font-size: 1.5rem;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    color: #1f2024;
}

.vip-tag {
    background: #C81E3A;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.2rem 0.8rem;
    border-radius: 4px;
    letter-spacing: 0.03em;
}

.event-card__meta {
    list-style: none;
    margin: 0 0 1rem;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.event-card__meta li {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    font-size: 0.9rem;
    color: #2a2a2e;
}

.event-card__meta i {
    color: #C81E3A;
    width: 20px;
    font-size: 1rem;
}

.event-card__meta strong {
    color: #1f2024;
}

.event-card hr {
    border: none;
    border-top: 1px solid #e8e8ea;
    margin: 0.75rem 0;
}

.event-card__desc {
    font-size: 0.88rem;
    color: #55555a;
    line-height: 1.6;
    margin: 0;
}

@media (max-width: 640px) {
    .event-card {
        grid-template-columns: 1fr;
    }

    .event-card__body {
        padding: 1.2rem;
    }
}

/* ========================================================================
   DETAILS GRID
   ======================================================================== */
.details-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.75rem;
}

.detail-card {
    border: 1px solid #ececee;
    border-radius: 12px;
    padding: 1.5rem 2rem;
    background: #fafafa;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.detail-card h3 {
    font-size: 0.85rem;
    letter-spacing: 0.06em;
    color: #C81E3A;
    margin: 0 0 1.2rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #C81E3A;
    display: inline-block;
    font-weight: 700;
}

.detail-card dl {
    margin: 0;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 0.6rem 0;
    font-size: 0.88rem;
    border-bottom: 1px solid #f0f0f0;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row dt {
    color: #6b6b70;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 400;
}

.detail-row dt i {
    color: #C81E3A;
    font-size: 0.85rem;
}

.detail-row dd {
    margin: 0;
    font-weight: 600;
    color: #1f2024;
}

.badge-acceso {
    background: #fef3c7;
    color: #d97706;
    padding: 0.15rem 0.8rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.03em;
}

.payment-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
    color: #6b6b70;
    padding: 0.5rem 0;
}

.payment-row strong {
    color: #1f2024;
    font-weight: 700;
}

.detail-card hr {
    border: none;
    border-top: 1px dashed #d8d8dc;
    margin: 0.3rem 0;
}

.payment-row--total {
    padding-top: 0.75rem;
}

.payment-row--total span {
    font-weight: 700;
    font-size: 0.9rem;
    color: #1f2024;
}

.payment-row--total strong {
    color: #C81E3A;
    font-size: 1.8rem;
}

.payment-row--total small {
    font-size: 0.9rem;
}

@media (max-width: 640px) {
    .details-grid {
        grid-template-columns: 1fr;
    }

    .detail-card {
        padding: 1.2rem;
    }
}

/* ========================================================================
   QR SECTION
   ======================================================================== */
.qr-section {
    background: linear-gradient(135deg, #fafafa, #f5f5f7);
    border: 2px solid #ececee;
    border-radius: 16px;
    padding: 2rem 2.5rem;
    margin-bottom: 0;
}

.qr-container {
    display: flex;
    align-items: center;
    gap: 3rem;
    justify-content: center;
}

.qr-code-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.qr-code {
    width: 250px;
    height: 250px;
    border: 4px solid #fff;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    background: #fff;
    padding: 12px;
}

.qr-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: #C81E3A;
}

.qr-info {
    flex: 1;
}

.qr-info h3 {
    font-size: 1.2rem;
    margin: 0 0 0.5rem;
    color: #1f2024;
}

.qr-info p {
    font-size: 0.9rem;
    color: #6b6b70;
    margin: 0 0 1rem;
}

.qr-hint {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: #fff;
    padding: 0.75rem 1.2rem;
    border-radius: 10px;
    border: 1px solid #e8e8ea;
    margin-bottom: 1rem;
}

.qr-hint i {
    color: #C81E3A;
    font-size: 1.2rem;
}

.qr-hint span {
    font-size: 0.82rem;
    color: #55555a;
}

.qr-folio {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.5rem 0;
    border-top: 1px solid #e8e8ea;
    margin-bottom: 0.75rem;
}

.folio-label {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    color: #6b6b70;
}

.folio-value {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1f2024;
    font-family: 'Courier New', monospace;
    letter-spacing: 0.03em;
}

.qr-direccion {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: #fff;
    padding: 0.6rem 1.2rem;
    border-radius: 10px;
    border: 1px solid #e8e8ea;
}

.qr-direccion i {
    color: #C81E3A;
    font-size: 1rem;
}

.qr-direccion span {
    font-size: 0.85rem;
    color: #1f2024;
    font-weight: 500;
}

@media (max-width: 700px) {
    .qr-container {
        flex-direction: column;
        text-align: center;
    }

    .qr-code {
        width: 200px;
        height: 200px;
    }

    .qr-folio {
        justify-content: center;
    }

    .qr-direccion {
        justify-content: center;
        text-align: left;
    }

    .qr-hint {
        justify-content: center;
        text-align: left;
    }
}

/* =========================================================================
   RESPONSIVE GENERAL
   ========================================================================= */
@media (max-width: 768px) {
    .comprobante-page {
        padding: 0.5rem 1rem 2rem;
    }

    .voucher {
        padding: 1.5rem;
    }

    .voucher-container {
        padding: 0.5rem;
    }

    .voucher__header {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 1rem;
        min-height: auto;
    }

    .voucher__brand {
        align-items: center;
    }

    .voucher__title {
        text-align: center;
    }

    .voucher__badge {
        align-self: center;
    }

    .tagline {
        text-align: center;
    }

    .voucher__logo {
        width: 160px;
        height: 160px;
    }

    .voucher__title h1 {
        font-size: 1.6rem;
    }

    .top-nav {
        flex-direction: column;
        align-items: stretch;
    }

    .top-nav-actions {
        width: 100%;
    }

    .top-nav-actions .btn {
        width: 100%;
        justify-content: center;
    }

    .btn-back {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .comprobante-page {
        padding: 0.25rem 0.5rem 1rem;
    }

    .voucher {
        padding: 1rem;
    }

    .voucher__title h1 {
        font-size: 1.3rem;
    }

    .voucher__logo {
        width: 120px;
        height: 120px;
    }

    .qr-code {
        width: 160px;
        height: 160px;
    }

    .voucher__title p {
        font-size: 0.8rem;
    }
}

@media print {
    .no-print {
        display: none !important;
    }

    .comprobante-page {
        background: #fff;
        padding: 0;
    }

    .voucher-container {
        background: #fff;
        padding: 0;
        border-radius: 0;
    }

    .voucher-page {
        border-radius: 0;
    }

    .voucher {
        border: none;
        padding: 1.5rem;
    }

    .qr-section {
        background: #fff;
        border: 1px solid #ddd;
    }

    .qr-code {
        border: 2px solid #ddd;
    }

    .voucher__header {
        border-bottom: 2px solid #ddd;
    }

    .voucher__logo {
        width: 180px;
        height: 180px;
    }

    .event-card {
        box-shadow: none;
    }

    .detail-card {
        box-shadow: none;
        background: #fff;
    }

    .qr-section {
        background: #fff;
    }
}
</style>