<script setup>
import { watch, ref } from 'vue';

const props = defineProps({
    llamada: { type: Object, default: null },
    estado: { type: String, default: 'inactiva' }, // sonando_saliente | sonando_entrante | en_curso
    esVideo: { type: Boolean, default: false },
    streamLocal: { type: Object, default: null },
    streamRemoto: { type: Object, default: null },
    microfonoActivo: { type: Boolean, default: true },
    camaraActiva: { type: Boolean, default: true },
    duracionSegundos: { type: Number, default: 0 },
    otroNombre: { type: String, default: 'Usuario' },
    otroAvatar: { type: String, default: '' },
});

const emit = defineEmits(['contestar', 'rechazar', 'colgar', 'alternar-microfono', 'alternar-camara']);

const videoLocalEl = ref(null);
const videoRemotoEl = ref(null);

watch(() => props.streamLocal, (stream) => {
    if (videoLocalEl.value) videoLocalEl.value.srcObject = stream;
});
watch(() => props.streamRemoto, (stream) => {
    if (videoRemotoEl.value) videoRemotoEl.value.srcObject = stream;
});

function formatoDuracion(segundos) {
    const m = Math.floor(segundos / 60);
    const s = segundos % 60;
    return `${m}:${String(s).padStart(2, '0')}`;
}
</script>

<template>
    <div v-if="estado !== 'inactiva'" class="llamada-overlay">
        <div class="llamada-modal" :class="{ 'llamada-modal--video': esVideo && estado === 'en_curso' }">

            <!-- Video en curso -->
            <template v-if="esVideo && estado === 'en_curso'">
                <video ref="videoRemotoEl" autoplay playsinline class="llamada-video-remoto"></video>
                <video ref="videoLocalEl" autoplay playsinline muted class="llamada-video-local"></video>
            </template>

            <!-- Avatar (llamada de audio, o video antes de conectar) -->
            <div v-else class="llamada-avatar-wrap">
                <img :src="otroAvatar" :alt="otroNombre" class="llamada-avatar" />
                <span v-if="estado === 'sonando_saliente' || estado === 'sonando_entrante'" class="llamada-pulso"></span>
            </div>

            <div class="llamada-info">
                <h2>{{ otroNombre }}</h2>
                <p v-if="estado === 'sonando_saliente'">Llamando...</p>
                <p v-else-if="estado === 'sonando_entrante'">
                    {{ esVideo ? 'Videollamada entrante' : 'Llamada entrante' }}
                </p>
                <p v-else-if="estado === 'en_curso'">{{ formatoDuracion(duracionSegundos) }}</p>
            </div>

            <!-- Controles -->
            <div class="llamada-controles">
                <template v-if="estado === 'sonando_entrante'">
                    <button class="llamada-btn llamada-btn--rechazar" @click="emit('rechazar')">
                        <i class="pi pi-phone" style="transform: rotate(135deg)"></i>
                    </button>
                    <button class="llamada-btn llamada-btn--aceptar" @click="emit('contestar')">
                        <i class="pi pi-phone"></i>
                    </button>
                </template>

                <template v-else>
                    <button class="llamada-btn llamada-btn--secundario" @click="emit('alternar-microfono')">
                        <i class="pi" :class="microfonoActivo ? 'pi-microphone' : 'pi-microphone-slash'"></i>
                    </button>
                    <button v-if="esVideo" class="llamada-btn llamada-btn--secundario" @click="emit('alternar-camara')">
                        <i class="pi" :class="camaraActiva ? 'pi-video' : 'pi-eye-slash'"></i>
                    </button>
                    <button class="llamada-btn llamada-btn--colgar" @click="emit('colgar')">
                        <i class="pi pi-phone" style="transform: rotate(135deg)"></i>
                    </button>
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
.llamada-overlay {
    position: fixed;
    inset: 0;
    z-index: 200;
    background: rgba(15, 12, 12, 0.85);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
}

.llamada-modal {
    width: 100%;
    max-width: 380px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
    padding: 3rem 1.5rem;
    color: #fff;
    text-align: center;
}

.llamada-modal--video {
    max-width: 100%;
    height: 100%;
    position: relative;
    padding: 0;
    justify-content: flex-end;
}

.llamada-video-remoto {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.llamada-video-local {
    position: absolute;
    bottom: 7rem;
    right: 1.25rem;
    width: 110px;
    height: 150px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid rgba(255, 255, 255, 0.4);
    z-index: 2;
}

.llamada-avatar-wrap {
    position: relative;
}
.llamada-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255, 255, 255, 0.2);
}
.llamada-pulso {
    position: absolute;
    inset: -8px;
    border-radius: 50%;
    border: 2px solid #c81e3a;
    animation: llamada-pulso 1.6s infinite;
}
@keyframes llamada-pulso {
    0% { transform: scale(1); opacity: 0.8; }
    100% { transform: scale(1.3); opacity: 0; }
}

.llamada-info h2 {
    font-size: 1.4rem;
    margin: 0 0 0.4rem;
}
.llamada-info p {
    font-size: 0.9rem;
    color: #d8d4d1;
    margin: 0;
}

.llamada-controles {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    position: relative;
    z-index: 2;
    margin-bottom: 1.5rem;
}
.llamada-modal--video .llamada-controles {
    margin-bottom: 2rem;
}

.llamada-btn {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    cursor: pointer;
    transition: transform 0.15s ease;
}
.llamada-btn:hover {
    transform: scale(1.06);
}
.llamada-btn--aceptar {
    background: #22c55e;
    color: #fff;
}
.llamada-btn--rechazar,
.llamada-btn--colgar {
    background: #c81e3a;
    color: #fff;
}
.llamada-btn--secundario {
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    width: 50px;
    height: 50px;
    font-size: 1.05rem;
}
</style>
