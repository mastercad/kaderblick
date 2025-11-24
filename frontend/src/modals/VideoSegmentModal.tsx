import React, { useState, useEffect } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TextField,
  FormControlLabel,
  Switch,
  Box,
  Typography,
  IconButton,
  List,
  ListItem,
  ListItemText,
  ListItemSecondaryAction,
  Divider,
  Chip,
  Alert,
  Paper
} from '@mui/material';
import {
  Add as AddIcon,
  Delete as DeleteIcon,
  Edit as EditIcon,
  Save as SaveIcon,
  Cancel as CancelIcon,
  Download as DownloadIcon
} from '@mui/icons-material';
import {
  VideoSegment,
  VideoSegmentInput,
  fetchVideoSegments,
  saveVideoSegment,
  updateVideoSegment,
  deleteVideoSegment,
  exportVideoSegments
} from '../services/videoSegments';
import { Video } from '../services/videos';
import { useToast } from '../context/ToastContext';

interface VideoSegmentModalProps {
  open: boolean;
  onClose: () => void;
  videos: Video[];
  gameId: number;
}

interface SegmentFormData {
  videoId: number;
  startMinute: string;
  lengthSeconds: string;
  title: string;
  subTitle: string;
  includeAudio: boolean;
}

const emptyFormData: SegmentFormData = {
  videoId: 0,
  startMinute: '0',
  lengthSeconds: '60',
  title: '',
  subTitle: '',
  includeAudio: true
};

