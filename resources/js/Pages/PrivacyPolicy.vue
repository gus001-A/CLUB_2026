<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';

const activeSection = ref('introduccion');
const sectionObserver = ref(null);

// Función para scroll suave a una sección dentro de la página
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

// Detectar sección activa en scroll
function updateActiveSection() {
  const sections = [
    'introduccion', 'responsable', 'datos', 'finalidad', 
    'transferencia', 'derechos', 'cookies', 'cambios', 'contacto'
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
  
  const sections = document.querySelectorAll('.pp-section');
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
  <UserLayout title="Aviso de Privacidad">
    <!-- ================= HERO ================= -->
    <section class="pp-hero">
      <div class="pp-hero__content">
        <div class="pp-hero__badge">
          <span class="pp-hero__badge-dot"></span>
          <i class="pi pi-shield pp-hero__badge-icon"></i>
          <span>Privacidad garantizada</span>
        </div>
        <h1 class="pp-hero__title">
          Aviso de <span class="pp-accent">Privacidad</span>
        </h1>
        <p class="pp-hero__subtitle">
          <i class="pi pi-lock pp-hero__subtitle-icon"></i>
          En Club de Fantasías, la protección de tus datos personales es nuestra prioridad.
        </p>
        <div class="pp-hero__meta">
          <span>
            <i class="pi pi-calendar pp-hero__meta-icon"></i>
            Última actualización: 15 de marzo de 2026
          </span>
          <span>
            <i class="pi pi-file pp-hero__meta-icon"></i>
            Versión 2.0
          </span>
        </div>
      </div>
      <div class="pp-hero__decoration">
        <div class="pp-hero__circle pp-hero__circle--1"></div>
        <div class="pp-hero__circle pp-hero__circle--2"></div>
        <div class="pp-hero__circle pp-hero__circle--3"></div>
      </div>
    </section>

    <!-- ================= CONTENIDO ================= -->
    <div class="pp-container">
      <!-- Barra lateral de navegación -->
      <aside class="pp-sidebar">
        <div class="pp-sidebar__sticky">
          <div class="pp-sidebar__header">
            <i class="pi pi-list pp-sidebar__header-icon"></i>
            <span>Ir a...</span>
          </div>
          <nav class="pp-sidebar__nav">
            <a 
              href="#introduccion" 
              class="pp-sidebar__link"
              :class="{ 'pp-sidebar__link--active': activeSection === 'introduccion' }"
              @click.prevent="scrollTo('introduccion')"
            >
              <i class="pi pi-info-circle pp-sidebar__link-icon"></i>
              Introducción
            </a>
            <a 
              href="#responsable" 
              class="pp-sidebar__link"
              :class="{ 'pp-sidebar__link--active': activeSection === 'responsable' }"
              @click.prevent="scrollTo('responsable')"
            >
              <i class="pi pi-building pp-sidebar__link-icon"></i>
              Responsable
            </a>
            <a 
              href="#datos" 
              class="pp-sidebar__link"
              :class="{ 'pp-sidebar__link--active': activeSection === 'datos' }"
              @click.prevent="scrollTo('datos')"
            >
              <i class="pi pi-database pp-sidebar__link-icon"></i>
              Datos recabados
            </a>
            <a 
              href="#finalidad" 
              class="pp-sidebar__link"
              :class="{ 'pp-sidebar__link--active': activeSection === 'finalidad' }"
              @click.prevent="scrollTo('finalidad')"
            >
              <i class="pi pi-bullseye pp-sidebar__link-icon"></i>
              Finalidad
            </a>
            <a 
              href="#transferencia" 
              class="pp-sidebar__link"
              :class="{ 'pp-sidebar__link--active': activeSection === 'transferencia' }"
              @click.prevent="scrollTo('transferencia')"
            >
              <i class="pi pi-share-alt pp-sidebar__link-icon"></i>
              Transferencia
            </a>
            <a 
              href="#derechos" 
              class="pp-sidebar__link"
              :class="{ 'pp-sidebar__link--active': activeSection === 'derechos' }"
              @click.prevent="scrollTo('derechos')"
            >
              <i class="pi pi-shield pp-sidebar__link-icon"></i>
              Derechos ARCO
            </a>
            <a 
              href="#cookies" 
              class="pp-sidebar__link"
              :class="{ 'pp-sidebar__link--active': activeSection === 'cookies' }"
              @click.prevent="scrollTo('cookies')"
            >
              <i class="pi pi-cookie pp-sidebar__link-icon"></i>
              Cookies
            </a>
            <a 
              href="#cambios" 
              class="pp-sidebar__link"
              :class="{ 'pp-sidebar__link--active': activeSection === 'cambios' }"
              @click.prevent="scrollTo('cambios')"
            >
              <i class="pi pi-refresh pp-sidebar__link-icon"></i>
              Cambios
            </a>
            <a 
              href="#contacto" 
              class="pp-sidebar__link"
              :class="{ 'pp-sidebar__link--active': activeSection === 'contacto' }"
              @click.prevent="scrollTo('contacto')"
            >
              <i class="pi pi-envelope pp-sidebar__link-icon"></i>
              Contacto
            </a>
          </nav>
          <div class="pp-sidebar__footer">
            <i class="pi pi-shield pp-sidebar__footer-icon"></i>
            <span>Datos protegidos</span>
          </div>
        </div>
      </aside>

      <!-- Contenido principal -->
      <main class="pp-main">
        <!-- INTRODUCCIÓN -->
        <section id="introduccion" class="pp-section">
          <div class="pp-section__header">
            <span class="pp-section__number">01</span>
            <h2 class="pp-section__title">
              <i class="pi pi-info-circle pp-section__title-icon"></i>
              Introducción
            </h2>
          </div>
          <div class="pp-section__content">
            <p>
              En <strong>Club de Fantasías</strong>, nos tomamos muy en serio la protección de tus datos personales. 
              Este Aviso de Privacidad describe cómo recopilamos, utilizamos, almacenamos y protegemos la 
              información que nos proporcionas al utilizar nuestra plataforma.
            </p>
            <p>
              Al registrarte en Club de Fantasías, aceptas las prácticas descritas en este documento. 
              Te recomendamos leerlo detenidamente para comprender cómo manejamos tu información.
            </p>
            <div class="pp-highlight">
              <i class="pi pi-shield pp-highlight__icon"></i>
              <div>
                <strong>Compromiso con tu privacidad</strong>
                <p>Tus datos personales son tratados con la más estricta confidencialidad y 
                bajo los más altos estándares de seguridad.</p>
              </div>
            </div>
          </div>
        </section>

        <!-- RESPONSABLE -->
        <section id="responsable" class="pp-section">
          <div class="pp-section__header">
            <span class="pp-section__number">02</span>
            <h2 class="pp-section__title">
              <i class="pi pi-building pp-section__title-icon"></i>
              Responsable de tus datos
            </h2>
          </div>
          <div class="pp-section__content">
            <div class="pp-card-info">
              <div class="pp-card-info__item">
                <i class="pi pi-building pp-card-info__icon"></i>
                <div>
                  <strong>Nombre del responsable</strong>
                  <p>Club de Fantasías S.A. de C.V.</p>
                </div>
              </div>
              <div class="pp-card-info__item">
                <i class="pi pi-map-marker pp-card-info__icon"></i>
                <div>
                  <strong>Domicilio</strong>
                  <p>Av. Principal #123, Colonia Centro, Ciudad de México, C.P. 06000</p>
                </div>
              </div>
              <div class="pp-card-info__item">
                <i class="pi pi-envelope pp-card-info__icon"></i>
                <div>
                  <strong>Correo electrónico</strong>
                  <p><a href="mailto:privacidad@clubdefantasias.com">privacidad@clubdefantasias.com</a></p>
                </div>
              </div>
              <div class="pp-card-info__item">
                <i class="pi pi-phone pp-card-info__icon"></i>
                <div>
                  <strong>Teléfono</strong>
                  <p>+52 (55) 1234-5678</p>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- DATOS RECABADOS -->
        <section id="datos" class="pp-section">
          <div class="pp-section__header">
            <span class="pp-section__number">03</span>
            <h2 class="pp-section__title">
              <i class="pi pi-database pp-section__title-icon"></i>
              Datos personales recabados
            </h2>
          </div>
          <div class="pp-section__content">
            <p>Para brindarte nuestros servicios, recopilamos los siguientes datos personales:</p>
            
            <div class="pp-grid-cards">
              <div class="pp-card-data">
                <div class="pp-card-data__icon">
                  <i class="pi pi-user pp-card-data__icon-icon"></i>
                </div>
                <div>
                  <h4>Identificación</h4>
                  <ul>
                    <li>Nombre completo</li>
                    <li>Nickname</li>
                    <li>Fecha de nacimiento</li>
                    <li>Género</li>
                  </ul>
                </div>
              </div>
              <div class="pp-card-data">
                <div class="pp-card-data__icon">
                  <i class="pi pi-envelope pp-card-data__icon-icon"></i>
                </div>
                <div>
                  <h4>Contacto</h4>
                  <ul>
                    <li>Correo electrónico</li>
                    <li>Número telefónico</li>
                    <li>Ciudad de residencia</li>
                  </ul>
                </div>
              </div>
              <div class="pp-card-data">
                <div class="pp-card-data__icon">
                  <i class="pi pi-lock pp-card-data__icon-icon"></i>
                </div>
                <div>
                  <h4>Seguridad</h4>
                  <ul>
                    <li>Contraseña (encriptada)</li>
                    <li>Historial de accesos</li>
                    <li>Dispositivos utilizados</li>
                  </ul>
                </div>
              </div>
              <div class="pp-card-data">
                <div class="pp-card-data__icon">
                  <i class="pi pi-image pp-card-data__icon-icon"></i>
                </div>
                <div>
                  <h4>Preferencias</h4>
                  <ul>
                    <li>Tipo de perfil</li>
                    <li>Preferencias de contenido</li>
                    <li>Intereses</li>
                  </ul>
                </div>
              </div>
            </div>

            <div class="pp-note">
              <i class="pi pi-info-circle pp-note__icon"></i>
              <p>
                <strong>Nota importante:</strong> No recopilamos datos sensibles como orientación sexual, 
                ideología política, creencias religiosas o estado de salud. La información que proporcionas 
                es voluntaria y con tu consentimiento expreso.
              </p>
            </div>
          </div>
        </section>

        <!-- FINALIDAD -->
        <section id="finalidad" class="pp-section">
          <div class="pp-section__header">
            <span class="pp-section__number">04</span>
            <h2 class="pp-section__title">
              <i class="pi pi-bullseye pp-section__title-icon"></i>
              Finalidad del tratamiento
            </h2>
          </div>
          <div class="pp-section__content">
            <p>Tus datos personales serán utilizados para las siguientes finalidades:</p>
            
            <div class="pp-list-finalidades">
              <div class="pp-finalidad">
                <span class="pp-finalidad__check">
                  <i class="pi pi-check-circle pp-finalidad__check-icon"></i>
                </span>
                <div>
                  <strong>Gestión de cuenta</strong>
                  <p>Creación, administración y mantenimiento de tu perfil en nuestra plataforma.</p>
                </div>
              </div>
              <div class="pp-finalidad">
                <span class="pp-finalidad__check">
                  <i class="pi pi-check-circle pp-finalidad__check-icon"></i>
                </span>
                <div>
                  <strong>Comunicación</strong>
                  <p>Envío de notificaciones, actualizaciones y comunicados relacionados con tu cuenta.</p>
                </div>
              </div>
              <div class="pp-finalidad">
                <span class="pp-finalidad__check">
                  <i class="pi pi-check-circle pp-finalidad__check-icon"></i>
                </span>
                <div>
                  <strong>Seguridad</strong>
                  <p>Verificación de identidad, prevención de fraudes y protección de la comunidad.</p>
                </div>
              </div>
              <div class="pp-finalidad">
                <span class="pp-finalidad__check">
                  <i class="pi pi-check-circle pp-finalidad__check-icon"></i>
                </span>
                <div>
                  <strong>Mejora del servicio</strong>
                  <p>Análisis de uso para optimizar nuestra plataforma y ofrecer una mejor experiencia.</p>
                </div>
              </div>
              <div class="pp-finalidad">
                <span class="pp-finalidad__check">
                  <i class="pi pi-check-circle pp-finalidad__check-icon"></i>
                </span>
                <div>
                  <strong>Cumplimiento legal</strong>
                  <p>Atender obligaciones fiscales, legales y regulatorias aplicables.</p>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- TRANSFERENCIA -->
        <section id="transferencia" class="pp-section">
          <div class="pp-section__header">
            <span class="pp-section__number">05</span>
            <h2 class="pp-section__title">
              <i class="pi pi-share-alt pp-section__title-icon"></i>
              Transferencia de datos
            </h2>
          </div>
          <div class="pp-section__content">
            <p>
              Club de Fantasías <strong>no vende, renta ni comparte</strong> tus datos personales con 
              terceros para fines de marketing o publicidad. La única excepción se da en los siguientes casos:
            </p>
            
            <ul class="pp-list-bullet">
              <li>
                <span class="pp-bullet">
                  <i class="pi pi-shield pp-bullet__icon"></i>
                </span>
                <div>
                  <strong>Proveedores de servicios</strong>
                  <p>Compartimos información con proveedores que nos ayudan a operar la plataforma 
                  (hosting, envío de correos, análisis de datos), siempre bajo estrictos acuerdos 
                  de confidencialidad.</p>
                </div>
              </li>
              <li>
                <span class="pp-bullet">
                  <i class="pi pi-gavel pp-bullet__icon"></i>
                </span>
                <div>
                  <strong>Autoridades competentes</strong>
                  <p>Cuando sea requerido por ley o para cumplir con procesos legales, podremos 
                  compartir información con autoridades judiciales o gubernamentales.</p>
                </div>
              </li>
              <li>
                <span class="pp-bullet">
                  <i class="pi pi-users pp-bullet__icon"></i>
                </span>
                <div>
                  <strong>Otros usuarios</strong>
                  <p>La información de tu perfil (nickname, tipo de perfil, ciudad) será visible 
                  para otros miembros de la comunidad, según la configuración de privacidad 
                  que elijas.</p>
                </div>
              </li>
            </ul>
          </div>
        </section>

        <!-- DERECHOS ARCO -->
        <section id="derechos" class="pp-section">
          <div class="pp-section__header">
            <span class="pp-section__number">06</span>
            <h2 class="pp-section__title">
              <i class="pi pi-shield pp-section__title-icon"></i>
              Derechos ARCO
            </h2>
          </div>
          <div class="pp-section__content">
            <p>
              Como titular de tus datos personales, tienes derechos de <strong>Acceso, Rectificación, 
              Cancelación y Oposición</strong> (ARCO). Puedes ejercerlos en cualquier momento:
            </p>

            <div class="pp-grid-arco">
              <div class="pp-arco-item">
                <div class="pp-arco-item__icon">
                  <i class="pi pi-eye pp-arco-item__icon-icon"></i>
                </div>
                <h4>Acceso</h4>
                <p>Conocer qué datos personales tenemos de ti.</p>
              </div>
              <div class="pp-arco-item">
                <div class="pp-arco-item__icon">
                  <i class="pi pi-pencil pp-arco-item__icon-icon"></i>
                </div>
                <h4>Rectificación</h4>
                <p>Solicitar la corrección de tus datos si son inexactos.</p>
              </div>
              <div class="pp-arco-item">
                <div class="pp-arco-item__icon">
                  <i class="pi pi-trash pp-arco-item__icon-icon"></i>
                </div>
                <h4>Cancelación</h4>
                <p>Pedir la eliminación de tus datos personales.</p>
              </div>
              <div class="pp-arco-item">
                <div class="pp-arco-item__icon">
                  <i class="pi pi-times pp-arco-item__icon-icon"></i>
                </div>
                <h4>Oposición</h4>
                <p>Negarte al uso de tus datos para fines específicos.</p>
              </div>
            </div>

            <div class="pp-procedure">
              <h4>Procedimiento para ejercer tus derechos</h4>
              <p>Para ejercer tus derechos ARCO, envía una solicitud a:</p>
              <div class="pp-procedure__contact">
                <span>
                  <i class="pi pi-envelope pp-procedure__contact-icon"></i>
                  <a href="mailto:arco@clubdefantasias.com">arco@clubdefantasias.com</a>
                </span>
              </div>
              <p class="pp-procedure__note">
                <i class="pi pi-info-circle pp-procedure__note-icon"></i>
                Responderemos tu solicitud en un plazo máximo de 20 días hábiles.
              </p>
            </div>
          </div>
        </section>

        <!-- COOKIES -->
        <section id="cookies" class="pp-section">
          <div class="pp-section__header">
            <span class="pp-section__number">07</span>
            <h2 class="pp-section__title">
              <i class="pi pi-cookie pp-section__title-icon"></i>
              Uso de cookies
            </h2>
          </div>
          <div class="pp-section__content">
            <p>
              Utilizamos cookies y tecnologías similares para mejorar tu experiencia en nuestra plataforma. 
              Las cookies nos permiten:
            </p>
            
            <ul class="pp-list-bullet pp-list-bullet--compact">
              <li>
                <span class="pp-bullet pp-bullet--small">
                  <i class="pi pi-check pp-bullet--small__icon"></i>
                </span>
                Mantener tu sesión iniciada
              </li>
              <li>
                <span class="pp-bullet pp-bullet--small">
                  <i class="pi pi-check pp-bullet--small__icon"></i>
                </span>
                Recordar tus preferencias y configuraciones
              </li>
              <li>
                <span class="pp-bullet pp-bullet--small">
                  <i class="pi pi-check pp-bullet--small__icon"></i>
                </span>
                Analizar el uso de la plataforma para mejorarla
              </li>
              <li>
                <span class="pp-bullet pp-bullet--small">
                  <i class="pi pi-check pp-bullet--small__icon"></i>
                </span>
                Mostrar contenido relevante según tus intereses
              </li>
            </ul>

            <div class="pp-note pp-note--cookies">
              <i class="pi pi-info-circle pp-note__icon"></i>
              <div>
                <strong>Gestiona tus cookies</strong>
                <p>Puedes configurar tu navegador para rechazar las cookies o eliminarlas. 
                Sin embargo, algunas funcionalidades de la plataforma podrían verse afectadas.</p>
                <a href="#" class="pp-link">
                  <i class="pi pi-cog pp-link__icon"></i>
                  Configurar cookies
                </a>
              </div>
            </div>
          </div>
        </section>

        <!-- CAMBIOS -->
        <section id="cambios" class="pp-section">
          <div class="pp-section__header">
            <span class="pp-section__number">08</span>
            <h2 class="pp-section__title">
              <i class="pi pi-refresh pp-section__title-icon"></i>
              Cambios al aviso de privacidad
            </h2>
          </div>
          <div class="pp-section__content">
            <p>
              Club de Fantasías se reserva el derecho de actualizar o modificar este Aviso de Privacidad 
              en cualquier momento. Cualquier cambio será publicado en esta página con la fecha de 
              actualización correspondiente.
            </p>
            <div class="pp-timeline">
              <div class="pp-timeline__item">
                <span class="pp-timeline__dot"></span>
                <div>
                  <strong>15 de marzo de 2026</strong>
                  <p>Versión 2.0 - Actualización de políticas de seguridad.</p>
                </div>
              </div>
              <div class="pp-timeline__item">
                <span class="pp-timeline__dot"></span>
                <div>
                  <strong>1 de enero de 2025</strong>
                  <p>Versión 1.0 - Publicación inicial del aviso de privacidad.</p>
                </div>
              </div>
            </div>
            <div class="pp-note pp-note--warning">
              <i class="pi pi-bell pp-note__icon"></i>
              <p>
                <strong>Te recomendamos revisar este aviso periódicamente</strong>
                para mantenerte informado sobre cómo protegemos tu información.
              </p>
            </div>
          </div>
        </section>

        <!-- CONTACTO -->
        <section id="contacto" class="pp-section pp-section--last">
          <div class="pp-section__header">
            <span class="pp-section__number">09</span>
            <h2 class="pp-section__title">
              <i class="pi pi-envelope pp-section__title-icon"></i>
              Contacto
            </h2>
          </div>
          <div class="pp-section__content">
            <p>
              Si tienes preguntas, comentarios o inquietudes sobre este Aviso de Privacidad, 
              no dudes en contactarnos:
            </p>
            
            <div class="pp-contact-cards">
              <div class="pp-contact-card">
                <div class="pp-contact-card__icon">
                  <i class="pi pi-envelope pp-contact-card__icon-icon"></i>
                </div>
                <div>
                  <strong>Correo electrónico</strong>
                  <p><a href="mailto:privacidad@clubdefantasias.com">privacidad@clubdefantasias.com</a></p>
                </div>
              </div>
              <div class="pp-contact-card">
                <div class="pp-contact-card__icon">
                  <i class="pi pi-phone pp-contact-card__icon-icon"></i>
                </div>
                <div>
                  <strong>Teléfono</strong>
                  <p>+52 (55) 1234-5678</p>
                </div>
              </div>
              <div class="pp-contact-card">
                <div class="pp-contact-card__icon">
                  <i class="pi pi-map-marker pp-contact-card__icon-icon"></i>
                </div>
                <div>
                  <strong>Domicilio</strong>
                  <p>Av. Principal #123, Col. Centro, CDMX</p>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Pie de página del contenido -->
        <div class="pp-main-footer">
          <div class="pp-main-footer__line"></div>
          <div class="pp-main-footer__content">
            <span>
              <i class="pi pi-shield pp-main-footer__icon"></i>
              Protegemos tu privacidad
            </span>
            <span>
              <i class="pi pi-lock pp-main-footer__icon"></i>
              Datos seguros
            </span>
            <span>
              <i class="pi pi-users pp-main-footer__icon"></i>
              Comunidad confiable
            </span>
          </div>
        </div>
      </main>
    </div>
  </UserLayout>
</template>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA (heredados del layout)
   ========================================================================= */
.pp-page {
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

.pp-accent {
  color: var(--brand);
}

/* =========================================================================
   ICONOS - ESTANDARIZADOS
   ========================================================================= */
.pp-hero__badge-icon,
.pp-hero__subtitle-icon,
.pp-hero__meta-icon,
.pp-sidebar__header-icon,
.pp-sidebar__link-icon,
.pp-sidebar__footer-icon,
.pp-section__title-icon,
.pp-highlight__icon,
.pp-card-info__icon,
.pp-card-data__icon-icon,
.pp-finalidad__check-icon,
.pp-bullet__icon,
.pp-bullet--small__icon,
.pp-arco-item__icon-icon,
.pp-procedure__contact-icon,
.pp-procedure__note-icon,
.pp-link__icon,
.pp-contact-card__icon-icon,
.pp-main-footer__icon,
.pp-note__icon {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  vertical-align: middle !important;
  flex-shrink: 0 !important;
  line-height: 1 !important;
}

/* Tamaños específicos */
.pp-hero__badge-icon { font-size: 14px !important; }
.pp-hero__subtitle-icon { font-size: 18px !important; }
.pp-hero__meta-icon { font-size: 14px !important; }
.pp-sidebar__header-icon { font-size: 18px !important; }
.pp-sidebar__link-icon { font-size: 14px !important; }
.pp-sidebar__footer-icon { font-size: 16px !important; }
.pp-section__title-icon { font-size: 20px !important; color: var(--brand); }
.pp-highlight__icon { font-size: 20px !important; }
.pp-card-info__icon { font-size: 18px !important; color: var(--brand); }
.pp-card-data__icon-icon { font-size: 20px !important; }
.pp-finalidad__check-icon { font-size: 18px !important; }
.pp-bullet__icon { font-size: 16px !important; }
.pp-bullet--small__icon { font-size: 14px !important; }
.pp-arco-item__icon-icon { font-size: 22px !important; }
.pp-procedure__contact-icon { font-size: 16px !important; }
.pp-procedure__note-icon { font-size: 14px !important; }
.pp-link__icon { font-size: 14px !important; }
.pp-contact-card__icon-icon { font-size: 20px !important; }
.pp-main-footer__icon { font-size: 14px !important; }
.pp-note__icon { font-size: 18px !important; }

/* =========================================================================
   SIDEBAR
   ========================================================================= */
.pp-sidebar__link--active {
  background: var(--brand-soft) !important;
  color: var(--brand) !important;
  font-weight: 600 !important;
}

.pp-sidebar__link--active .pp-sidebar__link-icon {
  color: var(--brand) !important;
}

/* =========================================================================
   HERO
   ========================================================================= */
.pp-hero {
  position: relative;
  background: linear-gradient(135deg, #FAF8F7 0%, #F5F0ED 100%);
  padding: 4rem 2.5rem 4rem;
  border-bottom: 1px solid var(--line);
  overflow: hidden;
}

.pp-hero__content {
  max-width: 1400px;
  margin: 0 auto;
  position: relative;
  z-index: 2;
}

.pp-hero__badge {
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

.pp-hero__badge-dot {
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

.pp-hero__title {
  font-family: var(--font-serif);
  font-size: 3.2rem;
  font-weight: 500;
  line-height: 1.2;
  margin: 0 0 0.5rem;
}

.pp-hero__subtitle {
  font-size: 1.05rem;
  color: var(--ink-soft);
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0 0 1.5rem;
}

.pp-hero__meta {
  display: flex;
  gap: 2rem;
  font-size: 0.78rem;
  color: var(--muted);
}

.pp-hero__meta span {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.pp-hero__decoration {
  position: absolute;
  right: 0;
  top: 0;
  bottom: 0;
  width: 40%;
  pointer-events: none;
  overflow: hidden;
}

.pp-hero__circle {
  position: absolute;
  border-radius: 50%;
  opacity: 0.05;
}

.pp-hero__circle--1 {
  width: 400px;
  height: 400px;
  background: var(--brand);
  top: -100px;
  right: -100px;
}

.pp-hero__circle--2 {
  width: 300px;
  height: 300px;
  background: var(--brand);
  bottom: -50px;
  right: 50px;
}

.pp-hero__circle--3 {
  width: 200px;
  height: 200px;
  background: var(--brand);
  top: 50%;
  right: -50px;
}

/* =========================================================================
   LAYOUT PRINCIPAL
   ========================================================================= */
.pp-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2.5rem;
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 2.5rem;
}

/* =========================================================================
   SIDEBAR
   ========================================================================= */
.pp-sidebar {
  position: relative;
}

.pp-sidebar__sticky {
  position: sticky;
  top: 100px;
}

.pp-sidebar__header {
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

.pp-sidebar__nav {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.pp-sidebar__link {
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

.pp-sidebar__link:hover {
  background: var(--brand-soft);
  color: var(--brand);
}

.pp-sidebar__footer {
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
   CONTENIDO PRINCIPAL
   ========================================================================= */
.pp-main {
  min-width: 0;
}

.pp-section {
  padding-bottom: 2.5rem;
  border-bottom: 1px solid var(--line);
  margin-bottom: 2.5rem;
}

.pp-section--last {
  border-bottom: none;
  margin-bottom: 0;
}

.pp-section__header {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

.pp-section__number {
  font-family: var(--font-serif);
  font-size: 1.8rem;
  font-weight: 500;
  color: var(--brand);
  opacity: 0.3;
  line-height: 1;
}

.pp-section__title {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-family: var(--font-serif);
  font-size: 1.6rem;
  font-weight: 500;
  margin: 0;
}

.pp-section__content p {
  line-height: 1.8;
  color: var(--ink-soft);
  margin: 0 0 1rem;
}

.pp-section__content p:last-child {
  margin-bottom: 0;
}

/* =========================================================================
   COMPONENTES
   ========================================================================= */
.pp-highlight {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: var(--brand-soft);
  border-radius: var(--radius-md);
  border-left: 4px solid var(--brand);
  margin-top: 1rem;
}

.pp-highlight p {
  margin: 0.25rem 0 0 !important;
  font-size: 0.88rem;
}

.pp-card-info {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-top: 0.5rem;
}

.pp-card-info__item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  background: var(--surface);
  border-radius: var(--radius-sm);
  border: 1px solid var(--line);
}

.pp-card-info__item strong {
  font-size: 0.75rem;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.03em;
  display: block;
}

.pp-card-info__item p {
  margin: 0.2rem 0 0;
  font-weight: 500;
}

.pp-card-info__item a {
  color: var(--brand);
  text-decoration: none;
}

.pp-card-info__item a:hover {
  text-decoration: underline;
}

.pp-grid-cards {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin: 1rem 0;
}

.pp-card-data {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1.25rem;
  background: var(--surface);
  border-radius: var(--radius-sm);
  border: 1px solid var(--line);
  transition: all 0.2s ease;
}

.pp-card-data:hover {
  border-color: var(--brand);
  background: var(--white);
  box-shadow: var(--shadow-card);
}

.pp-card-data__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  border-radius: var(--radius-sm);
  background: var(--brand-soft);
}

.pp-card-data h4 {
  margin: 0 0 0.3rem;
  font-size: 0.85rem;
  font-weight: 600;
}

.pp-card-data ul {
  margin: 0;
  padding: 0;
  list-style: none;
}

.pp-card-data ul li {
  font-size: 0.78rem;
  color: var(--ink-soft);
  padding: 0.1rem 0;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.pp-card-data ul li::before {
  content: '•';
  color: var(--brand);
  font-weight: bold;
}

.pp-note {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  background: #F0F7FF;
  border-radius: var(--radius-sm);
  border-left: 4px solid #3B82F6;
  margin-top: 1rem;
}

.pp-note p {
  margin: 0 !important;
  font-size: 0.85rem;
}

.pp-note--cookies {
  background: #FEFCF0;
  border-left-color: var(--warning);
}

.pp-note--warning {
  background: var(--warning-bg);
  border-left-color: var(--warning);
}

.pp-list-finalidades {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin: 1rem 0;
}

.pp-finalidad {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: var(--surface);
  border-radius: var(--radius-sm);
  border: 1px solid var(--line);
  transition: all 0.2s ease;
}

.pp-finalidad:hover {
  border-color: var(--brand);
  background: var(--white);
}

.pp-finalidad__check {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--brand-soft);
}

.pp-finalidad strong {
  font-size: 0.85rem;
}

.pp-finalidad p {
  margin: 0.1rem 0 0 !important;
  font-size: 0.78rem;
  color: var(--muted);
}

.pp-list-bullet {
  list-style: none;
  padding: 0;
  margin: 1rem 0;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.pp-list-bullet li {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.pp-list-bullet--compact li {
  gap: 0.5rem;
  padding: 0.25rem 0;
}

.pp-bullet {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--brand-soft);
}

.pp-bullet--small {
  width: 22px;
  height: 22px;
}

.pp-list-bullet strong {
  font-size: 0.85rem;
}

.pp-list-bullet p {
  margin: 0.1rem 0 0 !important;
  font-size: 0.82rem;
  color: var(--muted);
}

.pp-grid-arco {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin: 1.5rem 0;
}

.pp-arco-item {
  text-align: center;
  padding: 1.5rem 1rem;
  background: var(--surface);
  border-radius: var(--radius-sm);
  border: 1px solid var(--line);
  transition: all 0.2s ease;
}

.pp-arco-item:hover {
  border-color: var(--brand);
  background: var(--white);
  transform: translateY(-4px);
  box-shadow: var(--shadow-card);
}

.pp-arco-item__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  margin: 0 auto 0.75rem;
  border-radius: 50%;
  background: var(--brand-soft);
}

.pp-arco-item h4 {
  margin: 0 0 0.3rem;
  font-size: 0.85rem;
  font-weight: 600;
}

.pp-arco-item p {
  margin: 0;
  font-size: 0.78rem;
  color: var(--muted);
}

.pp-procedure {
  padding: 1.25rem 1.5rem;
  background: var(--surface);
  border-radius: var(--radius-md);
  border: 1px solid var(--line);
  margin-top: 1.5rem;
}

.pp-procedure h4 {
  margin: 0 0 0.5rem;
  font-size: 0.95rem;
}

.pp-procedure p {
  margin: 0 0 0.75rem !important;
}

.pp-procedure__contact {
  display: flex;
  gap: 1.5rem;
  padding: 0.75rem 0;
  border-top: 1px solid var(--line);
  border-bottom: 1px solid var(--line);
  margin: 0.75rem 0;
}

.pp-procedure__contact span {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
}

.pp-procedure__contact a {
  color: var(--brand);
  text-decoration: none;
  font-weight: 500;
}

.pp-procedure__contact a:hover {
  text-decoration: underline;
}

.pp-procedure__note {
  font-size: 0.78rem !important;
  color: var(--muted) !important;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.pp-timeline {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin: 1rem 0;
  padding-left: 1rem;
}

.pp-timeline__item {
  display: flex;
  gap: 1rem;
  padding-left: 1.5rem;
  position: relative;
}

.pp-timeline__item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 6px;
  bottom: -16px;
  width: 2px;
  background: var(--line);
}

.pp-timeline__item:last-child::before {
  display: none;
}

.pp-timeline__dot {
  position: absolute;
  left: -4px;
  top: 6px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: var(--brand);
  border: 2px solid var(--white);
  box-shadow: 0 0 0 2px var(--brand);
}

.pp-timeline__item strong {
  font-size: 0.85rem;
}

.pp-timeline__item p {
  margin: 0.1rem 0 0 !important;
  font-size: 0.82rem;
  color: var(--muted);
}

.pp-contact-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin: 1.5rem 0;
}

.pp-contact-card {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1.25rem;
  background: var(--surface);
  border-radius: var(--radius-sm);
  border: 1px solid var(--line);
  transition: all 0.2s ease;
}

.pp-contact-card:hover {
  border-color: var(--brand);
  background: var(--white);
  transform: translateY(-2px);
}

.pp-contact-card__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  border-radius: var(--radius-sm);
  background: var(--brand-soft);
}

.pp-contact-card strong {
  font-size: 0.72rem;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.03em;
  display: block;
}

.pp-contact-card p {
  margin: 0.2rem 0 0;
  font-weight: 500;
}

.pp-contact-card a {
  color: var(--brand);
  text-decoration: none;
}

.pp-contact-card a:hover {
  text-decoration: underline;
}

.pp-main-footer {
  padding-top: 2rem;
  margin-top: 2rem;
  border-top: 1px solid var(--line);
}

.pp-main-footer__line {
  height: 2px;
  width: 60px;
  background: var(--brand);
  margin-bottom: 1rem;
}

.pp-main-footer__content {
  display: flex;
  gap: 2rem;
  font-size: 0.78rem;
  color: var(--muted);
}

.pp-main-footer__content span {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.pp-link {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  color: var(--brand);
  text-decoration: none;
  font-weight: 500;
  font-size: 0.82rem;
}

.pp-link:hover {
  text-decoration: underline;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
  .pp-grid-cards {
    grid-template-columns: 1fr 1fr;
  }
  .pp-grid-arco {
    grid-template-columns: 1fr 1fr;
  }
  .pp-contact-cards {
    grid-template-columns: 1fr 1fr;
  }
  .pp-card-info {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 900px) {
  .pp-container {
    grid-template-columns: 1fr;
  }
  .pp-sidebar {
    display: none;
  }
  .pp-hero__title {
    font-size: 2.4rem;
  }
  .pp-hero__decoration {
    display: none;
  }
  .pp-grid-cards {
    grid-template-columns: 1fr;
  }
  .pp-grid-arco {
    grid-template-columns: 1fr 1fr;
  }
  .pp-contact-cards {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .pp-hero {
    padding: 2.5rem 1.25rem;
  }
  .pp-hero__title {
    font-size: 2rem;
  }
  .pp-hero__subtitle {
    font-size: 0.95rem;
    flex-wrap: wrap;
  }
  .pp-hero__meta {
    flex-direction: column;
    gap: 0.5rem;
  }
  .pp-grid-arco {
    grid-template-columns: 1fr;
  }
  .pp-procedure__contact {
    flex-direction: column;
    gap: 0.5rem;
  }
  .pp-section__title {
    font-size: 1.3rem;
  }
  .pp-section__number {
    font-size: 1.4rem;
  }
  .pp-container {
    padding: 1.25rem;
  }
}
</style>