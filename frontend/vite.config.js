import tailwindcss from '@tailwindcss/vite'
import react from '@vitejs/plugin-react'
import { defineConfig } from 'vite'

export default defineConfig({
  plugins: [react(), tailwindcss()],
  base: './',
  publicDir: false,
  build: {
    outDir: '../assets',
    emptyOutDir: true,
    rolldownOptions: {
      input: 'src/main.jsx',
      output: {
        entryFileNames: 'app.js',
        assetFileNames: 'app[extname]',
      },
    },
  },
})
