<!-- resources/js/Pages/Landing.vue -->
<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppIcon from '@/Components/AppIcon.vue';

// -----------------------------------------------------------------------
// Datos de la página
// -----------------------------------------------------------------------

const features = [
  { icon: 'shield-check', title: 'Comunidad verificada', text: 'Perfiles reales, validados para tu tranquilidad.' },
  { icon: 'map-pin', title: 'Geolocalización discreta', text: 'Conecta con personas cercanas sin revelar tu ubicación.' },
  { icon: 'lock', title: 'Privacidad y seguridad', text: 'Tecnología avanzada para proteger tu información.' },
  { icon: 'gem', title: 'Experiencias exclusivas', text: 'Acceso a eventos y experiencias únicas para miembros.' },
];

const mission = [
  { icon: 'ticket', title: 'Acceso por invitación', text: 'Solo miembros seleccionados por invitación.' },
  { icon: 'badge-check', title: 'Perfiles verificados', text: 'Validación de perfiles y sistema de reputación para mayor seguridad.' },
  { icon: 'map-pin', title: 'Conexiones reales', text: 'Geolocalización inteligente para interacciones en tiempo real.' },
  { icon: 'sparkles', title: 'Compatibilidad inteligente', text: 'Match dinámico basado en preferencias, intereses y comportamiento.' },
  { icon: 'calendar', title: 'Experiencias exclusivas', text: 'Eventos, fiestas y experiencias privadas para llevar tu estilo de vida al siguiente nivel.' },
  { icon: 'eye-off', title: 'Discreción y privacidad', text: 'Tu privacidad es nuestra prioridad. Entorno seguro, discreto y 100% confiable.' },
];

const services = [
  { title: 'Match inteligente', text: 'Perfiles compatibles según tus preferencias, ubicación y disponibilidad en tiempo real.', image: '/images/services/match.jpg' },
  { title: 'Geolocalización discreta', text: 'Conecta con personas cercanas con total privacidad y seguridad mediante zonas aproximadas.', image: '/images/services/geo.jpg' },
  { title: 'Modo activo', text: 'Activa tu disponibilidad y conecta al instante con perfiles compatibles que también están disponibles.', image: '/images/services/active.jpg' },
  { title: 'Validación y confianza', text: 'Perfiles verificados, sistema de reputación y comentarios para una comunidad 100% confiable.', image: '/images/services/trust.jpg' },
  { title: 'Puntos y recompensas', text: 'Gana, usa e intercambia puntos para acceder a beneficios, contenido y experiencias VIP.', image: '/images/services/rewards.jpg' },
  { title: 'Streaming y contenido premium', text: 'Transmisiones en vivo, contenido exclusivo e interacción privada con tus creadores favoritos.', image: '/images/services/streaming.jpg' },
  { title: 'Eventos y experiencias', text: 'Accede a fiestas exclusivas, eventos privados, viajes y experiencias inolvidables.', image: '/images/services/events.jpg' },
  { title: 'Cursos online', text: 'Aprende, explora y mejora tu experiencia con contenido educativo exclusivo.', image: '/images/services/courses.jpg' },
];

const experienceTypes = [
  { label: 'Fiestas privadas', image: '/images/experiences/parties.jpg' },
  { label: 'Club nights', image: '/images/experiences/clubnights.jpg' },
  { label: 'Eventos VIP', image: '/images/experiences/vip.jpg' },
  { label: 'Viajes temáticos', image: '/images/experiences/trips.jpg' },
  { label: 'Encuentros sociales', image: '/images/experiences/social.jpg' },
];

const events = [
  { day: '24', month: 'MAY', title: 'Noche de Seducción', location: 'Ciudad de México', time: '23:00 hrs', text: 'Una noche para romper la rutina y dejarte llevar por la pasión en un ambiente exclusivo.', image: '/images/events/seduction.jpg' },
  { day: '07', month: 'JUN', title: 'Jacuzzi Experience', location: 'Guadalajara', time: '20:00 hrs', text: 'Relájate, conecta y disfruta de una experiencia única bajo las estrellas.', image: '/images/events/jacuzzi.jpg' },
  { day: '21', month: 'JUN', title: 'Club Fantasías', location: 'Monterrey', time: '22:00 hrs', text: 'Música, show y mucha energía para vivir una noche que recordarás siempre.', image: '/images/events/club.jpg' },
];

