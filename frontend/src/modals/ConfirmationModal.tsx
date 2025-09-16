import React from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';

interface ConfirmationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  title?: string;
  message?: string;
  confirmText?: string;
  cancelText?: string;
  confirmColor?: 'primary' | 'error' | 'warning' | 'info' | 'success';
}

export const ConfirmationModal: React.FC<ConfirmationModalProps> = ({
  open,
  onClose,
  onConfirm,
  title = 'Bestätigung',
  message = 'Sind Sie sicher?',
  confirmText = 'Bestätigen',
  cancelText = 'Abbrechen',
  confirmColor = 'primary',
}) => (
  <Dialog open={open} onClose={onClose} maxWidth="xs" fullWidth>
    <DialogTitle>{title}</DialogTitle>
    <DialogContent>
      <p>{message}</p>
    </DialogContent>
    <DialogActions>
      <Button onClick={onClose} color="secondary">{cancelText}</Button>
      <Button onClick={onConfirm} color={confirmColor} variant="contained">{confirmText}</Button>
    </DialogActions>
  </Dialog>
);
