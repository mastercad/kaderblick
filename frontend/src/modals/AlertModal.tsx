import React from 'react';
import Button from '@mui/material/Button';
import Alert from '@mui/material/Alert';
import BaseModal from './BaseModal';

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
  <BaseModal
    open={open}
    onClose={onClose}
    title={title}
    maxWidth="sm"
    actions={
      <Button onClick={onClose} color="primary" variant="contained">
        {closeText}
      </Button>
    }
  >
    <Alert severity={severity}>
      {message}
    </Alert>
  </BaseModal>
);
