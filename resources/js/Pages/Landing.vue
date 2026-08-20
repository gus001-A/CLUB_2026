<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import AppIcon from '@/Components/AppIcon.vue';

// Props recibidas del controlador
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

// Datos estáticos de la página
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

// Función para scroll (se usará con el layout)
function handleScrollTo(event, sectionId) {
  // Esta función se pasa al layout y se ejecuta desde allí
  // El layout maneja el scroll
}

// Función para manejar errores de carga de imágenes
function handleImageError(event) {
  const img = event.target;
  console.warn('Error cargando imagen:', img.src);
  // Si no es la imagen por defecto, intentar cargarla
  if (!img.src.includes('default.jpg')) {
    img.src = '/images/events/default.jpg';
  } else {
    // Si ya es la default y falla, mostrar un color de fondo
    img.style.display = 'none';
    img.parentElement.style.backgroundColor = '#2a2a2a';
    // Mostrar un texto alternativo
    const fallback = document.createElement('div');
    fallback.className = 'user-image-fallback';
    fallback.innerHTML = '📷';
    img.parentElement.appendChild(fallback);
  }
}

// Función para verificar si la imagen existe antes de mostrarla
async function loadImageWithFallback(url, imgElement) {
  try {
    const response = await fetch(url, { method: 'HEAD' });
    if (!response.ok) {
      throw new Error('Image not found');
    }
    imgElement.src = url;
  } catch (error) {
    console.warn('Fallback para imagen:', url);
    imgElement.src = '/images/events/default.jpg';
  }
}

// Depuración: mostrar las URLs de las imágenes en consola
console.log('Eventos próximos recibidos:', props.eventosProximos);
console.log('URLs de imágenes:', props.eventosProximos.map(e => ({
  titulo: e.titulo,
  imagen: e.imagen,
  fechaCompleta: e.fechaCompleta
})));

// Al montar el componente, verificar las imágenes
onMounted(() => {
  props.eventosProximos.forEach(evento => {
    const img = document.querySelector(`img[data-event-id="${evento.id}"]`);
    if (img) {
      loadImageWithFallback(evento.imagen, img);
    }
  });
});
</script>

