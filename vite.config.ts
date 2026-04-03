import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    build: {
        lib: {
            entry: path.resolve(__dirname, 'resources/js/index.ts'),
            name: 'YjsoftAttendance',
            fileName: 'module',
            formats: ['iife'],
        },
        outDir: 'dist',
        rollupOptions: {
            output: {
                entryFileNames: 'js/[name].iife.js',
                assetFileNames: (assetInfo: { name?: string }) => {
                    if (assetInfo.name?.endsWith('.css')) {
                        return 'css/[name][extname]';
                    }
                    return 'assets/[name][extname]';
                },
            },
        },
        emptyOutDir: true,
        minify: true,
        sourcemap: true,
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
});
