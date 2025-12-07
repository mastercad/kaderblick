import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  Box,
  Card,
  CardContent,
  Typography,
  Button,
  CircularProgress,
  Container,
  Chip,
  Stack,
  Divider,
  IconButton,
  alpha,
  useTheme
} from '@mui/material';
import ArrowBackIcon from '@mui/icons-material/ArrowBack';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import PersonIcon from '@mui/icons-material/Person';
import CalendarTodayIcon from '@mui/icons-material/CalendarToday';
import VisibilityIcon from '@mui/icons-material/Visibility';
import { apiJson } from '../utils/api';
import { useAuth } from '../context/AuthContext';
import NewsEditModal from '../modals/NewsEditModal';
import { DynamicConfirmationModal } from '../modals/DynamicConfirmationModal';

interface NewsItem {
  id: number;
  title: string;
  content: string;
  createdAt: string;
  createdByUserName: string;
  createdByUserId: number;
  visibility: string;
  club?: number;
  team?: number;
}

const NewsDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const theme = useTheme();
  const { user } = useAuth();
  const [news, setNews] = useState<NewsItem | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [editModalOpen, setEditModalOpen] = useState(false);
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [deleteLoading, setDeleteLoading] = useState(false);

  const fetchNewsDetail = async () => {
    if (!id) return;
    
    setLoading(true);
    setError(null);
    
    try {
      const data = await apiJson(`/news/${id}`);
      
      // Check if response contains an error
      if (data && typeof data === 'object' && 'error' in data) {
        setError(data.error as string);
        setNews(null);
      } else {
        setNews(data as NewsItem);
      }
    } catch (e: any) {
      console.error('Failed to load news:', e);
      setError(e.message || 'Fehler beim Laden der News');
      setNews(null);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchNewsDetail();
  }, [id]);

  const handleEdit = () => {
    setEditModalOpen(true);
  };

  const handleDelete = () => {
    setDeleteDialogOpen(true);
  };

  const confirmDelete = async () => {
    if (!news) return;
    
    setDeleteLoading(true);
    try {
      await apiJson(`/news/${news.id}/delete`, { method: 'POST' });
      navigate('/news');
    } catch (e) {
      console.error('Failed to delete news:', e);
      setDeleteLoading(false);
    }
  };

  const canEditOrDelete = user && news && user.id === news.createdByUserId;

  if (loading) {
    return (
      <Container maxWidth="md" sx={{ py: 4, display: 'flex', justifyContent: 'center' }}>
        <CircularProgress />
      </Container>
    );
  }

  if (error || !news) {
    return (
      <Container maxWidth="md" sx={{ py: 4 }}>
        <Card
          sx={{
            borderTop: `4px solid ${theme.palette.error.main}`,
          }}
        >
          <CardContent sx={{ p: 4, textAlign: 'center' }}>
            <Typography variant="h4" color="error" gutterBottom sx={{ fontWeight: 500 }}>
              Nachricht nicht verfügbar
            </Typography>
            <Typography variant="body1" color="text.secondary" sx={{ mb: 3, fontSize: '1.1rem' }}>
              {error || 'Die gewünschte Nachricht wurde nicht gefunden oder ist nicht mehr verfügbar.'}
            </Typography>
            <Button
              variant="contained"
              size="large"
              startIcon={<ArrowBackIcon />}
              onClick={() => navigate('/news')}
            >
              Zurück zur Übersicht
            </Button>
          </CardContent>
        </Card>
      </Container>
    );
  }

  return (
    <Container maxWidth="md" sx={{ py: 4 }}>
      <Button
        startIcon={<ArrowBackIcon />}
        onClick={() => navigate('/news')}
        sx={{ mb: 3 }}
      >
        Zurück zur Übersicht
      </Button>

      <Card
        sx={{
          borderTop: `4px solid ${theme.palette.primary.main}`,
        }}
      >
        <CardContent sx={{ p: 3 }}>
          <Stack
            direction="row"
            justifyContent="space-between"
            alignItems="flex-start"
            sx={{ mb: 2 }}
          >
            <Typography variant="h4" component="h1">
              {news.title}
            </Typography>
            
            {canEditOrDelete && (
              <Stack direction="row" spacing={1}>
                <IconButton
                  aria-label="Bearbeiten"
                  onClick={handleEdit}
                  size="small"
                  color="primary"
                >
                  <EditIcon />
                </IconButton>
                <IconButton
                  aria-label="Löschen"
                  onClick={handleDelete}
                  size="small"
                  color="error"
                >
                  <DeleteIcon />
                </IconButton>
              </Stack>
            )}
          </Stack>

          <Stack direction="row" spacing={2} sx={{ mb: 2 }} flexWrap="wrap" useFlexGap>
            <Chip
              icon={<PersonIcon fontSize="small" />}
              label={news.createdByUserName}
              size="small"
              sx={{
                backgroundColor: alpha(theme.palette.primary.main, 0.1),
                color: theme.palette.primary.main,
              }}
            />
            <Chip
              icon={<CalendarTodayIcon fontSize="small" />}
              label={new Date(news.createdAt).toLocaleString('de-DE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
              })}
              size="small"
              variant="outlined"
            />
            <Chip
              icon={<VisibilityIcon fontSize="small" />}
              label={news.visibility}
              size="small"
              variant="outlined"
            />
          </Stack>

          <Divider sx={{ my: 3 }} />

          <Typography
            variant="body1"
            sx={{
              whiteSpace: 'pre-wrap',
              lineHeight: 1.8,
              fontSize: '1.1rem',
            }}
          >
            {news.content}
          </Typography>
        </CardContent>
      </Card>

      {news && (
        <>
          <NewsEditModal
            open={editModalOpen}
            onClose={() => setEditModalOpen(false)}
            onSuccess={() => {
              setEditModalOpen(false);
              fetchNewsDetail();
            }}
            news={news}
          />
          <DynamicConfirmationModal
            open={deleteDialogOpen}
            onClose={() => setDeleteDialogOpen(false)}
            onConfirm={confirmDelete}
            title="News löschen"
            message={`Möchten Sie die News "${news.title}" wirklich löschen?`}
            confirmText="Löschen"
            confirmColor="error"
            loading={deleteLoading}
          />
        </>
      )}
    </Container>
  );
};

export default NewsDetail;
