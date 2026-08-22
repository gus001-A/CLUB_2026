<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    reportes: Array,
});
</script>

<template>
    <Head title="Reportes" />

    <AdminLayout>
        <template #title>Reportes</template>
        <template #breadcrumb>Dashboard &gt; Reportes</template>

        <div class="admin-reportes-page">
            <div class="admin-cobros-card mb-6">
                <div class="admin-cobros-card__header">
                    <div class="admin-cobros-card__header-left">
                        <div class="admin-cobros-header-icon"><i class="pi pi-chart-bar"></i></div>
                        <div>
                            <h3>Reportes Analíticos</h3>
                            <p class="admin-cobros-header-subtitle">Genera y descarga reportes del sistema en PDF o Excel</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-reportes-hub-grid gap-6 w-full">
                <Link v-for="r in reportes" :key="r.tipo" :href="route('admin.reportes.detalle', r.tipo)" class="admin-reportes-hub-card">
                    <div class="admin-reportes-hub-icon">
                        <i class="pi" :class="r.icono"></i>
                    </div>
                    <div>
                        <p class="admin-reportes-hub-title">{{ r.titulo }}</p>
                        <p class="admin-reportes-hub-desc">{{ r.descripcion }}</p>
                    </div>
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>