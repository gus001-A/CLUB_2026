<template>

    <Head :title="'Reservar - ' + evento.titulo" />

    <AppLayout active-nav="eventos">
        <div class="reserva-unificada-page">
            <!-- ============================================================ -->
            <!-- HEADER -->
            <!-- ============================================================ -->
            <div class="page-header">
                <button class="btn-back" @click="volverAlEvento">
                    <i class="pi pi-arrow-left"></i>
                    <span>Volver al evento</span>
                </button>
                <div class="page-header__badges">
                    <span class="badge badge--secure">
                        <i class="pi pi-lock"></i> Reserva segura
                    </span>
                    <span v-if="eventoData.destacado" class="badge badge--featured">
                        <i class="pi pi-star-fill"></i> Destacado
                    </span>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- EVENTO HERO -->
            <!-- ============================================================ -->
            <div class="event-hero-enhanced">
                <div class="event-hero-enhanced__grid">
                    <div class="event-hero-enhanced__image">
                        <img :src="getImageUrl(eventoData.imagen)" :alt="eventoData.titulo" />
                        <div class="event-hero-enhanced__tags">
                            <span class="tag tag--vip" v-if="eventoData.tipo === 'vip'">
                                <i class="pi pi-crown"></i> VIP
                            </span>
                            <span class="tag tag--available" v-else-if="eventoData.lugaresDisponibles > 10">
                                <i class="pi pi-check-circle"></i> Disponible
                            </span>
                            <span class="tag tag--limited" v-else-if="eventoData.lugaresDisponibles > 0">
                                <i class="pi pi-exclamation-triangle"></i> Últimos lugares
                            </span>
                            <span class="tag tag--soldout" v-else>
                                <i class="pi pi-times-circle"></i> Agotado
                            </span>
                        </div>
                    </div>

                    <div class="event-hero-enhanced__info">
                        <span class="event-badge">{{ eventoData.categoria || 'Experiencia exclusiva' }}</span>
                        <h1>{{ eventoData.titulo }}</h1>
                        <p class="event-description">{{ eventoData.descripcion }}</p>

                        <div class="event-meta-grid">
                            <div class="meta-item">
                                <span class="meta-icon"><i class="pi pi-calendar"></i></span>
                                <div>
                                    <span class="meta-label">Fecha</span>
                                    <span class="meta-value">{{ eventoData.fecha }}</span>
                                </div>
                            </div>
                            <div class="meta-item">
                                <span class="meta-icon"><i class="pi pi-clock"></i></span>
                                <div>
                                    <span class="meta-label">Hora</span>
                                    <span class="meta-value">{{ eventoData.hora }}</span>
                                </div>
                            </div>
                            <div class="meta-item">
                                <span class="meta-icon"><i class="pi pi-map-marker"></i></span>
                                <div>
                                    <span class="meta-label">Ubicación</span>
                                    <span class="meta-value">{{ eventoData.ciudad }}</span>
                                </div>
                            </div>
                            <div class="meta-item">
                                <span class="meta-icon"><i class="pi pi-tag"></i></span>
                                <div>
                                    <span class="meta-label">Código de vestimenta</span>
                                    <span class="meta-value">{{ eventoData.codigo_vestimenta }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="event-includes-enhanced">
                            <div class="event-includes-enhanced__header">
                                <i class="pi pi-check-circle" style="color: var(--green);"></i>
                                <span>Qué incluye esta experiencia</span>
                            </div>
                            <div class="event-includes-enhanced__grid">
                                <div v-for="item in incluyeItems" :key="item.texto"
                                    class="event-includes-enhanced__item">
                                    <span class="event-includes-enhanced__icon">
                                        <i :class="item.icon"></i>
                                    </span>
                                    <span class="event-includes-enhanced__text">{{ item.texto }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="event-availability">
                            <div class="availability-bar">
                                <div class="availability-bar__fill" :style="{ width: porcentajeDisponible + '%' }">
                                </div>
                            </div>
                            <div class="availability-text">
                                <span class="availability-number">{{ eventoData.lugaresDisponibles }}</span>
                                <span class="availability-label">lugares disponibles de {{ eventoData.lugaresTotales
                                    }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- BARRA DE NAVEGACIÓN (2 PASOS) -->
            <!-- ============================================================ -->
            <div class="steps-navigation">
                <div v-for="(paso, index) in pasos" :key="paso.id" class="step-nav" :class="{
                    'step-nav--active': pasoActivo === paso.id,
                    'step-nav--completed': paso.id < pasoActivo,
                    'step-nav--clickable': paso.id < pasoActivo
                }" @click="paso.id < pasoActivo && irAlPaso(paso.id)">
                    <div class="step-nav__circle">
                        <span v-if="paso.id < pasoActivo" class="step-nav__check">
                            <i class="pi pi-check"></i>
                        </span>
                        <span v-else class="step-nav__number">{{ index + 1 }}</span>
                    </div>
                    <div class="step-nav__content">
                        <span class="step-nav__label">{{ paso.label }}</span>
                        <span class="step-nav__sub">{{ paso.sub }}</span>
                    </div>
                    <div v-if="index < pasos.length - 1" class="step-nav__connector">
                        <div class="connector-line" :class="{ 'connector-line--active': paso.id <= pasoActivo }"></div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- PASO 1: DETALLES DE RESERVA -->
            <!-- ============================================================ -->
            <section v-show="pasoActivo === 1" class="content-grid">
                <div class="main-column">
                    <div class="panel panel--step1">
                        <div class="panel__header">
                            <div class="panel__header-icon">
                                <i class="pi pi-file"></i>
                            </div>
                            <div>
                                <span class="panel__step">Paso 1 de 2</span>
                                <h2>Detalles de tu reserva</h2>
                                <p class="panel__sub">Completa la información para asegurar tu lugar</p>
                            </div>
                        </div>

                        <div class="panel__body">
                            <div class="field-row">
                                <div class="field field--stepper">
                                    <label><i class="pi pi-users"></i> Número de asistentes</label>
                                    <div class="stepper" :class="{ 'is-animating': contadorAnimacion }">
                                        <button @click="restarAsistentes" :disabled="form.num_asistentes <= 1"
                                            class="stepper__btn stepper__btn--minus">
                                            <i class="pi pi-minus"></i>
                                        </button>
                                        <span class="stepper__value">{{ form.num_asistentes }}</span>
                                        <button @click="sumarAsistentes"
                                            :disabled="form.num_asistentes >= eventoData.lugaresDisponibles || form.num_asistentes >= (config.max_asistentes || 10)"
                                            class="stepper__btn stepper__btn--plus">
                                            <i class="pi pi-plus"></i>
                                        </button>
                                    </div>
                                    <small>Máximo {{ Math.min(eventoData.lugaresDisponibles, config.max_asistentes ||
                                        10) }} personas</small>
                                </div>

                                <div class="field">
                                    <label><i class="pi pi-ticket"></i> Tipo de acceso</label>
                                    <div class="access-grid">
                                        <button type="button" class="access-option access-option--vip"
                                            :class="{ 'is-selected': form.tipo_acceso === 'vip' }"
                                            @click="form.tipo_acceso = 'vip'">
                                            <i class="pi pi-crown"></i>
                                            <span>
                                                <strong>VIP</strong>
                                                <em>Experiencia premium</em>
                                            </span>
                                            <i class="pi pi-check-circle access-option__check"
                                                v-if="form.tipo_acceso === 'vip'"></i>
                                        </button>
                                        <button type="button" class="access-option access-option--general"
                                            :class="{ 'is-selected': form.tipo_acceso === 'general' }"
                                            @click="form.tipo_acceso = 'general'">
                                            <i class="pi pi-users"></i>
                                            <span>
                                                <strong>General</strong>
                                                <em>Acceso estándar</em>
                                            </span>
                                            <i class="pi pi-check-circle access-option__check"
                                                v-if="form.tipo_acceso === 'general'"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="divider">
                                <span><i class="pi pi-user"></i> Titular de la reserva</span>
                            </div>

                            <div class="field-row">
                                <div class="field">
                                    <label>Nombre completo <em class="req">*</em></label>
                                    <div class="input input--red">
                                        <i class="pi pi-user" style="color: #C81E3A;"></i>
                                        <input v-model="form.titular.nombre" type="text" placeholder="Tu nombre" />
                                    </div>
                                </div>
                                <div class="field">
                                    <label>Correo electrónico <em class="req">*</em></label>
                                    <div class="input input--blue">
                                        <i class="pi pi-envelope" style="color: #2563EB;"></i>
                                        <input v-model="form.titular.email" type="email"
                                            placeholder="tucorreo@ejemplo.com" />
                                    </div>
                                </div>
                            </div>

                            <div class="field-row">
                                <div class="field">
                                    <label>Teléfono <em class="req">*</em></label>
                                    <div class="input input--green">
                                        <i class="pi pi-phone" style="color: #16A34A;"></i>
                                        <input v-model="form.titular.telefono" type="tel" placeholder="55 1234 5678" />
                                    </div>
                                </div>
                                <div class="field">
                                    <label><i class="pi pi-comment"></i> Comentarios <em
                                            class="opt">(opcional)</em></label>
                                    <div class="input input--purple">
                                        <i class="pi pi-align-left" style="color: #7C3AED;"></i>
                                        <input v-model="form.comentarios" type="text" maxlength="comentariosMax"
                                            placeholder="¿Alguna petición especial?" />
                                    </div>
                                    <span class="char-count">{{ form.comentarios.length }}/{{ comentariosMax }}</span>
                                </div>
                            </div>

                            <div v-if="form.num_asistentes > 1" class="guests">
                                <div class="divider">
                                    <span><i class="pi pi-user-plus"></i> Acompañantes <b class="count-pill">{{
                                            form.num_asistentes - 1 }}</b></span>
                                </div>

                                <div v-for="(acomp, index) in form.acompanantes" :key="index" class="guest-card">
                                    <div class="guest-card__header">
                                        <span class="guest-card__number">{{ index + 1 }}</span>
                                        <span>Acompañante {{ index + 1 }}</span>
                                    </div>
                                    <div class="field-row">
                                        <div class="field">
                                            <label>Nombre completo <em class="req">*</em></label>
                                            <div class="input input--pink">
                                                <i class="pi pi-user" style="color: #DB2777;"></i>
                                                <input v-model="acomp.nombre" type="text"
                                                    :placeholder="'Nombre del acompañante ' + (index + 1)" />
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label>Correo <em class="req">*</em></label>
                                            <div class="input input--pink">
                                                <i class="pi pi-envelope" style="color: #DB2777;"></i>
                                                <input v-model="acomp.email" type="email"
                                                    :placeholder="'email' + (index + 1) + '@ejemplo.com'" />
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label>Teléfono <em class="req">*</em></label>
                                            <div class="input input--pink">
                                                <i class="pi pi-phone" style="color: #DB2777;"></i>
                                                <input v-model="acomp.telefono" type="tel"
                                                    :placeholder="'55 8765 432' + (index + 1)" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="consent-actions-row">
                                <div class="consent-section">
                                    <label class="consent consent--red">
                                        <input type="checkbox" v-model="form.terminos" />
                                        <span class="consent__box"></span>
                                        <span>Acepto los <Link href="/terminos" class="consent__link">Términos y
                                                Condiciones</Link></span>
                                    </label>

                                    <label class="consent consent--gold">
                                        <input type="checkbox" v-model="form.reglasEvento" />
                                        <span class="consent__box"></span>
                                        <span>Acepto las <a href="#" class="consent__link">Reglas del evento</a></span>
                                    </label>
                                </div>

                                <div class="actions-section">
                                    <button class="btn btn--primary btn--large" @click="siguientePaso"
                                        :disabled="!puedeContinuar || isSubmitting">
                                        <span v-if="isSubmitting" class="btn__loading">
                                            <i class="pi pi-spin pi-spinner"></i> Procesando...
                                        </span>
                                        <span v-else>
                                            Continuar al pago <i class="pi pi-arrow-right"></i>
                                        </span>
                                    </button>
                                    <p class="cta-note">
                                        <i class="pi pi-shield" style="color: #16A34A;"></i>
                                        No se realizará ningún cargo hasta confirmar el pago
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="sidebar">
                    <div class="resume-card">
                        <div class="resume-card__header">
                            <span class="resume-card__title">
                                <i class="pi pi-receipt"></i> Resumen
                            </span>
                            <span class="resume-card__badge">Pendiente</span>
                        </div>

                        <div class="resume-card__event">
                            <img :src="getImageUrl(eventoData.imagen)" :alt="eventoData.titulo" />
                            <div>
                                <strong>{{ eventoData.titulo }}</strong>
                                <span class="event-type" v-if="eventoData.tipo === 'vip'">VIP</span>
                                <ul>
                                    <li><i class="pi pi-calendar"></i> {{ eventoData.fecha }}</li>
                                    <li><i class="pi pi-clock"></i> {{ eventoData.hora }}</li>
                                    <li><i class="pi pi-map-marker"></i> {{ eventoData.ciudad }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="resume-card__details">
                            <div class="detail-row">
                                <span><i class="pi pi-users"></i> Asistentes</span>
                                <strong>{{ form.num_asistentes }}</strong>
                            </div>
                            <div class="detail-row">
                                <span><i class="pi pi-ticket"></i> Tipo</span>
                                <strong>{{ form.tipo_acceso.toUpperCase() }}</strong>
                            </div>
                            <div class="detail-row">
                                <span><i class="pi pi-tag"></i> Precio por persona</span>
                                <strong>${{ formatoMoneda(precioPorPersona) }}</strong>
                            </div>
                        </div>

                        <div class="resume-card__divider"></div>

                        <div class="resume-card__totals">
                            <div class="total-row">
                                <span>Subtotal</span>
                                <span>${{ formatoMoneda(subtotal) }}</span>
                            </div>
                            <div class="total-row">
                                <span>Cargo por servicio</span>
                                <span>${{ formatoMoneda(cargoServicio) }}</span>
                            </div>
                            <div class="total-row total-row--grand">
                                <span>Total</span>
                                <strong>${{ formatoMoneda(total) }}</strong>
                            </div>
                        </div>

                        <div class="resume-card__availability">
                            <div class="availability-bar">
                                <div class="availability-bar__fill" :style="{ width: porcentajeDisponible + '%' }">
                                </div>
                            </div>
                            <span>{{ eventoData.lugaresDisponibles }} de {{ eventoData.lugaresTotales }} lugares
                                disponibles</span>
                        </div>
                    </div>

                    <div class="info-card info-card--privacy">
                        <div class="privacy-icon">
                            <i class="pi pi-shield"></i>
                        </div>
                        <div>
                            <strong>Privacidad garantizada</strong>
                            <p>Tus datos están protegidos. Solo los miembros confirmados acceden a los detalles del
                                evento.</p>
                        </div>
                    </div>
                </aside>
            </section>

            <!-- ============================================================ -->
            <!-- PASO 2: PAGO -->
            <!-- ============================================================ -->
            <section v-show="pasoActivo === 2" class="content-grid">
                <div class="main-column">
                    <div class="panel panel--step2">
                        <div class="panel__header">
                            <div class="panel__header-icon">
                                <i class="pi pi-credit-card"></i>
                            </div>
                            <div>
                                <span class="panel__step">Paso 2 de 2</span>
                                <h2>Datos de pago</h2>
                                <p class="panel__sub">Selecciona tu método de pago preferido</p>
                            </div>
                        </div>

                        <div class="panel__body">
                            <div class="payment-methods">
                                <label class="payment-methods__label">Método de pago</label>
                                <div class="payment-methods__grid">
                                    <button class="payment-method"
                                        :class="{ 'payment-method--active': metodoPago === 'tarjeta' }"
                                        @click="metodoPago = 'tarjeta'">
                                        <i class="pi pi-credit-card"></i>
                                        <span>Tarjeta</span>
                                    </button>
                                    <button class="payment-method"
                                        :class="{ 'payment-method--active': metodoPago === 'oxxo' }"
                                        @click="metodoPago = 'oxxo'">
                                        <i class="pi pi-receipt"></i>
                                        <span>OXXO</span>
                                    </button>
                                </div>
                                <div class="payment-methods__coming">
                                    <i class="pi pi-info-circle"></i>
                                    <span>Próximamente pagos con <strong>Mercado Pago</strong></span>
                                </div>
                            </div>

                            <div v-if="metodoPago === 'tarjeta'" class="card-data">
                                <label class="card-data__label">Datos de la tarjeta</label>
                                <div class="card-data__grid">
                                    <div class="form-group form-group--full">
                                        <label style="color: #2563EB;">Número de tarjeta</label>
                                        <div class="input-modern input--blue">
                                            <i class="pi pi-credit-card" style="color: #2563EB;"></i>
                                            <input v-model="pagoForm.numero_tarjeta" type="text"
                                                placeholder="1234 1234 1234 1234" maxlength="19"
                                                @input="formatearNumeroTarjeta" />
                                            <span class="card-type" v-if="tipoTarjeta !== 'unknown'">
                                                {{ tipoTarjeta.toUpperCase() }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group form-group--full">
                                        <label style="color: #7C3AED;">Nombre en la tarjeta</label>
                                        <div class="input-modern input--purple">
                                            <i class="pi pi-user" style="color: #7C3AED;"></i>
                                            <input v-model="pagoForm.nombre_tarjeta" type="text"
                                                placeholder="Nombre del titular" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label style="color: #D97706;">Fecha expiración</label>
                                        <div class="input-modern input--gold">
                                            <i class="pi pi-calendar" style="color: #D97706;"></i>
                                            <input v-model="pagoForm.expiracion" type="text" placeholder="MM/AA"
                                                maxlength="5" @input="formatearExpiracion" />
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label style="color: #C81E3A;">CVV</label>
                                        <div class="input-modern input--red">
                                            <i class="pi pi-lock" style="color: #C81E3A;"></i>
                                            <input v-model="pagoForm.cvv" type="text" placeholder="123" maxlength="4"
                                                @input="formatearCVV" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else-if="metodoPago === 'oxxo'" class="payment-info payment-info--oxxo">
                                <i class="pi pi-info-circle"></i>
                                <div>
                                    <strong>Pago en OXXO</strong>
                                    <p>Recibirás un correo con el código de barras para realizar tu pago en cualquier
                                        sucursal OXXO.</p>
                                </div>
                            </div>

                            <div class="secure-banner">
                                <i class="pi pi-lock" style="color: #2563EB;"></i>
                                <div>
                                    <strong style="color: #2563EB;">Pago 100% seguro</strong>
                                    <span>Tus datos están protegidos con cifrado SSL de 256 bits</span>
                                </div>
                            </div>

                            <div class="form-actions form-actions--dual">
                                <button class="btn btn--secondary" @click="pasoAnterior" type="button">
                                    <i class="pi pi-arrow-left"></i> Anterior
                                </button>
                                <button class="btn btn--primary btn--large btn--pulse" @click="procesarPago"
                                    :disabled="isSubmitting || (metodoPago === 'tarjeta' && !puedePagar)" type="button">
                                    <span v-if="isSubmitting" class="btn__loading">
                                        <i class="pi pi-spin pi-spinner"></i> Procesando...
                                    </span>
                                    <span v-else>
                                        <i class="pi pi-lock"></i> Pagar ahora
                                        <span class="btn__price">${{ formatoMoneda(total) }}</span>
                                    </span>
                                </button>
                            </div>
                            <p class="cta-note">
                                <i class="pi pi-shield" style="color: #16A34A;"></i>
                                Al hacer clic en "Pagar ahora" confirmas tu reserva
                            </p>
                        </div>
                    </div>
                </div>

                <aside class="sidebar">
                    <div class="resume-card">
                        <div class="resume-card__header">
                            <span class="resume-card__title">
                                <i class="pi pi-receipt"></i> Resumen
                            </span>
                            <span class="resume-card__badge resume-card__badge--pay">Pago pendiente</span>
                        </div>

                        <div class="resume-card__event">
                            <img :src="getImageUrl(eventoData.imagen)" :alt="eventoData.titulo" />
                            <div>
                                <strong>{{ eventoData.titulo }}</strong>
                                <span class="event-type" v-if="eventoData.tipo === 'vip'">VIP</span>
                                <ul>
                                    <li><i class="pi pi-calendar"></i> {{ eventoData.fecha }}</li>
                                    <li><i class="pi pi-clock"></i> {{ eventoData.hora }}</li>
                                    <li><i class="pi pi-users"></i> {{ form.num_asistentes }} persona{{
                                        form.num_asistentes > 1 ? 's' : '' }}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="resume-card__divider"></div>

                        <div class="resume-card__totals">
                            <div class="total-row">
                                <span>Precio por persona</span>
                                <span>${{ formatoMoneda(precioPorPersona) }}</span>
                            </div>
                            <div class="total-row">
                                <span>Asistentes</span>
                                <span>{{ form.num_asistentes }}</span>
                            </div>
                            <div class="total-row">
                                <span>Subtotal</span>
                                <span>${{ formatoMoneda(subtotal) }}</span>
                            </div>
                            <div class="total-row">
                                <span>Cargo por servicio</span>
                                <span>${{ formatoMoneda(cargoServicio) }}</span>
                            </div>
                            <div class="total-row total-row--grand">
                                <span>Total</span>
                                <strong>${{ formatoMoneda(total) }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="info-card info-card--privacy">
                        <div class="privacy-icon">
                            <i class="pi pi-shield"></i>
                        </div>
                        <div>
                            <strong>Privacidad garantizada</strong>
                            <p>Tus datos están protegidos. Solo los miembros confirmados acceden a los detalles del
                                evento.</p>
                        </div>
                    </div>
                </aside>
            </section>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { Head, usePage, router, Link } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    evento: {
        type: Object,
        required: true
    },
    usuario: {
        type: Object,
        required: true
    },
    config: {
        type: Object,
        default: () => ({
            max_asistentes: 10,
            min_asistentes: 1,
            tipos_acceso: ['vip', 'general'],
            metodos_pago: ['tarjeta', 'oxxo', 'paypal'],
            cargo_servicio: 0
        })
    }
});

const toast = useToast();
const page = usePage();

// ---------- QUÉ INCLUYE ----------
const incluyeItems = [
    { icon: 'pi-glass', texto: 'Cóctel de bienvenida' },
    { icon: 'pi-palette', texto: 'Ambientación premium' },
    { icon: 'pi-home', texto: 'Áreas privadas y lounge' },
    { icon: 'pi-volume-up', texto: 'Música en vivo & DJ' },
    { icon: 'pi-shield', texto: 'Seguridad y discreción' },
    { icon: 'pi-users', texto: 'Networking selecto' },
    { icon: 'pi-car', texto: 'Estacionamiento VIP' },
    { icon: 'pi-camera', texto: 'Fotógrafo profesional' },
    { icon: 'pi-wine', texto: 'Barra libre premium' },
];

// ---------- PASOS (SOLO 2) ----------
const pasos = [
    { id: 1, label: 'Reservar', sub: 'Detalles de tu reserva' },
    { id: 2, label: 'Pagar', sub: 'Método de pago' },
];
const pasoActivo = ref(1);
const isSubmitting = ref(false);
const metodoPago = ref('tarjeta');

// ---------- EVENTO DATA ----------
const eventoData = computed(() => {
    let fechaFormateada = props.evento.fecha || 'Fecha por confirmar';

    if (props.evento.fecha_completa) {
        try {
            const fechaObj = new Date(props.evento.fecha_completa + 'T' + (props.evento.hora_completa || '00:00'));
            if (!isNaN(fechaObj.getTime())) {
                const opciones = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                fechaFormateada = fechaObj.toLocaleDateString('es-ES', opciones);
                fechaFormateada = fechaFormateada.charAt(0).toUpperCase() + fechaFormateada.slice(1);
            }
        } catch (e) {
            console.warn('Error formateando fecha:', e);
        }
    }

    return {
        id: props.evento.id,
        titulo: props.evento.titulo,
        descripcion: props.evento.descripcion || 'Descripción del evento',
        imagen: props.evento.imagen || '/images/eventos/default-hero.jpg',
        fecha: fechaFormateada,
        fecha_completa: props.evento.fecha_completa || 'Fecha por confirmar',
        hora: props.evento.hora || 'Horario por definir',
        hora_completa: props.evento.hora_completa || '23:00',
        ciudad: props.evento.ciudad || 'Ciudad de México',
        ubicacion: props.evento.ubicacion || 'Ubicación privada',
        lugaresDisponibles: props.evento.lugaresDisponibles || 0,
        lugaresTotales: props.evento.lugaresTotales || 50,
        precio: props.evento.precio || 0,
        cargoServicio: props.evento.cargoServicio || 0,
        moneda: props.evento.moneda || 'MXN',
        tipo: props.evento.tipo || 'social',
        categoria: props.evento.categoria || 'experiencia',
        codigo_vestimenta: props.evento.codigo_vestimenta || 'Smart casual / Formal',
        incluye: props.evento.incluye || [],
        destacado: props.evento.destacado || false,
    };
});

const porcentajeDisponible = computed(() => {
    const disponibles = eventoData.value.lugaresDisponibles;
    const totales = eventoData.value.lugaresTotales;
    return totales > 0 ? (disponibles / totales) * 100 : 0;
});

// ---------- FORMULARIO ----------
const form = reactive({
    num_asistentes: 1,
    tipo_acceso: 'vip',
    titular: {
        nombre: props.usuario.nombre || '',
        email: props.usuario.email || '',
        telefono: props.usuario.telefono || '',
    },
    acompanantes: [],
    comentarios: '',
    terminos: false,
    reglasEvento: false,
});

function generarAcompanantes() {
    const total = form.num_asistentes - 1;
    const actuales = form.acompanantes.length;

    if (total > actuales) {
        for (let i = actuales; i < total; i++) {
            form.acompanantes.push({
                nombre: '',
                email: '',
                telefono: '',
            });
        }
    } else if (total < actuales) {
        form.acompanantes = form.acompanantes.slice(0, total);
    }
}

watch(() => form.num_asistentes, () => {
    generarAcompanantes();
});

generarAcompanantes();

const comentariosMax = 200;

function restarAsistentes() {
    if (form.num_asistentes > 1) {
        form.num_asistentes--;
        animarContador();
    }
}

function sumarAsistentes() {
    const maxAsistentes = Math.min(eventoData.value.lugaresDisponibles, props.config.max_asistentes || 10);
    if (form.num_asistentes < maxAsistentes) {
        form.num_asistentes++;
        animarContador();
    }
}

const contadorAnimacion = ref(false);
function animarContador() {
    contadorAnimacion.value = true;
    setTimeout(() => {
        contadorAnimacion.value = false;
    }, 200);
}

// ---------- PAGO ----------
const pagoForm = reactive({
    numero_tarjeta: '4111 1111 1111 1111',
    nombre_tarjeta: 'Titular de Prueba',
    expiracion: '12/25',
    cvv: '123',
});

const tipoTarjeta = computed(() => {
    const num = pagoForm.numero_tarjeta.replace(/\s/g, '');
    if (num.startsWith('4')) return 'visa';
    if (num.startsWith('5')) return 'mastercard';
    if (num.startsWith('3')) return 'amex';
    return 'unknown';
});

const ultimosDigitos = computed(() => {
    const num = pagoForm.numero_tarjeta.replace(/\s/g, '');
    return num.slice(-4);
});

// ---------- VALIDACIONES ----------
const puedeContinuar = computed(() => {
    if (!form.terminos || !form.reglasEvento) return false;

    if (form.titular.nombre.trim() === '' ||
        form.titular.email.trim() === '' ||
        form.titular.telefono.trim() === '') return false;

    for (const acomp of form.acompanantes) {
        if (acomp.nombre.trim() === '' ||
            acomp.email.trim() === '' ||
            acomp.telefono.trim() === '') return false;
    }

    return true;
});

const puedePagar = computed(() => {
    if (metodoPago.value === 'tarjeta') {
        const num = pagoForm.numero_tarjeta.replace(/\s/g, '');
        return num.length >= 16 &&
            pagoForm.nombre_tarjeta.trim() !== '' &&
            pagoForm.expiracion.length === 5 &&
            pagoForm.cvv.length >= 3;
    }
    return true;
});

// ---------- CÁLCULOS ----------
const precioPorPersona = computed(() => eventoData.value.precio || 0);
const cargoServicio = computed(() => props.config.cargo_servicio || Math.round(precioPorPersona.value * 0.20));
const subtotal = computed(() => precioPorPersona.value * form.num_asistentes);
const total = computed(() => subtotal.value + cargoServicio.value);

function formatoMoneda(valor) {
    return new Intl.NumberFormat('es-MX').format(Math.round(valor));
}

// ---------- NAVEGACIÓN ----------
function irAlPaso(paso) {
    if (paso >= 1 && paso <= pasos.length) {
        pasoActivo.value = paso;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function pasoAnterior() {
    if (pasoActivo.value > 1) {
        irAlPaso(pasoActivo.value - 1);
    }
}

function siguientePaso() {
    if (!puedeContinuar.value) {
        toast.add({
            severity: 'warn',
            summary: '⚠️ Campos incompletos',
            detail: 'Por favor, completa todos los campos requeridos y acepta los términos.',
            life: 4000
        });
        return;
    }

    if (form.num_asistentes > eventoData.value.lugaresDisponibles) {
        toast.add({
            severity: 'error',
            summary: '❌ Cupo insuficiente',
            detail: 'Solo hay ' + eventoData.value.lugaresDisponibles + ' lugares disponibles.',
            life: 4000
        });
        return;
    }

    irAlPaso(2);
}

// ---------- MÉTODOS ----------
function getImageUrl(path) {
    if (!path) return '/images/eventos/default-hero.jpg';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    if (path.startsWith('/storage/') || path.startsWith('/images/')) return path;
    if (path.startsWith('storage/')) return '/' + path;
    return '/storage/' + path.replace(/^\/+/, '');
}

function volverAlEvento() {
    router.visit(route('eventos.show', eventoData.value.id));
}

function procesarPago() {
    if (metodoPago.value === 'tarjeta' && !puedePagar.value) {
        toast.add({
            severity: 'error',
            summary: 'Datos de pago incompletos',
            detail: 'Por favor, completa todos los datos de la tarjeta.',
            life: 4000
        });
        return;
    }

    isSubmitting.value = true;

    const datosPago = {
        evento_id: eventoData.value.id,
        num_asistentes: form.num_asistentes,
        tipo_acceso: form.tipo_acceso,
        titular_nombre: form.titular.nombre,
        titular_email: form.titular.email,
        titular_telefono: form.titular.telefono,
        acompanantes: JSON.stringify(form.acompanantes),
        comentarios: form.comentarios,
        metodo: metodoPago.value,
        numero_tarjeta: metodoPago.value === 'tarjeta' ? pagoForm.numero_tarjeta.replace(/\s/g, '') : null,
        nombre_tarjeta: metodoPago.value === 'tarjeta' ? pagoForm.nombre_tarjeta : null,
        expiracion: metodoPago.value === 'tarjeta' ? pagoForm.expiracion : null,
        cvv: metodoPago.value === 'tarjeta' ? pagoForm.cvv : null,
        total: total.value,
        subtotal: subtotal.value,
        cargo_servicio: cargoServicio.value,
        precio_unitario: precioPorPersona.value,
    };

    router.post(route('eventos.reserva.procesar-pago'), datosPago, {
        onSuccess: () => {
            isSubmitting.value = false;
            toast.add({
                severity: 'success',
                summary: '🎉 ¡Reserva confirmada!',
                detail: 'Tu reserva ha sido confirmada exitosamente.',
                life: 5000
            });
        },
        onError: (errors) => {
            isSubmitting.value = false;
            console.error('Errores:', errors);

            let mensajeError = 'Ocurrió un error al procesar el pago.';
            if (errors.message) {
                mensajeError = errors.message;
            } else if (errors.error) {
                mensajeError = errors.error;
            }

            toast.add({
                severity: 'error',
                summary: 'Error en el pago',
                detail: mensajeError,
                life: 5000
            });
        }
    });
}

function formatearNumeroTarjeta(event) {
    let value = event.target.value.replace(/\D/g, '');
    let formatted = '';
    for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
            formatted += ' ';
        }
        formatted += value[i];
    }
    event.target.value = formatted;
    pagoForm.numero_tarjeta = formatted;
}

function formatearExpiracion(event) {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2);
    }
    event.target.value = value.substring(0, 5);
    pagoForm.expiracion = value.substring(0, 5);
}

function formatearCVV(event) {
    let value = event.target.value.replace(/\D/g, '');
    event.target.value = value.substring(0, 4);
    pagoForm.cvv = value.substring(0, 4);
}
</script>

<style scoped>
/* =========================================================================
   TODOS LOS ESTILOS (MISMOS QUE ANTES PERO SIN LOS ESTILOS DEL VOUCHER)
   ========================================================================= */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500;600;700&display=swap');

.reserva-unificada-page {
    --brand: #C81E3A;
    --brand-dark: #A6152D;
    --brand-soft: #FBEAEC;
    --blue: #2563EB;
    --blue-soft: #EFF6FF;
    --green: #16A34A;
    --green-soft: #F0FDF4;
    --purple: #7C3AED;
    --purple-soft: #F5F3FF;
    --gold: #D97706;
    --gold-soft: #FFFBEB;
    --pink: #DB2777;
    --pink-soft: #FDF2F8;
    --ink: #1B160F;
    --ink-soft: #55503F;
    --paper: #F6F1E6;
    --card: #FFFFFF;
    --line: #E4DAC2;
    --muted: #918A76;
    --muted-light: #B7B2AF;
    --shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    --shadow-hover: 0 8px 32px rgba(0, 0, 0, 0.12);
    --radius: 16px;
    --radius-sm: 10px;
    --radius-full: 999px;
    --ease: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --font-sans: 'Inter', system-ui, sans-serif;

    font-family: var(--font-sans);
    color: var(--ink);
    background: #f0f2f5;
    min-height: 100vh;
    padding: 1.5rem 2rem 3rem;
    max-width: 1180px;
    margin: 0 auto;
}

/* =========================================================================
   HEADER
   ========================================================================= */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.btn-back {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    background: var(--card);
    border: 1px solid var(--line);
    padding: 0.5rem 1.2rem;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--ink-soft);
    cursor: pointer;
    transition: var(--ease);
    font-family: var(--font-sans);
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow);
}

