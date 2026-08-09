<script setup>
const currentYear = new Date().getFullYear();

const footerSections = {
  navegacion: ['Inicio', 'Quiénes somos', 'Servicios', 'Eventos', 'Contacto'],
  comunidad: ['Mi perfil', 'Mis conexiones', 'Mis favoritos', 'Mis visitas', 'Configuración'],
  soporte: ['Centro de ayuda', 'Preguntas frecuentes', 'Consejos de seguridad', 'Contacto'],
  legal: ['Términos y condiciones', 'Política de privacidad', 'Política de cookies', 'Aviso legal'],
};
</script>

<template>
  <footer class="user-footer">
    <div class="user-footer__top">
      <div class="user-footer__brand">
        <div class="user-footer__logo">
          <img 
            src="/images/LOGO.png" 
            alt="Club de Fantasías" 
            class="user-footer__logo-img"
          />
        </div>
        <p class="user-footer__brand-desc">
          Una comunidad exclusiva para conectar con personas auténticas,
          de forma segura, privada y respetuosa.
        </p>
      </div>

      <div 
        v-for="(items, section) in footerSections" 
        :key="section" 
        class="user-footer__col"
      >
        <h4>{{ section.toUpperCase() }}</h4>
        <a 
          v-for="item in items" 
          :key="item" 
          href="#"
          @click.prevent="item === 'Inicio' ? $emit('scroll-to', $event, 'inicio') : null"
        >
          {{ item }}
        </a>
      </div>

      <div class="user-footer__security">
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

    <div class="user-footer__bottom">
      <span>© {{ currentYear }} Club de Fantasías. Todos los derechos reservados.</span>
      <span class="user-footer__age">+18 · Comunidad exclusiva para adultos</span>
    </div>
  </footer>
</template>

<style scoped>
.user-footer {
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  max-width: 1400px;
  margin: 2.5rem auto 0;
  padding: 2.5rem 2rem 1.5rem;
  color: #1f2024;
}

.user-footer__top {
  display: grid;
  grid-template-columns: 1.4fr 0.8fr 0.8fr 0.8fr 0.8fr 1.2fr;
  gap: 1.75rem;
  border-bottom: 1px solid #e6e6e8;
  padding-bottom: 2rem;
}

.user-footer__brand {
  grid-column: span 1;
}

.user-footer__logo {
  display: flex;
  align-items: center;
  margin-bottom: 0.9rem;
}

.user-footer__logo-img {
  height: 45px;
  width: auto;
  object-fit: contain;
}

.user-footer__brand-desc {
  font-size: 0.8rem;
  color: #8a8a90;
  line-height: 1.6;
  margin: 0;
  max-width: 280px;
}

.user-footer__col {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.user-footer__col h4 {
  font-size: 0.72rem;
  color: #a5a5aa;
  letter-spacing: 0.05em;
  margin: 0 0 0.3rem;
  text-transform: uppercase;
}

.user-footer__col a {
  font-size: 0.82rem;
  color: #55555a;
  text-decoration: none;
  transition: color 0.2s ease;
}

.user-footer__col a:hover {
  color: var(--brand, #C81E3A);
}

.user-footer__security {
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

.user-footer__security strong {
  display: block;
  font-size: 0.82rem;
  margin-bottom: 0.2rem;
}

.user-footer__security span {
  color: #8a8a90;
}

.user-footer__bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
  padding-top: 1.25rem;
  font-size: 0.78rem;
  color: #a5a5aa;
}

.user-footer__age {
  font-weight: 600;
  color: #8a8a90;
}

@media (max-width: 1024px) {
  .user-footer__top {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 768px) {
  .user-footer {
    padding: 2rem 1rem 1.5rem;
  }
  
  .user-footer__bottom {
    flex-direction: column;
    text-align: center;
    gap: 0.75rem;
  }
}

@media (max-width: 600px) {
  .user-footer__top {
    grid-template-columns: 1fr;
  }
}
</style>