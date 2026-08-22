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

        <div class="admin-reportes-page">

            <!-- Fila 1: Correo | Contraseña -->
            <div class="admin-two-col-grid gap-6 mb-6 w-full items-stretch">

                <!-- Correo electrónico -->
                <div class="admin-cobros-card min-w-0">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-envelope"></i></div>
                                <h3>Correo electrónico</h3>
                            </div>
                            <span class="admin-dash-badge admin-dash-badge--rol-admin">
                                <span class="admin-dash-badge-dot"></span>{{ rolLabel[cuenta.rol] ?? cuenta.rol }}
                            </span>
                        </div>

                        <form @submit.prevent="guardarEmail" class="flex flex-col gap-4" style="padding:1.5rem">
                            <div class="admin-user-field">
                                <label>Correo</label>
                                <input v-model="emailForm.email" type="email" :class="{ 'admin-user-input-error': emailForm.errors.email }" />
                                <p v-if="emailForm.errors.email" class="admin-user-error-text">{{ emailForm.errors.email }}</p>
                                <p v-if="!cuenta.email_verificado_en" class="admin-user-hint" style="color:#D97706" >
                                    <i class="pi pi-exclamation-triangle"></i> Correo sin verificar
                                </p>
                            </div>
                            <div class="admin-user-field">
                                <label>Confirma tu contraseña actual</label>
                                <input v-model="emailForm.password_actual" type="password" :class="{ 'admin-user-input-error': emailForm.errors.password_actual }" />
                                <p v-if="emailForm.errors.password_actual" class="admin-user-error-text">{{ emailForm.errors.password_actual }}</p>
                            </div>
                            <button type="submit" :disabled="emailForm.processing" class="admin-cobros-btn-primary" style="align-self:flex-start">
                                Guardar correo
                            </button>
                        </form>

                        <div class="admin-cobros-summary" style="border-top:1px solid var(--line);padding-top:1rem">
                            <div class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Último acceso</span>
                                <span class="admin-cobros-summary-value">{{ formatDate(cuenta.ultimo_acceso_en) }}</span>
                            </div>
                            <div class="admin-cobros-summary-row">
                                <span class="admin-cobros-summary-label">Cuenta creada</span>
                                <span class="admin-cobros-summary-value">{{ formatDate(cuenta.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contraseña -->
                <div class="admin-cobros-card min-w-0">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-lock"></i></div>
                                <h3>Contraseña</h3>
                            </div>
                        </div>

                        <form @submit.prevent="cambiarPassword" class="flex flex-col gap-4" style="padding:1.5rem">
                            <div class="admin-user-field">
                                <label>Contraseña actual</label>
                                <input v-model="passwordForm.password_actual" type="password" :class="{ 'admin-user-input-error': passwordForm.errors.password_actual }" />
                                <p v-if="passwordForm.errors.password_actual" class="admin-user-error-text">{{ passwordForm.errors.password_actual }}</p>
                            </div>
                            <div class="admin-user-field">
                                <label>Contraseña nueva</label>
                                <input v-model="passwordForm.password_nueva" type="password" placeholder="Mínimo 8 caracteres" />
                            </div>
                            <div class="admin-user-field">
                                <label>Confirmar contraseña nueva</label>
                                <input v-model="passwordForm.password_nueva_confirmation" type="password" />
                            </div>
                            <button type="submit" :disabled="passwordForm.processing" class="admin-cobros-btn-primary" style="align-self:flex-start">
                                Actualizar contraseña
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Fila 2: Sesiones Activas | Registro de Actividad -->
            <div class="admin-two-col-grid gap-6 mb-6 w-full items-stretch">

                <!-- Sesiones Activas -->
                <div class="admin-cobros-card min-w-0">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-desktop"></i></div>
                                <div>
                                    <h3>Sesiones Activas</h3>
                                    <p class="admin-cobros-header-subtitle">{{ sesionesActivas?.length || 0 }} dispositivos conectados</p>
                                </div>
                            </div>
                            <button v-if="sesionesActivas?.length > 1" @click="cerrarTodasSesiones" type="button"
                                style="color:var(--brand)" class="text-xs font-semibold hover:underline shrink-0">
                                Cerrar todas
                            </button>
                        </div>

                        <div class="admin-dash-list">
                            <div v-for="s in sesionesActivas" :key="s.id" class="admin-dash-list-item">
                                <div class="admin-dash-list-item__left">
                                    <div class="admin-dash-list-icon"><i class="pi" :class="dispositivoIconos[s.tipo] || 'pi-desktop'"></i></div>
                                    <div class="min-w-0">
                                        <p class="admin-dash-list-title">
                                            {{ s.dispositivo }}
                                            <span v-if="s.es_actual" style="color:#059669" class="text-[10px] font-semibold ml-0.5">· Este dispositivo</span>
                                        </p>
                                        <p class="admin-dash-list-meta truncate">{{ s.navegador }} · {{ s.ubicacion || s.ip }}</p>
                                        <p class="admin-dash-list-meta">Activo: {{ formatDate(s.ultima_actividad) }}</p>
                                    </div>
                                </div>
                                <button v-if="!s.es_actual" @click="cerrarSesion(s)" title="Cerrar sesión" class="admin-cobros-action-btn admin-cobros-action-btn--refund shrink-0">
                                    <i class="pi pi-times"></i>
                                </button>
                            </div>
                            <div v-if="!sesionesActivas?.length" class="admin-cobros-empty">No hay sesiones activas registradas.</div>
                        </div>
                    </div>
                </div>

                <!-- Registro de Actividad -->
                <div class="admin-cobros-card min-w-0">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-history"></i></div>
                                <div>
                                    <h3>Registro de Actividad</h3>
                                    <p class="admin-cobros-header-subtitle">Últimas acciones realizadas en el panel</p>
                                </div>
                            </div>
                        </div>

                        <div class="admin-dash-list">
                            <div v-for="r in registroActividad" :key="r.id" class="admin-dash-list-item" style="align-items:flex-start">
                                <div class="admin-dash-list-item__left" style="align-items:flex-start">
                                    <div class="admin-dash-list-icon"><i class="pi" :class="accionIconos[r.tipo] || 'pi-bolt'"></i></div>
                                    <div class="min-w-0">
                                        <p class="text-xs leading-snug" style="color:var(--ink)">
                                            <span class="font-semibold">{{ r.admin }}</span> {{ r.detalle }}
                                        </p>
                                        <p class="admin-dash-list-meta mt-0.5">{{ formatDate(r.fecha) }}</p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="!registroActividad?.length" class="admin-cobros-empty">Sin actividad registrada todavía.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila 3 (solo super_admin): Administradores | Permisos y Roles -->
            <div v-if="esSuperAdmin" class="admin-two-col-grid gap-6 w-full items-stretch">

                <!-- Administradores -->
                <div class="admin-cobros-card min-w-0">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-users"></i></div>
                                <h3>Administradores</h3>
                            </div>
                            <span class="admin-cobros-header-subtitle">{{ administradores.length }} cuentas</span>
                        </div>

                        <div class="admin-dash-list">
                            <div v-for="a in administradores" :key="a.id" class="admin-dash-list-item">
                                <div class="min-w-0">
                                    <p class="admin-dash-list-title">{{ a.nombre }}</p>
                                    <p class="admin-dash-list-meta truncate">{{ a.email }} · {{ rolLabel[a.rol] ?? a.rol }}</p>
                                    <p class="admin-dash-list-meta">Últ. acceso: {{ formatDate(a.ultimo_acceso_en) }}</p>
                                </div>
                                <button @click="toggleActivo(a)" class="admin-dash-badge shrink-0" style="cursor:pointer;border:1.5px solid transparent"
                                    :class="a.esta_activo ? 'admin-dash-badge--verificado' : 'admin-dash-badge--bloqueado'">
                                    <span class="admin-dash-badge-dot"></span>{{ a.esta_activo ? 'Activo' : 'Inactivo' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permisos y Roles -->
                <div class="admin-cobros-card min-w-0">
                    <div>
                        <div class="admin-cobros-card__header">
                            <div class="admin-cobros-card__header-left">
                                <div class="admin-cobros-header-icon"><i class="pi pi-sitemap"></i></div>
                                <h3>Permisos y Roles</h3>
                            </div>
                        </div>

                        <div class="overflow-x-auto" style="padding:1.5rem" v-if="permisosRoles?.permisos?.length">
                            <table class="admin-cobros-table min-w-[420px]">
                                <thead>
                                    <tr>
                                        <th>Módulo</th>
                                        <th v-for="rol in permisosRoles.roles" :key="rol" class="text-center" :class="rolColorHeader[rol]">
                                            {{ rolLabel[rol] ?? rol }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="p in permisosRoles.permisos" :key="p.clave">
                                        <td class="text-gray-700">{{ p.nombre }}</td>
                                        <td v-for="rol in permisosRoles.roles" :key="rol" class="text-center">
                                            <i v-if="permisosRoles.matriz[rol]?.[p.clave]" class="pi pi-check text-green-600"></i>
                                            <i v-else class="pi pi-times text-gray-300"></i>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p v-else class="admin-cobros-empty">Aún no hay permisos configurados.</p>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>