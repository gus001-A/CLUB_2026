<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, reactive, ref, onMounted, onUnmounted } from 'vue';
import { useToast } from '@/composables/useToast';
import ToastNotification from '@/Components/ToastNotification.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const page = usePage();
const admin = computed(() => page.props.auth?.admin);
const badges = computed(() => page.props.badges || { invitacionesPendientes: 0, notificaciones: 0 });
const toast = useToast();

// Estado del sidebar móvil / tablet (< xl)
const sidebarOpen = ref(false);

// Estado y control del Dropdown de Notificaciones
const showNotifications = ref(false);
const notificationDropdownRef = ref(null);

// Estado y control del Dropdown de Perfil
const showProfileMenu = ref(false);
const profileDropdownRef = ref(null);

const handleClickOutside = (event) => {
    if (notificationDropdownRef.value && !notificationDropdownRef.value.contains(event.target)) {
        showNotifications.value = false;
    }
    if (profileDropdownRef.value && !profileDropdownRef.value.contains(event.target)) {
        showProfileMenu.value = false;
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));

// Filtramos únicamente las notificaciones que están activas según los badges
const notificacionesActivas = computed(() => {
    const lista = [];

    // Notificaciones relevantes para el Admin:
    // 1. Invitaciones ACEPTADAS recientemente por el usuario
    if (badges.value.invitacionesAceptadas > 0) {
        lista.push({
            id: 'inv_aceptada',
            title: 'Invitación aceptada',
            message: `Un invitado ha aceptado tu invitación.`,
            time: 'Reciente',
            icon: 'pi-check-circle',
            route: 'admin.invitaciones.index'
        });
    }

    // 2. Invitaciones EXPIRADAS
    if (badges.value.invitacionesExpiradas > 0) {
        lista.push({
            id: 'inv_expirada',
            title: 'Invitación expirada',
            message: `Una invitación ha superado el límite de tiempo.`,
            time: 'Expiró',
            icon: 'pi-clock',
            route: 'admin.invitaciones.index'
        });
    }

    // 3. Mensajes de usuarios
    if (badges.value.mensajesNuevos > 0) {
        lista.push({
            id: 'mensajes',
            title: 'Nuevos mensajes',
            message: `Tienes mensajes sin leer en tu bandeja.`,
            time: 'Reciente',
            icon: 'pi-comments',
            route: 'admin.mensajes.index'
        });
    }

    return lista;
});

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

// abiertos guarda SOLO los clics manuales del usuario.
// Si el grupo está activo por la ruta actual, se muestra abierto SIEMPRE.
const abiertos = reactive({});

function estaAbierto(link) {
    if (grupoActivo(link)) return true;
    return !!abiertos[link.name];
}

function toggleGrupo(link) {
    abiertos[link.name] = !estaAbierto(link);
}

