<!-- resources/js/Pages/Landing.vue -->
<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppIcon from '@/Components/AppIcon.vue';

// -----------------------------------------------------------------------
// PROPS (recibidas del controlador)
// -----------------------------------------------------------------------

const props = defineProps({
  eventosProximos: {
    type: Array,
    default: () => []
  },
  hayEventos: {
    type: Boolean,
    default: false
  }
});

// -----------------------------------------------------------------------
// Datos de la página (estáticos)
// -----------------------------------------------------------------------

const features = [
  { icon: 'shield', title: 'Comunidad verificada', text: 'Perfiles reales, validados para tu tranquilidad.' },
  { icon: 'map-marker', title: 'Geolocalización discreta', text: 'Conecta con personas cercanas sin revelar tu ubicación.' },
  { icon: 'lock', title: 'Privacidad y seguridad', text: 'Tecnología avanzada para proteger tu información.' },
  { icon: 'diamond', title: 'Experiencias exclusivas', text: 'Acceso a eventos y experiencias únicas para miembros.' },
];

const mission = [
  { icon: 'ticket', title: 'Acceso por invitación', text: 'Solo miembros seleccionados por invitación.' },
  { icon: 'verified', title: 'Perfiles verificados', text: 'Validación de perfiles y sistema de reputación para mayor seguridad.' },
  { icon: 'map-marker', title: 'Conexiones reales', text: 'Geolocalización inteligente para interacciones en tiempo real.' },
  { icon: 'star-fill', title: 'Compatibilidad inteligente', text: 'Match dinámico basado en preferencias, intereses y comportamiento.' },
  { icon: 'calendar', title: 'Experiencias exclusivas', text: 'Eventos, fiestas y experiencias privadas para llevar tu estilo de vida al siguiente nivel.' },
  { icon: 'eye-slash', title: 'Discreción y privacidad', text: 'Tu privacidad es nuestra prioridad. Entorno seguro, discreto y 100% confiable.' },
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

// -----------------------------------------------------------------------
// SCROLL SUAVE CON ANIMACIÓN
// -----------------------------------------------------------------------

const activeLink = ref('inicio');
const isMobileMenuOpen = ref(false);

function scrollToSection(event, sectionId) {
  event.preventDefault();
  
  activeLink.value = sectionId;
  
  if (isMobileMenuOpen.value) {
    isMobileMenuOpen.value = false;
  }
  
  const target = document.getElementById(sectionId);
  
  if (target) {
    const header = document.querySelector('.cf-header');
    const headerHeight = header ? header.offsetHeight : 80;
    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight;
    
    window.scrollTo({
      top: targetPosition,
      behavior: 'smooth'
    });
  }
}

function updateActiveLink() {
  const sections = ['inicio', 'quienes-somos', 'servicios', 'eventos', 'contacto'];
  const header = document.querySelector('.cf-header');
  const headerHeight = header ? header.offsetHeight : 80;
  
  let currentSection = 'inicio';
  
  sections.forEach(sectionId => {
    const element = document.getElementById(sectionId);
    if (element) {
      const rect = element.getBoundingClientRect();
      if (rect.top <= headerHeight + 100) {
        currentSection = sectionId;
      }
    }
  });
  
  activeLink.value = currentSection;
}

onMounted(() => {
  window.addEventListener('scroll', updateActiveLink);
});

function toggleMobileMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
}

// Columnas del footer
const footerColumnas = {
  navegacion: ['Inicio', 'Quiénes somos', 'Servicios', 'Eventos', 'Contacto'],
  comunidad: ['Mi perfil', 'Mis conexiones', 'Mis favoritos', 'Mis visitas', 'Configuración'],
  soporte: ['Centro de ayuda', 'Preguntas frecuentes', 'Consejos de seguridad', 'Contacto'],
  legal: ['Términos y condiciones', 'Política de privacidad', 'Política de cookies', 'Aviso legal'],
};
</script>

