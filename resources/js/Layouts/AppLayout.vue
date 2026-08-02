<script setup>
import { computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import AppFooter from '@/Components/AppFooter.vue';

/* ---------------------------------------------------------------
 * Props
 * -------------------------------------------------------------
 * activeNav: cuál ítem del menú resaltar ('inicio' | 'descubrir' | 'eventos' | 'mensajes' | 'comunidad')
 * --------------------------------------------------------------- */
const props = defineProps({
    activeNav: { type: String, default: '' },
});

// Obtener datos del usuario desde Inertia
const page = usePage();

// Computed para el usuario autenticado
const usuario = computed(() => {
    const user = page.props.auth?.user || null;
    
    if (user) {
        return {
            id: user.id,
            nombre: user.nombre || user.apodo || 'Usuario',
            apodo: user.apodo || user.nombre || 'Usuario',
            email: user.email || '',
            avatar: user.avatar || '/images/shared/avatar-default.jpg',
            verificado: user.estado === 'verificado' || user.email_verificado_en !== null || false,
            rol: user.rol || 'usuario',
        };
    }
    
    return {
        id: null,
        nombre: 'Invitado',
        apodo: 'Invitado',
        email: '',
        avatar: '/images/shared/avatar-default.jpg',
        verificado: false,
        rol: 'invitado',
    };
});

// Computed para notificaciones
const notificaciones = computed(() => 0);
const favoritos = computed(() => 0);
const mensajes = computed(() => 0);

// Items del menú de navegación
const navItems = computed(() => [
    { key: 'inicio', label: 'INICIO', href: '/inicio' },
    { key: 'descubrir', label: 'DESCUBRIR', href: '/descubrir' },
    { key: 'eventos', label: 'EVENTOS', href: '/eventos' },
    { key: 'mensajes', label: 'MENSAJES', href: '/mensajes', badge: mensajes.value },
    { key: 'comunidad', label: 'COMUNIDAD', href: '/comunidad' },
]);

// Función para cerrar sesión
function logout() {
    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        router.post('/logout');
    }
}
</script>

<template>
    <div class="app-layout">
        <!-- ============================================================ -->
        <!-- NAVBAR -->
        <!-- ============================================================ -->
        <header class="topbar">
            <Link href="/inicio" class="topbar__logo">
                <img 
                    src="/images/LOGO.png" 
                    alt="Club de Fantasías" 
                    class="topbar__logo-img"
                />
            </Link>

            <nav class="topbar__nav">
                <Link
                    v-for="item in navItems"
                    :key="item.key"
                    :href="item.href"
                    class="topbar__nav-link"
                    :class="{ active: activeNav === item.key }"
                >
                    {{ item.label }}
                    <PvBadge v-if="item.badge" :value="item.badge" severity="danger" />
                </Link>
            </nav>

            <div class="topbar__actions">
                <!-- Notificaciones -->
                <button class="icon-btn" title="Notificaciones">
                    <i class="pi pi-bell"></i>
                    <PvBadge v-if="notificaciones" :value="notificaciones" severity="danger" class="icon-badge" />
                </button>
                
                <!-- Favoritos -->
                <button class="icon-btn" title="Favoritos">
                    <i class="pi pi-heart"></i>
                    <PvBadge v-if="favoritos" :value="favoritos" severity="danger" class="icon-badge" />
                </button>

                <!-- Menú de usuario -->
                <div class="user-chip">
                    <PvAvatar 
                        :image="usuario.avatar" 
                        :label="usuario.nombre.charAt(0).toUpperCase()"
                        shape="circle" 
                        size="large"
                        :style="{ backgroundColor: usuario.verificado ? '#1fbf5c' : '#c81e3a' }"
                    />
                    <span v-if="usuario.verificado" class="user-chip__verified">
                        <i class="pi pi-check"></i>
                    </span>
                    <div class="user-chip__info">
                        <span class="name">{{ usuario.nombre }}</span>
                        <span v-if="usuario.verificado" class="sub">Perfil verificado</span>
                        <span v-else-if="usuario.rol === 'admin'" class="sub admin">Administrador</span>
                        <span v-else-if="usuario.rol === 'creador'" class="sub creator">Creador</span>
                        <span v-else-if="usuario.id" class="sub">Miembro</span>
                        <span v-else class="sub invitado">Invitado</span>
                    </div>
                    <i class="pi pi-chevron-down"></i>
                </div>

                <!-- Menú desplegable de usuario -->
                <div v-if="usuario.id" class="user-dropdown">
                    <div class="user-dropdown__header">
                        <PvAvatar 
                            :image="usuario.avatar" 
                            :label="usuario.nombre.charAt(0).toUpperCase()"
                            shape="circle" 
                            size="normal"
                        />
                        <div>
                            <div class="user-dropdown__name">{{ usuario.nombre }}</div>
                            <div class="user-dropdown__email">{{ usuario.email }}</div>
                        </div>
                    </div>
                    <div class="user-dropdown__divider"></div>
                    <Link href="/perfil" class="user-dropdown__item">
                        <i class="pi pi-user"></i> Mi perfil
                    </Link>
                    <Link href="/configuracion" class="user-dropdown__item">
                        <i class="pi pi-cog"></i> Configuración
                    </Link>
                    <Link href="/favoritos" class="user-dropdown__item">
                        <i class="pi pi-heart"></i> Favoritos
                    </Link>
                    <div class="user-dropdown__divider"></div>
                    <button @click="logout" class="user-dropdown__item user-dropdown__item--danger">
                        <i class="pi pi-sign-out"></i> Cerrar sesión
                    </button>
                </div>
            </div>
        </header>

        <!-- ============================================================ -->
        <!-- CONTENIDO DE LA PÁGINA -->
        <!-- ============================================================ -->
        <main class="app-layout__content">
            <slot />
        </main>

        <!-- ============================================================ -->
        <!-- FOOTER -->
        <!-- ============================================================ -->
        <AppFooter />
    </div>