<template>
  <UserLayout title="Inicio">
    <!-- ================= HERO ================= -->
    <section id="inicio" class="user-hero">
      <div class="user-hero__grid">
        <div class="user-hero__copy">
          <h1 class="user-hero__title">
            Tu próxima experiencia comienza <span class="user-accent-italic">aquí.</span>
          </h1>
          <p class="user-hero__text">
            Conexiones reales, experiencias auténticas y momentos inolvidables en un entorno seguro, exclusivo y privado.
          </p>
          <div class="user-hero__actions">
            <a href="#servicios" class="user-btn user-btn--primary user-btn--lg" @click="handleScrollTo($event, 'servicios')">
              Explorar experiencias <span aria-hidden="true">→</span>
            </a>
            <Link :href="route('register.invite')" class="user-btn user-btn--outline user-btn--lg">
              <span class="user-play-dot">▶</span>
              Ver cómo funciona
            </Link>
          </div>
        </div>

        <div class="user-hero__media">
          <img src="/images/hero-couple.jpg" alt="Pareja en un ambiente exclusivo y privado" class="user-hero__img" @error="handleImageError" />
          <div class="user-hero__fade"></div>
        </div>
      </div>

      <div class="user-features">
        <div class="user-features__grid">
          <div v-for="f in features" :key="f.title" class="user-features__item">
            <AppIcon :name="f.icon" class="user-icon user-icon--brand" />
            <div>
              <p class="user-features__title">{{ f.title }}</p>
              <p class="user-features__text">{{ f.text }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= QUIÉNES SOMOS ================= -->
    <section id="quienes-somos" class="user-section user-about">
      <div class="user-about__copy">
        <p class="user-eyebrow">— Quiénes somos</p>
        <h2 class="user-h2">
          Más que una plataforma,<br />
          <span class="user-accent-italic">un estilo de vida.</span>
        </h2>
        <p class="user-body user-about__text">
          Club de Fantasías es la comunidad exclusiva para adultos que buscan conexiones reales, experiencias auténticas y momentos inolvidables en un entorno seguro, discreto y confiable.
        </p>
        <Link :href="route('register.invite')" class="user-btn user-btn--primary user-btn--lg">
          Únete a nuestra comunidad <span aria-hidden="true">→</span>
        </Link>
      </div>
      <div class="user-about__media">
        <img src="/images/about-couple.jpg" alt="Pareja compartiendo un momento" @error="handleImageError" />
      </div>
    </section>

    <!-- ================= NUESTRA MISIÓN ================= -->
    <section class="user-mission">
      <div class="user-mission__intro">
        <span class="user-mission__badge">✦ Nuestra misión</span>
        <h2 class="user-mission__title">
          Conectar personas,<br />
          <span class="user-mission__title-highlight">crear experiencias.</span>
        </h2>
        <p class="user-mission__description">
          Utilizamos tecnología e innovación para generar interacciones ágiles, reales e inmediatas, priorizando la seguridad, la confianza y la compatibilidad entre nuestros miembros.
        </p>
      </div>

      <div class="user-mission__grid">
        <div v-for="m in mission" :key="m.title" class="user-mission__card">
          <div class="user-mission__card-icon">
            <AppIcon :name="m.icon" class="user-icon user-icon--brand user-icon--lg" />
          </div>
          <h3 class="user-mission__card-title">{{ m.title }}</h3>
          <p class="user-mission__card-text">{{ m.text }}</p>
        </div>
      </div>
    </section>

    <!-- ================= SERVICIOS ================= -->
    <section id="servicios" class="user-section user-services">
      <div class="user-services__intro">
        <p class="user-eyebrow user-eyebrow--center">— Nuestros servicios</p>
        <h2 class="user-h2">
          Todo lo que necesitas para <span class="user-accent-italic">vivir tu fantasía.</span>
        </h2>
        <p class="user-body">Herramientas innovadoras para conectar, explorar y disfrutar de un estilo de vida sin límites.</p>
      </div>

      <div class="user-services__grid">
        <article v-for="s in services" :key="s.title" class="user-service-card">
          <div class="user-service-card__media">
            <img :src="s.image" :alt="s.title" @error="handleImageError" />
          </div>
          <h3 class="user-service-card__title">{{ s.title }}</h3>
          <p class="user-service-card__text">{{ s.text }}</p>
          <Link :href="route('register.invite')" class="user-link">
            Conocer más <span aria-hidden="true">→</span>
          </Link>
        </article>
      </div>
    </section>

    <!-- ================= EVENTOS: HERO ================= -->
    <section id="eventos" class="user-events-hero">
      <div class="user-events-hero__copy">
        <p class="user-eyebrow">Eventos</p>
        <h2 class="user-h2 user-h2--light">
          Momentos reales,<br />
          <span class="user-accent-italic">experiencias inolvidables.</span>
        </h2>
        <p class="user-body user-body--light">
          Fiestas exclusivas, reuniones privadas y experiencias diseñadas para conectar, explorar y disfrutar en un ambiente seguro, selecto y lleno de posibilidades.
        </p>
        <a href="#proximos-eventos" class="user-btn user-btn--primary user-btn--lg" @click="handleScrollTo($event, 'proximos-eventos')">
          Descubrir próximos eventos <span aria-hidden="true">→</span>
        </a>
      </div>
      <div class="user-events-hero__media">
        <img src="/images/events-hero.jpg" alt="Evento privado de la comunidad" @error="handleImageError" />
      </div>
    </section>

    <!-- ================= TIPOS DE EXPERIENCIAS ================= -->
    <section class="user-section user-experiences">
      <p class="user-eyebrow user-eyebrow--center">Tipos de experiencias</p>
      <div class="user-experiences__grid">
        <div v-for="e in experienceTypes" :key="e.label" class="user-experiences__item">
          <img :src="e.image" :alt="e.label" @error="handleImageError" />
          <div class="user-experiences__overlay"></div>
          <span class="user-experiences__label">{{ e.label }}</span>
        </div>
      </div>
    </section>

    <!-- ================= PRÓXIMOS EVENTOS ================= -->
    <section id="proximos-eventos" class="user-section user-upcoming">
      <div class="user-upcoming__header">
        <p class="user-eyebrow user-eyebrow--center">Próximos eventos</p>
        <h2 class="user-upcoming__title">
          Descubre las próximas <span class="user-accent-italic">experiencias</span>
        </h2>
        <p class="user-upcoming__subtitle">
          Eventos exclusivos diseñados para conectar, explorar y vivir momentos únicos.
        </p>
      </div>
      
      <div v-if="hayEventos" class="user-upcoming__grid">
        <article v-for="(ev, index) in eventosProximos" :key="ev.id" class="user-event-card" :style="{ animationDelay: (index * 0.15) + 's' }">
          <div class="user-event-card__media">
            <img 
              :data-event-id="ev.id"
              :src="ev.imagen" 
              :alt="ev.titulo" 
              @error="handleImageError"
              loading="lazy"
            />
            <div class="user-event-card__badge">
              <span class="user-event-card__badge-icon">✦</span>
              Evento exclusivo
            </div>
            <div class="user-event-card__date">
              <span class="user-event-card__day">{{ ev.dia }}</span>
              <span class="user-event-card__month">{{ ev.mes }}</span>
            </div>
          </div>
          <div class="user-event-card__body">
            <div class="user-event-card__meta-top">
              <span class="user-event-card__location">
                <AppIcon name="map-marker" class="user-event-card__icon" />
                {{ ev.ubicacion }}
              </span>
              <span class="user-event-card__time">
                <AppIcon name="clock" class="user-event-card__icon" />
                {{ ev.hora }}
              </span>
            </div>
            <h3 class="user-event-card__title">{{ ev.titulo }}</h3>
            <p class="user-event-card__text">{{ ev.texto }}</p>
            <!-- Fecha completa en español -->
            <p class="user-event-card__fecha" v-if="ev.fechaCompleta">
              <AppIcon name="calendar" class="user-event-card__icon" />
              {{ ev.fechaCompleta }}
            </p>
            <div class="user-event-card__footer">
              <Link :href="route('register.invite')" class="user-link">
                Ver detalles <span aria-hidden="true">→</span>
              </Link>
            </div>
          </div>
        </article>
      </div>

      <div v-else class="user-no-events">
        <div class="user-no-events__content">
          <div class="user-no-events__icon-wrapper">
            <AppIcon name="calendar" class="user-no-events__icon" />
          </div>
          <h3 class="user-no-events__title">Próximamente nuevos eventos</h3>
          <p class="user-no-events__text">
            Estamos preparando experiencias increíbles para ti. 
            <br />¡Muy pronto anunciaremos nuestras próximas fechas!
          </p>
          <div class="user-no-events__actions">
            <Link :href="route('register.invite')" class="user-btn user-btn--primary user-btn--sm">
              <AppIcon name="bell" class="user-btn__icon" />
              Recibir notificaciones
            </Link>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= CTA COMUNIDAD ================= -->
    <section class="user-cta">
      <div class="user-cta__bg-wrapper">
        <img src="/images/cta-band.jpg" alt="" class="user-cta__bg" @error="handleImageError" />
        <div class="user-cta__overlay"></div>
      </div>
      <div class="user-cta__content">
        <div class="user-cta__inner">
          <span class="user-cta__badge">✦ Comunidad exclusiva</span>
          <h2 class="user-cta__title">
            Sé parte de algo <span class="user-cta__title-highlight">excepcional.</span>
          </h2>
          <p class="user-cta__text">
            Únete a nuestra comunidad y accede a eventos, experiencias y momentos 
            que transformarán tu forma de vivir tu fantasía.
          </p>
          <div class="user-cta__actions">
            <Link :href="route('register.invite')" class="user-btn user-btn--primary user-btn--lg user-cta__btn">
              <AppIcon name="ticket" class="user-btn__icon" />
              Unirme ahora
              <span aria-hidden="true">→</span>
            </Link>
            <a href="#servicios" class="user-cta__link" @click="handleScrollTo($event, 'servicios')">
              Explorar servicios <span aria-hidden="true">→</span>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= CONTACTO ================= -->
    <section id="contacto" class="user-section user-contact">
      <div class="user-contact__info">
        <p class="user-eyebrow">Contacto</p>
        <h2 class="user-h2">Conecta con <span class="user-accent-italic">nosotros.</span></h2>
        <p class="user-body">Estamos para ayudarte. Escríbenos y nuestro equipo te responderá de manera discreta y personalizada.</p>
        <ul class="user-contact__list">
          <li class="user-contact__list-item">
            <AppIcon name="shield" class="user-icon user-icon--brand" />
            <div><p class="user-mission__card-title">Privacidad garantizada</p><p class="user-mission__card-text">Tu información está 100% protegida.</p></div>
          </li>
          <li class="user-contact__list-item">
            <AppIcon name="eye-slash" class="user-icon user-icon--brand" />
            <div><p class="user-mission__card-title">Respuesta discreta</p><p class="user-mission__card-text">Atención personalizada y confidencial.</p></div>
          </li>
          <li class="user-contact__list-item">
            <AppIcon name="ticket" class="user-icon user-icon--brand" />
            <div><p class="user-mission__card-title">Acceso por invitación</p><p class="user-mission__card-text">Comunidad exclusiva y selecta.</p></div>
          </li>
          <li class="user-contact__list-item">
            <AppIcon name="clock" class="user-icon user-icon--brand" />
            <div><p class="user-mission__card-title">Tiempos de respuesta</p><p class="user-mission__card-text">Respondemos en menos de 24 horas.</p></div>
          </li>
        </ul>
      </div>

      <form class="user-form" @submit.prevent="submitContact">
        <p class="user-form__title">Envíanos un mensaje</p>
        <div class="user-form__row">
          <div class="user-field">
            <label class="user-label" for="nombre">Nombre completo</label>
            <input id="nombre" v-model="contactForm.nombre" type="text" class="user-input" />
          </div>
          <div class="user-field">
            <label class="user-label" for="correo">Correo electrónico</label>
            <input id="correo" v-model="contactForm.correo" type="email" class="user-input" />
          </div>
        </div>
        <div class="user-field">
          <label class="user-label" for="asunto">Asunto</label>
          <select id="asunto" v-model="contactForm.asunto" class="user-input">
            <option value="">Selecciona un asunto</option>
            <option value="membresia">Membresía</option>
            <option value="eventos">Eventos</option>
            <option value="soporte">Soporte</option>
            <option value="otro">Otro</option>
          </select>
        </div>
        <div class="user-field">
          <label class="user-label" for="mensaje">Mensaje</label>
          <textarea id="mensaje" v-model="contactForm.mensaje" rows="4" class="user-input" placeholder="Cuéntanos cómo podemos ayudarte."></textarea>
        </div>
        <button type="submit" class="user-btn user-btn--primary user-btn--block">Enviar mensaje →</button>
        <p class="user-form__note">🔒 Tu información está protegida y será tratada con la máxima confidencialidad.</p>
      </form>

      <div class="user-faq">
        <p class="user-form__title">Preguntas frecuentes</p>
        <div class="user-faq__list">
          <div v-for="(f, i) in faqs" :key="f.q" class="user-faq__item">
            <button type="button" class="user-faq__question" @click="toggleFaq(i)">
              {{ f.q }}
              <span class="user-faq__toggle">{{ f.open ? '−' : '+' }}</span>
            </button>
            <p v-if="f.open" class="user-faq__answer">{{ f.a }}</p>
          </div>
        </div>
      </div>
    </section>
  </UserLayout>
</template>

<style scoped>
/* =========================================================================
   TODOS LOS ESTILOS DE LA PÁGINA (con prefijo user-)
   ========================================================================= */

/* Variables y estilos base */
.user-section {
  max-width: 1240px;
  margin: 0 auto;
  padding: 6rem 2.5rem;
}

.user-eyebrow {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--brand);
  margin: 0 0 1rem;
}

