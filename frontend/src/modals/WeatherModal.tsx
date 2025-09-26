import React, { useEffect, useState } from 'react';
import {
  Dialog,
  DialogTitle,
  DialogContent,
  IconButton,
  Typography,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  CircularProgress,
  Alert
} from '@mui/material';
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
  };
  hourlyWeatherData: {
    time: string[];
    temperature_2m: number[];
    apparent_temperature: number[];
    precipitation: number[];
    precipitation_probability: number[];
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
        {data && (
          <TableContainer>
            <Table size="small">
              <TableHead>
                <TableRow>
                  <TableCell>Zeit</TableCell>
                  <TableCell>Wetter</TableCell>
                  <TableCell>Temp. (°C)</TableCell>
                  <TableCell>Gefühlt (°C)</TableCell>
                  <TableCell>Regen (mm)</TableCell>
                  <TableCell>Regenwahr.</TableCell>
                  <TableCell>Wind (km/h)</TableCell>
                  <TableCell>Böen (km/h)</TableCell>
                  <TableCell>Windrichtung</TableCell>
                  <TableCell>UV</TableCell>
                  <TableCell>Wolken (%)</TableCell>
                  <TableCell>Feuchte (%)</TableCell>
                  <TableCell>Druck (hPa)</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {Object.entries(data.hourlyWeatherData.time).map(([key]) => (
                  <TableRow key={key}>
                    <TableCell>{new Date(data.hourlyWeatherData.time[key]).toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' })}</TableCell>
                    <TableCell><WeatherDisplay code={data.hourlyWeatherData.weathercode[key]} theme="light" /></TableCell>
                    <TableCell>{data.hourlyWeatherData.temperature_2m[key]}</TableCell>
                    <TableCell>{data.hourlyWeatherData.apparent_temperature[key]}</TableCell>
                    <TableCell>{data.hourlyWeatherData.precipitation[key]}</TableCell>
                    <TableCell>{data.hourlyWeatherData.precipitation_probability[key]}%</TableCell>
                    <TableCell>{data.hourlyWeatherData.wind_speed_10m[key]}</TableCell>
                    <TableCell>{data.hourlyWeatherData.wind_gusts_10m[key]}</TableCell>
                    <TableCell>{data.hourlyWeatherData.wind_direction_10m[key]}°</TableCell>
                    <TableCell>{data.hourlyWeatherData.uv_index[key]}</TableCell>
                    <TableCell>{data.hourlyWeatherData.cloudcover[key]}</TableCell>
                    <TableCell>{data.hourlyWeatherData.relative_humidity_2m[key]}</TableCell>
                    <TableCell>{data.hourlyWeatherData.pressure_msl[key]}</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </TableContainer>
        )}
        {!loading && !error && !data && (
          <Typography>Keine Wetterdaten verfügbar.</Typography>
        )}
      </DialogContent>
    </Dialog>
  );
}

export default WeatherModal;