</template>

<style scoped>
:root {
    --brand-red: #c81e3a;
    --brand-red-dark: #a3172f;
}

.app-layout {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background: #f7f7f8;
    min-height: 100vh;
    color: #1f2024;
}

/* ---------------- NAVBAR ---------------- */
.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
    border-bottom: 1px solid #ececec;
    padding: 0.75rem 2rem;
    position: sticky;
    top: 0;
    z-index: 50;
}

.topbar__logo {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: inherit;
    flex-shrink: 0;
}

.topbar__logo-img {
    height: 50px;
    width: auto;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.topbar__logo-img:hover {
    transform: scale(1.05);
}

.topbar__nav { 
    display: flex; 
    gap: 2rem; 
    font-size: 0.8rem; 
    font-weight: 600; 
    letter-spacing: 0.02em; 
    color: #6b6b70; 
}

.topbar__nav-link { 
    text-decoration: none; 
    color: inherit; 
    display: flex; 
    align-items: center; 
    gap: 0.35rem;
    transition: color 0.2s ease;
    padding: 0.4rem 0;
    position: relative;
}

.topbar__nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--brand-red);
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.topbar__nav-link:hover { 
    color: var(--brand-red); 
}

.topbar__nav-link:hover::after {
    width: 100%;
}

.topbar__nav-link.active { 
    color: var(--brand-red); 
}

.topbar__nav-link.active::after {
    width: 100%;
}

.topbar__actions { 
    display: flex; 
    align-items: center; 
    gap: 1rem; 
}

.icon-btn {
    position: relative;
    border: none;
    background: transparent;
    font-size: 1.1rem;
    color: #444;
    cursor: pointer;
    padding: 0.4rem;
    border-radius: 50%;
    transition: background 0.2s ease;
}

.icon-btn:hover {
    background: #f0f0f0;
}

.icon-badge { 
    position: absolute; 
    top: -4px; 
    right: -6px; 
}

/* ---------------- USER CHIP ---------------- */
.user-chip { 
    display: flex; 
    align-items: center; 
    gap: 0.5rem; 
    position: relative; 
    cursor: pointer;
    padding: 0.3rem 0.8rem 0.3rem 0.3rem;
    border-radius: 50px;
    transition: background 0.2s ease;
}

.user-chip:hover {
    background: #f0f0f0;
}

.user-chip__verified {
    position: absolute; 
    left: 28px; 
    bottom: -2px;
    background: #1fbf5c; 
    color: #ffffff; 
    border-radius: 50%;
    width: 16px; 
    height: 16px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 0.5rem;
    border: 2px solid #ffffff;
}

.user-chip__info { 
    display: flex; 
    flex-direction: column; 
    line-height: 1.1; 
}

.user-chip__info .name { 
    font-weight: 700; 
    font-size: 0.85rem; 
}

.user-chip__info .sub { 
    font-size: 0.65rem; 
    color: #1fbf5c; 
}

.user-chip__info .sub.admin { 
    color: #c81e3a; 
}

.user-chip__info .sub.creator { 
    color: #805ad5; 
}

.user-chip__info .sub.invitado { 
    color: #9a9a9a; 
}

/* ---------------- USER DROPDOWN ---------------- */
.user-dropdown {
    position: absolute;
    top: 60px;
    right: 0;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    min-width: 240px;
    padding: 0.5rem 0;
    display: none;
    z-index: 100;
}

.user-chip:hover .user-dropdown,
.user-dropdown:hover {
    display: block;
}

.user-dropdown__header {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.8rem 1rem;
}

.user-dropdown__name {
    font-weight: 600;
    font-size: 0.9rem;
}

.user-dropdown__email {
    font-size: 0.75rem;
    color: #6b6b70;
}

.user-dropdown__divider {
    height: 1px;
    background: #ececec;
    margin: 0.3rem 0;
}

.user-dropdown__item {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    padding: 0.6rem 1rem;
    color: #1f2024;
    text-decoration: none;
    font-size: 0.85rem;
    transition: background 0.2s ease;
    cursor: pointer;
    border: none;
    background: transparent;
    width: 100%;
    text-align: left;
}

.user-dropdown__item:hover {
    background: #f7f7f8;
}

.user-dropdown__item i {
    font-size: 1rem;
    color: #6b6b70;
}

.user-dropdown__item--danger {
    color: #c81e3a;
}

.user-dropdown__item--danger i {
    color: #c81e3a;
}

.user-dropdown__item--danger:hover {
    background: #fde8ea;
}

/* ---------------- CONTENT ---------------- */
.app-layout__content { 
    min-height: calc(100vh - 64px); 
}

/* ---------------- RESPONSIVE ---------------- */
@media (max-width: 1024px) {
    .topbar__nav {
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .topbar {
        padding: 0.75rem 1rem;
    }
    
    .topbar__nav {
        display: none;
    }
    
    .topbar__logo-img {
        height: 40px;
    }
    
    .user-chip__info {
        display: none;
    }
    
    .icon-btn {
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .topbar {
        padding: 0.5rem 0.75rem;
    }
    
    .topbar__logo-img {
        height: 35px;
    }
    
    .topbar__actions {
        gap: 0.5rem;
    }
    
    .icon-btn {
        font-size: 0.8rem;
        padding: 0.3rem;
    }
    
    .user-chip {
        padding: 0.2rem 0.5rem 0.2rem 0.2rem;
    }
    
    .user-chip__verified {
        left: 20px;
        width: 14px;
        height: 14px;
        font-size: 0.4rem;
    }
}
</style>