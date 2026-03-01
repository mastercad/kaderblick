import React, { useEffect, useState, useRef } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  Box,
  Typography,
  Card,
  CardContent,
  CardHeader,
  Button,
  Chip,
  List,
  ListItem,
  ListItemText,
  Alert,
  CircularProgress,
  IconButton,
  Fab,
  Icon,
  Link
} from '@mui/material';
import {
  ArrowBack as ArrowBackIcon,
  SportsSoccer as SoccerIcon,
  Edit as EditIcon,
  Delete as DeleteIcon,
  Add as AddIcon,
  VideoLibrary as VideoIcon,
  CalendarToday as CalendarIcon,
  Sync as SyncIcon,
  LocationOn as LocationIcon,
  ContentCut as ContentCutIcon
} from '@mui/icons-material';
import { 
  fetchGameDetails, 
  fetchGameEvents,
  deleteGameEvent, 
  syncFussballDe 
} from '../services/games';
import { fetchVideos, saveVideo, deleteVideo, Video, YoutubeLink, Camera } from '../services/videos';
import VideoModal from '../modals/VideoModal';
import VideoPlayModal from '../modals/VideoPlayModal';
import { VideoSegmentModal } from '../modals/VideoSegmentModal';
import { Game, GameEvent } from '../types/games';
import { useAuth } from '../context/AuthContext';
import { ToastProvider, useToast } from '../context/ToastContext';
import { ConfirmationModal } from '../modals/ConfirmationModal';
import { GameEventModal } from '../modals/GameEventModal';
import Location from '../components/Location';
import { getGameEventIconByCode } from '../constants/gameEventIcons';
import YouTubeIcon from '@mui/icons-material/YouTube';
import WeatherModal from '../modals/WeatherModal';
import { WeatherDisplay } from '../components/WeatherIcons';
import { formatEventTime, formatDateTime } from '../utils/formatter'
import { UserAvatar } from '../components/UserAvatar';
import { getAvatarFrameUrl } from '../utils/avatarFrame';
import { calculateCumulativeOffset } from '../utils/videoTimeline';

interface GameDetailsProps {
  gameId?: number;
  onBack?: () => void;
}

