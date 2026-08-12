<script setup>
import { computed, reactive, ref } from 'vue';
import { Head } from '@inertiajs/vue3';

/* ---------------------------------------------------------------
 * Cabecera / usuario
 * --------------------------------------------------------------- */
const usuario = { nombre: 'Alexandra', notificaciones: 5 };

/* ---------------------------------------------------------------
 * Iconos de confianza (fila superior)
 * --------------------------------------------------------------- */
const confianza = [
    { icon: 'pi-lock', titulo: 'Acceso exclusivo', desc: 'Solo mayores de 18 años.' },
    { icon: 'pi-shield', titulo: 'Privacidad', desc: 'Protección total de tus datos.' },
    { icon: 'pi-heart', titulo: 'Respeto', desc: 'No se permite ningún tipo de acoso.' },
    { icon: 'pi-verified', titulo: 'Verificación', desc: 'Todos los perfiles deben ser auténticos.' },
    { icon: 'pi-shield', titulo: 'Seguridad', desc: 'Eventos y comunidad moderados.' },
    { icon: 'pi-eye-slash', titulo: 'Confidencialidad', desc: 'Toda interacción es privada.' },
];

/* ---------------------------------------------------------------
 * Aspectos principales (acordeón)
 * --------------------------------------------------------------- */
const aspectos = reactive([
    {
        icon: 'pi-user',
        titulo: '1. Elegibilidad para usar la plataforma',
        contenido:
            'Para utilizar Club de Fantasías debes ser mayor de 18 años. Al registrarte, confirmas que tienes la edad legal en tu país o región para acceder a contenido y servicios para adultos. La plataforma está diseñada exclusivamente para personas adultas interesadas en el estilo de vida swinger y experiencias consensuadas.',
        abierto: true,
    },
    {
        icon: 'pi-user-edit',
        titulo: '2. Registro y autenticidad del perfil',
        contenido:
            'Te comprometes a proporcionar información veraz al crear tu perfil y a mantenerla actualizada. Los perfiles falsos, duplicados o suplantados serán eliminados sin previo aviso.',
        abierto: false,
    },
    {
        icon: 'pi-id-card',
        titulo: '3. Verificación de identidad',
        contenido:
            'Podemos solicitarte documentación o una verificación fotográfica para confirmar tu identidad y edad antes de habilitar ciertas funciones de la plataforma.',
        abierto: false,
    },
    {
        icon: 'pi-verified',
        titulo: '4. Conducta permitida',
        contenido:
            'Se espera un trato respetuoso, consensuado y honesto con el resto de la comunidad, dentro y fuera de la plataforma, incluidos los eventos presenciales.',
        abierto: false,
    },
    {
        icon: 'pi-thumbs-down',
        titulo: '5. Conducta prohibida',
        contenido:
            'No se permite el acoso, la coacción, la difusión de contenido sin consentimiento, ni cualquier actividad que vulnere las leyes locales o los derechos de otros miembros.',
        abierto: false,
    },
    {
        icon: 'pi-exclamation-triangle',
        titulo: '6. Pagos y suscripciones',
        contenido:
            'Las membresías, eventos y funciones premium se rigen por las condiciones de cobro informadas al momento de la compra. Los reembolsos siguen la Política de reembolsos vigente.',
        abierto: false,
    },
    {
        icon: 'pi-images',
        titulo: '7. Contenido exclusivo',
        contenido:
            'El contenido que subas o al que accedas dentro de la plataforma es para uso personal y no puede ser redistribuido, descargado masivamente ni compartido fuera de Club de Fantasías.',
        abierto: false,
    },
    {
        icon: 'pi-copy',
        titulo: '8. Derechos de autor',
        contenido:
            'Conservas los derechos sobre el contenido que publicas, pero otorgas a Club de Fantasías una licencia limitada para mostrarlo dentro de la plataforma con fines de funcionamiento del servicio.',
        abierto: false,
    },
]);

const verTodos = ref(false);
const aspectosVisibles = computed(() => (verTodos.value ? aspectos : aspectos));

function toggleAspecto(item) {
    item.abierto = !item.abierto;
}

/* ---------------------------------------------------------------
 * Checklist de seguridad
 * --------------------------------------------------------------- */
const checklistSeguridad = [
    'Comunidad verificada',
    'Eventos privados',
    'Moderación permanente',
    'Protección de identidad',
    'Pagos seguros',
    'Soporte especializado',
];

/* ---------------------------------------------------------------
 * Aceptación
 * --------------------------------------------------------------- */
