<!-- resources/js/Pages/Auth/Login.vue -->
<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// Importaciones de PrimeVue
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import Message from 'primevue/message';
import AppIcon from '@/Components/AppIcon.vue';
import ToastNotification from '@/Components/ToastNotification.vue';

const props = defineProps({
    modoVerificacion: { type: Boolean, default: false },
    emailPendiente: { type: String, default: null },
});

// -----------------------------------------------------------------------
// Formulario
// -----------------------------------------------------------------------
const form = useForm({
    nickname: '',
    password: '',
    remember: false,
});

const focusedField = ref(null);
const touchedFields = ref({});

// =======================================================================
// VALIDACIONES EN TIEMPO REAL
// =======================================================================

// 1. Validación de nickname
const isNicknameValid = computed(() => {
    if (!form.nickname) return false;
    return /^[A-Za-z0-9_]{3,20}$/.test(form.nickname);
});

const nicknameError = computed(() => {
    if (!form.nickname || !touchedFields.value.nickname) return '';
    if (form.nickname.length < 3) {
        return 'El nickname debe tener al menos 3 caracteres';
    }
    if (form.nickname.length > 20) {
        return 'El nickname no puede tener más de 20 caracteres';
    }
    if (!/^[A-Za-z0-9_]+$/.test(form.nickname)) {
        return 'Solo letras, números y guiones bajos';
    }
    return '';
});

// 2. Validación de contraseña
const isPasswordValid = computed(() => {
    if (!form.password) return false;
    return form.password.length >= 6;
});

const passwordError = computed(() => {
    if (!form.password || !touchedFields.value.password) return '';
    if (form.password.length < 6) {
        return 'La contraseña debe tener al menos 6 caracteres';
    }
    return '';
});

// =======================================================================
// VALIDACIÓN GENERAL DEL FORMULARIO
// =======================================================================
const isFormValid = computed(() => {
    return isNicknameValid.value && isPasswordValid.value;
});

// =======================================================================
// FUNCIONES DE UTILIDAD
// =======================================================================
function markTouched(field) {
    touchedFields.value[field] = true;
}

function handleBlur(field) {
    markTouched(field);
    focusedField.value = null;
}

// =======================================================================
// COMPUTED PARA ERRORES DEL SERVIDOR
// =======================================================================
const serverError = computed(() => {
    if (form.errors.login) {
        return form.errors.login;
    }
    return null;
});