.btn-back:hover {
    border-color: var(--brand);
    color: var(--brand);
    transform: translateX(-3px);
    box-shadow: var(--shadow-hover);
}

.page-header__badges {
    display: flex;
    gap: 0.6rem;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    padding: 0.4rem 1rem;
    border-radius: 50px;
}

.badge--secure {
    color: var(--blue);
    background: var(--blue-soft);
    border: 1px solid #BFDBFE;
}

.badge--featured {
    color: #78350F;
    background: var(--gold-soft);
    border: 1px solid var(--gold);
}

/* =========================================================================
   EVENT HERO
   ========================================================================= */
.event-hero-enhanced {
    background: var(--card);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow);
    border: 1px solid var(--line);
    margin-bottom: 1.5rem;
}

.event-hero-enhanced__grid {
    display: grid;
    grid-template-columns: 340px 1fr;
}

.event-hero-enhanced__image {
    position: relative;
    min-height: 280px;
    overflow: hidden;
}

.event-hero-enhanced__image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.event-hero-enhanced:hover .event-hero-enhanced__image img {
    transform: scale(1.05);
}

.event-hero-enhanced__tags {
    position: absolute;
    top: 1rem;
    left: 1rem;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.tag {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.6rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.25rem 0.7rem;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.tag--vip {
    background: linear-gradient(135deg, #FFD700, #FFA500);
    color: #7a5a00;
}

.tag--available {
    background: var(--green);
    color: #fff;
}

.tag--limited {
    background: var(--gold);
    color: #fff;
}

.tag--soldout {
    background: #DC2626;
    color: #fff;
}

.event-hero-enhanced__info {
    padding: 1.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.event-badge {
    display: inline-block;
    background: var(--brand-soft);
    color: var(--brand);
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0.2rem 0.7rem;
    border-radius: var(--radius-full);
    width: fit-content;
}

.event-hero-enhanced__info h1 {
    font-size: 1.5rem;
    font-weight: 800;
    margin: 0;
    line-height: 1.15;
}

.event-description {
    font-size: 0.88rem;
    color: var(--ink-soft);
    line-height: 1.6;
    margin: 0 0 0.5rem;
}

/* ============================================================ */
/* QUÉ INCLUYE */
/* ============================================================ */
.event-includes-enhanced {
    background: var(--paper);
    border-radius: var(--radius-sm);
    padding: 0.85rem 1.1rem 0.9rem;
    margin: 0.25rem 0 0.5rem;
    border: 1px solid var(--line);
}

.event-includes-enhanced__header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--ink-soft);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.5rem;
    border-bottom: 1px solid var(--line);
    padding-bottom: 0.4rem;
}

.event-includes-enhanced__header i {
    font-size: 0.85rem;
}

.event-includes-enhanced__grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.3rem 0.8rem;
}

.event-includes-enhanced__item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: var(--ink-soft);
    padding: 0.2rem 0;
}

