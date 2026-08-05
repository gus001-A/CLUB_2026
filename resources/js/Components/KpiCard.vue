<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    icon: { type: String, required: true }, // ej. 'pi-users'
    hint: { type: String, default: null }, // texto pequeño debajo (ej. "+3 nuevos hoy")
    hintColor: { type: String, default: 'text-brand' }, // clase de color para el hint
});
</script>

<template>
    <div class="admin-kpi-card w-full">
        <div>
            <p class="text-sm text-gray-400">{{ label }}</p>
            <p class="text-2xl font-semibold text-gray-800 mt-1">{{ value }}</p>
            <p v-if="hint" class="text-xs mt-1 font-medium" :class="hintColor">{{ hint }}</p>
        </div>
        <div class="admin-icon-circle" style="width:44px;height:44px">
            <i class="pi text-lg" :class="icon"></i>
        </div>
    </div>
</template>