<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { useToast } from '@/composables/useToast';
import Toast from '@/Components/Toast.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const page = usePage();
const admin = computed(() => page.props.auth?.admin);
const toast = useToast();

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
    },
    { deep: true, immediate: true }
);

const links = [
    { name: 'Dashboard', route: 'admin.dashboard', icon: 'pi-home' },
    { name: 'Usuarios', route: 'admin.usuarios.index', icon: 'pi-users', chevron: true },
    { name: 'Cobros y Pagos', route: 'admin.cobros.index', icon: 'pi-dollar' },
    { name: 'Invitaciones', route: 'admin.invitaciones.index', icon: 'pi-envelope' },
    { name: 'Eventos', route: 'admin.eventos.index', icon: 'pi-calendar', chevron: true },
    { name: 'Contenido', route: 'admin.contenido.index', icon: 'pi-folder', chevron: true },
    { name: 'Shop', route: 'admin.shop.index', icon: 'pi-shopping-bag' },
    { name: 'Reportes', route: 'admin.reportes.index', icon: 'pi-chart-line', chevron: true },
    { name: 'Mensajes', route: 'admin.mensajes.index', icon: 'pi-comments' },
    { name: 'Configuración', route: 'admin.configuracion.index', icon: 'pi-cog', chevron: true },
    { name: 'Seguridad', route: 'admin.seguridad.index', icon: 'pi-shield' },
    { name: 'Soporte', route: 'admin.soporte.index', icon: 'pi-headphones' },
];

function isActive(routeName) {
    return route().current(routeName) || route().current(routeName + '.*');
}

function logout() {
    router.post(route('admin.logout'));
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <Toast />
        <ConfirmModal />
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col shrink-0">
            <div class="px-6 py-5 flex items-center gap-2 border-b border-gray-100">
                <span class="text-brand text-3xl leading-none">♥</span>
                <div class="leading-tight">
                    <p class="font-serif font-semibold text-gray-800 text-lg">Club de</p>
                    <p class="font-serif font-semibold text-brand text-lg italic -mt-1">Fantasías</p>
                </div>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <Link
                    v-for="link in links"
                    :key="link.name"
                    :href="route(link.route)"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                    :class="isActive(link.route)
                        ? 'bg-brand text-white'
                        : 'text-gray-600 hover:bg-gray-100'"
                >
                    <i class="pi text-base" :class="link.icon"></i>
                    <span class="flex-1">{{ link.name }}</span>
                    <span
                        v-if="link.badge"
                        class="bg-brand text-white text-[11px] font-semibold rounded-full w-5 h-5 flex items-center justify-center"
                        :class="isActive(link.route) ? 'bg-white/25' : ''"
                    >
                        {{ link.badge }}
                    </span>
                    <i v-else-if="link.chevron" class="pi pi-chevron-right text-xs opacity-50"></i>
                </Link>
            </nav>

            <div class="p-3 border-t border-gray-100">
                <button
                    @click="logout"
                    class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium text-brand border border-brand/40 hover:bg-brand/5"
                >
                    <i class="pi pi-sign-out"></i>
                    Cerrar sesión
                </button>
            </div>
        </aside>

        <!-- Contenido -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-serif font-semibold text-gray-800">
                        <slot name="title">Panel de Administrador</slot>
                    </h1>
                    <p class="text-sm text-gray-400">
                        <slot name="breadcrumb">Dashboard</slot>
                    </p>
                </div>

                <div class="flex items-center gap-6">
                    <button class="relative text-gray-400 hover:text-gray-600">
                        <i class="pi pi-bell text-xl"></i>
                        <span class="absolute -top-1.5 -right-1.5 bg-brand text-white text-[10px] font-semibold rounded-full w-4.5 h-4.5 min-w-[18px] min-h-[18px] flex items-center justify-center">
                            8
                        </span>
                    </button>

                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-full bg-brand/10 flex items-center justify-center text-brand">
                            <i class="pi pi-user text-lg"></i>
                        </div>
                        <div class="text-sm leading-tight">
                            <p class="font-semibold text-gray-800">{{ admin?.nombre || 'Administrador' }}</p>
                            <p class="text-brand text-xs font-medium">{{ admin?.rol === 'super_admin' ? 'Super Admin' : 'Admin' }}</p>
                        </div>
                        <i class="pi pi-chevron-down text-gray-300 text-xs ml-1"></i>
                    </div>
                </div>
            </header>

            <!-- Slot principal -->
            <main class="flex-1 p-8">
                <slot />
            </main>
        </div>
    </div>
</template>