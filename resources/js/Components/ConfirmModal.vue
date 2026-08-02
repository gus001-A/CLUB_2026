<script setup>
import { useConfirm } from '@/composables/useConfirm';

const { state, respond } = useConfirm();
</script>

<template>
    <Transition name="fade">
        <div v-if="state.show" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/50" @click="respond(false)"></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
<div
    class="!w-12 !h-12 mx-auto rounded-full flex items-center justify-center mb-4 shrink-0"
    :class="state.danger ? 'bg-red-100 text-red-600' : 'bg-brand/10 text-brand'"
>
    <i class="pi" :class="state.danger ? 'pi-exclamation-triangle' : 'pi-question-circle'" style="font-size: 1.25rem"></i>
</div>

                <h3 class="font-semibold text-gray-800 text-base mb-1">{{ state.title }}</h3>
                <p class="text-sm text-gray-500 mb-6">{{ state.message }}</p>

                <div class="flex items-center gap-3">
                    <button
                        @click="respond(true)"
                        class="flex-1 text-white text-sm font-medium py-2.5 rounded-lg"
                        :class="state.danger ? 'bg-red-600 hover:bg-red-700' : 'bg-brand hover:bg-brand-dark'"
                    >
                        {{ state.confirmLabel }}
                    </button>
                    <button
                        @click="respond(false)"
                        class="flex-1 text-gray-600 text-sm font-medium py-2.5 rounded-lg border border-gray-300 hover:bg-gray-50"
                    >
                        {{ state.cancelLabel }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>