export const VideoSegmentModal: React.FC<VideoSegmentModalProps> = ({
  open,
  onClose,
  videos,
  gameId
}) => {
  const { showToast } = useToast();
  const [segments, setSegments] = useState<VideoSegment[]>([]);
  const [loading, setLoading] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [formData, setFormData] = useState<SegmentFormData>(emptyFormData);
  const [selectedVideoId, setSelectedVideoId] = useState<number | null>(null);
  const [showForm, setShowForm] = useState(false);

  useEffect(() => {
    if (open && videos.length > 0) {
      loadSegments();
    }
  }, [open, gameId]);

  const loadSegments = async () => {
    setLoading(true);
    try {
      const data = await fetchVideoSegments(gameId);
      setSegments(data);
    } catch (error) {
      showToast('Fehler beim Laden der Segmente', 'error');
    } finally {
      setLoading(false);
    }
  };

  const handleVideoSelect = (videoId: number) => {
    setSelectedVideoId(videoId);
    setShowForm(true);
    setFormData({ ...emptyFormData, videoId });
    setEditingId(null);
  };

  const handleEditSegment = (segment: VideoSegment) => {
    setEditingId(segment.id);
    setSelectedVideoId(segment.videoId);
    setShowForm(true);
    setFormData({
      videoId: segment.videoId,
      startMinute: segment.startMinute.toString(),
      lengthSeconds: segment.lengthSeconds.toString(),
      title: segment.title || '',
      subTitle: segment.subTitle || '',
      includeAudio: segment.includeAudio
    });
  };

  const handleSaveSegment = async () => {
    if (!formData.videoId) {
      showToast('Bitte wähle ein Video aus', 'warning');
      return;
    }

    const data: VideoSegmentInput = {
      videoId: formData.videoId,
      startMinute: parseFloat(formData.startMinute.replace(',', '.')),
      lengthSeconds: parseInt(formData.lengthSeconds),
      title: formData.title || null,
      subTitle: formData.subTitle || null,
      includeAudio: formData.includeAudio,
      sortOrder: segments.filter(s => s.videoId === formData.videoId).length
    };

    setLoading(true);
    try {
      if (editingId) {
        await updateVideoSegment(editingId, data);
        showToast('Segment aktualisiert', 'success');
      } else {
        await saveVideoSegment(data);
        showToast('Segment hinzugefügt', 'success');
      }
      setShowForm(false);
      setFormData(emptyFormData);
      setEditingId(null);
      await loadSegments();
    } catch (error) {
      showToast('Fehler beim Speichern', 'error');
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteSegment = async (id: number) => {
    if (!confirm('Segment wirklich löschen?')) return;

    setLoading(true);
    try {
      await deleteVideoSegment(id);
      showToast('Segment gelöscht', 'success');
      await loadSegments();
    } catch (error) {
      showToast('Fehler beim Löschen', 'error');
    } finally {
      setLoading(false);
    }
  };

  const handleExport = async () => {
    try {
      const blob = await exportVideoSegments(gameId);
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `video-segments-${gameId}.csv`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      window.URL.revokeObjectURL(url);

      showToast('Export erfolgreich', 'success');
    } catch (error) {
      showToast('Fehler beim Export', 'error');
    }
  };

  const handleCancelEdit = () => {
    setShowForm(false);
    setFormData(emptyFormData);
    setEditingId(null);
    setSelectedVideoId(null);
  };

  const formatTime = (minutes: number, seconds: number) => {
    const totalSeconds = minutes * 60 + seconds;
    const mins = Math.floor(totalSeconds / 60);
    const secs = totalSeconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  };

  const groupedSegments = segments.reduce((acc, segment) => {
    if (!acc[segment.videoId]) {
      acc[segment.videoId] = [];
    }
    acc[segment.videoId].push(segment);
    return acc;
  }, {} as Record<number, VideoSegment[]>);

  return (
    <Dialog open={open} onClose={onClose} maxWidth="md" fullWidth>
      <DialogTitle>
        <Box display="flex" justifyContent="space-between" alignItems="center">
          <Typography variant="h6">Video-Schnittliste</Typography>
          <Box>
            <Button
              startIcon={<DownloadIcon />}
              onClick={handleExport}
              disabled={segments.length === 0}
              size="small"
              sx={{ mr: 1 }}
            >
              Export CSV
            </Button>
          </Box>
        </Box>
      </DialogTitle>

      <DialogContent>
        <Alert severity="info" sx={{ mb: 2 }}>
          <Typography variant="body2">
            <strong>Anleitung:</strong> Wähle ein Video aus und lege Segmente fest, die du schneiden möchtest. 
            Gib an, ab welcher Minute das Segment startet und wie lang es sein soll.
          </Typography>
        </Alert>

        {/* Video Auswahl */}
        {!showForm && (
          <Box>
            <Typography variant="subtitle1" gutterBottom>
              Wähle ein Video aus:
            </Typography>
            <List>
              {videos.map((video) => (
                <Paper key={video.id} elevation={1} sx={{ mb: 1 }}>
                  <ListItem>
                    <ListItemText
                      primary={video.name}
                      secondary={`${groupedSegments[video.id]?.length || 0} Segment(e)`}
                    />
                    <ListItemSecondaryAction>
                      <Button
                        startIcon={<AddIcon />}
                        onClick={() => handleVideoSelect(video.id)}
                        color="primary"
                        size="small"
                      >
                        Segment hinzufügen
                      </Button>
                    </ListItemSecondaryAction>
                  </ListItem>

                  {groupedSegments[video.id] && groupedSegments[video.id].length > 0 && (
                    <Box sx={{ px: 2, pb: 2 }}>
                      {groupedSegments[video.id].map((segment) => (
                        <Paper key={segment.id} variant="outlined" sx={{ p: 1, mb: 1 }}>
                          <Box display="flex" justifyContent="space-between" alignItems="start">
                            <Box flex={1}>
                              <Typography variant="body2" fontWeight="bold">
                                Start: {segment.startMinute} Min • Länge: {segment.lengthSeconds}s
                              </Typography>
                              {segment.title && (
                                <Typography variant="body2" color="text.secondary">
                                  Titel: {segment.title}
                                </Typography>
                              )}
                              {segment.subTitle && (
                                <Typography variant="body2" color="text.secondary">
                                  Untertitel: {segment.subTitle}
                                </Typography>
                              )}
                              <Chip
                                label={segment.includeAudio ? 'Mit Ton' : 'Ohne Ton'}
                                size="small"
                                color={segment.includeAudio ? 'success' : 'default'}
                                sx={{ mt: 0.5 }}
                              />
                            </Box>
                            <Box>
                              <IconButton
                                size="small"
                                onClick={() => handleEditSegment(segment)}
                                color="primary"
                              >
                                <EditIcon fontSize="small" />
                              </IconButton>
                              <IconButton
                                size="small"
                                onClick={() => handleDeleteSegment(segment.id)}
                                color="error"
                              >
                                <DeleteIcon fontSize="small" />
                              </IconButton>
                            </Box>
                          </Box>
                        </Paper>
                      ))}
                    </Box>
                  )}
                </Paper>
              ))}
            </List>
          </Box>
        )}

        {/* Segment Formular */}
        {showForm && (
          <Box>
            <Typography variant="subtitle1" gutterBottom>
              {editingId ? 'Segment bearbeiten' : 'Neues Segment'}
            </Typography>
            <Typography variant="body2" color="text.secondary" gutterBottom>
              Video: {videos.find(v => v.id === selectedVideoId)?.name}
            </Typography>

            <Box sx={{ mt: 2 }}>
              <TextField
                fullWidth
                label="Startzeit (Minuten)"
                type="text"
                value={formData.startMinute}
                onChange={(e) => setFormData({ ...formData, startMinute: e.target.value })}
                helperText="z.B. 5 oder 5,5 für 5 Minuten 30 Sekunden"
                margin="normal"
              />

              <TextField
                fullWidth
                label="Länge (Sekunden)"
                type="number"
                value={formData.lengthSeconds}
                onChange={(e) => setFormData({ ...formData, lengthSeconds: e.target.value })}
                helperText="Wie viele Sekunden soll das Segment lang sein?"
                margin="normal"
              />

              <TextField
                fullWidth
                label="Titel (optional)"
                value={formData.title}
                onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                helperText="Wird im Export für alle Segmente dieses Videos verwendet"
                margin="normal"
              />

              <TextField
                fullWidth
                label="Untertitel (optional)"
                value={formData.subTitle}
                onChange={(e) => setFormData({ ...formData, subTitle: e.target.value })}
                helperText="Zusätzlicher Text für dieses spezifische Segment"
                margin="normal"
              />

              <FormControlLabel
                control={
                  <Switch
                    checked={formData.includeAudio}
                    onChange={(e) => setFormData({ ...formData, includeAudio: e.target.checked })}
                  />
                }
                label="Tonspur übernehmen"
              />

              <Box sx={{ mt: 2, display: 'flex', gap: 1 }}>
                <Button
                  variant="contained"
                  startIcon={<SaveIcon />}
                  onClick={handleSaveSegment}
                  disabled={loading}
                  fullWidth
                >
                  {editingId ? 'Aktualisieren' : 'Hinzufügen'}
                </Button>
                <Button
                  variant="outlined"
                  startIcon={<CancelIcon />}
                  onClick={handleCancelEdit}
                  disabled={loading}
                  fullWidth
                >
                  Abbrechen
                </Button>
              </Box>
            </Box>
          </Box>
        )}
      </DialogContent>

      <DialogActions>
        <Button onClick={onClose}>Schließen</Button>
      </DialogActions>
    </Dialog>
  );
};
