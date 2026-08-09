<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import AppFooter from '@/Components/AppFooter.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import CustomAvatar from '@/Components/AvatarCustom.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import { useConfirm } from '@/composables/useConfirm';

/* ---------------------------------------------------------------
 * Props
 * -------------------------------------------------------------
 * activeNav: cuál ítem del menú resaltar ('inicio' | 'descubrir' | 'eventos' | 'shop' | 'mensajes' | 'comunidad')
 * --------------------------------------------------------------- */
const props = defineProps({
    activeNav: { type: String, default: '' },
});

// Estado del dropdown
const isDropdownOpen = ref(false);

// Estado de carga para logout
const isLoggingOut = ref(false);

// Obtener datos del usuario desde Inertia
const page = usePage();

// Confirm modal
const { confirm } = useConfirm();

// 🔥 USUARIO - CORREGIDO PARA AGREGAR /storage/ AL AVATAR
const usuario = computed(() => {
    // Obtener usuario de page.props.usuario
    const user = page.props.usuario || null;
    
    if (user) {
        // 🔥 CORREGIDO: Asegurar que el avatar tenga la ruta completa
        let avatar = user.avatar || '/images/shared/avatar-default.jpg';
        
        // Si el avatar no es la imagen por defecto y no tiene /storage/ ni http, agregarlo
        if (avatar !== '/images/shared/avatar-default.jpg' && 
            !avatar.startsWith('http://') && 
            !avatar.startsWith('https://') && 
            !avatar.startsWith('/')) {
            avatar = '/storage/' + avatar;
        }
        
        return {
            id: user.id,
            nombre: user.nombre || user.apodo || 'Usuario',
            apodo: user.apodo || user.nombre || 'Usuario',
            email: user.email || '',
            avatar: avatar, // 🔥 AHORA CON LA RUTA COMPLETA
            verificado: user.verificado || false,
            rol: user.rol || 'usuario',
            foto_principal: user.foto_principal || null,
            perfil: user.perfil || null,
            tiene_perfil: user.tiene_perfil || false,
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
        foto_principal: null,
        perfil: null,
        tiene_perfil: false,
    };
});

// Computed para notificaciones
const notificaciones = computed(() => 0);
const mensajes = computed(() => 0);

// Items del menú de navegación
const navItems = computed(() => [
    { key: 'inicio', label: 'INICIO', href: '/inicio', icon: 'pi pi-home' },
    { key: 'descubrir', label: 'DESCUBRIR', href: '/descubrir', icon: 'pi pi-compass' },
    { key: 'eventos', label: 'EVENTOS', href: '/eventos', icon: 'pi pi-calendar' },
    { key: 'shop', label: 'SHOP', href: '/tienda', icon: 'pi pi-shopping-bag' },
    { key: 'mensajes', label: 'MENSAJES', href: '/mensajes', icon: 'pi pi-envelope', badge: mensajes.value },
    { key: 'comunidad', label: 'COMUNIDAD', href: '/comunidad', icon: 'pi pi-users' },
]);

// Toggle dropdown
const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

const closeDropdown = () => {
    isDropdownOpen.value = false;
};

// Función para cerrar sesión con confirmación
const handleLogout = async () => {
    if (isLoggingOut.value) return;
    
    const confirmed = await confirm(
        '¿Estás seguro de que deseas cerrar sesión?',
        {
            title: 'Cerrar sesión',
            confirmLabel: 'Sí, cerrar sesión',
            cancelLabel: 'Cancelar',
            danger: true,
        }
    );
    
    if (confirmed) {
        isLoggingOut.value = true;
        
        router.post('/logout', {}, {
            onFinish: () => {
                isLoggingOut.value = false;
                closeDropdown();
            },
            onError: () => {
                isLoggingOut.value = false;
                console.error('Error al cerrar sesión');
            }
        });
    }
};

// Cerrar dropdown al hacer clic fuera
const handleClickOutside = (event) => {
    const dropdown = document.querySelector('.user-dropdown-wrapper');
    if (dropdown && !dropdown.contains(event.target)) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="app-layout">
        <!-- ✅ Toast Notification para toda la aplicación -->
        <ToastNotification :duration="5000" />

        <header class="cf-header">
            <nav class="cf-nav">
                <!-- LOGO -->
                <Link href="/inicio" class="cf-brand">
                    <img 
                        src="/images/LOGO.png" 
                        alt="Club de Fantasías" 
                        class="cf-brand__logo"
                    />
                </Link>

                <!-- ENLACES DEL NAVBAR -->
                <div class="cf-nav__links">
                    <Link
                        v-for="item in navItems"
                        :key="item.key"
                        :href="item.href"
                        class="cf-nav__link"
                        :class="{ 'cf-nav__link--active': activeNav === item.key }"
                    >
                        <span class="cf-nav__link-label">
                            <i :class="item.icon" class="nav-icon"></i>
                            {{ item.label }}
                        </span>
                        <PvBadge v-if="item.badge" :value="item.badge" severity="danger" class="nav-badge" />
                    </Link>
                </div>

                <!-- ACCIONES -->
                <div class="cf-nav__actions">
                    <button class="icon-btn" title="Notificaciones">
                        <i class="pi pi-bell"></i>
                        <span v-if="notificaciones" class="icon-badge">{{ notificaciones }}</span>
                    </button>

                    <div class="user-dropdown-wrapper" @click.stop>
                        <div class="user-chip" @click="toggleDropdown">
                            <CustomAvatar 
                                :image="usuario.avatar" 
                                :label="usuario.nombre.charAt(0).toUpperCase()"
                                size="large"
                            />
                            <span v-if="usuario.verificado" class="user-chip__verified">
                                <i class="pi pi-check"></i>
                            </span>
                            <div class="user-chip__info">
                                <span class="name">{{ usuario.nombre }}</span>
                                <span v-if="usuario.verificado" class="sub verified">✓ Verificado</span>
                                <span v-else-if="usuario.rol === 'admin'" class="sub admin">Administrador</span>
                                <span v-else-if="usuario.rol === 'creador'" class="sub creator">✦ Creador</span>
                                <span v-else-if="usuario.id" class="sub">Miembro</span>
                                <span v-else class="sub invitado">Invitado</span>
                            </div>
                            <i class="pi pi-chevron-down" :class="{ rotated: isDropdownOpen }"></i>
                        </div>

                        <Transition name="dropdown">
                            <div v-if="isDropdownOpen && usuario.id" class="user-dropdown">
                                <div class="user-dropdown__header">
                                    <CustomAvatar 
                                        :image="usuario.avatar" 
                                        :label="usuario.nombre.charAt(0).toUpperCase()"
                                        size="xlarge"
                                    />
                                    <div>
                                        <div class="user-dropdown__name">{{ usuario.nombre }}</div>
                                        <div class="user-dropdown__email">{{ usuario.email }}</div>
                                        <span v-if="usuario.verificado" class="user-dropdown__verified">
                                            <i class="pi pi-check-circle"></i> Verificado
                                        </span>
                                    </div>
                                </div>
                                <div class="user-dropdown__divider"></div>
                                <Link :href="route('perfil.ver')" class="user-dropdown__item" @click="closeDropdown">
                                    <i class="pi pi-user"></i> Mi perfil
                                </Link>
                                <Link :href="route('profile.usuario')" class="user-dropdown__item" @click="closeDropdown">
                                    <i class="pi pi-cog"></i> Configuración
                                </Link>
                                <div class="user-dropdown__divider"></div>
                                <button 
                                    @click="handleLogout" 
                                    class="user-dropdown__item user-dropdown__item--danger"
                                    :disabled="isLoggingOut"
                                >
                                    <i class="pi pi-sign-out"></i> 
                                    {{ isLoggingOut ? 'Cerrando sesión...' : 'Cerrar sesión' }}
                                </button>
                            </div>
                        </Transition>
                    </div>
                </div>
            </nav>
        </header>

        <main class="app-layout__content">
            <slot />
        </main>

        <AppFooter />
        <ConfirmModal />
    </div>
</template>

<style scoped>
/* =========================================================================
   TOKENS DE MARCA
   ========================================================================= */
.app-layout {
  --brand: #C81E3A;
  --brand-dark: #A6152D;
  --brand-light: #E85A72;
  --brand-gradient: linear-gradient(135deg, #C81E3A 0%, #E85A72 100%);
  --gold: #D4AF37;
  --ink: #171412;
  --ink-soft: #4B4744;
  --muted: #8A8481;
  --muted-light: #B7B2AF;
  --line: #ECE9E7;
  --surface: #FAF8F7;
  --white: #FFFFFF;
  --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.04);
  --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.08);
  --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.12);
  --shadow-xl: 0 20px 60px rgba(0, 0, 0, 0.15);

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
  -moz-osx-font-smoothing: grayscale;
}

