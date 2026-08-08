<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps({
    perfil: Object,
    esSuperAdmin: Boolean,
    administradores: Array,
    sesionesActivas: Array,
    registroActividad: Array,
    notificaciones: Object,
    permisosRoles: Object, // { roles: ['admin','moderador','soporte'], permisos: [{ nombre: 'Usuarios', clave: 'usuarios' }, ...], matriz: { admin: { usuarios: true, ... }, moderador: {...} } }
});

const toast = useToast();
const { confirm } = useConfirm();

const perfilForm = useForm({
    nombre: props.perfil.nombre,
    email: props.perfil.email,
    telefono: props.perfil.telefono || '',
});

function guardarPerfil() {
    perfilForm.patch(route('admin.seguridad.perfil'));
}

const passwordForm = useForm({
    password_actual: '',
    password_nueva: '',
    password_nueva_confirmation: '',
});

function cambiarPassword() {
    if (passwordForm.password_nueva.length < 8) {
        toast.error('La nueva contraseña debe tener al menos 8 caracteres.');
        return;
    }
    if (passwordForm.password_nueva !== passwordForm.password_nueva_confirmation) {
        toast.error('Las contraseñas nuevas no coinciden.');
        return;
    }

    passwordForm.patch(route('admin.seguridad.password'), {
        onSuccess: () => passwordForm.reset(),
    });
}

const notificacionesForm = useForm({
    alerta_login_nuevo: props.notificaciones?.alerta_login_nuevo ?? true,
    alerta_intentos_fallidos: props.notificaciones?.alerta_intentos_fallidos ?? true,
    resumen_semanal: props.notificaciones?.resumen_semanal ?? false,
});

function guardarNotificaciones() {
    notificacionesForm.patch(route('admin.seguridad.notificaciones'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Preferencias de notificación actualizadas.'),
    });
}

