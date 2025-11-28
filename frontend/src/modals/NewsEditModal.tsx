import React, { useState, useEffect } from 'react';
import { Button, TextField, Dialog, DialogTitle, DialogContent, DialogActions, Alert } from '@mui/material';
import { apiJson } from '../utils/api';

interface Props {
  open: boolean;
  onClose: () => void;
  onSuccess: () => void;
  news: { id: number; title: string; content: string; };
}

const NewsEditModal: React.FC<Props> = ({ open, onClose, onSuccess, news }) => {
  const [title, setTitle] = useState(news.title);
  const [content, setContent] = useState(news.content);

  useEffect(() => {
    setTitle(news.title);
    setContent(news.content);
  }, [news]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    try {
      const payload = { title, content };
      const res = await apiJson(`/news/${news.id}/edit`, { method: 'POST', body: payload });
      const data = res as { success?: boolean; error?: string };
      if (data.success) {
        onSuccess();
        onClose();
      } else {
        setError(data.error || 'Fehler beim Speichern');
      }
    } catch (e) {
      setError('Fehler beim Speichern');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <DialogTitle>News bearbeiten</DialogTitle>
      <form onSubmit={handleSubmit}>
        <DialogContent>
          {error && <Alert severity="error">{error}</Alert>}
          <TextField
            label="Titel"
            value={title}
            onChange={e => setTitle(e.target.value)}
            fullWidth
            margin="normal"
            required
          />
          <TextField
            label="Inhalt"
            value={content}
            onChange={e => setContent(e.target.value)}
            fullWidth
            margin="normal"
            multiline
            minRows={4}
            required
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={onClose} color="secondary" disabled={loading}>Abbrechen</Button>
          <Button type="submit" color="primary" variant="contained" disabled={loading}>Speichern</Button>
        </DialogActions>
      </form>
    </Dialog>
  );
};

export default NewsEditModal;
