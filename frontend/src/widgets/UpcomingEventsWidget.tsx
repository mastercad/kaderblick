import React, { useEffect, useState } from 'react';
import Typography from '@mui/material/Typography';
import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemText from '@mui/material/ListItemText';
import ListItemIcon from '@mui/material/ListItemIcon';
import CalendarTodayIcon from '@mui/icons-material/CalendarToday';
import LocationOnIcon from '@mui/icons-material/LocationOn';
import Box from '@mui/material/Box';
import CircularProgress from '@mui/material/CircularProgress';
import { apiJson } from '../utils/api';
import { useWidgetRefresh } from '../context/WidgetRefreshContext';

interface EventType {
  id: number;
  title: string;
  startDate: string;
  endDate?: string;
  location?: string;
  calendarEventType?: {
    name?: string;
    color?: string;
  };
}

export const UpcomingEventsWidget: React.FC<{ widgetId: string; config?: any }> = ({ widgetId }) => {
  const { getRefreshTrigger } = useWidgetRefresh();
  const [events, setEvents] = useState<EventType[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const refreshTrigger = getRefreshTrigger(widgetId);

  useEffect(() => {
    if (!widgetId) {
      setError('Widget-ID fehlt');
      setLoading(false);
      return;
    }
    setLoading(true);
    setError(null);
    apiJson(`/widget/${widgetId}/content`)
      .then((data) => {
        setEvents(data.events || []);
      })
      .catch((e) => setError(e.message))
      .finally(() => setLoading(false));
  }, [widgetId, refreshTrigger]);

  if (loading) {
    return <Box sx={{ display: 'flex', justifyContent: 'center', py: 3 }}><CircularProgress size={28} /></Box>;
  }
  if (error) {
    return <Typography color="error">{error}</Typography>;
  }
  if (!events.length) {
    return <Typography variant="body2" color="text.secondary" align="center">Keine anstehenden Termine</Typography>;
  }

  return (
    <List dense disablePadding>
      {events.map(event => (
        <ListItem key={event.id} alignItems="flex-start" sx={{ borderLeft: `4px solid ${event.calendarEventType?.color || '#1976d2'}`, mb: 1, pl: 1 }}>
          <ListItemIcon sx={{ minWidth: 32, color: event.calendarEventType?.color || 'primary.main' }}>
            <CalendarTodayIcon fontSize="small" />
          </ListItemIcon>
          <ListItemText
            primary={<>
              <Typography variant="subtitle2" component="span">{event.title}</Typography>
              {event.calendarEventType?.name && (
                <Box component="span" sx={{ ml: 1, fontSize: 12, color: event.calendarEventType.color || 'primary.main', fontWeight: 500 }}>
                  {event.calendarEventType.name}
                </Box>
              )}
            </>}
            secondary={<>
              <Typography variant="caption" color="text.secondary" component="span">
                {new Date(event.startDate).toLocaleDateString()} {event.endDate ? `${new Date(event.startDate).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })} - ${new Date(event.endDate).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}` : new Date(event.startDate).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
              </Typography>
              {event.location && (
                <Box component="span" sx={{ ml: 2, display: 'inline-flex', alignItems: 'center', fontSize: 12, color: 'text.secondary' }}>
                  <LocationOnIcon fontSize="inherit" sx={{ mr: 0.5 }} />
                  {event.location}
                </Box>
              )}
            </>}
          />
        </ListItem>
      ))}
    </List>
  );
};
