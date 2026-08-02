<!-- resources/js/Pages/Terms.vue -->
<template>
  <Head title="Términos y Condiciones" />

  <div class="terms-page">
    <!-- ================= NAVBAR ================= -->
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
            href="/#inicio" 
            class="cf-nav__link"
            @click="scrollToSection($event, 'inicio')"
          >
            <i class="pi pi-home cf-nav__icon"></i>
            Inicio
          </a>
          <a 
            href="/#quienes-somos" 
            class="cf-nav__link"
            @click="scrollToSection($event, 'quienes-somos')"
          >
            <i class="pi pi-info-circle cf-nav__icon"></i>
            Quiénes somos
          </a>
          <a 
            href="/#servicios" 
            class="cf-nav__link"
            @click="scrollToSection($event, 'servicios')"
          >
            <i class="pi pi-briefcase cf-nav__icon"></i>
            Servicios
          </a>
          <a 
            href="/#eventos" 
            class="cf-nav__link"
            @click="scrollToSection($event, 'eventos')"
          >
            <i class="pi pi-calendar cf-nav__icon"></i>
            Eventos
          </a>
          <a 
            href="/#contacto" 
            class="cf-nav__link"
            @click="scrollToSection($event, 'contacto')"
          >
            <i class="pi pi-envelope cf-nav__icon"></i>
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

          <Link :href="route('login')" class="cf-btn cf-btn--ghost">
            <i class="pi pi-sign-in cf-btn__icon"></i>
            Iniciar sesión
          </Link>
          <Link :href="route('register.invite')" class="cf-btn cf-btn--primary">
            <i class="pi pi-user-plus cf-btn__icon"></i>
            Registro
          </Link>
        </div>
      </nav>
    </header>

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
          <section id="introduccion" class="terms-section" ref="sections">
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

    <!-- ================= FOOTER ================= -->
    <footer class="cf-footer">
      <div class="cf-footer__grid">
        <div class="cf-footer__brand">
          <Link href="/" class="cf-brand">
            <img 
              src="/images/LOGO.png" 
              alt="Club de Fantasías" 
              class="cf-footer__logo"
            />
          </Link>
          <p class="cf-footer__text">
            Conexiones reales, experiencias auténticas y momentos inolvidables en un entorno seguro, discreto y confiable.
          </p>
          <div class="cf-footer__social">
            <a href="#" class="cf-footer__social-link" aria-label="Instagram">
              <i class="pi pi-instagram" style="font-size: 20px;"></i>
            </a>
            <a href="#" class="cf-footer__social-link" aria-label="Twitter">
              <i class="pi pi-twitter" style="font-size: 20px;"></i>
            </a>
            <a href="#" class="cf-footer__social-link" aria-label="YouTube">
              <i class="pi pi-youtube" style="font-size: 20px;"></i>
            </a>
            <a href="#" class="cf-footer__social-link" aria-label="WhatsApp">
              <i class="pi pi-whatsapp" style="font-size: 20px;"></i>
            </a>
          </div>
        </div>

        <div class="cf-footer__col">
          <p class="cf-footer__heading">
            <i class="pi pi-compass cf-footer__heading-icon"></i>
            Navegación
          </p>
          <ul class="cf-footer__list">
            <li><a href="/#inicio"><i class="pi pi-home cf-footer__list-icon"></i>Inicio</a></li>
            <li><a href="/#quienes-somos"><i class="pi pi-info-circle cf-footer__list-icon"></i>Quiénes somos</a></li>
            <li><a href="/#servicios"><i class="pi pi-briefcase cf-footer__list-icon"></i>Servicios</a></li>
            <li><a href="/#eventos"><i class="pi pi-calendar cf-footer__list-icon"></i>Eventos</a></li>
            <li><a href="/#contacto"><i class="pi pi-envelope cf-footer__list-icon"></i>Contacto</a></li>
          </ul>
        </div>

        <div class="cf-footer__col">
          <p class="cf-footer__heading">
            <i class="pi pi-shield cf-footer__heading-icon"></i>
            Legal
          </p>
          <ul class="cf-footer__list">
            <li><Link :href="route('terms')"><i class="pi pi-file-text cf-footer__list-icon"></i>Términos y condiciones</Link></li>
            <li><a href="/privacidad"><i class="pi pi-lock cf-footer__list-icon"></i>Política de privacidad</a></li>
            <li><a href="/cookies"><i class="pi pi-cookie cf-footer__list-icon"></i>Política de cookies</a></li>
          </ul>
        </div>

        <div class="cf-footer__col cf-footer__col--contact">
          <p class="cf-footer__heading">
            <i class="pi pi-users cf-footer__heading-icon"></i>
            Comunidad
          </p>
          <p class="cf-footer__text cf-footer__text--small">
            Acceso exclusivo por invitación. Únete a una comunidad de personas reales buscando experiencias auténticas.
          </p>
          <Link :href="route('register.invite')" class="cf-btn cf-btn--primary cf-btn--sm cf-footer__cta">
            <i class="pi pi-user-plus cf-btn__icon"></i>
            Registrarme →
          </Link>
          
          <div class="cf-footer__contact-info">
            <div class="cf-footer__contact-item">
              <i class="pi pi-envelope cf-footer__contact-icon"></i>
              <a href="mailto:info@clubfantasias.com">info@clubfantasias.com</a>
            </div>
            <div class="cf-footer__contact-item">
              <i class="pi pi-phone cf-footer__contact-icon"></i>
              <a href="tel:+521234567890">+52 123 456 7890</a>
            </div>
            <div class="cf-footer__contact-item">
              <i class="pi pi-clock cf-footer__contact-icon"></i>
              <span>Lun - Vie: 10:00 - 20:00</span>
            </div>
          </div>
        </div>
      </div>

      <div class="cf-footer__bottom">
        <div class="cf-footer__bottom-content">
          <span>
            © {{ new Date().getFullYear() }} Club de Fantasías. Todos los derechos reservados.
          </span>
          <span class="cf-footer__bottom-badge">
            <i class="pi pi-shield cf-footer__bottom-icon"></i>
            Comunidad verificada
          </span>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const currentYear = new Date().getFullYear();