// =======================================================================
// SUBMIT
// =======================================================================
function submit() {
    markTouched('nickname');
    markTouched('password');
    
    // Validación del lado del cliente
    if (!isFormValid.value) {
        const firstError = document.querySelector('.rg-error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }
    
    // Enviar formulario
    form.post(route('login'), {
        onSuccess: () => {
            form.reset('password');
        },
        onError: (errors) => {
            console.log('Errores del servidor:', errors);
        },
    });
}

// =======================================================================
// VERIFICACIÓN DE CORREO (cuentas creadas por un admin, o interrumpidas
// a medio registro) — el backend nos manda aquí vía modoVerificacion.
// =======================================================================
const codeForm = useForm({
    codigo: '',
});

const resendCooldown = ref(0);
let cooldownTimer = null;

function startCooldown(seconds = 45) {
    resendCooldown.value = seconds;
    clearInterval(cooldownTimer);
    cooldownTimer = setInterval(() => {
        resendCooldown.value--;
        if (resendCooldown.value <= 0) clearInterval(cooldownTimer);
    }, 1000);
}
if (props.modoVerificacion) {
    startCooldown();
}

function confirmarCodigo() {
    codeForm.post(route('login.verificar-codigo'));
}

function reenviarCodigo() {
    if (resendCooldown.value > 0) return;
    codeForm.post(route('login.reenviar-codigo'), {
        preserveScroll: true,
        onSuccess: () => startCooldown(),
    });
}

function cancelarVerificacion() {
    codeForm.post(route('login.cancelar-verificacion'));
}
</script>

<template>
    <Head title="Iniciar sesión" />

    <div class="rg-page">
        <!-- Toast Notification -->
        <ToastNotification :duration="5000" />

        <!-- ================= NAVBAR ================= -->
        <header class="rg-header">
            <nav class="rg-nav">
                <Link href="/" class="rg-brand">
                    <img 
                        src="/images/LOGO.png" 
                        alt="Club de Fantasías" 
                        class="rg-brand__logo"
                    />
                </Link>

                <div class="rg-nav__links">
                    <Link href="/" class="rg-nav__link">Inicio</Link>
                    <a href="/#quienes-somos">Quiénes somos</a>
                    <a href="/#servicios">Servicios</a>
                    <a href="/#eventos">Eventos</a>
                    <a href="/#contacto">Contacto</a>
                </div>

                <div class="rg-nav__actions">
                    <Link :href="route('login')" class="rg-btn rg-btn--primary">
                        Iniciar sesión
                    </Link>
                    <Link :href="route('register.invite')" class="rg-btn rg-btn--ghost">
                        Registro
                    </Link>
                </div>
            </nav>
        </header>

        <!-- ================= SPLIT SECTION ================= -->
        <section class="rg-split">
            <!-- Panel izquierdo -->
            <div class="rg-media">
                <img src="/images/login.png" alt="Ambiente exclusivo" class="rg-media__img" />
                <div class="rg-media__overlay"></div>

                <div class="rg-media__content">
                    <div class="rg-media__badge">
                        <span class="rg-media__badge-dot"></span>
                        <AppIcon name="shield-check" />
                        <span>Bienvenido de vuelta</span>
                    </div>
                    
                    <h1 class="rg-media__title">
                        Conecta con <span class="rg-accent">experiencias</span><br />
                        auténticas.
                    </h1>

                    <ul class="rg-media__list">
                        <li>
                            <span class="rg-media__list-icon">
                                <AppIcon name="shield-check" />
                            </span>
                            Accede a tu cuenta
                        </li>
                        <li>
                            <span class="rg-media__list-icon">
                                <AppIcon name="users" />
                            </span>
                            Conecta con la comunidad
                        </li>
                        <li>
                            <span class="rg-media__list-icon">
                                <AppIcon name="calendar" />
                            </span>
                            Descubre eventos exclusivos
                        </li>
                    </ul>

                    <div class="rg-media__footnote">
                        <span class="rg-media__footnote-icon">
                            <AppIcon name="shield-check" />
                        </span>
                        <p>Ambiente seguro y discreto para adultos.</p>
                    </div>
                </div>
            </div>

            <!-- Panel derecho: formulario -->
            <div class="rg-form-panel">
                <form v-if="!modoVerificacion" class="rg-card" @submit.prevent="submit">
                    <!-- Header -->
                    <div class="rg-card__head">
                        <div class="rg-card__head-decoration">
                            <span class="rg-card__head-line"></span>
                            <span class="rg-card__head-dot"></span>
                        </div>
                        <div>
                            <h2 class="rg-card__title">
                                <AppIcon name="log-in" />
                                Iniciar <span class="rg-accent">sesión</span>
                            </h2>
                            <p class="rg-card__subtitle">
                                Ingresa tu nickname y contraseña para acceder a tu cuenta.
                            </p>
                        </div>
                    </div>

                    <!-- ============================================================= -->
                    <!-- MENSAJE DE ERROR DEL SERVIDOR                                -->
                    <!-- ============================================================= -->
                    <Message severity="error" :closable="false" class="rg-info" v-if="serverError">
                        <div class="rg-info__body">
                            <span class="rg-info__icon">
                                <AppIcon name="alert-circle" />
                            </span>
                            <div>
                                <p class="rg-info__title">Error al iniciar sesión</p>
                                <p class="rg-info__text">{{ serverError }}</p>
                            </div>
                        </div>
                    </Message>

                    <!-- Mensaje de bienvenida -->
                    <Message severity="info" :closable="false" class="rg-info rg-info--welcome">
                        <div class="rg-info__body">
                            <span class="rg-info__icon">
                                <AppIcon name="heart" />
                            </span>
                            <div>
                                <p class="rg-info__title">¡Bienvenido de vuelta!</p>
                                <p class="rg-info__text">Ingresa con tu nickname y contraseña para continuar disfrutando de la comunidad.</p>
                            </div>
                        </div>
                    </Message>

                    <!-- ============================================================= -->
                    <!-- NICKNAME                                                      -->
                    <!-- ============================================================= -->
                    <div class="rg-field">
                        <label class="rg-label" for="nickname">
                            <AppIcon name="user" />
                            Nickname
                            <span class="rg-label__badge">Obligatorio</span>
                            <span class="rg-label__badge rg-label__badge--format">3-20 caracteres</span>
                        </label>
                        <div class="rg-input-wrapper">
                            <InputText
                                id="nickname"
                                v-model="form.nickname"
                                placeholder="Ej: Juan_86, Maria_123"
                                class="rg-input"
                                :class="{ 
                                    'rg-input--error': nicknameError && touchedFields.nickname && !serverError,
                                    'rg-input--valid': isNicknameValid && touchedFields.nickname,
                                    'rg-input--focused': focusedField === 'nickname' 
                                }"
                                @focus="focusedField = 'nickname'"
                                @blur="handleBlur('nickname')"
                                @input="markTouched('nickname')"
                            />
                        </div>
                        <div class="rg-hint-wrapper">
                            <small v-if="nicknameError && touchedFields.nickname && !serverError" class="rg-error">
                                <AppIcon name="alert-circle" />
                                {{ nicknameError }}
                            </small>
                        </div>
                    </div>

                    <!-- ============================================================= -->
                    <!-- CONTRASEÑA                                                     -->
                    <!-- ============================================================= -->
                    <div class="rg-field">
                        <label class="rg-label" for="password">
                            <AppIcon name="lock" />
                            Contraseña
                            <span class="rg-label__badge">Obligatorio</span>
                            <span class="rg-label__badge rg-label__badge--format">Mín 6 caracteres</span>
                        </label>
                        <div class="rg-input-wrapper">
                            <span class="rg-input-icon">
                                <AppIcon name="lock" />
                            </span>
                            <Password
                                id="password"
                                v-model="form.password"
                                placeholder="Ingresa tu contraseña"
                                class="rg-password"
                                input-class="rg-password__input"
                                :class="{
                                    'rg-password--error': passwordError && touchedFields.password && !serverError,
                                    'rg-password--valid': isPasswordValid && touchedFields.password
                                }"
                                :feedback="false"
                                :toggle-mask="true"
                                @focus="focusedField = 'password'"
                                @blur="handleBlur('password')"
                                @input="markTouched('password')"
                            />
                        </div>
                        <div class="rg-hint-wrapper">
                            <small v-if="passwordError && touchedFields.password && !serverError" class="rg-error">
                                <AppIcon name="alert-circle" />
                                {{ passwordError }}
                            </small>
                        </div>
                    </div>

                    <!-- ============================================================= -->
                    <!-- RECORDAR / OLVIDÉ CONTRASEÑA                                  -->
                    <!-- ============================================================= -->
                    <div class="rg-options">
                        <div class="rg-remember">
                            <Checkbox 
                                v-model="form.remember" 
                                input-id="remember" 
                                binary 
                            />
                            <label for="remember">Recordarme</label>
                        </div>
                        <a href="#" class="rg-forgot" @click.prevent="alert('Próximamente disponible')">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    <!-- Botón de inicio -->
                    <Button
                        type="submit"
                        class="rg-submit"
                        :loading="form.processing"
                        :disabled="!isFormValid"
                    >
                        <AppIcon v-if="!form.processing" name="log-in" />
                        <span v-if="form.processing" class="rg-submit__spinner"></span>
                        <span>{{ form.processing ? 'Ingresando...' : 'INICIAR SESIÓN' }}</span>
                    </Button>

                    <div class="rg-divider"><span>o</span></div>

                    <Link :href="route('register.invite')" class="rg-btn rg-btn--outline rg-btn--block">
                        <AppIcon name="user-plus" />
                        ¿No tienes cuenta? <span class="rg-accent">Regístrate</span>
                    </Link>

                    <p class="rg-card__footnote">
                        <AppIcon name="shield-check" />
                        Comunidad exclusiva, privacidad garantizada.
                    </p>
                </form>

                <!-- Verificación de correo (cuentas creadas por un admin) -->
                <form v-else class="rg-card" @submit.prevent="confirmarCodigo">
                    <div class="rg-card__head">
                        <div class="rg-card__head-decoration">
                            <span class="rg-card__head-line"></span>
                            <span class="rg-card__head-dot"></span>
                        </div>
                        <div>
                            <h2 class="rg-card__title">
                                Verifica tu <span class="rg-accent">correo</span>
                            </h2>
                            <p class="rg-card__subtitle">Falta un último paso para entrar a tu cuenta.</p>
                        </div>
                    </div>

                    <Message severity="info" :closable="false" class="rg-info">
                        <div class="rg-info__body">
                            <span class="rg-info__icon">
                                <AppIcon name="mail" />
                            </span>
                            <div>
                                <p class="rg-info__title">Revisa tu correo</p>
                                <p class="rg-info__text">
                                    Te enviamos un código de 6 dígitos a
                                    <strong v-if="emailPendiente">{{ emailPendiente }}</strong>
                                    <span v-else>tu correo</span>. Revisa spam si no lo ves.
                                </p>
                            </div>
                        </div>
                    </Message>

                    <div class="rg-field rg-field--highlight">
                        <label class="rg-label" for="codigo">
                            <AppIcon name="shield-check" />
                            Código de verificación
                            <span class="rg-label__badge">Obligatorio</span>
                        </label>
                        <div class="rg-input-wrapper">
                            <InputText id="codigo" v-model="codeForm.codigo" type="text" inputmode="numeric"
                                maxlength="6" placeholder="000000" class="rg-input rg-input--large"
                                :class="{ 'rg-input--error': codeForm.errors.codigo }"
                                @input="codeForm.codigo = codeForm.codigo.replace(/\D/g, '').slice(0, 6)" />
                        </div>
                        <div class="rg-hint-wrapper">
                            <small v-if="codeForm.errors.codigo" class="rg-error">{{ codeForm.errors.codigo }}</small>
                        </div>
                    </div>

                    <Button type="submit" class="rg-submit" :loading="codeForm.processing"
                        :disabled="codeForm.codigo.length !== 6">
                        <AppIcon v-if="!codeForm.processing" name="log-in" />
                        <span v-if="codeForm.processing" class="rg-submit__spinner"></span>
                        <span>{{ codeForm.processing ? 'Verificando...' : 'CONFIRMAR Y ENTRAR' }}</span>
                    </Button>

                    <div class="rg-verification-actions">
                        <button type="button" class="rg-btn rg-btn--ghost" :disabled="resendCooldown > 0" @click="reenviarCodigo">
                            {{ resendCooldown > 0 ? `Reenviar código (${resendCooldown}s)` : 'Reenviar código' }}
                        </button>
                        <button type="button" class="rg-btn rg-btn--ghost" @click="cancelarVerificacion">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- ================= TRUST BANNER ================= -->
        <section class="rg-trust">
            <div class="rg-trust__item">
                <div class="rg-trust__icon-wrapper">
                    <AppIcon name="verified" />
                </div>
                <div>
                    <p class="rg-trust__title">
                        <AppIcon name="verified" />
                        Validación de perfiles
                    </p>
                    <p class="rg-trust__text">Revisamos cada perfil para asegurar una comunidad auténtica.</p>
                </div>
            </div>
            <div class="rg-trust__item">
                <div class="rg-trust__icon-wrapper">
                    <AppIcon name="lock" />
                </div>
                <div>
                    <p class="rg-trust__title">
                        <AppIcon name="lock" />
                        Mayor seguridad
                    </p>
                    <p class="rg-trust__text">Protegemos tu información con los más altos estándares de seguridad.</p>
                </div>
            </div>
            <div class="rg-trust__item">
                <div class="rg-trust__icon-wrapper">
                    <AppIcon name="users" />
                </div>
                <div>
                    <p class="rg-trust__title">
                        <AppIcon name="users" />
                        Comunidad exclusiva
                    </p>
                    <p class="rg-trust__text">Conecta con personas reales que buscan experiencias genuinas.</p>
                </div>
            </div>
            <div class="rg-trust__item">
                <div class="rg-trust__icon-wrapper">
                    <AppIcon name="eye-slash" />
                </div>
                <div>
                    <p class="rg-trust__title">
                        <AppIcon name="eye-slash" />
                        Privacidad garantizada
                    </p>
                    <p class="rg-trust__text">Tu privacidad es nuestra prioridad. Cero tolerancia a la exposición.</p>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