function logout() {
    router.post(route('admin.logout'));
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex relative overflow-x-hidden">
        <ToastNotification />
        <ConfirmModal />

        <!-- Overlay de fondo para pantallas pequeñas/medianas -->
        <div v-if="sidebarOpen" @click="sidebarOpen = false"
            class="fixed inset-0 bg-gray-900/50 z-40 xl:hidden transition-opacity"></div>

        <!-- Sidebar Adaptativo -->
        <aside
            class="fixed xl:static inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex flex-col shrink-0 transform transition-transform duration-300 ease-in-out"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full xl:translate-x-0'">
            <div class="px-6 py-5 flex items-center justify-between border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <span class="text-brand text-3xl leading-none">♥</span>
                    <div class="leading-tight">
                        <p class="font-serif font-semibold text-gray-800 text-lg">Club de</p>
                        <p class="font-serif font-semibold text-brand text-lg italic -mt-1">Fantasías</p>
                    </div>
                </div>
                <!-- Botón para cerrar en pantallas pequeñas -->
                <button @click="sidebarOpen = false" class="xl:hidden text-gray-400 hover:text-gray-600">
                    <i class="pi pi-times text-lg"></i>
                </button>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <template v-for="link in links" :key="link.name">
                    <!-- Link simple -->
                    <Link v-if="!link.children" :href="route(link.route)" @click="sidebarOpen = false"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                        :class="isActive(link.route)
                            ? 'bg-brand text-white'
                            : 'text-gray-600 hover:bg-gray-100'">
                        <i class="pi text-base" :class="link.icon"></i>
                        <span class="flex-1">{{ link.name }}</span>
                        <span v-if="link.name === 'Invitaciones' ? badges.invitacionesPendientes > 0 : link.badge"
                            class="bg-brand text-white text-[11px] font-semibold rounded-full w-5 h-5 flex items-center justify-center"
                            :class="isActive(link.route) ? 'bg-white/25' : ''">
                            {{ link.name === 'Invitaciones' ? badges.invitacionesPendientes : link.badge }}
                        </span>
                        <i v-else-if="link.chevron" class="pi pi-chevron-right text-xs opacity-50"></i>
                    </Link>

                    <!-- Link con submenú -->
                    <div v-else>
                        <button type="button" @click="toggleGrupo(link)"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
                            :class="grupoActivo(link) && !estaAbierto(link)
                                ? 'bg-brand text-white'
                                : 'text-gray-600 hover:bg-gray-100'">
                            <i class="pi text-base" :class="link.icon"></i>
                            <span class="flex-1 text-left">{{ link.name }}</span>
                            <span v-if="link.name === 'Invitaciones' && badges.invitacionesPendientes > 0"
                                class="bg-brand text-white text-[11px] font-semibold rounded-full w-5 h-5 flex items-center justify-center"
                                :class="grupoActivo(link) && !estaAbierto(link) ? 'bg-white/25' : ''">
                                {{ badges.invitacionesPendientes }}
                            </span>
                            <i class="pi text-xs transition-transform"
                                :class="estaAbierto(link) ? 'pi-chevron-down' : 'pi-chevron-right'"></i>
                        </button>

                        <div v-show="estaAbierto(link)" class="mt-1 ml-4 pl-4 border-l border-gray-100 space-y-0.5">
                            <Link v-for="child in link.children" :key="child.name" :href="child.url || route(child.route)"
                                @click="sidebarOpen = false"
                                class="block px-3 py-2 rounded-lg text-sm transition-colors"
                                :class="(child.url || isActive(child.route))
                                    ? 'text-brand font-semibold bg-brand/5'
                                    : 'text-gray-500 hover:bg-gray-100'">
                                {{ child.name }}
                            </Link>
                        </div>
                    </div>
                </template>
            </nav>

            <div class="p-3 border-t border-gray-100">
                <button @click="logout"
                    class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium text-brand border border-brand/40 hover:bg-brand/5">
                    <i class="pi pi-sign-out"></i>
                    Cerrar sesión
                </button>
            </div>
        </aside>

        <!-- Contenido -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="bg-white border-b border-gray-200 px-4 sm:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <!-- Botón Menú Hamburguesa -->
                    <button @click="sidebarOpen = !sidebarOpen"
                        class="xl:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 focus:outline-none">
                        <i class="pi pi-bars text-xl"></i>
                    </button>

                    <div>
                        <h1 class="text-lg sm:text-xl font-serif font-semibold text-gray-800">
                            <slot name="title">Panel de Administrador</slot>
                        </h1>
                        <p class="text-xs sm:text-sm text-gray-400">
                            <slot name="breadcrumb">Dashboard</slot>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 sm:gap-6">
                    <!-- CONTENEDOR CAMPANITA CON DROPDOWN -->
                    <div class="relative" ref="notificationDropdownRef">
                        <button @click="showNotifications = !showNotifications"
                            class="relative p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-full transition-colors focus:outline-none">
                            <i class="pi pi-bell text-xl"></i>
                            <span v-if="badges.notificaciones > 0"
                                class="absolute top-1 right-1 bg-brand text-white text-[10px] font-semibold rounded-full w-4.5 h-4.5 min-w-[18px] min-h-[18px] flex items-center justify-center">
                                {{ badges.notificaciones }}
                            </span>
                        </button>

                        <!-- DROPDOWN DESPLEGABLE DE NOTIFICACIONES -->
                        <div v-if="showNotifications"
                            class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 overflow-hidden">
                            <!-- Cabecera -->
                            <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="font-semibold text-sm text-gray-800">Notificaciones</h3>
                                <span class="text-[11px] bg-brand/10 text-brand px-2.5 py-0.5 rounded-full font-medium">
                                    {{ notificacionesActivas.length }} nuevas
                                </span>
                            </div>

                            <!-- Lista de Notificaciones Activas -->
                            <div class="max-h-72 overflow-y-auto divide-y divide-gray-50">
                                <template v-if="notificacionesActivas.length > 0">
                                    <Link v-for="item in notificacionesActivas" :key="item.id" :href="route(item.route)"
                                        @click="showNotifications = false"
                                        class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors">
                                        <!-- Cápsula / Círculo del Ícono -->
                                        <div
                                            class="!w-10 !h-10 !min-w-[40px] !min-h-[40px] rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0">
                                            <i class="pi text-sm" :class="item.icon"></i>
                                        </div>

                                        <!-- Detalles de la Notificación -->
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-gray-800 truncate">{{ item.title }}</p>
                                            <p class="text-[11px] text-gray-500 line-clamp-2 mt-0.5">{{ item.message }}</p>
                                            <span class="text-[10px] text-gray-400 mt-1 block">{{ item.time }}</span>
                                        </div>
                                    </Link>
                                </template>

                                <!-- Estado Vacío -->
                                <div v-else class="py-8 px-4 text-center">
                                    <i class="pi pi-bell-slash text-2xl text-gray-300 mb-2 block"></i>
                                    <p class="text-xs text-gray-400 font-medium">No tienes notificaciones pendientes</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Perfil de usuario con Dropdown -->
                    <div class="relative" ref="profileDropdownRef">
                        <button @click="showProfileMenu = !showProfileMenu"
                            class="flex items-center gap-2.5 focus:outline-none">
                            <div class="rounded-full bg-brand/10 flex items-center justify-center text-brand shrink-0"
                                style="width:40px;height:40px;min-width:40px;min-height:40px">
                                <i class="pi pi-user text-lg"></i>
                            </div>
                            <div class="text-sm leading-tight hidden sm:block text-left">
                                <p class="font-semibold text-gray-800">{{ admin?.nombre || 'Administrador' }}</p>
                                <p class="text-brand text-xs font-medium">{{ admin?.rol === 'super_admin' ? 'Super Admin' : 'Admin' }}</p>
                            </div>
                            <i class="pi pi-chevron-down text-gray-300 text-xs ml-1 hidden sm:block transition-transform"
                                :class="showProfileMenu ? 'rotate-180' : ''"></i>
                        </button>

                        <!-- DROPDOWN DESPLEGABLE DE PERFIL -->
                        <div v-if="showProfileMenu"
                            class="absolute right-0 mt-3 w-64 max-w-[calc(100vw-1.5rem)] bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 overflow-hidden">

                            <!-- Cabecera con avatar y nombre -->
                            <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100">
                                <div class="rounded-full bg-brand/10 flex items-center justify-center text-brand shrink-0"
                                    style="width:44px;height:44px;min-width:44px;min-height:44px">
                                    <i class="pi pi-user text-lg"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-semibold text-gray-800 text-sm truncate">{{ admin?.nombre || 'Administrador' }}</p>
                                    <p class="text-xs text-brand font-medium flex items-center gap-1 truncate">
                                        <i class="pi pi-verified text-[10px] shrink-0"></i>
                                        <span class="truncate">{{ admin?.rol === 'super_admin' ? 'Super Admin' : 'Admin' }}</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Opciones -->
                            <div class="py-1.5">
                                <a href="#" @click.prevent="showProfileMenu = false"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                                    <i class="pi pi-user text-sm"></i>
                                    Mi perfil
                                </a>
                                <Link :href="route('admin.configuracion.index')" @click="showProfileMenu = false"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors"
                                    :class="isActive('admin.configuracion.index') ? 'bg-brand/5 text-brand font-semibold' : 'text-gray-600 hover:bg-gray-50'">
                                    <i class="pi pi-cog text-sm"></i>
                                    Configuración
                                </Link>
                            </div>

                            <!-- Cerrar sesión -->
                            <div class="border-t border-gray-100 pt-1.5">
                                <button @click="logout"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="pi pi-sign-out text-sm"></i>
                                    Cerrar sesión
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Slot principal -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>