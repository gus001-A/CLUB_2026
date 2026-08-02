<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    usuario: Object,
});

const perfil = computed(() => props.usuario.perfil || null);

function formatDate(v) {
    if (!v) return '—';
    return new Date(v).toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' });
}

function edad(fechaNacimiento) {
    if (!fechaNacimiento) return null;
    const hoy = new Date();
    const nac = new Date(fechaNacimiento);
    let e = hoy.getFullYear() - nac.getFullYear();
    const m = hoy.getMonth() - nac.getMonth();
    if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) e--;
    return e;
}

// intereses/pasatiempos pueden venir como array (si el modelo ya tiene el cast)
// o como string JSON crudo (si no) — esto cubre ambos casos sin tronar.
function comoLista(valor) {
    if (!valor) return [];
    if (Array.isArray(valor)) return valor;
    try {
        const parsed = JSON.parse(valor);
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

const intereses = computed(() => comoLista(perfil.value?.intereses));
const pasatiempos = computed(() => comoLista(perfil.value?.pasatiempos));
const fotos = computed(() => comoLista(perfil.value?.fotos));

const estadoColores = {
    verificado: 'bg-green-100 text-green-700',
    pendiente: 'bg-amber-100 text-amber-700',
    incompleto: 'bg-amber-100 text-amber-700',
    bloqueado: 'bg-red-100 text-red-700',
};

const verificacionColores = {
    verificado: 'bg-green-100 text-green-700',
    pendiente: 'bg-amber-100 text-amber-700',
    rechazado: 'bg-red-100 text-red-700',
};

const tipoLabel = { personal: 'Personal', pareja: 'En pareja' };
</script>

<template>
    <Head :title="usuario.nombre" />

    <AdminLayout>
        <template #title>{{ usuario.nombre }}</template>
        <template #breadcrumb>Dashboard &gt; Usuarios &gt; {{ usuario.nombre }}</template>

        <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4">
            <Link :href="route('admin.usuarios.index')" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand mb-4">
                <i class="pi pi-arrow-left text-xs"></i> Volver a Usuarios
            </Link>

            <!-- Banner de perfil -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
                <div style="height:8px;background:linear-gradient(90deg,#C81E3A,#E85C74)"></div>
                <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0 font-bold overflow-hidden" style="width:64px;height:64px;font-size:1.5rem">
                            <img v-if="fotos[0]" :src="fotos[0]" class="w-full h-full object-cover" />
                            <span v-else>{{ usuario.nombre?.charAt(0)?.toUpperCase() || 'U' }}</span>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ usuario.nombre }}</h1>
                            <p class="text-sm text-gray-400">
                                @{{ usuario.apodo }}
                                <span v-if="edad(usuario.fecha_nacimiento)"> · {{ edad(usuario.fecha_nacimiento) }} años</span>
                                <span v-if="perfil?.ubicacion_ciudad"> · {{ perfil.ubicacion_ciudad }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 flex-wrap">
                        <span class="px-3 py-1.5 rounded-lg text-xs font-semibold capitalize bg-gray-100 text-gray-600">{{ usuario.rol }}</span>
                        <span v-if="perfil" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand/10 text-brand">{{ tipoLabel[perfil.tipo] }}</span>
                        <span class="px-3 py-1.5 rounded-lg text-xs font-semibold capitalize" :class="estadoColores[usuario.estado]">{{ usuario.estado }}</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-6 items-start w-full">

                <!-- Columna izquierda: datos + perfil -->
                <div class="w-full lg:w-2/3 flex flex-col gap-6">

                    <!-- Descripción / bio -->
                    <div v-if="perfil?.descripcion" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-gray-900 mb-2 flex items-center gap-2">
                            <i class="pi pi-align-left text-brand text-xs"></i> {{ usuario.nombre }}: Descripción
                        </h2>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ perfil.descripcion }}</p>
                    </div>

                    <!-- Datos de la cuenta -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-gray-900 mb-4">Datos de la cuenta</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div style="width:40px;height:40px;flex:none;display:flex;align-items:center;justify-content:center" class="rounded-lg bg-brand/10 text-brand"><i class="pi pi-envelope text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Correo</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ usuario.email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div style="width:40px;height:40px;flex:none;display:flex;align-items:center;justify-content:center" class="rounded-lg bg-brand/10 text-brand"><i class="pi pi-phone text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Teléfono</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ usuario.telefono || '—' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div style="width:40px;height:40px;flex:none;display:flex;align-items:center;justify-content:center" class="rounded-lg bg-brand/10 text-brand"><i class="pi pi-map-marker text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Ciudad</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ usuario.ciudad || perfil?.ubicacion_ciudad || '—' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div style="width:40px;height:40px;flex:none;display:flex;align-items:center;justify-content:center" class="rounded-lg bg-brand/10 text-brand"><i class="pi pi-calendar text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Fecha de nacimiento</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ formatDate(usuario.fecha_nacimiento) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div style="width:40px;height:40px;flex:none;display:flex;align-items:center;justify-content:center" class="rounded-lg bg-brand/10 text-brand"><i class="pi pi-ticket text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Código de invitación</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ usuario.codigo_invitacion || '—' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div style="width:40px;height:40px;flex:none;display:flex;align-items:center;justify-content:center" class="rounded-lg bg-brand/10 text-brand"><i class="pi pi-clock text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Registrado el</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ formatDate(usuario.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Intereses y pasatiempos -->
                    <div v-if="intereses.length || pasatiempos.length" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-gray-900 mb-4">Intereses y pasatiempos</h2>
                        <div v-if="intereses.length" class="mb-4">
                            <p class="text-[11px] text-gray-400 uppercase font-medium mb-2">Intereses</p>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="(i, idx) in intereses" :key="idx" class="px-3 py-1 rounded-full text-xs font-medium bg-brand/10 text-brand">{{ i }}</span>
                            </div>
                        </div>
                        <div v-if="pasatiempos.length">
                            <p class="text-[11px] text-gray-400 uppercase font-medium mb-2">Pasatiempos</p>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="(p, idx) in pasatiempos" :key="idx" class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ p }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Galería -->
                    <div v-if="fotos.length" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-gray-900 mb-4">Fotos ({{ fotos.length }})</h2>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                            <div v-for="(foto, idx) in fotos" :key="idx" class="aspect-square rounded-xl overflow-hidden bg-gray-100">
                                <img :src="foto" class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: estado de verificación + perfil resumen -->
                <div class="w-full lg:w-1/3 flex flex-col gap-6">

                    <!-- Verificación -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 text-center">
                        <div
                            class="mx-auto mb-3 rounded-full flex items-center justify-center"
                            :class="perfil ? (verificacionColores[perfil.estado_verificacion] || 'bg-gray-100 text-gray-500') : 'bg-gray-100 text-gray-400'"
                            style="width:64px;height:64px"
                        >
                            <i
                                class="pi"
                                style="font-size:1.5rem"
                                :class="perfil?.esta_verificado ? 'pi-verified' : 'pi-question-circle'"
                            ></i>
                        </div>
                        <p class="text-xs text-gray-400 uppercase font-medium mb-1">Verificación de perfil</p>
                        <p class="text-lg font-bold text-gray-900 capitalize">
                            {{ perfil ? (perfil.estado_verificacion) : 'Sin perfil creado' }}
                        </p>
                    </div>

                    <!-- Resumen del perfil -->
                    <div v-if="perfil" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-gray-900 mb-4">Resumen del perfil</h2>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-400">Tipo de cuenta:</dt>
                                <dd class="text-gray-800 font-medium">{{ tipoLabel[perfil.tipo] }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-400">Privacidad de fotos:</dt>
                                <dd class="text-gray-800 font-medium capitalize">{{ perfil.privacidad_fotos }}</dd>
                            </div>
                            <div class="flex justify-between" v-if="perfil.puntuacion_compatibilidad !== null">
                                <dt class="text-gray-400">Puntuación de compatibilidad:</dt>
                                <dd class="text-gray-800 font-medium">{{ perfil.puntuacion_compatibilidad }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-400">Fotos subidas:</dt>
                                <dd class="text-gray-800 font-medium">{{ fotos.length }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div v-else class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 text-center text-sm text-gray-400">
                        Este usuario todavía no ha completado su perfil.
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>