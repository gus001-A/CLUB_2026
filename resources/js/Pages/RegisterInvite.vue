<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import SelectButton from 'primevue/selectbutton';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import Message from 'primevue/message';

// -----------------------------------------------------------------------
// Opciones
// -----------------------------------------------------------------------
const profileTypes = [
  { label: 'Pareja', value: 'pareja', icon: 'pi pi-users' },
  { label: 'Hombre', value: 'hombre', icon: 'pi pi-user' },
  { label: 'Mujer', value: 'mujer', icon: 'pi pi-user' },
  { label: 'Trans', value: 'trans', icon: 'pi pi-sparkles' },
  { label: 'Otro', value: 'otro', icon: 'pi pi-comments' },
];

const cities = [
  { label: 'Ciudad de México', value: 'cdmx' },
  { label: 'Guadalajara', value: 'gdl' },
  { label: 'Monterrey', value: 'mty' },
  { label: 'Cuernavaca', value: 'cvca' },
  { label: 'Puebla', value: 'pue' },
];

// -----------------------------------------------------------------------
// Formulario (Inertia useForm: te da errores/processing/progress gratis)
// -----------------------------------------------------------------------
const form = useForm({
  invite_code: '',
  profile_type: 'pareja',
  nickname: '',
  email: '',
  password: '',
  password_confirmation: '',
  city: null,
  phone: '',
  birthdate: null,
  accepts_terms: false,
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const canSubmit = computed(() =>
  form.invite_code &&
  form.nickname &&
  form.email &&
  form.password &&
  form.password === form.password_confirmation &&
  form.accepts_terms
);

function submit() {
  form.post(route('register.invite'), {
    onSuccess: () => form.reset('password', 'password_confirmation'),
  });
}
</script>

<template>
  <Head title="Crear tu cuenta" />

  <div class="rg-page">
    <!-- ================= NAVBAR ================= -->
    <header class="rg-header">
      <nav class="rg-nav">
        <Link href="/" class="rg-brand">
          <span class="rg-brand__mark"></span>
          <span class="rg-brand__name">
            Club<br />de Fantasías
            <span class="rg-brand__tag">Todo puede suceder</span>
          </span>
        </Link>

        <div class="rg-nav__links">
          <a href="/#quienes-somos">Quiénes somos</a>
          <a href="/#servicios">Servicios</a>
          <a href="/#eventos">Eventos</a>
          <a href="/#contacto">Contacto</a>
        </div>

        <div class="rg-nav__actions">
          <Link :href="route('login')" class="rg-btn rg-btn--ghost">Iniciar sesión</Link>
          <Link :href="route('register.invite')" class="rg-btn rg-btn--primary">Registro</Link>
        </div>
      </nav>
    </header>

    <!-- ================= SPLIT: imagen + formulario ================= -->
    <section class="rg-split">
      <!-- Panel izquierdo -->
      <div class="rg-media">
        <img src="/images/register-couple.jpg" alt="Pareja en un ambiente exclusivo" class="rg-media__img" />
        <div class="rg-media__overlay"></div>

        <div class="rg-media__content">
          <h1 class="rg-media__title">
            Únete a una comunidad exclusiva<br />
            para <span class="rg-accent">adultos.</span>
          </h1>

          <ul class="rg-media__list">
            <li><i class="pi pi-verified"></i> Perfiles verificados</li>
            <li><i class="pi pi-lock"></i> Privacidad garantizada</li>
            <li><i class="pi pi-star"></i> Comunidad exclusiva</li>
          </ul>

          <div class="rg-media__footnote">
            <i class="pi pi-shield"></i>
            <p>Tus datos están protegidos y serán tratados de forma confidencial.</p>
          </div>
        </div>
      </div>

      <!-- Panel derecho: formulario -->
      <div class="rg-form-panel">
        <form class="rg-card" @submit.prevent="submit">
          <div class="rg-card__head">
            <div>
              <h2 class="rg-card__title">Crear tu <span class="rg-accent">cuenta</span></h2>
              <p class="rg-card__subtitle">
                Completa tus datos para unirte a nuestra comunidad exclusiva por invitación.
              </p>
            </div>
          </div>

          <Message severity="error" :closable="false" class="rg-info">
            <div class="rg-info__body">
              <i class="pi pi-info-circle"></i>
              <div>
                <p class="rg-info__title">Acceso por invitación.</p>
                <p class="rg-info__text">Necesitas un código válido para formar parte de Club de Fantasías.</p>
              </div>
            </div>
          </Message>

          <!-- Código de invitación -->
          <div class="rg-field">
            <label class="rg-label" for="invite_code">Código de invitación</label>
            <span class="rg-input-icon">
              <i class="pi pi-gift"></i>
              <InputText
                id="invite_code"
                v-model="form.invite_code"
                placeholder="Ingresa tu código de invitación"
                class="rg-input"
                :invalid="!!form.errors.invite_code"
              />
            </span>
            <small v-if="form.errors.invite_code" class="rg-error">{{ form.errors.invite_code }}</small>
          </div>

          <!-- Tipo de perfil -->
          <div class="rg-field">
            <label class="rg-label">Tipo de perfil</label>
            <SelectButton
              v-model="form.profile_type"
              :options="profileTypes"
              option-label="label"
              option-value="value"
              class="rg-profile-types"
            >
              <template #option="{ option }">
                <div class="rg-profile-type">
                  <i :class="option.icon"></i>
                  <span>{{ option.label }}</span>
                </div>
              </template>
            </SelectButton>
          </div>

          <!-- Nickname / correo -->
          <div class="rg-field-row">
            <div class="rg-field">
              <label class="rg-label" for="nickname">Nickname</label>
              <span class="rg-input-icon">
                <i class="pi pi-user"></i>
                <InputText
                  id="nickname"
                  v-model="form.nickname"
                  placeholder="Elige tu nickname"
                  class="rg-input"
                  :invalid="!!form.errors.nickname"
                />
              </span>
              <small v-if="form.errors.nickname" class="rg-error">{{ form.errors.nickname }}</small>
            </div>

            <div class="rg-field">
              <label class="rg-label" for="email">Correo electrónico</label>
              <span class="rg-input-icon">
                <i class="pi pi-envelope"></i>
                <InputText
                  id="email"
                  v-model="form.email"
                  type="email"
                  placeholder="tu@email.com"
                  class="rg-input"
                  :invalid="!!form.errors.email"
                />
              </span>
              <small v-if="form.errors.email" class="rg-error">{{ form.errors.email }}</small>
            </div>
          </div>

          <!-- Contraseñas -->
          <div class="rg-field-row">
            <div class="rg-field">
              <label class="rg-label" for="password">Contraseña</label>
              <Password
                id="password"
                v-model="form.password"
                placeholder="Crea una contraseña"
                class="rg-input rg-password"
                input-class="rg-password__input"
                :feedback="true"
                :toggle-mask="true"
                :invalid="!!form.errors.password"
              />
              <small v-if="form.errors.password" class="rg-error">{{ form.errors.password }}</small>
            </div>

            <div class="rg-field">
              <label class="rg-label" for="password_confirmation">Confirmar contraseña</label>
              <Password
                id="password_confirmation"
                v-model="form.password_confirmation"
                placeholder="Confirma tu contraseña"
                class="rg-input rg-password"
                input-class="rg-password__input"
                :feedback="false"
                :toggle-mask="true"
              />
              <small v-if="form.password && form.password_confirmation && form.password !== form.password_confirmation" class="rg-error">
                Las contraseñas no coinciden.
              </small>
            </div>
          </div>

          <!-- Ciudad / teléfono -->
          <div class="rg-field-row">
            <div class="rg-field">
              <label class="rg-label" for="city">Ciudad</label>
              <span class="rg-input-icon">
                <i class="pi pi-map-marker"></i>
                <Select
                  id="city"
                  v-model="form.city"
                  :options="cities"
                  option-label="label"
                  option-value="value"
                  placeholder="Selecciona tu ciudad"
                  class="rg-input rg-select"
                />
              </span>
            </div>

            <div class="rg-field">
              <label class="rg-label" for="phone">Teléfono (opcional)</label>
              <span class="rg-input-icon">
                <i class="pi pi-phone"></i>
                <InputText
                  id="phone"
                  v-model="form.phone"
                  placeholder="+57 300 123 4567"
                  class="rg-input"
                />
              </span>
            </div>
          </div>

          <!-- Fecha de nacimiento -->
          <div class="rg-field">
            <label class="rg-label" for="birthdate">Fecha de nacimiento</label>
            <span class="rg-input-icon">
              <i class="pi pi-calendar"></i>
              <DatePicker
                id="birthdate"
                v-model="form.birthdate"
                date-format="dd/mm/yy"
                placeholder="DD / MM / AAAA"
                show-icon
                icon-display="input"
                class="rg-input rg-date"
                :invalid="!!form.errors.birthdate"
              />
            </span>
            <small v-if="form.errors.birthdate" class="rg-error">{{ form.errors.birthdate }}</small>
          </div>

          <!-- Términos -->
          <div class="rg-terms">
            <Checkbox v-model="form.accepts_terms" input-id="accepts_terms" binary />
            <label for="accepts_terms">
              Acepto los <a href="/terminos" target="_blank">Términos y Condiciones</a> y la
              <a href="/privacidad" target="_blank">Política de Privacidad</a>
            </label>
          </div>

          <Button
            type="submit"
            label="REGISTRARME"
            class="rg-submit"
            :loading="form.processing"
            :disabled="!canSubmit"
          />

          <div class="rg-divider"><span>o</span></div>

          <Link :href="route('login')" class="rg-btn rg-btn--outline rg-btn--block">
            Ya tengo cuenta / <span class="rg-accent">Iniciar sesión</span>
          </Link>

          <p class="rg-card__footnote">
            <i class="pi pi-shield"></i>
            Perfiles verificados, privacidad garantizada y comunidad exclusiva.
          </p>
        </form>
      </div>
    </section>

    <!-- ================= FRANJA DE CONFIANZA ================= -->
    <section class="rg-trust">
      <div class="rg-trust__item">
        <i class="pi pi-verified"></i>
        <div>
          <p class="rg-trust__title">Validación de perfiles</p>
          <p class="rg-trust__text">Revisamos cada perfil para asegurar una comunidad auténtica.</p>
        </div>
      </div>
      <div class="rg-trust__item">
        <i class="pi pi-lock"></i>
        <div>
          <p class="rg-trust__title">Mayor seguridad</p>
          <p class="rg-trust__text">Protegemos tu información con los más altos estándares de seguridad.</p>
        </div>
      </div>
      <div class="rg-trust__item">
        <i class="pi pi-users"></i>
        <div>
          <p class="rg-trust__title">Comunidad exclusiva</p>
          <p class="rg-trust__text">Conecta con personas reales que buscan experiencias genuinas.</p>
        </div>
      </div>
      <div class="rg-trust__item">
        <i class="pi pi-eye-slash"></i>
        <div>
          <p class="rg-trust__title">Privacidad garantizada</p>
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

/* Botones genéricos de navbar/CTA de texto */
.rg-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-weight: 600;
  font-size: 0.85rem;
  border-radius: var(--radius-full);
  padding: 0.7rem 1.3rem;
  text-decoration: none;
  border: 1px solid transparent;
  cursor: pointer;
  transition: background-color 0.15s ease, border-color 0.15s ease;
}
.rg-btn--primary {
  background: var(--brand);
  color: var(--white);
}
.rg-btn--primary:hover {
  background: var(--brand-dark);
}
.rg-btn--ghost {
  background: transparent;
  border-color: #D9D5D2;
  color: var(--ink);
}
.rg-btn--ghost:hover {
  border-color: var(--ink);
}
.rg-btn--outline {
  background: transparent;
  border-color: #D9D5D2;
  color: var(--ink);
}
.rg-btn--outline:hover {
  border-color: var(--ink);
}
.rg-btn--block {
  width: 100%;
}

/* =========================================================================
   NAVBAR
   ========================================================================= */
.rg-header {
  border-bottom: 1px solid var(--line);
}

.rg-nav {
  max-width: 1400px;
  margin: 0 auto;
  height: 84px;
  padding: 0 2.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
}

.rg-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  text-decoration: none;
  color: var(--ink);
}
.rg-brand__mark {
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  background: var(--ink);
  clip-path: polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%);
}
.rg-brand__name {
  font-family: var(--font-serif);
  font-weight: 600;
  font-size: 1.15rem;
  line-height: 1.2;
}
.rg-brand__tag {
  display: block;
  font-family: var(--font-sans);
  font-size: 0.55rem;
  font-weight: 600;
  letter-spacing: 0.12em;
  color: var(--muted);
  margin-top: 0.15rem;
}

