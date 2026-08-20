<!-- resources/js/Components/ToastNotification.vue -->
<script setup>
import { ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    duration: {
        type: Number,
        default: 5000
    },
    position: {
        type: String,
        default: 'top-right'
    }
});

const page = usePage();
const toast = useToast();

// Función principal para mostrar toast
function showToast(data) {
    if (!data) return;
    
    if (typeof data === 'string') {
        toast.add({
            severity: 'info',
            summary: 'Notificación',
            detail: data,
            life: props.duration,
            closable: true
        });
        return;
    }

    toast.add({
        severity: data.type || 'info',
        summary: data.title || 'Notificación',
        detail: data.message || '',
        life: data.duration || props.duration,
        closable: true,
        ...data
    });
}

function showErrorToast(message, title = 'Error') {
    if (!message) return;
    toast.add({
        severity: 'error',
        summary: title,
        detail: message,
        life: props.duration,
        closable: true
    });
}

function showSuccessToast(message, title = 'Éxito') {
    if (!message) return;
    toast.add({
        severity: 'success',
        summary: title,
        detail: message,
        life: props.duration,
        closable: true
    });
}

function showWarningToast(message, title = 'Advertencia') {
    if (!message) return;
    toast.add({
        severity: 'warn',
        summary: title,
        detail: message,
        life: props.duration,
        closable: true
    });
}

function showInfoToast(message, title = 'Información') {
    if (!message) return;
    toast.add({
        severity: 'info',
        summary: title,
        detail: message,
        life: props.duration,
        closable: true
    });
}

function processErrorMessages(errors) {
    if (!errors || typeof errors !== 'object' || Object.keys(errors).length === 0) {
        return;
    }

    const firstKey = Object.keys(errors)[0];
    const firstError = errors[firstKey];
    
    if (!firstError) return;
    
    const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
    
    if (firstKey === 'invite_code') {
        showToast({
            type: 'error',
            title: 'Código de invitación inválido',
            message: errorMessage || 'El código de invitación no es válido.',
            duration: props.duration
        });
    } else {
        showErrorToast(errorMessage);
    }
}

// Escuchar cambios en los flashes de Inertia
watch(() => page.props.flash, (newFlash) => {
    if (!newFlash) return;
    
    if (newFlash.toast) {
        showToast(newFlash.toast);
        return;
    }
    
    if (newFlash.success) {
        showSuccessToast(newFlash.success);
    }
    if (newFlash.error) {
        showErrorToast(newFlash.error);
    }
    if (newFlash.warning) {
        showWarningToast(newFlash.warning);
    }
    if (newFlash.info) {
        showInfoToast(newFlash.info);
    }
}, { deep: true, immediate: true });

watch(() => page.props.toast, (newToast) => {
    if (newToast) {
        showToast(newToast);
    }
}, { deep: true, immediate: true });

watch(() => page.props.errors, (errors) => {
    processErrorMessages(errors);
}, { deep: true });

function handleCustomEvent(event) {
    if (event.detail) {
        showToast(event.detail);
    }
}

onMounted(() => {
    window.addEventListener('show-toast', handleCustomEvent);
    window.showToast = showToast;
    window.showErrorToast = showErrorToast;
    window.showSuccessToast = showSuccessToast;
    window.showWarningToast = showWarningToast;
    window.showInfoToast = showInfoToast;
    
    nextTick(() => {
        if (page.props.flash) {
            if (page.props.flash.toast) {
                showToast(page.props.flash.toast);
            } else {
                if (page.props.flash.success) {
                    showSuccessToast(page.props.flash.success);
                }
                if (page.props.flash.error) {
                    showErrorToast(page.props.flash.error);
                }
                if (page.props.flash.warning) {
                    showWarningToast(page.props.flash.warning);
                }
                if (page.props.flash.info) {
                    showInfoToast(page.props.flash.info);
                }
            }
        }
        if (page.props.toast) {
            showToast(page.props.toast);
        }
        if (page.props.errors) {
            processErrorMessages(page.props.errors);
        }
    });
});

onBeforeUnmount(() => {
    window.removeEventListener('show-toast', handleCustomEvent);
    delete window.showToast;
    delete window.showErrorToast;
    delete window.showSuccessToast;
    delete window.showWarningToast;
    delete window.showInfoToast;
});

defineExpose({
    showToast,
    showErrorToast,
    showSuccessToast,
    showWarningToast,
    showInfoToast
});
</script>

<template>
    <Toast 
        :position="position"
        :pt="{
            root: {
                class: 'custom-toast-container'
            },
            message: {
                class: 'custom-toast-message'
            },
            content: {
                class: 'custom-toast-content'
            },
            icon: {
                class: 'custom-toast-icon'
            },
            text: {
                class: 'custom-toast-text'
            },
            summary: {
                class: 'custom-toast-summary'
            },
            detail: {
                class: 'custom-toast-detail'
            }
        }"
    />
</template>

<style>
/* =========================================================================
   ESTILOS PARA TOAST DE PRIMEVUE
   ========================================================================= */
.custom-toast-container {
    z-index: 9999 !important;
    position: fixed !important;
    pointer-events: none !important;
}