.app-layout * {
  box-sizing: border-box;
}

/* =========================================================================
   NAVBAR - LOGO MÁS A LA IZQUIERDA
   ========================================================================= */
.cf-header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--line);
  transition: box-shadow 0.3s ease, background 0.3s ease;
}

.cf-header.scrolled {
  box-shadow: var(--shadow-md);
  background: rgba(255, 255, 255, 0.98);
}

.cf-nav {
  max-width: var(--container);
  margin: 0 auto;
  height: 76px;
  padding: 0 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* =========================================================================
   LOGO
   ========================================================================= */
.cf-brand {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: var(--ink);
  flex-shrink: 0;
  margin-right: 3rem;
}

.cf-brand__logo {
  height: 48px;
  width: auto;
  object-fit: contain;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.cf-brand__logo:hover {
  transform: scale(1.05);
}

/* =========================================================================
   NAVIGATION LINKS
   ========================================================================= */
.cf-nav__links {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 2.8rem;
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--ink-soft);
  flex: 1;
  padding: 0 1.5rem;
}

.cf-nav__link {
  text-decoration: none;
  color: inherit;
  padding: 0.4rem 0;
  position: relative;
  transition: color 0.3s ease;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  letter-spacing: 0.02em;
}

.cf-nav__link-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.nav-icon {
  font-size: 1rem;
  color: var(--muted-light);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.cf-nav__link:hover .nav-icon {
  color: var(--brand);
  transform: translateY(-1px) scale(1.05);
}

.cf-nav__link--active .nav-icon {
  color: var(--brand);
}

.cf-nav__link::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  width: 0;
  height: 2.5px;
  background: var(--brand-gradient);
  border-radius: 2px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  transform: translateX(-50%);
}

.cf-nav__link:hover {
  color: var(--brand);
}

.cf-nav__link:hover::after {
  width: 100%;
  left: 0;
  transform: translateX(0);
}

.cf-nav__link--active {
  color: var(--ink);
  font-weight: 600;
}

.cf-nav__link--active::after {
  width: 100%;
  left: 0;
  transform: translateX(0);
  background: var(--brand-gradient);
}

.nav-badge {
  font-size: 0.55rem;
  padding: 0.1rem 0.35rem;
  border-radius: 50%;
  font-weight: 700;
}

/* =========================================================================
   ACTIONS
   ========================================================================= */
.cf-nav__actions {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-shrink: 0;
  margin-left: 1.5rem;
}

.icon-btn {
  position: relative;
  border: none;
  background: transparent;
  font-size: 1.15rem;
  color: var(--muted);
  cursor: pointer;
  padding: 0.45rem;
  border-radius: 50%;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-btn:hover {
  background: var(--surface);
  color: var(--brand);
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.icon-btn:active {
  transform: scale(0.92);
}

.icon-badge { 
  position: absolute; 
  top: -2px; 
  right: -2px;
  background: var(--brand-gradient);
  color: var(--white);
  font-size: 0.5rem;
  font-weight: 700;
  min-width: 18px;
  height: 18px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--white);
  box-shadow: var(--shadow-sm);
}

/* =========================================================================
   USER DROPDOWN
   ========================================================================= */
.user-dropdown-wrapper {
  position: relative;
}

.user-chip { 
  display: flex; 
  align-items: center; 
  gap: 0.6rem; 
  cursor: pointer;
  padding: 0.25rem 0.8rem 0.25rem 0.25rem;
  border-radius: var(--radius-full);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  user-select: none;
  position: relative;
  border: 1px solid transparent;
}

.user-chip:hover {
  background: var(--surface);
  border-color: var(--line);
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

.user-chip:active {
  transform: scale(0.97);
}

.user-chip__verified {
  position: absolute; 
  left: 28px; 
  bottom: 0px;
  background: linear-gradient(135deg, #1fbf5c 0%, #34d399 100%);
  color: var(--white); 
  border-radius: 50%;
  width: 17px; 
  height: 17px; 
  display: flex; 
  align-items: center; 
  justify-content: center; 
  font-size: 0.4rem;
  border: 2px solid var(--white);
  box-shadow: var(--shadow-sm);
}

.user-chip__info { 
  display: flex; 
  flex-direction: column; 
  line-height: 1.15; 
}

.user-chip__info .name { 
  font-weight: 700; 
  font-size: 0.85rem;
  color: var(--ink);
  letter-spacing: -0.01em;
}

.user-chip__info .sub { 
  font-size: 0.6rem; 
  font-weight: 500;
  letter-spacing: 0.02em;
}

.user-chip__info .sub.verified { 
  color: #1fbf5c; 
}

.user-chip__info .sub.admin { 
  color: var(--brand); 
}

.user-chip__info .sub.creator { 
  color: #805ad5; 
}

.user-chip__info .sub.invitado { 
  color: var(--muted-light); 
}

.pi-chevron-down {
  transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  font-size: 0.65rem;
  color: var(--muted-light);
}

.pi-chevron-down.rotated {
  transform: rotate(180deg);
}

/* =========================================================================
   USER DROPDOWN MENU
   ========================================================================= */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-enter-from {
  opacity: 0;
  transform: translateY(-16px) scale(0.95);
}

.dropdown-enter-to {
  opacity: 1;
  transform: translateY(0) scale(1);
}

.dropdown-leave-from {
  opacity: 1;
  transform: translateY(0) scale(1);
}

.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-16px) scale(0.95);
}

.user-dropdown {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  background: var(--white);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-xl);
  min-width: 280px;
  padding: 0.5rem 0;
  z-index: 1000;
  border: 1px solid var(--line);
  overflow: hidden;
}

.user-dropdown::before {
  content: '';
  position: absolute;
  top: -8px;
  right: 24px;
  width: 16px;
  height: 16px;
  background: var(--white);
  border-left: 1px solid var(--line);
  border-top: 1px solid var(--line);
  transform: rotate(45deg);
  border-radius: 2px 0 0 0;
}

.user-dropdown__header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 1.25rem;
  background: var(--surface);
  border-bottom: 1px solid var(--line);
}