const faqs = ref([
  { q: '¿Cómo funciona el acceso por invitación?', a: 'Cada miembro nuevo ingresa mediante una invitación validada por la comunidad o el equipo de Club de Fantasías, lo que nos permite mantener un ambiente seguro y de confianza.', open: false },
  { q: '¿Cómo garantizan la privacidad de los miembros?', a: 'Usamos geolocalización aproximada, verificación de perfiles y cifrado de datos para que tu identidad y ubicación exacta nunca se compartan sin tu consentimiento.', open: false },
  { q: '¿Qué tipo de eventos se organizan?', a: 'Desde fiestas privadas y club nights hasta viajes temáticos y encuentros sociales, siempre en espacios seleccionados y verificados.', open: false },
  { q: '¿Cómo se verifican los perfiles?', a: 'Cada perfil pasa por un proceso de validación de identidad y un sistema de reputación basado en la comunidad.', open: false },
]);

function toggleFaq(i) {
  faqs.value[i].open = !faqs.value[i].open;
}

const contactForm = ref({ nombre: '', correo: '', asunto: '', mensaje: '' });

function submitContact() {
  console.log('Enviar formulario de contacto', contactForm.value);
}
</script>

<template>
  <Head title="Inicio" />

  <div class="cf-page">
    <!-- ================= NAVBAR ================= -->
    <header class="cf-header">
      <nav class="cf-nav">
        <Link href="/" class="cf-brand">
          <span class="cf-brand__mark"></span>
          <span class="cf-brand__name">Club<br />de Fantasías</span>
        </Link>

        <div class="cf-nav__links">
          <a href="#inicio" class="cf-nav__link cf-nav__link--active">Inicio</a>
          <a href="#quienes-somos" class="cf-nav__link">Quiénes somos</a>
          <a href="#servicios" class="cf-nav__link">Servicios</a>
          <a href="#eventos" class="cf-nav__link">Eventos</a>
          <a href="#contacto" class="cf-nav__link">Contacto</a>
        </div>

        <div class="cf-nav__actions">
          <Link :href="route('login')" class="cf-btn cf-btn--ghost">Iniciar sesión</Link>
          <Link :href="route('register')" class="cf-btn cf-btn--primary">Registro</Link>
        </div>
      </nav>
    </header>

    <!-- ================= HERO ================= -->
    <section id="inicio" class="cf-hero">
      <div class="cf-hero__grid">
        <div class="cf-hero__copy">
          <h1 class="cf-hero__title">
            Tu próxima experiencia comienza <span class="cf-accent-italic">aquí.</span>
          </h1>
          <p class="cf-hero__text">
            Conexiones reales, experiencias auténticas y momentos inolvidables en un entorno seguro, exclusivo y privado.
          </p>
          <div class="cf-hero__actions">
            <a href="#servicios" class="cf-btn cf-btn--primary cf-btn--lg">
              Explorar experiencias <span aria-hidden="true">→</span>
            </a>
            <button type="button" class="cf-btn cf-btn--outline cf-btn--lg">
              <span class="cf-play-dot">▶</span>
              Ver cómo funciona
            </button>
          </div>
        </div>

        <div class="cf-hero__media">
          <img src="/images/hero-couple.jpg" alt="Pareja en un ambiente exclusivo y privado" class="cf-hero__img" />
          <div class="cf-hero__fade"></div>
        </div>
      </div>

      <div class="cf-features">
        <div class="cf-features__grid">
          <div v-for="f in features" :key="f.title" class="cf-features__item">
            <AppIcon :name="f.icon" class="cf-icon cf-icon--brand" />
            <div>
              <p class="cf-features__title">{{ f.title }}</p>
              <p class="cf-features__text">{{ f.text }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= QUIÉNES SOMOS ================= -->
    <section id="quienes-somos" class="cf-section cf-about">
      <div class="cf-about__copy">
        <p class="cf-eyebrow">— Quiénes somos</p>
        <h2 class="cf-h2">
          Más que una plataforma,<br />
          <span class="cf-accent-italic">un estilo de vida.</span>
        </h2>
        <p class="cf-body cf-about__text">
          Club de Fantasías es la comunidad exclusiva para adultos que buscan conexiones reales, experiencias auténticas y momentos inolvidables en un entorno seguro, discreto y confiable.
        </p>
        <a href="#servicios" class="cf-btn cf-btn--primary cf-btn--lg">
          Únete a nuestra comunidad <span aria-hidden="true">→</span>
        </a>
      </div>
      <div class="cf-about__media">
        <img src="/images/about-couple.jpg" alt="Pareja compartiendo un momento" />
      </div>
    </section>

    <!-- ================= NUESTRA MISIÓN ================= -->
    <section class="cf-mission">
      <div class="cf-mission__intro">
        <p class="cf-eyebrow cf-eyebrow--center">Nuestra misión</p>
        <h2 class="cf-h2">Conectar personas, crear experiencias.</h2>
        <p class="cf-body">
          Utilizamos tecnología e innovación para generar interacciones ágiles, reales e inmediatas, priorizando la seguridad, la confianza y la compatibilidad entre nuestros miembros.
        </p>
      </div>

      <div class="cf-mission__grid">
        <div v-for="m in mission" :key="m.title" class="cf-mission__card">
          <AppIcon :name="m.icon" class="cf-icon cf-icon--brand cf-icon--lg" />
          <p class="cf-mission__title">{{ m.title }}</p>
          <p class="cf-mission__text">{{ m.text }}</p>
        </div>
      </div>

      <div class="cf-mission__banner">
        <div class="cf-mission__banner-item">
          <AppIcon name="lock" class="cf-icon cf-icon--brand cf-icon--lg" />
          <div>
            <p class="cf-mission__title">Tu privacidad es nuestra prioridad</p>
            <p class="cf-mission__text">Tecnología avanzada y políticas estrictas para proteger tu identidad y tu información.</p>
          </div>
        </div>
        <div class="cf-mission__banner-item">
          <AppIcon name="users" class="cf-icon cf-icon--brand cf-icon--lg" />
          <div>
            <p class="cf-mission__title">Comunidad activa y real</p>
            <p class="cf-mission__text">Miles de miembros reales compartiendo experiencias auténticas cada día.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= SERVICIOS ================= -->
    <section id="servicios" class="cf-section cf-services">
      <div class="cf-services__intro">
        <p class="cf-eyebrow cf-eyebrow--center">— Nuestros servicios</p>
        <h2 class="cf-h2">
          Todo lo que necesitas para <span class="cf-accent-italic">vivir tu fantasía.</span>
        </h2>
        <p class="cf-body">Herramientas innovadoras para conectar, explorar y disfrutar de un estilo de vida sin límites.</p>
      </div>

      <div class="cf-services__grid">
        <article v-for="s in services" :key="s.title" class="cf-service-card">
          <div class="cf-service-card__media">
            <img :src="s.image" :alt="s.title" />
          </div>
          <h3 class="cf-service-card__title">{{ s.title }}</h3>
          <p class="cf-service-card__text">{{ s.text }}</p>
          <a href="#" class="cf-link">Saber más <span aria-hidden="true">→</span></a>
        </article>
      </div>
    </section>

    <!-- ================= EVENTOS: HERO ================= -->
    <section id="eventos" class="cf-events-hero">
      <div class="cf-events-hero__copy">
        <p class="cf-eyebrow">Eventos</p>
        <h2 class="cf-h2 cf-h2--light">
          Momentos reales,<br />
          <span class="cf-accent-italic">experiencias inolvidables.</span>
        </h2>
        <p class="cf-body cf-body--light">
          Fiestas exclusivas, reuniones privadas y experiencias diseñadas para conectar, explorar y disfrutar en un ambiente seguro, selecto y lleno de posibilidades.
        </p>
        <a href="#proximos-eventos" class="cf-btn cf-btn--primary cf-btn--lg">
          Descubrir próximos eventos <span aria-hidden="true">→</span>
        </a>
      </div>
      <div class="cf-events-hero__media">
        <img src="/images/events-hero.jpg" alt="Evento privado de la comunidad" />
      </div>
    </section>

    <!-- ================= TIPOS DE EXPERIENCIAS ================= -->
    <section class="cf-section cf-experiences">
      <p class="cf-eyebrow cf-eyebrow--center">Tipos de experiencias</p>
      <div class="cf-experiences__grid">
        <div v-for="e in experienceTypes" :key="e.label" class="cf-experiences__item">
          <img :src="e.image" :alt="e.label" />
          <div class="cf-experiences__overlay"></div>
          <span class="cf-experiences__label">{{ e.label }}</span>
        </div>
      </div>
    </section>

    <!-- ================= PRÓXIMOS EVENTOS ================= -->
    <section id="proximos-eventos" class="cf-section cf-upcoming">
      <p class="cf-eyebrow cf-eyebrow--center">Próximos eventos</p>
      <div class="cf-upcoming__grid">
        <article v-for="ev in events" :key="ev.title" class="cf-event-card">
          <div class="cf-event-card__media">
            <img :src="ev.image" :alt="ev.title" />
            <div class="cf-event-card__date">
              <span class="cf-event-card__day">{{ ev.day }}</span>
              <span class="cf-event-card__month">{{ ev.month }}</span>
            </div>
          </div>
          <div class="cf-event-card__body">
            <h3 class="cf-event-card__title">{{ ev.title }}</h3>
            <p class="cf-event-card__meta">📍 {{ ev.location }} &nbsp; ⏱ {{ ev.time }}</p>
            <p class="cf-event-card__text">{{ ev.text }}</p>
            <a href="#" class="cf-link">Más información <span aria-hidden="true">→</span></a>
          </div>
        </article>
      </div>
    </section>

    <!-- ================= CTA COMUNIDAD ================= -->
    <section class="cf-cta">
      <img src="/images/cta-band.jpg" alt="" class="cf-cta__bg" aria-hidden="true" />
      <div class="cf-cta__content">
        <h2 class="cf-h2 cf-h2--light">Sé parte de algo excepcional.</h2>
        <p class="cf-body cf-body--light cf-cta__text">
          Únete a nuestra comunidad y accede a eventos, experiencias y momentos que transformarán tu forma de vivir tu fantasía.
        </p>
        <div class="cf-cta__grid">
          <div class="cf-cta__item"><AppIcon name="ticket" class="cf-icon cf-icon--brand" /> Acceso a eventos</div>
          <div class="cf-cta__item"><AppIcon name="mail" class="cf-icon cf-icon--brand" /> Invitaciones anticipadas</div>
          <div class="cf-cta__item"><AppIcon name="shield-check" class="cf-icon cf-icon--brand" /> Comunidad segura y verificada</div>
          <div class="cf-cta__item"><AppIcon name="gem" class="cf-icon cf-icon--brand" /> Experiencias inolvidables</div>
        </div>
      </div>
    </section>

    <!-- ================= CONTACTO ================= -->
    <section id="contacto" class="cf-section cf-contact">
      <div class="cf-contact__info">
        <p class="cf-eyebrow">Contacto</p>
        <h2 class="cf-h2">Conecta con <span class="cf-accent-italic">nosotros.</span></h2>
        <p class="cf-body">Estamos para ayudarte. Escríbenos y nuestro equipo te responderá de manera discreta y personalizada.</p>
        <ul class="cf-contact__list">
          <li class="cf-contact__list-item">
            <AppIcon name="shield-check" class="cf-icon cf-icon--brand" />
            <div><p class="cf-mission__title">Privacidad garantizada</p><p class="cf-mission__text">Tu información está 100% protegida.</p></div>
          </li>
          <li class="cf-contact__list-item">
            <AppIcon name="eye-off" class="cf-icon cf-icon--brand" />
            <div><p class="cf-mission__title">Respuesta discreta</p><p class="cf-mission__text">Atención personalizada y confidencial.</p></div>
          </li>
          <li class="cf-contact__list-item">
            <AppIcon name="ticket" class="cf-icon cf-icon--brand" />
            <div><p class="cf-mission__title">Acceso por invitación</p><p class="cf-mission__text">Comunidad exclusiva y selecta.</p></div>
          </li>
          <li class="cf-contact__list-item">
            <AppIcon name="clock" class="cf-icon cf-icon--brand" />
            <div><p class="cf-mission__title">Tiempos de respuesta</p><p class="cf-mission__text">Respondemos en menos de 24 horas.</p></div>
          </li>
        </ul>
      </div>

      <form class="cf-form" @submit.prevent="submitContact">
        <p class="cf-form__title">Envíanos un mensaje</p>
        <div class="cf-form__row">
          <div class="cf-field">
            <label class="cf-label" for="nombre">Nombre completo</label>
            <input id="nombre" v-model="contactForm.nombre" type="text" class="cf-input" />
          </div>
          <div class="cf-field">
            <label class="cf-label" for="correo">Correo electrónico</label>
            <input id="correo" v-model="contactForm.correo" type="email" class="cf-input" />
          </div>
        </div>
        <div class="cf-field">
          <label class="cf-label" for="asunto">Asunto</label>
          <select id="asunto" v-model="contactForm.asunto" class="cf-input">
            <option value="">Selecciona un asunto</option>
            <option value="membresia">Membresía</option>
            <option value="eventos">Eventos</option>
            <option value="soporte">Soporte</option>
            <option value="otro">Otro</option>
          </select>
        </div>
        <div class="cf-field">
          <label class="cf-label" for="mensaje">Mensaje</label>
          <textarea id="mensaje" v-model="contactForm.mensaje" rows="4" class="cf-input" placeholder="Cuéntanos cómo podemos ayudarte."></textarea>
        </div>
        <button type="submit" class="cf-btn cf-btn--primary cf-btn--block">Enviar mensaje →</button>
        <p class="cf-form__note">🔒 Tu información está protegida y será tratada con la máxima confidencialidad.</p>
      </form>

      <div class="cf-faq">
        <p class="cf-form__title">Preguntas frecuentes</p>
        <div class="cf-faq__list">
          <div v-for="(f, i) in faqs" :key="f.q" class="cf-faq__item">
            <button type="button" class="cf-faq__question" @click="toggleFaq(i)">
              {{ f.q }}
              <span class="cf-faq__toggle">{{ f.open ? '−' : '+' }}</span>
            </button>
            <p v-if="f.open" class="cf-faq__answer">{{ f.a }}</p>
          </div>
        </div>

        <div class="cf-help">
          <p class="cf-mission__title">¿Necesitas ayuda?</p>
          <p class="cf-help__text">Nuestro equipo está listo para asesorarte en lo que necesites.</p>
          <a href="#" class="cf-btn cf-btn--primary cf-btn--sm">Hablar por WhatsApp</a>
        </div>
      </div>
    </section>

    <!-- ================= FOOTER ================= -->
    <footer class="cf-footer">
      <div class="cf-footer__grid">
        <div class="cf-footer__brand">
          <span class="cf-brand">
            <span class="cf-brand__mark"></span>
            <span class="cf-brand__name">Club<br />de Fantasías</span>
          </span>
          <p class="cf-footer__text">Conexiones reales, experiencias auténticas y momentos inolvidables en un entorno seguro, discreto y confiable.</p>
        </div>
        <div class="cf-footer__col">
          <p class="cf-footer__heading">Navegación</p>
          <ul class="cf-footer__list">
            <li><a href="#inicio">Inicio</a></li>
            <li><a href="#quienes-somos">Quiénes somos</a></li>
            <li><a href="#servicios">Servicios</a></li>
            <li><a href="#eventos">Eventos</a></li>
            <li><a href="#contacto">Contacto</a></li>
          </ul>
        </div>
        <div class="cf-footer__col">
          <p class="cf-footer__heading">Legal</p>
          <ul class="cf-footer__list">
            <li><a href="#">Términos y condiciones</a></li>
            <li><a href="#">Política de privacidad</a></li>
            <li><a href="#">Aviso legal</a></li>
          </ul>
        </div>
        <div class="cf-footer__col">
          <p class="cf-footer__heading">Únete a nuestra comunidad</p>
          <p class="cf-footer__text">Acceso exclusivo por invitación.</p>
          <Link :href="route('register')" class="cf-btn cf-btn--primary cf-btn--sm">Registro →</Link>
        </div>
      </div>
      <div class="cf-footer__bottom">
        © {{ new Date().getFullYear() }} Club de Fantasías. Todos los derechos reservados.
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA
   ========================================================================= */
.cf-page {
  --brand: #C81E3A;
  --brand-dark: #A6152D;
  --ink: #171412;
  --ink-soft: #4B4744;
  --muted: #8A8481;
  --muted-light: #B7B2AF;
  --line: #ECE9E7;
  --surface: #FAF8F7;
  --white: #FFFFFF;

  --font-serif: 'Fraunces', Georgia, serif;
  --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;

  --container: 1240px;
  --radius-sm: 10px;
  --radius-md: 16px;
  --radius-lg: 24px;
  --radius-full: 999px;

  font-family: var(--font-sans);
  color: var(--ink);
  background: var(--white);
  -webkit-font-smoothing: antialiased;
}

.cf-page * {
  box-sizing: border-box;
}

.cf-page img {
  max-width: 100%;
  display: block;
}

/* Bloques de sección reutilizables */
.cf-section {
  max-width: var(--container);
  margin: 0 auto;
  padding: 6rem 2.5rem;
}

.cf-eyebrow {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--brand);
  margin: 0 0 1rem;
}

