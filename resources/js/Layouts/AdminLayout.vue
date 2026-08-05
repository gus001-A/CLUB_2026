<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { useToast } from '@/composables/useToast';
import ToastNotification from '@/Components/ToastNotification.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const page = usePage();
const admin = computed(() => page.props.auth?.admin);
const badges = computed(() => page.props.badges || { invitacionesPendientes: 0, notificaciones: 0 });
const toast = useToast();

const sidebarAbierto = ref(false);
const notificacionesAbiertas = ref(false);

// Vendrán de un prop compartido por Inertia (igual que "badges").
// Ajusta el nombre según cómo lo envíes desde el controlador/middleware.
const notificaciones = computed(() => page.props.notificaciones || []);

const links = computed(() => [
    { name: 'Dashboard', route: 'admin.dashboard', icon: 'pi-home' },
    {
        name: 'Usuarios',
        icon: 'pi-users',
        children: [
            { name: 'Todos los usuarios', route: 'admin.usuarios.index' },
            { name: 'Agregar usuario', route: 'admin.usuarios.create' },
            ...(route().current('admin.usuarios.edit')
                ? [{ name: 'Editar Usuario', url: window.location.pathname }]
                : []),
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
            ...(route().current('admin.eventos.edit')
                ? [{ name: 'Editar Evento', url: window.location.pathname }]
                : []),
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
    { name: 'Reportes', route: 'admin.reportes.index', icon: 'pi-chart-line' },
    { name: 'Mensajes', route: 'admin.mensajes.index', icon: 'pi-comments' },
    { name: 'Configuración', route: 'admin.configuracion.index', icon: 'pi-cog' },
    { name: 'Seguridad', route: 'admin.seguridad.index', icon: 'pi-shield' },
    { name: 'Soporte', route: 'admin.soporte.index', icon: 'pi-headphones' },
]);

function isActive(routeName) {
    if (!routeName) return false;
    return route().current(routeName) || route().current(routeName + '.*');
}

// true si la ruta actual pertenece a alguno de los hijos de este grupo
function grupoActivo(link) {
    return link.children?.some((c) => c.url || isActive(c.route)) ?? false;
}

// abiertos guarda SOLO los clics manuales del usuario (abrir/cerrar a mano).
// Si el grupo está activo por la ruta actual, se muestra abierto SIEMPRE,
// sin importar el estado guardado — así no depende de cuándo se calculó.
const abiertos = reactive({});

function estaAbierto(link) {
    if (grupoActivo(link)) return true;
    return !!abiertos[link.name];
}

function toggleGrupo(link) {
    abiertos[link.name] = !estaAbierto(link);
}

function cerrarSidebarMovil() {
    sidebarAbierto.value = false;
}

function toggleNotificaciones() {
    notificacionesAbiertas.value = !notificacionesAbiertas.value;
}

function cerrarNotificaciones() {
    notificacionesAbiertas.value = false;
}

function logout() {
    router.post(route('admin.logout'));
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <ToastNotification />
        <ConfirmModal />

        <!-- Overlay oscuro (solo en móvil, cuando el menú está abierto) -->
        <div
            v-if="sidebarAbierto"
            @click="sidebarAbierto = false"
            class="fixed inset-0 bg-black/40 z-40 lg:hidden"
        ></div>

        <!-- Sidebar -->
        <aside
            class="w-64 bg-white border-r border-gray-200 flex flex-col shrink-0 fixed inset-y-0 left-0 z-50 transition-transform duration-200 lg:static lg:translate-x-0"
            :class="sidebarAbierto ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="px-6 py-5 flex items-center justify-between gap-2 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="text-brand text-3xl leading-none">♥</span>
                    <div class="leading-tight">
                        <p class="font-serif font-semibold text-gray-800 text-lg">Club de</p>
                        <p class="font-serif font-semibold text-brand text-lg italic -mt-1">Fantasías</p>
                    </div>
                </div>
                <button @click="sidebarAbierto = false" class="lg:hidden text-gray-400 hover:text-gray-600">
                    <i class="pi pi-times text-lg"></i>
                </button>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <template v-for="link in links" :key="link.name">
                    <!-- Link simple -->
                    <Link
                        v-if="!link.children"
                        :href="route(link.route)"
                        @click="cerrarSidebarMovil"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                        :class="isActive(link.route)
                            ? 'bg-brand text-white'
                            : 'text-gray-600 hover:bg-gray-100'"
                    >
                        <i class="pi text-base" :class="link.icon"></i>
                        <span class="flex-1">{{ link.name }}</span>
                        <span
                            v-if="link.name === 'Invitaciones' ? badges.invitacionesPendientes > 0 : link.badge"
                            class="bg-brand text-white text-[11px] font-semibold rounded-full w-5 h-5 flex items-center justify-center"
                            :class="isActive(link.route) ? 'bg-white/25' : ''"
                        >
                            {{ link.name === 'Invitaciones' ? badges.invitacionesPendientes : link.badge }}
                        </span>
                    </Link>

                    <!-- Link con submenú -->
                    <div v-else>
                        <button
                            type="button"
                            @click="toggleGrupo(link)"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                            :class="grupoActivo(link) && !estaAbierto(link)
                                ? 'bg-brand text-white'
                                : 'text-gray-600 hover:bg-gray-100'"
                        >
                            <i class="pi text-base" :class="link.icon"></i>
                            <span class="flex-1 text-left">{{ link.name }}</span>
                            <span
                                v-if="link.name === 'Invitaciones' && badges.invitacionesPendientes > 0"
                                class="bg-brand text-white text-[11px] font-semibold rounded-full w-5 h-5 flex items-center justify-center"
                                :class="grupoActivo(link) && !estaAbierto(link) ? 'bg-white/25' : ''"
                            >
                                {{ badges.invitacionesPendientes }}
                            </span>
                            <i class="pi text-xs transition-transform" :class="estaAbierto(link) ? 'pi-chevron-down' : 'pi-chevron-right'"></i>
                        </button>

                        <div v-show="estaAbierto(link)" class="mt-1 ml-4 pl-4 border-l border-gray-100 space-y-0.5">
                            <Link
                                v-for="child in link.children"
                                :key="child.name"
                                :href="child.url || route(child.route)"
                                @click="cerrarSidebarMovil"
                                class="block px-3 py-2 rounded-lg text-sm transition-colors"
                                :class="(child.url || isActive(child.route))
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
            <header class="bg-white border-b border-gray-200 px-4 sm:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <button @click="sidebarAbierto = true" class="lg:hidden text-gray-500 hover:text-gray-700 shrink-0">
                        <i class="pi pi-bars text-xl"></i>
                    </button>
                    <div class="min-w-0">
                        <h1 class="text-lg sm:text-xl font-serif font-semibold text-gray-800 truncate">
                            <slot name="title">Panel de Administrador</slot>
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-400 truncate">
                            <slot name="breadcrumb">Dashboard</slot>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3 sm:gap-6 shrink-0">
                    <!-- Notificaciones -->
                    <div class="relative">
                        <button
                            @click="toggleNotificaciones"
                            class="relative text-gray-400 hover:text-gray-600"
                        >
                            <i class="pi pi-bell text-xl"></i>
                            <span
                                v-if="badges.notificaciones > 0"
                                class="absolute -top-1.5 -right-1.5 bg-brand text-white text-[10px] font-semibold rounded-full w-4.5 h-4.5 min-w-[18px] min-h-[18px] flex items-center justify-center"
                            >
                                {{ badges.notificaciones }}
                            </span>
                        </button>

                        <!-- Overlay para cerrar al hacer click fuera -->
                        <div
                            v-if="notificacionesAbiertas"
                            @click="cerrarNotificaciones"
                            class="fixed inset-0 z-40"
                        ></div>

                        <!-- Panel de notificaciones -->
                        <div
                            v-if="notificacionesAbiertas"
                            class="absolute right-0 top-full mt-3 w-80 bg-white rounded-2xl border border-gray-100 shadow-lg z-50 overflow-hidden"
                        >
                            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                                <h3 class="font-serif font-semibold text-gray-800">Notificaciones</h3>
                                <span
                                    class="text-[11px] font-semibold px-2.5 py-1 rounded-full"
                                    :class="badges.notificaciones > 0 ? 'bg-brand/10 text-brand' : 'bg-gray-100 text-gray-400'"
                                >
                                    {{ badges.notificaciones }} nuevas
                                </span>
                            </div>

                            <!-- Estado vacío -->
                            <div
                                v-if="!notificaciones.length"
                                class="flex flex-col items-center justify-center py-12 px-6 text-center"
                            >
                                <i class="pi pi-bell-slash text-gray-300" style="font-size: 2.25rem"></i>
                                <p class="text-gray-400 text-sm mt-3">No tienes notificaciones pendientes</p>
                            </div>

                            <!-- Lista de notificaciones -->
                            <div v-else class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                                <Link
                                    v-for="n in notificaciones"
                                    :key="n.id"
                                    :href="n.route ? route(n.route) : '#'"
                                    @click="cerrarNotificaciones"
                                    class="block px-5 py-3 hover:bg-gray-50 transition-colors cursor-pointer"
                                >
                                    <p class="text-sm text-gray-700 font-medium">{{ n.titulo }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ n.mensaje }}</p>
                                    <p class="text-[11px] text-gray-300 mt-1">{{ n.fecha }}</p>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-full bg-brand/10 flex items-center justify-center text-brand shrink-0">
                            <i class="pi pi-user text-lg"></i>
                        </div>
                        <div class="text-sm leading-tight hidden sm:block">
                            <p class="font-semibold text-gray-800">{{ admin?.nombre || 'Administrador' }}</p>
                            <p class="text-brand text-xs font-medium">{{ admin?.rol === 'super_admin' ? 'Super Admin' : 'Admin' }}</p>
                        </div>
                        <i class="pi pi-chevron-down text-gray-300 text-xs ml-1 hidden sm:inline"></i>
                    </div>
                </div>
            </header>

            <!-- Slot principal -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-x-hidden">
                <slot />
            </main>
        </div>
    </div>
</template>