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

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">
            <div class="admin-card overflow-hidden mb-6">
                <div class="admin-card-header">
                    <span class="admin-card-header-title"><i class="pi pi-chart-bar text-brand"></i> Reportes Analíticos</span>
                </div>
                <p class="text-sm px-6 py-4" style="color:var(--muted)">
                    Genera y descarga reportes del sistema en PDF o Excel. Elige uno para empezar.
                </p>
            </div>

            <div class="admin-reportes-hub-grid gap-6 w-full">
                <Link
                    v-for="r in reportes"
                    :key="r.tipo"
                    :href="route('admin.reportes.detalle', r.tipo)"
                    class="admin-card overflow-hidden p-6 flex flex-col items-center text-center gap-3 hover:shadow-md hover:-translate-y-0.5 transition"
                >
                    <div class="admin-icon-circle" style="width:56px;height:56px">
                        <i class="pi" :class="r.icono" style="font-size:1.4rem"></i>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900">{{ r.titulo }}</p>
                        <p class="text-xs mt-1" style="color:var(--muted)">{{ r.descripcion }}</p>
                    </div>
                </Link>
            </div>
        </div>
    </AdminLayout>
</template>