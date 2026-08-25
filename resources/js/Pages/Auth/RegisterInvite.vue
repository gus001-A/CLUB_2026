<!-- resources/js/Pages/Auth/RegisterInvite.vue -->
<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { usePrimeVue } from 'primevue/config';

// Importaciones de PrimeVue
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import Message from 'primevue/message';
import AppIcon from '@/Components/AppIcon.vue';
import ToastNotification from '@/Components/ToastNotification.vue';

// -----------------------------------------------------------------------
// Localización en español para el DatePicker (y demás componentes PrimeVue)
// -----------------------------------------------------------------------
const $primevue = usePrimeVue();

onMounted(() => {
  $primevue.config.locale = {
    firstDayOfWeek: 1,
    dayNames: ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'],
    dayNamesShort: ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'],
    dayNamesMin: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
    monthNames: [
      'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
      'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
    ],
    monthNamesShort: ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'],
    today: 'Hoy',
    clear: 'Limpiar',
    weekHeader: 'Sem',
    dateFormat: 'dd/mm/yy',
    weak: 'Débil',
    medium: 'Media',
    strong: 'Segura',
    passwordPrompt: 'Ingresa una contraseña',
    emptyFilterMessage: 'No se encontraron resultados',
    emptyMessage: 'No hay opciones disponibles',
    accept: 'Sí',
    reject: 'No',
    choose: 'Elegir',
    upload: 'Subir',
    cancel: 'Cancelar',
  };
});

// -----------------------------------------------------------------------
// Opciones
// -----------------------------------------------------------------------
const profileTypes = [
  {
    label: 'Pareja',
    value: 'pareja',
    icon: 'pi pi-users',
    iconSelected: 'pi pi-user-plus',
    color: '#C81E3A',
    description: 'Para parejas que buscan nuevas experiencias'
  },
  {
    label: 'Hombre',
    value: 'hombre',
    icon: 'pi pi-user',
    iconSelected: 'pi pi-user-check',
    color: '#2B6CB0',
    description: 'Perfil individual masculino'
  },
  {
    label: 'Mujer',
    value: 'mujer',
    icon: 'pi pi-user',
    iconSelected: 'pi pi-user-check',
    color: '#D53F8C',
    description: 'Perfil individual femenino'
  },
  {
    label: 'Trans',
    value: 'trans',
    icon: 'pi pi-user',
    iconSelected: 'pi pi-user-check',
    color: '#805AD5',
    description: 'Perfil individual trans'
  },
  {
    label: 'Otro',
    value: 'otro',
    icon: 'pi pi-users',
    iconSelected: 'pi pi-user-plus',
    color: '#718096',
    description: 'Identidad no especificada'
  },
];

// -----------------------------------------------------------------------
// Formulario
// -----------------------------------------------------------------------
const form = useForm({
  invite_code: '',
  profile_type: 'pareja',
  profile_other: '', // 👈 NUEVO: campo para cuando selecciona "Otro"
  nickname: '',
  email: '',
  password: '',
  password_confirmation: '',
  city: null,
  phone: '',
  birthdate: null,
  accepts_terms: false,
  verification_code: '',
});

const focusedField = ref(null);
const touchedFields = ref({});

// =======================================================================
// VALIDACIONES EN TIEMPO REAL
// =======================================================================

// 1. Validación de código de invitación
const isInviteCodeValid = computed(() => {
  if (!form.invite_code) return false;
  return /^[A-Za-z0-9\-_]{8,}$/.test(form.invite_code);
});

const inviteCodeError = computed(() => {
  if (!form.invite_code || !touchedFields.value.invite_code) return '';
  if (form.invite_code.length < 8) {
    return 'El código debe tener al menos 8 caracteres';
  }
  if (!/^[A-Za-z0-9\-_]+$/.test(form.invite_code)) {
    return 'Solo letras, números, guiones y guiones bajos';
  }
  return '';
});

// 2. Validación de nickname
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

// 3. Validación de email
const isEmailValid = computed(() => {
  if (!form.email) return false;
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email);
});

const emailError = computed(() => {
  if (!form.email || !touchedFields.value.email) return '';
  if (!isEmailValid.value) {
    return 'Ingresa un correo electrónico válido';
  }
  return '';
});

// 4. Validación de contraseña
const passwordStrength = computed(() => {
  const pwd = form.password;
  if (!pwd) return { score: 0, label: 'Débil', color: '#E53E3E' };

  let score = 0;
  if (pwd.length >= 8) score++;
  if (/[A-Z]/.test(pwd)) score++;
  if (/[0-9]/.test(pwd)) score++;
  if (/[^A-Za-z0-9]/.test(pwd)) score++;

  const levels = [
    { score: 4, label: 'Segura', color: '#48BB78' },
    { score: 3, label: 'Buena', color: '#48BB78' },
    { score: 2, label: 'Media', color: '#ECC94B' },
    { score: 1, label: 'Débil', color: '#E53E3E' },
    { score: 0, label: 'Muy débil', color: '#E53E3E' },
  ];

  return levels.find(l => l.score === Math.min(score, 4)) || levels[4];
});

