<script setup>
/* ---------------------------------------------------------------
 * Props
 * -------------------------------------------------------------
 * columnas: permite sobreescribir los enlaces por página si hace falta,
 * pero trae valores por defecto para que el footer luzca igual en todo el sitio.
 * --------------------------------------------------------------- */
const props = defineProps({
    columnas: {
        type: Object,
        default: () => ({
            navegacion: [
                { label: 'Inicio', route: 'inicio' },
                { label: 'Descubrir', route: 'descubrir' },
                { label: 'Eventos', route: 'eventos.index' },
                { label: 'Shop', route: 'tienda' },
                { label: 'Mensajes', route: 'mensajes' },
                { label: 'Comunidad', route: 'comunidad.index' }
            ],
            comunidad: [
                { label: 'Mi perfil', route: 'perfil.ver' }, // ✅ RUTA CORREGIDA
                { label: 'Configuración', route: 'configuracion' },
                { label: 'Favoritos', route: 'favoritos' }
            ]
        }),
    },
    anio: { type: Number, default: () => new Date().getFullYear() },
});
</script>

<template>
    <footer class="app-footer">
        <div class="app-footer__container">
            <!-- SECCIÓN PRINCIPAL -->
            <div class="app-footer__main">
                <!-- LOGO Y DESCRIPCIÓN -->
                <div class="app-footer__brand">
                    <img 
                        src="/images/LOGO.png" 
                        alt="Club de Fantasías" 
                        class="app-footer__logo"
                    />
                    <p class="app-footer__brand-desc">
                        Comunidad exclusiva para adultos
                    </p>
                    <div class="app-footer__badge">
                        <i class="pi pi-shield"></i>
                        <span>Plataforma segura</span>
                    </div>
                </div>

                <!-- EXPLORAR - EN 2 COLUMNAS -->
                <div class="app-footer__col app-footer__col--explore">
                    <h4 class="app-footer__col-title">Explorar</h4>
                    <div class="app-footer__links-grid">
                        <a 
                            v-for="item in columnas.navegacion" 
                            :key="item.route" 
                            :href="route(item.route)"
                            class="app-footer__link"
                        >
                            <i class="pi pi-chevron-right"></i>
                            {{ item.label }}
                        </a>
                    </div>
                </div>

                <!-- COMUNIDAD -->
                <div class="app-footer__col">
                    <h4 class="app-footer__col-title">Tu cuenta</h4>
                    <div class="app-footer__links">
                        <a 
                            v-for="item in columnas.comunidad" 
                            :key="item.route" 
                            :href="route(item.route)"
                            class="app-footer__link"
                        >
                            <i class="pi pi-chevron-right"></i>
                            {{ item.label }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN INFERIOR -->
            <div class="app-footer__bottom">
                <div class="app-footer__bottom-left">
                    <span>© {{ anio }} Club de Fantasías</span>
                    <span class="app-footer__dot">•</span>
                    <span>Todos los derechos reservados</span>
                </div>
                <div class="app-footer__bottom-right">
                    <span class="app-footer__legal">
                        <a :href="route('terms')">Términos</a>
                        <span class="app-footer__dot">•</span>
                        <a :href="route('privacy')">Privacidad</a>
                        <span class="app-footer__dot">•</span>
                        <a :href="route('cookies')">Cookies</a>
                    </span>
                    <span class="app-footer__age">
                        <i class="pi pi-lock"></i>
                        +18
                    </span>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped>
/* =========================================================================
   TOKENS
   ========================================================================= */
.app-footer {
  --brand: #C81E3A;
  --brand-dark: #A6152D;
  --brand-light: #E85A72;
  --brand-gradient: linear-gradient(135deg, #C81E3A 0%, #E85A72 100%);
  --ink: #171412;
  --ink-soft: #4B4744;
  --muted: #8A8481;
  --muted-light: #B7B2AF;
  --line: #ECE9E7;
  --surface: #FAF8F7;
  --white: #FFFFFF;
  --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.04);
  --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.06);
  --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;
  --radius-sm: 10px;
  --radius-md: 16px;
  --radius-full: 999px;
}

.app-footer {
  font-family: var(--font-sans);
  background: var(--surface);
  border-top: 2px solid var(--line);
  margin-top: 3rem;
  padding: 2rem 2rem 1.2rem;
  color: var(--ink-soft);
}

.app-footer__container {
  max-width: 1240px;
  margin: 0 auto;
}

/* =========================================================================
   MAIN SECTION
   ========================================================================= */
.app-footer__main {
  display: grid;
  grid-template-columns: 1.4fr 2fr 1fr;
  gap: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--line);
}

@media (max-width: 1024px) {
  .app-footer__main {
    grid-template-columns: 1.2fr 1.8fr 1fr;
    gap: 1.5rem;
  }
}

@media (max-width: 768px) {
  .app-footer__main {
    grid-template-columns: 1fr 1fr;
    gap: 1.2rem;
  }
}

@media (max-width: 600px) {
  .app-footer__main {
    grid-template-columns: 1fr;
    gap: 1rem;
    text-align: center;
  }
}

/* =========================================================================
   BRAND
   ========================================================================= */
.app-footer__brand {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  align-items: flex-start;
}

