import React from 'react';
import { Container, Typography, Box } from '@mui/material';

const NotFound: React.FC = () => (
  <Container maxWidth="md" sx={{ py: 8, textAlign: 'center' }}>
    <Typography variant="h2" color="error" gutterBottom>
      404 Not Found
    </Typography>
    <Typography variant="body1" sx={{ mb: 2 }}>
      Die angeforderte Seite wurde nicht gefunden.
    </Typography>
    <Box sx={{ fontSize: 48, mb: 2 }}>⚽️</Box>
    <Typography variant="body2" color="text.secondary">
      Bitte prüfe die URL oder gehe zurück zur Startseite.
    </Typography>
  </Container>
);

export default NotFound;
