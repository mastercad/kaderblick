import React from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';

export interface FeedbackResolveModalProps {
  open: boolean;
  onClose: () => void;
  onResolve: (comment: string) => void;
  feedbackText: string;
}

const FeedbackResolveModal: React.FC<FeedbackResolveModalProps> = ({ open, onClose, onResolve, feedbackText }) => {
  const [comment, setComment] = React.useState('');

  React.useEffect(() => {
    setComment('');
  }, [open]);

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <DialogTitle>Feedback erledigen</DialogTitle>
      <DialogContent>
        <Typography variant="subtitle1" gutterBottom>Feedback:</Typography>
        <Typography variant="body2" color="text.secondary" gutterBottom>{feedbackText}</Typography>
        <Typography variant="subtitle2" sx={{ mt: 2 }}>Kommentar (optional):</Typography>
        <textarea
          value={comment}
          onChange={e => setComment(e.target.value)}
          rows={3}
          style={{ width: '100%', marginTop: 8, padding: 8, borderRadius: 4, border: '1px solid #ccc', fontFamily: 'inherit' }}
        />
      </DialogContent>
      <DialogActions>
        <Button onClick={onClose}>Abbrechen</Button>
        <Button onClick={() => onResolve(comment)} color="primary" variant="contained">Erledigen</Button>
      </DialogActions>
    </Dialog>
  );
};

export default FeedbackResolveModal;
