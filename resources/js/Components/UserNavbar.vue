<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  activeLink: {
    type: String,
    default: 'inicio'
  },
  isMobileOpen: {
    type: Boolean,
    default: false
  }
});

defineEmits(['toggle-mobile', 'scroll-to']);

const navLinks = [
  { id: 'inicio', label: 'Inicio' },
  { id: 'quienes-somos', label: 'Quiénes somos' },
  { id: 'servicios', label: 'Servicios' },
  { id: 'eventos', label: 'Eventos' },
  { id: 'contacto', label: 'Contacto' }
];
</script>

<template>
  <header class="user-header">
    <nav class="user-nav">
      <Link href="/" class="user-brand">
        <img 
          src="/images/LOGO.png" 
          alt="Club de Fantasías" 
          class="user-brand__logo"
        />
      </Link>

      <div class="user-nav__links" :class="{ 'user-nav__links--open': isMobileOpen }">
        <a 
          v-for="link in navLinks"
          :key="link.id"
          :href="`#${link.id}`"
          class="user-nav__link"
          :class="{ 'user-nav__link--active': activeLink === link.id }"
          @click="$emit('scroll-to', $event, link.id)"
        >
          {{ link.label }}
        </a>
      </div>

      <div class="user-nav__actions">
        <button 
          class="user-nav__hamburger" 
          @click="$emit('toggle-mobile')"
          aria-label="Menú"
        >
          <span></span>
          <span></span>
          <span></span>
        </button>

        <Link :href="route('login')" class="user-btn user-btn--ghost">Iniciar sesión</Link>
        <Link :href="route('register.invite')" class="user-btn user-btn--primary">Registro</Link>
      </div>
    </nav>
  </header>
</template>

<style scoped>
.user-header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: var(--white);
  border-bottom: 1px solid var(--line);
  transition: box-shadow 0.3s ease;
}

.user-header.scrolled {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.user-nav {
  max-width: 1240px;
  margin: 0 auto;
  height: 80px;
  padding: 0 2.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
}

.user-brand {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: var(--ink);
}

.user-brand__logo {
  height: 50px;
  width: auto;
  object-fit: contain;
  transition: transform 0.3s ease;
}

.user-brand__logo:hover {
  transform: scale(1.05);
}

.user-nav__links {
  display: flex;
  align-items: center;
  gap: 2.25rem;
  font-size: 0.88rem;
  font-weight: 500;
  color: var(--ink-soft);
}

.user-nav__link {
  text-decoration: none;
  color: inherit;
  padding-bottom: 0.35rem;
  position: relative;
  transition: color 0.3s ease;
  cursor: pointer;
}

.user-nav__link::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 0;
  height: 2px;
  background: var(--brand);
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.user-nav__link:hover {
  color: var(--brand);
}

.user-nav__link:hover::after {
  width: 100%;
}

.user-nav__link--active {
  color: var(--ink);
  font-weight: 600;
}

.user-nav__link--active::after {
  width: 100%;
  background: var(--brand);
}

.user-nav__actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-nav__hamburger {
  display: none;
  flex-direction: column;
  gap: 5px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 5px;
}

.user-nav__hamburger span {
  display: block;
  width: 25px;
  height: 2.5px;
  background: var(--ink);
  border-radius: 2px;
  transition: all 0.3s ease;
}

.user-nav__hamburger:hover span {
  background: var(--brand);
}

.user-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-family: var(--font-sans);
  font-weight: 600;
  font-size: 0.88rem;
  border-radius: 999px;
  border: 1px solid transparent;
  padding: 0.75rem 1.4rem;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.user-btn--primary {
  background: var(--brand);
  color: var(--white);
}

.user-btn--primary:hover {
  background: var(--brand-dark);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(200, 30, 58, 0.3);
}

.user-btn--ghost {
  background: transparent;
  border-color: #D9D5D2;
  color: var(--ink);
}

.user-btn--ghost:hover {
  border-color: var(--ink);
}

@media (max-width: 900px) {
  .user-nav__links {
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

  .user-nav__links--open {
    display: flex;
  }

  .user-nav__hamburger {
    display: flex;
  }

  .user-nav__actions .user-btn--ghost {
    display: none;
  }
}

@media (max-width: 640px) {
  .user-nav {
    padding: 0 1.25rem;
    height: 70px;
  }
  
  .user-brand__logo {
    height: 40px;
  }
  
  .user-nav__links {
    top: 70px;
  }
}
</style>