<template>
  <Head title="Inicio" />

  <div class="cf-page">
    <!-- ================= NAVBAR BLANCO ================= -->
    <header class="cf-header">
      <nav class="cf-nav">
        <Link href="/" class="cf-brand">
          <img 
            src="/images/LOGO.png" 
            alt="Club de Fantasías" 
            class="cf-brand__logo"
          />
        </Link>

        <div class="cf-nav__links" :class="{ 'cf-nav__links--open': isMobileMenuOpen }">
          <a 
            href="#inicio" 
            class="cf-nav__link" 
            :class="{ 'cf-nav__link--active': activeLink === 'inicio' }"
            @click="scrollToSection($event, 'inicio')"
          >
            Inicio
          </a>
          <a 
            href="#quienes-somos" 
            class="cf-nav__link"
            :class="{ 'cf-nav__link--active': activeLink === 'quienes-somos' }"
            @click="scrollToSection($event, 'quienes-somos')"
          >
            Quiénes somos
          </a>
          <a 
            href="#servicios" 
            class="cf-nav__link"
            :class="{ 'cf-nav__link--active': activeLink === 'servicios' }"
            @click="scrollToSection($event, 'servicios')"
          >
            Servicios
          </a>
          <a 
            href="#eventos" 
            class="cf-nav__link"
            :class="{ 'cf-nav__link--active': activeLink === 'eventos' }"
            @click="scrollToSection($event, 'eventos')"
          >
            Eventos
          </a>
          <a 
            href="#contacto" 
            class="cf-nav__link"
            :class="{ 'cf-nav__link--active': activeLink === 'contacto' }"
            @click="scrollToSection($event, 'contacto')"
          >
            Contacto
          </a>
        </div>

        <div class="cf-nav__actions">
          <button 
            class="cf-nav__hamburger" 
            @click="toggleMobileMenu"
            aria-label="Menú"
          >
            <span></span>
            <span></span>
            <span></span>
          </button>

          <Link :href="route('login')" class="cf-btn cf-btn--ghost">Iniciar sesión</Link>
          <Link :href="route('register.invite')" class="cf-btn cf-btn--primary">Registro</Link>
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
            <a href="#servicios" class="cf-btn cf-btn--primary cf-btn--lg" @click="scrollToSection($event, 'servicios')">
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
        <a href="#servicios" class="cf-btn cf-btn--primary cf-btn--lg" @click="scrollToSection($event, 'servicios')">
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
        <span class="cf-mission__badge">✦ Nuestra misión</span>
        <h2 class="cf-mission__title">
          Conectar personas,<br />
          <span class="cf-mission__title-highlight">crear experiencias.</span>
        </h2>
        <p class="cf-mission__description">
          Utilizamos tecnología e innovación para generar interacciones ágiles, reales e inmediatas, priorizando la seguridad, la confianza y la compatibilidad entre nuestros miembros.
        </p>
      </div>

      <div class="cf-mission__grid">
        <div v-for="m in mission" :key="m.title" class="cf-mission__card">
          <div class="cf-mission__card-icon">
            <AppIcon :name="m.icon" class="cf-icon cf-icon--brand cf-icon--lg" />
          </div>
          <h3 class="cf-mission__card-title">{{ m.title }}</h3>
          <p class="cf-mission__card-text">{{ m.text }}</p>
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
        <a href="#proximos-eventos" class="cf-btn cf-btn--primary cf-btn--lg" @click="scrollToSection($event, 'proximos-eventos')">
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
      <div class="cf-upcoming__header">
        <p class="cf-eyebrow cf-eyebrow--center">Próximos eventos</p>
        <h2 class="cf-upcoming__title">
          Descubre las próximas <span class="cf-accent-italic">experiencias</span>
        </h2>
        <p class="cf-upcoming__subtitle">
          Eventos exclusivos diseñados para conectar, explorar y vivir momentos únicos.
        </p>
      </div>
      
      <div v-if="hayEventos" class="cf-upcoming__grid">
        <article v-for="(ev, index) in eventosProximos" :key="ev.id" class="cf-event-card" :style="{ animationDelay: (index * 0.15) + 's' }">
          <div class="cf-event-card__media">
            <img :src="ev.imagen" :alt="ev.titulo" />
            <div class="cf-event-card__badge">
              <span class="cf-event-card__badge-icon">✦</span>
              Evento exclusivo
            </div>
            <div class="cf-event-card__date">
              <span class="cf-event-card__day">{{ ev.dia }}</span>
              <span class="cf-event-card__month">{{ ev.mes }}</span>
            </div>
          </div>
          <div class="cf-event-card__body">
            <div class="cf-event-card__meta-top">
              <span class="cf-event-card__location">
                <AppIcon name="map-marker" class="cf-event-card__icon" />
                {{ ev.ubicacion }}
              </span>
              <span class="cf-event-card__time">
                <AppIcon name="clock" class="cf-event-card__icon" />
                {{ ev.hora }}
              </span>
            </div>
            <h3 class="cf-event-card__title">{{ ev.titulo }}</h3>
            <p class="cf-event-card__text">{{ ev.texto }}</p>
            <div class="cf-event-card__footer">
              <a href="#" class="cf-link">
                Ver detalles <span aria-hidden="true">→</span>
              </a>
              <button class="cf-event-card__btn">
                <AppIcon name="heart" class="cf-event-card__heart" />
              </button>
            </div>
          </div>
        </article>
      </div>

      <div v-else class="cf-no-events">
        <div class="cf-no-events__content">
          <div class="cf-no-events__icon-wrapper">
            <AppIcon name="calendar" class="cf-no-events__icon" />
          </div>
          <h3 class="cf-no-events__title">Próximamente nuevos eventos</h3>
          <p class="cf-no-events__text">
            Estamos preparando experiencias increíbles para ti. 
            <br />¡Muy pronto anunciaremos nuestras próximas fechas!
          </p>
          <div class="cf-no-events__actions">
            <Link :href="route('register.invite')" class="cf-btn cf-btn--primary cf-btn--sm">
              <AppIcon name="bell" class="cf-btn__icon" />
              Recibir notificaciones
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= CTA COMUNIDAD ================= -->
    <section class="cf-cta">
      <div class="cf-cta__bg-wrapper">
        <img src="/images/cta-band.jpg" alt="" class="cf-cta__bg" />
        <div class="cf-cta__overlay"></div>
      </div>
      <div class="cf-cta__content">
        <div class="cf-cta__inner">
          <span class="cf-cta__badge">✦ Comunidad exclusiva</span>
          <h2 class="cf-cta__title">
            Sé parte de algo <span class="cf-cta__title-highlight">excepcional.</span>
          </h2>
          <p class="cf-cta__text">
            Únete a nuestra comunidad y accede a eventos, experiencias y momentos 
            que transformarán tu forma de vivir tu fantasía.
          </p>
          <div class="cf-cta__actions">
            <Link :href="route('register.invite')" class="cf-btn cf-btn--primary cf-btn--lg cf-cta__btn">
              <AppIcon name="ticket" class="cf-btn__icon" />
              Unirme ahora
              <span aria-hidden="true">→</span>
            </Link>
            <a href="#servicios" class="cf-cta__link" @click="scrollToSection($event, 'servicios')">
              Explorar servicios <span aria-hidden="true">→</span>
            </a>
          </div>
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
            <AppIcon name="shield" class="cf-icon cf-icon--brand" />
            <div><p class="cf-mission__card-title">Privacidad garantizada</p><p class="cf-mission__card-text">Tu información está 100% protegida.</p></div>
          </li>
          <li class="cf-contact__list-item">
            <AppIcon name="eye-slash" class="cf-icon cf-icon--brand" />
            <div><p class="cf-mission__card-title">Respuesta discreta</p><p class="cf-mission__card-text">Atención personalizada y confidencial.</p></div>
          </li>
          <li class="cf-contact__list-item">
            <AppIcon name="ticket" class="cf-icon cf-icon--brand" />
            <div><p class="cf-mission__card-title">Acceso por invitación</p><p class="cf-mission__card-text">Comunidad exclusiva y selecta.</p></div>
          </li>
          <li class="cf-contact__list-item">
            <AppIcon name="clock" class="cf-icon cf-icon--brand" />
            <div><p class="cf-mission__card-title">Tiempos de respuesta</p><p class="cf-mission__card-text">Respondemos en menos de 24 horas.</p></div>
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
          <p class="cf-mission__card-title">¿Necesitas ayuda?</p>
          <p class="cf-help__text">Nuestro equipo está listo para asesorarte en lo que necesites.</p>
          <a href="#" class="cf-btn cf-btn--primary cf-btn--sm">Hablar por WhatsApp</a>
        </div>
      </div>
    </section>

    <!-- ================= FOOTER ================= -->
    <footer class="app-footer">
      <div class="app-footer__top">
        <div class="app-footer__brand">
          <div class="app-footer__logo">
            <img 
              src="/images/LOGO.png" 
              alt="Club de Fantasías" 
              class="app-footer__logo-img"
            />
          </div>
          <p class="app-footer__brand-desc">
            Una comunidad exclusiva para conectar con personas auténticas,
            de forma segura, privada y respetuosa.
          </p>
        </div>

        <div class="app-footer__col">
          <h4>NAVEGACIÓN</h4>
          <a v-for="l in footerColumnas.navegacion" :key="l" href="#" @click.prevent="l === 'Inicio' ? scrollToSection($event, 'inicio') : null">
            {{ l }}
          </a>
        </div>
        <div class="app-footer__col">
          <h4>COMUNIDAD</h4>
          <a v-for="l in footerColumnas.comunidad" :key="l" href="#">{{ l }}</a>
        </div>
        <div class="app-footer__col">
          <h4>SOPORTE</h4>
          <a v-for="l in footerColumnas.soporte" :key="l" href="#">{{ l }}</a>
        </div>
        <div class="app-footer__col">
          <h4>LEGAL</h4>
          <a v-for="l in footerColumnas.legal" :key="l" href="#">{{ l }}</a>
        </div>

        <div class="app-footer__security">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #c81e3a; flex-shrink: 0; margin-top: 2px;">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            <path d="M9 12l2 2 4-4"/>
          </svg>
          <div>
            <strong>Tu seguridad es nuestra prioridad</strong>
            <span>Plataforma verificada y monitoreada 24/7 para tu tranquilidad.</span>
          </div>
        </div>
      </div>

      <div class="app-footer__bottom">
        <span>© {{ new Date().getFullYear() }} Club de Fantasías. Todos los derechos reservados.</span>
        <span class="app-footer__age">+18 · Comunidad exclusiva para adultos</span>
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
  transition: gap 0.3s ease;
}

