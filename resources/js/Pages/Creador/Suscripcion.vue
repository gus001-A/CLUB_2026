<template>

    <Head :title="'Suscríbete a ' + creador.nombre" />
    <ToastNotification ref="toastRef" :duration="5000" />
    <AppLayout active-nav="comunidad" :usuario="usuario">
        <div class="suscripcion-page">
            <Link :href="route('creador.comunidad')" class="btn-back">
                <i class="pi pi-arrow-left"></i> Volver a la comunidad
            </Link>

            <div class="content-grid">
                <!-- ============================================================ -->
                <!-- COLUMNA IZQUIERDA: PLANES + PAGO -->
                <!-- ============================================================ -->
                <div class="main-column">
                    <div class="page-heading">
                        <span class="page-heading__eyebrow">Suscripción</span>
                        <h1>Suscríbete a {{ creador.nombre }}</h1>
                        <p>Desbloquea todo su contenido exclusivo y apoya directamente a este creador.</p>
                    </div>

                    <!-- Selector de plan -->
                    <section class="form-card">
                        <h3><i class="pi pi-crown"></i> Elige tu plan</h3>
                        <div class="planes-grid">
                            <button v-for="plan in planes" :key="plan.clave" type="button" class="plan-card"
                                :class="{ selected: planSeleccionado === plan.clave }" @click="elegirPlan(plan.clave)">
                                <span v-if="plan.descuento_pct > 0" class="plan-card__ahorro">-{{
                                    Math.round(plan.descuento_pct) }}%</span>
                                <span class="plan-card__nombre">{{ plan.nombre }}</span>
                                <span class="plan-card__precio">
                                    ${{ formatoMoneda(plan.precio_total) }}
                                    <small>/ {{ plan.meses }} {{ plan.meses === 1 ? 'mes' : 'meses' }}</small>
                                </span>
                                <span class="plan-card__equivalente" v-if="plan.meses > 1">
                                    equivale a ${{ formatoMoneda(plan.precio_equivalente_mensual) }} / mes
                                </span>
                                <i v-if="planSeleccionado === plan.clave"
                                    class="pi pi-check-circle plan-card__check"></i>
                            </button>
                        </div>
                    </section>

                    <!-- Método de pago -->
                    <section class="form-card">
                        <div class="form-card__header-row">
                            <h3><i class="pi pi-credit-card"></i> Método de pago</h3>
                            <span class="secure-badge"><i class="pi pi-lock"></i> Pago seguro</span>
                        </div>

                        <!-- Selector de método de pago -->
                        <div class="metodos-pago-grid">
                            <button v-for="metodo in metodosPago" :key="metodo.clave" type="button"
                                class="metodo-pago-card" :class="{ selected: form.metodo_pago === metodo.clave }"
                                @click="seleccionarMetodo(metodo.clave)">
                                <i :class="metodo.icon"></i>
                                <span>{{ metodo.nombre }}</span>
                                <i v-if="form.metodo_pago === metodo.clave"
                                    class="pi pi-check-circle metodo-pago-card__check"></i>
                            </button>
                        </div>

                        <!-- Campos según método de pago -->
                        <div class="payment-fields">
                            <!-- TARJETA -->
                            <template v-if="form.metodo_pago === 'tarjeta'">
                                <div class="field">
                                    <label>Número de tarjeta</label>
                                    <input :value="form.numero_tarjeta" type="text" class="field-input"
                                        placeholder="1234 1234 1234 1234" maxlength="19"
                                        @input="formatearNumeroTarjeta" />
                                    <p v-if="form.errors.numero_tarjeta" class="field-error">{{
                                        form.errors.numero_tarjeta }}</p>
                                </div>
                                <div class="field">
                                    <label>Nombre en la tarjeta</label>
                                    <input v-model="form.nombre_tarjeta" type="text" class="field-input"
                                        placeholder="Como aparece en tu tarjeta" />
                                    <p v-if="form.errors.nombre_tarjeta" class="field-error">{{
                                        form.errors.nombre_tarjeta }}</p>
                                </div>
                                <div class="field-row">
                                    <div class="field">
                                        <label>Fecha de expiración</label>
                                        <input :value="form.expiracion" type="text" class="field-input"
                                            placeholder="MM / AA" maxlength="5" @input="formatearExpiracion" />
                                        <p v-if="form.errors.expiracion" class="field-error">{{ form.errors.expiracion
                                            }}</p>
                                    </div>
                                    <div class="field">
                                        <label>CVV</label>
                                        <input :value="form.cvv" type="text" class="field-input" placeholder="123"
                                            maxlength="4" @input="formatearCVV" />
                                        <p v-if="form.errors.cvv" class="field-error">{{ form.errors.cvv }}</p>
                                    </div>
                                </div>
                            </template>

                            <!-- OXXO -->
                            <template v-if="form.metodo_pago === 'oxxo'">
                                <div class="oxxo-info">
                                    <i class="pi pi-info-circle"></i>
                                    <span>Recibirás un correo con la referencia de pago para realizar el pago en
                                        cualquier sucursal OXXO.</span>
                                </div>
                                <div class="field">
                                    <label>Nombre completo <span class="required">*</span></label>
                                    <input v-model="form.nombre_completo" type="text" class="field-input"
                                        placeholder="Tu nombre completo" />
                                    <p v-if="form.errors.nombre_completo" class="field-error">{{
                                        form.errors.nombre_completo }}</p>
                                </div>
                                <div class="field">
                                    <label>Correo electrónico <span class="required">*</span></label>
                                    <input v-model="form.email" type="email" class="field-input"
                                        placeholder="tu@email.com" />
                                    <p v-if="form.errors.email" class="field-error">{{ form.errors.email }}</p>
                                </div>
                                <div class="field">
                                    <label>Teléfono <span class="required">*</span></label>
                                    <input v-model="form.telefono" type="text" class="field-input"
                                        placeholder="55 1234 5678" />
                                    <p v-if="form.errors.telefono" class="field-error">{{ form.errors.telefono }}</p>
                                </div>
                            </template>

                            <!-- MERCADO PAGO -->
                            <template v-if="form.metodo_pago === 'mercadopago'">
                                <div class="mercadopago-info">
                                    <img src="/images/mercadopago-logo.png" alt="Mercado Pago"
                                        class="mercadopago-logo" />
                                    <span>Serás redirigido a Mercado Pago para completar el pago de forma segura.</span>
                                </div>
                                <div class="field">
                                    <label>Correo electrónico de Mercado Pago <span class="required">*</span></label>
                                    <input v-model="form.email_mercadopago" type="email" class="field-input"
                                        placeholder="tu@email.com" />
                                    <p v-if="form.errors.email_mercadopago" class="field-error">{{
                                        form.errors.email_mercadopago }}</p>
                                </div>
                            </template>
                        </div>

                        <button class="continue-btn" :disabled="!puedeEnviar || form.processing"
                            @click="confirmarSuscripcion">
                            <i v-if="form.processing" class="pi pi-spin pi-spinner"></i>
                            <i v-else class="pi pi-crown"></i>
                            {{ form.processing ? 'Procesando...' : `Suscribirme por
                            $${formatoMoneda(planActivo?.precio_total || 0)}` }}
                        </button>
                        <p class="no-charge-note">Puedes cancelar tu suscripción cuando quieras desde tu perfil.</p>
                    </section>
                </div>

                <!-- ============================================================ -->
                <!-- SIDEBAR: PERFIL DEL CREADOR -->
                <!-- ============================================================ -->
                <aside class="sidebar-column">
                    <div class="sidebar-card sidebar-card--creador">
                        <!-- ✅ FOTO DE PERFIL DEL CREADOR -->
                        <img :src="creador.avatar" :alt="creador.nombre" class="creador-avatar" />
                        <strong class="creador-nombre">
                            {{ creador.nombre }}
                            <i v-if="creador.verificado" class="pi pi-verified"></i>
                        </strong>
                        <p v-if="creador.biografia" class="creador-bio">{{ creador.biografia }}</p>

                        <div class="creador-stats">
                            <div>
                                <strong>{{ creador.total_suscriptores }}</strong>
                                <span>Suscriptores</span>
                            </div>
                            <div>
                                <strong>{{ creador.total_contenidos }}</strong>
                                <span>Publicaciones</span>
                            </div>
                        </div>

                        <div v-if="creador.categorias?.length" class="creador-categorias">
                            <span v-for="c in creador.categorias" :key="c" class="chip">{{ c }}</span>
                        </div>
                    </div>

                    <div class="sidebar-card sidebar-card--resumen">
                        <h3>Resumen</h3>
                        <div class="payment-row">
                            <span>Plan</span>
                            <strong>{{ planActivo?.nombre }}</strong>
                        </div>
                        <div class="payment-row">
                            <span>Duración</span>
                            <strong>{{ planActivo?.meses }} {{ planActivo?.meses === 1 ? 'mes' : 'meses' }}</strong>
                        </div>
                        <div class="payment-row" v-if="planActivo?.descuento_pct > 0">
                            <span>Descuento</span>
                            <strong class="text-success">-{{ Math.round(planActivo.descuento_pct) }}%</strong>
                        </div>
                        <div class="payment-row">
                            <span>Método de pago</span>
                            <strong>{{ metodoSeleccionado?.nombre || 'Selecciona' }}</strong>
                        </div>
                        <hr />
                        <div class="payment-row payment-row--total">
                            <span>Total</span>
                            <strong>${{ formatoMoneda(planActivo?.precio_total || 0) }} <small>MXN</small></strong>
                        </div>
                    </div>

                    <div class="sidebar-card privacy-card">
                        <span class="privacy-card__icon"><i class="pi pi-shield"></i></span>
                        <div>
                            <strong>Pago seguro y discreto</strong>
                            <p>Tu información de pago está protegida. El cargo aparecerá de forma discreta en tu estado
                                de cuenta.</p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    usuario: { type: Object, required: true },
    creador: { type: Object, required: true },
    planes: { type: Array, required: true },
});