.user-eyebrow--center {
  text-align: center;
}

.user-h2 {
  font-family: var(--font-serif);
  font-size: 2.4rem;
  font-weight: 500;
  line-height: 1.15;
  margin: 0;
}

.user-h2--light {
  color: var(--white);
}

.user-accent-italic {
  color: var(--brand);
  font-style: italic;
}

.user-body {
  color: var(--muted);
  line-height: 1.7;
  font-size: 0.95rem;
  margin: 1rem 0 0;
}

.user-body--light {
  color: #D8D4D1;
}

.user-link {
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

.user-link:hover {
  gap: 0.6rem;
}

.user-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-family: var(--font-sans);
  font-weight: 600;
  font-size: 0.88rem;
  border-radius: 999px;
  border: 1px solid transparent;
  padding: 0.75rem 1.4rem;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.user-btn__icon {
  width: 16px;
  height: 16px;
}

.user-btn--primary {
  background: var(--brand);
  color: var(--white);
}

.user-btn--primary:hover {
  background: var(--brand-dark);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(200, 30, 58, 0.3);
}

.user-btn--ghost {
  background: transparent;
  border-color: #D9D5D2;
  color: var(--ink);
}

.user-btn--ghost:hover {
  border-color: var(--ink);
}

.user-btn--outline {
  background: transparent;
  border-color: #D9D5D2;
  color: var(--ink);
}

.user-btn--outline:hover {
  border-color: var(--ink);
}

.user-btn--lg {
  padding: 0.95rem 1.6rem;
}

.user-btn--sm {
  padding: 0.55rem 1.1rem;
  font-size: 0.78rem;
}

.user-btn--block {
  width: 100%;
}

.user-play-dot {
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

.user-icon {
  font-size: 20px;
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.user-icon--brand {
  color: var(--brand);
}

.user-icon--lg {
  font-size: 28px;
  margin-bottom: 0.5rem;
}

/* =========================================================================
   HERO
   ========================================================================= */
.user-hero {
  position: relative;
  overflow: hidden;
}

.user-hero__grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 560px;
}

.user-hero__copy {
  display: flex;
  flex-direction: column;
  justify-content: center;
  max-width: 560px;
  padding: 4rem 2.5rem;
}

.user-hero__title {
  font-family: var(--font-serif);
  font-size: 3.4rem;
  font-weight: 500;
  line-height: 1.05;
  letter-spacing: -0.01em;
  margin: 0;
}

.user-hero__text {
  color: var(--muted);
  line-height: 1.7;
  margin: 1.5rem 0 0;
}

.user-hero__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.user-hero__media {
  position: relative;
  min-height: 360px;
}

.user-hero__img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-hero__fade {
  position: absolute;
  inset: 0;
  width: 33%;
  background: linear-gradient(to right, var(--white), rgba(255, 255, 255, 0.05));
}

.user-features {
  border-top: 1px solid var(--line);
}

.user-features__grid {
  max-width: 1240px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
}

.user-features__item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 2rem 1.5rem;
  border-left: 1px solid var(--line);
  transition: background-color 0.3s ease;
}

.user-features__item:first-child {
  border-left: none;
}

.user-features__item:hover {
  background: var(--surface);
}

.user-features__title {
  font-weight: 600;
  font-size: 0.85rem;
  margin: 0;
}

.user-features__text {
  font-size: 0.75rem;
  color: var(--muted);
  line-height: 1.6;
  margin: 0.25rem 0 0;
}

/* =========================================================================
   QUIÉNES SOMOS
   ========================================================================= */
.user-about {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
  align-items: center;
}

.user-about__text {
  max-width: 420px;
}

.user-about .user-btn {
  margin-top: 2rem;
}

.user-about__media {
  border-radius: var(--radius-lg);
  overflow: hidden;
  transition: transform 0.5s ease, box-shadow 0.5s ease;
}

.user-about__media:hover {
  transform: scale(1.02);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}

.user-about__media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* =========================================================================
   NUESTRA MISIÓN
   ========================================================================= */
.user-mission {
  background: linear-gradient(180deg, var(--surface) 0%, var(--white) 100%);
  padding: 6rem 0;
}

.user-mission__intro {
  max-width: 700px;
  margin: 0 auto;
  text-align: center;
  padding: 0 1.5rem;
}

.user-mission__badge {
  display: inline-block;
  font-size: 0.7rem;
  font-weight: 600;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--brand);
  background: rgba(200, 30, 58, 0.08);
  padding: 0.4rem 1.2rem;
  border-radius: 999px;
  margin-bottom: 1.5rem;
}

