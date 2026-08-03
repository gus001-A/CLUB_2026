<script setup>
import { Link } from '@inertiajs/vue3';

/**
 * Espera el objeto de paginación completo que devuelve Laravel
 * (el mismo que ya usabas: pedidos, usuarios, reportes, etc.)
 * Ejemplo: <Pagination :data="usuarios" />
 */
defineProps({
    data: { type: Object, required: true },
});
</script>

<template>
    <div v-if="data.last_page > 1" class="flex items-center justify-between mt-5 text-sm">
        <p class="text-gray-400">Mostrando {{ data.from }}–{{ data.to }} de {{ data.total }}</p>
        <div class="flex items-center gap-1">
            <template v-for="(link, i) in data.links" :key="i">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    preserve-state
                    class="px-3 py-1.5 rounded-lg"
                    :class="link.active ? 'bg-brand text-white' : 'text-gray-500 hover:bg-gray-100'"
                    v-html="link.label"
                />
                <span v-else class="px-3 py-1.5 text-gray-300" v-html="link.label"></span>
            </template>
        </div>
    </div>
</template>