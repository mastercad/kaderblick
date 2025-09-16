import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'

import './index.css';
import './styles/mobile-responsive.css';
import App from './App.tsx';
import { ToastProvider } from './context/ToastContext';
import { AuthProvider } from './context/AuthContext.tsx';
import { ThemeProvider, createTheme } from '@mui/material/styles';
import { ThemeProvider as CustomThemeProvider } from './context/ThemeContext';
import { BrowserRouter } from 'react-router-dom';

const theme = createTheme();

const rootElement = document.getElementById('root');
if (!rootElement) throw new Error('Root element not found');

createRoot(rootElement).render(
  <StrictMode>
    <CustomThemeProvider>
      <ThemeProvider theme={theme}>
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
