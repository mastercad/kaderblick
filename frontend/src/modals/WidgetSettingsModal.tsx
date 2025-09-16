import React, { useState } from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';
import FormControl from '@mui/material/FormControl';
import InputLabel from '@mui/material/InputLabel';
import Select from '@mui/material/Select';
import MenuItem from '@mui/material/MenuItem';

export interface WidgetSettingsModalProps {
  open: boolean;
  currentWidth: number;
  onClose: () => void;
  onSave: (width: number) => void;
}

const WIDTH_OPTIONS = [
  { value: 3, label: 'Schmal (25%)' },
  { value: 4, label: 'Normal (33%)' },
  { value: 6, label: 'Breit (50%)' },
  { value: 12, label: 'Volle Breite (100%)' },
];

export const WidgetSettingsModal: React.FC<WidgetSettingsModalProps> = ({ open, currentWidth, onClose, onSave }) => {
  const [width, setWidth] = useState(currentWidth);

  // Reset width when modal opens with new value
  React.useEffect(() => {
    setWidth(currentWidth);
  }, [currentWidth, open]);

  return (
    <Dialog open={open} onClose={onClose} maxWidth="xs" fullWidth>
      <DialogTitle>Widget-Einstellungen</DialogTitle>
      <DialogContent>
        <FormControl fullWidth sx={{ mt: 2 }}>
          <InputLabel id="widget-width-label">Breite</InputLabel>
          <Select
            labelId="widget-width-label"
            value={width}
            label="Breite"
            onChange={e => setWidth(Number(e.target.value))}
          >
            {WIDTH_OPTIONS.map(opt => (
              <MenuItem key={opt.value} value={opt.value}>{opt.label}</MenuItem>
            ))}
          </Select>
        </FormControl>
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Abbrechen</Button>
        <Button onClick={() => onSave(width)} variant="contained">Speichern</Button>
      </DialogActions>
    </Dialog>
  );
};
