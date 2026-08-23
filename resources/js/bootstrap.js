/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// 🔧 FIX: antes esto se importaba de forma dinámica/async (await import('pusher-js'))
// dentro de una función aparte, lo que generaba una condición de carrera: si
// existía OTRA inicialización de Echo en algún otro archivo (ej.
// resources/js/echo.js) que corriera de forma síncrona al cargar la página,
// esa otra alcanzaba a ejecutarse ANTES de que este import dinámico
// terminara — por eso el error de Pusher aparecía ANTES que los logs de
// este archivo. Con un import estático normal, esto se resuelve en cuanto
// el módulo carga, sin esa ventana de carrera.
window.Pusher = Pusher;

// 🔧 FIX: antes la key/host estaban escritos literalmente en este archivo
// ('hxdfzgjxd8vcjkpgatu6', 'localhost', 8080...). Aunque coincidían con tu
// .env en este momento, quedaban duplicados a mano — si algún día cambias
// la key en el .env (ej. al pasar a producción), este archivo se queda con
// el valor viejo silenciosamente. Ahora se lee directo de
// import.meta.env.VITE_REVERB_*, que Vite ya expone automáticamente desde
// tu .env sin configuración adicional (una vez quitado el "define" roto de
// vite.config.js).
const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST;
const reverbPort = import.meta.env.VITE_REVERB_PORT;
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';

if (!reverbKey) {
    console.error(
        '❌ VITE_REVERB_APP_KEY llegó vacío. Verifica que tu .env tenga ' +
        'VITE_REVERB_APP_KEY="${REVERB_APP_KEY}" y que reiniciaste "npm run dev" ' +
        '(los cambios en .env y vite.config.js requieren reinicio completo, no basta con guardar).'
    );
}

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: reverbKey,
    wsHost: reverbHost,
    wsPort: reverbPort,
    wssPort: reverbPort,
    forceTLS: reverbScheme === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        }
    }
});

console.log('✅ Echo inicializado con Reverb:', {
    key: reverbKey ? reverbKey.slice(0, 4) + '…' : '(vacío)',
    host: reverbHost,
    port: reverbPort,
    scheme: reverbScheme,
});

// Verificar conexión del socket
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('🔌✅ Socket conectado a Reverb');
});
window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('🔌❌ Error de conexión con Reverb:', err);
});