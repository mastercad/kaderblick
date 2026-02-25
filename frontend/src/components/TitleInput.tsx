import React, { memo } from 'react';
import TextField from '@mui/material/TextField';

interface TitleInputProps {
  value: string;
  onChange: (value: string) => void;
  required?: boolean;
}

export const TitleInput = memo<TitleInputProps>(({ value, onChange, required = true }) => {
  return (
    <TextField
      label="Titel *"
      value={value}
      onChange={e => onChange(e.target.value)}
      fullWidth
      margin="normal"
      required={required}
    />
  );
});

TitleInput.displayName = 'TitleInput';