.cf-eyebrow--center {
  text-align: center;
}

.cf-h2 {
  font-family: var(--font-serif);
  font-size: 2.4rem;
  font-weight: 500;
  line-height: 1.15;
  margin: 0;
}

.cf-h2--light {
  color: var(--white);
}

.cf-accent-italic {
  color: var(--brand);
  font-style: italic;
}

.cf-body {
  color: var(--muted);
  line-height: 1.7;
  font-size: 0.95rem;
  margin: 1rem 0 0;
}

.cf-body--light {
  color: #D8D4D1;
}

.cf-link {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  color: var(--brand);
  font-size: 0.78rem;
  font-weight: 700;
  text-decoration: none;
  margin-top: 0.75rem;
}

/* Botones */
.cf-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-family: var(--font-sans);
  font-weight: 600;
  font-size: 0.88rem;
  border-radius: var(--radius-full);
  border: 1px solid transparent;
  padding: 0.75rem 1.4rem;
  text-decoration: none;
  cursor: pointer;
  transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
  white-space: nowrap;
}

.cf-btn--primary {
  background: var(--brand);
  color: var(--white);
}
.cf-btn--primary:hover {
  background: var(--brand-dark);
}

.cf-btn--ghost {
  background: transparent;
  border-color: #D9D5D2;
  color: var(--ink);
}
.cf-btn--ghost:hover {
  border-color: var(--ink);
}