const isMobileMenuOpen = ref(false);
const activeSection = ref('introduccion');
const sectionObserver = ref(null);

// Función para scroll suave a una sección
function scrollTo(sectionId) {
  const element = document.getElementById(sectionId);
  if (element) {
    const header = document.querySelector('.cf-header');
    const headerHeight = header ? header.offsetHeight : 80;
    const targetPosition = element.getBoundingClientRect().top + window.pageYOffset - headerHeight - 20;
    
    window.scrollTo({
      top: targetPosition,
      behavior: 'smooth'
    });
    
    // Actualizar sección activa
    activeSection.value = sectionId;
  }
}

// Función para scrollToSection (usada en el navbar)
function scrollToSection(event, sectionId) {
  event.preventDefault();
  
  if (isMobileMenuOpen.value) {
    isMobileMenuOpen.value = false;
  }
  
  if (sectionId) {
    window.location.href = `/#${sectionId}`;
  } else {
    window.location.href = '/';
  }
}

// Función para toggle del menú móvil
function toggleMobileMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
}

// Detectar sección activa en scroll
function updateActiveSection() {
  const sections = [
    'introduccion', 'aceptacion', 'registro', 'uso', 
    'propiedad', 'privacidad', 'suspension', 'cambios', 'contacto'
  ];
  
  const header = document.querySelector('.cf-header');
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

// Click en el sidebar para scroll suave (prevenir comportamiento por defecto)
function handleSidebarClick(event) {
  // El evento se maneja en el @click.prevent del enlace
}

onMounted(() => {
  // Observer para detectar secciones visibles
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
  
  // Observar todas las secciones
  const sections = document.querySelectorAll('.terms-section');
  sections.forEach(section => {
    observer.observe(section);
  });
  
  sectionObserver.value = observer;
  
  // También usar scroll para detectar
  window.addEventListener('scroll', updateActiveSection);
});

onBeforeUnmount(() => {
  // Limpiar observer y event listeners
  if (sectionObserver.value) {
    sectionObserver.value.disconnect();
  }
  window.removeEventListener('scroll', updateActiveSection);
});
</script>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA
   ========================================================================= */
.terms-page {
  --brand: #C81E3A;
  --brand-dark: #A6152D;
  --brand-soft: #FBEAEC;
  --brand-light: #FED7D4;
  --ink: #171412;
  --ink-soft: #4B4744;
  --muted: #8A8481;
  --muted-light: #B7B2AF;
  --line: #ECE9E7;
  --surface: #FAF8F7;
  --white: #FFFFFF;
  --shadow-card: 0 30px 60px -20px rgba(23, 20, 18, 0.18);
  --shadow-sm: 0 4px 12px rgba(0, 0, 0, 0.06);
  --success: #48BB78;
  --warning: #ECC94B;
  --warning-bg: #FFFFF0;
  --info-bg: #EBF8FF;

  --font-serif: 'Fraunces', Georgia, serif;
  --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;
  --container: 1240px;
  --radius-sm: 10px;
  --radius-md: 16px;
  --radius-lg: 24px;
  --radius-full: 999px;

  font-family: var(--font-sans);
  color: var(--ink);
  background: var(--surface);
  -webkit-font-smoothing: antialiased;
}

.terms-page * {
  box-sizing: border-box;
}

.terms-accent {
  color: var(--brand);
}

/* =========================================================================
   ICONOS - ESTANDARIZADOS PARA TODOS
   ========================================================================= */
.cf-nav__icon,
.cf-btn__icon,
.terms-btn__icon,
.cf-footer__heading-icon,
.cf-footer__list-icon,
.cf-footer__contact-icon,
.cf-footer__bottom-icon,
.terms-hero__badge-icon,
.terms-hero__subtitle-icon,
.terms-hero__meta-icon,
.terms-sidebar__header-icon,
.terms-sidebar__link-icon,
.terms-sidebar__footer-icon,
.terms-section__title-icon,
.terms-highlight__icon,
.terms-list__icon,
.terms-card__icon,
.terms-principle__icon,
.terms-alert__icon,
.terms-feature__icon,
.terms-contact-item__icon,
.terms-notice__icon,
.terms-content-footer__icon {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  vertical-align: middle !important;
  flex-shrink: 0 !important;
  line-height: 1 !important;
}

/* Tamaños específicos */
.cf-nav__icon {
  font-size: 16px !important;
}

.cf-btn__icon {
  font-size: 16px !important;
}

.terms-btn__icon {
  font-size: 16px !important;
}

.cf-footer__heading-icon {
  font-size: 16px !important;
  color: var(--brand);
}

.cf-footer__list-icon {
  font-size: 14px !important;
  color: var(--brand);
}

.cf-footer__contact-icon {
  font-size: 14px !important;
  color: var(--brand);
}

.cf-footer__bottom-icon {
  font-size: 14px !important;
  color: var(--brand);
}

.terms-hero__badge-icon {
  font-size: 14px !important;
}

.terms-hero__subtitle-icon {
  font-size: 18px !important;
}

.terms-hero__meta-icon {
  font-size: 14px !important;
}

.terms-sidebar__header-icon {
  font-size: 18px !important;
}

.terms-sidebar__link-icon {
  font-size: 14px !important;
}

.terms-sidebar__footer-icon {
  font-size: 16px !important;
}

.terms-section__title-icon {
  font-size: 20px !important;
  color: var(--brand);
}

.terms-highlight__icon {
  font-size: 20px !important;
}

.terms-list__icon {
  font-size: 16px !important;
  color: var(--brand);
}

.terms-card__icon {
  font-size: 24px !important;
  color: var(--brand);
}

.terms-principle__icon {
  font-size: 20px !important;
  color: var(--brand);
}

.terms-alert__icon {
  font-size: 20px !important;
  color: var(--warning);
}

.terms-feature__icon {
  font-size: 20px !important;
  color: var(--brand);
}

.terms-contact-item__icon {
  font-size: 18px !important;
  color: var(--brand);
}

.terms-notice__icon {
  font-size: 20px !important;
  color: var(--brand);
}

.terms-content-footer__icon {
  font-size: 14px !important;
}

/* =========================================================================
   SIDEBAR - ESTILOS MEJORADOS Y FUNCIONALES
   ========================================================================= */
.terms-sidebar__link--active {
  background: var(--brand-soft) !important;
  color: var(--brand) !important;
  font-weight: 600 !important;
}

.terms-sidebar__link--active .terms-sidebar__link-icon {
  color: var(--brand) !important;
}

/* =========================================================================
   BOTONES
   ========================================================================= */
.terms-btn {
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

.terms-btn--primary {
  background: var(--brand);
  color: var(--white);
  box-shadow: 0 4px 12px rgba(200, 30, 58, 0.25);
}
.terms-btn--primary:hover {
  background: var(--brand-dark);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(200, 30, 58, 0.35);
}

.terms-btn--outline {
  background: transparent;
  border-color: #D9D5D2;
  color: var(--ink);
}
.terms-btn--outline:hover {
  border-color: var(--brand);
  color: var(--brand);
  background: var(--brand-soft);
}

.terms-btn--large {
  padding: 1rem 2rem;
  font-size: 0.95rem;
}

.cf-btn {
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

.cf-btn--primary {
  background: var(--brand);
  color: var(--white);
  box-shadow: 0 4px 12px rgba(200, 30, 58, 0.25);
}
.cf-btn--primary:hover {
  background: var(--brand-dark);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(200, 30, 58, 0.35);
}

.cf-btn--ghost {
  background: transparent;
  border-color: #D9D5D2;
  color: var(--ink);
}
.cf-btn--ghost:hover {
  border-color: var(--brand);
  color: var(--brand);
  background: var(--brand-soft);
}

.cf-btn--sm {
  padding: 0.55rem 1.1rem;
  font-size: 0.78rem;
}

/* =========================================================================
   HEADER / NAVBAR
   ========================================================================= */
.cf-header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: var(--white);
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
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
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
.terms-hero {
  position: relative;
  background: linear-gradient(135deg, #FAF8F7 0%, #F5F0ED 100%);
  padding: 4rem 2.5rem 4rem;
  border-bottom: 1px solid var(--line);
  overflow: hidden;
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
  border-radius: var(--radius-full);
  font-size: 0.7rem;
  font-weight: 600;
  color: var(--brand);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-bottom: 1.5rem;
}
.terms-hero__badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--brand);
  animation: pulse-dot 2s infinite;
}
@keyframes pulse-dot {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.3; }
}

.terms-hero__title {
  font-family: var(--font-serif);
  font-size: 3.2rem;
  font-weight: 500;
  line-height: 1.2;
  margin: 0 0 0.5rem;
}

.terms-hero__subtitle {
  font-size: 1.05rem;
  color: var(--ink-soft);
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0 0 1.5rem;
}

.terms-hero__meta {
  display: flex;
  gap: 2rem;
  font-size: 0.78rem;
  color: var(--muted);
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
  background: var(--brand);
  top: -100px;
  right: -100px;
}
.terms-hero__circle--2 {
  width: 300px;
  height: 300px;
  background: var(--brand);
  bottom: -50px;
  right: 50px;
}
.terms-hero__circle--3 {
  width: 200px;
  height: 200px;
  background: var(--brand);
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
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--line);
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
  border-radius: var(--radius-sm);
  font-size: 0.82rem;
  font-weight: 500;
  color: var(--ink-soft);
  text-decoration: none;
  transition: all 0.2s ease;
  cursor: pointer;
}
.terms-sidebar__link:hover {
  background: var(--brand-soft);
  color: var(--brand);
}
.terms-sidebar__link--active {
  background: var(--brand-soft) !important;
  color: var(--brand) !important;
  font-weight: 600 !important;
}
.terms-sidebar__link--active .terms-sidebar__link-icon {
  color: var(--brand) !important;
}

