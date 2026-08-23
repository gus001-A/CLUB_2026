<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div v-if="visible" class="confirm-backdrop" @click.self="cerrar(false)">
                <div class="confirm-wrapper">
                    <div class="confirm-card" :class="{ 'confirm-card--danger': danger }">
                        <!-- Header con icono -->
                        <div class="confirm-header">
                            <div class="confirm-icon" :class="{ 'confirm-icon--danger': danger }">
                                <svg v-if="danger" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M12 9v4m0 4h.01M3 12a9 9 0 1 0 18 0 9 9 0 1 0-18 0z" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                        </div>

                        <!-- Contenido -->
                        <div class="confirm-body">
                            <h3 class="confirm-title">{{ title }}</h3>
                            <p class="confirm-message" v-html="message"></p>
                        </div>

                        <!-- Botones -->
                        <div class="confirm-footer">
                            <button class="confirm-btn confirm-btn--cancel" @click="cerrar(false)">
                                {{ cancelLabel }}
                            </button>
                            <button class="confirm-btn" :class="danger ? 'confirm-btn--danger' : 'confirm-btn--confirm'"
                                @click="cerrar(true)">
                                {{ confirmLabel }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue';

// ============================================================
// PROPS
// ============================================================
const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: 'Confirmar acción'
    },
    message: {
        type: String,
        default: '¿Estás seguro de realizar esta acción?'
    },
    confirmLabel: {
        type: String,
        default: 'Confirmar'
    },
    cancelLabel: {
        type: String,
        default: 'Cancelar'
    },
    danger: {
        type: Boolean,
        default: false
    }
});

// ============================================================
// EMITS
// ============================================================
const emit = defineEmits(['update:modelValue', 'confirm', 'cancel']);

// ============================================================
// REFERENCIAS
// ============================================================
const visible = ref(props.modelValue);

// ============================================================
// WATCH
// ============================================================
watch(() => props.modelValue, (newVal) => {
    visible.value = newVal;
});

// ============================================================
// FUNCIONES
// ============================================================
function cerrar(aceptado) {
    visible.value = false;
    emit('update:modelValue', false);

    if (aceptado) {
        emit('confirm');
    } else {
        emit('cancel');
    }
}
</script>

<style scoped>
/* ============================================
   BACKDROP
   ============================================ */
.confirm-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

/* ============================================
   WRAPPER
   ============================================ */
.confirm-wrapper {
    width: 100%;
    max-width: 420px;
    animation: slideUp 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(40px) scale(0.95);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* ============================================
   CARD
   ============================================ */
.confirm-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 2rem 1.75rem 1.5rem;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.confirm-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: #6366f1;
}

.confirm-card--danger::before {
    background: #ef4444;
}

/* ============================================
   HEADER - ICONO
   ============================================ */
.confirm-header {
    display: flex;
    justify-content: center;
    margin-bottom: 1.25rem;
}

.confirm-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eef2ff;
    color: #6366f1;
}

.confirm-icon--danger {
    background: #fef2f2;
    color: #ef4444;
}

.confirm-icon svg {
    width: 32px;
    height: 32px;
}

/* ============================================
   BODY
   ============================================ */
.confirm-body {
    text-align: center;
    margin-bottom: 1.5rem;
}

.confirm-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.5rem;
    line-height: 1.3;
}

.confirm-message {
    font-size: 0.9rem;
    color: #6b7280;
    line-height: 1.6;
    margin: 0;
}

.confirm-message :deep(strong) {
    color: #111827;
    font-weight: 700;
}

/* ============================================
   FOOTER - BOTONES
   ============================================ */
.confirm-footer {
    display: flex;
    gap: 0.75rem;
}

.confirm-btn {
    flex: 1;
    padding: 0.7rem 1rem;
    border: none;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 48px;
}

.confirm-btn:active {
    transform: scale(0.95);
}

/* Botón Cancelar */
.confirm-btn--cancel {
    background: #f3f4f6;
    color: #4b5563;
}

.confirm-btn--cancel:hover {
    background: #e5e7eb;
    color: #1f2937;
}

/* Botón Confirmar */
.confirm-btn--confirm {
    background: #6366f1;
    color: #ffffff;
    box-shadow: 0 4px 16px rgba(99, 102, 241, 0.3);
}

.confirm-btn--confirm:hover {
    background: #4f46e5;
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(99, 102, 241, 0.4);
}

/* Botón Peligro */
.confirm-btn--danger {
    background: #ef4444;
    color: #ffffff;
    box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
}

.confirm-btn--danger:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(239, 68, 68, 0.4);
}

/* ============================================
   TRANSITIONS
   ============================================ */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.modal-fade-enter-from .confirm-card,
.modal-fade-leave-to .confirm-card {
    transform: translateY(30px) scale(0.95);
}

.modal-fade-leave-active {
    transition-duration: 0.25s;
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 480px) {
    .confirm-card {
        padding: 1.5rem 1.25rem 1.25rem;
        border-radius: 20px;
    }

    .confirm-icon {
        width: 56px;
        height: 56px;
    }

    .confirm-icon svg {
        width: 28px;
        height: 28px;
    }

    .confirm-title {
        font-size: 1.1rem;
    }

    .confirm-message {
        font-size: 0.85rem;
    }

    .confirm-footer {
        flex-direction: column-reverse;
        gap: 0.5rem;
    }

    .confirm-btn {
        width: 100%;
        min-height: 44px;
        padding: 0.6rem;
        font-size: 0.8rem;
    }
}
</style>