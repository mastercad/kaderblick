import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  plugins: [
    react(),
    VitePWA({
      registerType: 'autoUpdate',
      manifest: {
        name: 'Kaderblick Fußballverein',
        short_name: 'Kaderblick',
        start_url: '.',
        display: 'standalone',
        background_color: '#ffffff',
        theme_color: '#1976d2',
        description: 'Die Vereinsapp für Mitglieder, Teams und Fans.',
        icons: [
          {
            src: '/images/icon-192.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: '/images/icon-512.png',
            sizes: '512x512',
            type: 'image/png'
          }
        ]
      },
      workbox: {
        globPatterns: ['**/*.{js,css,html,png,svg,ico,json}'],
        globIgnores: [
          'uploads/**',
        ]
      }
    }),
  ],
  /* Wahrscheinlich sinnfrei, bleibt aber erstmal drin, der login modal für google sso jetzt erstmal so funktioniert */
  server: {
    middlewareMode: false,
    setupMiddlewares(middlewares) {
      middlewares.use((req, res, next) => {
        res.setHeader('Cross-Origin-Opener-Policy', 'unsafe-none');
        next();
      });
      return middlewares;
    }
  },
  build: {
    sourcemap: true
  }
})