.cf-btn--outline {
  background: transparent;
  border-color: #D9D5D2;
  color: var(--ink);
}
.cf-btn--outline:hover {
  border-color: var(--ink);
}

.cf-btn--lg {
  padding: 0.95rem 1.6rem;
}

.cf-btn--sm {
  padding: 0.55rem 1.1rem;
  font-size: 0.78rem;
}

.cf-btn--block {
  width: 100%;
}

.cf-play-dot {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: var(--ink);
  color: var(--white);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 9px;
}

/* Iconos */
.cf-icon {
  width: 22px;
  height: 22px;
  flex-shrink: 0;
}
.cf-icon--brand {
  color: var(--brand);
}
.cf-icon--lg {
  width: 28px;
  height: 28px;
  margin-bottom: 1rem;
}

/* =========================================================================
   NAVBAR
   ========================================================================= */
.cf-header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(6px);
  border-bottom: 1px solid var(--line);
}

.cf-nav {
  max-width: var(--container);
  margin: 0 auto;
  height: 80px;
  padding: 0 2.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
}

.cf-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  text-decoration: none;
  color: var(--ink);
}

.cf-brand__mark {
  width: 36px;
  height: 36px;
  flex-shrink: 0;
  background: var(--ink);
  clip-path: polygon(0 0, 100% 0, 100% 70%, 70% 100%, 0 100%);
}