.cf-link:hover {
  gap: 0.6rem;
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
  transition: all 0.3s ease;
  white-space: nowrap;
}

.cf-btn__icon {
  width: 16px;
  height: 16px;
}

.cf-btn--primary {
  background: var(--brand);
  color: var(--white);
}
.cf-btn--primary:hover {
  background: var(--brand-dark);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(200, 30, 58, 0.3);
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
  font-size: 20px;
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.cf-icon--brand {
  color: var(--brand);
}
.cf-icon--lg {
  font-size: 28px;
  margin-bottom: 0.5rem;
}

/* =========================================================================
   NAVBAR BLANCO
   ========================================================================= */
.cf-header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: var(--white);
  border-bottom: 1px solid var(--line);
  transition: box-shadow 0.3s ease;
}

.cf-header.scrolled {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
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
  text-decoration: none;
  color: var(--ink);
}

.cf-brand__logo {
  height: 50px;
  width: auto;
  object-fit: contain;
  transition: transform 0.3s ease;
}

.cf-brand__logo:hover {
  transform: scale(1.05);
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
  position: relative;
  transition: color 0.3s ease;
  cursor: pointer;
}

.cf-nav__link::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0;
  height: 2px;
  background: var(--brand);
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.cf-nav__link:hover {
  color: var(--brand);
}

