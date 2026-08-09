<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';

const currentYear = new Date().getFullYear();
const activeSection = ref('introduccion');
const sectionObserver = ref(null);

function scrollTo(sectionId) {
  const element = document.getElementById(sectionId);
  if (element) {
    const header = document.querySelector('.user-header');
    const headerHeight = header ? header.offsetHeight : 80;
    const targetPosition = element.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20;
    
    window.scrollTo({
      top: targetPosition,
      behavior: 'smooth'
    });
    
    activeSection.value = sectionId;
  }
}

function updateActiveSection() {
  const sections = [
    'introduccion', 'aceptacion', 'registro', 'uso', 
    'propiedad', 'privacidad', 'suspension', 'cambios', 'contacto'
  ];
  
  const header = document.querySelector('.user-header');
  const headerHeight = header ? header.offsetHeight : 80;
  const scrollPosition = window.pageYOffset + headerHeight + 50;
  
  let currentSection = 'introduccion';
  
  for (const sectionId of sections) {
    const element = document.getElementById(sectionId);
    if (element) {
      const offsetTop = element.offsetTop;
      const offsetBottom = offsetTop + element.offsetHeight;
      
      if (scrollPosition >= offsetTop && scrollPosition < offsetBottom) {
        currentSection = sectionId;
        break;
      }
    }
  }
  
  activeSection.value = currentSection;
}

onMounted(() => {
  const options = {
    root: null,
    rootMargin: '-100px 0px -100px 0px',
    threshold: 0
  };
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const sectionId = entry.target.id;
        if (sectionId) {
          activeSection.value = sectionId;
        }
      }
    });
  }, options);
  
  const sections = document.querySelectorAll('.terms-section');
  sections.forEach(section => {
    observer.observe(section);
  });
  
  sectionObserver.value = observer;
  window.addEventListener('scroll', updateActiveSection);
});

onBeforeUnmount(() => {
  if (sectionObserver.value) {
    sectionObserver.value.disconnect();
  }
  window.removeEventListener('scroll', updateActiveSection);
});
</script>

