import React, { useEffect, useState } from 'react';
import {
  Box,
  Typography,
  Card,
  CardContent,
  List,
  ListItem,
  ListItemButton,
  ListItemText,
  Chip,
  Button,
  Alert,
  CircularProgress,
  Divider
} from '@mui/material';
import {
  SportsSoccer as SoccerIcon,
  PlayArrow as LiveIcon,
  Schedule as ScheduleIcon,
  CheckCircle as CompletedIcon
} from '@mui/icons-material';
import { fetchGamesOverview, GamesOverviewData } from '../services/games';
import { Game, GameWithScore } from '../types/games';
import { useAuth } from '../context/AuthContext';
import Location from '../components/Location';

interface GamesProps {
  onGameSelect?: (gameId: number) => void;
}

export default function Games({ onGameSelect }: GamesProps) {
  const { user } = useAuth();
  const [data, setData] = useState<GamesOverviewData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    loadGamesOverview();
  }, []);

  const loadGamesOverview = async () => {
    try {
      setLoading(true);
      setError(null);
      const result = await fetchGamesOverview();
      setData(result);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Fehler beim Laden der Spiele');
    } finally {
      setLoading(false);
    }
  };

  const handleGameClick = (gameId: number) => {
    if (onGameSelect) {
      onGameSelect(gameId);
    }
  };

  const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('de-DE', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('de-DE', {
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  if (loading) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', alignItems: 'center', p: 4 }}>
        <CircularProgress />
      </Box>
    );
  }

  if (error) {
    return (
      <Box sx={{ p: 3 }}>
        <Alert severity="error" sx={{ mb: 2 }}>
          {error}
        </Alert>
        <Button variant="contained" onClick={loadGamesOverview}>
          Erneut versuchen
        </Button>
      </Box>
    );
  }

  if (!data) {
    return (
      <Box sx={{ p: 3 }}>
        <Alert severity="info">Keine Daten verfügbar</Alert>
      </Box>
    );
  }

  const GameListItem = ({ game, isRunning = false, score }: { 
    game: Game; 
    isRunning?: boolean; 
    score?: { homeScore: number | null; awayScore: number | null } 
  }) => (
    <ListItem disablePadding sx={{ display: 'flex', alignItems: 'stretch' }}>
      <Box sx={{ flex: 1, minWidth: 0 }}>
        <ListItemButton onClick={() => handleGameClick(game.id)} sx={{ alignItems: 'flex-start' }}>
          <ListItemText
            primary={
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                <Typography variant="body1" component="span">
                  <strong>{game.homeTeam.name}</strong> vs <strong>{game.awayTeam.name}</strong>
                </Typography>
                {isRunning && (
                  <Chip 
                    icon={<LiveIcon />} 
                    label="Live" 
                    color="success" 
                    size="small" 
                  />
                )}
                {score && score.homeScore !== null && score.awayScore !== null && (
                  <Chip 
                    label={`${score.homeScore} : ${score.awayScore}`} 
                    variant="outlined" 
                    size="small" 
                  />
                )}
              </Box>
            }
            secondary={
              <Typography variant="body2" color="text.secondary">
                {game.calendarEvent?.startDate && formatDateTime(game.calendarEvent.startDate)}
                {game.calendarEvent?.endDate && ` - ${formatTime(game.calendarEvent.endDate)}`}
              </Typography>
            }
          />
          {isRunning && (
            <Button
              variant="contained"
              color="success"
              size="small"
              startIcon={<SoccerIcon />}
              onClick={(e) => {
                e.stopPropagation();
                // Link zu Game Events Seite
                window.open(`/game/${game.id}/events`, '_blank');
              }}
              sx={{ ml: 2, alignSelf: 'center' }}
            >
              Spielereignis erfassen
            </Button>
          )}
        </ListItemButton>
      </Box>
      <Box sx={{ display: 'block', minWidth: 120, pl: 2, pr: 1, alignSelf: 'center' }}>
        {game.location && (
          <Location
            name={game.location.name}
            latitude={game.location.latitude}
            longitude={game.location.longitude}
            address={game.location.address}
          />
        )}
      </Box>
    </ListItem>
  );

  return (
    <Box sx={{ p: 3 }}>
      <Typography variant="h4" component="h1" gutterBottom sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
        <SoccerIcon fontSize="large" />
        Spiele
      </Typography>

      {/* Laufende Spiele */}
      {data.running_games.length > 0 && (
        <Card sx={{ mb: 3 }}>
          <CardContent>
            <Typography variant="h6" component="h2" gutterBottom sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
              <LiveIcon color="success" />
              Aktuell laufende Spiele
            </Typography>
            <List>
              {data.running_games.map((game, index) => (
                <React.Fragment key={game.id}>
                  <GameListItem game={game} isRunning={true} />
                  {index < data.running_games.length - 1 && <Divider />}
                </React.Fragment>
              ))}
            </List>
          </CardContent>
        </Card>
      )}

      {/* Anstehende Spiele */}
      {data.upcoming_games.length > 0 && (
        <Card sx={{ mb: 3 }}>
          <CardContent>
            <Typography variant="h6" component="h2" gutterBottom sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
              <ScheduleIcon color="primary" />
              Anstehende Spiele
            </Typography>
            <List>
              {data.upcoming_games.map((game, index) => (
                <React.Fragment key={game.id}>
                  <GameListItem game={game} />
                  {index < data.upcoming_games.length - 1 && <Divider />}
                </React.Fragment>
              ))}
            </List>
          </CardContent>
        </Card>
      )}

      {/* Absolvierte Spiele */}
      {data.finished_games.length > 0 && (
        <Card sx={{ mb: 3 }}>
          <CardContent>
            <Typography variant="h6" component="h2" gutterBottom sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
              <CompletedIcon color="action" />
              Absolvierte Spiele
            </Typography>
            <List>
              {data.finished_games.map((gameData, index) => (
                <React.Fragment key={gameData.game.id}>
                  <GameListItem 
                    game={gameData.game} 
                    score={{ homeScore: gameData.homeScore, awayScore: gameData.awayScore }}
                  />
                  {index < data.finished_games.length - 1 && <Divider />}
                </React.Fragment>
              ))}
            </List>
          </CardContent>
        </Card>
      )}

      {/* Keine Spiele */}
      {data.running_games.length === 0 && 
       data.upcoming_games.length === 0 && 
       data.finished_games.length === 0 && (
        <Alert severity="info" sx={{ mt: 3 }}>
          Keine Spiele gefunden.
        </Alert>
      )}
    </Box>
  );
}
