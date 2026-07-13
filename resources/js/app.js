// resources/js/app.js
import '../css/app.css';
import './bootstrap';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';

import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import 'primeflex/primeflex.css';


import Button from 'primevue/button';
import Card from 'primevue/card';
import Avatar from 'primevue/avatar';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
// ⚠️ Comment en PrimeVue 4.x se llama 'Comment' pero puede no existir
// import Comment from 'primevue/comment'; // ❌ ESTO CAUSA EL ERROR
import Divider from 'primevue/divider';
import Galleria from 'primevue/galleria';
import Chip from 'primevue/chip';
import Tag from 'primevue/tag';
import Badge from 'primevue/badge';
import ProgressSpinner from 'primevue/progressspinner';
import Skeleton from 'primevue/skeleton';
import ToastService from 'primevue/toastservice';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
import ConfirmationService from 'primevue/confirmationservice';
import Dialog from 'primevue/dialog';
import Menubar from 'primevue/menubar';

const appName = import.meta.env.VITE_APP_NAME || 'Club de Fantasías';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue')
    ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        
        // ========== CONFIGURAR PRIMEVUE 4.x ==========
        app.use(plugin);
        app.use(ZiggyVue);
        app.use(PrimeVue, {
            theme: {
                preset: Aura,
                options: {
                    darkModeSelector: '.p-dark',
                }
            }
        });
        app.use(ToastService);
        app.use(ConfirmationService);
        
        // ========== REGISTRAR COMPONENTES GLOBALES ==========
        app.component('PvButton', Button);
        app.component('PvCard', Card);
        app.component('PvAvatar', Avatar);
        app.component('PvInputText', InputText);
        app.component('PvTextarea', Textarea);
        // app.component('PvComment', Comment); // ❌ Comentado porque no existe en 4.x
        app.component('PvDivider', Divider);
        app.component('PvGalleria', Galleria);
        app.component('PvChip', Chip);
        app.component('PvTag', Tag);
        app.component('PvBadge', Badge);
        app.component('PvProgressSpinner', ProgressSpinner);
        app.component('PvSkeleton', Skeleton);
        app.component('PvToast', Toast);
        app.component('PvConfirmDialog', ConfirmDialog);
        app.component('PvDialog', Dialog);
        app.component('PvMenubar', Menubar);
        
        app.mount(el);
        
        return app;
    },
    progress: {
        color: '#C81E3A',
    },
});