.event-includes-enhanced__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--green-soft);
    color: var(--green);
    font-size: 0.55rem;
    flex-shrink: 0;
}

.event-includes-enhanced__text {
    font-weight: 400;
}

@media (max-width: 768px) {
    .event-includes-enhanced__grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .event-includes-enhanced__grid {
        grid-template-columns: 1fr;
    }
}

/* ============================================================ */
/* META GRID */
/* ============================================================ */
.event-meta-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-top: 0.25rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.4rem 0.7rem;
    background: var(--paper);
    border-radius: var(--radius-sm);
    border: 1px solid var(--line);
}

.meta-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--brand);
    font-size: 0.8rem;
    flex-shrink: 0;
}

.meta-item div {
    display: flex;
    flex-direction: column;
}

.meta-label {
    font-size: 0.55rem;
    text-transform: uppercase;
    color: var(--muted-light);
    font-weight: 600;
    letter-spacing: 0.04em;
}

.meta-value {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ink);
}

@media (max-width: 768px) {
    .event-hero-enhanced__grid {
        grid-template-columns: 1fr;
    }

    .event-hero-enhanced__image {
        min-height: 200px;
    }

    .event-meta-grid {
        grid-template-columns: 1fr;
    }
}

/* ============================================================ */
/* LUGARES DISPONIBLES */
/* ============================================================ */
.event-availability {
    margin-top: 0.25rem;
    padding: 0.5rem 0.75rem;
    background: var(--paper);
    border-radius: var(--radius-sm);
    border: 1px solid var(--line);
}

