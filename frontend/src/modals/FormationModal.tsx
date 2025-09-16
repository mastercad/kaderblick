import React, { useState } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TextField,
  MenuItem,
  Box,
  Typography,
  Alert
} from '@mui/material';
import { apiJson } from '../utils/api';

interface FormationType {
  id: number;
  name: string;
}

interface FormationModalProps {
  open: boolean;
  onClose: () => void;
  onCreated?: (formation: any) => void;
}

const FormationModal: React.FC<FormationModalProps> = ({ open, onClose, onCreated }) => {
  const [name, setName] = useState('');
  const [formationType, setFormationType] = useState('');
  const [types, setTypes] = useState<FormationType[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  React.useEffect(() => {
    if (open) {
      apiJson<FormationType[]>('/formation-types')
        .then(setTypes)
        .catch(() => setTypes([]));
    }
  }, [open]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    try {
      const result = await apiJson('/formation/new', {
        method: 'POST',
        body: { name, formationType },
      });
      setLoading(false);
      setName('');
      setFormationType('');
      onCreated?.(result);
      onClose();
    } catch (err: any) {
      setError(err.message || 'Fehler beim Anlegen');
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <DialogTitle>Neue Teamaufstellung erstellen</DialogTitle>
      <form onSubmit={handleSubmit}>
        <DialogContent>
          {error && <Alert severity="error">{error}</Alert>}
          <Box display="flex" gap={2} mb={2}>
            <TextField
              label="Aufstellungsname"
              value={name}
              onChange={e => setName(e.target.value)}
              required
              fullWidth
            />
            <TextField
              label="Sportart"
              select
              value={formationType}
              onChange={e => setFormationType(e.target.value)}
              required
              fullWidth
            >
              {types.map(type => (
                <MenuItem key={type.id} value={type.id}>{type.name}</MenuItem>
              ))}
            </TextField>
          </Box>
          <Alert severity="info" sx={{ mt: 2 }}>
            Nach dem Erstellen der Grunddaten können Sie die Positionen der Spieler festlegen und später jederzeit anpassen.
          </Alert>
        </DialogContent>
        <DialogActions>
          <Button onClick={onClose} disabled={loading} variant="outlined">Abbrechen</Button>
          <Button type="submit" variant="contained" disabled={loading}>
            Erstellen und bearbeiten
          </Button>
        </DialogActions>
      </form>
    </Dialog>
  );
};

export default FormationModal;
