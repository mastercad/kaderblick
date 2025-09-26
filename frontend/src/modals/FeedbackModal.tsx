import React, { useState } from 'react';
import Snackbar from '@mui/material/Snackbar';
import {
  Dialog, DialogTitle, DialogContent, DialogActions,
  Button, TextField, Select, MenuItem, FormControl, InputLabel,
  Checkbox, FormControlLabel, Box, Alert, useTheme, CircularProgress
} from '@mui/material';
import { apiRequest } from '../utils/api';
import html2canvas from 'html2canvas';

interface FeedbackModalProps {
  open: boolean;
  onClose: () => void;
}

const FEEDBACK_TYPES = [
  { value: 'bug', label: 'Fehler melden' },
  { value: 'feature', label: 'Verbesserungsvorschlag' },
  { value: 'question', label: 'Frage' },
  { value: 'other', label: 'Sonstiges' },
];

const FeedbackModal: React.FC<FeedbackModalProps> = ({ open, onClose }) => {
  const theme = useTheme();
  const [type, setType] = useState('bug');
  const [message, setMessage] = useState('');
    const [screenshot, setScreenshot] = useState<string | null>(null);
  const [attachScreenshot, setAttachScreenshot] = useState(false);
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [showSuccess, setShowSuccess] = useState(false);
  const [showError, setShowError] = useState(false);
  const handleScreenshot = async () => {
    setError(null);
    setLoading(true);
    try {
      // Modal und Backdrop für Screenshot ausblenden
  const modal = document.querySelector('.MuiDialog-root') as HTMLElement | null;
  const backdrop = document.querySelector('.MuiBackdrop-root') as HTMLElement | null;
  const prevModalDisplay = modal ? modal.style.display : null;
  const prevBackdropDisplay = backdrop ? backdrop.style.display : null;
  if (modal) modal.style.display = 'none';
  if (backdrop) backdrop.style.display = 'none';
      await new Promise(res => setTimeout(res, 100));
      const canvas = await html2canvas(document.body, {useCORS: true, logging: false});
      const dataUrl = canvas.toDataURL('image/png');
      setScreenshot(dataUrl);
      // Modal und Backdrop wieder einblenden
  if (modal && prevModalDisplay !== null) modal.style.display = prevModalDisplay;
  if (backdrop && prevBackdropDisplay !== null) backdrop.style.display = prevBackdropDisplay;
    } catch (err) {
      setError('Screenshot konnte nicht erstellt werden');
      setAttachScreenshot(false);
    } finally {
      setLoading(false);
    }
  };

    const handleSubmit = async (e: React.FormEvent) => {
      e.preventDefault();
      setLoading(true);
  setError(null);
  setSuccess(null);
  setShowSuccess(false);
  setShowError(false);
      try {
        const payload: Record<string, any> = {
          type,
          message,
          url: window.location.toString(),
          userAgent: navigator.userAgent,
        };
        if (attachScreenshot && screenshot) {
          payload.screenshot = screenshot;
        }
        const response = await apiRequest('/api/feedback/create', {
          method: 'POST',
          body: payload,
        });
        if (response.ok) {
          setSuccess('Vielen Dank für Ihr Feedback!');
          setShowSuccess(true);
          setType('bug');
          setMessage('');
          setAttachScreenshot(false);
          setScreenshot(null);
          setTimeout(() => {
            setShowSuccess(false);
            setSuccess(null);
            onClose();
          }, 2000);
        } else {
          const data = await response.json();
          setError(data.message || 'Fehler beim Senden des Feedbacks');
          setShowError(true);
        }
      } catch (err) {
        setError('Fehler beim Senden des Feedbacks');
      } finally {
        setLoading(false);
      }
    };

  // Felder zurücksetzen, wenn Modal geschlossen wird
  React.useEffect(() => {
    if (!open) {
      setType('bug');
      setMessage('');
      setAttachScreenshot(false);
      setScreenshot(null);
      setSuccess(null);
      setError(null);
      setShowSuccess(false);
      setShowError(false);
    }
  }, [open]);

  return (
    <>
      <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
        <DialogTitle sx={{ bgcolor: theme.palette.background.paper, fontWeight: 600 }}>Feedback</DialogTitle>
        <DialogContent sx={{ bgcolor: theme.palette.background.paper }}>
          <Box component="form" onSubmit={handleSubmit} sx={{ mt: 1, display: 'flex', flexDirection: 'column', gap: 2 }}>
          <FormControl fullWidth required>
            <InputLabel id="feedback-type-label">Feedback-Typ</InputLabel>
            <Select
              labelId="feedback-type-label"
              value={type}
              label="Feedback-Typ"
              onChange={e => setType(e.target.value)}
            >
              {FEEDBACK_TYPES.map(opt => (
                <MenuItem key={opt.value} value={opt.value}>{opt.label}</MenuItem>
              ))}
            </Select>
          </FormControl>
          <TextField
            label="Ihre Nachricht"
            value={message}
            onChange={e => setMessage(e.target.value)}
            required
            multiline
            minRows={4}
          />
          <FormControlLabel
            control={<Checkbox checked={attachScreenshot} onChange={e => {
              setAttachScreenshot(e.target.checked);
              if (e.target.checked) handleScreenshot();
              else setScreenshot(null);
            }} />}
            label="Screenshot anhängen"
          />
          {attachScreenshot && screenshot && (
            <Box sx={{ mb: 2 }}>
              <img src={screenshot} alt="Screenshot Vorschau" style={{ maxWidth: '100%', borderRadius: 8 }} />
            </Box>
          )}
          <DialogActions sx={{ px: 0 }}>
            <Button onClick={onClose} color="secondary" variant="outlined" disabled={loading}>
              Abbrechen
            </Button>
            <Button type="submit" color="primary" variant="contained" disabled={loading || !message}>
              {loading ? <CircularProgress size={24} /> : 'Senden'}
            </Button>
          </DialogActions>
          </Box>
        </DialogContent>
      </Dialog>
      <Snackbar
        open={showSuccess}
        autoHideDuration={2000}
        onClose={() => setShowSuccess(false)}
        message={success}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'center' }}
      />
      <Snackbar
        open={showError}
        autoHideDuration={3000}
        onClose={() => setShowError(false)}
        message={error ? error : ''}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'center' }}
      />
    </>
  );
};

export default FeedbackModal;