.cf-nav__link:hover::after {
  width: 100%;
}

.cf-nav__link--active {
  color: var(--ink);
  font-weight: 600;
}

.cf-nav__link--active::after {
  width: 100%;
  background: var(--brand);
}

.cf-nav__actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.cf-nav__hamburger {
  display: none;
  flex-direction: column;
  gap: 5px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 5px;
}

.cf-nav__hamburger span {
  display: block;
  width: 25px;
  height: 2.5px;
  background: var(--ink);
  border-radius: 2px;
  transition: all 0.3s ease;
}

.cf-nav__hamburger:hover span {
  background: var(--brand);
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
  transition: background-color 0.3s ease;
}

.cf-features__item:first-child {
  border-left: none;
}

.cf-features__item:hover {
  background: var(--surface);
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
  transition: transform 0.5s ease, box-shadow 0.5s ease;
}

.cf-about__media:hover {
  transform: scale(1.02);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
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
  background: linear-gradient(180deg, var(--surface) 0%, var(--white) 100%);
  padding: 6rem 0;
}

.cf-mission__intro {
  max-width: 700px;
  margin: 0 auto;
  text-align: center;
  padding: 0 1.5rem;
}

.cf-mission__badge {
  display: inline-block;
  font-size: 0.7rem;
  font-weight: 600;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--brand);
  background: rgba(200, 30, 58, 0.08);
  padding: 0.4rem 1.2rem;
  border-radius: var(--radius-full);
  margin-bottom: 1.5rem;
}