.availability-bar {
    height: 4px;
    background: var(--line);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 0.3rem;
}

.availability-bar__fill {
    height: 100%;
    background: linear-gradient(90deg, var(--brand), #F59E0B);
    border-radius: 4px;
    transition: width 0.8s ease;
}

.availability-text {
    display: flex;
    align-items: baseline;
    gap: 0.3rem;
}

.availability-number {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--ink);
}

.availability-label {
    font-size: 0.7rem;
    color: var(--muted);
}

/* =========================================================================
   STEPS NAVIGATION
   ========================================================================= */
.steps-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--card);
    border-radius: var(--radius);
    padding: 1.25rem 2rem;
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
    margin-bottom: 1.5rem;
    position: relative;
}

.step-nav {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    flex: 1;
    position: relative;
}

.step-nav__circle {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.9rem;
    flex-shrink: 0;
    transition: var(--ease);
    border: 3px solid var(--line);
    background: var(--white);
    color: var(--muted);
}

.step-nav--active .step-nav__circle {
    border-color: var(--brand);
    background: var(--brand);
    color: var(--white);
    box-shadow: 0 0 0 4px rgba(200, 30, 58, 0.15);
}

.step-nav--completed .step-nav__circle {
    border-color: #10B981;
    background: #10B981;
    color: var(--white);
}