.cf-brand__name {
  font-family: var(--font-serif);
  font-weight: 600;
  font-size: 1.05rem;
  line-height: 1.15;
}

.cf-nav__links {
  display: flex;
  align-items: center;
  gap: 2.25rem;
  font-size: 0.88rem;
  font-weight: 500;
  color: var(--ink-soft);
}

.cf-nav__link {
  text-decoration: none;
  color: inherit;
  padding-bottom: 0.35rem;
  transition: color 0.15s ease;
}
.cf-nav__link:hover {
  color: var(--brand);
}
.cf-nav__link--active {
  color: var(--ink);
  border-bottom: 2px solid var(--brand);
}

.cf-nav__actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

/* =========================================================================
   HERO
   ========================================================================= */
.cf-hero {
  position: relative;
  overflow: hidden;
}

.cf-hero__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 560px;
}

.cf-hero__copy {
  display: flex;
  flex-direction: column;
  justify-content: center;
  max-width: 560px;
  padding: 4rem 2.5rem;
}

.cf-hero__title {
  font-family: var(--font-serif);
  font-size: 3.4rem;
  font-weight: 500;
  line-height: 1.05;
  letter-spacing: -0.01em;
  margin: 0;
}

.cf-hero__text {
  color: var(--muted);
  line-height: 1.7;
  margin: 1.5rem 0 0;
}

