<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post(route('admin.login.attempt'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Iniciar sesión - Administrador" />

    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <span class="text-brand text-4xl">♥</span>
                <h1 class="font-serif text-2xl font-semibold text-gray-800 mt-2">
                    Club de <span class="text-brand">Fantasías</span>
                </h1>
                <p class="text-gray-400 text-sm mt-1">Panel de Administrador</p>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                    <input
                        v-model="form.email"
                        type="email"
                        autofocus
                        class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring-brand text-sm"
                        placeholder="admin@clubdefantasias.com"
                    />
                    <p v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full rounded-lg border-gray-300 focus:border-brand focus:ring-brand text-sm"
                        placeholder="••••••••"
                    />
                    <p v-if="form.errors.password" class="text-red-600 text-xs mt-1">{{ form.errors.password }}</p>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input v-model="form.remember" type="checkbox" class="rounded border-gray-300 text-brand focus:ring-brand" />
                    Recordarme
                </label>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-brand hover:bg-brand-dark text-white font-medium py-2.5 rounded-lg text-sm transition-colors disabled:opacity-50"
                >
                    Iniciar sesión
                </button>
            </form>
        </div>
    </div>
</template>