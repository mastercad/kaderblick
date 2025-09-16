import React from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';
import Alert from '@mui/material/Alert';

interface AlertModalProps {
  open: boolean;
  onClose: () => void;
  title?: string;
  message: string;
  severity?: 'error' | 'warning' | 'info' | 'success';
  closeText?: string;
}

export const AlertModal: React.FC<AlertModalProps> = ({
  open,
  onClose,
  title,
  message,
  severity = 'info',
  closeText = 'OK',
}) => (
  <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
    {title && <DialogTitle>{title}</DialogTitle>}
    <DialogContent>
      <Alert severity={severity}>
        {message}
      </Alert>
    </DialogContent>
    <DialogActions>
      <Button onClick={onClose} color="primary" variant="contained">
        {closeText}
      </Button>
    </DialogActions>
  </Dialog>
);
