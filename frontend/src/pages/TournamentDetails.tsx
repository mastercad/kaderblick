import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  Box,
  Typography,
  Card,
  CardContent,
  List,
  ListItem,
  ListItemButton,
  Chip,
  Button,
  Alert,
  CircularProgress,
  Divider,
  IconButton,
  Switch,
  FormControlLabel,
} from '@mui/material';
import {
  ArrowBack as ArrowBackIcon,
  EmojiEvents as TournamentIcon,
  SportsSoccer as SoccerIcon,
  PlayArrow as LiveIcon,
  Schedule as ScheduleIcon,
  CheckCircle as CompletedIcon,
} from '@mui/icons-material';
import { fetchTournamentDetails } from '../services/games';
import { TournamentDetail, TournamentMatchOverview } from '../types/games';
import { useAuth } from '../context/AuthContext';
import Location from '../components/Location';
import { WeatherDisplay } from '../components/WeatherIcons';
import WeatherModal from '../modals/WeatherModal';
import { formatDateTime, formatTime } from '../utils/formatter';
import { GamesOverviewData, fetchGamesOverview } from '../services/games';

export default function TournamentDetails() {
  const { user } = useAuth();
  const params = useParams<{ id: string }>();
  const navigate = useNavigate();
  const tournamentId = params.id ? parseInt(params.id, 10) : undefined;

  const [tournament, setTournament] = useState<TournamentDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [weatherModalOpen, setWeatherModalOpen] = useState(false);
  const [selectedEventId, setSelectedEventId] = useState<number | null>(null);
  const [showAllMatches, setShowAllMatches] = useState(false);
  const [userTeamIds, setUserTeamIds] = useState<number[]>([]);

  const openWeatherModal = (eventId: number | null) => {
    setSelectedEventId(eventId);
    setWeatherModalOpen(true);
  };

  useEffect(() => {
    if (tournamentId) {
      loadTournament();
      loadUserTeamIds();
    }
  }, [tournamentId]);

  const loadTournament = async () => {
    if (!tournamentId) return;
    try {
      setLoading(true);
      setError(null);
      const result = await fetchTournamentDetails(tournamentId);
      if ('error' in result) {
        throw new Error(String((result as any).error));
      }
      setTournament(result as TournamentDetail);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Fehler beim Laden des Turniers');
    } finally {
      setLoading(false);
    }
  };

  const loadUserTeamIds = async () => {
    try {
      const overview = await fetchGamesOverview();
      if (!('error' in overview)) {
        setUserTeamIds((overview as GamesOverviewData).userTeamIds || []);
      }
    } catch {
      // ignore - userTeamIds just won't be available for filtering
    }
  };

  const handleGameClick = (gameId: number) => {
    navigate(`/games/${gameId}`);
  };

  const handleBack = () => {
    navigate('/games');
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
        <Button variant="contained" onClick={loadTournament}>
          Erneut versuchen
        </Button>
      </Box>
    );
  }

  if (!tournament) {
    return (
      <Box sx={{ p: 3 }}>
        <Alert severity="info">Turnier nicht gefunden</Alert>
      </Box>
    );
  }

  const allMatches = tournament.matches || [];

  // Filter matches by user's teams
  const userMatches = userTeamIds.length > 0
    ? allMatches.filter(m =>
        (m.homeTeam && userTeamIds.includes(m.homeTeam.id)) ||
        (m.awayTeam && userTeamIds.includes(m.awayTeam.id))
      )
    : allMatches;

  const displayedMatches = showAllMatches ? allMatches : userMatches;
  const hasUserFilter = userTeamIds.length > 0 && userMatches.length < allMatches.length;

  // Group matches by stage
  const matchesByStage = displayedMatches.reduce<Record<string, TournamentMatchOverview[]>>((acc, match) => {
    const stage = match.stage || 'Sonstige';
    if (!acc[stage]) acc[stage] = [];
    acc[stage].push(match);
    return acc;
  }, {});

  const getStatusChip = (status: string) => {
    switch (status) {
      case 'running':
        return <Chip icon={<LiveIcon />} label="Live" color="success" size="small" />;
      case 'upcoming':
        return <Chip icon={<ScheduleIcon />} label="Anstehend" color="primary" size="small" />;
      case 'finished':
        return <Chip icon={<CompletedIcon />} label="Abgeschlossen" color="default" size="small" />;
      default:
        return null;
    }
  };

  return (
    <Box sx={{ p: 3 }}>
      {/* Header */}
      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, mb: 2 }}>
        <IconButton onClick={handleBack}>
          <ArrowBackIcon />
        </IconButton>
        <TournamentIcon color="warning" fontSize="large" />
        <Typography variant="h4" component="h1" sx={{ flex: 1 }}>
          {tournament.name}
        </Typography>
        {getStatusChip(tournament.status)}
      </Box>

      {/* Turnier-Info Card */}
      <Card sx={{ mb: 3 }}>
        <CardContent>
          <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 2, alignItems: 'center' }}>
            {/* Datum */}
            {tournament.calendarEvent?.startDate && (
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 0.5 }}>
                <ScheduleIcon fontSize="small" color="action" />
                <Typography variant="body2" color="text.secondary">
                  {formatDateTime(tournament.calendarEvent.startDate)}
                  {tournament.calendarEvent.endDate && ` - ${formatTime(tournament.calendarEvent.endDate)}`}
                </Typography>
              </Box>
            )}

            {/* Ort */}
            {tournament.location && (
              <Location
                id={tournament.location.id}
                name={tournament.location.name}
                address={tournament.location.address}
                longitude={tournament.location.longitude}
                latitude={tournament.location.latitude}
              />
            )}

            {/* Wetter */}
            {tournament.calendarEvent && (
              <span
                style={{ cursor: 'pointer', display: 'inline-flex', alignItems: 'center' }}
                title="Wetterdetails anzeigen"
                onClick={() => openWeatherModal(tournament.calendarEvent!.id)}
              >
                <WeatherDisplay
                  code={Array.isArray(tournament.calendarEvent.weatherData?.weatherCode) ? tournament.calendarEvent.weatherData.weatherCode[0] : undefined}
                  theme={'light'}
                  size={32}
                />
              </span>
            )}

            {/* Typ */}
            {tournament.type && (
              <Chip label={tournament.type} size="small" variant="outlined" />
            )}

            {/* Spiele-Anzahl */}
            <Chip label={`${allMatches.length} Spiele`} size="small" variant="outlined" />

            {/* Teams-Anzahl */}
            {tournament.teams.length > 0 && (
              <Chip label={`${tournament.teams.length} Teams`} size="small" variant="outlined" />
            )}
          </Box>

          {/* Einstellungen */}
          {tournament.settings && (
            <Box sx={{ mt: 2, display: 'flex', flexWrap: 'wrap', gap: 1 }}>
              {tournament.settings.roundDuration && (
                <Chip label={`${tournament.settings.roundDuration} Min. pro Halbzeit`} size="small" color="secondary" variant="outlined" />
              )}
              {tournament.settings.gameMode && (
                <Chip label={tournament.settings.gameMode} size="small" color="secondary" variant="outlined" />
              )}
            </Box>
          )}
        </CardContent>
      </Card>

      {/* Teams */}
      {tournament.teams.length > 0 && (
        <Card sx={{ mb: 3 }}>
          <CardContent>
            <Typography variant="h6" component="h2" gutterBottom sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
              <SoccerIcon fontSize="small" />
              Teams
            </Typography>
            <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1 }}>
              {tournament.teams.map(team => (
                <Chip
                  key={team.id}
                  label={`${team.name}${team.groupKey ? ` (Gruppe ${team.groupKey})` : ''}${team.seed ? ` #${team.seed}` : ''}`}
                  variant={userTeamIds.includes(team.teamId) ? 'filled' : 'outlined'}
                  color={userTeamIds.includes(team.teamId) ? 'primary' : 'default'}
                  size="small"
                />
              ))}
            </Box>
          </CardContent>
        </Card>
      )}

      {/* Filter-Toggle */}
      {hasUserFilter && (
        <Box sx={{ mb: 2 }}>
          <FormControlLabel
            control={
              <Switch
                size="small"
                checked={showAllMatches}
                onChange={() => setShowAllMatches(prev => !prev)}
              />
            }
            label={
              <Typography variant="body2" color="text.secondary">
                Alle Spiele anzeigen ({allMatches.length} statt {userMatches.length})
              </Typography>
            }
          />
        </Box>
      )}

      {/* Spiele */}
      {Object.keys(matchesByStage).length > 0 ? (
        Object.entries(matchesByStage).map(([stage, matches]) => (
          <Card key={stage} sx={{ mb: 2 }}>
            <CardContent>
              <Typography variant="h6" component="h2" gutterBottom>
                {stage}
              </Typography>
              <List>
                {matches.map((match, index) => (
                  <React.Fragment key={match.id}>
                    <ListItem disablePadding className="game-list-item-responsive">
                      <ListItemButton
                        onClick={() => match.gameId && handleGameClick(match.gameId)}
                        disabled={!match.gameId}
                        sx={{ width: '100%', alignItems: 'flex-start', p: { xs: 1, sm: 2 } }}
                      >
                        <Box sx={{ width: '100%' }}>
                          {/* Teams & Score */}
                          <Box sx={{
                            width: '100%',
                            mb: 0.5,
                            display: 'flex',
                            alignItems: 'center',
                            flexWrap: 'wrap',
                            gap: 1,
                          }}>
                            <Typography
                              variant="body1"
                              component="div"
                              sx={{
                                fontWeight: 700,
                                overflow: 'hidden',
                                textOverflow: 'ellipsis',
                                display: '-webkit-box',
                                WebkitLineClamp: { xs: 2, sm: 'unset' },
                                WebkitBoxOrient: 'vertical',
                                lineHeight: 1.2,
                                maxHeight: { xs: '2.6em', sm: 'unset' },
                                wordBreak: 'break-word',
                              }}
                            >
                              <strong>{match.homeTeam?.name || 'TBD'}</strong> vs <strong>{match.awayTeam?.name || 'TBD'}</strong>
                            </Typography>
                            {match.homeScore !== null && match.awayScore !== null && (
                              <Chip
                                label={`${match.homeScore} : ${match.awayScore}`}
                                variant="outlined"
                                size="small"
                              />
                            )}
                            {match.status === 'finished' && (
                              <Chip label="Beendet" size="small" color="default" />
                            )}
                          </Box>

                          {/* Zeit & Runde */}
                          <Box sx={{
                            display: 'flex',
                            flexDirection: 'row',
                            justifyContent: 'space-between',
                            alignItems: 'center',
                            width: '100%',
                            gap: 1,
                          }}>
                            <Typography variant="body2" color="text.secondary" sx={{ flex: 1, minWidth: 0 }}>
                              {match.scheduledAt && formatDateTime(match.scheduledAt)}
                              {match.round !== null && ` | Runde ${match.round}`}
                              {match.slot !== null && `, Spiel ${match.slot}`}
                            </Typography>
                            {match.location && (
                              <Box sx={{ flexShrink: 0, ml: 1, maxWidth: { xs: '60%', sm: 'unset' }, overflow: 'hidden', textOverflow: 'ellipsis' }}>
                                <Location
                                  id={match.location.id}
                                  name={match.location.name}
                                  address={match.location.address}
                                  longitude={match.location.longitude}
                                  latitude={match.location.latitude}
                                />
                              </Box>
                            )}
                          </Box>
                        </Box>
                      </ListItemButton>
                    </ListItem>
                    {index < matches.length - 1 && <Divider />}
                  </React.Fragment>
                ))}
              </List>
            </CardContent>
          </Card>
        ))
      ) : (
        <Alert severity="info">
          {allMatches.length > 0
            ? 'Keine Spiele für dein Team in diesem Turnier.'
            : 'Noch keine Turnierspiele angelegt.'}
        </Alert>
      )}

      <WeatherModal
        open={weatherModalOpen}
        onClose={() => setWeatherModalOpen(false)}
        eventId={selectedEventId}
      />
    </Box>
  );
}
