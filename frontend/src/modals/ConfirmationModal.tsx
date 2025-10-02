import React from 'react';
import Button from '@mui/material/Button';
import BaseModal from './BaseModal';

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
  <BaseModal
    open={open}
    onClose={onClose}
    title={title}
    maxWidth="xs"
    actions={
      <>
        <Button onClick={onClose} color="secondary" variant="outlined">
          {cancelText}
        </Button>
        <Button onClick={onConfirm} color={confirmColor} variant="contained">
          {confirmText}
        </Button>
      </>
    }
  >
    <p>{message}</p>
  </BaseModal>
);