.terms-sidebar__footer {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 0.75rem;
  margin-top: 1rem;
  border-radius: var(--radius-sm);
  background: var(--brand-soft);
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--brand);
}

/* =========================================================================
   CONTENIDO - (Mantiene todos los estilos existentes)
   ========================================================================= */
.terms-content {
  min-width: 0;
}

.terms-section {
  padding-bottom: 2.5rem;
  border-bottom: 1px solid var(--line);
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
  font-family: var(--font-serif);
  font-size: 1.8rem;
  font-weight: 500;
  color: var(--brand);
  opacity: 0.3;
  line-height: 1;
}

.terms-section__title {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-family: var(--font-serif);
  font-size: 1.6rem;
  font-weight: 500;
  margin: 0;
}

.terms-section__content p {
  line-height: 1.8;
  color: var(--ink-soft);
  margin: 0 0 1rem;
}
.terms-section__content p:last-child {
  margin-bottom: 0;
}

/* =========================================================================
   COMPONENTES - (Mantiene todos los estilos existentes)
   ========================================================================= */
.terms-highlight {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: var(--brand-soft);
  border-radius: var(--radius-md);
  border-left: 4px solid var(--brand);
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
  background: var(--surface);
  border-radius: var(--radius-md);
  border: 1px solid var(--line);
  transition: all 0.2s ease;
}
.terms-card:hover {
  border-color: var(--brand);
  transform: translateY(-4px);
  box-shadow: var(--shadow-sm);
}

