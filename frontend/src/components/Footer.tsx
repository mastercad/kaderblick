
import React from 'react';
import { Box, Container, Link, Typography, useTheme } from '@mui/material';
import { useLocation } from 'react-router-dom';
import { Link as RouterLink } from 'react-router-dom';

const Footer: React.FC = () => {
  const theme = useTheme();
  const location = useLocation();
  const isHome = location.pathname === '/' || location.pathname === '';

  return (
    <Box
      component="footer"
      sx={{
        mt: 'auto',
        py: 3,
        background: isHome
          ? 'transparent'
          : `linear-gradient(135deg, ${theme.palette.primary.main} 0%, ${theme.palette.secondary.main} 100%)`,
        color: isHome
          ? '#fff'
          : theme.palette.primary.contrastText,
      }}
    >
      <Container maxWidth="md" sx={{ display: 'flex', flexDirection: { xs: 'column', sm: 'row' }, justifyContent: 'space-between', alignItems: 'center', gap: 2 }}>
        <Box>
          <Typography variant="body2" sx={{ fontWeight: 500 }}>
            &copy; {new Date().getFullYear()} Kaderblick
          </Typography>
        </Box>
        <Box sx={{ display: 'flex', gap: 2 }}>
          <Link component={RouterLink} to="/imprint" color="inherit" underline="hover">
            Impressum
          </Link>
          <Link component={RouterLink} to="/privacy" color="inherit" underline="hover">
            Datenschutz
          </Link>
          <Link href="#" color="inherit" underline="hover" onClick={e => {
            e.preventDefault();
            const event = new CustomEvent('openContactModal');
            window.dispatchEvent(event);
          }}>
            Kontakt
          </Link>
        </Box>
      </Container>
    </Box>
  );
};

export default Footer;
