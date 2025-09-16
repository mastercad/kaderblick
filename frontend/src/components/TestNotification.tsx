import { useState, useEffect } from 'react';
import {
  Box,
  Button,
  Typography,
  Alert,
  Paper,
  TextField,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  FormControlLabel,
  Checkbox,
} from '@mui/material';
import { apiJson } from '../utils/api';
import { notificationService } from '../services/notificationService';
import { useNotifications } from '../context/NotificationContext';

interface TestNotificationProps {}

export const TestNotification: React.FC<TestNotificationProps> = () => {
  const [message, setMessage] = useState('');
  const [type, setType] = useState('news');
  const [title, setTitle] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [showToast, setShowToast] = useState(true);
  const [showPush, setShowPush] = useState(true);
  
  const { addNotification, requestPermission } = useNotifications();

  const handleRequestPermission = async () => {
    const granted = await requestPermission();
    setMessage(granted ? 'Push-Berechtigung erteilt!' : 'Push-Berechtigung verweigert');
  };

  const sendTestNotification = async () => {
    if (!title.trim()) {
      setMessage('Bitte geben Sie einen Titel ein');
      return;
    }

    setIsLoading(true);
    try {
      const result = await apiJson('/api/notifications/test', {
        method: 'POST',
        body: {
          type,
          title,
          message: message || undefined
        }
      });

      setMessage('Test-Benachrichtigung erfolgreich gesendet!');
      setTitle('');
      setMessage('');
    } catch (error) {
      setMessage('Fehler beim Senden der Test-Benachrichtigung');
    } finally {
      setIsLoading(false);
    }
  };

  const sendLocalNotification = () => {
    if (!title.trim()) {
      setMessage('Bitte geben Sie einen Titel ein');
      return;
    }

    addNotification({
      type: type as any,
      title,
      message: message || '',
      showToast,
      showPush,
    });

    setMessage('Lokale Benachrichtigung gesendet!');
    setTitle('');
    setMessage('');
  };

  const testSSEConnection = () => {
    setMessage('SSE-Verbindung läuft automatisch im Hintergrund. Server-Test-Benachrichtigungen sollten binnen 5 Sekunden ankommen.');
  };

  return (
    <Paper sx={{ p: 3, maxWidth: 600, mx: 'auto' }}>
      <Typography variant="h5" gutterBottom>
        Notification System Test
      </Typography>
      
      <Box sx={{ mb: 3 }}>
        <Typography variant="h6" gutterBottom>
          Test-Benachrichtigung senden
        </Typography>
        
        <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
          <FormControl fullWidth>
            <InputLabel>Benachrichtigungstyp</InputLabel>
            <Select
              value={type}
              label="Benachrichtigungstyp"
              onChange={(e) => setType(e.target.value)}
            >
              <MenuItem value="news">News</MenuItem>
              <MenuItem value="message">Nachricht</MenuItem>
              <MenuItem value="participation">Teilnahme</MenuItem>
              <MenuItem value="system">System</MenuItem>
            </Select>
          </FormControl>
          
          <TextField
            label="Titel"
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            fullWidth
            required
          />
          
          <TextField
            label="Nachricht (optional)"
            value={message}
            onChange={(e) => setMessage(e.target.value)}
            fullWidth
            multiline
            rows={3}
          />
          
          <Box sx={{ display: 'flex', gap: 2 }}>
            <FormControlLabel
              control={
                <Checkbox
                  checked={showToast}
                  onChange={(e) => setShowToast(e.target.checked)}
                />
              }
              label="Toast anzeigen"
            />
            <FormControlLabel
              control={
                <Checkbox
                  checked={showPush}
                  onChange={(e) => setShowPush(e.target.checked)}
                />
              }
              label="Push-Notification"
            />
          </Box>

          <Box sx={{ display: 'flex', gap: 1 }}>
            <Button
              variant="contained"
              onClick={sendTestNotification}
              disabled={isLoading}
              sx={{ flex: 1 }}
            >
              {isLoading ? 'Sende...' : 'Server Test-Benachrichtigung'}
            </Button>
            
            <Button
              variant="outlined"
              onClick={sendLocalNotification}
              sx={{ flex: 1 }}
            >
              Lokale Test-Benachrichtigung
            </Button>
          </Box>
        </Box>
      </Box>
      
      <Box sx={{ mb: 3 }}>
        <Typography variant="h6" gutterBottom>
          Push-Berechtigung
        </Typography>
        
        <Button
          variant="outlined"
          onClick={handleRequestPermission}
          fullWidth
        >
          Push-Berechtigung anfordern
        </Button>
      </Box>

      <Box sx={{ mb: 3 }}>
        <Typography variant="h6" gutterBottom>
          SSE-Verbindung testen
        </Typography>
        
        <Button
          variant="outlined"
          onClick={testSSEConnection}
          fullWidth
        >
          SSE-Status prüfen
        </Button>
      </Box>
      
      {message && (
        <Alert severity={message.includes('Fehler') ? 'error' : 'success'} sx={{ mt: 2 }}>
          {message}
        </Alert>
      )}
    </Paper>
  );
};
