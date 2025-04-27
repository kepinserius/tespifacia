import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
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
            vue: 'vue/dist/vue.esm-bundler.js',
            // Polyfills untuk browser
            stream: 'stream-browserify',
            path: 'path-browserify',
            util: 'util/',
            buffer: 'buffer/',
        },
    },
    optimizeDeps: {
        esbuildOptions: {
            // Mengatasi masalah platform dengan esbuild
            platform: 'browser',
            supported: { 'top-level-await': true },
            define: {
                global: 'globalThis',
            },
        }
    },
    build: {
        // Mengatasi masalah cross-platform
        target: 'es2015',
        commonjsOptions: {
            transformMixedEsModules: true,
        },
        rollupOptions: {
            // Mengatasi masalah dengan node polyfills
            onwarn(warning, warn) {
                if (warning.code === 'MODULE_LEVEL_DIRECTIVE') {
                    return;
                }
                warn(warning);
            },
        },
    },
    server: {
        // Konfigurasi server development
        hmr: {
            host: 'localhost',
        },
        watch: {
            usePolling: true,
        },
    },
});