.cf-mission__title {
  font-family: var(--font-serif);
  font-size: 2.8rem;
  font-weight: 500;
  line-height: 1.1;
  margin: 0 0 1.5rem;
}

.cf-mission__title-highlight {
  color: var(--brand);
  font-style: italic;
  position: relative;
}

.cf-mission__title-highlight::after {
  content: '';
  position: absolute;
  bottom: 2px;
  left: 0;
  right: 0;
  height: 6px;
  background: rgba(200, 30, 58, 0.15);
  border-radius: 2px;
}

.cf-mission__description {
  font-size: 1.05rem;
  color: var(--muted);
  line-height: 1.8;
  max-width: 560px;
  margin: 0 auto;
}

.cf-mission__grid {
  max-width: var(--container);
  margin: 4rem auto 0;
  padding: 0 2.5rem;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.cf-mission__card {
  background: var(--white);
  padding: 2.5rem 2rem;
  border-radius: var(--radius-lg);
  text-align: center;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid var(--line);
  position: relative;
  overflow: hidden;
}

.cf-mission__card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: var(--brand);
  opacity: 0;
  transition: opacity 0.4s ease;
}

.cf-mission__card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.06);
  border-color: transparent;
}

.cf-mission__card:hover::before {
  opacity: 1;
}

.cf-mission__card-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: rgba(200, 30, 58, 0.06);
  margin-bottom: 1rem;
  transition: all 0.3s ease;
}

.cf-mission__card:hover .cf-mission__card-icon {
  background: var(--brand);
}

.cf-mission__card:hover .cf-mission__card-icon .cf-icon {
  color: var(--white) !important;
}

.cf-mission__card-title {
  font-weight: 600;
  font-size: 0.95rem;
  margin: 0 0 0.5rem;
}

.cf-mission__card-text {
  font-size: 0.8rem;
  color: var(--muted);
  line-height: 1.6;
  margin: 0;
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

.cf-service-card {
  transition: transform 0.4s ease, box-shadow 0.4s ease;
}

.cf-service-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
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
  transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.cf-service-card:hover .cf-service-card__media img {
  transform: scale(1.08);
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
  overflow: hidden;
}

.cf-events-hero__media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  position: absolute;
  inset: 0;
  transition: transform 0.6s ease;
}

.cf-events-hero__media:hover img {
  transform: scale(1.05);
}

/* =========================================================================
   EXPERIENCIAS
   ========================================================================= */
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
  cursor: pointer;
}

.cf-experiences__item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.cf-experiences__item:hover img {
  transform: scale(1.1);
}

.cf-experiences__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.05) 60%, transparent);
  transition: opacity 0.3s ease;
}

.cf-experiences__item:hover .cf-experiences__overlay {
  opacity: 0.8;
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
  transition: transform 0.3s ease;
}

.cf-experiences__item:hover .cf-experiences__label {
  transform: translateY(-4px);
}

/* =========================================================================
   PRÓXIMOS EVENTOS
   ========================================================================= */
.cf-upcoming__header {
  text-align: center;
  margin-bottom: 3rem;
}

.cf-upcoming__title {
  font-family: var(--font-serif);
  font-size: 2.4rem;
  font-weight: 500;
  margin: 0.5rem 0 0.75rem;
}

.cf-upcoming__subtitle {
  color: var(--muted);
  font-size: 0.95rem;
  max-width: 500px;
  margin: 0 auto;
}

.cf-upcoming__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.8rem;
}

.cf-event-card {
  background: var(--white);
  border-radius: var(--radius-lg);
  overflow: hidden;
  border: 1px solid var(--line);
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  opacity: 0;
  transform: translateY(30px);
  animation: fadeInUp 0.6s ease forwards;
}