.cf-hero__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.cf-hero__media {
  position: relative;
  min-height: 360px;
}

.cf-hero__img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cf-hero__fade {
  position: absolute;
  inset: 0;
  width: 33%;
  background: linear-gradient(to right, var(--white), rgba(255, 255, 255, 0.05));
}

/* Franja de features */
.cf-features {
  border-top: 1px solid var(--line);
}

.cf-features__grid {
  max-width: var(--container);
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
}

.cf-features__item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 2rem 1.5rem;
  border-left: 1px solid var(--line);
}
.cf-features__item:first-child {
  border-left: none;
}

.cf-features__title {
  font-weight: 600;
  font-size: 0.85rem;
  margin: 0;
}

.cf-features__text {
  font-size: 0.75rem;
  color: var(--muted);
  line-height: 1.6;
  margin: 0.25rem 0 0;
}

/* =========================================================================
   QUIÉNES SOMOS
   ========================================================================= */
.cf-about {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
  align-items: center;
}

.cf-about__text {
  max-width: 420px;
}

.cf-about .cf-btn {
  margin-top: 2rem;
}

.cf-about__media {
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.cf-about__media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* =========================================================================
   NUESTRA MISIÓN
   ========================================================================= */
.cf-mission {
  background: var(--surface);
  padding: 6rem 0;
}

.cf-mission__intro {
  max-width: 620px;
  margin: 0 auto;
  text-align: center;
  padding: 0 1.5rem;
}

.cf-mission__grid {
  max-width: var(--container);
  margin: 3.5rem auto 0;
  padding: 0 2.5rem;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1px;
  background: var(--line);
}

.cf-mission__card {
  background: var(--surface);
  padding: 2rem;
}

.cf-mission__title {
  font-weight: 600;
  font-size: 0.85rem;
  margin: 0;
}

.cf-mission__text {
  font-size: 0.78rem;
  color: var(--muted);
  line-height: 1.6;
  margin: 0.4rem 0 0;
}

.cf-mission__banner {
  max-width: var(--container);
  margin: 1px auto 0;
  padding: 0 2.5rem;
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1px;
  background: var(--line);
}

.cf-mission__banner-item {
  background: var(--white);
  padding: 2rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

/* =========================================================================
   SERVICIOS
   ========================================================================= */
.cf-services__intro {
  max-width: 620px;
  margin: 0 auto;
  text-align: center;
}

.cf-services__grid {
  margin-top: 3.5rem;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

.cf-service-card__media {
  aspect-ratio: 4 / 3;
  border-radius: var(--radius-md);
  overflow: hidden;
  background: var(--ink);
}

.cf-service-card__media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}
.cf-service-card:hover .cf-service-card__media img {
  transform: scale(1.05);
}

.cf-service-card__title {
  font-size: 0.88rem;
  font-weight: 600;
  margin: 1rem 0 0;
}

.cf-service-card__text {
  font-size: 0.78rem;
  color: var(--muted);
  line-height: 1.6;
  margin: 0.4rem 0 0;
}

/* =========================================================================
   EVENTOS: HERO
   ========================================================================= */
.cf-events-hero {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 420px;
}

.cf-events-hero__copy {
  background: var(--ink);
  color: var(--white);
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 4rem 2.5rem;
}

.cf-events-hero .cf-btn {
  margin-top: 2rem;
  width: fit-content;
}

.cf-events-hero__media {
  position: relative;
  min-height: 320px;
}

.cf-events-hero__media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  position: absolute;
  inset: 0;
}

/* Tipos de experiencias */
.cf-experiences {
  padding-top: 4rem;
  padding-bottom: 4rem;
}

.cf-experiences__grid {
  margin-top: 2rem;
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 1rem;
}

.cf-experiences__item {
  position: relative;
  border-radius: var(--radius-sm);
  overflow: hidden;
  aspect-ratio: 1 / 1;
}

.cf-experiences__item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}
.cf-experiences__item:hover img {
  transform: scale(1.05);
}