/* =========================================================================
   TOKENS
   ========================================================================= */
.rg-page {
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
  --shadow-card: 0 30px 60px -20px rgba(23, 20, 18, 0.18);
  --success: #48BB78;
  --success-bg: #F0FFF4;
  --error: #E53E3E;
  --error-bg: #FFF5F5;

  --font-serif: 'Fraunces', Georgia, serif;
  --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;
  --radius-sm: 10px;
  --radius-md: 16px;
  --radius-lg: 28px;
  --radius-full: 999px;

  font-family: var(--font-sans);
  color: var(--ink);
  background: var(--white);
  -webkit-font-smoothing: antialiased;
}

.rg-page * {
  box-sizing: border-box;
}

.rg-accent {
  color: var(--brand);
}

/* =========================================================================
   ICONOS - TAMAÑOS GLOBALES
   ========================================================================= */
.rg-page :deep(.app-icon) {
  width: 16px !important;
  height: 16px !important;
  flex-shrink: 0 !important;
}

/* Excepciones para contextos específicos */
.rg-media__list-icon :deep(.app-icon) {
  width: 14px !important;
  height: 14px !important;
}

.rg-media__footnote-icon :deep(.app-icon) {
  width: 18px !important;
  height: 18px !important;
  color: var(--brand);
}