function GameDetailsInner({ gameId: propGameId, onBack }: GameDetailsProps) {
  // Ref für Video-Player-API
  const videoPlayerRef = useRef<any>(null);
  // State für Video-Event-Modal (im VideoPlayModal!)
  const [videoEventFormOpen, setVideoEventFormOpen] = useState(false);
  const [videoEventInitialMinute, setVideoEventInitialMinute] = useState<number | undefined>(undefined);

  // Handler für Event-Button: Zeit holen, Video pausieren, Event-Form öffnen (im VideoPlayModal)
  const handleCreateEventFromVideo = async () => {
    let seconds: number = 0;
    if (videoPlayerRef.current && typeof videoPlayerRef.current.getCurrentTime === 'function') {
      const sec = await videoPlayerRef.current.getCurrentTime();
      videoPlayerRef.current.pauseVideo?.();
      if (typeof sec === 'number' && !isNaN(sec)) {
        // Videozeit minus gameStart (Offset im Video) + kumulativer Offset = Spielzeit
        const gameStartOffset = videoToPlay?.gameStart ?? 0;
        const cumulativeOffset = videoToPlay ? calculateCumulativeOffset(
          videoToPlay as any,
          videos as any
        ) : 0;
        // sec ist absolute Video-Position, ziehe gameStart ab und addiere kumulative Zeit
        seconds = Math.round(sec - gameStartOffset + cumulativeOffset);
      }
    }
    setVideoEventInitialMinute(seconds);
    setVideoEventFormOpen(true);
  };
    // State für Play-Modal
    const [playVideoModalOpen, setPlayVideoModalOpen] = useState(false);
    const [videoToPlay, setVideoToPlay] = useState<Video | null>(null);

    // Handler für Play-Button
    const handleOpenPlayVideo = (video: Video) => {
      setVideoToPlay(video);
      setPlayVideoModalOpen(true);
    };

    const handleClosePlayVideo = () => {
      setPlayVideoModalOpen(false);
      setVideoToPlay(null);
    };
  const { user } = useAuth();
  const { showToast } = useToast();
  const params = useParams<{ id: string }>();
  const navigate = useNavigate();
  
  // Use URL param if available, otherwise fall back to prop
  const gameId = params.id ? parseInt(params.id, 10) : propGameId;
  
  const [game, setGame] = useState<Game | null>(null);
  const [gameStartDate, setGameStartDate] = useState<string | null>(null);
  const [gameEvents, setGameEvents] = useState<GameEvent[]>([]);
  const [homeScore, setHomeScore] = useState<number | null>(null);
  const [awayScore, setAwayScore] = useState<number | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [eventToDelete, setEventToDelete] = useState<GameEvent | null>(null);
  const [eventFormOpen, setEventFormOpen] = useState(false);
  const [eventToEdit, setEventToEdit] = useState<GameEvent | null>(null);
  const [syncing, setSyncing] = useState(false);
  const [videos, setVideos] = useState<Video[]>([]);
  const [videoTypes, setVideoTypes] = useState<any[]>([]);
  const [youtubeLinks, setYoutubeLinks] = useState<YoutubeLink[]>([]);
  const [cameras, setCameras] = useState<any[]>([]);
  const [eventVideos, setEventVideos] = useState<Record<number, Video[]>>({});
  const [mappedCameras, setMappedCameras] = useState<Record<number, string>>({});
  const [videoDialogOpen, setVideoDialogOpen] = useState(false);
  const [videoDialogLoading, setVideoDialogLoading] = useState(false);
  const [videoToEdit, setVideoToEdit] = useState<Video | null>(null);
  const [videoToDelete, setVideoToDelete] = useState<Video | null>(null);
  const [videoDeleteLoading, setVideoDeleteLoading] = useState(false);
  const [weatherModalOpen, setWeatherModalOpen] = useState(false);
  const [selectedEventId, setSelectedEventId] = useState<number | null>(null);
  const [videoSegmentModalOpen, setVideoSegmentModalOpen] = useState(false);

  useEffect(() => {
    if (!gameId) {
      setError('Keine Spiel-ID angegeben');
      setLoading(false);
      return;
    }
    loadGameDetails();
    loadVideos();
  }, [gameId]);

  const loadVideos = async () => {
    if (!gameId) return;
    try {
      const res = await fetchVideos(gameId);
      setVideos(res.videos);
      setYoutubeLinks(res.youtubeLinks);
      setVideoTypes(res.videoTypes);
      // Mapping Event-ID -> Videos
      const mapping: Record<number, Video[]> = {};
      if (res.videos) {
        res.videos.forEach((video: any) => {
          if (Array.isArray(video.eventIds)) {
            video.eventIds.forEach((eventId: number) => {
              if (!mapping[eventId]) mapping[eventId] = [];
              mapping[eventId].push(video);
            });
          }
        });
      }
      const mappedCameras: Record<number, string> = {};
      if (res.cameras) {
        res.cameras.forEach((camera: Camera) => {
          if (camera.id) { 
            mappedCameras[camera.id] = camera.name;
          }
        });
      }

      setCameras(res.cameras);
      setMappedCameras(mappedCameras);
      setEventVideos(mapping);
    } catch (e) {
      // Fehler ignorieren, Videos optional
    }
  };

  // Video Handlers
  const handleOpenAddVideo = () => {
    setVideoToEdit(null);
    setVideoDialogOpen(true);
  };

  const handleOpenEditVideo = (video: Video) => {
    setVideoToEdit(video);
    setVideoDialogOpen(true);
  };

  const handleCloseVideoDialog = () => {
    setVideoDialogOpen(false);
    setVideoToEdit(null);
  };

  const handleSaveVideo = async (data: any) => {
    if (!gameId) return;
    setVideoDialogLoading(true);
    try {
      await saveVideo(gameId, data);
      setVideoDialogOpen(false);
      setVideoToEdit(null);
      await loadVideos();
      await loadGameEvents(); // Reload events so timeline has current data
      showToast('Das Video wurde erfolgreich gespeichert.', 'success');
    } catch (e: any) {
      setError(e.message || 'Fehler beim Speichern des Videos');
    } finally {
      setVideoDialogLoading(false);
    }
  };

  const handleDeleteVideo = async () => {
    if (!videoToDelete) return;
    setVideoDeleteLoading(true);
    try {
      await deleteVideo(videoToDelete.id);
      setVideoToDelete(null);
      await loadVideos();
      await loadGameEvents(); // Reload events for consistency
    } catch (e: any) {
      setError(e.message || 'Fehler beim Löschen des Videos');
    } finally {
      setVideoDeleteLoading(false);
    }
  };

  const loadGameDetails = async () => {
    if (!gameId) return;
    try {
      setLoading(true);
      setError(null);
      const result = await fetchGameDetails(gameId);
      setGame(result.game);
      setGameEvents(result.gameEvents);
      setHomeScore(result.homeScore);
      setAwayScore(result.awayScore);
      setGameStartDate(result.game?.calendarEvent?.startDate ?? null);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Fehler beim Laden der Spieldetails');
    } finally {
      setLoading(false);
    }
  };

  // Nur Events laden (für SPA-Feeling)
  const loadGameEvents = async () => {
    if (!gameId) return;
    try {
      const events = await fetchGameEvents(gameId);
      // Repariere Events: Falls timestamp fehlt, berechne ihn aus minute + gameStartDate
      const repairedEvents = events.map(event => {
        if (!event.timestamp && game?.calendarEvent?.startDate && typeof event.minute === 'number') {
          const gameStart = new Date(game.calendarEvent.startDate);
          const eventTime = new Date(gameStart.getTime() + event.minute * 1000);
          return { ...event, timestamp: eventTime.toISOString() };
        }
        return event;
      });
      setGameEvents(repairedEvents);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Fehler beim Laden der Ereignisse');
    }
  };

  const handleDeleteEvent = async () => {
    if (!eventToDelete || !gameId) return;
    
    try {
      await deleteGameEvent(gameId, eventToDelete.id);
      setEventToDelete(null);
      // Reload data without showing loading spinner
      const result = await fetchGameDetails(gameId);
      setGame(result.game);
      setGameEvents(result.gameEvents);
      setHomeScore(result.homeScore);
      setAwayScore(result.awayScore);
      setGameStartDate(result.game?.calendarEvent?.startDate ?? null);
      await loadVideos(); // Reload videos to update event mappings
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Fehler beim Löschen des Ereignisses');
    }
  };

  const handleSyncFussballDe = async () => {
    if (!game?.fussballDeUrl || !gameId) return;
    
    try {
      setSyncing(true);
      await syncFussballDe(gameId);
      await loadGameDetails(); // Reload data
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Fehler beim Synchronisieren mit Fussball.de');
    } finally {
      setSyncing(false);
    }
  };

  const handleEventFormSuccess = async () => {
    setEventFormOpen(false);
    setEventToEdit(null);
    // Reload data without showing loading spinner
    if (!gameId) return;
    try {
      const result = await fetchGameDetails(gameId);
      setGame(result.game);
      setGameEvents(result.gameEvents);
      setHomeScore(result.homeScore);
      setAwayScore(result.awayScore);
      setGameStartDate(result.game?.calendarEvent?.startDate ?? null);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Fehler beim Laden der Spieldetails');
    }
    await loadVideos(); // Reload videos to update event mappings
  };

  // Nur für Event-Updates (z.B. Drag&Drop auf Timeline) - weniger störend
  const handleEventUpdated = async () => {
    await loadGameEvents(); // Nur Events neu laden für schnelles Update
  };

  const openWeatherModal = (eventId: number | null) => {
    setSelectedEventId(eventId);
    setWeatherModalOpen(true);
  };

  const isGameRunning = () => {
    if (!game?.calendarEvent?.startDate || !game?.calendarEvent?.endDate) return false;
    const now = new Date();
    const start = new Date(game.calendarEvent.startDate);
    const end = new Date(game.calendarEvent.endDate);
    return now >= start && now <= end;
  };

  const canCreateEvents = () => {
    return game?.permissions?.can_create_game_events ?? false;
  };

  const canCreateVideos = () => {
    return game?.permissions?.can_create_videos ?? false;
  }

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
        <Button variant="contained" onClick={loadGameDetails}>
          Erneut versuchen
        </Button>
      </Box>
    );
  }

  if (!game) {
    return (
      <Box sx={{ p: 3 }}>
        <Alert severity="info">Spiel nicht gefunden</Alert>
      </Box>
    );
  }

  return (
    <Box sx={{ p: 3 }}>
      {/* Header */}
      <Box sx={{ display: 'flex', alignItems: 'center', mb: 3 }}>
        <IconButton 
          onClick={() => onBack ? onBack() : navigate('/games')} 
          sx={{ mr: 2 }}
        >
          <ArrowBackIcon />
        </IconButton>
        <Typography variant="h4" component="h1">
          Spieldetails
        </Typography>
      </Box>

      {/* Game Info Card */}
      <Card sx={{ mb: 3 }}>
        <CardContent>
          <Box sx={{ mb: 2, display: 'flex', flexDirection: 'row', alignItems: 'stretch', gap: 2 }}>
            {/* Linke Box: Teams, Zeit, Location */}
            <Box sx={{ flex: 1, display: 'flex', flexDirection: 'column', justifyContent: 'flex-start', gap: 1 }}>
              <Typography variant="h5" component="h2" gutterBottom>
                <strong>{game.homeTeam.name}</strong> vs <strong>{game.awayTeam.name}</strong>
              </Typography>
              {(game.calendarEvent?.startDate || game.location) && (
                <Box sx={{ display: 'flex', alignItems: 'center', gap: 2 }}>
                  {game.calendarEvent?.startDate && (
                    <>
                      <CalendarIcon fontSize="small" />
                      <Typography variant="body2" color="text.secondary">
                        {formatDateTime(game.calendarEvent.startDate)}
                        {game.calendarEvent.endDate && ` - ${formatDateTime(game.calendarEvent.endDate).split(' ')[1]}`}
                      </Typography>
                    </>
                  )}
                  {game.location && (
                    <Location 
                      id={game.location.id}
                      name={game.location.name}
                      latitude={game.location.latitude}
                      longitude={game.location.longitude}
                      address={game.location.address}
                    />
                  )}
                </Box>
              )}
            </Box>
            {/* Rechte Box: Weather Icon */}
            <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'center', minWidth: 80, pr: 2, pt: 1 }}
              onClick={() => {
                openWeatherModal(game.calendarEvent?.id ?? null);
              }}>
              <span style={{ cursor: 'pointer' }} title="Wetterdetails anzeigen">
                <WeatherDisplay 
                  code={game.weatherData?.dailyWeatherData?.weathercode?.[0]} theme={'light'}
                />
              </span>
            </Box>
          </Box>

          <Box sx={{ mb: 2 }}>
            {homeScore !== null && awayScore !== null ? (
              <Chip 
                label={`${homeScore} : ${awayScore}`} 
                color="primary" 
                sx={{ fontSize: '1.2rem', p: 1 }}
              />
            ) : (
              <Chip label="Noch kein Ergebnis" color="default" />
            )}
          </Box>
          {game.fussballDeUrl && (
            <Box sx={{ mt: 2 }}>
              <Button
                variant="outlined"
                startIcon={<SyncIcon />}
                onClick={handleSyncFussballDe}
                disabled={syncing}
                size="small"
              >
                {syncing ? 'Synchronisiere...' : 'Mit Fussball.de synchronisieren'}
              </Button>
            </Box>
          )}
        </CardContent>
      </Card>

      {/* Game Events Card */}
      <Card className="gameevents-mobile-card" sx={{ mb: 3 }}>
        <CardHeader
          title="Spielereignisse"
          action={
            canCreateEvents() && (
              <Button
                variant="contained"
                color="primary"
                startIcon={<AddIcon />}
                onClick={() => {
                  setEventToEdit(null);
                  setEventFormOpen(true);
                }}
                sx={{ ml: 1 }}
              >
                Ereignis erfassen
              </Button>
            )
          }
        />
        <CardContent>
          {gameEvents.length > 0 ? (
            <List>
              {gameEvents.map((event) => {
                const videosForEvent = youtubeLinks[event.id] || [];
                
                const e = event as any;
                let playerDisplay = '';
                let minute = 0;
                let code = '';
                let icon = '';
                let color = '#FFF';

                if (typeof e.player === 'string') {
                  playerDisplay = e.player;
                } else if (e.player && typeof e.player === 'object') {
                  playerDisplay = `${e.player.firstName ?? ''} ${e.player.lastName ?? ''}`.trim();
                }
                if (e.minute) {
                  const totalSeconds = Math.round(e.minute);
                  const mins = Math.floor(totalSeconds / 60);
                  const secs = totalSeconds % 60;
                  minute = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}` as unknown as number;
                } else if (e.timestamp) {
                  minute = formatEventTime(e.timestamp, gameStartDate ?? '') as unknown as number;
                }
                if (e.code) {
                  code = e.code;
                } else if (e.gameEventType?.code) {
                  code = e.gameEventType.code;
                }
                if (e.icon) {
                  icon = e.icon;
                } else if (e.gameEventType?.icon) {
                  icon = e.gameEventType.icon;
                }
                if (e.color) {
                  color = e.color;
                } else if (e.gameEventType?.color) {
                  color = e.gameEventType?.color;
                }
                return (
                  <ListItem
                    key={e.id}
                    alignItems="flex-start"
                    sx={{ 
                      flexDirection: { xs: 'column', md: 'row' },
                      py: 2,
                      gap: { xs: 1, md: 0 }
                    }}
                    secondaryAction={
                      user && (
                        <Box sx={{ 
                          display: 'flex',
                          position: { xs: 'static', md: 'absolute' },
                          right: { md: 16 },
                          top: { md: '50%' },
                          transform: { md: 'translateY(-50%)' },
                          mt: { xs: 1, md: 0 }
                        }}>
                          <IconButton
                            edge="end"
                            onClick={() => {
                              setEventToEdit(event);
                              setEventFormOpen(true);
                            }}
                            sx={{ mr: 1 }}
                            size="small"
                          >
                            <EditIcon />
                          </IconButton>
                          <IconButton
                            edge="end"
                            onClick={() => {
                              setEventToDelete(event);
                            }}
                            size="small"
                          >
                            <DeleteIcon />
                          </IconButton>
                        </Box>
                      )
                    }
                  >
                    <ListItemText
                      sx={{ 
                        width: '100%',
                        m: 0,
                        pr: { xs: 0, md: user ? 10 : 0 }
                      }}
                      primary={
                        <Box sx={{ 
                          display: 'flex',
                          flexDirection: { xs: 'column', md: 'row' },
                          gap: { xs: 1.5, md: 2 },
                          alignItems: { xs: 'flex-start', md: 'center' }
                        }}>
                          {/* Zeit und Icon-Zeile */}
                          <Box sx={{ 
                            display: 'flex',
                            alignItems: 'center',
                            gap: 1,
                            minWidth: { md: '200px' }
                          }}>
                            <Chip 
                              label={minute ?? ''} 
                              size="small" 
                              sx={{ 
                                bgcolor: 'grey.200', 
                                color: 'text.primary',
                                fontWeight: 'bold',
                                minWidth: '60px'
                              }} 
                            />
                            {e.typeColor && (
                              <span style={{
                                display: 'inline-block',
                                width: 12,
                                height: 12,
                                borderRadius: '50%',
                                background: e.typeColor,
                                flexShrink: 0
                              }} />
                            )}
                            <span style={{ 
                              color: color,
                              display: 'flex',
                              alignItems: 'center',
                              fontSize: '1.2rem',
                              flexShrink: 0
                            }}>
                              {getGameEventIconByCode(icon)}
                            </span>
                            <Typography 
                              component="strong" 
                              sx={{ 
                                fontWeight: 'bold',
                                fontSize: { xs: '0.95rem', md: '1rem' }
                              }}
                            >
                              {e.type ?? e?.gameEventType.name ?? 'Unbekannt'}
                            </Typography>
                          </Box>

                          {/* Spieler-Zeile */}
                          <Box sx={{ 
                            display: 'flex',
                            alignItems: 'center',
                            gap: 1,
                            flex: { md: 1 },
                            pl: { xs: 0, md: 0 }
                          }}>
                            <UserAvatar
                              icon={e.player?.playerAvatarUrl}
                              name={playerDisplay || 'Unbekannt'}
                              avatarSize={26}
                              fontSize={12}
                              titleObj={e.player?.titleData && e.player?.titleData.hasTitle ? e.player.titleData : undefined}
                              level={typeof e.player?.level === 'number' ? e.player.level : undefined}
                            />
                          </Box>

                          {/* Beschreibung-Zeile (optional) */}
                          {e.description && (
                            <Box sx={{ 
                              width: { xs: '100%', md: 'auto' },
                              pl: { xs: 0, md: 0 }
                            }}>
                              <Typography 
                                variant="body2" 
                                sx={{ 
                                  color: '#888',
                                  fontStyle: 'italic',
                                  fontSize: { xs: '0.85rem', md: '0.9rem' }
                                }}
                              >
                                {e.description}
                              </Typography>
                            </Box>
                          )}

                          {/* Video-Links (optional) */}
                          {Object.keys(videosForEvent).length > 0 && (
                            <Box sx={{ 
                              display: 'flex',
                              gap: 1.5,
                              flexWrap: 'wrap',
                              alignItems: 'center',
                              width: { xs: '100%', md: 'auto' },
                              pl: { xs: 0, md: 1 },
                              pt: { xs: 0.5, md: 0 }
                            }}>
                              {Object.entries(videosForEvent).map((currentVideo) => (
                                <Box 
                                  key={currentVideo[0]}
                                  sx={{ 
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: 0.5
                                  }}
                                >
                                  <YouTubeIcon fontSize="small" color="error" />
                                  <Link
                                    href={currentVideo[1]}
                                    target="_blank"
                                    sx={{ 
                                      fontSize: { xs: '0.85rem', md: '0.9rem' },
                                      textDecoration: 'none',
                                      '&:hover': { textDecoration: 'underline' }
                                    }}
                                  >
                                    {mappedCameras[Number(currentVideo[0])]}
                                  </Link>
                                </Box>
                              ))}
                            </Box>
                          )}
                        </Box>
                      }
                    />
                  </ListItem>
                );
              })}
            </List>
          ) : (
            <Typography color="text.secondary">
              Keine Ereignisse für dieses Spiel.
            </Typography>
          )}
        </CardContent>
      </Card>

      {/* Videos Card */}
      <Card className="gamevideos-mobile-card" sx={{ mb: 3 }}>
        <CardHeader
          title="Videos"
          action={
            <Box>
              {videos.length > 0 && (
                <Button
                  variant="outlined"
                  startIcon={<ContentCutIcon />}
                  size="small"
                  onClick={() => setVideoSegmentModalOpen(true)}
                  sx={{ mr: 1 }}
                >
                  Schnittliste
                </Button>
              )}
              {canCreateVideos() && (
                <Button
                  variant="contained"
                  startIcon={<VideoIcon />}
                  size="small"
                  onClick={handleOpenAddVideo}
                >
                  Video hinzufügen
                </Button>
              )}
            </Box>
          }
        />
        <CardContent>
          {videos.length > 0 ? (
            <List>
              {videos.map((video) => (
                <ListItem
                  key={video.id}
                  sx={{
                    display: 'flex',
                    flexDirection: { xs: 'column', sm: 'row' },
                    alignItems: { xs: 'flex-start', sm: 'center' },
                    gap: { xs: 1, sm: 2 },
                    py: 1.5,
                  }}
                  disablePadding
                >
                  {/* Thumbnail + Info */}
                  <Box sx={{
                    display: 'flex',
                    alignItems: 'flex-start',
                    gap: 2,
                    flex: 1,
                    minWidth: 0,
                    width: '100%',
                  }}>
                    {video.youtubeId && (
                      <a href={`https://youtu.be/${video.youtubeId}`} target="_blank" rel="noopener noreferrer" style={{ flexShrink: 0 }}>
                        <img
                          src={`https://img.youtube.com/vi/${video.youtubeId}/hqdefault.jpg`}
                          alt="Video Thumbnail"
                          style={{ width: 120, height: 'auto', borderRadius: 4 }}
                        />
                      </a>
                    )}
                    <Box sx={{ minWidth: 0, flex: 1 }}>
                      <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, flexWrap: 'wrap' }}>
                        {video.youtubeId ? (
                          <a href={`https://youtu.be/${video.youtubeId}`} target="_blank" rel="noopener noreferrer">
                            <Typography variant="body1" color="primary" fontWeight="bold">{video.name}</Typography>
                          </a>
                        ) : video.url ? (
                          <a href={video.url} target="_blank" rel="noopener noreferrer">
                            <Typography variant="body1" color="primary" fontWeight="bold">{video.name}</Typography>
                          </a>
                        ) : (
                          <Typography variant="body1">{video.name}</Typography>
                        )}
                        {video.videoType?.name && (
                          <Chip label={video.videoType.name} size="small" />
                        )}
                        {video.length && (
                          <Chip label={`${video.length}s`} size="small" />
                        )}
                      </Box>
                      {video.filePath && (
                        <Typography variant="body2" color="text.secondary" sx={{ mt: 0.5 }}>
                          Dateipfad: {video.filePath}
                        </Typography>
                      )}
                    </Box>
                  </Box>

                  {/* Action Buttons */}
                  {user && (
                    <Box sx={{
                      display: 'flex',
                      alignItems: 'center',
                      gap: 0.5,
                      flexShrink: 0,
                      bgcolor: 'action.hover',
                      borderRadius: 2,
                      px: 0.5,
                      py: 0.25,
                      alignSelf: { xs: 'flex-end', sm: 'center' },
                    }}>
                      <IconButton
                        color="primary"
                        aria-label="Abspielen"
                        onClick={() => handleOpenPlayVideo(video)}
                        size="small"
                      >
                        <YouTubeIcon />
                      </IconButton>
                      <IconButton onClick={() => handleOpenEditVideo(video)} size="small">
                        <EditIcon />
                      </IconButton>
                      <IconButton color="error" onClick={() => setVideoToDelete(video)} size="small">
                        <DeleteIcon />
                      </IconButton>
                    </Box>
                  )}
                </ListItem>
              ))}
            </List>
          ) : (
            <Typography color="text.secondary">
              Keine Videos für dieses Spiel.
            </Typography>
          )}
        </CardContent>
      </Card>

      {/* Video Play Modal */}
      <VideoPlayModal
        ref={videoPlayerRef}
        open={playVideoModalOpen}
        onClose={handleClosePlayVideo}
        videoId={videoToPlay?.youtubeId || undefined}
        videoName={videoToPlay?.name}
        videoObj={videoToPlay ? { 
          id: videoToPlay.id,
          youtubeId: videoToPlay.youtubeId || undefined,
          gameStart: videoToPlay.gameStart ?? null, 
          length: videoToPlay.length ?? 0,
          camera: videoToPlay.camera || undefined
        } : { id: 0, youtubeId: undefined, gameStart: null, length: 0 }}
        gameEvents={gameEvents}
        gameStartDate={gameStartDate || ''}
        gameId={gameId}
        onEventUpdated={handleEventUpdated}
        allVideos={videos}
        youtubeLinks={youtubeLinks}
      >
        {canCreateEvents() && (
          <Box sx={{ mt: 2, display: 'flex', justifyContent: 'flex-end' }}>
            <Button
              variant="contained"
              startIcon={<AddIcon />}
              onClick={handleCreateEventFromVideo}
              color="primary"
            >
              Spielereignis anlegen
            </Button>
          </Box>
        )}
        {/* Das Event-Formular wird als Overlay im VideoPlayModal eingeblendet */}
        <GameEventModal
          open={videoEventFormOpen}
          onClose={() => {
            setVideoEventFormOpen(false);
            setVideoEventInitialMinute(undefined);
            setEventToEdit(null);
          }}
          onSuccess={() => {
            setVideoEventFormOpen(false);
            setVideoEventInitialMinute(undefined);
            setEventToEdit(null);
            handleEventFormSuccess();
          }}
          gameId={gameId!}
          game={game}
          existingEvent={eventToEdit}
          initialMinute={videoEventInitialMinute}
        />
      </VideoPlayModal>

      {/* Video Modal (Bearbeiten/Hinzufügen) */}
      <VideoModal
        open={videoDialogOpen}
        onClose={handleCloseVideoDialog}
        onSave={handleSaveVideo}
        videoTypes={videoTypes}
        cameras={cameras}
        initialData={videoToEdit || undefined}
        loading={videoDialogLoading}
      />

      {/* Video Delete Confirmation */}
      <ConfirmationModal
        open={!!videoToDelete}
        onClose={() => setVideoToDelete(null)}
        onConfirm={handleDeleteVideo}
        title="Video löschen"
        message={`Soll das Video "${videoToDelete?.name}" wirklich gelöscht werden?`}
        confirmText="Löschen"
        confirmColor="error"
      />

      {/* Floating Action Button for quick event creation */}
      {canCreateEvents() && (
        <Fab
          color="primary"
          aria-label="add event"
          sx={{ position: 'fixed', bottom: 16, right: 16 }}
          onClick={() => setEventFormOpen(true)}
        >
          <AddIcon />
        </Fab>
      )}

      {/* Confirmation Modal for Event Deletion */}
      <ConfirmationModal
        open={!!eventToDelete}
        onClose={() => setEventToDelete(null)}
        onConfirm={handleDeleteEvent}
        title="Ereignis löschen"
        message={`Soll das Ereignis "${eventToDelete?.gameEventType?.name || eventToDelete?.type || 'Unbekannt'}" wirklich gelöscht werden?`}
        confirmText="Löschen"
        confirmColor="error"
      />

      {/* Game Event Modal außerhalb für andere Fälle (z.B. FAB) */}
      <GameEventModal
        open={eventFormOpen}
        onClose={() => {
          setEventFormOpen(false);
          setEventToEdit(null);
        }}
        onSuccess={handleEventFormSuccess}
        gameId={gameId!}
        game={game}
        existingEvent={eventToEdit}
      />

      <WeatherModal
        open={weatherModalOpen}
        onClose={() => setWeatherModalOpen(false)}
        eventId={selectedEventId}
      />

      {/* Video Segment Modal */}
      <VideoSegmentModal
        open={videoSegmentModalOpen}
        onClose={() => setVideoSegmentModalOpen(false)}
        videos={videos}
        gameId={gameId!}
      />
    </Box>
  );
}

export default function GameDetails(props: GameDetailsProps) {
  return (
    <ToastProvider>
      <GameDetailsInner {...props} />
    </ToastProvider>
  );
}