const aceptaTerminos = ref(false);
const aceptaPrivacidad = ref(false);
const puedeContinuar = computed(() => aceptaTerminos.value && aceptaPrivacidad.value);

function aceptarYContinuar() {
    if (!puedeContinuar.value) return;
    // TODO: router.post(route('terminos.aceptar'))
}

/* ---------------------------------------------------------------
 * Footer
 * --------------------------------------------------------------- */
const footerLinks = {
    navegacion: ['Inicio', 'Descubrir', 'Eventos', 'Comunidad', 'Shop'],
    comunidad: ['Mi perfil', 'Mis conexiones', 'Mis favoritos', 'Mis visitas', 'Configuración'],
    shop: ['Lencería', 'Juguetes', 'Aceites y lubricantes', 'Kits y accesorios', 'Ofertas'],
    ayuda: ['Preguntas frecuentes', 'Guías de uso', 'Consejos de seguridad', 'Recomendaciones', 'Blog'],
    soporte: ['Centro de ayuda', 'Contactar soporte', 'Reportar un problema', 'Estado del servicio', 'Soporte para eventos'],
    politicas: ['Términos y Condiciones', 'Aviso de Privacidad', 'Política de Cookies', 'Política de reembolsos', 'Normas de la comunidad'],
};
</script>

