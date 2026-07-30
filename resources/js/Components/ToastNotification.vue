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

// Función para mostrar toast usando PrimeVue
function showToast(data) {
    if (!data) return;
    
    toast.add({
        severity: data.type || 'info',
        summary: data.title || 'Notificación',
        detail: data.message || '',
        life: data.duration || props.duration,
        closable: true,
        ...data
    });
}

// Función para mostrar toast desde un mensaje de error de validación
function showErrorToast(message) {
    if (!message) return;
    
    toast.add({
        severity: 'error',
        summary: 'Error',
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

// Escuchar cambios en los flashes de Inertia
watch(() => page.props.flash, (newFlash) => {
    
    if (!newFlash) return;
    
    // Priorizar toast
    if (newFlash.toast) {
        showToast(newFlash.toast);
    }
    
    // También manejar mensajes directos
    if (newFlash.success) {
        showSuccessToast(newFlash.success, 'Éxito');
    }
    
    if (newFlash.error) {
        showErrorToast(newFlash.error);
    }
    
    if (newFlash.warning) {
        showToast({
            type: 'warn',
            title: 'Advertencia',
            message: newFlash.warning,
            duration: props.duration
        });
    }
    
    if (newFlash.info) {
        showToast({
            type: 'info',
            title: 'Información',
            message: newFlash.info,
            duration: props.duration
        });
    }
}, { deep: true, immediate: true });

// También escuchar toast directo
watch(() => page.props.toast, (newToast) => {
    if (newToast) {
        showToast(newToast);
    }
}, { deep: true, immediate: true });

// Escuchar errores de validación
watch(() => page.props.errors, (errors) => {
    
    if (errors && typeof errors === 'object' && Object.keys(errors).length > 0) {
        // Tomar el primer error
        const firstKey = Object.keys(errors)[0];
        const firstError = errors[firstKey];
        
        if (firstError) {
            const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
            
            // Si el error coincide con el campo de código de invitación, mostrar mensaje personalizado
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
    }
}, { deep: true });

// Escuchar evento personalizado para pruebas
function handleCustomEvent(event) {
    if (event.detail) {
        showToast(event.detail);
    }
}

onMounted(() => {
    // Escuchar evento personalizado
    window.addEventListener('show-toast', handleCustomEvent);
    
    // Exponer funciones al window para pruebas
    window.showToast = showToast;
    window.showErrorToast = showErrorToast;
    window.showSuccessToast = showSuccessToast;
    
    // Si hay un toast en la carga inicial
    nextTick(() => {
        if (page.props.flash?.toast) {
            showToast(page.props.flash.toast);
        }
        
        if (page.props.toast) {
            showToast(page.props.toast);
        }
        
        // Verificar errores iniciales
        if (page.props.errors && Object.keys(page.props.errors).length > 0) {
            const firstKey = Object.keys(page.props.errors)[0];
            const firstError = page.props.errors[firstKey];
            if (firstError) {
                const errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                showErrorToast(errorMessage);
            }
        }
    });
});

onBeforeUnmount(() => {
    window.removeEventListener('show-toast', handleCustomEvent);
    delete window.showToast;
    delete window.showErrorToast;
    delete window.showSuccessToast;
});

// Exponer funciones para uso en otros componentes
defineExpose({
    showToast,
    showErrorToast,
    showSuccessToast
});
</script>

<template>
    <Toast position="top-right" />
</template>