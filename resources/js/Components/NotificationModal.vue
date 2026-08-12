<!-- Components/NotificationModal.vue -->
<script setup>
import { useNotification } from '@/composables/useNotification';

const { state } = useNotification();
</script>

<template>
    <Transition name="slide">
        <div v-if="state.show" class="fixed top-4 right-4 z-[70] max-w-sm w-full">
            <div 
                class="rounded-lg shadow-lg p-4 flex items-start gap-3"
                :class="{
                    'bg-red-50 border border-red-200': state.type === 'error',
                    'bg-green-50 border border-green-200': state.type === 'success',
                    'bg-blue-50 border border-blue-200': state.type === 'info'
                }"
            >
                <i 
                    class="pi mt-0.5"
                    :class="{
                        'pi-times-circle text-red-500': state.type === 'error',
                        'pi-check-circle text-green-500': state.type === 'success',
                        'pi-info-circle text-blue-500': state.type === 'info'
                    }"
                ></i>
                <div class="flex-1">
                    <p class="text-sm font-medium" :class="{
                        'text-red-800': state.type === 'error',
                        'text-green-800': state.type === 'success',
                        'text-blue-800': state.type === 'info'
                    }">{{ state.message }}</p>
                </div>
                <button @click="state.show = false" class="text-gray-400 hover:text-gray-600">
                    <i class="pi pi-times"></i>
                </button>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s ease;
}
.slide-enter-from {
    transform: translateX(100%);
    opacity: 0;
}
.slide-leave-to {
    transform: translateX(100%);
    opacity: 0;
}
</style>