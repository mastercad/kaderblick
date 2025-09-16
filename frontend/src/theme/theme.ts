import { createTheme, ThemeOptions } from '@mui/material/styles';

// Gemeinsame Theme-Basis
const baseTheme: ThemeOptions = {
  typography: {
    fontFamily: '"Roboto", "Helvetica", "Arial", sans-serif',
    h1: {
      fontWeight: 600,
      fontSize: '2.5rem',
      '@media (max-width:600px)': {
        fontSize: '2rem',
      },
    },
    h2: {
      fontWeight: 600,
      fontSize: '2rem',
      '@media (max-width:600px)': {
        fontSize: '1.75rem',
      },
    },
    h3: {
      fontWeight: 600,
      fontSize: '1.75rem',
      '@media (max-width:600px)': {
        fontSize: '1.5rem',
      },
    },
    h4: {
      fontWeight: 600,
      fontSize: '1.5rem',
      '@media (max-width:600px)': {
        fontSize: '1.25rem',
      },
    },
    h5: {
      fontSize: '1.25rem',
      '@media (max-width:600px)': {
        fontSize: '1.1rem',
      },
    },
    h6: {
      fontSize: '1rem',
      '@media (max-width:600px)': {
        fontSize: '0.95rem',
      },
    },
  },
  shape: {
    borderRadius: 8,
  },
  breakpoints: {
    values: {
      xs: 0,
      sm: 600,
      md: 960,
      lg: 1280,
      xl: 1920,
    },
  },
  components: {
    MuiButton: {
      styleOverrides: {
        root: {
          textTransform: 'none',
          fontWeight: 500,
          minHeight: 44, // Better touch targets
          '@media (max-width:600px)': {
            minHeight: 48,
          },
        },
      },
    },
    MuiIconButton: {
      styleOverrides: {
        root: {
          '@media (max-width:600px)': {
            padding: 8,
          },
        },
      },
    },
    MuiCard: {
      styleOverrides: {
        root: {
          boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
          '&:hover': {
            boxShadow: '0 4px 16px rgba(0,0,0,0.15)',
          },
        },
      },
    },
    MuiToolbar: {
      styleOverrides: {
        root: {
          minHeight: '64px !important',
          '@media (max-width:600px)': {
            minHeight: '56px !important',
            paddingLeft: 8,
            paddingRight: 8,
          },
        },
      },
    },
    MuiContainer: {
      styleOverrides: {
        root: {
          '@media (max-width:600px)': {
            paddingLeft: 8,
            paddingRight: 8,
          },
        },
      },
    },
  },
};

// Light Theme mit Grün-Gradient
export const lightTheme = createTheme({
  ...baseTheme,
  palette: {
    mode: 'light',
    primary: {
      main: '#2e7d32', // Grün
      light: '#60ad5e',
      dark: '#005005',
      contrastText: '#ffffff',
    },
    secondary: {
      main: '#4caf50', // Helleres Grün
      light: '#80e27e',
      dark: '#087f23',
      contrastText: '#ffffff',
    },
    success: {
      main: '#66bb6a',
    },
    background: {
      default: '#fafafa',
      paper: '#ffffff',
    },
    text: {
      primary: '#2e2e2e',
      secondary: '#666666',
    },
  },
  components: {
    ...baseTheme.components,
    MuiAppBar: {
      styleOverrides: {
        root: {
          background: 'linear-gradient(45deg, #2e7d32 30%, #4caf50 90%)',
        },
      },
    },
  },
});

// Dark Theme mit Grün-Accent
export const darkTheme = createTheme({
  ...baseTheme,
  palette: {
    mode: 'dark',
    primary: {
      main: '#4caf50', // Grün
      light: '#80e27e',
      dark: '#2e7d32',
      contrastText: '#ffffff',
    },
    secondary: {
      main: '#66bb6a', // Helleres Grün
      light: '#98ee99',
      dark: '#338a3e',
      contrastText: '#000000',
    },
    success: {
      main: '#4caf50',
    },
    background: {
      default: '#121212',
      paper: '#1e1e1e',
    },
    text: {
      primary: '#ffffff',
      secondary: '#bbbbbb',
    },
  },
  components: {
    ...baseTheme.components,
    MuiAppBar: {
      styleOverrides: {
        root: {
          background: 'linear-gradient(45deg, #1b5e20 30%, #2e7d32 90%)',
        },
      },
    },
    MuiCard: {
      styleOverrides: {
        root: {
          backgroundColor: '#1e1e1e',
          boxShadow: '0 2px 8px rgba(0,0,0,0.3)',
          '&:hover': {
            boxShadow: '0 4px 16px rgba(0,0,0,0.4)',
          },
        },
      },
    },
  },
});