const isPasswordValid = computed(() => {
  if (!form.password) return false;
  return passwordStrength.value.score >= 3;
});

const passwordErrors = computed(() => {
  if (!form.password || !touchedFields.value.password) return [];
  const errors = [];
  if (form.password.length < 8) {
    errors.push('Mínimo 8 caracteres');
  }
  if (!/[A-Z]/.test(form.password)) {
    errors.push('Al menos una mayúscula');
  }
  if (!/[a-z]/.test(form.password)) {
    errors.push('Al menos una minúscula');
  }
  if (!/[0-9]/.test(form.password)) {
    errors.push('Al menos un número');
  }
  if (!/[^A-Za-z0-9]/.test(form.password)) {
    errors.push('Al menos un carácter especial');
  }
  return errors;
});

// 5. Validación de confirmación de contraseña
const isPasswordConfirmationValid = computed(() => {
  if (!form.password_confirmation || !form.password) return false;
  return form.password === form.password_confirmation;
});

const passwordConfirmationError = computed(() => {
  if (!form.password_confirmation || !touchedFields.value.password_confirmation) return '';
  if (!isPasswordConfirmationValid.value) {
    return 'Las contraseñas no coinciden';
  }
  return '';
});

// 6. Validación de teléfono - SOLO 10 DÍGITOS NUMÉRICOS
const isPhoneValid = computed(() => {
  if (!form.phone) return true;
  return /^[0-9]{10}$/.test(form.phone);
});

const phoneError = computed(() => {
  if (!form.phone || !touchedFields.value.phone) return '';
  const cleanPhone = form.phone.replace(/\D/g, '');
  if (!/^[0-9]+$/.test(cleanPhone)) {
    return 'Solo se permiten números';
  }
  if (cleanPhone.length !== 10) {
    return 'El número debe tener exactamente 10 dígitos';
  }
  return '';
});

// 7. Validación de fecha de nacimiento
const isBirthdateValid = computed(() => {
  if (!form.birthdate) return false;
  const today = new Date();
  const birthDate = new Date(form.birthdate);
  let age = today.getFullYear() - birthDate.getFullYear();
  const monthDiff = today.getMonth() - birthDate.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
    age--;
  }
  return age >= 18 && age <= 120;
});

const birthdateError = computed(() => {
  if (!form.birthdate || !touchedFields.value.birthdate) return '';
  const today = new Date();
  const birthDate = new Date(form.birthdate);

  if (birthDate > today) {
    return 'La fecha no puede ser futura';
  }

  let age = today.getFullYear() - birthDate.getFullYear();
  const monthDiff = today.getMonth() - birthDate.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
    age--;
  }

  if (age < 18) {
    return 'Debes ser mayor de 18 años';
  }
  if (age > 120) {
    return 'Por favor verifica tu fecha de nacimiento';
  }
  return '';
});

// 8. Validación de términos
const isTermsValid = computed(() => form.accepts_terms === true);

const termsError = computed(() => {
  if (!isTermsValid.value && touchedFields.value.accepts_terms) {
    return 'Debes aceptar los términos y condiciones';
  }
  return '';
});

// 9. Validación de tipo de perfil - NUEVA validación para "Otro"
const isProfileTypeValid = computed(() => {
  if (form.profile_type === 'otro') {
    // Si es "Otro", debe haber escrito algo en profile_other
    return form.profile_other && form.profile_other.trim().length >= 2;
  }
  return profileTypes.some(p => p.value === form.profile_type);
});

const profileOtherError = computed(() => {
  if (form.profile_type !== 'otro') return '';
  if (!touchedFields.value.profile_other) return '';
  if (!form.profile_other || form.profile_other.trim().length < 2) {
    return 'Por favor, escribe tu identidad (mínimo 2 caracteres)';
  }
  return '';
});