.step-nav__check {
    font-size: 0.9rem;
}

.step-nav__number {
    font-weight: 700;
}

.step-nav__content {
    display: flex;
    flex-direction: column;
}

.step-nav__label {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--muted);
    transition: var(--ease);
}

.step-nav--active .step-nav__label {
    color: var(--brand);
}

.step-nav--completed .step-nav__label {
    color: #10B981;
}

.step-nav__sub {
    font-size: 0.7rem;
    color: var(--muted-light);
}

.step-nav--active .step-nav__sub {
    color: var(--muted);
}

.step-nav__connector {
    flex: 1;
    padding: 0 0.5rem;
    position: relative;
}

.connector-line {
    height: 2px;
    background: var(--line);
    border-radius: 2px;
    position: relative;
    transition: var(--ease);
}

.connector-line--active {
    background: linear-gradient(90deg, var(--brand), #10B981);
}

.step-nav--clickable {
    cursor: pointer;
}

.step-nav--clickable:hover .step-nav__circle {
    transform: scale(1.05);
    border-color: var(--brand);
}

.step-nav--clickable:hover .step-nav__label {
    color: var(--brand);
}

@media (max-width: 768px) {
    .steps-navigation {
        flex-direction: column;
        align-items: stretch;
        padding: 1rem;
        gap: 0.75rem;
    }

    .step-nav {
        gap: 0.6rem;
    }

    .step-nav__connector {
        display: none;
    }

    .step-nav__circle {
        width: 34px;
        height: 34px;
        font-size: 0.75rem;
    }

    .step-nav__sub {
        display: none;
    }
}

/* =========================================================================
   CONTENT GRID
   ========================================================================= */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

.main-column {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* =========================================================================
   PANEL
   ========================================================================= */
.panel {
    background: var(--card);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--line);
    overflow: hidden;
}

.panel__header {
    padding: 1.5rem 1.75rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border-bottom: 1px solid var(--line);
    background: var(--paper);
}

.panel__header-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--brand-soft);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.panel__step {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--muted-light);
}

