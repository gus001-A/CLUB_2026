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
const perfilAbierto = ref(false);

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
            ...(route().current('admin.contenido.show')
                ? [{ name: 'Ver Contenido', url: window.location.pathname }]
                : []),
        ],
    },
    { name: 'Pedidos', route: 'admin.shop.index', icon: 'pi-shopping-bag' },
    {
        name: 'Productos',
        icon: 'pi-tags',
        children: [
            { name: 'Todos los productos', route: 'admin.productos.index' },
            { name: 'Nuevo producto', route: 'admin.productos.create' },
            ...(route().current('admin.productos.edit')
                ? [{ name: 'Editar Producto', url: window.location.pathname }]
                : []),
        ],
    },
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
    perfilAbierto.value = false;
}

function cerrarNotificaciones() {
    notificacionesAbiertas.value = false;
}

function togglePerfil() {
    perfilAbierto.value = !perfilAbierto.value;
    notificacionesAbiertas.value = false;
}

function cerrarPerfil() {
    perfilAbierto.value = false;
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
                    <img src="/images/LOGO.png" alt="Club de Fantasías" style="height:48px;width:auto;object-fit:contain" />
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
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors"
                        :class="isActive(link.route)
                            ? 'bg-gradient-to-br from-brand to-brand-dark text-white shadow-sm'
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
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors"
                            :class="grupoActivo(link) && !estaAbierto(link)
                                ? 'bg-gradient-to-br from-brand to-brand-dark text-white shadow-sm'
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
                                class="block px-3 py-2 rounded-xl text-sm transition-colors"
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
                    class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-brand border border-brand/40 hover:bg-brand/5 transition-colors"
                >
                    <i class="pi pi-sign-out"></i>
                    Cerrar sesión
                </button>
            </div>
        </aside>

        <!-- Contenido -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="admin-topbar px-4 sm:px-8 py-4">
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
                            title="Notificaciones"
                            class="admin-icon-btn"
                            style="width:42px;height:42px;font-size:1.15rem"
                        >
                            <i class="pi pi-bell"></i>
                            <span
                                v-if="badges.notificaciones > 0"
                                class="admin-icon-badge text-[10px]"
                                style="width:18px;height:18px;top:-2px;right:-2px"
                            >
                                {{ badges.notificaciones }}
                            </span>
                        </button>

                        <!-- Overlay para cerrar al hacer click fuera -->
                        <div
                            v-if="notificacionesAbiertas"
                            @click="cerrarNotificaciones"
                            class="fixed inset-0 z-30"
                        ></div>

                        <!-- Panel de notificaciones -->
                        <Transition name="admin-dropdown">
                            <div v-if="notificacionesAbiertas" class="admin-dropdown" style="width:320px">
                                <div class="admin-dropdown-header">
                                    <div class="min-w-0">
                                        <h3 class="font-serif font-semibold text-gray-800">Notificaciones</h3>
                                        <span
                                            class="inline-block mt-1 text-[11px] font-semibold px-2.5 py-1 rounded-full"
                                            :class="badges.notificaciones > 0 ? 'bg-brand/10 text-brand' : 'bg-gray-100 text-gray-400'"
                                        >
                                            {{ badges.notificaciones }} nuevas
                                        </span>
                                    </div>
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
                        </Transition>
                    </div>

                    <!-- Perfil -->
                    <div class="relative">
                        <button type="button" @click="togglePerfil" class="admin-user-chip" style="padding:0.25rem 0.8rem 0.25rem 0.25rem">
                            <div class="relative shrink-0">
                                <div class="rounded-full bg-brand/10 flex items-center justify-center text-brand overflow-hidden" style="width:40px;height:40px">
                                    <img v-if="admin?.foto_perfil_url" :src="admin.foto_perfil_url" class="w-full h-full object-cover" />
                                    <i v-else class="pi pi-user text-lg"></i>
                                </div>
                                <span
                                    class="absolute rounded-full flex items-center justify-center text-white"
                                    style="left:26px;bottom:-2px;width:16px;height:16px;font-size:0.55rem;background:linear-gradient(135deg,#1fbf5c 0%,#34d399 100%);border:2px solid #fff"
                                >
                                    <i class="pi pi-check"></i>
                                </span>
                            </div>
                            <div class="text-sm leading-tight hidden sm:block text-left">
                                <p class="font-bold text-gray-800" style="letter-spacing:-0.01em">{{ admin?.nombre || 'Administrador' }}</p>
                                <p class="text-brand font-medium" style="font-size:0.65rem">{{ admin?.rol === 'super_admin' ? 'Super Admin' : 'Admin' }}</p>
                            </div>
                            <i class="pi text-gray-300 text-xs ml-1 hidden sm:inline transition-transform" :class="perfilAbierto ? 'pi-chevron-up' : 'pi-chevron-down'"></i>
                        </button>

                        <!-- Overlay para cerrar al hacer click fuera -->
                        <div v-if="perfilAbierto" @click="cerrarPerfil" class="fixed inset-0 z-30"></div>

                        <!-- Menú desplegable -->
                        <Transition name="admin-dropdown">
                            <div v-if="perfilAbierto" class="admin-dropdown" style="width:280px">
                                <div class="admin-dropdown-header">
                                    <div class="rounded-full bg-brand/10 flex items-center justify-center text-brand shrink-0 overflow-hidden" style="width:48px;height:48px">
                                        <img v-if="admin?.foto_perfil_url" :src="admin.foto_perfil_url" class="w-full h-full object-cover" />
                                        <i v-else class="pi pi-user text-xl"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-gray-900 text-sm truncate" style="letter-spacing:-0.01em">{{ admin?.nombre || 'Administrador' }}</p>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ admin?.email }}</p>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold mt-1" style="color:var(--success)">
                                            <i class="pi pi-check-circle" style="font-size:0.7rem"></i> {{ admin?.rol === 'super_admin' ? 'Super Admin' : 'Admin' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="py-1.5">
                                    <Link :href="route('admin.seguridad.index')" @click="cerrarPerfil" class="admin-dropdown-item">
                                        <i class="pi pi-user text-sm"></i>
                                        Mi perfil
                                    </Link>
                                    <Link :href="route('admin.configuracion.index')" @click="cerrarPerfil" class="admin-dropdown-item">
                                        <i class="pi pi-cog text-sm"></i>
                                        Configuración
                                    </Link>
                                </div>

                                <div class="border-t py-1.5" style="border-color:var(--line)">
                                    <button type="button" @click="logout" class="admin-dropdown-item admin-dropdown-item--danger">
                                        <i class="pi pi-sign-out text-sm"></i>
                                        Cerrar sesión
                                    </button>
                                </div>
                            </div>
                        </Transition>
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