.custom-toast-container .p-toast-message {
    pointer-events: auto !important;
    border-radius: 12px !important;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15) !important;
    border: none !important;
    padding: 0 !important;
    overflow: hidden !important;
    margin-bottom: 12px !important;
}

.custom-toast-container .p-toast-message-content {
    padding: 16px 20px !important;
    display: flex !important;
    align-items: flex-start !important;
    gap: 12px !important;
    border-left: 4px solid transparent !important;
}

/* Success */
.custom-toast-container .p-toast-message-success {
    background: #f0fdf4 !important;
    border-left-color: #22c55e !important;
}

.custom-toast-container .p-toast-message-success .p-toast-message-icon {
    color: #22c55e !important;
}

.custom-toast-container .p-toast-message-success .p-toast-summary {
    color: #15803d !important;
}

.custom-toast-container .p-toast-message-success .p-toast-detail {
    color: #166534 !important;
}

/* Error */
.custom-toast-container .p-toast-message-error {
    background: #fef2f2 !important;
    border-left-color: #ef4444 !important;
}

.custom-toast-container .p-toast-message-error .p-toast-message-icon {
    color: #ef4444 !important;
}

.custom-toast-container .p-toast-message-error .p-toast-summary {
    color: #b91c1c !important;
}

.custom-toast-container .p-toast-message-error .p-toast-detail {
    color: #991b1b !important;
}

/* Warning */
.custom-toast-container .p-toast-message-warn {
    background: #fffbeb !important;
    border-left-color: #f59e0b !important;
}

.custom-toast-container .p-toast-message-warn .p-toast-message-icon {
    color: #f59e0b !important;
}

.custom-toast-container .p-toast-message-warn .p-toast-summary {
    color: #b45309 !important;
}

.custom-toast-container .p-toast-message-warn .p-toast-detail {
    color: #92400e !important;
}

/* Info */
.custom-toast-container .p-toast-message-info {
    background: #eff6ff !important;
    border-left-color: #3b82f6 !important;
}

.custom-toast-container .p-toast-message-info .p-toast-message-icon {
    color: #3b82f6 !important;
}

.custom-toast-container .p-toast-message-info .p-toast-summary {
    color: #1d4ed8 !important;
}

.custom-toast-container .p-toast-message-info .p-toast-detail {
    color: #1e40af !important;
}

/* Íconos */
.custom-toast-icon {
    font-size: 1.25rem !important;
    width: 1.5rem !important;
    height: 1.5rem !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex-shrink: 0 !important;
    margin-top: 2px !important;
}

.custom-toast-text {
    flex: 1 !important;
    min-width: 0 !important;
}

.custom-toast-summary {
    font-weight: 700 !important;
    font-size: 0.9rem !important;
    margin-bottom: 2px !important;
    line-height: 1.3 !important;
}

.custom-toast-detail {
    font-size: 0.85rem !important;
    line-height: 1.4 !important;
    opacity: 0.9 !important;
}

/* Botón de cerrar */
.custom-toast-container .p-toast-message .p-toast-icon-close {
    position: absolute !important;
    top: 8px !important;
    right: 8px !important;
    width: 24px !important;
    height: 24px !important;
    border-radius: 50% !important;
    background: transparent !important;
    border: none !important;
    color: #6b7280 !important;
    font-size: 0.8rem !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s ease !important;
}

.custom-toast-container .p-toast-message .p-toast-icon-close:hover {
    background: rgba(0, 0, 0, 0.05) !important;
    color: #1f2937 !important;
}

/* Animación de entrada */
@keyframes toastSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.custom-toast-container .p-toast-message {
    animation: toastSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards !important;
}

/* Responsive */
@media (max-width: 640px) {
    .custom-toast-container {
        padding: 8px !important;
    }
    
    .custom-toast-container .p-toast-message-content {
        padding: 12px 16px !important;
    }
    
    .custom-toast-container .p-toast-message {
        max-width: 100% !important;
    }
}

/* Asegurar que los toasts se muestren correctamente */
.custom-toast-container .p-toast {
    width: 100% !important;
}

.custom-toast-container .p-toast .p-toast-message {
    width: 100% !important;
}

/* Estilos para el contenedor del toast cuando está en posición top-right */
.custom-toast-container.p-toast-top-right {
    top: 80px !important;
    right: 20px !important;
    left: auto !important;
}

.custom-toast-container.p-toast-top-left {
    top: 80px !important;
    left: 20px !important;
    right: auto !important;
}

.custom-toast-container.p-toast-bottom-right {
    bottom: 20px !important;
    right: 20px !important;
    left: auto !important;
}

.custom-toast-container.p-toast-bottom-left {
    bottom: 20px !important;
    left: 20px !important;
    right: auto !important;
}

/* Asegurar que el toast esté por encima de todo */
.custom-toast-container .p-toast-message {
    position: relative !important;
    z-index: 10000 !important;
}

/* Estilo para el icono de cierre en hover */
.custom-toast-container .p-toast-message .p-toast-icon-close .p-toast-icon-close-icon {
    font-size: 0.75rem !important;
}
</style>