.user-dropdown__name {
  font-weight: 700;
  font-size: 0.95rem;
  color: var(--ink);
  letter-spacing: -0.01em;
}

.user-dropdown__email {
  font-size: 0.75rem;
  color: var(--muted);
  margin-top: 0.1rem;
  font-weight: 400;
}

.user-dropdown__verified {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.65rem;
  font-weight: 600;
  color: #1fbf5c;
  margin-top: 0.2rem;
}

.user-dropdown__verified i {
  font-size: 0.7rem;
}

.user-dropdown__divider {
  height: 1px;
  background: var(--line);
  margin: 0.3rem 1rem;
}

.user-dropdown__item {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 0.7rem 1.25rem;
  color: var(--ink);
  text-decoration: none;
  font-size: 0.85rem;
  transition: all 0.2s ease;
  cursor: pointer;
  border: none;
  background: transparent;
  width: 100%;
  text-align: left;
  font-family: inherit;
  font-weight: 500;
}

.user-dropdown__item:hover {
  background: var(--surface);
  color: var(--brand);
}

.user-dropdown__item i {
  font-size: 1rem;
  color: var(--muted-light);
  transition: all 0.2s ease;
  width: 1.25rem;
  text-align: center;
}

.user-dropdown__item:hover i {
  color: var(--brand);
  transform: translateX(2px);
}

