<script setup>
import { computed, reactive, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
// ELIMINADO: import Footer from '@/Components/Footer.vue';

/* ---------------------------------------------------------------
 * Usuario / cabecera
 * --------------------------------------------------------------- */
const usuario = {
    nombre: 'Alexandra',
    avatar: '/images/reserva-checkout/avatar-alexandra.jpg',
    verificado: true,
};

/* ---------------------------------------------------------------
 * Breadcrumb
 * --------------------------------------------------------------- */
const breadcrumb = [
    { label: 'Eventos', href: '/eventos' },
    { label: 'Noche de Seducción', href: '/eventos/noche-de-seduccion' },
    { label: 'Reservar lugar', href: null },
];

/* ---------------------------------------------------------------
 * Stepper del checkout
 * --------------------------------------------------------------- */
const pasos = [
    { id: 1, label: 'Reserva' },
    { id: 2, label: 'Pago' },
    { id: 3, label: 'Confirmación' },
];
const pasoActivo = 1;

/* ---------------------------------------------------------------
 * Evento
 * --------------------------------------------------------------- */
const evento = {
    titulo: 'Noche de Seducción',
    imagen: '/images/reserva-checkout/evento-noche-seduccion.jpg',
    fecha: 'Sábado 31 de mayo',
    hora: '23:00 hrs',
    ciudad: 'Ciudad de México',
    lugaresDisponibles: 18,
    lugaresTotales: 50,
};
const porcentajeDisponible = computed(() => (evento.lugaresDisponibles / evento.lugaresTotales) * 100);

/* ---------------------------------------------------------------
 * Detalles de la reserva
 * --------------------------------------------------------------- */
const form = reactive({
    numAsistentes: 2,
    tipoAcceso: 'vip', // 'vip' | 'general'
    perfil: 'pareja', // 'personal' | 'pareja'
    titular: { nombre: 'Alexandra Torres', email: 'alexandra.torres@gmail.com', telefono: '+52 55 1234 5678' },
    acompanante: { nombre: 'Javier Ramírez', email: 'javier.ramirez@gmail.com', telefono: '+52 55 8765 4321' },
    comentarios: '',
});
const comentariosMax = 250;

function restarAsistentes() { if (form.numAsistentes > 1) form.numAsistentes--; }
function sumarAsistentes() { form.numAsistentes++; }

/* ---------------------------------------------------------------
 * Método de pago
 * --------------------------------------------------------------- */
const pagoForm = reactive({
    numeroTarjeta: '',
    nombreTarjeta: '',
    expiracion: '',
    cvv: '',
});

/* ---------------------------------------------------------------
 * Aceptación de condiciones
 * --------------------------------------------------------------- */
const aceptacion = reactive({
    terminos: true,
    privacidad: true,
    reglasEvento: true,
});
const puedeContinuar = computed(() => aceptacion.terminos && aceptacion.privacidad && aceptacion.reglasEvento);

/* ---------------------------------------------------------------
 * Resumen de pago (sidebar)
 * --------------------------------------------------------------- */
const precioPorPersona = 1290;
const cargoServicio = 258;
const subtotal = computed(() => precioPorPersona * form.numAsistentes);
const total = computed(() => subtotal.value + cargoServicio);

function formatoMoneda(valor) {
    return new Intl.NumberFormat('es-MX').format(valor);
}

/* ---------------------------------------------------------------
 * Incluye
 * --------------------------------------------------------------- */
const incluye = [
    { icon: 'pi-th-large', texto: 'Cóctel de bienvenida' },
    { icon: 'pi-circle', texto: 'Música en vivo & DJ' },
    { icon: 'pi-shield', texto: 'Seguridad y discreción' },
    { icon: 'pi-users', texto: 'Networking selecto' },
    { icon: 'pi-directions-alt', texto: 'Áreas privadas y lounge' },
];

/* ---------------------------------------------------------------
 * Otros eventos que podrían interesarte (carrusel)
 * --------------------------------------------------------------- */
const otrosEventos = reactive([
    { dia: '07', mes: 'JUN', titulo: 'Jacuzzi Experience', vip: true, ciudad: 'Guadalajara', hora: '20:00 hrs', imagen: '/images/reserva-checkout/evento-jacuzzi.jpg' },
    { dia: '13', mes: 'JUN', titulo: 'Luxury Dinner', vip: true, ciudad: 'Ciudad de México', hora: '21:00 hrs', imagen: '/images/reserva-checkout/evento-luxury-dinner.jpg' },
    { dia: '21', mes: 'JUN', titulo: 'Masquerade Night', vip: true, ciudad: 'Monterrey', hora: '23:00 hrs', imagen: '/images/reserva-checkout/evento-masquerade.jpg' },
    { dia: '28', mes: 'JUN', titulo: 'Private Lounge', vip: true, ciudad: 'Cancún', hora: '22:30 hrs', imagen: '/images/reserva-checkout/evento-private-lounge.jpg' },
]);
const carruselIndex = ref(0);
function carruselAnterior() { if (carruselIndex.value > 0) carruselIndex.value--; }
function carruselSiguiente() { if (carruselIndex.value < otrosEventos.length - 1) carruselIndex.value++; }

function continuarAlPago() {
    if (!puedeContinuar.value) return;
    // TODO: router.post(route('eventos.reserva.pago'), { form, pagoForm })
}
</script>

<template>
    <Head title="Reservar lugar" />

    <AppLayout
        active-nav="eventos"
        :usuario="usuario"
        :notificaciones="6"
        :favoritos="12"
        :mensajes="3"
    >
        <div class="checkout-page">
            <!-- ============================================================ -->
            <!-- BREADCRUMB -->
            <!-- ============================================================ -->
            <nav class="breadcrumb">
                <template v-for="(item, i) in breadcrumb" :key="item.label">
                    <a v-if="item.href" :href="item.href">{{ item.label }}</a>
                    <span v-else class="breadcrumb__current">{{ item.label }}</span>
                    <i v-if="i < breadcrumb.length - 1" class="pi pi-angle-right"></i>
                </template>
            </nav>

            <!-- ============================================================ -->
            <!-- STEPPER DE CHECKOUT -->
            <!-- ============================================================ -->
            <section class="checkout-stepper">
                <template v-for="(paso, i) in pasos" :key="paso.id">
                    <div class="checkout-stepper__item" :class="{ active: pasoActivo === paso.id, done: pasoActivo > paso.id }">
                        <span class="checkout-stepper__circle">
                            <i v-if="pasoActivo > paso.id" class="pi pi-check"></i>
                            <template v-else>{{ paso.id }}</template>
                        </span>
                        <span class="checkout-stepper__label">{{ paso.label }}</span>
                    </div>
                    <div v-if="i < pasos.length - 1" class="checkout-stepper__line" :class="{ done: pasoActivo > paso.id }"></div>
                </template>
            </section>

            <div class="content-grid">
                <div class="main-column">
                    <!-- Encabezado -->
                    <div class="page-heading">
                        <h1>Reservar lugar</h1>
                        <p>Tu reserva es privada, segura y confirmada al instante.</p>
                    </div>

                    <!-- Tarjeta del evento -->
                    <section class="event-card">
                        <div class="event-card__image">
                            <img :src="evento.imagen" :alt="evento.titulo" />
                            <span class="event-card__badge">EVENTO VIP</span>
                        </div>
                        <div class="event-card__body">
                            <h2>{{ evento.titulo }}</h2>
                            <div class="event-card__meta">
                                <span><i class="pi pi-calendar"></i> {{ evento.fecha }}</span>
                                <span><i class="pi pi-clock"></i> {{ evento.hora }}</span>
                                <span><i class="pi pi-map-marker"></i> {{ evento.ciudad }}</span>
                            </div>
                            <p class="event-card__note"><i class="pi pi-lock"></i> La ubicación exacta se comparte después de la confirmación.</p>
                        </div>
                    </section>

                    <!-- Detalles de tu reserva -->
                    <section class="form-card">
                        <h3>Detalles de tu reserva</h3>
                        <div class="reserva-fields">
                            <div class="field">
                                <label>Número de asistentes</label>
                                <div class="quantity-stepper">
                                    <button @click="restarAsistentes"><i class="pi pi-minus"></i></button>
                                    <span>{{ form.numAsistentes }}</span>
                                    <button @click="sumarAsistentes"><i class="pi pi-plus"></i></button>
                                </div>
                            </div>
                            <div class="field">
                                <label>Tipo de acceso</label>
                                <div class="pill-row">
                                    <button class="pill" :class="{ selected: form.tipoAcceso === 'vip' }" @click="form.tipoAcceso = 'vip'"><i class="pi pi-crown"></i> VIP</button>
                                    <button class="pill" :class="{ selected: form.tipoAcceso === 'general' }" @click="form.tipoAcceso = 'general'"><i class="pi pi-circle"></i> General</button>
                                </div>
                            </div>
                            <div class="field">
                                <label>Perfil con el que asistes</label>
                                <div class="pill-row">
                                    <button class="pill" :class="{ selected: form.perfil === 'personal' }" @click="form.perfil = 'personal'"><i class="pi pi-user"></i> Personal</button>
                                    <button class="pill" :class="{ selected: form.perfil === 'pareja' }" @click="form.perfil = 'pareja'"><i class="pi pi-users"></i> Pareja</button>
                                </div>
                            </div>
                        </div>

                        <h4>Tus datos de contacto</h4>
                        <div class="contact-fields">
                            <div class="field">
                                <label>Nombre completo</label>
                                <PvInputText v-model="form.titular.nombre" />
                            </div>
                            <div class="field">
                                <label>Correo electrónico</label>
                                <PvInputText v-model="form.titular.email" type="email" />
                            </div>
                            <div class="field">
                                <label>Teléfono / WhatsApp</label>
                                <div class="phone-input">
                                    <span class="phone-input__flag">🇲🇽 <i class="pi pi-chevron-down"></i></span>
                                    <input v-model="form.titular.telefono" type="tel" />
                                </div>
                            </div>
                        </div>

                        <h4>Datos de tu acompañante</h4>
                        <div class="contact-fields">
                            <div class="field">
                                <label>Nombre completo</label>
                                <PvInputText v-model="form.acompanante.nombre" />
                            </div>
                            <div class="field">
                                <label>Correo electrónico</label>
                                <PvInputText v-model="form.acompanante.email" type="email" />
                            </div>
                            <div class="field">
                                <label>Teléfono / WhatsApp</label>
                                <div class="phone-input">
                                    <span class="phone-input__flag">🇲🇽 <i class="pi pi-chevron-down"></i></span>
                                    <input v-model="form.acompanante.telefono" type="tel" />
                                </div>
                            </div>
                        </div>

                        <div class="field mt">
                            <label>Comentarios o solicitudes <span class="optional">(opcional)</span></label>
                            <textarea v-model="form.comentarios" :maxlength="comentariosMax" rows="2" placeholder="Cuéntanos si tienes alguna petición especial, alergias, celebración, etc."></textarea>
                            <span class="char-count">{{ form.comentarios.length }}/{{ comentariosMax }}</span>
                        </div>
                    </section>

                    <!-- Método de pago -->
                    <section class="form-card">
                        <div class="form-card__header-row">
                            <h3>Método de pago</h3>
                            <a href="#" class="apply-code"><i class="pi pi-tag"></i> Aplicar código</a>
                        </div>

                        <div class="payment-grid">
                            <div class="payment-fields">
                                <div class="field">
                                    <label>Número de tarjeta</label>
                                    <div class="card-input">
                                        <input v-model="pagoForm.numeroTarjeta" type="text" placeholder="1234 1234 1234 1234" />
                                        <div class="card-brands">
                                            <span class="brand brand--visa">VISA</span>
                                            <span class="brand brand--mc"></span>
                                            <span class="brand brand--amex">AMEX</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="field">
                                    <label>Nombre en la tarjeta</label>
                                    <PvInputText v-model="pagoForm.nombreTarjeta" placeholder="Alexandra Torres" />
                                </div>
                                <div class="field-row">
                                    <div class="field">
                                        <label>Fecha de expiración</label>
                                        <PvInputText v-model="pagoForm.expiracion" placeholder="MM / AA" />
                                    </div>
                                    <div class="field">
                                        <label>CVV <i class="pi pi-question-circle"></i></label>
                                        <PvInputText v-model="pagoForm.cvv" placeholder="123" />
                                    </div>
                                </div>
                            </div>

                            <div class="secure-payment-box">
                                <i class="pi pi-lock"></i>
                                <strong>Pago 100% seguro</strong>
                                <span>Tus datos están protegidos con cifrado SSL de 256 bits.</span>
                                <div class="secure-payment-box__brands">Verified by <strong>VISA</strong> &nbsp;·&nbsp; Mastercard <strong>SecureCode</strong> &nbsp;·&nbsp; AMERICAN EXPRESS</div>
                            </div>
                        </div>

                        <div class="acceptance-checks">
                            <label class="check-line">
                                <input type="checkbox" v-model="aceptacion.terminos" />
                                Acepto los <a href="#">Términos y Condiciones</a>
                            </label>
                            <label class="check-line">
                                <input type="checkbox" v-model="aceptacion.privacidad" />
                                He leído la <a href="#">Política de Privacidad</a>
                            </label>
                            <label class="check-line">
                                <input type="checkbox" v-model="aceptacion.reglasEvento" />
                                Acepto las <a href="#">Reglas del evento</a> y código de conducta
                            </label>
                        </div>

                        <PvButton
                            label="Continuar al pago"
                            icon="pi pi-lock"
                            class="continue-btn"
                            :disabled="!puedeContinuar"
                            @click="continuarAlPago"
                        />
                        <p class="no-charge-note">No se realizará ningún cargo hasta la confirmación en el siguiente paso.</p>
                    </section>

                    <!-- Otros eventos -->
                    <section class="related-events">
                        <h3>Otros eventos que podrían interesarte</h3>
                        <div class="carousel">
                            <button class="carousel__nav carousel__nav--prev" @click="carruselAnterior"><i class="pi pi-chevron-left"></i></button>
                            <div class="carousel__track">
                                <div v-for="e in otrosEventos" :key="e.titulo" class="mini-event-card">
                                    <div class="mini-event-card__image">
                                        <img :src="e.imagen" :alt="e.titulo" />
                                        <div class="mini-event-card__date"><strong>{{ e.dia }}</strong><span>{{ e.mes }}</span></div>
                                    </div>
                                    <div class="mini-event-card__body">
                                        <strong>{{ e.titulo }} <PvTag v-if="e.vip" value="VIP" /></strong>
                                        <span><i class="pi pi-map-marker"></i> {{ e.ciudad }} &nbsp; <i class="pi pi-clock"></i> {{ e.hora }}</span>
                                    </div>
                                </div>
                            </div>
                            <button class="carousel__nav carousel__nav--next" @click="carruselSiguiente"><i class="pi pi-chevron-right"></i></button>
                        </div>
                    </section>
                </div>

                <!-- ------------------------- SIDEBAR ------------------------- -->
                <aside class="sidebar-column">
                    <div class="sidebar-card">
                        <h3>Resumen de tu reserva</h3>
                        <div class="summary-event">
                            <img :src="evento.imagen" :alt="evento.titulo" />
                            <div>
                                <strong>{{ evento.titulo }}</strong>
                                <span class="vip-chip">Evento VIP</span>
                                <ul>
                                    <li><i class="pi pi-calendar"></i> {{ evento.fecha }}</li>
                                    <li><i class="pi pi-clock"></i> {{ evento.hora }}</li>
                                    <li><i class="pi pi-map-marker"></i> {{ evento.ciudad }}</li>
                                    <li><i class="pi pi-lock"></i> Ubicación privada</li>
                                </ul>
                            </div>
                        </div>

                        <div class="availability">
                            <span><i class="pi pi-users"></i> {{ evento.lugaresDisponibles }} de {{ evento.lugaresTotales }} lugares disponibles</span>
                            <div class="availability-bar"><div class="availability-bar__fill" :style="{ width: porcentajeDisponible + '%' }"></div></div>
                        </div>

                        <div class="payment-row">
                            <span>Precio por persona</span>
                            <strong>${{ formatoMoneda(precioPorPersona) }} MXN</strong>
                        </div>
                        <div class="payment-row">
                            <span>Cantidad</span>
                            <strong>{{ form.numAsistentes }}</strong>
                        </div>
                        <div class="payment-row">
                            <span>Subtotal</span>
                            <strong>${{ formatoMoneda(subtotal) }} MXN</strong>
                        </div>
                        <div class="payment-row">
                            <span>Cargo por servicio <i class="pi pi-info-circle"></i></span>
                            <strong>${{ formatoMoneda(cargoServicio) }} MXN</strong>
                        </div>
                        <hr />
                        <div class="payment-row payment-row--total">
                            <span>Total</span>
                            <strong>${{ formatoMoneda(total) }} <small>MXN</small></strong>
                        </div>
                    </div>

                    <div class="sidebar-card">
                        <h3>Incluye</h3>
                        <div class="includes-grid">
                            <div v-for="item in incluye" :key="item.texto" class="include-item">
                                <i class="pi" :class="item.icon"></i> {{ item.texto }}
                            </div>
                        </div>
                    </div>

                    <div class="sidebar-card privacy-card">
                        <span class="privacy-card__icon"><i class="pi pi-shield"></i></span>
                        <div>
                            <strong>Privacidad y confianza</strong>
                            <p>La ubicación exacta, tus datos y los de otros asistentes están protegidos. Solo los miembros confirmados reciben acceso a los detalles del evento.</p>
                            <a href="#">Conoce más sobre nuestra privacidad <i class="pi pi-arrow-right"></i></a>
                        </div>
                    </div>

                    <div class="sidebar-card help-card">
                        <img src="/images/reserva-checkout/agente-soporte.jpg" alt="Agente de soporte" class="help-card__avatar" />
                        <div>
                            <strong>¿Necesitas ayuda con tu reserva?</strong>
                            <p>Nuestro equipo está aquí para ayudarte.</p>
                            <PvButton label="Hablar con soporte" icon="pi pi-headphones" outlined class="help-card__btn" />
                        </div>
                    </div>

                    <div class="sidebar-card guarantee-card">
                        <span class="guarantee-card__icon"><i class="pi pi-shield"></i></span>
                        <div>
                            <strong>Tu experiencia está garantizada</strong>
                            <p>Club de Fantasías cumple con altos estándares de seguridad, privacidad y bienestar para todos los miembros.</p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <!-- ELIMINADO: <Footer /> -->
    </AppLayout>
</template>

<style scoped>
:root {
    --brand-red: #c81e3a;
}

.checkout-page { font-family: 'Inter', system-ui, sans-serif; color: #1f2024; max-width: 1500px; margin: 0 auto; padding: 1.25rem 2rem 0; }

/* ---------------- BREADCRUMB ---------------- */
.breadcrumb { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: #8a8a90; margin-bottom: 1rem; }
.breadcrumb a { color: #8a8a90; text-decoration: none; }
.breadcrumb__current { color: #2a2a2e; font-weight: 600; }

/* ---------------- CHECKOUT STEPPER ---------------- */
.checkout-stepper {
    background: #fff; border: 1px solid #ececee; border-radius: 12px; padding: 1.1rem 2rem;
    display: flex; align-items: center; justify-content: center; gap: 2rem; margin-bottom: 1.5rem;
}
.checkout-stepper__item { display: flex; flex-direction: column; align-items: center; gap: 0.4rem; }
.checkout-stepper__circle {
    width: 28px; height: 28px; border-radius: 50%; border: 2px solid #d8d8dc; color: #b5b5ba;
    display: flex; align-items: center; justify-content: center; font-size: 0.78rem; font-weight: 700; background: #fff;
}
.checkout-stepper__item.active .checkout-stepper__circle { border-color: var(--brand-red); background: var(--brand-red); color: #fff; }
.checkout-stepper__item.done .checkout-stepper__circle { border-color: #1fbf5c; background: #1fbf5c; color: #fff; }
.checkout-stepper__label { font-size: 0.78rem; font-weight: 600; color: #a5a5aa; }
.checkout-stepper__item.active .checkout-stepper__label { color: var(--brand-red); }
.checkout-stepper__item.done .checkout-stepper__label { color: #1fbf5c; }
.checkout-stepper__line { width: 90px; height: 1px; border-top: 2px dashed #e3e3e7; }
.checkout-stepper__line.done { border-top-color: #1fbf5c; }

/* ---------------- CONTENT GRID ---------------- */
.content-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 1.5rem; align-items: start; padding-bottom: 2.5rem; }
@media (max-width: 1100px) { .content-grid { grid-template-columns: 1fr; } }
.main-column { display: flex; flex-direction: column; gap: 1.5rem; }

.page-heading h1 { font-size: 1.9rem; margin: 0 0 0.3rem; }
.page-heading p { font-size: 0.85rem; color: #8a8a90; margin: 0; }

/* Event card */
.event-card { background: #fff; border: 1px solid #ececee; border-radius: 14px; overflow: hidden; display: grid; grid-template-columns: 1fr 1.6fr; }
@media (max-width: 700px) { .event-card { grid-template-columns: 1fr; } }
.event-card__image { position: relative; }
.event-card__image img { width: 100%; height: 100%; object-fit: cover; display: block; min-height: 140px; }
.event-card__badge { position: absolute; top: 0.75rem; left: 0.75rem; background: var(--brand-red); color: #fff; font-size: 0.65rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 4px; }
.event-card__body { padding: 1.5rem; }
.event-card__body h2 { font-size: 1.25rem; margin: 0 0 0.7rem; }
.event-card__meta { display: flex; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 0.7rem; }
.event-card__meta span { font-size: 0.83rem; color: #55555a; display: flex; align-items: center; gap: 0.4rem; }
.event-card__meta i { color: var(--brand-red); }
.event-card__note { font-size: 0.78rem; color: #8a8a90; display: flex; align-items: center; gap: 0.4rem; margin: 0; }

/* Form cards */
.form-card { background: #fff; border: 1px solid #ececee; border-radius: 14px; padding: 1.75rem; }
.form-card h3 { font-size: 1.05rem; margin: 0 0 1.25rem; }
.form-card h4 { font-size: 0.88rem; margin: 1.5rem 0 1rem; }
.form-card__header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.form-card__header-row h3 { margin: 0; }
.apply-code { color: var(--brand-red); font-size: 0.82rem; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 0.4rem; }

.reserva-fields { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
@media (max-width: 700px) { .reserva-fields { grid-template-columns: 1fr; } }
.field { display: flex; flex-direction: column; gap: 0.4rem; }
.field label { font-size: 0.8rem; font-weight: 600; color: #55555a; }
.field label .optional { font-weight: 400; color: #a5a5aa; }
.field :deep(.p-inputtext) { width: 100%; border-radius: 8px; }
.field textarea {
    width: 100%; border: 1px solid #e3e3e7; border-radius: 8px; padding: 0.75rem 1rem;
    font-family: inherit; font-size: 0.85rem; resize: none; color: #1f2024;
}
.char-count { align-self: flex-end; font-size: 0.7rem; color: #a5a5aa; }
.mt { margin-top: 1.25rem; }

.quantity-stepper { display: inline-flex; align-items: center; border: 1.5px solid #e3e3e7; border-radius: 8px; overflow: hidden; width: fit-content; }
.quantity-stepper button { width: 38px; height: 38px; border: none; background: #fafafa; cursor: pointer; color: #2a2a2e; }
.quantity-stepper span { width: 42px; text-align: center; font-weight: 700; font-size: 0.9rem; }

.pill-row { display: flex; gap: 0.6rem; }
.pill {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 0.4rem;
    border: 1.5px solid #e3e3e7; border-radius: 8px; background: #fff; padding: 0.6rem; cursor: pointer;
    font-size: 0.82rem; font-weight: 600; color: #55555a;
}
.pill.selected { border-color: var(--brand-red); color: var(--brand-red); background: #fdf1f2; }

.contact-fields { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
@media (max-width: 700px) { .contact-fields { grid-template-columns: 1fr; } }
.phone-input { display: flex; align-items: center; border: 1px solid #e3e3e7; border-radius: 8px; padding: 0 0.6rem; gap: 0.5rem; }
.phone-input__flag { font-size: 0.85rem; display: flex; align-items: center; gap: 0.3rem; color: #55555a; border-right: 1px solid #e3e3e7; padding-right: 0.6rem; }
.phone-input__flag i { font-size: 0.6rem; }
.phone-input input { border: none; outline: none; font-size: 0.85rem; padding: 0.6rem 0; width: 100%; color: #1f2024; }

/* Payment */
.payment-grid { display: grid; grid-template-columns: 1.4fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
@media (max-width: 800px) { .payment-grid { grid-template-columns: 1fr; } }
.payment-fields { display: flex; flex-direction: column; gap: 1.25rem; }
.card-input { position: relative; }
.card-input input {
    width: 100%; border: 1px solid #e3e3e7; border-radius: 8px; padding: 0.65rem 5.5rem 0.65rem 1rem;
    font-size: 0.85rem; outline: none; color: #1f2024;
}
.card-brands { position: absolute; right: 0.6rem; top: 50%; transform: translateY(-50%); display: flex; gap: 0.4rem; }
.brand { font-size: 0.6rem; font-weight: 800; color: #a5a5aa; }
.brand--visa { color: #1a1f71; }
.brand--mc { width: 20px; height: 12px; border-radius: 3px; background: linear-gradient(90deg, #eb001b 50%, #f79e1b 50%); opacity: 0.85; }
.brand--amex { color: #2563eb; }

.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.field label i { color: #a5a5aa; font-size: 0.72rem; }

.secure-payment-box { background: #fafafa; border: 1px solid #ececee; border-radius: 10px; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.4rem; }
.secure-payment-box i { color: var(--brand-red); font-size: 1.1rem; }
.secure-payment-box strong { font-size: 0.88rem; }
.secure-payment-box span { font-size: 0.76rem; color: #8a8a90; }
.secure-payment-box__brands { font-size: 0.62rem; color: #a5a5aa; margin-top: 0.5rem; line-height: 1.6; }

.acceptance-checks { display: flex; flex-direction: column; gap: 0.7rem; margin-bottom: 1.25rem; }
.check-line { display: flex; align-items: center; gap: 0.6rem; font-size: 0.83rem; color: #2a2a2e; }
.check-line a { color: var(--brand-red); font-weight: 700; text-decoration: none; }

.continue-btn { width: 100%; font-weight: 700; border-radius: 8px; padding: 0.9rem; margin-bottom: 0.6rem; }
.no-charge-note { text-align: center; font-size: 0.76rem; color: #a5a5aa; margin: 0; }

/* Related events carousel */
.related-events h3 { font-size: 1.05rem; margin: 0 0 1.1rem; }
.carousel { position: relative; display: flex; align-items: center; gap: 0.75rem; }
.carousel__track { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.1rem; flex: 1; overflow: hidden; }
@media (max-width: 900px) { .carousel__track { grid-template-columns: repeat(2, 1fr); } }
.carousel__nav {
    width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e3e3e7; background: #fff;
    display: flex; align-items: center; justify-content: center; color: #55555a; cursor: pointer; flex-shrink: 0;
}
.mini-event-card { background: #fff; border: 1px solid #ececee; border-radius: 10px; overflow: hidden; }
.mini-event-card__image { position: relative; aspect-ratio: 16/11; }
.mini-event-card__image img { width: 100%; height: 100%; object-fit: cover; }
.mini-event-card__date { position: absolute; top: 8px; left: 8px; background: var(--brand-red); color: #fff; border-radius: 6px; padding: 0.25rem 0.5rem; text-align: center; line-height: 1.05; }
.mini-event-card__date strong { display: block; font-size: 0.9rem; }
.mini-event-card__date span { font-size: 0.56rem; }
.mini-event-card__body { padding: 0.9rem; }
.mini-event-card__body strong { font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.4rem; }
.mini-event-card__body span { font-size: 0.72rem; color: #8a8a90; display: flex; align-items: center; gap: 0.3rem; flex-wrap: wrap; }

/* ---------------- SIDEBAR ---------------- */
.sidebar-column { display: flex; flex-direction: column; gap: 1.25rem; }
.sidebar-card { background: #fff; border: 1px solid #ececee; border-radius: 14px; padding: 1.5rem; }
.sidebar-card h3 { font-size: 1rem; margin: 0 0 1.1rem; }

.summary-event { display: flex; gap: 0.9rem; margin-bottom: 1.1rem; }
.summary-event img { width: 64px; height: 64px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
.summary-event strong { font-size: 0.92rem; }
.vip-chip { background: var(--brand-red); color: #fff; font-size: 0.62rem; font-weight: 700; padding: 0.15rem 0.4rem; border-radius: 4px; margin-left: 0.4rem; }
.summary-event ul { list-style: none; margin: 0.5rem 0 0; padding: 0; display: flex; flex-direction: column; gap: 0.3rem; }
.summary-event li { font-size: 0.76rem; color: #8a8a90; display: flex; align-items: center; gap: 0.4rem; }
.summary-event li i { color: var(--brand-red); font-size: 0.7rem; }

.availability { border-top: 1px solid #f0f0f2; border-bottom: 1px solid #f0f0f2; padding: 1rem 0; margin-bottom: 1rem; }
.availability span { font-size: 0.78rem; color: #55555a; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.6rem; }
.availability-bar { height: 6px; border-radius: 4px; background: #f0f0f2; overflow: hidden; }
.availability-bar__fill { height: 100%; background: var(--brand-red); border-radius: 4px; }

.payment-row { display: flex; justify-content: space-between; font-size: 0.85rem; color: #55555a; padding: 0.4rem 0; }
.payment-row strong { color: #1f2024; }
.payment-row i { font-size: 0.7rem; color: #a5a5aa; margin-left: 0.3rem; }
.sidebar-card hr { border: none; border-top: 1px dashed #d8d8dc; margin: 0.4rem 0; }
.payment-row--total span { font-weight: 700; font-size: 0.92rem; color: #1f2024; }
.payment-row--total strong { color: var(--brand-red); font-size: 1.3rem; }

.includes-grid { display: flex; flex-direction: column; gap: 0.8rem; }
.include-item { font-size: 0.83rem; color: #2a2a2e; display: flex; align-items: center; gap: 0.6rem; }
.include-item i { color: #1c7a3c; }

.privacy-card, .help-card, .guarantee-card { display: flex; gap: 0.9rem; align-items: flex-start; }
.privacy-card__icon, .guarantee-card__icon {
    width: 38px; height: 38px; border-radius: 10px; background: #fdf1f2; color: var(--brand-red);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.privacy-card strong, .guarantee-card strong { display: block; font-size: 0.88rem; margin-bottom: 0.4rem; }
.privacy-card p, .guarantee-card p { font-size: 0.78rem; color: #8a8a90; margin: 0 0 0.5rem; line-height: 1.5; }
.privacy-card a { font-size: 0.78rem; color: var(--brand-red); font-weight: 700; text-decoration: none; }

.help-card__avatar { width: 54px; height: 54px; border-radius: 10px; object-fit: cover; flex-shrink: 0; }
.help-card strong { display: block; font-size: 0.88rem; margin-bottom: 0.3rem; }
.help-card p { font-size: 0.78rem; color: #8a8a90; margin: 0 0 0.8rem; }
.help-card__btn { font-weight: 700; border-radius: 8px; width: 100%; }
</style>