<!-- resources/js/Components/CarritoResumen.vue -->
<script setup>
import { useCarrito } from '@/composables/useCarrito';

const { carrito, subtotal, totalItems, quitarDelCarrito } = useCarrito();

const emit = defineEmits(['ir-checkout']);

function formatoMoneda(valor) {
    return new Intl.NumberFormat('es-MX').format(valor);
}

function getImageUrl(imagen) {
    if (!imagen) return '/images/shared/placeholder.jpg';
    if (imagen.startsWith('http://') || imagen.startsWith('https://')) return imagen;
    if (imagen.startsWith('/storage/') || imagen.startsWith('/images/')) return imagen;
    return '/storage/' + imagen.replace(/^\/+/, '');
}

function eliminarItem(item) {
    // Usar la talla del item (puede ser undefined)
    const talla = item.talla || '';
    quitarDelCarrito(item.id, talla);
}

function irACheckout() {
    emit('ir-checkout');
}
</script>

<template>
    <div class="cart-resumen">
        <div class="cart-resumen__header">
            <h3><i class="pi pi-shopping-cart"></i> Carrito ({{ totalItems }})</h3>
        </div>

        <div class="cart-resumen__items">
            <div v-for="item in carrito" :key="item.id + (item.talla || '')" class="cart-item">
                <img :src="getImageUrl(item.imagen)" :alt="item.nombre" />
                <div class="cart-item__info">
                    <strong>{{ item.nombre }}</strong>
                    <span>{{ item.cantidad }} × ${{ formatoMoneda(item.precio) }}</span>
                    <span v-if="item.talla" class="cart-item__talla">Talla: {{ item.talla }}</span>
                </div>
                <button class="cart-item__remove" @click="eliminarItem(item)">
                    <i class="pi pi-times"></i>
                </button>
            </div>
            <p v-if="carrito.length === 0" class="cart-empty">Tu carrito está vacío.</p>
        </div>

        <div class="cart-resumen__footer" v-if="carrito.length > 0">
            <div class="cart-resumen__total">
                <span>Total</span>
                <strong>${{ formatoMoneda(subtotal) }}</strong>
            </div>
            <button class="cart-resumen__checkout" @click="irACheckout">
                FINALIZAR COMPRA
            </button>
        </div>
    </div>
</template>

<style scoped>
.cart-resumen {
    background: #fff;
    border: 1px solid var(--line, #e5e5e5);
    border-radius: 16px;
    padding: 1.5rem;
    position: sticky;
    top: 2rem;
}

.cart-resumen__header {
    margin-bottom: 1rem;
}

.cart-resumen__header h3 {
    font-size: 1rem;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.cart-resumen__items {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    margin-bottom: 1rem;
    max-height: 300px;
    overflow-y: auto;
}

.cart-item {
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.cart-item img {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
}

.cart-item__info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.cart-item__info strong {
    font-size: 0.78rem;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cart-item__info span {
    font-size: 0.72rem;
    color: var(--muted, #8a8a8a);
}

.cart-item__talla {
    font-size: 0.65rem;
    color: var(--muted-light, #b7b2af);
}

.cart-item__remove {
    border: none;
    background: none;
    color: var(--muted-light, #b7b2af);
    cursor: pointer;
    flex-shrink: 0;
    padding: 0.2rem;
    transition: color 0.2s ease;
}

.cart-item__remove:hover {
    color: #EF4444;
}

.cart-empty {
    font-size: 0.82rem;
    color: var(--muted-light, #b7b2af);
    text-align: center;
    padding: 1.5rem 0;
}

.cart-resumen__footer {
    border-top: 1px solid var(--line, #e5e5e5);
    padding-top: 1rem;
}

.cart-resumen__total {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 0.75rem;
}

.cart-resumen__total strong {
    color: var(--brand, #C81E3A);
    font-size: 1.3rem;
}

.cart-resumen__checkout {
    width: 100%;
    font-weight: 700;
    border-radius: 10px;
    padding: 0.8rem;
    border: none;
    background: var(--brand, #C81E3A);
    color: #fff;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.cart-resumen__checkout:hover {
    background: var(--brand-dark, #A6152D);
    transform: scale(1.02);
}
</style>