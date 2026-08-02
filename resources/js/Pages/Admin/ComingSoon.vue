<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    modulo: String,
});
</script>

<template>
    <Head :title="modulo" />

    <AdminLayout>
        <template #title>{{ modulo }}</template>
        <template #breadcrumb>Dashboard &gt; {{ modulo }}</template>

        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <i class="pi pi-hammer text-4xl text-brand mb-3"></i>
            <p class="text-gray-600 font-medium">El módulo de {{ modulo }} está en construcción.</p>
            <p class="text-gray-400 text-sm mt-1">Lo armamos en la próxima sesión.</p>
        </div>
    </AdminLayout>
</template>