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
            navegacion: ['Inicio', 'Descubrir', 'Eventos', 'Comunidad', 'Shop'],
            comunidad: ['Mi perfil', 'Mis conexiones', 'Mis favoritos', 'Mis visitas', 'Configuración'],
            soporte: ['Centro de ayuda', 'Preguntas frecuentes', 'Consejos de seguridad', 'Contacto'],
            legal: ['Términos y condiciones', 'Política de privacidad', 'Política de cookies', 'Aviso legal'],
        }),
    },
    mostrarSeguridad: { type: Boolean, default: true },
    anio: { type: Number, default: () => new Date().getFullYear() },
});
</script>

<template>
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
                <div class="app-footer__social">
                    <a href="#" aria-label="Instagram"><i class="pi pi-instagram"></i></a>
                    <a href="#" aria-label="Twitter"><i class="pi pi-twitter"></i></a>
                    <a href="#" aria-label="Facebook"><i class="pi pi-facebook"></i></a>
                    <a href="#" aria-label="TikTok"><i class="pi pi-tiktok"></i></a>
                    <a href="#" aria-label="YouTube"><i class="pi pi-youtube"></i></a>
                </div>
            </div>

            <div class="app-footer__col">
                <h4>NAVEGACIÓN</h4>
                <a v-for="l in columnas.navegacion" :key="l" href="#">{{ l }}</a>
            </div>
            <div class="app-footer__col">
                <h4>COMUNIDAD</h4>
                <a v-for="l in columnas.comunidad" :key="l" href="#">{{ l }}</a>
            </div>
            <div class="app-footer__col">
                <h4>SOPORTE</h4>
                <a v-for="l in columnas.soporte" :key="l" href="#">{{ l }}</a>
            </div>
            <div class="app-footer__col">
                <h4>LEGAL</h4>
                <a v-for="l in columnas.legal" :key="l" href="#">{{ l }}</a>
            </div>

            <div v-if="mostrarSeguridad" class="app-footer__security">
                <i class="pi pi-shield"></i>
                <div>
                    <strong>Tu seguridad es nuestra prioridad</strong>
                    <span>Plataforma verificada y monitoreada 24/7 para tu tranquilidad.</span>
                </div>
            </div>
        </div>

        <div class="app-footer__bottom">
            <span>© {{ anio }} Club de Fantasías. Todos los derechos reservados.</span>
            <span class="app-footer__age">+18 · Comunidad exclusiva para adultos</span>
        </div>
    </footer>
</template>

<style scoped>
:root {
    --brand-red: #c81e3a;
}

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
    margin: 0 0 1rem; 
    max-width: 280px; 
}

.app-footer__social { 
    display: flex; 
    gap: 0.9rem; 
    font-size: 1rem; 
    color: #6b6b70; 
}

.app-footer__social a { 
    color: inherit; 
    text-decoration: none;
    transition: color 0.2s ease, transform 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f5f5f5;
    transition: all 0.2s ease;
}

.app-footer__social a:hover { 
    color: var(--brand-red);
    background: #fde8ea;
    transform: translateY(-2px);
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
    color: var(--brand-red); 
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

.app-footer__security i { 
    color: var(--brand-red); 
    font-size: 1.1rem;
    margin-top: 0.1rem;
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
</style>