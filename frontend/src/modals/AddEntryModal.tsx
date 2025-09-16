import React from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';

export interface AddEntryModalProps {
  open: boolean;
  onClose: () => void;
  onSubmit: (data: Record<string, any>) => void;
  fields: Array<{ name: string; label: string; type?: string; required?: boolean; value?: any }>;
  title?: string;
}

const AddEntryModal: React.FC<AddEntryModalProps> = ({ open, onClose, onSubmit, fields, title }) => {
  const [form, setForm] = React.useState<Record<string, any>>({});

  React.useEffect(() => {
    const initial: Record<string, any> = {};
    fields.forEach(f => { initial[f.name] = f.value ?? ''; });
    setForm(initial);
  }, [fields, open]);

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <DialogTitle>{title || 'Neuen Eintrag hinzufügen'}</DialogTitle>
      <DialogContent>
        {fields.map(field => (
          <TextField
            key={field.name}
            label={field.label}
            type={field.type || 'text'}
            value={form[field.name]}
            onChange={e => setForm(f => ({ ...f, [field.name]: e.target.value }))}
            margin="normal"
            fullWidth
            required={field.required}
          />
        ))}
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Abbrechen</Button>
        <Button onClick={() => onSubmit(form)} color="primary" variant="contained">Speichern</Button>
      </DialogActions>
    </Dialog>
  );
};

export default AddEntryModal;
