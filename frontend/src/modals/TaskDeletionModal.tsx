import React from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';

interface TaskDeletionModalProps {
  open: boolean;
  onClose: () => void;
  onDeleteSingle: () => void;
  onDeleteSeries: () => void;
  loading?: boolean;
}

export const TaskDeletionModal: React.FC<TaskDeletionModalProps> = ({
  open,
  onClose,
  onDeleteSingle,
  onDeleteSeries,
  loading = false,
}) => (
  <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
    <DialogTitle>Task löschen</DialogTitle>
    <DialogContent sx={{ pt: 2 }}>
      <p>Möchten Sie nur dieses Event oder die gesamte Task-Serie löschen?</p>
    </DialogContent>
    <DialogActions sx={{ gap: 1 }}>
      <Button onClick={onClose} disabled={loading}>
        Abbrechen
      </Button>
      <Button 
        onClick={onDeleteSingle} 
        color="warning" 
        variant="contained" 
        disabled={loading}
      >
        Nur dieses Event
      </Button>
      <Button 
        onClick={onDeleteSeries} 
        color="error" 
        variant="contained" 
        disabled={loading}
      >
        Gesamte Serie
      </Button>
    </DialogActions>
  </Dialog>
);