.rg-info__icon :deep(.app-icon) {
  width: 18px !important;
  height: 18px !important;
  color: var(--brand);
}

.rg-trust__icon-wrapper :deep(.app-icon) {
  width: 20px !important;
  height: 20px !important;
  color: var(--brand);
}

.rg-trust__title :deep(.app-icon) {
  width: 14px !important;
  height: 14px !important;
  color: var(--brand);
}

.rg-input-icon :deep(.app-icon) {
  width: 16px !important;
  height: 16px !important;
  color: var(--muted);
}

.rg-card__title :deep(.app-icon) {
  width: 24px !important;
  height: 24px !important;
  color: var(--brand);
}

.rg-card__footnote :deep(.app-icon) {
  width: 14px !important;
  height: 14px !important;
  color: var(--brand);
}

.rg-label :deep(.app-icon) {
  width: 14px !important;
  height: 14px !important;
  color: var(--brand);
}

.rg-hint :deep(.app-icon),
.rg-error :deep(.app-icon) {
  width: 14px !important;
  height: 14px !important;
}

.rg-submit :deep(.app-icon) {
  width: 18px !important;
  height: 18px !important;
}

.rg-btn :deep(.app-icon) {
  width: 16px !important;
  height: 16px !important;
}

/* =========================================================================
   BOTONES
   ========================================================================= */