<template>
  <UserLayout title="Términos y Condiciones">
    <!-- ================= HERO ================= -->
    <section class="terms-hero">
      <div class="terms-hero__content">
        <div class="terms-hero__badge">
          <span class="terms-hero__badge-dot"></span>
          <i class="pi pi-file-text terms-hero__badge-icon"></i>
          <span>Documento legal</span>
        </div>
        <h1 class="terms-hero__title">
          Términos y <span class="terms-accent">Condiciones</span>
        </h1>
        <p class="terms-hero__subtitle">
          <i class="pi pi-calendar terms-hero__subtitle-icon"></i>
          Última actualización: {{ currentYear }}
        </p>
        <div class="terms-hero__meta">
          <span>
            <i class="pi pi-clock terms-hero__meta-icon"></i>
            Tiempo de lectura: 5 min
          </span>
          <span>
            <i class="pi pi-users terms-hero__meta-icon"></i>
            Aplicable a todos los usuarios
          </span>
          <span>
            <i class="pi pi-shield terms-hero__meta-icon"></i>
            Documento legal
          </span>
        </div>
      </div>
      <div class="terms-hero__decoration">
        <div class="terms-hero__circle terms-hero__circle--1"></div>
        <div class="terms-hero__circle terms-hero__circle--2"></div>
        <div class="terms-hero__circle terms-hero__circle--3"></div>
      </div>
    </section>

    <!-- ================= CONTENIDO PRINCIPAL ================= -->
    <main class="terms-main">
      <div class="terms-container">
        <!-- ===== SIDEBAR ===== -->
        <aside class="terms-sidebar">
          <div class="terms-sidebar__sticky">
            <div class="terms-sidebar__header">
              <i class="pi pi-list terms-sidebar__header-icon"></i>
              <span>Contenido</span>
            </div>
            <nav class="terms-sidebar__nav">
              <a 
                href="#introduccion" 
                class="terms-sidebar__link" 
                :class="{ 'terms-sidebar__link--active': activeSection === 'introduccion' }"
                @click.prevent="scrollTo('introduccion')"
              >
                <i class="pi pi-info-circle terms-sidebar__link-icon"></i>
                Introducción
              </a>
              <a 
                href="#aceptacion" 
                class="terms-sidebar__link"
                :class="{ 'terms-sidebar__link--active': activeSection === 'aceptacion' }"
                @click.prevent="scrollTo('aceptacion')"
              >
                <i class="pi pi-check-circle terms-sidebar__link-icon"></i>
                Aceptación
              </a>
              <a 
                href="#registro" 
                class="terms-sidebar__link"
                :class="{ 'terms-sidebar__link--active': activeSection === 'registro' }"
                @click.prevent="scrollTo('registro')"
              >
                <i class="pi pi-user-plus terms-sidebar__link-icon"></i>
                Registro
              </a>
              <a 
                href="#uso" 
                class="terms-sidebar__link"
                :class="{ 'terms-sidebar__link--active': activeSection === 'uso' }"
                @click.prevent="scrollTo('uso')"
              >
                <i class="pi pi-users terms-sidebar__link-icon"></i>
                Uso de la plataforma
              </a>
              <a 
                href="#propiedad" 
                class="terms-sidebar__link"
                :class="{ 'terms-sidebar__link--active': activeSection === 'propiedad' }"
                @click.prevent="scrollTo('propiedad')"
              >
                <i class="pi pi-copyright terms-sidebar__link-icon"></i>
                Propiedad intelectual
              </a>
              <a 
                href="#privacidad" 
                class="terms-sidebar__link"
                :class="{ 'terms-sidebar__link--active': activeSection === 'privacidad' }"
                @click.prevent="scrollTo('privacidad')"
              >
                <i class="pi pi-shield terms-sidebar__link-icon"></i>
                Privacidad
              </a>
              <a 
                href="#suspension" 
                class="terms-sidebar__link"
                :class="{ 'terms-sidebar__link--active': activeSection === 'suspension' }"
                @click.prevent="scrollTo('suspension')"
              >
                <i class="pi pi-ban terms-sidebar__link-icon"></i>
                Suspensión
              </a>
              <a 
                href="#cambios" 
                class="terms-sidebar__link"
                :class="{ 'terms-sidebar__link--active': activeSection === 'cambios' }"
                @click.prevent="scrollTo('cambios')"
              >
                <i class="pi pi-refresh terms-sidebar__link-icon"></i>
                Cambios
              </a>
              <a 
                href="#contacto" 
                class="terms-sidebar__link"
                :class="{ 'terms-sidebar__link--active': activeSection === 'contacto' }"
                @click.prevent="scrollTo('contacto')"
              >
                <i class="pi pi-envelope terms-sidebar__link-icon"></i>
                Contacto
              </a>
            </nav>
            <div class="terms-sidebar__footer">
              <i class="pi pi-shield terms-sidebar__footer-icon"></i>
              <span>Documento vigente</span>
            </div>
          </div>
        </aside>

        <!-- ===== CONTENIDO ===== -->
        <div class="terms-content">
          <!-- INTRODUCCIÓN -->
          <section id="introduccion" class="terms-section">
            <div class="terms-section__header">
              <span class="terms-section__number">01</span>
              <h2 class="terms-section__title">
                <i class="pi pi-info-circle terms-section__title-icon"></i>
                Introducción
              </h2>
            </div>
            <div class="terms-section__content">
              <p>
                Bienvenido a <strong>Club de Fantasías</strong>. Al registrarte y utilizar nuestra plataforma, 
                aceptas cumplir con los siguientes términos y condiciones. Te recomendamos leer detenidamente 
                este documento antes de continuar con el proceso de registro.
              </p>
              <p>
                Estos términos regulan el uso de nuestra plataforma, la relación entre los usuarios y 
                las obligaciones que asumes al formar parte de nuestra comunidad exclusiva para adultos.
              </p>
              <div class="terms-highlight">
                <i class="pi pi-shield terms-highlight__icon"></i>
                <div>
                  <strong>Compromiso con nuestra comunidad</strong>
                  <p>Club de Fantasías es un espacio seguro, exclusivo y respetuoso para adultos.</p>
                </div>
              </div>
            </div>
          </section>

          <!-- ACEPTACIÓN -->
          <section id="aceptacion" class="terms-section">
            <div class="terms-section__header">
              <span class="terms-section__number">02</span>
              <h2 class="terms-section__title">
                <i class="pi pi-check-circle terms-section__title-icon"></i>
                Aceptación de los Términos
              </h2>
            </div>
            <div class="terms-section__content">
              <p>
                Al crear una cuenta en <strong>Club de Fantasías</strong>, aceptas de manera expresa e 
                irrevocable estos Términos y Condiciones, así como nuestra Política de Privacidad.
              </p>
              <ul class="terms-list">
                <li>
                  <i class="pi pi-check terms-list__icon"></i>
                  <span>Debes ser mayor de 18 años para registrarte.</span>
                </li>
                <li>
                  <i class="pi pi-check terms-list__icon"></i>
                  <span>La información proporcionada debe ser veraz y actualizada.</span>
                </li>
                <li>
                  <i class="pi pi-check terms-list__icon"></i>
                  <span>Eres responsable de mantener la confidencialidad de tu cuenta.</span>
                </li>
                <li>
                  <i class="pi pi-check terms-list__icon"></i>
                  <span>Te comprometes a utilizar la plataforma de manera respetuosa.</span>
                </li>
              </ul>
            </div>
          </section>

          <!-- REGISTRO -->
          <section id="registro" class="terms-section">
            <div class="terms-section__header">
              <span class="terms-section__number">03</span>
              <h2 class="terms-section__title">
                <i class="pi pi-user-plus terms-section__title-icon"></i>
                Registro y Cuenta de Usuario
              </h2>
            </div>
            <div class="terms-section__content">
              <p>
                Para acceder a los servicios de <strong>Club de Fantasías</strong>, es necesario crear una 
                cuenta proporcionando datos personales como nombre de usuario, correo electrónico y 
                fecha de nacimiento.
              </p>
              <div class="terms-cards">
                <div class="terms-card">
                  <div class="terms-card__icon"><i class="pi pi-user"></i></div>
                  <h4 class="terms-card__title">Perfil</h4>
                  <p class="terms-card__text">
                    Debes proporcionar información precisa y completa. Cualquier información falsa 
                    puede resultar en la suspensión de tu cuenta.
                  </p>
                </div>
                <div class="terms-card">
                  <div class="terms-card__icon"><i class="pi pi-lock"></i></div>
                  <h4 class="terms-card__title">Seguridad</h4>
                  <p class="terms-card__text">
                    Eres responsable de mantener la seguridad de tu cuenta. No compartas tu 
                    contraseña con terceros.
                  </p>
                </div>
                <div class="terms-card">
                  <div class="terms-card__icon"><i class="pi pi-shield"></i></div>
                  <h4 class="terms-card__title">Verificación</h4>
                  <p class="terms-card__text">
                    Nos reservamos el derecho de verificar la identidad de los usuarios para 
                    mantener la seguridad de la comunidad.
                  </p>
                </div>
              </div>
            </div>
          </section>

          <!-- USO -->
          <section id="uso" class="terms-section">
            <div class="terms-section__header">
              <span class="terms-section__number">04</span>
              <h2 class="terms-section__title">
                <i class="pi pi-users terms-section__title-icon"></i>
                Uso de la Plataforma
              </h2>
            </div>
            <div class="terms-section__content">
              <p>
                <strong>Club de Fantasías</strong> es una comunidad para adultos que busca conectar 
                personas con intereses afines. El uso de la plataforma debe regirse por los siguientes 
                principios:
              </p>
              <div class="terms-grid-2">
                <div class="terms-principle">
                  <div class="terms-principle__icon"><i class="pi pi-heart"></i></div>
                  <div>
                    <strong>Respeto mutuo</strong>
                    <p>Trata a los demás usuarios con respeto y cortesía.</p>
                  </div>
                </div>
                <div class="terms-principle">
                  <div class="terms-principle__icon"><i class="pi pi-eye-slash"></i></div>
                  <div>
                    <strong>Privacidad</strong>
                    <p>No compartas información personal de otros usuarios.</p>
                  </div>
                </div>
                <div class="terms-principle">
                  <div class="terms-principle__icon"><i class="pi pi-ban"></i></div>
                  <div>
                    <strong>Contenido inapropiado</strong>
                    <p>No está permitido el contenido ofensivo o ilegal.</p>
                  </div>
                </div>
                <div class="terms-principle">
                  <div class="terms-principle__icon"><i class="pi pi-flag"></i></div>
                  <div>
                    <strong>Reportes</strong>
                    <p>Reporta cualquier comportamiento inapropiado.</p>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- PROPIEDAD INTELECTUAL -->
          <section id="propiedad" class="terms-section">
            <div class="terms-section__header">
              <span class="terms-section__number">05</span>
              <h2 class="terms-section__title">
                <i class="pi pi-copyright terms-section__title-icon"></i>
                Propiedad Intelectual
              </h2>
            </div>
            <div class="terms-section__content">
              <p>
                Todo el contenido de <strong>Club de Fantasías</strong> incluyendo texto, imágenes, 
                logotipos, diseño y software, está protegido por derechos de autor y otras leyes 
                de propiedad intelectual.
              </p>
              <div class="terms-alert">
                <i class="pi pi-exclamation-triangle terms-alert__icon"></i>
                <div>
                  <strong>Prohibido:</strong>
                  <ul>
                    <li>Reproducir o distribuir contenido sin autorización.</li>
                    <li>Utilizar el contenido para fines comerciales.</li>
                    <li>Modificar o crear trabajos derivados.</li>
                  </ul>
                </div>
              </div>
            </div>
          </section>

          <!-- PRIVACIDAD -->
          <section id="privacidad" class="terms-section">
            <div class="terms-section__header">
              <span class="terms-section__number">06</span>
              <h2 class="terms-section__title">
                <i class="pi pi-shield terms-section__title-icon"></i>
                Privacidad y Protección de Datos
              </h2>
            </div>
            <div class="terms-section__content">
              <p>
                En <strong>Club de Fantasías</strong>, nos tomamos muy en serio tu privacidad. 
                Todos tus datos personales son tratados conforme a nuestra Política de Privacidad.
              </p>
              <div class="terms-grid-2">
                <div class="terms-feature">
                  <div class="terms-feature__icon"><i class="pi pi-lock"></i></div>
                  <div>
                    <h5>Datos encriptados</h5>
                    <p>Todos tus datos están protegidos con encriptación de última generación.</p>
                  </div>
                </div>
                <div class="terms-feature">
                  <div class="terms-feature__icon"><i class="pi pi-user"></i></div>
                  <div>
                    <h5>Uso limitado</h5>
                    <p>Tu información solo se utiliza para mejorar tu experiencia en la plataforma.</p>
                  </div>
                </div>
                <div class="terms-feature">
                  <div class="terms-feature__icon"><i class="pi pi-share-alt"></i></div>
                  <div>
                    <h5>No compartimos</h5>
                    <p>Nunca compartimos tus datos con terceros sin tu consentimiento explícito.</p>
                  </div>
                </div>
                <div class="terms-feature">
                  <div class="terms-feature__icon"><i class="pi pi-trash"></i></div>
                  <div>
                    <h5>Derecho al olvido</h5>
                    <p>Puedes solicitar la eliminación de tus datos en cualquier momento.</p>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- SUSPENSIÓN -->
          <section id="suspension" class="terms-section">
            <div class="terms-section__header">
              <span class="terms-section__number">07</span>
              <h2 class="terms-section__title">
                <i class="pi pi-ban terms-section__title-icon"></i>
                Suspensión y Cancelación de Cuenta
              </h2>
            </div>
            <div class="terms-section__content">
              <p>
                <strong>Club de Fantasías</strong> se reserva el derecho de suspender o cancelar 
                cuentas que incumplan estos términos.
              </p>
              <div class="terms-timeline">
                <div class="terms-timeline-item">
                  <span class="terms-timeline-item__bullet">1</span>
                  <div>
                    <h5>Violación de términos</h5>
                    <p>Incumplimiento de las normas de la comunidad.</p>
                  </div>
                </div>
                <div class="terms-timeline-item">
                  <span class="terms-timeline-item__bullet">2</span>
                  <div>
                    <h5>Actividad sospechosa</h5>
                    <p>Comportamiento fraudulento o engañoso.</p>
                  </div>
                </div>
                <div class="terms-timeline-item">
                  <span class="terms-timeline-item__bullet">3</span>
                  <div>
                    <h5>Contenido inapropiado</h5>
                    <p>Publicación de contenido ofensivo o ilegal.</p>
                  </div>
                </div>
                <div class="terms-timeline-item">
                  <span class="terms-timeline-item__bullet">4</span>
                  <div>
                    <h5>Falta de verificación</h5>
                    <p>No cumplir con los requisitos de verificación.</p>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- CAMBIOS -->
          <section id="cambios" class="terms-section">
            <div class="terms-section__header">
              <span class="terms-section__number">08</span>
              <h2 class="terms-section__title">
                <i class="pi pi-refresh terms-section__title-icon"></i>
                Modificaciones de los Términos
              </h2>
            </div>
            <div class="terms-section__content">
              <p>
                <strong>Club de Fantasías</strong> se reserva el derecho de actualizar estos términos 
                en cualquier momento. Notificaremos a los usuarios sobre cambios importantes a través 
                de correo electrónico o notificaciones en la plataforma.
              </p>
              <div class="terms-notice">
                <i class="pi pi-bell terms-notice__icon"></i>
                <div>
                  <strong>Mantente informado</strong>
                  <p>Te recomendamos revisar periódicamente esta página para estar al tanto de 
                  cualquier cambio en nuestros términos y condiciones.</p>
                </div>
              </div>
            </div>
          </section>

          <!-- CONTACTO -->
          <section id="contacto" class="terms-section terms-section--last">
            <div class="terms-section__header">
              <span class="terms-section__number">09</span>
              <h2 class="terms-section__title">
                <i class="pi pi-envelope terms-section__title-icon"></i>
                Contacto
              </h2>
            </div>
            <div class="terms-section__content">
              <p>
                Si tienes alguna pregunta o inquietud sobre estos Términos y Condiciones, 
                no dudes en contactarnos:
              </p>
              <div class="terms-contact-grid">
                <div class="terms-contact-item">
                  <div class="terms-contact-item__icon"><i class="pi pi-envelope"></i></div>
                  <div>
                    <span class="terms-contact-item__label">Email</span>
                    <a href="mailto:legal@clubdefantasias.com">legal@clubdefantasias.com</a>
                  </div>
                </div>
                <div class="terms-contact-item">
                  <div class="terms-contact-item__icon"><i class="pi pi-phone"></i></div>
                  <div>
                    <span class="terms-contact-item__label">Teléfono</span>
                    <a href="tel:+573001234567">+57 300 123 4567</a>
                  </div>
                </div>
                <div class="terms-contact-item">
                  <div class="terms-contact-item__icon"><i class="pi pi-map-marker"></i></div>
                  <div>
                    <span class="terms-contact-item__label">Dirección</span>
                    <span>Ciudad de México, CDMX</span>
                  </div>
                </div>
              </div>

              <div class="terms-footer-actions">
                <Link :href="route('home')" class="terms-btn terms-btn--outline terms-btn--large">
                  <i class="pi pi-arrow-left terms-btn__icon"></i>
                  Volver al inicio
                </Link>
              </div>
            </div>
          </section>

          <!-- FOOTER DEL CONTENIDO -->
          <div class="terms-content-footer">
            <div class="terms-content-footer__line"></div>
            <div class="terms-content-footer__content">
              <span>
                <i class="pi pi-shield terms-content-footer__icon"></i>
                Documento legal vigente
              </span>
              <span>
                <i class="pi pi-calendar terms-content-footer__icon"></i>
                Actualizado: {{ currentYear }}
              </span>
              <span>
                <i class="pi pi-users terms-content-footer__icon"></i>
                Aplicable a todos los usuarios
              </span>
            </div>
          </div>
        </div>
      </div>
    </main>
  </UserLayout>