/* ---------------------------------------------------------------
 * Selección de plan
 * --------------------------------------------------------------- */
const planSeleccionado = ref(
    props.planes.find(p => p.clave === 'mensual')?.clave || props.planes[0]?.clave
);

const planActivo = computed(() =>
    props.planes.find(p => p.clave === planSeleccionado.value) || props.planes[0]
);

function formatoMoneda(valor) {
    return new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(valor);
}

/* ---------------------------------------------------------------
 * Métodos de pago
 * --------------------------------------------------------------- */
const metodosPago = [
    { clave: 'tarjeta', nombre: 'Tarjeta', icon: 'pi pi-credit-card' },
    { clave: 'oxxo', nombre: 'OXXO', icon: 'pi pi-receipt' },
    { clave: 'mercadopago', nombre: 'Mercado Pago', icon: 'pi pi-wallet' },
];

const metodoSeleccionado = computed(() => {
    return metodosPago.find(m => m.clave === form.metodo_pago);
});

function seleccionarMetodo(clave) {
    form.metodo_pago = clave;
    // Limpiar errores del método anterior
    ['numero_tarjeta', 'nombre_tarjeta', 'expiracion', 'cvv', 'nombre_completo', 'email', 'telefono', 'email_mercadopago'].forEach(field => {
        delete form.errors[field];
    });
}

