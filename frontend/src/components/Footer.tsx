import React from 'react';
import { Box, Container, Link, Typography, useTheme } from '@mui/material';

const Footer: React.FC = () => {
  const theme = useTheme();
  return (
    <Box
      component="footer"
      sx={{
        mt: 'auto',
        py: 3,
        background: theme.palette.mode === 'dark'
          ? 'linear-gradient(45deg, #1b5e20 30%, #2e7d32 90%)'
          : 'linear-gradient(45deg, #2e7d32 30%, #4caf50 90%)',
        color: theme.palette.getContrastText(theme.palette.primary.main),
      }}
    >
      <Container maxWidth="md" sx={{ display: 'flex', flexDirection: { xs: 'column', sm: 'row' }, justifyContent: 'space-between', alignItems: 'center', gap: 2 }}>
        <Box>
          <Typography variant="body2" sx={{ fontWeight: 500 }}>
            &copy; {new Date().getFullYear()} Kaderblick
          </Typography>
        </Box>
        <Box sx={{ display: 'flex', gap: 2 }}>
          <Link href="/imprint" color="inherit" underline="hover">
            Impressum
          </Link>
          <Link href="/privacy" color="inherit" underline="hover">
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
