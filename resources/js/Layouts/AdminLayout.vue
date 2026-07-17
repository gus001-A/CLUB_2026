<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, watch, reactive } from 'vue';
import { useToast } from '@/composables/useToast';
import Toast from '@/Components/Toast.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const page = usePage();
const admin = computed(() => page.props.auth?.admin);
const badges = computed(() => page.props.badges || { invitacionesPendientes: 0, notificaciones: 0 });
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
    {
        name: 'Usuarios',
        icon: 'pi-users',
        children: [
            { name: 'Todos los usuarios', route: 'admin.usuarios.index' },
            { name: 'Agregar usuario', route: 'admin.usuarios.create' },
        ],
    },
    { name: 'Cobros y Pagos', route: 'admin.cobros.index', icon: 'pi-dollar' },
    {
        name: 'Invitaciones',
        icon: 'pi-envelope',
        children: [
            { name: 'Todas las invitaciones', route: 'admin.invitaciones.index' },
            { name: 'Nueva invitación', route: 'admin.invitaciones.create' },
            { name: 'Códigos generados', route: 'admin.invitaciones.codigos' },
        ],
    },
    {
        name: 'Eventos',
        icon: 'pi-calendar',
        children: [
            { name: 'Todos los eventos', route: 'admin.eventos.index' },
            { name: 'Nuevo evento', route: 'admin.eventos.create' },
        ],
    },
    {
        name: 'Contenido',
        icon: 'pi-folder',
        children: [
            { name: 'Todo el contenido', route: 'admin.contenido.index' },
            { name: 'Nuevo contenido', route: 'admin.contenido.create' },
        ],
    },
    { name: 'Shop', route: 'admin.shop.index', icon: 'pi-shopping-bag' },
    { name: 'Reportes', route: 'admin.reportes.index', icon: 'pi-chart-line', chevron: true },
    { name: 'Mensajes', route: 'admin.mensajes.index', icon: 'pi-comments' },
    { name: 'Configuración', route: 'admin.configuracion.index', icon: 'pi-cog', chevron: true },
    { name: 'Seguridad', route: 'admin.seguridad.index', icon: 'pi-shield' },
    { name: 'Soporte', route: 'admin.soporte.index', icon: 'pi-headphones' },
];

function isActive(routeName) {
    if (!routeName) return false;
    return route().current(routeName) || route().current(routeName + '.*');
}

function grupoActivo(link) {
    return link.children?.some((c) => isActive(c.route)) ?? false;
}

const abiertos = reactive({});
links.forEach((l) => {
    if (l.children) abiertos[l.name] = grupoActivo(l);
});

function toggleGrupo(link) {
    abiertos[link.name] = !abiertos[link.name];
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
                <template v-for="link in links" :key="link.name">
                    <!-- Link simple -->
                    <Link
                        v-if="!link.children"
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

                    <!-- Link con submenú -->
                    <div v-else>
                        <button
                            type="button"
                            @click="toggleGrupo(link)"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                            :class="grupoActivo(link) && !abiertos[link.name]
                                ? 'bg-brand text-white'
                                : 'text-gray-600 hover:bg-gray-100'"
                        >
                            <i class="pi text-base" :class="link.icon"></i>
                            <span class="flex-1 text-left">{{ link.name }}</span>
                            <span
                                v-if="link.name === 'Invitaciones' ? badges.invitacionesPendientes > 0 : link.badge"
                                class="bg-brand text-white text-[11px] font-semibold rounded-full w-5 h-5 flex items-center justify-center"
                                :class="grupoActivo(link) && !abiertos[link.name] ? 'bg-white/25' : ''"
                            >
                                {{ link.name === 'Invitaciones' ? badges.invitacionesPendientes : link.badge }}
                            </span>
                            <i class="pi text-xs transition-transform" :class="abiertos[link.name] ? 'pi-chevron-down' : 'pi-chevron-right'"></i>
                        </button>

                        <div v-show="abiertos[link.name]" class="mt-1 ml-4 pl-4 border-l border-gray-100 space-y-0.5">
                            <Link
                                v-for="child in link.children"
                                :key="child.name"
                                :href="route(child.route)"
                                class="block px-3 py-2 rounded-lg text-sm transition-colors"
                                :class="isActive(child.route)
                                    ? 'text-brand font-semibold bg-brand/5'
                                    : 'text-gray-500 hover:bg-gray-100'"
                            >
                                {{ child.name }}
                            </Link>
                        </div>
                    </div>
                </template>
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
                        <span
                            v-if="badges.notificaciones > 0"
                            class="absolute -top-1.5 -right-1.5 bg-brand text-white text-[10px] font-semibold rounded-full w-4.5 h-4.5 min-w-[18px] min-h-[18px] flex items-center justify-center"
                        >
                            {{ badges.notificaciones }}
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