.user-mission__title {
  font-family: var(--font-serif);
  font-size: 2.8rem;
  font-weight: 500;
  line-height: 1.1;
  margin: 0 0 1.5rem;
}

.user-mission__title-highlight {
  color: var(--brand);
  font-style: italic;
  position: relative;
}

.user-mission__title-highlight::after {
  content: '';
  position: absolute;
  bottom: 2px;
  left: 0;
  right: 0;
  height: 6px;
  background: rgba(200, 30, 58, 0.15);
  border-radius: 2px;
}

.user-mission__description {
  font-size: 1.05rem;
  color: var(--muted);
  line-height: 1.8;
  max-width: 560px;
  margin: 0 auto;
}

.user-mission__grid {
  max-width: 1240px;
  margin: 4rem auto 0;
  padding: 0 2.5rem;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.user-mission__card {
  background: var(--white);
  padding: 2.5rem 2rem;
  border-radius: var(--radius-lg);
  text-align: center;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  border: 1px solid var(--line);
  position: relative;
  overflow: hidden;
}

.user-mission__card::before {
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

.user-mission__card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.06);
  border-color: transparent;
}

.user-mission__card:hover::before {
  opacity: 1;
}

.user-mission__card-icon {
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

.user-mission__card:hover .user-mission__card-icon {
  background: var(--brand);
}

.user-mission__card:hover .user-mission__card-icon .user-icon {
  color: var(--white) !important;
}

.user-mission__card-title {
  font-weight: 600;
  font-size: 0.95rem;
  margin: 0 0 0.5rem;
}

.user-mission__card-text {
  font-size: 0.8rem;
  color: var(--muted);
  line-height: 1.6;
  margin: 0;
}

/* =========================================================================
   SERVICIOS
   ========================================================================= */
.user-services__intro {
  max-width: 620px;
  margin: 0 auto;
  text-align: center;
}

.user-services__grid {
  margin-top: 3.5rem;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

.user-service-card {
  transition: transform 0.4s ease, box-shadow 0.4s ease;
}

.user-service-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
}

.user-service-card__media {
  aspect-ratio: 4 / 3;
  border-radius: var(--radius-md);
  overflow: hidden;
  background: var(--ink);
}

.user-service-card__media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.user-service-card:hover .user-service-card__media img {
  transform: scale(1.08);
}

.user-service-card__title {
  font-size: 0.88rem;
  font-weight: 600;
  margin: 1rem 0 0;
}

.user-service-card__text {
  font-size: 0.78rem;
  color: var(--muted);
  line-height: 1.6;
  margin: 0.4rem 0 0;
}

/* =========================================================================
   EVENTOS: HERO
   ========================================================================= */
.user-events-hero {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 420px;
}

.user-events-hero__copy {
  background: var(--ink);
  color: var(--white);
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 4rem 2.5rem;
}

.user-events-hero .user-btn {
  margin-top: 2rem;
  width: fit-content;
}

.user-events-hero__media {
  position: relative;
  min-height: 320px;
  overflow: hidden;
}

.user-events-hero__media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  position: absolute;
  inset: 0;
  transition: transform 0.6s ease;
}