.panel__header h2 {
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0;
}

.panel__sub {
    font-size: 0.8rem;
    color: var(--muted);
    margin: 0;
}

.panel__body {
    padding: 1.75rem;
}

/* =========================================================================
   FIELD ROW
   ========================================================================= */
.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}

@media (max-width: 600px) {
    .field-row {
        grid-template-columns: 1fr;
    }
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    margin-top: 0;
}

.field label {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink-soft);
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.field label i {
    color: var(--brand);
}

.req {
    color: var(--brand);
    font-style: normal;
}

.opt {
    font-weight: 400;
    color: var(--muted);
    font-style: normal;
}

.field small {
    font-size: 0.68rem;
    color: var(--muted);
}

.field--stepper {
    background: var(--paper);
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0.85rem 1.1rem;
}

.field--stepper label {
    color: var(--ink);
}

.char-count {
    align-self: flex-end;
    font-size: 0.65rem;
    color: var(--muted-light);
}

/* =========================================================================
   ACCESS OPTIONS
   ========================================================================= */
.access-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.access-option {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: var(--radius-sm);
    border: 2px solid var(--line);
    background: var(--card);
    cursor: pointer;
    transition: var(--ease);
    font-family: var(--font-sans);
    text-align: left;
    position: relative;
}

.access-option:hover {
    border-color: var(--brand);
    transform: translateY(-2px);
}

.access-option i:first-child {
    font-size: 1.1rem;
    color: var(--muted);
}

.access-option span {
    display: flex;
    flex-direction: column;
}

.access-option strong {
    font-size: 0.85rem;
}

.access-option em {
    font-size: 0.62rem;
    font-style: normal;
    color: var(--muted);
}

.access-option--vip.is-selected {
    border-color: var(--gold);
    background: var(--gold-soft);
    box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.12);
}

.access-option--vip.is-selected i:first-child {
    color: var(--gold);
}

