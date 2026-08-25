<script setup>
import { useConfirm } from '@/composables/useConfirm';
import { ref, watch } from 'vue';

const { state, respond } = useConfirm();
const isVisible = ref(false);

// Controlar la visibilidad para la animación de entrada
watch(() => state.show, (newVal) => {
    if (newVal) {
        isVisible.value = true;
    } else {
        setTimeout(() => {
            isVisible.value = false;
        }, 400);
    }
}, { immediate: true });
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div v-if="isVisible" class="modal-backdrop" @click.self="respond(false)">
                <div class="modal-wrapper">
                    <div class="modal-card" :class="{ 'modal-card-danger': state.danger }">
                        <!-- Partículas de fondo -->
                        <div class="modal-particles">
                            <div class="particle particle-1"></div>
                            <div class="particle particle-2"></div>
                            <div class="particle particle-3"></div>
                            <div class="particle particle-4"></div>
                            <div class="particle particle-5"></div>
                        </div>

                        <!-- Decoración superior con gradiente mejorado -->
                        <div class="modal-decoration" :class="{ 'danger': state.danger }">
                            <div class="decoration-glow"></div>
                        </div>

                        <!-- Botón cerrar -->
                        <button @click="respond(false)" class="modal-close" aria-label="Cerrar">
                            <svg class="modal-close-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <!-- Icono con efecto 3D -->
                        <div class="modal-icon-wrapper">
                            <div class="modal-icon-ring" :class="{ 'danger': state.danger }">
                                <div class="modal-icon-glow" :class="{ 'danger': state.danger }"></div>
                                <div class="modal-icon" :class="{ 'danger': state.danger }">
                                    <svg v-if="state.danger" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 9v4m0 4h.01M3 12a9 9 0 1 0 18 0 9 9 0 1 0-18 0z" />
                                    </svg>
                                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div class="modal-body">
                            <h3 class="modal-title">{{ state.title }}</h3>
                            <p class="modal-message">{{ state.message }}</p>
                        </div>

                        <!-- Botones con efecto glassmorphism -->
                        <div class="modal-footer">
                            <button @click="respond(false)" class="btn btn-cancel">
                                <span class="btn-content">{{ state.cancelLabel }}</span>
                            </button>
                            <button @click="respond(true)" class="btn"
                                :class="state.danger ? 'btn-danger' : 'btn-confirm'">
                                <span class="btn-content">
                                    <svg v-if="state.danger" class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else class="btn-icon" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    {{ state.confirmLabel }}
                                </span>
                            </button>
                        </div>

                        <!-- Barra de progreso con efecto brillante -->
                        <div class="modal-progress">
                            <div class="modal-progress-bar" :class="{ 'danger': state.danger }"></div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* ============================================
   BACKDROP
   ============================================ */
.modal-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
}

/* ============================================
   WRAPPER
   ============================================ */
.modal-wrapper {
    width: 100%;
    max-width: 440px;
    animation: modalSlideUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes modalSlideUp {
    from {
        opacity: 0;
        transform: translateY(50px) scale(0.9);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* ============================================
   CARD
   ============================================ */
.modal-card {
    position: relative;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 28px;
    padding: 2.5rem 2rem 1.5rem;
    box-shadow:
        0 30px 100px rgba(0, 0, 0, 0.25),
        0 0 0 1px rgba(255, 255, 255, 0.1);
    overflow: hidden;
    transition: all 0.3s ease;
}

.modal-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 30% 10%, rgba(99, 102, 241, 0.03), transparent 60%);
    pointer-events: none;
}

.modal-card.modal-card-danger::before {
    background: radial-gradient(circle at 30% 10%, rgba(239, 68, 68, 0.03), transparent 60%);
}

/* ============================================
   PARTICLES
   ============================================ */
.modal-particles {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
}

.particle {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.05));
    animation: float 6s ease-in-out infinite;
}

.particle-1 {
    width: 80px;
    height: 80px;
    top: -20px;
    right: -20px;
    animation-delay: 0s;
}

.particle-2 {
    width: 60px;
    height: 60px;
    bottom: 30px;
    left: -20px;
    animation-delay: 1s;
}