.rg-nav__links {
  display: flex;
  align-items: center;
  gap: 2.25rem;
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--ink);
}
.rg-nav__links a {
  text-decoration: none;
  color: inherit;
}
.rg-nav__links a:hover {
  color: var(--brand);
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
  position: relative;
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
  background: linear-gradient(to top, rgba(10, 8, 8, 0.85), rgba(10, 8, 8, 0.35) 55%, rgba(10, 8, 8, 0.2));
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
.rg-media__list i {
  color: var(--brand);
  font-size: 1rem;
}
.rg-media__footnote {
  display: flex;
  align-items: flex-start;
  gap: 0.65rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.15);
  max-width: 340px;
}
.rg-media__footnote i {
  color: var(--brand);
  margin-top: 0.15rem;
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
  max-width: 620px;
  margin: 0 auto;
  background: var(--white);
  border-radius: var(--radius-lg);
  box-shadow: 0 30px 60px -20px rgba(23, 20, 18, 0.18);
  padding: 3rem;
  display: flex;
  flex-direction: column;
  gap: 1.4rem;
}

.rg-card__title {
  font-family: var(--font-serif);
  font-size: 2rem;
  font-weight: 500;
  margin: 0;
}
.rg-card__subtitle {
  font-size: 0.85rem;
  color: var(--muted);
  line-height: 1.6;
  margin: 0.5rem 0 0;
  max-width: 380px;
}