/* ---------------------------------------------------------------
 * Formulario de pago
 * --------------------------------------------------------------- */
const form = useForm({
    plan: planSeleccionado.value,
    metodo_pago: 'tarjeta',
    // Tarjeta
    numero_tarjeta: '',
    nombre_tarjeta: '',
    expiracion: '',
    cvv: '',
    // OXXO
    nombre_completo: '',
    email: '',
    telefono: '',
    // Mercado Pago
    email_mercadopago: '',
});

function elegirPlan(clave) {
    planSeleccionado.value = clave;
    form.plan = clave;
}

// Watch para resetear errores cuando cambia el método de pago
watch(() => form.metodo_pago, () => {
    Object.keys(form.errors).forEach(key => {
        if (['numero_tarjeta', 'nombre_tarjeta', 'expiracion', 'cvv', 'nombre_completo', 'email', 'telefono', 'email_mercadopago'].includes(key)) {
            delete form.errors[key];
        }
    });
});

/* ---------------------------------------------------------------
 * Formateadores de campos
 * --------------------------------------------------------------- */
function formatearNumeroTarjeta(event) {
    let value = event.target.value.replace(/\D/g, '');
    let formatted = '';
    for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) formatted += ' ';
        formatted += value[i];
    }
    form.numero_tarjeta = formatted;
    event.target.value = formatted;
}

