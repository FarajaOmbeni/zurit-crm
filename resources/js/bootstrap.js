import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

// Set up CSRF token interceptor
window.axios.interceptors.request.use(function (config) {
    // Try to get CSRF token from multiple sources (in priority order)
    let token = null;

    // Priority 1: Get from meta tag (always updated on page load/refresh)
    const metaToken = document.head.querySelector('meta[name="csrf-token"]');
    if (metaToken) {
        token = metaToken.content;
    }

    // Priority 2: Fallback to cookie
    if (!token) {
        const csrfCookie = document.cookie.split(';').find(c => c.trim().startsWith('XSRF-TOKEN='));
        if (csrfCookie) {
            token = decodeURIComponent(csrfCookie.split('=')[1]);
            config.headers['X-XSRF-TOKEN'] = token;
        }
    } else {
        config.headers['X-CSRF-TOKEN'] = token;
    }

    return config;
}, function (error) {
    return Promise.reject(error);
});
