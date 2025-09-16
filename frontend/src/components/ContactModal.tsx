import React, { useState } from 'react';
import { Dialog, DialogTitle, DialogContent, DialogActions, Button, TextField, Box, Alert, useTheme } from '@mui/material';
import { apiJson } from '../utils/api';

interface ContactModalProps {
  open: boolean;
  onClose: () => void;
}

const ContactModal: React.FC<ContactModalProps> = ({ open, onClose }) => {
  const theme = useTheme();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [message, setMessage] = useState('');
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    setSuccess(null);
    try {
      // Endpunkt wie im alten Backend, ggf. anpassen
      await apiJson('/api/contact', {
        method: 'POST',
        body: { name, email, message },
      });
      setSuccess('Nachricht erfolgreich gesendet!');
      setName('');
      setEmail('');
      setMessage('');
    } catch (err: any) {
      setError(err.message || 'Fehler beim Senden der Nachricht.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <DialogTitle sx={{ bgcolor: theme.palette.background.paper, fontWeight: 600 }}>Kontaktformular</DialogTitle>
      <DialogContent sx={{ bgcolor: theme.palette.background.paper }}>
        {success && <Alert severity="success" sx={{ mb: 2 }}>{success}</Alert>}
        {error && <Alert severity="error" sx={{ mb: 2 }}>{error}</Alert>}
        <Box component="form" onSubmit={handleSubmit} sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
          <TextField
            label="Name"
            value={name}
            onChange={e => setName(e.target.value)}
            required
            autoFocus
          />
          <TextField
            label="E-Mail"
            type="email"
            value={email}
            onChange={e => setEmail(e.target.value)}
            required
          />
          <TextField
            label="Nachricht"
            value={message}
            onChange={e => setMessage(e.target.value)}
            required
            multiline
            minRows={4}
          />
          <DialogActions sx={{ px: 0 }}>
            <Button onClick={onClose} color="secondary" variant="outlined" disabled={loading}>
              Abbrechen
            </Button>
            <Button type="submit" color="primary" variant="contained" disabled={loading}>
              Senden
            </Button>
          </DialogActions>
        </Box>
      </DialogContent>
    </Dialog>
  );
};

export default ContactModal;
