import React, { useEffect, useState } from 'react';
import Typography from '@mui/material/Typography';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Divider from '@mui/material/Divider';
import CircularProgress from '@mui/material/CircularProgress';
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
    <div style={{ width: '100%', display: 'flex', flexDirection: 'column', alignItems: 'stretch', justifyContent: 'flex-start' }}>
      {news.map((item, idx) => (
        <Card key={item.id} variant="outlined" sx={{ mb: 2, boxShadow: 0, width: '100%', display: 'block', alignSelf: 'stretch' }}>
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
  );
};
