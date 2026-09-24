import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
    plugins: [
        vue(),
    ],

    // All built assets will be served from /vendor/laraviz/
    base: '/vendor/laraviz/',

    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },

    build: {
        outDir: 'public/laraviz',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: resolve(__dirname, 'resources/js/main.js'),
            output: {
                chunkFileNames: 'chunks/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]',
                entryFileNames: '[name]-[hash].js',
            },
        },
    },

    server: {
        port: 5173,
        strictPort: true,
        cors: true,
    },
})