.particle-3 {
    width: 40px;
    height: 40px;
    top: 40%;
    right: -10px;
    animation-delay: 2s;
}

.particle-4 {
    width: 30px;
    height: 30px;
    bottom: 40%;
    left: -5px;
    animation-delay: 3s;
}

.particle-5 {
    width: 50px;
    height: 50px;
    top: 10%;
    left: 30%;
    animation-delay: 1.5s;
}

@keyframes float {

    0%,
    100% {
        transform: translate(0, 0) scale(1);
    }

    50% {
        transform: translate(10px, -20px) scale(1.1);
    }
}

/* ============================================
   DECORATION
   ============================================ */
.modal-decoration {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg,
            #6366f1,
            #8b5cf6,
            #a78bfa,
            #8b5cf6,
            #6366f1);
    background-size: 300% 100%;
    animation: shimmer 3s ease-in-out infinite;
    z-index: 2;
}

.modal-decoration.danger {
    background: linear-gradient(90deg,
            #ef4444,
            #f87171,
            #fca5a5,
            #f87171,
            #ef4444);
    background-size: 300% 100%;
    animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
    0% {
        background-position: -300% 0;
    }

    100% {
        background-position: 300% 0;
    }
}

.decoration-glow {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60%;
    height: 100%;
    background: rgba(255, 255, 255, 0.3);
    filter: blur(10px);
    animation: glowPulse 2s ease-in-out infinite;
}

@keyframes glowPulse {

    0%,
    100% {
        opacity: 0.3;
        transform: translateX(-50%) scaleX(1);
    }

    50% {
        opacity: 0.6;
        transform: translateX(-50%) scaleX(1.3);
    }
}

/* ============================================
   CLOSE BUTTON
   ============================================ */
.modal-close {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: rgba(0, 0, 0, 0.03);
    color: #9ca3af;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    z-index: 10;
}

.modal-close:hover {
    background: rgba(0, 0, 0, 0.06);
    color: #4b5563;
    transform: rotate(90deg) scale(1.1);
}

.modal-close:active {
    transform: rotate(90deg) scale(0.9);
}

.modal-close-icon {
    width: 20px;
    height: 20px;
}

/* ============================================
   ICON
   ============================================ */
.modal-icon-wrapper {
    display: flex;
    justify-content: center;
    margin-bottom: 1.5rem;
    position: relative;
}

.modal-icon-ring {
    position: relative;
    width: 88px;
    height: 88px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.modal-icon-ring::before {
    content: '';
    position: absolute;
    inset: -6px;
    border-radius: 50%;
    padding: 3px;
    background: conic-gradient(from 0deg,
            #6366f1,
            #8b5cf6,
            #a78bfa,
            #8b5cf6,
            #6366f1);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    animation: spin 8s linear infinite;
}

.modal-icon-ring.danger::before {
    background: conic-gradient(from 0deg,
            #ef4444,
            #f87171,
            #fca5a5,
            #f87171,
            #ef4444);
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.modal-icon-glow {
    position: absolute;
    inset: -10px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.15), transparent 70%);
    animation: pulseGlow 2s ease-in-out infinite;
}

.modal-icon-glow.danger {
    background: radial-gradient(circle, rgba(239, 68, 68, 0.15), transparent 70%);
}

@keyframes pulseGlow {

    0%,
    100% {
        transform: scale(1);
        opacity: 0.5;
    }

    50% {
        transform: scale(1.1);
        opacity: 1;
    }
}

.modal-icon {
    position: relative;
    width: 68px;
    height: 68px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #eef2ff, #e0e7ff);
    color: #6366f1;
    transition: all 0.3s ease;
    z-index: 1;
}

.modal-icon.danger {
    background: linear-gradient(135deg, #fef2f2, #fee2e2);
    color: #ef4444;
}

.modal-icon svg {
    width: 34px;
    height: 34px;
}

/* ============================================
   BODY
   ============================================ */
.modal-body {
    text-align: center;
    margin-bottom: 1.75rem;
    position: relative;
}

.modal-title {
    font-size: 1.375rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
    letter-spacing: -0.02em;
    line-height: 1.3;
}

.modal-message {
    font-size: 0.9375rem;
    color: #6b7280;
    line-height: 1.7;
    max-width: 90%;
    margin: 0 auto;
}

/* ============================================
   FOOTER
   ============================================ */
.modal-footer {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    position: relative;
}

.btn {
    flex: 1;
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 16px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 50px;
    position: relative;
    overflow: hidden;
    user-select: none;
}

.btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.btn:hover::before {
    opacity: 1;
}

.btn:active {
    transform: scale(0.95);
}

/* Botón Cancelar */
.btn-cancel {
    background: rgba(0, 0, 0, 0.04);
    color: #4b5563;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(0, 0, 0, 0.06);
}

.btn-cancel:hover {
    background: rgba(0, 0, 0, 0.08);
    color: #1f2937;
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
}

.btn-cancel:active {
    transform: translateY(0) scale(0.95);
}

/* Botón Confirmar */
.btn-confirm {
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #ffffff;
    box-shadow: 0 4px 20px rgba(99, 102, 241, 0.35);
}

.btn-confirm:hover {
    box-shadow: 0 6px 30px rgba(99, 102, 241, 0.45);
    transform: translateY(-2px);
}

.btn-confirm:active {
    transform: translateY(0) scale(0.95);
}

/* Botón Peligro */
.btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #ffffff;
    box-shadow: 0 4px 20px rgba(239, 68, 68, 0.35);
}

.btn-danger:hover {
    box-shadow: 0 6px 30px rgba(239, 68, 68, 0.45);
    transform: translateY(-2px);
}

.btn-danger:active {
    transform: translateY(0) scale(0.95);
}

.btn-content {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    position: relative;
    z-index: 1;
}

.btn-icon {
    width: 18px;
    height: 18px;
}

/* ============================================
   PROGRESS BAR
   ============================================ */
.modal-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: transparent;
    overflow: hidden;
    border-radius: 0 0 28px 28px;
}

.modal-progress-bar {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #6366f1, #8b5cf6, #a78bfa);
    animation: progress 3s ease-in-out forwards;
    position: relative;
}

.modal-progress-bar::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    animation: progressShine 1.5s ease-in-out infinite;
}

.modal-progress-bar.danger {
    background: linear-gradient(90deg, #ef4444, #f87171, #fca5a5);
}

@keyframes progress {
    0% {
        width: 0%;
    }

    100% {
        width: 100%;
    }
}

@keyframes progressShine {
    0% {
        transform: translateX(-100%);
    }

    100% {
        transform: translateX(100%);
    }
}

/* ============================================
   TRANSITIONS
   ============================================ */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.modal-fade-leave-active {
    transition-duration: 0.3s;
}

.modal-fade-enter-from .modal-card,
.modal-fade-leave-to .modal-card {
    opacity: 0;
    transform: translateY(40px) scale(0.92);
}

.modal-fade-leave-to .modal-card {
    transition-duration: 0.3s;
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 480px) {
    .modal-card {
        padding: 2rem 1.5rem 1.25rem;
        border-radius: 24px;
    }

    .modal-icon-ring {
        width: 76px;
        height: 76px;
    }

    .modal-icon {
        width: 58px;
        height: 58px;
    }

    .modal-icon svg {
        width: 28px;
        height: 28px;
    }

    .modal-title {
        font-size: 1.2rem;
    }

    .modal-message {
        font-size: 0.875rem;
        max-width: 100%;
    }

    .modal-footer {
        flex-direction: column-reverse;
        gap: 0.625rem;
    }

    .btn {
        width: 100%;
        padding: 0.875rem 1rem;
        min-height: 52px;
        border-radius: 14px;
    }

    .modal-close {
        top: 0.5rem;
        right: 0.5rem;
        width: 34px;
        height: 34px;
    }

    .modal-close-icon {
        width: 18px;
        height: 18px;
    }

    .particle {
        display: none;
    }
}

@media (max-width: 380px) {
    .modal-card {
        padding: 1.5rem 1rem 1rem;
        border-radius: 20px;
    }

    .modal-icon-ring {
        width: 64px;
        height: 64px;
    }

    .modal-icon {
        width: 50px;
        height: 50px;
    }

    .modal-icon svg {
        width: 24px;
        height: 24px;
    }
}
</style>