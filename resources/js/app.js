import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import { createStore } from 'vuex';
import App from './components/App.vue';
import routes from './routes';
import store from './store';
import axios from 'axios';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';

// Add global route function to mimic Laravel's route() helper
window.route = function(name, params = {}, absolute = true) {
    // Map of Laravel route names to Vue route names
    const routeMap = {
        'login': 'login',
        'register': 'register',
        'dashboard': 'dashboard',
        'home': 'home',
        'users.index': 'users.index',
        'users.create': 'users.create',
        'users.edit': 'users.edit',
        'roles.index': 'roles.index',
        'roles.create': 'roles.create',
        'roles.edit': 'roles.edit',
        'categories.index': 'categories.index',
        'categories.create': 'categories.create',
        'categories.edit': 'categories.edit',
        'categories.view': 'categories.view',
        'projects.index': 'projects.index',
        'projects.create': 'projects.create',
        'projects.edit': 'projects.edit',
        'projects.view': 'projects.view',
        'tasks.index': 'tasks.index',
        'tasks.create': 'tasks.create',
        'tasks.edit': 'tasks.edit',
        'tasks.view': 'tasks.view'
    };
    
    // Get the Vue route name from the Laravel route name
    const routeName = routeMap[name] || name;
    
    // Create a temporary router to resolve the route
    const tempRouter = createRouter({
        history: createWebHistory(),
        routes
    });
    
    try {
        // Try to resolve the route
        return tempRouter.resolve({name: routeName, params}).href;
    } catch (error) {
        console.error(`Route [${name}] not defined.`, error);
        // Fallback to default routes if not found
        if (name === 'login') return '/login';
        if (name === 'register') return '/register';
        if (name === 'dashboard') return '/dashboard';
        return '/';
    }
};

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation guards
router.beforeEach((to, from, next) => {
    const isAuthenticated = store.getters['auth/isAuthenticated'];
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
    const requiresAdmin = to.matched.some(record => record.meta.requiresAdmin);

    if (requiresAuth && !isAuthenticated) {
        next({ name: 'login' });
    } else if (requiresAdmin && !store.getters['auth/isAdmin']) {
        next({ name: 'dashboard' });
    } else {
        next();
    }
});

// Konfigurasi axios - Optimized for performance
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['Content-Type'] = 'application/json';
axios.defaults.withCredentials = true; // Penting untuk session cookies

// Dapatkan CSRF token dan base URL dari window.Laravel yang didefinisikan di app.blade.php
if (window.Laravel) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = window.Laravel.csrfToken;
    axios.defaults.baseURL = window.Laravel.baseUrl;
}

// Optimize axios for better performance
axios.defaults.timeout = 10000; // 10 seconds timeout
axios.defaults.maxRedirects = 5; // Limit redirects

// Disable axios debug logs in production
if (process.env.NODE_ENV === 'production') {
    axios.interceptors.request.use(request => {
        return request;
    }, null, { synchronous: true });
}

// Optimized interceptor for better performance and error handling
axios.interceptors.response.use(
    // Success handler - just pass through the response
    response => response,
    // Error handler - optimized for performance
    error => {
        // Only log errors in development
        if (process.env.NODE_ENV !== 'production') {
            console.error('Axios error:', error.message);
        }
        
        // Handle specific error codes
        if (error.response) {
            switch (error.response.status) {
                // Handle 401 Unauthorized errors
                case 401:
                    // Clear auth state
                    store.commit('auth/SET_AUTHENTICATED', false);
                    store.commit('auth/SET_USER', null);
                    
                    // Only redirect if we're not already on the login page
                    if (window.location.pathname !== '/login') {
                        window.location.href = '/login';
                    }
                    break;
                    
                // Handle 419 CSRF token mismatch errors
                case 419:
                    // Get a fresh CSRF token
                    axios.get('/sanctum/csrf-cookie');
                    break;
                    
                // Handle 429 Too Many Requests
                case 429:
                    console.warn('Rate limit exceeded. Please try again later.');
                    break;
            }
        }
        
        return Promise.reject(error);
    }
);

// Add global route function to mimic Laravel's route() helper
window.route = function(name, params = {}, absolute = true) {
    // Map of Laravel route names to Vue route names
    const routeMap = {
        'login': 'login',
        'register': 'register',
        'dashboard': 'dashboard',
        'home': 'home',
        'users.index': 'users.index',
        'users.create': 'users.create',
        'users.edit': 'users.edit',
        'roles.index': 'roles.index',
        'roles.create': 'roles.create',
        'roles.edit': 'roles.edit',
        'categories.index': 'categories.index',
        'categories.create': 'categories.create',
        'categories.edit': 'categories.edit',
        'categories.view': 'categories.view',
        'projects.index': 'projects.index',
        'projects.create': 'projects.create',
        'projects.edit': 'projects.edit',
        'projects.view': 'projects.view',
        'tasks.index': 'tasks.index',
        'tasks.create': 'tasks.create',
        'tasks.edit': 'tasks.edit',
        'tasks.view': 'tasks.view'
    };
    
    const routeName = routeMap[name] || name;
    try {
        return router.resolve({name: routeName, params}).href;
    } catch (error) {
        console.error(`Route [${name}] not defined.`, error);
        return '/';
    }
};

// Inisialisasi aplikasi Vue
const app = createApp(App);
app.use(router);
app.use(store);

// Mount aplikasi setelah DOM siap
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        app.mount('#app');
    });
} else {
    app.mount('#app');
}
