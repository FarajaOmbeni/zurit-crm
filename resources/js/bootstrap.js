import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

// Set up CSRF token interceptor
window.axios.interceptors.request.use(function (config) {
    // Get CSRF token from meta tag
    const token = document.head.querySelector('meta[name="csrf-token"]');
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token.content;
    } else {
        // Fallback: get from cookie
        const csrfCookie = document.cookie.split(';').find(c => c.trim().startsWith('XSRF-TOKEN='));
        if (csrfCookie) {
            config.headers['X-XSRF-TOKEN'] = decodeURIComponent(csrfCookie.split('=')[1]);
        }
    }
    return config;
}, function (error) {
    return Promise.reject(error);
});