@keyframes fadeInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.cf-event-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.08);
  border-color: var(--brand);
}

.cf-event-card__media {
  position: relative;
  aspect-ratio: 16 / 10;
  overflow: hidden;
  background: var(--ink);
}

.cf-event-card__media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
}

.cf-event-card:hover .cf-event-card__media img {
  transform: scale(1.08);
}

.cf-event-card__badge {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  background: var(--brand);
  color: var(--white);
  font-size: 0.55rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  padding: 0.3rem 0.7rem;
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.cf-event-card__badge-icon {
  font-size: 0.5rem;
}

.cf-event-card__date {
  position: absolute;
  bottom: 0.75rem;
  left: 0.75rem;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(8px);
  border-radius: var(--radius-sm);
  padding: 0.4rem 0.7rem;
  text-align: center;
  line-height: 1;
  min-width: 52px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.cf-event-card__day {
  display: block;
  color: var(--white);
  font-weight: 700;
  font-size: 1.1rem;
}

.cf-event-card__month {
  display: block;
  font-size: 0.55rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.6);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.cf-event-card__body {
  padding: 1.25rem 1.25rem 1.25rem;
}

.cf-event-card__meta-top {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.cf-event-card__location,
.cf-event-card__time {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.68rem;
  color: var(--muted-light);
}

.cf-event-card__icon {
  font-size: 12px;
  color: var(--brand);
}

.cf-event-card__title {
  font-size: 0.95rem;
  font-weight: 600;
  margin: 0 0 0.4rem;
}

.cf-event-card__text {
  font-size: 0.78rem;
  color: var(--muted);
  line-height: 1.6;
  margin: 0 0 0.75rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.cf-event-card__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 0.75rem;
  border-top: 1px solid var(--line);
}

.cf-event-card__btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.4rem 0.6rem;
  border-radius: var(--radius-sm);
  transition: all 0.3s ease;
  color: var(--muted-light);
}

.cf-event-card__btn:hover {
  background: rgba(200, 30, 58, 0.06);
  color: var(--brand);
  transform: scale(1.1);
}

.cf-event-card__heart {
  font-size: 18px;
  color: currentColor;
}

/* =========================================================================
   NO EVENTS
   ========================================================================= */
.cf-no-events {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
  padding: 2rem;
}

.cf-no-events__content {
  text-align: center;
  max-width: 500px;
  padding: 3rem 2rem;
}

.cf-no-events__icon-wrapper {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: rgba(200, 30, 58, 0.06);
  margin-bottom: 1.5rem;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.cf-no-events__icon {
  font-size: 40px;
  color: var(--brand);
}

.cf-no-events__title {
  font-family: var(--font-serif);
  font-size: 1.6rem;
  font-weight: 500;
  margin: 0 0 0.75rem;
  color: var(--ink);
}

.cf-no-events__text {
  font-size: 0.95rem;
  color: var(--muted);
  line-height: 1.7;
  margin: 0 0 1.5rem;
}

.cf-no-events__actions {
  display: flex;
  justify-content: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

/* =========================================================================
   CTA COMUNIDAD
   ========================================================================= */
.cf-cta {
  position: relative;
  background: var(--ink);
  color: var(--white);
  overflow: hidden;
  min-height: 520px;
  width: 100%;
}

.cf-cta__bg-wrapper {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--ink);
}

.cf-cta__bg {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center 40%;
  display: block;
}

.cf-cta__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    135deg,
    rgba(23, 20, 18, 0.85) 0%,
    rgba(23, 20, 18, 0.4) 50%,
    rgba(23, 20, 18, 0.7) 100%
  );
}

.cf-cta__content {
  position: relative;
  z-index: 1;
  width: 100%;
  min-height: 520px;
  display: flex;
  align-items: center;
  padding: 0;
}

.cf-cta__inner {
  max-width: var(--container);
  margin: 0 auto;
  padding: 4rem 2.5rem;
  width: 100%;
}

.cf-cta__badge {
  display: inline-block;
  font-size: 0.65rem;
  font-weight: 600;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 0.35rem 1rem;
  border-radius: var(--radius-full);
  margin-bottom: 1.5rem;
}

.cf-cta__title {
  font-family: var(--font-serif);
  font-size: 3.2rem;
  font-weight: 500;
  line-height: 1.1;
  margin: 0 0 1.25rem;
  max-width: 700px;
}

.cf-cta__title-highlight {
  color: var(--brand);
  font-style: italic;
  position: relative;
}

.cf-cta__title-highlight::after {
  content: '';
  position: absolute;
  bottom: 4px;
  left: 0;
  right: 0;
  height: 6px;
  background: rgba(200, 30, 58, 0.25);
  border-radius: 2px;
}

.cf-cta__text {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.75);
  line-height: 1.8;
  max-width: 560px;
  margin: 0 0 2rem;
}