<template>
    <Head title="Términos y Condiciones" />

    <div class="terminos-page">
        <!-- ============================================================ -->
        <!-- HEADER -->
        <!-- ============================================================ -->
        <header class="topbar">
            <div class="topbar__logo">
                <span class="logo-line1">CLUB DE</span>
                <span class="logo-line2">FANTAS<span class="logo-x">Í</span>AS</span>
                <span class="logo-underline"></span>
            </div>

            <nav class="topbar__nav">
                <a href="#">Inicio</a>
                <a href="#">Descubrir</a>
                <a href="#">Eventos</a>
                <a href="#">Mensajes <PvBadge value="3" severity="danger" /></a>
                <a href="#">Comunidad</a>
                <a href="#">Shop</a>
            </nav>

            <div class="topbar__actions">
                <button class="icon-btn"><i class="pi pi-bell"></i><PvBadge :value="usuario.notificaciones" severity="danger" class="icon-badge" /></button>
                <div class="user-chip">
                    <PvAvatar image="/images/terminos/avatar-alexandra.jpg" shape="circle" size="large" />
                    <span class="user-chip__name">{{ usuario.nombre }} <i class="pi pi-verified"></i></span>
                    <i class="pi pi-chevron-down"></i>
                </div>
            </div>
        </header>

        <!-- ============================================================ -->
        <!-- HERO -->
        <!-- ============================================================ -->
        <section class="hero">
            <div class="hero__content">
                <h1>Términos y <span>Condiciones</span></h1>
                <p>
                    Queremos ofrecer una comunidad segura, exclusiva y basada en el respeto.
                    Antes de continuar, conoce las normas que protegen a todos nuestros miembros.
                </p>
                <div class="hero__actions">
                    <PvButton label="Leer documento completo" icon="pi pi-file" />
                    <PvButton label="Descargar PDF" icon="pi pi-download" outlined severity="secondary" />
                </div>
            </div>
            <div class="hero__image">
                <img src="/images/terminos/hero-pareja-lounge.jpg" alt="Pareja en Private Lounge" />
                <div class="hero__lounge-tag">
                    <i class="pi pi-sparkles"></i>
                    <span>PRIVATE<br />LOUNGE</span>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- FILA DE CONFIANZA -->
        <!-- ============================================================ -->
        <section class="trust-row">
            <div v-for="item in confianza" :key="item.titulo" class="trust-item">
                <span class="trust-item__icon"><i class="pi" :class="item.icon"></i></span>
                <div>
                    <strong>{{ item.titulo }}</strong>
                    <span>{{ item.desc }}</span>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- CONTENIDO PRINCIPAL -->
        <!-- ============================================================ -->
        <section class="content-grid">
            <!-- ----------------- Aspectos principales ----------------- -->
            <div class="aspectos-card">
                <h2>Aspectos principales</h2>

                <div class="aspectos-list">
                    <div v-for="item in aspectosVisibles" :key="item.titulo" class="aspecto-item">
                        <button class="aspecto-item__header" @click="toggleAspecto(item)">
                            <span class="aspecto-item__icon"><i class="pi" :class="item.icon"></i></span>
                            <strong>{{ item.titulo }}</strong>
                            <i class="pi toggle-icon" :class="item.abierto ? 'pi-minus' : 'pi-plus'"></i>
                        </button>
                        <transition name="collapse">
                            <p v-if="item.abierto" class="aspecto-item__body">{{ item.contenido }}</p>
                        </transition>
                    </div>
                </div>

                <button class="ver-todos-btn" @click="verTodos = !verTodos">
                    Ver todos los aspectos
                    <i class="pi" :class="verTodos ? 'pi-chevron-up' : 'pi-chevron-down'"></i>
                </button>
            </div>

            <!-- ----------------- Sidebar seguridad ----------------- -->
            <div class="sidebar-column">
                <div class="security-card">
                    <h2>Tu seguridad es nuestra prioridad</h2>
                    <ul class="security-checklist">
                        <li v-for="item in checklistSeguridad" :key="item">
                            <i class="pi pi-check"></i> {{ item }}
                        </li>
                    </ul>
                    <PvButton label="Contactar soporte" icon="pi pi-headphones" class="security-cta" />
                </div>

                <div class="notice-card">
                    <span class="notice-card__icon"><i class="pi pi-shield"></i></span>
                    <div>
                        <strong>Antes de aceptar</strong>
                        <p>Al utilizar Club de Fantasías aceptas respetar las normas de convivencia, privacidad y seguridad para mantener una comunidad confiable.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- ACEPTACIÓN -->
        <!-- ============================================================ -->
        <section class="acceptance-bar">
            <div class="acceptance-bar__checks">
                <label class="checkbox-line">
                    <input type="checkbox" v-model="aceptaTerminos" />
                    He leído y acepto los <a href="#">Términos y Condiciones</a>.
                </label>
                <label class="checkbox-line">
                    <input type="checkbox" v-model="aceptaPrivacidad" />
                    Acepto el <a href="#">Aviso de Privacidad</a>.
                </label>
            </div>

            <div class="acceptance-bar__actions">
                <PvButton
                    label="Aceptar y continuar"
                    :disabled="!puedeContinuar"
                    @click="aceptarYContinuar"
                />
                <PvButton label="Volver" outlined severity="secondary" />
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- FOOTER -->
        <!-- ============================================================ -->
        <footer class="footer">
            <div class="footer__top">
                <div class="footer__col">
                    <h4>Navegación</h4>
                    <a v-for="l in footerLinks.navegacion" :key="l" href="#">{{ l }}</a>
                </div>
                <div class="footer__col">
                    <h4>Comunidad</h4>
                    <a v-for="l in footerLinks.comunidad" :key="l" href="#">{{ l }}</a>
                </div>
                <div class="footer__col">
                    <h4>Shop</h4>
                    <a v-for="l in footerLinks.shop" :key="l" href="#">{{ l }}</a>
                </div>
                <div class="footer__col">
                    <h4>Ayuda</h4>
                    <a v-for="l in footerLinks.ayuda" :key="l" href="#">{{ l }}</a>
                </div>
                <div class="footer__col">
                    <h4>Soporte</h4>
                    <a v-for="l in footerLinks.soporte" :key="l" href="#">{{ l }}</a>
                </div>
                <div class="footer__col">
                    <h4>Políticas</h4>
                    <a
                        v-for="l in footerLinks.politicas"
                        :key="l"
                        href="#"
                        :class="{ active: l === 'Términos y Condiciones' }"
                    >{{ l }}</a>
                </div>
                <div class="footer__col">
                    <h4>Contacto</h4>
                    <span class="footer__text">contacto@clubdefantasias.com</span>
                    <span class="footer__text">+52 55 1234 5678</span>
                    <span class="footer__text">Ciudad de México, México</span>
                    <div class="footer__social">
                        <i class="pi pi-instagram"></i>
                        <i class="pi pi-twitter"></i>
                        <i class="pi pi-youtube"></i>
                        <i class="pi pi-tiktok"></i>
                    </div>
                </div>
            </div>

            <div class="footer__security">
                <span class="footer__security-icon"><i class="pi pi-shield"></i></span>
                <div>
                    <strong>Plataforma segura y confidencial</strong>
                    <p>Tus datos están protegidos con cifrado de nivel bancario.</p>
                    <a href="#">Conocer más</a>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
:root {
    --brand-red: #c81e3a;
    --brand-red-dark: #a3172f;
}

.terminos-page {
    font-family: 'Inter', system-ui, sans-serif;
    background: #f7f7f8;
    min-height: 100vh;
    color: #1f2024;
}