.rg-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-weight: 600;
  font-size: 0.85rem;
  border-radius: var(--radius-full);
  padding: 0.7rem 1.5rem;
  text-decoration: none;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: var(--font-sans);
}

.rg-btn--primary {
  background: var(--brand);
  color: var(--white);
  box-shadow: 0 4px 12px rgba(200, 30, 58, 0.25);
}
.rg-btn--primary:hover {
  background: var(--brand-dark);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(200, 30, 58, 0.35);
}

.rg-btn--ghost {
  background: transparent;
  border-color: #D9D5D2;
  color: var(--ink);
}
.rg-btn--ghost:hover {
  border-color: var(--brand);
  color: var(--brand);
  background: var(--brand-soft);
}

.rg-btn--outline {
  background: transparent;
  border-color: #D9D5D2;
  color: var(--ink);
}
.rg-btn--outline:hover {
  border-color: var(--brand);
  color: var(--brand);
  background: var(--brand-soft);
}

.rg-btn--block {
  width: 100%;
}

/* =========================================================================
   NAVBAR
   ========================================================================= */
.rg-header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: var(--white);
  border-bottom: 1px solid var(--line);
}

.rg-nav {
  max-width: 1400px;
  margin: 0 auto;
  height: 80px;
  padding: 0 2.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
}

