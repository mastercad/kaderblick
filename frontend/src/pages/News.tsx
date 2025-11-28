import React, { useEffect, useState } from 'react';
import { Box, Button, Card, CardContent, Typography, Stack, IconButton } from '@mui/material';
import EditIcon from '@mui/icons-material/Edit';
import DeleteIcon from '@mui/icons-material/Delete';
import AddIcon from '@mui/icons-material/Add';
import NewsCreateModal from '../modals/NewsCreateModal';
import NewsEditModal from '../modals/NewsEditModal';
import { DynamicConfirmationModal } from '../modals/DynamicConfirmationModal';
import { apiJson } from '../utils/api';

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

interface Club { id: number; name: string; }
interface Team { id: number; name: string; }
interface VisibilityOption { label: string; value: string; }

interface NewsApiResponse {
  clubs: Club[];
  news: NewsItem[];
  teams: Team[];
  visibilityOptions: VisibilityOption[];
}

const News: React.FC = () => {
  const [news, setNews] = useState<NewsItem[]>([]);
  const [clubs, setClubs] = useState<Club[]>([]);
  const [teams, setTeams] = useState<Team[]>([]);
  const [visibilityOptions, setVisibilityOptions] = useState<VisibilityOption[]>([]);
  const [modalOpen, setModalOpen] = useState(false);
  const [editModalOpen, setEditModalOpen] = useState(false);
  const [editNews, setEditNews] = useState<NewsItem | null>(null);
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [deleteNews, setDeleteNews] = useState<NewsItem | null>(null);
  const [deleteLoading, setDeleteLoading] = useState(false);
    const handleEdit = (item: NewsItem) => {
      setEditNews(item);
      setEditModalOpen(true);
    };

    const handleDelete = (item: NewsItem) => {
      setDeleteNews(item);
      setDeleteDialogOpen(true);
    };

    const confirmDelete = async () => {
      if (!deleteNews) return;
      setDeleteLoading(true);
      try {
        await apiJson(`/news/${deleteNews.id}/delete`, { method: 'POST' });
        setDeleteDialogOpen(false);
        setDeleteNews(null);
        fetchNews();
      } catch (e) {
        // Fehlerbehandlung könnte hier ergänzt werden
      } finally {
        setDeleteLoading(false);
      }
    };
  const [loading, setLoading] = useState(true);

  const fetchNews = async () => {
    setLoading(true);
    try {
      const res = await apiJson('/news');
      const data = res as NewsApiResponse;
      console.log(data.news);
      setNews(data.news || []);
      setClubs(data.clubs || []);
      setTeams(data.teams || []);
      setVisibilityOptions(data.visibilityOptions || []);
    } catch (e) {
        console.error(e);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchNews();
  }, []);

  return (
    <Box sx={{mx: 'auto', p: 3 }}>
      <Stack direction="row" justifyContent="space-between" alignItems="center" mb={4}>
        <Typography variant="h4">News</Typography>
        {news.length > 0 && (
          <Button variant="contained" startIcon={<AddIcon />} onClick={() => setModalOpen(true)}>
            Neue Nachricht erstellen
          </Button>
        )}
      </Stack>
      {loading ? (
        <Typography>Loading...</Typography>
      ) : news.length === 0 ? (
        <Box textAlign="center" py={5}>
          <Typography color="text.secondary" variant="h6">Keine News verfügbar.</Typography>
          <Button variant="outlined" startIcon={<AddIcon />} onClick={() => setModalOpen(true)} sx={{ mt: 2 }}>
            Erste Nachricht erstellen
          </Button>
        </Box>
      ) : (
        <>
          {news.map(item => (
            <Card key={item.id} sx={{ mb: 2 }}>
              <CardContent>
                <Stack direction="row" justifyContent="space-between" alignItems="flex-start" spacing={2}>
                  <Box sx={{ flex: 1 }}>
                    <Typography variant="h6">{item.title}</Typography>
                    <Typography variant="body1" sx={{ mb: 1 }}>{item.content}</Typography>
                    <Typography variant="caption" color="text.secondary">
                      {item.createdAt && new Date(item.createdAt).toLocaleString()} – {item.createdByUserName}
                    </Typography>
                  </Box>
                  <Stack direction="row" spacing={1}>
                    <IconButton aria-label="Bearbeiten" onClick={() => handleEdit(item)} size="small">
                      <EditIcon fontSize="small" />
                    </IconButton>
                    <IconButton aria-label="Löschen" onClick={() => handleDelete(item)} size="small" color="error">
                      <DeleteIcon fontSize="small" />
                    </IconButton>
                  </Stack>
                </Stack>
              </CardContent>
            </Card>
          ))}
        </>
      )}
      <NewsEditModal
        open={editModalOpen}
        onClose={() => { setEditModalOpen(false); setEditNews(null); }}
        onSuccess={() => { setEditModalOpen(false); setEditNews(null); fetchNews(); }}
        news={editNews || { id: 0, title: '', content: '' }}
      />
      <DynamicConfirmationModal
        open={deleteDialogOpen}
        onClose={() => setDeleteDialogOpen(false)}
        onConfirm={confirmDelete}
        title="News löschen"
        message={deleteNews ? `Möchten Sie die News \"${deleteNews.title}\" wirklich löschen?` : ''}
        confirmText="Löschen"
        confirmColor="error"
        loading={deleteLoading}
      />
      <NewsCreateModal
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        onSuccess={fetchNews}
        clubs={clubs}
        teams={teams}
        visibilityOptions={visibilityOptions}
      />
    </Box>
  );
};

export default News;