.terms-card__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: var(--brand-soft);
  margin-bottom: 0.75rem;
}

.terms-card__title {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 0.3rem;
  color: var(--ink);
}

.terms-card__text {
  font-size: 0.85rem;
  color: var(--muted);
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
  background: var(--surface);
  border-radius: var(--radius-sm);
  border: 1px solid var(--line);
  transition: all 0.2s ease;
}
.terms-principle:hover {
  border-color: var(--brand);
}
.terms-principle__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--brand-soft);
}
.terms-principle strong {
  font-size: 0.88rem;
  display: block;
}
.terms-principle p {
  margin: 0.1rem 0 0 !important;
  font-size: 0.78rem;
  color: var(--muted);
}

.terms-alert {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: var(--warning-bg);
  border-left: 4px solid var(--warning);
  border-radius: var(--radius-sm);
  margin-top: 1rem;
}
.terms-alert > div {
  flex: 1;
}
.terms-alert strong {
  display: block;
  color: var(--ink);
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
  color: var(--ink-soft);
  padding: 0.15rem 0;
  padding-left: 1.5rem;
  position: relative;
}
.terms-alert ul li::before {
  content: '•';
  position: absolute;
  left: 0;
  color: var(--warning);
  font-weight: bold;
}

.terms-feature {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1.25rem;
  background: var(--surface);
  border-radius: var(--radius-sm);
  border: 1px solid var(--line);
  transition: all 0.2s ease;
}
.terms-feature:hover {
  border-color: var(--brand);
}
.terms-feature__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--brand-soft);
}
.terms-feature h5 {
  font-size: 0.92rem;
  font-weight: 600;
  margin: 0 0 0.15rem;
  color: var(--ink);
}
.terms-feature p {
  font-size: 0.82rem;
  color: var(--muted);
  margin: 0;
  line-height: 1.5;
}