/* Caja informativa de invitación (PrimeVue Message) */
.rg-info :deep(.p-message-content) {
  background: var(--brand-soft);
  border-radius: var(--radius-md);
  border: none;
  padding: 1rem 1.2rem;
}
.rg-info :deep(.p-message-icon) {
  display: none;
}
.rg-info__body {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}
.rg-info__body > i {
  color: var(--brand);
  font-size: 1.1rem;
  margin-top: 0.1rem;
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

/* Campos */
.rg-field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}
.rg-field-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}
.rg-label {
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--ink);
}
.rg-error {
  font-size: 0.72rem;
  color: var(--brand);
}

.rg-input-icon {
  position: relative;
  display: flex;
  align-items: center;
}
.rg-input-icon > i {
  position: absolute;
  left: 0.9rem;
  color: var(--muted);
  font-size: 0.9rem;
  z-index: 1;
  pointer-events: none;
}

/* Inputs de PrimeVue reestilizados al lenguaje de marca */
.rg-input :deep(.p-inputtext),
.rg-input.p-inputtext {
  width: 100%;
  border-radius: var(--radius-sm);
  border: 1px solid #DDD8D5;
  padding: 0.75rem 1rem 0.75rem 2.6rem;
  font-size: 0.88rem;
  font-family: inherit;
  color: var(--ink);
  background: var(--white);
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.rg-input :deep(.p-inputtext:enabled:focus),
.rg-input.p-inputtext:enabled:focus {
  border-color: var(--brand);
  box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
}
.rg-input :deep(.p-invalid) {
  border-color: var(--brand) !important;
}

/* Password (PrimeVue envuelve el input en un div propio) */
.rg-password {
  width: 100%;
}
.rg-password :deep(.p-password) {
  width: 100%;
}
.rg-password :deep(.p-password-input) {
  width: 100%;
  border-radius: var(--radius-sm);
  border: 1px solid #DDD8D5;
  padding: 0.75rem 2.6rem 0.75rem 1rem;
  font-size: 0.88rem;
  font-family: inherit;
}
.rg-password :deep(.p-password-input:enabled:focus) {
  border-color: var(--brand);
  box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
}
.rg-password :deep(.p-password-toggle-mask-icon) {
  color: var(--muted);
}

/* Select (ciudad) */
.rg-select :deep(.p-select) {
  width: 100%;
  border-radius: var(--radius-sm);
  border: 1px solid #DDD8D5;
  padding-left: 1.6rem;
}
.rg-select :deep(.p-select-label) {
  padding: 0.75rem 1rem 0.75rem 1rem;
  font-size: 0.88rem;
}
.rg-select :deep(.p-select:not(.p-disabled).p-focus) {
  border-color: var(--brand);
  box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
}

/* DatePicker */
.rg-date :deep(.p-datepicker-input) {
  width: 100%;
  border-radius: var(--radius-sm);
  border: 1px solid #DDD8D5;
  padding: 0.75rem 1rem 0.75rem 2.6rem;
  font-size: 0.88rem;
  font-family: inherit;
}
.rg-date :deep(.p-datepicker-input:enabled:focus) {
  border-color: var(--brand);
  box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
}
.rg-date :deep(.p-datepicker) {
  width: 100%;
}

/* Tipo de perfil (SelectButton con tarjetas) */
.rg-profile-types {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 0.6rem;
}
.rg-profile-types :deep(.p-togglebutton) {
  border-radius: var(--radius-sm);
  border: 1px solid #DDD8D5;
  background: var(--white);
  padding: 0.9rem 0.5rem;
}
.rg-profile-types :deep(.p-togglebutton.p-togglebutton-checked) {
  border-color: var(--brand);
  background: var(--brand-soft);
}
.rg-profile-type {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--ink-soft);
}
.rg-profile-type i {
  font-size: 1.1rem;
  color: var(--muted);
}
.rg-profile-types :deep(.p-togglebutton-checked) .rg-profile-type,
.rg-profile-types :deep(.p-togglebutton-checked) .rg-profile-type i {
  color: var(--brand);
}