function formatDate(v) {
    if (!v) return 'Nunca';
    return new Date(v).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

const rolLabel = { super_admin: 'Super Admin', admin: 'Admin', moderador: 'Moderador', soporte: 'Soporte' };

async function toggleActivo(admin) {
    const activar = !admin.esta_activo;
    const ok = await confirm(`Se ${activar ? 'reactivará' : 'desactivará'} la cuenta de ${admin.nombre}.`, {
        title: activar ? 'Reactivar administrador' : 'Desactivar administrador',
        confirmLabel: activar ? 'Sí, reactivar' : 'Sí, desactivar',
        danger: !activar,
    });
    if (!ok) return;
    router.post(route('admin.seguridad.toggle-activo', admin.id), {}, { preserveScroll: true });
}

// --- Sesiones activas ---
const dispositivoIconos = { desktop: 'pi-desktop', mobile: 'pi-mobile', tablet: 'pi-tablet' };

async function cerrarSesion(s) {
    const ok = await confirm(`Se cerrará la sesión en ${s.dispositivo} (${s.ubicacion || s.ip}).`, {
        title: 'Cerrar sesión',
        confirmLabel: 'Sí, cerrar sesión',
        danger: true,
    });
    if (!ok) return;
    router.delete(route('admin.seguridad.sesiones.destroy', s.id), { preserveScroll: true });
}

async function cerrarTodasSesiones() {
    const ok = await confirm('Se cerrarán todas las demás sesiones activas, excepto esta.', {
        title: 'Cerrar todas las sesiones',
        confirmLabel: 'Sí, cerrar todas',
        danger: true,
    });
    if (!ok) return;
    router.post(route('admin.seguridad.sesiones.destroy-all'), {}, { preserveScroll: true });
}

// --- Registro de actividad ---
const accionIconos = {
    usuario_eliminado: 'pi-user-minus',
    usuario_bloqueado: 'pi-lock',
    pago_aprobado: 'pi-check-circle',
    pago_reembolsado: 'pi-replay',
    contenido_publicado: 'pi-upload',
    login: 'pi-sign-in',
    perfil_actualizado: 'pi-user-edit',
};

// --- Permisos y roles ---
const rolColorHeader = { admin: 'text-brand', moderador: 'text-blue-600', soporte: 'text-gray-600' };
</script>

<template>
    <Head title="Seguridad" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Dashboard &gt; Seguridad</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- Fila 1: Mi cuenta | Contraseña | Notificaciones de Seguridad -->
            <div class="admin-two-col-grid gap-6 mb-6 w-full">

                <!-- Mi cuenta -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                        <div class="rounded-xl bg-gradient-to-br from-brand to-brand-dark text-white flex items-center justify-center shrink-0" style="width:44px;height:44px">
                            <i class="pi pi-user text-lg"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-gray-900">Mi cuenta</h2>
                            <p class="text-xs text-gray-400">{{ rolLabel[perfil.rol] ?? perfil.rol }}</p>
                        </div>
                    </div>

                    <form @submit.prevent="guardarPerfil" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre</label>
                            <input v-model="perfilForm.nombre" type="text" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand" />
                            <p v-if="perfilForm.errors.nombre" class="text-red-600 text-xs mt-1">{{ perfilForm.errors.nombre }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico</label>
                            <input v-model="perfilForm.email" type="email" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand" />
                            <p v-if="perfilForm.errors.email" class="text-red-600 text-xs mt-1">{{ perfilForm.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Teléfono</label>
                            <input v-model="perfilForm.telefono" type="text" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand" />
                        </div>
                        <button type="submit" :disabled="perfilForm.processing" class="bg-brand hover:bg-brand-dark text-white text-sm font-medium px-5 py-2.5 rounded-xl disabled:opacity-50 shadow-sm">
                            Guardar cambios
                        </button>
                    </form>

                    <div class="mt-5 pt-4 border-t border-gray-100 text-sm space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Último acceso</span>
                            <span class="text-gray-700">{{ formatDate(perfil.ultimo_acceso_en) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Cuenta creada</span>
                            <span class="text-gray-700">{{ formatDate(perfil.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Contraseña -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center gap-3 pb-4 mb-5 border-b border-gray-100">
                        <div class="rounded-xl bg-gradient-to-br from-brand to-brand-dark text-white flex items-center justify-center shrink-0" style="width:44px;height:44px">
                            <i class="pi pi-lock text-lg"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-gray-900">Contraseña</h2>
                            <p class="text-xs text-gray-400">Actualiza tu clave de acceso</p>
                        </div>
                    </div>

                    <form @submit.prevent="cambiarPassword" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Contraseña actual</label>
                            <input v-model="passwordForm.password_actual" type="password" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand" />
                            <p v-if="passwordForm.errors.password_actual" class="text-red-600 text-xs mt-1">{{ passwordForm.errors.password_actual }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Contraseña nueva</label>
                            <input v-model="passwordForm.password_nueva" type="password" placeholder="Mínimo 8 caracteres" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirmar contraseña nueva</label>
                            <input v-model="passwordForm.password_nueva_confirmation" type="password" class="w-full rounded-xl border-gray-300 text-sm px-3 py-2.5 focus:border-brand focus:ring-brand" />
                        </div>
                        <button type="submit" :disabled="passwordForm.processing" class="bg-brand hover:bg-brand-dark text-white text-sm font-medium px-5 py-2.5 rounded-xl disabled:opacity-50 shadow-sm">
                            Actualizar contraseña
                        </button>
                    </form>
                </div>
            </div>

            <!-- Fila 2: Sesiones Activas | Registro de Actividad -->
            <div class="admin-two-col-grid gap-6 mb-6 w-full">

                <!-- Sesiones Activas -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-3">
                                <div class="rounded-xl bg-gradient-to-br from-brand to-brand-dark text-white flex items-center justify-center shrink-0" style="width:44px;height:44px">
                                    <i class="pi pi-desktop text-lg"></i>
                                </div>
                                <div>
                                    <h2 class="font-semibold text-gray-900">Sesiones Activas</h2>
                                    <p class="text-xs text-gray-400">{{ sesionesActivas?.length || 0 }} dispositivos conectados</p>
                                </div>
                            </div>
                            <button v-if="sesionesActivas?.length > 1" @click="cerrarTodasSesiones" type="button"
                                class="text-xs font-semibold text-red-600 hover:underline shrink-0">
                                Cerrar todas
                            </button>
                        </div>

                        <ul class="space-y-3">
                            <li v-for="s in sesionesActivas" :key="s.id" class="flex items-center justify-between gap-3 p-3 rounded-xl border border-gray-100">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0" style="width:40px;height:40px">
                                        <i class="pi" :class="dispositivoIconos[s.tipo] || 'pi-desktop'"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 truncate">
                                            {{ s.dispositivo }}
                                            <span v-if="s.es_actual" class="ml-1 text-[10px] font-semibold text-green-600">· Este dispositivo</span>
                                        </p>
                                        <p class="text-xs text-gray-400 truncate">{{ s.navegador }} · {{ s.ubicacion || s.ip }}</p>
                                        <p class="text-[11px] text-gray-400">Activo: {{ formatDate(s.ultima_actividad) }}</p>
                                    </div>
                                </div>
                                <button v-if="!s.es_actual" @click="cerrarSesion(s)" title="Cerrar sesión"
                                    class="w-8 h-8 min-w-[32px] max-w-[32px] rounded-lg border border-gray-200 text-red-600 hover:bg-red-50 transition flex items-center justify-center shrink-0">
                                    <i class="pi pi-times text-xs"></i>
                                </button>
                            </li>
                            <li v-if="!sesionesActivas?.length" class="text-center py-8 text-gray-400 text-xs">
                                No hay sesiones activas registradas.
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Registro de Actividad -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-5">
                            <div class="rounded-xl bg-gradient-to-br from-brand to-brand-dark text-white flex items-center justify-center shrink-0" style="width:44px;height:44px">
                                <i class="pi pi-history text-lg"></i>
                            </div>
                            <div>
                                <h2 class="font-semibold text-gray-900">Registro de Actividad</h2>
                                <p class="text-xs text-gray-400">Últimas acciones realizadas en el panel</p>
                            </div>
                        </div>

                        <ul class="space-y-3.5">
                            <li v-for="r in registroActividad" :key="r.id" class="flex items-start gap-3">
                                <div class="rounded-full bg-red-50 text-brand flex items-center justify-center shrink-0 text-xs" style="width:36px;height:36px;min-width:36px">
                                    <i class="pi" :class="accionIconos[r.tipo] || 'pi-bolt'"></i>
                                </div>
                                <div class="text-xs">
                                    <p class="text-gray-800 leading-snug">
                                        <span class="font-semibold">{{ r.admin }}</span> {{ r.detalle }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ formatDate(r.fecha) }}</p>
                                </div>
                            </li>
                            <li v-if="!registroActividad?.length" class="text-center py-8 text-gray-400 text-xs">
                                Sin actividad registrada todavía.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Fila 3 (solo super_admin): Administradores | Permisos y Roles -->
            <div v-if="esSuperAdmin" class="admin-two-col-grid gap-6 w-full">

                <!-- Administradores -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center gap-3 pb-4 mb-4 border-b border-gray-100">
                        <div class="rounded-xl bg-gradient-to-br from-brand to-brand-dark text-white flex items-center justify-center shrink-0" style="width:44px;height:44px">
                            <i class="pi pi-shield text-lg"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-gray-900">Administradores</h2>
                            <p class="text-xs text-gray-400">{{ administradores.length }} cuentas</p>
                        </div>
                    </div>

                    <ul class="space-y-3">
                        <li v-for="a in administradores" :key="a.id" class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ a.nombre }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ a.email }} · {{ rolLabel[a.rol] ?? a.rol }}</p>
                                <p class="text-[11px] text-gray-400">Últ. acceso: {{ formatDate(a.ultimo_acceso_en) }}</p>
                            </div>
                            <button @click="toggleActivo(a)" class="text-xs font-medium px-2.5 py-1 rounded-full shrink-0"
                                :class="a.esta_activo ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-500'">
                                {{ a.esta_activo ? 'Activo' : 'Inactivo' }}
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Permisos y Roles -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-center gap-3 pb-4 mb-4 border-b border-gray-100">
                        <div class="rounded-xl bg-gradient-to-br from-brand to-brand-dark text-white flex items-center justify-center shrink-0" style="width:44px;height:44px">
                            <i class="pi pi-sitemap text-lg"></i>
                        </div>
                        <div>
                            <h2 class="font-semibold text-gray-900">Permisos y Roles</h2>
                            <p class="text-xs text-gray-400">Qué puede hacer cada rol en el panel</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto" v-if="permisosRoles?.permisos?.length">
                        <table class="w-full text-sm min-w-[420px]">
                            <thead>
                                <tr class="text-left text-gray-400 border-b border-gray-100 text-xs uppercase tracking-wider">
                                    <th class="pb-2 font-semibold">Módulo</th>
                                    <th v-for="rol in permisosRoles.roles" :key="rol" class="pb-2 font-semibold text-center" :class="rolColorHeader[rol]">
                                        {{ rolLabel[rol] ?? rol }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="p in permisosRoles.permisos" :key="p.clave">
                                    <td class="py-2.5 text-gray-700">{{ p.nombre }}</td>
                                    <td v-for="rol in permisosRoles.roles" :key="rol" class="py-2.5 text-center">
                                        <i v-if="permisosRoles.matriz[rol]?.[p.clave]" class="pi pi-check text-green-600"></i>
                                        <i v-else class="pi pi-times text-gray-300"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="text-center py-8 text-gray-400 text-xs">Aún no hay permisos configurados.</p>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>