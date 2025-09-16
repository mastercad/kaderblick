import React from 'react';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';

export interface FussballDeResultModalProps {
  open: boolean;
  onClose: () => void;
  results?: Array<{
    team1: string;
    team2: string;
    score: string;
    date: string;
  }>;
}

const FussballDeResultModal: React.FC<FussballDeResultModalProps> = ({ open, onClose, results }) => (
  <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
    <DialogTitle>Ergebnisse von Fussball.de</DialogTitle>
    <DialogContent>
      {results && results.length > 0 ? (
        <>
          {results.map((res, idx) => (
            <div key={idx} style={{ marginBottom: 16 }}>
              <Typography variant="subtitle1">{res.team1} vs. {res.team2}</Typography>
              <Typography variant="body2">{res.score} &mdash; {res.date}</Typography>
            </div>
          ))}
        </>
      ) : (
        <Typography variant="body2">Keine Ergebnisse gefunden.</Typography>
      )}
    </DialogContent>
    <DialogActions>
      <Button onClick={onClose} color="primary">Schließen</Button>
    </DialogActions>
  </Dialog>
);

export default FussballDeResultModal;
