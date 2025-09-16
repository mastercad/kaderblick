import React from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';

export interface ContactModalProps {
  open: boolean;
  onClose: () => void;
  onSubmit: (data: { name: string; email: string; message: string }) => void;
  initialData?: { name: string; email: string; message: string };
}

const ContactModal: React.FC<ContactModalProps> = ({ open, onClose, onSubmit, initialData }) => {
  const [form, setForm] = React.useState(initialData || { name: '', email: '', message: '' });

  React.useEffect(() => {
    setForm(initialData || { name: '', email: '', message: '' });
  }, [initialData, open]);

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <DialogTitle>Kontakt aufnehmen</DialogTitle>
      <DialogContent>
        <TextField
          label="Name"
          value={form.name}
          onChange={e => setForm(f => ({ ...f, name: e.target.value }))}
          margin="normal"
          fullWidth
        />
        <TextField
          label="E-Mail"
          value={form.email}
          onChange={e => setForm(f => ({ ...f, email: e.target.value }))}
          margin="normal"
          fullWidth
        />
        <TextField
          label="Nachricht"
          value={form.message}
          onChange={e => setForm(f => ({ ...f, message: e.target.value }))}
          margin="normal"
          fullWidth
          multiline
          minRows={3}
        />
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Abbrechen</Button>
        <Button onClick={() => onSubmit(form)} color="primary" variant="contained">Senden</Button>
      </DialogActions>
    </Dialog>
  );
};

export default ContactModal;
