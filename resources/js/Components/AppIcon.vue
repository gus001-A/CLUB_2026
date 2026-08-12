<!-- resources/js/Components/AppIcon.vue -->
<template>
  <i :class="['pi', `pi-${name}`]" :style="computedStyle"></i>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  name: {
    type: String,
    required: true
  },
  size: {
    type: [String, Number],
    default: null
  },
  color: {
    type: String,
    default: null
  }
});

const computedStyle = computed(() => {
  const style = {};
  if (props.size) {
    style.fontSize = typeof props.size === 'number' ? `${props.size}px` : props.size;
  }
  if (props.color) {
    style.color = props.color;
  }
  return style;
});
</script>