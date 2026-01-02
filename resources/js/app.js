import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

// Board color classes mapping
const boardColorClasses = {
    blue: 'bg-blue-500 ring-blue-500',
    red: 'bg-red-500 ring-red-500',
    purple: 'bg-purple-500 ring-purple-500',
    green: 'bg-green-500 ring-green-500',
    yellow: 'bg-yellow-500 ring-yellow-500',
    emerald: 'bg-emerald-500 ring-emerald-500',
    gray: 'bg-gray-500 ring-gray-500',
    pink: 'bg-pink-500 ring-pink-500',
}

// Status color classes
const statusClasses = {
    purple: 'border-purple-200 bg-purple-50 text-purple-700',
    emerald: 'border-emerald-200 bg-emerald-50 text-emerald-500',
    green: 'border-green-200 bg-green-50 text-green-600',
    blue: 'border-blue-200 bg-blue-50 text-blue-700',
    red: 'border-red-200 bg-red-50 text-red-500',
    gray: 'border-gray-200 bg-gray-100 text-gray-700',
    yellow: 'border-yellow-200 bg-yellow-50 text-yellow-500',
    pink: 'border-pink-200 bg-pink-50 text-pink-700',
}

const statusDotColors = {
    orange: 'bg-orange-500',
    yellow: 'bg-yellow-500',
    blue: 'bg-blue-500',
    purple: 'bg-purple-500',
    green: 'bg-green-500',
    emerald: 'bg-emerald-500',
    red: 'bg-red-500',
    gray: 'bg-gray-500',
    pink: 'bg-pink-500',
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} | ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        // 1️⃣ Create the Vue app instance
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // 2️⃣ Register global properties
        vueApp.config.globalProperties.$statusClasses = statusClasses;
        vueApp.config.globalProperties.$boardColorClasses = boardColorClasses;
        vueApp.config.globalProperties.$statusDotColors = statusDotColors;

        // 3️⃣ Mount
        vueApp.mount(el);

        return vueApp;
    },
    progress: {
        color: '#4B5563',
    },
});
