import React, { useState } from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import Box from '@mui/material/Box';
import { apiJson } from '../utils/api';

interface AddTeamRideModalProps {
  open: boolean;
  onClose: () => void;
  eventId: number | null;
  onAdded?: () => void;
}

const AddTeamRideModal: React.FC<AddTeamRideModalProps> = ({ open, onClose, eventId, onAdded }) => {
  const [seats, setSeats] = useState(1);
  const [note, setNote] = useState('');
  const [saving, setSaving] = useState(false);

  const handleAddRide = () => {
    if (!eventId || seats < 1) return;
    setSaving(true);
    apiJson(`/api/teamrides/add`, {
      method: 'POST',
      body: { event_id: eventId, seats, note },
    })
      .then(() => {
        if (onAdded) onAdded();
        onClose();
      })
      .finally(() => setSaving(false));
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="xs" fullWidth>
      <DialogTitle>Mitfahrgelegenheit anbieten</DialogTitle>
      <DialogContent dividers>
        <Box mb={2}>
          <TextField
            label="Anzahl Plätze"
            type="number"
            value={seats}
            onChange={e => setSeats(Number(e.target.value))}
            inputProps={{ min: 1 }}
            fullWidth
            disabled={saving}
          />
        </Box>
        <Box mb={2}>
          <TextField
            label="Notiz (optional)"
            value={note}
            onChange={e => setNote(e.target.value)}
            fullWidth
            multiline
            minRows={1}
            maxRows={3}
            disabled={saving}
          />
        </Box>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose} color="primary" variant="outlined" disabled={saving}>Abbrechen</Button>
        <Button onClick={handleAddRide} color="secondary" variant="contained" disabled={saving || seats < 1}>Anbieten</Button>
      </DialogActions>
    </Dialog>
  );
};

export default AddTeamRideModal;