/* Checkbox de términos */
.rg-terms {
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  font-size: 0.8rem;
  color: var(--ink-soft);
  line-height: 1.5;
}
.rg-terms :deep(.p-checkbox) {
  margin-top: 0.1rem;
}
.rg-terms :deep(.p-checkbox-box.p-highlight) {
  background: var(--brand);
  border-color: var(--brand);
}
.rg-terms a {
  color: var(--brand);
  font-weight: 600;
  text-decoration: none;
}
.rg-terms a:hover {
  text-decoration: underline;
}

/* Botón principal */
.rg-submit {
  width: 100%;
  background: var(--brand);
  border-color: var(--brand);
  border-radius: var(--radius-full);
  padding: 0.9rem 1.5rem;
  font-weight: 700;
  font-size: 0.85rem;
  letter-spacing: 0.03em;
}
.rg-submit:enabled:hover {
  background: var(--brand-dark);
  border-color: var(--brand-dark);
}
.rg-submit:disabled {
  opacity: 0.5;
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
.rg-card__footnote i {
  color: var(--brand);
}

/* =========================================================================
   FRANJA DE CONFIANZA
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
.rg-trust__item i {
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  border-radius: 10px;
  background: var(--brand-soft);
  color: var(--brand);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.05rem;
}
.rg-trust__title {
  font-weight: 600;
  font-size: 0.9rem;
  margin: 0;
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

@media (max-width: 720px) {
  .rg-nav__links {
    display: none;
  }
  .rg-card {
    padding: 2rem;
  }
  .rg-field-row {
    grid-template-columns: 1fr;
  }
  .rg-profile-types {
    grid-template-columns: repeat(3, 1fr);
  }
  .rg-form-panel {
    padding: 2rem 1.25rem;
  }
  .rg-trust {
    grid-template-columns: 1fr;
    padding: 2.5rem 1.25rem;
  }
}
</style>

