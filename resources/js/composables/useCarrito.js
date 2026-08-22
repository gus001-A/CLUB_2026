// resources/js/composables/useCarrito.js
import { ref, watch, computed, readonly } from 'vue';
import axios from 'axios';

const CARRITO_STORAGE_KEY = 'club_fantasias_carrito';

// Estado global del carrito (singleton)
const carrito = ref([]);
const cargado = ref(false);

// Cargar carrito desde localStorage
function cargarCarrito() {
    try {
        const stored = localStorage.getItem(CARRITO_STORAGE_KEY);
        if (stored) {
            carrito.value = JSON.parse(stored);
        } else {
            carrito.value = [];
        }
    } catch (e) {
        console.error('Error al cargar carrito:', e);
        carrito.value = [];
    }
    cargado.value = true;
}

// Guardar carrito en localStorage
function guardarCarrito() {
    try {
        localStorage.setItem(CARRITO_STORAGE_KEY, JSON.stringify(carrito.value));
    } catch (e) {
        console.error('Error al guardar carrito:', e);
    }
}

// 🔧 FIX: el carrito vivía SOLO en localStorage. ShopController::checkout()
// lee el carrito desde la SESIÓN de Laravel ($request->session()->get('carrito')),
// que nunca se llenaba porque nada llamaba al endpoint de sincronización
// (tienda.carrito.sincronizar). Resultado: al ir a /tienda/checkout, el
// backend siempre veía el carrito vacío y te regresaba con
// redirect()->route('tienda') — de ahí que "se recargara la página" justo
// al hacer clic en Finalizar compra.
//
// Con este watch, cada vez que el carrito cambia (agregar, quitar,
// actualizar cantidad) se manda también al backend, con un pequeño debounce
// para no disparar una petición por cada tecla/click seguido.
let syncTimeout = null;
function sincronizarConServidor() {
    clearTimeout(syncTimeout);
    syncTimeout = setTimeout(() => {
        axios.post('/tienda/carrito/sincronizar', { carrito: carrito.value })
            .catch((e) => console.error('No se pudo sincronizar el carrito con el servidor:', e));
    }, 400);
}

// Inicializar carrito
cargarCarrito();

// Watch para guardar automáticamente (localStorage + servidor)
watch(carrito, () => {
    guardarCarrito();
    sincronizarConServidor();
}, { deep: true });

// ---------- FUNCIONES DEL CARRITO ----------

function agregarAlCarrito(producto) {
    if (!producto || !producto.id) return false;

    // Determinar la talla (si existe)
    const tallaItem = producto.talla || '';

    // Buscar si ya existe (mismo ID y misma talla)
    const existenteIndex = carrito.value.findIndex(
        item => item.id === producto.id && (item.talla || '') === tallaItem
    );

    if (existenteIndex !== -1) {
        // Si existe, sumar cantidad
        carrito.value[existenteIndex].cantidad += producto.cantidad || 1;
    } else {
        // Si no existe, agregar nuevo
        carrito.value.push({
            id: producto.id,
            nombre: producto.nombre || 'Producto',
            precio: producto.precio || 0,
            imagen: producto.imagen || producto.imagenes?.[0] || '/images/shared/placeholder.jpg',
            cantidad: producto.cantidad || 1,
            talla: tallaItem,
            categoria: producto.categoria || '',
            marca: producto.marca || '',
            precioOriginal: producto.precioOriginal || null,
            descuento: producto.descuento || 0
        });
    }

    guardarCarrito();
    return true;
}

function quitarDelCarrito(id, talla = null) {
    if (!id) return false;

    const tallaItem = talla || '';

    // Buscar el índice del producto
    const idx = carrito.value.findIndex(
        item => item.id === id && (item.talla || '') === tallaItem
    );

    if (idx !== -1) {
        carrito.value.splice(idx, 1);
        guardarCarrito();
        return true;
    }
    return false;
}

function actualizarCantidad(id, cantidad, talla = null) {
    const tallaItem = talla || '';
    const item = carrito.value.find(
        i => i.id === id && (i.talla || '') === tallaItem
    );
    if (item) {
        if (cantidad <= 0) {
            return quitarDelCarrito(id, talla);
        }
        item.cantidad = cantidad;
        guardarCarrito();
        return true;
    }
    return false;
}

function vaciarCarrito() {
    carrito.value = [];
    guardarCarrito();
}

function obtenerTotal() {
    return carrito.value.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);
}

function obtenerCantidadItems() {
    return carrito.value.reduce((acc, item) => acc + item.cantidad, 0);
}

function estaEnCarrito(productoId, talla = null) {
    const tallaItem = talla || '';
    return carrito.value.some(
        item => item.id === productoId && (item.talla || '') === tallaItem
    );
}

function obtenerItem(productoId, talla = null) {
    const tallaItem = talla || '';
    return carrito.value.find(
        item => item.id === productoId && (item.talla || '') === tallaItem
    );
}

// Computed
const subtotal = computed(() => obtenerTotal());
const totalItems = computed(() => obtenerCantidadItems());

export function useCarrito() {
    if (!cargado.value) {
        cargarCarrito();
    }

    return {
        carrito: readonly(carrito),
        subtotal,
        totalItems,
        agregarAlCarrito,
        quitarDelCarrito,
        actualizarCantidad,
        vaciarCarrito,
        estaEnCarrito,
        obtenerItem,
        obtenerTotal,
        obtenerCantidadItems,
        recargar: cargarCarrito,
    };
}