.cf-cta__actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.cf-cta__btn {
  background: var(--brand);
  border-color: var(--brand);
  color: var(--white);
  padding: 0.85rem 2rem;
  font-size: 0.9rem;
  gap: 0.75rem;
}

.cf-cta__btn:hover {
  background: var(--brand-dark);
  border-color: var(--brand-dark);
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(200, 30, 58, 0.35);
}

.cf-cta__link {
  color: rgba(255, 255, 255, 0.6);
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 500;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.cf-cta__link:hover {
  color: var(--white);
  gap: 0.8rem;
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
  padding: 0.5rem;
  border-radius: var(--radius-sm);
  transition: background-color 0.3s ease;
}

.cf-contact__list-item:hover {
  background: var(--surface);
}

.cf-form {
  background: var(--surface);
  border-radius: var(--radius-lg);
  padding: 2rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.cf-form:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06);
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
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
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

/* =========================================================================
   FAQ
   ========================================================================= */
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
  transition: color 0.3s ease;
}

.cf-faq__question:hover {
  color: var(--brand);
}

.cf-faq__toggle {
  color: var(--brand);
  font-size: 1.2rem;
  line-height: 1;
  flex-shrink: 0;
  transition: transform 0.3s ease;
}

.cf-faq__open .cf-faq__toggle {
  transform: rotate(45deg);
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
  transition: transform 0.3s ease;
}

.cf-help:hover {
  transform: translateY(-4px);
}

.cf-help .cf-mission__card-title {
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
   FOOTER (NUEVO ESTILO)
   ========================================================================= */
.app-footer {
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  max-width: 1400px;
  margin: 2.5rem auto 0;
  padding: 2.5rem 2rem 1.5rem;
  color: #1f2024;
}

.app-footer__top {
  display: grid;
  grid-template-columns: 1.4fr 0.8fr 0.8fr 0.8fr 0.8fr 1.2fr;
  gap: 1.75rem;
  border-bottom: 1px solid #e6e6e8;
  padding-bottom: 2rem;
}

@media (max-width: 1024px) {
  .app-footer__top { 
    grid-template-columns: 1fr 1fr; 
  }
}

@media (max-width: 600px) {
  .app-footer__top { 
    grid-template-columns: 1fr; 
  }
}

.app-footer__brand { 
  grid-column: span 1; 
}

.app-footer__logo { 
  display: flex; 
  align-items: center; 
  margin-bottom: 0.9rem; 
}

.app-footer__logo-img {
  height: 45px;
  width: auto;
  object-fit: contain;
}

.app-footer__brand-desc { 
  font-size: 0.8rem; 
  color: #8a8a90; 
  line-height: 1.6; 
  margin: 0; 
  max-width: 280px; 
}

.app-footer__col { 
  display: flex; 
  flex-direction: column; 
  gap: 0.6rem; 
}

.app-footer__col h4 { 
  font-size: 0.72rem; 
  color: #a5a5aa; 
  letter-spacing: 0.05em; 
  margin: 0 0 0.3rem;
  text-transform: uppercase;
}

.app-footer__col a { 
  font-size: 0.82rem; 
  color: #55555a; 
  text-decoration: none;
  transition: color 0.2s ease;
}

.app-footer__col a:hover { 
  color: var(--brand); 
}

.app-footer__security {
  background: #fafafa;
  border: 1px solid #ececee;
  border-radius: 10px;
  padding: 1rem;
  display: flex;
  gap: 0.75rem;
  align-items: flex-start;
  font-size: 0.78rem;
  height: fit-content;
}

.app-footer__security svg { 
  color: var(--brand); 
  flex-shrink: 0;
  margin-top: 2px;
}

.app-footer__security strong { 
  display: block; 
  font-size: 0.82rem; 
  margin-bottom: 0.2rem; 
}

.app-footer__security span { 
  color: #8a8a90; 
}

.app-footer__bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
  padding-top: 1.25rem;
  font-size: 0.78rem;
  color: #a5a5aa;
}

