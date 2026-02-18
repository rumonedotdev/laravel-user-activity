import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue'
import laravel from "laravel-vite-plugin"
import path from "node:path"

// https://vite.dev/config/
export default defineConfig({
    plugins: [
        laravel({
            input: ['views/ts/main.ts', 'views/css/style.css'],
        }),
        vue()
    ],

    root: path.resolve(__dirname, './'),

    build: {
        outDir: "dist",
        emptyOutDir: true,
        manifest: true,

        rollupOptions: {
            input: ['views/ts/main.ts', 'views/css/style.css'],
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: '[name].js',
                assetFileNames: '[name].[ext]',
            },
        }
    },


    resolve: {
        alias: {
            '@': path.resolve(__dirname, './views/ts'),
        }
    }
})