.user-events-hero__media:hover img {
  transform: scale(1.05);
}

/* =========================================================================
   EXPERIENCIAS
   ========================================================================= */
.user-experiences {
  padding-top: 4rem;
  padding-bottom: 4rem;
}

.user-experiences__grid {
  margin-top: 2rem;
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 1rem;
}

.user-experiences__item {
  position: relative;
  border-radius: var(--radius-sm);
  overflow: hidden;
  aspect-ratio: 1 / 1;
  cursor: pointer;
}

.user-experiences__item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.user-experiences__item:hover img {
  transform: scale(1.1);
}

.user-experiences__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.05) 60%, transparent);
  transition: opacity 0.3s ease;
}

.user-experiences__item:hover .user-experiences__overlay {
  opacity: 0.8;
}

.user-experiences__label {
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

.user-experiences__item:hover .user-experiences__label {
  transform: translateY(-4px);
}

/* =========================================================================
   PRÓXIMOS EVENTOS
   ========================================================================= */
.user-upcoming__header {
  text-align: center;
  margin-bottom: 3rem;
}

.user-upcoming__title {
  font-family: var(--font-serif);
  font-size: 2.4rem;
  font-weight: 500;
  margin: 0.5rem 0 0.75rem;
}

.user-upcoming__subtitle {
  color: var(--muted);
  font-size: 0.95rem;
  max-width: 500px;
  margin: 0 auto;
}

.user-upcoming__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.8rem;
}

