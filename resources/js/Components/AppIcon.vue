<script setup>
// Set mínimo de íconos en SVG puro (sin librería externa) usados en la
// landing. Si prefieres una librería de íconos, esta es la pieza a
// reemplazar por, por ejemplo, `lucide-vue-next` manteniendo la misma
// prop `name` -> componente.
defineProps({
  name: { type: String, required: true },
});
</script>

<template>
  <svg
    v-if="name === 'shield-check'"
    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
    stroke-linecap="round" stroke-linejoin="round"
  >
    <path d="M12 2 4 5v6c0 5 3.4 8.4 8 11 4.6-2.6 8-6 8-11V5l-8-3Z" />
    <path d="m9 12 2 2 4-4" />
  </svg>

  <svg v-else-if="name === 'map-pin'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
    <circle cx="12" cy="10" r="3" />
  </svg>

  <svg v-else-if="name === 'lock'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    <rect x="4" y="11" width="16" height="10" rx="2" />
    <path d="M8 11V7a4 4 0 0 1 8 0v4" />
  </svg>

  <svg v-else-if="name === 'gem'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    <path d="M6 3h12l4 6-10 12L2 9Z" />
    <path d="M2 9h20M9 3l3 6-3 12M15 3l-3 6 3 12" />
  </svg>

  <svg v-else-if="name === 'ticket'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    <path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v1a2 2 0 0 0 0 4v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1a2 2 0 0 0 0-4Z" />
    <path d="M13 5v14" stroke-dasharray="2 2" />
  </svg>

  <svg v-else-if="name === 'badge-check'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    <path d="m12 2 2.4 1.9 3-.5 1 2.9 2.9 1-.5 3L22 12l-1.2 2.7.5 3-2.9 1-1 2.9-3-.5L12 22l-2.4-1.9-3 .5-1-2.9-2.9-1 .5-3L2 12l1.2-2.7-.5-3 2.9-1 1-2.9 3 .5Z" />
    <path d="m9 12 2 2 4-4" />
  </svg>

  <svg v-else-if="name === 'sparkles'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    <path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M18.4 5.6l-2.8 2.8M8.4 15.6l-2.8 2.8" />
  </svg>

  <svg v-else-if="name === 'calendar'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    <rect x="3" y="5" width="18" height="16" rx="2" />
    <path d="M8 3v4M16 3v4M3 10h18" />
  </svg>

  <svg v-else-if="name === 'eye-off'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    <path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M6.7 6.7C4.6 8.1 3 10 2 12c1.9 4 6 7 10 7 1.6 0 3.1-.4 4.5-1.2M9.9 5.1C10.6 5 11.3 5 12 5c4 0 8.1 3 10 7-.5 1.1-1.2 2.1-2 3" />
  </svg>

  <svg v-else-if="name === 'users'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="9" cy="8" r="3.5" />
    <path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 5.5c1.7.4 3 2 3 3.9 0 1.9-1.3 3.5-3 3.9M21.5 20a5.7 5.7 0 0 0-4.5-5.6" />
  </svg>

  <svg v-else-if="name === 'mail'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    <rect x="3" y="5" width="18" height="14" rx="2" />
    <path d="m3 7 9 6 9-6" />
  </svg>

  <svg v-else-if="name === 'clock'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="12" cy="12" r="9" />
    <path d="M12 7v5l3.5 2" />
  </svg>
</template>
