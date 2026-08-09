<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Head } from '@inertiajs/vue3';
import UserNavbar from '@/Components/UserNavbar.vue';
import UserFooter from '@/Components/UserFooter.vue';

defineProps({
  title: {
    type: String,
    default: 'Club de Fantasías'
  }
});

const isMobileMenuOpen = ref(false);
const activeLink = ref('inicio');

function toggleMobileMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
}

function scrollToSection(event, sectionId) {
  event?.preventDefault?.();
  
  if (isMobileMenuOpen.value) {
    isMobileMenuOpen.value = false;
  }
  
  // Si no hay sectionId, ir al home
  if (!sectionId) {
    window.location.href = '/';
    return;
  }
  
  // Si estamos en la página de inicio, hacer scroll suave
  if (window.location.pathname === '/') {
    const target = document.getElementById(sectionId);
    if (target) {
      const header = document.querySelector('.user-header');
      const headerHeight = header ? header.offsetHeight : 80;
      const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight;
      
      window.scrollTo({
        top: targetPosition,
        behavior: 'smooth'
      });
    }
  } else {
    // Si estamos en otra página, navegar al home con el hash
    window.location.href = `/#${sectionId}`;
  }
}

function updateActiveLink() {
  const sections = ['inicio', 'quienes-somos', 'servicios', 'eventos', 'contacto'];
  const header = document.querySelector('.user-header');
  const headerHeight = header ? header.offsetHeight : 80;
  
  let currentSection = 'inicio';
  
  sections.forEach(sectionId => {
    const element = document.getElementById(sectionId);
    if (element) {
      const rect = element.getBoundingClientRect();
      if (rect.top <= headerHeight + 100) {
        currentSection = sectionId;
      }
    }
  });
  
  activeLink.value = currentSection;
}

onMounted(() => {
  // Solo agregar el listener si estamos en la página de inicio
  if (window.location.pathname === '/') {
    window.addEventListener('scroll', updateActiveLink);
  }
});

onBeforeUnmount(() => {
  window.removeEventListener('scroll', updateActiveLink);
});
</script>

<template>
  <div class="user-layout">
    <Head :title="title" />
    
    <UserNavbar 
      :active-link="activeLink"
      :is-mobile-open="isMobileMenuOpen"
      @toggle-mobile="toggleMobileMenu"
      @scroll-to="scrollToSection"
    />
    
    <main class="user-layout__main">
      <slot />
    </main>
    
    <UserFooter @scroll-to="scrollToSection" />
  </div>
</template>

<style scoped>
.user-layout {
  --brand: #C81E3A;
  --brand-dark: #A6152D;
  --ink: #171412;
  --ink-soft: #4B4744;
  --muted: #8A8481;
  --muted-light: #B7B2AF;
  --line: #ECE9E7;
  --surface: #FAF8F7;
  --white: #FFFFFF;
  --radius-sm: 10px;
  --radius-md: 16px;
  --radius-lg: 24px;
  --radius-full: 999px;
  --font-serif: 'Fraunces', Georgia, serif;
  --font-sans: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;
  
  font-family: var(--font-sans);
  color: var(--ink);
  background: var(--white);
  -webkit-font-smoothing: antialiased;
}

.user-layout__main {
  min-height: 70vh;
}
</style>