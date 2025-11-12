import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue'
import path from 'path';

const modulesMap = {
    builder: '../../module/page/admin/scss/builder.scss',
    live:'module/template/admin/scss/live.scss'
}

export default defineConfig((mode) => ({
  base: '/themes/admin/dist',
  resolve: {
    alias: {
      '@': path.resolve(__dirname),
    }
  },
  build: {
    sourcemap: mode === 'development',
    outDir: 'dist',
    rollupOptions: {
      input: {
        app:'js/app.js',
        templateLive:'module/template/admin/live/index.js',
        style: 'scss/app.scss',
        ...modulesMap,
      },
      output: {
        manualChunks(id) {
          if (id.includes('node_modules')) {
            return 'vendor';
          }
          return null;
        },
        // Định dạng cho JS
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name?.endsWith('.css')) {
            return 'css/[name][extname]'
          }
          return 'assets/[name][extname]'
        },
      },
    },
  },
  plugins: [vue()],
}));
