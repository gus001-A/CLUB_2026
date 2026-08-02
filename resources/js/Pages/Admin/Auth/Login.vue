<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const mostrarPassword = ref(false);

function submit() {
    form.post(route('admin.login.attempt'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Iniciar sesión - Administrador" />

    <div class="min-h-screen flex bg-white">

        <!-- Panel izquierdo: imagen + branding (oculto en móvil) -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
            <img src="/images/hero-couple.jpg" alt="" class="absolute inset-0 w-full h-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-black/20"></div>

            <div class="relative z-10 flex flex-col justify-between p-10 w-full">
                <div class="flex items-center gap-2">
                    <span class="text-brand text-3xl leading-none">♥</span>
                    <div>
                        <p class="font-serif text-xl font-semibold text-white leading-tight">
                            Club de <span class="text-brand">Fantasías</span>
                        </p>
                        <p class="text-white/50 text-[11px] tracking-wide uppercase">Todo puede suceder</p>
                    </div>
                </div>

                <div>
                    <h1 class="text-white text-3xl font-serif font-semibold leading-tight mb-3">
                        Panel de <span class="text-brand">Administrador</span>
                    </h1>
                    <p class="text-white/70 text-sm max-w-sm mb-6">
                        Acceso exclusivo para el equipo de Club de Fantasías. Gestiona usuarios, contenido y la operación de la plataforma.
                    </p>

                    <ul class="space-y-2.5">
                        <li class="flex items-center gap-2.5 text-white/80 text-sm">
                            <span class="rounded-full bg-brand/20 text-brand flex items-center justify-center shrink-0" style="width:24px;height:24px">
                                <i class="pi pi-shield text-xs"></i>
                            </span>
                            Acceso seguro y auditado
                        </li>
                        <li class="flex items-center gap-2.5 text-white/80 text-sm">
                            <span class="rounded-full bg-brand/20 text-brand flex items-center justify-center shrink-0" style="width:24px;height:24px">
                                <i class="pi pi-lock text-xs"></i>
                            </span>
                            Datos protegidos y confidenciales
                        </li>
                        <li class="flex items-center gap-2.5 text-white/80 text-sm">
                            <span class="rounded-full bg-brand/20 text-brand flex items-center justify-center shrink-0" style="width:24px;height:24px">
                                <i class="pi pi-verified text-xs"></i>
                            </span>
                            Solo para personal autorizado
                        </li>
                    </ul>
                </div>

                <p class="text-white/40 text-xs">© {{ new Date().getFullYear() }} Club de Fantasías. Todos los derechos reservados.</p>
            </div>
        </div>

        <!-- Panel derecho: formulario -->
        <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12 bg-gray-50">
            <div class="w-full max-w-sm">

                <!-- Logo (visible en móvil, ya que el panel izquierdo se oculta) -->
                <div class="text-center mb-8 lg:hidden">
                    <span class="text-brand text-4xl">♥</span>
                    <h1 class="font-serif text-2xl font-semibold text-gray-800 mt-2">
                        Club de <span class="text-brand">Fantasías</span>
                    </h1>
                    <p class="text-gray-400 text-sm mt-1">Panel de Administrador</p>
                </div>

                <div class="hidden lg:block mb-8">
                    <h2 class="text-2xl font-serif font-semibold text-gray-900">Iniciar <span class="text-brand">sesión</span></h2>
                    <p class="text-sm text-gray-500 mt-1">Ingresa tus credenciales de administrador.</p>
                </div>

                <form @submit.prevent="submit" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico</label>
                        <div class="relative">
                            <i class="pi pi-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                            <input
                                v-model="form.email"
                                type="email"
                                autofocus
                                class="w-full rounded-xl border-gray-300 pl-10 pr-3 py-2.5 text-sm focus:border-brand focus:ring-brand"
                                placeholder="admin@clubdefantasias.com"
                            />
                        </div>
                        <p v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Contraseña</label>
                        <div class="relative">
                            <i class="pi pi-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 text-sm"></i>
                            <input
                                v-model="form.password"
                                :type="mostrarPassword ? 'text' : 'password'"
                                class="w-full rounded-xl border-gray-300 pl-10 pr-10 py-2.5 text-sm focus:border-brand focus:ring-brand"
                                placeholder="••••••••"
                            />
                            <button type="button" @click="mostrarPassword = !mostrarPassword"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500 text-sm">
                                <i class="pi" :class="mostrarPassword ? 'pi-eye-slash' : 'pi-eye'"></i>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-red-600 text-xs mt-1">{{ form.errors.password }}</p>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input v-model="form.remember" type="checkbox" class="rounded border-gray-300 text-brand focus:ring-brand" />
                        Recordarme
                    </label>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-brand hover:bg-brand-dark text-white font-medium py-2.5 rounded-xl text-sm transition-colors disabled:opacity-50 shadow-sm flex items-center justify-center gap-2"
                    >
                        <i v-if="form.processing" class="pi pi-spin pi-spinner"></i>
                        {{ form.processing ? 'Ingresando...' : 'Iniciar sesión' }}
                    </button>
                </form>

                <p class="text-center text-xs text-gray-400 mt-6 flex items-center justify-center gap-1.5">
                    <i class="pi pi-shield"></i>
                    Acceso restringido solo para personal autorizado
                </p>
            </div>
        </div>
    </div>
</template>