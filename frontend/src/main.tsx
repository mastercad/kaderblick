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