.access-option--general.is-selected {
    border-color: var(--blue);
    background: var(--blue-soft);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.access-option--general.is-selected i:first-child {
    color: var(--blue);
}

.access-option__check {
    margin-left: auto;
    color: var(--brand) !important;
    font-size: 1.1rem !important;
}

@media (max-width: 500px) {
    .access-grid {
        grid-template-columns: 1fr;
    }
}

/* =========================================================================
   INPUTS
   ========================================================================= */
.input {
    display: flex;
    align-items: center;
    background: var(--paper);
    border: 2px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0 0.85rem;
    transition: var(--ease);
}

.input:focus-within {
    background: var(--card);
}

.input--red:focus-within {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.12);
}

.input--blue:focus-within {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.input--green:focus-within {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.12);
}

.input--purple:focus-within {
    border-color: var(--purple);
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.12);
}

.input--gold:focus-within {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.12);
}

.input--pink:focus-within {
    border-color: var(--pink);
    box-shadow: 0 0 0 3px rgba(219, 39, 119, 0.12);
}

.input i {
    font-size: 0.85rem;
    color: var(--muted);
    margin-right: 0.6rem;
}

.input input {
    border: none;
    background: transparent;
    padding: 0.7rem 0;
    font-size: 0.85rem;
    font-family: var(--font-sans);
    color: var(--ink);
    width: 100%;
    outline: none;
}

/* =========================================================================
   STEPPER
   ========================================================================= */
.stepper {
    display: inline-flex;
    align-items: center;
    background: var(--card);
    border-radius: var(--radius-sm);
    border: 2px solid var(--line);
    overflow: hidden;
    width: fit-content;
}

.stepper.is-animating .stepper__value {
    animation: bump 0.2s ease;
}

@keyframes bump {
    50% {
        transform: scale(1.2);
    }
}

.stepper__btn {
    width: 38px;
    height: 38px;
    border: none;
    background: transparent;
    cursor: pointer;
    color: var(--ink);
    font-size: 0.8rem;
    transition: var(--ease);
}

.stepper__btn--minus:hover:not(:disabled) {
    background: var(--brand-soft);
    color: var(--brand);
}

.stepper__btn--plus:hover:not(:disabled) {
    background: var(--green-soft);
    color: var(--green);
}

.stepper__btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.stepper__value {
    width: 38px;
    text-align: center;
    font-family: 'IBM Plex Mono', monospace;
    font-weight: 600;
    font-size: 1rem;
}

/* =========================================================================
   DIVIDER
   ========================================================================= */
.divider {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    margin: 1.75rem 0 0.25rem;
}

.divider span {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--line);
}

.count-pill {
    background: var(--brand);
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    padding: 0.05rem 0.5rem;
    border-radius: 50px;
}

/* =========================================================================
   GUEST CARDS
   ========================================================================= */
.guests {
    animation: reveal 0.3s ease;
}

@keyframes reveal {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.guest-card {
    background: var(--paper);
    border: 1px solid var(--line);
    border-left: 3px solid var(--pink);
    border-radius: var(--radius-sm);
    padding: 1rem;
    margin-top: 0.85rem;
}

.guest-card__header {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.6rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ink-soft);
}

.guest-card__number {
    background: var(--pink);
    color: #fff;
    font-size: 0.68rem;
    font-weight: 700;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* =========================================================================
   CONSENT + ACTIONS ROW
   ========================================================================= */
.consent-actions-row {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 2rem;
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--line);
    align-items: center;
}

@media (max-width: 768px) {
    .consent-actions-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}

.consent-section {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.consent {
    display: flex;
    align-items: flex-start;
    gap: 0.65rem;
    padding: 0.3rem 0.5rem;
    cursor: pointer;
    font-size: 0.8rem;
    color: var(--ink-soft);
    line-height: 1.4;
    border-radius: var(--radius-sm);
    transition: var(--ease);
}

.consent:hover {
    background: var(--paper);
}

.consent input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.consent__box {
    width: 20px;
    height: 20px;
    border-radius: 5px;
    border: 2px solid var(--line);
    background: var(--paper);
    flex-shrink: 0;
    margin-top: 0.05rem;
    position: relative;
    transition: var(--ease);
    display: flex;
    align-items: center;
    justify-content: center;
}

.consent__box::after {
    content: '✓';
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 0.75rem;
    font-weight: 700;
    opacity: 0;
    transform: scale(0.5);
    transition: var(--ease);
}

.consent--red input:checked+.consent__box {
    background: var(--brand);
    border-color: var(--brand);
}

.consent--red input:checked+.consent__box::after {
    opacity: 1;
    transform: scale(1);
}

.consent--gold input:checked+.consent__box {
    background: var(--gold);
    border-color: var(--gold);
}

.consent--gold input:checked+.consent__box::after {
    opacity: 1;
    transform: scale(1);
}

.consent__link {
    color: var(--brand);
    font-weight: 600;
    text-decoration: none;
}

.consent__link:hover {
    text-decoration: underline;
}

.actions-section {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.3rem;
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .actions-section {
        align-items: stretch;
    }
}

/* =========================================================================
   BUTTONS
   ========================================================================= */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    padding: 0.7rem 1.6rem;
    border: none;
    border-radius: var(--radius-sm);
    font-weight: 700;
    font-size: 0.9rem;
    cursor: pointer;
    transition: var(--ease);
    font-family: var(--font-sans);
    text-decoration: none;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn--large {
    padding: 0.85rem 2rem;
    font-size: 0.95rem;
}

.btn--primary {
    background: linear-gradient(135deg, var(--brand), var(--brand-dark));
    color: #fff;
    box-shadow: 0 4px 16px rgba(200, 30, 58, 0.25);
}

.btn--primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(200, 30, 58, 0.35);
}

.btn--secondary {
    background: #f0f0f2;
    color: var(--ink-soft);
    border: 2px solid var(--line);
}

.btn--secondary:hover:not(:disabled) {
    background: var(--line);
    transform: translateY(-2px);
}

.btn--dark {
    background: var(--ink);
    color: #fff;
}

.btn--dark:hover {
    background: var(--brand);
    transform: translateY(-2px);
}

.btn--outline {
    background: transparent;
    color: var(--ink);
    border: 2px solid var(--line);
}

.btn--outline:hover {
    background: var(--paper);
    transform: translateY(-2px);
}

.btn--pulse {
    animation: btnPulse 2s infinite;
}

@keyframes btnPulse {

    0%,
    100% {
        box-shadow: 0 4px 16px rgba(200, 30, 58, 0.25);
    }

    50% {
        box-shadow: 0 4px 32px rgba(200, 30, 58, 0.45);
    }
}

.btn__loading {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn__price {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.15rem 0.7rem;
    border-radius: var(--radius-full);
    font-size: 0.85rem;
    margin-left: 0.3rem;
}

.cta-note {
    font-size: 0.7rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

/* =========================================================================
   SIDEBAR - RESUME CARD
   ========================================================================= */
.sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
}

.resume-card {
    background: var(--card);
    border-radius: var(--radius);
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
    overflow: hidden;
}

.resume-card__header {
    padding: 1rem 1.4rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--line);
    background: var(--paper);
}

