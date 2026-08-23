<script setup>
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import AppFooter from '@/Components/AppFooter.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import CustomAvatar from '@/Components/AvatarCustom.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import { useConfirm } from '@/composables/useConfirm';
import PvBadge from 'primevue/badge';
import axios from 'axios';

/* ---------------------------------------------------------------
 * Props
 * ---------------------------------------------------------------
 * activeNav: cuál ítem del menú resaltar ('inicio' | 'descubrir' | 'eventos' | 'shop' | 'mensajes' | 'comunidad')
 * hideFooter: oculta el AppFooter — útil en páginas tipo chat, donde el
 * pie de página le quita espacio útil vertical a la conversación.
 * --------------------------------------------------------------- */
const props = defineProps({
  activeNav: { type: String, default: '' },
  hideFooter: { type: Boolean, default: false },
});

// Estado del dropdown
const isDropdownOpen = ref(false);

// Estado de carga para logout
const isLoggingOut = ref(false);

// Obtener datos del usuario desde Inertia
const page = usePage();

// Confirm modal
const { confirm } = useConfirm();

// ============================================================
// 🔔 SISTEMA DE NOTIFICACIONES
// ============================================================
const notificaciones = ref([]);
const notificacionesNoLeidas = ref(0);
const mostrarPanelNotificaciones = ref(false);
const cargandoNotificaciones = ref(false);

// 🔥 FUNCIÓN PARA OBTENER URL DEL AVATAR CORRECTAMENTE
const getAvatarUrl = (avatar) => {
  if (!avatar) return '/images/shared/avatar-default.jpg';

  if (avatar.startsWith('http://') || avatar.startsWith('https://')) {
    return avatar;
  }

  if (avatar.startsWith('/storage/') || avatar.startsWith('storage/')) {
    return avatar.startsWith('/') ? avatar : '/' + avatar;
  }

  return '/storage/' + avatar;
};

