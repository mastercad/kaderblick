import React from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import Typography from '@mui/material/Typography';

export interface SuccessModalProps {
  open: boolean;
  onClose: () => void;
  message?: string;
}

const SuccessModal: React.FC<SuccessModalProps> = ({ open, onClose, message }) => (
  <Dialog open={open} onClose={onClose} maxWidth="xs">
    <DialogTitle>
      <CheckCircleIcon color="success" style={{ verticalAlign: 'middle', marginRight: 8 }} /> Erfolg
    </DialogTitle>
    <DialogContent>
      <Typography variant="body1">{message || 'Aktion erfolgreich abgeschlossen.'}</Typography>
    </DialogContent>
    <DialogActions>
      <Button onClick={onClose} color="primary" variant="contained">OK</Button>
    </DialogActions>
  </Dialog>
);

export default SuccessModal;