.user-dropdown__item--danger {
  color: var(--brand);
}

.user-dropdown__item--danger i {
  color: var(--brand);
}

.user-dropdown__item--danger:hover {
  background: rgba(200, 30, 58, 0.06);
  color: var(--brand-dark);
}

.user-dropdown__item:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* =========================================================================
   CONTENT
   ========================================================================= */
.app-layout__content { 
  min-height: calc(100vh - 76px); 
}

/* =========================================================================
   RESPONSIVE
   ========================================================================= */
@media (max-width: 1024px) {
  .cf-nav__links {
    gap: 2rem;
    padding: 0 1rem;
  }
  
  .cf-nav__link {
    font-size: 0.8rem;
  }
  
  .cf-nav {
    padding: 0 1rem;
  }
  
  .cf-brand {
    margin-right: 2rem;
  }
}

@media (max-width: 768px) {
  .cf-nav {
    padding: 0 0.8rem;
    height: 68px;
  }
  
  .cf-nav__links {
    display: none;
  }
  
  .cf-brand__logo {
    height: 38px;
  }
  
  .cf-brand {
    margin-right: 0;
  }
  
  .user-chip__info {
    display: none;
  }
  
  .user-chip {
    padding: 0.15rem 0.5rem 0.15rem 0.15rem;
  }
  
  .icon-btn {
    font-size: 0.95rem;
    padding: 0.35rem;
  }
  
  .cf-nav__actions {
    gap: 0.5rem;
    margin-left: 0;
  }
  
  .user-chip__verified {
    left: 20px;
    width: 15px;
    height: 15px;
    font-size: 0.35rem;
  }
  
  .user-dropdown {
    min-width: 240px;
    right: -10px;
  }
}

@media (max-width: 480px) {
  .cf-nav {
    padding: 0 0.5rem;
    height: 60px;
  }
  
  .cf-brand__logo {
    height: 32px;
  }
  
  .cf-nav__actions {
    gap: 0.25rem;
  }
  
  .icon-btn {
    font-size: 0.8rem;
    padding: 0.3rem;
  }
  
  .user-chip {
    padding: 0.1rem 0.3rem 0.1rem 0.1rem;
  }
  
  .user-chip__verified {
    left: 16px;
    width: 13px;
    height: 13px;
    font-size: 0.3rem;
    bottom: 1px;
  }
  
  .user-dropdown {
    min-width: 200px;
    right: -20px;
  }
  
  .user-dropdown__header {
    padding: 1rem;
  }
  
  .user-dropdown__item {
    padding: 0.6rem 1rem;
    font-size: 0.8rem;
  }
}

/* =========================================================================
   SCROLLBAR
   ========================================================================= */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: var(--surface);
}

::-webkit-scrollbar-thumb {
  background: var(--muted-light);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: var(--muted);
}
</style>