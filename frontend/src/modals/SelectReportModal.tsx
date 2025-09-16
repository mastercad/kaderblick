import React from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';
import CircularProgress from '@mui/material/CircularProgress';

interface SelectReportModalProps {
  open: boolean;
  onClose: () => void;
  onAdd: () => void;
  loading?: boolean;
  children?: React.ReactNode; // For report list
}

export const SelectReportModal: React.FC<SelectReportModalProps> = ({
  open,
  onClose,
  onAdd,
  loading = false,
  children,
}) => (
  <Dialog open={open} onClose={onClose} maxWidth="lg" fullWidth>
    <DialogTitle>Report auswählen</DialogTitle>
    <DialogContent>
      {loading ? (
        <div style={{ textAlign: 'center', margin: '2em 0' }}>
          <CircularProgress />
        </div>
      ) : (
        <div id="reportListContainer">
          {children}
        </div>
      )}
    </DialogContent>
    <DialogActions>
      <Button onClick={onClose} color="secondary">Abbrechen</Button>
      <Button onClick={onAdd} color="primary" variant="contained">Hinzufügen</Button>
    </DialogActions>
  </Dialog>
);
