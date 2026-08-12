<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
    usuario: Object,
});

const { formatDate: formatDateBase } = useFormatters();
const formatDate = (v) => formatDateBase(v, { month: 'long' });

const perfil = computed(() => props.usuario.perfil || null);

function formatDateTime(v) {
    if (!v) return '—';
    return new Date(v).toLocaleString('es-MX', { 
        day: '2-digit', 
        month: 'long', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
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

// Estado para el modal de imágenes
const modalVisible = ref(false);
const modalImageIndex = ref(0);

function openModal(index) {
    if (!fotos.value.length) return;
    modalImageIndex.value = index;
    modalVisible.value = true;
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    modalVisible.value = false;
    document.body.style.overflow = 'auto';
}

function nextImage() {
    if (modalImageIndex.value < fotos.value.length - 1) {
        modalImageIndex.value++;
    }
}

function prevImage() {
    if (modalImageIndex.value > 0) {
        modalImageIndex.value--;
    }
}

function handleKeydown(e) {
    if (!modalVisible.value) return;
    if (e.key === 'Escape') closeModal();
    if (e.key === 'ArrowRight') nextImage();
    if (e.key === 'ArrowLeft') prevImage();
}

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
    console.log('Fotos recibidas:', fotos.value);
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = 'auto';
});
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
                            <img 
                                v-if="fotoPrincipal && fotoPrincipal.url" 
                                :src="fotoPrincipal.url" 
                                class="w-full h-full object-cover" 
                                @error="manejarErrorImagen(0)"
                            />
                            <span v-else>{{ usuario.nombre?.charAt(0)?.toUpperCase() || 'U' }}</span>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ usuario.nombre }}</h1>
                            <p class="text-sm text-gray-400">
                                @{{ usuario.apodo }}
                                <span v-if="edad(usuario.fecha_nacimiento)"> · {{ edad(usuario.fecha_nacimiento) }} años</span>
                                <span v-if="perfil?.ubicacion_ciudad"> · {{ perfil.ubicacion_ciudad }}</span>
                            </p>
                            <div class="flex items-center gap-2 mt-1">
                                <span v-if="usuario.rol === 'admin'" class="px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">Administrador</span>
                                <span v-else class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">{{ usuario.rol }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 flex-wrap">
                        <span v-if="perfil" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand/10 text-brand">{{ tipoLabel[perfil.tipo] || 'Personal' }}</span>
                        <span class="px-3 py-1.5 rounded-lg text-xs font-semibold capitalize" :class="estadoColores[usuario.estado]">{{ usuario.estado }}</span>
                        <span v-if="usuario.email_verificado_en" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700">
                            <i class="pi pi-check-circle mr-1"></i> Verificado
                        </span>
                    </div>
                </div>
            </div>

            <!-- Debug info (solo en desarrollo) -->
            <div v-if="isDev" class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4 text-xs">
                <p><strong>Debug Fotos:</strong></p>
                <p>Total fotos: {{ fotos.length }}</p>
                <p>Foto principal: {{ fotoPrincipal ? 'Si' : 'No' }}</p>
                <div v-for="(foto, idx) in fotos" :key="idx" class="mt-1">
                    Foto {{ idx + 1 }}: {{ foto.url }}
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-6 items-start w-full">

                <!-- Columna izquierda: datos + perfil -->
                <div class="w-full lg:w-2/3 min-w-0 flex flex-col gap-6">

                    <!-- Descripción -->
                    <div v-if="perfil?.descripcion" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-gray-900 mb-2 flex items-center gap-2">
                            <i class="pi pi-align-left text-brand text-xs"></i> {{ usuario.nombre }}: Descripción
                        </h2>
                        <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap">{{ perfil.descripcion }}</p>
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
                                <div style="width:40px;height:40px;flex:none;display:flex;align-items:center;justify-content:center" class="rounded-lg bg-brand/10 text-brand"><i class="pi pi-clock text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Registrado el</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ formatDateTime(usuario.created_at) }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-gray-50/60 rounded-xl p-3">
                                <div style="width:40px;height:40px;flex:none;display:flex;align-items:center;justify-content:center" class="rounded-lg bg-brand/10 text-brand"><i class="pi pi-user text-sm"></i></div>
                                <div>
                                    <p class="text-[11px] text-gray-400 uppercase font-medium">Perfil creado</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ perfil ? formatDateTime(perfil.created_at) : 'No creado' }}</p>
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

                    <!-- Perfil de Creador (solo si rol = creador) -->
                    <div v-if="usuario.rol === 'creador'" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="pi pi-star text-brand text-xs"></i> Perfil de Creador
                        </h2>
                        <template v-if="usuario.creador">
                            <div class="flex items-center gap-2 mb-4 flex-wrap">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold capitalize"
                                    :class="{
                                        'bg-green-100 text-green-700': usuario.creador.estado_verificacion === 'aprobado',
                                        'bg-amber-100 text-amber-700': usuario.creador.estado_verificacion === 'pendiente',
                                        'bg-red-100 text-red-700': usuario.creador.estado_verificacion === 'rechazado',
                                    }">
                                    {{ usuario.creador.estado_verificacion }}
                                </span>
                                <span v-if="usuario.creador.es_premium" class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-600 flex items-center gap-1">
                                    <i class="pi pi-crown text-[10px]"></i> Premium
                                </span>
                            </div>
                            <p v-if="usuario.creador.biografia" class="text-sm text-gray-600 mb-4">{{ usuario.creador.biografia }}</p>
                            <p v-else class="text-sm text-gray-400 mb-4 italic">Aún no ha completado su biografía.</p>

                            <div v-if="usuario.creador.categorias?.length" class="mb-3">
                                <p class="text-[11px] text-gray-400 uppercase font-medium mb-2">Categorías</p>
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="(c, idx) in usuario.creador.categorias" :key="idx" class="px-3 py-1 rounded-full text-xs font-medium bg-brand/10 text-brand">{{ c }}</span>
                                </div>
                            </div>
                            <p v-if="usuario.creador.metodo_pago" class="text-sm text-gray-600">
                                <span class="text-gray-400">Método de pago:</span>
                                <span class="font-medium capitalize">{{ usuario.creador.metodo_pago }}</span>
                            </p>
                        </template>
                        <p v-else class="text-sm text-gray-400">Sin perfil de creador registrado.</p>
                    </div>
                </div>

                <!-- Columna derecha: estado de verificación + perfil resumen -->
                <div class="w-full lg:w-1/3 min-w-0 flex flex-col gap-6">

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
                            {{ perfil ? (perfil.estado_verificacion === 'verificado' ? 'Verificado' : 'Pendiente') : 'Sin perfil creado' }}
                        </p>
                    </div>

                    <!-- Resumen del perfil -->
                    <div v-if="perfil" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-gray-900 mb-4">Resumen del perfil</h2>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-400">Tipo de cuenta:</dt>
                                <dd class="text-gray-800 font-medium">{{ tipoLabel[perfil.tipo] || 'Personal' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-400">Privacidad de fotos:</dt>
                                <dd class="text-gray-800 font-medium capitalize">{{ perfil.privacidad_fotos || 'Todos' }}</dd>
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

                    <!-- Galería de fotos -->
                    <div v-if="fotos.length" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                        <h2 class="text-sm font-bold text-gray-900 mb-4 flex items-center justify-between">
                            <span>Fotos ({{ fotos.length }})</span>
                            <span class="text-xs font-normal text-gray-400">Click para ampliar</span>
                        </h2>
                        <div class="grid grid-cols-3 gap-2">
                            <div 
                                v-for="(foto, idx) in fotos.slice(0, 6)" 
                                :key="foto.id || idx" 
                                class="relative aspect-square rounded-xl overflow-hidden bg-gray-100 cursor-pointer hover:ring-2 hover:ring-brand transition-all"
                                @click="openModal(idx)"
                            >
                                <img 
                                    :src="foto.url" 
                                    class="w-full h-full object-cover" 
                                    :alt="`Foto ${idx + 1}`"
                                    @error="manejarErrorImagen(idx)"
                                />
                                <div v-if="foto.es_principal" class="absolute top-1 right-1 bg-brand text-white text-xs px-1.5 py-0.5 rounded-full">
                                    <i class="pi pi-star text-[8px]"></i>
                                </div>
                                <div v-if="erroresImagenes[idx]" class="absolute inset-0 flex items-center justify-center bg-gray-200 text-gray-500 text-xs">
                                    Error al cargar
                                </div>
                            </div>
                            <div v-if="fotos.length > 6" 
                                class="aspect-square rounded-xl bg-gray-200 flex items-center justify-center text-gray-500 font-semibold cursor-pointer hover:bg-gray-300 transition-colors"
                                @click="openModal(6)"
                            >
                                +{{ fotos.length - 6 }} más
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal de imágenes -->
        <Teleport to="body">
            <div v-if="modalVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90" @click.self="closeModal">
                <div class="relative max-w-4xl w-full mx-4">
                    <button @click="closeModal" class="absolute -top-12 right-0 text-white hover:text-gray-300 text-2xl transition-colors">
                        <i class="pi pi-times"></i>
                    </button>
                    
                    <div class="relative bg-transparent">
                        <img 
                            v-if="fotos[modalImageIndex]?.url && !erroresImagenes[modalImageIndex]"
                            :src="fotos[modalImageIndex].url" 
                            class="w-full max-h-[80vh] object-contain rounded-lg"
                            :alt="`Foto ${modalImageIndex + 1}`"
                            @error="manejarErrorImagen(modalImageIndex)"
                        />
                        <div v-else class="w-full h-96 flex items-center justify-center text-white">
                            <div class="text-center">
                                <i class="pi pi-exclamation-triangle text-4xl block mb-2"></i>
                                <span class="text-sm">No se pudo cargar la imagen</span>
                            </div>
                        </div>
                        
                        <!-- Navegación -->
                        <button 
                            v-if="fotos.length > 1 && modalImageIndex > 0"
                            @click="prevImage"
                            class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white rounded-full p-2 transition-colors"
                        >
                            <i class="pi pi-chevron-left text-xl"></i>
                        </button>
                        <button 
                            v-if="fotos.length > 1 && modalImageIndex < fotos.length - 1"
                            @click="nextImage"
                            class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/50 hover:bg-black/70 text-white rounded-full p-2 transition-colors"
                        >
                            <i class="pi pi-chevron-right text-xl"></i>
                        </button>
                        
                        <!-- Indicador -->
                        <div v-if="fotos.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                            <span 
                                v-for="(_, idx) in fotos" 
                                :key="idx"
                                class="w-2 h-2 rounded-full transition-all"
                                :class="idx === modalImageIndex ? 'bg-white w-4' : 'bg-white/50'"
                            ></span>
                        </div>
                        
                        <!-- Info de la foto -->
                        <div class="absolute bottom-4 left-4 text-white text-sm">
                            <span v-if="fotos[modalImageIndex]?.es_principal" class="bg-brand px-2 py-0.5 rounded-full text-xs font-semibold">
                                <i class="pi pi-star mr-1"></i> Principal
                            </span>
                            <span class="block text-xs text-gray-300 mt-1">
                                Foto {{ modalImageIndex + 1 }} de {{ fotos.length }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>