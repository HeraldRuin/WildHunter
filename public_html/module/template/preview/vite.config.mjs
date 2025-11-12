import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig((mode) => ({
  base: 'dist',
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
        app:'js/preview.js',
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
  plugins: [],
}));