.user-event-card {
  background: var(--white);
  border-radius: var(--radius-lg);
  overflow: hidden;
  border: 1px solid var(--line);
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  opacity: 0;
  transform: translateY(30px);
  animation: userFadeInUp 0.6s ease forwards;
}

@keyframes userFadeInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.user-event-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.08);
  border-color: var(--brand);
}

.user-event-card__media {
  position: relative;
  aspect-ratio: 16 / 10;
  overflow: hidden;
  background: var(--ink);
}

.user-event-card__media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
}

.user-event-card:hover .user-event-card__media img {
  transform: scale(1.08);
}

.user-event-card__badge {
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
  border-radius: 999px;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.user-event-card__badge-icon {
  font-size: 0.5rem;
}

.user-event-card__date {
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

.user-event-card__day {
  display: block;
  color: var(--white);
  font-weight: 700;
  font-size: 1.1rem;
}

.user-event-card__month {
  display: block;
  font-size: 0.55rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.6);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.user-event-card__body {
  padding: 1.25rem 1.25rem 1.25rem;
}

.user-event-card__meta-top {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.user-event-card__location,
.user-event-card__time {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.68rem;
  color: var(--muted-light);
}

.user-event-card__icon {
  font-size: 12px;
  color: var(--brand);
}

.user-event-card__title {
  font-size: 0.95rem;
  font-weight: 600;
  margin: 0 0 0.4rem;
}

.user-event-card__text {
  font-size: 0.78rem;
  color: var(--muted);
  line-height: 1.6;
  margin: 0 0 0.5rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Nueva estilo para la fecha en español */
.user-event-card__fecha {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.7rem;
  color: var(--brand);
  margin: 0 0 0.75rem;
  font-weight: 500;
}

.user-event-card__footer {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  padding-top: 0.75rem;
  border-top: 1px solid var(--line);
}

/* Fallback para imágenes */
.user-image-fallback {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  color: #666;
  background: #2a2a2a;
}

/* =========================================================================
   NO EVENTS
   ========================================================================= */
.user-no-events {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
  padding: 2rem;
}

.user-no-events__content {
  text-align: center;
  max-width: 500px;
  padding: 3rem 2rem;
}

.user-no-events__icon-wrapper {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: rgba(200, 30, 58, 0.06);
  margin-bottom: 1.5rem;
  animation: userPulse 2s ease-in-out infinite;
}

@keyframes userPulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.user-no-events__icon {
  font-size: 40px;
  color: var(--brand);
}

.user-no-events__title {
  font-family: var(--font-serif);
  font-size: 1.6rem;
  font-weight: 500;
  margin: 0 0 0.75rem;
  color: var(--ink);
}

.user-no-events__text {
  font-size: 0.95rem;
  color: var(--muted);
  line-height: 1.7;
  margin: 0 0 1.5rem;
}

.user-no-events__actions {
  display: flex;
  justify-content: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

/* =========================================================================
   CTA COMUNIDAD
   ========================================================================= */
.user-cta {
  position: relative;
  background: var(--ink);
  color: var(--white);
  overflow: hidden;
  min-height: 520px;
  width: 100%;
}

.user-cta__bg-wrapper {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--ink);
}

.user-cta__bg {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center 40%;
  display: block;
}

.user-cta__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    135deg,
    rgba(23, 20, 18, 0.85) 0%,
    rgba(23, 20, 18, 0.4) 50%,
    rgba(23, 20, 18, 0.7) 100%
  );
}

.user-cta__content {
  position: relative;
  z-index: 1;
  width: 100%;
  min-height: 520px;
  display: flex;
  align-items: center;
  padding: 0;
}

.user-cta__inner {
  max-width: 1240px;
  margin: 0 auto;
  padding: 4rem 2.5rem;
  width: 100%;
}

.user-cta__badge {
  display: inline-block;
  font-size: 0.65rem;
  font-weight: 600;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 0.35rem 1rem;
  border-radius: 999px;
  margin-bottom: 1.5rem;
}

.user-cta__title {
  font-family: var(--font-serif);
  font-size: 3.2rem;
  font-weight: 500;
  line-height: 1.1;
  margin: 0 0 1.25rem;
  max-width: 700px;
}

.user-cta__title-highlight {
  color: var(--brand);
  font-style: italic;
  position: relative;
}

.user-cta__title-highlight::after {
  content: '';
  position: absolute;
  bottom: 4px;
  left: 0;
  right: 0;
  height: 6px;
  background: rgba(200, 30, 58, 0.25);
  border-radius: 2px;
}

.user-cta__text {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.75);
  line-height: 1.8;
  max-width: 560px;
  margin: 0 0 2rem;
}

.user-cta__actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.user-cta__btn {
  background: var(--brand);
  border-color: var(--brand);
  color: var(--white);
  padding: 0.85rem 2rem;
  font-size: 0.9rem;
  gap: 0.75rem;
}

.user-cta__btn:hover {
  background: var(--brand-dark);
  border-color: var(--brand-dark);
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(200, 30, 58, 0.35);
}

.user-cta__link {
  color: rgba(255, 255, 255, 0.6);
  text-decoration: none;
  font-size: 0.85rem;
  font-weight: 500;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}

.user-cta__link:hover {
  color: var(--white);
  gap: 0.8rem;
}

/* =========================================================================
   CONTACTO
   ========================================================================= */
.user-contact {
  display: grid;
  grid-template-columns: 1fr 1.2fr 1fr;
  gap: 3rem;
}

.user-contact__list {
  list-style: none;
  margin: 2rem 0 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.user-contact__list-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.5rem;
  border-radius: var(--radius-sm);
  transition: background-color 0.3s ease;
}

.user-contact__list-item:hover {
  background: var(--surface);
}

.user-form {
  background: var(--surface);
  border-radius: var(--radius-lg);
  padding: 2rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.user-form:hover {
  transform: translateY(-4px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06);
}

.user-form__title {
  font-weight: 600;
  margin: 0 0 1.25rem;
}

.user-form__row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.user-field {
  margin-top: 1rem;
}

.user-form__row .user-field {
  margin-top: 0;
}

.user-label {
  display: block;
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--ink-soft);
  margin-bottom: 0.4rem;
}

.user-input {
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

.user-input:focus {
  outline: none;
  border-color: var(--brand);
  box-shadow: 0 0 0 3px rgba(200, 30, 58, 0.15);
}

.user-form button.user-btn {
  margin-top: 1.25rem;
}

.user-form__note {
  text-align: center;
  font-size: 0.68rem;
  color: var(--muted-light);
  margin: 0.75rem 0 0;
}

/* =========================================================================
   FAQ
   ========================================================================= */
.user-faq__list {
  border-top: 1px solid var(--line);
  border-bottom: 1px solid var(--line);
}

.user-faq__item {
  border-bottom: 1px solid var(--line);
}

.user-faq__item:last-child {
  border-bottom: none;
}

.user-faq__question {
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

.user-faq__question:hover {
  color: var(--brand);
}

.user-faq__toggle {
  color: var(--brand);
  font-size: 1.2rem;
  line-height: 1;
  flex-shrink: 0;
  transition: transform 0.3s ease;
}

.user-faq__answer {
  padding: 0 0 1rem;
  font-size: 0.78rem;
  color: var(--muted);
  line-height: 1.6;
  margin: 0;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
  .user-services__grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .user-experiences__grid {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .user-mission__grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .user-contact {
    grid-template-columns: 1fr;
  }
  
  .user-upcoming__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 900px) {
  .user-hero__grid,
  .user-about,
  .user-events-hero {
    grid-template-columns: 1fr;
  }
  
  .user-hero__copy {
    padding: 3rem 1.5rem;
    max-width: none;
  }
  
  .user-hero__media {
    min-height: 320px;
    order: -1;
  }
  
  .user-hero__fade {
    display: none;
  }
  
  .user-hero__title {
    font-size: 2.5rem;
  }
  
  .user-features__grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .user-features__item {
    border-left: none;
    border-top: 1px solid var(--line);
  }
  
  .user-services__grid {
    grid-template-columns: 1fr;
  }
  
  .user-experiences__grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .user-upcoming__grid {
    grid-template-columns: 1fr;
  }
  
  .user-mission__title {
    font-size: 2.2rem;
  }
  
  .user-cta {
    min-height: 450px;
  }
  
  .user-cta__content {
    min-height: 450px;
  }
  
  .user-cta__inner {
    padding: 3rem 1.5rem;
  }
  
  .user-cta__title {
    font-size: 2.5rem;
    max-width: 100%;
  }
  
  .user-cta__text {
    font-size: 1rem;
    max-width: 100%;
  }
  
  .user-cta__bg {
    object-position: center 50%;
  }
}

@media (max-width: 640px) {
  .user-section {
    padding: 3.5rem 1.25rem;
  }
  
  .user-h2 {
    font-size: 1.9rem;
  }
  
  .user-form__row {
    grid-template-columns: 1fr;
  }
  
  .user-mission__grid {
    grid-template-columns: 1fr;
    padding: 0 1.25rem;
  }
  
  .user-mission__title {
    font-size: 1.8rem;
  }
  
  .user-no-events {
    min-height: 250px;
    padding: 1rem;
  }
  
  .user-no-events__content {
    padding: 2rem 1rem;
  }
  
  .user-no-events__title {
    font-size: 1.3rem;
  }
  
  .user-upcoming__title {
    font-size: 1.8rem;
  }
  
  .user-event-card__badge {
    font-size: 0.45rem;
    padding: 0.2rem 0.5rem;
  }
  
  .user-event-card__date {
    min-width: 44px;
    padding: 0.3rem 0.5rem;
  }
  
  .user-event-card__day {
    font-size: 0.9rem;
  }
  
  .user-cta {
    min-height: 400px;
  }
  
  .user-cta__content {
    min-height: 400px;
  }
  
  .user-cta__inner {
    padding: 2.5rem 1.25rem;
    text-align: center;
  }
  
  .user-cta__title {
    font-size: 2rem;
  }
  
  .user-cta__text {
    font-size: 0.9rem;
  }
  
  .user-cta__actions {
    justify-content: center;
    flex-direction: column;
    align-items: stretch;
  }
  
  .user-cta__btn {
    justify-content: center;
    width: 100%;
  }
  
  .user-cta__link {
    justify-content: center;
    width: 100%;
  }
  
  .user-cta__badge {
    font-size: 0.55rem;
  }
}
</style>