.resume-card__title {
    font-weight: 700;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.resume-card__badge {
    font-size: 0.6rem;
    font-weight: 700;
    text-transform: uppercase;
    padding: 0.15rem 0.7rem;
    border-radius: var(--radius-full);
    background: #FEF3C7;
    color: #92400E;
}

.resume-card__badge--pay {
    background: var(--blue-soft);
    color: var(--blue);
}

.resume-card__event {
    padding: 1rem 1.4rem;
    display: flex;
    gap: 0.8rem;
    border-bottom: 1px solid var(--line);
}

.resume-card__event img {
    width: 52px;
    height: 52px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
}

.resume-card__event strong {
    font-size: 0.85rem;
}

.event-type {
    display: inline-block;
    background: var(--gold);
    color: #fff;
    font-size: 0.5rem;
    font-weight: 700;
    padding: 0.05rem 0.4rem;
    border-radius: 4px;
    margin-left: 0.3rem;
}

.resume-card__event ul {
    list-style: none;
    margin: 0.2rem 0 0;
    padding: 0;
}

.resume-card__event li {
    font-size: 0.7rem;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.resume-card__event li i {
    color: var(--brand);
    font-size: 0.6rem;
}

.resume-card__details {
    padding: 0.75rem 1.4rem;
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.78rem;
    color: var(--muted);
}

.detail-row strong {
    color: var(--ink);
    font-weight: 600;
}

.resume-card__divider {
    border-top: 1px dashed var(--line);
    margin: 0 1.4rem;
}

.resume-card__totals {
    padding: 0.75rem 1.4rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.total-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.82rem;
    color: var(--ink-soft);
}

.total-row--grand {
    font-weight: 700;
    font-size: 1rem;
    padding-top: 0.3rem;
    border-top: 1px solid var(--line);
}

.total-row--grand strong {
    color: var(--brand);
    font-size: 1.2rem;
}

.resume-card__availability {
    padding: 0.75rem 1.4rem 1rem;
    border-top: 1px solid var(--line);
    background: var(--paper);
}

.resume-card__availability span {
    font-size: 0.7rem;
    color: var(--muted);
}

/* =========================================================================
   INFO CARDS
   ========================================================================= */
.info-card {
    background: var(--card);
    border-radius: var(--radius);
    padding: 1.2rem 1.4rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--line);
}

.info-card--privacy {
    display: flex;
    gap: 0.8rem;
    align-items: flex-start;
    border-left: 3px solid var(--purple);
    background: var(--purple-soft);
}

.privacy-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--white);
    color: var(--purple);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
}

.info-card--privacy strong {
    display: block;
    font-size: 0.85rem;
    color: var(--purple);
}

.info-card--privacy p {
    font-size: 0.78rem;
    color: var(--muted);
    margin: 0.2rem 0 0;
    line-height: 1.5;
}

/* =========================================================================
   MÉTODOS DE PAGO
   ========================================================================= */
.payment-methods {
    margin-bottom: 1.25rem;
}

.payment-methods__label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--ink-soft);
    display: block;
    margin-bottom: 0.75rem;
}

.payment-methods__grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.payment-method {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    padding: 0.75rem;
    border: 2px solid var(--line);
    border-radius: var(--radius-sm);
    background: var(--card);
    cursor: pointer;
    transition: var(--ease);
    font-family: var(--font-sans);
}

.payment-method:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.payment-method i {
    font-size: 1.5rem;
    color: var(--muted);
    transition: var(--ease);
}

.payment-method span {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--muted);
    transition: var(--ease);
}

.payment-method--active {
    border-color: var(--blue);
    background: var(--blue-soft);
}

.payment-method--active i {
    color: var(--blue);
}

.payment-method--active span {
    color: var(--blue);
}

.payment-methods__coming {
    margin-top: 0.75rem;
    padding: 0.5rem 0.75rem;
    background: #FEFCE8;
    border: 1px solid #FDE68A;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.78rem;
    color: #78350F;
}

.payment-methods__coming i {
    color: #D97706;
    font-size: 1rem;
}

.payment-methods__coming strong {
    color: #92400E;
}

@media (max-width: 500px) {
    .payment-methods__grid {
        grid-template-columns: 1fr;
    }
}

/* =========================================================================
   CARD DATA
   ========================================================================= */
.card-data {
    margin-bottom: 1.25rem;
}

.card-data__label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--ink-soft);
    display: block;
    margin-bottom: 0.75rem;
}

.card-data__grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.card-data__grid .form-group--full {
    grid-column: 1 / -1;
}

@media (max-width: 600px) {
    .card-data__grid {
        grid-template-columns: 1fr;
    }
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.form-group label {
    font-size: 0.78rem;
    font-weight: 600;
}

.input-modern {
    display: flex;
    align-items: center;
    background: var(--paper);
    border: 2px solid var(--line);
    border-radius: var(--radius-sm);
    padding: 0 0.75rem;
    transition: var(--ease);
}

.input-modern:focus-within {
    background: var(--card);
}

.input--blue:focus-within {
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.input--purple:focus-within {
    border-color: var(--purple);
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.12);
}

.input--gold:focus-within {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.12);
}

.input--red:focus-within {
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.12);
}

.input-modern i {
    font-size: 0.9rem;
    margin-right: 0.6rem;
}

.input-modern input {
    border: none;
    background: transparent;
    padding: 0.65rem 0;
    font-size: 0.85rem;
    font-family: var(--font-sans);
    color: var(--ink);
    width: 100%;
    outline: none;
}

.input-modern .card-type {
    font-size: 0.6rem;
    font-weight: 700;
    margin-left: auto;
}

/* =========================================================================
   PAYMENT INFO
   ========================================================================= */
.payment-info {
    display: flex;
    align-items: flex-start;
    gap: 0.8rem;
    padding: 1rem;
    border-radius: var(--radius-sm);
    margin-bottom: 1rem;
}

.payment-info i {
    font-size: 1.3rem;
    margin-top: 0.1rem;
}

.payment-info strong {
    display: block;
    font-size: 0.85rem;
}

.payment-info p {
    font-size: 0.78rem;
    color: var(--muted);
    margin: 0.2rem 0 0;
}

.payment-info--oxxo {
    background: var(--gold-soft);
    border: 1px solid #FDE68A;
}

.payment-info--oxxo i {
    color: var(--gold);
}

.payment-info--oxxo strong {
    color: #78350F;
}

/* =========================================================================
   SECURE BANNER
   ========================================================================= */
.secure-banner {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    background: var(--blue-soft);
    border: 2px solid #BFDBFE;
    padding: 0.75rem 1rem;
    border-radius: var(--radius-sm);
    margin: 1rem 0 0;
}

.secure-banner strong {
    display: block;
    font-size: 0.8rem;
}

.secure-banner span {
    font-size: 0.72rem;
    color: var(--muted);
}

/* =========================================================================
   FORM ACTIONS (PASO 2)
   ========================================================================= */
.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.75rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--line);
}

.form-actions--dual {
    justify-content: space-between;
}

@media (max-width: 600px) {
    .form-actions {
        flex-direction: column;
    }

    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 768px) {
    .reserva-unificada-page {
        padding: 1rem;
    }

    .panel__body {
        padding: 1.25rem;
    }

    .panel__header {
        padding: 1rem 1.25rem;
    }

    .btn {
        font-size: 0.85rem;
        padding: 0.65rem 1.2rem;
    }

    .btn--large {
        padding: 0.75rem 1.5rem;
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .reserva-unificada-page {
        padding: 0.5rem;
    }

    .event-hero-enhanced__info h1 {
        font-size: 1.2rem;
    }

    .event-hero-enhanced__info {
        padding: 1rem;
    }

    .event-hero-enhanced__image {
        min-height: 160px;
    }
}

@media print {
    .no-print {
        display: none !important;
    }
}
</style>