/* ---------------- HEADER ---------------- */
.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    border-bottom: 1px solid #ececec;
    padding: 0.85rem 2.5rem;
}
.topbar__logo { display: flex; flex-direction: column; align-items: center; position: relative; line-height: 1; }
.logo-line1 { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.15em; color: #1f2024; }
.logo-line2 { font-size: 1.35rem; font-weight: 800; letter-spacing: 0.06em; color: #1f2024; }
.logo-x { color: var(--brand-red); }
.logo-underline { width: 55%; height: 2px; background: var(--brand-red); margin-top: 4px; }

.topbar__nav { display: flex; gap: 2.25rem; font-size: 0.88rem; font-weight: 500; color: #3a3a3f; }
.topbar__nav a { text-decoration: none; color: inherit; display: flex; align-items: center; gap: 0.4rem; }

.topbar__actions { display: flex; align-items: center; gap: 1.25rem; }
.icon-btn { position: relative; border: none; background: transparent; font-size: 1.2rem; color: #444; cursor: pointer; }
.icon-badge { position: absolute; top: -4px; right: -6px; }
.user-chip { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; }
.user-chip__name { font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 0.3rem; }
.user-chip__name i { color: var(--brand-red); font-size: 0.85rem; }

/* ---------------- HERO ---------------- */
.hero {
    display: flex;
    align-items: center;
    max-width: 1536px;
    margin: 0 auto;
    padding: 2.5rem 2.5rem 2rem;
    gap: 2rem;
}
.hero__content { flex: 1; max-width: 560px; }
.hero h1 { font-size: 2.6rem; font-weight: 800; line-height: 1.05; margin: 0 0 1rem; }
.hero h1 span { color: var(--brand-red); }
.hero__content p { font-size: 1rem; color: #55555a; margin: 0 0 1.5rem; line-height: 1.5; }
.hero__actions { display: flex; gap: 0.9rem; flex-wrap: wrap; }
.hero__actions :deep(.p-button) { border-radius: 8px; font-weight: 700; padding: 0.85rem 1.5rem; }

.hero__image { position: relative; flex: 1; border-radius: 14px; overflow: hidden; aspect-ratio: 16/9; max-width: 720px; }
.hero__image img { width: 100%; height: 100%; object-fit: cover; }
.hero__lounge-tag {
    position: absolute; right: 1.25rem; bottom: 1.25rem;
    display: flex; align-items: center; gap: 0.5rem;
    color: #fff; text-align: right;
}
.hero__lounge-tag i { color: var(--brand-red); font-size: 1.2rem; }
.hero__lounge-tag span { font-size: 0.85rem; font-weight: 700; letter-spacing: 0.1em; line-height: 1.3; }

/* ---------------- TRUST ROW ---------------- */
.trust-row {
    max-width: 1536px;
    margin: 0 auto;
    padding: 0 2.5rem;
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 1rem;
}
@media (max-width: 1200px) { .trust-row { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 700px) { .trust-row { grid-template-columns: 1fr 1fr; } }
.trust-item {
    background: #fff; border: 1px solid #ececee; border-radius: 12px;
    padding: 1.1rem 1rem; display: flex; gap: 0.75rem; align-items: flex-start;
}
.trust-item__icon {
    width: 36px; height: 36px; border-radius: 10px; background: #fdf1f2; color: var(--brand-red);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1rem;
}
.trust-item strong { display: block; font-size: 0.88rem; margin-bottom: 0.15rem; }
.trust-item span { font-size: 0.76rem; color: #8a8a90; }

/* ---------------- CONTENT GRID ---------------- */
.content-grid {
    max-width: 1536px;
    margin: 1.75rem auto 0;
    padding: 0 2.5rem;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 380px;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 1100px) { .content-grid { grid-template-columns: 1fr; } }

.aspectos-card { background: #fff; border: 1px solid #ececee; border-radius: 14px; padding: 1.75rem; }
.aspectos-card h2 { font-size: 1.15rem; margin: 0 0 1.25rem; }

.aspectos-list { display: flex; flex-direction: column; gap: 0.5rem; }
.aspecto-item { border-bottom: 1px solid #f0f0f2; padding-bottom: 0.6rem; }
.aspecto-item:first-child { padding-bottom: 0.9rem; }
.aspecto-item__header {
    width: 100%; background: none; border: none; cursor: pointer;
    display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0; text-align: left;
}
.aspecto-item__icon { color: var(--brand-red); font-size: 1rem; flex-shrink: 0; width: 20px; }
.aspecto-item__header strong { flex: 1; font-size: 0.92rem; font-weight: 700; color: #1f2024; }
.toggle-icon { color: var(--brand-red); font-size: 0.85rem; }
.aspecto-item__body { font-size: 0.85rem; color: #55555a; line-height: 1.6; margin: 0.3rem 0 0.4rem 2rem; max-width: 90%; }

.ver-todos-btn {
    margin-top: 1rem; background: none; border: none; color: var(--brand-red);
    font-weight: 700; font-size: 0.88rem; cursor: pointer; display: flex; align-items: center; gap: 0.4rem;
}

.collapse-enter-active, .collapse-leave-active { transition: all 0.2s ease; }
.collapse-enter-from, .collapse-leave-to { opacity: 0; }

/* Sidebar */
.sidebar-column { display: flex; flex-direction: column; gap: 1.25rem; }
.security-card { background: #fff; border: 1px solid #ececee; border-radius: 14px; padding: 1.75rem; }
.security-card h2 { font-size: 1.1rem; margin: 0 0 1.1rem; }
.security-checklist { list-style: none; margin: 0 0 1.4rem; padding: 0; display: flex; flex-direction: column; gap: 0.7rem; }
.security-checklist li { display: flex; align-items: center; gap: 0.6rem; font-size: 0.88rem; color: #2a2a2e; }
.security-checklist i { color: var(--brand-red); font-weight: 700; }
.security-cta { width: 100%; font-weight: 700; border-radius: 8px; }

.notice-card {
    background: #fff; border: 1px solid #ececee; border-radius: 14px; padding: 1.5rem;
    display: flex; gap: 0.9rem; align-items: flex-start;
}
.notice-card__icon {
    width: 34px; height: 34px; border-radius: 10px; background: #fdf1f2; color: var(--brand-red);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.notice-card strong { display: block; font-size: 0.92rem; margin-bottom: 0.35rem; }
.notice-card p { font-size: 0.82rem; color: #8a8a90; margin: 0; line-height: 1.5; }

/* ---------------- ACCEPTANCE BAR ---------------- */
.acceptance-bar {
    max-width: 1536px;
    margin: 1.75rem auto 0;
    padding: 1.5rem 2.5rem;
    background: #fff;
    border-top: 1px solid #ececee;
    border-bottom: 1px solid #ececee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.25rem;
}
.acceptance-bar__checks { display: flex; flex-direction: column; gap: 0.6rem; }
.checkbox-line { display: flex; align-items: center; gap: 0.6rem; font-size: 0.88rem; color: #2a2a2e; }
.checkbox-line a { color: var(--brand-red); font-weight: 700; text-decoration: none; }
.acceptance-bar__actions { display: flex; gap: 0.9rem; }
.acceptance-bar__actions :deep(.p-button) { font-weight: 700; border-radius: 8px; padding: 0.8rem 1.75rem; }

/* ---------------- FOOTER ---------------- */
.footer { max-width: 1536px; margin: 0 auto; padding: 2.5rem 2.5rem 2rem; }
.footer__top {
    display: grid;
    grid-template-columns: repeat(6, 1fr) 1.3fr;
    gap: 1.5rem;
    padding-bottom: 2rem;
}
@media (max-width: 1100px) { .footer__top { grid-template-columns: repeat(3, 1fr); } }
.footer__col { display: flex; flex-direction: column; gap: 0.55rem; }
.footer__col h4 { font-size: 0.85rem; font-weight: 700; margin: 0 0 0.3rem; }
.footer__col a { font-size: 0.8rem; color: #6b6b70; text-decoration: none; }
.footer__col a.active { color: var(--brand-red); font-weight: 700; }
.footer__text { font-size: 0.8rem; color: #6b6b70; }
.footer__social { display: flex; gap: 0.9rem; margin-top: 0.5rem; font-size: 1rem; color: #3a3a3f; }

.footer__security {
    display: flex; gap: 0.9rem; align-items: flex-start;
    border-top: 1px solid #ececee; padding-top: 1.5rem;
}
.footer__security-icon {
    width: 40px; height: 40px; border-radius: 10px; background: #fdf1f2; color: var(--brand-red);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.1rem;
}
.footer__security strong { display: block; font-size: 0.9rem; margin-bottom: 0.2rem; }
.footer__security p { font-size: 0.8rem; color: #8a8a90; margin: 0 0 0.3rem; }
.footer__security a { font-size: 0.8rem; color: var(--brand-red); font-weight: 700; text-decoration: none; }
</style>
