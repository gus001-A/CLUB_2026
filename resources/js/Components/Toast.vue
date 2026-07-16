<script setup>
import { useToast } from '@/composables/useToast';

const { toasts, remove } = useToast();
</script>

<template>
    <div class="fixed top-5 right-5 z-50 flex flex-col gap-2 w-80">
        <TransitionGroup name="toast">
            <div
                v-for="t in toasts"
                :key="t.id"
                class="flex items-start gap-3 rounded-xl shadow-lg border px-4 py-3 bg-white"
                :class="t.type === 'success' ? 'border-green-200' : 'border-red-200'"
            >
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                    :class="t.type === 'success' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'"
                >
                    <i class="pi" :class="t.type === 'success' ? 'pi-check' : 'pi-exclamation-triangle'"></i>
                </div>
                <p class="text-sm text-gray-700 flex-1 pt-1">{{ t.message }}</p>
                <button @click="remove(t.id)" class="text-gray-300 hover:text-gray-500 pt-1">
                    <i class="pi pi-times text-xs"></i>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.25s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(20px);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(20px);
}
</style>