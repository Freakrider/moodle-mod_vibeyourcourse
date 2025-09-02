import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  build: {
    outDir: '../build',
    emptyOutDir: true,
    rollupOptions: {
      output: {
        entryFileNames: 'bundle.js',
        chunkFileNames: 'bundle.js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name.endsWith('.css')) {
            return 'bundle.css';
          }
          return '[name].[ext]';
        }
      }
    }
  },
  base: './',
})