function formatearExpiracion(event) {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length >= 2) value = value.substring(0, 2) + '/' + value.substring(2, 4);
    form.expiracion = value;
    event.target.value = value;
}

function formatearCVV(event) {
    const value = event.target.value.replace(/\D/g, '').substring(0, 4);
    form.cvv = value;
    event.target.value = value;
}

/* ---------------------------------------------------------------
 * Validación de formulario
 * --------------------------------------------------------------- */
const puedeEnviar = computed(() => {
    if (!form.plan || !form.metodo_pago) return false;

    if (form.metodo_pago === 'tarjeta') {
        return form.numero_tarjeta.replace(/\s/g, '').length >= 16
            && form.nombre_tarjeta.trim() !== ''
            && /^\d{2}\/\d{2}$/.test(form.expiracion)
            && form.cvv.length >= 3;
    }

    if (form.metodo_pago === 'oxxo') {
        return form.nombre_completo.trim() !== ''
            && form.email.trim() !== ''
            && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)
            && form.telefono.trim() !== '';
    }

    if (form.metodo_pago === 'mercadopago') {
        return form.email_mercadopago.trim() !== ''
            && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email_mercadopago);
    }

    return false;
});

/* ---------------------------------------------------------------
 * Enviar formulario
 * --------------------------------------------------------------- */
function confirmarSuscripcion() {
    if (!puedeEnviar.value) return;

    // Limpiar campos que no corresponden al método seleccionado
    if (form.metodo_pago !== 'tarjeta') {
        form.numero_tarjeta = '';
        form.nombre_tarjeta = '';
        form.expiracion = '';
        form.cvv = '';
    }
    if (form.metodo_pago !== 'oxxo') {
        form.nombre_completo = '';
        form.email = '';
        form.telefono = '';
    }
    if (form.metodo_pago !== 'mercadopago') {
        form.email_mercadopago = '';
    }

    form.post(route('creador.suscripcion.procesar', props.creador.id), {
        preserveScroll: true,
    });
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700;800&display=swap');

.suscripcion-page {
    --brand-red: #c81e3a;
    --brand-red-dark: #a6152d;
    --brand-red-light: #fdf1f2;
    --brand-gold: #d4a53a;
    --brand-dark: #1a1817;
    --brand-gray: #6b6764;
    --brand-gray-light: #e8e5e3;
    --brand-white: #faf8f7;
    --font-display: 'Fraunces', Georgia, serif;
    --font-body: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    --shadow-sm: 0 2px 10px rgba(26, 24, 23, 0.05);

    font-family: var(--font-body);
    color: var(--brand-dark);
    background: var(--brand-white);
    max-width: 1200px;
    margin: 0 auto;
    padding: 1.25rem 2rem 3rem;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
    color: var(--brand-gray);
    text-decoration: none;
    margin-bottom: 1.5rem;
    transition: color 0.2s ease;
}

.btn-back:hover {
    color: var(--brand-red);
}

.content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 340px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 1000px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

.main-column {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.page-heading__eyebrow {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--brand-red);
}

.page-heading h1 {
    font-family: var(--font-display);
    font-size: 1.9rem;
    margin: 0.4rem 0 0.3rem;
}

.page-heading p {
    font-size: 0.9rem;
    color: var(--brand-gray);
    margin: 0;
}

.form-card {
    background: #fff;
    border: 1px solid var(--brand-gray-light);
    border-radius: 18px;
    padding: 1.85rem;
    box-shadow: var(--shadow-sm);
}

.form-card h3 {
    font-family: var(--font-display);
    font-size: 1.1rem;
    margin: 0 0 1.3rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-card h3 i {
    color: var(--brand-red);
    font-size: 1rem;
}

.form-card__header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
}

.form-card__header-row h3 {
    margin: 0;
}

.secure-badge {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.7rem;
    color: #22c55e;
    background: #dcfce7;
    padding: 0.2rem 0.8rem;
    border-radius: 50px;
    font-weight: 600;
}

/* ============================================================
   PLANES
   ============================================================ */
.planes-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.9rem;
}