.rg-brand__logo {
  height: 50px;
  width: auto;
  object-fit: contain;
  transition: transform 0.3s ease;
}
.rg-brand__logo:hover {
  transform: scale(1.05);
}

.rg-nav__links {
  display: flex;
  align-items: center;
  gap: 2.25rem;
  font-size: 0.88rem;
  font-weight: 500;
  color: var(--ink-soft);
}
.rg-nav__links a,
.rg-nav__link {
  text-decoration: none;
  color: inherit;
  transition: color 0.3s ease;
  padding-bottom: 0.35rem;
  position: relative;
  cursor: pointer;
}
.rg-nav__links a::after,
.rg-nav__link::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0;
  height: 2px;
  background: var(--brand);
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.rg-nav__links a:hover,
.rg-nav__link:hover {
  color: var(--brand);
}
.rg-nav__links a:hover::after,
.rg-nav__link:hover::after {
  width: 100%;
}

.rg-nav__actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

/* =========================================================================
   SPLIT LAYOUT
   ========================================================================= */
.rg-split {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 780px;
}

.rg-media {
  position: relative;
  overflow: hidden;
  background: var(--ink);
}
.rg-media__img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.rg-media__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(10, 8, 8, 0.9), rgba(10, 8, 8, 0.4) 55%, rgba(10, 8, 8, 0.2));
}
.rg-media__content {
  position: relative;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 3.5rem;
  color: var(--white);
}
.rg-media__badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  padding: 0.4rem 1rem;
  border-radius: var(--radius-full);
  font-size: 0.7rem;
  font-weight: 500;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  margin-bottom: 2rem;
  width: fit-content;
}
.rg-media__badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #48BB78;
  animation: pulse-dot 2s infinite;
}
@keyframes pulse-dot {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}
.rg-media__title {
  font-family: var(--font-serif);
  font-size: 2.4rem;
  font-weight: 500;
  line-height: 1.2;
  margin: 0 0 1.5rem;
}
.rg-media__list {
  list-style: none;
  margin: 0 0 2rem;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}
.rg-media__list li {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  font-size: 0.95rem;
  font-weight: 500;
}
.rg-media__list-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: rgba(200, 30, 58, 0.2);
  flex-shrink: 0;
}
.rg-media__list-icon :deep(.app-icon) {
  color: var(--brand);
}
.rg-media__footnote {
  display: flex;
  align-items: flex-start;
  gap: 0.65rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.15);
  max-width: 340px;
}
.rg-media__footnote p {
  margin: 0;
  font-size: 0.8rem;
  color: #D8D4D1;
  line-height: 1.6;
}

/* Panel del formulario */
.rg-form-panel {
  display: flex;
  align-items: center;
  padding: 3rem 3rem 3rem 0;
  background: var(--white);
}

.rg-card {
  width: 100%;
  max-width: 520px;
  margin: 0 auto;
  background: var(--white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-card);
  padding: 3rem;
  display: flex;
  flex-direction: column;
  gap: 1.4rem;
}

.rg-card__head-decoration {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}
.rg-card__head-line {
  flex: 1;
  height: 2px;
  background: linear-gradient(to right, var(--brand), transparent);
}
.rg-card__head-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--brand);
}
.rg-card__title {
  font-family: var(--font-serif);
  font-size: 2rem;
  font-weight: 500;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.rg-card__subtitle {
  font-size: 0.85rem;
  color: var(--muted);
  line-height: 1.6;
  margin: 0.5rem 0 0;
  max-width: 380px;
}

/* =========================================================================
   MENSAJES INFORMATIVOS
   ========================================================================= */
.rg-info :deep(.p-message-content) {
  background: var(--brand-soft) !important;
  border-radius: var(--radius-md) !important;
  border: none !important;
  padding: 1rem 1.2rem !important;
}
.rg-info :deep(.p-message-icon) {
  display: none !important;
}
.rg-info--welcome :deep(.p-message-content) {
  background: #EBF8FF !important;
}
.rg-info__body {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}
.rg-info__title {
  font-weight: 700;
  font-size: 0.82rem;
  margin: 0;
  color: var(--ink);
}
.rg-info__text {
  font-size: 0.78rem;
  color: var(--ink-soft);
  line-height: 1.5;
  margin: 0.2rem 0 0;
}

/* =========================================================================
   FORMULARIO
   ========================================================================= */
.rg-field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.rg-label {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--ink);
  flex-wrap: wrap;
}

