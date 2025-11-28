import React, { useEffect, useState } from 'react';
import Typography from '@mui/material/Typography';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Divider from '@mui/material/Divider';
import CircularProgress from '@mui/material/CircularProgress';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import IconButton from '@mui/material/IconButton';
import CloseIcon from '@mui/icons-material/Close';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import Button from '@mui/material/Button';
import { apiJson } from '../utils/api';
import { useWidgetRefresh } from '../context/WidgetRefreshContext';

type NewsItem = {
  id: number;
  title: string;
  createdAt: string;
  content: string;
};


export const NewsWidget: React.FC<{ config?: any; widgetId?: string }> = ({ config, widgetId }) => {
  const { getRefreshTrigger } = useWidgetRefresh();
  const [news, setNews] = useState<NewsItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [openNews, setOpenNews] = useState<NewsItem | null>(null);

  const refreshTrigger = widgetId ? getRefreshTrigger(widgetId) : 0;

  useEffect(() => {
    if (!widgetId) {
      setError('Widget-ID fehlt');
      setLoading(false);
      return;
    }
    setLoading(true);
    apiJson(`/widget/${widgetId}/content`)
      .then((data) => {
        setNews(data.news || []);
        setLoading(false);
      })
      .catch((e) => {
        setError(e.message);
        setLoading(false);
      });
  }, [widgetId, refreshTrigger]);

  if (loading) return <CircularProgress size={24} />;
  if (error) return <Typography color="error">{error}</Typography>;
  if (!news.length) return <Typography variant="body2" color="text.secondary">Keine News vorhanden.</Typography>;

  return (
    <>
      <div style={{ width: '100%', display: 'flex', flexDirection: 'column', alignItems: 'stretch', justifyContent: 'flex-start' }}>
        {news.map((item, idx) => (
          <Card
            key={item.id}
            variant="outlined"
            sx={{ mb: 2, boxShadow: 0, width: '100%', display: 'block', alignSelf: 'stretch', cursor: 'pointer' }}
            onClick={() => setOpenNews(item)}
          >
            <CardContent sx={{ pb: 1, pt: 1 }}>
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 12 }}>
                <Typography variant="subtitle1" fontWeight={600} noWrap sx={{ flex: 1 }}>
                  {item.title}
                </Typography>
                <Typography variant="caption" color="text.secondary" sx={{ whiteSpace: 'nowrap', ml: 2 }}>
                  {new Date(item.createdAt).toLocaleDateString()}
                </Typography>
              </div>
              <Typography variant="body2" color="text.secondary" sx={{ mt: 1, display: '-webkit-box', WebkitLineClamp: 3, WebkitBoxOrient: 'vertical', overflow: 'hidden' }}>
                {item.content}
              </Typography>
            </CardContent>
            {idx < news.length - 1 && <Divider />}
          </Card>
        ))}
      </div>
      <Dialog open={!!openNews} onClose={() => setOpenNews(null)} maxWidth="sm" fullWidth>
        {openNews && (
          <>
            <DialogTitle sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', pr: 1 }}>
              <span>{openNews.title}</span>
              <IconButton aria-label="close" onClick={() => setOpenNews(null)} size="small" sx={{ ml: 2 }}>
                <CloseIcon fontSize="small" />
              </IconButton>
            </DialogTitle>
            <DialogContent dividers>
              <Typography variant="caption" color="text.secondary" sx={{ mb: 2, display: 'block' }}>
                {new Date(openNews.createdAt).toLocaleString()}
              </Typography>
              <Typography variant="body1" sx={{ whiteSpace: 'pre-line' }}>
                {openNews.content}
              </Typography>
            </DialogContent>
            <DialogActions>
              <Button onClick={() => setOpenNews(null)} color="primary" autoFocus>
                Schließen
              </Button>
            </DialogActions>
          </>
        )}
      </Dialog>
    </>
  );
};
