<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps({
    cuenta: Object,
    esSuperAdmin: Boolean,
    administradores: Array,
    sesionesActivas: Array,
    registroActividad: Array,
    permisosRoles: Object, // { roles: [...], permisos: [{ nombre, clave }], matriz: { rol: { clave: true } } }
});

const toast = useToast();
const { confirm } = useConfirm();
const page = usePage();

function formatDate(v) {
    if (!v) return 'Nunca';
    return new Date(v).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

/* ---------------------------------------------------------------
 * Correo electrónico
 * --------------------------------------------------------------- */
const emailForm = useForm({
    email: props.cuenta.email,
    password_actual: '',
});

function guardarEmail() {
    emailForm.patch(route('admin.seguridad.email'), {
        preserveScroll: true,
        onSuccess: () => {
            emailForm.password_actual = '';
            toast.success('Correo actualizado correctamente.');
        },
    });
}

/* ---------------------------------------------------------------
 * Contraseña
 * --------------------------------------------------------------- */
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
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            toast.success('Contraseña actualizada correctamente.');
        },
    });
}

/* ---------------------------------------------------------------
 * Administradores (solo super_admin)
 * --------------------------------------------------------------- */
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

/* ---------------------------------------------------------------
 * Sesiones activas / Registro de actividad
 * --------------------------------------------------------------- */
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

const accionIconos = {
    usuario_eliminado: 'pi-user-minus',
    usuario_bloqueado: 'pi-lock',
    pago_aprobado: 'pi-check-circle',
    pago_reembolsado: 'pi-replay',
    contenido_publicado: 'pi-upload',
    login: 'pi-sign-in',
    perfil_actualizado: 'pi-user-edit',
};

const rolColorHeader = { admin: 'text-brand', moderador: 'text-blue-600', soporte: 'text-gray-600' };
</script>

<template>
    <Head title="Seguridad" />

    <AdminLayout>
        <template #title>Panel de Administrador</template>
        <template #breadcrumb>Dashboard &gt; Seguridad</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">

            <!-- Fila 1: Correo | Contraseña -->
            <div class="admin-two-col-grid gap-6 mb-6 w-full">

                <!-- Correo electrónico -->
                <div class="admin-card overflow-hidden">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-envelope text-brand"></i> Correo electrónico</span>
                        <span class="text-xs" style="color:var(--muted)">{{ rolLabel[cuenta.rol] ?? cuenta.rol }}</span>
                    </div>

                    <form @submit.prevent="guardarEmail" class="space-y-4 p-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo</label>
                            <input v-model="emailForm.email" type="email" class="admin-input px-3 py-2.5" />
                            <p v-if="emailForm.errors.email" class="text-red-600 text-xs mt-1">{{ emailForm.errors.email }}</p>
                            <p v-if="!cuenta.email_verificado_en" class="text-amber-600 text-xs mt-1 flex items-center gap-1">
                                <i class="pi pi-exclamation-triangle"></i> Correo sin verificar
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirma tu contraseña actual</label>
                            <input v-model="emailForm.password_actual" type="password" class="admin-input px-3 py-2.5" />
                            <p v-if="emailForm.errors.password_actual" class="text-red-600 text-xs mt-1">{{ emailForm.errors.password_actual }}</p>
                        </div>
                        <button type="submit" :disabled="emailForm.processing" class="admin-btn-primary disabled:opacity-50">
                            Guardar correo
                        </button>
                    </form>

                    <div class="mx-6 mb-6 pt-4 border-t border-gray-100 text-sm space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Último acceso</span>
                            <span class="text-gray-700">{{ formatDate(cuenta.ultimo_acceso_en) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Última IP</span>
                            <span class="text-gray-700">{{ cuenta.ultimo_acceso_ip || '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Cuenta creada</span>
                            <span class="text-gray-700">{{ formatDate(cuenta.created_at) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Contraseña -->
                <div class="admin-card overflow-hidden">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-lock text-brand"></i> Contraseña</span>
                    </div>

                    <form @submit.prevent="cambiarPassword" class="space-y-4 p-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Contraseña actual</label>
                            <input v-model="passwordForm.password_actual" type="password" class="admin-input px-3 py-2.5" />
                            <p v-if="passwordForm.errors.password_actual" class="text-red-600 text-xs mt-1">{{ passwordForm.errors.password_actual }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Contraseña nueva</label>
                            <input v-model="passwordForm.password_nueva" type="password" placeholder="Mínimo 8 caracteres" class="admin-input px-3 py-2.5" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirmar contraseña nueva</label>
                            <input v-model="passwordForm.password_nueva_confirmation" type="password" class="admin-input px-3 py-2.5" />
                        </div>
                        <button type="submit" :disabled="passwordForm.processing" class="admin-btn-primary disabled:opacity-50">
                            Actualizar contraseña
                        </button>
                    </form>
                </div>
            </div>

            <!-- Fila 2: Sesiones Activas | Registro de Actividad -->
            <div class="admin-two-col-grid gap-6 mb-6 w-full">

                <!-- Sesiones Activas -->
                <div class="admin-card overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-desktop text-brand"></i> Sesiones Activas</span>
                            <button v-if="sesionesActivas?.length > 1" @click="cerrarTodasSesiones" type="button"
                                class="text-xs font-semibold text-brand hover:underline shrink-0">
                                Cerrar todas
                            </button>
                        </div>
                        <p class="text-xs px-6 pt-4" style="color:var(--muted)">{{ sesionesActivas?.length || 0 }} dispositivos conectados</p>

                        <ul class="space-y-3 p-6">
                            <li v-for="s in sesionesActivas" :key="s.id" class="flex items-center justify-between gap-3 p-3 rounded-xl border border-gray-100">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="admin-icon-circle" style="width:40px;height:40px">
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
                                <button v-if="!s.es_actual" @click="cerrarSesion(s)" title="Cerrar sesión" class="admin-table-action text-red-600 hover:bg-red-50 shrink-0">
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
                <div class="admin-card overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="admin-card-header">
                            <span class="admin-card-header-title"><i class="pi pi-history text-brand"></i> Registro de Actividad</span>
                        </div>
                        <p class="text-xs px-6 pt-4" style="color:var(--muted)">Últimas acciones realizadas en el panel</p>

                        <ul class="space-y-3.5 p-6">
                            <li v-for="r in registroActividad" :key="r.id" class="flex items-start gap-3">
                                <div class="admin-icon-circle text-xs" style="width:36px;height:36px;min-width:36px">
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
                <div class="admin-card overflow-hidden">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-users text-brand"></i> Administradores</span>
                        <span class="text-xs" style="color:var(--muted)">{{ administradores.length }} cuentas</span>
                    </div>

                    <ul class="space-y-3 p-6">
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
                <div class="admin-card overflow-hidden">
                    <div class="admin-card-header">
                        <span class="admin-card-header-title"><i class="pi pi-sitemap text-brand"></i> Permisos y Roles</span>
                    </div>

                    <div class="overflow-x-auto p-6" v-if="permisosRoles?.permisos?.length">
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