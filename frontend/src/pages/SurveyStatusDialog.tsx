import React, { useEffect, useState } from 'react';
import { Dialog, DialogTitle, DialogContent, Typography, CircularProgress, Alert, Box, Divider } from '@mui/material';
import { apiJson } from '../utils/api';

interface SurveyStatusDialogProps {
  surveyId: number | null;
  open: boolean;
  onClose: () => void;
}

const SurveyStatusDialog: React.FC<SurveyStatusDialogProps> = ({ surveyId, open, onClose }) => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [survey, setSurvey] = useState<any>(null);
  const [results, setResults] = useState<any>(null);

  useEffect(() => {
    if (!open || !surveyId) return;
    setLoading(true);
    setError(null);
    setResults(null);
    apiJson(`/api/surveys/${surveyId}`)
      .then(setSurvey)
      .catch(() => setError('Fehler beim Laden der Umfrage'));
    apiJson(`/api/surveys/${surveyId}/results`)
      .then(setResults)
      .catch(() => setResults(null))
      .finally(() => setLoading(false));
  }, [open, surveyId]);

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <DialogTitle>Umfrage-Status</DialogTitle>
      <DialogContent>
        {loading && <CircularProgress />}
        {error && <Alert severity="error">{error}</Alert>}
        {survey && (
          <Box>
            <Typography variant="h6" mb={1}>{survey.title}</Typography>
            <Typography mb={2}>{survey.description}</Typography>
            <Divider sx={{ my: 2 }} />
            {results && (
              <Box>
                <Typography variant="subtitle1" mb={1}>Auswertung:</Typography>
                {results.results.length === 0 && (
                  <Typography color="text.secondary">Keine Auswertungsdaten vorhanden.</Typography>
                )}
                {results.results.map((q: any, idx: number) => {
                  // Mapping typeKey zu Name
                  let typeName = '';
                  if (q.type === 'single_choice') typeName = 'Einzelauswahl';
                  else if (q.type === 'multiple_choice') typeName = 'Mehrfachauswahl';
                  else if (q.type === 'text') typeName = 'Freitext';
                  else if (q.type === 'scale_1_5') typeName = 'Skala 1–5';
                  else if (q.type === 'scale_1_10') typeName = 'Skala 1–10';
                  else typeName = q.type;
                  return (
                    <Box key={q.id} mb={2}>
                      <Typography><b>{idx + 1}. {q.questionText}</b> <span style={{ color: '#888', fontSize: 13 }}>({typeName})</span></Typography>
                      {q.options && q.options.length > 0 ? (
                        <Box ml={2}>
                          {q.options.map((opt: any) => (
                            <Typography key={opt.id} variant="body2">
                              {(opt.optionText && opt.optionText !== '' ? opt.optionText : (typeof opt.id === 'number' ? opt.id : ''))}: <b>{opt.count}</b>
                            </Typography>
                          ))}
                        </Box>
                      ) : (
                        <Typography variant="body2" color="text.secondary" ml={2}>
                          {q.type === 'text' ? 'Freitext-Frage (keine Auswertung)' : (q.type === 'scale_1_5' || q.type === 'scale_1_10') ? q.answers : 'Keine Antwortoptionen'}
                        </Typography>
                      )}
                    </Box>
                  );
                })}
              </Box>
            )}
          </Box>
        )}
      </DialogContent>
    </Dialog>
  );
};

export default SurveyStatusDialog;
