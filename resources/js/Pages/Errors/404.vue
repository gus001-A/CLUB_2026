<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="error-page">
        <div class="error-page__container">
            <div class="error-page__content">
                <div class="error-page__code">404</div>
                <h1 class="error-page__title">Página no encontrada</h1>
                <p class="error-page__description">
                    Lo sentimos, la página que estás buscando no existe o ha sido movida.
                </p>
                <Link href="/" class="error-page__btn">
                    <i class="pi pi-home"></i>
                    Volver al inicio
                </Link>
            </div>
        </div>
    </div>
</template>

<style scoped>
.error-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f7f7f8;
    padding: 2rem;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

.error-page__container {
    max-width: 500px;
    width: 100%;
}

.error-page__content {
    text-align: center;
    background: white;
    padding: 3rem 2rem;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid #ece9e7;
}

.error-page__code {
    font-size: 6rem;
    font-weight: 800;
    color: #C81E3A;
    font-family: 'Fraunces', Georgia, serif;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.error-page__title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #171412;
    margin: 0 0 0.5rem;
}

.error-page__description {
    color: #8A8481;
    font-size: 0.95rem;
    margin: 0 0 2rem;
    line-height: 1.6;
}

.error-page__btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.7rem 1.5rem;
    background: #C81E3A;
    color: white;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.error-page__btn:hover {
    background: #A6152D;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(200, 30, 58, 0.3);
}
</style>