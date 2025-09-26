import React, { useEffect, useState } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  IconButton,
  Typography,
  CircularProgress,
  Alert,
  Box,
  Card,
  CardContent
} from '@mui/material';
import Grid from '@mui/material/Grid';
import CloseIcon from '@mui/icons-material/Close';
import { WeatherDisplay } from '../components/WeatherIcons';
import { apiJson } from '../utils/api';

interface WeatherModalProps {
  open: boolean;
  onClose: () => void;
  eventId: number | null;
}

interface WeatherData {
  dailyWeatherData: {
    time: string[];
    temperature_2m: number[];
    apparent_temperature: number[];
    precipitation: number[];
    precipitation_probability: number[];
    weathercode?: number[];
    cloudcover?: number[];
  };
  hourlyWeatherData: {
    time: string[];
    temperature_2m: number[];
    apparent_temperature: number[];
    precipitation: number[];
    precipitation_probability: number[];
    weathercode?: number[];
    cloudcover?: number[];
  };
}

const WeatherModal: React.FC<WeatherModalProps> = ({ open, onClose, eventId }) => {
  const [data, setData] = useState<WeatherData | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (open && eventId) {
      fetchWeatherData(eventId);
    }
  }, [open, eventId]);

  const fetchWeatherData = async (id: number) => {
    setLoading(true);
    setError(null);
    try {
      const json = await apiJson(`/api/calendar/event/${id}/weather-data`);
      setData(json);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Fehler beim Laden der Wetterdaten');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onClose={onClose} maxWidth="lg" fullWidth>
      <DialogTitle>
        Wetterverlauf
        <IconButton onClick={onClose} sx={{ position: 'absolute', right: 8, top: 8 }}>
          <CloseIcon />
        </IconButton>
      </DialogTitle>
      <DialogContent>
        {loading && (
          <CircularProgress />
        )}
        {error && (
          <Alert severity="error">{error}</Alert>
        )}
        {data && data.hourlyWeatherData && data.hourlyWeatherData.time ? (
          <Box sx={{ mt: 2 }}>
            <Grid container spacing={2}>
              {Object.entries(data.hourlyWeatherData.time).map(([index, time]) => {
                // Wettercode, Temperatur, etc. holen
                const temp = data.hourlyWeatherData.temperature_2m[index];
                const weatherCode = data.hourlyWeatherData.weathercode ? data.hourlyWeatherData.weathercode[index] : 0;
                const precipitation = data.hourlyWeatherData.precipitation[index];
                const clouds = data.hourlyWeatherData.cloudcover ? data.hourlyWeatherData.cloudcover[index] : 0;
                // Hintergrundfarbe je nach Wetter
                let bgColor = '#ffe082'; // sonnig
                if (precipitation > 0.5) bgColor = '#90caf9'; // regen
                else if (clouds > 60) bgColor = '#e0e0e0'; // bewölkt
                // Spruch
                let spruch = 'Genieße den Tag!';
                if (precipitation > 0.5) spruch = 'Vergiss den Regenschirm nicht!';
                else if (temp > 25) spruch = 'Sonnencreme nicht vergessen!';
                else if (temp < 10) spruch = 'Zieh dich warm an!';
                else if (clouds > 60) spruch = 'Heute eher gemütlich!';
                return (
                  <Grid key={index} item xs={12} sm={6} md={4}>
                    <Card sx={{ background: bgColor, borderRadius: 4, boxShadow: 3, transition: '0.3s', minHeight: 220 }}>
                      <CardContent>
                        <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', mb: 1 }}>
                          <span style={{ fontSize: 48 }}>
                            <WeatherDisplay code={weatherCode} theme="light" />
                          </span>
                        </Box>
                        <Typography variant="h4" align="center" sx={{ fontWeight: 'bold', color: temp > 25 ? '#d84315' : temp < 10 ? '#1565c0' : '#333' }}>
                          {temp}°C
                        </Typography>
                        <Typography align="center" sx={{ mb: 1, fontSize: 18 }}>{spruch}</Typography>
                        <Box sx={{ display: 'flex', justifyContent: 'space-between', fontSize: 14 }}>
                          <span style={{ marginRight: 5 }}>
                            {new Date(time).toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' })}
                          </span>
                          <span style={{ marginRight: 5 }}>
                            :
                          </span>
                          <span>Regen: {precipitation} mm</span>
                          <span style={{ marginLeft: 5, marginRight: 5 }}>/</span>
                          <span>Wolken: {clouds}%</span>
                        </Box>
                      </CardContent>
                    </Card>
                  </Grid>
                );
              })}
            </Grid>
          </Box>
        ) : data && (!data.hourlyWeatherData || !Array.isArray(data.hourlyWeatherData.time)) ? (
          <Typography>Keine Wetterdaten vorhanden</Typography>
        ) : null}
        {!loading && !error && !data && (
          <Typography>Keine Wetterdaten verfügbar.</Typography>
        )}
      </DialogContent>
    </Dialog>
  );
}

export default WeatherModal;