// =======================================================================
// VALIDACIÓN GENERAL DEL FORMULARIO
// =======================================================================
const isFormValid = computed(() => {
  return (
    isInviteCodeValid.value &&
    isNicknameValid.value &&
    isEmailValid.value &&
    isPasswordValid.value &&
    isPasswordConfirmationValid.value &&
    isPhoneValid.value &&
    isBirthdateValid.value &&
    isTermsValid.value &&
    isProfileTypeValid.value
  );
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

// Watch para limpiar caracteres no permitidos en teléfono - SOLO NÚMEROS
watch(() => form.phone, (newVal) => {
  if (newVal) {
    let cleaned = newVal.replace(/\D/g, '');
    if (cleaned.length > 10) {
      cleaned = cleaned.slice(0, 10);
    }
    form.phone = cleaned;
  }
});

// Watch para limpiar profile_other cuando cambia el tipo de perfil
watch(() => form.profile_type, () => {
  if (form.profile_type !== 'otro') {
    form.profile_other = '';
  }
});

// =======================================================================
// VERIFICACIÓN DE CORREO (paso previo a crear la cuenta)
// =======================================================================
const showVerification = ref(false);
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

// Si cambia el correo después de haber pedido un código, ese código ya no
// sirve para el correo nuevo — regresamos al formulario para que pida uno
// nuevo en vez de dejar que intente confirmar con un código desincronizado.
watch(() => form.email, () => {
  if (showVerification.value) {
    showVerification.value = false;
    form.verification_code = '';
  }
});

function enviarCodigo() {
  if (resendCooldown.value > 0) return;
  form.post(route('register.invite.enviar-codigo'), {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      showVerification.value = true;
      startCooldown();
    },
  });
}

// Paso 1: valida todo el formulario (igual que antes) y, si está bien,
// pide el código de verificación en vez de crear la cuenta de una vez.
function irAVerificar() {
  const fields = ['invite_code', 'nickname', 'email', 'password', 'password_confirmation', 'birthdate', 'accepts_terms', 'phone'];
  fields.forEach(field => {
    touchedFields.value[field] = true;
  });

  if (form.profile_type === 'otro') {
    touchedFields.value.profile_other = true;
  }

  if (!isFormValid.value) {
    const firstError = document.querySelector('.rg-error:not(.rg-password-errors .rg-error)');
    if (firstError) {
      firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return;
  }

  enviarCodigo();
}

// Paso 2: ya con el código en mano, se crea la cuenta de verdad.
function submit() {
  if (form.phone) {
    form.phone = form.phone.replace(/\D/g, '');
  }

  // Si es "otro" y tiene valor, lo enviamos
  if (form.profile_type === 'otro' && form.profile_other) {
    form.profile_type = form.profile_other.trim(); // Enviamos el texto personalizado
  }

  form.post(route('register.invite.store'), {
    onSuccess: () => {
      form.reset('password', 'password_confirmation');
    },
  });
}
</script>

<template>

  <Head title="Crear tu cuenta" />

  <div class="rg-page">
    <!-- Toast Notification -->
    <ToastNotification :duration="5000" />

    <!-- ================= NAVBAR ================= -->
    <header class="rg-header">
      <nav class="rg-nav">
        <Link href="/" class="rg-brand">
          <img src="/images/LOGO.png" alt="Club de Fantasías" class="rg-brand__logo" />
        </Link>

        <div class="rg-nav__links">
          <Link href="/" class="rg-nav__link">Inicio</Link>
          <a href="/#quienes-somos">Quiénes somos</a>
          <a href="/#servicios">Servicios</a>
          <a href="/#eventos">Eventos</a>
          <a href="/#contacto">Contacto</a>
        </div>

        <div class="rg-nav__actions">
          <Link :href="route('login')" class="rg-btn rg-btn--ghost">
            Iniciar sesión
          </Link>
          <Link :href="route('register.invite')" class="rg-btn rg-btn--primary">
            Registro
          </Link>
        </div>
      </nav>
    </header>

    <!-- ================= SPLIT SECTION ================= -->
    <section class="rg-split">
      <!-- Panel izquierdo -->
      <div class="rg-media">
        <img src="/images/register.png" alt="Pareja en un ambiente exclusivo" class="rg-media__img" />
        <div class="rg-media__overlay"></div>

        <div class="rg-media__content">
          <div class="rg-media__badge">
            <span class="rg-media__badge-dot"></span>
            <AppIcon name="shield-check" />
            <span>Comunidad verificada</span>
          </div>

          <h1 class="rg-media__title">
            Únete a una comunidad exclusiva<br />
            para <span class="rg-accent">adultos.</span>
          </h1>

          <ul class="rg-media__list">
            <li>
              <span class="rg-media__list-icon">
                <AppIcon name="shield" />
              </span>
              Perfiles verificados
            </li>
            <li>
              <span class="rg-media__list-icon">
                <AppIcon name="lock" />
              </span>
              Privacidad garantizada
            </li>
            <li>
              <span class="rg-media__list-icon">
                <AppIcon name="users" />
              </span>
              Comunidad exclusiva
            </li>
            <li>
              <span class="rg-media__list-icon">
                <AppIcon name="heart" />
              </span>
              Conexiones auténticas
            </li>
          </ul>

          <div class="rg-media__footnote">
            <span class="rg-media__footnote-icon">
              <AppIcon name="shield-check" />
            </span>
            <p>Tus datos están protegidos y serán tratados de forma confidencial.</p>
          </div>
        </div>
      </div>

      <!-- Panel derecho: formulario -->
      <div class="rg-form-panel">
        <form class="rg-card" @submit.prevent="submit">
          <!-- Header -->
          <div class="rg-card__head">
            <div class="rg-card__head-decoration">
              <span class="rg-card__head-line"></span>
              <span class="rg-card__head-dot"></span>
            </div>
            <div>
              <h2 class="rg-card__title">
                <AppIcon name="sparkles" />
                Crear tu <span class="rg-accent">cuenta</span>
              </h2>
              <p class="rg-card__subtitle">
                Completa tus datos para unirte a nuestra comunidad exclusiva por invitación.
              </p>
            </div>
          </div>

          <!-- Mensaje informativo de invitación -->
          <Message severity="info" :closable="false" class="rg-info rg-info--invite">
            <div class="rg-info__body">
              <span class="rg-info__icon">
                <AppIcon name="gift" />
              </span>
              <div>
                <p class="rg-info__title">Acceso por invitación</p>
                <p class="rg-info__text">Necesitas un código válido para formar parte de Club de Fantasías.</p>
              </div>
            </div>
          </Message>

          <!-- ============================================================= -->
          <!-- CÓDIGO DE INVITACIÓN                                           -->
          <!-- ============================================================= -->
          <div class="rg-field rg-field--highlight">
            <label class="rg-label" for="invite_code">
              <AppIcon name="gift" />
              Código de invitación
              <span class="rg-label__badge">Obligatorio</span>
              <span class="rg-label__badge rg-label__badge--format">Mín 8 caracteres</span>
            </label>
            <div class="rg-input-wrapper">
              <InputText id="invite_code" v-model="form.invite_code" placeholder="Ej: CLUB-2026-FANTASIA"
                class="rg-input rg-input--large" :class="{
                  'rg-input--error': inviteCodeError && touchedFields.invite_code,
                  'rg-input--focused': focusedField === 'invite_code'
                }" @focus="focusedField = 'invite_code'" @blur="handleBlur('invite_code')"
                @input="markTouched('invite_code')" />
            </div>
            <div class="rg-hint-wrapper">
              <small v-if="inviteCodeError && touchedFields.invite_code" class="rg-error">
                <AppIcon name="alert-circle" />
                {{ inviteCodeError }}
              </small>
            </div>
          </div>

          <!-- ============================================================= -->
          <!-- TIPO DE PERFIL                                                -->
          <!-- ============================================================= -->
          <div class="rg-field rg-field--profile">
            <label class="rg-label rg-label--profile">
              <AppIcon name="users" />
              Tipo de perfil
              <span class="rg-label__badge">Selecciona uno</span>
            </label>

            <div class="rg-profile-group">
              <button v-for="option in profileTypes" :key="option.value" type="button" class="rg-profile-btn"
                :class="{ 'rg-profile-btn--active': form.profile_type === option.value }" :style="{
                  '--btn-color': option.color,
                  '--btn-bg': form.profile_type === option.value ? option.color + '12' : 'transparent'
                }" @click="form.profile_type = option.value; markTouched('profile_type')">
                <span class="rg-profile-btn__icon"
                  :style="{ color: form.profile_type === option.value ? option.color : '#9CA3AF' }">
                  <AppIcon :name="form.profile_type === option.value ? option.iconSelected : option.icon" />
                </span>
                <span class="rg-profile-btn__label">{{ option.label }}</span>
              </button>
            </div>

            <!-- 👇 NUEVO: Campo de texto para "Otro" -->
            <div v-if="form.profile_type === 'otro'" class="rg-field rg-field--other-profile">
              <label class="rg-label" for="profile_other">
                <AppIcon name="edit" />
                Especifica tu identidad
                <span class="rg-label__badge">Obligatorio</span>
              </label>
              <div class="rg-input-wrapper">
                <InputText id="profile_other" v-model="form.profile_other"
                  placeholder="Ej: No binario, Género fluido, Otro..." class="rg-input" :class="{
                    'rg-input--error': profileOtherError && touchedFields.profile_other,
                    'rg-input--focused': focusedField === 'profile_other'
                  }" @focus="focusedField = 'profile_other'" @blur="handleBlur('profile_other')"
                  @input="markTouched('profile_other')" />
              </div>
              <div class="rg-hint-wrapper">
                <small v-if="profileOtherError && touchedFields.profile_other" class="rg-error">
                  <AppIcon name="alert-circle" />
                  {{ profileOtherError }}
                </small>
                <small v-else class="rg-hint">
                  <AppIcon name="info" />
                  Escribe tu identidad de género o cómo te identificas
                </small>
              </div>
            </div>
          </div>

          <!-- Nickname / Correo -->
          <div class="rg-field-row">
            <div class="rg-field">
              <label class="rg-label" for="nickname">
                <AppIcon name="user" />
                Nickname
                <span class="rg-label__badge">Obligatorio</span>
              </label>
              <div class="rg-input-wrapper">
                <InputText id="nickname" v-model="form.nickname" placeholder="Ej: Juan_86, Maria_123" class="rg-input"
                  :class="{
                    'rg-input--error': nicknameError && touchedFields.nickname,
                    'rg-input--focused': focusedField === 'nickname'
                  }" @focus="focusedField = 'nickname'" @blur="handleBlur('nickname')"
                  @input="markTouched('nickname')" />
              </div>
              <div class="rg-hint-wrapper">
                <small v-if="nicknameError && touchedFields.nickname" class="rg-error">
                  <AppIcon name="alert-circle" />
                  {{ nicknameError }}
                </small>
              </div>
            </div>

            <div class="rg-field">
              <label class="rg-label" for="email">
                <AppIcon name="mail" />
                Correo electrónico
                <span class="rg-label__badge">Obligatorio</span>
              </label>
              <div class="rg-input-wrapper">
                <span class="rg-input-icon">
                  <AppIcon name="mail" />
                </span>
                <InputText id="email" v-model="form.email" type="email" placeholder="ejemplo@correo.com"
                  class="rg-input" :class="{
                    'rg-input--error': emailError && touchedFields.email,
                    'rg-input--focused': focusedField === 'email'
                  }" @focus="focusedField = 'email'" @blur="handleBlur('email')" @input="markTouched('email')" />
              </div>
              <div class="rg-hint-wrapper">
                <small v-if="emailError && touchedFields.email" class="rg-error">
                  <AppIcon name="alert-circle" />
                  {{ emailError }}
                </small>
              </div>
            </div>
          </div>

          <!-- Contraseñas -->
          <div class="rg-field-row">
            <div class="rg-field">
              <label class="rg-label" for="password">
                <AppIcon name="lock" />
                Contraseña
                <span class="rg-label__badge">Obligatorio</span>
                <span class="rg-label__badge rg-label__badge--format">Segura</span>
              </label>
              <div class="rg-input-wrapper">
                <span class="rg-input-icon">
                  <AppIcon name="lock" />
                </span>
                <Password id="password" v-model="form.password" placeholder="Crea una contraseña segura"
                  class="rg-password" input-class="rg-password__input" :class="{
                    'rg-password--error': passwordErrors.length > 0 && touchedFields.password
                  }" :feedback="false" :toggle-mask="true" @focus="focusedField = 'password'"
                  @blur="handleBlur('password')" @input="markTouched('password')" />
              </div>

              <!-- Barra de fortaleza de contraseña DEBAJO del input -->
              <div v-if="form.password && touchedFields.password" class="rg-password-strength-below">
                <div class="rg-password-strength__bar">
                  <div class="rg-password-strength__fill" :style="{
                    width: `${(passwordStrength.score / 4) * 100}%`,
                    backgroundColor: passwordStrength.color
                  }"></div>
                </div>
                <span class="rg-password-strength__label" :style="{ color: passwordStrength.color }">
                  {{ passwordStrength.label }}
                </span>
              </div>

              <div v-if="passwordErrors.length > 0 && touchedFields.password" class="rg-password-errors">
                <small v-for="(error, index) in passwordErrors" :key="index" class="rg-error">
                  <AppIcon name="x-circle" />
                  {{ error }}
                </small>
              </div>
            </div>

            <div class="rg-field">
              <label class="rg-label" for="password_confirmation">
                <AppIcon name="lock" />
                Confirmar contraseña
                <span class="rg-label__badge">Obligatorio</span>
              </label>
              <div class="rg-input-wrapper">
                <span class="rg-input-icon">
                  <AppIcon name="lock" />
                </span>
                <Password id="password_confirmation" v-model="form.password_confirmation"
                  placeholder="Confirma tu contraseña" class="rg-password" input-class="rg-password__input" :class="{
                    'rg-password--error': passwordConfirmationError && touchedFields.password_confirmation
                  }" :feedback="false" :toggle-mask="true" @focus="focusedField = 'password_confirmation'"
                  @blur="handleBlur('password_confirmation')" @input="markTouched('password_confirmation')" />
              </div>
              <div class="rg-hint-wrapper">
                <small v-if="passwordConfirmationError && touchedFields.password_confirmation" class="rg-error">
                  <AppIcon name="alert-circle" />
                  {{ passwordConfirmationError }}
                </small>
              </div>
            </div>
          </div>

          <!-- Ciudad / Teléfono -->
          <div class="rg-field-row">
            <div class="rg-field">
              <label class="rg-label" for="city">
                <AppIcon name="map-pin" />
                Ciudad
                <span class="rg-label__badge">Opcional</span>
              </label>
              <div class="rg-input-wrapper">
                <span class="rg-input-icon">
                  <AppIcon name="map-pin" />
                </span>
                <InputText id="city" v-model="form.city" placeholder="Ej: Ciudad de México" class="rg-input" :class="{
                  'rg-input--focused': focusedField === 'city'
                }" @focus="focusedField = 'city'" @blur="handleBlur('city')" />
              </div>
            </div>

            <div class="rg-field">
              <label class="rg-label" for="phone">
                <AppIcon name="phone" />
                Teléfono
                <span class="rg-label__badge">Opcional</span>
                <span class="rg-label__badge rg-label__badge--format">10 dígitos</span>
              </label>
              <div class="rg-input-wrapper">
                <InputText id="phone" v-model="form.phone" placeholder="3001234567" class="rg-input rg-input--no-icon"
                  :class="{
                    'rg-input--focused': focusedField === 'phone',
                    'rg-input--error': phoneError && touchedFields.phone
                  }" @focus="focusedField = 'phone'" @blur="handleBlur('phone')" @input="markTouched('phone')" />
              </div>
              <div class="rg-hint-wrapper">
                <small v-if="phoneError && touchedFields.phone" class="rg-error">
                  <AppIcon name="alert-circle" />
                  {{ phoneError }}
                </small>
              </div>
            </div>
          </div>

          <!-- Fecha de nacimiento -->
          <div class="rg-field">
            <label class="rg-label" for="birthdate">
              <AppIcon name="calendar" />
              Fecha de nacimiento
              <span class="rg-label__badge">Obligatorio</span>
              <span class="rg-label__badge rg-label__badge--age">+18</span>
            </label>
            <div class="rg-input-wrapper">
              <span class="rg-input-icon">
                <AppIcon name="calendar" />
              </span>
              <DatePicker id="birthdate" v-model="form.birthdate" date-format="dd/mm/yy" placeholder="DD / MM / AAAA"
                show-icon icon-display="input" class="rg-date" :class="{
                  'rg-input--error': birthdateError && touchedFields.birthdate
                }" :max-date="new Date(new Date().getFullYear() - 18, new Date().getMonth(), new Date().getDate())"
                @focus="focusedField = 'birthdate'" @blur="handleBlur('birthdate')"
                @date-select="markTouched('birthdate')" />
            </div>
            <div class="rg-hint-wrapper">
              <small v-if="birthdateError && touchedFields.birthdate" class="rg-error">
                <AppIcon name="alert-circle" />
                {{ birthdateError }}
              </small>
            </div>
          </div>

          <!-- ============================================================= -->
          <!-- TÉRMINOS Y CONDICIONES                                        -->
          <!-- ============================================================= -->
          <div class="rg-terms-card" :class="{
            'rg-terms-card--error': termsError && touchedFields.accepts_terms,
            'rg-terms-card--valid': isTermsValid && touchedFields.accepts_terms,
          }">
            <label class="rg-terms" for="accepts_terms">
              <div class="rg-terms__checkbox-wrapper">
                <div class="rg-terms__custom-checkbox" :class="{
                  'rg-terms__custom-checkbox--checked': form.accepts_terms,
                  'rg-terms__custom-checkbox--error': termsError && touchedFields.accepts_terms
                }" @click="form.accepts_terms = !form.accepts_terms; markTouched('accepts_terms')">
                  <svg v-if="form.accepts_terms" class="rg-terms__custom-checkbox-icon" viewBox="0 0 24 24" fill="none"
                    stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                </div>
              </div>
              <span class="rg-terms__text">
                Acepto los
                <a href="/terminos" target="_blank" @click.stop class="rg-terms__link">Términos y Condiciones</a>
                y la
                <a href="/privacidad" target="_blank" @click.stop class="rg-terms__link">Política de Privacidad</a>
                de Club de Fantasías.
              </span>
            </label>

            <!-- Mensaje de error simplificado -->
            <div v-if="termsError && touchedFields.accepts_terms" class="rg-terms__error">
              <AppIcon name="alert-circle" />
              {{ termsError }}
            </div>
          </div>

          <!-- Paso 1: valida todo y pide el código -->
          <Button v-if="!showVerification" type="button" class="rg-submit" :loading="form.processing"
            :disabled="!isFormValid" @click="irAVerificar">
            <AppIcon v-if="!form.processing" name="mail" />
            <span v-if="form.processing" class="rg-submit__spinner"></span>
            <span>{{ form.processing ? 'Enviando código...' : 'VERIFICAR MI CORREO' }}</span>
          </Button>

          <!-- Paso 2: código de verificación + confirmar -->
          <template v-else>
            <Message severity="success" :closable="false" class="rg-info">
              <div class="rg-info__body">
                <span class="rg-info__icon">
                  <AppIcon name="mail" />
                </span>
                <div>
                  <p class="rg-info__title">Revisa tu correo</p>
                  <p class="rg-info__text">Te enviamos un código de 6 dígitos a <strong>{{ form.email }}</strong>. Revisa spam si no lo ves.</p>
                </div>
              </div>
            </Message>

            <div class="rg-field rg-field--highlight">
              <label class="rg-label" for="verification_code">
                <AppIcon name="shield-check" />
                Código de verificación
                <span class="rg-label__badge">Obligatorio</span>
              </label>
              <div class="rg-input-wrapper">
                <InputText id="verification_code" v-model="form.verification_code" type="text" inputmode="numeric"
                  maxlength="6" placeholder="000000" class="rg-input rg-input--large"
                  :class="{ 'rg-input--error': form.errors.verification_code }"
                  @input="form.verification_code = form.verification_code.replace(/\D/g, '').slice(0, 6)" />
              </div>
              <div class="rg-hint-wrapper">
                <small v-if="form.errors.verification_code" class="rg-error">{{ form.errors.verification_code }}</small>
              </div>
            </div>

            <Button type="submit" class="rg-submit" :loading="form.processing"
              :disabled="form.verification_code.length !== 6">
              <AppIcon v-if="!form.processing" name="user-plus" />
              <span v-if="form.processing" class="rg-submit__spinner"></span>
              <span>{{ form.processing ? 'Creando cuenta...' : 'CONFIRMAR Y CREAR CUENTA' }}</span>
            </Button>

            <div class="rg-verification-actions">
              <button type="button" class="rg-btn rg-btn--ghost" :disabled="resendCooldown > 0" @click="enviarCodigo">
                {{ resendCooldown > 0 ? `Reenviar código (${resendCooldown}s)` : 'Reenviar código' }}
              </button>
              <button type="button" class="rg-btn rg-btn--ghost" @click="showVerification = false">
                Editar mis datos
              </button>
            </div>
          </template>

          <div class="rg-divider"><span>o</span></div>

          <Link :href="route('login')" class="rg-btn rg-btn--outline rg-btn--block">
            Ya tengo cuenta / <span class="rg-accent">Iniciar sesión</span>
          </Link>

          <p class="rg-card__footnote">
            <AppIcon name="shield-check" />
            Perfiles verificados, privacidad garantizada y comunidad exclusiva.
          </p>
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

