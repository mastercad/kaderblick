import React, { useEffect, useState } from 'react';
import { Box, Button, Card, CardContent, Typography, Stack } from '@mui/material';
import AddIcon from '@mui/icons-material/Add';
import NewsCreateModal from '../modals/NewsCreateModal';
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
    <Box sx={{mx: 'auto', mt: 4, p: 3 }}>
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
        news.map(item => (
          <Card key={item.id} sx={{ mb: 2 }}>
            <CardContent>
              <Typography variant="h6">{item.title}</Typography>
              <Typography variant="body1" sx={{ mb: 1 }}>{item.content}</Typography>
              <Typography variant="caption" color="text.secondary">
                {item.createdAt && new Date(item.createdAt).toLocaleString()} – {item.createdByUserName}
              </Typography>
            </CardContent>
          </Card>
        ))
      )}
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