// 🔥 USUARIO
const usuario = computed(() => {
  const user = page.props.usuario || null;

  if (user) {
    let avatar = user.avatar || '/images/shared/avatar-default.jpg';
    avatar = getAvatarUrl(avatar);

    return {
      id: user.id,
      nombre: user.nombre || user.apodo || 'Usuario',
      apodo: user.apodo || user.nombre || 'Usuario',
      email: user.email || '',
      avatar: avatar,
      verificado: user.verificado || false,
      rol: user.rol || 'usuario',
      foto_principal: user.foto_principal || null,
      perfil: user.perfil || null,
      tiene_perfil: user.tiene_perfil || false,
      es_creador: user.rol === 'creador' || user.es_creador || false,
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
    es_creador: false,
  };
});

// ============================================================
// 🔔 FUNCIONES DE NOTIFICACIONES
// ============================================================

// Obtener notificaciones del servidor
const obtenerNotificaciones = async () => {
  if (!usuario.value.id) return;

  try {
    cargandoNotificaciones.value = true;
    const response = await axios.get('/notificaciones');

    if (response.data && response.data.ok) {
      notificaciones.value = response.data.notificaciones || [];
      notificacionesNoLeidas.value = response.data.no_leidas || 0;

      // Actualizar el badge del icono de campana
      actualizarBadgeNotificaciones();
    }
  } catch (error) {
    console.error('Error al obtener notificaciones:', error);
  } finally {
    cargandoNotificaciones.value = false;
  }
};

// Marcar notificaciones como leídas
const marcarComoLeidas = async () => {
  if (notificacionesNoLeidas.value === 0) return;

  try {
    await axios.post('/notificaciones/marcar-leidas');
    notificacionesNoLeidas.value = 0;
    actualizarBadgeNotificaciones();

    // Actualizar el estado de las notificaciones
    notificaciones.value = notificaciones.value.map(n => ({
      ...n,
      leida: true
    }));
  } catch (error) {
    console.error('Error al marcar notificaciones como leídas:', error);
  }
};

// Marcar una notificación como leída
const marcarNotificacionComoLeida = async (id) => {
  try {
    await axios.post(`/notificaciones/${id}/marcar-leida`);

    const notificacion = notificaciones.value.find(n => n.id === id);
    if (notificacion && !notificacion.leida) {
      notificacion.leida = true;
      notificacionesNoLeidas.value = Math.max(0, notificacionesNoLeidas.value - 1);
      actualizarBadgeNotificaciones();
    }
  } catch (error) {
    console.error('Error al marcar notificación como leída:', error);
  }
};

// Actualizar el badge del icono de campana
const actualizarBadgeNotificaciones = () => {
  const badge = document.querySelector('.icon-badge--notificaciones');
  if (badge) {
    if (notificacionesNoLeidas.value > 0) {
      badge.textContent = notificacionesNoLeidas.value > 99 ? '99+' : notificacionesNoLeidas.value;
      badge.style.display = 'flex';
    } else {
      badge.style.display = 'none';
    }
  }
};

// Alternar panel de notificaciones
const togglePanelNotificaciones = () => {
  mostrarPanelNotificaciones.value = !mostrarPanelNotificaciones.value;
  if (mostrarPanelNotificaciones.value) {
    obtenerNotificaciones();
  }
};

// Cerrar panel de notificaciones
const cerrarPanelNotificaciones = () => {
  mostrarPanelNotificaciones.value = false;
};

// Hacer clic en una notificación
const clickNotificacion = (notificacion) => {
  if (!notificacion.leida) {
    marcarNotificacionComoLeida(notificacion.id);
  }

  cerrarPanelNotificaciones();

  // Redirigir según el tipo de notificación
  if (notificacion.tipo === 'like' || notificacion.tipo === 'comentario') {
    router.visit(`/contenido/${notificacion.contenido_id}`);
  } else if (notificacion.tipo === 'suscripcion') {
    router.visit(`/suscripciones`);
  } else if (notificacion.tipo === 'match') {
    router.visit('/mensajes');
  } else if (notificacion.tipo === 'seguidor') {
    router.visit(`/creador/${notificacion.usuario_id}`);
  } else {
    // Redirigir al enlace de la notificación si existe
    if (notificacion.link) {
      router.visit(notificacion.link);
    }
  }
};

// Escuchar eventos de notificaciones en tiempo real (con Polling)
let pollingInterval = null;

const iniciarPollingNotificaciones = () => {
  if (pollingInterval) {
    clearInterval(pollingInterval);
  }

  // Solo iniciar polling si el usuario está autenticado
  if (!usuario.value.id) return;

  // Cada 30 segundos verificar nuevas notificaciones
  pollingInterval = setInterval(() => {
    // Solo verificar si el panel no está abierto para no interrumpir
    if (!mostrarPanelNotificaciones.value) {
      verificarNuevasNotificaciones();
    }
  }, 30000);
};

const verificarNuevasNotificaciones = async () => {
  try {
    const response = await axios.get('/notificaciones/nuevas');
    if (response.data && response.data.ok && response.data.nuevas > 0) {
      // Hay nuevas notificaciones, actualizar el badge
      notificacionesNoLeidas.value += response.data.nuevas;
      actualizarBadgeNotificaciones();

      // Si el panel está cerrado, mostrar un toast
      if (!mostrarPanelNotificaciones.value) {
        // Mostrar toast con el número de nuevas notificaciones
        const toast = document.querySelector('.toast-notification');
        if (toast) {
          // Usar el sistema de toast si está disponible
          window.dispatchEvent(new CustomEvent('toast', {
            detail: {
              type: 'info',
              title: 'Nuevas notificaciones',
              message: `Tienes ${response.data.nuevas} notificación(es) nueva(s)`,
              duration: 5000
            }
          }));
        }
      }
    }
  } catch (error) {
    // Silenciar errores de polling
  }
};

// ============================================================
// Computed para mensajes
// ============================================================
const mensajes = computed(() => 0);

// Items del menú de navegación
const navItems = computed(() => [
  { key: 'inicio', label: 'INICIO', href: '/inicio', icon: 'pi pi-home' },
  { key: 'descubrir', label: 'DESCUBRIR', href: '/descubrir', icon: 'pi pi-compass' },
  { key: 'eventos', label: 'EVENTOS', href: '/eventos', icon: 'pi pi-calendar' },
  { key: 'shop', label: 'TIENDA', href: '/tienda', icon: 'pi pi-shopping-bag' },
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
        // Detener polling al cerrar sesión
        if (pollingInterval) {
          clearInterval(pollingInterval);
          pollingInterval = null;
        }
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

  // Cerrar panel de notificaciones
  const notifPanel = document.querySelector('.notificaciones-panel');
  if (notifPanel && !notifPanel.contains(event.target) && !event.target.closest('.icon-btn--notificaciones')) {
    cerrarPanelNotificaciones();
  }
};

// Perfil y rutas
const perfilRoute = computed(() => {
  try {
    return route('perfil.ver');
  } catch (e) {
    return '/perfil';
  }
});

const configuracionRoute = computed(() => {
  try {
    return route('profile.usuario');
  } catch (e) {
    return '/configuracion';
  }
});

const gananciasRoute = computed(() => {
  try {
    return route('creador.ganancias');
  } catch (e) {
    return '/creador/ganancias';
  }
});

// Watch para cambios en el usuario
watch(() => page.props.usuario, (newUser) => {
  console.log('🔄 Usuario actualizado en AppLayout:', newUser);
  if (newUser && newUser.id) {
    // Iniciar polling cuando el usuario inicia sesión
    iniciarPollingNotificaciones();
    // Obtener notificaciones iniciales
    obtenerNotificaciones();
  }
}, { deep: true });

// Escuchar eventos de notificaciones desde otros componentes
const handleNuevaNotificacion = (event) => {
  if (event.detail) {
    notificacionesNoLeidas.value += 1;
    actualizarBadgeNotificaciones();

    // Agregar al inicio de la lista
    notificaciones.value.unshift({
      id: Date.now(),
      ...event.detail,
      leida: false,
      created_at: new Date().toISOString()
    });

    // Mostrar toast
    window.dispatchEvent(new CustomEvent('toast', {
      detail: {
        type: 'info',
        title: event.detail.titulo || 'Nueva notificación',
        message: event.detail.mensaje || 'Tienes una nueva notificación',
        duration: 5000
      }
    }));
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  window.addEventListener('nueva-notificacion', handleNuevaNotificacion);

  console.log('✅ AppLayout montado');
  console.log('📋 Usuario:', usuario.value);
  console.log('📋 activeNav:', props.activeNav);
  console.log('📋 Es creador:', usuario.value.es_creador);

  // Iniciar polling si el usuario está autenticado
  if (usuario.value.id) {
    iniciarPollingNotificaciones();
    obtenerNotificaciones();
  }

  try {
    console.log('🔍 Ruta perfil.ver:', route('perfil.ver'));
  } catch (e) {
    console.warn('⚠️ Ruta perfil.ver no definida:', e.message);
  }
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  window.removeEventListener('nueva-notificacion', handleNuevaNotificacion);
  if (pollingInterval) {
    clearInterval(pollingInterval);
    pollingInterval = null;
  }
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
          <img src="/images/LOGO.png" alt="Club de Fantasías" class="cf-brand__logo" />
        </Link>

        <!-- ENLACES DEL NAVBAR -->
        <div class="cf-nav__links">
          <Link v-for="item in navItems" :key="item.key" :href="item.href" class="cf-nav__link"
            :class="{ 'cf-nav__link--active': activeNav === item.key }">
            <span class="cf-nav__link-label">
              <i :class="item.icon" class="nav-icon"></i>
              {{ item.label }}
            </span>
            <PvBadge v-if="item.badge" :value="item.badge" severity="danger" class="nav-badge" />
          </Link>
        </div>

        <!-- ACCIONES -->
        <div class="cf-nav__actions">
          <!-- 🔔 BOTÓN DE NOTIFICACIONES -->
          <div class="notificaciones-wrapper">
            <button class="icon-btn icon-btn--notificaciones" @click="togglePanelNotificaciones" title="Notificaciones">
              <i class="pi pi-bell"></i>
              <span class="icon-badge icon-badge--notificaciones" id="notificaciones-badge">
                {{ notificacionesNoLeidas > 99 ? '99+' : notificacionesNoLeidas }}
              </span>
            </button>

            <!-- PANEL DE NOTIFICACIONES -->
            <Transition name="dropdown">
              <div v-if="mostrarPanelNotificaciones" class="notificaciones-panel">
                <div class="notificaciones-panel__header">
                  <h3>Notificaciones</h3>
                  <button v-if="notificacionesNoLeidas > 0" class="notificaciones-panel__mark-read"
                    @click="marcarComoLeidas">
                    Marcar todas como leídas
                  </button>
                </div>

                <div class="notificaciones-panel__lista">
                  <div v-if="cargandoNotificaciones" class="notificaciones-panel__cargando">
                    <i class="pi pi-spin pi-spinner"></i>
                    Cargando...
                  </div>

                  <template v-else-if="notificaciones.length === 0">
                    <div class="notificaciones-panel__vacio">
                      <i class="pi pi-inbox"></i>
                      <span>No tienes notificaciones</span>
                    </div>
                  </template>

                  <template v-else>
                    <div v-for="notificacion in notificaciones" :key="notificacion.id" class="notificacion-item"
                      :class="{ 'notificacion-item--no-leida': !notificacion.leida }"
                      @click="clickNotificacion(notificacion)">
                      <div class="notificacion-item__icon" :class="{
                        'notif-like': notificacion.tipo === 'like',
                        'notif-comentario': notificacion.tipo === 'comentario',
                        'notif-suscripcion': notificacion.tipo === 'suscripcion',
                        'notif-match': notificacion.tipo === 'match',
                        'notif-seguidor': notificacion.tipo === 'seguidor',
                      }">
                        <i :class="{
                          'pi pi-heart-fill': notificacion.tipo === 'like',
                          'pi pi-comment': notificacion.tipo === 'comentario',
                          'pi pi-crown': notificacion.tipo === 'suscripcion',
                          'pi pi-heart': notificacion.tipo === 'match',
                          'pi pi-user-plus': notificacion.tipo === 'seguidor',
                          'pi pi-bell': !['like', 'comentario', 'suscripcion', 'match', 'seguidor'].includes(notificacion.tipo)
                        }"></i>
                      </div>
                      <div class="notificacion-item__contenido">
                        <div class="notificacion-item__mensaje" v-html="notificacion.mensaje"></div>
                        <span class="notificacion-item__tiempo">
                          {{ new Date(notificacion.created_at).toLocaleDateString('es-ES', {
                            day: '2-digit',
                            month: 'short',
                            hour: '2-digit',
                            minute: '2-digit'
                          }) }}
                        </span>
                      </div>
                      <div v-if="!notificacion.leida" class="notificacion-item__dot"></div>
                    </div>
                  </template>
                </div>

                <div v-if="notificaciones.length > 0" class="notificaciones-panel__footer">
                  <Link href="/notificaciones" class="notificaciones-panel__ver-todas"
                    @click="cerrarPanelNotificaciones">
                    Ver todas las notificaciones
                  </Link>
                </div>
              </div>
            </Transition>
          </div>

          <!-- USER DROPDOWN -->
          <div class="user-dropdown-wrapper" @click.stop>
            <div class="user-chip" @click="toggleDropdown">
              <CustomAvatar :image="usuario.avatar" :label="usuario.nombre.charAt(0).toUpperCase()" size="large" />
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
                  <CustomAvatar :image="usuario.avatar" :label="usuario.nombre.charAt(0).toUpperCase()" size="xlarge" />
                  <div>
                    <div class="user-dropdown__name">{{ usuario.nombre }}</div>
                    <div class="user-dropdown__email">{{ usuario.email }}</div>
                    <span v-if="usuario.verificado" class="user-dropdown__verified">
                      <i class="pi pi-check-circle"></i> Verificado
                    </span>
                  </div>
                </div>
                <div class="user-dropdown__divider"></div>

                <Link :href="perfilRoute" class="user-dropdown__item" @click="closeDropdown">
                  <i class="pi pi-user"></i> Mi perfil
                </Link>

                <Link :href="configuracionRoute" class="user-dropdown__item" @click="closeDropdown">
                  <i class="pi pi-cog"></i> Configuración
                </Link>

                <Link v-if="usuario.es_creador" :href="gananciasRoute"
                  class="user-dropdown__item user-dropdown__item--creator" @click="closeDropdown">
                  <i class="pi pi-wallet"></i> Ganancias del creador
                </Link>

                <div class="user-dropdown__divider"></div>
                <button @click="handleLogout" class="user-dropdown__item user-dropdown__item--danger"
                  :disabled="isLoggingOut">
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

    <AppFooter v-if="!hideFooter" />
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
  --creator: #805ad5;
  --creator-light: #f0ebff;

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
   NAVBAR
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

.icon-btn--notificaciones {
  position: relative;
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

.icon-badge--notificaciones {
  background: #EF4444;
}

/* =========================================================================
   NOTIFICACIONES PANEL
   ========================================================================= */
.notificaciones-wrapper {
  position: relative;
}

.notificaciones-panel {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  background: var(--white);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-xl);
  width: 380px;
  max-height: 480px;
  z-index: 1000;
  border: 1px solid var(--line);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.notificaciones-panel::before {
  content: '';
  position: absolute;
  top: -8px;
  right: 18px;
  width: 16px;
  height: 16px;
  background: var(--white);
  border-left: 1px solid var(--line);
  border-top: 1px solid var(--line);
  transform: rotate(45deg);
  border-radius: 2px 0 0 0;
}

.notificaciones-panel__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.8rem 1.25rem;
  border-bottom: 1px solid var(--line);
  flex-shrink: 0;
  background: var(--surface);
}

.notificaciones-panel__header h3 {
  font-size: 0.9rem;
  font-weight: 700;
  margin: 0;
  color: var(--ink);
}

.notificaciones-panel__mark-read {
  font-size: 0.7rem;
  font-weight: 600;
  color: var(--brand);
  background: none;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.notificaciones-panel__mark-read:hover {
  color: var(--brand-dark);
  text-decoration: underline;
}

.notificaciones-panel__lista {
  flex: 1;
  overflow-y: auto;
  padding: 0.25rem 0;
}

.notificaciones-panel__cargando {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 2rem 0;
  color: var(--muted);
  font-size: 0.8rem;
}

.notificaciones-panel__vacio {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2.5rem 0;
  color: var(--muted-light);
}

.notificaciones-panel__vacio i {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.notificaciones-panel__vacio span {
  font-size: 0.85rem;
}

.notificaciones-panel__footer {
  padding: 0.6rem 1.25rem;
  border-top: 1px solid var(--line);
  text-align: center;
  flex-shrink: 0;
  background: var(--surface);
}

.notificaciones-panel__ver-todas {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--brand);
  text-decoration: none;
  transition: all 0.2s ease;
}

.notificaciones-panel__ver-todas:hover {
  color: var(--brand-dark);
  text-decoration: underline;
}

/* =========================================================================
   NOTIFICACION ITEM
   ========================================================================= */
.notificacion-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.7rem 1.25rem;
  cursor: pointer;
  transition: all 0.2s ease;
  border-bottom: 1px solid var(--line);
  position: relative;
}

.notificacion-item:last-child {
  border-bottom: none;
}

.notificacion-item:hover {
  background: var(--surface);
}

.notificacion-item--no-leida {
  background: rgba(200, 30, 58, 0.04);
}

.notificacion-item--no-leida:hover {
  background: rgba(200, 30, 58, 0.08);
}

.notificacion-item__icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 0.8rem;
}

.notif-like {
  background: #FEE2E2;
  color: #EF4444;
}

.notif-comentario {
  background: #DBEAFE;
  color: #3B82F6;
}

.notif-suscripcion {
  background: #FEF3C7;
  color: #F59E0B;
}

.notif-match {
  background: #FCE4EC;
  color: #EC407A;
}

.notif-seguidor {
  background: #E8F5E9;
  color: #4CAF50;
}

.notificacion-item__contenido {
  flex: 1;
  min-width: 0;
}

.notificacion-item__mensaje {
  font-size: 0.82rem;
  color: var(--ink);
  line-height: 1.4;
}

.notificacion-item__mensaje :deep(strong) {
  font-weight: 700;
  color: var(--ink);
}

.notificacion-item__tiempo {
  font-size: 0.6rem;
  color: var(--muted-light);
  display: block;
  margin-top: 0.15rem;
}

.notificacion-item__dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--brand);
  flex-shrink: 0;
  margin-top: 0.4rem;
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
  color: var(--creator);
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

.user-dropdown__item--creator {
  color: var(--creator);
}

.user-dropdown__item--creator i {
  color: var(--creator);
}

.user-dropdown__item--creator:hover {
  background: var(--creator-light);
  color: #6b46c1;
}

.user-dropdown__item--creator:hover i {
  color: #6b46c1;
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

  .notificaciones-panel {
    width: 320px;
    right: -10px;
    max-height: 400px;
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

  .notificaciones-panel {
    width: 280px;
    right: -20px;
    max-height: 350px;
  }

  .notificacion-item {
    padding: 0.5rem 0.8rem;
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