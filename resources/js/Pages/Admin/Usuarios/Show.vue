<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useFormatters } from '@/composables/useFormatters';

const props = defineProps({
    usuario: Object,
});

const { formatDate: formatDateBase } = useFormatters();
const formatDate = (v) => formatDateBase(v, { month: 'long' });

const perfil = computed(() => props.usuario.perfil || null);
const fotos = computed(() => perfil.value?.fotos || []);
const fotoPrincipal = computed(() => fotos.value.find((f) => f.es_principal) || fotos.value[0] || null);

const erroresImagenes = ref({});
function manejarErrorImagen(idx) {
    erroresImagenes.value[idx] = true;
}

// OJO: mismo bug que ya corregimos en Usuarios/Create.vue — new Date('2003-03-11')
// se interpreta como medianoche UTC, y getMonth()/getDate() (locales) la
// corren un día atrás en zonas con offset negativo. Parseamos el string
// directo para no depender de Date en absoluto.
function edad(fechaNacimiento) {
    if (!fechaNacimiento) return null;
    const soloFecha = String(fechaNacimiento).slice(0, 10); // por si llega con hora/timestamp
    const [anio, mes, dia] = soloFecha.split('-').map(Number);
    if (!anio || !mes || !dia) return null;

    const hoy = new Date();
    let e = hoy.getFullYear() - anio;
    const mesActual = hoy.getMonth() + 1; // getMonth() es 0-indexado
    if (mesActual < mes || (mesActual === mes && hoy.getDate() < dia)) e--;
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

const badgeEstado = { verificado: 'admin-dash-badge--verificado', pendiente: 'admin-dash-badge--pendiente', incompleto: 'admin-dash-badge--incompleto', bloqueado: 'admin-dash-badge--bloqueado' };
const badgeVerificacion = { aprobado: 'admin-dash-badge--verificado', pendiente: 'admin-dash-badge--pendiente', rechazado: 'admin-dash-badge--bloqueado' };
const tipoLabel = { personal: 'Personal', pareja: 'En pareja' };

// Modal de galería
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
    if (modalImageIndex.value < fotos.value.length - 1) modalImageIndex.value++;
}
function prevImage() {
    if (modalImageIndex.value > 0) modalImageIndex.value--;
}
function handleKeydown(e) {
    if (!modalVisible.value) return;
    if (e.key === 'Escape') closeModal();
    if (e.key === 'ArrowRight') nextImage();
    if (e.key === 'ArrowLeft') prevImage();
}
onMounted(() => document.addEventListener('keydown', handleKeydown));
onBeforeUnmount(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = 'auto';
});
</script>