.app-footer__logo {
  height: 38px;
  width: auto;
  object-fit: contain;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  filter: brightness(0.98);
}

.app-footer__logo:hover {
  transform: scale(1.05) rotate(-1deg);
}

.app-footer__brand-desc {
  font-size: 0.75rem;
  color: var(--muted);
  margin: 0;
  line-height: 1.5;
  max-width: 260px;
  font-weight: 400;
}

.app-footer__badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: var(--white);
  padding: 0.25rem 0.8rem;
  border-radius: var(--radius-full);
  font-size: 0.6rem;
  font-weight: 600;
  color: var(--brand);
  border: 1px solid var(--line);
  box-shadow: var(--shadow-sm);
  transition: all 0.3s ease;
  margin-top: 0.2rem;
}

.app-footer__badge:hover {
  border-color: var(--brand);
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}

.app-footer__badge i {
  font-size: 0.7rem;
}

/* =========================================================================
   COLUMNAS
   ========================================================================= */
.app-footer__col {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

/* EXPLORAR - 2 COLUMNAS */
.app-footer__col--explore {
  grid-column: span 1;
}

.app-footer__links-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.3rem 1rem;
}

.app-footer__links {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.app-footer__col-title {
  font-size: 0.6rem;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--muted-light);
  margin: 0;
  font-weight: 700;
  position: relative;
  padding-bottom: 0.3rem;
}

.app-footer__col-title::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 24px;
  height: 2px;
  background: var(--brand-gradient);
  border-radius: 2px;
}

.app-footer__link {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.78rem;
  color: var(--muted);
  text-decoration: none;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  padding: 0.2rem 0;
  font-weight: 450;
}

.app-footer__link i {
  font-size: 0.5rem;
  color: var(--muted-light);
  transition: all 0.25s ease;
}

.app-footer__link:hover {
  color: var(--brand);
  transform: translateX(4px);
}

.app-footer__link:hover i {
  color: var(--brand);
  transform: translateX(2px) scale(1.2);
}

/* =========================================================================
   BOTTOM SECTION
   ========================================================================= */
.app-footer__bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.8rem;
  padding-top: 1rem;
  font-size: 0.65rem;
  color: var(--muted-light);
}

.app-footer__bottom-left {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.app-footer__bottom-right {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.app-footer__legal {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.app-footer__legal a {
  color: var(--muted);
  text-decoration: none;
  transition: color 0.2s ease;
}

.app-footer__legal a:hover {
  color: var(--brand);
}

.app-footer__dot {
  color: var(--muted-light);
  font-weight: 300;
}

.app-footer__age {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-weight: 700;
  color: var(--brand);
  background: rgba(200, 30, 58, 0.06);
  padding: 0.15rem 0.8rem;
  border-radius: var(--radius-full);
  font-size: 0.6rem;
  border: 1px solid rgba(200, 30, 58, 0.1);
  transition: all 0.3s ease;
}

.app-footer__age:hover {
  background: rgba(200, 30, 58, 0.1);
  transform: translateY(-1px);
}

.app-footer__age i {
  font-size: 0.5rem;
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 768px) {
  .app-footer {
    padding: 1.5rem 1rem 1rem;
    margin-top: 2rem;
  }

  .app-footer__main {
    padding-bottom: 1rem;
  }

  .app-footer__brand {
    align-items: flex-start;
  }

  .app-footer__brand-desc {
    max-width: 100%;
  }

  .app-footer__col-title::after {
    left: 0;
  }

  .app-footer__bottom {
    flex-direction: column;
    text-align: center;
    gap: 0.5rem;
  }

  .app-footer__bottom-left {
    justify-content: center;
    flex-direction: column;
    gap: 0.2rem;
  }

  .app-footer__bottom-right {
    justify-content: center;
  }

  .app-footer__dot {
    display: none;
  }

  .app-footer__legal {
    gap: 0.3rem;
  }

  .app-footer__legal .app-footer__dot {
    display: inline;
  }
}

@media (max-width: 600px) {
  .app-footer__brand {
    align-items: center;
  }

  .app-footer__brand-desc {
    text-align: center;
  }

  .app-footer__col {
    align-items: center;
  }

  .app-footer__col-title::after {
    left: 50%;
    transform: translateX(-50%);
  }

  .app-footer__links-grid {
    grid-template-columns: 1fr 1fr;
    gap: 0.2rem 0.8rem;
  }

  .app-footer__links {
    align-items: center;
  }

  .app-footer__link {
    text-align: center;
  }

  .app-footer__link i {
    display: none;
  }

  .app-footer__bottom {
    gap: 0.4rem;
  }

  .app-footer__bottom-right {
    flex-direction: column;
    gap: 0.3rem;
  }

  .app-footer__legal {
    flex-wrap: wrap;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .app-footer {
    padding: 1rem 0.6rem 0.8rem;
  }

  .app-footer__logo {
    height: 30px;
  }

  .app-footer__link {
    font-size: 0.72rem;
  }

  .app-footer__links-grid {
    grid-template-columns: 1fr;
    gap: 0.1rem;
  }

  .app-footer__legal {
    gap: 0.2rem;
    font-size: 0.6rem;
  }
}
</style>