.cf-experiences__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.05) 60%, transparent);
}

.cf-experiences__label {
  position: absolute;
  bottom: 0.75rem;
  left: 0.75rem;
  color: var(--white);
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

/* Próximos eventos */
.cf-upcoming__grid {
  margin-top: 2.5rem;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.cf-event-card {
  border: 1px solid var(--line);
  border-radius: var(--radius-md);
  overflow: hidden;
}

.cf-event-card__media {
  position: relative;
  aspect-ratio: 16 / 10;
}

.cf-event-card__media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cf-event-card__date {
  position: absolute;
  top: 0.75rem;
  left: 0.75rem;
  background: var(--white);
  border-radius: 8px;
  padding: 0.4rem 0.75rem;
  text-align: center;
  line-height: 1;
}

.cf-event-card__day {
  display: block;
  color: var(--brand);
  font-weight: 700;
  font-size: 0.9rem;
}

.cf-event-card__month {
  display: block;
  font-size: 0.6rem;
  font-weight: 700;
  color: var(--muted);
}

.cf-event-card__body {
  padding: 1.25rem;
}

.cf-event-card__title {
  font-size: 0.88rem;
  font-weight: 600;
  margin: 0;
}

.cf-event-card__meta {
  font-size: 0.72rem;
  color: var(--muted-light);
  margin: 0.4rem 0 0;
}

.cf-event-card__text {
  font-size: 0.78rem;
  color: var(--muted);
  line-height: 1.6;
  margin: 0.5rem 0 0;
}

/* =========================================================================
   CTA COMUNIDAD
   ========================================================================= */
.cf-cta {
  position: relative;
  background: var(--ink);
  color: var(--white);
  overflow: hidden;
}

.cf-cta__bg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.4;
}

.cf-cta__content {
  position: relative;
  max-width: var(--container);
  margin: 0 auto;
  padding: 4rem 2.5rem;
}

.cf-cta__text {
  max-width: 560px;
}

.cf-cta__grid {
  margin-top: 2rem;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
  max-width: 720px;
}

.cf-cta__item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.78rem;
  font-weight: 500;
}

/* =========================================================================
   CONTACTO
   ========================================================================= */
.cf-contact {
  display: grid;
  grid-template-columns: 1fr 1.2fr 1fr;
  gap: 3rem;
}

.cf-contact__list {
  list-style: none;
  margin: 2rem 0 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.cf-contact__list-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.cf-form {
  background: var(--surface);
  border-radius: var(--radius-lg);
  padding: 2rem;
}

.cf-form__title {
  font-weight: 600;
  margin: 0 0 1.25rem;
}

.cf-form__row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.cf-field {
  margin-top: 1rem;
}
.cf-form__row .cf-field {
  margin-top: 0;
}

.cf-label {
  display: block;
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--ink-soft);
  margin-bottom: 0.4rem;
}