</template>

<style scoped>
/* =========================================================================
   ESTILOS CON PREFIJO terms-
   ========================================================================= */
.terms-hero {
  position: relative;
  background: linear-gradient(135deg, #FAF8F7 0%, #F5F0ED 100%);
  padding: 4rem 2.5rem 4rem;
  border-bottom: 1px solid var(--line, #ECE9E7);
  overflow: hidden;
}

.terms-accent {
  color: var(--brand, #C81E3A);
}

.terms-hero__content {
  max-width: 1400px;
  margin: 0 auto;
  position: relative;
  z-index: 2;
}

.terms-hero__badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(200, 30, 58, 0.08);
  padding: 0.4rem 1rem;
  border-radius: 999px;
  font-size: 0.7rem;
  font-weight: 600;
  color: var(--brand, #C81E3A);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-bottom: 1.5rem;
}

.terms-hero__badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--brand, #C81E3A);
  animation: pulse-dot 2s infinite;
}

@keyframes pulse-dot {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}

.terms-hero__title {
  font-family: var(--font-serif, 'Fraunces', Georgia, serif);
  font-size: 3.2rem;
  font-weight: 500;
  line-height: 1.2;
  margin: 0 0 0.5rem;
}

.terms-hero__subtitle {
  font-size: 1.05rem;
  color: var(--ink-soft, #4B4744);
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0 0 1.5rem;
}

.terms-hero__meta {
  display: flex;
  gap: 2rem;
  font-size: 0.78rem;
  color: var(--muted, #8A8481);
}

.terms-hero__meta span {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.terms-hero__decoration {
  position: absolute;
  right: 0;
  top: 0;
  bottom: 0;
  width: 40%;
  pointer-events: none;
  overflow: hidden;
}

.terms-hero__circle {
  position: absolute;
  border-radius: 50%;
  opacity: 0.05;
}

.terms-hero__circle--1 {
  width: 400px;
  height: 400px;
  background: var(--brand, #C81E3A);
  top: -100px;
  right: -100px;
}

.terms-hero__circle--2 {
  width: 300px;
  height: 300px;
  background: var(--brand, #C81E3A);
  bottom: -50px;
  right: 50px;
}

.terms-hero__circle--3 {
  width: 200px;
  height: 200px;
  background: var(--brand, #C81E3A);
  top: 50%;
  right: -50px;
}

/* =========================================================================
   MAIN LAYOUT
   ========================================================================= */
.terms-main {
  max-width: 1400px;
  margin: 0 auto;
  padding: 3rem 2.5rem;
}

.terms-container {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 2.5rem;
}

/* =========================================================================
   SIDEBAR
   ========================================================================= */
.terms-sidebar {
  position: relative;
}

.terms-sidebar__sticky {
  position: sticky;
  top: 100px;
}

.terms-sidebar__header {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 700;
  font-size: 0.82rem;
  color: var(--muted, #8A8481);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--line, #ECE9E7);
  margin-bottom: 1rem;
}

.terms-sidebar__nav {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.terms-sidebar__link {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.55rem 0.75rem;
  border-radius: 10px;
  font-size: 0.82rem;
  font-weight: 500;
  color: var(--ink-soft, #4B4744);
  text-decoration: none;
  transition: all 0.2s ease;
  cursor: pointer;
}

.terms-sidebar__link:hover {
  background: var(--brand-soft, #FBEAEC);
  color: var(--brand, #C81E3A);
}

.terms-sidebar__link--active {
  background: var(--brand-soft, #FBEAEC) !important;
  color: var(--brand, #C81E3A) !important;
  font-weight: 600 !important;
}

.terms-sidebar__link--active .terms-sidebar__link-icon {
  color: var(--brand, #C81E3A) !important;
}

.terms-sidebar__footer {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 0.75rem;
  margin-top: 1rem;
  border-radius: 10px;
  background: var(--brand-soft, #FBEAEC);
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--brand, #C81E3A);
}

/* =========================================================================
   CONTENIDO
   ========================================================================= */
.terms-content {
  min-width: 0;
}

.terms-section {
  padding-bottom: 2.5rem;
  border-bottom: 1px solid var(--line, #ECE9E7);
  margin-bottom: 2.5rem;
}

.terms-section--last {
  border-bottom: none;
  margin-bottom: 0;
}

.terms-section__header {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

.terms-section__number {
  font-family: var(--font-serif, 'Fraunces', Georgia, serif);
  font-size: 1.8rem;
  font-weight: 500;
  color: var(--brand, #C81E3A);
  opacity: 0.3;
  line-height: 1;
}

.terms-section__title {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-family: var(--font-serif, 'Fraunces', Georgia, serif);
  font-size: 1.6rem;
  font-weight: 500;
  margin: 0;
}

.terms-section__content p {
  line-height: 1.8;
  color: var(--ink-soft, #4B4744);
  margin: 0 0 1rem;
}

.terms-section__content p:last-child {
  margin-bottom: 0;
}

/* =========================================================================
   COMPONENTES
   ========================================================================= */
.terms-highlight {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: var(--brand-soft, #FBEAEC);
  border-radius: 16px;
  border-left: 4px solid var(--brand, #C81E3A);
  margin-top: 1rem;
}

.terms-highlight p {
  margin: 0.25rem 0 0 !important;
  font-size: 0.88rem;
}

.terms-list {
  list-style: none;
  padding: 0;
  margin: 1rem 0 0;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.terms-list li {
  display: inline-flex;
  align-items: flex-start;
  gap: 0.6rem;
  font-size: 0.92rem;
}

.terms-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.25rem;
  margin-top: 1.25rem;
}

.terms-card {
  padding: 1.5rem;
  background: var(--surface, #FAF8F7);
  border-radius: 16px;
  border: 1px solid var(--line, #ECE9E7);
  transition: all 0.2s ease;
}

.terms-card:hover {
  border-color: var(--brand, #C81E3A);
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.terms-card__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: var(--brand-soft, #FBEAEC);
  margin-bottom: 0.75rem;
  color: var(--brand, #C81E3A);
  font-size: 24px !important;
}

.terms-card__title {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 0.3rem;
  color: var(--ink, #171412);
}

.terms-card__text {
  font-size: 0.85rem;
  color: var(--muted, #8A8481);
  margin: 0;
  line-height: 1.6;
}

.terms-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-top: 1rem;
}

.terms-principle {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem;
  background: var(--surface, #FAF8F7);
  border-radius: 10px;
  border: 1px solid var(--line, #ECE9E7);
  transition: all 0.2s ease;
}

.terms-principle:hover {
  border-color: var(--brand, #C81E3A);
}

.terms-principle__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--brand-soft, #FBEAEC);
  color: var(--brand, #C81E3A);
  font-size: 20px !important;
}

.terms-principle strong {
  font-size: 0.88rem;
  display: block;
}

.terms-principle p {
  margin: 0.1rem 0 0 !important;
  font-size: 0.78rem;
  color: var(--muted, #8A8481);
}

.terms-alert {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: #FFFFF0;
  border-left: 4px solid #ECC94B;
  border-radius: 10px;
  margin-top: 1rem;
}

.terms-alert > div {
  flex: 1;
}

.terms-alert strong {
  display: block;
  color: var(--ink, #171412);
  font-size: 0.92rem;
  margin-bottom: 0.3rem;
}

.terms-alert ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.terms-alert ul li {
  font-size: 0.85rem;
  color: var(--ink-soft, #4B4744);
  padding: 0.15rem 0;
  padding-left: 1.5rem;
  position: relative;
}

.terms-alert ul li::before {
  content: '•';
  position: absolute;
  left: 0;
  color: #ECC94B;
  font-weight: bold;
}

.terms-feature {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1.25rem;
  background: var(--surface, #FAF8F7);
  border-radius: 10px;
  border: 1px solid var(--line, #ECE9E7);
  transition: all 0.2s ease;
}

.terms-feature:hover {
  border-color: var(--brand, #C81E3A);
}

.terms-feature__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--brand-soft, #FBEAEC);
  color: var(--brand, #C81E3A);
  font-size: 20px !important;
}

.terms-feature h5 {
  font-size: 0.92rem;
  font-weight: 600;
  margin: 0 0 0.15rem;
  color: var(--ink, #171412);
}

.terms-feature p {
  font-size: 0.82rem;
  color: var(--muted, #8A8481);
  margin: 0;
  line-height: 1.5;
}

.terms-timeline {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 1rem;
}

.terms-timeline-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 0.75rem 1rem;
  background: var(--surface, #FAF8F7);
  border-radius: 10px;
  border: 1px solid var(--line, #ECE9E7);
  transition: all 0.2s ease;
}

.terms-timeline-item:hover {
  border-color: var(--brand, #C81E3A);
}

.terms-timeline-item__bullet {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--brand-soft, #FBEAEC);
  color: var(--brand, #C81E3A);
  font-weight: 700;
  font-size: 0.78rem;
}

.terms-timeline-item h5 {
  font-size: 0.9rem;
  font-weight: 600;
  margin: 0 0 0.1rem;
  color: var(--ink, #171412);
}

.terms-timeline-item p {
  font-size: 0.82rem;
  color: var(--muted, #8A8481);
  margin: 0;
}

.terms-notice {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: #EBF8FF;
  border-radius: 10px;
  margin-top: 1rem;
}

.terms-notice strong {
  display: block;
  color: var(--ink, #171412);
  font-size: 0.92rem;
  margin-bottom: 0.15rem;
}

.terms-notice p {
  font-size: 0.85rem;
  color: var(--ink-soft, #4B4744);
  margin: 0;
  line-height: 1.6;
}

.terms-contact-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.25rem;
  margin-top: 1.25rem;
}

.terms-contact-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1.25rem;
  background: var(--surface, #FAF8F7);
  border-radius: 10px;
  border: 1px solid var(--line, #ECE9E7);
  transition: all 0.2s ease;
}

.terms-contact-item:hover {
  border-color: var(--brand, #C81E3A);
}

.terms-contact-item__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--brand-soft, #FBEAEC);
  color: var(--brand, #C81E3A);
  font-size: 18px !important;
}

.terms-contact-item__label {
  display: block;
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--muted, #8A8481);
  margin-bottom: 0.15rem;
}

.terms-contact-item a {
  color: var(--brand, #C81E3A);
  text-decoration: none;
  font-size: 0.88rem;
  font-weight: 500;
}

.terms-contact-item a:hover {
  text-decoration: underline;
}

.terms-contact-item span {
  font-size: 0.88rem;
  color: var(--ink-soft, #4B4744);
}

.terms-footer-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--line, #ECE9E7);
}

.terms-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-weight: 600;
  font-size: 0.85rem;
  border-radius: 999px;
  padding: 0.7rem 1.5rem;
  text-decoration: none;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: var(--font-sans, 'Inter', system-ui, sans-serif);
}

.terms-btn--outline {
  background: transparent;
  border-color: #D9D5D2;
  color: var(--ink, #171412);
}

.terms-btn--outline:hover {
  border-color: var(--brand, #C81E3A);
  color: var(--brand, #C81E3A);
  background: var(--brand-soft, #FBEAEC);
}

.terms-btn--large {
  padding: 1rem 2rem;
  font-size: 0.95rem;
}

.terms-content-footer {
  padding-top: 2rem;
  margin-top: 2rem;
  border-top: 1px solid var(--line, #ECE9E7);
}

.terms-content-footer__line {
  height: 2px;
  width: 60px;
  background: var(--brand, #C81E3A);
  margin-bottom: 1rem;
}

.terms-content-footer__content {
  display: flex;
  gap: 2rem;
  font-size: 0.78rem;
  color: var(--muted, #8A8481);
}

.terms-content-footer__content span {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
  .terms-cards {
    grid-template-columns: 1fr 1fr;
  }
  .terms-contact-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 900px) {
  .terms-container {
    grid-template-columns: 1fr;
  }
  .terms-sidebar {
    display: none;
  }
  .terms-hero__title {
    font-size: 2.4rem;
  }
  .terms-hero__decoration {
    display: none;
  }
  .terms-grid-2 {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 720px) {
  .terms-main {
    padding: 2rem 1.25rem;
  }
  .terms-hero {
    padding: 2.5rem 1.25rem;
  }
  .terms-hero__title {
    font-size: 2rem;
  }
  .terms-hero__subtitle {
    font-size: 0.95rem;
    flex-wrap: wrap;
  }
  .terms-hero__meta {
    flex-direction: column;
    gap: 0.5rem;
  }
  .terms-cards {
    grid-template-columns: 1fr;
  }
  .terms-contact-grid {
    grid-template-columns: 1fr;
  }
  .terms-footer-actions {
    flex-direction: column;
  }
  .terms-footer-actions .terms-btn {
    width: 100%;
    justify-content: center;
  }
  .terms-content-footer__content {
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
  }
  .terms-section__title {
    font-size: 1.3rem;
  }
  .terms-section__number {
    font-size: 1.4rem;
  }
}
</style>