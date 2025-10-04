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
          transition: 'all 0.2s ease-in-out',
        },
        contained: {
          boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
          '&:hover': {
            boxShadow: '0 4px 8px rgba(0,0,0,0.2)',
            transform: 'translateY(-1px)',
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
    MuiAvatar: {
      styleOverrides: {
        root: {
          backgroundColor: '#018606',
          transition: 'all 0.2s ease-in-out',
          '&:hover': {
            backgroundColor: '#02b008',
          },
        },
        colorDefault: {
          backgroundColor: '#018606',
          color: '#ffffff',
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
      main: '#018606', // Primäres Grün
      light: '#02b008', // Hellere, frischere Variante für Hover
      dark: '#015504', // Dunklere Variante
      contrastText: '#ffffff',
      danger: '#d32f2f', // Kräftiges Rot für Warnungen/Löschen
    },
    secondary: {
      main: '#00c853', // Frisches, lebendiges Grün
      light: '#5efc82',
      dark: '#009624',
      contrastText: '#ffffff',
    },
    success: {
      main: '#00e676', // Leuchtend grün für Erfolg
    },
    background: {
      default: '#f5f5f5', // Etwas wärmer als zuvor
      paper: '#ffffff',
    },
    text: {
      primary: '#1a1a1a', // Satteres Schwarz
      secondary: '#424242', // Dunkleres Grau für besseren Kontrast
    },
  },
  components: {
    ...baseTheme.components,
    MuiAppBar: {
      styleOverrides: {
        root: {
          background: 'linear-gradient(135deg, #018606 0%, #00c853 100%)', // Frischer Gradient
        },
      },
    },
    MuiButton: {
      ...baseTheme.components?.MuiButton,
      styleOverrides: {
        ...baseTheme.components?.MuiButton?.styleOverrides,
        containedPrimary: {
          backgroundColor: '#018606',
          '&:hover': {
            backgroundColor: '#02b008', // Heller beim Hover
          },
        },
        containedSecondary: {
          backgroundColor: '#00c853',
          '&:hover': {
            backgroundColor: '#5efc82', // Heller beim Hover
          },
        },
        outlined: {
          borderWidth: 2,
          '&:hover': {
            borderWidth: 2,
            backgroundColor: 'rgba(1, 134, 6, 0.08)', // Leichter grüner Hintergrund
          },
        },
        text: {
          '&:hover': {
            backgroundColor: 'rgba(1, 134, 6, 0.08)', // Leichter grüner Hintergrund
          },
        },
      },
    },
    MuiFab: {
      styleOverrides: {
        root: {
          transition: 'all 0.2s ease-in-out',
        },
        primary: {
          backgroundColor: '#018606',
          '&:hover': {
            backgroundColor: '#02b008', // Heller beim Hover
            transform: 'scale(1.05)',
          },
        },
        secondary: {
          backgroundColor: '#00c853',
          '&:hover': {
            backgroundColor: '#5efc82', // Heller beim Hover
            transform: 'scale(1.05)',
          },
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
      main: '#02b008', // Helles, lebendiges Grün für Dark Mode
      light: '#5efc82',
      dark: '#018606',
      contrastText: '#000000',
    },
    secondary: {
      main: '#00e676', // Leuchtendes Grün als Akzent
      light: '#66ffa6',
      dark: '#00b248',
      contrastText: '#000000',
    },
    success: {
      main: '#00e676',
    },
    background: {
      default: '#0a0a0a', // Tieferes Schwarz
      paper: '#1a1a1a', // Etwas heller für Kontrast
    },
    text: {
      primary: '#ffffff',
      secondary: '#e0e0e0', // Helleres Grau für bessere Lesbarkeit
    },
  },
  components: {
    ...baseTheme.components,
    MuiAppBar: {
      styleOverrides: {
        root: {
          background: 'linear-gradient(135deg, #018606 0%, #02b008 100%)', // Frischer Gradient
        },
      },
    },
    MuiCard: {
      styleOverrides: {
        root: {
          backgroundColor: '#1a1a1a',
          boxShadow: '0 2px 12px rgba(2, 176, 8, 0.15)', // Grüner Gloweffekt
          '&:hover': {
            boxShadow: '0 4px 20px rgba(2, 176, 8, 0.25)', // Stärkerer Glow beim Hover
          },
        },
      },
    },
  },
});
