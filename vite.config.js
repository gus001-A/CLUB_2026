import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    // 🔧 FIX: se quitó el bloque "define" que había aquí. Intentaba exponer
    // las variables VITE_REVERB_* leyendo `process.env` (contexto de
    // Node.js dentro de este archivo), pero Node NUNCA carga tu .env
    // automáticamente — solo Vite lo hace, de forma nativa, para exponerlas
    // al navegador vía `import.meta.env`. Ese `define` no solo era
    // innecesario: estaba ACTIVAMENTE rompiendo el mecanismo automático de
    // Vite, dejando `import.meta.env.VITE_REVERB_APP_KEY` en `undefined`
    // sin importar qué tuvieras en tu .env. Con este bloque eliminado,
    // Vite vuelve a exponer solo las variables VITE_* del .env,
    // automáticamente, sin configuración extra.
});