.cf-input {
  width: 100%;
  border: 1px solid #DDD8D5;
  border-radius: var(--radius-sm);
  padding: 0.65rem 0.9rem;
  font-size: 0.88rem;
  font-family: inherit;
  color: var(--ink);
  background: var(--white);
}
.cf-input:focus {
  outline: none;
  border-color: var(--brand);
  box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
}

.cf-form button.cf-btn {
  margin-top: 1.25rem;
}

.cf-form__note {
  text-align: center;
  font-size: 0.68rem;
  color: var(--muted-light);
  margin: 0.75rem 0 0;
}

.cf-faq__list {
  border-top: 1px solid var(--line);
  border-bottom: 1px solid var(--line);
}

.cf-faq__item {
  border-bottom: 1px solid var(--line);
}
.cf-faq__item:last-child {
  border-bottom: none;
}

.cf-faq__question {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  background: none;
  border: none;
  padding: 1rem 0;
  text-align: left;
  font-family: inherit;
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--ink);
  cursor: pointer;
}

.cf-faq__toggle {
  color: var(--brand);
  font-size: 1.2rem;
  line-height: 1;
  flex-shrink: 0;
}

.cf-faq__answer {
  padding: 0 0 1rem;
  font-size: 0.78rem;
  color: var(--muted);
  line-height: 1.6;
  margin: 0;
}

.cf-help {
  margin-top: 1.5rem;
  background: var(--ink);
  color: var(--white);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
}

.cf-help .cf-mission__title {
  color: var(--white);
}

.cf-help__text {
  font-size: 0.75rem;
  color: #D8D4D1;
  line-height: 1.6;
  margin: 0.3rem 0 0;
}

.cf-help .cf-btn {
  margin-top: 1rem;
}

/* =========================================================================
   FOOTER
   ========================================================================= */
.cf-footer {
  border-top: 1px solid var(--line);
}

.cf-footer__grid {
  max-width: var(--container);
  margin: 0 auto;
  padding: 4rem 2.5rem;
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 2.5rem;
}

.cf-footer__text {
  font-size: 0.78rem;
  color: var(--muted);
  line-height: 1.6;
  max-width: 260px;
  margin: 1rem 0 0;
}

.cf-footer__heading {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin: 0 0 1rem;
}

.cf-footer__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
}

.cf-footer__list a {
  color: var(--muted);
  text-decoration: none;
  font-size: 0.78rem;
}
.cf-footer__list a:hover {
  color: var(--brand);
}

.cf-footer__col .cf-btn {
  margin-top: 0.5rem;
}

.cf-footer__bottom {
  border-top: 1px solid var(--line);
  padding: 1.5rem;
  text-align: center;
  font-size: 0.68rem;
  color: var(--muted-light);
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
  .cf-services__grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .cf-experiences__grid {
    grid-template-columns: repeat(3, 1fr);
  }
  .cf-mission__grid,
  .cf-mission__banner {
    grid-template-columns: repeat(2, 1fr);
  }
  .cf-contact {
    grid-template-columns: 1fr;
  }
  .cf-footer__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 900px) {
  .cf-nav__links {
    display: none;
  }
  .cf-hero__grid,
  .cf-about,
  .cf-events-hero {
    grid-template-columns: 1fr;
  }
  .cf-hero__copy {
    padding: 3rem 1.5rem;
    max-width: none;
  }
  .cf-hero__media {
    min-height: 320px;
    order: -1;
  }
  .cf-hero__fade {
    display: none;
  }
  .cf-hero__title {
    font-size: 2.5rem;
  }
  .cf-features__grid,
  .cf-cta__grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .cf-features__item {
    border-left: none;
    border-top: 1px solid var(--line);
  }
  .cf-upcoming__grid,
  .cf-services__grid {
    grid-template-columns: 1fr;
  }
  .cf-experiences__grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .cf-nav__actions .cf-btn--ghost {
    display: none;
  }
}

@media (max-width: 640px) {
  .cf-section {
    padding: 3.5rem 1.25rem;
  }
  .cf-nav {
    padding: 0 1.25rem;
  }
  .cf-h2 {
    font-size: 1.9rem;
  }
  .cf-form__row {
    grid-template-columns: 1fr;
  }
  .cf-footer__grid {
    grid-template-columns: 1fr;
    padding: 3rem 1.25rem;
  }
  .cf-mission__grid,
  .cf-mission__banner {
    grid-template-columns: 1fr;
    padding: 0 1.25rem;
  }
}
</style>