@media (max-width: 600px) {
    .planes-grid {
        grid-template-columns: 1fr;
    }
}

.plan-card {
    position: relative;
    text-align: left;
    border: 2px solid var(--brand-gray-light);
    border-radius: 14px;
    background: #fff;
    padding: 1.1rem 1.2rem;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    transition: all 0.2s ease;
}

.plan-card:hover {
    border-color: var(--brand-red);
}

.plan-card.selected {
    border-color: var(--brand-red);
    background: var(--brand-red-light);
}

.plan-card__nombre {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 1.05rem;
}

.plan-card__precio {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--brand-dark);
}

.plan-card__precio small {
    font-size: 0.7rem;
    font-weight: 500;
    color: var(--brand-gray);
}

.plan-card__equivalente {
    font-size: 0.72rem;
    color: var(--brand-gray);
}

.plan-card__ahorro {
    position: absolute;
    top: 0.8rem;
    right: 0.8rem;
    background: var(--brand-gold);
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
}

.plan-card__check {
    position: absolute;
    bottom: 0.9rem;
    right: 0.9rem;
    color: var(--brand-red);
    font-size: 1.1rem;
}

/* ============================================================
   MÉTODOS DE PAGO
   ============================================================ */
.metodos-pago-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.6rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 600px) {
    .metodos-pago-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.metodo-pago-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.3rem;
    padding: 0.8rem 0.5rem;
    border: 2px solid var(--brand-gray-light);
    border-radius: 12px;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    font-family: inherit;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--brand-gray);
}

.metodo-pago-card:hover {
    border-color: var(--brand-red);
}

.metodo-pago-card.selected {
    border-color: var(--brand-red);
    background: var(--brand-red-light);
    color: var(--brand-red);
}

.metodo-pago-card i {
    font-size: 1.4rem;
}

.metodo-pago-card__check {
    position: absolute;
    top: 0.3rem;
    right: 0.3rem;
    color: var(--brand-red);
    font-size: 0.9rem;
}

/* ============================================================
   CAMPOS DE PAGO
   ============================================================ */
.payment-fields {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.field {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.field label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--brand-gray);
}

.field label .required {
    color: var(--brand-red);
}

.field-input {
    width: 100%;
    border: 2px solid var(--brand-gray-light);
    border-radius: 10px;
    padding: 0.7rem 1rem;
    font-family: inherit;
    font-size: 0.85rem;
    color: var(--brand-dark);
    outline: none;
    transition: all 0.2s ease;
    background: #fff;
}

.field-input:focus {
    border-color: var(--brand-red);
    box-shadow: 0 0 0 4px rgba(200, 30, 58, 0.1);
}

.field-error {
    color: #dc2626;
    font-size: 0.75rem;
    margin: 0;
}

.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 480px) {
    .field-row {
        grid-template-columns: 1fr;
    }
}

/* ============================================================
   OXXO Y MERCADO PAGO
   ============================================================ */
