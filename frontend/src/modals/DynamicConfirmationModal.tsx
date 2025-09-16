import React from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';

interface DynamicConfirmationModalProps {
  open: boolean;
  onClose: () => void;
  onConfirm: () => void;
  title?: string;
  message?: string;
  confirmText?: string;
  cancelText?: string;
  confirmColor?: 'primary' | 'error' | 'warning' | 'info' | 'success';
  loading?: boolean;
}

export const DynamicConfirmationModal: React.FC<DynamicConfirmationModalProps> = ({
  open,
  onClose,
  onConfirm,
  title = 'Bestätigung',
  message = 'Sind Sie sicher?',
  confirmText = 'Bestätigen',
  cancelText = 'Abbrechen',
  confirmColor = 'primary',
  loading = false,
}) => (
  <Dialog open={open} onClose={onClose} maxWidth="xs" fullWidth>
    <DialogTitle>{title}</DialogTitle>
    <DialogContent>
      <p>{message}</p>
    </DialogContent>
    <DialogActions>
      <Button onClick={onClose} color="secondary" disabled={loading}>{cancelText}</Button>
      <Button onClick={onConfirm} color={confirmColor} variant="contained" disabled={loading}>{confirmText}</Button>
    </DialogActions>
  </Dialog>
);
