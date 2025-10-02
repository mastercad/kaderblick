import React, { useState } from 'react';
import Button from '@mui/material/Button';
import FormControl from '@mui/material/FormControl';
import InputLabel from '@mui/material/InputLabel';
import Select from '@mui/material/Select';
import MenuItem from '@mui/material/MenuItem';
import BaseModal from './BaseModal';

export interface WidgetSettingsModalProps {
  open: boolean;
  currentWidth: number | string;
  onClose: () => void;
  onSave: (width: number | string) => void;
}

const GRID_OPTIONS = [
  { value: 3, label: 'Schmal (25%)' },
  { value: 4, label: 'Normal (33%)' },
  { value: 6, label: 'Halbe Breite (50%)' },
  { value: 8, label: 'Groß (66%)' },
  { value: 12, label: 'Volle Breite (100%)' },
];

export const WidgetSettingsModal: React.FC<WidgetSettingsModalProps> = ({ open, currentWidth, onClose, onSave }) => {
  const [width, setWidth] = useState<number>(typeof currentWidth === 'number' ? currentWidth : 6);

  React.useEffect(() => {
    setWidth(typeof currentWidth === 'number' ? currentWidth : 6);
  }, [currentWidth, open]);

  return (
    <BaseModal
      open={open}
      onClose={onClose}
      title="Widget-Einstellungen"
      maxWidth="xs"
      actions={
        <>
          <Button onClick={onClose} variant="outlined" color="secondary">
            Abbrechen
          </Button>
          <Button onClick={() => onSave(width)} variant="contained" color="primary">
            Speichern
          </Button>
        </>
      }
    >
      <FormControl fullWidth sx={{ mt: 2 }}>
        <InputLabel id="widget-width-label">Breite</InputLabel>
        <Select
          labelId="widget-width-label"
          value={width}
          label="Breite"
          onChange={e => setWidth(Number(e.target.value))}
        >
          {GRID_OPTIONS.map(opt => (
            <MenuItem key={opt.value} value={opt.value}>{opt.label}</MenuItem>
          ))}
        </Select>
      </FormControl>
    </BaseModal>
  );
};