.rg-profile-btn__icon :deep(.app-icon) {
  width: 16px !important;
  height: 16px !important;
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

/* Iconos de términos */
.rg-terms__error :deep(.app-icon) {
  width: 14px !important;
  height: 14px !important;
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

  0%,
  100% {
    opacity: 1;
  }

  50% {
    opacity: 0.3;
  }
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
  max-width: 620px;
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
  border-radius: var(--radius-md) !important;
  border: none !important;
  padding: 1rem 1.2rem !important;
}

.rg-info :deep(.p-message-icon) {
  display: none !important;
}

.rg-info--invite :deep(.p-message-content) {
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

.rg-field--highlight {
  background: var(--brand-soft);
  padding: 1rem 1.25rem;
  border-radius: var(--radius-md);
  border-left: 3px solid var(--brand);
}

.rg-field--profile {
  margin: 0.25rem 0;
}

.rg-field--other-profile {
  margin-top: 0.75rem;
  padding: 0.75rem 1rem;
  background: #F7FAFC;
  border-radius: var(--radius-md);
  border-left: 3px solid #805AD5;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.rg-field-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
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

.rg-label--profile {
  margin-bottom: 0.35rem;
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

.rg-label__badge--age {
  background: #FED7D4;
  color: #C81E3A;
  font-weight: 600;
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
   INPUTS
   ========================================================================= */
.rg-input :deep(.p-inputtext),
.rg-input :deep(input) {
  width: 100%;
  border-radius: var(--radius-sm) !important;
  border: 1.5px solid #DDD8D5 !important;
  padding: 0.75rem 1rem !important;
  font-size: 0.88rem !important;
  font-family: inherit !important;
  color: var(--ink) !important;
  background: var(--white) !important;
  transition: all 0.2s ease !important;
}

/* Input con icono a la izquierda */
.rg-input-wrapper:has(.rg-input-icon) .rg-input :deep(.p-inputtext),
.rg-input-wrapper:has(.rg-input-icon) .rg-input :deep(input) {
  padding: 0.75rem 1rem 0.75rem 2.6rem !important;
}

/* Input grande SIN icono (para código de invitación) */
.rg-input--large :deep(.p-inputtext),
.rg-input--large :deep(input) {
  font-size: 1rem !important;
  padding: 0.9rem 1rem !important;
  letter-spacing: 0.5px !important;
}

/* Input SIN icono para nickname y teléfono */
.rg-input--no-icon :deep(.p-inputtext),
.rg-input--no-icon :deep(input) {
  padding: 0.75rem 1rem !important;
}

.rg-input :deep(.p-inputtext:enabled:focus),
.rg-input :deep(input:focus) {
  border-color: var(--brand) !important;
  box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.12) !important;
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
  width: 100%;
}

.rg-password :deep(.p-password) {
  width: 100%;
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
}

.rg-password :deep(.p-password-toggle-mask-icon) {
  color: var(--muted) !important;
}

/* Barra de fortaleza de contraseña DEBAJO del input */
.rg-password-strength-below {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-top: 0.25rem;
}

.rg-password-strength__bar {
  flex: 1;
  height: 4px;
  background: #EDF2F7;
  border-radius: 2px;
  overflow: hidden;
}

.rg-password-strength__fill {
  height: 100%;
  border-radius: 2px;
  transition: width 0.3s ease;
}

.rg-password-strength__label {
  font-size: 0.65rem;
  font-weight: 600;
  min-width: 45px;
  text-align: right;
}

.rg-password-errors {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.rg-password-errors .rg-error {
  font-size: 0.68rem;
}

/* =========================================================================
   DATEPICKER
   ========================================================================= */
.rg-date :deep(.p-datepicker-input),
.rg-date :deep(input) {
  width: 100% !important;
  border-radius: var(--radius-sm) !important;
  border: 1.5px solid #DDD8D5 !important;
  padding: 0.75rem 2.8rem 0.75rem 2.6rem !important;
  font-size: 0.88rem !important;
  font-family: inherit !important;
  transition: all 0.2s ease !important;
}

.rg-date :deep(.p-datepicker-trigger) {
  display: none !important;
}

/* Días con 3 letras */
.rg-date :deep(.p-datepicker th) {
  font-size: 0.75rem !important;
  font-weight: 600 !important;
  color: var(--muted) !important;
  padding: 0.4rem 0 !important;
}

.rg-date :deep(.p-datepicker th span) {
  font-size: 0.75rem !important;
  text-transform: capitalize !important;
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
   TIPO DE PERFIL
   ========================================================================= */
.rg-profile-group {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
}

.rg-profile-btn {
  --btn-color: #9CA3AF;
  --btn-bg: transparent;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.9rem;
  border: 1.5px solid #E5E7EB;
  border-radius: var(--radius-full);
  background: var(--white);
  font-family: var(--font-sans);
  font-size: 0.78rem;
  font-weight: 500;
  color: var(--ink-soft);
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.rg-profile-btn:hover {
  border-color: var(--btn-color);
  background: var(--btn-bg);
  transform: translateY(-1px);
}

.rg-profile-btn--active {
  border-color: var(--btn-color) !important;
  background: var(--btn-bg) !important;
  box-shadow: 0 0 0 2px rgba(var(--btn-color), 0.12);
  transform: translateY(-1px);
  color: var(--ink);
  font-weight: 600;
}

.rg-profile-btn--active .rg-profile-btn__label {
  color: var(--btn-color);
}

/* =========================================================================
   TÉRMINOS Y CONDICIONES
   ========================================================================= */
.rg-terms-card {
  border: 2px solid #E5E1DE;
  border-radius: var(--radius-md);
  padding: 1.25rem 1.5rem;
  background: var(--surface);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.rg-terms-card:hover {
  border-color: #D3CDC9;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.rg-terms-card--valid {
  border-color: var(--success);
  background: var(--success-bg);
  border-left: 4px solid var(--success);
}

.rg-terms-card--error {
  border-color: var(--error);
  background: var(--error-bg);
  border-left: 4px solid var(--error);
  animation: shake 0.4s ease;
}

@keyframes shake {

  0%,
  100% {
    transform: translateX(0);
  }

  25% {
    transform: translateX(-4px);
  }

  75% {
    transform: translateX(4px);
  }
}

.rg-terms {
  display: flex;
  align-items: flex-start;
  gap: 0.85rem;
  cursor: pointer;
}

.rg-terms__checkbox-wrapper {
  flex-shrink: 0;
  margin-top: 0.1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.rg-terms__custom-checkbox {
  width: 24px;
  height: 24px;
  min-width: 24px;
  min-height: 24px;
  border-radius: 7px;
  border: 2px solid #D3CDC9;
  background: var(--white);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  cursor: pointer;
  position: relative;
}

.rg-terms__custom-checkbox:hover {
  border-color: var(--brand);
}

.rg-terms__custom-checkbox--checked {
  background: var(--brand);
  border-color: var(--brand);
  box-shadow: 0 0 0 4px rgba(200, 30, 58, 0.14);
}

.rg-terms__custom-checkbox--error {
  border-color: var(--error);
  background: var(--error-bg);
}

.rg-terms__custom-checkbox--checked.rg-terms__custom-checkbox--error {
  background: var(--brand);
  border-color: var(--brand);
}

.rg-terms__custom-checkbox-icon {
  width: 16px;
  height: 16px;
  stroke: white;
  animation: checkmark 0.25s ease;
}

@keyframes checkmark {
  0% {
    transform: scale(0);
    opacity: 0;
  }

  50% {
    transform: scale(1.2);
  }

  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.rg-terms__text {
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--ink);
  line-height: 1.6;
  padding-top: 0.1rem;
}

.rg-terms__link {
  color: var(--brand);
  font-weight: 700;
  text-decoration: none;
  border-bottom: 1.5px solid rgba(200, 30, 58, 0.3);
  transition: border-color 0.2s ease;
}

.rg-terms__link:hover {
  border-bottom-color: var(--brand);
}

.rg-terms__error {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin-top: 0.6rem;
  padding: 0.4rem 0.75rem;
  background: var(--error-bg);
  border-radius: var(--radius-sm);
  font-size: 0.78rem;
  color: var(--error);
  font-weight: 500;
}

.rg-terms__error :deep(.app-icon) {
  color: var(--error);
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
  to {
    transform: rotate(360deg);
  }
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
  }

  .rg-field-row {
    grid-template-columns: 1fr;
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

  .rg-password-strength-below {
    flex-direction: column;
    align-items: stretch;
    gap: 0.3rem;
  }

  .rg-password-strength__label {
    text-align: left;
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

  .rg-profile-group {
    gap: 0.3rem;
  }

  .rg-profile-btn {
    padding: 0.4rem 0.75rem;
    font-size: 0.72rem;
  }

  .rg-terms-card {
    padding: 1rem;
  }

  .rg-terms {
    flex-direction: column;
    gap: 0.5rem;
  }

  .rg-terms__checkbox-wrapper {
    margin-top: 0;
  }

  .rg-field--other-profile {
    padding: 0.5rem 0.75rem;
  }
}
</style>