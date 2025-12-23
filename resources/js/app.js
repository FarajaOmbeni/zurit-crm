import "../css/app.css";
import "./bootstrap";

import { createInertiaApp, router } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createApp, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: "#FF5B5D",
    },
    onError: (error) => {
        // Handle CSRF token mismatch (419 Page Expired)
        if (error.response && error.response.status === 419) {
            alert('Your session has expired. The page will now refresh to get a new session.');
            window.location.reload();
        }
    },
});

// Update CSRF token in meta tag after each Inertia navigation
// This ensures the token stays fresh even during client-side navigation
router.on('navigate', (event) => {
    const csrfToken = event.detail.page.props.csrf_token;
    if (csrfToken) {
        const metaTag = document.head.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            metaTag.setAttribute('content', csrfToken);
        }
    }
});