.terms-timeline {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 1rem;
}
.terms-timeline__item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 0.75rem 1rem;
  background: var(--surface);
  border-radius: var(--radius-sm);
  border: 1px solid var(--line);
  transition: all 0.2s ease;
}
.terms-timeline__item:hover {
  border-color: var(--brand);
}
.terms-timeline__bullet {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--brand-soft);
  color: var(--brand);
  font-weight: 700;
  font-size: 0.78rem;
}
.terms-timeline__item h5 {
  font-size: 0.9rem;
  font-weight: 600;
  margin: 0 0 0.1rem;
  color: var(--ink);
}
.terms-timeline__item p {
  font-size: 0.82rem;
  color: var(--muted);
  margin: 0;
}

.terms-notice {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: var(--info-bg);
  border-radius: var(--radius-sm);
  margin-top: 1rem;
}
.terms-notice strong {
  display: block;
  color: var(--ink);
  font-size: 0.92rem;
  margin-bottom: 0.15rem;
}
.terms-notice p {
  font-size: 0.85rem;
  color: var(--ink-soft);
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
  background: var(--surface);
  border-radius: var(--radius-sm);
  border: 1px solid var(--line);
  transition: all 0.2s ease;
}
.terms-contact-item:hover {
  border-color: var(--brand);
}
.terms-contact-item__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--brand-soft);
}
.terms-contact-item__label {
  display: block;
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--muted);
  margin-bottom: 0.15rem;
}
.terms-contact-item a {
  color: var(--brand);
  text-decoration: none;
  font-size: 0.88rem;
  font-weight: 500;
}
.terms-contact-item a:hover {
  text-decoration: underline;
}
.terms-contact-item span {
  font-size: 0.88rem;
  color: var(--ink-soft);
}

.terms-footer-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--line);
}