.rg-label__badge {
  font-size: 0.6rem;
  font-weight: 400;
  color: var(--muted);
  background: var(--surface);
  padding: 0.1rem 0.5rem;
  border-radius: var(--radius-full);
  display: inline-flex;
  align-items: center;
}
.rg-label__badge--format {
  background: #EBF8FF;
  color: #2B6CB0;
  font-weight: 600;
}

.rg-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
}
.rg-input-icon {
  position: absolute;
  left: 0.75rem;
  display: flex;
  align-items: center;
  pointer-events: none;
  z-index: 2;
}

/* =========================================================================
   NICKNAME
   ========================================================================= */
.rg-input :deep(.p-inputtext),
.rg-input :deep(input) {
  width: 100% !important;
  border-radius: var(--radius-sm) !important;
  border: 1.5px solid #DDD8D5 !important;
  padding: 0.75rem 1rem !important;
  font-size: 0.88rem !important;
  font-family: inherit !important;
  color: var(--ink) !important;
  background: var(--white) !important;
  transition: all 0.2s ease !important;
  height: 42px !important;
  box-sizing: border-box !important;
}

.rg-input :deep(.p-inputtext:enabled:focus),
.rg-input :deep(input:focus) {
  border-color: var(--brand) !important;
  box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.12) !important;
}

/* Estados de validación */
.rg-input--valid :deep(.p-inputtext),
.rg-input--valid :deep(input) {
  border-color: var(--success) !important;
  background: var(--success-bg) !important;
}
.rg-input--error :deep(.p-inputtext),
.rg-input--error :deep(input) {
  border-color: var(--error) !important;
  background: var(--error-bg) !important;
}

/* =========================================================================
   PASSWORD
   ========================================================================= */
.rg-password {
  width: 100% !important;
}
.rg-password :deep(.p-password) {
  width: 100% !important;
}
.rg-password :deep(.p-password-input),
.rg-password :deep(input) {
  width: 100% !important;
  border-radius: var(--radius-sm) !important;
  border: 1.5px solid #DDD8D5 !important;
  padding: 0.75rem 2.8rem 0.75rem 2.6rem !important;
  font-size: 0.88rem !important;
  font-family: inherit !important;
  transition: all 0.2s ease !important;
  height: 42px !important;
  box-sizing: border-box !important;
  background: var(--white) !important;
  color: var(--ink) !important;
}

.rg-password :deep(.p-password-toggle-mask-icon) {
  color: var(--muted) !important;
  position: absolute !important;
  right: 0.75rem !important;
}

.rg-password :deep(.p-password-input:enabled:focus),
.rg-password :deep(input:focus) {
  border-color: var(--brand) !important;
  box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.12) !important;
}

.rg-password--valid :deep(.p-password-input),
.rg-password--valid :deep(input) {
  border-color: var(--success) !important;
  background: var(--success-bg) !important;
}
.rg-password--error :deep(.p-password-input),
.rg-password--error :deep(input) {
  border-color: var(--error) !important;
  background: var(--error-bg) !important;
}

/* =========================================================================
   OPCIONES (Recordar / Olvidé contraseña)
   ========================================================================= */
.rg-options {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.8rem;
  margin-top: -0.2rem;
}

.rg-remember {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--ink-soft);
}
.rg-remember :deep(.p-checkbox-box) {
  border-radius: 4px !important;
  border: 2px solid #DDD8D5 !important;
  width: 16px !important;
  height: 16px !important;
}
.rg-remember :deep(.p-checkbox-box.p-highlight) {
  background: var(--brand) !important;
  border-color: var(--brand) !important;
}
.rg-remember label {
  cursor: pointer;
}

