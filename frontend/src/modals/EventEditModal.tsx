import React from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import MenuItem from '@mui/material/MenuItem';
import Select from '@mui/material/Select';
import InputLabel from '@mui/material/InputLabel';
import FormControl from '@mui/material/FormControl';

export interface EventEditData {
  id?: string;
  title: string;
  date: string;
  time?: string;
  type?: string;
  location?: string;
  team?: string;
  opponent?: string;
  description?: string;
}

interface EventEditModalProps {
  open: boolean;
  onClose: () => void;
  onSave: (data: EventEditData) => void;
  event: EventEditData;
  eventTypes: { value: string; label: string }[];
  teams?: { value: string; label: string }[];
  loading?: boolean;
  error?: string;
  onChange: (field: keyof EventEditData, value: string) => void;
}

export const EventEditModal: React.FC<EventEditModalProps> = ({
  open,
  onClose,
  onSave,
  event,
  eventTypes,
  teams = [],
  loading = false,
  error,
  onChange,
}) => (
  <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
    <DialogTitle>Event bearbeiten</DialogTitle>
    <DialogContent sx={{ pt: 2 }}>
      <TextField
        label="Titel *"
        value={event.title || ''}
        onChange={e => onChange('title', e.target.value)}
        fullWidth
        required
        margin="dense"
        autoFocus
      />
      <TextField
        label="Datum *"
        type="date"
        value={event.date || ''}
        onChange={e => onChange('date', e.target.value)}
        fullWidth
        required
        margin="dense"
        InputLabelProps={{ shrink: true }}
      />
      <TextField
        label="Uhrzeit"
        type="time"
        value={event.time || ''}
        onChange={e => onChange('time', e.target.value)}
        fullWidth
        margin="dense"
        InputLabelProps={{ shrink: true }}
      />
      <FormControl fullWidth margin="dense">
        <InputLabel id="event-type-label">Event-Typ</InputLabel>
        <Select
          labelId="event-type-label"
          value={event.type || ''}
          label="Event-Typ"
          onChange={e => onChange('type', e.target.value as string)}
        >
          <MenuItem value=""><em>Bitte wählen...</em></MenuItem>
          {eventTypes.map(opt => (
            <MenuItem key={opt.value} value={opt.value}>{opt.label}</MenuItem>
          ))}
        </Select>
      </FormControl>
      <TextField
        label="Ort"
        value={event.location || ''}
        onChange={e => onChange('location', e.target.value)}
        fullWidth
        margin="dense"
      />
      {teams.length > 0 && (
        <FormControl fullWidth margin="dense">
          <InputLabel id="team-label">Team</InputLabel>
          <Select
            labelId="team-label"
            value={event.team || ''}
            label="Team"
            onChange={e => onChange('team', e.target.value as string)}
          >
            <MenuItem value=""><em>Bitte wählen...</em></MenuItem>
            {teams.map(opt => (
              <MenuItem key={opt.value} value={opt.value}>{opt.label}</MenuItem>
            ))}
          </Select>
        </FormControl>
      )}
      <TextField
        label="Gegner"
        value={event.opponent || ''}
        onChange={e => onChange('opponent', e.target.value)}
        fullWidth
        margin="dense"
      />
      <TextField
        label="Beschreibung"
        value={event.description || ''}
        onChange={e => onChange('description', e.target.value)}
        fullWidth
        margin="dense"
        multiline
        rows={3}
      />
      {error && <div style={{ color: 'red', marginTop: 8 }}>{error}</div>}
    </DialogContent>
    <DialogActions>
      <Button onClick={onClose} color="secondary">Abbrechen</Button>
      <Button onClick={() => onSave(event)} color="primary" variant="contained" disabled={loading}>
        Speichern
      </Button>
    </DialogActions>
  </Dialog>
);