.terms-content-footer {
  padding-top: 2rem;
  margin-top: 2rem;
  border-top: 1px solid var(--line);
}
.terms-content-footer__line {
  height: 2px;
  width: 60px;
  background: var(--brand);
  margin-bottom: 1rem;
}
.terms-content-footer__content {
  display: flex;
  gap: 2rem;
  font-size: 0.78rem;
  color: var(--muted);
}
.terms-content-footer__content span {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

/* =========================================================================
   FOOTER - (Mantiene todos los estilos existentes)
   ========================================================================= */
.cf-footer {
  background: var(--ink);
  color: var(--white);
  border-top: none;
  margin-top: 0;
}

.cf-footer__grid {
  max-width: var(--container);
  margin: 0 auto;
  padding: 4rem 2.5rem;
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1.2fr;
  gap: 2.5rem;
}

.cf-footer__brand {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.cf-footer__logo {
  height: 45px;
  width: auto;
  object-fit: contain;
  transition: transform 0.3s ease;
}
.cf-footer__logo:hover {
  transform: scale(1.05);
}

.cf-footer__text {
  font-size: 0.82rem;
  color: rgba(255, 255, 255, 0.6);
  line-height: 1.7;
  max-width: 320px;
  margin: 0;
}
.cf-footer__text--small {
  font-size: 0.75rem;
}

.cf-footer__social {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.cf-footer__social-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: rgba(255, 255, 255, 0.6);
  transition: all 0.3s ease;
  text-decoration: none;
}

.cf-footer__social-link:hover {
  background: var(--brand);
  border-color: var(--brand);
  color: var(--white);
  transform: translateY(-3px);
  box-shadow: 0 8px 20px rgba(200, 30, 58, 0.3);
}

.cf-footer__col {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.cf-footer__col--contact {
  gap: 0.75rem;
}

.cf-footer__heading {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: rgba(255, 255, 255, 0.4);
  margin: 0 0 0.25rem;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.cf-footer__list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.cf-footer__list a {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: rgba(255, 255, 255, 0.6);
  text-decoration: none;
  font-size: 0.82rem;
  transition: all 0.3s ease;
}

.cf-footer__list a:hover {
  color: var(--white);
  transform: translateX(4px);
}

.cf-footer__cta {
  background: var(--brand);
  border-color: var(--brand);
  align-self: flex-start;
  margin-top: 0.5rem;
}
.cf-footer__cta:hover {
  background: var(--brand-dark);
  border-color: var(--brand-dark);
}

.cf-footer__contact-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.5rem;
  padding-top: 0.75rem;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.cf-footer__contact-item {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
}

.cf-footer__contact-item a {
  color: rgba(255, 255, 255, 0.6);
  text-decoration: none;
  transition: color 0.3s ease;
}

.cf-footer__contact-item a:hover {
  color: var(--white);
}

.cf-footer__bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  padding: 1.25rem 2.5rem;
}

.cf-footer__bottom-content {
  max-width: var(--container);
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 0.5rem;
  font-size: 0.72rem;
  color: rgba(255, 255, 255, 0.35);
}

.cf-footer__bottom-separator {
  margin: 0 0.5rem;
  opacity: 0.3;
}

.cf-footer__bottom-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.3rem 0.8rem;
  border-radius: var(--radius-full);
  background: rgba(255, 255, 255, 0.05);
  font-weight: 500;
  color: rgba(255, 255, 255, 0.5);
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
  .cf-footer__grid {
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
  .cf-nav__actions .cf-btn--ghost {
    display: none;
  }

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

  .cf-footer__grid {
    grid-template-columns: 1fr;
    padding: 3rem 1.25rem;
    gap: 2rem;
  }
  .cf-footer__bottom {
    padding: 1rem 1.25rem;
  }
  .cf-footer__bottom-content {
    flex-direction: column;
    text-align: center;
  }
  .cf-footer__bottom-separator {
    display: none;
  }
  .cf-footer__text {
    max-width: none;
    text-align: center;
  }
  .cf-footer__brand {
    align-items: center;
  }
  .cf-footer__col {
    align-items: center;
    text-align: center;
  }
  .cf-footer__list a {
    justify-content: center;
  }
  .cf-footer__contact-info {
    align-items: center;
  }
  .cf-footer__cta {
    align-self: center;
  }
  .cf-footer__social {
    justify-content: center;
  }
}

@media (max-width: 720px) {
  .cf-nav {
    height: 70px;
    padding: 0 1.25rem;
  }
  .cf-brand__logo {
    height: 40px;
  }
  .cf-nav__links {
    top: 70px;
  }

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