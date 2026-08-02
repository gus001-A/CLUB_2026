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
    
    // Si es un string, tratarlo como mensaje simple
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

// Función para mostrar toast de error
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

// Función para mostrar toast de éxito
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

// Función para mostrar toast de advertencia
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

// Función para mostrar toast de información
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

// Procesar mensajes de error
function processErrorMessages(errors) {
    if (!errors || typeof errors !== 'object' || Object.keys(errors).length === 0) {
        return;
    }

    const firstKey = Object.keys(errors)[0];
    const firstError = errors[firstKey];
    
    if (!firstError) return;
    
    const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
    
    // Mensajes personalizados para campos específicos
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
    
    // Priorizar toast
    if (newFlash.toast) {
        showToast(newFlash.toast);
        return; // Si hay toast personalizado, no procesar otros mensajes
    }
    
    // Manejar mensajes directos
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

// Escuchar toast directo
watch(() => page.props.toast, (newToast) => {
    if (newToast) {
        showToast(newToast);
    }
}, { deep: true, immediate: true });

// Escuchar errores de validación
watch(() => page.props.errors, (errors) => {
    processErrorMessages(errors);
}, { deep: true });

// Evento personalizado para pruebas
function handleCustomEvent(event) {
    if (event.detail) {
        showToast(event.detail);
    }
}

// Inicializar
onMounted(() => {
    // Escuchar evento personalizado
    window.addEventListener('show-toast', handleCustomEvent);
    
    // Exponer funciones al window para pruebas
    window.showToast = showToast;
    window.showErrorToast = showErrorToast;
    window.showSuccessToast = showSuccessToast;
    window.showWarningToast = showWarningToast;
    window.showInfoToast = showInfoToast;
    
    // Procesar mensajes iniciales
    nextTick(() => {
        // Procesar flash
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
        
        // Procesar toast directo
        if (page.props.toast) {
            showToast(page.props.toast);
        }
        
        // Procesar errores iniciales
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

// Exponer funciones para uso en otros componentes
defineExpose({
    showToast,
    showErrorToast,
    showSuccessToast,
    showWarningToast,
    showInfoToast
});
</script>

<template>
    <!-- Componente Toast de PrimeVue -->
    <Toast 
        :position="position"
        :pt="{
            root: {
                class: 'toast-container'
            }
        }"
    />
</template>

<style scoped>
/* Estilos personalizados si son necesarios */
.toast-container {
    z-index: 9999;
}
</style>