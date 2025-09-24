import React, { useState } from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import Select from '@mui/material/Select';
import MenuItem from '@mui/material/MenuItem';
import InputLabel from '@mui/material/InputLabel';
import FormControl from '@mui/material/FormControl';
import Autocomplete from '@mui/material/Autocomplete';

interface EventData {
  title?: string;
  date?: string;
  time?: string;
  endDate?: string;
  endTime?: string;
  eventType?: string;
  locationId?: string;
  description?: string;
  homeTeam?: string;
  awayTeam?: string;
  gameType?: string;
}

interface EventModalProps {
  open: boolean;
  onClose: () => void;
  onSave: () => void;
  onDelete?: () => void;
  showDelete?: boolean;
  event?: EventData;
  eventTypes: { value: string; label: string }[];
  teams?: { value: string; label: string }[];
  gameTypes?: { value: string; label: string }[];
  locations: { value: string; label: string }[];
  onChange: (field: string, value: string) => void;
  loading?: boolean;
}

export const EventModal: React.FC<EventModalProps> = ({
  open,
  onClose,
  onSave,
  onDelete,
  showDelete = false,
  event = {},
  eventTypes,
  teams = [],
  gameTypes = [],
  locations,
  onChange,
  loading = false,
}) => {
  // Prüfen ob es sich um ein Spiel-Event handelt
  const selectedEventType = eventTypes.find(et => et.value === event.eventType);
  const isGameEvent = selectedEventType?.label?.toLowerCase().includes('spiel') 
    || selectedEventType?.label?.toLowerCase() === 'spiel' 
    || false;

  return (
    <Dialog open={open} onClose={onClose} maxWidth="lg" fullWidth>
      <DialogTitle>Event verwalten</DialogTitle>
      <DialogContent>
        <div style={{ display: 'flex', gap: 24 }}>
          <div style={{ flex: 1 }}>
            <TextField
              label="Titel *"
              value={event.title || ''}
              onChange={e => onChange('title', e.target.value)}
              fullWidth
              margin="normal"
              required
            />
            <TextField
              label="Datum *"
              type="date"
              value={event.date || ''}
              onChange={e => onChange('date', e.target.value)}
              fullWidth
              margin="normal"
              required
              InputLabelProps={{ shrink: true }}
            />
            <TextField
              label="Uhrzeit"
              type="time"
              value={event.time || ''}
              onChange={e => onChange('time', e.target.value)}
              fullWidth
              margin="normal"
              InputLabelProps={{ shrink: true }}
            />
            <TextField
              label="End-Datum"
              type="date"
              value={event.endDate || ''}
              onChange={e => onChange('endDate', e.target.value)}
              fullWidth
              margin="normal"
              InputLabelProps={{ shrink: true }}
            />
            <TextField
              label="End-Uhrzeit"
              type="time"
              value={event.endTime || ''}
              onChange={e => onChange('endTime', e.target.value)}
              fullWidth
              margin="normal"
              InputLabelProps={{ shrink: true }}
            />
            <FormControl fullWidth margin="normal" required>
              <InputLabel id="event-type-label">Event-Typ *</InputLabel>
              <Select
                labelId="event-type-label"
                value={event.eventType || ''}
                label="Event-Typ *"
                onChange={e => onChange('eventType', e.target.value as string)}
              >
                <MenuItem value=""><em>Bitte wählen...</em></MenuItem>
                {eventTypes.map(opt => (
                  <MenuItem key={opt.value} value={opt.value}>{opt.label}</MenuItem>
                ))}
              </Select>
            </FormControl>
          </div>
          <div style={{ flex: 1 }}>
            <Autocomplete
              options={locations}
              getOptionLabel={(option) => option.label}
              value={locations.find(l => l.value === event.locationId) || null}
              onChange={(_, newValue) => onChange('locationId', newValue?.value || '')}
              renderInput={(params) => (
                <TextField
                  {...params}
                  label="Ort"
                  placeholder="Ort suchen..."
                  fullWidth
                  margin="normal"
                />
              )}
              filterOptions={(options, { inputValue }) => {
                if (inputValue.length < 2) return [];
                return options.filter(option =>
                  option.label.toLowerCase().includes(inputValue.toLowerCase())
                );
              }}
              noOptionsText="Keine Orte gefunden (mindestens 2 Zeichen eingeben)"
            />
            {/* Spiel-spezifische Felder - nur wenn explizit "Spiel" ausgewählt */}
            {isGameEvent && (
              <>
                <FormControl fullWidth margin="normal" required>
                  <InputLabel id="home-team-label">Heim-Team *</InputLabel>
                  <Select
                    labelId="home-team-label"
                    value={event.homeTeam || ''}
                    label="Heim-Team *"
                    onChange={e => onChange('homeTeam', e.target.value as string)}
                  >
                    <MenuItem value=""><em>Bitte wählen...</em></MenuItem>
                    {teams.map(opt => (
                      <MenuItem key={opt.value} value={opt.value}>{opt.label}</MenuItem>
                    ))}
                  </Select>
                </FormControl>
                <FormControl fullWidth margin="normal" required>
                  <InputLabel id="away-team-label">Auswärts-Team *</InputLabel>
                  <Select
                    labelId="away-team-label"
                    value={event.awayTeam || ''}
                    label="Auswärts-Team *"
                    onChange={e => onChange('awayTeam', e.target.value as string)}
                  >
                    <MenuItem value=""><em>Bitte wählen...</em></MenuItem>
                    {teams.map(opt => (
                      <MenuItem key={opt.value} value={opt.value}>{opt.label}</MenuItem>
                    ))}
                  </Select>
                </FormControl>
                {gameTypes.length > 0 && (
                  <FormControl fullWidth margin="normal">
                    <InputLabel id="game-type-label">Spiel-Typ</InputLabel>
                    <Select
                      labelId="game-type-label"
                      value={event.gameType || ''}
                      label="Spiel-Typ"
                      onChange={e => onChange('gameType', e.target.value as string)}
                    >
                      <MenuItem value=""><em>Bitte wählen...</em></MenuItem>
                      {gameTypes.map(opt => (
                        <MenuItem key={opt.value} value={opt.value}>{opt.label}</MenuItem>
                      ))}
                    </Select>
                  </FormControl>
                )}
              </>
            )}
            <TextField
              label="Beschreibung"
              value={event.description || ''}
              onChange={e => onChange('description', e.target.value)}
              fullWidth
              margin="normal"
              multiline
              rows={4}
            />
          </div>
        </div>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose} color="secondary" disabled={loading}>Abbrechen</Button>
        {showDelete && onDelete && (
          <Button onClick={onDelete} color="error" disabled={loading}>Löschen</Button>
        )}
        <Button onClick={onSave} color="primary" variant="contained" disabled={loading}>
          {loading ? 'Wird gespeichert...' : 'Speichern'}
        </Button>
      </DialogActions>
    </Dialog>
  );
};
