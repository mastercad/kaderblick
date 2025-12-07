import React from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  Typography,
  Box,
  Chip,
  Divider,
  IconButton,
  useTheme,
  alpha
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import NewsIcon from '@mui/icons-material/Article';
import MessageIcon from '@mui/icons-material/Message';
import EventIcon from '@mui/icons-material/Event';
import SystemIcon from '@mui/icons-material/Info';
import { AppNotification } from '../types/notifications';

interface NotificationDetailModalProps {
  notification: AppNotification | null;
  open: boolean;
  onClose: () => void;
}

const getNotificationIcon = (type: AppNotification['type']) => {
  switch (type) {
    case 'news': return <NewsIcon />;
    case 'message': return <MessageIcon />;
    case 'participation': return <EventIcon />;
    case 'system': return <SystemIcon />;
    default: return <SystemIcon />;
  }
};

const getNotificationColor = (type: AppNotification['type']) => {
  switch (type) {
    case 'news': return '#1976d2';
    case 'message': return '#2e7d32';
    case 'participation': return '#ed6c02';
    case 'system': return '#0288d1';
    default: return '#757575';
  }
};

const getNotificationLabel = (type: AppNotification['type']) => {
  switch (type) {
    case 'news': return 'Neuigkeiten';
    case 'message': return 'Nachricht';
    case 'participation': return 'Teilnahme';
    case 'system': return 'System';
    default: return 'Benachrichtigung';
  }
};

export const NotificationDetailModal: React.FC<NotificationDetailModalProps> = ({
  notification,
  open,
  onClose
}) => {
  const theme = useTheme();

  if (!notification) {
    return null;
  }

  const color = getNotificationColor(notification.type);
  const icon = getNotificationIcon(notification.type);
  const label = getNotificationLabel(notification.type);

  return (
    <Dialog
      open={open}
      onClose={onClose}
      maxWidth="md"
      fullWidth
      PaperProps={{
        sx: {
          borderTop: `4px solid ${color}`,
        }
      }}
    >
      <DialogTitle sx={{ 
        display: 'flex', 
        alignItems: 'center', 
        justifyContent: 'space-between',
        pb: 1
      }}>
        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
          <Box sx={{ color, display: 'flex', alignItems: 'center' }}>
            {icon}
          </Box>
          <Typography variant="h6" component="span">
            {notification.title}
          </Typography>
        </Box>
        <IconButton
          aria-label="close"
          onClick={onClose}
          sx={{
            color: theme.palette.grey[500],
          }}
        >
          <CloseIcon />
        </IconButton>
      </DialogTitle>

      <Divider />

      <DialogContent sx={{ pt: 2 }}>
        <Box sx={{ mb: 2, display: 'flex', alignItems: 'center', gap: 1 }}>
          <Chip 
            label={label}
            size="small"
            sx={{ 
              backgroundColor: alpha(color, 0.1),
              color: color,
              fontWeight: 'medium'
            }}
          />
          <Typography variant="caption" color="text.secondary">
            {notification.timestamp instanceof Date 
              ? notification.timestamp.toLocaleString('de-DE', {
                  day: '2-digit',
                  month: '2-digit',
                  year: 'numeric',
                  hour: '2-digit',
                  minute: '2-digit'
                })
              : 'Ungültiges Datum'
            }
          </Typography>
        </Box>

        <Box sx={{ mb: 2 }}>
          <Typography variant="body1" sx={{ whiteSpace: 'pre-wrap', lineHeight: 1.6 }}>
            {notification.message}
          </Typography>
        </Box>

        {notification.data && Object.keys(notification.data).length > 0 && (
          <Box sx={{ 
            mt: 3,
            p: 2,
            backgroundColor: alpha(theme.palette.primary.main, 0.05),
            borderRadius: 1,
            border: `1px solid ${alpha(theme.palette.primary.main, 0.1)}`
          }}>
            <Typography variant="subtitle2" sx={{ mb: 1, fontWeight: 'bold' }}>
              Zusätzliche Informationen
            </Typography>
            {renderNotificationData(notification.type, notification.data)}
          </Box>
        )}
      </DialogContent>

      <DialogActions sx={{ px: 3, py: 2 }}>
        <Button onClick={onClose} variant="contained">
          Schließen
        </Button>
      </DialogActions>
    </Dialog>
  );
};

// Hilfsfunktion zum Rendern typenspezifischer Daten
const renderNotificationData = (type: AppNotification['type'], data: any) => {
  switch (type) {
    case 'news':
      return (
        <Box>
          {data.author && (
            <Typography variant="body2" sx={{ mb: 0.5 }}>
              <strong>Autor:</strong> {data.author}
            </Typography>
          )}
          {data.category && (
            <Typography variant="body2" sx={{ mb: 0.5 }}>
              <strong>Kategorie:</strong> {data.category}
            </Typography>
          )}
          {data.url && (
            <Typography variant="body2">
              <strong>Link:</strong>{' '}
              <a href={data.url} target="_blank" rel="noopener noreferrer">
                {data.url}
              </a>
            </Typography>
          )}
        </Box>
      );

    case 'message':
      return (
        <Box>
          {data.sender && (
            <Typography variant="body2" sx={{ mb: 0.5 }}>
              <strong>Von:</strong> {data.sender}
            </Typography>
          )}
          {data.conversationId && (
            <Typography variant="body2" sx={{ mb: 0.5 }}>
              <strong>Konversations-ID:</strong> {data.conversationId}
            </Typography>
          )}
        </Box>
      );

    case 'participation':
      return (
        <Box>
          {data.eventName && (
            <Typography variant="body2" sx={{ mb: 0.5 }}>
              <strong>Event:</strong> {data.eventName}
            </Typography>
          )}
          {data.eventDate && (
            <Typography variant="body2" sx={{ mb: 0.5 }}>
              <strong>Datum:</strong> {new Date(data.eventDate).toLocaleString('de-DE')}
            </Typography>
          )}
          {data.location && (
            <Typography variant="body2" sx={{ mb: 0.5 }}>
              <strong>Ort:</strong> {data.location}
            </Typography>
          )}
          {data.status && (
            <Typography variant="body2">
              <strong>Status:</strong> {data.status}
            </Typography>
          )}
        </Box>
      );

    case 'system':
      return (
        <Box>
          {data.version && (
            <Typography variant="body2" sx={{ mb: 0.5 }}>
              <strong>Version:</strong> {data.version}
            </Typography>
          )}
          {data.priority && (
            <Typography variant="body2" sx={{ mb: 0.5 }}>
              <strong>Priorität:</strong> {data.priority}
            </Typography>
          )}
        </Box>
      );

    default:
      return (
        <Box>
          {Object.entries(data).map(([key, value]) => (
            <Typography key={key} variant="body2" sx={{ mb: 0.5 }}>
              <strong>{key}:</strong> {String(value)}
            </Typography>
          ))}
        </Box>
      );
  }
};
