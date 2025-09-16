import React, { useState, useEffect } from 'react';
import {
  Alert,
  Snackbar,
  IconButton,
  Box,
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import { AppNotification } from '../types/notifications';

interface ToastNotificationProps {
  notification: AppNotification;
  onClose: () => void;
  autoHideDuration?: number;
}

export const ToastNotification: React.FC<ToastNotificationProps> = ({
  notification,
  onClose,
  autoHideDuration = 5000
}) => {
  const [open, setOpen] = useState(true);

  useEffect(() => {
    setOpen(true);
  }, [notification]);

  const handleClose = (event?: React.SyntheticEvent | Event, reason?: string) => {
    if (reason === 'clickaway') {
      return;
    }
    setOpen(false);
    setTimeout(onClose, 150); // Warten auf Animation
  };

  const getSeverity = (type: string) => {
    switch (type) {
      case 'system': return 'info';
      case 'news': return 'info';
      case 'message': return 'success';
      case 'participation': return 'warning';
      default: return 'info';
    }
  };

  return (
    <Snackbar
      open={open}
      autoHideDuration={autoHideDuration}
      onClose={handleClose}
      anchorOrigin={{ vertical: 'top', horizontal: 'right' }}
      sx={{ mt: 8 }}
    >
      <Alert
        onClose={handleClose}
        severity={getSeverity(notification.type)}
        variant="filled"
        sx={{
          width: '100%',
          maxWidth: 400,
          '& .MuiAlert-message': {
            width: '100%'
          }
        }}
        action={
          <IconButton
            size="small"
            aria-label="close"
            color="inherit"
            onClick={handleClose}
          >
            <CloseIcon fontSize="small" />
          </IconButton>
        }
      >
        <Box>
          <Box sx={{ fontWeight: 'bold', mb: 0.5 }}>
            {notification.title}
          </Box>
          {notification.message && (
            <Box sx={{ fontSize: '0.875rem', opacity: 0.9 }}>
              {notification.message}
            </Box>
          )}
        </Box>
      </Alert>
    </Snackbar>
  );
};