.oxxo-info,
.mercadopago-info {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.8rem 1rem;
    background: #fefce8;
    border: 1px solid #fde68a;
    border-radius: 10px;
    font-size: 0.8rem;
    color: #92400e;
}

.oxxo-info i {
    font-size: 1.2rem;
    color: #d97706;
    flex-shrink: 0;
}

.mercadopago-info {
    background: #e6f7ff;
    border-color: #91d5ff;
    color: #0050b3;
    flex-wrap: wrap;
}

.mercadopago-logo {
    height: 30px;
    object-fit: contain;
}

/* ============================================================
   BOTÓN CONTINUAR
   ============================================================ */
.continue-btn {
    width: 100%;
    background: var(--brand-red);
    color: #fff;
    border: none;
    font-weight: 700;
    font-size: 0.95rem;
    border-radius: 12px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
}

.continue-btn:hover:not(:disabled) {
    background: var(--brand-red-dark);
    transform: translateY(-2px);
    box-shadow: 0 8px 26px rgba(200, 30, 58, 0.3);
}

.continue-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.no-charge-note {
    text-align: center;
    font-size: 0.76rem;
    color: #a5a5aa;
    margin: 0.6rem 0 0;
}

/* ============================================================
   SIDEBAR
   ============================================================ */
.sidebar-column {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.sidebar-card {
    background: #fff;
    border: 1px solid var(--brand-gray-light);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
}

.sidebar-card--creador {
    text-align: center;
}

.creador-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 0.8rem;
    display: block;
    border: 3px solid var(--brand-red-light);
    transition: transform 0.3s ease;
}

.creador-avatar:hover {
    transform: scale(1.05);
}

.creador-nombre {
    font-family: var(--font-display);
    font-size: 1.05rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.3rem;
}

.creador-nombre i {
    color: #2563eb;
    font-size: 0.85rem;
}

.creador-bio {
    font-size: 0.8rem;
    color: var(--brand-gray);
    margin: 0.5rem 0 1rem;
}

.creador-stats {
    display: flex;
    justify-content: center;
    gap: 1.8rem;
    padding: 0.8rem 0;
    border-top: 1px solid var(--brand-gray-light);
    border-bottom: 1px solid var(--brand-gray-light);
    margin-bottom: 0.9rem;
}

.creador-stats div {
    display: flex;
    flex-direction: column;
}

.creador-stats strong {
    font-size: 1.05rem;
}

.creador-stats span {
    font-size: 0.68rem;
    color: var(--brand-gray);
}

.creador-categorias {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    justify-content: center;
}

.chip {
    font-size: 0.68rem;
    background: var(--brand-red-light);
    color: var(--brand-red);
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    font-weight: 600;
}

.sidebar-card--resumen h3 {
    font-family: var(--font-display);
    font-size: 1rem;
    margin: 0 0 1rem;
}

.payment-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: var(--brand-gray);
    padding: 0.35rem 0;
}

.payment-row strong {
    color: var(--brand-dark);
}

.text-success {
    color: #22c55e !important;
}

.sidebar-card hr {
    border: none;
    border-top: 1px dashed #d8d8dc;
    margin: 0.4rem 0;
}

.payment-row--total span {
    font-weight: 700;
    font-size: 0.92rem;
}

.payment-row--total strong {
    color: var(--brand-red);
    font-size: 1.4rem;
}

.payment-row--total strong small {
    font-size: 0.65rem;
    font-weight: 400;
    color: var(--brand-gray);
}

.privacy-card {
    display: flex;
    gap: 0.9rem;
    align-items: flex-start;
}

.privacy-card__icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: var(--brand-red-light);
    color: var(--brand-red);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.2rem;
}

.privacy-card strong {
    display: block;
    font-size: 0.88rem;
    margin-bottom: 0.3rem;
}

.privacy-card p {
    font-size: 0.76rem;
    color: var(--brand-gray);
    margin: 0;
    line-height: 1.5;
}
</style>