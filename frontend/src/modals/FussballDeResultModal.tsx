import React from 'react';
import Button from '@mui/material/Button';
import Typography from '@mui/material/Typography';
import BaseModal from './BaseModal';

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
  <BaseModal
    open={open}
    onClose={onClose}
    maxWidth="md"
    title="Ergebnisse von Fussball.de"
    actions={
      <Button onClick={onClose} color="primary" variant="contained">Schließen</Button>
    }
  >
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
  </BaseModal>
);

export default FussballDeResultModal;
