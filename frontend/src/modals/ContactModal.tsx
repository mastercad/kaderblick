import React, { useState } from 'react';
import { Button, TextField, Box, Alert } from '@mui/material';
import { apiJson } from '../utils/api';
import BaseModal from './BaseModal';

interface ContactModalProps {
  open: boolean;
  onClose: () => void;
}

const ContactModal: React.FC<ContactModalProps> = ({ open, onClose }) => {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [message, setMessage] = useState('');
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async () => {
    setLoading(true);
    setError(null);
    setSuccess(null);
    try {
      await apiJson('/api/contact', {
        method: 'POST',
        body: { name, email, message },
      });
      setSuccess('Nachricht erfolgreich gesendet!');
      setName('');
      setEmail('');
      setMessage('');
      setTimeout(() => {
        onClose();
      }, 2000);
    } catch (err: any) {
      setError(err.message || 'Fehler beim Senden der Nachricht.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <BaseModal
      open={open}
      onClose={onClose}
      title="Kontaktformular"
      maxWidth="sm"
      actions={
        <>
          <Button onClick={onClose} color="secondary" variant="outlined" disabled={loading}>
            Abbrechen
          </Button>
          <Button onClick={handleSubmit} color="primary" variant="contained" disabled={loading}>
            Senden
          </Button>
        </>
      }
    >
      <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2, mt: 1 }}>
        {success && <Alert severity="success">{success}</Alert>}
        {error && <Alert severity="error">{error}</Alert>}
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
      </Box>
    </BaseModal>
  );
};

export default ContactModal;
