<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    usuariosRecientes: Array,
    cobrosRecientes: Array,
    eventosProximos: Array,
    gestionUsuarios: Array,
    accionesRapidas: Array,
    actividadReciente: Array,
});

function money(v) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(v ?? 0);
}

function formatDate(v) {
    if (!v) return '—';
    return new Date(v).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Bienvenido, Administrador</template>

        <!-- Contenedor Principal que abarca todo el ancho sin miedo -->
        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">
            <!-- KPIs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                <!-- Usuarios -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Usuarios Totales</p>
                        <h2 class="text-3xl font-bold text-gray-900">{{ stats.usuariosTotales }}</h2>
                        <p class="text-brand text-xs mt-2 font-medium">+{{ stats.usuariosNuevosHoy }} nuevos hoy</p>
                    </div>
                    <div class="w-14 h-14 min-w-[56px] max-w-[56px] min-h-[56px] max-h-[56px] flex-none rounded-full bg-red-50 flex items-center justify-center text-brand text-2xl">
                        <i class="pi pi-users"></i>
                    </div>
                </div>

                <!-- Ingresos -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Ingresos Totales</p>
                        <h2 class="text-3xl font-bold text-gray-900">{{ money(stats.ingresosTotales) }}</h2>
                        <p class="text-brand text-xs mt-2 font-medium">&nbsp;</p>
                    </div>
                    <div class="w-14 h-14 min-w-[56px] max-w-[56px] min-h-[56px] max-h-[56px] flex-none rounded-full bg-red-50 flex items-center justify-center text-brand text-2xl">
                        <i class="pi pi-dollar"></i>
                    </div>
                </div>

                <!-- Suscripciones -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Suscripciones Activas</p>
                        <h2 class="text-3xl font-bold text-gray-900">{{ stats.suscripcionesActivas }}</h2>
                        <p class="text-brand text-xs mt-2 font-medium">+{{ stats.suscripcionesNuevasHoy }} nuevas hoy</p>
                    </div>
                    <div class="w-14 h-14 min-w-[56px] max-w-[56px] min-h-[56px] max-h-[56px] flex-none rounded-full bg-red-50 flex items-center justify-center text-brand text-2xl">
                        <i class="pi pi-crown"></i>
                    </div>
                </div>

                <!-- Shop -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm px-6 py-5 flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Ventas en Shop</p>
                        <h2 class="text-3xl font-bold text-gray-900">{{ money(stats.ventasShop) }}</h2>
                        <p class="text-brand text-xs mt-2 font-medium">&nbsp;</p>
                    </div>
                    <div class="w-14 h-14 min-w-[56px] max-w-[56px] min-h-[56px] max-h-[56px] flex-none rounded-full bg-red-50 flex items-center justify-center text-brand text-2xl">
                        <i class="pi pi-shopping-bag"></i>
                    </div>
                </div>
            </div>

            <!-- Fila 1: Gestión de Usuarios + Acciones Rápidas -->
            <div class="grid grid-cols-12 gap-4 mb-6">
                <!-- Gestión de Usuarios -->
                <div class="col-span-12 lg:col-span-8 2xl:col-span-9 bg-white rounded-2xl border border-gray-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <!-- Encabezado -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-6 pt-6">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Gestión de Usuarios</h2>
                                <p class="text-xs text-gray-500 mt-0.5">Administra los usuarios registrados.</p>
                            </div>
                            <Link :href="route('admin.usuarios.index')"
                                class="bg-brand hover:bg-brand-dark text-white text-sm font-medium px-4 py-2.5 rounded-xl flex items-center justify-center gap-2 transition flex-none">
                                <i class="pi pi-plus text-xs"></i>
                                Agregar Usuario
                            </Link>
                        </div>

                        <!-- Filtros -->
                        <div class="grid grid-cols-12 gap-3 px-6 py-4">
                            <div class="col-span-12 md:col-span-6 xl:col-span-5 relative">
                                <i class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="text" placeholder="Buscar usuario..."
                                    class="w-full rounded-xl border-gray-300 pl-10 pr-3 py-2 text-sm focus:border-brand focus:ring-brand">
                            </div>
                            <div class="col-span-6 md:col-span-3 xl:col-span-3">
                                <select class="w-full rounded-xl border-gray-300 py-2 text-sm focus:border-brand focus:ring-brand">
                                    <option>Todos los roles</option>
                                    <option>Usuario</option>
                                    <option>Creador</option>
                                    <option>Admin</option>
                                </select>
                            </div>
                            <div class="col-span-6 md:col-span-3 xl:col-span-4">
                                <select class="w-full rounded-xl border-gray-300 py-2 text-sm focus:border-brand focus:ring-brand">
                                    <option>Todos los estados</option>
                                    <option>Verificado</option>
                                    <option>Pendiente</option>
                                    <option>Bloqueado</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div class="overflow-x-auto w-full">
                            <table class="w-full text-left text-sm table-auto">
                                <thead>
                                    <tr class="border-y border-gray-100 bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider">
                                        <th class="pl-6 pr-4 py-3 font-semibold w-1/4">Usuario</th>
                                        <th class="px-3 py-3 font-semibold w-1/4">Correo</th>
                                        <th class="px-3 py-3 font-semibold">Rol</th>
                                        <th class="px-3 py-3 font-semibold">Estado</th>
                                        <th class="px-3 py-3 font-semibold">Registro</th>
                                        <th class="pl-2 pr-6 py-3 text-center font-semibold">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="u in gestionUsuarios" :key="u.id" class="hover:bg-gray-50/50 transition">
                                        <td class="pl-6 pr-4 py-3.5 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <!-- Avatar blindado -->
                                                <div class="w-9 h-9 min-w-[36px] max-w-[36px] min-h-[36px] max-h-[36px] flex-none rounded-full bg-brand/10 text-brand flex items-center justify-center font-semibold text-sm">
                                                    {{ u.nombre.charAt(0).toUpperCase() }}
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-gray-800 text-sm leading-tight truncate">{{ u.nombre }}</p>
                                                    <p class="text-xs text-gray-400 truncate">@{{ u.apodo }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3.5 text-gray-600 text-xs whitespace-nowrap">{{ u.email }}</td>
                                        <td class="px-3 py-3.5 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold capitalize"
                                                :class="u.rol === 'creador' ? 'bg-brand/10 text-brand' : 'bg-gray-100 text-gray-700'">
                                                {{ u.rol }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3.5 whitespace-nowrap">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold capitalize" :class="{
                                                'bg-green-100 text-green-700': u.estado === 'verificado',
                                                'bg-yellow-100 text-yellow-700': u.estado === 'pendiente' || u.estado === 'incompleto',
                                                'bg-red-100 text-red-700': u.estado === 'bloqueado'
                                            }">
                                                {{ u.estado }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3.5 text-gray-500 text-xs whitespace-nowrap">{{ formatDate(u.created_at) }}</td>
                                        <td class="pl-2 pr-6 py-3.5 whitespace-nowrap">
                                            <div class="flex justify-center items-center gap-1.5">
                                                <!-- Botones de acción blindados -->
                                                <button class="w-8 h-8 min-w-[32px] max-w-[32px] min-h-[32px] max-h-[32px] flex-none rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 transition flex items-center justify-center">
                                                    <i class="pi pi-eye text-xs"></i>
                                                </button>
                                                <button class="w-8 h-8 min-w-[32px] max-w-[32px] min-h-[32px] max-h-[32px] flex-none rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 transition flex items-center justify-center">
                                                    <i class="pi pi-pencil text-xs"></i>
                                                </button>
                                                <button class="w-8 h-8 min-w-[32px] max-w-[32px] min-h-[32px] max-h-[32px] flex-none rounded-lg border border-gray-200 text-red-600 hover:bg-red-50 transition flex items-center justify-center">
                                                    <i class="pi pi-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!gestionUsuarios?.length">
                                        <td colspan="6" class="py-8 text-center text-gray-400 text-xs">
                                            Aún no hay usuarios registrados.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Footer Tabla -->
                    <div class="border-t border-gray-100 py-3 text-center">
                        <Link :href="route('admin.usuarios.index')" class="text-brand font-medium hover:underline text-xs">
                            Ver todos los usuarios
                        </Link>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="col-span-12 lg:col-span-4 2xl:col-span-3 bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <h2 class="font-semibold text-gray-900 text-base mb-3">Acciones Rápidas</h2>
                    <div class="divide-y divide-gray-100">
                        <Link v-for="a in accionesRapidas" :key="a.label" :href="route(a.route)"
                            class="flex items-center gap-3 py-2.5 hover:bg-gray-50/50 transition-colors w-full first:pt-0 last:pb-0 group">
                            <!-- Ícono rápido blindado -->
                            <div class="w-9 h-9 min-w-[36px] max-w-[36px] min-h-[36px] max-h-[36px] flex-none rounded-xl bg-red-50 text-red-600 flex items-center justify-center border border-red-100/50 shadow-sm">
                                <i class="pi text-sm font-bold" :class="a.icon"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-gray-900 leading-tight group-hover:text-brand transition-colors truncate">
                                    {{ a.label }}
                                </p>
                                <p class="text-[11px] text-gray-400 mt-0.5 truncate">
                                    {{ a.desc }}
                                </p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Fila 2: Cobros Recientes + Eventos Próximos + Actividad Reciente -->
            <div class="grid grid-cols-12 gap-4">
                <!-- Cobros Recientes -->
                <div class="col-span-12 lg:col-span-4 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-4 mb-4">
                            <h2 class="font-semibold text-gray-900 text-base truncate">Cobros Recientes</h2>
                            <button class="text-xs font-semibold text-brand hover:underline shrink-0">Ver todos</button>
                        </div>
                        <ul class="space-y-3.5">
                            <li v-for="c in cobrosRecientes" :key="c.id" class="flex items-center gap-3 text-sm">
                                <!-- Ícono de cobro blindado -->
                                <div class="w-8 h-8 min-w-[32px] max-w-[32px] min-h-[32px] max-h-[32px] flex-none rounded-full bg-brand/10 text-brand flex items-center justify-center font-bold text-xs">
                                    $
                                </div>
                                <div class="min-w-0 flex-1 flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="font-medium text-brand text-xs truncate">@{{ c.usuario }}</p>
                                        <p class="text-[11px] text-gray-400 truncate">{{ c.tipo }}</p>
                                    </div>
                                    <div class="text-right flex-none">
                                        <p class="font-bold text-gray-800 text-xs">{{ money(c.monto) }}</p>
                                        <p class="text-[10px] text-gray-400">{{ c.hace_cuanto || 'Hoy' }}</p>
                                    </div>
                                </div>
                            </li>
                            <li v-if="!cobrosRecientes?.length" class="text-gray-400 text-xs py-6 text-center">
                                Sin cobros todavía.
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Eventos Próximos -->
                <div class="col-span-12 lg:col-span-4 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-4 mb-4">
                            <h2 class="font-semibold text-gray-900 text-base truncate">Eventos Próximos</h2>
                            <button class="text-xs font-semibold text-brand hover:underline shrink-0">Ver todos</button>
                        </div>
                        <ul class="space-y-3.5">
                            <li v-for="e in eventosProximos" :key="e.id" class="flex items-center gap-3 text-sm">
                                <!-- Imagen de evento blindada -->
                                <div class="w-12 h-10 min-w-[48px] max-w-[48px] min-h-[40px] max-h-[40px] flex-none rounded-lg bg-gray-100 overflow-hidden border border-gray-100">
                                    <img v-if="e.imagen" :src="e.imagen" :alt="e.nombre" class="w-full h-full object-cover">
                                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                        <i class="pi pi-image text-xs"></i>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1 flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-800 text-xs truncate">{{ e.nombre }}</p>
                                        <p class="text-[11px] text-gray-400 truncate">{{ formatDate(e.fecha) }}</p>
                                    </div>
                                    <span :class="e.en_vivo ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-600'"
                                        class="text-[10px] font-semibold px-2 py-0.5 rounded-md flex-none">
                                        {{ e.en_vivo ? 'En vivo' : 'Programado' }}
                                    </span>
                                </div>
                            </li>
                            <li v-if="!eventosProximos?.length" class="text-gray-400 text-xs py-6 text-center">
                                Sin eventos próximos.
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Actividad Reciente -->
                <div class="col-span-12 lg:col-span-4 bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <h2 class="font-semibold text-gray-900 text-base mb-4 truncate">Actividad Reciente</h2>
                        <ul class="space-y-3.5">
                            <li v-for="(act, i) in actividadReciente" :key="i" class="flex items-start gap-3">
                                <!-- Ícono de actividad blindado -->
                                <div class="w-8 h-8 min-w-[32px] max-w-[32px] min-h-[32px] max-h-[32px] flex-none rounded-xl bg-brand/10 text-brand flex items-center justify-center">
                                    <i class="pi text-xs font-semibold" :class="act.icon || 'pi-bell'"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-gray-700 leading-snug">
                                        <span>{{ act.titulo || act.texto }}</span>
                                        <span v-if="act.destacado" class="font-semibold text-brand ml-1">
                                            {{ act.destacado }}
                                        </span>
                                    </p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">
                                        {{ act.hace_cuanto || formatDate(act.fecha) }}
                                    </p>
                                </div>
                            </li>
                            <li v-if="!actividadReciente?.length" class="text-gray-400 text-xs py-6 text-center">
                                Sin actividad todavía.
                            </li>
                        </ul>
                    </div>
                    <div v-if="actividadReciente?.length" class="mt-4 pt-3 border-t border-gray-100 text-center">
                        <button class="text-xs font-semibold text-brand hover:text-brand-dark transition-colors">
                            Ver toda la actividad
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>