.rg-forgot {
  color: var(--brand);
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s ease;
}
.rg-forgot:hover {
  color: var(--brand-dark);
  text-decoration: underline;
}

/* =========================================================================
   ERRORES Y HINTS
   ========================================================================= */
.rg-error {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.72rem;
  color: var(--error);
}
.rg-hint {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.72rem;
  color: var(--muted);
}
.rg-hint-wrapper {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

/* =========================================================================
   BOTÓN PRINCIPAL
   ========================================================================= */
.rg-submit {
  width: 100% !important;
  background: var(--brand) !important;
  border: none !important;
  border-radius: var(--radius-full) !important;
  padding: 0.9rem 1.5rem !important;
  font-weight: 700 !important;
  font-size: 0.85rem !important;
  letter-spacing: 0.03em !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 0.5rem !important;
  font-family: var(--font-sans) !important;
  transition: all 0.2s ease !important;
  color: var(--white) !important;
}
.rg-submit:enabled:hover {
  background: var(--brand-dark) !important;
  transform: translateY(-2px) !important;
  box-shadow: 0 6px 20px rgba(200, 30, 58, 0.35) !important;
}
.rg-submit:disabled {
  opacity: 0.5 !important;
  cursor: not-allowed !important;
}
.rg-submit__spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: var(--white);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

.rg-verification-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-top: 0.25rem;
}

.rg-verification-actions .rg-btn {
  background: none;
  border: none;
  padding: 0;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--brand);
  cursor: pointer;
}

.rg-verification-actions .rg-btn:hover:not(:disabled) {
  text-decoration: underline;
}

.rg-verification-actions .rg-btn:disabled {
  color: var(--gray-400, #9ca3af);
  cursor: not-allowed;
}

.rg-divider {
  position: relative;
  text-align: center;
  color: var(--muted-light);
  font-size: 0.78rem;
}
.rg-divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background: var(--line);
}
.rg-divider span {
  position: relative;
  background: var(--white);
  padding: 0 0.75rem;
}

.rg-card__footnote {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  text-align: center;
  font-size: 0.72rem;
  color: var(--muted);
  margin: 0.25rem 0 0;
}

/* =========================================================================
   TRUST BANNER
   ========================================================================= */
.rg-trust {
  max-width: 1400px;
  margin: 0 auto;
  padding: 3.5rem 2.5rem;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
}
.rg-trust__item {
  display: flex;
  align-items: flex-start;
  gap: 0.9rem;
}
.rg-trust__icon-wrapper {
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  border-radius: 10px;
  background: var(--brand-soft);
  display: flex;
  align-items: center;
  justify-content: center;
}
.rg-trust__title {
  font-weight: 600;
  font-size: 0.9rem;
  margin: 0;
  color: var(--ink);
  display: flex;
  align-items: center;
  gap: 0.3rem;
}
.rg-trust__text {
  font-size: 0.78rem;
  color: var(--muted);
  line-height: 1.5;
  margin: 0.3rem 0 0;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1100px) {
  .rg-split {
    grid-template-columns: 1fr;
  }
  .rg-media {
    min-height: 420px;
  }
  .rg-form-panel {
    padding: 3rem 2.5rem;
  }
  .rg-trust {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 900px) {
  .rg-nav__links {
    display: none;
  }
}

@media (max-width: 720px) {
  .rg-card {
    padding: 2rem;
    max-width: 100%;
  }
  .rg-form-panel {
    padding: 2rem 1.25rem;
  }
  .rg-trust {
    grid-template-columns: 1fr;
    padding: 2.5rem 1.25rem;
  }
  .rg-nav {
    padding: 0 1.25rem;
    height: 70px;
  }
  .rg-brand__logo {
    height: 40px;
  }
  .rg-nav__actions .rg-btn--ghost {
    display: none;
  }
  .rg-media__content {
    padding: 2rem;
  }
  .rg-media__title {
    font-size: 1.8rem;
  }
  .rg-card__title {
    font-size: 1.6rem;
  }
  .rg-options {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }
}
</style>