<template>

    <Head :title="usuario.nombre" />

    <AdminLayout>
        <template #title>{{ usuario.nombre }}</template>
        <template #breadcrumb>Dashboard / Usuarios / {{ usuario.nombre }}</template>

        <div class="admin-prod-show-page">
            <Link :href="route('admin.usuarios.index')" class="admin-user-back-link">
                <i class="pi pi-arrow-left"></i>
                Volver a Usuarios
            </Link>

            <!-- Header del usuario -->
            <div class="admin-prod-header">
                <div class="admin-prod-header-content">
                    <div class="admin-prod-header-left">
                        <div class="admin-user-header-avatar-ring">
                            <div class="admin-user-header-avatar">
                                <img v-if="fotoPrincipal && fotoPrincipal.url" :src="fotoPrincipal.url" @error="manejarErrorImagen(0)" />
                                <span v-else>{{ usuario.nombre?.charAt(0)?.toUpperCase() || 'U' }}</span>
                            </div>
                        </div>

                        <div class="admin-prod-header-info">
                            <div class="admin-prod-header-name-row">
                                <h1>{{ usuario.nombre }}</h1>
                                <span class="admin-prod-header-sku">@{{ usuario.apodo }}</span>
                            </div>
                            <div class="admin-prod-header-meta">
                                <span class="admin-prod-meta-item">
                                    <i class="pi pi-calendar"></i>
                                    {{ edad(usuario.fecha_nacimiento) ? `${edad(usuario.fecha_nacimiento)} años` : 'Edad no disponible' }}
                                </span>
                                <span v-if="perfil?.ubicacion_ciudad" class="admin-prod-meta-divider">•</span>
                                <span v-if="perfil?.ubicacion_ciudad" class="admin-prod-meta-item">
                                    <i class="pi pi-map-marker"></i>
                                    {{ perfil.ubicacion_ciudad }}
                                </span>
                            </div>
                            <div class="admin-prod-header-stats">
                                <div class="admin-prod-stat-item">
                                    <i class="pi pi-tag"></i>
                                    <span class="admin-prod-stat-value">{{ perfil ? (tipoLabel[perfil.tipo] || 'Personal') : 'Sin perfil' }}</span>
                                    <span class="admin-prod-stat-label">cuenta</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-prod-header-right">
                        <span class="admin-dash-badge" :class="badgeEstado[usuario.estado]" style="padding:0.5rem 1rem;font-size:0.78rem">
                            <span class="admin-dash-badge-dot"></span>{{ usuario.estado }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Grid principal -->
            <div class="admin-prod-show-grid">
                <!-- Columna izquierda -->
                <div class="admin-prod-show-left">
                    <!-- Descripción -->
                    <div v-if="perfil?.descripcion" class="admin-prod-info-card admin-prod-info-card--highlight">
                        <div class="admin-prod-info-card-header">
                            <h3><i class="pi pi-align-left"></i> Descripción</h3>
                        </div>
                        <div class="admin-prod-info-card-body">
                            <p class="admin-prod-description-text">{{ perfil.descripcion }}</p>
                        </div>
                    </div>

                    <!-- Datos de la cuenta -->
                    <div class="admin-prod-info-card">
                        <div class="admin-prod-info-card-header">
                            <h3><i class="pi pi-id-card"></i> Datos de la cuenta</h3>
                        </div>
                        <div class="admin-prod-info-card-body">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="admin-user-data-item">
                                    <div class="admin-user-data-icon"><i class="pi pi-envelope"></i></div>
                                    <div class="min-w-0">
                                        <p class="admin-user-data-label">Correo</p>
                                        <p class="admin-user-data-value truncate">{{ usuario.email }}</p>
                                    </div>
                                </div>
                                <div class="admin-user-data-item">
                                    <div class="admin-user-data-icon"><i class="pi pi-phone"></i></div>
                                    <div class="min-w-0">
                                        <p class="admin-user-data-label">Teléfono</p>
                                        <p class="admin-user-data-value">{{ usuario.telefono || '—' }}</p>
                                    </div>
                                </div>
                                <div class="admin-user-data-item">
                                    <div class="admin-user-data-icon"><i class="pi pi-map-marker"></i></div>
                                    <div class="min-w-0">
                                        <p class="admin-user-data-label">Ciudad</p>
                                        <p class="admin-user-data-value">{{ usuario.ciudad || perfil?.ubicacion_ciudad || '—' }}</p>
                                    </div>
                                </div>
                                <div class="admin-user-data-item">
                                    <div class="admin-user-data-icon"><i class="pi pi-calendar"></i></div>
                                    <div class="min-w-0">
                                        <p class="admin-user-data-label">Fecha de nacimiento</p>
                                        <p class="admin-user-data-value">{{ formatDate(usuario.fecha_nacimiento) }}</p>
                                    </div>
                                </div>
                                <div class="admin-user-data-item">
                                    <div class="admin-user-data-icon"><i class="pi pi-hashtag"></i></div>
                                    <div class="min-w-0">
                                        <p class="admin-user-data-label">Código de invitación</p>
                                        <p class="admin-user-data-value">{{ usuario.codigo_invitacion || '—' }}</p>
                                    </div>
                                </div>
                                <div class="admin-user-data-item">
                                    <div class="admin-user-data-icon"><i class="pi pi-user"></i></div>
                                    <div class="min-w-0">
                                        <p class="admin-user-data-label">Registrado el</p>
                                        <p class="admin-user-data-value">{{ formatDate(usuario.created_at) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Intereses y pasatiempos -->
                    <div v-if="intereses.length || pasatiempos.length" class="admin-prod-info-card">
                        <div class="admin-prod-info-card-header">
                            <h3><i class="pi pi-heart"></i> Intereses y pasatiempos</h3>
                        </div>
                        <div class="admin-prod-info-card-body">
                            <div v-if="intereses.length" style="margin-bottom:1rem">
                                <p class="admin-user-data-label" style="margin-bottom:0.5rem">Intereses</p>
                                <div class="admin-prod-tags-group">
                                    <span v-for="(i, idx) in intereses" :key="idx" class="admin-prod-tag"><i class="pi pi-tag"></i>{{ i }}</span>
                                </div>
                            </div>
                            <div v-if="pasatiempos.length">
                                <p class="admin-user-data-label" style="margin-bottom:0.5rem">Pasatiempos</p>
                                <div class="admin-prod-tags-group">
                                    <span v-for="(p, idx) in pasatiempos" :key="idx" class="admin-prod-tag"><i class="pi pi-star"></i>{{ p }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Perfil de Creador -->
                    <div v-if="usuario.rol === 'creador'" class="admin-prod-info-card">
                        <div class="admin-prod-info-card-header">
                            <h3><i class="pi pi-star"></i> Perfil de Creador</h3>
                        </div>
                        <div class="admin-prod-info-card-body">
                            <template v-if="usuario.creador">
                                <div class="flex items-center gap-2 mb-4 flex-wrap">
                                    <span class="admin-dash-badge" :class="badgeVerificacion[usuario.creador.estado_verificacion]">
                                        <span class="admin-dash-badge-dot"></span>{{ usuario.creador.estado_verificacion }}
                                    </span>
                                    <span v-if="usuario.creador.es_premium" class="admin-dash-badge admin-dash-badge--pendiente">
                                        <i class="pi pi-crown" style="font-size:0.6rem"></i> Premium
                                    </span>
                                </div>
                                <p v-if="usuario.creador.biografia" class="admin-prod-description-text" style="margin-bottom:1rem">{{ usuario.creador.biografia }}</p>
                                <p v-else class="admin-user-hint" style="margin-bottom:1rem;font-style:italic">Aún no ha completado su biografía.</p>

                                <div v-if="usuario.creador.categorias?.length" style="margin-bottom:0.8rem">
                                    <p class="admin-user-data-label" style="margin-bottom:0.5rem">Categorías</p>
                                    <div class="admin-prod-tags-group">
                                        <span v-for="(c, idx) in usuario.creador.categorias" :key="idx" class="admin-prod-tag">{{ c }}</span>
                                    </div>
                                </div>
                                <p v-if="usuario.creador.metodo_pago" class="admin-prod-description-text">
                                    <span style="color:var(--muted)">Método de pago:</span>
                                    <span style="font-weight:600" class="capitalize">{{ usuario.creador.metodo_pago }}</span>
                                </p>
                            </template>
                            <p v-else class="admin-user-hint">Sin perfil de creador registrado.</p>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha -->
                <div class="admin-prod-show-right">
                    <!-- Verificación -->
                    <div class="admin-prod-info-card" style="text-align:center;padding:1.5rem">
                        <div class="admin-user-verif-circle" :class="perfil?.esta_verificado ? 'admin-dash-badge--verificado' : 'admin-dash-badge--incompleto'">
                            <i class="pi" :class="perfil?.esta_verificado ? 'pi-verified' : 'pi-question-circle'"></i>
                        </div>
                        <p class="admin-user-data-label">Verificación de perfil</p>
                        <p style="font-size:1.05rem;font-weight:700;color:var(--ink)" class="capitalize">{{ perfil ? perfil.estado_verificacion : 'Sin perfil creado' }}</p>
                    </div>

                    <!-- Resumen del perfil -->
                    <div v-if="perfil" class="admin-prod-info-card">
                        <div class="admin-prod-info-card-header"><h3><i class="pi pi-file"></i> Resumen del perfil</h3></div>
                        <div class="admin-prod-info-card-body">
                            <dl class="admin-user-preview-dl" style="margin-top:0;padding-top:0;border-top:none">
                                <div class="admin-user-preview-dl-row"><dt>Tipo de cuenta</dt><dd>{{ tipoLabel[perfil.tipo] }}</dd></div>
                                <div class="admin-user-preview-dl-row"><dt>Privacidad de fotos</dt><dd class="capitalize">{{ perfil.privacidad_fotos }}</dd></div>
                                <div v-if="perfil.puntuacion_compatibilidad !== null" class="admin-user-preview-dl-row"><dt>Compatibilidad</dt><dd>{{ perfil.puntuacion_compatibilidad }}</dd></div>
                                <div class="admin-user-preview-dl-row"><dt>Fotos subidas</dt><dd>{{ fotos.length }}</dd></div>
                            </dl>
                        </div>
                    </div>
                    <div v-else class="admin-prod-info-card" style="padding:1.5rem;text-align:center">
                        <p class="admin-user-hint">Este usuario todavía no ha completado su perfil.</p>
                    </div>

                    <!-- Galería -->
                    <div v-if="fotos.length" class="admin-prod-info-card">
                        <div class="admin-prod-info-card-header">
                            <h3><i class="pi pi-images"></i> Fotos</h3>
                            <span class="admin-prod-count-badge">{{ fotos.length }}</span>
                        </div>
                        <div class="admin-prod-info-card-body">
                            <div class="admin-prod-gallery-grid">
                                <div v-for="(foto, idx) in fotos.slice(0, 6)" :key="foto.id || idx" class="admin-prod-gallery-item" @click="openModal(idx)" style="cursor:pointer">
                                    <img :src="foto.url" :alt="`Foto ${idx + 1}`" @error="manejarErrorImagen(idx)" />
                                    <div v-if="foto.es_principal" class="admin-prod-gallery-item-overlay">
                                        <span class="admin-prod-gallery-item-badge"><i class="pi pi-star-fill"></i></span>
                                    </div>
                                    <div v-if="erroresImagenes[idx]" class="admin-prod-inline-empty" style="position:absolute;inset:0;background:rgba(0,0,0,0.05)">
                                        <span>Error</span>
                                    </div>
                                </div>
                                <div v-if="fotos.length > 6" class="admin-prod-gallery-item" style="display:flex;align-items:center;justify-content:center;background:var(--surface);color:var(--muted);font-weight:600;cursor:pointer" @click="openModal(6)">
                                    +{{ fotos.length - 6 }} más
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="admin-prod-actions-card">
                        <div class="admin-prod-actions-card-header"><h3><i class="pi pi-bolt"></i> Acciones</h3></div>
                        <div class="admin-prod-actions-card-body">
                            <Link :href="route('admin.usuarios.edit', usuario.id)" class="admin-prod-btn-edit">
                                <i class="pi pi-pencil"></i><span>Editar usuario</span>
                            </Link>
                            <div class="admin-prod-actions-divider"></div>
                            <Link :href="route('admin.usuarios.index')" class="admin-prod-btn-back">
                                <i class="pi pi-arrow-left"></i><span>Volver al listado</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de galería -->
        <div v-if="modalVisible" class="admin-user-modal-overlay" @click.self="closeModal">
            <button class="admin-user-modal-close" @click="closeModal"><i class="pi pi-times"></i></button>
            <button v-if="modalImageIndex > 0" class="admin-user-modal-nav admin-user-modal-nav--prev" @click="prevImage"><i class="pi pi-chevron-left"></i></button>
            <img v-if="fotos[modalImageIndex]" :src="fotos[modalImageIndex].url" class="admin-user-modal-img" />
            <button v-if="modalImageIndex < fotos.length - 1" class="admin-user-modal-nav admin-user-modal-nav--next" @click="nextImage"><i class="pi pi-chevron-right"></i></button>
        </div>
    </AdminLayout>
</template>