.app-footer__age { 
  font-weight: 600; 
  color: #8a8a90;
}

@media (max-width: 768px) {
  .app-footer {
    padding: 2rem 1rem 1.5rem;
  }
  
  .app-footer__bottom {
    flex-direction: column;
    text-align: center;
    gap: 0.75rem;
  }
}

/* =========================================================================
   RESPONSIVE (para el resto de la página)
   ========================================================================= */
@media (max-width: 1024px) {
  .cf-services__grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .cf-experiences__grid {
    grid-template-columns: repeat(3, 1fr);
  }
  .cf-mission__grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .cf-contact {
    grid-template-columns: 1fr;
  }
  .cf-upcoming__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 900px) {
  .cf-nav__links {
    display: none;
    flex-direction: column;
    position: absolute;
    top: 80px;
    left: 0;
    right: 0;
    background: var(--white);
    padding: 1.5rem;
    gap: 1.25rem;
    border-bottom: 1px solid var(--line);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  }

  .cf-nav__links--open {
    display: flex;
  }

  .cf-nav__hamburger {
    display: flex;
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
  .cf-features__grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .cf-features__item {
    border-left: none;
    border-top: 1px solid var(--line);
  }
  .cf-services__grid {
    grid-template-columns: 1fr;
  }
  .cf-experiences__grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .cf-upcoming__grid {
    grid-template-columns: 1fr;
  }
  .cf-nav__actions .cf-btn--ghost {
    display: none;
  }

  .cf-mission__title {
    font-size: 2.2rem;
  }

  .cf-cta {
    min-height: 450px;
  }

  .cf-cta__content {
    min-height: 450px;
  }

  .cf-cta__inner {
    padding: 3rem 1.5rem;
  }

  .cf-cta__title {
    font-size: 2.5rem;
    max-width: 100%;
  }

  .cf-cta__text {
    font-size: 1rem;
    max-width: 100%;
  }

  .cf-cta__bg {
    object-position: center 50%;
  }
}

@media (max-width: 640px) {
  .cf-section {
    padding: 3.5rem 1.25rem;
  }
  .cf-nav {
    padding: 0 1.25rem;
    height: 70px;
  }
  .cf-brand__logo {
    height: 40px;
  }
  .cf-h2 {
    font-size: 1.9rem;
  }
  .cf-form__row {
    grid-template-columns: 1fr;
  }
  
  .cf-nav__links {
    top: 70px;
  }

  .cf-mission__grid {
    grid-template-columns: 1fr;
    padding: 0 1.25rem;
  }

  .cf-mission__title {
    font-size: 1.8rem;
  }

  .cf-no-events {
    min-height: 250px;
    padding: 1rem;
  }

  .cf-no-events__content {
    padding: 2rem 1rem;
  }

  .cf-no-events__title {
    font-size: 1.3rem;
  }

  .cf-upcoming__title {
    font-size: 1.8rem;
  }

  .cf-event-card__badge {
    font-size: 0.45rem;
    padding: 0.2rem 0.5rem;
  }

  .cf-event-card__date {
    min-width: 44px;
    padding: 0.3rem 0.5rem;
  }

  .cf-event-card__day {
    font-size: 0.9rem;
  }

  .cf-cta {
    min-height: 400px;
  }

  .cf-cta__content {
    min-height: 400px;
  }

  .cf-cta__inner {
    padding: 2.5rem 1.25rem;
    text-align: center;
  }

  .cf-cta__title {
    font-size: 2rem;
  }

  .cf-cta__text {
    font-size: 0.9rem;
  }

  .cf-cta__actions {
    justify-content: center;
    flex-direction: column;
    align-items: stretch;
  }

  .cf-cta__btn {
    justify-content: center;
    width: 100%;
  }

  .cf-cta__link {
    justify-content: center;
    width: 100%;
  }

  .cf-cta__badge {
    font-size: 0.55rem;
  }
}
</style>