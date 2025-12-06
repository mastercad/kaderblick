import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import App from './App';
import { ToastProvider } from './context/ToastContext';
import { AuthProvider } from './context/AuthContext';
import { ThemeProvider } from '@mui/material/styles';
import { ThemeProvider as CustomThemeProvider } from './context/ThemeContext';
import { BrowserRouter } from 'react-router-dom';
import { lightTheme } from './theme/theme';

import './index.css';
import './styles/tour-tool-tip.css';
import './styles/mobile-responsive.css';

// Service Worker für PWA registrieren
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then(registration => {
        console.info('Service Worker registered:', registration);
      })
      .catch(error => {
        console.error('Service Worker registration failed:', error);
      });
  });
}

const rootElement = document.getElementById('root');
if (!rootElement) throw new Error('Root element not found');

createRoot(rootElement).render(
  <StrictMode>
    <CustomThemeProvider>
      <ThemeProvider theme={lightTheme}>
        <AuthProvider>
          <BrowserRouter>
            <ToastProvider>
              <App />
            </ToastProvider>
          </BrowserRouter>
        </AuthProvider>
      </ThemeProvider>
    </CustomThemeProvider>
  </StrictMode>
);

// App-Ready-Event dispatchen nach dem ersten Render
setTimeout(() => {
  window.dispatchEvent(new Event('app-ready'));
}, 100);
